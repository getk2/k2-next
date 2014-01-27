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

class JFormFieldK2Settings extends JFormField
{

	var $type = 'K2Settings';

	function getInput()
	{
		// Add K2 fields CSS
		$document = JFactory::getDocument();
		$document->addStyleSheet(JURI::base(true).'/administrator/components/com_k2/css/fields.k2.css');

		// Include custom layout
		ob_start();
		include dirname(__FILE__).'/tmpl/settings.php';
		$contents = ob_get_clean();

		// Remove the rest groups since we rendered them already in our layout
		$this->form->removeGroup('params');

		// Return
		return $contents;
	}

	function getLabel()
	{
		return null;
	}

}