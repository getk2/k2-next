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
		$query->select($db->quoteName('user.name', 'authorName'));
		$query->leftJoin($db->quoteName('#__users', 'user').' ON '.$db->quoteName('user.id').' = '.$db->quoteName('category.created_by'));
		
		// Join over the user
		$query->select($db->quoteName('user.name', 'moderatorName'));
		$query->leftJoin($db->quoteName('#__users', 'moderator').' ON '.$db->quoteName('moderator.id').' = '.$db->quoteName('category.modified_by'));

		// Set query conditions
		$this->setQueryConditions($query);
		
		// Append sorting
		if ($this->getState('sorting'))
		{
			$query->order($this->getState('sorting'));
		}

		// Hook for plugins
		$this->onBeforeSetQuery($query, 'com_k2.categories.list');

		// Set the query
		$db->setQuery($query, (int)$this->getState('limitstart'), (int)$this->getState('limit'));

		// Get rows
		$data = $db->loadAssocList();

		// Generate K2 resources instances from the result data.
		$rows = $this->getResources($data, 'item');

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

		// Join over the language
		$query->leftJoin($db->quoteName('#__languages', 'lang').' ON '.$db->quoteName('lang.lang_code').' = '.$db->quoteName('category.language'));

		// Join over the asset groups.
		$query->leftJoin($db->quoteName('#__viewlevels', 'assetGroup').' ON '.$db->quoteName('assetGroup.id').' = '.$db->quoteName('category.access'));
		
		// Join over the user
		$query->select($db->quoteName('user.name', 'authorName'));
		$query->leftJoin($db->quoteName('#__users', 'user').' ON '.$db->quoteName('user.id').' = '.$db->quoteName('category.created_by'));
		
		// Join over the user
		$query->select($db->quoteName('user.name', 'moderatorName'));
		$query->leftJoin($db->quoteName('#__users', 'moderator').' ON '.$db->quoteName('moderator.id').' = '.$db->quoteName('category.modified_by'));

		// Set query conditions
		$this->setQueryConditions($query);

		// Hook for plugins
		$this->setQueryConditions($query, 'com_k2.categories.count');

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
		if (is_numeric($this->getState('published')))
		{
			$query->where($db->quoteName('category.published').' = '.(int)$this->getState('published'));
		}
		if (is_numeric($this->getState('trashed')))
		{
			$query->where($db->quoteName('category.trashed').' = '.(int)$this->getState('trashed'));
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
				$query->where('( LOWER('.$db->quoteName('category.name').'.) LIKE '.$db->Quote('%'.$search.'%', false).' 
				OR '.$db->quoteName('category.id').' = '.(int)$search.')  
				OR LOWER('.$db->quoteName('category.description').'.) LIKE '.$db->Quote('%'.$search.'%', false).')');
			}
		}
	}

	/**
	 * Save method.
	 *
	 * @param   boolean   $patch	Flag to indicate if we are patching or performing a normal save.
	 *
	 * @return boolean	True on success false on failure.
	 */

	public function save($patch = false)
	{
		$table = $this->getTable();
		$data = $this->getState('data');
		if ($patch)
		{
			$table->load($data['id']);
		}
		$location = isset($data['parent_id']) ? $data['parent_id'] : $table->parent_id;
		$table->setLocation($location, 'last-child');
		if (!$table->save($data))
		{
			$this->setError($table->getError());
			return false;
		}
		if (!$table->rebuildPath($table->id))
		{
			$this->setError($table->getError());
			return false;
		}
		$this->setState('id', $table->id);
		return true;
	}

}
