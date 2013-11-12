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

class K2ModelExtraFields extends K2Model
{
	public function getRows()
	{
		// Get database
		$db = $this->getDBO();

		// Get query
		$query = $db->getQuery(true);

		// Select rows
		$query->select($db->quoteName('extraField').'.*')->from($db->quoteName('#__k2_extra_fields', 'extraField'));

		// Join over the groups
		$query->select($db->quoteName('group.name', 'groupName'));
		$query->leftJoin($db->quoteName('#__k2_extra_fields_groups', 'group').' ON '.$db->quoteName('extraField.group').' = '.$db->quoteName('group.id'));

		// Set query conditions
		$this->setQueryConditions($query);

		// Set query sorting
		$this->setQuerySorting($query);

		// Hook for plugins
		$this->onBeforeSetQuery($query, 'com_k2.extraFields.list');

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
		$query->select('COUNT(*)')->from($db->quoteName('#__k2_extra_fields', 'extraField'));

		// Set query conditions
		$this->setQueryConditions($query);

		// Hook for plugins
		$this->setQueryConditions($query, 'com_k2.extraFields.count');

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
		if (is_numeric($this->getState('published')))
		{
			$query->where($db->quoteName('extraField.published').' = '.(int)$this->getState('published'));
		}
		if ($this->getState('id'))
		{
			$id = $this->getState('id');
			if (is_array($id))
			{
				JArrayHelper::toInteger($id);
				$query->where($db->quoteName('extraField.id').' IN '.$id);
			}
			else
			{
				$query->where($db->quoteName('extraField.id').' = '.(int)$id);
			}
		}
		if ($this->getState('group'))
		{
			$query->where($db->quoteName('extraField.group').' = '.(int)$this->getState('group'));
		}
		if ($this->getState('search'))
		{
			$search = JString::trim($this->getState('search'));
			$search = JString::strtolower($search);
			if ($search)
			{
				$search = $db->escape($search, true);
				$query->where('( LOWER('.$db->quoteName('extraField.name').') LIKE '.$db->Quote('%'.$search.'%', false).' 
				OR '.$db->quoteName('extraField.id').' = '.(int)$search.')');
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
					$ordering = 'extraField.id';
					$direction = 'DESC';
					break;
				case 'name' :
					$ordering = 'extraField.name';
					$direction = 'ASC';
					break;
				case 'group' :
					$ordering = 'groupName';
					$direction = 'ASC';
					break;
				case 'type' :
					$ordering = 'type';
					$direction = 'ASC';
					break;
				case 'published' :
					$ordering = 'extraField.published';
					$direction = 'DESC';
					break;
				case 'ordering' :
					$ordering = 'extraField.ordering';
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
		if (!$user->authorise('k2.extrafields.manage'))
		{
			$this->setError(JText::_('K2_YOU_ARE_NOT_AUTHORIZED_TO_PERFORM_THIS_OPERATION'));
			return false;
		}

		return true;
	}

}
