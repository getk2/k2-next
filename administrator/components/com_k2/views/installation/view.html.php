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

class K2ViewInstallation extends JViewLegacy
{
	public function display($tpl = null)
	{
		// Get document
		$document = JFactory::getDocument();

		// Load the CSS
		$document->addStyleSheet(JURI::root(true).'/media/k2app/assets/css/installation.css');

		// Upgrade flag
		$this->upgrade = JFactory::getSession()->get('k2.upgrade');

		// Display
		parent::display($tpl);
	}

}
