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

class K2Categories extends K2Resource
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
	 * @return K2Category The category object.
	 */
	public static function getInstance($id)
	{
		if (empty(self::$instances[$id]))
		{
			K2Model::addIncludePath(JPATH_ADMINISTRATOR.'/components/com_k2/models');
			$model = K2Model::getInstance('Categories', 'K2Model');
			if (is_numeric($id))
			{
				$model->setState('id', $id);
			}
			else
			{
				$model->setState('alias', $id);
			}
			$item = $model->getRow();
			self::$instances[$item->id] = $item;
			self::$instances[$item->alias] = $item;
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
		$this->editLink = '#categories/edit/'.$this->id;

		// Link
		$this->link = $this->getLink();

		// Permisisons
		$user = JFactory::getUser();
		if ($user->guest)
		{
			$this->canEdit = false;
			$this->canEditState = false;
			$this->canDelete = false;
			$this->canSort = false;
			$this->canAddItem = false;
		}
		else
		{
			$this->canEdit = $user->authorise('k2.category.edit', 'com_k2.category.'.$this->id) || ($user->id == $this->created_by && $user->authorise('k2.category.edit.own', 'com_k2.category.'.$this->id));
			$this->canEditState = $user->authorise('k2.category.edit.state', 'com_k2.category.'.$this->id);
			$this->canDelete = $user->authorise('k2.category.delete', 'com_k2.category.'.$this->id);
			$this->canSort = $user->authorise('k2.category.edit', 'com_k2');
			$this->canAddItem = $user->authorise('k2.item.create', 'com_k2.category.'.$this->id);
		}

		// Image
		$this->image = $this->getImage();

	}

	public function getLink()
	{
		return JRoute::_(K2HelperRoute::getCategoryRoute($this->id.':'.$this->alias));
	}

	public function getImage()
	{
		$image = null;
		require_once JPATH_ADMINISTRATOR.'/components/com_k2/helpers/images.php';
		$result = K2HelperImages::getResourceImages('category', $this);
		$this->_image = json_decode($this->image);
		if ($result->image)
		{
			$image = $result->image;
			$this->_image->preview = $result->image;
			$this->_image->id = $result->id;
		}
		return $image;
	}

}
