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
			$model = K2Model::getInstance('Items');
			$model->setState('id', $id);
			$item = $model->getRow();
			self::$instances[$id] = $item;
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

		// Category params
		$this->categoryParams = new JRegistry($this->categoryParams);

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

		// Attachments
		$this->attachments = $this->getAttachments();

		// Author
		$this->author = $this->getAuthor();
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
		$tags = array();
		$tagIds = json_decode($this->tags);
		if ($this->id && is_array($tagIds))
		{
			$application = JFactory::getApplication();
			$model = K2Model::getInstance('Tags');
			if ($application->isSite())
			{
				$model->setState('state', 1);
			}
			$model->setState('id', $tagIds);
			$tags = $model->getRows();
		}
		return $tags;
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
			require_once JPATH_ADMINISTRATOR.'/components/com_k2/helpers/images.php';
			$result = K2HelperImages::getResourceImages('item', $this);
			$this->_image = json_decode($this->image);
			if (count($result->images))
			{
				$images = $result->images;
				$this->image = $images['S'];
				$this->image_caption = $this->_image->caption;
				$this->image_credits = $this->_image->credits;
				$this->image_alt = $this->_image->caption ? $this->_image->caption : $this->title;
				$this->imageWidth = 180;
				$this->_image->preview = $this->image;
				$this->_image->id = $result->id;
			}
			else
			{
				$this->image = false;
				$this->image_caption = '';
				$this->image_credits = '';
			}
		}
		return $images;
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
				$dispatcher->trigger('onContentPrepare', array(
					'com_k2',
					&$gallery,
					&$params,
					0
				));

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
					$dispatcher->trigger('onContentPrepare', array(
						'com_k2',
						&$entry,
						&$params,
						0
					));

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
		return JRoute::_(K2HelperRoute::getItemRoute($this->id.':'.$this->alias));
	}

	public function getUrl()
	{
		$uri = JUri::getInstance();
		$base = $uri->toString(array(
			'scheme',
			'host',
			'port'
		));
		return $base.JRoute::_('index.php?option=com_k2&view=item&id='.$this->id, false);
	}

	public function getPrintLink()
	{
		return JRoute::_('index.php?option=com_k2&view=item&id='.$this->id.'&print=1');
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
			// Set permissions to variables
			$canReport = $params->get('commentsReporting') == '1' || ($params->get('commentsReporting') == '2' && !$user->guest);
			$canReportUser = $user->authorise('core.admin', 'com_k2');
			$canEditComments = $user->authorise('k2.comment.edit', 'com_k2');

			// Get comments model
			$model = K2Model::getInstance('Comments');
			$model->setState('itemId', $this->id);
			$model->setState('limit', (int)$params->get('commentsLimit', 10));
			$model->setState('limitstart', $offset);
			$model->setState('sorting', 'id');
			if ($params->get('commentsOrdering') == 'ASC')
			{
				$model->setState('sorting', 'id.asc');
			}

			// User cannot edit any comments. Load only the published comments
			if (!$canEditComments)
			{
				$model->setState('state', 1);
			}

			// Load comments
			$comments = $model->getRows();

			// Comments pagination
			jimport('joomla.html.pagination');
			$pagination = new JPagination($model->countRows(), $offset, (int)$params->get('commentsLimit', 10));

			// User ids array
			$userIds = array();

			// Prepare comments
			foreach ($comments as $comment)
			{
				$comment->canReport = $canReport && $user->id != $comment->userId;
				$comment->canReportUser = false;
				$comment->canEdit = $canEditComments;
				$comment->isAuthorResponse = !$this->created_by_alias && $comment->userId == $this->created_by;
				$comment->date = JHtml::_('date', $comment->date, JText::_('K2_DATE_FORMAT_LC2'));
				if ($comment->userId)
				{
					$comment->canReportUser = $canReport && $user->id != $comment->userId;
				}
			}

			// Load the comments users in one query
			$userIds = array_unique($userIds);
			if (count($userIds))
			{
				$model = K2Model::getInstance('Users');
				$model->setState('id', $userIds);
				$users = $model->getRows();
			}

			// Assign the user data to comments
			foreach ($comments as $comment)
			{
				$comment->user = new stdClass;
				if ($comment->userId)
				{
					$commentUser = K2Users::getInstance($comment->userId);
					$comment->user->name = $commentUser->name;
					$comment->user->link = $commentUser->link;
					$comment->user->image = $commentUser->image;
					if($comment->user->image)
					{
						$comment->user->image = substr($comment->user->image, strlen(JURI::root(true)));
					}
				}
				else
				{
					$comment->user->name = $comment->name;
					$comment->user->link = false;
					$comment->user->image = false;
				}
				unset($comment->email);
				unset($comment->ip);
				unset($comment->hostname);
			}

		}

		$response = new stdClass;
		$response->rows = $comments;
		$response->pagination = $pagination;

		return $response;
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
		$previous = $model->getRow();
		return $previous;
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
		$next = $model->getRow();
		return $next;
	}

	public function triggerPlugins($context, &$params, $offset)
	{
		// Get dispatcher
		$dispatcher = JDispatcher::getInstance();

		// Import content plugins
		JPluginHelper::importPlugin('content');

		// Import K2 plugins
		JPluginHelper::importPlugin('k2');

		// Create the text variable
		$this->text = $this->introtext.'{K2Splitter}'.$this->fulltext;

		// Create the event object
		$this->events = new stdClass;

		// Content plugins
		$dispatcher->trigger('onContentPrepare', array(
			$context,
			&$this,
			&$params,
			$offset
		));
		$results = $dispatcher->trigger('onContentAfterTitle', array(
			$context,
			&$this,
			&$params,
			$offset
		));
		$this->events->AfterDisplayTitle = trim(implode("\n", $results));
		$results = $dispatcher->trigger('onContentBeforeDisplay', array(
			$context,
			&$this,
			&$params,
			$offset
		));
		$this->events->BeforeDisplayContent = trim(implode("\n", $results));
		$results = $dispatcher->trigger('onContentAfterDisplay', array(
			$context,
			&$this,
			&$params,
			$offset
		));
		$this->events->AfterDisplayContent = trim(implode("\n", $results));

		// K2 plugins
		$results = $dispatcher->trigger('onK2BeforeDisplay', array(
			&$this,
			&$params,
			$offset
		));
		$this->events->K2BeforeDisplay = trim(implode("\n", $results));

		$results = $dispatcher->trigger('onK2AfterDisplay', array(
			&$this,
			&$params,
			$offset
		));
		$this->events->K2AfterDisplay = trim(implode("\n", $results));

		$results = $dispatcher->trigger('onK2AfterDisplayTitle', array(
			&$this,
			&$params,
			$offset
		));
		$this->events->K2AfterDisplayTitle = trim(implode("\n", $results));

		$results = $dispatcher->trigger('onK2BeforeDisplayContent', array(
			&$this,
			&$params,
			$offset
		));
		$this->events->K2BeforeDisplayContent = trim(implode("\n", $results));

		$results = $dispatcher->trigger('onK2AfterDisplayContent', array(
			&$this,
			&$params,
			$offset
		));
		$this->events->K2AfterDisplayContent = trim(implode("\n", $results));

		$dispatcher->trigger('onK2PrepareContent', array(
			&$this,
			&$params,
			$offset
		));

		// Restore introtext and fulltext
		list($this->introtext, $this->fulltext) = explode('{K2Splitter}', $this->text);

		// Unset the text
		unset($this->text);
	}

	public function checkSiteAccess()
	{
		// Get date
		$date = JFactory::getDate();
		$now = $date->toSql();

		// State check
		if ($this->state < 1 || $this->categoryState < 1)
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
		if (!in_array($this->access, $viewLevels) || !in_array($this->categoryAccess, $viewLevels))
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
