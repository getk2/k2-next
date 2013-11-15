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

/**
 * Galleries JSON controller.
 */

class K2ControllerGalleries extends K2Controller
{

	public function upload()
	{
		// Check for token
		JSession::checkToken() or K2Response::throwError(JText::_('JINVALID_TOKEN'));

		// Filesystem
		$filesystem = K2FileSystem::getInstance();

		// Get input
		$input = JFactory::getApplication()->input;
		$itemId = $input->get('itemId', '', 'cmd');
		$upload = $input->get('upload', '', 'cmd');
		$gallery = uniqid();
		$folder = $itemId;
		$archive = $file = $input->files->get('archive');

		// Permissions check
		if (is_numeric($itemId))
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

		// Extract the gallery to a temporaty folder
		jimport('joomla.archive.archive');
		jimport('joomla.filesystem.file');
		jimport('joomla.filesystem.folder');

		// Random temporary path
		$tmpFolder = JPATH_SITE.'/media/k2/galleries/'.uniqid();

		// Create the folder
		JFolder::create($tmpFolder);

		// Upload the file to the temporary folder
		JFile::upload($archive['tmp_name'], $tmpFolder.'/'.$archive['name']);

		// Extract the archive
		JArchive::extract($tmpFolder.'/'.$archive['name'], $tmpFolder);

		// Delete the archive
		JFile::delete($tmpFolder.'/'.$archive['name']);

		// Transfer the files of the archive to the current filesystem
		$files = JFolder::files($tmpFolder);
		$target = 'media/k2/galleries/'.$folder.'/'.$gallery;
		foreach ($files as $file)
		{
			$buffer = JFile::read($file);
			$filesystem->write($target.'/'.$file, $buffer, true);
		}

		// Delete the temporary folder
		JFolder::delete($tmpFolder);

		// If the current gallery is uploaded then we should remove it when we upload a new one
		if ($upload && $filesystem->has('media/k2/galleries/'.$folder.'/'.$upload))
		{
			$filesystem->delete('media/k2/galleries/'.$folder.'/'.$upload);
		}

		// Response
		echo json_encode($gallery);

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

		// Get id from input
		$input = $this->input;
		$itemId = $input->get('itemId', '', 'cmd');
		$itemFolder = $itemId;
		$upload = $input->get('upload', '', 'cmd');

		// Permissions check
		$user = JFactory::getUser();
		if (is_numeric($itemId))
		{
			// Existing items check permission for specific item
			$authorised = K2Items::getInstance($itemId)->canEdit;
		}
		else
		{
			$authorised = true;
		}
		if (!$authorised)
		{
			K2Response::throwError(JText::_('K2_YOU_ARE_NOT_AUTHORIZED_TO_PERFORM_THIS_OPERATION'), 403);
		}

		if ($upload)
		{
			// Filesystem
			require_once JPATH_ADMINISTRATOR.'/components/com_k2/classes/filesystem.php';
			$filesystem = K2FileSystem::getInstance();

			// Key
			$galleryKey = 'media/k2/galleries/'.$itemFolder.'/'.$upload;

			$files = $filesystem->listKeys($galleryKey);

			foreach ($files['keys'] as $key)
			{
				if ($filesystem->has($key))
				{
					$filesystem->delete($key);
				}
			}
			$filesystem->delete($galleryKey);

			// Check if the item folder contains more galleries. If not delete it.
			$keys = $filesystem->listKeys('media/k2/galleries/'.$itemFolder.'/');
			if (empty($keys['dirs']))
			{
				$filesystem->delete('media/k2/galleries/'.$itemFolder);
			}

		}

		// Return
		K2Response::setResponse(true);
	}

}
