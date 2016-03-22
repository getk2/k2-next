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

require_once JPATH_ADMINISTRATOR.'/components/com_k2/controller.php';

/**
 * Items JSON controller.
 */

class K2ControllerItems extends K2Controller
{

	protected function onBeforeRead($mode, $id)
	{
		$user = JFactory::getUser();
		$authorized = false;
		if ($mode == 'row')
		{
			// Create
			if ($id)
			{
				$item = K2Items::getInstance($id);
				$authorized = $item->canEdit;
			}
			else
			{
				$authorized = $user->authorise('k2.item.create', 'com_k2');
			}
		}
		else
		{
			$authorized = $user->authorise('k2.item.create', 'com_k2') || $user->authorise('k2.item.edit', 'com_k2') || $user->authorise('k2.item.edit.own', 'com_k2') || $user->authorise('k2.item.edit.state', 'com_k2') || $user->authorise('k2.item.edit.state.featured', 'com_k2') || $user->authorise('k2.item.delete', 'com_k2');
		}
		return $authorized;
	}

	protected function getInputData()
	{
		$data = parent::getInputData();
		$params = JComponentHelper::getParams('com_k2');
		if ($params->get('mergeEditors'))
		{
			$data['text'] = JComponentHelper::filterText($this->input->get('text', '', 'raw'));
		}
		else
		{
			$data['introtext'] = JComponentHelper::filterText($this->input->get('introtext', '', 'raw'));
			$data['fulltext'] = JComponentHelper::filterText($this->input->get('fulltext', '', 'raw'));
		}
		$data['media'] = JComponentHelper::filterText($this->input->get('media', '', 'raw'));
		$data['extra_fields'] = $this->input->get('extra_fields', '', 'raw');
		return $data;
	}

	public function resetHits()
	{
		// Check for token
		JSession::checkToken() or K2Response::throwError(JText::_('JINVALID_TOKEN'));

		// User
		$user = JFactory::getUser();

		// Item
		$application = JFactory::getApplication();
		$id = $application->input->get('id');
		$item = K2Items::getInstance($id);
		if (!$item->canEdit)
		{
			K2Response::throwError(JText::_('K2_YOU_ARE_NOT_AUTHORIZED_TO_PERFORM_THIS_OPERATION'), 403);
		}
		$statistics = K2Model::getInstance('Statistics');
		$statistics->resetItemHitsCounter($item->id);
		echo json_encode(K2Response::render());
		return $this;
	}

	public function close()
	{
		// Check for token
		JSession::checkToken() or K2Response::throwError(JText::_('JINVALID_TOKEN'));

		// User
		$user = JFactory::getUser();

		if (!$user->authorise('k2.item.edit', 'com_k2'))
		{
			K2Response::throwError(JText::_('K2_YOU_ARE_NOT_AUTHORIZED_TO_PERFORM_THIS_OPERATION'), 403);
		}
		$this->model->close();
		return $this;
	}

