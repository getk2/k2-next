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
	var $groups = null;

	public function getRows()
	{
		// Get database
		$db = $this->getDBO();

		// Get query
		$query = $db->getQuery(true);

		// Select rows
		$query->select($db->quoteName('user').'.*')->from($db->quoteName('#__users', 'user'));

		// Join over the K2 users
		$query->select($db->quoteName('profile.description'));
		$query->select($db->quoteName('profile.image'));
		$query->select($db->quoteName('profile.site'));
		$query->select($db->quoteName('profile.gender'));
		$query->select($db->quoteName('profile.notes'));
		$query->select($db->quoteName('profile.extra_fields'));
		$query->select($db->quoteName('profile.ip'));
		$query->select($db->quoteName('profile.hostname'));
		$query->select($db->quoteName('profile.plugins'));
		$query->leftJoin($db->quoteName('#__k2_users', 'profile').' ON '.$db->quoteName('user.id').' = '.$db->quoteName('profile.id'));
				
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

		// Get user groups
		$application = JFactory::getApplication();
		if ($application->isAdmin())
		{
			if (count($data))
			{
				$groups = $this->getGroups();
				$userIds = array();
				foreach ($data as $user)
				{
					$userIds[] = $user['id'];
				}
				$query = $db->getQuery(true);
				$query->select('*');
				$query->from($db->quoteName('#__user_usergroup_map', 'map'));
				$query->where($db->quoteName('map.user_id').' IN ('.implode(',', $userIds).')');
				$db->setQuery($query);
				$mappings = $db->loadObjectList();

				foreach ($data as &$user)
				{
					$user['groups'] = array();
					foreach ($mappings as $mapping)
					{
						if ($mapping->user_id == $user['id'])
						{
							$user['groups'][] = $groups[$mapping->group_id]->title;
						}
					}
				}
			}
		}

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
				$query->where($db->quoteName('user.id').' IN ('.implode(',', $id).')');
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
		if (is_numeric($this->getState('block')))
		{
			$query->where($db->quoteName('user.block').' = '.(int)$this->getState('block'));
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
			case 'username' :
				$ordering = 'user.username';
				$direction = 'ASC';
				break;
			case 'email' :
				$ordering = 'user.email';
				$direction = 'ASC';
				break;
			case 'lastvisitDate' :
				$ordering = 'user.lastvisitDate';
				$direction = 'DESC';
				break;
			case 'ip' :
				$ordering = 'profile.ip';
				$direction = 'ASC';
				break;
			case 'hostname' :
				$ordering = 'profile.hostname';
				$direction = 'ASC';
				break;
		}

		// Append sorting
		$db = $this->getDbo();
		$query->order($db->quoteName($ordering).' '.$direction);

	}

	/**
	 * Save method.
	 *
	 * @return boolean	True on success false on failure.
	 */

	public function save()
	{
		// Get some variables
		$table = $this->getTable();
		$data = $this->getState('data');

		// Check permissions
		$user = JFactory::getUser();
		$id = (isset($data['id'])) ? $data['id'] : 0;

		// Edit
		if ($id)
		{
			// Actions
			$canEdit = $user->authorise('core.edit', 'com_users');
			$canEditState = $user->authorise('core.edit.state', 'com_users');

			// User cannot edit the user neither it's state. Stop the process
			if (!$canEdit && !$canEditState)
			{
				$this->setError(JText::_('K2_YOU_ARE_NOT_AUTHORIZED_TO_PERFORM_THIS_OPERATION'));
				return false;
			}
			else
			{
				// Store the input states values in case we need them after
				$block = $data['block'];
				$activation = $data['activation'];

				// User cannot edit the item. Reset the input
				if (!$canEdit)
				{
					$data = array();
					$data['id'] = $table->id;
				}

				// Set the states values depending on permissions
				if ($canEditState)
				{
					$data['block'] = $block;
					$data['activation'] = $activation;
				}
				else
				{
					$jUser = JFactory::getUser($data['id']);
					$data['block'] = $jUser->block;
					$data['activation'] = $jUser->activation;
				}
			}

		}
		else
		{
			if (!$user->authorise('core.create', 'com_users'))
			{
				$this->setError(JText::_('K2_YOU_ARE_NOT_AUTHORIZED_TO_PERFORM_THIS_OPERATION'));
				return false;
			}
		}

		// Load core users language files
		$language = JFactory::getLanguage();
		$language->load('com_users');

		// Get core users model
		JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR.'/components/com_users/models');
		$model = JModelLegacy::getInstance('User', 'UsersModel');

		// Prepare some data for the core model
		if (isset($data['id']) && $data['id'] && !isset($data['block']))
		{
			$jUser = JFactory::getUser($data['id']);
			$data['block'] = $jUser->block;
		}

		// First try to save the Joomla! user data. The model also makes checks for permissions
		if (!$model->save($data))
		{
			$this->setError($model->getError());
			return false;
		}

		$data['id'] = $model->getState('user.id');

		// Continue with K2 user data. If profile does not exists create the record before we save the data
		if (!$table->load($data['id']) && (int)$data['id'] > 0)
		{
			// Create record
			$db = $this->getDBO();
			$query = $db->getQuery(true);
			$query->insert($db->quoteName('#__k2_users'))->columns($db->quoteName('id'))->values((int)$data['id']);
			$db->setQuery($query);
			$db->execute();

			// Try to load again the record
			if (!$table->load($data['id']))
			{
				$this->setError($table->getError());
				return false;
			}
		}

		// Continue the save process normally
		if (!$this->onBeforeSave($data, $table))
		{
			return false;
		}
		if (!$table->save($data))
		{
			$this->setError($table->getError());
			return false;
		}
		$this->setState('id', $table->id);
		if (!$this->onAfterSave($data, $table))
		{
			return false;
		}
		return true;
	}

	/**
	 * onBeforeSave method. Hook for chidlren model to prepare the data.
	 *
	 * @param   array  $data     The data to be saved.
	 * @param   JTable  $table   The table object.
	 *
	 * @return boolean
	 */
	protected function onBeforeSave(&$data, $table)
	{

		// Extra fields
		if (isset($data['extra_fields']))
		{
			$data['extra_fields'] = json_encode($data['extra_fields']);
		}
		return true;

	}

	/**
	 * Delete method.
	 *
	 * @return boolean	True on success false on failure.
	 */

	public function delete()
	{
		// Check permissions
		$user = JFactory::getUser();
		if (!$user->authorise('core.delete', 'com_users'))
		{
			$this->setError(JText::_('K2_YOU_ARE_NOT_AUTHORIZED_TO_PERFORM_THIS_OPERATION'));
			return false;
		}

		// Set some variables
		$table = $this->getTable();
		$id = $this->getState('id');

		// Load core users language files
		$language = JFactory::getLanguage();
		$language->load('com_users');

		// Get core users model
		JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR.'/components/com_users/models');
		$model = JModelLegacy::getInstance('User', 'UsersModel');

		// Delete the core user entry first
		$input = array($id);
		if (!$model->delete($input))
		{
			$this->setError($model->getError());
			return false;
		}

		// Delete the K2 user. If the profile does not exists return true there is nothing more to do
		if (!$table->load($id))
		{
			return true;
		}

		if (!$this->onBeforeDelete($table))
		{
			return false;
		}
		if (!$table->delete())
		{
			$this->setError($table->getError());
			return false;
		}

		if (!$this->onAfterDelete($table))
		{
			return false;
		}
		return true;
	}

	/**
	 * onAfterDelete method. Hook for chidlren model.
	 *
	 * @param   JTable  $table     	The table object.
	 *
	 * @return boolean
	 */

	protected function onAfterDelete($table)
	{
		// Delete statistics entry
		$statistics = K2Model::getInstance('Statistics', 'K2Model');
		$statistics->deleteUserEntry($this->getState('id'));

		// Return
		return true;
	}

	public function getGroups()
	{
		if (!is_null($this->groups))
		{
			return $this->groups;
		}

		$db = $this->getDBO();

		// Get query
		$query = $db->getQuery(true);

		// Select statement
		$query->select(array(
			$db->quoteName('id'),
			$db->quoteName('title')
		));

		$query->from($db->quoteName('#__usergroups'));

		// Set the query
		$db->setQuery($query);

		// Get the result
		$this->groups = $db->loadObjectList('id');

		return $this->groups;
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

	public function getTopCommenters()
	{
		// Get database
		$db = $this->getDBO();

		// Get query
		$query = $db->getQuery(true);
	
		// Select statement
		$query->select($db->quoteName('stats.userId'));
		$query->select($db->quoteName('stats.comments'));
		$query->from($db->quoteName('#__k2_users_stats', 'stats'));
		
		// Join with users
		$query->leftJoin($db->quoteName('#__users', 'user').' ON '.$db->quoteName('user.id').' = '.$db->quoteName('stats.userId'));
		$query->where($db->quoteName('user.block').' = 0');
		
		// Sorting
		$query->order($db->quoteName('stats.comments').' DESC');

		// Set the query
		$db->setQuery($query, 0, (int)$this->getState('limit'));

		// Get the result
		$rows = $db->loadObjectList();

		// Return the result
		return $rows;
	}


}
