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

require_once JPATH_ADMINISTRATOR.'/components/com_k2/resources/items.php';
require_once JPATH_ADMINISTRATOR.'/components/com_k2/resources/categories.php';

class K2Router extends JComponentRouterBase
{

	private $params = null;
	private $menu = null;
	private $active = null;

	public function __construct()
	{
		$this->params = JComponentHelper::getParams('com_k2');
		$application = JFactory::getApplication();
		$this->menu = $application->getMenu();
		$this->active = $this->menu->getActive();
	}

	/**
	 * Build the route for the K2 component
	 *
	 * @param   array  &$query  An array of URL arguments
	 *
	 * @return  array  The URL arguments to use to assemble the subsequent URL.
	 *
	 * @since   3.3
	 */

	public function build(&$query)
	{
		// Initialize segments
		$segments = array();

		// Handle the matched menu item ( if any )
		if (empty($query['Itemid']))
		{
			unset($query['Itemid']);
		}
		else
		{
			// Get the matched menu item
			$item = $this->menu->getItem($query['Itemid']);

			// Itemlist menu link
			if (isset($query['view']) && $query['view'] == 'itemlist' && isset($item->query['view']) && $item->query['view'] == 'itemlist')
			{
				unset($query['view']);
				unset($query['task']);
				if (isset($query['id']))
				{
					unset($query['id']);
				}
			}

			// Item menu link
			if (isset($query['view']) && $query['view'] == 'item' && isset($item->query['view']) && $item->query['view'] == 'item')
			{
				unset($query['view']);
				unset($query['id']);
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
			if (strpos($query['id'], ':') !== false)
			{
				list($id, $alias) = explode(':', $query['id'], 2);
				$query['id'] = $id.'-'.$alias;
			}
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
		if ($this->params->get('k2Sef') && count($segments))
		{
			$segments = $this->advacedBuild($segments, $query);
		}

		return $segments;
	}

	/**
	 * Parse the segments of a URL.
	 *
	 * @param   array  &$segments  The segments of the URL to parse.
	 *
	 * @return  array  The URL attributes to be used by the application.
	 *
	 * @since   3.3
	 */

	public function parse(&$segments)
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
		else if ($vars['view'] == 'calendar')
		{
			$vars['year'] = $segments[1];
			$vars['month'] = $segments[2];
		}
		if ($this->params->get('k2Sef') && count($vars))
		{
			$vars = $this->advancedParse($vars, $segments);
		}

		return $vars;
	}

	/**
	 * Build the route for the K2 component using the advanced SEF options
	 *
	 * @param  array  An array of URL arguments
	 * @return  void
	 */

	private function advacedBuild($segments, $query)
	{
		if (!empty($query['Itemid']))
		{
			// Items
			if ($query['Itemid'] == $this->params->get('k2SefLabelItem'))
			{
				$view = 'item';
				unset($segments[0]);
			}
			// Categories
			else if ($query['Itemid'] == $this->params->get('k2SefLabelCat'))
			{
				$view = 'itemlist';
				$task = 'category';
				unset($segments[0]);
				unset($segments[1]);
			}
			// Tags
			else if ($query['Itemid'] == $this->params->get('k2SefLabelTag'))
			{
				$view = 'itemlist';
				$task = 'tag';
				unset($segments[0]);
				unset($segments[1]);
			}
			// Users
			else if ($query['Itemid'] == $this->params->get('k2SefLabelUser'))
			{
				$view = 'itemlist';
				$task = 'user';
				unset($segments[0]);
				unset($segments[1]);
			}
			// Date
			else if ($query['Itemid'] == $this->params->get('k2SefLabelDate'))
			{
				$view = 'itemlist';
				$task = 'date';
				unset($segments[0]);
				unset($segments[1]);
			}
			// If we have a matched Itemid from the category of the item then unset the "item" from the URL.
			else
			{
				$item = $this->menu->getItem($query['Itemid']);
				if ($item && isset($item->query['task']) && $item->query['task'] == 'category')
				{
					$view = $segments[0];
					unset($segments[0]);
				}
			}

		}

		if (isset($view))
		{
			if ($view == 'itemlist')
			{
				if (!isset($task) && isset($segments[1]))
				{
					$task = $segments[1];
				}

				if ($task == 'category' && isset($segments[2]))
				{
					$segments[2] = $this->buildIdByPattern($segments[2], $this->params->get('k2SefPatternCat'));
				}
				else if ($task == 'tag' && isset($segments[2]))
				{
					$segments[2] = $this->buildIdByPattern($segments[2], $this->params->get('k2SefPatternTag'));
				}

			}
			else if ($view == 'item')
			{
				$segments[1] = $this->buildIdByPattern($segments[1], $this->params->get('k2SefPatternItem'));
			}
		}

		// Reorder segments array
		$segments = array_values($segments);

		return $segments;

	}

	/**
	 * Parse the route for the K2 component using the advanced SEF options
	 *
	 * @param  array  An array of already parsed vars
	 * @param  array  An array of URL arguments
	 *
	 * @return  void
	 */
	private function advancedParse($vars, $segments)
	{
		$item = $this->active;

		if ($item && $item->component == 'com_k2')
		{
			if ($item->id == $this->params->get('k2SefLabelItem'))
			{
				$vars['view'] = 'item';
				$itemId = $segments[0];
			}
			if ($item->id == $this->params->get('k2SefLabelCat'))
			{
				$vars['view'] = 'itemlist';
				$vars['task'] = 'category';
				$categoryId = $segments[0];
			}
			else if ($item->id == $this->params->get('k2SefLabelUser'))
			{
				$vars['view'] = 'itemlist';
				$vars['task'] = 'user';
				$userId = $segments[0];
			}
			else if ($item->id == $this->params->get('k2SefLabelTag'))
			{
				$vars['view'] = 'itemlist';
				$vars['task'] = 'tag';
				$tagId = $segments[0];
			}
			else if ($item->id == $this->params->get('k2SefLabelDate'))
			{
				$vars['view'] = 'itemlist';
				$vars['task'] = 'date';
				if (isset($segments[0]))
				{
					$vars['year'] = $segments[0];
				}
				if (isset($segments[1]))
				{
					$vars['month'] = $segments[1];
				}
				if (isset($segments[2]))
				{
					$vars['day'] = $segments[2];
				}
				if (isset($segments[3]))
				{
					$vars['category'] = $segments[3];
				}
				return $vars;
			}
			else if (isset($item->query['task']) && $item->query['task'] == 'category' && count($segments) === 1)
			{
				$segments[1] = $vars['view'];
				$vars['view'] = 'item';
			}
		}
		if ($vars['view'] == 'itemlist')
		{
			switch($vars['task'])
			{
				case 'category' :
					$id = isset($categoryId) ? $categoryId : $segments[2];
					$vars['id'] = $this->parseIdByPattern($id, $this->params->get('k2SefPatternCat'), 'category');
					break;
				case 'user' :
					$vars['id'] = isset($userId) ? $userId : $segments[2];
					break;
				case 'tag' :
					$id = isset($tagId) ? $tagId : $segments[2];
					$vars['id'] = $this->parseIdByPattern($id, $this->params->get('k2SefPatternTag'), 'tag');
					break;
			}
		}
		else if ($vars['view'] == 'item')
		{
			$id = isset($itemId) ? $itemId : $segments[1];
			$vars['id'] = $this->parseIdByPattern($id, $this->params->get('k2SefPatternItem'), 'item');
		}

		return $vars;

	}

	private function buildIdByPattern($input, $pattern)
	{
		$patterns = array('id-dash-alias', 'id-slash-alias', 'id', 'alias');
		if (!in_array($pattern, $patterns))
		{
			$pattern = 'id-dash-alias';
		}
		if ($pattern == 'id-dash-alias')
		{
			$result = $input;
		}
		else
		{
			list($id, $alias) = explode('-', $input, 2);
			if ($pattern == 'id-slash-alias')
			{
				$result = $id.'/'.$alias;
			}
			else if ($pattern == 'id')
			{
				$result = (int)$id;
			}
			else if ($pattern == 'alias')
			{
				$result = $alias;
			}
		}

		return $result;
	}

	private function parseIdByPattern($input, $pattern, $type)
	{
		$patterns = array('id-dash-alias', 'id-slash-alias', 'id', 'alias');
		if (!in_array($pattern, $patterns))
		{
			$pattern = 'id-dash-alias';
		}
		if ($pattern == 'id-dash-alias')
		{
			$result = $input;
		}
		else
		{
			if ($type == 'item')
			{
				$row = K2Items::getInstance($input);
			}
			else if ($type == 'category')
			{
				$row = K2Categories::getInstance($input);
			}
			else if ($type == 'tag')
			{
				$row = K2Tags::getInstance($input);
			}
			$result = $row->id.':'.$row->alias;
		}
		return $result;
	}

}
