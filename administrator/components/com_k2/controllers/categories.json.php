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

		// If the category exists update now the database field
		if ($id)
		{
			$model = K2Model::getInstance('Categories', 'K2Model');
			$model->setState('id', $id);
			$data = array(
				'id' => $id,
				'image' => $filename
			);
			$model->setState('data', $data);
			$model->save();
		}

		// Prepare the response
		$response = new stdClass;
		$response->value = ($id) ? '' : $filename;
		$response->preview = JURI::root(true).'/'.$path.'/'.$filename.'?t='.time();
		echo json_encode($response);
		return $this;

	}

}
