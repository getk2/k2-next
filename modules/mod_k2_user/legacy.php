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

$usersConfig = JComponentHelper::getParams('com_users');

$moduleclass_sfx = $params->get('moduleclass_sfx', '');
$userGreetingText = $params->get('userGreetingText', '');
$userAvatarWidthSelect = $params->get('userAvatarWidthSelect', 'custom');
$userAvatarWidth = $params->get('userAvatarWidth', 50);

// Legacy params
$greeting = 0;

$componentParams = JComponentHelper::getParams('com_k2');
$K2CommentsEnabled = $componentParams->get('comments');

// User avatar
if ($userAvatarWidthSelect == 'inherit')
{
	$avatarWidth = $componentParams->get('userImageWidth');
}
else
{
	$avatarWidth = $userAvatarWidth;
}
$jUser = JFactory::getUser();
if ($jUser->guest)
{
	$passwordFieldName = $login->passwordFieldName;
	$resetLink = $login->resetLink;
	$remindLink = $login->remindLink;
	$registrationLink = $login->registrationLink;
	$option = $login->option;
	$task = $login->task;
	$return = $login->return;
}
else
{
    $menu = $logout->menu;
    $profileLink = $logout->profileLink;
	$option = $logout->option;
	$task = $logout->task;
	$return = $logout->return;
}
