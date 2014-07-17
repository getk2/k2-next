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
		$input = JFactory::getApplication()->input;
		JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR.'/components/com_users/models');
		$model = JModelLegacy::getInstance('Groups', 'UsersModel');

		switch($this->getState('sorting'))
		{
			default :
			case '' :
				$ordering = 'a.lft';
				$direction = 'asc';
				break;
			case 'id' :
				$ordering = 'a.id';
				$direction = 'asc';
				break;
			case 'title' :
				$ordering = 'a.title';
				$direction = 'asc';
				break;
			case 'id.reverse' :
				$ordering = 'a.id';
				$direction = 'desc';
				break;
			case 'title.reverse' :
				$ordering = 'a.title';
				$direction = 'desc';
				break;
		}

		$input->set('limitstart', $this->getState('limitstart'));
		$input->set('filter_order', $ordering);
		$input->set('filter_order_Dir', $direction);

		$model->setState('list.ordering', $ordering);
		$model->setState('list.direction', $direction);

		$model->setState('list.start', $this->getState('limitstart'));
		$model->setState('list.limit', $this->getState('limit'));

		$input->set('filter_search', $this->getState('search'));
		$model->setState('filter.search', $this->getState('search'));

		$data = $model->getItems();

		// Reset input
		$input->set('limitstart', '');
		$input->set('filter_order', '');
		$input->set('filter_order_Dir', '');
		$input->set('filter_search', '');

		// Generate K2 resources instances from the result data.
		$rows = $this->getResources($data);

		// Return rows
		return (array)$rows;
	}

	public function countRows()
	{
		JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR.'/components/com_users/models');
		$input = JFactory::getApplication()->input;
		$model = JModelLegacy::getInstance('Groups', 'UsersModel');
		$input->set('filter_search', $this->getState('search'));
		$model->setState('filter.search', $this->getState('search'));
		$total = $model->getTotal();

		// Return the result
		return (int)$total;
	}

	/**
	 * Save method.
	 *
	 * @return boolean	True on success false on failure.
	 */

	public function save()
	{
		$user = JFactory::getUser();
		if (!$user->authorise('core.admin', 'com_users'))
		{
			$this->setError(JText::_('K2_YOU_ARE_NOT_AUTHORIZED_TO_PERFORM_THIS_OPERATION'));
			return false;
		}
		JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR.'/components/com_users/models');
		$model = JModelLegacy::getInstance('Group', 'UsersModel');
		$table = $this->getTable();
		$data = $this->getState('data');
		$this->onBeforeSave($data, $table);
		$model->save($data);
		$this->setState('id', $model->getState('group.id'));
		$this->onAfterSave($data, $table);
		return true;
	}

	/**
	 * onAfterSave method. Hook for chidlren model to save extra data.
	 *
	 * @return void
	 */

	protected function onAfterSave(&$data, $table)
	{

		if (isset($data['rules']))
		{
			$groupId = $this->getState('id');
			$model = K2Model::getInstance('Categories', 'K2Model');
			$categories = $model->getRows();
			foreach ($data['rules']['assets'] as $assetId)
			{
				$asset = JTable::getInstance('Asset');
				$asset->load($assetId);
				$rules = json_decode($asset->rules);

				foreach ($data['rules']['actions'][$assetId] as $action => $value)
				{
					$rule = isset($rules->$action) ? (array)$rules->$action : array();
					$newRule = array();
					foreach ($rule as $group => $allow)
					{
						$newRule[(int)$group] = $allow;
					}
					if (!is_numeric($value))
					{
						unset($newRule[(int)$groupId]);
					}
					else
					{
						$newRule[(int)$groupId] = (int)$value;
					}
					$rule = $newRule;
					$rules->$action = (object)$rule;
					$asset->rules = json_encode($rules);
					$asset->store();
				}
			}

		}

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
			$this->setError(JText::_('K2_YOU_ARE_NOT_AUTHORIZED_TO_PERFORM_THIS_OPERATION'));
			return false;
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

	public function getGroupPermissions()
	{
		// Get actions
		$actions = JAccess::getActionsFromFile(JPATH_ADMINISTRATOR.'/components/com_k2/access.xml', "/access/section[@name='category']/");

		// Get database
		$db = $this->getDBO();

		// Get query
		$query = $db->getQuery(true);

		// Select rows
		$query->select($db->quoteName('rules'));
		$query->from($db->quoteName('#__assets'));
		$query->where($db->quoteName('name').' LIKE '.$db->quote('%'.$db->escape('root.').'%'));
		$query->order($db->quoteName('lft').' ASC');

		// Set query
		$db->setQuery($query);

		// Get result
		$rootRules = json_decode($db->loadColumn());
		$groupId = $this->getState('id');
		$isSuperUsersGroup = isset($rootRules['core.admin']->$groupId) && $rootRules['core.admin']->$groupId > 0;

		// Get query
		$query = $db->getQuery(true);

		// Select rows
		$query->select('*');
		$query->from($db->quoteName('#__assets'));
		$query->where($db->quoteName('name').' LIKE '.$db->quote('%'.$db->escape('com_k2.category').'%'));
		$query->order($db->quoteName('lft').' ASC');

		// Set query
		$db->setQuery($query);

		// Get rows
		$categories = $db->loadObjectList();

		foreach ($categories as $category)
		{
			$category->rules = (array)json_decode($category->rules);
			$category->values = array();
			foreach ($actions as $action)
			{
				$category->values[$action->name] = (isset($category->rules[$action->name]->$groupId) && $category->rules[$action->name]->$groupId > 0) || $isSuperUsersGroup;
			}
		}

		// Return
		$result = new stdClass;
		$result->actions = $actions;
		$result->categories = $categories;
		return $result;
	}

}
