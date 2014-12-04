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

jimport('joomla.form.formfield');

require_once JPATH_ADMINISTRATOR.'/components/com_k2/helpers/html.php';

class JFormFieldK2ExtraFieldGroups extends JFormField
{
	var $type = 'K2ExtraFieldGroups';

	public function getInput()
	{
		if(!is_array($this->value))
		{
			$this->value = $this->value ? array($this->value) : array();
		}		
		return '<div data-widget="extrafieldgroups" data-value="'.implode('|', $this->value).'">'.K2HelperHTML::extraFieldsGroups($this->name, null, ' ', array('data-role' => 'extra-field-groups-selector'), 'item').'<div data-role="list"></div></div>';		
	}

}
