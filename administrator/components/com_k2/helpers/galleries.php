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
 * K2 galleries helper class.
 */

class K2HelperGalleries
{
	public static function add($archive)
	{
		// Application
		$application = JFactory::getApplication();

		// Generate gallery name
		$gallery = uniqid('k2_gallery_');

		// Random temporary path
		$tmpFolder = $application->getCfg('tmp_path').'/'.$gallery;

		// Create the folder
		JFolder::create($tmpFolder);

		// Upload the file to the temporary folder
		JFile::upload($archive['tmp_name'], $tmpFolder.'/'.$archive['name']);

		// Extract the archive
		JArchive::extract($tmpFolder.'/'.$archive['name'], $tmpFolder);

		// Delete the archive
		JFile::delete($tmpFolder.'/'.$archive['name']);

		// return
		return $gallery;

	}

	public static function update($source, $target)
	{
		// Application
		$application = JFactory::getApplication();

		// File system
		$filesystem = K2FileSystem::getInstance();

		// Get the temporary folder
		$tmpFolder = $application->getCfg('tmp_path').'/'.$source;

		// Transfer the files of the archive to the current file system
		$files = JFolder::files($tmpFolder);
		$target = 'media/k2/galleries/'.$itemId.'/'.$gallery;
		foreach ($files as $file)
		{
			$buffer = JFile::read($file);
			$filesystem->write($target.'/'.$file, $buffer, true);
		}

		// Delete the temporary folder
		JFolder::delete($tmpFolder);
	}

	public static function clean($gallery)
	{
		// Application
		$application = JFactory::getApplication();

		// Delete temporary gallery
		if ($gallery && JFolder::exists($application->getCfg('tmp_path').'/'.$gallery))
		{
			JFolder::delete($application->getCfg('tmp_path').'/'.$gallery);
		}

	}

	public static function remove($gallery, $itemId)
	{
		// File system
		$filesystem = K2FileSystem::getInstance();

		// Key
		$galleryKey = 'media/k2/galleries/'.$itemId.'/'.$gallery;

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
		$keys = $filesystem->listKeys('media/k2/galleries/'.$itemId.'/');
		if (empty($keys['dirs']))
		{
			$filesystem->delete('media/k2/galleries/'.$itemId);
		}

		return true;
	}

}
