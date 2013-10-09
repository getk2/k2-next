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
 * K2 Response class.
 * This class sets the JSON response object structure for the Backbone.js application.
 */

class K2Response
{

	/**
	 * The object which holds the actual data of the response.
	 *
	 * @var object $response
	 */
	public static $response = null;

	/**
	 * The title.
	 *
	 * @var string $title
	 */
	public static $title = '';

	/**
	 * The menu array.
	 *
	 * @var array $menu
	 */
	public static $menu = array();

	/**
	 * The actions array.
	 *
	 * @var array $actions
	 */
	public static $actions = array();

	/**
	 * Rows for list rendering.
	 *
	 * @var array $rows
	 */
	public static $rows = array();

	/**
	 * Pagination data for list rendering.
	 *
	 * @var object $pagination
	 */
	public static $pagination = null;

	/**
	 * Filters for list rendering.
	 *
	 * @var array $filters
	 */
	public static $filters = array();

	/**
	 * Toolbar buttons for list rendering.
	 *
	 * @var array $toolbar
	 */
	public static $toolbar = array();

	/**
	 * Batch operation actions for list rendering.
	 *
	 * @var array $batch
	 */
	public static $batch = array();

	/**
	 * Row for form rendering.
	 *
	 * @var object $row
	 */
	public static $row = null;

	/**
	 * Array containing the fields that need to be rendered in form view.
	 *
	 * @var array $form
	 */
	public static $form = null;

	/**
	 * Array containing the the messages enqueued by the application.
	 *
	 * @var array $messages
	 */
	public static $messages = array();

	/**
	 * Setter function for the title variable.
	 *
	 * @param string $title
	 *
	 * @return void
	 */
	public static function setTitle($title)
	{
		self::$title = $title;
	}

	/**
	 * Getter function for the title variable.
	 *
	 * @return string $title
	 */
	public static function getTitle()
	{
		return self::$title;
	}

	/**
	 * Setter function for the menu.
	 *
	 * @param object $menu	The menu object.
	 * @param string $name	The menu name.
	 *
	 * @return void
	 */
	public static function setMenu($menu, $name = null)
	{
		if (is_null($name))
		{
			self::$menu = $menu;
		}
		else
		{
			self::$menu[$name] = $menu;
		}

	}

	/**
	 * Getter function for the menu.
	 * @param string $name	The menu name to fetch. Leave null to fetch all the menus.
	 *
	 * @return array $menu
	 */
	public static function getMenu($name = null)
	{
		if (is_null($name))
		{
			return self::$menu;
		}
		else
		{
			return self::$menu[$name];
		}

	}

	/**
	 * Setter function for the actions.
	 *
	 * @param array $actions
	 *
	 * @return void
	 */
	public static function setActions($actions)
	{
		self::$actions = $actions;
	}

	/**
	 * Getter function for the actions.
	 *
	 * @return array $actions
	 */
	public static function getActions()
	{
		return self::$actions;
	}

	/**
	 * Setter function for the rows variable.
	 *
	 * @param array $rows
	 *
	 * @return void
	 */
	public static function setRows($rows)
	{
		self::$rows = $rows;
	}

	/**
	 * Getter function for the rows variable.
	 *
	 * @return array $rows
	 */
	public static function getRows()
	{
		return self::$rows;
	}

	/**
	 * Setter function for the pagination variable.
	 *
	 * @param object $pagination
	 *
	 * @return void
	 */
	public static function setPagination($pagination)
	{
		self::$pagination = $pagination;
	}

	/**
	 * Getter function for the pagination variable.
	 *
	 * @return object $pagination
	 */
	public static function getPagination()
	{
		return self::$pagination;
	}

	/**
	 * Setter function for the filters variable.
	 *
	 * @param array $filters
	 * @param string $location	The filters target location.
	 *
	 * @return void
	 */
	public static function setFilters($filters, $location = null)
	{
		if (is_null($location))
		{
			self::$filters = $filters;
		}
		else
		{
			self::$filters[$location] = $filters;
		}
	}

	/**
	 * Getter function for the filters variable.
	 *
	 * @return string $location The filters target location.
	 */
	public static function getFilters($location = null)
	{
		if (is_null($location))
		{
			return self::$filters;
		}
		else
		{
			return self::$filters[$location];
		}
	}

	/**
	 * Setter function for the toolbar variable.
	 *
	 * @param array $toolbar
	 *
	 * @return void
	 */
	public static function setToolbar($toolbar)
	{
		self::$toolbar = $toolbar;
	}

