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

require_once JPATH_ADMINISTRATOR.'/components/com_k2/resources/resource.php';

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

		// Prepare specific properties
		$this->link = '#items/edit/'.$this->id;
		JFilterOutput::objectHTMLSafe($this, ENT_QUOTES, array(
			'media',
			'metadata',
			'plugins',
			'params',
			'rules'
		));

		$this->media = json_decode($this->media);

		$this->hits = (int)$this->hits;

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

		$this->tags = $this->getTags();

		$this->images = $this->getImages();

		$this->attachments = $this->getAttachments();
	}

	public function getTags()
	{
		$tags = array();
		if ($this->id)
		{
			K2Model::addIncludePath(JPATH_ADMINISTRATOR.'/components/com_k2/models');
			$model = K2Model::getInstance('Tags', 'K2Model');
			$model->setState('itemId', $this->id);
			$tags = $model->getRows();
		}
		return $tags;
	}

	public function getImages()
	{
		$images = array();
		$this->image_flag = (int)$this->image_flag;
		if ($this->id && $this->image_flag)
		{
			$sizes = array(
				'XL' => 600,
				'L' => 400,
				'M' => 240,
				'S' => 180,
				'XS' => 100
			);
			$baseFileName = md5('Image'.$this->id);
			$modifiedDate = ((int)$this->modified > 0) ? $this->modified : $this->created;
			$timestamp = JFactory::getDate($modifiedDate)->toUnix();
			foreach ($sizes as $size => $width)
			{
				$images[$size] = JURI::root(true).'/media/k2/items/cache/'.$baseFileName.'_'.$size.'.jpg?t='.$timestamp;
			}

			$this->imagePreview = $this->images[$size];
		}
		return $images;
	}

	public function getAttachments()
	{
		$attachments = array();
		if ($this->id)
		{
			K2Model::addIncludePath(JPATH_ADMINISTRATOR.'/components/com_k2/models');
			$model = K2Model::getInstance('Attachments', 'K2Model');
			$model->setState('itemId', $this->id);
			$attachments = $model->getRows();
		}
		return $attachments;
	}

	public function checkSiteAccess()
	{
		// Get date
		$date = JFactory::getDate();
		$now = $date->toSql();

		// Published check
		if (!$this->published || !$this->categoryPublished || $this->trashed || $this->categoryTrashed)
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
		if (!in_array($item->access, $viewLevels) || !in_array($item->categoryAccess, $viewLevels))
		{
			if ($user->guest)
			{
				require_once JPATH_SITE.'/components/com_users/helpers/route.php';
				$uri = JFactory::getURI();
				$url = 'index.php?option=com_users&view=login&return='.base64_encode($uri->toString()).'&Itemid='.UsersHelperRoute::getLoginRoute();
				$application = JFactory::getApplication();
				$application->redirect(JRoute::_($url, false), JText::_('K2_YOU_NEED_TO_LOGIN_FIRST'));
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
