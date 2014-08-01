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

// Get user
$user = JFactory::getUser();

// Process only if user is authorized for managing
if ($user->authorise('core.manage', 'com_k2'))
{
	// API
	$mainframe = JFactory::getApplication();
	$document = JFactory::getDocument();
	$user = JFactory::getUser();

	// Module parameters
	$moduleclass_sfx = $params->get('moduleclass_sfx', '');
	$modCSSStyling = (int)$params->get('modCSSStyling', 1);
	$modLogo = (int)$params->get('modLogo', 1);

	// Component parameters
	$componentParams = JComponentHelper::getParams('com_k2');

	$onlineImageEditor = $componentParams->get('onlineImageEditor', 'splashup');

	switch($onlineImageEditor)
	{
		case 'splashup' :
			$onlineImageEditorLink = 'http://splashup.com/splashup/';
			break;
		case 'sumopaint' :
			$onlineImageEditorLink = 'http://www.sumopaint.com/app/';
			break;
		case 'pixlr' :
			$onlineImageEditorLink = 'http://pixlr.com/editor/';
			break;
	}

	// Call the modal and add some needed JS
	JHTML::_('behavior.modal');

	// Append CSS to the document's head
	if ($modCSSStyling)
	{
		$document->addStyleSheet(JURI::base(true).'/modules/mod_k2_quickicons/tmpl/css/style.css?v=2.6.8');
	}

	// Output content with template
	require JModuleHelper::getLayoutPath('mod_k2_quickicons', 'default');
}
