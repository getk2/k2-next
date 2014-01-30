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
		
		// Load the CSS
		$document->addStyleSheet(JURI::root(true).'/administrator/components/com_k2/css/admin.k2.css');

		// Add javascript variables
		$document->addScriptDeclaration('var K2SessionToken = "'.JSession::getFormToken().'";');
		$document->addScriptDeclaration('var K2Editor = '.$this->getEditor().';');
		$document->addScriptDeclaration('var K2SitePath = "'.JURI::root(true).'";');
		$document->addScriptDeclaration('var K2Language = '.$this->getLanguage().';');
		
		// Add DropBox drop-in
		$params = JComponentHelper::getParams('com_k2');
		if($dropBoxAppKey = $params->get('dropboxAppKey'))
		{
			// Load DropBox script
			$document->addCustomTag('<script data-app-key="'.$dropBoxAppKey.'" id="dropboxjs" src="https://www.dropbox.com/static/api/2/dropins.js"></script>');
		}
		
		// Calculate session lifetime
		$config = JFactory::getConfig();
		$lifetime = ($config->get('lifetime') * 60000);
		$refreshTime = ($lifetime <= 60000) ? 30000 : $lifetime - 60000;
		if ($refreshTime > 3600000 || $refreshTime <= 0)
		{
			$refreshTime = 3600000;
		}
		$document->addScriptDeclaration('var K2SessionTimeout = '.$refreshTime.';');

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

	protected function getEditor()
	{
		require_once JPATH_ADMINISTRATOR.'/components/com_k2/classes/editor.php';
		$config = JFactory::getConfig();
		$K2Editor = K2Editor::getInstance($config->get('editor'));
		$init = 'function () {'.$K2Editor->init().'}';
		$setContent = 'function (name, content) {'.$K2Editor->setContent('REPLACE_NAME', 'REPLACE_CONTENT').'}';
		$getContent = 'function (name) {'.$K2Editor->getContent('REPLACE_NAME').'}';
		$save = 'function (name) {'.$K2Editor->save('REPLACE_NAME').'}';
		$js = '{init : '.$init.', setContent : '.$setContent.', getContent : '.$getContent.', save : '.$save.'}';
		$js = JString::str_ireplace("'REPLACE_NAME'", 'name', $js);
		$js = JString::str_ireplace('"REPLACE_NAME"', 'name', $js);
		$js = JString::str_ireplace('REPLACE_NAME', 'name', $js);
		$js = JString::str_ireplace("'REPLACE_CONTENT'", 'content', $js);
		$js = JString::str_ireplace('"REPLACE_CONTENT"', 'content', $js);
		$js = JString::str_ireplace('REPLACE_CONTENT', 'content', $js);
		$K2Editor->display('text', '', '100%', '300', '40', '5');
		return $js;
	}

	protected function getLanguage()
	{
		$language = JFactory::getLanguage();
		$contents = file_get_contents(JPATH_ADMINISTRATOR.'/language/'.$language->getTag().'/'.$language->getTag().'.com_k2.ini');
		$contents = str_replace('_QQ_', '"\""', $contents);
		$strings = @parse_ini_string($contents);
		return json_encode($strings);
	}

}
