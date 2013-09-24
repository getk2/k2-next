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
		$this->loadHelper('html');
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

	public function edit($id = null)
	{
		// Set row
		$this->setRow($id);

		// Set form
		$this->setForm();

		// Set menu
		$this->setMenu('edit');

		// Set Actions
		$this->setActions('edit');

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
		// Get the response
		$response = K2Response::render();

		// Trigger an event before outputing the response
		$dispatcher = JDispatcher::getInstance();
		JPluginHelper::importPlugin('k2');
		$dispatcher->trigger('onBeforeRenderK2Response', array(&$response));

		// Output the JSON response.
		echo json_encode($response);
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
		if (is_null($row))
		{
			$row = $model->getTable('', 'K2Table');
		}

		// Get helper
		$helper = $this->loadHelper();

		// Prepare row
		if ($helper)
		{
			$helper::prepare($row);
		}

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

		// Get helper
		$this->loadHelper($this->getName());
		$helper = 'K2Helper'.ucfirst($this->getName());

		// Prepare rows
		if (class_exists($helper) && method_exists($helper, 'prepare'))
		{
			// Prepare using helper
			foreach ($rows as $row)
			{
				$helper::prepare($row);
			}
		}
		// For lists we need to also check for controller prepare methods
		else if (method_exists($this, 'prepareRow'))
		{
			// Prepare rows using class method
			foreach ($rows as $row)
			{
				$this->prepareRow($row);
			}
		}

		// Set K2 response rows
		K2Response::setRows($rows);
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

		// Get the pagination
		$pagination = new JPagination($total, $limitstart, $limit);

		// Convert some variables for Joomla! 2.5 compatibility
		if (version_compare(JVERSION, '3.0', 'lt'))
		{
			$pagination->pagesCurrent = $pagination->get('pages.current');
			$pagination->pagesTotal = $pagination->get('pages.total');
			$pagination->pagesStart = $pagination->get('pages.start');
			$pagination->pagesStop = $pagination->get('pages.stop');
		}

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
	protected function setUserState($name, $default, $type)
	{
		// Get application
		$application = JFactory::getApplication();

		// Get the prefix
		$prefix = $this->getName();

		// Get the state.
		$state = $application->getUserStateFromRequest($prefix.'.'.$name, $name, $default, $type);

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
	 * Children may need to override this method.
	 *
	 * @return void
	 */
	protected function setForm()
	{
		// Import JForm
		jimport('joomla.form.form');

		// Determine form name and path
		$formName = 'K2'.ucfirst($this->getName()).'Form';
		$formPath = JPATH_ADMINISTRATOR.'/components/com_k2/models/'.$this->getName().'.xml';

		// Get the form instance
		$form = JForm::getInstance($formName, $formPath);

		// Get the row to bind the values to the form
		$row = K2Response::getRow();
		$row->params = json_decode($row->params);
		$form->bind($row);

		// Build the form object
		$_form = new stdClass;
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
		K2Response::setForm($_form);
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
	 * Hook for children views to allow them set the menu for the list and edit requests.
	 * Children views usually will not need to override this method.
	 *
	 * @param   string  $mode	The mode of the menu. It is null for lists and has the value of 'edit' in edit requests.
	 *
	 * @return void
	 */
	protected function setActions($mode = null)
	{
		// Get user
		$user = JFactory::getUser();

		if ($mode == 'edit')
		{
			K2Response::addAction('save', 'K2_SAVE', array(
				'class' => 'jwAction',
				'id' => 'jwActionSave'
			));
			K2Response::addAction('saveAndNew', 'K2_SAVE_AND_NEW', array(
				'class' => 'jwAction',
				'id' => 'jwActionSaveAndNew'
			));
			K2Response::addAction('saveAndClose', 'K2_SAVE_AND_CLOSE', array(
				'class' => 'jwAction',
				'id' => 'jwActionSaveAndClose'
			));
			K2Response::addAction('close', 'K2_CLOSE', array(
				'class' => 'jwAction',
				'id' => 'jwActionClose'
			));
		}
		else
		{

			if ($user->authorise('core.create', 'com_k2'))
			{
				if ($this->getName() == 'items' || $this->getName() == 'categories')
				{
					K2Response::addAction('add', 'K2_ADD', array(
						'class' => 'jwAction',
						'id' => 'jwActionAdd'
					));
				}
			}
		}
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
			K2Response::addMenuLink('items', 'K2_ITEMS', array(
				'href' => '#items',
				'class' => 'jwMenuLink',
				'id' => 'k2ItemsLink'
			), 'primary');
			K2Response::addMenuLink('categories', 'K2_CATEGORIES', array(
				'href' => '#categories',
				'class' => 'jwMenuLink',
				'id' => 'k2CategoriesLink'
			), 'primary');

		}

		// Set secondary menu
		K2Response::addMenuLink('information', 'K2_INFORMATION', array(
			'href' => '#information',
			'class' => 'jwMenuLink',
			'id' => 'jwInformationLink'
		), 'secondary');
		if ($user->authorise('core.admin', 'com_k2'))
		{
			K2Response::addMenuLink('settings', 'K2_SETTINGS', array(
				'href' => '#settings',
				'class' => 'jwMenuLink',
				'id' => 'jwSettingsLink'
			), 'secondary');
		}
		K2Response::addMenuLink('help', 'K2_HELP', array(
			'href' => '#help',
			'class' => 'jwMenuLink',
			'id' => 'jwHelpLink'
		), 'secondary');

	}

}