	public function import()
	{
		// Check for token
		JSession::checkToken() or K2Response::throwError(JText::_('JINVALID_TOKEN'));

		// Get application
		$application = JFactory::getApplication();

		// get user
		$user = JFactory::getUser();

		// Get session
		$session = JFactory::getSession();

		// Get database
		$db = JFactory::getDbo();

		// Get id to import
		$id = $application->input->get('id', 0, 'int');

		// Permissions check
		if (!$user->authorise('core.admin', 'com_k2'))
		{
			K2Response::throwError(JText::_('K2_YOU_ARE_NOT_AUTHORIZED_TO_PERFORM_THIS_OPERATION'), 403);
		}

		// Setup session variables
		if ($id == 0)
		{
			$mapping = new stdClass;
			$mapping->articles = array();
			$mapping->categories = array();
			$mapping->parents = array();

			// Count all articles to provide a progress status
			$query = $db->getQuery(true);
			$query->select('COUNT(*)')->from($db->quoteName('#__content'));
			$db->setQuery($query);
			$total = $db->loadResult();
			$session->set('k2.import.total', $total);
			$session->set('k2.import.processed', 0);

			// Import all categories at once in the first request
			$query = $db->getQuery(true);
			$query->select('*')->from($db->quoteName('#__categories'))->where($db->quoteName('extension').' = '.$db->quote('com_content'))->order($db->quoteName('lft'));
			$db->setQuery($query);
			$categories = $db->loadObjectList();

			// Import
			foreach ($categories as $category)
			{
				$categoryId = $this->importCategory($category);
				$mapping->categories[(int)$category->id] = (int)$categoryId;
				$mapping->parents[(int)$category->id] = (int)$category->parent_id;
			}

			// Fix tree
			foreach ($mapping->categories as $sourceCategoryId => $importedCategoryId)
			{
				$parentId = $mapping->categories[$mapping->parents[$sourceCategoryId]];
				$table = JTable::getInstance('Categories', 'K2Table');
				$table->moveByReference($parentId, 'last-child', $importedCategoryId);
			}

		}
		else
		{
			$mapping = $session->get('k2.import.mapping');
		}

		// Articles import
		$step = $id == 0 ? 1 : 10;

		// Get next articles to import
		$query = $db->getQuery(true);
		$query->select('*')->from($db->quoteName('#__content'))->where($db->quoteName('id').' > '.$id)->order($db->quoteName('id'));
		$db->setQuery($query, 0, $step);
		$articles = $db->loadObjectList();

		// Check if we are done
		if (!count($articles))
		{
			// Clear session
			$mapping = new stdClass;
			$mapping->articles = array();
			$mapping->categories = array();
			$session->set('k2.import.mapping', $mapping);
			$session->set('k2.import.total', 0);
			return $this;
		}

		foreach ($articles as $article)
		{
			// Detect category Id
			$categoryId = $mapping->categories[$article->catid];

			// Import the item
			$itemId = $this->importArticle($article, $categoryId);

			// Update article/items mapping
			$mapping->articles[$article->id] = $itemId;
		}

		// Update mapping to session
		$session->set('k2.import.mapping', $mapping);
		$session->set('k2.import.processed', (int)$session->get('k2.import.processed') + count($articles));


		// Output
		$response = new stdClass;
		$response->lastId = $article->id;
		$response->total = $session->get('k2.import.total');
		$response->percentage = round(100*($session->get('k2.import.processed')/$session->get('k2.import.total')));
		echo json_encode($response);

		// Return
		return $this;

	}

	private function importArticle($article, $categoryId)
	{
		$itemData = array();
		$itemData['id'] = null;
		$itemData['catid'] = $categoryId;
		$itemData['title'] = $article->title;

		if ($article->state < 0)
		{
			$itemData['state'] = -1;
		}
		else if ($article->state > 0)
		{
			$itemData['state'] = 1;
		}
		else
		{
			$itemData['state'] = 0;
		}

		// Detect featured state
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select($db->quoteName('content_id'))->from($db->quoteName('#__content_frontpage'))->where($db->quoteName('content_id').' = '.$article->id);
		$db->setQuery($query);
		$featured = $db->loadResult();

		$itemData['featured'] = $featured ? 1 : 0;
		$itemData['introtext'] = $article->introtext;
		$itemData['fulltext'] = $article->fulltext;
		$itemData['created_by_alias'] = $article->created_by_alias;
		$itemData['publish_up'] = $article->publish_up;
		$itemData['publish_down'] = $article->publish_down;
		$itemData['access'] = $article->access;
		$itemData['ordering'] = $article->ordering;
		$metadata = (array)json_decode($article->metadata);
		$metadata['keywords'] = $article->metakey;
		$metadata['description'] = $article->metadesc;
		$itemData['metadata'] = json_encode($metadata);
		$itemData['language'] = $article->language;
		$itemData['tags'] = $article->metakey;

		$articleImages = new JRegistry($article->images);

		if ($articleImages->get('image_fulltext'))
		{
			$image = K2HelperImages::add('item', null, $articleImages->get('image_fulltext'));
			$itemData['image'] = array('id' => '', 'temp' => $image->temp, 'path' => '', 'remove' => 0, 'caption' => $articleImages->get('image_fulltext_caption'), 'credits' => '');
		}
		else if ($articleImages->get('image_intro'))
		{
			$image = K2HelperImages::add('item', null, $articleImages->get('image_intro'));
			$itemData['image'] = array('id' => '', 'temp' => $image->temp, 'path' => '', 'remove' => 0, 'caption' => $articleImages->get('image_intro_caption'), 'credits' => '');
		}

		$model = K2Model::getInstance('Items');
		$model->setState('data', $itemData);
		if (!$model->save())
		{
			K2Response::throwError($model->getError());
		}

		// Get generated item id
		$itemId = $model->getState('id');

		// Import JForm
		jimport('joomla.form.form');

		// Determine form name and path
		$formName = 'K2ItemsForm';
		$formPath = JPATH_ADMINISTRATOR.'/components/com_k2/models/items.xml';
		$form = JForm::getInstance($formName, $formPath);
		$params = new JRegistry('');
		foreach ($form->getFieldset() as $field)
		{
			$params->def($field->__get('fieldname'), $field->__get('value'));
		}

		// Update date and author information since the model has auto set this data during save
		$query = $db->getQuery(true);
		$query->update($db->quoteName('#__k2_items'));
		$query->set($db->quoteName('created').' = '.$db->quote($article->created));
		$query->set($db->quoteName('modified').' = '.$db->quote($article->modified));
		$query->set($db->quoteName('created_by').' = '.$db->quote($article->created_by));
		$query->set($db->quoteName('modified_by').' = '.$db->quote($article->modified_by));
		$query->set($db->quoteName('galleries').' = '.$db->quote('[]'));
		$query->set($db->quoteName('media').' = '.$db->quote('[]'));
		$query->set($db->quoteName('tags').' = '.$db->quote('[]'));
		$query->set($db->quoteName('attachments').' = '.$db->quote('[]'));
		$query->set($db->quoteName('galleries').' = '.$db->quote('[]'));
		$query->set($db->quoteName('params').' = '.$db->quote($params->toString()));
		$query->where($db->quoteName('id').' = '.$itemId);
		$db->setQuery($query);
		$db->execute();

		return $itemId;
	}

