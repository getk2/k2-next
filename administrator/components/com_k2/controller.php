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
			$this->addModelPath(JPATH_ADMINISTRATOR.'/components/com_k2/models', 'K2Model');
			$this->model = $this->getModel($this->resourceType);

		}

		// Fix when we are in front-end
		$application = JFactory::getApplication();
		if ($application->isSite() && $this->input->get('view') == 'admin')
		{
			$uri = JURI::getInstance();
			$document->setBase($uri->toString());
			$this->input->set('view', 'k2');
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
			// For GET request we want to render the whole page, so send back the whole response
			$response = K2Response::getResponse();
			$response->method = $method;
			echo json_encode($response);
		}
		// POST requests ( create, update, delete functionality)
		else if ($method == 'POST')
		{
			// Get BackboneJS method variable
			$this->_method = $this->input->get('_method', '', 'cmd');

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

			// For actions we only send back the status the row (if any) and the errors
			$response = new stdClass;
			$response->row = K2Response::getRow();
			$response->status = K2Response::getStatus();
			$response->messages = JFactory::getApplication()->getMessageQueue();
			$response->method = $method;
			echo json_encode($response);
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
		// Check for token
		JSession::checkToken() or K2Response::throwError(JText::_('JINVALID_TOKEN'));

		// Get input data
		$data = $this->getInputData();

		// Ensure we are not passed with an id
		$data['id'] = null;

		// Pass data to the model
		$this->model->setState('data', $data);

		// Save
		$result = $this->model->save();

		// Handle save result
		if ($result)
		{
			// Save was successful try to checkin the row
			if (!$this->model->checkin($this->model->getState('id')))
			{
				// An error occured while trying to checkin. Notify the client.
				K2Response::throwError($this->model->getError());
			}

			// Row saved. Pass the id of the new object to the client.
			$row = new stdClass;
			$row->id = $this->model->getState('id');
			K2Response::setRow($row);
			K2Response::setStatus(true);
		}
		else
		{
			// An error occured while saving. Notify the client.
			K2Response::throwError($this->model->getError());
		}
	}

	/**
	 * Update function.
	 * Updates an existing resource.
	 *
	 * @return void
	 */
	protected function update()
	{
		// Check for token
		JSession::checkToken() or K2Response::throwError(JText::_('JINVALID_TOKEN'));

		// Ensure we have an id
		$id = $this->input->get('id', 0, 'int');
		if (!$id)
		{
			K2Response::throwError(JText::_('K2_INVALID_INPUT'));
		}

		// Get input data
		$data = $this->getInputData();

		// Pass data to the model
		$this->model->setState('data', $data);

		// Save
		$result = $this->model->save();

		// Handle save result
		if ($result)
		{
			// Save was successful try to checkin the row
			if (!$this->model->checkin($this->model->getState('id')))
			{
				// An error occured while trying to checkin. Notify the client.
				K2Response::throwError($this->model->getError());
			}

			K2Response::setStatus(true);
		}
		else
		{
			// An error occured while saving. Notify the client.
			K2Response::throwError($this->model->getError());
		}
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
		K2Response::setResponse(true);
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
		JSession::checkToken() or K2Response::throwError(JText::_('JINVALID_TOKEN'));

		// Batch update
		$ids = $this->input->get('id', array(), 'array');
		JArrayHelper::toInteger($ids);
		$states = $this->input->get('states', array(), 'array');
		$mode = $this->input->get('mode', 'apply', 'string');

		// Ensure we have ids
		$ids = array_filter($ids);
		if (!count($ids))
		{
			K2Response::throwError('K2_NO_ROWS_SELECTED', 401);
		}

		foreach ($ids as $key => $id)
		{
			$data = array();
			$data['id'] = $id;
			foreach ($states as $state => $values)
			{
				$value = is_array($values) ? $values[$key] : $values;
				if ($value != '')
				{
					$data[$state] = $value;
				}
			}
			if ($mode == 'clone')
			{
				$sourceData = $this->model->getCopyData($id);
				$data = array_merge($sourceData, $data);
				$data['id'] = null;
			}
			$this->model->setState('data', $data);
			$result = $this->model->save();
			if (!$result)
			{
				K2Response::throwError($this->model->getError());
			}
		}

		K2Response::setResponse($result);
	}

	protected function getInputData()
	{
		return $this->input->getArray();
	}

}
