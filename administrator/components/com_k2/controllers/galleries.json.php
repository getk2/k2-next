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
require_once JPATH_ADMINISTRATOR.'/components/com_k2/classes/filesystem.php';
require_once JPATH_ADMINISTRATOR.'/components/com_k2/resources/items.php';
require_once JPATH_ADMINISTRATOR.'/components/com_k2/helpers/galleries.php';

/**
 * Galleries JSON controller.
 */

class K2ControllerGalleries extends K2Controller
{

	public function upload()
	{
		// Check for token
		JSession::checkToken() or K2Response::throwError(JText::_('JINVALID_TOKEN'));

		// File system
		$filesystem = K2FileSystem::getInstance();

		// Get input
		$input = JFactory::getApplication()->input;
		$itemId = $input->get('itemId', 0, 'int');
		$upload = $input->get('upload', '', 'cmd');
		$archive = $input->files->get('archive');

		// Permissions check
		if ($itemId)
		{
			// Existing items check permission for specific item
			$authorised = K2Items::getInstance($itemId)->canEdit;
		}
		else
		{
			// New items. We can only check the generic create permission. We cannot check against specific category since we do not know the category of the item.
			$authorised = JFactory::getUser()->authorise('k2.item.create', 'com_k2');
		}
		if (!$authorised)
		{
			K2Response::throwError(JText::_('K2_YOU_ARE_NOT_AUTHORIZED_TO_PERFORM_THIS_OPERATION'), 403);
		}

		// Create the gallery
		$gallery = K2HelperGalleries::add($archive);

		// If the current gallery is uploaded then we should remove it when we upload a new one
		if ($upload)
		{
			K2HelperGalleries::clean($upload);
		}

		// Response
		echo json_encode($gallery);

		// Return
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

		// Get id from input
		$input = $this->input;
		$itemId = $input->get('itemId', 0, 'int');
		$upload = $input->get('upload', '', 'cmd');

		// Permissions check
		$user = JFactory::getUser();
		if ($itemId && !K2Items::getInstance($itemId)->canEdit)
		{
			K2Response::throwError(JText::_('K2_YOU_ARE_NOT_AUTHORIZED_TO_PERFORM_THIS_OPERATION'), 403);
		}

		// If the gallery has been set, try to delete it
		if ($upload)
		{
			K2HelperGalleries::clean($upload);
		}

		// Return
		K2Response::setResponse(true);
	}

}
