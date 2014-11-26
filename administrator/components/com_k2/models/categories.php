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
require_once JPATH_ADMINISTRATOR.'/components/com_k2/tables/table.php';
require_once JPATH_ADMINISTRATOR.'/components/com_k2/resources/categories.php';
require_once JPATH_ADMINISTRATOR.'/components/com_k2/helpers/images.php';
require_once JPATH_ADMINISTRATOR.'/components/com_k2/classes/filesystem.php';

class K2ModelCategories extends K2Model
{

	private static $authorised = null;
	private static $cache = array(
		'roots' => array(),
		'trees' => array()
	);

	public function getRows()
	{

		// Get database
		$db = $this->getDBO();

		// Get query
		$query = $db->getQuery(true);

		// Select rows
		$query->select($db->quoteName('category').'.*')->from($db->quoteName('#__k2_categories', 'category'));

		// Join over the user
		$query->select($db->quoteName('author.name', 'authorName'));
		$query->leftJoin($db->quoteName('#__users', 'author').' ON '.$db->quoteName('author.id').' = '.$db->quoteName('category.created_by'));

		// Join over the user
		$query->select($db->quoteName('moderator.name', 'moderatorName'));
		$query->leftJoin($db->quoteName('#__users', 'moderator').' ON '.$db->quoteName('moderator.id').' = '.$db->quoteName('category.modified_by'));

		// Set query conditions
		$this->setQueryConditions($query);

		// Set query sorting
		$this->setQuerySorting($query);

		// Hook for plugins
		$this->onBeforeSetQuery($query, 'com_k2.categories.list');

		// Set the query
		$db->setQuery($query, (int)$this->getState('limitstart'), (int)$this->getState('limit'));

		// Get rows
		$data = $db->loadAssocList();

		// Generate K2 resources instances from the result data.
		$rows = $this->getResources($data, 'category');

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
		$query->select('COUNT(*)')->from($db->quoteName('#__k2_categories', 'category'));

		// Set query conditions
		$this->setQueryConditions($query);

		// Hook for plugins
		$this->onBeforeSetQuery($query, 'com_k2.categories.count');

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
		if ($this->getState('site'))
		{
			// Get authorised view levels
			$viewlevels = array_unique(JFactory::getUser()->getAuthorisedViewLevels());

			// Published items only
			$this->setState('state', 1);

			// Set state for access
			$this->setState('access', $viewlevels);

			// Language filter
			$application = JFactory::getApplication();
			if ($application->isSite() && $application->getLanguageFilter())
			{
				$language = JFactory::getLanguage();
				$query->where($db->quoteName('category.language').' IN ('.$db->quote($language->getTag()).', '.$db->quote('*').')');
			}
		}
		$query->where($db->quoteName('category.id').' != 1');
		if ($this->getState('language'))
		{
			$query->where($db->quoteName('category.language').' = '.$db->quote($this->getState('language')));
		}
		if (is_numeric($this->getState('state')))
		{
			$query->where($db->quoteName('category.state').' = '.(int)$this->getState('state'));
		}
		if ($this->getState('access'))
		{
			$query->where($db->quoteName('category.access').' = '.(int)$this->getState('access'));
		}
		if ($this->getState('id'))
		{
			$id = $this->getState('id');
			if (is_array($id))
			{
				JArrayHelper::toInteger($id);
				$query->where($db->quoteName('category.id').' IN ('.implode(',', $id).')');
			}
			else
			{
				$query->where($db->quoteName('category.id').' = '.(int)$id);
			}
		}
		if ($this->getState('alias'))
		{
			$query->where($db->quoteName('category.alias').' = '.$db->quote($this->getState('alias')));
		}
		if ($this->getState('search'))
		{
			$search = JString::trim($this->getState('search'));
			$search = JString::strtolower($search);
			if ($search)
			{
				$search = $db->escape($search, true);
				$query->where('( LOWER('.$db->quoteName('category.title').') LIKE '.$db->Quote('%'.$search.'%', false).' 
				OR '.$db->quoteName('category.id').' = '.(int)$search.' 
				OR LOWER('.$db->quoteName('category.description').') LIKE '.$db->Quote('%'.$search.'%', false).')');
			}
		}
		if ($this->getState('root'))
		{
			$rootId = (int)$this->getState('root');
			if (!isset(self::$cache['roots'][$rootId]))
			{
				$root = $this->getTable();
				$root->load($rootId);
				self::$cache['roots'][$rootId] = $root;
			}
			else
			{
				$root = self::$cache['roots'][$rootId];
			}

			$query->where($db->quoteName('category.lft').' >= '.(int)$root->lft);
			$query->where($db->quoteName('category.rgt').' <= '.(int)$root->rgt);
		}
		if ($this->getState('parent'))
		{
			$query->where($db->quoteName('category.parent_id').' = '.(int)$this->getState('parent'));
		}
	}

