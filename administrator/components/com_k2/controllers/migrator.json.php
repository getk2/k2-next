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

/**
 * Migrator JSON controller.
 */

class K2ControllerMigrator extends JControllerLegacy
{

	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 * Recognized key values include 'name', 'default_task', 'model_path', and
	 * 'view_path' (this list is not meant to be comprehensive).
	 *
	 * @since   12.2
	 */
	public function __construct($config = array())
	{
		$application = JFactory::getApplication();
		$user = JFactory::getUser();
		if ($application->isSite() || !$user->authorise('core.manage', 'com_installer'))
		{
			throw new Exception(JText::_('JLIB_APPLICATION_ERROR_ACCESS_FORBIDDEN'), 403);
		}
		parent::__construct($config);

		set_error_handler(array(
			$this,
			'error'
		));

		$this->response = new stdClass;
		$this->response->type = '';
		$this->response->id = 0;
		$this->response->status = '';
		$this->response->errors = array();
		$this->response->completed = 0;
		$this->response->failed = 0;
	}

	public function run()
	{
		if (JSession::checkToken())
		{
			set_time_limit(0);
			$application = JFactory::getApplication();
			$type = $application->input->get('type', '', 'word');
			$id = $application->input->get('id', 0, 'int');
			if (method_exists($this, $type))
			{
				$this->response->type = $type;
				try
				{
					call_user_func(array(
						$this,
						$type
					), $id);
				}
				catch(Exception $exception)
				{
					$this->response->failed = 1;
					$this->response->errors[] = $exception->getMessage();
				}

			}
			else
			{
				throw new Exception(JText::_('K2_NOT_FOUND'), 404);
			}

		}
		else
		{
			$this->response->errors[] = JText::_('JINVALID_TOKEN');
			$this->response->failed = 1;
		}
		echo json_encode($this->response);
		return $this;
	}

	public function restore()
	{
		if (!JSession::checkToken())
		{
			$this->response->errors[] = JText::_('JINVALID_TOKEN');
			$this->response->failed = 1;
		}
		else
		{
			// database
			$db = JFactory::getDbo();

			// Delete v3 tables
			$sql = JPATH_SITE.'/administrator/components/com_k2/uninstall.sql';
			$queries = JDatabaseDriver::splitSql(file_get_contents($sql));
			foreach ($queries as $query)
			{
				$query = trim($query);
				if ($query != '' && $query{0} != '#')
				{
					$db->setQuery($query);
					$db->execute();
				}
			}

			// Restore v2 tables
			$tables = array(
				'#__k2_v2_attachments',
				'#__k2_v2_categories',
				'#__k2_v2_comments',
				'#__k2_v2_extra_fields',
				'#__k2_v2_extra_fields_groups',
				'#__k2_v2_items',
				'#__k2_v2_rating',
				'#__k2_v2_tags',
				'#__k2_v2_tags_xref',
				'#__k2_v2_users',
				'#__k2_v2_user_groups'
			);
			foreach ($tables as $table)
			{
				$name = str_replace('#__k2_v2_', '#__k2_', $table);
				$db->setQuery('DROP TABLE IF EXISTS '.$db->quoteName($name));
				$db->execute();

				$db->setQuery('RENAME TABLE '.$db->quoteName($table).' TO '.$db->quoteName($name));
				$db->execute();
			}

			// Restore component files manually to keep any custom templates
			if (JFolder::exists(JPATH_SITE.'/components/com_k2'))
			{
				JFolder::delete(JPATH_SITE.'/components/com_k2');
				JFolder::move(JPATH_SITE.'/components/com_k2_v2', JPATH_SITE.'/components/com_k2');
			}
			if (JFolder::exists(JPATH_ADMINISTRATOR.'/components/com_k2'))
			{
				JFolder::delete(JPATH_ADMINISTRATOR.'/components/com_k2');
				JFolder::move(JPATH_ADMINISTRATOR.'/components/com_k2_v2', JPATH_ADMINISTRATOR.'/components/com_k2');
			}

			// Install K2 v2 package to restore rest extension files
			$installer = JInstaller::getInstance();
			$file = JInstallerHelper::downloadPackage('http://getk2.org/downloads/?f=K2_v2.6.8.zip');
			$config = JFactory::getConfig();
			$package = JInstallerHelper::unpack($config->get('tmp_path').'/'.$file, true);
			$installer->install($package['dir']);
		}

		echo json_encode($this->response);
		return $this;

	}

	public function error($code, $description, $file, $line)
	{
		switch ($code)
		{
			case E_ERROR :
				$message = 'Error['.$code.'] '.$description.'. Line '.$line.' in file '.$file;
				$type = 'error';
				$this->response->failed = 1;
				break;

			case E_WARNING :
				$message = 'Warning['.$code.'] '.$description.'. Line '.$line.' in file '.$file;
				$type = 'warning';
				break;

			case E_NOTICE :
				$message = 'Notice['.$code.'] '.$description.'. Line '.$line.' in file '.$file;
				$type = 'notice';
				break;

			default :
				$message = 'Uknown error type['.$code.'] '.$description.'. Line '.$line.' in file '.$file;
				$type = 'error';
				break;
		}
		$this->response->errors[] = $message;
	}

	private function attachments($id)
	{
		$this->response->status = 'Processing Attahcments';

		$step = 1;
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('*')->from($db->quoteName('#__k2_v2_attachments'))->where($db->quoteName('id').' > '.$id)->order($db->quoteName('id'));
		$db->setQuery($query, 0, $step);
		$attachments = $db->loadObjectList();
		foreach ($attachments as $attachment)
		{
			if (JFile::exists(JPATH_SITE.'/media/k2/attachments/'.$attachment->filename))
			{
				if (!JFolder::exists(JPATH_SITE.'/media/k2/attachments/'.$attachment->itemID))
				{
					JFolder::create(JPATH_SITE.'/media/k2/attachments/'.$attachment->itemID);
				}
				JFile::move(JPATH_SITE.'/media/k2/attachments/'.$attachment->filename, JPATH_SITE.'/media/k2/attachments/'.$attachment->itemID.'/'.$attachment->filename);
			}
			$query = $db->getQuery(true);
			$query->insert($db->quoteName('#__k2_attachments'));
			$query->values((int)$attachment->id.','.(int)$attachment->itemID.','.$db->quote($attachment->title).','.$db->quote($attachment->titleAttribute).','.$db->quote($attachment->filename).','.$db->quote('').',0,'.(int)$attachment->hits);
			$db->setQuery($query);
			$db->execute();
			$this->response->id = $attachment->id;
		}

		if (count($attachments) == 0)
		{
			$this->response->id = 0;
			$this->response->type = 'categories';
		}
	}

