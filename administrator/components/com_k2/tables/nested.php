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

jimport('joomla.database.tablenested');

class K2TableNested extends JTableNested
{

	/**
	 * Method to store a row in the database from the JTable instance properties.
	 * If a primary key value is set the row with that primary key value will be
	 * updated with the instance property values.  If no primary key value is set
	 * a new row will be inserted into the database with the properties from the
	 * JTable instance.
	 *
	 * @param   boolean  $updateNulls  True to update fields even if they are null.
	 *
	 * @return  boolean  True on success.
	 *
	 * @link    http://docs.joomla.org/JTable/store
	 * @since   11.1
	 */
	public function store($updateNulls = false)
	{
		$user = JFactory::getUser();
		$date = JFactory::getDate();
		if (!$this->id && property_exists($this, 'created'))
		{
			$this->created = $date->toSql();
		}
		if (!$this->id && property_exists($this, 'created_by'))
		{
			$this->created_by = $user->get('id');
		}
		if ($this->id && property_exists($this, 'modified'))
		{
			$this->modified = $date->toSql();
		}
		if ($this->id && property_exists($this, 'modified_by'))
		{
			$this->modified_by = $user->get('id');
		}
		if (property_exists($this, 'language') && !$this->language)
		{
			$this->language = '*';
		}
		return parent::store($updateNulls);
	}

	/**
	 * Method to bind an associative array or object to the JTable instance.This
	 * method only binds properties that are publicly accessible and optionally
	 * takes an array of properties to ignore when binding.
	 *
	 * @param   mixed  $src     An associative array or object to bind to the JTable instance.
	 * @param   mixed  $ignore  An optional array or space separated list of properties to ignore while binding.
	 *
	 * @return  boolean  True on success.
	 *
	 * @link    http://docs.joomla.org/JTable/bind
	 * @since   11.1
	 * @throws  InvalidArgumentException
	 */
	public function bind($src, $ignore = array())
	{
		if (is_object($src))
		{
			$src = get_object_vars($src);
		}
		if (isset($src['params']) && is_array($src['params']))
		{
			$registry = new JRegistry;
			$registry->loadArray($src['params']);
			$src['params'] = $registry->toString();
		}
		if (isset($src['plugins']) && is_array($src['plugins']))
		{
			$registry = new JRegistry;
			$registry->loadArray($src['plugins']);
			$src['plugins'] = $registry->toString();
		}
		if (isset($src['rules']) && is_array($src['rules']))
		{
			$rules = array();
			foreach ((array) $src['rules'] as $action => $ids)
			{
				$rules[$action] = array();
				foreach ($ids as $id => $p)
				{
					if ($p !== '')
					{
						$rules[$action][$id] = ($p == '1' || $p == 'true') ? true : false;
					}
				}
			}
			$this->setRules(new JAccessRules($rules));
		}
		return parent::bind($src, $ignore);
	}

}
