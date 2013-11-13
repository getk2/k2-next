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

class K2ModelCategories extends K2Model
{
	public function getRows()
	{
		// Get database
		$db = $this->getDBO();

		// Get query
		$query = $db->getQuery(true);

		// Select rows
		$query->select($db->quoteName('category').'.*')->from($db->quoteName('#__k2_categories', 'category'));

		// Join over the language
		$query->select($db->quoteName('lang.title', 'languageTitle'));
		$query->leftJoin($db->quoteName('#__languages', 'lang').' ON '.$db->quoteName('lang.lang_code').' = '.$db->quoteName('category.language'));

		// Join over the asset groups.
		$query->select($db->quoteName('assetGroup.title', 'viewLevel'));
		$query->leftJoin($db->quoteName('#__viewlevels', 'assetGroup').' ON '.$db->quoteName('assetGroup.id').' = '.$db->quoteName('category.access'));

		// Join over the user
		$query->select($db->quoteName('author.name', 'authorName'));
		$query->leftJoin($db->quoteName('#__users', 'author').' ON '.$db->quoteName('author.id').' = '.$db->quoteName('category.created_by'));

		// Join over the user
		$query->select($db->quoteName('moderator.name', 'moderatorName'));
		$query->leftJoin($db->quoteName('#__users', 'moderator').' ON '.$db->quoteName('moderator.id').' = '.$db->quoteName('category.modified_by'));

		// Set query conditions
		$this->setQueryConditions($query);

		// Set query sorting
		$this->setQuerySorting($query);

		// Hook for plugins
		$this->onBeforeSetQuery($query, 'com_k2.categories.list');

		// Set the query
		$db->setQuery($query, (int)$this->getState('limitstart'), (int)$this->getState('limit'));

		// Get rows
		$data = $db->loadAssocList();

		// Generate K2 resources instances from the result data.
		$rows = $this->getResources($data, 'category');

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
		$query->select('COUNT(*)')->from($db->quoteName('#__k2_categories', 'category'));

		// Set query conditions
		$this->setQueryConditions($query);

		// Hook for plugins
		$this->onBeforeSetQuery($query, 'com_k2.categories.count');

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
		$query->where($db->quoteName('category.id').' != 1');
		if ($this->getState('language'))
		{
			$query->where($db->quoteName('category.language').' = '.$db->quote($this->getState('language')));
		}
		if (is_numeric($this->getState('state')))
		{
			$query->where($db->quoteName('category.state').' = '.(int)$this->getState('state'));
		}
		if ($this->getState('access'))
		{
			$query->where($db->quoteName('category.access').' = '.(int)$this->getState('access'));
		}
		if ($this->getState('id'))
		{
			$id = $this->getState('id');
			if (is_array($id))
			{
				JArrayHelper::toInteger($id);
				$query->where($db->quoteName('category.id').' IN '.$id);
			}
			else
			{
				$query->where($db->quoteName('category.id').' = '.(int)$id);
			}
		}
		if ($this->getState('alias'))
		{
			$query->where($db->quoteName('category.alias').' = '.$db->quote($this->getState('alias')));
		}
		if ($this->getState('search'))
		{
			$search = JString::trim($this->getState('search'));
			$search = JString::strtolower($search);
			if ($search)
			{
				$search = $db->escape($search, true);
				$query->where('( LOWER('.$db->quoteName('category.title').') LIKE '.$db->Quote('%'.$search.'%', false).' 
				OR '.$db->quoteName('category.id').' = '.(int)$search.' 
				OR LOWER('.$db->quoteName('category.description').') LIKE '.$db->Quote('%'.$search.'%', false).')');
			}
		}
	}