	private function categories($id)
	{
		$this->response->status = 'Processing Categories';
		$step = 1;
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('*')->from($db->quoteName('#__k2_v2_categories'))->where($db->quoteName('id').' > '.$id)->order($db->quoteName('id'));
		$db->setQuery($query, 0, $step);
		$categories = $db->loadObjectList();
		foreach ($categories as $category)
		{
			$hasImage = false;
			$newCategoryId = $category->id == 1 ? 99999 : $category->id;
			$categoryParams = json_decode($category->params);

			$query = $db->getQuery(true);
			$query->select($db->quoteName('id'))->from($db->quoteName('#__k2_categories'))->where($db->quoteName('alias').' = '.$db->quote($category->alias));

			$db->setQuery($query);
			if ($db->loadResult())
			{
				$category->alias .= '-'.uniqid();
			}

			$data = array();
			$data['id'] = '';
			$data['title'] = $category->name;
			$data['alias'] = $category->alias;
			$data['state'] = $category->trash ? -1 : $category->published;
			$data['access'] = $category->access;
			$data['description'] = $category->description;
			$data['parent_id'] = 1;
			if ($category->image && JFile::exists(JPATH_SITE.'/media/k2/categories/'.$category->image))
			{
				JFile::move(JPATH_SITE.'/media/k2/categories/'.$category->image, JPATH_SITE.'/media/k2/categories/'.md5('Image'.$newCategoryId).'.jpg');
				$hasImage = true;
			}
			$data['template'] = isset($categoryParams->theme) && $categoryParams->theme ? $categoryParams->theme : '';
			$data['inheritance'] = isset($categoryParams->inheritFrom) && $categoryParams->inheritFrom ? $categoryParams->inheritFrom : 0;
			if ($data['inheritance'] == 1)
			{
				$data['inheritance'] = 99999;
			}
			$data['metadata'] = array();
			$data['metadata']['description'] = isset($categoryParams->catMetaDesc) && $categoryParams->catMetaDesc ? $categoryParams->catMetaDesc : '';
			$data['metadata']['keywords'] = isset($categoryParams->catMetaKey) && $categoryParams->catMetaKey ? $categoryParams->catMetaKey : '';
			$data['metadata']['robots'] = isset($categoryParams->catMetaRobots) && $categoryParams->catMetaRobots ? $categoryParams->catMetaRobots : '';
			$data['metadata']['author'] = isset($categoryParams->catMetaAuthor) && $categoryParams->catMetaAuthor ? $categoryParams->catMetaAuthor : '';
			$data['language'] = $category->language;

			$model = K2Model::getInstance('Categories');
			$model->setState('data', $data);
			if (!$model->save())
			{
				$this->response->errors[] = $model->getError();
				$this->response->failed = 1;
				return;
			}
			$lastInsertedId = $model->getState('id');
			$image = new stdClass;
			$image->flag = $hasImage ? 1 : 0;
			$image->caption = '';
			$image->credits = '';
			$image = json_encode($image);

			$updatedParams = new JRegistry($category->params);
			$this->updateImageSizeParam($updatedParams, 'leadingImgSize', 'Large');
			$this->updateImageSizeParam($updatedParams, 'primaryImgSize', 'Medium');
			$this->updateImageSizeParam($updatedParams, 'secondaryImgSize', 'Small');
			$this->updateImageSizeParam($updatedParams, 'linksImgSize', 'XSmall');
			$this->updateImageSizeParam($updatedParams, 'itemImgSize', 'Large');
			$this->updateImageSizeParam($updatedParams, 'itemRelatedImageSize', '0');

			$this->updateOrderingParam($updatedParams, 'catOrdering', '');
			$this->updateOrderingParam($updatedParams, 'subCatOrdering', '');

			$query = $db->getQuery(true);
			$query->update($db->quoteName('#__k2_categories'))->set(array(
				$db->quoteName('id').' = '.$newCategoryId,
				$db->quoteName('image').' = '.$db->quote($image),
				$db->quoteName('plugins').' = '.$db->quote($category->plugins),
				$db->quoteName('params').' = '.$db->quote($updatedParams->toString())
			))->where($db->quoteName('id').' = '.$lastInsertedId);
			$db->setQuery($query);
			$db->execute();

			// Update auto increment to avoid SQL errors
			if ($newCategoryId != 99999)
			{
				$db->setQuery('ALTER TABLE '.$db->quoteName('#__k2_categories').' AUTO_INCREMENT='.((int)$newCategoryId + 1));
				$db->execute();
			}
			$this->response->id = $category->id;
		}

		if (count($categories) == 0)
		{
			$this->response->id = 0;
			$this->response->type = 'categoriestree';
		}
	}

	private function categoriestree($id)
	{
		$this->response->status = 'Processing Categories Tree';
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('id, parent')->from($db->quoteName('#__k2_v2_categories'))->order('parent, ordering');
		$db->setQuery($query);
		$categories = $db->loadObjectList();
		foreach ($categories as $category)
		{
			$srcId = $category->id == 1 ? 99999 : $category->id;
			JTable::addIncludePath(JPATH_SITE.'/administrator/components/com_k2/tables');
			$table = JTable::getInstance('Categories', 'K2Table');
			$table->load($srcId);
			if ($category->parent)
			{
				$parentId = $category->parent;
				if ($parentId == 1)
				{
					$parentId = 99999;
				}
			}
			else
			{
				$parentId = 1;
			}
			if ($srcId != $parentId)
			{
				$table->setLocation($parentId, 'last-child');
			}
			if (!$table->store())
			{
				$this->response->errors[] = $table->getError();
				$this->response->failed = 1;
				return;
			}
		}
		$this->response->id = 0;
		$this->response->type = 'comments';
	}

	private function comments($id)
	{
		$this->response->status = 'Processing Comments';
		$db = JFactory::getDbo();
		$query = 'INSERT INTO '.$db->quoteName('#__k2_comments').'
		('.$db->quoteName('id').','.$db->quoteName('itemId').','.$db->quoteName('userId').','.$db->quoteName('name').','.$db->quoteName('date').','.$db->quoteName('email').','.$db->quoteName('url').','.$db->quoteName('ip').','.$db->quoteName('hostname').','.$db->quoteName('text').','.$db->quoteName('state').') 
		SELECT 
		'.$db->quoteName('id').','.$db->quoteName('itemID').','.$db->quoteName('userID').','.$db->quoteName('userName').','.$db->quoteName('commentDate').','.$db->quoteName('commentEmail').','.$db->quoteName('commentURL').','.$db->quote('').','.$db->quote('').','.$db->quoteName('commentText').','.$db->quoteName('published').' 
		FROM '.$db->quoteName('#__k2_v2_comments');
		$db->setQuery($query);
		$db->execute();
		$this->response->id = 0;
		$this->response->type = 'extrafields';
	}

