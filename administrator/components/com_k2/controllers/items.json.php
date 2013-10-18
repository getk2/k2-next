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
 * Items JSON controller.
 */

class K2ControllerItems extends K2Controller
{
	public function addImage()
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
		$id = $input->get('id', 0, 'int');
		$tmpId = $input->get('tmpId', '', 'cmd');
		$imageFile = $input->files->get('imageFile');
		$path = 'media/k2/items';
		$source = $imageFile['tmp_name'];

		$image = $processor->open($source);
		$baseFileName = ($id) ? md5('Image'.$id) : $tmpId;

		$filesystem->write($path.'/src/'.$baseFileName.'.jpg', $image->__toString(), true);
		foreach ($sizes as $size => $width)
		{
			$filename = $baseFileName.'_'.$size.'.jpg';
			$image->resize($image->getSize()->widen($width));
			$filesystem->write($path.'/cache/'.$filename, $image->__toString(), true);
		}

		// Update the database if needed
		if ($id)
		{
			$row = JTable::getInstance('Items', 'K2Table');
			$row->load($id);
			$row->image_flag = 1;
			$row->store();
		}

		// Response
		$response = new stdClass;
		$response->preview = JURI::root(true).'/'.$path.'/cache/'.$baseFileName.'_S.jpg?t='.time();
		echo json_encode($response);
		return $this;

	}

	public function removeImage()
	{
		// Check for token
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		// Filesystem
		require_once JPATH_ADMINISTRATOR.'/components/com_k2/classes/filesystem.php';
		$filesystem = K2FileSystem::getInstance();

		// Get input
		$input = JFactory::getApplication()->input;
		$id = $input->get('id', 0, 'int');
		$tmpId = $input->get('tmpId', '', 'cmd');
		$image = $input->get('image', '', 'cmd');
		$baseFileName = ($id) ? md5('Image'.$id) : $tmpId;

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
		if ($id)
		{
			$row = JTable::getInstance('Items', 'K2Table');
			$row->load($id);
			$row->image_flag = 0;
			$row->store();
		}

		// Response
		echo json_encode(true);

		// Return
		return $this;
	}

}
