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
require_once JPATH_ADMINISTRATOR.'/components/com_k2/classes/imageprocessor.php';

/**
 * K2 images helper class.
 */

class K2HelperImages
{

	private static $types = array(
		'item',
		'category'
	);

	private static function getTypes()
	{
		return self::$types;
	}

	public static function addResourceImage($type, $id, $file, $path)
	{

		if (!in_array($type, self::getTypes()))
		{
			return false;
		}

		// Filesystem
		$filesystem = K2FileSystem::getInstance();

		// ImageProcessor
		$processor = K2ImageProcessor::getInstance();

		// Generate the base file name
		$baseFileName = md5('Image'.$id);

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

		if ($type == 'item')
		{
			$preview = self::_generateItemImage($baseFileName, $imageResource);
		}
		else if ($type == 'category')
		{
			$preview = self::_generateCategoryImage($baseFileName, $imageResource);
		}

		// Return
		$result = new stdClass;
		$result->id = $baseFileName;
		$result->preview = $preview;
		return $result;
	}

	public static function removeResourceImage($type, $id)
	{

		if (!in_array($type, self::getTypes()))
		{
			return false;
		}

		if ($type == 'item')
		{
			$preview = self::_deleteItemImage($id);
		}
		else if ($type == 'category')
		{
			$preview = self::_deleteCategoryImage($id);
		}

	}

	private static function _generateItemImage($baseFileName, $imageResource)
	{
		// Filesystem
		$filesystem = K2FileSystem::getInstance();

		// Save path
		$savepath = 'media/k2/items';

		// Source image
		$filesystem->write($savepath.'/src/'.$baseFileName.'.jpg', $imageResource->__toString(), true);

		// Get available sizes
		$sizes = array(
			'XL' => 600,
			'L' => 400,
			'M' => 240,
			'S' => 180,
			'XS' => 100
		);

		// Resized images
		foreach ($sizes as $size => $width)
		{
			$filename = $baseFileName.'_'.$size.'.jpg';
			$imageResource->resize($imageResource->getSize()->widen($width));
			$filesystem->write($savepath.'/cache/'.$filename, $imageResource->__toString(), true);
		}

		// Return preview url
		return JURI::root(true).'/'.$savepath.'/cache/'.$baseFileName.'_S.jpg?t='.time();
	}

	private static function _generateCategoryImage($baseFileName, $imageResource)
	{
		// Filesystem
		$filesystem = K2FileSystem::getInstance();

		// Save path
		$savepath = 'media/k2/categories';

		// Write image file
		$filesystem->write($savepath.'/'.$baseFileName.'.jpg', $imageResource->__toString(), true);

		// Return
		return JURI::root(true).'/'.$savepath.'/'.$baseFileName.'.jpg?t='.time();
	}

	private static function _deleteItemImage($id)
	{
		// Save path
		$savepath = 'media/k2/items';

		// Keys to delete
		$keys = array();

		// Detect the files to delete
		$keys[] = $savepath.'/src/'.$id.'.jpg';
		$sizes = array(
			'XL' => 600,
			'L' => 400,
			'M' => 240,
			'S' => 180,
			'XS' => 100
		);
		foreach ($sizes as $size => $width)
		{
			$keys[] = $savepath.'/cache/'.$id.'_'.$size.'.jpg';
		}

		// Delete the files
		$filesystem = K2FileSystem::getInstance();
		foreach ($keys as $key)
		{
			if ($filesystem->has($key))
			{
				$filesystem->delete($key);
			}
		}

	}

	private static function _deleteCategoryImage($id)
	{
		// Save path
		$savepath = 'media/k2/categories';

		// File to delete
		$key = $savepath.'/'.$id.'.jpg';

		// Delete the file
		$filesystem = K2FileSystem::getInstance();
		if ($filesystem->has($key))
		{
			$filesystem->delete($key);
		}
	}

}
