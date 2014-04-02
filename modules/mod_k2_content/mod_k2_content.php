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

require_once dirname(__FILE__).'/helper.php';
include dirname(__FILE__).'/legacy.php';

$items = ModK2ContentHelper::getItems($params);
$componentParams = JComponentHelper::getParams('com_k2');
if (count($items))
{
	require JModuleHelper::getLayoutPath('mod_k2_content', $params->get('template', 'Default').'/default');
}
