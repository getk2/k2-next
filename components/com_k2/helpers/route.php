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
 * K2 route helper class.
 */
class K2HelperRoute
{
	public static function getItemRoute($id, $category = 0)
	{
		return 'index.php?option=com_k2&view=item&id='.$id;
	}

	public static function getCategoryRoute($id)
	{
		return 'index.php?option=com_k2&view=itemlist&task=category&id='.$id;
	}

	public static function getUserRoute($id)
	{
		return 'index.php?option=com_k2&view=itemlist&task=user&id='.$id;
	}

	public static function getTagRoute($id)
	{
		return 'index.php?option=com_k2&view=itemlist&task=tag&id='.$id;
	}

	public static function getDateRoute($year, $month, $day = null, $category = null)
	{
		$route = 'index.php?option=com_k2&view=itemlist&task=date&year='.$year.'&month='.$month;
		if ($day)
		{
			$route .= '&day='.$day;
		}
		if ($category)
		{
			$route .= '&category='.$category;
		}
		return $route;
	}

}
