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
require_once JPATH_ADMINISTRATOR.'/components/com_k2/models/model.php';
require_once JPATH_ADMINISTRATOR.'/components/com_k2/classes/filesystem.php';

/**
 * Attachments controller.
 */

class K2ControllerAttachments extends K2Controller
{
	public function download()
	{
		// Get application
		$application = JFactory::getApplication();

		// Get input
		$input = $application->input;
		$id = $input->get('id', 0, 'int');
		$hash = $input->get('hash', '', 'string');

		// Both input fields are required
		if (!$id || empty($hash))
		{
			JError::raiseError(404, JText::_('K2_NOT_FOUND'));
		}

		// Check hash
		if (JApplication::getHash($id) != $hash)
		{
			JError::raiseError(404, JText::_('K2_NOT_FOUND'));
		}

		// Get model
		K2Model::addIncludePath(JPATH_ADMINISTRATOR.'/components/com_k2/models');
		$model = K2Model::getInstance('Attachments', 'K2Model');

		// Get attachment
		$model->setState('id', $id);
		$attachment = $model->getRow();

		// Get item
		$model = K2Model::getInstance('Items', 'K2Model');
		$model->setState('id', $attachment->itemId);
		$item = $model->getRow();

		// If we are on front-end check access verify that user has the permission to download this attachment
		if ($application->isSite())
		{
			$item->checkSiteAccess();
		}

		// Import K2 plugins
		JPluginHelper::importPlugin('k2');
		$dispatcher = JDispatcher::getInstance();

		// Trigger onK2BeforeDownload event
		$dispatcher->trigger('onK2BeforeDownload', array(&$attachment));

		// Filesystem
		$filesystem = K2FileSystem::getInstance();

		// Determine the key
		if ($attachment->file)
		{
			$key = 'media/k2/attachments/'.$attachment->itemId.'/'.$attachment->file;
		}
		else if ($attachment->url)
		{
			$key = $attachment->url;
		}

		// Check if file exists
		if (!$filesystem->has($key))
		{
			JError::raiseError(404, JText::_('K2_NOT_FOUND'));
		}

		// Update downloads counter
		if ($application->isSite())
		{
			$attachment->track();
		}

		// Trigger the onK2AfterDownload event
		$dispatcher->trigger('onK2AfterDownload', array(&$attachment));

		// Read the file
		$file = $filesystem->get($key);
		$size = $file->getSize();
		$content = $file->getContent();
		$filename = basename($key);
		$finfo = new finfo(FILEINFO_MIME);
		$mime = $finfo->buffer($content);
		ob_end_clean();
		JResponse::clearHeaders();
		JResponse::setHeader('Pragma', 'public', true);
		JResponse::setHeader('Expires', '0', true);
		JResponse::setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true);
		JResponse::setHeader('Content-Type', $mime, true);
		JResponse::setHeader('Content-Disposition', 'attachment; filename='.$filename.';', true);
		JResponse::setHeader('Content-Transfer-Encoding', 'binary', true);
		JResponse::setHeader('Content-Length', $size, true);
		JResponse::sendHeaders();
		echo $content;
		$application->close();

	}

}
