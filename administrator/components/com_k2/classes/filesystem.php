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

class K2FileSystem
{
	
	protected static $instances = array();
	
	public static function getInstance($adapter = null)
	{
		if (is_null($adapter))
		{
			$adapter = 'Local';
		}

		if (empty(self::$instances[$adapter]))
		{
			$filesystem = new Gaufrette\Filesystem(new Gaufrette\Adapter\Local(JPATH_SITE));
			self::$instances[$adapter] = $filesystem;
		}

		return self::$instances[$adapter];
	}

}
