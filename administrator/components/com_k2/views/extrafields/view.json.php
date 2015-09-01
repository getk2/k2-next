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

require_once JPATH_ADMINISTRATOR.'/components/com_k2/views/view.php';

/**
 * Extra fields JSON view.
 */

class K2ViewExtraFields extends K2View
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
		$this->setTitle('K2_EXTRA_FIELDS');

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
		$this->setTitle($id ? 'K2_EDIT_EXTRA_FIELD' : 'K2_ADD_EXTRA_FIELD');

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
		$this->setUserState('limit', JFactory::getApplication()->get('list_limit'), 'int');
		$this->setUserState('page', 1, 'int');
		$this->setUserState('search', '', 'string');
		$this->setUserState('state', '', 'cmd');
		$this->setUserState('group', '', 'cmd');
		$this->setUserState('type', '', 'cmd');
		$this->setUserState('sorting', '', 'string');
	}

	protected function setFilters()
	{

		// Type filter
		K2Response::addFilter('type', JText::_('K2_TYPE'), K2HelperHTML::extraFieldsTypes('type', null, 'K2_ANY'), false, 'header');

		// Group filter
		K2Response::addFilter('group', JText::_('K2_GROUP'), K2HelperHTML::extraFieldsGroups('group', null, 'K2_ANY'), false, 'header');

		// Sorting filter
		$sortingOptions = array(
			'K2_NONE' => '',
			'K2_ID_ASC' => 'id',
			'K2_ID_DESC' => 'id.reverse',
			'K2_NAME_ASC' => 'name',
			'K2_NAME_DESC' => 'name.reverse',
			'K2_GROUP_ASC' => 'group',
			'K2_GROUP_DESC' => 'group.reverse',
			'K2_TYPE_ASC' => 'type',
			'K2_TYPE_DESC' => 'type.reverse',
			'K2_ORDERING' => 'ordering',
			'K2_STATE_ASC' => 'state',
			'K2_STATE_DESC' => 'state.reverse'
		);
		K2Response::addFilter('sorting', JText::_('K2_SORT_BY'), K2HelperHTML::sorting($sortingOptions), false, 'header');

		// Search filter
		K2Response::addFilter('search', JText::_('K2_SEARCH'), K2HelperHTML::search(), false, 'sidebar');

		// State filter
		K2Response::addFilter('state', JText::_('K2_STATE'), K2HelperHTML::state('state', null, 'K2_ANY', false), true, 'sidebar');

	}

	protected function setToolbar()
	{
		K2Response::addToolbarAction('remove', 'K2_DELETE', array('data-action' => 'remove'));
	}

	protected function setFormFields(&$form, $row)
	{
		$form->state = K2HelperHTML::state('state', $row->state, false, false, 'radio', true);
		$form->group = K2HelperHTML::extraFieldsGroups('group', $row->group);
		$form->type = K2HelperHTML::extraFieldsTypes('type', $row->type, 'K2_SELECT_TYPE');
		$definitions = K2HelperExtraFields::getDefinitions();
		if ($row->id)
		{
			$definitions[$row->type] = $row->getDefinition();
		}
		$form->definitions = $definitions;

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
		if ($user->authorise('k2.extrafields.manage', 'com_k2'))
		{
			K2Response::addAction('add', 'K2_ADD', array('data-action' => 'add'));
		}
	}

	protected function prepareRow($row)
	{
		// State
		if (!$row->id)
		{
			$row->state = 1;
		}
	}

}
