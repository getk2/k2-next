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
require_once JPATH_ADMINISTRATOR.'/components/com_k2/classes/filesystem.php';
require_once JPATH_ADMINISTRATOR.'/components/com_k2/resources/items.php';
require_once JPATH_ADMINISTRATOR.'/components/com_k2/resources/attachments.php';

/**
 * Attachments JSON controller.
 */

class K2ControllerAttachments extends K2Controller
{
	public function upload()
	{
		// Check for token
		JSession::checkToken() or K2Response::throwError(JText::_('JINVALID_TOKEN'));

		// Filesystem
		$filesystem = K2FileSystem::getInstance();

		// Get file from input
		$input = JFactory::getApplication()->input;
		$id = $input->get('id', 0, 'int');
		$itemId = $input->get('itemId', 0, 'int');
		$tmpId = $input->get('tmpId', '', 'cmd');
		$folder = $itemId ? $itemId : $tmpId;
		$file = $input->files->get('file');

		// Permissions check
		if ($itemId)
		{
			// Existing items check permission for specific item
			$authorised = K2Items::getInstance($itemId)->canEdit;
		}
		else
		{
			// New items. We can only check the generic create permission. We cannot check against specific category since we do not know the category of the item.
			$authorised = JFactory::getUser()->authorise('k2.item.create', 'com_k2');
		}
		if (!$authorised)
		{
			K2Response::throwError(JText::_('K2_YOU_ARE_NOT_AUTHORIZED_TO_PERFORM_THIS_OPERATION'), 403);
		}

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
			$this->model->setState('id', $id);
			$attachment = $this->model->getRow();
			$this->model->deleteFile($attachment);
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

		foreach ($input as $id)
		{
			// Get attachment
			$this->model->setState('id', $id);
			$attachment = $this->model->getRow();

			// If user tried to delete an attachment of a specific item we need to check permissions
			if ($attachment->itemId)
			{
				// Get item
				$item = K2Items::getInstance($attachment->itemId);

				// Permissions check
				if (!$item->canEdit)
				{
					K2Response::throwError(JText::_('K2_YOU_ARE_NOT_AUTHORIZED_TO_PERFORM_THIS_OPERATION'), 403);
				}
			}

			// Delete
			$this->model->delete();
		}

		K2Response::setResponse(true);

	}

}
