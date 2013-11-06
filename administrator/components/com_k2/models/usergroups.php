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

		$input = JFactory::getApplication()->input;
		$input->set('limitstart', $this->getState('limitstart'));
		$input->set('filter_order', $ordering);
		$input->set('filter_order_Dir', $direction);
		

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

	/**
	 * Save method.
	 *
	 * @return boolean	True on success false on failure.
	 */

	public function save()
	{
		JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR.'/components/com_users/models');
		$model = JModelLegacy::getInstance('Group', 'UsersModel');
		$table = $this->getTable();
		$data = $this->getState('data');
		$this->onBeforeSave($data, $table);
		$model->save($data);
		$this->setState('id', $table->id);
		$this->onAfterSave($data, $table);
		return true;
	}

	/**
	 * Delete method.
	 *
	 * @return boolean	True on success false on failure.
	 */

	public function delete()
	{
		if (!JFactory::getUser()->authorise('core.admin', 'com_users'))
		{
			jexit(JText::_('JERROR_ALERTNOAUTHOR'));
		}
		JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR.'/components/com_users/models');
		$model = JModelLegacy::getInstance('Group', 'UsersModel');
		$table = $this->getTable();
		$id = $this->getState('id');
		$this->onBeforeDelete($table);
		$array = array($id);
		if (!$model->delete($array))
		{
			$this->setError($model->getError());
			return false;
		}
		$this->onAfterDelete($table);
		return true;
	}

	/**
	 * Method to get a table object, load it if necessary.
	 *
	 * @param   string  $name     The table name. Optional.
	 * @param   string  $prefix   The class prefix. Optional.
	 * @param   array   $options  Configuration array for model. Optional.
	 *
	 * @return  JTable  A JTable object
	 */
	public function getTable($name = 'Usergroup', $prefix = 'JTable', $options = array())
	{
		return parent::getTable($name, $prefix, $options);
	}

}