	/**
	 * Getter function for the toolbar variable.
	 *
	 * @return array $toolbar
	 */
	public static function getToolbar()
	{
		return self::$toolbar;
	}

	/**
	 * Setter function for the batch variable.
	 *
	 * @param array $batch
	 *
	 * @return void
	 */
	public static function setBatch($batch)
	{
		self::$batch = $batch;
	}

	/**
	 * Getter function for the batch variable.
	 *
	 * @return array $batch
	 */
	public static function getBatch()
	{
		return self::$batch;
	}

	/**
	 * Setter function for the row variable.
	 *
	 * @param object $row
	 *
	 * @return void
	 */
	public static function setRow($row)
	{
		self::$row = $row;
	}

	/**
	 * Getter function for the row variable.
	 *
	 * @return object $row
	 */
	public static function getRow()
	{
		return self::$row;
	}

	/**
	 * Setter function for the form variable.
	 *
	 * @param array $form
	 *
	 * @return void
	 */
	public static function setForm($form)
	{
		self::$form = $form;
	}

	/**
	 * Getter function for the form variable.
	 *
	 * @return array $form
	 */
	public static function getForm()
	{
		return self::$form;
	}

	/**
	 * Adds a filter to the filters array that will be rendered on the page.
	 *
	 * @param string $id		The id of the filter. This identifier allows the client to get the specific filter from the Backbone filters collection.
	 * @param string $label		The label of the filter.
	 * @param string $input		The HTML that the filter outputs.
	 * @param string $location	The location of the filter. Use this to set a position so the client can render filters together in specific areas.
	 *
	 * @return void
	 */
	public static function addFilter($id, $label, $input, $radio = false, $location = null)
	{
		$filter = new stdClass;
		$filter->id = $id;
		$filter->label = $label ? $label : '';
		$filter->input = $input ? $input : '';
		$filter->radio = $radio;
		self::$filters[$location][$id] = $filter;
	}

	/**
	 * Removes a filter from the filters array that will be rendered on the page.
	 *
	 * @param string $id		The id of the filter to remove.
	 *
	 * @return void
	 */
	public static function removeFilter($id)
	{
		foreach (self::$filters as $location)
		{
			if (isset($self::$filters[$location][$id]))
			{
				unset($self::$filters[$location][$id]);
			}
		}
	}

	/**
	 * Adds a menu link to the menu array that will be rendered on the page.
	 *
	 * @param string $id			The id of the menu link. This identifier allows the client to get the specific link from the Backbone menu collection.
	 * @param string $name			The name of the menu link.
	 * @param array $attributes		The attributes of the menu link.
	 * @param string $menu			The name of the menu that the link will be attached.
	 *
	 * @return void
	 */
	public static function addMenuLink($id, $name, $attributes = array(), $menu = 'primary')
	{
		$link = new stdClass;
		$link->id = $id;
		$link->name = JText::_($name);
		$link->attributes = $attributes;
		self::$menu[$menu][$id] = $link;
	}

	/**
	 * Removes a menu link from the menu array that will be rendered on the page.
	 *
	 * @param string $id	The id of the menu link to remove.
	 *
	 * @return void
	 */
	public static function removeMenuLink($id)
	{
		foreach (self::$menu as $menu)
		{
			if (isset($self::$menu[$menu][$id]))
			{
				unset($self::$menu[$menu][$id]);
			}
		}

	}

	/**
	 * Adds an action button to the actions array.
	 *
	 * @param string $id			The id of the action. This identifier allows the client to get the specific action.
	 * @param string $name			The name of the action.
	 * @param array $attributes		The attributes of the button.
	 *
	 * @return void
	 */
	public static function addAction($id, $name, $attributes = array())
	{
		$button = new stdClass;
		$button->id = $id;
		$button->name = JText::_($name);
		$button->attributes = $attributes;
		self::$actions[$id] = $button;
	}

	/**
	 * Removes a button from the actions array.
	 *
	 * @param string $id	The id of the button to remove.
	 *
	 * @return void
	 */
	public static function removeAction($id)
	{
		if (isset($self::$actions[$id]))
		{
			unset($self::$actions[$id]);
		}

	}

	/**
	 * Adds an action button to the toolbar array.
	 *
	 * @param string $id			The id of the action. This identifier allows the client to get the specific action.
	 * @param string $name			The name of the action.
	 * @param array $attributes		The attributes of the button.
	 *
	 * @return void
	 */
	public static function addToolbarAction($id, $name, $attributes = array())
	{
		$button = new stdClass;
		$button->id = $id;
		$button->name = JText::_($name);
		$button->attributes = $attributes;
		self::$toolbar[$id] = $button;
	}

