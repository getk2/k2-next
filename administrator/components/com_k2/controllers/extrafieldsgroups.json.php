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

require_once JPATH_ADMINISTRATOR.'/components/com_k2/controller.php';

/**
 * Extra Fields Groups JSON controller.
 */

class K2ControllerExtraFieldsGroups extends K2Controller
{
	protected function onBeforeRead($mode, $id)
	{
		$user = JFactory::getUser();
		return $user->authorise('k2.extrafields.manage', 'com_k2');
	}

}
