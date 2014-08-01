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

$moduleclass_sfx = $params->get('moduleclass_sfx', '');
$itemAuthorAvatarWidthSelect = $params->get('itemAuthorAvatarWidthSelect', 'custom');
$itemAuthorAvatarWidth = $params->get('itemAuthorAvatarWidth', 50);
$itemCustomLinkTitle = $params->get('itemCustomLinkTitle', '');
if ($params->get('itemCustomLinkMenuItem'))
{
	$menu = JMenu::getInstance('site');
	$menuLink = $menu->getItem($params->get('itemCustomLinkMenuItem'));
	if (!$itemCustomLinkTitle)
	{
		$itemCustomLinkTitle = $menuLink->title;
	}
	$params->set('itemCustomLinkURL', JRoute::_('index.php?&Itemid='.$menuLink->id));
}

// Get component params
$componentParams = JComponentHelper::getParams('com_k2');

// User avatar
if ($itemAuthorAvatarWidthSelect == 'inherit')
{
	$avatarWidth = $componentParams->get('userImageWidth');
}
else
{
	$avatarWidth = $itemAuthorAvatarWidth;
}

foreach ($items as $item)
{
	if (is_string($item->extra_fields))
	{
		$item->extraFields = $item->getExtraFields();
		$item->extra_fields = $item->getextra_fields();
	}
}
