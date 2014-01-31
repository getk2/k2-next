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

// Get application
$application = JFactory::getApplication();

// Get input
$view = $application->input->get('view');
$task = $application->input->get('task');

// Build controller configuration
$configuration = array();
$configuration['originalTask'] = JFactory::getApplication()->input->get('task');
if ($view == '' || $view == 'admin')
{
	// If no view is set proxy all requests to administrator controllers
	$configuration['base_path'] = JPATH_ADMINISTRATOR.'/components/com_k2';
}

// Bootstrap K2
$controller = JControllerLegacy::getInstance('K2', $configuration);
$controller->execute($application->input->get('task'));
$controller->redirect();
