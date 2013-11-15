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
require_once JPATH_ADMINISTRATOR.'/components/com_k2/classes/filesystem.php';
require_once JPATH_ADMINISTRATOR.'/components/com_k2/resources/items.php';

jimport('joomla.filesystem.file');

/**
 * Media JSON controller.
 */

class K2ControllerMedia extends K2Controller
{

	public function upload()
	{
		// Check for token
		JSession::checkToken() or K2Response::throwError(JText::_('JINVALID_TOKEN'));

		// Filesystem
		$filesystem = K2FileSystem::getInstance();

		// Get input
		$input = JFactory::getApplication()->input;
		$itemId = $input->get('itemId', '', 'cmd');
		$upload = $input->get('upload', '', 'cmd');
		$folder = $itemId;
		$file = $input->files->get('file');

		// Permissions check
		if (is_numeric($itemId))
		{
			// Existing items check permission for specific item
			$authorised = K2Items::getInstance($itemId)->canEdit;
		}
		else
		{
			// New items. We can only check the generic create permission. We cannot check against specific category since we do not know the category of the item.
			$authorised = JFactory::getUser()->authorise('k2.item.create', 'com_k2');
		}
		if (!$authorised)
		{
			K2Response::throwError(JText::_('K2_YOU_ARE_NOT_AUTHORIZED_TO_PERFORM_THIS_OPERATION'), 403);
		}

		// Setup some variables
		$path = 'media/k2/media/'.$folder;
		$filename = $file['name'];
		$buffer = file_get_contents($file['tmp_name']);
		$target = $path.'/'.$filename;

		// If the current file is uploaded then we should remove it when we upload a new one
		if ($upload && $filesystem->has($path.'/'.$upload))
		{
			$filesystem->delete($path.'/'.$upload);
		}

		// Write it to the filesystem
		$filesystem->write($target, $buffer, true);

		// Response
		$response = new stdClass;
		$response->upload = $filename;
		$response->url = $target;
		echo json_encode($response);

		// Return
		return $this;

	}

	/**
	 * Delete function.
	 * Deletes a resource.
	 * Usually there will be no need to override this function.
	 *
	 * @return void
	 */
	protected function delete()
	{
		// Check for token
		JSession::checkToken() or K2Response::throwError(JText::_('JINVALID_TOKEN'));

		// Get id from input
		$input = $this->input;
		$itemId = $input->get('itemId', '', 'cmd');
		$upload = $input->get('upload', '', 'cmd');
		$folder = $itemId;

		// Permissions check
		if (is_numeric($itemId))
		{
			// Existing items check permission for specific item
			$authorised = K2Items::getInstance($itemId)->canEdit;
		}
		else
		{
			$authorised = true;
		}
		if (!$authorised)
		{
			K2Response::throwError(JText::_('K2_YOU_ARE_NOT_AUTHORIZED_TO_PERFORM_THIS_OPERATION'), 403);
		}

		if ($upload)
		{
			// Filesystem
			require_once JPATH_ADMINISTRATOR.'/components/com_k2/classes/filesystem.php';
			$filesystem = K2FileSystem::getInstance();

			// Key
			$key = 'media/k2/media/'.$folder.'/'.$upload;

			// Delete
			if ($filesystem->has($key))
			{
				$filesystem->delete($key);
			}
		}

		// Response
		K2Response::setResponse(true);

	}

	public function connector()
	{
		$application = JFactory::getApplication();
		$params = JComponentHelper::getParams('com_media');
		$root = $params->get('file_path', 'media');
		$folder = $this->input->get('folder', $root, 'path');
		$type = $this->input->get('type', 'video', 'cmd');
		if (JString::trim($folder) == "")
		{
			$folder = $root;
		}
		else
		{
			// Ensure that we are always below the root directory
			if (strpos($folder, $root) !== 0)
			{
				$folder = $root;
			}
		}
		// Disable debug
		$this->input->set('debug', false);
		$url = JURI::root(true).'/'.$folder;
		$path = JPATH_SITE.'/'.JPath::clean($folder);
		JPath::check($path);
		include_once JPATH_COMPONENT_ADMINISTRATOR.'/js/widgets/elfinder/php/elFinderConnector.class.php';
		include_once JPATH_COMPONENT_ADMINISTRATOR.'/js/widgets/elfinder/php/elFinder.class.php';
		include_once JPATH_COMPONENT_ADMINISTRATOR.'/js/widgets/elfinder/php/elFinderVolumeDriver.class.php';
		include_once JPATH_COMPONENT_ADMINISTRATOR.'/js/widgets/elfinder/php/elFinderVolumeLocalFileSystem.class.php';
		function access($attr, $path, $data, $volume)
		{
			$application = JFactory::getApplication();

			$ext = strtolower(JFile::getExt(basename($path)));
			if ($ext == 'php')
			{
				return true;
			}

			// Hide files and folders starting with .
			if (strpos(basename($path), '.') === 0 && $attr == 'hidden')
			{
				return true;
			}
			// Read only access for front-end. Full access for administration section.
			switch($attr)
			{
				case 'read' :
					return true;
					break;
				case 'write' :
					return ($application->isSite()) ? false : true;
					break;
				case 'locked' :
					return ($application->isSite()) ? true : false;
					break;
				case 'hidden' :
					return false;
					break;
			}

		}

		if ($application->isAdmin())
		{
			$permissions = array(
				'read' => true,
				'write' => true
			);
		}
		else
		{
			$permissions = array(
				'read' => true,
				'write' => false
			);
		}
		$options = array('roots' => array( array(
					'driver' => 'LocalFileSystem',
					'path' => $path,
					'URL' => $url,
					'accessControl' => 'access',
					'defaults' => $permissions
				)));
		$connector = new elFinderConnector(new elFinder($options));
		$connector->run();
		return $this;
	}

}
