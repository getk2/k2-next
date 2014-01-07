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
 * Users JSON controller.
 */

class K2ControllerUsers extends JControllerLegacy
{
	// Disable front-end access to read function
	public function read($mode = 'row', $id = null)
	{
		return $this;
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

}
