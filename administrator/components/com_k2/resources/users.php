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
 * K2 user group resource class.
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
			$model = K2Model::getInstance('Users', 'K2Model');
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
		$this->link = '#users/edit/'.$this->id;
		
		if($this->id)
		{
			$this->enabled = (int)!$this->block;
			$this->activated = (int)!$this->activation;
			$this->groupsValue = implode(', ', $this->groups);
		}

		
		// Image
		$this->image = $this->getImage();

	}


	public function getImage()
	{
		$image = null;
		require_once JPATH_ADMINISTRATOR.'/components/com_k2/helpers/images.php';
		$result = K2HelperImages::getResourceImages('user', $this);
		$this->_image = new stdClass;
		if ($result->image)
		{
			$image = $result->image;
			$this->_image->preview = $result->image;
			$this->_image->id = $result->id;
		}
		return $image;
	}
}
