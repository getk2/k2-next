<?php
/**
 * @version		3.0.0b
 * @package		K2
 * @author		JoomlaWorks http://www.joomlaworks.net
 * @copyright	Copyright (c) 2006 - 2014 JoomlaWorks Ltd. All rights reserved.
 * @license		GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 */

// no direct access
defined('_JEXEC') or die ;

/**
 * K2 base view class.
 */

class K2View extends JViewLegacy
{

	/**
	 * The user states array.
	 *
	 * @var array $userStates
	 */
	public $userStates = array();

	/**
	 * Constructor
	 *
	 * @param   array  $config  @see JViewLegacy
	 *
	 */
	public function __construct($config = array())
	{
		// Parent constructor
		parent::__construct($config);

		// Load the helpers
		require_once JPATH_ADMINISTRATOR.'/components/com_k2/helpers/html.php';
	}

	/**
	 * Builds the response variables needed for rendering a list.
	 * Usually there will be no need to override this function.
	 *
	 * @return void
	 */

	public function show()
	{
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
		$this->render();
	}

	/**
	 * Builds the response variables needed for rendering a form.
	 * Usually there will be no need to override this function.
	 *
	 * @param integer $id	The id of the resource to load.
	 *
	 * @return void
	 */

	public function edit($id)
	{
		// Set row
		$this->setRow($id);

		// Set form
		$this->setForm();

		// Set menu
		$this->setMenu('edit');

		// Set Actions
		$this->setFormActions();

		// Render
		$this->render();
	}

	/**
	 * Renders the response object into JSON.
	 * Usually there will be no need to override this function.
	 *
	 * @return void
	 */

	protected function render()
	{
		// Unset not used states
		unset($this->userStates['page']);
		unset($this->userStates['limit']);
		unset($this->userStates['limitstart']);
		unset($this->userStates['persist']);

		// Pass the states to the response
		K2Response::setStates($this->userStates);

		// Render
		K2Response::render();
	}

	/**
	 * Helper method for fetching a single row and pass it to K2 response.
	 * This is triggered by the edit function.
	 * Usually there will be no need to override this function.
	 *
	 * @param   integer  $id  The id of the row to edit.
	 *
	 * @return void
	 */
	protected function setRow($id)
	{
		// Get model
		$model = $this->getModel();

		// Checkout the row if needed
		if ($id)
		{
			if (!$model->checkout($id))
			{
				JFactory::getApplication()->enqueueMessage($model->getError());
			}
		}

		// Get the row
		$model->setState('id', $id);
		$row = $model->getRow();

		// Prepare row
		$this->prepareRow($row);

		// Set K2 response row
		K2Response::setRow($row);
	}

	/**
	 * Helper method for fetching the rows and pass them to K2 response.
	 * This is triggered by the display function.
	 * Usually there will be no need to override this function.
	 *
	 * @return void
	 */
	protected function setRows()
	{
		// Get rows
		$model = $this->getModel();
		$model->setState('id', false);
		$rows = $model->getRows();

		// Prepare rows
		$this->prepareRows($rows);

		// Set K2 response rows
		K2Response::setRows($rows);
	}

	/**
	 * Hook to allow children view to attach extra data to the row.
	 *
	 * @return void
	 */
	protected function prepareRow($row)
	{
	}

	/**
	 * Hook to allow children view to attach extra data to the rows.
	 *
	 * @return void
	 */
	protected function prepareRows($rows)
	{
	}

	/**
	 * Helper method for fetching the pagination object and pass it to K2 response object.
	 * This is triggered by the display function.
	 * Usually there will be no need to override this function.
	 *
	 * @return void
	 */
	protected function setPagination()
	{
		// Import Joomla! pagination
		jimport('joomla.html.pagination');

		// Count rows
		$model = $this->getModel();
		$total = $model->countRows();
		$limitstart = $this->getUserState('limitstart');
		$limit = $this->getUserState('limit');

		if ($limitstart > ($total - $limit))
		{

			$limitstart = max(0, (int)(ceil($total / $limit) - 1) * $limit);
			$page = (int)$limitstart / $limit;
			$input = JFactory::getApplication()->input;
			$input->set('page', $page);
			$input->set('limitstart', $limitstart);
			$model->setState('page', $page);
			$model->setState('limitstart', $limitstart);
		}

		// Get the pagination
		$pagination = new JPagination($total, $limitstart, $limit);

		// Set the K2 response pagination object
		K2Response::setPagination($pagination);
	}

