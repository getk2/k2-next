<?php
/**
 * @version		3.0.0
 * @package		K2
 * @author		JoomlaWorks http://www.joomlaworks.net
 * @copyright	Copyright (c) 2006 - 2014 JoomlaWorks Ltd. All rights reserved.
 * @license		GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 */

// no direct access
defined('_JEXEC') or die ;

class JFormFieldK2Settings extends JFormField
{

	var $type = 'K2Settings';

	function getInput()
	{
		// Add head data
		JHtml::_('jquery.framework');
		$document = JFactory::getDocument();
		$document->addStyleSheet(JURI::root(true).'/media/k2app/assets/css/k2.css?v=3.0.0');
		$document->addScript(JURI::root(true).'/media/k2app/assets/js/fields.js?v=3.0.0');

		// Detect context
		$isMenu = JFactory::getApplication()->input->get('option') == 'com_menus';

		// Load administration language file
		$language = JFactory::getLanguage();
		$language->load('com_k2', JPATH_ADMINISTRATOR);

		// Include custom layout
		ob_start();
		include dirname(__FILE__).'/tmpl/settings.php';
		$contents = ob_get_clean();

		// Remove the rest groups
		if (!$isMenu)
		{
			$this->form->removeGroup('params');
		}
		else
		{
			foreach ($this->form->getFieldset('k2basic') as $field)
			{
				$this->form->removeField($field->fieldname, 'params');
			}
		}

		// Hide the extra tab in menus
		if ($isMenu)
		{
			$document->addScriptDeclaration('jQuery(document).ready(function() {jQuery("#attrib-k2basic").remove();});');
			$document->addStyleDeclaration('#myTabTabs li:nth-child(3) { display: none !important;}');
		}

		// Return
		return $contents;
	}

	function getLabel()
	{
		return null;
	}

}
