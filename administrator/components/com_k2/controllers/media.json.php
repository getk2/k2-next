<?php
/**
 * @version		3.0.0b
 * @package		K2
 * @author		JoomlaWorks http://www.joomlaworks.net
 * @copyright	Copyright (c) 2006 - 2014 JoomlaWorks Ltd. All rights reserved.
 * @license		GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 */

// no direct access
defined('_JEXEC') or die ;

require_once JPATH_ADMINISTRATOR.'/components/com_k2/controller.php';
require_once JPATH_ADMINISTRATOR.'/components/com_k2/classes/filesystem.php';
require_once JPATH_ADMINISTRATOR.'/components/com_k2/resources/items.php';
require_once JPATH_ADMINISTRATOR.'/components/com_k2/helpers/media.php';

jimport('joomla.filesystem.file');

/**
 * Media JSON controller.
 */

class K2ControllerMedia extends K2Controller
{

	/**
	 * onBeforeRead function.
	 * Hook for chidlren controllers to check for access
	 *
	 * @param string $mode		The mode of the read function. Pass 'row' for retrieving a single row or 'list' to retrieve a collection of rows.
	 * @param mixed $id			The id of the row to load when we are retrieving a single row.
	 *
	 * @return void
	 */
	protected function onBeforeRead($mode, $id)
	{
		$user = JFactory::getUser();
		return !$user->guest;
	}

	public function upload()
	{
		// Check for token
		JSession::checkToken() or K2Response::throwError(JText::_('JINVALID_TOKEN'));

		// Get user
		$user = JFactory::getUser();

		// Permissions check
		if (!$user->authorise('k2.item.create', 'com_k2') && !$user->authorise('k2.item.edit', 'com_k2') && !$user->authorise('k2.item.edit.own', 'com_k2'))
		{
			K2Response::throwError(JText::_('K2_YOU_ARE_NOT_AUTHORIZED_TO_PERFORM_THIS_OPERATION'), 403);
		}

		// Get input
		$upload = $this->input->get('upload', '', 'cmd');
		$url = $this->input->get('url', '', 'string');
		$file = $this->input->files->get('file');

		// Upload media using helper
		$media = K2HelperMedia::add($file, $url, $upload);

		echo json_encode($media);

		// Return
		return $this;

	}

	public function connector()
	{
		$application = JFactory::getApplication();
		$user = JFactory::getUser();
		if ($user->guest)
		{
			K2Response::throwError(JText::_('K2_YOU_ARE_NOT_AUTHORIZED_TO_PERFORM_THIS_OPERATION'), 403);
		}
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
		include_once JPATH_COMPONENT_ADMINISTRATOR.'/js/vendor/elfinder/php/elFinderConnector.class.php';
		include_once JPATH_COMPONENT_ADMINISTRATOR.'/js/vendor/elfinder/php/elFinder.class.php';
		include_once JPATH_COMPONENT_ADMINISTRATOR.'/js/vendor/elfinder/php/elFinderVolumeDriver.class.php';
		include_once JPATH_COMPONENT_ADMINISTRATOR.'/js/vendor/elfinder/php/elFinderVolumeLocalFileSystem.class.php';
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
			$permissions = array('read' => true, 'write' => true);
		}
		else
		{
			$permissions = array('read' => true, 'write' => false);
		}
		$options = array('roots' => array( array('driver' => 'LocalFileSystem', 'path' => $path, 'URL' => $url, 'accessControl' => 'access', 'defaults' => $permissions)));
		$connector = new elFinderConnector(new elFinder($options));
		$connector->run();
		return $this;
	}

}
