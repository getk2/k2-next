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
			throw new Exception(JText::_('K2_NOT_FOUND'), 404);
		}

		// Check hash
		if (JApplication::getHash($id) != $hash)
		{
			throw new Exception(JText::_('K2_NOT_FOUND'), 404);
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

		// Params
		$params = JComponentHelper::getParams('com_k2');

		// Custom path flag
		$customPathFlag = $params->get('attachmentsFolder') && $params->get('filesystem') == 'Local' ? true : false;

		// File system
		$filesystem = $customPathFlag ? K2FileSystem::getInstance('Local', $params->get('attachmentsFolder')) : K2FileSystem::getInstance();

		// Path
		$path = $customPathFlag ? '' : 'media/k2/attachments';

		// Determine the key
		if ($attachment->file)
		{
			$key = $path.'/'.$attachment->itemId.'/'.$attachment->file;
		}
		else if ($attachment->path)
		{
			$key = $attachment->path;
			// Since it is a path we need to enforce the local adapter for the file system
			$filesystem = K2FileSystem::getInstance('Local');
		}

		// Check if file exists
		if (!$filesystem->has($key))
		{
			throw new Exception(JText::_('K2_NOT_FOUND'), 404);
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
		$application->clearHeaders();
		$application->setHeader('Pragma', 'public', true);
		$application->setHeader('Expires', '0', true);
		$application->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true);
		$application->setHeader('Content-Type', $mime, true);
		$application->setHeader('Content-Disposition', 'attachment; filename='.$filename.';', true);
		$application->setHeader('Content-Transfer-Encoding', 'binary', true);
		$application->setHeader('Content-Length', $size, true);
		$application->sendHeaders();
		echo $content;
		$application->close();
	}

}
