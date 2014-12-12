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

class K2ViewImport extends JViewLegacy
{
	public function display($tpl = null)
	{
		// Get user
		$user = JFactory::getUser();

		// Permissions check
		if (!$user->authorise('core.admin', 'com_k2'))
		{
			throw new Exception(JText::_('JLIB_APPLICATION_ERROR_ACCESS_FORBIDDEN'), 403);
		}

		// Load jQuery
		JHtml::_('jquery.framework');

		// Get document
		$document = JFactory::getDocument();

		// Add javascript variables
		$document->addScriptDeclaration('
		/* K2 v3.0.0 (beta) - START */
		var K2SessionToken = "'.JSession::getFormToken().'";
		/* K2 v3.0.0 (beta) - FINISH */
		');

		// Display
		parent::display($tpl);

	}

}