	private function setQuerySorting(&$query)
	{
		$sorting = $this->getState('sorting');
		switch($sorting)
		{
			default :
			case 'ordering' :
				$ordering = 'category.lft';
				$direction = 'ASC';
				break;
			case 'id' :
			case 'id.reverse' :
				$ordering = 'category.id';
				$direction = $sorting == 'id' ? 'ASC' : 'DESC';
				break;
			case 'title' :
			case 'title.reverse' :
				$ordering = 'category.title';
				$direction = $sorting == 'title' ? 'ASC' : 'DESC';
				break;
			case 'state' :
			case 'state.reverse' :
				$ordering = 'category.state';
				$direction = $sorting == 'state' ? 'ASC' : 'DESC';
				break;
			case 'author' :
			case 'author.reverse' :
				$ordering = 'authorName';
				$direction = $sorting == 'author' ? 'ASC' : 'DESC';
				break;
			case 'moderator' :
			case 'moderator.reverse' :
				$ordering = 'moderatorName';
				$direction = $sorting == 'moderator' ? 'ASC' : 'DESC';
				break;
			case 'access' :
			case 'access.reverse' :
				$ordering = 'category.access';
				$direction = $sorting == 'access' ? 'ASC' : 'DESC';
				break;
			case 'created' :
			case 'created.reverse' :
				$ordering = 'category.created';
				$direction = $sorting == 'created' ? 'ASC' : 'DESC';
				break;
			case 'modified' :
			case 'modified.reverse' :
				$order = 'category.modified';
				$direction = $sorting == 'modified' ? 'ASC' : 'DESC';
				break;
			case 'language' :
			case 'language.reverse' :
				$ordering = 'category.language';
				$direction = $sorting == 'language' ? 'ASC' : 'DESC';
				break;
		}

		// Append sorting
		$db = $this->getDbo();
		$query->order($db->quoteName($ordering).' '.$direction);

	}

	/**
	 * getAuthorisedCategories method.
	 *
	 * @return array
	 */
	public static function getAuthorised()
	{

		if (is_null(self::$authorised))
		{
			// Get database
			$db = JFactory::getDbo();

			// Get authorised view levels
			$viewlevels = array_unique(JFactory::getUser()->getAuthorisedViewLevels());

			// Get query
			$query = $db->getQuery(true);

			// Build query
			$query->select($db->quoteName('id'))->from('#__k2_categories')->where($db->quoteName('state').' = 1')->where($db->quoteName('access').' IN ('.implode(',', $viewlevels).')');

			// Set query
			$db->setQuery($query);

			// Load result
			self::$authorised = $db->loadColumn();
		}

		return self::$authorised;
	}

