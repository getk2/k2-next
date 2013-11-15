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

require_once JPATH_ADMINISTRATOR.'/components/com_k2/classes/filesystem.php';

/**
 * K2 galleries helper class.
 */

class K2HelperGalleries
{
	public static function add()
	{

	}

	public static function remove($gallery, $itemId)
	{
		// Filesystem
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
