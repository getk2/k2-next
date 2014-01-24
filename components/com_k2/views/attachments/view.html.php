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

require_once JPATH_SITE.'/components/com_k2/views/view.php';
require_once JPATH_ADMINISTRATOR.'/components/com_k2/resources/items.php';

/**
 * K2 attachment view class
 */

class K2ViewAttachments extends K2View
{
	public function display($tpl = null)
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

		// Remote path file
		if ($attachment->path && (strpos($attachment->path, 'http:') === 0 || strpos($attachment->path, 'https:') === 0))
		{
			// Set the required variables
			$content = JFile::read($attachment->path);
			$filename = basename($attachment->path);

			// We need to write a temporary file. There is no other way to get it's size
			$filesystem = K2FileSystem::getInstance('Local');
			$filesystem->write($application->getCfg('tmp_path').'/'.$filename, $content, true);
			$file = $filesystem->get($application->getCfg('tmp_path').'/'.$filename);
			$size = $file->getSize();
			$filesystem->delete($application->getCfg('tmp_path').'/'.$filename);
		}
		else
		{
			// File in K2 attachments folder
			if ($attachment->file)
			{
				// File system
				$filesystem = K2FileSystem::getInstance();
				$key = 'media/k2/attachments/'.$attachment->itemId.'/'.$attachment->file;
			}
			// File path in out server
			else
			{
				// File system. Enforced the Local adapter
				$filesystem = K2FileSystem::getInstance('Local');
				$key = $attachment->path;
			}

			// Since the file is in our server we can check if it exists
			if (!$filesystem->has($key))
			{
				JError::raiseError(404, JText::_('K2_NOT_FOUND'));
			}

			// Set the required variables
			$file = $filesystem->get($key);
			$size = $file->getSize();
			$content = $file->getContent();
			$filename = basename($key);
		}

		// Update downloads counter
		if ($application->isSite())
		{
			$attachment->track();
		}

		// Trigger the onK2AfterDownload event
		$dispatcher->trigger('onK2AfterDownload', array(&$attachment));

		// Read the file
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
