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

			// Add custom error handling for JSON requests to avoid breaking the JSON response from server
			set_error_handler('K2Response::errorHandler');
			set_exception_handler('K2Response::exceptionHandler');

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
	 * Search function. Used for autocomplete and search requests
	 *
	 * @return JControllerLegacy	A JControllerLegacy object to support chaining.
	 */
	public function search()
	{
		$model = $this->getModel($this->resourceType);
		$model->setState('search', $this->input->get('search'));
		$model->setState('sorting', $this->input->get('sorting'));
		$limit = $this->input->get('limit', 50);
		$page = $this->input->get('page', 1);
		$limitstart = ($page * $limit) - $limit;
		$model->setState('limit', $limit);
		$model->setState('limitstart', $limitstart);

		$response = new stdClass;
		$response->rows = $model->getRows();
		$response->total = $model->countRows();

		echo json_encode($response);
		return $this;
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

			// Check permissions
			if (!$this->checkPermissions($this->_method))
			{
				K2Response::throwError(JText::_('K2_YOU_ARE_NOT_AUTHORIZED_TO_PERFORM_THIS_OPERATION'), 403);
			}

			// Execute the task based on the Backbone method
			switch($this->_method)
			{
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

		// For new records we need to return the id in order to allow the client to know.
		$object = new stdClass;
		$object->id = $this->model->getState('id');
		echo json_encode($object);
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
		echo json_encode(true);
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
		JSession::checkToken() or K2Response::throwError(JText::_('JINVALID_TOKEN'));

		// Get and prepare input
		$input = $this->input->get('id', array(), 'array');
		JArrayHelper::toInteger($input);

		// Delete
		foreach ($input as $id)
		{
			$this->model->setState('id', $id);
			$result = $this->model->delete();
			if (!$result)
			{
				K2Response::throwError($this->model->getError());
			}
		}
		echo json_encode(true);
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
		JSession::checkToken() or K2Response::throwError(JText::_('JINVALID_TOKEN'));

		// Save
		$data = JRequest::get('post', 2);
		$this->model->setState('data', $data);
		$result = $this->model->save();
		// If save was successful checkin the row
		if ($result)
		{
			if (!$this->model->checkin($this->model->getState('id')))
			{
				K2Response::throwError($this->model->getError());
			}
		}
		else
		{
			K2Response::throwError($this->model->getError());
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
		
		// Ensure we have ids
		$ids = array_filter($ids);
		if(!count($ids))
		{
			K2Response::throwError('', 401);
		}

		// Handle categories sorting different than any other patch request
		if (array_key_exists('ordering', $states) && $this->resourceType == 'categories')
		{
			$this->model->saveOrder($ids, $states['ordering']);
		}
		else
		{
			foreach ($ids as $key => $id)
			{
				$data = array();
				$data['id'] = $id;
				foreach ($states as $state => $values)
				{
					$data[$state] = is_array($values) ? $values[$key] : $values;
				}
				$this->model->setState('data', $data);
				$result = $this->model->save();
				if (!$result)
				{
					K2Response::throwError($this->model->getError());
				}
			}
		}
		echo json_encode(true);
	}

	/**
	 * This function checks if the user is authorized to perform an action.
	 *
	 * @param string $method 	The method to be performed.
	 *
	 * @return boolean
	 */
	protected function checkPermissions($method)
	{
		return true;
	}

}
