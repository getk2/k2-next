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
require_once JPATH_ADMINISTRATOR.'/components/com_k2/models/categories.php';

class K2ModelTags extends K2Model
{
	public function getRows()
	{
		// Get database
		$db = $this->getDBO();

		// Get query
		$query = $db->getQuery(true);

		// Select rows
		$query->select($db->quoteName('tag').'.*');
		$query->from($db->quoteName('#__k2_tags', 'tag'));

		// Set query conditions
		$this->setQueryConditions($query);

		// Set query sorting
		$this->setQuerySorting($query);

		// Hook for plugins
		$this->onBeforeSetQuery($query, 'com_k2.tags.list');

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
		$query->select('COUNT(*)')->from($db->quoteName('#__k2_tags', 'tag'));

		// Set query conditions
		$this->setQueryConditions($query);

		// Hook for plugins
		$this->onBeforeSetQuery($query, 'com_k2.tags.count');

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

		if ($this->getState('itemId'))
		{
			$query->leftJoin($db->quoteName('#__k2_tags_xref', 'xref').' ON '.$db->quoteName('xref.tagId').' = '.$db->quoteName('tag.id'));
			$query->where($db->quoteName('xref.itemId').' = '.(int)$this->getState('itemId'));
		}
		if (is_numeric($this->getState('state')))
		{
			$query->where($db->quoteName('tag.state').' = '.(int)$this->getState('state'));
		}
		if ($this->getState('id'))
		{
			$id = $this->getState('id');
			if (is_array($id))
			{
				JArrayHelper::toInteger($id);
				$query->where($db->quoteName('tag.id').' IN ('.implode(',', $id).')');
			}
			else
			{
				$query->where($db->quoteName('tag.id').' = '.(int)$id);
			}
		}
		if ($this->getState('alias'))
		{
			$query->where($db->quoteName('tag.alias').' = '.$db->quote($this->getState('alias')));
		}
		if ($this->getState('search'))
		{
			$search = JString::trim($this->getState('search'));
			$search = JString::strtolower($search);
			if ($search)
			{
				$search = $db->escape($search, true);
				$query->where('( LOWER('.$db->quoteName('tag.name').') LIKE '.$db->Quote('%'.$search.'%', false).' 
				OR '.$db->quoteName('tag.id').' = '.(int)$search.'  
				OR LOWER('.$db->quoteName('tag.alias').') LIKE '.$db->Quote('%'.$search.'%', false).')');
			}
		}
	}

