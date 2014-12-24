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

jimport('joomla.form.formfield');

class JFormFieldK2FieldDescription extends JFormField
{
	var $type = 'K2FieldDescription';

	public function getInput()
	{
		$document = JFactory::getDocument();
		$document->addStyleSheet(JURI::root(true).'/media/k2app/assets/css/modules.css?v=3.0.0');
		return '<div>'.JText::_($this->value).'</div>';
	}
	
	public function getLabel()
	{
		return null;
	}

}
