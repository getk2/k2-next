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
 * User groups JSON controller.
 */

class K2ControllerUserGroups extends K2Controller
{
	protected function onBeforeRead($mode, $id)
	{
		$user = JFactory::getUser();
		$authorized = false;
		if ($mode == 'row')
		{
			// Edit
			if ($id)
			{
				$authorized = ($id == $user->id) || $user->authorise('core.edit', 'com_users');
			}
			else
			{
				$authorized = $user->authorise('core.create', 'com_users');
			}
		}
		else
		{
			$authorized = $user->authorise('core.edit', 'com_users');
		}
		return $authorized;
	}

}
