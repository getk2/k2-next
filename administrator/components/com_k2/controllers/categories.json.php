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
 * Categories JSON controller.
 */

class K2ControllerCategories extends K2Controller
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

		// Get input
		$input = JFactory::getApplication()->input;
		$id = $input->get('id', 0, 'int');
		$tmpId = $input->get('tmpId', '', 'cmd');
		$imageFile = $input->files->get('imageFile');

		// Set some variables
		$source = $imageFile['tmp_name'];
		$filename = ($id) ? $id.'.jpg' : $tmpId.'.jpg';
		$path = 'media/k2/categories';

		// Try to open the image to ensure it's a valid image file
		$image = $processor->open($source);

		// Write it to the filesystem
		$filesystem->write($path.'/'.$filename, $image->__toString(), true);

		// Update the database if needed
		if ($id)
		{
			$row = JTable::getInstance('Categories', 'K2Table');
			$row->load($id);
			$row->image = $filename;
			$row->store();
		}

		// Prepare the response
		$response = new stdClass;
		$response->value = $filename;
		$response->preview = JURI::root(true).'/'.$path.'/'.$filename.'?t='.time();
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

		// Compute the key
		$key = 'media/k2/categories/'.$image;

		// Remove the file
		if ($filesystem->has($key))
		{
			$filesystem->delete($key);
		}

		// Update the database if needed
		if ($id)
		{
			$row = JTable::getInstance('Categories', 'K2Table');
			$row->load($id);
			$row->image = '';
			$row->store();
		}
		
		// Response
		echo json_encode(true);

		// Return
		return $this;
	}

}
