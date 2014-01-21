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
 * K2 media helper class.
 */

class K2HelperMedia
{
	public static function add($file, $replace = null)
	{
		// Application
		$application = JFactory::getApplication();

		// Session
		$session = JFactory::getSession();

		// Generate gallery name
		$name = uniqid().'.'.JFile::getExt($file['name']);

		// Upload the file to the temporary folder
		JFile::upload($file['tmp_name'], $application->getCfg('tmp_path').'/'.$name);

		// Add the temporary folder to session so we can perform clean up when needed
		$media = $session->get('k2.media', array());
		$media[] = $name;
		$session->set('k2.media', $media);

		// Handle previous temporary folder
		if ($replace && JFile::exists($application->getCfg('tmp_path').'/'.$replace))
		{
			// Remove from file system
			JFile::delete($application->getCfg('tmp_path').'/'.$replace);

			// Remove from session
			if (($key = array_search($replace, $media)) !== false)
			{
				unset($media[$key]);
				$session->set('k2.media', $media);
			}

		}

		// return
		return $name;

	}

	public static function update($media, $item)
	{
		// Application
		$application = JFactory::getApplication();

		// Session
		$session = JFactory::getSession();

		// File system
		$filesystem = K2FileSystem::getInstance();
		
		// Item id
		$itemId = $item->id;

		// Uploaded media
		$uploadedMedia = array();

		// Iterate over the galleries and transfer the new media files from /tmp to /media/k2/media
		foreach ($media as $entry)
		{
			$entry = (object)$entry;
			if (!$entry->remove && $entry->upload)
			{
				$source = $application->getCfg('tmp_path').'/'.$entry->upload;

				$target = 'media/k2/media/'.$itemId.'/'.$entry->upload;

				// Push the gallery to valid array
				$uploadedMedia[] = $target;

				// Check if the gallery is new
				if (JFile::exists($source))
				{
					// Transfer the file from the temporary folder to the current file system
					$buffer = JFile::read($source);
					$filesystem->write($target, $buffer, true);

					// Delete the temporary file
					JFile::delete($source);

					// Remove temporary folder from session
					$temp = $session->get('k2.media', array());
					if (($key = array_search($entry->upload, $temp)) !== false)
					{
						unset($temp[$key]);
						$session->set('k2.media', $temp);
					}
				}

			}
		}

		// Iterate over the media files in /media/k2/media and delete those who have been removed by the user
		$keys = $filesystem->listKeys('media/k2/media/'.$itemId.'/');
		$files = $keys['keys'];

		foreach ($files as $mediaKey)
		{
			if (!in_array($mediaKey, $uploadedMedia) && $filesystem->has($mediaKey))
			{
				$filesystem->delete($mediaKey);
			}
		}

		// Check if the item folder contains more media files. If not delete it.
		$keys = $filesystem->listKeys('media/k2/media/'.$itemId.'/');
		if (empty($keys['keys']) && $filesystem->has('media/k2/media/'.$itemId))
		{
			$filesystem->delete('media/k2/media/'.$itemId);
		}

	}

	public static function remove($media, $itemId)
	{
		// File system
		$filesystem = K2FileSystem::getInstance();

		// Key
		$mediaKey = 'media/k2/media/'.$itemId.'/'.$media;

		if ($filesystem->has($mediaKey))
		{
			$filesystem->delete($mediaKey);

			// Check if the item folder contains more media. If not delete it.
			$keys = $filesystem->listKeys('media/k2/media/'.$itemId.'/');
			if (empty($keys['keys']))
			{
				$filesystem->delete('media/k2/media/'.$itemId);
			}
		}

		return true;
	}

	public static function purge()
	{
		// Application
		$application = JFactory::getApplication();

		// Session
		$session = JFactory::getSession();

		// Temporary folders
		$media = $session->get('k2.media', array());

		foreach ($media as $entry)
		{
			if ($entry && JFile::exists($application->getCfg('tmp_path').'/'.$entry))
			{
				// Remove from tmp folder
				JFile::delete($application->getCfg('tmp_path').'/'.$entry);
			}
		}
		$session->set('k2.media', array());

	}

}
