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

/**
 * K2 base resource class. All K2 resources inherit this class.
 */

class K2Resource
{

	/**
	 * @var array	Items instances container.
	 */
	protected static $instances = array();

	/**
	 * @var array	Languages instances container.
	 */
	protected static $languages = array();

	/**
	 * @var array	View levels instances container.
	 */
	protected static $viewlevels = array();

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
	 * Gets the row from cache.
	 *
	 * @param array $data
	 *
	 * @return K2Resource
	 */
	public static function get($data)
	{
		$class = get_called_class();
		if (is_object($data))
		{
			$data = get_object_vars($data);
		}
		if ($data['id'] && isset(self::$instances[$class][$data['id']]))
		{
			$row = self::$instances[$class][$data['id']];
		}
		else
		{
			$row = new $class($data);
			self::$instances[$class][$data['id']] = $row;
		}
		return $row;
	}

	/**
	 * Magic function.
	 * Allows on demand loading of resource properties.
	 *
	 * @param string $name	The name of the requested property.
	 *
	 * @return mixed 	The property if it is available. Null if the requested property is invalid.
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
			trigger_error('Undefined property: '.get_called_class().': '.$name);
			return null;
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

		if (property_exists($this, 'access'))
		{
			if (empty(self::$viewlevels))
			{
				self::getViewLevels();
			}
			$this->viewLevel = self::$viewlevels[$this->access];
		}

		if (property_exists($this, 'created'))
		{
			$this->createdDate = JHtml::_('date', $this->created, 'Y-m-d');
			$this->createdTime = JHtml::_('date', $this->created, 'H:i');
			$this->createdOn = JHtml::_('date', $this->created, JText::_('K2_DATE_FORMAT'));
		}

		if (property_exists($this, 'modified'))
		{
			if ((int)$this->modified_by > 0)
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

		if (property_exists($this, 'metadata'))
		{
			$this->metadata = new JRegistry($this->metadata);
		}

		if (property_exists($this, 'params'))
		{
			$this->params = new JRegistry($this->params);
		}

		if (property_exists($this, 'plugins'))
		{
			$this->plugins = new JRegistry($this->plugins);
		}

		if (property_exists($this, 'language'))
		{
			if ($this->language == '*' || $this->language == '')
			{
				$this->languageTitle = JText::_('K2_ALL');
			}
			else
			{
				if (empty(self::$languages))
				{
					$languages = JLanguageHelper::getLanguages();
					foreach ($languages as $language)
					{
						self::$languages[$language->lang_code] = $language->title;
					}
				}
				$this->languageTitle = self::$languages[$this->language];
			}
		}

		// Checked out
		if (property_exists($this, 'checked_out') && defined('K2_EDIT_MODE') && K2_EDIT_MODE)
		{
			$this->isLocked = false;
			$user = JFactory::getUser();
			$this->canUnlock = true;
			if ($this->checked_out && $this->checked_out != $user->id)
			{
				$this->isLocked = true;
				$this->lockedBy = JUser::getInstance($this->checked_out)->name;
				$this->lockedAt = JHtml::_('date', $this->checked_out_time, 'Y-m-d H:i');
				$this->canUnlock = $user->authorise('core.manage', 'com_checkin');
			}
		}

	}

	private static function getViewLevels()
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select($db->quoteName('id'));
		$query->select($db->quoteName('title'));
		$query->from($db->quoteName('#__viewlevels'));
		$db->setQuery($query);
		$levels = $db->loadObjectList();
		foreach ($levels as $level)
		{
			self::$viewlevels[$level->id] = $level->title;
		}
	}

}
