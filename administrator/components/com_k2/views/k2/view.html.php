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

class K2ViewK2 extends JViewLegacy
{
	public function display($tpl = null)
	{
		// Get application
		$application = JFactory::getApplication();

		// Get document
		$document = JFactory::getDocument();

		// Get user
		$user = JFactory::getUser();

		// Extra class for Front-end
		$this->class = ' jw-backend';

		// Front-end permissions check.
		// We need to do this here since front-end requests are not executed through /administrator/components/com_k2/k2.php
		if ($application->isSite())
		{
			$this->class = ' jw-frontend';
			if (!$user->authorise('core.manage', 'com_k2'))
			{
				if ($user->guest)
				{
					// If user is guest redirect him to login page
					require_once JPATH_SITE.'/components/com_users/helpers/route.php';
					$uri = JUri::getInstance();
					$url = 'index.php?option=com_users&view=login&return='.base64_encode($uri->toString()).'&Itemid='.UsersHelperRoute::getLoginRoute();
					$application->redirect(JRoute::_($url, false), JText::_('K2_YOU_NEED_TO_LOGIN_FIRST'));
					return false;
				}
				else
				{
					throw new Exception(JText::_('JLIB_APPLICATION_ERROR_ACCESS_FORBIDDEN'), 403);
				}
			}
		}

		// Set the correct metadata
		$document->setMetaData('viewport', 'width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no');

		// Load jQuery
		JHtml::_('jquery.framework');

		// Keep alive the session
		JHtml::_('behavior.keepalive');

		// Load the CSS
		if ($application->isSite())
		{
			$document->addScript(JURI::root(true).'/media/k2app/assets/js/frontend.js?v=3.0.0');
			$document->addStyleSheet(JURI::root(true).'/media/k2app/assets/css/frontend.css?v=3.0.0');
		}
		$document->addStyleSheet('//maxcdn.bootstrapcdn.com/font-awesome/4.1.0/css/font-awesome.min.css?v=3.0.0');
		$document->addStyleSheet(JURI::root(true).'/media/k2app/assets/css/k2.css?v=3.0.0');

		// Add javascript variables
		$document->addScriptDeclaration('
			/* K2 v3.0.0 - START */
			var K2SessionToken = "'.JSession::getFormToken().'";
			var K2Editor = '.$this->getEditor().';
			var K2SitePath = "'.JURI::root(true).'";
			var K2BasePath = "'.JURI::base(true).'";
		 	var K2DateTimePickerLanguage = "'.$this->getDateTimePickerLanguage().'";
			var K2MediaManagerLanguage = "'.$this->getMediaManagerLanguage().'";
			var K2Language = '.$this->getLanguage().';
			/* K2 v3.0.0 - FINISH */
		');
		// Add DropBox drop-in
		$params = JComponentHelper::getParams('com_k2');
		if ($dropBoxAppKey = $params->get('dropboxAppKey'))
		{
			// Load DropBox script
			$document->addCustomTag('<script data-app-key="'.$dropBoxAppKey.'" id="dropboxjs" src="https://www.dropbox.com/static/api/2/dropins.js"></script>');
		}

		// Load the application
		$document->addCustomTag('<script data-main="'.JURI::root(true).'/media/k2app/app/main" src="'.JURI::root(true).'/media/k2app/vendor/require/require.js?v=3.0.0"></script>');

		// Set title
		if (class_exists('JToolBarHelper'))
		{
			JToolBarHelper::title(JText::_('COM_K2'));
		}

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
		$getContent = 'function (name) { return '.$K2Editor->getContent('REPLACE_NAME').'}';
		$save = 'function (name) {'.$K2Editor->save('REPLACE_NAME').'}';
		$js = '{init : '.$init.', setContent : '.$setContent.', getContent : '.$getContent.', save : '.$save.'}';
		$js = JString::str_ireplace("'REPLACE_NAME'", 'name', $js);
		$js = JString::str_ireplace('"REPLACE_NAME"', 'name', $js);
		$js = JString::str_ireplace('REPLACE_NAME', 'name', $js);
		$js = JString::str_ireplace("'REPLACE_CONTENT'", 'content', $js);
		$js = JString::str_ireplace('"REPLACE_CONTENT"', 'content', $js);
		$js = JString::str_ireplace('REPLACE_CONTENT', 'content', $js);
		return $js;
	}

	protected function getLanguage()
	{
		$language = JFactory::getLanguage();
		$file = JPATH_ADMINISTRATOR.'/language/'.$language->getTag().'/'.$language->getTag().'.com_k2.ini';
		if (!file_exists($file))
		{
			$file = JPATH_ADMINISTRATOR.'/language/en-GB/en-GB.com_k2.ini';
		}
		$contents = file_get_contents($file);
		$contents = str_replace('_QQ_', '"\""', $contents);
		$strings = @parse_ini_string($contents);
		return json_encode($strings);
	}

	protected function getMediaManagerLanguage()
	{
		$language = JFactory::getLanguage();
		$tag = $language->getTag();
		$needles = array();
		$needles[] = str_replace('-', '_', $tag);
		$needles[] = substr($tag, 0, strpos($tag, '-'));
		$tag = 'en';
		foreach ($needles as $needle)
		{
			if (file_exists(JPATH_SITE.'/media/k2app/vendor/elfinder/js/i18n/elfinder.'.$needle.'.js'))
			{
				$tag = $needle;
				break;
			}
		}
		return $tag;
	}

	protected function getDateTimePickerLanguage()
	{
		// set language tag for http://momentjs.com/ supported date/time pickers
		$validLanguages = array('af','ar-ma','ar-sa','ar-tn','ar','az','be','bg','bn',
				'bo', 'br', 'bs', 'ca', 'cs', 'cv', 'cy', 'da', 'de-at', 'de', 'dv',
				'el', 'en-au', 'en-ca', 'en-gb', 'en-ie', 'en-nz', 'eo', 'es', 'et',
				'eu', 'fa', 'fl', 'fo', 'fr-ca', 'fr-ch', 'fr', 'fy', 'gd', 'gl', 'he',
				'hl', 'hi', 'hr', 'hu', 'hy-am', 'id', 'is', 'it', 'ja', 'jv', 'ka',
				'kk', 'km', 'ko', 'lb', 'lo','lt', 'lv', 'me', 'mk', 'ml', 'mr',
				'ms-my', 'ms', 'my', 'nb', 'ne', 'nl', 'nn', 'pa-in', 'pl', 'pt-br',
				'pt', 'ro', 'ru', 'se', 'si', 'sk', 'sl', 'sq', 'sr-cyrl', 'sr',
				'sv', 'sw', 'ta', 'th', 'tl-ph', 'tlh', 'tr', 'tzl', 'tzm', 'uk',
				'uz', 'vj', 'zh-cn', 'zh-tw'
		);
		$language = JFactory::getLanguage();
		$tag = strtolower($language->getTag());
		$tag = str_replace('_', '-', $tag);
		if(array_search($tag, $validLanguages)) {
			return $tag;
		}
		$tag = substr($tag, 0, strpos($tag, '-'));
		if(array_search($tag, $validLanguages)) {
			return $tag;
		}
		return 'en';
	}

}
