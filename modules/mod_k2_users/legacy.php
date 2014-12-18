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

$moduleclass_sfx = $params->get('moduleclass_sfx', '');
$getTemplate = $params->get('getTemplate', 'Default');
$userName = $params->get('userName', 1);
$userAvatar = $params->get('userAvatar', 1);
$userAvatarWidthSelect = $params->get('userAvatarWidthSelect', 'custom');
$userAvatarWidth = $params->get('userAvatarWidth', 50);
$userDescription = $params->get('userDescription', 1);
$userDescriptionWordLimit = $params->get('userDescriptionWordLimit');
$userURL = $params->get('userURL', 1);
$userEmail = $params->get('userEmail', 0);
$userFeed = $params->get('userFeed', 1);
$userItemCount = $params->get('userItemCount', 1);

// User avatar
if ($userAvatarWidthSelect == 'inherit')
{
	$componentParams = JComponentHelper::getParams('com_k2');
	$avatarWidth = $componentParams->get('userImageWidth');
}
else
{
	$avatarWidth = $userAvatarWidth;
}
