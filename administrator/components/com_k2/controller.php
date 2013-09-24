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
 * K2 base controller class.
 * Handles all JSON requests made by Backbone.js via the sync method.
 */

class K2Controller extends JControllerLegacy
{

	/**
	 * This holds the name of the resource, for example 'items'.
	 * We need this since the contructor of JControllerLegacy strips it from the task variable.
	 *
	 * @var string $resourceType
	 */
	protected $resourceType = null;

	/**
	 * Quickreference variable for the model of the current request.
	 *
	 * @var object $model
	 */
	protected $model = null;

	/**
	 * Quickreference variable for the method of the current request
	 *
	 * @var string _method
	 */
	protected $_method = '';

	/**
	 * Constructor. Includes the K2 response class for handling the response.
	 *
	 * @param array $config		An optional associative array of configuration settings.
	 *
	 * @return void
	 */
	public function __construct($config = array())
	{
		// Parent constructor
		parent::__construct($config);

		// Add extra functionality only for JSON views
		$document = JFactory::getDocument();
		if ($document->getType() == 'json')
		{
			// Add the Backbone response class to auto load
			require_once JPATH_ADMINISTRATOR.'/components/com_k2/classes/response.php';

			// Determine the resource type. We need that in order to be able to load the correct model.
			if (strpos($config['originalTask'], '.') === false)
			{
				$resourceType = $config['originalTask'];
			}
			else
			{
				list($resourceType, $action) = explode('.', $config['originalTask']);
			}
			$this->resourceType = $resourceType;

			// If we are at Joomla! 2.5 set the controller input
			if (version_compare(JVERSION, '3.0', 'lt'))
			{
				$this->input = JFactory::getApplication()->input;
			}

			// Add the model to the controller for quick access
			$this->model = $this->getModel($this->resourceType);

		}

	}

	/**
	 * Sync function. Entry point for the Backbone.js requests.
	 * All Backbone JSON requests are routed here.
	 * This function performs inside routing depending on the request.
	 *
	 * @return JControllerLegacy	A JControllerLegacy object to support chaining.
	 */

	public function sync()
	{
		// Get the HTTP request method
		$method = $this->input->getMethod();

		// GET requests ( list or form rendering )
		if ($method == 'GET')
		{
			$id = $this->input->get('id', null, 'int');
			$id === null ? $this->read('collection') : $this->read('row', $id);
		}
		// POST requests ( create, update, delete functionality)
		else if ($method == 'POST')
		{
			// Get BackboneJS method variable
			$this->_method = $this->input->get('_method', '', 'cmd');

			// Execute the task based on the Backbone method
			switch($this->_method)
			{
				default :
				case 'POST' :
					$this->create();
					break;
				case 'PUT' :
					$this->update();
					break;
				case 'PATCH' :
					$this->patch();
					break;
				case 'DELETE' :
					$this->delete();
					break;
			}
			
		}
		
		// Return
		return $this;
	}

	/**
	 * Read function.
	 * Handles all the read requests ( lists and forms ) and triggers the appropriate view method.
	 *
	 * @param string $mode		The mode of the read function. Pass 'row' for retrieving a single row or 'list' to retrieve a collection of rows.
	 * @param mixed $id			The id of the row to load when we are retrieving a single row.
	 *
	 * @return void
	 */
	protected function read($mode = 'row', $id = null)
	{
		// Get the document
		$document = JFactory::getDocument();

		// Get the view
		$view = $this->getView($this->resourceType, $document->getType());

		// Get the model
		if ($model = $this->getModel($this->resourceType))
		{
			// Push the model into the view (as default)
			$view->setModel($model, true);
		}

		// Pass the document object to the view
		$view->document = $document;

		// Trigger the appropriate view method
		if ($mode == 'row')
		{
			$view->edit($id);
		}
		else
		{
			$view->show();
		}

	}

	/**
	 * Create function.
	 * Creates a new resource and stores it to the database.
	 *
	 * @return void
	 */
	protected function create()
	{
		$this->save();
	}

	/**
	 * Update function.
	 * Updates an existing resource.
	 *
	 * @param boolean $patch	True if we apply a patch operation instead of a full update.
	 *
	 * @return void
	 */
	protected function update()
	{
		$this->save();
	}

	/**
	 * Delete function.
	 * Deletes a resource.
	 * Usually there will be no need to override this function.
	 *
	 * @return void
	 */
	protected function delete()
	{
		// Check for token
		JSession::checkToken() or $this->throwError(JText::_('JINVALID_TOKEN'));

		// Delete
		$id = $this->input->get('id', null, 'array');
		$this->model->setState('id', $id);
		$result = $this->model->delete();
		if (!$result)
		{
			$this->throwError($this->model->getError());
		}
		
	}

