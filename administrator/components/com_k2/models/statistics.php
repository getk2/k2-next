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

class K2ModelStatistics extends K2Model
{
	public function increaseUserItemsCounter($userId)
	{
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
		$query->set($db->quoteName('items').' = ('.$db->quoteName('items').' - 1)');
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
		$query->set($db->quoteName('comments').' = ('.$db->quoteName('comments').' - 1)');
		$query->where($db->quoteName('itemId').' = '.(int)$itemId);
		$db->setQuery($query);
		$db->execute();

	}

	public function increaseUserCommentsCounter($userId)
	{
		// Get database
		$db = $this->getDBO();

		// Get query
		$query = $db->getQuery(true);

		// Update
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
		$query->set($db->quoteName('comments').' = ('.$db->quoteName('comments').' - 1)');
		$query->where($db->quoteName('userId').' = '.(int)$userId);
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

}