	private function setQuerySorting(&$query)
	{
		$sorting = $this->getState('sorting');
		$order = null;
		if ($sorting)
		{
			switch($sorting)
			{
				default :
				case 'id' :
					$order = 'category.id DESC';
					break;
				case 'title' :
					$order = 'category.title ASC';
					break;
				case 'ordering' :
					$order = 'category.lft ASC';
					break;
				case 'state' :
					$order = 'category.state DESC';
					break;
				case 'author' :
					$order = 'authorName ASC';
					break;
				case 'moderator' :
					$order = 'moderatorName ASC';
					break;
				case 'access' :
					$order = 'viewLevel ASC';
					break;
				case 'created' :
					$order = 'category.created DESC';
					break;
				case 'modified' :
					$order = 'category.modified DESC';
					break;
				case 'language' :
					$order = 'languageTitle ASC';
					break;
				case 'image' :
					$order = 'category.image DESC';
					break;
			}
		}
		// Append sorting
		if ($order)
		{
			$query->order($order);
		}
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
		// User
		$user = JFactory::getUser();

		// Create action
		if (!$table->id)
		{
			// Detect the context
			$context = (isset($data['parent_id']) && $data['parent_id']) ? 'com_k2.category.'.$data['parent_id'] : 'com_k2';

			// If the user has not the permission to create category stop the processs. Otherwise handle the category state
			if (!$user->authorise('k2.category.create', $context))
			{
				$this->setError(JText::_('K2_YOU_ARE_NOT_AUTHORIZED_TO_PERFORM_THIS_OPERATION'));
				return false;
			}
			else
			{
				// User can create the category but cannot edit it's state so we set the category state to 0
				if (!$user->authorise('k2.category.edit.state', $context))
				{
					$data['state'] = 0;
				}
			}

		}
		// Edit action
		if ($table->id)
		{
			// Detect the context
			$context = 'com_k2.category.'.$table->id;

			// Actions
			$canEdit = $user->authorise('k2.category.edit', $context) || ($user->authorise('k2.item.edit.own', $context) && $user->id == $table->created_by);
			$canEditState = $user->authorise('k2.item.edit.state', $context);

			// User cannot edit the category neither it's state. Stop the process
			if (!$canEdit && !$canEditState)
			{
				$this->setError(JText::_('K2_YOU_ARE_NOT_AUTHORIZED_TO_PERFORM_THIS_OPERATION'));
				return false;
			}
			else
			{
				// Store the input states values in case we need them after
				$state = $data['state'];

				// User cannot edit the item. Reset the input
				if (!$canEdit)
				{
					$data = array();
					$data['id'] = $table->id;
				}

				// Set the states values depending on permissions
				$data['state'] = ($canEditState) ? $state : $table->state;
			}
		}

		// Get timezone
		$configuration = JFactory::getConfig();
		$userTimeZone = $user->getParam('timezone', $configuration->get('offset'));

		// Handle date data
		if ($data['id'] && isset($data['createdDate']))
		{
			// Convert date to UTC
			$createdDateTime = $data['createdDate'].' '.$data['createdTime'];
			$data['created'] = JFactory::getDate($createdDateTime, $userTimeZone)->toSql();
		}

		// Update category location
		if (isset($data['parent_id']) && !$data['id'])
		{
			$table->setLocation($data['parent_id'], 'last-child');
		}

		// Image
		if (isset($data['image']))
		{
			$this->setState('imageId', $data['image']['id']);
			$data['image']['flag'] = (int)(bool)$data['image']['id'];
			unset($data['image']['path']);
			unset($data['image']['id']);
			$data['image'] = json_encode($data['image']);
		}

		// Extra fields
		if (isset($data['extra_fields']))
		{
			$data['extra_fields'] = json_encode($data['extra_fields']);
		}

		return true;

	}

	/**
	 * onAfterSave method. Hook for chidlren model to save extra data.
	 *
	 * @param   array  $data     The data passed to the save function.
	 * @param   JTable  $table   The table object.
	 *
	 * @return boolean
	 */

	protected function onAfterSave(&$data, $table)
	{
		// If we have a tmpId we have a new category and we need to handle accordingly uploaded files
		if (isset($data['tmpId']) && $data['tmpId'])
		{
			// Image
			if ($this->getState('imageId'))
			{
				require_once JPATH_ADMINISTRATOR.'/components/com_k2/classes/filesystem.php';
				$filesystem = K2FileSystem::getInstance();
				$baseSourceFileName = $this->getState('imageId');
				$baseTargetFileName = md5('Image'.$table->id);

				$path = 'media/k2/categories';
				$source = $baseSourceFileName.'.jpg';
				$target = $baseTargetFileName.'.jpg';
				$filesystem->rename($path.'/'.$source, $path.'/'.$target);
			}
		}

		if (!$table->rebuildPath($table->id))
		{
			$this->setError($table->getError());
			return false;
		}

		if (!$table->rebuild($table->id, $table->lft, $table->level, $table->path))
		{
			$this->setError($table->getError());
			return false;
		}

		return true;
	}

	/**
	 * onBeforeDelete method. 		Hook for chidlren model.
	 *
	 * @param   JTable  $table     	The table object.
	 *
	 * @return boolean
	 */

	protected function onBeforeDelete($table)
	{
		$user = JFactory::getUser();
		if (!$user->authorise('k2.category.delete', 'com_k2.category.'.$table->id))
		{
			$this->setError(JText::_('K2_YOU_ARE_NOT_AUTHORIZED_TO_PERFORM_THIS_OPERATION'));
			return false;
		}
		return true;
	}

	public function saveOrder($ids, $ordering)
	{
		$user = JFactory::getUser();
		if (!$user->authorise('k2.category.edit', 'com_k2'))
		{
			$this->setError(JText::_('K2_YOU_ARE_NOT_AUTHORIZED_TO_PERFORM_THIS_OPERATION'));
			return false;
		}
		$table = $this->getTable();
		if (!$table->saveorder($ids, $ordering))
		{
			$this->setError($table->getError());
			return false;
		}
		return true;
	}

}
