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

	private static $paths = array(
		'item' => 'media/k2/items',
		'category' => 'media/k2/categories',
		'user' => 'media/k2/users'
	);

	private static $placeholders = array();

	public static function getPlaceholder($type)
	{
		if (!isset(self::$placeholders[$type]))
		{
			jimport('joomla.filesystem.file');
			$application = JFactory::getApplication();
			$template = $application->getTemplate();

			if (JFile::exists(JPATH_SITE.'/templates/'.$template.'/images/placeholder/'.$type.'.png'))
			{
				self::$placeholders[$type] = 'templates/'.$mainframe->getTemplate().'/images/placeholder/'.$type.'.png';
			}
			else
			{
				self::$placeholders[$type] = 'components/com_k2/images/placeholder/'.$type.'.png';
			}
		}
		return self::$placeholders[$type];
	}

	public static function getItemImages($item)
	{
		// Save path
		$savepath = self::$paths['item'];

		// Images
		$images = array();

		// Value
		$value = json_decode($item->image);

		if (isset($value->flag) && $value->flag)
		{
			$category = K2Categories::getInstance($item->catid);
			$sizes = (array)$category->params->get('imageSizes');
		
			$id = md5('Image'.$item->id);
			$timestamp = JFactory::getDate($item->modified)->toUnix();
			foreach ($sizes as $size)
			{
				$image = new stdClass;
				$image->id = $id;
				$image->src = JURI::root(true).'/'.$savepath.'/cache/'.$id.'_'.$size->id.'.jpg?t='.$timestamp;
				$image->url = JURI::root(false).$savepath.'/cache/'.$id.'_'.$size->id.'.jpg?t='.$timestamp;
				$image->alt = $value->caption ? $value->caption : $item->title;
				$image->caption = $value->caption;
				$image->credits = $value->credits;
				$image->flag = 1;
				$images[$size->id] = $image;
			}
		}
		// Return
		return $images;
	}

	public static function addItemImage($file, $path, $categoryId = null)
	{
		// Settings
		$params = JComponentHelper::getParams('com_k2');

		// File system
		$filesystem = K2FileSystem::getInstance();

		// ImageProcessor
		$processor = K2ImageProcessor::getInstance();

		// Get session
		$session = JFactory::getSession();

		// Save path
		$savepath = self::$paths['item'];

		// Get available sizes from category
		$sizes = array();
		if ($categoryId)
		{
			$category = K2Categories::getInstance($categoryId);
			$sizes = (array)$category->params->get('imageSizes');
		}

		// Clean up
		if ($tempImageId = $session->get('K2Temp'))
		{
			// Clean temporary source image
			$key = $savepath.'/src/'.$tempImageId.'.jpg';
			if ($filesystem->has($key))
			{
				$filesystem->delete($key);
			}

			// Clean temporary resized images
			foreach ($sizes as $size)
			{
				$key = $savepath.'/cache/'.$tempImageId.'_'.$size->id.'.jpg';
				if ($filesystem->has($key))
				{
					$filesystem->delete($key);
				}
			}

		}

		// Generate image id
		$imageId = uniqid();

		// Store it to session
		$session->set('K2Temp', $imageId);

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

		// Original image
		$quality = $params->get('imagesQuality', 100);
		$originalImageBuffer = $imageResource->get('jpeg', array('quality' => $quality));
		$filesystem->write($savepath.'/src/'.$imageId.'.jpg', $originalImageBuffer, true);

		// Resized images
		foreach ($sizes as $size)
		{
			// Resize
			$imageResource = $processor->load($originalImageBuffer);
			$imageResource->resize($imageResource->getSize()->widen($size->width));
			$buffer = $imageResource->get('jpeg', array('quality' => $size->quality));
			
			// Write image file
			$filesystem->write($savepath.'/cache/'.$imageId.'_'.$size->id.'.jpg', $buffer, true);
		}

		// Return
		$result = new stdClass;
		$result->temp = $imageId;
		$result->preview = JURI::root(true).'/'.$savepath.'/cache/'.$imageId.'_'.$size->id.'.jpg?t='.time();
		return $result;

	}

	public static function updateItemImage($sourceImageId, $targetImageId, $categoryId = null)
	{
		// File system
		$filesystem = K2FileSystem::getInstance();

		// Save path
		$savepath = self::$paths['item'];

		// Original image
		$source = $sourceImageId.'.jpg';
		$target = $targetImageId.'.jpg';
		if ($filesystem->has($savepath.'/src/'.$target))
		{
			$filesystem->delete($savepath.'/src/'.$target);
		}
		$filesystem->rename($savepath.'/src/'.$source, $savepath.'/src/'.$target);

		// Resized images
		$sizes = array();
		if ($categoryId)
		{
			$category = K2Categories::getInstance($categoryId);
			$sizes = (array)$category->params->get('imageSizes');
		}
		foreach ($sizes as $size)
		{
			$source = $sourceImageId.'_'.$size->id.'.jpg';
			$target = $targetImageId.'_'.$size->id.'.jpg';
			if ($filesystem->has($savepath.'/cache/'.$target))
			{
				$filesystem->delete($savepath.'/cache/'.$target);
			}
			$filesystem->rename($savepath.'/cache/'.$source, $savepath.'/cache/'.$target);
		}
	}

	public static function removeItemImage($imageId, $categoryId = null)
	{
		// File system
		$filesystem = K2FileSystem::getInstance();

		// Save path
		$savepath = self::$paths['item'];

		// Original image
		$key = $savepath.'/src/'.$imageId.'.jpg';
		if ($filesystem->has($key))
		{
			$filesystem->delete($key);
		}

		// Resized images
		$sizes = array();
		if ($categoryId)
		{
			$category = K2Categories::getInstance($categoryId);
			$sizes = (array)$category->params->get('imageSizes');
		}
		foreach ($sizes as $size)
		{
			$key = $savepath.'/cache/'.$imageId.'_'.$size->id.'.jpg';
			if ($filesystem->has($key))
			{
				$filesystem->delete($key);
			}
		}
	}

	public static function getCategoryImage($category)
	{
		// Settings
		$params = JComponentHelper::getParams('com_k2');

		// Initialize value
		$image = null;

		// Save path
		$savepath = self::$paths['category'];

		// Value
		$value = json_decode($category->image);

		if (isset($value->flag) && $value->flag)
		{
			$image = new stdClass;
			$image->id = md5('Image'.$category->id);
			$timestamp = JFactory::getDate($category->modified)->toUnix();
			$image->src = JURI::root(true).'/'.$savepath.'/'.$image->id.'.jpg?t='.$timestamp;
			$image->url = JURI::root(false).$savepath.'/'.$image->id.'.jpg?t='.$timestamp;
			$image->alt = $value->caption ? $value->caption : $category->title;
			$image->caption = $value->caption;
			$image->credits = $value->credits;
			$image->flag = 1;
		}
		else if ($params->get('catImageDefault'))
		{
			$placeholder = self::getPlaceholder('category');
			$image = new stdClass;
			$image->src = JURI::root(true).'/'.$placeholder;
			$image->url = JURI::root(false).$placeholder;
			$image->alt = $category->title;
			$image->caption = '';
			$image->credits = '';
			$image->flag = 0;
		}
		return $image;
	}

	public static function addCategoryImage($file, $path)
	{
		// Params
		$params = JComponentHelper::getParams('com_k2');

		// File system
		$filesystem = K2FileSystem::getInstance();

		// ImageProcessor
		$processor = K2ImageProcessor::getInstance();

		// Get session
		$session = JFactory::getSession();

		// Save path
		$savepath = self::$paths['category'];

		// Clean up
		if ($tempImageId = $session->get('K2Temp'))
		{
			$key = $savepath.'/'.$tempImageId.'.jpg';
			if ($filesystem->has($key))
			{
				$filesystem->delete($key);
			}
		}

		// Generate image id
		$imageId = uniqid();

		// Store it to session
		$session->set('K2Temp', $imageId);

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

		// Resize the image
		$width = $params->get('catImageWidth', 100);
		$quality = $params->get('imagesQuality', 100);
		$imageResource->resize($imageResource->getSize()->widen($width));
		$buffer = $imageResource->get('jpeg', array('quality' => $quality));

		// Write image file
		$filesystem->write($savepath.'/'.$imageId.'.jpg', $buffer, true);

		// Return
		$result = new stdClass;
		$result->temp = $imageId;
		$result->preview = JURI::root(true).'/'.$savepath.'/'.$imageId.'.jpg?t='.time();
		return $result;

	}

	public static function updateCategoryImage($sourceImageId, $targetImageId)
	{
		// Save path
		$savepath = self::$paths['category'];

		// Source
		$source = $sourceImageId.'.jpg';

		// Target
		$target = $targetImageId.'.jpg';

		// Delete current image
		$filesystem = K2FileSystem::getInstance();
		if ($filesystem->has($savepath.'/'.$target))
		{
			$filesystem->delete($savepath.'/'.$target);
		}

		// Rename
		$filesystem->rename($savepath.'/'.$source, $savepath.'/'.$target);
	}

	public static function removeCategoryImage($imageId)
	{
		// Save path
		$savepath = self::$paths['category'];

		// File to delete
		$key = $savepath.'/'.$imageId.'.jpg';

		// Delete the file
		$filesystem = K2FileSystem::getInstance();
		if ($filesystem->has($key))
		{
			$filesystem->delete($key);
		}
	}

	public static function getUserImage($user)
	{
		// Settings
		$params = JComponentHelper::getParams('com_k2');

		// Initialize value
		$image = null;

		// Save path
		$savepath = self::$paths['user'];

		// Value
		$value = json_decode($user->image);

		if (isset($value->flag) && $value->flag)
		{
			$image = new stdClass;
			$image->id = md5('Image'.$user->id);
			$image->src = JURI::root(true).'/'.$savepath.'/'.$image->id.'.jpg';
			$image->url = JURI::root(false).$savepath.'/'.$image->id.'.jpg';
			$image->flag = 1;
		}
		else if ($params->get('userImageDefault'))
		{
			$placeholder = self::getPlaceholder('user');
			$image = new stdClass;
			$image->src = JURI::root(true).'/'.$placeholder;
			$image->url = JURI::root(false).$placeholder;
			$image->flag = 0;
		}
		if ($params->get('gravatar') && !$image->flag)
		{
			if (is_null($image))
			{
				$image = new stdClass;
			}
			$image->src = '//www.gravatar.com/avatar/'.md5($user->email);
			if (isset($image->url))
			{
				$image->src .= '?d='.urlencode($image->url);
			}
			$image->url = $image->src;
			$image->flag = 0;
		}
		return $image;
	}

	public static function addUserImage($file, $path)
	{
		// Params
		$params = JComponentHelper::getParams('com_k2');

		// File system
		$filesystem = K2FileSystem::getInstance();

		// ImageProcessor
		$processor = K2ImageProcessor::getInstance();

		// Get session
		$session = JFactory::getSession();

		// Save path
		$savepath = self::$paths['user'];

		// Clean up
		if ($tempImageId = $session->get('K2Temp'))
		{
			$key = $savepath.'/'.$tempImageId.'.jpg';
			if ($filesystem->has($key))
			{
				$filesystem->delete($key);
			}
		}

		// Generate image id
		$imageId = uniqid();

		// Store it to session
		$session->set('K2Temp', $imageId);

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

		// Resize the image
		$width = $params->get('userImageWidth', 100);
		$quality = $params->get('imagesQuality', 100);
		$imageResource->resize($imageResource->getSize()->widen($width));
		$buffer = $imageResource->get('jpeg', array('quality' => $quality));

		// Write image file
		$filesystem->write($savepath.'/'.$imageId.'.jpg', $buffer, true);

		// Return
		$result = new stdClass;
		$result->temp = $imageId;
		$result->preview = JURI::root(true).'/'.$savepath.'/'.$imageId.'.jpg?t='.time();
		return $result;

	}

	public static function updateUserImage($sourceImageId, $targetImageId)
	{
		// Save path
		$savepath = self::$paths['user'];

		// Source
		$source = $sourceImageId.'.jpg';

		// Target
		$target = $targetImageId.'.jpg';

		// Delete current image
		$filesystem = K2FileSystem::getInstance();
		if ($filesystem->has($savepath.'/'.$target))
		{
			$filesystem->delete($savepath.'/'.$target);
		}

		// Rename
		$filesystem->rename($savepath.'/'.$source, $savepath.'/'.$target);
	}

	public static function removeUserImage($imageId)
	{
		// Save path
		$savepath = self::$paths['user'];

		// File to delete
		$key = $savepath.'/'.$imageId.'.jpg';

		// Delete the file
		$filesystem = K2FileSystem::getInstance();
		if ($filesystem->has($key))
		{
			$filesystem->delete($key);
		}
	}

}
