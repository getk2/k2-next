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
 * Settings JSON controller.
 */

class K2ControllerSettings extends K2Controller
{

	/**
	 * onBeforeRead function.
	 * Hook for chidlren controllers to check for access
	 *
	 * @param string $mode		The mode of the read function. Pass 'row' for retrieving a single row or 'list' to retrieve a collection of rows.
	 * @param mixed $id			The id of the row to load when we are retrieving a single row.
	 *
	 * @return void
	 */
	protected function onBeforeRead($mode, $id)
	{
		$user = JFactory::getUser();
		return $user->authorise('core.admin', 'com_k2');
	}

	/**
	 * Update function.
	 * Updates an existing resource.
	 *
	 * @return void
	 */
	protected function update()
	{
		// Check for token
		JSession::checkToken() or K2Response::throwError(JText::_('JINVALID_TOKEN'));

		// Permissions
		$user = JFactory::getUser();
		if (!$user->authorise('core.admin', 'com_k2'))
		{
			K2Response::throwError(JText::_('K2_YOU_ARE_NOT_AUTHORIZED_TO_PERFORM_THIS_OPERATION'));
		}

		// Get extension
		$component = JComponentHelper::getComponent('com_k2');

		// Prepare data for model
		$id = $component->id;
		$option = 'com_k2';
		$data = $this->input->get('jform', array(), 'array');

		// Use Joomla! model for saving settings
		require_once JPATH_SITE.'/components/com_config/model/cms.php';
		require_once JPATH_SITE.'/components/com_config/model/form.php';

		JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR.'/components/com_config/model');
		$model = JModelLegacy::getInstance('Component', 'ConfigModel');

		// Get form
		JForm::addFormPath(JPATH_ADMINISTRATOR.'/components/com_k2');
		$form = JForm::getInstance('com_k2.settings', 'config', array('control' => 'jform'), false, '/config');

		// Validate the posted data
		$return = $model->validate($form, $data);

		// Check for validation errors
		if ($return === false)
		{
			// Get the validation errors
			$errors = $model->getErrors();
			$message = $errors[0] instanceof Exception ? $errors[0]->getMessage() : $errors[0];
			K2Response::throwError($message);
		}

		// Attempt to save the configuration.
		$data = array('params' => $return, 'id' => $id, 'option' => $option);
		$return = $model->save($data);

		$options = array('defaultgroup' => '_system', 'cachebase' => JPATH_ADMINISTRATOR.'/cache');
		$cache = JCache::getInstance('callback', $options);
		$cache->clean();

		// Check the return value.
		if ($return === false)
		{
			// Save failed, go back to the screen and display a notice.
			K2Response::throwError(JText::sprintf('JERROR_SAVE_FAILED', $model->getError()));
		}
	}

}
