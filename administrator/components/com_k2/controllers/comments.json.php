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

require_once JPATH_ADMINISTRATOR.'/components/com_k2/controller.php';

/**
 * Comments JSON controller.
 */

class K2ControllerComments extends K2Controller
{
	protected function onBeforeRead($mode, $id)
	{
		$user = JFactory::getUser();
		$application = JFactory::getApplication();
		$params = JComponentHelper::getParams('com_k2');
		$itemId = $application->input->get('itemId', 0, 'int');
		if ($application->isSite() && !$params->get('comments'))
		{
			return false;
		}

		if (!$itemId && !$user->authorise('k2.comment.edit', 'com_k2'))
		{
			return false;
		}

		if ($itemId && $application->isSite())
		{
			$item = K2Items::getInstance($itemId);
			return $item->checkSiteAccess();
		}

		if ($mode == 'row')
		{
			// Edit
			/*if ($id)
			 {
			 $item = K2Items::getInstance($id);
			 $authorized = $item->canEdit;
			 }
			 else
			 {
			 $authorized = $user->authorise('k2.item.create', 'com_k2');
			 }*/
		}
		else
		{

		}

		return true;
	}

}
