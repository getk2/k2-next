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

class K2ModelExtraFieldsGroups extends K2Model
{
	public function getRows()
	{
		// Get database
		$db = $this->getDBO();

		// Get query
		$query = $db->getQuery(true);

		// Select rows
		$query->select($db->quoteName('extraFieldsGroup').'.*')->from($db->quoteName('#__k2_extra_fields_groups', 'extraFieldsGroup'));

		// Join over the resoures xref
		$query->leftJoin($db->quoteName('#__k2_extra_fields_groups_xref', 'xref').' ON '.$db->quoteName('extraFieldsGroup.id').' = '.$db->quoteName('xref.groupId'));

		// Set query conditions
		$this->setQueryConditions($query);

		// Set query sorting
		$this->setQuerySorting($query);

		// Hook for plugins
		$this->onBeforeSetQuery($query, 'com_k2.extraFieldsGroups.list');

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
		$query->select('COUNT(*)')->from($db->quoteName('#__k2_extra_fields_groups', 'extraFieldsGroup'));

		// Join over the resources xref
		$query->leftJoin($db->quoteName('#__k2_extra_fields_groups_xref', 'xref').' ON '.$db->quoteName('extraFieldsGroup.id').' = '.$db->quoteName('xref.groupId'));

		// Set query conditions
		$this->setQueryConditions($query);

		// Hook for plugins
		$this->setQueryConditions($query, 'com_k2.extraFieldsGroups.count');

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
				$query->where($db->quoteName('extraFieldsGroup.id').' IN '.$id);
			}
			else
			{
				$query->where($db->quoteName('extraFieldsGroup.id').' = '.(int)$id);
			}
		}
		if ($this->getState('scope'))
		{
			$query->where($db->quoteName('extraFieldsGroup.scope').' = '.$db->quote($this->getState('scope')));
		}
		if ($this->getState('resourceId'))
		{
			$resourceId = $this->getState('resourceId');
			$query->where($db->quoteName('xref.resourceId').' = '.(int)$resourceId);
		}
		if ($this->getState('search'))
		{
			$search = JString::trim($this->getState('search'));
			$search = JString::strtolower($search);
			if ($search)
			{
				$search = $db->escape($search, true);
				$query->where('( LOWER('.$db->quoteName('extraFieldsGroup.name').') LIKE '.$db->Quote('%'.$search.'%', false).' 
				OR '.$db->quoteName('extraFieldsGroup.id').' = '.(int)$search.')');
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
					$order = 'extraFieldsGroup.id DESC';
					break;
				case 'name' :
					$order = 'extraFieldsGroup.name ASC';
					break;
			}
		}
		// Append sorting
		if ($order)
		{
			$query->order($order);
		}
	}

	protected function onAfterSave(&$data, $table)
	{
		if (isset($data['assignmentsSwitch']))
		{
			$this->unassign($table->id);
			if ($data['assignmentsSwitch'] == 'all')
			{
				$this->assign($table->id, array(0));
			}
			elseif ($data['assignmentsSwitch'] == 'select')
			{
				$this->assign($table->id, $data['assignments']);
			}
		}

	}

	public function assign($groupId, $resources)
	{
		// Get database
		$db = $this->getDBO();

		foreach ($resources as $resourceId)
		{
			// Delete any duplicates
			$query = $db->getQuery(true);
			$query->delete('#__k2_extra_fields_groups_xref')->where($db->quoteName('groupId').' = '.(int)$groupId)->where($db->quoteName('resourceId').' = '.(int)$resourceId);
			$db->setQuery($query);
			$db->execute();

			// Insert query
			$query = $db->getQuery(true);
			$query->insert('#__k2_extra_fields_groups_xref')->columns('groupId, resourceId')->values((int)$groupId.','.(int)$resourceId);
			$db->setQuery($query);
			$db->execute();
		}

		// Return
		return true;
	}

	public function unassign($groupId)
	{
		// Get database
		$db = $this->getDBO();

		// Delete all group assignements
		$query = $db->getQuery(true);
		$query->delete('#__k2_extra_fields_groups_xref')->where($db->quoteName('groupId').' = '.(int)$groupId);
		$db->setQuery($query);
		$db->execute();

		// Return
		return true;
	}

	public function getAssignments($groupId)
	{
		// Get database
		$db = $this->getDBO();

		// Delete all group assignements
		$query = $db->getQuery(true);
		$query->select('resourceId')->from('#__k2_extra_fields_groups_xref')->where($db->quoteName('groupId').' = '.(int)$groupId);
		$db->setQuery($query);
		$assignments = $db->loadColumn();

		// Return
		return $assignments;
	}

}