	/**
	 * Hook for children views to allow them set their user states.
	 * Children views should override this method.
	 *
	 * @return void
	 */
	protected function setUserStates()
	{
	}

	/**
	 * Helper method for setting a user state.
	 * The function pushes the given state to the $userStates array and also sets the state for the model.
	 * It also converts the page variable to the limitstart variable since models need it.
	 * This is usually triggered by the display function.
	 * Usually there will be no need to override this function.
	 *
	 * @param   string  $name	The name of the variable.
	 * @param   string  $default	The default value of the variable.
	 * @param   string  $type	The type of the variable.
	 *
	 * @return void
	 */
	protected function setUserState($name, $default, $type, $session = true)
	{
		// Get application
		$application = JFactory::getApplication();

		// Get the state
		if ($session)
		{
			$state = $application->getUserStateFromRequest('com_k2.'.$this->getName().'.'.$name, $name, $default, $type);
		}
		else
		{
			$state = $application->input->get($name, $default, $type);
		}

		// Push the state to the array
		$this->userStates[$name] = $state;

		// Let the model know about the state
		$model = $this->getModel();
		if (is_object($model))
		{
			$model->setState($name, $state);

			// Auto calculate the limitstart
			if (!isset($this->userStates['limitstart']) && isset($this->userStates['page']) && isset($this->userStates['limit']))
			{
				$this->userStates['limitstart'] = ($this->userStates['page'] * $this->userStates['limit']) - $this->userStates['limit'];
				$model->setState('limitstart', $this->userStates['limitstart']);
			}
		}
	}

	/**
	 * Helper method for getting a user state from the $userStates array
	 * Usually there will be no need to override this function.
	 *
	 * @param   string  $name	The name of the variable.
	 *
	 * @return  mixed  The value of the state.
	 */
	protected function getUserState($name)
	{
		return isset($this->userStates[$name]) ? $this->userStates[$name] : null;
	}

	/**
	 * Loads the XML form and pass the fields to the response.
	 * Usually there would be no need to override this method.
	 *
	 * @return void
	 */
	protected function setForm()
	{
		// Get the row
		$row = K2Response::getRow();

		// Initialize form object
		$_form = new stdClass;

		// Get the dispatcher.
		$dispatcher = JDispatcher::getInstance();

		// Check if form file exists
		jimport('joomla.filesystem.file');
		if (JFile::exists(JPATH_ADMINISTRATOR.'/components/com_k2/models/'.$this->getName().'.xml'))
		{
			// Import JForm
			jimport('joomla.form.form');

			// Determine form name and path
			$formName = 'K2'.ucfirst($this->getName()).'Form';
			$formPath = JPATH_ADMINISTRATOR.'/components/com_k2/models/'.$this->getName().'.xml';

			// Convert JRegistry instances to plain object so JForm can bind them
			if (property_exists($row, 'metadata'))
			{
				$row->metadata = $row->metadata->toObject();
			}
			if (property_exists($row, 'params'))
			{
				$row->params = $row->params->toObject();
			}
			if (property_exists($row, 'plugins'))
			{
				$row->plugins = $row->plugins->toObject();
			}

			// Get the form instance
			$form = JForm::getInstance($formName, $formPath);

			// Bind values
			$form->bind($row);

			// Import plugins to extend the form
			JPluginHelper::importPlugin('content');

			// Trigger the form preparation event
			$results = $dispatcher->trigger('onContentPrepareForm', array(
				$form,
				$row
			));

			// Pass the JForm to the model before attaching the fields
			$this->prepareJForm($form, $row);

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
		}

		// Extend the form with K2 plugins
		$_form->k2Plugins = array();
		JPluginHelper::importPlugin('k2');
		$dispatcher->trigger('onK2RenderAdminForm', array(
			&$_form,
			$row,
			$this->getName()
		));

		$this->setFormFields($_form, $row);

		K2Response::setForm($_form);
	}

	/**
	 * Hook for children views to allow them attach fields to the form object.
	 * Children views usually should override this method.
	 *
	 * @return void
	 */
	protected function setFormFields(&$form, $row)
	{
	}