	private function extrafields($id)
	{
		$this->response->status = 'Processing Extra Fields';
		$step = 1;
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('*')->from($db->quoteName('#__k2_v2_extra_fields'))->where($db->quoteName('id').' > '.$id)->order($db->quoteName('id'));
		$db->setQuery($query, 0, $step);
		$fields = $db->loadObjectList();
		foreach ($fields as $field)
		{
			$json = json_decode($field->value);
			$object = is_array($json) && isset($json[0]) ? $json[0] : $json;

			$required = isset($object->required) ? $object->required : 0;

			// Build alias
			if (isset($object->alias) && !empty($object->alias))
			{
				$alias = $object->alias;
			}
			else
			{
				$filter = JFilterInput::getInstance();
				$alias = $filter->clean($field->name, 'WORD');
				if (!$alias)
				{
					$alias = 'extraField'.$field->id;
				}
			}

			// Ensure alias is unique during upgrade
			$query = $db->getQuery(true);
			$query->select('COUNT(*)')->from($db->quoteName('#__k2_extra_fields'))->where($db->quoteName('alias').' = '.$db->quote($alias));
			$db->setQuery($query);
			$aliasTaken = (int)$db->loadResult();
			if ($aliasTaken)
			{
				$alias .= '_'.uniqid();
			}

			if ($field->type == 'textfield')
			{
				$type = 'text';
				$value = new stdClass;
				$value->value = isset($object->value) ? $object->value : '';
			}
			else if ($field->type == 'textarea')
			{
				$type = 'textarea';
				$value = new stdClass;
				$value->rows = isset($object->rows) ? $object->rows : 0;
				$value->columns = isset($object->cols) ? $object->cols : 0;
				$value->value = isset($object->value) ? $object->value : '';
				$value->editor = isset($object->editor) ? $object->editor : 0;
			}
			else if ($field->type == 'select')
			{
				$type = 'select';
				$value = new stdClass;
				$value->options = array();
				foreach ($json as $option)
				{
					$value->options[] = $option->name;
				}
			}
			else if ($field->type == 'multipleSelect')
			{
				$type = 'select';
				$value = new stdClass;
				$value->multiple = 1;
				$value->options = array();
				foreach ($json as $option)
				{
					$value->options[] = $option->name;
				}
				if ($json[0]->showNull)
				{
					$value->null = 1;
				}
			}
			else if ($field->type == 'radio')
			{
				$type = 'radio';
				$value = new stdClass;
				$value->options = array();
				foreach ($json as $option)
				{
					$value->options[] = $option->name;
				}
			}
			else if ($field->type == 'link')
			{
				$type = 'link';
				$value = new stdClass;
				$value->text = $object->name;
				$value->url = $object->value;
				$value->target = $object->target;
			}
			else if ($field->type == 'labels')
			{
				$type = 'labels';
				$value = new stdClass;
				$value->value = isset($object->value) ? $object->value : '';
			}
			else if ($field->type == 'date')
			{
				$type = 'date';
				$value = new stdClass;
				$value->date = isset($object->value) ? $object->value : '';
			}
			else if ($field->type == 'image')
			{
				$type = 'image';
				$value = new stdClass;
				$value->src = isset($object->value) ? $object->value : '';
				$value->alt = '';
			}
			else if ($field->type == 'csv')
			{
				$type = 'csv';
				$value = new stdClass;
				$value->value = isset($object->value) ? json_encode($object->value) : '';
			}
			else if ($field->type == 'header')
			{
				$type = 'header';
				$value = new stdClass;
				$value->value = isset($object->value) ? $object->value : '';
			}

			$value = json_encode($value);

			$query = $db->getQuery(true);
			$query->insert($db->quoteName('#__k2_extra_fields'));
			$query->values((int)$field->id.','.$db->quote($field->name).','.$db->quote($alias).','.$db->quote($value).','.(int)$required.','.$db->quote($type).','.(int)$field->group.','.(int)$field->published.','.(int)$field->ordering);
			$db->setQuery($query);
			$db->execute();
			$this->response->id = $field->id;
		}

		if (count($fields) == 0)
		{
			$this->response->id = 0;
			$this->response->type = 'extrafieldsgroups';
		}
	}

	private function extrafieldsgroups($id)
	{
		$this->response->status = 'Processing Extra Fields Groups';
		$step = 10;
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('*')->from($db->quoteName('#__k2_v2_extra_fields_groups'))->where($db->quoteName('id').' > '.$id)->order($db->quoteName('id'));
		$db->setQuery($query, 0, $step);
		$groups = $db->loadObjectList();
		foreach ($groups as $group)
		{
			$query = $db->getQuery(true);
			$query->select($db->quoteName('id'))->from($db->quoteName('#__k2_v2_categories'))->where($db->quoteName('extraFieldsGroup').' = '.(int)$group->id);
			$db->setQuery($query);
			$categories = $db->loadColumn();

			$newCategories = array();
			foreach ($categories as $categoryId)
			{
				if ($categoryId == 1)
				{
					$newCategories[] = 99999;
				}
				else
				{
					$newCategories[] = $categoryId;
				}
			}

			$assignments = new stdClass;
			$assignments->mode = 'specific';
			$assignments->categories = $categories;
			$assignments->recursive = 0;
			$assignments = json_encode($assignments);

			$query = $db->getQuery(true);
			$query->insert($db->quoteName('#__k2_extra_fields_groups'));
			$query->values((int)$group->id.','.$db->quote($group->name).','.$db->quote('item').','.$db->quote($assignments).', 0');
			$db->setQuery($query);
			$db->execute();
			$this->response->id = $group->id;
		}

		if (count($groups) == 0)
		{
			$this->response->id = 0;
			$this->response->type = 'tags';
		}
	}

