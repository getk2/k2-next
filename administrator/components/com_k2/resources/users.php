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
require_once JPATH_SITE.'/components/com_k2/helpers/route.php';
require_once JPATH_ADMINISTRATOR.'/components/com_k2/helpers/images.php';

/**
 * K2 user resource class.
 */

class K2Users extends K2Resource
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
	 * @return K2Tag The tag object.
	 */
	public static function getInstance($id)
	{
		if (empty(self::$instances[$id]))
		{
			K2Model::addIncludePath(JPATH_ADMINISTRATOR.'/components/com_k2/models');
			$model = K2Model::getInstance('Users');
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

		if ($this->id)
		{
			// Prepare specific properties
			$this->editLink = '#users/edit/'.$this->id;

			// Link
			$this->link = $this->getLink();

			// URL
			$this->url = $this->getUrl();

			$this->enabled = (int)!$this->block;
			$this->activated = (int)!$this->activation;
			$this->groupsValue = isset($this->groups) ? implode(', ', $this->groups) : '';

			// Image
			$this->image = $this->getImage();
			
			// Unset password
			$this->password = '';
		}

	}

	public function getLink()
	{
		if (JFactory::getConfig()->get('unicodeslugs') == 1)
		{
			$this->alias = JFilterOutput::stringURLUnicodeSlug($this->name);
		}
		else
		{
			$this->alias = JFilterOutput::stringURLSafe($this->name);
		}
		return JRoute::_(K2HelperRoute::getUserRoute($this->id.':'.$this->alias));
	}

	public function getFeedLink()
	{
		if (JFactory::getConfig()->get('unicodeslugs') == 1)
		{
			$this->alias = JFilterOutput::stringURLUnicodeSlug($this->name);
		}
		else
		{
			$this->alias = JFilterOutput::stringURLSafe($this->name);
		}
		return JRoute::_(K2HelperRoute::getUserRoute($this->id.':'.$this->alias).'&format=feed');
	}

	public function getUrl()
	{
		if (JFactory::getConfig()->get('unicodeslugs') == 1)
		{
			$this->alias = JFilterOutput::stringURLUnicodeSlug($this->name);
		}
		else
		{
			$this->alias = JFilterOutput::stringURLSafe($this->name);
		}
		return JRoute::_(K2HelperRoute::getUserRoute($this->id.':'.$this->alias), true, -1);
	}

	public function getImage()
	{
		return K2HelperImages::getUserImage($this);
	}

	public function getNumOfComments()
	{
		K2Model::addIncludePath(JPATH_ADMINISTRATOR.'/components/com_k2/models');
		$model = K2Model::getInstance('Comments');
		$model->setState('userId', $this->id);
		$model->setState('state', 1);
		$numOfComments = $model->countRows();
		return $numOfComments;
	}
	
	public function getExtraFields()
	{
		$extraFields = array();
		if ($this->id)
		{
			$extraFields = K2HelperExtraFields::getUserExtraFields($this->id, $this->extra_fields);
		}
		return $extraFields;
	}

	public function getEvents()
	{
		$events = new stdClass;
		$dispatcher = JDispatcher::getInstance();
		JPluginHelper::importPlugin('k2');
		$results = $dispatcher->trigger('onK2UserDisplay', array(&$this, &$params, 0));
		$events->K2UserDisplay = trim(implode("\n", $results));
		return $events;
	}

	public function getLatest($limit = 10, $exclude = null)
	{
		K2Model::addIncludePath(JPATH_ADMINISTRATOR.'/components/com_k2/models');
		$model = K2Model::getInstance('Items');
		$model->setState('site', true);
		$model->setState('author', $this->id);
		$model->setState('ordering', 'created');
		if ($exclude)
		{
			$model->setState('exclude', $exclude);
		}
		$model->setState('limit', 0);
		$model->setState('limitstart', 0);
		$rows = $model->getRows();
		return $rows;
	}

	public function checkSiteAccess()
	{
		// State check
		if ((int)$this->block > 0)
		{
			JError::raiseError(404, JText::_('K2_NOT_FOUND'));
			return false;
		}
		return true;
	}

}
