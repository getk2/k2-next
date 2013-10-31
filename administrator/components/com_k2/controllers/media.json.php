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
 * Media JSON controller.
 */

class K2ControllerMedia extends K2Controller
{
	public function connector()
	{
		$mainframe = JFactory::getApplication();
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
		$path = JPATH_SITE.DS.JPath::clean($folder);

		JPath::check($path);
		include_once JPATH_COMPONENT_ADMINISTRATOR.'/lib/elfinder/elFinderConnector.class.php';
		include_once JPATH_COMPONENT_ADMINISTRATOR.'/lib/elfinder/elFinder.class.php';
		include_once JPATH_COMPONENT_ADMINISTRATOR.'/lib/elfinder/elFinderVolumeDriver.class.php';
		include_once JPATH_COMPONENT_ADMINISTRATOR.'/lib/elfinder/elFinderVolumeLocalFileSystem.class.php';
		function access($attr, $path, $data, $volume)
		{
			$mainframe = JFactory::getApplication();
			// Hide PHP files.
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
					return ($mainframe->isSite()) ? false : true;
					break;
				case 'locked' :
					return ($mainframe->isSite()) ? true : false;
					break;
				case 'hidden' :
					return false;
					break;
			}

		}

		if ($mainframe->isAdmin())
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
		$options = array(
			'debug' => false,
			'roots' => array( array(
					'driver' => 'LocalFileSystem',
					'path' => $path,
					'URL' => $url,
					'accessControl' => 'access',
					'defaults' => $permissions
				))
		);
		$connector = new elFinderConnector(new elFinder($options));
		$connector->run();
		return $this;
	}

}
