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
require_once JPATH_ADMINISTRATOR.'/components/com_k2/classes/filesystem.php';
require_once JPATH_ADMINISTRATOR.'/components/com_k2/resources/items.php';
require_once JPATH_ADMINISTRATOR.'/components/com_k2/helpers/images.php';
require_once JPATH_ADMINISTRATOR.'/components/com_k2/helpers/media.php';
require_once JPATH_ADMINISTRATOR.'/components/com_k2/helpers/galleries.php';
require_once JPATH_ADMINISTRATOR.'/components/com_k2/helpers/attachments.php';


class K2ModelItems extends K2Model
{
	public function getRows()
	{
		// Get database
		$db = $this->getDBO();

		// Get query
		$query = $db->getQuery(true);

		// Select rows
		$query->select($db->quoteName('item').'.*')->from($db->quoteName('#__k2_items', 'item'));

		// Join over the categories
		$query->select($db->quoteName('category.title', 'categoryName'));
		$query->select($db->quoteName('category.state', 'categoryState'));
		$query->select($db->quoteName('category.access', 'categoryAccess'));
		$query->select($db->quoteName('category.level', 'categoryLevel'));
		$query->select($db->quoteName('category.path', 'categoryPath'));
		$query->select($db->quoteName('category.params', 'categoryParams'));
		$query->leftJoin($db->quoteName('#__k2_categories', 'category').' ON '.$db->quoteName('category.id').' = '.$db->quoteName('item.catid'));

		// Join over the asset groups.
		$query->select($db->quoteName('assetGroup.title', 'viewLevel'));
		$query->leftJoin($db->quoteName('#__viewlevels', 'assetGroup').' ON '.$db->quoteName('assetGroup.id').' = '.$db->quoteName('item.access'));

		// Join over the author
		$query->select($db->quoteName('author.name', 'authorName'));
		$query->leftJoin($db->quoteName('#__users', 'author').' ON '.$db->quoteName('author.id').' = '.$db->quoteName('item.created_by'));

		// Join over the moderator
		$query->select($db->quoteName('moderator.name', 'moderatorName'));
		$query->leftJoin($db->quoteName('#__users', 'moderator').' ON '.$db->quoteName('moderator.id').' = '.$db->quoteName('item.modified_by'));

		// Join over the hits
		$query->select($db->quoteName('stats.hits', 'hits'));
		$query->leftJoin($db->quoteName('#__k2_items_stats', 'stats').' ON '.$db->quoteName('stats.itemId').' = '.$db->quoteName('item.id'));

		// Set query conditions
		$this->setQueryConditions($query);

		// Set query sorting
		$this->setQuerySorting($query);

		// Hook for plugins
		$this->onBeforeSetQuery($query, 'com_k2.items.list');

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
		$query->select('COUNT(*)')->from($db->quoteName('#__k2_items', 'item'));

		// Join over the categories
		$query->leftJoin($db->quoteName('#__k2_categories', 'category').' ON '.$db->quoteName('category.id').' = '.$db->quoteName('item.catid'));

		// Set query conditions
		$this->setQueryConditions($query);

		// Hook for plugins
		$this->onBeforeSetQuery($query, 'com_k2.items.count');

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
			// Get current datetime
			$date = JFactory::getDate()->toSql();

			// Get authorised view levels
			$viewlevels = array_unique(JFactory::getUser()->getAuthorisedViewLevels());

			// Published items only
			$this->setState('state', 1);
			$this->setState('publish_up', $date);
			$this->setState('publish_down', $date);

			// Set state for access
			$this->setState('access', $viewlevels);
		}

		// Shortcut method for setting the categoy filter
		if ($this->getState('category.filter'))
		{
			$filter = $this->getState('category.filter');
			if (isset($filter->enabled) && $filter->enabled)
			{
				$this->setState('category', $filter->categories);
				$this->setState('recursive', $filter->recursive);
			}
		}

