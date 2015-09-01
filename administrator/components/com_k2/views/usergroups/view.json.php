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
		$this->setUserState('limit', JFactory::getApplication()->get('list_limit'), 'int');
		$this->setUserState('page', 1, 'int');
		$this->setUserState('search', '', 'string');
		$this->setUserState('state', '', 'cmd');
		$this->setUserState('sorting', 'ordering', 'string');
	}

	protected function setFilters()
	{
		// Sorting filter
		$sortingOptions = array(
			'K2_NONE' => 'ordering',
			'K2_ID_ASC' => 'id',
			'K2_ID_DESC' => 'id.reverse',
			'K2_TITLE_ASC' => 'title',
			'K2_TITLE_DESC' => 'title.reverse'
		);
		K2Response::addFilter('sorting', JText::_('K2_SORT_BY'), K2HelperHTML::sorting($sortingOptions), false, 'header');

		// Search filter
		K2Response::addFilter('search', JText::_('K2_SEARCH'), K2HelperHTML::search(), false, 'sidebar');

	}

	protected function setToolbar()
	{
		K2Response::addToolbarAction('remove', 'K2_DELETE', array('data-action' => 'remove'));
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
		if ($user->authorise('core.create', 'com_users'))
		{
			K2Response::addAction('add', 'K2_ADD', array('data-action' => 'add'));
		}
	}

	/**
	 * Hook for children views to allow them attach fields to the form object.
	 * Children views usually should override this method.
	 *
	 * @return void
	 */
	protected function setFormFields(&$_form, $row)
	{
		$language = JFactory::getLanguage();
		$language->load('com_users');

		// Import JForm
		jimport('joomla.form.form');

		// Determine form name and path
		$formName = 'JUsersGroupForm';
		$formPath = JPATH_ADMINISTRATOR.'/components/com_users/models/forms/group.xml';

		// Get the form instance
		JForm::addFieldPath(JPATH_ADMINISTRATOR.'/components/com_users/models/fields');
		$form = JForm::getInstance($formName, $formPath);

		// Bind values
		$form->bind($row);
		if (isset($row->parent_id) && $row->parent_id == 0 && $row->id > 0)
		{
			$form->setFieldAttribute('parent_id', 'type', 'hidden');
			$form->setFieldAttribute('parent_id', 'hidden', 'true');
		}
		$parent_id = $form->getField('parent_id');
		$_form->showParentId = !$parent_id->hidden;
		$_form->parent_id = $form->getInput('parent_id');

	}

	protected function prepareJForm(&$form, $row)
	{
		$form->setFieldAttribute('rules', 'groupId', $row->id);
	}

}
