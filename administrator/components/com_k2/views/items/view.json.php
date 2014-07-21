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

require_once JPATH_ADMINISTRATOR.'/components/com_k2/views/view.php';

/**
 * Items JSON view.
 */

class K2ViewItems extends K2View
{

	/**
	 * Builds the response variables needed for rendering a list.
	 * Usually there will be no need to override this function.
	 *
	 * @return void
	 */

	public function show()
	{
		// Set title
		$this->setTitle('K2_ITEMS');

		// Set user states
		$this->setUserStates();

		// Set pagination
		$this->setPagination();

		// Set rows
		$this->setRows();

		// Set filters
		$this->setFilters();

		// Set toolbar
		$this->setToolbar();

		// Set menu
		$this->setMenu();

		// Set Actions
		$this->setListActions();

		// Set Batch Actions
		$this->setBatchActions();

		// Render
		parent::render();
	}

	/**
	 * Builds the response variables needed for rendering a form.
	 * Usually there will be no need to override this function.
	 *
	 * @param integer $id	The id of the resource to load.
	 *
	 * @return void
	 */

	public function edit($id = null)
	{
		// Set title
		$this->setTitle($id ? 'K2_EDIT_ITEM' : 'K2_ADD_ITEM');

		// Set row
		$this->setRow($id);

		// Set form
		$this->setForm();

		// Set menu
		$this->setMenu('edit');

		// Set Actions
		$this->setFormActions();

		// Render
		parent::render();
	}

	protected function setUserStates()
	{
		$this->setUserState('persist', 1, 'int');
		$this->setUserState('limit', 10, 'int', $this->getUserState('persist'));
		$this->setUserState('page', 1, 'int');
		$this->setUserState('search', '', 'string');
		$this->setUserState('access', 0, 'int');
		$this->setUserState('state', '', 'cmd');
		$this->setUserState('featured', '', 'cmd');
		$this->setUserState('category', 0, 'cmd', $this->getUserState('persist'));
		$this->setUserState('author', 0, 'int');
		$this->setUserState('tag', 0, 'int');
		$this->setUserState('language', '', 'string');
		$this->setUserState('sorting', '', 'string');
		$this->setUserState('recursive', 1, 'int');
		if ($this->getUserState('author') > 0)
		{
			$this->setUserState('authorName', JFactory::getUser($this->getUserState('author'))->name, 'string', false);
		}
		if ($this->getUserState('tag') > 0)
		{
			$this->setUserState('tagName', K2Tags::getInstance($this->getUserState('tag'))->name, 'string', false);
		}
	}

	protected function setFilters()
	{
		// Sorting filter
		$sortingOptions = array(
			'K2_NONE' => '',
			'K2_ID_ASC' => 'id',
			'K2_ID_DESC' => 'id.reverse',
			'K2_TITLE_ASC' => 'title',
			'K2_TITLE_DESC' => 'title.reverse',
			'K2_ORDERING' => 'ordering',
			'K2_FEATURED_ORDERING' => 'featured_ordering',
			'K2_STATE_ASC' => 'state',
			'K2_STATE_DESC' => 'state.reverse',
			'K2_FEATURED_ASC' => 'featured',
			'K2_FEATURED_DESC' => 'featured.reverse',
			'K2_CATEGORY_ASC' => 'category',
			'K2_CATEGORY_DESC' => 'category.reverse',
			'K2_AUTHOR_ASC' => 'author',
			'K2_AUTHOR_DESC' => 'author.reverse',
			'K2_MODERATOR_ASC' => 'moderator',
			'K2_MODERATOR_DESC' => 'moderator.reverse',
			'K2_ACCESS_LEVEL_ASC' => 'access',
			'K2_ACCESS_LEVEL_DESC' => 'access.reverse',
			'K2_CREATED_ASC' => 'created',
			'K2_CREATED_DESC' => 'created.reverse',
			'K2_MODIFIED_ASC' => 'modified',
			'K2_MODIFIED_DESC' => 'modified.reverse',
			'K2_LANGUAGE_ASC' => 'language',
			'K2_LANGUAGE_DESC' => 'language.reverse',
			'K2_HITS_ASC' => 'hits',
			'K2_HITS_DESC' => 'hits.reverse'
		);
		K2Response::addFilter('sorting', JText::_('K2_SORT_BY'), K2HelperHTML::sorting($sortingOptions), false, 'header');

		// Categories filter
		K2Response::addFilter('category', JText::_('K2_CATEGORY'), K2HelperHTML::categories('category', null, 'K2_ANY'), false, 'header');

		// Author filter
		K2Response::addFilter('author', JText::_('K2_AUTHOR'), '<input data-widget="user" data-null="'.JText::_('K2_ANY').'" data-min="0" data-name="'.JText::_('K2_ANY').'" type="hidden" name="author" value="" />', false, 'header');

		// Author filter
		K2Response::addFilter('tag', JText::_('K2_TAG'), '<input data-widget="tag" data-null="'.JText::_('K2_ANY').'" data-min="0" data-name="'.JText::_('K2_ANY').'" type="hidden" name="tag" value="" />', false, 'header');

		// Access filter
		K2Response::addFilter('access', JText::_('K2_ACCESS'), JHtml::_('access.level', 'access', null, '', array(JHtml::_('select.option', '0', JText::_('K2_ANY')))), false, 'header');

		// Language filter
		K2Response::addFilter('language', JText::_('K2_LANGUAGE'), K2HelperHTML::language('language', '', 'K2_ANY'), false, 'header');

		// Search filter
		K2Response::addFilter('search', JText::_('K2_SEARCH'), K2HelperHTML::search(), false, 'sidebar');

		// State filter
		K2Response::addFilter('state', JText::_('K2_STATE'), K2HelperHTML::state('state', null, 'K2_ANY', true), true, 'sidebar');

		// Featured filter
		K2Response::addFilter('featured', JText::_('K2_FEATURED'), K2HelperHTML::featured(), true, 'sidebar');
	}

