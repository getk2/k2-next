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

// Bootstrap K2
$controller = JControllerLegacy::getInstance('K2', array('originalTask' => JFactory::getApplication()->input->get('task')));
$controller->execute(JFactory::getApplication()->input->get('task'));
$controller->redirect();
