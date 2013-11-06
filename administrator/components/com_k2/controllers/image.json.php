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

	protected $types = array(
		'item',
		'category'
	);

	public function upload()
	{
		// Check for token
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		// Filesystem
		require_once JPATH_ADMINISTRATOR.'/components/com_k2/classes/filesystem.php';
		$filesystem = K2FileSystem::getInstance();

		// ImageProcessor
		require_once JPATH_ADMINISTRATOR.'/components/com_k2/classes/imageprocessor.php';
		$processor = K2ImageProcessor::getInstance();

		$input = JFactory::getApplication()->input;
		$type = $input->get('type', '', 'cmd');
		if (!in_array($type, $this->types))
		{
			jexit(JText::_('K2_INVALID_TYPE'));
		}

		$itemId = $input->get('itemId', 0, 'int');
		$tmpId = $input->get('tmpId', '', 'cmd');
		$file = $input->files->get('file');
		$path = $input->get('path', '', 'string');
		$path = str_replace(JURI::root(true).'/', '', $path);
		$savepath = ($type == 'item') ? 'media/k2/items' : 'media/k2/categories';
		$imageKey = $itemId ? $itemId : $tmpId;
		$baseFileName = md5('Image'.$imageKey);
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

		if ($type == 'item')
		{
			// Source image
			$filesystem->write($savepath.'/src/'.$baseFileName.'.jpg', $image->__toString(), true);
			// Resized images
			$sizes = array(
				'XL' => 600,
				'L' => 400,
				'M' => 240,
				'S' => 180,
				'XS' => 100
			);
			foreach ($sizes as $size => $width)
			{
				$filename = $baseFileName.'_'.$size.'.jpg';
				$image->resize($image->getSize()->widen($width));
				$filesystem->write($savepath.'/cache/'.$filename, $image->__toString(), true);
			}
			// Get the table for item type
			JTable::addIncludePath(JPATH_ADMINISTRATOR.'/components/com_k2/tables');
			$row = JTable::getInstance('Items', 'K2Table');
			// Get preview
			$preview = JURI::root(true).'/'.$savepath.'/cache/'.$baseFileName.'_S.jpg?t='.time();
		}
		else
		{
			// Upload
			$filesystem->write($savepath.'/'.$baseFileName.'.jpg', $image->__toString(), true);
			// Get the table for category type
			JTable::addIncludePath(JPATH_ADMINISTRATOR.'/components/com_k2/tables');
			$row = JTable::getInstance('Categories', 'K2Table');
			// Get preview
			$preview = JURI::root(true).'/'.$savepath.'/'.$baseFileName.'.jpg?t='.time();
		}

		// Update the database if needed
		if ($itemId)
		{
			$row->load($itemId);
			$image = new stdClass;
			$row->image = json_encode($image);
			$row->store();
		}

		// Response
		$response = new stdClass;
		$response->id = $baseFileName;
		$response->preview = $preview;
		echo json_encode($response);
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
		// Check for token
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		// Filesystem
		require_once JPATH_ADMINISTRATOR.'/components/com_k2/classes/filesystem.php';
		$filesystem = K2FileSystem::getInstance();

		// Get input
		$input = JFactory::getApplication()->input;
		$type = $input->get('type', '', 'cmd');
		if (!in_array($type, $this->types))
		{
			jexit(JText::_('K2_INVALID_TYPE'));
		}
		$id = $input->get('id', '', 'cmd');
		$itemId = $input->get('itemId', 0, 'int');
		$keys = array();
		if ($type == 'item')
		{
			// Get keys to delete
			$keys[] = 'media/k2/items/src/'.$id.'.jpg';
			$sizes = array(
				'XL' => 600,
				'L' => 400,
				'M' => 240,
				'S' => 180,
				'XS' => 100
			);
			foreach ($sizes as $size => $width)
			{
				$keys[] = 'media/k2/items/cache/'.$baseFileName.'_'.$size.'.jpg';
			}
			// Get table
			JTable::addIncludePath(JPATH_ADMINISTRATOR.'/components/com_k2/tables');
			$row = JTable::getInstance('Items', 'K2Table');
		}
		else
		{
			// Get keys to delete
			$keys[] = 'media/k2/categories/'.$id.'.jpg';
			// Get table
			JTable::addIncludePath(JPATH_ADMINISTRATOR.'/components/com_k2/tables');
			$row = JTable::getInstance('Categories', 'K2Table');
		}

		// Delete keys
		foreach ($keys as $key)
		{
			if ($filesystem->has($key))
			{
				$filesystem->delete($key);
			}
		}

		// Update the database if needed
		if ($itemId)
		{
			$row->load($itemId);
			$row->image = '';
			$row->store();
		}

		// Response
		echo json_encode(true);
	}

}