	private function tags($id)
	{
		$this->response->status = 'Processing Tags';
		$step = 10;
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('*')->from($db->quoteName('#__k2_v2_tags'))->where($db->quoteName('id').' > '.$id)->order($db->quoteName('id'));
		$db->setQuery($query, 0, $step);
		$tags = $db->loadObjectList();
		foreach ($tags as $tag)
		{
			$alias = $tag->name;
			if (JFactory::getConfig()->get('unicodeslugs') == 1)
			{
				$alias = JFilterOutput::stringURLUnicodeSlug($alias);
			}
			else
			{
				$alias = JFilterOutput::stringURLSafe($alias);
			}
			if (trim($alias) == '')
			{
				$alias = uniqid();
			}
			$query = $db->getQuery(true);
			$query->select($db->quoteName('id'))->from($db->quoteName('#__k2_tags'))->where($db->quoteName('alias').' = '.$db->quote($alias));
			$db->setQuery($query);
			if ($db->loadResult())
			{
				$alias .= '-'.uniqid();
			}
			$query = $db->getQuery(true);
			$query->insert($db->quoteName('#__k2_tags'));
			$query->values((int)$tag->id.','.$db->quote($tag->name).','.$db->quote($alias).','.(int)$tag->published.','.$db->quote(''));
			$db->setQuery($query);
			$db->execute();
			$this->response->id = $tag->id;
		}

		if (count($tags) == 0)
		{
			$this->response->id = 0;
			$this->response->type = 'tagsxref';
		}
	}

	private function tagsxref($id)
	{
		$this->response->status = 'Processing Tags Reference';
		$db = JFactory::getDbo();
		$query = 'INSERT IGNORE INTO '.$db->quoteName('#__k2_tags_xref').'('.$db->quoteName('tagId').','.$db->quoteName('itemId').') SELECT '.$db->quoteName('tagID').','.$db->quoteName('itemID').' FROM '.$db->quoteName('#__k2_v2_tags_xref');
		$db->setQuery($query);
		$db->execute();
		$this->response->id = 0;
		$this->response->type = 'items';
	}

