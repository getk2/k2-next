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
require_once JPATH_ADMINISTRATOR.'/components/com_k2/classes/filesystem.php';

class K2ModelAttachments extends K2Model
{
	public function getRows()
	{
		// Get database
		$db = $this->getDBO();

		// Get query
		$query = $db->getQuery(true);

		// Select rows
		$query->select('*')->from($db->quoteName('#__k2_attachments'));

		// Set query conditions
		$this->setQueryConditions($query);

		// Set query sorting
		$this->setQuerySorting($query);

		// Hook for plugins
		$this->onBeforeSetQuery($query, 'com_k2.attachments.list');

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
		$query->select('COUNT(*)')->from($db->quoteName('#__k2_attachments'));

		// Set query conditions
		$this->setQueryConditions($query);

		// Hook for plugins
		$this->setQueryConditions($query, 'com_k2.attachments.count');

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
			$query->where($db->quoteName('itemId').' = '.(int)$this->getState('itemId'));
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
					$ordering = 'id';
					$direction = 'DESC';
					break;
				case 'name' :
					$ordering = 'name';
					$direction = 'ASC';
					break;
				case 'downloads' :
					$ordering = 'downloads';
					$direction = 'DESC';
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

	public function download()
	{
		$db = $this->getDBO();
		$query = $db->getQuery(true);
		$query->update($db->quoteName('#__k2_attachments'))->set($db->quoteName('downloads').' = ('.$db->quoteName('downloads').' + 1)')->where('id = '.(int)$this->getState('id'));
		$db->setQuery($query);
		$db->execute();
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
		// Permissions check
		$itemId = $data['itemId'];

		// Check only for existing items
		if (is_numeric($itemId) && $itemId > 0)
		{
			require_once JPATH_ADMINISTRATOR.'/components/com_k2/resources/items.php';
			$item = K2Items::getInstance($data['itemId']);
			if (!$item->canEdit)
			{
				$this->setError(JText::_('K2_YOU_ARE_NOT_AUTHORIZED_TO_PERFORM_THIS_OPERATION'));
				return false;
			}
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
		// Permissions check
		if ($table->itemId > 0)
		{
			require_once JPATH_ADMINISTRATOR.'/components/com_k2/resources/items.php';
			$item = K2Items::getInstance($table->itemId);
			if (!$item->canEdit)
			{
				$this->setError(JText::_('K2_YOU_ARE_NOT_AUTHORIZED_TO_PERFORM_THIS_OPERATION'));
				return false;
			}
		}

		// Delete any associated files
		$this->deleteFile($table);

		return true;
	}

	public function deleteFile($table)
	{
		if ($table->file)
		{
			// Filesystem
			$filesystem = K2FileSystem::getInstance();

			$path = 'media/k2/attachments';
			if ($table->itemId)
			{
				$folder = $table->itemId;
				$key = $path.'/'.$folder.'/'.$table->file;
			}
			else
			{
				list($folder, $file) = explode('/', $table->file);
				$key = $path.'/'.$folder.'/'.$file;
			}

			if ($filesystem->has($key))
			{
				$filesystem->delete($key);
			}

			$keys = $filesystem->listKeys($path.'/'.$folder.'/');

			if (empty($keys['keys']) && $filesystem->has($path.'/'.$folder))
			{
				$filesystem->delete($path.'/'.$folder);
			}
		}
	}

}
