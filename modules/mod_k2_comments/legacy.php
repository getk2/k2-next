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

// Params
$moduleclass_sfx = $params->get('moduleclass_sfx', '');
$module_usage = $params->get('module_usage', '0');

$commentAvatarWidthSelect = $params->get('commentAvatarWidthSelect', 'custom');
$commentAvatarWidth = $params->get('commentAvatarWidth', 50);

$commenterAvatarWidthSelect = $params->get('commenterAvatarWidthSelect', 'custom');
$commenterAvatarWidth = $params->get('commenterAvatarWidth', 50);

// Get component params
$componentParams = JComponentHelper::getParams('com_k2');

// User avatar for latest comments
if ($commentAvatarWidthSelect == 'inherit')
{
	$lcAvatarWidth = $componentParams->get('commenterImgWidth');
}
else
{
	$lcAvatarWidth = $commentAvatarWidth;
}

// User avatar for top commenters
if ($commenterAvatarWidthSelect == 'inherit')
{
	$tcAvatarWidth = $componentParams->get('commenterImgWidth');
}
else
{
	$tcAvatarWidth = $commenterAvatarWidth;
}
