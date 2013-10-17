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

		$input = JFactory::getApplication()->input;
		$id = $input->get('id', uniqid(), 'int');
		$imageFile = $input->files->get('imageFile');
		$source = $imageFile['tmp_name'];
		$filename = $imageFile['name'];
		$image = $processor->open($source);
		$filesystem->write('media/k2/categories/'.$filename, $image->__toString(), true);
		$response = new stdClass;
		$response->value = $id;
		$response->preview = JURI::root(true).'/media/k2/categories/'.$filename.'?t='.time();
		echo json_encode($response);
		return $this;

	}

}
