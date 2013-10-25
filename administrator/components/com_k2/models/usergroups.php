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

class K2ModelUserGroups extends K2Model
{
	public function getRows()
	{
		JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR.'/components/com_users/models');
		$model = JModelLegacy::getInstance('Groups', 'UsersModel');

		if ($this->getState('search'))
		{
			$model->setState('filter.search', $this->getState('search'));
		}
				
		switch($this->getState('sorting'))
		{
				default :
					$ordering = 'a.lft';
					$direction = 'asc';
				case 'id' :
					$ordering = 'a.id';
					$direction = 'desc';
					break;
				case 'title' :
					$ordering = 'a.title';
					$direction = 'desc';
					break;
		}
		
		$model->setState('list.ordering', $ordering);
		$model->setState('list.direction', $direction);
		
		
		$model->setState('list.start', $this->getState('limitstart'));
		$model->setState('list.limit', $this->getState('limit'));

		$data = $model->getItems();

		// Generate K2 resources instances from the result data.
		$rows = $this->getResources($data);

		// Return rows
		return (array)$rows;
	}

	public function countRows()
	{
		JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR.'/components/com_users/models');
		$model = JModelLegacy::getInstance('Groups', 'UsersModel');

		if ($this->getState('search'))
		{
			$model->setState('filter.search', $this->getState('search'));
		}
		
		$total = $model->getTotal();
		

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

}
