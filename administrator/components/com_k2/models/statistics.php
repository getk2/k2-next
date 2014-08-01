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

class K2ModelStatistics extends K2Model
{
	public function increaseUserItemsCounter($userId)
	{
		// If the entry does not exist, create it
		if (!$this->userEntryExists($userId))
		{
			$this->createUserEntry($userId);
		}

		// Get database
		$db = $this->getDBO();

		// Get query
		$query = $db->getQuery(true);

		// Update
		$query->update('#__k2_users_stats');
		$query->set($db->quoteName('items').' = ('.$db->quoteName('items').' + 1)');
		$query->where($db->quoteName('userId').' = '.(int)$userId);
		$db->setQuery($query);
		$db->execute();

	}

	public function decreaseUserItemsCounter($userId)
	{
		// Get database
		$db = $this->getDBO();

		// Get query
		$query = $db->getQuery(true);

		// Update
		$query->update('#__k2_users_stats');
		$query->set($db->quoteName('items').' = CASE WHEN '.$db->quoteName('items').' > 0 THEN ('.$db->quoteName('items').' - 1) ELSE 0 END');
		$query->where($db->quoteName('userId').' = '.(int)$userId);
		$db->setQuery($query);
		$db->execute();

	}

	public function increaseItemCommentsCounter($itemId)
	{
		// Get database
		$db = $this->getDBO();

		// Get query
		$query = $db->getQuery(true);

		// Update
		$query->update('#__k2_items_stats');
		$query->set($db->quoteName('comments').' = ('.$db->quoteName('comments').' + 1)');
		$query->where($db->quoteName('itemId').' = '.(int)$itemId);
		$db->setQuery($query);
		$db->execute();

	}

	public function decreaseItemCommentsCounter($itemId)
	{
		// Get database
		$db = $this->getDBO();

		// Get query
		$query = $db->getQuery(true);

		// Update
		$query->update('#__k2_items_stats');
		$query->set($db->quoteName('comments').' = CASE WHEN '.$db->quoteName('comments').' > 0 THEN ('.$db->quoteName('comments').' - 1) ELSE 0 END');
		$query->where($db->quoteName('itemId').' = '.(int)$itemId);
		$db->setQuery($query);
		$db->execute();

	}

	public function increaseUserCommentsCounter($userId)
	{
		// If the entry does not exist, create it
		if (!$this->userEntryExists($userId))
		{
			$this->createUserEntry($userId);
		}

		// Get database
		$db = $this->getDBO();

		// Get query
		$query = $db->getQuery(true);

		// Update entry counter
		$query = $db->getQuery(true);
		$query->update('#__k2_users_stats');
		$query->set($db->quoteName('comments').' = ('.$db->quoteName('comments').' + 1)');
		$query->where($db->quoteName('userId').' = '.(int)$userId);
		$db->setQuery($query);
		$db->execute();

	}

	public function decreaseUserCommentsCounter($userId)
	{
		// Get database
		$db = $this->getDBO();

		// Get query
		$query = $db->getQuery(true);

		// Update
		$query->update('#__k2_users_stats');
		$query->set($db->quoteName('comments').' = CASE WHEN '.$db->quoteName('comments').' > 0 THEN ('.$db->quoteName('comments').' - 1) ELSE 0 END');
		$query->where($db->quoteName('userId').' = '.(int)$userId);
		$db->setQuery($query);
		$db->execute();

	}

	public function increaseItemHitsCounter($itemId)
	{
		// Get database
		$db = $this->getDBO();

		// Get query
		$query = $db->getQuery(true);

		// Update
		$query->update('#__k2_items_stats');
		$query->set($db->quoteName('hits').' = ('.$db->quoteName('hits').' + 1)');
		$query->where($db->quoteName('itemId').' = '.(int)$itemId);
		$db->setQuery($query);
		$db->execute();

	}

	public function decreaseItemHitsCounter($itemId)
	{
		// Get database
		$db = $this->getDBO();

		// Get query
		$query = $db->getQuery(true);

		// Update
		$query->update('#__k2_items_stats');
		$query->set($db->quoteName('hits').' = CASE WHEN '.$db->quoteName('hits').' > 0 THEN ('.$db->quoteName('hits').' - 1) ELSE 0 END');
		$query->where($db->quoteName('itemId').' = '.(int)$itemId);
		$db->setQuery($query);
		$db->execute();

	}

	public function resetItemHitsCounter($itemId)
	{
		// Get database
		$db = $this->getDBO();

		// Get query
		$query = $db->getQuery(true);

		// Update
		$query->update('#__k2_items_stats');
		$query->set($db->quoteName('hits').' = 0');
		$query->where($db->quoteName('itemId').' = '.(int)$itemId);
		$db->setQuery($query);
		$db->execute();
	}

	public function createItemEntry($itemId)
	{
		$db = $this->getDbo();
		$query = $db->getQuery(true);
		$query->insert($db->quoteName('#__k2_items_stats'))->set($db->quoteName('itemId').' = '.(int)$itemId);
		$db->setQuery($query);
		$db->execute();
	}

	public function createUserEntry($userId)
	{
		$db = $this->getDbo();
		$query = $db->getQuery(true);
		$query->insert($db->quoteName('#__k2_users_stats'))->set($db->quoteName('userId').' = '.(int)$userId);
		$db->setQuery($query);
		$db->execute();
	}

	public function deleteItemEntry($itemId)
	{
		$db = $this->getDBO();
		$query = $db->getQuery(true);
		$query->delete($db->quoteName('#__k2_items_stats'))->where($db->quoteName('itemId').' = '.(int)$itemId);
		$db->setQuery($query);
		$db->execute();
	}

	public function deleteUserEntry($userId)
	{
		$db = $this->getDBO();
		$query = $db->getQuery(true);
		$query->delete($db->quoteName('#__k2_users_stats'))->where($db->quoteName('userId').' = '.(int)$userId);
		$db->setQuery($query);
		$db->execute();
	}

	public function itemEntryExists($itemId)
	{
		$db = $this->getDbo();
		$query = $db->getQuery(true);
		$query->select($db->quoteName('itemId'));
		$query->from($db->quoteName('#__k2_items_stats'));
		$query->where($db->quoteName('itemId').' = '.(int)$itemId);
		$db->setQuery($query);
		$exists = $db->loadColumn();
		return $exists;
	}

	public function userEntryExists($userId)
	{
		$db = $this->getDbo();
		$query = $db->getQuery(true);
		$query->select($db->quoteName('userId'));
		$query->from($db->quoteName('#__k2_users_stats'));
		$query->where($db->quoteName('userId').' = '.(int)$userId);
		$db->setQuery($query);
		$exists = $db->loadColumn();
		return $exists;
	}

}
