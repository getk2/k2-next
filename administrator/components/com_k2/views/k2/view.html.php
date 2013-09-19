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

class K2ViewK2 extends JViewLegacy
{
	public function display($tpl = null)
	{
		// Get document
		$document = JFactory::getDocument();

		// Set the correct metadata
		$document->setMetaData('viewport', 'width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no');

		// Load jQuery for Joomla! 3.x series
		if (version_compare(JVERSION, '3.0', 'ge'))
		{
			JHtml::_('jquery.framework');
		}
		
		// Add the session token variable
		$token = JSession::getFormToken();
		$document->addScriptDeclaration('var K2SessionToken = "'.$token.'";');

		// Load the application
		$document->addCustomTag('<script data-main="'.JURI::base(true).'/components/com_k2/js/boot" src="'.JURI::base(true).'/components/com_k2/js/require.js"></script>');

		// Add version variable
		if (version_compare(JVERSION, '3.0', 'ge'))
		{
			$JVersion = '31';
		}
		else
		{
			$JVersion = '25';
		}
		$this->assignRef('JVersion', $JVersion);

		// Set title
		JToolBarHelper::title(JText::_('COM_K2'));

		// Display
		parent::display($tpl);

	}

	protected function getEditorFunctions()
	{
		require_once JPATH_ADMINISTRATOR.'/components/com_k2/lib/editor.php';
		$config = JFactory::getConfig();
		$K2Editor = K2Editor::getInstance($config->get('editor'));
		$init = 'function () {'.$K2Editor->init().'}';
		$setContent = 'function (name, content) {'.$K2Editor->setContent('REPLACE_NAME', 'REPLACE_CONTENT').'}';
		$getContent = 'function (name) {'.$K2Editor->getContent('REPLACE_NAME').'}';
		$save = 'function (name) {'.$K2Editor->save('REPLACE_NAME').'}';
		$js = 'init : '.$init.', setContent : '.$setContent.', getContent : '.$getContent.', save : '.$save;
		$js = JString::str_ireplace("'REPLACE_NAME'", 'name', $js);
		$js = JString::str_ireplace('"REPLACE_NAME"', 'name', $js);
		$js = JString::str_ireplace('REPLACE_NAME', 'name', $js);
		$js = JString::str_ireplace("'REPLACE_CONTENT'", 'content', $js);
		$js = JString::str_ireplace('"REPLACE_CONTENT"', 'content', $js);
		$js = JString::str_ireplace('REPLACE_CONTENT', 'content', $js);
		return $js;
	}

}