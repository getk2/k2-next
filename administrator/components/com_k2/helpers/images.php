<?php
/**
 * @version		3.0.0b
 * @package		K2
 * @author		JoomlaWorks http://www.joomlaworks.net
 * @copyright	Copyright (c) 2006 - 2014 JoomlaWorks Ltd. All rights reserved.
 * @license		GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 */

// no direct access
defined('_JEXEC') or die ;

require_once JPATH_ADMINISTRATOR.'/components/com_k2/classes/filesystem.php';
require_once JPATH_ADMINISTRATOR.'/components/com_k2/classes/imageprocessor.php';
require_once JPATH_ADMINISTRATOR.'/components/com_k2/resources/image.php';

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

	public static function add($type, $file, $path, $replace = null, $categoryId = null)
	{
		// Application
		$application = JFactory::getApplication();

		// Params
		$params = JComponentHelper::getParams('com_k2');

		// Images quality
		$quality = $params->get('imagesQuality', 100);

		// ImageProcessor
		$processor = K2ImageProcessor::getInstance();

		// Get session
		$session = JFactory::getSession();

		// Generate image id
		$imageId = uniqid();

		// Save path
		$savepath = ($type == 'item') ? JPATH_SITE.'/tmp/'.$imageId : JPATH_SITE.'/tmp';

		// Ensure for items that folder exists
		if ($type == 'item' && !JFolder::exists($savepath))
		{
			JFolder::create($savepath);
		}

		// Clean up
		if ($replace)
		{
			if ($type == 'item')
			{
				if (JFolder::exists(JPATH_SITE.'/tmp/'.$replace))
				{
					JFolder::delete(JPATH_SITE.'/tmp/'.$replace);
				}
			}
			else
			{
				if (JFile::exists($savepath.'/'.$replace.'.jpg'))
				{
					JFile::delete($savepath.'/'.$replace.'.jpg');
				}
			}

		}

		// Store it to session
		$session->set('k2.'.$type.'.image', $imageId);

		// Get image depending on source
		if ($path)
		{
			// Local or remote
			if (strpos($path, 'http') === 0)
			{
				$buffer = file_get_contents($path);
			}
			else
			{
				$buffer = file_get_contents(JPATH_SITE.'/'.$path);
			}
			$imageResource = $processor->load($buffer);
		}
		else
		{
			$source = $file['tmp_name'];
			$imageResource = $processor->open($source);
		}

		// Resize the image
		if ($type == 'item')
		{
			// Src image
			$sourceImageBuffer = $imageResource->get('jpeg', array('quality' => $quality));
			JFile::write($savepath.'/src/'.$imageId.'.jpg', $sourceImageBuffer);

			// Get sizes from global settings
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
				$imageResource = $processor->load($sourceImageBuffer);
				$imageResource->resize($imageResource->getSize()->widen($width));
				$buffer = $imageResource->get('jpeg', array('quality' => $size->quality));
				JFile::write($savepath.'/cache/'.$filename, $buffer);
			}

			// Pass the last size as preview
			$adminPreviewImageSize = $params->get('adminPreviewImageSize');
			if ($adminPreviewImageSize)
			{
				$preview = JURI::root(true).'/tmp/'.$imageId.'/cache/'.$imageId.'_'.$adminPreviewImageSize.'.jpg';
			}
			else
			{
				$preview = JURI::root(true).'/tmp/'.$imageId.'/cache/'.$filename.'?t='.time();
			}
		}
		else
		{
			$width = ($type == 'category') ? $params->get('catImageWidth', 100) : $params->get('userImageWidth', 100);
			$imageResource->resize($imageResource->getSize()->widen($width));
			$buffer = $imageResource->get('jpeg', array('quality' => $quality));
			JFile::write($savepath.'/'.$imageId.'.jpg', $buffer);
			$preview = JURI::root(true).'/tmp/'.$imageId.'.jpg?t='.time();
		}

		// Return
		$result = new stdClass;
		$result->temp = $imageId;
		$result->preview = $preview;
		return $result;
	}

	public static function update($type, $image, $table)
	{
		// File system
		$filesystem = K2FileSystem::getInstance();

		// ImageProcessor
		$processor = K2ImageProcessor::getInstance();

		// Params
		$params = JComponentHelper::getParams('com_k2');

		// Save path
		$savepath = self::$paths[$type];

		// Current image
		$currentImageId = md5('Image'.$table->id);

		// Temporary (new) image
		$tempImageId = $image['temp'];

		// Image has been removed
		if ($image['remove'])
		{

			if ($type == 'item')
			{
				// Src image
				$key = $savepath.'/src/'.$currentImageId.'.jpg';
				if ($filesystem->has($key))
				{
					$filesystem->delete($key);
				}

				// Resized images
				$sizes = (array)$params->get('imageSizes');
				foreach ($sizes as $size)
				{
					$key = $savepath.'/cache/'.$currentImageId.'_'.$size->id.'.jpg';
					if ($filesystem->has($key))
					{
						$filesystem->delete($key);
					}
				}
			}
			else
			{
				// File to delete
				$key = $savepath.'/'.$currentImageId.'.jpg';

				// Delete the file
				if ($filesystem->has($key))
				{
					$filesystem->delete($key);
				}
			}

		}
		// We have a new image so we need to update our filesystem
		else if ($tempImageId)
		{
			// Item image
			if ($type == 'item')
			{
				if (JFile::exists(JPATH_SITE.'/tmp/'.$tempImageId.'/src/'.$tempImageId.'.jpg'))
				{
					// Src image
					$srcImageBuffer = file_get_contents(JPATH_SITE.'/tmp/'.$tempImageId.'/src/'.$tempImageId.'.jpg');
					K2FileSystem::writeImageFile($savepath.'/src/'.$currentImageId.'.jpg', $srcImageBuffer);

					// Get sizes from global settings
					$sizes = (array)$params->get('imageSizes');

					// Check for category overrides
					$overrides = array();
					if ($table->catid)
					{
						$category = K2Categories::getInstance($table->catid);
						$categorySizes = (array)$category->params->get('imageSizes');
						foreach ($categorySizes as $categorySize)
						{
							if ((int)$categorySize->width > 0)
							{
								$overrides[$categorySize->id] = (int)$categorySize->width;
							}
						}
					}

					// Resized images
					foreach ($sizes as $size)
					{
						// Filename
						$filename = $currentImageId.'_'.$size->id.'.jpg';

						// Width. Check for overrides
						$width = (array_key_exists($size->id, $overrides) && (int)$size->width != (int)$overrides[$size->id]) ? $overrides[$size->id] : $size->width;

						// Resize
						$imageResource = $processor->load($srcImageBuffer);
						$imageResource->resize($imageResource->getSize()->widen($width));
						$buffer = $imageResource->get('jpeg', array('quality' => $size->quality));
						K2FileSystem::writeImageFile($savepath.'/cache/'.$filename, $buffer);
					}

					JFolder::delete(JPATH_SITE.'/tmp/'.$tempImageId);
				}
			}
			// Category and user image
			else
			{
				// Copy the temporary image to the filesystem
				if (JFile::exists(JPATH_SITE.'/tmp/'.$tempImageId.'.jpg'))
				{
					$buffer = file_get_contents(JPATH_SITE.'/tmp/'.$tempImageId.'.jpg');
					K2FileSystem::writeImageFile($savepath.'/'.$currentImageId.'.jpg', $buffer);
					JFile::delete(JPATH_SITE.'/tmp/'.$tempImageId.'.jpg');
				}
			}

		}
	}

	public static function remove($type, $id)
	{
		// File system
		$filesystem = K2FileSystem::getInstance();

		// Save path
		$savepath = self::$paths[$type];

		// Image id
		$imageId = md5('Image'.$id);

		if ($type == 'item')
		{
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
		else
		{
			$key = $savepath.'/'.$imageId.'.jpg';
			if ($filesystem->has($key))
			{
				$filesystem->delete($key);
			}
		}

	}

	public static function purge($type)
	{
		// Application
		$application = JFactory::getApplication();

		// Session
		$session = JFactory::getSession();

		// Get image
		$image = $session->get('k2.'.$type.'.image');

		// Temporary folders
		if ($image)
		{
			if (JFile::exists(JPATH_SITE.'/tmp/'.$image.'.jpg'))
			{
				JFile::delete(JPATH_SITE.'/tmp/'.$image.'.jpg');
			}

			if (JFolder::exists(JPATH_SITE.'/tmp/'.$image))
			{
				JFolder::delete(JPATH_SITE.'/tmp/'.$image);
			}
		}

		$session->set('k2.'.$type.'.image', null);
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
				self::$placeholders[$type] = 'templates/'.$template.'/images/placeholder/'.$type.'.png';
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
				$image = new K2Image;
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
			$image = new K2Image;
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
			$image->width = null;
			$images['src'] = $image;

			// Preview image when editing items
			$adminPreviewImageSize = $params->get('adminPreviewImageSize');
			if ($adminPreviewImageSize && isset($images[$adminPreviewImageSize]))
			{
				$images['admin'] = $images[$adminPreviewImageSize];
			}
			else
			{
				$images['admin'] = $images['src'];
			}

			// Modal image
			$modalImageSize = $params->get('modalImageSize');
			if ($modalImageSize && isset($images[$modalImageSize]))
			{
				$images['modal'] = $images[$modalImageSize];
			}
			else
			{
				$images['modal'] = $images['src'];
			}

		}
		// Return
		return $images;
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
			$image = new K2Image;
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
			$image = new K2Image;
			$image->src = JURI::root(true).'/'.$placeholder;
			$image->url = JURI::root(false).$placeholder;
			$image->alt = $category->title;
			$image->caption = '';
			$image->credits = '';
			$image->flag = 0;
		}
		return $image;
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
			$image = new K2Image;
			$image->id = md5('Image'.$user->id);
			$image->src = K2Filesystem::getURIRoot(true).$savepath.'/'.$image->id.'.jpg';
			$image->url = K2Filesystem::getURIRoot(false).$savepath.'/'.$image->id.'.jpg';
			$image->alt = $user->name;
			$image->flag = 1;
		}
		else if ($params->get('userImageDefault'))
		{
			$placeholder = self::getPlaceholder('user');
			$image = new K2Image;
			$image->src = JURI::root(true).'/'.$placeholder;
			$image->url = JURI::root(false).$placeholder;
			$image->alt = $user->name;
			$image->flag = 0;
		}
		if ($params->get('gravatar') && (is_null($image) || !$image->flag))
		{
			if (is_null($image))
			{
				$image = new K2Image;
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

}
