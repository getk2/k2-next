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

require_once JPATH_ADMINISTRATOR.'/components/com_k2/controller.php';

/**
 * Image JSON controller.
 */

class K2ControllerImage extends K2Controller
{

	public function upload()
	{
		$types = array(
			'item',
			'category'
		);
		$input = JFactory::getApplication()->input;
		$type = $input->get('type', '', 'cmd');
		if (!in_array($type, $types))
		{
			jexit(JText::_('K2_INVALID_TYPE'));
		}
		elseif ($type == 'item')
		{
			$this->_uploadItemImage();
		}
		elseif ($type == 'category')
		{
			$this->_uploadCategoryImage();
		}

		return $this;
	}

	/**
	 * Delete function.
	 * Deletes a resource.
	 * Usually there will be no need to override this function.
	 *
	 * @return void
	 */
	protected function delete()
	{
		$types = array(
			'item',
			'category'
		);
		$input = JFactory::getApplication()->input;
		$type = $input->get('type', '', 'cmd');
		if (!in_array($type, $types))
		{
			jexit(JText::_('K2_INVALID_TYPE'));
		}
		elseif ($type == 'item')
		{
			$this->_removeItemImage();
		}
		elseif ($type == 'category')
		{
			$this->_removeCategoryImage();
		}
		return $this;
	}

	private function _uploadItemImage()
	{
		// Check for token
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		// Filesystem
		require_once JPATH_ADMINISTRATOR.'/components/com_k2/classes/filesystem.php';
		$filesystem = K2FileSystem::getInstance();

		// ImageProcessor
		require_once JPATH_ADMINISTRATOR.'/components/com_k2/classes/imageprocessor.php';
		$processor = K2ImageProcessor::getInstance();

		$sizes = array(
			'XL' => 600,
			'L' => 400,
			'M' => 240,
			'S' => 180,
			'XS' => 100
		);

		$input = JFactory::getApplication()->input;
		$itemId = $input->get('itemId', '', 'cmd');
		$file = $input->files->get('file');
		$path = $input->get('path', '', 'string');
		$path = str_replace(JURI::root(true).'/', '', $path);
		$savepath = 'media/k2/items';

		if ($path)
		{
			$buffer = $filesystem->read($path);
			$image = $processor->load($buffer);

		}
		else
		{
			$source = $file['tmp_name'];
			$image = $processor->open($source);
		}

		$baseFileName = md5('Image'.$itemId);

		$filesystem->write($savepath.'/src/'.$baseFileName.'.jpg', $image->__toString(), true);
		foreach ($sizes as $size => $width)
		{
			$filename = $baseFileName.'_'.$size.'.jpg';
			$image->resize($image->getSize()->widen($width));
			$filesystem->write($savepath.'/cache/'.$filename, $image->__toString(), true);
		}

		// Update the database if needed
		if (is_numeric($itemId))
		{
			JTable::addIncludePath(JPATH_ADMINISTRATOR.'/components/com_k2/tables');
			$row = JTable::getInstance('Items', 'K2Table');
			$row->load($itemId);
			$image = json_decode($row->image);
			$image->flag = 1;
			$row->image = json_encode($image);
			$row->store();
		}

		// Response
		$response = new stdClass;
		$response->upload = $baseFileName;
		$response->preview = JURI::root(true).'/'.$savepath.'/cache/'.$baseFileName.'_S.jpg?t='.time();
		echo json_encode($response);

	}

	private function _removeItemImage()
	{
		// Check for token
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		// Filesystem
		require_once JPATH_ADMINISTRATOR.'/components/com_k2/classes/filesystem.php';
		$filesystem = K2FileSystem::getInstance();

		// Get input
		$input = JFactory::getApplication()->input;
		$itemId = $input->get('itemId', '', 'cmd');
		$baseFileName = md5('Image'.$itemId);

		// Delete source image
		$key = 'media/k2/items/src/'.$baseFileName.'.jpg';
		if ($filesystem->has($key))
		{
			$filesystem->delete($key);
		}

		// Get sizes. @TODO Fetch from params
		$sizes = array(
			'XL' => 600,
			'L' => 400,
			'M' => 240,
			'S' => 180,
			'XS' => 100
		);

		foreach ($sizes as $size => $width)
		{
			// Delete source image
			$key = 'media/k2/items/cache/'.$baseFileName.'_'.$size.'.jpg';
			if ($filesystem->has($key))
			{
				$filesystem->delete($key);
			}
		}

		// Update the database if needed
		if (is_numeric($itemId))
		{
			JTable::addIncludePath(JPATH_ADMINISTRATOR.'/components/com_k2/tables');
			$row = JTable::getInstance('Items', 'K2Table');
			$row->load($itemId);
			$image = json_decode($row->image);
			$image->flag = 0;
			$row->image = json_encode($image);
			$row->store();
		}

		// Response
		echo json_encode(true);

	}

	private function _uploadCategoryImage()
	{
		// Check for token
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		// Filesystem
		require_once JPATH_ADMINISTRATOR.'/components/com_k2/classes/filesystem.php';
		$filesystem = K2FileSystem::getInstance();

		// ImageProcessor
		require_once JPATH_ADMINISTRATOR.'/components/com_k2/classes/imageprocessor.php';
		$processor = K2ImageProcessor::getInstance();

		// Get input
		$input = JFactory::getApplication()->input;
		$itemId = $input->get('itemId', '', 'cmd');
		$imageFile = $input->files->get('file');
		$imagePath = $input->get('path', '', 'string');
		$imagePath = str_replace(JURI::root(true).'/', '', $imagePath);

		// Set some variables
		$filename = md5('Image'.$itemId).'.jpg';
		$path = 'media/k2/categories';

		if ($imagePath)
		{
			$buffer = $filesystem->read($imagePath);
			$image = $processor->load($buffer);

		}
		else
		{
			$source = $imageFile['tmp_name'];
			$image = $processor->open($source);
		}

		// Write it to the filesystem
		$filesystem->write($path.'/'.$filename, $image->__toString(), true);

		// Update the database if needed
		if (is_numeric($itemId))
		{
			JTable::addIncludePath(JPATH_ADMINISTRATOR.'/components/com_k2/tables');
			$row = JTable::getInstance('Categories', 'K2Table');
			$row->load($itemId);
			$image = json_decode($row->image);
			$image->flag = 1;
			$row->image = json_encode($image);
			$row->store();
		}

		$response = new stdClass;
		$response->upload = $filename;
		$response->preview = JURI::root(true).'/'.$path.'/'.$filename.'?t='.time();
		echo json_encode($response);

	}

	private function _removeCategoryImage()
	{
		// Check for token
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		// Filesystem
		require_once JPATH_ADMINISTRATOR.'/components/com_k2/classes/filesystem.php';
		$filesystem = K2FileSystem::getInstance();

		// Get input
		$input = JFactory::getApplication()->input;
		$itemId = $input->get('itemId', '', 'cmd');
		$image = $input->get('upload', '', 'cmd');

		// Compute the key
		$key = 'media/k2/categories/'.$image;

		// Remove the file
		if ($filesystem->has($key))
		{
			$filesystem->delete($key);
		}

		// Update the database if needed
		if (is_numeric($itemId))
		{
			JTable::addIncludePath(JPATH_ADMINISTRATOR.'/components/com_k2/tables');
			$row = JTable::getInstance('Categories', 'K2Table');
			$row->load($itemId);
			$image = json_decode($row->image);
			$image->flag = 0;
			$row->image = json_encode($image);
			$row->store();
		}

		// Response
		echo json_encode(true);

	}

}
