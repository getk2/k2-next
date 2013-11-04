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
jimport('joomla.filesystem.file');

/**
 * Media JSON controller.
 */

class K2ControllerMedia extends K2Controller
{

	public function upload()
	{
		// Check for token
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		// Filesystem
		require_once JPATH_ADMINISTRATOR.'/components/com_k2/classes/filesystem.php';
		$filesystem = K2FileSystem::getInstance();

		// Get input
		$input = JFactory::getApplication()->input;
		$itemId = $input->get('itemId', '', 'cmd');
		$upload = $input->get('upload', '', 'cmd');
		$folder = $itemId;
		$file = $input->files->get('file');

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
		$input = JFactory::getApplication()->input;
		$itemId = $input->get('itemId', '', 'cmd');
		$upload = $input->get('upload', '', 'cmd');
		$folder = $itemId;

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

		// Return
		echo json_encode(true);
		return $this;
	}

	public function connector()
	{
		$application = JFactory::getApplication();
		$params = JComponentHelper::getParams('com_media');
		$root = $params->get('file_path', 'media');
		$folder = JRequest::getVar('folder', $root, 'default', 'path');
		$type = JRequest::getCmd('type', 'video');
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
		JRequest::setVar('debug', false);
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