	/**
	 * Hook for children views to allow them to modify the JForm object.
	 *
	 * @return void
	 */
	protected function prepareJForm(&$form, $row)
	{
	}

	/**
	 * Hook for children views to allow them set the filters at their list (read).
	 * Children views should override this method.
	 *
	 * @return void
	 */
	protected function setFilters()
	{
	}

	/**
	 * Hook for children views to allow them set the toolbar (batch buttons) at their list (read).
	 * Children views should override this method.
	 *
	 * @return void
	 */
	protected function setToolbar()
	{
	}

	/**
	 * Hook for children views to allow them set the menu for the list requests.
	 * Children views usually will not need to override this method.
	 *
	 * @return void
	 */
	protected function setListActions()
	{
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
			'data-action' => 'save',
			'data-resource' => $this->getName()
		));
		K2Response::addAction('saveAndNew', 'K2_SAVE_AND_NEW', array('data-action' => 'save-and-new'));
		K2Response::addAction('saveAndClose', 'K2_SAVE_AND_CLOSE', array('data-action' => 'save-and-close'));
		K2Response::addAction('close', 'K2_CLOSE', array('data-action' => 'close'));
	}

	/**
	 * Hook for children views to allow them set the batch actions for the list requests.
	 *
	 * @return void
	 */
	protected function setBatchActions()
	{
	}

	/**
	 * Hook for children views to allow them set the menu for the list and edit requests.
	 * Children views usually will not need to override this method.
	 *
	 * @param   string  $mode	The mode of the menu. It is null for lists and has the value of 'edit' in edit requests.
	 *
	 * @return void
	 */
	protected function setMenu($mode = null)
	{
		// Get user
		$user = JFactory::getUser();

		// Set prmary menu only for listings
		if ($mode != 'edit')
		{
			K2Response::addMenuLink('items', 'K2_ITEMS', array('href' => '#items'), 'primary');
			K2Response::addMenuLink('categories', 'K2_CATEGORIES', array('href' => '#categories'), 'primary');
			if ($user->authorise('k2.tags.manage', 'com_k2'))
			{
				K2Response::addMenuLink('tags', 'K2_TAGS', array('href' => '#tags'), 'primary');
			}

			if ($user->authorise('k2.comment.edit', 'com_k2'))
			{
				K2Response::addMenuLink('comments', 'K2_COMMENTS', array('href' => '#comments'), 'primary');
			}

			if ($user->authorise('k2.extrafields.manage', 'com_k2'))
			{
				K2Response::addMenuLink('extrafields', 'K2_EXTRA_FIELDS', array('href' => '#extrafields'), 'primary');

				K2Response::addMenuLink('extrafieldsgroups', 'K2_EXTRA_FIELD_GROUPS', array('href' => '#extrafieldsgroups'), 'primary');
			}

			K2Response::addMenuLink('usergroups', 'K2_USER_GROUPS', array('href' => '#usergroups'), 'primary');
			K2Response::addMenuLink('users', 'K2_USERS', array('href' => '#users'), 'primary');
			K2Response::addMenuLink('media', 'K2_MEDIA_MANAGER', array('href' => '#media'), 'primary');
			if ($user->authorise('core.admin', 'com_k2'))
			{
				K2Response::addMenuLink('utilities', 'K2_UTILITIES', array('href' => '#utilities'), 'primary');
			}

		}

		// Set secondary menu
		if ($user->authorise('core.login.admin', 'com_k2'))
		{
			K2Response::addMenuLink('information', 'K2_INFORMATION', array('href' => '#information'), 'secondary');
		}

		if ($user->authorise('core.admin', 'com_k2'))
		{
			K2Response::addMenuLink('settings', 'K2_SETTINGS', array('href' => '#settings'), 'secondary');
		}
		K2Response::addMenuLink('help', 'K2_HELP', array(
			'href' => 'http://getk2.org/community',
			'target' => '_blank'
		), 'secondary');

	}

	/**
	 * Hook for children views to allow them set the title.
	 *
	 * @param   string  $title	The title.
	 *
	 * @return void
	 */
	protected function setTitle($title)
	{
		K2Response::setTitle(JText::_($title));
	}

}
