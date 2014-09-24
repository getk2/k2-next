<?php
/**
 * @version		3.0.0b
 * @package		K2
 * @author		JoomlaWorks http://www.joomlaworks.net
 * @copyright	Copyright (c) 2006 - 2014 JoomlaWorks Ltd. All rights reserved.
 * @license		GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 */

// no direct access
defined('_JEXEC') or die ;

require_once JPATH_ADMINISTRATOR.'/components/com_k2/classes/response.php';

/**
 * K2 Plugin class.
 */

class K2Plugin extends JPlugin
{

	public function onK2PluginInit($row)
	{
		$this->values = $row->plugins;
	}

	public function onK2RenderAdminForm(&$form, $row, $type)
	{
		jimport('joomla.form.form');
		$manifest = JPATH_SITE.'/plugins/k2/'.$this->_name.'/'.$this->_name.'.xml';
		$jform = JForm::getInstance('plg_k2_'.$this->_name.'_'.$type, $manifest, array(), true, 'fieldset[starts-with(@name, "'.$type.'")]');

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

		$this->onK2RenderAdminHead($row, $type);

		return true;
	}

	protected function onK2RenderAdminHead($row, $type)
	{
	}

	protected function getValue($name, $default = null)
	{
		return $this->values->get($this->_name.'_'.$name, $default);
	}

	protected function getParam($name, $default = null)
	{
		return $this->params->get($name, $default);
	}

	protected function addScript($url)
	{
		K2Response::addScript($url);
	}

	protected function addScriptDeclaration($js)
	{
		K2Response::addScriptDeclaration($js);
	}

	protected function addStyle($url)
	{
		K2Response::addStyle($url);
	}

}
