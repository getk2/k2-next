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
		'category',
		'user'
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
		else if ($type == 'user')
		{
			$preview = self::_generateUserImage($baseFileName, $imageResource);
		}

		// Return
		$result = new stdClass;
		$result->id = $baseFileName;
		$result->preview = $preview;
		return $result;
	}

	public static function removeResourceImage($type, $resourceId, $id = null)
	{

		if (!in_array($type, self::getTypes()))
		{
			return false;
		}

		if (is_null($id))
		{
			$id = md5('Image'.$resourceId);
		}

		if ($type == 'item')
		{
			$preview = self::_deleteItemImage($id);
		}
		else if ($type == 'category')
		{
			$preview = self::_deleteCategoryImage($id);
		}
		else if ($type == 'user')
		{
			$preview = self::_deleteUserImage($id);
		}

	}

	public static function getResourceImages($type, $resource)
	{

		if (!in_array($type, self::getTypes()))
		{
			return false;
		}

		if ($type == 'item')
		{
			return self::_getItemImages($resource);
		}
		else if ($type == 'category')
		{
			return self::_getCategoryImage($resource);
		}
		else if ($type == 'user')
		{
			return self::_getUserImage($resource);
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
	
	private static function _generateUserImage($baseFileName, $imageResource)
	{
		// Filesystem
		$filesystem = K2FileSystem::getInstance();

		// Save path
		$savepath = 'media/k2/users';

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
	
	private static function _deleteUserImage($id)
	{
		// Save path
		$savepath = 'media/k2/users';

		// File to delete
		$key = $savepath.'/'.$id.'.jpg';
		
		// Delete the file
		$filesystem = K2FileSystem::getInstance();
		if ($filesystem->has($key))
		{
			$filesystem->delete($key);
		}
	}

	private static function _getItemImages($item)
	{
		// Save path
		$savepath = 'media/k2/items';

		// Images
		$images = array();
		$id = null;

		// Value
		$value = json_decode($item->image);

		if (isset($value->flag) && $value->flag)
		{
			// Sizes
			$sizes = array(
				'XL' => 600,
				'L' => 400,
				'M' => 240,
				'S' => 180,
				'XS' => 100
			);
			$id = md5('Image'.$item->id);
			$timestamp = JFactory::getDate($item->modified)->toUnix();
			foreach ($sizes as $size => $width)
			{
				$images[$size] = JURI::root(true).'/'.$savepath.'/cache/'.$id.'_'.$size.'.jpg?t='.$timestamp;
			}
		}

		// Return
		$result = new stdClass;
		$result->images = $images;
		$result->id = $id;

		return $result;
	}

	private static function _getCategoryImage($category)
	{
		// Save path
		$savepath = 'media/k2/categories';

		// Images
		$image = null;
		$id = null;

		// Value
		$value = json_decode($category->image);

		if (isset($value->flag) && $value->flag)
		{
			$id = md5('Image'.$category->id);
			$timestamp = JFactory::getDate($category->modified)->toUnix();
			$image = JURI::root(true).'/'.$savepath.'/'.$id.'.jpg?t='.$timestamp;
		}

		// Return
		$result = new stdClass;
		$result->image = $image;
		$result->id = $id;

		return $result;
	}
	
	private static function _getUserImage($user)
	{
		// Save path
		$savepath = 'media/k2/users';

		// Images
		$image = null;
		$id = null;

		// Value
		if ($user->image)
		{
			$id = md5('Image'.$user->id);
			$image = JURI::root(true).'/'.$savepath.'/'.$id.'.jpg';
		}

		// Return
		$result = new stdClass;
		$result->image = $image;
		$result->id = $id;

		return $result;
	}

}
