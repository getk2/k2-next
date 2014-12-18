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

require_once JPATH_ADMINISTRATOR.'/components/com_k2/controller.php';
require_once JPATH_ADMINISTRATOR.'/components/com_k2/helpers/images.php';
require_once JPATH_ADMINISTRATOR.'/components/com_k2/resources/items.php';
require_once JPATH_ADMINISTRATOR.'/components/com_k2/resources/categories.php';

/**
 * Image JSON controller.
 */

class K2ControllerImages extends K2Controller
{

	/**
	 * onBeforeRead function.
	 * Hook for chidlren controllers to check for access
	 *
	 * @param string $mode		The mode of the read function. Pass 'row' for retrieving a single row or 'list' to retrieve a collection of rows.
	 * @param mixed $id			The id of the row to load when we are retrieving a single row.
	 *
	 * @return void
	 */
	protected function onBeforeRead($mode, $id)
	{
		return false;
	}

	public function upload()
	{
		// Check for token
		JSession::checkToken() or K2Response::throwError(JText::_('JINVALID_TOKEN'));

		// Get user
		$user = JFactory::getUser();

		// Get input
		$type = $this->input->get('type', '', 'cmd');
		$itemId = $this->input->get('itemId', 0, 'int');
		$replace = $this->input->get('temp', '', 'cmd');
		$file = $this->input->files->get('file');
		$path = $this->input->get('path', '', 'string');
		$path = str_replace(JURI::root(true).'/', '', $path);
		$categoryId = null;

		// Permissions check
		if ($itemId)
		{
			if ($type == 'item')
			{
				$item = K2Items::getInstance($itemId);
				$authorised = $item->canEdit;
				$categoryId = $item->catid;
			}
			else if ($type == 'category')
			{
				$authorised = K2Categories::getInstance($itemId)->canEdit;
			}
			else if ($type == 'user')
			{
				$authorised = $user->authorise('core.edit', 'com_users') || $user->id == $itemId;
			}
		}
		else
		{
			$authorised = $user->authorise('k2.'.$type.'.create', 'com_k2');
		}
		if (!$authorised)
		{
			K2Response::throwError(JText::_('K2_YOU_ARE_NOT_AUTHORIZED_TO_PERFORM_THIS_OPERATION'), 403);
		}

		// Generate image using helper depending on type
		$image = K2HelperImages::add($type, $file, $path, $replace, $categoryId);
		
		// Response
		echo json_encode($image);

		return $this;
	}

}
