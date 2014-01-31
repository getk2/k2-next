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

	protected function onBeforeRead($mode, $id)
	{
		$user = JFactory::getUser();
		$authorized = false;
		if ($mode == 'row')
		{
			// Create
			if ($id)
			{
				$item = K2Items::getInstance();
				$authorized = $item->canEdit;
			}
			else
			{
				$authorized = $user->authorise('k2.item.create', 'com_k2');
			}
		}
		else
		{
			$authorized = $user->authorise('k2.item.create', 'com_k2') || $user->authorise('k2.item.edit', 'com_k2') || $user->authorise('k2.item.edit.own', 'com_k2') || $user->authorise('k2.item.edit.state', 'com_k2') || $user->authorise('k2.item.edit.state.featured', 'com_k2') || $user->authorise('k2.item.delete', 'com_k2');
		}
		return $authorized;
	}

	protected function getInputData()
	{
		$data = parent::getInputData();
		$data['text'] = JComponentHelper::filterText($this->input->get('text', '', 'raw'));
		return $data;
	}

}
