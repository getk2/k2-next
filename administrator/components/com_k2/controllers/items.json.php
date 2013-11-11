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

require_once JPATH_ADMINISTRATOR.'/components/com_k2/controller.php';

/**
 * Items JSON controller.
 */

class K2ControllerItems extends K2Controller
{
	protected function checkPermissions($method)
	{
		$user = JFactory::getUser();
		$input = JFactory::getApplication()->input;
		$itemId = $input->get('id', 0, 'int');
		$categoryId = $input->get('catid', 0, 'int');
		switch($method)
		{
			case 'POST' :
				$result = $user->authorise('k2.item.create', 'com_k2.category.'.$categoryId);
				break;
			case 'PUT' :
			case 'PATCH' :
				$result = $user->authorise('k2.item.edit', 'com_k2.item.'.$itemId);
				break;
			case 'DELETE' :
				$result = $user->authorise('k2.item.delete', 'com_k2.item.'.$itemId);
				break;
		}
		return $result;
	}

}
