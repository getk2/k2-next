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
 * K2 extra field resource class.
 */

class K2ExtraFields extends K2Resource
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
	 * @return K2ExtraField The tag object.
	 */
	public static function getInstance($id)
	{
		if (empty(self::$instances[$id]))
		{
			K2Model::addIncludePath(JPATH_ADMINISTRATOR.'/components/com_k2/models');
			$model = K2Model::getInstance('ExtraFields', 'K2Model');
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
		$this->link = '#extrafields/edit/'.$this->id;
		
		// Set type label
		$this->typeName = JText::_('K2_EXTRA_FIELD_TYPE_'.strtoupper($this->type));

		// Get types
		$this->getDefinitions();

	}

	public function getDefinitions()
	{
		$definitions = array();
		require_once JPATH_ADMINISTRATOR.'/components/com_k2/helpers/extrafields.php';
		$types = K2HelperExtraFields::getTypes();
		foreach ($types as $type)
		{
			$definitions[$type] = $this->getFieldDefinition($type);
		}
		$this->definitions = $definitions;
	}
	
	public function getFieldDefinition($type)
	{
		$definition = '';
		jimport('joomla.filesystem.folder');
		if (JFolder::exists(JPATH_ADMINISTRATOR.'/components/com_k2/extrafields/'.$type))
		{
			$field = ($type == $this->type) ? new JRegistry($this->value) : new JRegistry();
			ob_start();
			include JPATH_ADMINISTRATOR.'/components/com_k2/extrafields/'.$type.'/definition.php';
			$definition = ob_get_contents();
			ob_end_clean();
		}
		return $definition;
	}

	public function getFieldInput($type)
	{
		$input = '';
		jimport('joomla.filesystem.folder');
		if (JFolder::exists(JPATH_ADMINISTRATOR.'/components/com_k2/extrafields/'.$type))
		{
			$field = ($type == $this->type) ? new JRegistry($this->value) : new JRegistry();
			ob_start();
			include JPATH_ADMINISTRATOR.'/components/com_k2/extrafields/'.$type.'/input.php';
			$input = ob_get_contents();
			ob_end_clean();
		}
		return $input;
	}

	public function getFieldOutput($type, $value)
	{
		$output = '';
		jimport('joomla.filesystem.folder');
		if (JFolder::exists(JPATH_ADMINISTRATOR.'/components/com_k2/extrafields/'.$type))
		{
			$field = new JRegistry($value);
			ob_start();
			include JPATH_ADMINISTRATOR.'/components/com_k2/extrafields/'.$type.'/output.php';
			$output = ob_get_contents();
			ob_end_clean();
		}
		return $output;
	}

	public function escape($string)
	{
		return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
	}

}
