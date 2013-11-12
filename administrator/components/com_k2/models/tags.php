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

class K2ModelTags extends K2Model
{
	public function getRows()
	{
		// Get database
		$db = $this->getDBO();

		// Get query
		$query = $db->getQuery(true);

		// Select rows
		$query->select($db->quoteName('tag').'.*')->from($db->quoteName('#__k2_tags', 'tag'));

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
		if (is_numeric($this->getState('published')))
		{
			$query->where($db->quoteName('tag.published').' = '.(int)$this->getState('published'));
		}
		if ($this->getState('id'))
		{
			$id = $this->getState('id');
			if (is_array($id))
			{
				JArrayHelper::toInteger($id);
				$query->where($db->quoteName('tag.id').' IN '.$id);
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
		$order = null;
		if ($sorting)
		{
			switch($sorting)
			{
				case 'id' :
					$order = 'id DESC';
					break;
				case 'name' :
					$order = 'name ASC';
					break;
				case 'published' :
					$order = 'published DESC';
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

		// Permissions check
		if (!$user->authorise('k2.tags.manage'))
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
			$data = array(
				'name' => $name,
				'published' => 1
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

}
