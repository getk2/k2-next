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
 * Attachments JSON controller.
 */

class K2ControllerAttachments extends K2Controller
{
	protected function read($mode = 'row', $id = null)
	{
		// Get input
		$input = JFactory::getApplication()->input;

		// Get the model
		$model = $this->getModel($this->resourceType);
		$model->setState('itemId', $input->get('itemId', 0, 'int'));
		K2Response::setRows($model->getRows());
		$response = K2Response::render();
		echo json_encode($response);
		return $this;
	}

	public function upload()
	{
		// Check for token
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		// Filesystem
		require_once JPATH_ADMINISTRATOR.'/components/com_k2/classes/filesystem.php';
		$filesystem = K2FileSystem::getInstance();

		// Get file from input
		$input = JFactory::getApplication()->input;
		$id = $input->get('id', 0, 'int');
		$itemId = $input->get('itemId', 0, 'int');
		$tmpId = $input->get('tmpId', '', 'cmd');
		$folder = $itemId ? $itemId : $tmpId;
		$file = $input->files->get('file');

		// Get attachment instance
		require_once JPATH_ADMINISTRATOR.'/components/com_k2/resources/attachments.php';
		$attachment = K2Attachments::getInstance($id);

		// Setup some variables
		$path = 'media/k2/attachments';
		$filename = $file['name'];
		if ($itemId)
		{
			$value = $filename;
		}
		else
		{
			$value = $folder.'/'.$filename;
		}

		// Read file
		$buffer = file_get_contents($file['tmp_name']);

		// Update filesystem
		if ($filename)
		{
			// Write new file
			$filesystem->write($path.'/'.$folder.'/'.$filename, $buffer);

			// Delete current file
			$attachment->deleteFile();
		}

		// Response
		echo json_encode($value);

		// Return
		return $this;
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

		// Get and prepare input
		$input = $this->input->get('id', array(), 'array');
		JArrayHelper::toInteger($input);

		require_once JPATH_ADMINISTRATOR.'/components/com_k2/resources/attachments.php';
		foreach ($input as $id)
		{
			// Get attachment
			$attachment = K2Attachments::getInstance($id);

			// Delete
			$attachment->delete();
		}

		echo json_encode(true);

	}

}
