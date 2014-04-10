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
 * Users JSON view.
 */

class K2ViewUsers extends K2View
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
		$this->setTitle('K2_USERS');

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
		$this->setTitle('K2_USER');

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
		$sortingOptions = array('K2_ID' => 'id', 'K2_NAME' => 'name', 'K2_USERNAME' => 'username', 'K2_EMAIL' => 'email', 'K2_LAST_VISIT' => 'lastvisitDate', 'K2_IP' => 'ip', 'K2_HOSTNAME' => 'hostname', );
		K2Response::addFilter('sorting', JText::_('K2_SORT_BY'), K2HelperHTML::sorting($sortingOptions), false, 'header');

		// Search filter
		K2Response::addFilter('search', JText::_('K2_SEARCH'), K2HelperHTML::search(), false, 'sidebar');

	}

	protected function setToolbar()
	{
		$user = JFactory::getUser();
		if ($user->authorise('core.edit.state', 'com_users'))
		{
			K2Response::addToolbarAction('activate', 'K2_ACTIVATE', array('data-state' => 'activation', 'data-value' => '0', 'data-action' => 'set-state'));
			K2Response::addToolbarAction('block', 'K2_BLOCK', array('data-state' => 'block', 'data-value' => '1', 'data-action' => 'set-state'));
			K2Response::addToolbarAction('unblock', 'K2_UNBLOCK', array('data-state' => 'block', 'data-value' => '0', 'data-action' => 'set-state'));
		}
		if ($user->authorise('core.delete', 'com_users'))
		{
			K2Response::addToolbarAction('remove', 'K2_DELETE', array('data-action' => 'remove'));
		}

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

		// Import JForm
		jimport('joomla.form.form');

		// Determine form name and path
		$formName = 'K2'.ucfirst($this->getName()).'Form';
		$formPath = JPATH_ADMINISTRATOR.'/components/com_users/models/forms/user.xml';

		// Convert JRegistry instances to plain object so JForm can bind them
		if ($row->id)
		{
			$row->params = $row->params->toObject();
		}

		// Get the form instance
		$form = JForm::getInstance($formName, $formPath);

		// Bind values
		$form->bind($row);

		$form->setValue('password', null);
		$form->setValue('password2', null);

		// Attach the JForm fields to the form
		foreach ($form->getFieldsets() as $fieldset)
		{
			$array = array();
			foreach ($form->getFieldset($fieldset->name) as $field)
			{
				$tmp = new stdClass;
				$tmp->label = $field->label;
				$tmp->input = $field->input;
				$array[$field->name] = $tmp;
			}
			$name = $fieldset->name;
			$_form->$name = $array;
		}

		JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR.'/components/com_users/models');
		$model = JModelLegacy::getInstance('User', 'UsersModel');
		if ($row->id)
		{
			$assignedGroups = $model->getAssignedGroups($row->id);
		}
		else
		{
			$assignedGroups = null;
		}
		$_form->groups = JHtml::_('access.usergroups', 'groups', $assignedGroups, true);
		require_once JPATH_ADMINISTRATOR.'/components/com_k2/helpers/html.php';
		$_form->gender = K2HelperHTML::gender('gender', $row->gender);
		require_once JPATH_ADMINISTRATOR.'/components/com_k2/helpers/extrafields.php';
		$_form->extraFields = K2HelperExtraFields::getUserExtraFieldsGroups($row->id, $row->extra_fields);
		require_once JPATH_ADMINISTRATOR.'/components/com_k2/classes/editor.php';
		$config = JFactory::getConfig();
		$editor = K2Editor::getInstance($config->get('editor'));
		$_form->description = $editor->display('description', $row->description, '100%', '300', '40', '5');

	}

}
