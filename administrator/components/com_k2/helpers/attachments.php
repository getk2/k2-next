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

jimport('joomla.archive.archive');
jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder');

require_once JPATH_ADMINISTRATOR.'/components/com_k2/classes/filesystem.php';

/**
 * K2 attachments helper class.
 */

class K2HelperAttachments
{
	public static function add($file, $url = null, $replace = null)
	{
		// Application
		$application = JFactory::getApplication();

		// Session
		$session = JFactory::getSession();

		// Handle URLs and uploaded files
		if ($file)
		{
			// Generate gallery name
			$name = uniqid().'_'.$file['name'];

			// Upload the file to the temporary folder
			JFile::upload($file['tmp_name'], $application->getCfg('tmp_path').'/'.$name);
		}
		elseif ($url)
		{
			// Generate gallery name
			$name = uniqid().'_'.basename($url);

			// Download the file to the temporary folder
			$buffer = file_get_contents($url);
			JFile::write($application->getCfg('tmp_path').'/'.$name, $buffer);
		}

		// Add the temporary folder to session so we can perform clean up when needed
		$attachments = $session->get('k2.attachments', array());
		$attachments[] = $name;
		$session->set('k2.attachments', $attachments);

		// Handle previous temporary files
		if ($replace && JFile::exists($application->getCfg('tmp_path').'/'.$replace))
		{
			// Remove from file system
			JFile::delete($application->getCfg('tmp_path').'/'.$replace);

			// Remove from session
			if (($key = array_search($replace, $attachments)) !== false)
			{
				unset($attachments[$key]);
				$session->set('k2.attachments', $attachments);
			}

		}

		// return
		return $name;

	}

	public static function update($attachments, $item)
	{
		// Application
		$application = JFactory::getApplication();

		// Params
		$params = JComponentHelper::getParams('com_k2');

		// Session
		$session = JFactory::getSession();

		// Custom path flag
		$customPathFlag = $params->get('attachmentsFolder') && $params->get('filesystem') == 'Local' ? true : false;

		// File system
		$filesystem = $customPathFlag ? K2FileSystem::getInstance('Local', $params->get('attachmentsFolder')) : K2FileSystem::getInstance();

		// Target path
		$targetPath = $customPathFlag ? '' : 'media/k2/attachments';

		// Uploaded media
		$uploadedAttachments = array();

		// Item id
		$itemId = $item->id;

		// Get attachments model
		$model = K2Model::getInstance('Attachments');

		// Attachment Ids
		$attachmentIds = array();

		// Ordering
		$ordering = 1;

		// Iterate over the galleries and transfer the new attachment files from /tmp to /media/k2/attachments
		foreach ($attachments as $attachment)
		{
			$attachment = (object)$attachment;

			if ($attachment->remove)
			{
				// Delete attachment
				if ($attachment->id)
				{
					$model->setState('id', $attachment->id);
					$model->delete();
				}
			}
			else
			{

				if ($attachment->file)
				{
					// Since we have a file reset the path
					$attachment->path = '';

					// Target file
					$target = $targetPath.'/'.$itemId.'/'.$attachment->file;

					// Source file
					$source = $application->getCfg('tmp_path').'/'.$attachment->file;

					// Check if the attachment has a new uploaded file
					if (JFile::exists($source))
					{
						// Get the generated unique file name
						$uniqueFileName = $attachment->file;

						// Convert back the filename
						$attachment->file = substr($uniqueFileName, strpos($attachment->file, '_') + 1);
						$target = $targetPath.'/'.$itemId.'/'.$attachment->file;

						// Ensure we don't override any existing attachments with the same filename
						if ($filesystem->has($target))
						{
							// File exists, roll back the name changes we will keep the generated file name
							$attachment->file = $uniqueFileName;
							$target = $targetPath.'/'.$itemId.'/'.$attachment->file;
						}

						// Transfer the file from the temporary folder to the current file system
						$buffer = file_get_contents($source);
						$filesystem->write($target, $buffer, true);

						// Delete the temporary file
						JFile::delete($source);

						// Remove temporary file from session
						$temp = $session->get('k2.attachments', array());
						if (($key = array_search($attachment->file, $temp)) !== false)
						{
							unset($temp[$key]);
							$session->set('k2.attachments', $temp);
						}
					}

					// Push the attachment to uploaded attachment files array
					$uploadedAttachments[] = $target;

				}
				else if ($attachment->path)
				{
					// Since we have a path reset the file
					$attachment->file = '';
				}

				// Save attachment
				$attachment->ordering = $ordering;
				$attachment->itemId = $itemId;
				$model->setState('data', (array)$attachment);
				if ($model->save())
				{
					// Push the id of the attachment to the array
					$attachmentIds[] = (int)$model->getState('id');
				}
				$ordering++;
			}
		}

		// Update item table
		$item->attachments = json_encode($attachmentIds);
		$item->store();

		// Iterate over the media files in /media/k2/media and delete those who have been removed by the user
		$folderKey = $targetPath.'/'.$itemId.'/';
		$folderKey = ltrim($folderKey, '/');
		$keys = $filesystem->listKeys($folderKey);
		$files = isset($keys['keys']) ? $keys['keys'] : $keys;
		foreach ($files as $attachmentKey)
		{
			if ($key != $folderKey && !in_array($attachmentKey, $uploadedAttachments) && $filesystem->has($attachmentKey))
			{
				$filesystem->delete($attachmentKey);
			}
		}

	}

	public static function purge()
	{
		// Application
		$application = JFactory::getApplication();

		// Session
		$session = JFactory::getSession();

		// Temporary attachments
		$attachments = $session->get('k2.attachments', array());

		foreach ($attachments as $attachment)
		{
			if ($attachment && JFile::exists($application->getCfg('tmp_path').'/'.$attachment))
			{
				// Remove from tmp folder
				JFile::delete($application->getCfg('tmp_path').'/'.$attachment);
			}
		}
		$session->set('k2.attachments', array());

	}

}
