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
 * Users JSON controller.
 */

class K2ControllerUsers extends K2Controller
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

	protected function getInputData()
	{
		$data = parent::getInputData();
		$data['description'] = JComponentHelper::filterText($this->input->get('description', '', 'raw'));
		return $data;
	}

	public function report()
	{
		// Check for token
		JSession::checkToken() or K2Response::throwError(JText::_('JINVALID_TOKEN'));

		// Get application
		$application = JFactory::getApplication();

		// Get input
		$id = $application->input->get('id', 0, 'int');

		// Get model
		$model = K2Model::getInstance('Users');
		$model->setState('id', $id);
		$model->report();
		if (!$model->report())
		{
			K2Response::throwError($model->getError());
		}

		// Response
		echo json_encode(K2Response::render());

		// Return
		return $this;
	}

	public function close()
	{
		// Check for token
		JSession::checkToken() or K2Response::throwError(JText::_('JINVALID_TOKEN'));

		// User
		$user = JFactory::getUser();

		if (!$user->authorise('core.edit', 'com_users'))
		{
			K2Response::throwError(JText::_('K2_YOU_ARE_NOT_AUTHORIZED_TO_PERFORM_THIS_OPERATION'), 403);
		}
		$this->model->close();
		return $this;
	}

}