		if ($this->getState('language'))
		{
			$query->where($db->quoteName('item.language').' = '.$db->quote($this->getState('language')));
		}
		if (is_numeric($this->getState('state')))
		{
			$operator = $this->getState('state.operator') ? $this->getState('state.operator') : '=';
			$query->where($db->quoteName('item.state').' '.$operator.' '.(int)$this->getState('state'));
		}
		if (is_numeric($this->getState('featured')))
		{
			$query->where($db->quoteName('item.featured').' = '.(int)$this->getState('featured'));
		}
		if ($this->getState('category'))
		{
			$categories = (array)$this->getState('category');
			$filter = K2ModelCategories::getCategoryFilter($categories, $this->getState('recursive'), $this->getState('site'));
			$query->where($db->quoteName('item.catid').' IN ('.implode(',', $filter).')');
		}
		else if ($this->getState('site'))
		{
			$authorised = K2ModelCategories::getAuthorised();
			if (!count($authorised))
			{
				$authorised[] = 1;
			}
			$query->where($db->quoteName('item.catid').' IN ('.implode(',', $authorised).')');
		}

		if ($this->getState('access'))
		{
			$access = $this->getState('access');
			if (is_array($access))
			{
				$access = array_unique($access);
				JArrayHelper::toInteger($access);
				$query->where($db->quoteName('item.access').' IN ('.implode(',', $access).')');
			}
			else
			{
				$query->where($db->quoteName('item.access').' = '.(int)$access);
			}
		}
		if ($this->getState('id'))
		{
			$id = $this->getState('id');
			if (is_array($id))
			{
				JArrayHelper::toInteger($id);
				$query->where($db->quoteName('item.id').' IN ('.implode(',', $id).')');
			}
			else
			{
				$query->where($db->quoteName('item.id').' = '.(int)$id);
			}
		}
		if ($this->getState('alias'))
		{
			$query->where($db->quoteName('item.alias').' = '.$db->quote($this->getState('alias')));
		}
		if ($this->getState('author'))
		{
			$query->where($db->quoteName('item.created_by').' = '.(int)$this->getState('author'));
			if ($this->getState('site'))
			{
				$query->where($db->quoteName('item.created_by_alias').' = '.$db->quote(''));
			}
		}
		if ($this->getState('publish_up'))
		{
			$query->where('('.$db->quoteName('item.publish_up').' = '.$db->Quote($db->getNullDate()).' OR '.$db->quoteName('item.publish_up').' <= '.$db->Quote($this->getState('publish_up')).')');
		}
		if ($this->getState('publish_down'))
		{
			$query->where('('.$db->quoteName('item.publish_down').' = '.$db->Quote($db->getNullDate()).' OR '.$db->quoteName('item.publish_down').' >= '.$db->Quote($this->getState('publish_down')).')');
		}
		if ($this->getState('search'))
		{
			$search = trim($this->getState('search'));
			if ($search)
			{
				// Site search
				if ($this->getState('site'))
				{
					$mode = $this->getState('search.mode');
					switch ($mode)
					{
						case 'exact' :
							$text = $db->quote('%'.$db->escape($search, true).'%', false);
							$where = $db->quoteName('item.title').' LIKE '.$text.' OR '.$db->quoteName('item.introtext').' LIKE '.$text.' OR '.$db->quoteName('item.fulltext').' LIKE '.$text.' OR '.$db->quoteName('item.extra_fields').' LIKE '.$text.' OR '.$db->quoteName('item.tags').' LIKE '.$text;
							break;

						case 'all' :
						case 'any' :
						default :
							$words = explode(' ', $search);
							$searchConditions = array();
							foreach ($words as $word)
							{
								$word = $db->quote('%'.$db->escape($word, true).'%', false);
								$wordConditions = array();
								$wordConditions[] = $db->quoteName('item.title').' LIKE '.$word;
								$wordConditions[] = $db->quoteName('item.introtext').' LIKE '.$word;
								$wordConditions[] = $db->quoteName('item.fulltext').' LIKE '.$word;
								$wordConditions[] = $db->quoteName('item.extra_fields').' LIKE '.$word;
								$wordConditions[] = $db->quoteName('item.tags').' LIKE '.$word;
								$searchConditions[] = implode(' OR ', $wordConditions);
							}
							$where = '('.implode(($mode == 'all' ? ') AND (' : ') OR ('), $searchConditions).')';
							break;
					}
					$query->where('('.$where.')');

				}
				// Admin search
				else
				{
					$search = $db->escape($search, true);
					$query->where('('.$db->quoteName('item.title').' LIKE '.$db->Quote('%'.$search.'%', false).' 
					OR '.$db->quoteName('item.id').' = '.(int)$search.'
					OR '.$db->quoteName('item.introtext').' LIKE '.$db->Quote('%'.$search.'%', false).'
					OR '.$db->quoteName('item.fulltext').' LIKE '.$db->Quote('%'.$search.'%', false).')');
				}

			}
		}
		if ($this->getState('day'))
		{
			$query->where('DAY('.$db->quoteName('item.created').') = '.(int)$this->getState('day'));
		}
		if ($this->getState('month'))
		{
			$query->where('MONTH('.$db->quoteName('item.created').') = '.(int)$this->getState('month'));
		}
		if ($this->getState('year'))
		{
			$query->where('YEAR('.$db->quoteName('item.created').') = '.(int)$this->getState('year'));
		}
		if ($this->getState('media'))
		{
			$query->where($db->quoteName('item.media').' != '.$db->quote('[]'));
			$query->where($db->quoteName('item.media').' != '.$db->quote(''));
		}
		if ($this->getState('created.value'))
		{
			$query->where($db->quoteName('item.created').' '.$this->getState('created.operator').' '.$db->quote($this->getState('created.value')));
		}
		if ($this->getState('ordering.value'))
		{
			$query->where($db->quoteName('item.ordering').' '.$this->getState('ordering.operator').' '.(int)$this->getState('ordering.value'));
		}
	}

	private function setQuerySorting(&$query)
	{
		$sorting = $this->getState('sorting');
		switch($sorting)
		{
			default :
			case 'id' :
				$ordering = 'item.id';
				$direction = 'DESC';
				break;
			case 'title' :
				$ordering = 'item.title';
				$direction = 'ASC';
				break;
			case 'title.reverse' :
				$ordering = 'item.title';
				$direction = 'DESC';
				break;
			case 'ordering' :
				$ordering = array('category.lft', 'item.ordering');
				$direction = 'ASC';
				break;
			case 'ordering.reverse' :
				$ordering = array('category.lft', 'item.ordering');
				$direction = 'DESC';
				break;
			case 'featured_ordering' :
				$ordering = 'item.featured_ordering';
				$direction = 'ASC';
				break;
			case 'state' :
				$ordering = 'item.state';
				$direction = 'DESC';
				break;
			case 'featured' :
				$ordering = 'item.featured';
				$direction = 'DESC';
				break;
			case 'category' :
				$ordering = array('categoryName', 'item.title');
				$direction = 'ASC';
				break;
			case 'author' :
				$ordering = 'authorName';
				$direction = 'ASC';
				break;
			case 'moderator' :
				$ordering = 'moderatorName';
				$direction = 'ASC';
				break;
			case 'access' :
				$ordering = 'viewLevel';
				$direction = 'ASC';
				break;
			case 'created' :
				$ordering = 'item.created';
				$direction = 'DESC';
				break;
			case 'created.reverse' :
				$ordering = 'item.created';
				$direction = 'ASC';
				break;
			case 'modified' :
				$ordering = 'item.modified';
				$direction = 'DESC';
				break;
			case 'hits' :
				$ordering = 'stats.hits';
				$direction = 'DESC';
				break;
			case 'comments' :
				$ordering = 'stats.comments';
				$direction = 'DESC';
				break;
			case 'language' :
				$ordering = 'languageTitle';
				$direction = 'ASC';
				break;
			case 'publishUp' :
				$ordering = 'item.publish_up';
				$direction = 'DESC';
				break;
			case 'custom' :
				$ordering = $this->getState('sorting.custom.value');
				$direction = $this->getState('sorting.custom.direction');
				break;
		}
		// Append sorting
		$db = $this->getDbo();
		if (is_array($ordering))
		{
			$conditions = array();
			foreach ($ordering as $column)
			{
				$conditions[] = $db->quoteName($column).' '.$direction;
			}
			$query->order(implode(', ', $conditions));
		}
		else
		{
			if ($sorting == 'random')
			{
				$query->order('RAND()');
			}
			else
			{
				$query->order($db->quoteName($ordering).' '.$direction);
			}
		}

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
			// Set isNew flag
			$this->setState('isNew', true);

			// Permissions context for new items is the category
			$context = 'com_k2.category.'.$data['catid'];

			// If the user has not the permission to create item in this category stop the processs. Otherwise handle the item state
			if (!$user->authorise('k2.item.create', $context))
			{
				$this->setError(JText::_('K2_YOU_ARE_NOT_AUTHORIZED_TO_PERFORM_THIS_OPERATION'));
				return false;
			}
			else
			{
				// User can create the item but cannot edit it's state so we set the item state to 0
				if (!$user->authorise('k2.item.edit.state', $context))
				{
					$data['state'] = 0;
				}

				// User can create the item but cannot edit it's featured state so we set the item featured state to 0
				if (!$user->authorise('k2.item.edit.state.featured', $context))
				{
					$data['featured'] = 0;
				}
			}

		}
		// Edit action
		if ($table->id)
		{
			// Set isNew flag
			$this->setState('isNew', false);

			// Set owner change flag
			$this->setState('owner.changed', isset($data['created_by']) && $data['created_by'] != $table->created_by);
			$this->setState('owner', $table->created_by);

			$context = 'com_k2.item.'.$table->id;
			$canEdit = $user->authorise('k2.item.edit', $context) || ($user->authorise('k2.item.edit.own', $context) && $user->id == $table->created_by);
			$canEditState = $user->authorise('k2.item.edit.state', $context);
			$canEditFeaturedState = $user->authorise('k2.item.edit.state.featured', $context);

			// User cannot edit the item neither it's states. Stop the process
			if (!$canEdit && !$canEditState && !$canEditFeaturedState)
			{
				$this->setError(JText::_('K2_YOU_ARE_NOT_AUTHORIZED_TO_PERFORM_THIS_OPERATION'));
				return false;
			}
			else
			{
				// User cannot edit the item. Reset the input
				if (!$canEdit)
				{
					$data = array();
					$data['id'] = $table->id;
				}
				// Set the states values depending on permissions
				if (!$canEditState)
				{
					$data['state'] = $table->state;
				}
				if (!$canEditFeaturedState)
				{
					$data['featured'] = $table->featured;
				}
			}

		}

		$configuration = JFactory::getConfig();
		$userTimeZone = $user->getParam('timezone', $configuration->get('offset'));

		// Handle date data
		if ($data['id'] && isset($data['createdDate']))
		{
			// Convert date to UTC
			$createdDateTime = $data['createdDate'].' '.$data['createdTime'];
			$data['created'] = JFactory::getDate($createdDateTime, $userTimeZone)->toSql();
		}

		if (isset($data['publishUpDate']) && isset($data['publishUpTime']))
		{
			// Convert date to UTC
			$publishUpDateTime = $data['publishUpDate'].' '.$data['publishUpTime'];
			if ((int)$publishUpDateTime > 0)
			{
				$data['publish_up'] = JFactory::getDate($publishUpDateTime, $userTimeZone)->toSql();
			}
			else
			{
				$data['publish_up'] = $data['created'];
			}
		}

		if (isset($data['publishDownDate']) && isset($data['publishDownTime']))
		{
			// Convert date to UTC
			$publishDownDateTime = $data['publishDownDate'].' '.$data['publishDownTime'];
			if ((int)$publishDownDateTime > 0)
			{
				$data['publish_down'] = JFactory::getDate($publishDownDateTime, $userTimeZone)->toSql();
			}
			else
			{
				$data['publish_down'] = '';
			}
		}

		if (isset($data['startDate']) && isset($data['startTime']))
		{
			// Convert date to UTC
			$startDateTime = $data['startDate'].' '.$data['startTime'];
			if ((int)$startDateTime > 0)
			{
				$data['start_date'] = JFactory::getDate($startDateTime, $userTimeZone)->toSql();
			}
			else
			{
				$data['start_date'] = '';
			}
		}

		if (isset($data['endDate']) && isset($data['endTime']))
		{
			// Convert date to UTC
			$endDateTime = $data['endDate'].' '.$data['endTime'];
			if ((int)$endDateTime > 0)
			{
				$data['end_date'] = JFactory::getDate($endDateTime, $userTimeZone)->toSql();
			}
			else
			{
				$data['end_date'] = '';
			}
		}

		// Ordering
		if (!$table->id)
		{
			$data['ordering'] = $table->getNextOrder('catid = '.(int)$data['catid']);
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

		// Galleries
		if (isset($data['galleries']))
		{
			// Set the galleries input to state
			$this->setState('galleries', $data['galleries']);

			// Prepare the galleries input data for storing
			$data['galleries'] = array_values($data['galleries']);
			foreach ($data['galleries'] as $key => $gallery)
			{
				if ($gallery['remove'])
				{
					unset($data['galleries'][$key]);
				}
				else
				{
					unset($data['galleries'][$key]['remove']);
				}
			}
			$data['galleries'] = json_encode(array_values($data['galleries']));
		}

		// Media
		if (isset($data['media']))
		{
			// Set the media input to state
			$this->setState('media', $data['media']);

			// Prepare the media input data for storing
			$data['media'] = array_values($data['media']);
			foreach ($data['media'] as $key => $media)
			{
				if ($media['remove'])
				{
					unset($data['media'][$key]);
				}
				else
				{
					unset($data['media'][$key]['remove']);
				}
			}
			$data['media'] = json_encode(array_values($data['media']));
		}
		
		// Attachments
		if (isset($data['attachments']))
		{
			// Set the media input to state
			$this->setState('attachments', $data['attachments']);

			// Prepare the attachments input data for storing
			$data['attachments'] = array_values($data['attachments']);
			foreach ($data['attachments'] as $key => $attachment)
			{
				if ($attachment['remove'])
				{
					unset($data['attachments'][$key]);
				}
				else
				{
					unset($data['attachments'][$key]['remove']);
				}
			}
			$data['attachments'] = json_encode(array_values($data['attachments']));
		}

		if (isset($data['tags']))
		{
			$model = K2Model::getInstance('Tags', 'K2Model');
			$tags = explode(',', $data['tags']);
			$tags = array_unique($tags);
			$data['tags'] = array();
			foreach ($tags as $tag)
			{
				$entry = new stdClass;
				$entry->name = $tag;
				$entry->id = $model->addTag($tag);
				$data['tags'][] = $entry;
			}
			$data['tags'] = json_encode($data['tags']);
		}

		// Extra fields
		if (isset($data['extra_fields']))
		{
			$data['extra_fields'] = json_encode($data['extra_fields']);
		}

		return true;

	}

	/**
	 * onAfterSave method.
	 *
	 * @return void
	 */

	protected function onAfterSave(&$data, $table)
	{

		// Image
		if ($image = $this->getState('image'))
		{
			// Current image
			$currentImageId = md5('Image'.$table->id);

			// Temporary (new) image
			$tempImageId = $image['temp'];

			// Item image has been removed
			if ($image['remove'])
			{
				K2HelperImages::removeItemImage($currentImageId);
			}
			else if ($tempImageId)
			{
				K2HelperImages::updateItemImage($tempImageId, $currentImageId);
				K2HelperImages::resizeItemImage($currentImageId, $table->catid);
			}
		}

		// Clean up any temporary files
		$session = JFactory::getSession();
		if ($tmpId = $session->get('k2.image'))
		{
			K2HelperImages::removeItemImage($tmpId);
		}

		// Tags
		if (isset($data['tags']))
		{
			$tags = json_decode($data['tags']);
			$model = K2Model::getInstance('Tags', 'K2Model');
			$itemId = $this->getState('id');
			$model->deleteItemTags($itemId);
			foreach ($tags as $tag)
			{
				$model->tagItem($tag->id, $itemId);
			}
		}

		// Galleries
		if ($galleries = $this->getState('galleries'))
		{
			K2HelperGalleries::update($galleries, $table);
		}

		// Media
		if ($media = $this->getState('media'))
		{
			K2HelperMedia::update($media, $table);
		}
		
		// Attachments
		if ($attachments = $this->getState('attachments'))
		{
			K2HelperAttachments::update($attachments, $table);
		}
		
		// Clean up temporary uploads
		K2HelperGalleries::purge();
		K2HelperMedia::purge();
		K2HelperAttachments::purge();

		// Handle statistics
		$statistics = K2Model::getInstance('Statistics', 'K2Model');
		if ($this->getState('isNew'))
		{
			$statistics->increaseUserItemsCounter($table->created_by);
		}
		else
		{
			if ($this->getState('owner.changed'))
			{
				$statistics->decreaseUserItemsCounter($this->getState('owner'));
				$statistics->increaseUserItemsCounter($table->created_by);
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
		$user = JFactory::getUser();
		if (!$user->authorise('k2.item.delete', 'com_k2.category.'.$table->catid))
		{
			$this->setError(JText::_('K2_YOU_ARE_NOT_AUTHORIZED_TO_PERFORM_THIS_OPERATION'));
			return false;
		}

		// Check that the item is trashed
		if ($table->state != -1)
		{
			$this->setError(JText::_('K2_YOU_CAN_ONLY_DELETE_TRASHED_ITEMS'));
			return false;
		}

		// Set some variables for later usage in the model
		$this->setState('galleries', $table->galleries);
		$this->setState('media', $table->media);
		$this->setState('categoryId', $table->catid);
		$this->setState('userId', $table->created_by);

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
		K2HelperImages::removeItemImage(md5('Image'.$table->id));

		// Delete item galleries
		$galleries = json_decode($this->getState('galleries'));
		foreach ($galleries as $gallery)
		{
			if (isset($gallery->upload) && $gallery->upload)
			{
				K2HelperGalleries::remove($gallery->upload, $table->id);
			}
		}

		// Delete item media
		$media = json_decode($this->getState('media'));
		if (is_array($media))
		{
			foreach ($media as $entry)
			{
				if (isset($entry->upload) && $entry->upload)
				{
					K2HelperMedia::remove($entry->upload, $table->id);
				}
			}
		}

		// Delete item tags reference
		$tagsModel = K2Model::getInstance('Tags');
		$tagsModel->deleteItemTags($table->id);

		// Delete item attachments
		$attachmentsModel = K2Model::getInstance('Attachments');
		$attachmentsModel->setState('itemId', $table->id);
		$attachments = $attachmentsModel->getRows();
		foreach ($attachments as $attachment)
		{
			$attachmentsModel->setState('id', $attachment->id);
			$attachmentsModel->delete();
		}

		// Handle statistics
		// First get statistics model
		$statistics = K2Model::getInstance('Statistics', 'K2Model');

		// Delete the item entry
		$statistics->deleteItemEntry($table->id);

		// Decrease users statistics
		$statistics->decreaseUserItemsCounter($this->getState('userId'));

		// Return
		return true;

	}

	public function getCopyData($id)
	{
		// Get source item
		$source = K2Items::getInstance($id);

		// Get source item properties as data array. This array will be the inout to the model.
		$data = get_object_vars($source);

		// It's a new item so reset some properties
		$data['id'] = '';
		$data['tmpId'] = uniqid();
		$data['title'] = JText::_('K2_COPY_OF').' '.$data['title'];
		$data['alias'] = '';
		$data['extra_fields'] = json_decode($data['extra_fields']);
		$data['metadata'] = $data['metadata']->toString();
		$data['plugins'] = $data['plugins']->toString();
		$data['params'] = $data['params']->toString();
		unset($data['ordering']);
		unset($data['featured_ordering']);

		// Handle tags
		$tagNames = array();
		$tags = json_decode($data['tags']);
		foreach ($tags as $tag)
		{
			$tagNames[] = $tag->name;
		}
		$data['tags'] = implode(',', $tagNames);

		// Handle image
		$imageId = isset($data['image']->id) ? $data['image']->id : false;
		if ($imageId)
		{
			$path = 'media/k2/items/src/'.$imageId.'.jpg';
			$image = K2HelperImages::addItemImage(null, $path);
			$data['image'] = array('id' => '', 'temp' => $image->temp, 'path' => '', 'remove' => 0, 'caption' => $data['image']->caption, 'credits' => $data['image']->credits);
		}
		else
		{
			unset($data['image']);
		}

		// Handle media
		$media = array('url' => array(), 'upload' => array(), 'provider' => array(), 'id' => array(), 'embed' => array(), 'caption' => array(), 'credits' => array());
		if (is_array($data['media']))
		{
			foreach ($data['media'] as $entry)
			{
				if ($entry->upload)
				{
					$filesystem = K2FileSystem::getInstance();
					if ($filesystem->has('media/k2/media/'.$id.'/'.$entry->upload))
					{
						$buffer = $filesystem->read('media/k2/media/'.$id.'/'.$entry->upload);
						$filesystem->write('media/k2/media/'.$data['tmpId'].'/'.$entry->upload, $buffer, true);
					}
				}
				$media['url'][] = $entry->url;
				$media['upload'][] = $entry->upload;
				$media['provider'][] = $entry->provider;
				$media['id'][] = $entry->id;
				$media['embed'][] = $entry->embed;
				$media['caption'][] = $entry->caption;
				$media['credits'][] = $entry->credits;
			}
		}
		$data['media'] = $media;

		// Handle galleries
		$galleries = array('url' => array(), 'upload' => array());
		foreach ($data['galleries'] as $entry)
		{
			if ($entry->upload)
			{
				$filesystem = K2FileSystem::getInstance();
				if ($filesystem->has('media/k2/galleries/'.$id.'/'.$entry->upload))
				{
					$files = $filesystem->listKeys('media/k2/galleries/'.$id.'/'.$entry->upload);
					foreach ($files['keys'] as $key)
					{
						if ($filesystem->has($key))
						{
							$buffer = $filesystem->read($key);
							$filesystem->write('media/k2/galleries/'.$data['tmpId'].'/'.$entry->upload.'/'.basename($key), $buffer, true);
						}
					}
				}
			}
			$galleries['url'][] = $entry->url;
			$galleries['upload'][] = $entry->upload;
		}
		$data['galleries'] = $galleries;

		// Handle attachments
		$filesystem = K2FileSystem::getInstance();
		$attachmentsModel = K2Model::getInstance('Attachments');
		$input = array();
		$attachments = array('id' => array(), 'name' => array(), 'title' => array(), 'file' => array());
		foreach ($data['attachments'] as $attachment)
		{
			// Save the new attachment record
			$input['id'] = null;
			$input['itemId'] = 0;
			$input['name'] = $attachment->name;
			$input['title'] = $attachment->title;
			$input['file'] = '';
			if ($attachment->file)
			{
				if ($filesystem->has('media/k2/attachments/'.$id.'/'.$attachment->file))
				{
					$buffer = $filesystem->read('media/k2/attachments/'.$id.'/'.$attachment->file);
					$filesystem->write('media/k2/attachments/'.$data['tmpId'].'/'.$attachment->file, $buffer, true);
				}
				$input['file'] = $data['tmpId'].'/'.$attachment->file;
			}
			$input['url'] = $attachment->url;
			$input['downloads'] = 0;
			$attachmentsModel->setState('data', $input);
			$attachmentsModel->save();

			// Prepare the data array
			$attachments['id'][] = $attachmentsModel->getState('id');
			$attachments['name'][] = $input['name'];
			$attachments['title'][] = $input['title'];
			$attachments['file'][] = $input['file'];
		}
		$data['attachments'] = $attachments;

		// Return the input data
		return $data;
	}

	public function getArchive()
	{
		// Get database
		$db = $this->getDBO();

		// Get query
		$query = $db->getQuery(true);

		// Select rows
		$query->select('DISTINCT MONTH('.$db->quoteName('item.created').') AS '.$db->quoteName('month'));
		$query->select('YEAR('.$db->quoteName('item.created').') AS '.$db->quoteName('year'));
		$query->from($db->quoteName('#__k2_items', 'item'));

		// Join over the categories
		$query->leftJoin($db->quoteName('#__k2_categories', 'category').' ON '.$db->quoteName('category.id').' = '.$db->quoteName('item.catid'));

		// Set state for site
		$this->setState('site', true);

		// Set query conditions
		$this->setQueryConditions($query);

		// Set query sorting
		$this->setQuerySorting($query);

		// Hook for plugins
		$this->onBeforeSetQuery($query, 'com_k2.archive');

		// Set the query
		$db->setQuery($query, 0, 12);

		// Get rows
		$rows = $db->loadObjectList();

		// Return rows
		return $rows;
	}

	public function getAuthors()
	{
		// Get database
		$db = $this->getDBO();

		// Get query
		$query = $db->getQuery(true);

		// Select rows
		$query->select('DISTINCT ('.$db->quoteName('item.created_by').')');
		$query->from($db->quoteName('#__k2_items', 'item'));

		// Join over the categories
		$query->leftJoin($db->quoteName('#__k2_categories', 'category').' ON '.$db->quoteName('category.id').' = '.$db->quoteName('item.catid'));

		// Set state for site
		$this->setState('site', true);

		// Set query conditions
		$this->setQueryConditions($query);

		// Set query sorting
		$this->setQuerySorting($query);

		// Hook for plugins
		$this->onBeforeSetQuery($query, 'com_k2.authors');

		// Set the query
		$db->setQuery($query);

		// Get rows
		$rows = $db->loadObjectList();

		// Return rows
		return $rows;
	}

}
