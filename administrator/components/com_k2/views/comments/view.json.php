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
 * Comments JSON view.
 */

class K2ViewComments extends K2View
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
		$this->setTitle('K2_COMMENTS');

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
		$this->setTitle('K2_COMMENT');

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
			'K2_NAME' => 'name',
			'K2_EMAIL' => 'email',
			'K2_URL' => 'url',
			'K2_IP' => 'ip',
			'K2_DATE' => 'date',
			'K2_STATE' => 'state'
		);
		K2Response::addFilter('sorting', JText::_('K2_SORT_BY'), K2HelperHTML::sorting($sortingOptions), false, 'header');

		// Search filter
		K2Response::addFilter('search', JText::_('K2_SEARCH'), K2HelperHTML::search(), false, 'sidebar');

		// State filter
		K2Response::addFilter('state', JText::_('K2_STATE'), K2HelperHTML::state('state', null, 'K2_ANY', false, 'radio'), true, 'sidebar');

	}

	protected function setFormFields(&$form, $row)
	{
		require_once JPATH_ADMINISTRATOR.'/components/com_k2/helpers/html.php';
		$form->state = K2HelperHTML::state('state', $row->state);
	}

	/**
	 * Hook for children views to allow them set the menu for the edit requests.
	 * Children views usually will not need to override this method.
	 *
	 * @return void
	 */
	protected function setFormActions()
	{
		K2Response::addAction('save', 'K2_SAVE', array(
			'class' => 'appAction',
			'id' => 'appActionSave',
			'data-resource' => $this->getName()
		));
		K2Response::addAction('saveAndClose', 'K2_SAVE_AND_CLOSE', array(
			'class' => 'appAction',
			'id' => 'appActionSaveAndClose'
		));
		K2Response::addAction('close', 'K2_CLOSE', array(
			'class' => 'appAction',
			'id' => 'appActionClose'
		));
	}

	protected function setToolbar()
	{
		K2Response::addToolbarAction('publish', 'K2_PUBLISH', array(
			'data-state' => 'state',
			'data-value' => '1',
			'class' => 'appActionSetState',
			'id' => 'appActionPublish'
		));
		K2Response::addToolbarAction('unpublish', 'K2_UNPUBLISH', array(
			'data-state' => 'state',
			'data-value' => '0',
			'class' => 'appActionSetState',
			'id' => 'appActionUnpublish'
		));

		K2Response::addToolbarAction('remove', 'K2_DELETE', array('id' => 'appActionRemove'));
	}

}
