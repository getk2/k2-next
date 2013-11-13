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
 * Galleries JSON controller.
 */

class K2ControllerGalleries extends K2Controller
{

	public function upload()
	{
		// Check for token
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		// Filesystem
		require_once JPATH_ADMINISTRATOR.'/components/com_k2/classes/filesystem.php';
		$filesystem = K2FileSystem::getInstance();

		// Get input
		$input = JFactory::getApplication()->input;
		$itemId = $input->get('itemId', '', 'cmd');
		$upload = $input->get('upload', '', 'cmd');
		$gallery = uniqid();
		$folder = $itemId;
		$archive = $file = $input->files->get('archive');

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
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		// Get id from input
		$input = $this->input;
		$itemId = $input->get('itemId', '', 'cmd');
		$itemFolder = $itemId;
		$upload = $input->get('upload', '', 'cmd');

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
			var_dump($keys);
			if (empty($keys['dirs']))
			{
				$filesystem->delete('media/k2/galleries/'.$itemFolder);
			}

		}

		// Return
		K2Response::setResponse(true);
	}

}
