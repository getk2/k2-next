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
		// Legacy
		if ($this->params->get('k2Sef') && $this->params->get('k2SefMode', 'legacy') == 'legacy')
		{
			return $this->advacedBuildLegacy($query);
		}

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
		// Legacy
		if ($this->params->get('k2Sef') && $this->params->get('k2SefMode', 'legacy') == 'legacy')
		{
			return $this->advancedParseLegacy($segments);
		}

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
			if ($query['Itemid'] == $this->params->get('k2SefPrefixItem'))
			{
				$view = 'item';
				unset($segments[0]);
			}
			// Categories
			else if ($query['Itemid'] == $this->params->get('k2SefPrefixCat'))
			{
				$view = 'itemlist';
				$task = 'category';
				unset($segments[0]);
				unset($segments[1]);
			}
			// Tags
			else if ($query['Itemid'] == $this->params->get('k2SefPrefixTag'))
			{
				$view = 'itemlist';
				$task = 'tag';
				unset($segments[0]);
				unset($segments[1]);
			}
			// Users
			else if ($query['Itemid'] == $this->params->get('k2SefPrefixUser'))
			{
				$view = 'itemlist';
				$task = 'user';
				unset($segments[0]);
				unset($segments[1]);
			}
			// Date
			else if ($query['Itemid'] == $this->params->get('k2SefPrefixDate'))
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
			if ($item->id == $this->params->get('k2SefPrefixItem'))
			{
				$vars['view'] = 'item';
				$itemId = $segments[0];
			}
			if ($item->id == $this->params->get('k2SefPrefixCat'))
			{
				$vars['view'] = 'itemlist';
				$vars['task'] = 'category';
				$categoryId = $segments[0];
			}
			else if ($item->id == $this->params->get('k2SefPrefixUser'))
			{
				$vars['view'] = 'itemlist';
				$vars['task'] = 'user';
				$userId = $segments[0];
			}
			else if ($item->id == $this->params->get('k2SefPrefixTag'))
			{
				$vars['view'] = 'itemlist';
				$vars['task'] = 'tag';
				$tagId = $segments[0];
			}
			else if ($item->id == $this->params->get('k2SefPrefixDate'))
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
			else if (isset($item->query['task']) && $item->query['task'] == 'category')
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

	/**
	 * Build the route for the K2 component using the advanced SEF options in legacy mode
	 */

	private function advacedBuildLegacy(&$query)
	{
		// Initialize
		$segments = array();

		// Detect the active menu item
		if (empty($query['Itemid']))
		{
			$menuItem = $this->menu->getActive();
		}
		else
		{
			$menuItem = $this->menu->getItem($query['Itemid']);
		}

		// Load data from the current menu item
		$mView = ( empty($menuItem->query['view'])) ? null : $menuItem->query['view'];
		$mTask = ( empty($menuItem->query['task'])) ? null : $menuItem->query['task'];
		$mId = ( empty($menuItem->query['id'])) ? null : $menuItem->query['id'];
		$mTag = ( empty($menuItem->query['tag'])) ? null : $menuItem->query['tag'];

		if (isset($query['layout']))
		{
			unset($query['layout']);
		}

		if ($mView == @$query['view'] && $mTask == @$query['task'] && $mId == @intval($query['id']) && @intval($query['id']) > 0)
		{
			unset($query['view']);
			unset($query['task']);
			unset($query['id']);
		}

		if ($mView == @$query['view'] && $mTask == @$query['task'] && $mTag == @$query['tag'] && isset($query['tag']))
		{
			unset($query['view']);
			unset($query['task']);
			unset($query['tag']);
		}

		if (isset($query['view']))
		{
			$segments[] = $query['view'];
			unset($query['view']);
		}

		if (isset($query['task']))
		{
			$segments[] = $query['task'];
			unset($query['task']);
		}

		if (isset($query['id']))
		{
			$segments[] = $query['id'];
			unset($query['id']);
		}

		if (isset($query['cid']))
		{
			$segments[] = $query['cid'];
			unset($query['cid']);
		}

		if (isset($query['tag']))
		{
			$segments[] = $query['tag'];
			unset($query['tag']);
		}

		if (isset($query['year']))
		{
			$segments[] = $query['year'];
			unset($query['year']);
		}

		if (isset($query['month']))
		{
			$segments[] = $query['month'];
			unset($query['month']);
		}

		if (isset($query['day']))
		{
			$segments[] = $query['day'];
			unset($query['day']);
		}

		if (isset($query['task']))
		{
			$segments[] = $query['task'];
			unset($query['task']);
		}

		// Item view
		if (isset($segments[0]) && $segments[0] == 'item' && @$segments[1] != 'add')
		{

			// Enabled category prefix  for items
			if ($this->params->get('k2SefLabelItem'))
			{
				// Tasks available for an item
				$itemTasks = array('edit', 'download');

				// If it's a task pick the next key
				if (in_array($segments[1], $itemTasks))
				{
					$ItemId = $segments[2];
				}
				else
				{
					$ItemId = $segments[1];
				}

				// Replace the item with the category slug
				if ($this->params->get('k2SefLabelItem') == '1')
				{
					$item = K2Items::getInstance((int)$ItemId);
					$category = K2Categories::getInstance($item->catid);
					$segments[0] = $category->id.'-'.$category->alias;
				}
				else
				{
					$segments[0] = $this->params->get('k2SefLabelItemCustomPrefix');
				}

			}
			// Remove "item" from the URL
			else
			{
				unset($segments[0]);
			}

			// Handle item id and alias
			if ($this->params->get('k2SefInsertItemId'))
			{
				if ($this->params->get('k2SefUseItemTitleAlias'))
				{
					if ($this->params->get('k2SefItemIdTitleAliasSep') == 'slash')
					{
						$segments[1] = JString::str_ireplace(':', '/', $segments[1]);
					}
					else if ($this->params->get('k2SefItemIdTitleAliasSep') == 'dash')
					{
						$segments[1] = JString::str_ireplace(':', '-', $segments[1]);
					}
				}
				else
				{
					$temp = @explode(':', $segments[1]);
					$segments[1] = $temp[0];
				}

			}
			else
			{
				if (isset($segments[1]) && $segments[1] != 'download')
				{
					// Try to split the slud
					$temp = @explode(':', $segments[1]);

					// If the slug contained an item id do not use it
					if (count($temp) > 1)
					{
						$segments[1] = $temp[1];
					}

				}
			}
		}
		// Itemlist view. Check for prefix segments
		elseif (isset($segments[0]) && $segments[0] == 'itemlist')
		{
			if (isset($segments[1]))
			{
				switch ($segments[1])
				{
					case 'category' :
						$segments[0] = $this->params->get('k2SefLabelCat', 'content');
						unset($segments[1]);
						// Handle category id and alias
						if ($this->params->get('k2SefInsertCatId'))
						{
							if ($this->params->get('k2SefUseCatTitleAlias'))
							{
								if ($this->params->get('k2SefCatIdTitleAliasSep') == 'slash')
								{
									$segments[2] = @JString::str_ireplace(':', '/', $segments[2]);
								}
								else if ($this->params->get('k2SefCatIdTitleAliasSep') == 'dash')
								{
									$segments[2] = @JString::str_ireplace(':', '-', $segments[2]);
								}
							}
							else
							{
								$temp = @explode(':', $segments[2]);
								$segments[2] = (int)$temp[0];
							}

						}
						else
						{
							// Try to split the slud
							$temp = @explode(':', $segments[2]);
							// If the slug contained an item id do not use it
							if (count($temp) > 1)
							{
								@$segments[2] = end($temp);
							}

						}

						break;
					case 'tag' :
						$segments[0] = $this->params->get('k2SefLabelTag', 'tag');
						unset($segments[1]);
						if (strpos($segments[2], ':'))
						{
							$temp = @explode(':', $segments[2]);
							$segments[2] = $temp[1];
						}
						break;
					case 'user' :
						$segments[0] = $this->params->get('k2SefLabelUser', 'author');
						unset($segments[1]);
						break;
					case 'date' :
						$segments[0] = $this->params->get('k2SefLabelDate', 'date');
						unset($segments[1]);
						break;
					case 'search' :
						$segments[0] = $this->params->get('k2SefLabelSearch', 'search');
						unset($segments[1]);
						break;
					default :
						$segments[0] = 'itemlist';
						break;
				}
			}

		}
		// Return reordered segments array
		return array_values($segments);
	}

	/**
	 * Parse the route for the K2 component using the advanced SEF options in legacy mode
	 *
	 * @param  array  An array of URL arguments
	 *
	 * @return  void
	 */
	private function advancedParseLegacy($segments)
	{
		// Initialize
		$vars = array();

		$reservedViews = array('item', 'itemlist', 'media', 'users', 'comments', 'latest');

		if (!in_array($segments[0], $reservedViews))
		{
			// Category view
			if ($segments[0] == $this->params->get('k2SefLabelCat', 'content'))
			{
				$segments[0] = 'itemlist';
				array_splice($segments, 1, 0, 'category');
				if (!$this->params->get('k2SefInsertCatId'))
				{
					$category = K2categories::getInstance($segments[2]);
					$segments[2] = $category->id.':'.$category->alias;
				}
			}
			// Tag view
			elseif ($segments[0] == $this->params->get('k2SefLabelTag', 'tag'))
			{
				$segments[0] = 'itemlist';
				array_splice($segments, 1, 0, 'tag');
			}
			// User view
			elseif ($segments[0] == $this->params->get('k2SefLabelUser', 'author'))
			{
				$segments[0] = 'itemlist';
				array_splice($segments, 1, 0, 'user');
			}
			// Date view
			elseif ($segments[0] == $this->params->get('k2SefLabelDate', 'date'))
			{
				$segments[0] = 'itemlist';
				array_splice($segments, 1, 0, 'date');
			}
			// Search view
			elseif ($segments[0] == $this->params->get('k2SefLabelSearch', 'search'))
			{
				$segments[0] = 'itemlist';
				array_splice($segments, 1, 0, 'search');
			}
			// Item view
			else
			{
				// Replace the category prefix with item
				if ($this->params->get('k2SefLabelItem'))
				{
					$segments[0] = 'item';
				}
				// Reinsert the removed item segment
				else
				{
					array_splice($segments, 0, 0, 'item');
				}

				// Reinsert item id to the item alias
				if (!$this->params->get('k2SefInsertItemId') && @$segments[1] != 'download' && @$segments[1] != 'edit')
				{
					$segments[1] = str_replace(':', '-', $segments[1]);
					$item = K2Items::getInstance($segments[1]);
					$ItemId = $item->id;
					$segments[1] = $ItemId.':'.$segments[1];
				}
			}

		}

		$vars['view'] = $segments[0];

		if (!isset($segments[1]))
		{
			$segments[1] = '';
		}
		$vars['task'] = $segments[1];
		if ($segments[0] == 'itemlist')
		{
			switch ($segments[1])
			{

				case 'category' :
					if (isset($segments[2]))
					{
						$vars['id'] = $segments[2];
					}
					break;

				case 'tag' :
					if (isset($segments[2]))
					{
						$tag = K2Tags::getInstance($segments[2]);
						$vars['id'] = $tag->id;
					}
					break;

				case 'user' :
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
					break;
			}

		}
		elseif ($segments[0] == 'item')
		{
			switch ($segments[1])
			{
				case 'add' :
				case 'edit' :
					if (isset($segments[2]))
					{
						$vars['cid'] = $segments[2];
					}
					break;

				case 'download' :
					if (isset($segments[2]))
					{
						$vars['id'] = $segments[2];
					}
					break;

				default :
					$vars['id'] = $segments[1];
					if (isset($segments[2]))
					{
						$vars['id'] .= ':'.str_replace(':', '-', $segments[2]);
					}
					unset($vars['task']);
					break;
			}

		}

		if ($segments[0] == 'comments' && isset($segments[1]) && $segments[1] == 'reportSpammer')
		{
			$vars['id'] = $segments[2];
		}

		return $vars;
	}

}
