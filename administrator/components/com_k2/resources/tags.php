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

/**
 * K2 item resource class.
 */

class K2Tags extends K2Resource
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
	 * Gets a tag instance.
	 *
	 * @param integer $id	The id of the tag to get.
	 *
	 * @return K2Tag The tag object.
	 */
	public static function getInstance($id)
	{
		if (empty(self::$instances[$id]))
		{
			K2Model::addIncludePath(JPATH_ADMINISTRATOR.'/components/com_k2/models');
			$model = K2Model::getInstance('Tags', 'K2Model');
			$model->setState('id', $id);
			$item = $model->getRow();
			self::$instances[$id] = $item;
		}
		return self::$instances[$id];
	}

	/**
	 * Check if an instance is loaded.
	 *
	 * @param integer $id	The id of the tag to get.
	 *
	 * @return boolean 		Instance loaded flag.
	 */
	public static function loaded($id)
	{
		return isset(self::$instances[$id]);
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
		$this->editLink = '#tags/edit/'.$this->id;

		// Link
		$this->link = $this->getLink();

		// URL
		$this->url = $this->getUrl();

		// Legacy
		$this->tag = $this->name;
	}

	public function getItems()
	{
		$items = 0;
		if ($this->id)
		{
			$model = K2Model::getInstance('Tags', 'K2Model');
			$items = $model->countTagItems($this->id);
		}
		return $items;
	}

	public function getLink()
	{
		return JRoute::_(K2HelperRoute::getTagRoute($this->id.':'.$this->alias));
	}

	public function getUrl()
	{
		return JRoute::_(K2HelperRoute::getTagRoute($this->id.':'.$this->alias), true, -1);
	}

	public function getFeedLink()
	{
		return JRoute::_(K2HelperRoute::getTagRoute($this->id.':'.$this->alias).'&format=feed');
	}

	public function getExtraFields()
	{
		$extraFields = new stdClass;
		foreach ($this->extraFieldsGroups as $extraFieldsGroup)
		{
			foreach ($extraFieldsGroup->fields as $extraField)
			{
				$property = $extraField->alias;
				$extraFields->$property = $extraField;
			}
		}
		return $extraFields;
	}

	public function getExtraFieldsGroups()
	{
		$groups = array();
		if ($this->id)
		{
			$groups = K2HelperExtraFields::getTagExtraFieldsGroups($this->id, $this->extra_fields);
		}
		return $groups;
	}

	public function checkSiteAccess()
	{
		// State check
		if ($this->state < 1 || (int)$this->id < 1)
		{
			JError::raiseError(404, JText::_('K2_NOT_FOUND'));
			return false;
		}
		return true;
	}

	// Legacy
	public function getTag()
	{
		return $this->name;
	}

}
