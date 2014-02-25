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

		// Set rows
		$this->setRows();

		// Set pagination
		$this->setPagination();

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
		$this->setTitle('K2_ITEM');

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
		$this->setUserState('limit', 10, 'int');
		$this->setUserState('page', 1, 'int');
		$this->setUserState('search', '', 'string');
		$this->setUserState('access', 0, 'int');
		$this->setUserState('state', '', 'cmd');
		$this->setUserState('featured', '', 'cmd');
		$this->setUserState('category', '', 'cmd');
		$this->setUserState('author', 0, 'int');
		$this->setUserState('tag', 0, 'int');
		$this->setUserState('language', '', 'string');
		$this->setUserState('sorting', 'id', 'string');
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
		$sortingOptions = array('K2_ID' => 'id', 'K2_TITLE' => 'title', 'K2_ORDERING' => 'ordering', 'K2_FEATURED_ORDERING' => 'featured_ordering', 'K2_STATE' => 'state', 'K2_FEATURED' => 'featured', 'K2_CATEGORY' => 'category', 'K2_AUTHOR' => 'author', 'K2_MODERATOR' => 'moderator', 'K2_ACCESS_LEVEL' => 'access', 'K2_CREATED' => 'created', 'K2_MODIFIED' => 'modified', 'K2_LANGUAGE' => 'language', 'K2_HITS' => 'hits');
		K2Response::addFilter('sorting', JText::_('K2_SORT_BY'), K2HelperHTML::sorting($sortingOptions), false, 'header');

		// Categories filter
		K2Response::addFilter('category', JText::_('K2_CATEGORY'), K2HelperHTML::categories('category', null, 'K2_ANY'), false, 'header');

		// Author filter
		K2Response::addFilter('author', JText::_('K2_AUTHOR'), '<input data-widget="user" data-null="'.JText::_('K2_ANY').'" data-min="0" data-name="'.JText::_('K2_ANY').'" type="hidden" name="author" value="" />', false, 'header');
		
		// Author filter
		K2Response::addFilter('tag', JText::_('K2_TAG'), '<input data-widget="tag" data-null="'.JText::_('K2_ANY').'" data-min="0" data-name="'.JText::_('K2_ANY').'" type="hidden" name="tag" value="" />', false, 'header');

		// Language filter
		K2Response::addFilter('language', JText::_('K2_SELECT_LANGUAGE'), K2HelperHTML::language('language', '', 'K2_ANY'), false, 'header');

		// Search filter
		K2Response::addFilter('search', JText::_('K2_SEARCH'), K2HelperHTML::search(), false, 'sidebar');

		// State filter
		K2Response::addFilter('state', JText::_('K2_STATE'), K2HelperHTML::state('state', null, 'K2_ANY', true, 'radio'), true, 'sidebar');

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
			K2Response::addToolbarAction('feature', 'K2_FEATURE', array('data-state' => 'featured', 'data-value' => '1', 'class' => 'appActionSetState', 'id' => 'appActionFeature'));
			K2Response::addToolbarAction('unfeature', 'K2_UNFEATURE', array('data-state' => 'featured', 'data-value' => '0', 'class' => 'appActionSetState', 'id' => 'appActionUnFeature'));
		}

		if ($canEditState)
		{
			K2Response::addToolbarAction('publish', 'K2_PUBLISH', array('data-state' => 'state', 'data-value' => '1', 'class' => 'appActionSetState', 'id' => 'appActionPublish'));
			K2Response::addToolbarAction('unpublish', 'K2_UNPUBLISH', array('data-state' => 'state', 'data-value' => '0', 'class' => 'appActionSetState', 'id' => 'appActionUnpublish'));

			K2Response::addToolbarAction('trash', 'K2_TRASH', array('data-state' => 'state', 'data-value' => '-1', 'class' => 'appActionSetState', 'id' => 'appActionTrash'));
		}
		K2Response::addToolbarAction('batch', 'K2_BATCH', array('id' => 'appActionBatch'));

		if ($canDelete)
		{
			K2Response::addToolbarAction('remove', 'K2_DELETE', array('id' => 'appActionRemove'));
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
		$form->extraFields = K2HelperExtraFields::getItemExtraFields($row->catid, $row->extra_fields);
		
		
		// Import plugins to extend the form
		JPluginHelper::importPlugin('k2');

		// Get the dispatcher.
		$dispatcher = JDispatcher::getInstance();
		
			
			
		$dispatcher->trigger('onK2RenderAdminForm', array('com_k2.item', &$form, $row));
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
		if ($user->authorise('k2.item.create', 'com_k2'))
		{
			K2Response::addAction('add', 'K2_ADD', array('class' => 'appAction', 'id' => 'appActionAdd'));
		}
	}

	protected function setBatchActions()
	{
		K2Response::addBatchAction('category', 'K2_CATEGORY', K2HelperHTML::categories('catid', null, 'K2_LEAVE_UNCHANGED'));
		K2Response::addBatchAction('access', 'K2_ACCESS', JHtml::_('access.level', 'access', null, '', array(JHtml::_('select.option', '', JText::_('K2_LEAVE_UNCHANGED')))));
		K2Response::addBatchAction('language', 'K2_LANGUAGE', K2HelperHTML::language('language', '', 'K2_LEAVE_UNCHANGED'));
	}

}
