<?php
/**
 * @version		3.0.0
 * @package		K2
 * @author		JoomlaWorks http://www.joomlaworks.net
 * @copyright	Copyright (c) 2006 - 2013 JoomlaWorks Ltd. All rights reserved.
 * @license		GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 */

// no direct access
defined('_JEXEC') or die ;

require_once JPATH_ADMINISTRATOR.'/components/com_k2/models/items.php';
require_once JPATH_ADMINISTRATOR.'/components/com_k2/resources/resource.php';
require_once JPATH_ADMINISTRATOR.'/components/com_k2/resources/categories.php';
require_once JPATH_ADMINISTRATOR.'/components/com_k2/resources/users.php';
require_once JPATH_ADMINISTRATOR.'/components/com_k2/resources/tags.php';
require_once JPATH_ADMINISTRATOR.'/components/com_k2/resources/attachments.php';
require_once JPATH_ADMINISTRATOR.'/components/com_k2/helpers/extrafields.php';
require_once JPATH_ADMINISTRATOR.'/components/com_k2/helpers/images.php';
require_once JPATH_SITE.'/components/com_k2/helpers/route.php';

/**
 * K2 item resource class.
 */

class K2Items extends K2Resource
{

	/**
	 * @var array	Items instances container.
	 */
	protected static $instances = array();

	/**
	 * @var integer	Hits.
	 */
	public $hits = 0;

	/**
	 * Constructor.
	 *
	 * @param object $data
	 *
	 * @return void
	 */

	public function __construct($data)
	{
		parent::__construct($data);
		self::$instances[$this->id] = $this;
	}

	/**
	 * Gets an item instance.
	 *
	 * @param integer $id	The id of the item to get.
	 *
	 * @return K2Item The item object.
	 */
	public static function getInstance($id)
	{
		if (empty(self::$instances[$id]))
		{
			K2Model::addIncludePath(JPATH_ADMINISTRATOR.'/components/com_k2/models');
			$model = K2Model::getInstance('Items', 'K2Model');
			if (is_numeric($id))
			{
				$model->setState('id', $id);
			}
			else
			{
				$model->setState('alias', $id);
			}
			$item = $model->getRow();
			if ($item->id)
			{
				self::$instances[$item->id] = $item;
				self::$instances[$item->alias] = $item;
			}
			else
			{
				self::$instances[$id] = $item;
			}

		}
		return self::$instances[$id];
	}

	/**
	 * Prepares the row for output
	 *
	 * @param string $mode	The mode for preparing data. 'site' for fron-end data, 'admin' for administrator operations.
	 *
	 * @return void
	 */
	public function prepare($mode = null)
	{

		// Prepare generic properties like dates and authors
		parent::prepare($mode);

		// Category
		$this->category = $this->getCategory();

		// Edit link
		$this->editLink = '#items/edit/'.$this->id;

		// Link
		$this->link = $this->getLink();

		// URL (absolute link)
		$this->url = $this->getUrl();

		// PrintLink
		$this->printLink = $this->getPrintLink();

		// Email link
		$this->emailLink = $this->getEmailLink();

		// Permisisons
		$user = JFactory::getUser();
		if ($user->guest)
		{
			$this->canEdit = false;
			$this->canEditState = false;
			$this->canEditFeaturedState = false;
			$this->canDelete = false;
			$this->canSort = false;
		}
		else
		{
			$this->canEdit = $user->authorise('k2.item.edit', 'com_k2.item.'.$this->id) || ($user->id == $this->created_by && $user->authorise('k2.item.edit.own', 'com_k2.item.'.$this->id));
			$this->canEditState = $user->authorise('k2.item.edit.state', 'com_k2.item.'.$this->id);
			$this->canEditFeaturedState = $user->authorise('k2.item.edit.state.featured', 'com_k2.item.'.$this->id);
			$this->canDelete = $user->authorise('k2.item.delete', 'com_k2.item.'.$this->id);
			$this->canSort = $user->authorise('k2.item.edit', 'com_k2');
		}

		// Media
		$this->media = $this->getMedia();

		// Galleries
		$this->galleries = $this->getGalleries();

		// Hits
		$this->hits = (int)$this->hits;

		// Start and end dates
		if ((int)$this->start_date > 0)
		{
			$this->startDate = JHtml::_('date', $this->start_date, 'Y-m-d');
			$this->startTime = JHtml::_('date', $this->start_date, 'H:i');
		}
		if ((int)$this->end_date > 0)
		{
			$this->endDate = JHtml::_('date', $this->end_date, 'Y-m-d');
			$this->endTime = JHtml::_('date', $this->end_date, 'H:i');
		}

		// Tags
		$this->tags = $this->getTags();
		$tagsValue = array();
		foreach ($this->tags as $tag)
		{
			$tagsValue[] = $tag->name;
		}
		$this->tagsValue = implode(',', $tagsValue);

		// Images
		$this->images = $this->getImages();
		$this->image = $this->getImage();

		// Attachments
		$this->attachments = $this->getAttachments();

		// Author
		$this->author = $this->getAuthor();

		// Revisions
		if ($this->canEdit)
		{
			$this->revisions = $this->getRevisions();
		}
	}

