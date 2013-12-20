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
 * Build the route for the K2 component
 *
 * @return  array  An array of URL arguments
 * @return  array  The URL arguments to use to assemble the subsequent URL.
 * @since    1.5
 */

function K2BuildRoute(&$query)
{
	// Initialize segments
	$segments = array();

	// If we are viewing a menu item check if it is the current one. If it is remove all it's variables
	if (!empty($query['Itemid']))
	{
		// Get application
		$application = JFactory::getApplication();

		// Get menu
		$menu = $application->getMenu();

		// Get active
		$active = $menu->getItem($query['Itemid']);

		// Assume that this is our menu item
		$match = true;

		// Check that all variables match
		foreach ($active->query as $key => $value)
		{
			// The variable of the menu does not exist in query. Don't match and break
			if (!isset($query[$key]))
			{
				$match = false;
				break;
			}

			// Check for numeric values ( for example when id contains alias )
			$checkedValue = is_numeric($value) ? (int)$query[$key] : $query[$key];

			// The variable of the menu does exist in query but has different value. Don't match and break
			if ($checkedValue != $value)
			{
				$match = false;
				break;
			}
		}

		// If the menu item is verified unset the common query variables. Keep only Itemid and option
		if ($match)
		{
			foreach ($active->query as $key => $value)
			{
				if ($key != 'Itemid' && $key != 'option')
				{
					unset($query[$key]);
				}
			}
		}
	}
		

	if (isset($query['view']))
	{
		$view = $query['view'];
		$segments[] = $view;
		unset($query['view']);
	}
	if (isset($query['task']))
	{
		$task = $query['task'];
		$segments[] = $task;
		unset($query['task']);
	}

	if (isset($query['id']))
	{
		$id = $query['id'];
		$segments[] = $id;
		unset($query['id']);
	}
	if (isset($query['year']))
	{
		$year = $query['year'];
		$segments[] = $year;
		unset($query['year']);
	}
	if (isset($query['month']))
	{
		$month = $query['month'];
		$segments[] = $month;
		unset($query['month']);
	}
	if (isset($query['day']))
	{
		$day = $query['day'];
		$segments[] = $day;
		unset($query['day']);
	}
	if (isset($query['hash']))
	{
		$hash = $query['hash'];
		$segments[] = $hash;
		unset($query['hash']);
	}

	return $segments;
}

/**
 * Parse the segments of a URL.
 *
 * @return  array  The segments of the URL to parse.
 *
 * @return  array  The URL attributes to be used by the application.
 * @since    1.5
 */

function K2ParseRoute($segments)
{
	$vars = array();
	$vars['view'] = $segments[0];

	if ($vars['view'] == 'itemlist')
	{
		$vars['task'] = $segments[1];
		switch($vars['task'])
		{
			case 'category' :
			case 'tag' :
			case 'user' :
			case 'module' :
				if (isset($segments[2]))
				{
					$vars['id'] = $segments[2];
				}
				break;
			case 'date' :
				if (isset($segments[2]))
				{
					$vars['year'] = $segments[2];
				}
				if (isset($segments[3]))
				{
					$vars['month'] = $segments[3];
				}
				if (isset($segments[4]))
				{
					$vars['day'] = $segments[4];
				}
				if (isset($segments[5]))
				{
					$vars['categories'] = $segments[5];
				}
				break;
		}

	}
	else if ($vars['view'] == 'item')
	{
		$vars['id'] = $segments[1];

	}
	else if ($vars['view'] == 'attachments')
	{
		$vars['id'] = $segments[2];
		$vars['hash'] = $segments[3];
	}

	return $vars;
}
