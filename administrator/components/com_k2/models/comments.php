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
require_once JPATH_ADMINISTRATOR.'/components/com_k2/models/categories.php';

class K2ModelComments extends K2Model
{
	public function getRows()
	{
		// Get database
		$db = $this->getDBO();

		// Get query
		$query = $db->getQuery(true);

		// Select rows
		$query->select($db->quoteName('comment').'.*')->from($db->quoteName('#__k2_comments', 'comment'));

		// Join with items
		$query->select($db->quoteName('item.title', 'itemTitle'));
		$query->select($db->quoteName('item.alias', 'itemAlias'));
		$query->select($db->quoteName('item.created_by', 'itemCreatedBy'));
		$query->select($db->quoteName('item.created_by_alias', 'itemCreatedByAlias'));
		$query->leftJoin($db->quoteName('#__k2_items', 'item').' ON '.$db->quoteName('comment.itemId').' = '.$db->quoteName('item.id'));

		// Join with categories
		$query->select($db->quoteName('category.id', 'categoryId'));
		$query->select($db->quoteName('category.title', 'categoryTitle'));
		$query->select($db->quoteName('category.alias', 'categoryAlias'));
		$query->leftJoin($db->quoteName('#__k2_categories', 'category').' ON '.$db->quoteName('category.id').' = '.$db->quoteName('item.catid'));

		// Set query conditions
		$this->setQueryConditions($query);

		// Set query sorting
		$this->setQuerySorting($query);

		// Hook for plugins
		$this->onBeforeSetQuery($query, 'com_k2.comments.list');

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
		$query->select('COUNT(*)')->from($db->quoteName('#__k2_comments', 'comment'));

		// Join with items
		$query->leftJoin($db->quoteName('#__k2_items', 'item').' ON '.$db->quoteName('comment.itemId').' = '.$db->quoteName('item.id'));

		// Join with categories
		$query->leftJoin($db->quoteName('#__k2_categories', 'category').' ON '.$db->quoteName('category.id').' = '.$db->quoteName('item.catid'));

		// Set query conditions
		$this->setQueryConditions($query);

		// Hook for plugins
		$this->onBeforeSetQuery($query, 'com_k2.comments.count');

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
			$query->where($db->quoteName('comment.itemId').' = '.(int)$this->getState('itemId'));
		}
		if (is_numeric($this->getState('state')))
		{
			$query->where($db->quoteName('comment.state').' = '.(int)$this->getState('state'));
		}
		if ($this->getState('id'))
		{
			$id = $this->getState('id');
			if ($this->getState('id.operator'))
			{
				$operator = $this->getState('id.operator');
			}
			else
			{
				$operator = '=';
			}
			$query->where($db->quoteName('comment.id').' '.$operator.' '.(int)$id);

		}
		if ($this->getState('search'))
		{
			$search = JString::trim($this->getState('search'));
			$search = JString::strtolower($search);
			if ($search)
			{
				$search = $db->escape($search, true);
				$query->where($db->quoteName('comment.text').' LIKE '.$db->Quote('%'.$search.'%', false));
			}
		}

		if ($this->getState('userId'))
		{
			$query->where($db->quoteName('comment.userId').' = '.(int)$this->getState('userId'));
		}

