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
 * User groups JSON view.
 */

class K2ViewUserGroups extends K2View
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
		$this->setTitle('K2_USER_GROUPS');

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
		$this->setTitle('K2_USER_GROUP');

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
		$this->setUserState('state', '', 'cmd');
		$this->setUserState('sorting', 'ordering', 'string');
	}

	protected function setFilters()
	{
		// Sorting filter
		$sortingOptions = array(
			'K2_ID' => 'id',
			'K2_TITLE' => 'title'
		);
		K2Response::addFilter('sorting', JText::_('K2_SORT_BY'), K2HelperHTML::sorting($sortingOptions), false, 'header');

		// Search filter
		K2Response::addFilter('search', JText::_('K2_SEARCH'), K2HelperHTML::search(), false, 'sidebar');

	}

	protected function setToolbar()
	{
		K2Response::addToolbarAction('remove', 'K2_DELETE', array('id' => 'appActionRemove'));
	}

	protected function prepareJForm(&$form, $row)
	{
		
		$form->setFieldAttribute('rules', 'groupId', $row->id);
		
		/*require_once JPATH_ADMINISTRATOR.'/components/com_k2/helpers/html.php';
		$recursive = new stdClass;
		$recursive->label = 'K2_APPLY_RECUSRIVELY';
		$recursive->name = 'permissions[recursive]';
		$form->categories = K2HelperHTML::categories('permissions[categories][]', null, false, false, 'multiple="multiple"', $recursive);

		if (!$row->id)
		{
			$form->parent_id = K2HelperHTML::usergroups('parent_id', null, false, '');
		}*/
	}

}
