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

// Get user
$user = JFactory::getUser();

// Process only if user has the permission
if ($user->authorise('core.manage', 'com_k2'))
{
	// Load helper
	require_once dirname(__FILE__).'/helper.php';

	// Fetch data depending on settings
	if ($params->get('latestItems', 1))
	{
		$latestItems = ModK2StatsHelper::getLatestItems();
	}
	if ($params->get('popularItems', 1))
	{
		$popularItems = ModK2StatsHelper::getPopularItems();
	}
	if ($params->get('mostCommentedItems', 1))
	{
		$mostCommentedItems = ModK2StatsHelper::getMostCommentedItems();
	}
	if ($params->get('latestComments', 1))
	{
		$latestComments = ModK2StatsHelper::getLatestComments();
	}
	if ($params->get('statistics', 1))
	{
		$statistics = ModK2StatsHelper::getStatistics();
	}

	// Output
	require JModuleHelper::getLayoutPath('mod_k2_stats');

}
