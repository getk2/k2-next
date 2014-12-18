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
 * Tags JSON controller.
 */

class K2ControllerTags extends K2Controller
{
	protected function onBeforeRead($mode, $id)
	{
		$user = JFactory::getUser();
		return $user->authorise('k2.tags.manage', 'com_k2');
	}

	public function deleteOrphans()
	{
		// Check for token
		JSession::checkToken() or K2Response::throwError(JText::_('JINVALID_TOKEN'));

		// Check permissions
		$user = JFactory::getUser();
		if (!$user->authorise('k2.tags.manage', 'com_k2'))
		{
			K2Response::throwError(JText::_('K2_YOU_ARE_NOT_AUTHORIZED_TO_PERFORM_THIS_OPERATION'));
		}

		// Get model
		$model = K2Model::getInstance('Tags');
		$model->deleteOrphans();

		$application = JFactory::getApplication();
		$application->enqueueMessage(JText::_('K2_DELETE_COMPLETED'));
		echo json_encode(K2Response::render());
		return $this;

	}

}
