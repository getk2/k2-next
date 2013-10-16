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
		$this->prepare();
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
			$data = call_user_func(array(
				$this,
				$method
			));
			$this->$name = $data;
			return $this->$name;
		}
		else
		{
			$application = JFactory::getApplication();
			$application->enqueueMessage('Invalid property', 'error');
			return false;
		}
	}

	/**
	 * Prepares the row for output
	 *
	 * @param string $mode	The mode for preparing data. 'site' for fron-end data, 'admin' for administrator operations.
	 *
	 * @return void
	 */
	public function prepare($mode = null)
	{

		if (is_null($mode))
		{
			$mode = (JFactory::getApplication()->isSite()) ? 'site' : 'admin';
		}

		if (property_exists($this, 'created'))
		{
			$this->createdDate = JHtml::_('date', $this->created, 'Y-m-d');
			$this->createdTime = JHtml::_('date', $this->created, 'H:i');
			$this->createdOn = JHtml::_('date', $this->created, JText::_('K2_DATE_FORMAT'));
		}

		if (property_exists($this, 'modified'))
		{
			if ((int)$this->modified > 0)
			{
				$this->modifiedOn = JHtml::_('date', $this->modified, JText::_('K2_DATE_FORMAT'));
			}
			else
			{
				$this->modifiedOn = JText::_('K2_NEVER');
			}
		}

		if (property_exists($this, 'publish_up'))
		{
			$this->publishUpDate = JHtml::_('date', $this->publish_up, 'Y-m-d');
			$this->publishUpTime = JHtml::_('date', $this->publish_up, 'H:i');
		}

		if (property_exists($this, 'publish_down'))
		{
			if ((int)$this->publish_down > 0)
			{
				$this->publishDownDate = JHtml::_('date', $this->publish_down, 'Y-m-d');
				$this->publishDownTime = JHtml::_('date', $this->publish_down, 'H:i');
			}
			else
			{
				$this->publishDownDate = '';
				$this->publishDownTime = '';
			}
		}

		if (property_exists($this, 'language') && property_exists($this, 'languageTitle') && empty($this->languageTitle))
		{

			$this->languageTitle = JText::_('K2_ALL');
		}

	}

}
