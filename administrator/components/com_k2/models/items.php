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

class K2ModelItems extends K2Model
{
	public function getRows()
	{
		// Get database
		$db = $this->getDBO();

		// Get query
		$query = $db->getQuery(true);

		// Select rows
		$query->select($db->quoteName('item').'.*')->from($db->quoteName('#__k2_items', 'item'));

		// Join over the categories
		$query->select($db->quoteName('category.title', 'categoryName'));
		$query->leftJoin($db->quoteName('#__k2_categories', 'category').' ON '.$db->quoteName('category.id').' = '.$db->quoteName('item.catid'));

		// Join over the language
		$query->select($db->quoteName('lang.title', 'languageTitle'));
		$query->leftJoin($db->quoteName('#__languages', 'lang').' ON '.$db->quoteName('lang.lang_code').' = '.$db->quoteName('item.language'));

		// Join over the asset groups.
		$query->select($db->quoteName('assetGroup.title', 'viewLevel'));
		$query->leftJoin($db->quoteName('#__viewlevels', 'assetGroup').' ON '.$db->quoteName('assetGroup.id').' = '.$db->quoteName('item.access'));

		// Join over the user
		$query->select($db->quoteName('user.name', 'authorName'));
		$query->leftJoin($db->quoteName('#__users', 'user').' ON '.$db->quoteName('user.id').' = '.$db->quoteName('item.created_by'));
		
		// Join over the user
		$query->select($db->quoteName('user.name', 'moderatorName'));
		$query->leftJoin($db->quoteName('#__users', 'moderator').' ON '.$db->quoteName('moderator.id').' = '.$db->quoteName('item.modified_by'));

		// Set query conditions
		$this->setQueryConditions($query);

		// Append sorting
		if ($this->getState('sorting'))
		{
			$query->order($this->getState('sorting'));
		}

		// Hook for plugins
		$this->onBeforeSetQuery($query, 'com_k2.items.list');

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
		$query->select('COUNT(*)')->from($db->quoteName('#__k2_items', 'item'));

		// Join over the categories
		$query->leftJoin($db->quoteName('#__k2_categories', 'category').' ON '.$db->quoteName('category.id').' = '.$db->quoteName('item.catid'));

		// Join over the language
		$query->leftJoin($db->quoteName('#__languages', 'lang').' ON '.$db->quoteName('lang.lang_code').' = '.$db->quoteName('item.language'));

		// Join over the asset groups.
		$query->leftJoin($db->quoteName('#__viewlevels', 'assetGroup').' ON '.$db->quoteName('assetGroup.id').' = '.$db->quoteName('item.access'));

		// Join over the user
		$query->leftJoin($db->quoteName('#__users', 'user').' ON '.$db->quoteName('user.id').' = '.$db->quoteName('item.created_by'));

		// Set query conditions
		$this->setQueryConditions($query);

		// Hook for plugins
		$this->setQueryConditions($query, 'com_k2.items.count');

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
		if ($this->getState('language'))
		{
			$query->where($db->quoteName('item.language').' = '.$db->quote($this->getState('language')));
		}
		if (!is_null($this->getState('published')))
		{
			$query->where($db->quoteName('item.published').' = '.(int)$this->getState('published'));
		}
		if (!is_null($this->getState('featured')))
		{
			$query->where($db->quoteName('item.featured').' = '.(int)$this->getState('featured'));
		}
		if (!is_null($this->getState('trashed')))
		{
			$query->where($db->quoteName('item.trashed').' = '.(int)$this->getState('trashed'));
		}
		if ($this->getState('category'))
		{
			$query->where($db->quoteName('item.catid').' = '.(int)$this->getState('category'));
		}
		if ($this->getState('access'))
		{
			$query->where($db->quoteName('item.access').' = '.(int)$this->getState('access'));
		}
		if ($this->getState('id'))
		{
			$id = $this->getState('id');
			if (is_array($id))
			{
				JArrayHelper::toInteger($id);
				$query->where($db->quoteName('item.id').' IN '.$id);
			}
			else
			{
				$query->where($db->quoteName('item.id').' = '.(int)$id);
			}
		}
		if ($this->getState('alias'))
		{
			$query->where($db->quoteName('item.alias').' = '.$db->quote($this->getState('alias')));
		}
		if ($this->getState('author'))
		{
			$query->where($db->quoteName('item.created_by').' = '.(int)$this->getState('author'));
		}
		if ($this->getState('publish_up'))
		{
			$query->where('('.$db->quoteName('item.publish_up').' = '.$db->Quote($db->getNullDate()).' OR '.$db->quoteName('item.publish_up').' <= '.$db->Quote($this->getState('publish_up')).')');
		}
		if ($this->getState('publish_down'))
		{
			$query->where('('.$db->quoteName('item.publish_down').' = '.$db->Quote($db->getNullDate()).' OR '.$db->quoteName('item.publish_down').' >= '.$db->Quote($this->getState('publish_down')).')');
		}
		if ($this->getState('search'))
		{
			$search = JString::trim($this->getState('search'));
			$search = JString::strtolower($search);
			if ($search)
			{
				$search = $db->escape($search, true);
				$query->where('( LOWER('.$db->quoteName('item.title').'.) LIKE '.$db->Quote('%'.$search.'%', false).' 
				OR '.$db->quoteName('item.id').' = '.(int)$search.')  
				OR LOWER('.$db->quoteName('item.introtext').'.) LIKE '.$db->Quote('%'.$search.'%', false).'
				OR LOWER('.$db->quoteName('item.fulltext').'.) LIKE '.$db->Quote('%'.$search.'%', false).')');
			}
		}
	}

}