	private function items($id)
	{
		$this->response->status = 'Processing Items';
		$step = 1;
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('*')->from($db->quoteName('#__k2_v2_items'))->where($db->quoteName('id').' > '.$id)->order($db->quoteName('id'));
		$db->setQuery($query, 0, $step);
		$items = $db->loadObjectList();
		foreach ($items as $item)
		{

			$query = $db->getQuery(true);
			$query->select($db->quoteName('id'))->from($db->quoteName('#__k2_items'))->where($db->quoteName('alias').' = '.$db->quote($item->alias));

			$db->setQuery($query);
			if ($db->loadResult())
			{
				$item->alias .= '-'.uniqid();
			}

			$data = array();
			$data['id'] = '';
			$data['title'] = $item->title;
			$data['alias'] = $item->alias;
			$data['state'] = $item->trash ? -1 : $item->published;
			$data['featured'] = $item->featured;
			$data['access'] = $item->access;
			$data['catid'] = $item->catid;
			if ($data['catid'] == 1)
			{
				$data['catid'] = 99999;
			}
			$data['introtext'] = $item->introtext;
			$data['fulltext'] = $item->fulltext;
			$data['ordering'] = $item->ordering;
			$data['featured_ordering'] = $item->featured_ordering;
			$data['created'] = $item->created;
			$data['created_by'] = $item->created_by;
			$data['created_by_alias'] = $item->created_by_alias;
			$data['modified'] = $item->modified;
			$data['modified_by'] = $item->modified_by ? $item->modified_by : $item->created_by;
			$data['publish_up'] = $item->publish_up;
			$data['publish_down'] = $item->publish_down;
			$data['metadata'] = array();
			$data['metadata']['description'] = $item->metadesc;
			$data['metadata']['keywords'] = $item->metakey;
			$data['metadata']['robots'] = '';
			$data['metadata']['author'] = '';
			$metadata = new JRegistry($item->metadata);
			$metadata = $metadata->toArray();
			foreach ($metadata as $key => $value)
			{
				if ($key == 'robots' || $key == 'author')
				{
					$data['metadata'][$key] = $value;
				}
			}
			$data['language'] = $item->language;

			$model = K2Model::getInstance('Items');
			$model->setState('data', $data);
			if (!$model->save())
			{
				$this->response->errors[] = $model->getError();
				$this->response->failed = 1;
				return;
			}

			$lastInsertedId = $model->getState('id');

			$image = new stdClass;
			$image->caption = $item->image_caption;
			$image->credits = $item->image_credits;
			$image->flag = JFile::exists(JPATH_SITE.'/media/k2/items/cache/'.md5('Image'.$item->id).'_Generic.jpg') ? 1 : 0;
			$image = json_encode($image);

			$media = array();
			$mediaEntry = new stdClass;
			$mediaEntry->url = '';
			$mediaEntry->provider = '';
			$mediaEntry->id = '';
			$mediaEntry->embed = '';
			$mediaEntry->caption = $item->video_caption;
			$mediaEntry->credits = $item->video_credits;
			$mediaEntry->upload = '';
			if (!empty($item->video))
			{
				if (substr($item->video, 0, 1) !== '{')
				{
					$mediaEntry->embed = $item->video;
				}
				else
				{
					if (strpos($item->video, 'remote}'))
					{
						preg_match("#}(.*?){/#s", $item->video, $matches);

						if (substr($matches[1], 0, 4) != 'http')
						{
							$mediaEntry->upload = basename($matches[1]);
							if (JFile::exists(JPATH_SITE.'/media/k2/videos/'.$mediaEntry->upload))
							{
								if (!JFolder::exists(JPATH_SITE.'/media/k2/media'))
								{
									JFolder::create(JPATH_SITE.'/media/k2/media');
								}

								if (!JFolder::exists(JPATH_SITE.'/media/k2/media/'.$item->id))
								{
									JFolder::create(JPATH_SITE.'/media/k2/media/'.$item->id);
								}
								JFile::move(JPATH_SITE.'/media/k2/videos/'.$mediaEntry->upload, JPATH_SITE.'/media/k2/media/'.$item->id.'/'.$mediaEntry->upload);
							}
						}
						else
						{
							$mediaEntry->url = $matches[1];
						}
					}
					else
					{
						preg_match("#}(.*?){/#s", $item->video, $matches);
						$mediaEntry->id = $matches[1];
						$video = substr($item->video, 1);
						$mediaEntry->provider = substr($video, 0, strpos($video, '}'));
					}
				}

				$media[] = $mediaEntry;

			}
			$media = json_encode($media);

			$tags = array();

			$query = $db->getQuery(true);
			$query->select($db->quoteName('tagId'))->from($db->quoteName('#__k2_tags_xref'))->where($db->quoteName('itemId').' = '.$item->id);
			$db->setQuery($query);
			$tagIds = $db->loadColumn();

			if (count($tagIds))
			{
				$query = $db->getQuery(true);
				$query->select($db->quoteName('name').','.$db->quoteName('id'))->from($db->quoteName('#__k2_tags'))->where($db->quoteName('id').' IN('.implode(',', $tagIds).')')->where($db->quoteName('state').' = 1');
				$db->setQuery($query);
				$tags = $db->loadObjectList();
			}
			$tags = json_encode($tags);

			$query = $db->getQuery(true);
			$query->select($db->quoteName('id'))->from($db->quoteName('#__k2_attachments'))->where($db->quoteName('itemId').' = '.$item->id);
			$db->setQuery($query);
			$attachments = $db->loadColumn();
			$attachments = json_encode($attachments);

			$galleries = array();
			if (strpos($item->gallery, '{gallery') !== false)
			{
				$gallery = new stdClass;
				preg_match("#}(.*?){/#s", $item->gallery, $matches);
				$value = $matches[1];
				if (strpos($value, 'flickr.com') !== false)
				{
					$gallery->url = $value;
					$gallery->upload = '';
				}
				else
				{
					$gallery->url = '';
					$tmp = uniqid();
					if (JFolder::exists(JPATH_SITE.'/media/k2/galleries/'.$item->id))
					{
						JFolder::move(JPATH_SITE.'/media/k2/galleries/'.$item->id, JPATH_SITE.'/media/k2/galleries/'.$tmp);
						JFolder::create(JPATH_SITE.'/media/k2/galleries/'.$item->id);
						JFolder::move(JPATH_SITE.'/media/k2/galleries/'.$tmp, JPATH_SITE.'/media/k2/galleries/'.$item->id.'/'.$tmp);
					}
					$gallery->upload = $tmp;
				}
				$galleries[] = $gallery;
			}
			$galleries = json_encode($galleries);

			$extraFields = new stdClass;

			$itemFields = json_decode($item->extra_fields);
			if (is_array($itemFields))
			{
				foreach ($itemFields as $itemField)
				{
					$query = $db->getQuery(true);
					$query->select('*')->from($db->quoteName('#__k2_extra_fields'))->where($db->quoteName('id').' = '.$itemField->id);
					$db->setQuery($query);
					$field = $db->loadObject();

					// No field found, go for the next one
					if (!$field)
					{
						continue;
					}

					$entry = new stdClass;
					if ($field->type == 'text')
					{
						$entry->value = isset($itemField->value) ? $itemField->value : '';
					}
					else if ($field->type == 'textarea')
					{
						$entry->value = isset($itemField->value) ? $itemField->value : '';
					}
					else if ($field->type == 'date')
					{
						$entry->date = isset($itemField->value) ? $itemField->value : '';
					}
					else if ($field->type == 'image')
					{
						$entry->src = isset($itemField->value) ? $itemField->value : '';
						$entry->alt = isset($itemField->value) ? $itemField->value : '';
					}
					else if ($field->type == 'labels')
					{
						$entry->value = isset($itemField->value) ? $itemField->value : '';
					}
					else if ($field->type == 'link')
					{
						$entry->text = isset($itemField->value) && is_array($itemField->value) && isset($itemField->value[0]) ? $itemField->value[0] : '';
						$entry->url = isset($itemField->value) && is_array($itemField->value) && isset($itemField->value[1]) ? $itemField->value[1] : '';
						$entry->target = isset($itemField->value) && is_array($itemField->value) && isset($itemField->value[2]) ? $itemField->value[2] : '';
					}
					else if ($field->type == 'radio')
					{
						$entry->value = '';
						$json = json_decode($field->value);
						if (isset($json->options) && is_array($json->options) && isset($itemField->value) && $itemField->value && isset($json->options[($itemField->value - 1)]))
						{
							$entry->value = $json->options[($itemField->value - 1)];
						}
					}
					else if ($field->type == 'select')
					{
						$entry->value = '';
						$json = json_decode($field->value);

						if (isset($json->multiple) && $json->multiple)
						{
							$entry->value = array();
							if (isset($itemField->value) && is_array($itemField->value) && isset($json->options) && is_array($json->options))
							{
								foreach ($itemField->value as $value)
								{
									if (isset($json->options[($value - 1)]))
									{
										$entry->value[] = $json->options[($value - 1)];
									}
								}
							}
						}
						else
						{
							$entry->value = '';
							if (isset($json->options) && is_array($json->options) && isset($itemField->value) && $itemField->value && isset($json->options[($itemField->value - 1)]))
							{
								$entry->value = $json->options[($itemField->value - 1)];
							}
						}

					}
					else if ($field->type == 'csv')
					{
						$entry->value = isset($itemField->value) ? json_encode($itemField->value) : '';
					}
					else if ($field->type == 'header')
					{
						$entry->value = isset($itemField->value) ? $itemField->value : '';
					}
					$property = $field->id;
					$extraFields->$property = $entry;
				}
			}

			$extraFields = json_encode($extraFields);

			$updatedParams = new JRegistry($item->params);
			$this->updateImageSizeParam($updatedParams, 'itemImgSize', '');
			$this->updateImageSizeParam($updatedParams, 'itemRelatedImageSize', '');

			$query = $db->getQuery(true);
			$query->update($db->quoteName('#__k2_items'));
			$query->set(array(
				$db->quoteName('id').' = '.$item->id,
				$db->quoteName('ordering').' = '.(int)$item->ordering,
				$db->quoteName('image').' = '.$db->quote($image),
				$db->quoteName('media').' = '.$db->quote($media),
				$db->quoteName('tags').' = '.$db->quote($tags),
				$db->quoteName('attachments').' = '.$db->quote($attachments),
				$db->quoteName('galleries').' = '.$db->quote($galleries),
				$db->quoteName('extra_fields').' = '.$db->quote($extraFields),
				$db->quoteName('created').' = '.$db->quote($item->created),
				$db->quoteName('created_by').' = '.$db->quote($item->created_by),
				$db->quoteName('modified').' = '.$db->quote($item->modified),
				$db->quoteName('modified_by').' = '.$db->quote($item->modified_by),
				$db->quoteName('plugins').' = '.$db->quote($item->plugins),
				$db->quoteName('params').' = '.$db->quote($updatedParams->toString())
			))->where($db->quoteName('id').' = '.$lastInsertedId);
			$db->setQuery($query);
			$db->execute();

			// Update auto increment to avoid SQL errors
			$db->setQuery('ALTER TABLE '.$db->quoteName('#__k2_items').' AUTO_INCREMENT='.((int)$item->id + 1));
			$db->execute();

			$query = $db->getQuery(true);
			$query->select('COUNT(*)')->from('#__k2_v2_comments')->where($db->quoteName('itemID').' = '.(int)$item->id)->where('published = 1');
			$db->setQuery($query);
			$comments = $db->loadResult();

			$query = $db->getQuery(true);
			$query->update($db->quoteName('#__k2_items_stats'));
			$query->set($db->quoteName('hits').' = '.(int)$item->hits);
			$query->set($db->quoteName('comments').' = '.(int)$comments);
			$query->where($db->quoteName('itemId').' = '.(int)$item->id);
			$db->setQuery($query);
			$db->execute();

			$this->response->id = $item->id;
		}

		if (count($items) == 0)
		{
			$this->response->id = 0;
			$this->response->type = 'users';
		}

	}

