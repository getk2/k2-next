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

// Check component access
$user = JFactory::getUser();
if (!$user->authorise('core.manage', 'com_k2'))
{
	throw new Exception(JText::_('JLIB_APPLICATION_ERROR_ACCESS_FORBIDDEN'), 403);
}

// Bootstrap K2
$controller = JControllerLegacy::getInstance('K2', array('originalTask' => JFactory::getApplication()->input->get('task')));
$controller->execute(JFactory::getApplication()->input->get('task'));
$controller->redirect();
