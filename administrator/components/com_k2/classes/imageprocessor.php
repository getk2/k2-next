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
		// If adapter is null choose the optimal
		if (is_null($adapter))
		{
			if (class_exists('Gmagick'))
			{
				$adapter = 'Gmagick';
			}
			else if (class_exists('Imagick'))
			{
				$adapter = 'Imagick';
			}
			else if (function_exists('gd_info'))
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
			$params = JComponentHelper::getParams('com_k2');
			if ($memoryLimit = (int)$params->get('imageMemoryLimit'))
			{
				ini_set('memory_limit', $memoryLimit.'M');
			}
		}

		return self::$instances[$adapter];
	}

}