	/**
	 * getCategoryFilter method.
	 *
	 * @return array
	 */
	public static function getCategoryFilter($categories = null, $recursive = false, $access = false)
	{
		$filter = K2ModelCategories::getAuthorised();
		if ($categories)
		{
			if (!is_array($categories))
			{
				$categories = (array)$categories;
			}
			$categories = array_filter($categories);
			if (count($categories))
			{
				if ($recursive)
				{
					$children = array();
					$model = K2Model::getInstance('Categories');
					foreach ($categories as $categoryId)
					{
						$key = (string)$access.'|'.(string)$categoryId;
						if (!isset(self::$cache['trees'][$key]))
						{
							$model->setState('site', $access);
							$model->setState('root', $categoryId);
							$rows = $model->getRows();
							self::$cache['trees'][$key] = $rows;
						}
						else
						{
							$rows = self::$cache['trees'][$key];
						}

						foreach ($rows as $row)
						{
							$children[] = $row->id;
						}
					}
					$categories = array_merge($categories, $children);
					$categories = array_unique($categories);
				}
				if ($access)
				{
					$filter = array_intersect($categories, K2ModelCategories::getAuthorised());
				}
				else
				{
					$filter = $categories;
				}
			}
		}
		return array_unique($filter);
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
		// User
		$user = JFactory::getUser();

		// Create action
		if (!$table->id)
		{
			// Detect the context
			$context = (isset($data['parent_id']) && $data['parent_id']) ? 'com_k2.category.'.$data['parent_id'] : 'com_k2';

			// If the user has not the permission to create category stop the processs. Otherwise handle the category state
			if (!$user->authorise('k2.category.create', $context))
			{
				$this->setError(JText::_('K2_YOU_ARE_NOT_AUTHORIZED_TO_PERFORM_THIS_OPERATION'));
				return false;
			}
			else
			{
				// User can create the category but cannot edit it's state so we set the category state to 0
				if (!$user->authorise('k2.category.edit.state', $context))
				{
					$data['state'] = 0;
				}
			}

		}
		// Edit action
		if ($table->id)
		{
			// Detect the context
			$context = 'com_k2.category.'.$table->id;

			// Actions
			$canEdit = $user->authorise('k2.category.edit', $context) || ($user->authorise('k2.item.edit.own', $context) && $user->id == $table->created_by);
			$canEditState = $user->authorise('k2.item.edit.state', $context);

			// User cannot edit the category neither it's state. Stop the process
			if (!$canEdit && !$canEditState)
			{
				$this->setError(JText::_('K2_YOU_ARE_NOT_AUTHORIZED_TO_PERFORM_THIS_OPERATION'));
				return false;
			}
			else
			{
				// Store the input states values in case we need them after
				$state = isset($data['state']) ? $data['state'] : $table->state;

				// User cannot edit the item. Reset the input
				if (!$canEdit)
				{
					$data = array();
					$data['id'] = $table->id;
				}

				// Set the states values depending on permissions
				$data['state'] = ($canEditState) ? $state : $table->state;
			}
		}

		// Get timezone
		$configuration = JFactory::getConfig();
		$userTimeZone = $user->getParam('timezone', $configuration->get('offset'));

		// Handle date data
		if ($data['id'] && isset($data['createdDate']))
		{
			// Convert date to UTC
			$createdDateTime = $data['createdDate'].' '.$data['createdTime'];
			$data['created'] = JFactory::getDate($createdDateTime, $userTimeZone)->toSql();
		}

		// Update category location
		if ((isset($data['parent_id']) && $table->parent_id != $data['parent_id']) || !$data['id'])
		{
			$table->setLocation($data['parent_id'], 'last-child');
		}
		if ($this->getState('patch'))
		{
			$table->setLocation($data['parent_id'], 'first-child');
		}

		// Image
		if (isset($data['image']))
		{
			// Detect if category has an image
			$data['image']['flag'] = (int)(!$data['image']['remove'] && ($data['image']['id'] || $data['image']['temp']));

			// Store the input of the image to state
			$this->setState('image', $data['image']);

			// Unset values we do not want to get stored to our database
			unset($data['image']['path']);
			unset($data['image']['id']);
			unset($data['image']['temp']);
			unset($data['image']['remove']);

			// Encode the value to JSON
			$data['image'] = json_encode($data['image']);
		}

		// Extra fields
		if (isset($data['extra_fields']))
		{
			$data['extra_fields'] = json_encode($data['extra_fields']);
		}

		// Add flag for moving a category to trash
		if (isset($data['state']) && $data['state'] == -1 && $table->state != -1)
		{
			$this->setState('trash', true);
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

		// Image
		if ($image = $this->getState('image'))
		{
			K2HelperImages::update('category', $image, $table);
		}

		// Clean up any temporary files
		K2HelperImages::purge('category');

		// Handle trash action
		if ($this->getState('trash'))
		{
			// Trash all subcategories and items
			$categories = $this->getTable();
			$tree = $categories->getTree($table->id);
			foreach ($tree as $category)
			{
				if ($category->id != $table->id)
				{
					$subcategory = $this->getTable();
					$subcategory->load($category->id);
					$subcategory->state = -1;
					$subcategory->store();
				}
				$this->trashItems($category->id);
			}
		}

		if (!$table->rebuildPath($table->id))
		{
			$this->setError($table->getError());
			return false;
		}

		if (!$table->rebuild($table->id, $table->lft, $table->level, $table->path))
		{
			$this->setError($table->getError());
			return false;
		}

		// Handle associations
		if (JLanguageAssociations::isEnabled() && isset($data['associations']))
		{
			// Get associations input
			$associations = $data['associations'];

			// Sanitize array with empty values
			foreach ($associations as $languageTag => $itemId)
			{
				if (empty($itemId))
				{
					unset($associations[$languageTag]);
				}
			}

			// Ensure that the item does not have associations when it's language is set to "All"
			if ($table->language == '*' && !empty($associations))
			{
				$associations = array();
			}

			// Add current item to associations
			$associations[$table->language] = $table->id;

			// Deleting old association for affected items
			$db = JFactory::getDbo();
			$query = $db->getQuery(true)->delete($db->quoteName('#__associations'))->where($db->quoteName('context').' = '.$db->quote('com_k2.category'))->where($db->quoteName('id').' IN ('.implode(',', $associations).')');
			$db->setQuery($query);
			$db->execute();

			if ($table->language != '*' && count($associations))
			{
				// Adding new association for these items
				$key = md5(json_encode($associations));
				$query->clear()->insert($db->quoteName('#__associations'));
				foreach ($associations as $id)
				{
					$query->values((int)$id.','.$db->quote('com_k2.category').','.$db->quote($key));
				}
				$db->setQuery($query);
				$db->execute();
			}
		}

		return true;
	}

	/**
	 * Close method.
	 *
	 * @return boolean	True on success false on failure.
	 */

	public function close()
	{
		// Clean up any temporary images
		K2HelperImages::purge('category');
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
		$user = JFactory::getUser();
		if (!$user->authorise('k2.category.delete', 'com_k2.category.'.$table->id))
		{
			$this->setError(JText::_('K2_YOU_ARE_NOT_AUTHORIZED_TO_PERFORM_THIS_OPERATION'));
			return false;
		}

		// Check that the category is trashed
		if ($table->state != -1)
		{
			$this->setError(JText::_('K2_YOU_CAN_ONLY_DELETE_TRASHED_CATEGORIES'));
			return false;
		}

		// Check for categories that are not trashed in the tree
		$tree = $table->getTree($table->id);
		foreach ($tree as $category)
		{
			if ($category->state != -1)
			{
				$this->setError(JText::_('K2_COULD_NOT_DELETE_CATEGORY_BECAUSE_IT_CONTAINS_NON_TRASHED_CATEGORIES'));
				return false;
			}
		}

		// Ensure the category and it's children do not contain any items
		$model = K2Model::getInstance('Items');
		$model->setState('category', $table->id);
		$count = $model->countRows();
		if ($count)
		{
			$this->setError(JText::_('K2_COULD_NOT_DELETE_CATEGORY_BECAUSE_IT_CONTAINS_ITEMS'));
			return false;
		}

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
		// Delete item image
		K2HelperImages::remove('category', $table->id);

		return true;
	}

	public function saveOrder($ids, $ordering)
	{
		$user = JFactory::getUser();
		if (!$user->authorise('k2.category.edit', 'com_k2'))
		{
			$this->setError(JText::_('K2_YOU_ARE_NOT_AUTHORIZED_TO_PERFORM_THIS_OPERATION'));
			return false;
		}
		$table = $this->getTable();
		if (!$table->saveorder($ids, $ordering))
		{
			$this->setError($table->getError());
			return false;
		}
		return true;
	}

	public function trashItems($categoryId)
	{
		// Get database
		$db = $this->getDBO();

		// Get query
		$query = $db->getQuery(true);

		// Update
		$query->update($db->quoteName('#__k2_items'))->set($db->quoteName('state').' = -1')->where($db->quoteName('catid').' = '.(int)$categoryId);

		// Set query
		$db->setQuery($query);

		// Execute
		$db->execute();

	}

	public function getCopyData($id)
	{
		// Get params
		$params = JComponentHelper::getParams('com_k2');

		// Get source category
		$source = K2Categories::getInstance($id);

		// Get source category properties as data array. This array will be the input to the model.
		$data = get_object_vars($source);

		// It's a new category so reset some properties
		$data['id'] = '';
		$data['title'] = JText::_('K2_COPY_OF').' '.$data['title'];
		$data['alias'] = '';
		$data['extra_fields'] = json_decode($data['extra_fields']);
		$data['metadata'] = $data['metadata']->toString();
		$data['plugins'] = $data['plugins']->toString();
		$data['params'] = $data['params']->toString();

		// Handle image
		if (isset($data['image']) && isset($data['image']->id))
		{
			// If filesystem is not local then path is the URL
			$filesystem = $params->get('filesystem');
			$path = ($filesystem == 'Local' || !$filesystem) ? 'media/k2/categories/'.$data['image']->id.'.jpg' : $data['image']->url;
			$image = K2HelperImages::add('category', null, $path);
			$data['image'] = array(
				'id' => '',
				'temp' => $image->temp,
				'path' => '',
				'remove' => 0,
				'caption' => $data['image']->caption,
				'credits' => $data['image']->credits
			);
		}
		else
		{
			unset($data['image']);
		}

		// Return the input data
		return $data;
	}

}
