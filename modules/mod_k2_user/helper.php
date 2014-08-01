<?php
/**
 * @version		3.0.0b
 * @package		K2
 * @author		JoomlaWorks http://www.joomlaworks.net
 * @copyright	Copyright (c) 2006 - 2014 JoomlaWorks Ltd. All rights reserved.
 * @license		GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 */

// no direct access
defined('_JEXEC') or die ;

require_once JPATH_SITE.'/components/com_users/helpers/route.php';

class ModK2UserHelper
{

	public static function getLogin($params)
	{
		$login = new stdClass;
		$login->passwordFieldName = 'password';
		$login->resetLink = JRoute::_('index.php?option=com_users&view=reset&Itemid='.UsersHelperRoute::getResetRoute());
		$login->remindLink = JRoute::_('index.php?option=com_users&view=remind&Itemid='.UsersHelperRoute::getRemindRoute());
		$login->registrationLink = JRoute::_('index.php?option=com_users&view=registration&Itemid='.UsersHelperRoute::getRegistrationRoute());
		$login->option = 'com_users';
		$login->task = 'user.login';
		$login->allowRegistration = JComponentHelper::getParams('com_users')->get('allowUserRegistration');
		$login->return = self::getReturnURL($params, 'login');
		return $login;
	}

	public static function getLogout($params)
	{
		$logout = new stdClass;
		$logout->menu = self::getMenu($params);
		$logout->profileLink = JRoute::_('index.php?option=com_users&view=profile&layout=edit&Itemid='.UsersHelperRoute::getProfileRoute());
		$logout->option = 'com_users';
		$logout->task = 'user.logout';
		$logout->return = self::getReturnURL($params, 'logout');
		$logout->K2CommentsEnabled = JComponentHelper::getParams('com_k2')->get('comments');
		return $logout;
	}

	private static function getReturnURL($params, $type)
	{
		if ($itemid = $params->get($type))
		{
			$application = JFactory::getApplication();
			$menu = $application->getMenu();
			$item = $menu->getItem($itemid);
			$url = JRoute::_($item->link.'&Itemid='.$itemid, false);
		}
		else
		{
			// stay on the same page
			$uri = JUri::getInstance();
			$url = $uri->toString(array(
				'path',
				'query',
				'fragment'
			));
		}
		return base64_encode($url);
	}

	public static function getMenu($params)
	{
		$items = array();
		$children = array();
		if ($params->get('menu'))
		{
			$menu = JSite::getMenu();
			$items = $menu->getItems('menutype', $params->get('menu'));
		}
		foreach ($items as $item)
		{
			$item->name = $item->title;
			$item->parent = $item->parent_id;
			$index = $item->parent;
			$list = @$children[$index] ? $children[$index] : array();
			array_push($list, $item);
			$children[$index] = $list;
		}
		$items = JHTML::_('menu.treerecurse', 1, '', array(), $children, 9999, 0, 0);
		$links = array();
		foreach ($items as $item)
		{
			$item->flink = $item->link;
			switch ($item->type)
			{
				case 'separator' :
					continue;

				case 'url' :
					if ((strpos($item->link, 'index.php?') === 0) && (strpos($item->link, 'Itemid=') === false))
					{
						$item->flink = $item->link.'&Itemid='.$item->id;
					}
					break;

				case 'alias' :
					$item->flink = 'index.php?Itemid='.$item->params->get('aliasoptions');
					break;

				default :
					$router = JSite::getRouter();
					if ($router->getMode() == JROUTER_MODE_SEF)
					{
						$item->flink = 'index.php?Itemid='.$item->id;
					}
					else
					{
						$item->flink .= '&Itemid='.$item->id;
					}
					break;
			}

			if (strcasecmp(substr($item->flink, 0, 4), 'http') && (strpos($item->flink, 'index.php?') !== false))
			{
				$item->flink = JRoute::_($item->flink, true, $item->params->get('secure'));
			}
			else
			{
				$item->flink = JRoute::_($item->flink);
			}

			$item->route = $item->flink;

			$links[] = $item;
		}
		return $links;
	}

}
