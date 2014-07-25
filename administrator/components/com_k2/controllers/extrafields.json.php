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
require_once JPATH_ADMINISTRATOR.'/components/com_k2/controller.php';

/**
 * Extra Fields JSON controller.
 */

class K2ControllerExtraFields extends K2Controller
{

	protected function onBeforeRead($mode, $id)
	{
		$user = JFactory::getUser();
		return $user->authorise('k2.extrafields.manage', 'com_k2');
	}

	public function render()
	{
		require_once JPATH_ADMINISTRATOR.'/components/com_k2/helpers/extrafields.php';
		$input = JFactory::getApplication()->input;
		$scope = $input->get('scope', '', 'cmd');
		$resourceId = $input->get('resourceId', 0, 'int');
		$filterId = $input->get('filterId', 0, 'raw');
		if ($scope == 'item')
		{
			require_once JPATH_ADMINISTRATOR.'/components/com_k2/resources/items.php';
			$item = K2Items::getInstance($resourceId);
			$values = $item->extra_fields;
			$fields = K2HelperExtraFields::getItemExtraFieldsGroups((int)$filterId, $values);
		}
		else if ($scope == 'category')
		{
			require_once JPATH_ADMINISTRATOR.'/components/com_k2/resources/categories.php';
			$category = K2Categories::getInstance($resourceId);
			$values = $category->extra_fields;
			$fields = K2HelperExtraFields::getCategoryExtraFieldsGroups((int)$filterId, $values);
		}
		else if ($scope == 'user')
		{
			require_once JPATH_ADMINISTRATOR.'/components/com_k2/resources/users.php';
			$user = K2Users::getInstance($resourceId);
			$values = $user->extra_fields;
			$groups = explode('|', $filterId);
			$fields = K2HelperExtraFields::getUserExtraFieldsGroups($groups, $values);
		}
		else if ($scope == 'tag')
		{
			require_once JPATH_ADMINISTRATOR.'/components/com_k2/resources/tags.php';
			$tag = K2Tags::getInstance($resourceId);
			$values = $tag->extra_fields;
			$fields = K2HelperExtraFields::getTagExtraFieldsGroups((int)$filterId, $values);
		}
		echo json_encode($fields);
		return $this;
	}

}