		if ($this->getState('filter.items'))
		{
			// Items should be published
			$query->where($db->quoteName('item.state').' = 1');

			// Check categories access level
			$filter = K2ModelCategories::getCategoryFilter($this->getState('category'), false, true);
			$query->where($db->quoteName('item.catid').' IN ('.implode(',', $filter).')');

			// Check item access level
			$viewlevels = array_unique(JFactory::getUser()->getAuthorisedViewLevels());
			$query->where($db->quoteName('item.access').' IN ('.implode(',', $viewlevels).')');

			// Check publish up/down
			$date = JFactory::getDate()->toSql();
			$query->where('('.$db->quoteName('item.publish_up').' = '.$db->Quote($db->getNullDate()).' OR '.$db->quoteName('item.publish_up').' <= '.$db->Quote($date).')');
			$query->where('('.$db->quoteName('item.publish_down').' = '.$db->Quote($db->getNullDate()).' OR '.$db->quoteName('item.publish_down').' >= '.$db->Quote($date).')');
		}
	}

	private function setQuerySorting(&$query)
	{
		$sorting = $this->getState('sorting');

		switch($sorting)
		{
			default :
			case 'id' :
				$ordering = 'comment.id';
				$direction = 'DESC';
				break;
			case 'id.asc' :
				$ordering = 'comment.id';
				$direction = 'ASC';
				break;
			case 'name' :
				$ordering = 'comment.name';
				$direction = 'ASC';
				break;
			case 'email' :
				$ordering = 'comment.email';
				$direction = 'ASC';
				break;
			case 'url' :
				$ordering = 'comment.url';
				$direction = 'ASC';
				break;
			case 'ip' :
				$ordering = 'comment.ip';
				$direction = 'ASC';
				break;
			case 'hostname' :
				$ordering = 'comment.hostname';
				$direction = 'ASC';
				break;
			case 'date' :
				$ordering = 'comment.date';
				$direction = 'DESC';
				break;
			case 'state' :
				$ordering = 'comment.state';
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
		// Get application
		$application = JFactory::getApplication();

		// Params
		$params = JComponentHelper::getParams('com_k2');

		// Get user
		$user = JFactory::getUser();

		// New comments
		if (!$table->id)
		{
			// New comments only allowed in frontend
			if ($application->isAdmin())
			{
				$this->setError(JText::_('K2_YOU_ARE_NOT_AUTHORIZED_TO_PERFORM_THIS_OPERATION'));
				return false;
			}

			// Get the item to check permissions
			$model = K2Model::getInstance('Items');
			$model->setState('id', $data['itemId']);
			$item = $model->getRow();

			// First check that user can actualy view the specific item
			if (!$item->checkSiteAccess())
			{
				$this->setError(JText::_('K2_YOU_ARE_NOT_AUTHORIZED_TO_PERFORM_THIS_OPERATION'));
				return false;
			}

			// Check that the current user can comment on this category
			if (!$user->authorise('k2.comment.create', 'com_k2.category.'.$item->catid))
			{
				$this->setError(JText::_('K2_YOU_ARE_NOT_AUTHORIZED_TO_PERFORM_THIS_OPERATION'));
				return false;
			}

			// Text is required for both guests and authenticated users
			if (trim($data['text']) == '')
			{
				$this->setError(JText::_('K2_YOU_NEED_TO_FILL_IN_ALL_REQUIRED_FIELDS'));
				return false;
			}

			// Validate user data for guests
			if ($user->guest)
			{
				// Check that the required fields have been set
				if (trim($data['name']) == '' || trim($data['email']) == '')
				{
					$this->setError(JText::_('K2_YOU_NEED_TO_FILL_IN_ALL_REQUIRED_FIELDS'));
					return false;
				}

				// Check that the email is valid
				if (!JMailHelper::isEmailAddress($data['email']))
				{
					$this->setError(JText::_('K2_INVALID_EMAIL_ADDRESS'));
					return false;
				}

				// Check for spoofing
				$model = K2Model::getInstance('Users');
				$spoofing = $model->checkSpoofing(trim($data['name']), $data['email']);
				if ($spoofing > 0)
				{
					$this->setError(JText::_('K2_THE_NAME_OR_EMAIL_ADDRESS_YOU_TYPED_IS_ALREADY_IN_USE'));
					return false;
				}

				// Enforce some data for guests
				$data['userId'] = 0;

			}
			else
			{
				// Enforce some data for authenticated users
				$data['userId'] = $user->id;
				$data['name'] = $user->name;
				$data['email'] = $user->email;
			}

			// @TODO Check captcha depending on settings

			// Everything seems fine, lets enforce the common variables
			$data['ip'] = $_SERVER['REMOTE_ADDR'];
			$data['hostname'] = gethostbyaddr($_SERVER['REMOTE_ADDR']);
			$data['date'] = JFactory::getDate()->toSql();
			$data['state'] = $params->get('commentsPublishing') ? 1 : 0;

			// Set a variable to indicate that this was a new comment
			$this->setState('isNew', true);

		}
		// Edit existing comments
		else
		{
			// Check permissions
			$canEditAnyComment = $user->authorise('k2.comment.edit', 'com_k2');
			if (!$canEditAnyComment)
			{
				$this->setError(JText::_('K2_YOU_ARE_NOT_AUTHORIZED_TO_PERFORM_THIS_OPERATION'));
				return false;
			}

			// Edit is only allowed for comment text and state. The rest fields should not be edited.
			$data['id'] = $table->id;
			$data['itemId'] = $table->itemId;
			$data['userId'] = $table->userId;
			$data['name'] = $table->name;
			$data['date'] = $table->date;
			$data['email'] = $table->email;
			$data['url'] = $table->url;
			$data['ip'] = $table->ip;
			$data['hostname'] = $table->hostname;

		}

		return true;
	}

	/**
	 * onAfterSave method. Hook for chidlren model to save extra data.
	 *
	 * @param   array  $data     The data passed to the save function.
	 * @param   JTable  $table   The table object.
	 *
	 * @return boolean
	 */

	protected function onAfterSave(&$data, $table)
	{
		// Increase item comments counter for new comments
		if ($this->getState('isNew'))
		{
			$statistics = K2Model::getInstance('Statistics', 'K2Model');
			$statistics->increaseItemCommentsCounter($table->itemId);
			// Increase user comments counter for new comments
			if ($table->userId > 0)
			{
				$statistics->increaseUserCommentsCounter($table->userId);
			}

			// Throw error if auto-publishing is disabled
			if (!$table->state)
			{
				$this->setError(JText::_('K2_COMMENT_ADDED_AND_WAITING_FOR_APPROVAL'));
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
		// User
		$user = JFactory::getUser();

		// Permissions check
		$canEditAnyComment = $user->authorise('k2.comment.edit', 'com_k2');
		if (!$canEditAnyComment)
		{
			$this->setError(JText::_('K2_YOU_ARE_NOT_AUTHORIZED_TO_PERFORM_THIS_OPERATION'));
			return false;
		}

		// Set the itemId to a state because we need it after delete
		$this->setState('itemId', $table->itemId);
		$this->setState('userId', $table->userId);

		return true;
	}

	/**
	 * onAfterDelete method. Hook for chidlren model.
	 *
	 * @param   JTable  $table     	The table object.
	 *
	 * @return boolean
	 */

	protected function onAfterDelete($table)
	{
		// Decrease comments counter
		$statistics = K2Model::getInstance('Statistics', 'K2Model');
		$statistics->decreaseItemCommentsCounter($this->getState('itemId'));

		// Increase user comments counter for new comments
		if ($this->getState('isNew') && $this->getState('userId') > 0)
		{
			$statistics->decreaseUserCommentsCounter($this->getState('userId'));
		}

		// Return
		return true;

	}

}
