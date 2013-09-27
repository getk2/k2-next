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

class K2ViewCategories extends K2View
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
		K2Response::setTitle(JText::_('K2_CATEGORIES'));

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
		$this->setActions();

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
		K2Response::setTitle(JText::_('K2_CATEGORY'));

		// Set row
		$this->setRow($id);

		// Set form
		$this->setForm();

		// Set menu
		$this->setMenu('edit');

		// Set Actions
		$this->setActions('edit');

		// Render
		parent::render();
	}

	protected function setUserStates()
	{
		$this->setUserState('limit', 10, 'int');
		$this->setUserState('page', 1, 'int');
		$this->setUserState('search', '', 'string');
		$this->setUserState('access', 0, 'int');
		$this->setUserState('trashed', null, 'cmd');
		$this->setUserState('published', null, 'cmd');
		$this->setUserState('language', '', 'string');
		$this->setUserState('sorting', 'category.id DESC', 'string');
	}

	protected function setFilters()
	{

		// Language filter
		K2Response::addFilter('language', JText::_('K2_SELECT_LANGUAGE'), K2HelperHTML::language($this->getUserState('language')));

		// Sorting filter
		$sortingOptions = array(
			'K2_ID' => 'category.id DESC',
			'K2_TITLE' => 'category.title ASC',
			'K2_ORDERING' => 'category.lft ASC',
			'K2_PUBLISHED' => 'category.published DESC',
			'K2_AUTHOR' => 'authorName ASC',
			'K2_MODERATOR' => 'moderatorName ASC',
			'K2_ACCESS_LEVEL' => 'accessLevel ASC',
			'K2_CREATED' => 'category.created DESC',
			'K2_MODIFIED' => 'category.modified DESC'
		);
		K2Response::addFilter('sorting', JText::_('K2_SORT_BY'), K2HelperHTML::sorting($this->getUserState('sorting'), $sortingOptions));

		// Published filter
		K2Response::addFilter('published', JText::_('K2_PUBLISHED'), K2HelperHTML::published($this->getUserState('published')), true);

	}

	protected function setToolbar()
	{
		K2Response::addToolbarAction('published', 'K2_TOGGLE_PUBLISHED_STATE', array(
			'data-state' => 'published',
			'class' => 'jwBatchToggler',
			'id' => 'jwBatchPublishedToggler'
		));
		K2Response::addToolbarAction('batch', 'K2_BATCH', array('id' => 'jwBatchButton'));

		K2Response::addToolbarAction('delete', 'K2_DELETE', array('id' => 'jwDeleteButton'));
	}

}
