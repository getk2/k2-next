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
require_once JPATH_ADMINISTRATOR.'/components/com_k2/helpers/images.php';
require_once JPATH_ADMINISTRATOR.'/components/com_k2/resources/items.php';
require_once JPATH_ADMINISTRATOR.'/components/com_k2/resources/categories.php';

/**
 * Image JSON controller.
 */

class K2ControllerImages extends K2Controller
{

	public function upload()
	{
		// Check for token
		JSession::checkToken() or K2Response::throwError(JText::_('JINVALID_TOKEN'));

		// Get user
		$user = JFactory::getUser();

		// Get input
		$type = $this->input->get('type', '', 'cmd');
		$itemId = $this->input->get('itemId', 0, 'int');
		$categoryId = $this->input->get('categoryId', 0, 'int');
		$file = $this->input->files->get('file');
		$path = $this->input->get('path', '', 'string');
		$path = str_replace(JURI::root(true).'/', '', $path);

		// Permissions check
		if ($itemId)
		{
			if ($type == 'item')
			{
				$authorised = K2Items::getInstance($itemId)->canEdit;
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
		if ($type == 'item')
		{
			$image = K2HelperImages::addItemImage($file, $path, $categoryId);
		}
		else if ($type == 'category')
		{
			$image = K2HelperImages::addCategoryImage($file, $path);
		}
		else if ($type == 'user')
		{
			$image = K2HelperImages::addUserImage($file, $path);
		}

		// Response
		echo json_encode($image);

		return $this;
	}

	/**
	 * Delete function.
	 * Deletes a resource.
	 * Usually there will be no need to override this function.
	 *
	 * @return void
	 */
	protected function delete()
	{
		// Check for token
		JSession::checkToken() or K2Response::throwError(JText::_('JINVALID_TOKEN'));

		// User
		$user = JFactory::getUser();

		// Get input
		$input = $this->input;
		$type = $input->get('type', '', 'cmd');
		$imageId = $input->get('id', '', 'cmd');
		$itemId = $input->get('itemId', 0, 'int');
		$categoryId = $this->input->get('categoryId', 0, 'int');

		// Permissions check
		if ($itemId)
		{
			if ($type == 'item')
			{
				$authorised = K2Items::getInstance($itemId)->canEdit;
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
			$authorised = true;
		}
		if (!$authorised)
		{
			K2Response::throwError(JText::_('K2_YOU_ARE_NOT_AUTHORIZED_TO_PERFORM_THIS_OPERATION'), 403);
		}

		// Remove image using helper depending on type
		if ($type == 'item')
		{
			K2HelperImages::removeItemImage($imageId, $categoryId);
		}
		else if ($type == 'category')
		{
			K2HelperImages::removeCategoryImage($imageId);
		}
		else if ($type == 'user')
		{
			K2HelperImages::removeUserImage($imageId);
		}

		// Response
		K2Response::setResponse(true);
	}

}
