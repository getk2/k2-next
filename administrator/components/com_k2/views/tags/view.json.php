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
 * Tags JSON view.
 */

class K2ViewTags extends K2View
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
		$this->setTitle('K2_TAGS');

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
		$this->setTitle('K2_TAG');

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
		$this->setUserState('published', '', 'cmd');
		$this->setUserState('sorting', 'ordering', 'string');
	}

	protected function setFilters()
	{

		// Sorting filter
		$sortingOptions = array(
			'K2_ID' => 'id',
			'K2_NAME' => 'name',
			'K2_PUBLISHED' => 'published'
		);
		K2Response::addFilter('sorting', JText::_('K2_SORT_BY'), K2HelperHTML::sorting($sortingOptions), false, 'header');

		// Search filter
		K2Response::addFilter('search', JText::_('K2_SEARCH'), K2HelperHTML::search(), false, 'sidebar');

		// Published filter
		K2Response::addFilter('published', JText::_('K2_PUBLISHED'), K2HelperHTML::published(), true, 'sidebar');

	}

	protected function setToolbar()
	{
		K2Response::addToolbarAction('published', 'K2_TOGGLE_PUBLISHED_STATE', array(
			'data-state' => 'published',
			'class' => 'appActionToggleState',
			'id' => 'appActionTogglePublishedState'
		));

		K2Response::addToolbarAction('remove', 'K2_DELETE', array('id' => 'appActionRemove'));
	}

}
