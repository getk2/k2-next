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

jimport('joomla.form.formfield');

class JFormFieldK2Header extends JFormField
{
	var $type = 'K2Header';

	public function getInput()
	{
		$document = JFactory::getDocument();
		$document->addStyleSheet(JURI::root(true).'/administrator/components/com_k2/css/modules.k2.css?v=3.0.0');
		return '<div class="paramHeaderContainer"><div class="paramHeaderContent">'.JText::_($this->value).'</div><div class="k2clr"></div></div>';
	}
	
	public function getLabel()
	{
		return null;
	}

}