	public function getCategory()
	{
		$category = null;
		if ($this->id)
		{
			$category = K2Categories::getInstance($this->catid);
		}
		return $category;
	}

	public function getExtraFields()
	{
		$extraFields = array();
		if ($this->id)
		{
			$extraFields = K2HelperExtraFields::getItemExtraFields($this->catid, $this->extra_fields);
		}
		return $extraFields;
	}

	public function getTags()
	{
		$instances = array();
		$tags = json_decode($this->tags);
		if ($this->id && is_array($tags) && count($tags))
		{

			foreach ($tags as $tag)
			{
				$instance = K2Tags::getInstance($tag->id);
				if ($instance->state)
				{
					$instances[] = $instance;
				}
			}

			/*
			 $tagIds = array();
			 foreach ($tags as $tag)
			 {
			 $tagIds[] = (int)$tag->id;
			 }
			 $application = JFactory::getApplication();
			 $model = K2Model::getInstance('Tags');
			 if ($application->isSite())
			 {
			 $model->setState('state', 1);
			 }
			 $model->setState('id', $tagIds);
			 $tags = $model->getRows();
			 */
		}
		return $instances;
	}

	public function getAuthor()
	{
		$author = null;
		if ($this->id)
		{
			$author = K2Users::getInstance($this->created_by);
		}
		return $author;
	}

	public function getImages()
	{
		$images = array();
		if ($this->id)
		{
			$images = K2HelperImages::getItemImages($this);
		}
		return $images;
	}

	public function getImage($size = null)
	{
		$image = null;
		if (!isset($this->images))
		{
			$this->images = $this->getImages();
		}
		if (count($this->images))
		{
			if (is_null($size))
			{
				$image = end($this->images);
			}
			else if (array_key_exists($size, $this->images))
			{
				$image = $this->images[$size];
			}
		}
		return $image;
	}

	public function getGalleries()
	{
		// Initialize value
		$galleries = array();

		// Process only if value is set
		if ($this->galleries)
		{
			// Decode the value
			$galleries = json_decode($this->galleries);

			// Get params
			$params = JComponentHelper::getParams('com_k2');

			// Get dispatcher
			$dispatcher = JDispatcher::getInstance();

			// Import content plugins
			JPluginHelper::importPlugin('content');

			foreach ($galleries as $gallery)
			{
				$tag = ($gallery->upload) ? $this->id.'/'.$gallery->upload : $gallery->url;
				$gallery->text = '{gallery}'.$tag.'{/gallery}';

				// Render the gallery
				$dispatcher->trigger('onContentPrepare', array('com_k2', &$gallery, &$params, 0));

				// Restore
				$gallery->output = $gallery->text;
				unset($gallery->text);
			}
		}

		return $galleries;
	}

	public function getMedia()
	{
		// Initialize value
		$media = array();

		// Process only if value is set
		if ($this->media)
		{
			// Decode value
			$media = json_decode($this->media);

			// Get params
			$params = JComponentHelper::getParams('com_k2');

			// Get dispatcher
			$dispatcher = JDispatcher::getInstance();

			// Import content plugins
			JPluginHelper::importPlugin('content');

			foreach ($media as $entry)
			{
				if ($entry->embed)
				{
					$entry->output = $entry->embed;
				}
				else
				{
					if ($entry->upload)
					{
						$parts = explode('.', $entry->upload);
						$extension = strtolower(end($parts));
						$tag = '{'.$extension.'}'.$entry->upload.'{'.$extension.'}';
					}
					else if ($entry->provider)
					{
						$tag = '{'.$entry->provider.'}'.$entry->id.'{'.$entry->provider.'}';
					}
					else if ($entry->url)
					{
						$parts = explode('.', $entry->url);
						$extension = strtolower(end($parts));
						$tag = '{'.$extension.'remote}'.$entry->url.'{'.$extension.'remote}';
					}

					$entry->text = $tag;

					// Render media
					$dispatcher->trigger('onContentPrepare', array('com_k2', &$entry, &$params, 0));

					// Restore
					$entry->output = $entry->text;
					unset($entry->text);
				}
			}
		}

		return $media;
	}

	public function getAttachments()
	{
		$attachments = array();
		$this->attachments = json_decode($this->attachments);
		if (is_array($this->attachments))
		{

			$application = JFactory::getApplication();
			$model = K2Model::getInstance('Attachments');
			$model->setState('id', $this->attachments);
			$attachments = $model->getRows();
		}
		return $attachments;
	}

