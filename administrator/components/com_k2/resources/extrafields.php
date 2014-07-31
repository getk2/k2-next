<?php
/**
 * @version		3.0.0
 * @package		K2
 * @author		JoomlaWorks http://www.joomlaworks.net
 * @copyright	Copyright (c) 2006 - 2014 JoomlaWorks Ltd. All rights reserved.
 * @license		GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 */

// no direct access
defined('_JEXEC') or die ;

require_once JPATH_ADMINISTRATOR.'/components/com_k2/resources/resource.php';
jimport('joomla.filesystem.file');

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
		$this->editLink = JURI::base(true).'/index.php?option=com_k2#extrafields/edit/'.$this->id;

		// Set type label
		$this->typeName = JText::_('K2_EXTRA_FIELD_TYPE_'.strtoupper($this->type));

	}

	public function getDefinition()
	{
		$definition = '';
		if (JFile::exists(JPATH_ADMINISTRATOR.'/components/com_k2/extrafields/'.$this->type.'/definition.php'))
		{
			$field = new JRegistry($this->value);
			$field->set('prefix', 'value');
			ob_start();
			include JPATH_ADMINISTRATOR.'/components/com_k2/extrafields/'.$this->type.'/definition.php';
			$definition = ob_get_contents();
			ob_end_clean();
			$definition = trim($definition);
		}
		return $definition;
	}

	public function getInput()
	{
		$input = '';
		if (JFile::exists(JPATH_ADMINISTRATOR.'/components/com_k2/extrafields/'.$this->type.'/input.php'))
		{
			$field = new JRegistry($this->value);
			$field->set('prefix', 'extra_fields['.$this->id.']');
			ob_start();
			include JPATH_ADMINISTRATOR.'/components/com_k2/extrafields/'.$this->type.'/input.php';
			$input = ob_get_contents();
			ob_end_clean();
			$input = trim($input);
		}
		return $input;
	}

	public function getOutput()
	{
		$output = '';
		if (JFile::exists(JPATH_ADMINISTRATOR.'/components/com_k2/extrafields/'.$this->type.'/output.php'))
		{
			$field = new JRegistry($this->value);
			ob_start();
			include JPATH_ADMINISTRATOR.'/components/com_k2/extrafields/'.$this->type.'/output.php';
			$output = ob_get_contents();
			ob_end_clean();
			$output = trim($output);
			if ($output)
			{
				$item = new stdClass;
				$item->text = $output;
				$params = JComponentHelper::getParams('com_k2');
				$dispatcher = JDispatcher::getInstance();
				JPluginHelper::importPlugin('content');
				$dispatcher->trigger('onContentPrepare', array(
					'com_k2.extrafield',
					&$item,
					&$params,
					0
				));
				$output = $item->text;
			}
		}
		return $output;
	}

	private function escape($string)
	{
		return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
	}

}
