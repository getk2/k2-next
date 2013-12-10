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

require_once dirname(__FILE__).'/helper.php';
require_once JPATH_SITE.'/components/com_k2/helpers/route.php';
require_once JPATH_ADMINISTRATOR.'/components/com_k2/models/items.php';

switch ($params->get('usage', 'archive'))
{
	case 'archive' :
		$months = ModK2ToolsHelper::getArchive($params);
		require JModuleHelper::getLayoutPath('mod_k2_tools', 'archive');
		break;

	case 'authors' :
		$authors = ModK2ToolsHelper::getAuthors($params);
		require JModuleHelper::getLayoutPath('mod_k2_tools', 'authors');
		break;

	case 'calendar' :
		$calendar = ModK2ToolsHelper::calendar($params);
		require JModuleHelper::getLayoutPath('mod_k2_tools', 'calendar');
		break;

	case 'breadcrumbs' :
		$breadcrumbs = ModK2ToolsHelper::breadcrumbs($params);
		require JModuleHelper::getLayoutPath('mod_k2_tools', 'breadcrumbs');
		break;

	case 'categories' :
		$output = ModK2ToolsHelper::treerecurse($params, 0, 0, true);
		require JModuleHelper::getLayoutPath('mod_k2_tools', 'categories');
		break;

	case 'categoriesList' :
		echo ModK2ToolsHelper::treeselectbox($params);
		break;

	case 'search' :
		$categoryFilter = ModK2ToolsHelper::getSearchCategoryFilter($params);
		$action = JRoute::_(K2HelperRoute::getSearchRoute());
		require JModuleHelper::getLayoutPath('mod_k2_tools', 'search');
		break;

	case 'tags' :
		$tags = ModK2ToolsHelper::tagCloud($params);
		require JModuleHelper::getLayoutPath('mod_k2_tools', 'tags');
		break;

	case 'custom' :
		$customcode = ModK2ToolsHelper::renderCustomCode($params);
		require JModuleHelper::getLayoutPath('mod_k2_tools', 'customcode');
		break;
}