	public function getLink()
	{
		return JRoute::_(K2HelperRoute::getItemRoute($this->id.':'.$this->alias, $this->catid));
	}

	public function getUrl()
	{
		return JRoute::_(K2HelperRoute::getItemRoute($this->id.':'.$this->alias, $this->catid), true, -1);
	}

	public function getPrintLink()
	{
		JRoute::_(K2HelperRoute::getItemRoute($this->id.':'.$this->alias, $this->catid).'&print=1');
	}

	public function getEmailLink()
	{
		require_once JPATH_SITE.'/components/com_mailto/helpers/mailto.php';
		$application = JFactory::getApplication();
		$template = $application->getTemplate();
		return JRoute::_('index.php?option=com_mailto&tmpl=component&template='.$template.'&link='.MailToHelper::addLink($this->url));
	}

	public function getComments($offset = 0)
	{
		$user = JFactory::getUser();
		$params = JComponentHelper::getParams('com_k2');
		$comments = array();
		if ($this->id)
		{
			// Get comments model
			$model = K2Model::getInstance('Comments');
			$model->setState('itemId', $this->id);
			$model->setState('limit', (int)$params->get('commentsLimit', 10));
			$model->setState('limitstart', $offset);
			$model->setState('sorting', 'id');
			$model->setState('state', 1);
			if ($params->get('commentsOrdering') == 'ASC')
			{
				$model->setState('sorting', 'id.asc');
			}
			// Load comments
			$comments = $model->getRows();
		}
		return $comments;
	}

	public function getNumOfComments()
	{
		$result = 0;
		if ($this->id)
		{
			$model = K2Model::getInstance('Comments');
			$model->setState('itemId', $this->id);
			$model->setState('state', 1);
			$model->setState('sorting', 'id');
			$result = $model->countRows();
		}
		return $result;
	}

	public function getPrevious()
	{
		if ($this->ordering == 1)
		{
			return false;
		}
		$model = K2Model::getInstance('Items');
		$model->setState('site', 1);
		$model->setState('category', $this->catid);
		$model->setState('sorting', 'custom');
		$model->setState('sorting.custom.value', 'item.ordering');
		$model->setState('sorting.custom.direction', 'ASC');
		$model->setState('ordering.value', $this->ordering);
		$model->setState('ordering.operator', '<');
		$model->setState('limit', 1);
		$rows = $model->getRows();
		return ($rows && isset($rows[0])) ? $rows[0] : false;
	}

	public function getNext()
	{
		$model = K2Model::getInstance('Items');
		$model->setState('site', 1);
		$model->setState('category', $this->catid);
		$model->setState('sorting', 'custom');
		$model->setState('sorting.custom.value', 'item.ordering');
		$model->setState('sorting.custom.direction', 'ASC');
		$model->setState('ordering.value', $this->ordering);
		$model->setState('ordering.operator', '>');
		$model->setState('limit', 1);
		$rows = $model->getRows();
		return ($rows && isset($rows[0])) ? $rows[0] : false;
	}

	public function getRelated($limit = 10)
	{
		$rows = array();
		if (count($this->tags))
		{
			$model = K2Model::getInstance('Items');
			$model->setState('site', 1);
			$model->setState('limit', 10);
			$tagIds = array();
			foreach ($this->tags as $tag)
			{
				$tagIds[] = $tag->id;
			}
			$model->setState('tag', $tagIds);
			$model->setState('tag.exclude.item', $this->id);
			$model->setState('limit', $limit);
			$model->setState('limitstart', 0);
			$rows = $model->getRows();
		}
		return $rows;
	}

	public function getLatestByAuthor($limit = 10)
	{
		$rows = array();
		if (trim($this->created_by_alias) == '')
		{
			$rows = $this->author->getLatest($limit, $this->id);
		}
		return $rows;
	}