	protected function setToolbar()
	{
		// Check permissions for the current rows to determine if we should show the states togglers and the delete button
		$rows = K2Response::getRows();
		$canEditState = false;
		$canEditFeaturedState = false;
		$canDelete = false;
		foreach ($rows as $row)
		{
			if ($row->canEditState)
			{
				$canEditState = true;
			}
			if ($row->canEditFeaturedState)
			{
				$canEditFeaturedState = true;
			}
			if ($row->canDelete)
			{
				$canDelete = true;
			}
		}

		if ($canEditFeaturedState)
		{
			K2Response::addToolbarAction('feature', 'K2_FEATURE', array(
				'data-state' => 'featured',
				'data-value' => '1',
				'data-action' => 'set-state'
			));
			K2Response::addToolbarAction('unfeature', 'K2_UNFEATURE', array(
				'data-state' => 'featured',
				'data-value' => '0',
				'data-action' => 'set-state'
			));
		}

		if ($canEditState)
		{
			K2Response::addToolbarAction('publish', 'K2_PUBLISH', array(
				'data-state' => 'state',
				'data-value' => '1',
				'data-action' => 'set-state'
			));
			K2Response::addToolbarAction('unpublish', 'K2_UNPUBLISH', array(
				'data-state' => 'state',
				'data-value' => '0',
				'data-action' => 'set-state'
			));

			K2Response::addToolbarAction('trash', 'K2_TRASH', array(
				'data-state' => 'state',
				'data-value' => '-1',
				'data-action' => 'set-state'
			));
		}
		K2Response::addToolbarAction('batch', 'K2_BATCH', array('data-action' => 'batch'));

		if ($canDelete)
		{
			K2Response::addToolbarAction('remove', 'K2_DELETE', array('data-action' => 'remove'));
		}
	}

	protected function setFormFields(&$form, $row)
	{
		$form->state = K2HelperHTML::state('state', $row->state, false, true);
		$form->language = K2HelperHTML::language('language', $row->language);
		$form->access = JHtml::_('access.level', 'access', $row->access, '', false);
		$form->category = K2HelperHTML::categories('catid', $row->catid, 'K2_SELECT_CATEGORY');
		require_once JPATH_ADMINISTRATOR.'/components/com_k2/classes/editor.php';
		$config = JFactory::getConfig();
		$editor = K2Editor::getInstance($config->get('editor'));

		$params = JComponentHelper::getParams('com_k2');
		if ($params->get('mergeEditors'))
		{
			$value = trim($row->fulltext) != '' ? $row->introtext.'<hr id="system-readmore" />'.$row->fulltext : $row->introtext;
			$form->text = $editor->display('text', $value, '100%', '300', '40', '5');
		}
		else
		{
			$form->introtext = $editor->display('introtext', $row->introtext, '100%', '300', '40', '5', array('readmore'));
			$form->fulltext = $editor->display('fulltext', $row->fulltext, '100%', '300', '40', '5', array('readmore'));
		}

		require_once JPATH_ADMINISTRATOR.'/components/com_k2/helpers/extrafields.php';
		$form->extraFields = K2HelperExtraFields::getItemExtraFieldsGroups($row->catid, $row->extra_fields);

	}

	/**
	 * Hook for children views to allow them set the menu for the list requests.
	 * Children views usually will not need to override this method.
	 *
	 * @return void
	 */
	protected function setListActions()
	{
		$user = JFactory::getUser();
		$params = JComponentHelper::getParams('com_k2');
		if ($user->authorise('k2.item.create', 'com_k2'))
		{
			K2Response::addAction('add', 'K2_ADD', array('data-action' => 'add'));
		}
	}

	protected function setBatchActions()
	{
		K2Response::addBatchAction('category', 'K2_CATEGORY', K2HelperHTML::categories('catid', null, 'K2_LEAVE_UNCHANGED'));
		K2Response::addBatchAction('access', 'K2_ACCESS', JHtml::_('access.level', 'access', null, '', array(JHtml::_('select.option', '', JText::_('K2_LEAVE_UNCHANGED')))));
		K2Response::addBatchAction('language', 'K2_LANGUAGE', K2HelperHTML::language('language', '', 'K2_LEAVE_UNCHANGED'));
	}

	protected function prepareRow($row)
	{
		// Tags
		$row->tags = $row->getTags();
		$tagsValue = array();
		foreach ($row->tags as $tag)
		{
			$tagsValue[] = $tag->name;
		}
		$row->tagsValue = implode(',', $tagsValue);
		$user = JFactory::getUser();
		$canCreateTag = $user->authorise('k2.tags.create', 'com_k2') || $user->authorise('k2.tags.manage', 'com_k2');
		$row->canCreateTag = $canCreateTag ? '1' : '';

		// Attachments
		$row->attachments = $row->getAttachments();

		// Revisions
		$params = JComponentHelper::getParams('com_k2');
		$row->revisionsEnabled = (bool)$params->get('revisions');
		$row->revisions = array();
		if ($row->canEdit && $row->revisionsEnabled)
		{
			$row->revisions = $row->getRevisions();
		}

		// Media
		$row->allVideos = JPluginHelper::isEnabled('content', 'jw_allvideos');
		$row->media = $row->getMedia();

		// Galleries
		$row->sigPro = JPluginHelper::isEnabled('content', 'jw_sigpro');
		$row->galleries = $row->getGalleries();

	}

}
