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
 * Categories JSON controller.
 */

class K2ControllerCategories extends K2Controller
{
	protected function checkPermissions($method)
	{
		$user = JFactory::getUser();
		$states = $this->input->get('states', array(), 'array');
		$result = false;
		switch($method)
		{
			case 'POST' :
				// Create permission
				$result = $user->authorise('k2.category.create', 'com_k2');
				break;
			case 'PUT' :
				// Edit permission. We need to check both edit and edit own actions.
				$row = JTable::getInstance('Categories', 'K2Table');
				$row->load($this->input->get('id', 0, 'int'));
				$result = $user->authorise('k2.category.edit', 'com_k2') || ($user->authorise('k2.category.edit.own', 'com_k2') && $user->id == $row->created_by);
			case 'PATCH' :
				// Edit state permission for lists
				if (array_key_exists('published', $states) && count(array_keys($states)) == 1)
				{
					$result = $user->authorise('k2.category.edit.state', 'com_k2');
				}
				// Reorder. We need to check the edit any permission for that
				else if (array_key_exists('ordering', $states) && count(array_keys($states)) == 1)
				{
					$result = $user->authorise('k2.category.edit', 'com_k2');
				}
				break;
			case 'DELETE' :
				// Delete
				$result = $user->authorise('k2.category.delete', 'com_k2');
				break;
		}
		return $result;
	}

}