	public function getEvents($context = 'com_k2.item', &$params = null, $offset = 0, $k2Plugins = true, $jPlugins = true)
	{
		// Params
		if (is_null($params))
		{
			$params = JComponentHelper::getParams('com_k2');
		}

		// Get dispatcher
		$dispatcher = JDispatcher::getInstance();

		// Create the text variable
		$this->text = $this->introtext.'{K2Splitter}'.$this->fulltext;

		// Create the event object with null values
		$events = new stdClass;
		$events->AfterDisplayTitle = '';
		$events->BeforeDisplayContent = '';
		$events->AfterDisplayContent = '';
		$events->K2BeforeDisplay = '';
		$events->K2AfterDisplay = '';
		$events->K2AfterDisplayTitle = '';
		$events->K2BeforeDisplayContent = '';
		$events->K2AfterDisplayContent = '';

		// Content plugins
		if ($jPlugins)
		{
			// Import content plugins
			JPluginHelper::importPlugin('content');

			$dispatcher->trigger('onContentPrepare', array($context, &$this, &$params, $offset));
			$results = $dispatcher->trigger('onContentAfterTitle', array($context, &$this, &$params, $offset));
			$events->AfterDisplayTitle = trim(implode("\n", $results));
			$results = $dispatcher->trigger('onContentBeforeDisplay', array($context, &$this, &$params, $offset));
			$events->BeforeDisplayContent = trim(implode("\n", $results));
			$results = $dispatcher->trigger('onContentAfterDisplay', array($context, &$this, &$params, $offset));
			$events->AfterDisplayContent = trim(implode("\n", $results));

		}

		// K2 plugins
		if ($k2Plugins)
		{
			// Import K2 plugins
			JPluginHelper::importPlugin('k2');

			$results = $dispatcher->trigger('onK2BeforeDisplay', array(&$this, &$params, $offset));
			$events->K2BeforeDisplay = trim(implode("\n", $results));

			$results = $dispatcher->trigger('onK2AfterDisplay', array(&$this, &$params, $offset));
			$events->K2AfterDisplay = trim(implode("\n", $results));

			$results = $dispatcher->trigger('onK2AfterDisplayTitle', array(&$this, &$params, $offset));
			$events->K2AfterDisplayTitle = trim(implode("\n", $results));

			$results = $dispatcher->trigger('onK2BeforeDisplayContent', array(&$this, &$params, $offset));
			$events->K2BeforeDisplayContent = trim(implode("\n", $results));

			$results = $dispatcher->trigger('onK2AfterDisplayContent', array(&$this, &$params, $offset));
			$events->K2AfterDisplayContent = trim(implode("\n", $results));

			$dispatcher->trigger('onK2PrepareContent', array(&$this, &$params, $offset));

			if ($params->get('comments'))
			{
				$results = $dispatcher->trigger('onK2CommentsCounter', array(&$this, &$params, $offset));
				$events->K2CommentsCounter = trim(implode("\n", $results));

				$results = $dispatcher->trigger('onK2CommentsBlock', array(&$this, &$params, $offset));
				$events->K2CommentsBlock = trim(implode("\n", $results));
			}
		}

		// Restore introtext and fulltext
		list($this->introtext, $this->fulltext) = explode('{K2Splitter}', $this->text);

		// Unset the text
		unset($this->text);

		// return
		return $events;
	}

	public function getRevisions()
	{
		$revisions = array();
		$params = JComponentHelper::getParams('com_k2');
		if ($this->id && $params->get('revisions'))
		{
			$model = K2Model::getInstance('Revisions');
			$model->setState('itemId', $this->id);
			$revisions = $model->getRows();
		}
		return $revisions;
	}

	public function hit()
	{
		$model = K2Model::getInstance('Statistics');
		$model->increaseItemHitsCounter($this->id);
		$this->hits++;
	}

	public function checkSiteAccess()
	{
		// Get date
		$date = JFactory::getDate();
		$now = $date->toSql();

		// State check
		if ($this->state < 1 || $this->category->state < 1 || (int)$this->id < 1)
		{
			JError::raiseError(404, JText::_('K2_NOT_FOUND'));
			return false;
		}
		if ((int)$this->publish_up > 0 && $this->publish_up > $now)
		{
			JError::raiseError(404, JText::_('K2_NOT_FOUND'));
			return false;
		}
		if ((int)$this->publish_down > 0 && $this->publish_down < $now)
		{
			JError::raiseError(404, JText::_('K2_NOT_FOUND'));
			return false;
		}

		// Get user
		$user = JFactory::getUser();
		$viewLevels = $user->getAuthorisedViewLevels();

		// Access check
		if (!in_array($this->access, $viewLevels) || !in_array($this->category->access, $viewLevels))
		{
			if ($user->guest)
			{
				// Get application
				$application = JFactory::getApplication();

				// Get document
				$document = JFactory::getDocument();

				// In front end HTML requests redirect the user to the login page
				if ($application->isSite() && $document->getType() == 'html')
				{
					require_once JPATH_SITE.'/components/com_users/helpers/route.php';
					$uri = JFactory::getURI();
					$url = 'index.php?option=com_users&view=login&return='.base64_encode($uri->toString()).'&Itemid='.UsersHelperRoute::getLoginRoute();
					$application->redirect(JRoute::_($url, false), JText::_('K2_YOU_NEED_TO_LOGIN_FIRST'));
				}

				// Return false
				return false;
			}
			else
			{
				JError::raiseError(403, JText::_('K2_NOT_AUTHORISED'));
				return false;
			}
		}

		return true;

	}

}
