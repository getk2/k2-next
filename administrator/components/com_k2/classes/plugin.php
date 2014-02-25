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

/**
 * K2 Plugin class.
 */

class K2Plugin extends JPlugin
{

	public function onK2PluginInit($row)
	{
		$this->values = $row->plugins;
	}

	public function onK2RenderAdminForm($context, &$form, $row)
	{

		$mainframe = JFactory::getApplication();
		$manifest = JPATH_SITE.'/plugins/k2/'.$this->_name.'/'.$this->_name.'.xml';
		$parts = explode('.', $context);
		$type = end($parts);
		
		jimport('joomla.form.form');
		$jform = JForm::getInstance('plg_k2_'.$this->_name.'_'.$type, $manifest, array(), true, 'fieldset[starts-with(@name, "'.$type.'")]');
		
		if (!isset($form->k2Plugins))
		{
			$form->k2Plugins = array();
		}

		foreach ($jform->getFieldsets() as $fieldset)
		{

			if (!isset($form->k2Plugins[$fieldset->name]))
			{
				$form->k2Plugins[$fieldset->name] = array();
			}

			foreach ($jform->getFieldset($fieldset->name) as $field)
			{
				// Compute the field name
				$name = $this->_name.'_'.$field->__get('name');

				// Set the value
				if (isset($row->plugins->$name))
				{
					$field->__set('value', $row->plugins->$name);
				}

				// Set the field name
				$field->__set('name', 'plugins['.$name.']');

				// Create field object
				$tmp = new stdClass;
				$tmp->input = $field->__get('input');
				$tmp->label = $field->__get('label');

				// Push it to the array
				$form->k2Plugins[$fieldset->name][] = $tmp;
			}
		}

		return true;
	}

	protected function getValue($name, $default = null)
	{
		return $this->values->get($this->_name.'_'.$name, $default);
	}

	protected function getParam($name, $default = null)
	{
		return $this->params->get($name, $default);
	}

}
