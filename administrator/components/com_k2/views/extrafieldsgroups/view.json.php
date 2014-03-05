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
 * Extra fields groups JSON view.
 */

class K2ViewExtraFieldsGroups extends K2View
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
		$this->setTitle('K2_EXTRA_FIELD_GROUPS');

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
		$this->setTitle('K2_EXTRA_FIELDS_GROUP');

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
		$this->setUserState('sorting', 'ordering', 'string');
	}

	protected function setFilters()
	{

		// Sorting filter
		$sortingOptions = array('K2_ID' => 'id', 'K2_NAME' => 'name');
		K2Response::addFilter('sorting', JText::_('K2_SORT_BY'), K2HelperHTML::sorting($sortingOptions), false, 'header');

		// Search filter
		K2Response::addFilter('search', JText::_('K2_SEARCH'), K2HelperHTML::search(), false, 'sidebar');

	}

	protected function setToolbar()
	{
		K2Response::addToolbarAction('remove', 'K2_DELETE', array('data-action' => 'remove'));
	}

	protected function setFormFields(&$form, $row)
	{
		$form->scope = K2HelperHTML::extraFieldsScopes('scope', $row->scope);
		$assignments = array();
		$recursive = new stdClass;
		$recursive->label = 'K2_APPLY_RECUSRIVELY';
		$recursive->name = 'assignments[recursive]';
		$recursive->value = isset($row->assignments->recursive) ? $row->assignments->recursive : 0;
		$assignments['item'] = K2HelperHTML::categories('assignments[categories][]', isset($row->assignments->categories) ? $row->assignments->categories : array(), false, false, 'multiple="multiple"', $recursive);
		$assignments['category'] = $assignments['item'];
		$assignments['user'] = K2HelperHTML::usergroups('assignments[usergroups][]', isset($row->assignments->usergroups) ? $row->assignments->usergroups : array(), false, 'multiple="multiple"');
		$assignments['tag'] = K2HelperHTML::tags('assignments[tags][]', isset($row->assignments->tags) ? $row->assignments->tags : array(), false, 'multiple="multiple"');
		$form->assignments = $assignments;

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

}
