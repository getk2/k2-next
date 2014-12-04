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

class JFormFieldK2Header extends JFormField
{
	var $type = 'K2Header';

	public function getInput()
	{
		$document = JFactory::getDocument();
		$document->addStyleSheet(JURI::root(true).'/media/k2app/assets/css/modules.css?v=3.0.0b');
		return '<h4>'.JText::_($this->value).'</h4>';
	}
	
	public function getLabel()
	{
		return null;
	}

}