	private function users($id)
	{
		$this->response->status = 'Processing Users';

		$step = 5;
		$db = JFactory::getDbo();

		if ($id == 0)
		{
			$query = $db->getQuery(true);
			$query->delete($db->quoteName('#__k2_users'));
			$db->setQuery($query);
			$db->execute();

			$query = $db->getQuery(true);
			$query->delete($db->quoteName('#__k2_users_stats'));
			$db->setQuery($query);
			$db->execute();
		}

		$query = $db->getQuery(true);
		$query->select('*')->from($db->quoteName('#__k2_v2_users'))->where($db->quoteName('userID').' > '.$id)->order($db->quoteName('userID'));
		$db->setQuery($query, 0, $step);
		$authors = $db->loadObjectList();
		foreach ($authors as $author)
		{
			$query = $db->getQuery(true);
			$query->select('COUNT(*)')->from('#__k2_users')->where($db->quoteName('id').' = '.(int)$author->userID);
			$db->setQuery($query);
			$exists = $db->loadResult();
			if ($exists)
			{
				continue;
			}

			$image = new stdClass;
			$image->flag = 0;
			if ($author->image)
			{
				$image->flag = 1;
				if (JFile::exists(JPATH_SITE.'/media/k2/users/'.$author->image))
				{
					JFile::move(JPATH_SITE.'/media/k2/users/'.$author->image, JPATH_SITE.'/media/k2/users/'.md5('Image'.$author->userID).'.jpg');
				}
			}
			$image = json_encode($image);
			$query = $db->getQuery(true);
			$query->insert($db->quoteName('#__k2_users'));
			$query->values((int)$author->userID.','.$db->quote($author->description).','.$db->quote($image).','.$db->quote($author->url).','.$db->quote($author->gender).','.$db->quote($author->notes).','.$db->quote('').','.$db->quote($author->ip).','.$db->quote($author->hostname).','.$db->quote($author->plugins));
			$db->setQuery($query);
			$db->execute();

			$query = $db->getQuery(true);
			$query->select('COUNT(*)')->from('#__k2_v2_items')->where($db->quoteName('created_by').' = '.(int)$author->userID);
			$db->setQuery($query);
			$items = $db->loadResult();

			$query = $db->getQuery(true);
			$query->select('COUNT(*)')->from('#__k2_v2_comments')->where($db->quoteName('userID').' = '.(int)$author->userID);
			$db->setQuery($query);
			$comments = $db->loadResult();

			$query = $db->getQuery(true);
			$query->insert($db->quoteName('#__k2_users_stats'));
			$query->values((int)$author->userID.','.(int)$items.','.(int)$comments);
			$db->setQuery($query);
			$db->execute();

			$this->response->id = $author->userID;
		}

		if (count($authors) == 0)
		{
			$this->response->id = 0;
			$this->response->type = 'setup';
		}

	}

