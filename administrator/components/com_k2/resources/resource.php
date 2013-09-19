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

/**
 * K2 base resource class. All K2 resources inherit this class.
 */

class K2Resource
{

	/**
	 * Constructor.
	 * It assigns the data to object properties.
	 *
	 * @param object $data
	 *
	 * @return void
	 */

	public function __construct($data)
	{
		foreach ($data as $key => $value)
		{
			$this->$key = $value;
		}
	}

	/**
	 * Magic function.
	 * Allows on demand loading of resource properties.
	 *
	 * @param string $name	The name of the requested property.
	 *
	 * @return boolean	True if the requested propery is loaded. False if the requested property is invalid.
	 */
	public function __get($name)
	{
		$method = 'get'.ucfirst($name);
		if (method_exists($this, $method))
		{
			call_user_func(array(
				$this,
				$method
			));
			return $this->$name;
		}
		else
		{
			$application = JFactory::getApplication();
			$application->enqueueMessage('Invalid property', 'error');
			return false;
		}
	}

}