	/**
	 * Removes a button from the actions array.
	 *
	 * @param string $id	The id of the button to remove.
	 *
	 * @return void
	 */
	public static function removeToolbarAction($id)
	{
		if (isset($self::$toolbar[$id]))
		{
			unset($self::$toolbar[$id]);
		}

	}

	/**
	 * Adds an action to the batch operations array.
	 *
	 * @param string $id		The id of the action. This identifier allows the client to get the specific action from the Backbone batch collection.
	 * @param string $label		The label of the action.
	 * @param string $input		The HTML of the action.
	 *
	 * @return void
	 */
	public static function addBatchAction($id, $label, $input)
	{
		$action = new stdClass;
		$action->id = 'appBatch'.JString::ucfirst($id);
		$action->label = $label;
		$action->input = $input;
		self::$batch[$id] = $action;
	}

	/**
	 * Removes an action from the batch operations array.
	 *
	 * @param string $id		The id of the action to be removed.
	 *
	 * @return void
	 */
	public static function removeBatchAction($id)
	{
		if (isset($self::$batch[$id]))
		{
			unset($self::$batch[$id]);
		}
	}

	/**
	 * Adds a field to the form array which contains the fields to be rendered by the client in a form view.
	 *
	 * @param string $id	The id of the field. This identifier allows the client to get the specific field from the Backbone form collection.
	 * @param string $html	The HTML of the field.
	 *
	 * @return void
	 */
	public static function addFormField($id, $html)
	{
		self::$form[$id] = $html;
	}

	/**
	 * Removes a field from the form array.
	 *
	 * @param string $id	The id of the fieldto be removed.
	 *
	 * @return void
	 */
	public static function removeFormField($id)
	{
		if (isset($self::$form[$id]))
		{
			unset($self::$form[$id]);
		}
	}

	/**
	 * Builds the whole response object.
	 *
	 * @return  object $response.
	 */
	public static function render()
	{
		if (!is_object(self::$response))
		{
			self::$response = new stdClass;
		}
		self::$response->title = self::getTitle();
		self::$response->menu = self::getMenu();
		self::$response->actions = self::getActions();
		self::$response->rows = self::getRows();
		self::$response->pagination = self::getPagination();
		self::$response->filters = self::getFilters();
		self::$response->toolbar = self::getToolbar();
		self::$response->batch = self::getBatch();
		self::$response->row = self::getRow();
		self::$response->form = self::getForm();
		self::$response->messages = JFactory::getApplication()->getMessageQueue();
		return self::$response;
	}

	/**
	 * This function is used to process all PHP errors and convert them to messages instead of outputing directly to the screen.
	 * More information at http://php.net/manual/en/function.set-error-handler.php.
	 * This guarantees that we will always have a valid JSON response.
	 *
	 * @param integer $code				The code of the error.
	 * @param string $description		The description of the error.
	 * @param string $file				The filename that the error was raised in.
	 * @param integer $line				The line number the error was raised at.
	 *
	 * @return void
	 */
	public static function errorHandler($code, $description, $file, $line)
	{
		$application = JFactory::getApplication();

		if (!(error_reporting() & $code))
		{
			return;
		}

		switch ($code)
		{
			case E_ERROR :
				$message = 'Error['.$code.'] '.$description.'. Line '.$line.' in file '.$file;
				$type = 'error';
				break;

			case E_WARNING :
				$message = 'Warning['.$code.'] '.$description.'. Line '.$line.' in file '.$file;
				$type = 'warning';
				break;

			case E_NOTICE :
				$message = 'Notice['.$code.'] '.$description.'. Line '.$line.' in file '.$file;
				$type = 'notice';
				break;

			default :
				$message = 'Uknown error type['.$code.'] '.$description.'. Line '.$line.' in file '.$file;
				$type = 'error';
				break;
		}

		$application->enqueueMessage($message, 'error');

		return;
	}

	/**
	 * This function is used to process all PHP errors and convert them to messages instead of outputing directly to the screen.
	 * More information at http://php.net/manual/en/function.set-error-handler.php.
	 * This guarantees that we will always have a valid JSON response.
	 *
	 * @param string $text		The text of the error.
	 * @param integer $status	The HTTP status.
	 *
	 * @return void
	 */
	public static function throwError($text, $status = 400)
	{
		header('HTTP/1.1 '.$status);
		jexit($text);
	}

}
