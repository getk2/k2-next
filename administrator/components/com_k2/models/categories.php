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
				case 'id' :
					$order = 'category.id DESC';
					break;
				case 'title' :
					$order = 'category.title ASC';
					break;
				case 'ordering' :
					$order = 'category.lft ASC';
					break;
				case 'published' :
					$order = 'category.published DESC';
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
			}
		}
		// Append sorting
		if ($order)
		{
			$query->order($order);
		}
	}


	/**
	 * onBeforeSave method.
	 * @param   array  $data     The data to be saved.
	 *
	 * @return void
	 */

	protected function onBeforeSave(&$data, $table)
	{
		$user = JFactory::getUser();
		$configuration = JFactory::getConfig();
		$userTimeZone = $user->getParam('timezone', $configuration->get('offset'));

		// Handle date data
		if ($data['id'] && isset($data['createdDate']))
		{
			// Convert date to UTC
			$createdDateTime = $data['createdDate'].' '.$data['createdTime'];
			$data['created'] = JFactory::getDate($createdDateTime, $userTimeZone)->toSql();
		}

		if (isset($data['parent_id']))
		{
			$table->setLocation($data['parent_id'], 'last-child');
		}

	}

}