	/**
	 * Default implementation for save function.
	 * This function saves a row and then performs inside routing to fetch the data for the next screen.
	 * Create and update requests are routed here by the main Sync function.
	 * Usually there will be no need to override this function.
	 *
	 * @return void
	 */
	protected function save()
	{
		// Check for token
		JSession::checkToken() or $this->throwError(JText::_('JINVALID_TOKEN'));

		// Save
		$data = JRequest::get('post', 2);
		$this->model->setState('data', $data);
		$result = $this->model->save();
		// If save was successful checkin the row
		if ($result)
		{
			if (!$this->model->checkin($this->model->getState('id')))
			{
				$this->throwError($this->model->getError());
			}
		}
		else
		{
			$this->throwError($this->model->getError());
		}

	}

	/**
	 * Default implementation for patch function.
	 * Patch requests are routed here by the main Sync function.
	 * These requests are usually coming from lists togglers and state buttons.
	 * Usually there will be no need to override this function.
	 *
	 * @return void
	 */
	protected function patch()
	{
		// Check for token
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		// Batch update
		$ids = $this->input->get('id', array(), 'array');
		JArrayHelper::toInteger($ids);
		$states = $this->input->get('states', array(), 'array');
		foreach ($ids as $id)
		{
			$data = array();
			$data['id'] = $id;
			// Apply any common states
			foreach ($states as $key => $value)
			{
				$data[$key] = $value;
			}
			$this->model->setState('data', $data);
			$result = $this->model->save(true);
			if (!$result)
			{
				$this->throwError($this->model->getError());
			}
		}
	}
	
	private function throwError($text, $status = 400)
	{
		header('HTTP/1.1 '.$status);
		jexit($text);
	}

	/**
	 * This function checks if the user is authorized to perform an action.
	 * It determines the action based on the request and checks for the appropriate permissions.
	 *
	 * @return boolean
	 */
	private function checkPermissions()
	{
		// Get application
		$application = JFactory::getApplication();

		// Get variables from URL
		$option = $application->input->get('option', '', 'cmd');
		$view = $application->input->get('view', '', 'cmd');
		$id = $application->input->get('id', null, 'int');

		// Get user
		$user = JFactory::getUser();

		// Generic manage permission check
		if (!$user->authorise('core.manage', $option))
		{
			$application->enqueueMessage(JText::_('K2_YOU_ARE_NOT_AUTHORIZED_TO_EXECUTE_THIS_TASK'), 'error');
			return false;
		}

		// Settings permissions check
		if ($view == 'settings' && !$user->authorise('core.admin', $option))
		{
			$application->enqueueMessage(JText::_('K2_YOU_ARE_NOT_AUTHORIZED_TO_EXECUTE_THIS_TASK'), 'error');
			return false;
		}

		// Initialize variables
		$asset = $option;
		$action = false;

		// If we are in items context get the item to check against it's category
		if ($view == 'items')
		{
			if ($id)
			{
				JTable::addIncludePath(JPATH_COMPONENT_ADMINISTRATOR.'/tables');
				$item = JTable::getInstance('Items', 'K2Table');
				$item->load($id);
				$category = $item->catid;
				$owner = $item->created_by;
			}
			else
			{
				$category = $application->input->get('catid', 0, 'int');
				$owner = $user->id;
			}
			$asset .= '.category.'.$category;
		}

		// Detect the action we need to check
		$method = $application->input->getMethod();
		if ($method == 'GET' && !is_null($id))
		{
			if ($id)
			{
				$action = 'core.edit';
				if (isset($owner) && $owner == $user->id)
				{
					$action .= '.own';
				}
			}
			else
			{
				$action = 'core.create';
			}

		}
		else if ($method == 'POST')
		{

			// Get the Backbone method
			$_method = $application->input->get('_method', '', 'cmd');

			switch($_method)
			{
				default :
				case 'POST' :
					$action = 'core.create';
					break;
				case 'PUT' :
					$action = 'core.edit';
					if (isset($owner) && $owner == $user->id)
					{
						$action .= '.own';
					}
					break;
				case 'PATCH' :
					$_models = $application->input->get('models', array(), 'array');
					if (isset($_models[0]))
					{
						$data = json_decode($_models[0]);
					}
					else
					{
						$_model = $application->input->get('model', '', 'string');
						$data = json_decode($_model);
					}
					if (property_exists($data, 'published') || property_exists($data, 'featured'))
					{
						$action = 'core.edit.state';
					}
					else
					{
						$action = 'core.edit';
						if (isset($owner) && $owner == $user->id)
						{
							$action .= '.own';
						}
					}
					break;
				case 'DELETE' :
					$action = 'core.delete';
					break;
			}
		}

		if ($action)
		{
			if (!$user->authorise($action, $asset))
			{
				$application->enqueueMessage(JText::_('K2_YOU_ARE_NOT_AUTHORIZED_TO_EXECUTE_THIS_TASK'), 'error');
				return false;
			}
		}

	}

}
