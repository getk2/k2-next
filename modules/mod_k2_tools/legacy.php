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

$moduleclass_sfx = $params->get('moduleclass_sfx', '');
$module_usage = $params->get('module_usage', 0);
$authorAvatarWidthSelect = $params->get('authorAvatarWidthSelect', 'custom');
$authorAvatarWidth = $params->get('authorAvatarWidth', 50);
$button = $params->get('button', '');
$imagebutton = $params->get('imagebutton', '');
$button_pos = $params->get('button_pos', 'left');
$button_text = $params->get('button_text', JText::_('K2_SEARCH'));
$width = intval($params->get('width', 20));
$maxlength = $width > 20 ? $width : 20;
$text = $params->get('text', JText::_('K2_SEARCH'));
$document = JFactory::getDocument();
$app = JFactory::getApplication();
