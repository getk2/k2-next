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

require_once JPATH_ADMINISTRATOR.'/components/com_k2/helpers/html.php';

class JFormFieldK2Settings extends JFormField
{

	var $type = 'K2Settings';

	function getInput()
	{
		// Add head data
		K2HelperHTML::jQuery();		
		$document = JFactory::getDocument();
		$document->addStyleSheet(JURI::root(true).'/administrator/components/com_k2/css/fields.k2.css');
		$document->addScript(JURI::root(true).'/administrator/components/com_k2/js/fields.k2.js');

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
