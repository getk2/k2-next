<?php
/**
 * @version		3.0.0b
 * @package		K2
 * @author		JoomlaWorks http://www.joomlaworks.net
 * @copyright	Copyright (c) 2006 - 2014 JoomlaWorks Ltd. All rights reserved.
 * @license		GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 */

// no direct access
defined('_JEXEC') or die ;

require_once JPATH_ADMINISTRATOR.'/components/com_k2/models/model.php';

class K2ModelRevisions extends K2Model
{
	public function getRows()
	{
		// Get database
		$db = $this->getDBO();

		// Get query
		$query = $db->getQuery(true);

		// Select rows
		$query->select($db->quoteName('revision').'.*');
		$query->from($db->quoteName('#__k2_revisions', 'revision'));

		// Set query conditions
		$this->setQueryConditions($query);

		// Set query sorting
		$this->setQuerySorting($query);

		// Hook for plugins
		$this->onBeforeSetQuery($query, 'com_k2.revisions.list');

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
		$query->select('COUNT(*)')->from($db->quoteName('#__k2_revisions', 'revision'));

		// Set query conditions
		$this->setQueryConditions($query);

		// Hook for plugins
		$this->onBeforeSetQuery($query, 'com_k2.revisions.count');

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
			$query->where($db->quoteName('revision.itemId').' = '.(int)$this->getState('itemId'));
		}
		if ($this->getState('id'))
		{
			$id = $this->getState('id');
			if (is_array($id))
			{
				JArrayHelper::toInteger($id);
				$query->where($db->quoteName('revision.id').' IN ('.implode(',', $id).')');
			}
			else
			{
				$query->where($db->quoteName('revision.id').' = '.(int)$id);
			}
		}
	}

	private function setQuerySorting(&$query)
	{
		$sorting = $this->getState('sorting');
		switch($sorting)
		{
			default :
			case 'id' :
				$ordering = 'id';
				$direction = 'DESC';
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
		$data['userId'] = JFactory::getUser()->id;
		$data['date'] = JFactory::getDate()->toSql();
		return true;
	}

	/**
	 * onAfterSave method.
	 *
	 * @param   array  $data     The input data.
	 * @param   JTable  $table   The table object.
	 *
	 * @return boolean
	 */

	protected function onAfterSave(&$data, $table)
	{
		$params = JComponentHelper::getParams('com_k2');
		$itemId = (int)$table->itemId;

		if ($maximumRevisions = (int)$params->get('maxRevisions'))
		{
			// First count item revisions
			$this->setState('id', '');
			$this->setState('itemId', $itemId);
			$revisions = $this->countRows();

			// Revisions are more than the allowed limit. We need to purge.
			if ($revisions > $maximumRevisions)
			{
				// Get database
				$db = $this->getDbo();

				// Find the last id we want to delete
				$query = $db->getQuery(true);
				$query->select($db->quoteName('id'))->from($db->quoteName('#__k2_revisions'))->where($db->quoteName('itemId').' = '.$itemId)->order($db->quoteName('id').' DESC');
				$db->setQuery($query, $maximumRevisions, 1);
				$id = (int)$db->loadResult();

				// If we have found an id delete all previous revisions
				if ($id)
				{
					$query = $db->getQuery(true);
					$query->delete($db->quoteName('#__k2_revisions'))->where($db->quoteName('id').' <= '.$id)->where($db->quoteName('itemId').' = '.$itemId);
					$db->setQuery($query);
					$db->execute();
				}
			}
		}

		return true;
	}

	public function buildRevisionData($item)
	{
		$data = new stdClass;
		$data->title = $item->title;
		$data->introtext = $item->introtext;
		$data->fulltext = $item->fulltext;
		$data->tags = $item->tags;
		$data->extra_fields = $item->extra_fields;
		return $data;
	}

	public function computeDataHash($data)
	{
		return sha1(json_encode($data));
	}

	public function deleteItemRevisions($itemId)
	{
		// Get database
		$db = $this->getDbo();

		// Get query
		$query = $db->getQuery(true);

		// Build query
		$query->delete($db->quoteName('#__k2_revisions'))->where($db->quoteName('itemId').' = '.(int)$itemId);

		// Set query
		$db->setQuery($query);

		// Execute
		$db->execute();
	}

}
