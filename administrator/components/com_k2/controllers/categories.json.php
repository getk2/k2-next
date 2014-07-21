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
 * Categories JSON controller.
 */

class K2ControllerCategories extends K2Controller
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
				$category = K2Categories::getInstance($id);
				$authorized = $category->canEdit;
			}
			else
			{
				$authorized = $user->authorise('k2.category.create', 'com_k2');
			}
		}
		else
		{
			$authorized = $user->authorise('k2.category.create', 'com_k2') || $user->authorise('k2.category.edit', 'com_k2') || $user->authorise('k2.category.edit.own', 'com_k2') || $user->authorise('k2.category.edit.state', 'com_k2') || $user->authorise('k2.category.delete', 'com_k2');
		}
		return $authorized;
	}

	public function saveOrder()
	{
		// Check for token
		JSession::checkToken() or K2Response::throwError(JText::_('JINVALID_TOKEN'));

		// Get user
		$user = JFactory::getUser();

		// Check permissions
		if (!$user->authorise('k2.category.edit', 'com_k2'))
		{
			K2Response::throwError(JText::_('K2_YOU_ARE_NOT_AUTHORIZED_TO_PERFORM_THIS_OPERATION'), 403);
		}

		// Get input
		$id = $this->input->get('id', 0, 'int');
		$reference_id = $this->input->get('reference_id', 0, 'int');
		$location = $this->input->get('location', '', 'cmd');

		// Valid location values
		$locations = array('first-child', 'after');

		// Ensure that we have valid input
		if (!$id || $reference_id < 1 || !in_array($location, $locations))
		{
			K2Response::throwError(JText::_('K2_INVALID_INPUT'));
		}

		// Get table
		$table = $this->model->getTable();

		// Update
		if (!$table->moveByReference($reference_id, $location, $id))
		{
			K2Response::throwError($table->getError());
		}

		return $this;
	}

	protected function getInputData()
	{
		$data = parent::getInputData();
		$data['description'] = JComponentHelper::filterText($this->input->get('description', '', 'raw'));
		return $data;
	}

	public function close()
	{
		// Check for token
		JSession::checkToken() or K2Response::throwError(JText::_('JINVALID_TOKEN'));

		// User
		$user = JFactory::getUser();

		if (!$user->authorise('k2.category.edit', 'com_k2'))
		{
			K2Response::throwError(JText::_('K2_YOU_ARE_NOT_AUTHORIZED_TO_PERFORM_THIS_OPERATION'), 403);
		}

		$this->model->close();
		return $this;
	}

}
