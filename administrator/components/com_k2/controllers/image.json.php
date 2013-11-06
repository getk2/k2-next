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

/**
 * Image JSON controller.
 */

class K2ControllerImage extends K2Controller
{

	public function upload()
	{
		// Check for token
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		// Get input
		$input = JFactory::getApplication()->input;
		$type = $input->get('type', '', 'cmd');
		$itemId = $input->get('itemId', 0, 'int');
		$tmpId = $input->get('tmpId', '', 'cmd');
		$id = $itemId ? $itemId : $tmpId;
		$file = $input->files->get('file');
		$path = $input->get('path', '', 'string');
		$path = str_replace(JURI::root(true).'/', '', $path);

		// Add image using helper
		$image = K2HelperImages::addResourceImage($type, $id, $file, $path);

		// Update the database if needed
		if ($itemId)
		{
			JTable::addIncludePath(JPATH_ADMINISTRATOR.'/components/com_k2/tables');
			$table = ($type == 'item') ? JTable::getInstance('Items', 'K2Table') : JTable::getInstance('Categories', 'K2Table');
			$table->load($itemId);
			$value = json_decode($table->image);
			$value->flag = 1;
			$table->image = json_encode($value);
			$table->store();
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
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		// Get input
		$input = JFactory::getApplication()->input;
		$type = $input->get('type', '', 'cmd');
		$id = $input->get('id', '', 'cmd');
		$itemId = $input->get('itemId', 0, 'int');

		// Remove image using helper
		K2HelperImages::removeResourceImage($type, $itemId, $id);

		// Update the database if needed
		if ($itemId)
		{
			JTable::addIncludePath(JPATH_ADMINISTRATOR.'/components/com_k2/tables');
			$table = ($type == 'item') ? JTable::getInstance('Items', 'K2Table') : JTable::getInstance('Categories', 'K2Table');
			$table->load($itemId);
			$value = json_decode($table->image);
			$value->flag = 0;
			$table->image = json_encode($value);
			$table->store();
		}

		// Response
		echo json_encode(true);
	}

}
