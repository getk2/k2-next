<?php
/**
 * @version		3.0.0
 * @package		K2
 * @author		JoomlaWorks http://www.joomlaworks.net
 * @copyright	Copyright (c) 2006 - 2014 JoomlaWorks Ltd. All rights reserved.
 * @license		GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 */

// no direct access
defined('_JEXEC') or die ;

require_once JPATH_SITE.'/components/com_k2/helpers/route.php';
require_once JPATH_SITE.'/components/com_k2/helpers/utilities.php';

class ModK2UsersHelper
{
	public static function getUsers($params)
	{
		$model = K2Model::getInstance('Users');

		if ($params->get('source') == 'specific' && $params->get('userIDs'))
		{
			if (is_array($params->get('userIDs')))
			{
				$ids = $params->get('userIDs');
			}
			else
			{
				$ids = array();
				$ids[] = $params->get('userIDs');
			}
			$users = array();
			$model->setState('block', 0);
			foreach ($ids as $id)
			{
				$model->setState('id', $id);
				$users = array_merge($users, $model->getRows());
			}
		}
		else
		{
			$users = array();
			switch($params->get('filter', 'mostItems'))
			{

				case 'mostItems' :
					$model->setState('limit', $params->get('limit', 4));
					$rows = $model->getTopAuthors();
					foreach ($rows as $row)
					{
						$users[] = K2Users::getInstance($row->userId);
					}
					break;
				case 'mostPopularItems' :

					// Get database
					$db = JFactory::getDbo();

					// Get query
					$query = $db->getQuery(true);

					// Select statement
					$query->select('DISTINCT('.$db->quoteName('item.created_by').')');
					$query->from($db->quoteName('#__k2_items', 'item'));
					$query->where($db->quoteName('item.created_by_alias').' = '.$db->quote(''));
					$query->rightJoin($db->quoteName('#__k2_items_stats', 'stats').' ON '.$db->quoteName('stats.itemId').' = '.$db->quoteName('item.id'));

					// Sorting
					$query->order($db->quoteName('stats.hits').' DESC');

					// Set the query
					$db->setQuery($query, 0, (int)$params->get('limit', 4));

					// Get the result
					$rows = $db->loadObjectList();

					foreach ($rows as $row)
					{
						$users[] = K2Users::getInstance($row->created_by);
					}

					break;

				case 'mostCommentedItems' :
					// Get database
					$db = JFactory::getDbo();

					// Get query
					$query = $db->getQuery(true);

					// Select statement
					$query->select('DISTINCT('.$db->quoteName('item.created_by').')');
					$query->from($db->quoteName('#__k2_items', 'item'));
					$query->where($db->quoteName('item.created_by_alias').' = '.$db->quote(''));
					$query->rightJoin($db->quoteName('#__k2_items_stats', 'stats').' ON '.$db->quoteName('stats.itemId').' = '.$db->quoteName('item.id'));

					// Sorting
					$query->order($db->quoteName('stats.comments').' DESC');

					// Set the query
					$db->setQuery($query, 0, (int)$params->get('limit', 4));

					// Get the result
					$rows = $db->loadObjectList();

					foreach ($rows as $row)
					{
						$users[] = K2Users::getInstance($row->created_by);
					}
					break;
			}

		}

		foreach ($users as $user)
		{
			if ($params->get('userItemCount'))
			{
				$model = K2Model::getInstance('Items');
				$model->setState('site', true);
				$model->setState('author', $user->id);
				$model->setState('limit', $params->get('userItemCount'));
				$user->items = $model->getRows();
			}
			else
			{
				$user->items = array();
			}
		}

		return $users;

	}

}
