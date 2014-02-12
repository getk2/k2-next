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
				$item = K2Items::getInstance($id);
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
		$params = JComponentHelper::getParams('com_k2');
		if ($params->get('mergeEditors'))
		{
			$data['text'] = JComponentHelper::filterText($this->input->get('text', '', 'raw'));
		}
		else
		{
			$data['introtext'] = JComponentHelper::filterText($this->input->get('introtext', '', 'raw'));
			$data['fulltext'] = JComponentHelper::filterText($this->input->get('fulltext', '', 'raw'));
		}
		return $data;
	}
	
	public function close()
	{
		// Check for token
		JSession::checkToken() or K2Response::throwError(JText::_('JINVALID_TOKEN'));

		// User
		$user = JFactory::getUser();

		if ($user->authorise('k2.item.edit', 'com_k2'))
		{
			$this->model->close();
		}
		return $this;
	}

}