	private function setup()
	{
		$this->response->status = 'Processing System Setup';

		$db = JFactory::getDbo();

		// Component parameters
		$componentParams = JComponentHelper::getParams('com_k2');
		$imageSizes = new stdClass;

		$index = 1;
		$imageSizeXSmall = new stdClass;
		$imageSizeXSmall->id = 'XS';
		$imageSizeXSmall->name = 'XSmall';
		$imageSizeXSmall->width = $componentParams->get('itemImageXS', 100);
		$imageSizeXSmall->quality = $componentParams->get('imagesQuality', 100);
		$imageSizes->$index = $imageSizeXSmall;

		$index = 2;
		$imageSizeSmall = new stdClass;
		$imageSizeSmall->id = 'S';
		$imageSizeSmall->name = 'Small';
		$imageSizeSmall->width = $componentParams->get('itemImageS', 200);
		$imageSizeSmall->quality = $componentParams->get('imagesQuality', 100);
		$imageSizes->$index = $imageSizeSmall;

		$index = 3;
		$imageSizeMedium = new stdClass;
		$imageSizeMedium->id = 'M';
		$imageSizeMedium->name = 'Medium';
		$imageSizeMedium->width = $componentParams->get('itemImageM', 400);
		$imageSizeMedium->quality = $componentParams->get('imagesQuality', 100);
		$imageSizes->$index = $imageSizeMedium;

		$index = 4;
		$imageSizeLarge = new stdClass;
		$imageSizeLarge->id = 'L';
		$imageSizeLarge->name = 'Large';
		$imageSizeLarge->width = $componentParams->get('itemImageL', 600);
		$imageSizeLarge->quality = $componentParams->get('imagesQuality', 100);
		$imageSizes->$index = $imageSizeLarge;

		$index = 5;
		$imageSizeXLarge = new stdClass;
		$imageSizeXLarge->id = 'XL';
		$imageSizeXLarge->name = 'XLarge';
		$imageSizeXLarge->width = $componentParams->get('itemImageXL', 900);
		$imageSizeXLarge->quality = $componentParams->get('imagesQuality', 100);
		$imageSizes->$index = $imageSizeXLarge;

		$componentParams->set('imageSizes', $imageSizes);

		$this->updateImageSizeParam($componentParams, 'facebookImage', 'Medium');

		$query = $db->getQuery(true);
		$query->update($db->quoteName('#__extensions'));
		$query->set($db->quoteName('params').' = '.$db->quote($componentParams->toString()));
		$query->where($db->quoteName('element').' = '.$db->quote('com_k2'));
		$db->setQuery($query);
		$db->execute();

		// Modules
		$modules = array(
			'comments',
			'content',
			'tools',
			'user',
			'users'
		);
		foreach ($modules as $module)
		{
			$query = $db->getQuery(true);
			$query->select('*')->from($db->quoteName('#__modules'))->where($db->quoteName('module').' = '.$db->quote('mod_k2_'.$module));
			$db->setQuery($query);
			$rows = $db->loadObjectList();

			foreach ($rows as $row)
			{
				$newParams = $this->upgradeParams($row->module, $row->params);
				$query = $db->getQuery(true);
				$query->update($db->quoteName('#__modules'))->set($db->quoteName('params').' = '.$db->quote($newParams))->where($db->quoteName('id').' = '.(int)$row->id);
				$db->setQuery($query);
				$db->execute();

			}

		}
		// Category view menu links
		$query = $db->getQuery(true);
		$query->select('*')->from($db->quoteName('#__menu'))->where($db->quoteName('link').' LIKE '.$db->quote('%com_k2&view=itemlist&layout=category%', false));
		$db->setQuery($query);
		$rows = $db->loadObjectList();

		foreach ($rows as $row)
		{
			$params = new JRegistry($row->params);
			$filter = new stdClass;
			$filter->categories = (array)$params->get('categories');
			$filter->enabled = count($filter->categories) ? '1' : '';
			$exists = array_search('1', $filter->categories);
			if ($exists !== false)
			{
				$filter->categories[$exists] = 99999;
			}
			$filter->recursive = $params->get('catCatalogMode') == 1 ? 0 : 1;
			$params->set('categories', $filter);

			$url = array();
			$link = str_replace('index.php?', '', $row->link);

			parse_str($link, $url);
			$flag = false;
			if (!isset($url['task']) || $url['task'] == '')
			{
				$url['task'] = 'category';
				$link = 'index.php?'.http_build_query($url);
				$flag = true;
			}

			$this->updateImageSizeParam($params, 'leadingImgSize', 'Large');
			$this->updateImageSizeParam($params, 'primaryImgSize', 'Medium');
			$this->updateImageSizeParam($params, 'secondaryImgSize', 'Small');
			$this->updateImageSizeParam($params, 'linksImgSize', 'XSmall');

			$query = $db->getQuery(true);
			$query->update($db->quoteName('#__menu'));
			$query->set($db->quoteName('params').' = '.$db->quote($params->toString()));
			if ($flag)
			{
				$query->set($db->quoteName('link').' = '.$db->quote($link));
			}
			$query->where($db->quoteName('id').' = '.(int)$row->id);
			$db->setQuery($query);
			$db->execute();
		}

		// Tag view menu links
		$query = $db->getQuery(true);
		$query->select('*')->from($db->quoteName('#__menu'))->where($db->quoteName('link').' LIKE '.$db->quote('%com_k2&view=itemlist&layout=tag%', false));
		$db->setQuery($query);
		$rows = $db->loadObjectList();
		foreach ($rows as $row)
		{
			$params = new JRegistry($row->params);
			$filter = new stdClass;
			$filter->categories = (array)$params->get('categoriesFilter');
			$filter->enabled = count($filter->categories) ? '1' : '';
			$exists = array_search('1', $filter->categories);
			if ($exists !== false)
			{
				$filter->categories[$exists] = 99999;
			}
			$filter->recursive = 0;
			$params->set('categoriesFilter', $filter);

			$link = str_replace('index.php?', '', $row->link);
			$url = array();
			parse_str($link, $url);
			$tag = $url['tag'];

			$query = $db->getQuery(true);
			$query->select($db->quoteName('id'))->from($db->quoteName('#__k2_tags'))->where($db->quoteName('name').' = '.$db->quote($tag));
			$db->setQuery($query);
			$tagId = $db->loadResult();
			$url['id'] = $tagId;
			$link = 'index.php?'.http_build_query($url);

			$query = $db->getQuery(true);
			$query->update($db->quoteName('#__menu'));
			$query->set($db->quoteName('params').' = '.$db->quote($params->toString()));
			$query->set($db->quoteName('link').' = '.$db->quote($link));
			$query->where($db->quoteName('id').' = '.(int)$row->id);
			$db->setQuery($query);
			$db->execute();
		}

		// Latest view menu links
		$query = $db->getQuery(true);
		$query->select('*')->from($db->quoteName('#__menu'))->where($db->quoteName('link').' LIKE '.$db->quote('%com_k2&view=latest%', false));
		$db->setQuery($query);
		$rows = $db->loadObjectList();
		foreach ($rows as $row)
		{
			$params = new JRegistry($row->params);

			if ($params->get('source') == 1)
			{
				$filter = new stdClass;
				$filter->categories = (array)$params->get('categoryIDs');
				$filter->enabled = count($filter->categories) ? '1' : '';
				$exists = array_search('1', $filter->categories);
				if ($exists !== false)
				{
					$filter->categories[$exists] = 99999;
				}
				$filter->recursive = 0;
				$params->set('categoryIDs', $filter);
				$params->set('source', 'categories');
			}
			else
			{
				$params->set('source', 'users');
			}

			$this->updateImageSizeParam($params, 'latestItemImageSize', 'Medium');

			$query = $db->getQuery(true);
			$query->update($db->quoteName('#__menu'));
			$query->set($db->quoteName('params').' = '.$db->quote($params->toString()));
			$query->where($db->quoteName('id').' = '.(int)$row->id);
			$db->setQuery($query);
			$db->execute();
		}

		$this->response->completed = 1;
	}

