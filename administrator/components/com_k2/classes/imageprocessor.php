<?php
/**
 * @version		3.0.0
 * @package		K2
 * @author		JoomlaWorks http://www.joomlaworks.net
 * @copyright	Copyright (c) 2006 - 2014 JoomlaWorks Ltd. All rights reserved.
 * @license		GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 */

// no direct access
defined('_JEXEC') or die ;

// Auto load
require_once JPATH_ADMINISTRATOR.'/components/com_k2/vendor/autoload.php';

/**
 * K2 File class.
 * Uses the Gaufrette library
 */

class K2ImageProcessor
{
	protected static $instances = array();

	public static function getInstance($adapter = null)
	{
		// Get params
		$params = JComponentHelper::getParams('com_k2');

		// If adapter argument is null read it from parameters
		if (is_null($adapter))
		{
			// Read parameters
			$adapter = $params->get('imageProcessor', 'Gd');

			// Ensure we have a valid adapter
			if (!in_array($adapter, array('Gd', 'Gmagick', 'Gmagick')))
			{
				$adapter = 'Gd';
			}

			// Fall back to GD if other adapters are not available
			if (($adapter == 'Gmagick' && !class_exists('Gmagick')) || ($adapter == 'Imagick' && !class_exists('Gmagick')))
			{
				$adapter = 'Gd';
			}
		}

		if (empty(self::$instances[$adapter]))
		{
			$instanceName = 'Imagine\\'.$adapter.'\Imagine';
			$processor = new $instanceName();
			self::$instances[$adapter] = $processor;

			// Check for memory limit override in K2 settings
			if ($memoryLimit = (int)$params->get('imageMemoryLimit'))
			{
				ini_set('memory_limit', $memoryLimit.'M');
			}
		}

		return self::$instances[$adapter];
	}

}
