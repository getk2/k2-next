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

class JFormFieldK2Language extends JFormField
{
	var $type = 'K2Language';

	public function getInput()
	{
		$language = JFactory::getLanguage();
		$language->load('com_k2', JPATH_ADMINISTRATOR);
	}

	public function getLabel()
	{
		return;
	}

}
