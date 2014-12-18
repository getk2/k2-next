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

require_once dirname(__FILE__).'/helper.php';

$user = JFactory::getUser();

if ($user->guest)
{
	$login = ModK2UserHelper::getLogin($params);
	$layout = 'login';
}
else
{
	$user = K2Users::getInstance($user->id);
	$user->numOfComments = $user->getNumOfComments();
	$logout = ModK2UserHelper::getLogout($params);
	$layout = 'userblock';
}

include dirname(__FILE__).'/legacy.php';
require JModuleHelper::getLayoutPath('mod_k2_user', $layout);
