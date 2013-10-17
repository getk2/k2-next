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
	public function image()
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
		$id = $input->get('id', uniqid(), 'int');
		$imageFile = $input->files->get('image_file');
		$source = $imageFile['tmp_name'];

		$image = $processor->open($source);
		$baseFileName = md5('Image'.$id);

		$filesystem->write('media/k2/items/src/'.$baseFileName.'.jpg', $image->__toString(), true);
		foreach ($sizes as $size => $width)
		{
			$filename = $baseFileName.'_'.$size.'.jpg';
			$image->resize($image->getSize()->widen($width));
			$filesystem->write('media/k2/items/cache/'.$filename, $image->__toString(), true);
		}
		$response = new stdClass;
		$response->value = $id;
		$response->preview = JURI::root(true).'/media/k2/items/cache/'.$baseFileName.'_S.jpg?t='.time();
		echo json_encode($response);
		return $this;

	}

}