	private function setQuerySorting(&$query)
	{
		$sorting = $this->getState('sorting');
		switch($sorting)
		{
			default :
				$ordering = 'id';
				$direction = 'DESC';
				break;
			case 'id' :
			case 'id.reverse' :
				$ordering = 'id';
				$direction = $sorting == 'id' ? 'ASC' : 'DESC';
				break;
			case 'name' :
			case 'name.reverse' :
				$ordering = 'name';
				$direction = $sorting == 'name' ? 'ASC' : 'DESC';
				break;
			case 'state' :
			case 'state.reverse' :
				$ordering = 'state';
				$direction = $sorting == 'state' ? 'ASC' : 'DESC';
				break;
		}

		// Append sorting
		$db = $this->getDbo();
		$query->order($db->quoteName($ordering).' '.$direction);

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

		// Permissions check
		if (!$user->authorise('k2.tags.manage', 'com_k2'))
		{
			$this->setError(JText::_('K2_YOU_ARE_NOT_AUTHORIZED_TO_PERFORM_THIS_OPERATION'));
			return false;
		}

		// Extra fields
		if (isset($data['extra_fields']))
		{
			$data['extra_fields'] = json_encode($data['extra_fields']);
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
		// User
		$user = JFactory::getUser();

		// Permissions check
		if (!$user->authorise('k2.tags.manage'))
		{
			$this->setError(JText::_('K2_YOU_ARE_NOT_AUTHORIZED_TO_PERFORM_THIS_OPERATION'));
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
		// Get database
		$db = $this->getDBO();

		// Get query
		$query = $db->getQuery(true);

		// Delete
		$query->delete('#__k2_tags_xref')->where($db->quoteName('tagId').' = '.(int)$this->getState('id'));
		$db->setQuery($query);
		$db->execute();

		// Return
		return true;

	}

	public function addTag($name)
	{
		// Get user
		$user = JFactory::getUser();

		// Get database
		$db = $this->getDBO();

		// Get query
		$query = $db->getQuery(true);

		// Select tag
		$query->select('id')->from($db->quoteName('#__k2_tags'));

		// Search
		$search = JString::trim($name);
		$search = JString::strtolower($search);
		$query->where('LOWER('.$db->quoteName('name').') = '.$db->Quote($search));

		// Set the query
		$db->setQuery($query);

		// Get the result
		$id = $db->loadResult();

		// If it does not exist, add it
		if (!$id)
		{
			// Ensure that tags creation is not locked
			if (!$user->authorise('k2.tags.create', 'com_k2') && !$user->authorise('k2.tags.manage', 'com_k2'))
			{
				return false;
			}

			$data = array(
				'name' => $name,
				'state' => 1
			);
			$this->setState('data', $data);
			if (!$this->save())
			{
				return false;
			}
			$id = $this->getState('id');
		}

		// Return the tag id
		return $id;

	}

	public function deleteItemTags($itemId)
	{
		// Get database
		$db = $this->getDBO();

		// Get query
		$query = $db->getQuery(true);

		// Delete
		$query->delete('#__k2_tags_xref')->where($db->quoteName('itemId').' = '.(int)$itemId);
		$db->setQuery($query);
		$db->execute();

		// Return
		return true;
	}

	public function tagItem($tagId, $itemId)
	{
		// Get database
		$db = $this->getDBO();

		// Get query
		$query = $db->getQuery(true);

		// Delete any duplicates
		$query->delete('#__k2_tags_xref')->where($db->quoteName('tagId').' = '.(int)$tagId)->where($db->quoteName('itemId').' = '.(int)$itemId);
		$db->setQuery($query);
		$db->execute();

		// Insert query
		$query->insert('#__k2_tags_xref')->columns('tagId, itemId')->values((int)$tagId.','.(int)$itemId);
		$db->setQuery($query);
		$db->execute();

		// Return
		return true;
	}

	public function countTagItems($tagId)
	{
		// Get database
		$db = $this->getDBO();

		// Get query
		$query = $db->getQuery(true);

		// Select statement
		$query->select('COUNT(*)')->from($db->quoteName('#__k2_tags_xref'));

		// Set query conditions
		$query->where($db->quoteName('tagId').' = '.(int)$tagId);

		// Set the query
		$db->setQuery($query);

		// Get the result
		$result = $db->loadResult();

		// Return the result
		return (int)$result;
	}

	public function getItemTags($itemId)
	{
		// Get database
		$db = $this->getDBO();

		// Get query
		$query = $db->getQuery(true);

		// Select statement
		$query->select('tagId')->from($db->quoteName('#__k2_tags_xref'));

		// Set query conditions
		$query->where($db->quoteName('itemId').' = '.(int)$itemId);

		// Set the query
		$db->setQuery($query);

		// Get the result
		$result = $db->loadColumn();

		// Return the result
		return $result;
	}

	public function getTagCloud()
	{
		// Get database
		$db = $this->getDbo();

		// Get query
		$query = $db->getQuery(true);

		// Select tag id
		$query->select($db->quoteName('tag').'.*');

		// counter
		$query->select('COUNT('.$db->quoteName('tag.id').') AS '.$db->quoteName('counter'));

		// From statement
		$query->from($db->quoteName('#__k2_tags', 'tag'));

		// Tags should be published
		$query->where($db->quoteName('tag.state').' = 1');

		// Join over the reference table
		$query->leftJoin($db->quoteName('#__k2_tags_xref', 'xref').' ON '.$db->quoteName('xref.tagId').' = '.$db->quoteName('tag.id'));

		// Join over the items table
		$query->leftJoin($db->quoteName('#__k2_items', 'item').' ON '.$db->quoteName('item.id').' = '.$db->quoteName('xref.itemId'));

		// Items should be published
		$query->where($db->quoteName('item.state').' = 1');

		// Handle categories
		$categories = K2ModelCategories::getCategoryFilter($this->getState('categories'), $this->getState('recursive'), true);

		// user cannot see any category return empty data
		if (empty($categories))
		{
			return array();
		}

		// Apply the filter to the query
		$query->where($db->quoteName('item.catid').' IN ('.implode(',', $categories).')');

		// Check access level
		$viewlevels = array_unique(JFactory::getUser()->getAuthorisedViewLevels());
		$query->where($db->quoteName('item.access').' IN ('.implode(',', $viewlevels).')');

		// Check publish up/down
		$date = JFactory::getDate()->toSql();
		$query->where('('.$db->quoteName('item.publish_up').' = '.$db->Quote($db->getNullDate()).' OR '.$db->quoteName('item.publish_up').' <= '.$db->Quote($date).')');
		$query->where('('.$db->quoteName('item.publish_down').' = '.$db->Quote($db->getNullDate()).' OR '.$db->quoteName('item.publish_down').' >= '.$db->Quote($date).')');

		// Group by tag Id
		$query->order($db->quoteName('counter').' DESC');

		// Group by tag Id
		$query->group($db->quoteName('tag.id'));

		// Set query
		$db->setQuery($query, 0, (int)$this->getState('limit'));

		// Get rows
		$rows = $db->loadObjectList();

		// Return
		return $rows;
	}

}
