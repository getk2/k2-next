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
require_once JPATH_ADMINISTRATOR.'/components/com_k2/helpers/images.php';
require_once JPATH_ADMINISTRATOR.'/components/com_k2/resources/items.php';
require_once JPATH_ADMINISTRATOR.'/components/com_k2/resources/categories.php';

/**
 * Image JSON controller.
 */

class K2ControllerImages extends K2Controller
{

	public function upload()
	{
		// Check for token
		JSession::checkToken() or K2Response::throwError(JText::_('JINVALID_TOKEN'));

		// Get user
		$user = JFactory::getUser();

		// Get input
		$type = $this->input->get('type', '', 'cmd');
		$itemId = $this->input->get('itemId', 0, 'int');
		$file = $this->input->files->get('file');
		$path = $this->input->get('path', '', 'string');
		$path = str_replace(JURI::root(true).'/', '', $path);

		// Permissions check
		if ($itemId)
		{
			if ($type == 'item')
			{
				$authorised = K2Items::getInstance($itemId)->canEdit;
			}
			else if ($type == 'category')
			{
				$authorised = K2Categories::getInstance($itemId)->canEdit;
			}
			else if ($type == 'user')
			{
				$authorised = $user->authorise('core.edit', 'com_users') || $user->id == $itemId;
			}
		}
		else
		{
			$authorised = $user->authorise('k2.'.$type.'.create', 'com_k2');
		}
		if (!$authorised)
		{
			K2Response::throwError(JText::_('K2_YOU_ARE_NOT_AUTHORIZED_TO_PERFORM_THIS_OPERATION'), 403);
		}
		
		// Get session
		$session = JFactory::getSession();
		
		// Get last uploaded temp file value
		$temp = $session->get('K2Temp') ? $session->get('K2Temp') : false;

		// File system
		$filesystem = K2FileSystem::getInstance();

		// Save path
		$savepath = 'media/k2/categories';

		// First delete any previous temp file
		if ($temp)
		{
			$key = $savepath.'/'.$temp.'.jpg';
			if ($filesystem->has($key))
			{
				$filesystem->delete($key);
			}
		}

		// ImageProcessor
		$processor = K2ImageProcessor::getInstance();

		// Generate the base file name
		$baseFileName = uniqid();
		
		// Store it to session
		$session->set('K2Temp', $baseFileName);

		// Get image depending on source
		if ($path)
		{
			$buffer = $filesystem->read($path);
			$imageResource = $processor->load($buffer);
		}
		else
		{
			$source = $file['tmp_name'];
			$imageResource = $processor->open($source);
		}

		// Write image file
		$filesystem->write($savepath.'/'.$baseFileName.'.jpg', $imageResource->__toString(), true);

		// Return
		$response = new stdClass;
		$response->temp = $baseFileName;
		$response->preview = JURI::root(true).'/'.$savepath.'/'.$baseFileName.'.jpg?t='.time();

		echo json_encode($response);

		return $this;

		$image = K2HelperImages::addResourceImage($type, $tmpId, $file, $path);

		// Response
		echo json_encode($image);
	}

	/**
	 * Delete function.
	 * Deletes a resource.
	 * Usually there will be no need to override this function.
	 *
	 * @return void
	 */
	protected function delete()
	{
		// Check for token
		JSession::checkToken() or K2Response::throwError(JText::_('JINVALID_TOKEN'));

		// User
		$user = JFactory::getUser();

		// Get input
		$input = $this->input;
		$type = $input->get('type', '', 'cmd');
		$id = $input->get('id', '', 'cmd');
		$itemId = $input->get('itemId', 0, 'int');

		// Permissions check
		if ($itemId)
		{
			if ($type == 'item')
			{
				$authorised = K2Items::getInstance($itemId)->canEdit;
			}
			else if ($type == 'category')
			{
				$authorised = K2Categories::getInstance($itemId)->canEdit;
			}
			else if ($type == 'user')
			{
				$authorised = $user->authorise('core.edit', 'com_users') || $user->id == $itemId;
			}
		}
		else
		{
			$authorised = true;
		}
		if (!$authorised)
		{
			K2Response::throwError(JText::_('K2_YOU_ARE_NOT_AUTHORIZED_TO_PERFORM_THIS_OPERATION'), 403);
		}

		// Remove image using helper
		K2HelperImages::removeResourceImage($type, $itemId, $id);

		// Response
		K2Response::setResponse(true);
	}

}
