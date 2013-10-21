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
		$imagePath = $input->get('imagePath', '', 'string');
		$imagePath = str_replace(JURI::root(true).'/', '', $imagePath);
		$path = 'media/k2/items';

		if ($imagePath)
		{
			$buffer = $filesystem->read($imagePath);
			// Try to open the image to ensure it's a valid image file
			$image = $processor->load($buffer);

		}
		else
		{
			$source = $imageFile['tmp_name'];
			// Try to open the image to ensure it's a valid image file
			$image = $processor->open($source);
		}

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

	public function addAttachment()
	{
		// Check for token
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		// Filesystem
		require_once JPATH_ADMINISTRATOR.'/components/com_k2/classes/filesystem.php';
		$filesystem = K2FileSystem::getInstance();

		// Get file from input
		$input = JFactory::getApplication()->input;
		$id = $input->get('id', 0, 'int');
		$itemId = $input->get('itemId', 0, 'int');
		$attachments = $input->files->get('attachments');
		$file = $attachments['file'][0];
		$name = $input->get('name', '', 'string');
		$title = $input->get('title', '', 'string');
		$attachmentPath = $input->get('attachmentPath', '', 'string');
		$attachmentPath = str_replace(JURI::root(true).'/', '', $attachmentPath);
		$path = 'media/k2/attachments';

		// Setup some variables depending on source
		if ($attachmentPath)
		{
			$targetFilename = basename($attachmentPath);
			$buffer = $filesystem->read($attachmentPath);
		}
		else
		{
			$targetFilename = $file['name'];
			$buffer = file_get_contents($file['tmp_name']);
		}

		// Handle empty fields
		if (trim($name) == '')
		{
			$name = $targetFilename;
		}
		if (trim($title) == '')
		{
			$title = $targetFilename;
		}

		// Get model
		K2Model::addIncludePath(JPATH_ADMINISTRATOR.'/components/com_k2/models');
		$model = K2Model::getInstance('Attachments', 'K2Model');

		// Save the attachment
		$filesystem->write($path.'/'.$targetFilename, $buffer, true);
		$data = array(
			'id' => $id,
			'itemId' => $itemId,
			'file' => $targetFilename,
			'name' => $name,
			'title' => $title
		);
		$model->setState('data', $data);
		$model->save();

		// Response
		$attachment = $model->getRow();
		echo json_encode($attachment);

		// Return
		return $this;
	}

	public function removeAttachment()
	{
		// Check for token
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		// Get id from input
		$input = JFactory::getApplication()->input;
		$attachments = $input->getArray(array('attachments' => array('id' => 'array')));
		$id = $input->get('id', 0, 'int');
		$rows = array($id);
		foreach ($attachments['attachments']['id'] as $attachmentId)
		{
			$rows[] = (int)$attachmentId;
		}
		$rows = array_unique($rows);
		$rows = array_filter($rows);

		if (count($rows))
		{
			// Load resource class
			require_once JPATH_ADMINISTRATOR.'/components/com_k2/resources/attachments.php';
			foreach ($rows as $id)
			{
				// Get attachment
				$attachment = K2Attachments::getInstance($id);

				// Delete
				$attachment->delete();
			}
		}

		// Return
		echo json_encode(true);
		return $this;
	}

}
