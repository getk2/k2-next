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

// Get application
$application = JFactory::getApplication();

// Get task
$task = $application->input->get('task');

// Build controller configuration
$configuration = array();
$configuration['originalTask'] = JFactory::getApplication()->input->get('task');
if (K2_EDIT_MODE)
{
	// If we are in edit mode proxy all requests to administrator controllers
	$configuration['base_path'] = JPATH_ADMINISTRATOR.'/components/com_k2';
}

// Bootstrap K2
$controller = JControllerLegacy::getInstance('K2', $configuration);
$controller->execute($application->input->get('task'));
$controller->redirect();
