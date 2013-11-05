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
 * Extra Fields JSON controller.
 */

class K2ControllerExtraFields extends K2Controller
{
	public function render()
	{
		require_once JPATH_ADMINISTRATOR.'/components/com_k2/helpers/extrafields.php';
		$input = JFactory::getApplication()->input;
		$scope = $input->get('scope', '', 'cmd');
		$resourceId = $input->get('resourceId', 0, 'int');
		$filterId = $input->get('filterId', 0, 'int');
		if ($scope == 'item')
		{
			require_once JPATH_ADMINISTRATOR.'/components/com_k2/resources/items.php';
			$item = K2Items::getInstance($resourceId);
			$values = $item->extra_fields;
			$fields = K2HelperExtraFields::getItemExtraFields($filterId, $values);
		}
		else if ($scope == 'category')
		{
			require_once JPATH_ADMINISTRATOR.'/components/com_k2/resources/categories.php';
			$category = K2Categories::getInstance($resourceId);
			$values = $category->extra_fields;
			$fields = K2HelperExtraFields::getCategoryExtraFields($filterId, $values);
		}
		
		echo json_encode($fields);
		return $this;
	}

}
