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

	public function read($mode = 'row', $id = null)
	{
		// Get application
		$application = JFactory::getApplication();

		// Get input
		$itemId = $application->input->get('itemId', 0, 'int');
		$offset = $application->input->get('limitstart', 0, 'int');
		
		if ($itemId)
		{
			// Get Item
			require_once JPATH_ADMINISTRATOR.'/components/com_k2/resources/items.php';
			$item = K2Items::getInstance($itemId);

			// Check access
			$item->checkSiteAccess();

			// Get rows
			$comments = $item->getComments($offset);

			// Response
			K2Response::setRows($comments->rows);			
			K2Response::setPagination($comments->pagination);
		}

		echo K2Response::render();

		// Return
		return $this;
	}

}
