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

require_once JPATH_ADMINISTRATOR.'/components/com_k2/models/model.php';

class K2ModelUsers extends K2Model
{
	public function getRows()
	{
		// Get database
		$db = $this->getDBO();

		// Get query
		$query = $db->getQuery(true);

		// Select rows
		$query->select($db->quoteName('user').'.*')->from($db->quoteName('#__users', 'user'));

		// Set query conditions
		$this->setQueryConditions($query);

		// Set query sorting
		$this->setQuerySorting($query);

		// Hook for plugins
		$this->onBeforeSetQuery($query, 'com_k2.users.list');

		// Set the query
		$db->setQuery($query, (int)$this->getState('limitstart'), (int)$this->getState('limit'));

		// Get rows
		$data = $db->loadAssocList();

		// Generate K2 resources instances from the result data.
		$rows = $this->getResources($data);

		// Return rows
		return (array)$rows;
	}

	public function countRows()
	{
		// Get database
		$db = $this->getDBO();

		// Get query
		$query = $db->getQuery(true);

		// Select statement
		$query->select('COUNT(*)')->from($db->quoteName('#__users', 'user'));

		// Set query conditions
		$this->setQueryConditions($query);

		// Hook for plugins
		$this->setQueryConditions($query, 'com_k2.users.count');

		// Set the query
		$db->setQuery($query);

		// Get the result
		$total = $db->loadResult();

		// Return the result
		return (int)$total;
	}

	private function setQueryConditions(&$query)
	{
		$db = $this->getDBO();
		if ($this->getState('id'))
		{
			$id = $this->getState('id');
			if (is_array($id))
			{
				JArrayHelper::toInteger($id);
				$query->where($db->quoteName('user.id').' IN '.$id);
			}
			else
			{
				$query->where($db->quoteName('user.id').' = '.(int)$id);
			}
		}
		if ($this->getState('email'))
		{
			$query->where($db->quoteName('user.email').' = '.$db->quote($this->getState('email')));
		}
		if ($this->getState('search'))
		{
			$search = JString::trim($this->getState('search'));
			$search = JString::strtolower($search);
			if ($search)
			{
				$search = $db->escape($search, true);
				$query->where('( LOWER('.$db->quoteName('user.name').') LIKE '.$db->Quote('%'.$search.'%', false).' 
				OR '.$db->quoteName('user.id').' = '.(int)$search.'
				OR LOWER('.$db->quoteName('user.username').') LIKE '.$db->Quote('%'.$search.'%', false).'
				OR LOWER('.$db->quoteName('user.email').') LIKE '.$db->Quote('%'.$search.'%', false).')');
			}
		}
	}

	private function setQuerySorting(&$query)
	{
		$sorting = $this->getState('sorting');
		$ordering = null;
		if ($sorting)
		{
			switch($sorting)
			{
				default :
				case 'id' :
					$ordering = 'user.id';
					$direction = 'DESC';
					break;
				case 'name' :
					$ordering = 'user.name';
					$direction = 'ASC';
					break;
			}
		}

		// Append sorting
		if ($ordering)
		{
			$db = $this->getDbo();
			$query->order($db->quoteName($ordering).' '.$direction);
		}
	}

	/**
	 * Save method.
	 *
	 * @return boolean	True on success false on failure.
	 */

	public function save()
	{
		JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR.'/components/com_users/models');
		$model = JModelLegacy::getInstance('User', 'UsersModel');
		$table = $this->getTable();
		$data = $this->getState('data');
		$this->onBeforeSave($data, $table);
		$model->save($data);
		$this->setState('id', $model->getState('user.id'));
		$this->onAfterSave($data, $table);
		return true;
	}

	/**
	 * Method to get a table object, load it if necessary.
	 *
	 * @param   string  $name     The table name. Optional.
	 * @param   string  $prefix   The class prefix. Optional.
	 * @param   array   $options  Configuration array for model. Optional.
	 *
	 * @return  JTable  A JTable object
	 */
	public function getTable($name = 'User', $prefix = 'JTable', $options = array())
	{
		return parent::getTable($name, $prefix, $options);
	}

	public function checkSpoofing($name, $email)
	{
		// Get database
		$db = $this->getDBO();

		// Get query
		$query = $db->getQuery(true);

		// Select statement
		$query->select('COUNT(*)')->from($db->quoteName('#__users'));

		// Conditions
		$query->where($db->quoteName('email').' = '.$db->quote($this->getState('email')).' OR '.$db->quoteName('name').' = '.$db->quote($this->getState('name')));

		// Set the query
		$db->setQuery($query);

		// Get the result
		$total = $db->loadResult();

		// Return the result
		return (int)$total;
	}

}
