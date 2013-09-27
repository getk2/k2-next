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
		$query->select('*')->from($db->quoteName('#__k2_tags'));

		// Set query conditions
		$this->setQueryConditions($query);

		// Append sorting
		if ($this->getState('sorting'))
		{
			$query->order($this->getState('sorting'));
		}

		// Hook for plugins
		$this->onBeforeSetQuery($query, 'com_k2.tags.list');

		// Set the query
		$db->setQuery($query, (int)$this->getState('limitstart'), (int)$this->getState('limit'));

		// Get rows
		$data = $db->loadAssocList();

		// Generate K2 resources instances from the result data.
		$rows = $this->getResources($data, 'tag');

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
		$query->select('COUNT(*)')->from($db->quoteName('#__k2_tags'));

		// Set query conditions
		$this->setQueryConditions($query);

		// Hook for plugins
		$this->setQueryConditions($query, 'com_k2.tags.count');

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
			$query->where($db->quoteName('published').' = '.(int)$this->getState('published'));
		}
		if ($this->getState('id'))
		{
			$id = $this->getState('id');
			if (is_array($id))
			{
				JArrayHelper::toInteger($id);
				$query->where($db->quoteName('id').' IN '.$id);
			}
			else
			{
				$query->where($db->quoteName('id').' = '.(int)$id);
			}
		}
		if ($this->getState('alias'))
		{
			$query->where($db->quoteName('alias').' = '.$db->quote($this->getState('alias')));
		}
		if ($this->getState('search'))
		{
			$search = JString::trim($this->getState('search'));
			$search = JString::strtolower($search);
			if ($search)
			{
				$search = $db->escape($search, true);
				$query->where('( LOWER('.$db->quoteName('name').'.) LIKE '.$db->Quote('%'.$search.'%', false).' 
				OR '.$db->quoteName('id').' = '.(int)$search.')  
				OR LOWER('.$db->quoteName('alias').'.) LIKE '.$db->Quote('%'.$search.'%', false).')');
			}
		}
	}

}
