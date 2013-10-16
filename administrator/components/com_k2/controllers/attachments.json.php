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
 * Attachments JSON controller.
 */

class K2ControllerAttachments extends K2Controller
{

	public function upload()
	{
		// Check for token
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		// Filesystem
		require_once JPATH_ADMINISTRATOR.'/components/com_k2/classes/filesystem.php';
		$filesystem = K2FileSystem::getInstance();

		// Get file from input
		$input = JFactory::getApplication()->input;
		$attachments = $input->files->get('attachments');
		$file = $attachments['file'][0];

		// Get model
		K2Model::addIncludePath(JPATH_ADMINISTRATOR.'/components/com_k2/models');
		$model = K2Model::getInstance('Attachments', 'K2Model');

		// Save the attachment
		$path = 'media/k2/attachments/'.$file['name'];
		$filesystem->write($path, file_get_contents($file['tmp_name']), true);
		$data = array(
			'file' => $file['name'],
			'name' => $file['name'],
			'title' => $file['name']
		);
		$model->setState('data', $data);
		$model->save();

		$response = new stdClass;
		$response->id = $model->getState('id');
		$response->file = $file['name'];
		$response->path = $path;
		echo json_encode($response);

		return $this;
	}

}
