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
		$tmpId = $this->input->get('tmpId', '', 'cmd');
		$id = $itemId ? $itemId : $tmpId;
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

		// Add image using helper
		$image = K2HelperImages::addResourceImage($type, $id, $file, $path);

		// Update the database if needed
		if ($itemId)
		{
			JTable::addIncludePath(JPATH_ADMINISTRATOR.'/components/com_k2/tables');
			if ($type == 'item')
			{
				$table = JTable::getInstance('Items', 'K2Table');
				$table->load($itemId);
				$value = json_decode($table->image);
				$value->flag = 1;
				$table->image = json_encode($value);
				$table->store();
			}
			else if ($type == 'category')
			{
				$table = JTable::getInstance('Categories', 'K2Table');
				$table->load($itemId);
				$value = json_decode($table->image);
				$value->flag = 1;
				$table->image = json_encode($value);
				$table->store();
			}
			else if ($type == 'user')
			{
				$table = JTable::getInstance('Users', 'K2Table');
				$table->load($itemId);
				$table->image = 1;
				$table->store();
			}

		}

		// Response
		echo json_encode($image);
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
		$id = $input->get('id', '', 'cmd');
		$itemId = $input->get('itemId', 0, 'int');

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

		// Remove image using helper
		K2HelperImages::removeResourceImage($type, $itemId, $id);

		// Update the database if needed
		if ($itemId)
		{
			JTable::addIncludePath(JPATH_ADMINISTRATOR.'/components/com_k2/tables');
			if ($type == 'item')
			{
				$table = JTable::getInstance('Items', 'K2Table');
				$table->load($itemId);
				$value = json_decode($table->image);
				$value->flag = 0;
				$table->image = json_encode($value);
				$table->store();
			}
			else if ($type == 'category')
			{
				$table = JTable::getInstance('Categories', 'K2Table');
				$table->load($itemId);
				$value = json_decode($table->image);
				$value->flag = 0;
				$table->image = json_encode($value);
				$table->store();
			}
			else if ($type == 'user')
			{
				$table = JTable::getInstance('Users', 'K2Table');
				$table->load($itemId);
				$table->image = 0;
				$table->store();
			}

		}

		// Response
		K2Response::setResponse(true);
	}

}
