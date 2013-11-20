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
 * K2 media helper class.
 */

class K2HelperMedia
{
	public static function add($file, $itemId)
	{
		// Filesystem
		$filesystem = K2FileSystem::getInstance();

		// Setup some variables
		$path = 'media/k2/media/'.$itemId;
		$filename = $file['name'];
		$buffer = file_get_contents($file['tmp_name']);
		$target = $path.'/'.$filename;

		// Write it to the filesystem
		$filesystem->write($target, $buffer, true);

		// Response
		$response = new stdClass;
		$response->upload = $filename;
		$response->url = $target;

		// Return
		return $response;

	}

	public static function remove($upload, $itemId)
	{
		// Filesystem
		$filesystem = K2FileSystem::getInstance();

		$path = 'media/k2/media/'.$itemId;

		if ($upload && $filesystem->has($path.'/'.$upload))
		{
			$filesystem->delete($path.'/'.$upload);
		}

		return true;
	}

}
