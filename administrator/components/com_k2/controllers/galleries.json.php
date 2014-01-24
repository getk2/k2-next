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

		// Get user
		$user = JFactory::getUser();

		// Permissions check
		if (!$user->authorise('k2.item.create', 'com_k2') && !$user->authorise('k2.item.edit', 'com_k2') && !$user->authorise('k2.item.edit.own', 'com_k2'))
		{
			K2Response::throwError(JText::_('K2_YOU_ARE_NOT_AUTHORIZED_TO_PERFORM_THIS_OPERATION'), 403);
		}

		// Get input
		$input = JFactory::getApplication()->input;
		$upload = $input->get('upload', '', 'cmd');
		$url = $input->get('url', '', 'string');
		$archive = $input->files->get('archive');

		// Create the gallery and delete the previous one if it is set
		$gallery = K2HelperGalleries::add($archive, $url, $upload);

		// Response
		echo json_encode($gallery);

		// Return
		return $this;
	}

}
