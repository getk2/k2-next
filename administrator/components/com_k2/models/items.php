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
require_once JPATH_ADMINISTRATOR.'/components/com_k2/helpers/images.php';
require_once JPATH_ADMINISTRATOR.'/components/com_k2/helpers/galleries.php';

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
		$query->leftJoin($db->quoteName('#__k2_categories', 'category').' ON '.$db->quoteName('category.id').' = '.$db->quoteName('item.catid'));

		// Join over the language
		$query->select($db->quoteName('lang.title', 'languageTitle'));
		$query->leftJoin($db->quoteName('#__languages', 'lang').' ON '.$db->quoteName('lang.lang_code').' = '.$db->quoteName('item.language'));

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
		$query->leftJoin($db->quoteName('#__k2_stats', 'stats').' ON '.$db->quoteName('stats.itemId').' = '.$db->quoteName('item.id'));

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
		if ($this->getState('language'))
		{
			$query->where($db->quoteName('item.language').' = '.$db->quote($this->getState('language')));
		}
		if (is_numeric($this->getState('state')))
		{
			$query->where($db->quoteName('item.state').' = '.(int)$this->getState('state'));
		}
		if (is_numeric($this->getState('featured')))
		{
			$query->where($db->quoteName('item.featured').' = '.(int)$this->getState('featured'));
		}
		if ($this->getState('category'))
		{
			$model = K2Model::getInstance('Categories');
			$root = $model->getTable();
			$tree = $root->getTree((int)$this->getState('category'));
			$categories = array();
			foreach ($tree as $category)
			{
				$categories[] = $category->id;
			}
			$query->where($db->quoteName('item.catid').' IN ('.implode(',', $categories).')');
		}
		if ($this->getState('access'))
		{
			$query->where($db->quoteName('item.access').' = '.(int)$this->getState('access'));
		}
		if ($this->getState('id'))
		{
			$id = $this->getState('id');
			if (is_array($id))
			{
				JArrayHelper::toInteger($id);
				$query->where($db->quoteName('item.id').' IN '.$id);
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
			$search = JString::trim($this->getState('search'));
			$search = JString::strtolower($search);
			if ($search)
			{
				$search = $db->escape($search, true);
				$query->where('( LOWER('.$db->quoteName('item.title').') LIKE '.$db->Quote('%'.$search.'%', false).' 
				OR '.$db->quoteName('item.id').' = '.(int)$search.'
				OR LOWER('.$db->quoteName('item.introtext').') LIKE '.$db->Quote('%'.$search.'%', false).'
				OR LOWER('.$db->quoteName('item.fulltext').') LIKE '.$db->Quote('%'.$search.'%', false).')');
			}
		}
		if (is_numeric($this->getState('category.state')))
		{
			$query->where($db->quoteName('category.state').' = '.(int)$this->getState('category.state'));
		}
		if (is_numeric($this->getState('category.access')))
		{
			$query->where($db->quoteName('category.access').' = '.(int)$this->getState('category.access'));
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
				case 'id' :
					$ordering = 'item.id';
					$direction = 'DESC';
					break;
				case 'title' :
					$ordering = 'item.title';
					$direction = 'ASC';
					break;
				case 'ordering' :
					$ordering = array(
						'category.lft',
						'item.ordering'
					);
					$direction = 'ASC';
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
					$ordering = 'categoryName';
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
				case 'modified' :
					$ordering = 'item.modified';
					$direction = 'DESC';
					break;
				case 'hits' :
					$ordering = 'hits';
					$direction = 'DESC';
					break;
				case 'language' :
					$ordering = 'languageTitle';
					$direction = 'ASC';
					break;
			}
		}
		// Append sorting
		if ($ordering)
		{
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
			$this->setState('imageId', $data['image']['id']);
			$data['image']['flag'] = (int)(bool)$data['image']['id'];
			unset($data['image']['path']);
			unset($data['image']['id']);
			$data['image'] = json_encode($data['image']);
		}

		// Media
		if (isset($data['media']))
		{
			$media = array();
			if ($data['media'])
			{
				$urls = $data['media']['url'];
				$uploads = $data['media']['upload'];
				$providers = $data['media']['provider'];
				$ids = $data['media']['id'];
				$embed = $data['media']['embed'];
				$captions = $data['media']['caption'];
				$credits = $data['media']['credits'];
				foreach ($urls as $key => $value)
				{
					$mediaEntry = new stdClass;
					$mediaEntry->url = $urls[$key];
					$mediaEntry->upload = $uploads[$key];
					$mediaEntry->provider = $providers[$key];
					$mediaEntry->id = $ids[$key];
					$mediaEntry->embed = $embed[$key];
					$mediaEntry->caption = $captions[$key];
					$mediaEntry->credits = $credits[$key];
					$media[] = $mediaEntry;
				}
			}
			$data['media'] = json_encode($media);
		}

		// Galleries
		if (isset($data['galleries']))
		{
			$galleries = array();
			if ($data['galleries'])
			{
				$urls = $data['galleries']['url'];
				$uploads = $data['galleries']['upload'];
				foreach ($urls as $key => $value)
				{
					$galleryEntry = new stdClass;
					$galleryEntry->url = $urls[$key];
					$galleryEntry->upload = $uploads[$key];
					$galleries[] = $galleryEntry;
				}
			}
			$data['galleries'] = json_encode($galleries);
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
		// Tags
		if (isset($data['tags']) && JString::trim($data['tags']) != '')
		{
			$model = K2Model::getInstance('Tags', 'K2Model');
			$itemId = $this->getState('id');
			$model->deleteItemTags($itemId);

			$tags = explode(',', $data['tags']);
			$tags = array_unique($tags);
			foreach ($tags as $tag)
			{
				$tagId = $model->addTag($tag);
				$model->tagItem($tagId, $itemId);
			}
		}

		// If we have a tmpId we have a new item and we need to handle accordingly uploaded files
		if (isset($data['tmpId']) && $data['tmpId'])
		{
			// Image
			if ($this->getState('imageId'))
			{
				$sizes = array(
					'XL' => 600,
					'L' => 400,
					'M' => 240,
					'S' => 180,
					'XS' => 100
				);

				$filesystem = K2FileSystem::getInstance();
				$baseSourceFileName = $this->getState('imageId');
				$baseTargetFileName = md5('Image'.$table->id);

				// Original image
				$path = 'media/k2/items/src';
				$source = $baseSourceFileName.'.jpg';
				$target = $baseTargetFileName.'.jpg';
				$filesystem->rename($path.'/'.$source, $path.'/'.$target);

				// Resized images
				$path = 'media/k2/items/cache';
				foreach ($sizes as $size => $width)
				{
					$source = $baseSourceFileName.'_'.$size.'.jpg';
					$target = $baseTargetFileName.'_'.$size.'.jpg';
					$filesystem->rename($path.'/'.$source, $path.'/'.$target);
				}
			}
		}

		// If we have a tmpId we need to rename the gallery directory
		if (isset($data['galleries']) && $data['galleries'] && isset($data['tmpId']) && $data['tmpId'])
		{
			$filesystem = K2FileSystem::getInstance();
			$path = 'media/k2/galleries';
			$source = $data['tmpId'];
			$target = $table->id;
			$filesystem->rename($path.'/'.$source, $path.'/'.$target);
		}

		if (isset($data['attachments']))
		{

			$filesystem = K2FileSystem::getInstance();

			K2Model::addIncludePath(JPATH_ADMINISTRATOR.'/components/com_k2/models');
			$model = K2Model::getInstance('Attachments', 'K2Model');

			$ids = $data['attachments']['id'];
			$names = $data['attachments']['name'];
			$titles = $data['attachments']['title'];
			$files = $data['attachments']['file'];
			$path = 'media/k2/attachments';

			foreach ($ids as $key => $value)
			{
				$attachmentsData = array();
				$attachmentsData['id'] = $ids[$key];
				$attachmentsData['name'] = $names[$key];
				$attachmentsData['title'] = $titles[$key];
				$attachmentsData['itemId'] = $this->getState('id');
				$attachmentsData['file'] = $files[$key];
				if ($data['tmpId'])
				{
					list($folder, $file) = explode('/', $attachmentsData['file']);
					$source = $path.'/'.$folder.'/'.$file;
					$target = $path.'/'.$this->getState('id').'/'.$file;
					$filesystem->rename($source, $target);
					$attachmentsData['file'] = $file;
				}
				$model->setState('data', $attachmentsData);
				$model->save();
			}

			if ($data['tmpId'] && $filesystem->has($path.'/'.$data['tmpId']))
			{
				$filesystem->delete($path.'/'.$data['tmpId']);
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
		K2HelperImages::removeResourceImage('item', $table->id);

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
		foreach ($media as $entry)
		{
			if (isset($entry->upload) && $entry->upload)
			{
				K2HelperMedia::remove($entry->upload, $table->id);
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

		// Return
		return true;

	}

}