	private function importCategory($category)
	{
		$categoryData = array();
		$categoryData['id'] = null;
		$categoryData['title'] = $category->title;
		$categoryData['description'] = $category->description;
		if ($category->published < 0)
		{
			$categoryData['state'] = -1;
		}
		else if ($category->published > 0)
		{
			$categoryData['state'] = 1;
		}
		else
		{
			$categoryData['state'] = 0;
		}
		$categoryData['parent_id'] = 0;
		$categoryData['access'] = $category->access;
		$categoryData['language'] = $category->language;
		$categoryParams = new JRegistry($category->params);
		$categoryImage = $categoryParams->get('image');
		if ($categoryImage)
		{
			$image = K2HelperImages::add('category', null, $categoryImage);
			$categoryData['image'] = array('id' => '', 'temp' => $image->temp, 'path' => '', 'remove' => 0, 'caption' => '', 'credits' => '');
		}

		$model = K2Model::getInstance('Categories');
		$model->setState('data', $categoryData);
		if (!$model->save())
		{
			K2Response::throwError($model->getError());
		}

		// Get generated category id
		$categoryId = $model->getState('id');

		// Import JForm
		jimport('joomla.form.form');

		// Determine form name and path
		$formName = 'K2CategoriesForm';
		$formPath = JPATH_ADMINISTRATOR.'/components/com_k2/models/categories.xml';
		$form = JForm::getInstance($formName, $formPath);
		$params = new JRegistry('');
		foreach ($form->getFieldset() as $field)
		{
			$params->def($field->__get('fieldname'), $field->__get('value'));
		}

		// Update date and author information since the model has auto set this data during save
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->update($db->quoteName('#__k2_categories'));
		$query->set($db->quoteName('created').' = '.$db->quote($category->created_time));
		$query->set($db->quoteName('modified').' = '.$db->quote($category->modified_time));
		$query->set($db->quoteName('created_by').' = '.$db->quote($category->created_user_id));
		$query->set($db->quoteName('modified_by').' = '.$db->quote($category->modified_user_id));
		$query->set($db->quoteName('params').' = '.$db->quote($params->toString()));
		$query->where($db->quoteName('id').' = '.$categoryId);
		$db->setQuery($query);
		$db->execute();

		return $categoryId;
	}

}