	private function upgradeParams($extension, $legacyParams)
	{
		$params = new JRegistry($legacyParams);

		switch($extension)
		{
			case 'mod_k2_comments' :
				// Usage
				$usage = $params->get('module_usage') ? 'commenters' : 'comments';
				$params->set('usage', $usage);

				// Categories filter
				$filter = new stdClass;
				$filter->enabled = $params->get('catfilter');
				$filter->categories = (array)$params->get('category_id');
				$exists = array_search('1', $filter->categories);
				if ($exists !== false)
				{
					$filter->categories[$exists] = 99999;
				}
				$filter->recursive = '';
				$params->set('catfilter', $filter);
				break;

			case 'mod_k2_content' :
				$template = $params->get('getTemplate');
				$params->set('template', $template);

				// Categories filter
				$filter = new stdClass;
				$filter->enabled = $params->get('catfilter');
				$filter->categories = (array)$params->get('category_id');
				$exists = array_search('1', $filter->categories);
				if ($exists !== false)
				{
					$filter->categories[$exists] = 99999;
				}
				$filter->recursive = $params->get('getChildren');
				$params->set('filter', $filter);

				$this->updateImageSizeParam($params, 'itemImgSize', 'Small');

				$limit = $params->get('itemCount');
				$params->set('limit', $limit);

				$params->set('sorting', $params->get('itemsOrdering'));
				$this->updateOrderingParam($params, 'sorting', 'id.reverse');

				$params->set('featured', $params->get('FeaturedItems'));
				$params->set('timeRange', $params->get('popularityRange'));
				$params->set('media', $params->get('videosOnly'));
				$params->set('jPlugins', $params->get('JPlugins'));
				$params->set('k2Plugins', $params->get('K2Plugins'));
				break;

			case 'mod_k2_tools' :
				$usage = $params->get('module_usage');
				switch($usage)
				{
					case '0' :
						$params->set('usage', 'archive');
						break;
					case '1' :
						$params->set('usage', 'authors');
						break;
					case '2' :
						$params->set('usage', 'calendar');
						break;
					case '3' :
						$params->set('usage', 'breadcrumbs');
						break;
					case '4' :
						$params->set('usage', 'categories');
						break;
					case '5' :
						$params->set('usage', 'categoriesList');
						break;
					case '6' :
						$params->set('usage', 'search');
						break;
					case '7' :
						$params->set('usage', 'tags');
						break;
					case '8' :
						$params->set('usage', 'custom');
						break;
				}

				$filter = new stdClass;
				$filter->enabled = $params->get('archiveCategory') ? '1' : '';
				$filter->categories = $params->get('archiveCategory') ? array($params->get('archiveCategory')) : array();
				$exists = array_search('1', $filter->categories);
				if ($exists !== false)
				{
					$filter->categories[$exists] = 99999;
				}
				$filter->recursive = '';
				$params->set('archiveCategory', $filter);

				$filter = new stdClass;
				$filter->enabled = $params->get('authors_module_category') ? '1' : '';
				$filter->categories = $params->get('authors_module_category') ? array($params->get('authors_module_category')) : array();
				$exists = array_search('1', $filter->categories);
				if ($exists !== false)
				{
					$filter->categories[$exists] = 99999;
				}
				$filter->recursive = '1';
				$params->set('authors_module_category', $filter);

				$filter = new stdClass;
				$filter->enabled = $params->get('calendarCategory') ? '1' : '';
				$filter->categories = $params->get('calendarCategory') ? array($params->get('calendarCategory')) : array();
				$exists = array_search('1', $filter->categories);
				if ($exists !== false)
				{
					$filter->categories[$exists] = 99999;
				}
				$filter->recursive = '';
				$params->set('calendarCategory', $filter);

				$filter = new stdClass;
				$filter->enabled = $params->get('root_id') ? '1' : '';
				$filter->categories = $params->get('root_id') ? array($params->get('root_id')) : array();
				$exists = array_search('1', $filter->categories);
				if ($exists !== false)
				{
					$filter->categories[$exists] = 99999;
				}
				$filter->recursive = '';
				$params->set('root_id', $filter);

				$filter = new stdClass;
				$filter->enabled = $params->get('root_id2') ? '1' : '';
				$filter->categories = $params->get('root_id2') ? array($params->get('root_id2')) : array();
				$exists = array_search('1', $filter->categories);
				if ($exists !== false)
				{
					$filter->categories[$exists] = 99999;
				}
				$filter->recursive = '';
				$params->set('root_id2', $filter);

				$filter = new stdClass;
				$filter->enabled = $params->get('catfilter');
				$filter->categories = (array)$params->get('category_id');
				$exists = array_search('1', $filter->categories);
				if ($exists !== false)
				{
					$filter->categories[$exists] = 99999;
				}
				$filter->recursive = $params->get('getChildren');
				$params->set('category_id', $filter);

				$filter = new stdClass;
				$filter->enabled = $params->get('cloud_category') ? '1' : '';
				$filter->categories = (array)$params->get('cloud_category');
				$exists = array_search('1', $filter->categories);
				if ($exists !== false)
				{
					$filter->categories[$exists] = 99999;
				}
				$filter->recursive = $params->get('cloud_category_recursive');
				$params->set('cloud_category', $filter);
				break;

			case 'mod_k2_users' :
				$filter = $params->get('filter');
				switch($filter)
				{
					case '1' :
						$params->set('filter', 'mostItems');
						break;
					case '2' :
						$params->set('filter', 'mostPopularItems');
						break;
					case '3' :
						$params->set('filter', 'mostCommentedItems');
						break;
				}
				break;
		}

		return $params->toString();

	}

	private function updateImageSizeParam(&$params, $name, $default = 'Small')
	{
		$image = $params->get($name, $default);
		if ($image == 'XSmall')
		{
			$params->set($name, 'XS');
		}
		else if ($image == 'Small')
		{
			$params->set($name, 'S');
		}
		else if ($image == 'Medium')
		{
			$params->set($name, 'M');
		}
		else if ($image == 'Large')
		{
			$params->set($name, 'L');
		}
		else if ($image == 'XLarge')
		{
			$params->set($name, 'XL');
		}
	}

	private function updateOrderingParam(&$params, $name, $default = 'id.reverse')
	{
		$value = $params->get($name, $default);
		if ($value == '')
		{
			$params->set($name, 'id.reverse');
		}
		else if ($value == 'date')
		{
			$params->set($name, 'created');
		}
		else if ($value == 'rdate')
		{
			$params->set($name, 'created.reverse');
		}
		else if ($value == 'publishUp')
		{
			$params->set($name, 'publishUp.reverse');
		}
		else if ($value == 'alpha')
		{
			$params->set($name, 'title');
		}
		else if ($value == 'ralpha')
		{
			$params->set($name, 'title.reverse');
		}
		else if ($value == 'order')
		{
			$params->set($name, 'ordering');
		}
		else if ($value == 'rorder')
		{
			$params->set($name, 'ordering.reverse');
		}
		else if ($value == 'featured')
		{
			$params->set($name, 'featured.reverse');
		}
		else if ($value == 'hits')
		{
			$params->set($name, 'hits.reverse');
		}
		else if ($value == 'modified')
		{
			$params->set($name, 'modified.reverse');
		}
		elseif ($value == 'reversedefault')
		{
			$params->set($name, 'id');
		}
		elseif ($value == 'rand')
		{
			$params->set($name, 'random');
		}
		elseif ($value == 'comments')
		{
			$params->set($name, 'comments.reverse');
		}
	}

}
