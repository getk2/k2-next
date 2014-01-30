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

	private static $paths = array('item' => 'media/k2/items', 'category' => 'media/k2/categories', 'user' => 'media/k2/users');

	private static $placeholders = array();

	public static function add($file, $path, $replace = null)
	{
		// Application
		$application = JFactory::getApplication();

		// Session
		$session = JFactory::getSession();

		// ImageProcessor
		$processor = K2ImageProcessor::getInstance();

		// Load the image
		if ($path)
		{
			$filesystem = K2FileSystem::getInstance('Local');
			$buffer = $filesystem->read($path);
			$imageResource = $processor->load($buffer);
		}
		else
		{
			$imageResource = $processor->open($file['tmp_name']);
		}

		// Convert to JPEG
		$buffer = $imageResource->get('jpeg', array('quality' => 100));

		// Generate temporary image name
		$name = uniqid().'.jpg';

		// Upload the file to the temporary folder
		JFile::write($application->getCfg('tmp_path').'/'.$name, $buffer);

		// Add the temporary folder to session so we can perform clean up when needed
		$session->set('k2.image', $name);

		// Handle previous temporary files
		if ($replace && JFile::exists($application->getCfg('tmp_path').'/'.$replace))
		{
			// Remove from file system
			JFile::delete($application->getCfg('tmp_path').'/'.$replace);
		}

		// Return
		$result = new stdClass;
		$result->temp = $name;
		$result->preview = JURI::root(true).'/tmp/'.$name.'?t='.time();
		return $result;

	}

	public static function purge()
	{
		// Application
		$application = JFactory::getApplication();

		// Session
		$session = JFactory::getSession();

		// Temporary image file
		$image = $session->get('k2.image', array());

		if ($image && JFile::exists($application->getCfg('tmp_path').'/'.$image))
		{
			// Remove from tmp folder
			JFile::delete($application->getCfg('tmp_path').'/'.$image);
		}

		$session->set('k2.image', null);

	}

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
			$id = md5('Image'.$item->id);

			$params = JComponentHelper::getParams('com_k2');
			$sizes = (array)$params->get('imageSizes');
			if ($params->get('imageTimestamp'))
			{
				$timestamp = JFactory::getDate($item->modified)->toUnix();
			}
			// Add resized images to the array
			foreach ($sizes as $size)
			{
				$image = new stdClass;
				$image->id = $id;
				$image->src = K2Filesystem::getURIRoot(true).$savepath.'/cache/'.$id.'_'.$size->id.'.jpg';
				$image->url = K2Filesystem::getURIRoot(false).$savepath.'/cache/'.$id.'_'.$size->id.'.jpg';
				if (isset($timestamp))
				{
					$image->src .= '?t='.$timestamp;
					$image->url .= '?t='.$timestamp;
				}
				$image->alt = $value->caption ? $value->caption : $item->title;
				$image->caption = $value->caption;
				$image->credits = $value->credits;
				$image->width = $size->width;
				$image->flag = 1;
				$images[$size->id] = $image;
			}

			// Add the source image to the array
			$image = new stdClass;
			$image->id = $id;
			$image->src = K2Filesystem::getURIRoot(true).$savepath.'/src/'.$id.'.jpg';
			$image->url = K2Filesystem::getURIRoot(false).$savepath.'/src/'.$id.'.jpg';
			if (isset($timestamp))
			{
				$image->src .= '?t='.$timestamp;
				$image->url .= '?t='.$timestamp;
			}
			$image->alt = $value->caption ? $value->caption : $item->title;
			$image->caption = $value->caption;
			$image->credits = $value->credits;
			$image->flag = 1;
			$images['src'] = $image;

		}
		// Return
		return $images;
	}

	public static function addItemImage($file, $path)
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

		// Get available sizes from settings
		$sizes = (array)$params->get('imageSizes');

		// Clean up
		if ($tempImageId = $session->get('k2.image'))
		{
			// Clean temporary source image
			$key = $savepath.'/src/'.$tempImageId.'.jpg';
			if ($filesystem->has($key))
			{
				$filesystem->delete($key);
			}
		}

		// Generate image id
		$imageId = uniqid();

		// Store it to session
		$session->set('k2.image', $imageId);

		// Get image depending on source
		if ($path)
		{
			// Local or remote
			if (strpos($path, 'http') === 0)
			{
				$buffer = JFile::read($path);
			}
			else
			{
				$buffer = JFile::read(JPATH_SITE.'/'.$path);
			}
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
		K2FileSystem::writeImageFile($savepath.'/src/'.$imageId.'.jpg', $originalImageBuffer);

		// Return
		$result = new stdClass;
		$result->temp = $imageId;
		$result->preview = K2Filesystem::getURIRoot(true).$savepath.'/src/'.$imageId.'.jpg?t='.time();
		return $result;

	}

	public static function updateItemImage($sourceImageId, $targetImageId, $categoryId = null)
	{
		// File system
		$filesystem = K2FileSystem::getInstance();

		// ImageProcessor
		$processor = K2ImageProcessor::getInstance();

		// Save path
		$savepath = self::$paths['item'];

		// Rename temporary image
		$source = $sourceImageId.'.jpg';
		$target = $targetImageId.'.jpg';
		K2FileSystem::writeImageFile($savepath.'/src/'.$target, $filesystem->read($savepath.'/src/'.$source));
	}

	public static function resizeItemImage($imageId, $categoryId)
	{
		// File system
		$filesystem = K2FileSystem::getInstance();

		// ImageProcessor
		$processor = K2ImageProcessor::getInstance();

		// Save path
		$savepath = self::$paths['item'];

		// Buffer of source image
		$sourceBuffer = $filesystem->read($savepath.'/src/'.$imageId.'.jpg');

		// Get sizes from global settings
		$params = JComponentHelper::getParams('com_k2');
		$sizes = (array)$params->get('imageSizes');

		// Check for category overrides
		$overrides = array();
		if ($categoryId)
		{
			$category = K2Categories::getInstance($categoryId);
			$categorySizes = (array)$category->params->get('imageSizes');
			foreach ($categorySizes as $categorySize)
			{
				if ((int)$categorySize->width > 0)
				{
					$overrides[$categorySize->id] = (int)$categorySize->width;
				}
			}
		}

		// Resize image
		foreach ($sizes as $size)
		{
			// Filename
			$filename = $imageId.'_'.$size->id.'.jpg';

			// Width. Check for overrides
			$width = (array_key_exists($size->id, $overrides) && (int)$size->width != (int)$overrides[$size->id]) ? $overrides[$size->id] : $size->width;

			// Resize
			$imageResource = $processor->load($sourceBuffer);
			$imageResource->resize($imageResource->getSize()->widen($width));
			$buffer = $imageResource->get('jpeg', array('quality' => $size->quality));
			K2FileSystem::writeImageFile($savepath.'/cache/'.$filename, $buffer);
		}

	}

	public static function removeItemImage($imageId)
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
		$params = JComponentHelper::getParams('com_k2');
		$sizes = (array)$params->get('imageSizes');
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

		// File system
		$filesystem = K2FileSystem::getInstance();

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
			$image->src = K2FileSystem::getUriRoot(true).$savepath.'/'.$image->id.'.jpg';
			$image->url = K2FileSystem::getUriRoot().$savepath.'/'.$image->id.'.jpg';
			if ($params->get('imageTimestamp'))
			{
				$timestamp = JFactory::getDate($category->modified)->toUnix();
				$image->src .= '?t='.$timestamp;
				$image->url .= '?t='.$timestamp;
			}
			$image->alt = $value->caption ? $value->caption : $category->title;
			$image->caption = $value->caption;
			$image->credits = $value->credits;
			$image->flag = 1;
		}
		else if ($params->get('catImageDefault'))
		{
			$placeholder = self::getPlaceholder('category');
			$image = new stdClass;
			$image->src = K2Filesystem::getURIRoot(true).$placeholder;
			$image->url = K2Filesystem::getURIRoot(false).$placeholder;
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
		if ($tempImageId = $session->get('k2.image'))
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
		$session->set('k2.image', $imageId);

		// Get image depending on source
		if ($path)
		{
			// Local or remote
			if (strpos($path, 'http') === 0)
			{
				$buffer = JFile::read($path);
			}
			else
			{
				$buffer = JFile::read(JPATH_SITE.'/'.$path);
			}
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
		K2FileSystem::writeImageFile($savepath.'/'.$imageId.'.jpg', $buffer);

		// Return
		$result = new stdClass;
		$result->temp = $imageId;
		$result->preview = K2Filesystem::getUriRoot(true).$savepath.'/'.$imageId.'.jpg?t='.time();
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

		// Rename
		K2FileSystem::writeImageFile($savepath.'/'.$target, $filesystem->read($savepath.'/'.$source));
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
			$image->src = K2Filesystem::getURIRoot(true).$savepath.'/'.$image->id.'.jpg';
			$image->url = K2Filesystem::getURIRoot(false).$savepath.'/'.$image->id.'.jpg';
			$image->alt = $user->name;
			$image->flag = 1;
		}
		else if ($params->get('userImageDefault'))
		{
			$placeholder = self::getPlaceholder('user');
			$image = new stdClass;
			$image->src = K2Filesystem::getURIRoot(true).$placeholder;
			$image->url = K2Filesystem::getURIRoot(false).$placeholder;
			$image->alt = $user->name;
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
			$image->alt = $user->name;
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
		if ($tempImageId = $session->get('k2.image'))
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
		$session->set('k2.image', $imageId);

		// Get image depending on source
		if ($path)
		{
			// Local or remote
			if (strpos($path, 'http') === 0)
			{
				$buffer = JFile::read($path);
			}
			else
			{
				$buffer = JFile::read(JPATH_SITE.'/'.$path);
			}
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
		K2FileSystem::writeImageFile($savepath.'/'.$imageId.'.jpg', $buffer);

		// Return
		$result = new stdClass;
		$result->temp = $imageId;
		$result->preview = K2Filesystem::getURIRoot(true).$savepath.'/'.$imageId.'.jpg?t='.time();
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

		// Rename
		$filesystem = K2FileSystem::getInstance();
		K2FileSystem::writeImageFile($savepath.'/'.$target, $filesystem->read($savepath.'/'.$source));

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
