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

/**
 * K2 System plugin
 */

class PlgSystemK2 extends JPlugin
{

	public function onAfterRoute()
	{
		// Get application
		$application = JFactory::getApplication();

		// Base path
		$basepath = ($application->isSite()) ? JPATH_SITE : JPATH_ADMINISTRATOR;

		$this->loadLanguage('com_k2', $basepath);

		if ($application->isAdmin())
		{
			return;
		}
		if ((JRequest::getCmd('task') == 'add' || JRequest::getCmd('task') == 'edit') && JRequest::getCmd('option') == 'com_k2')
		{
			return;
		}
		return;

		$params = JComponentHelper::getParams('com_k2');

		$document = JFactory::getDocument();

		// jQuery and K2 JS loading
		K2HelperHTML::loadjQuery();

		$document->addScript(JURI::root(true).'/components/com_k2/js/k2.js?v2.6.8&amp;sitepath='.JURI::root(true).'/');
		//$document->addScriptDeclaration("var K2SitePath = '".JURI::root(true)."/';");

		if (JRequest::getCmd('task') == 'search' && $params->get('googleSearch'))
		{
			$language = JFactory::getLanguage();
			$lang = $language->getTag();
			// Fallback to the new container ID without breaking things
			$googleSearchContainerID = trim($params->get('googleSearchContainer', 'k2GoogleSearchContainer'));
			if ($googleSearchContainerID == 'k2Container')
			{
				$googleSearchContainerID = 'k2GoogleSearchContainer';
			}
			$document->addScript('http://www.google.com/jsapi');
			$js = '
			//<![CDATA[
			google.load("search", "1", {"language" : "'.$lang.'"});

			function OnLoad(){
				var searchControl = new google.search.SearchControl();
				var siteSearch = new google.search.WebSearch();
				siteSearch.setUserDefinedLabel("'.$application->getCfg('sitename').'");
				siteSearch.setUserDefinedClassSuffix("k2");
				options = new google.search.SearcherOptions();
				options.setExpandMode(google.search.SearchControl.EXPAND_MODE_OPEN);
				siteSearch.setSiteRestriction("'.JURI::root().'");
				searchControl.addSearcher(siteSearch, options);
				searchControl.setResultSetSize(google.search.Search.LARGE_RESULTSET);
				searchControl.setLinkTarget(google.search.Search.LINK_TARGET_SELF);
				searchControl.draw(document.getElementById("'.$googleSearchContainerID.'"));
				searchControl.execute("'.JRequest::getString('searchword').'");
			}

			google.setOnLoadCallback(OnLoad);
			//]]>
 			';
			$document->addScriptDeclaration($js);
		}

		// Add related CSS to the <head>
		if ($document->getType() == 'html' && $params->get('enable_css'))
		{

			jimport('joomla.filesystem.file');

			// k2.css
			if (JFile::exists(JPATH_SITE.DS.'templates'.DS.$application->getTemplate().DS.'css'.DS.'k2.css'))
				$document->addStyleSheet(JURI::root(true).'/templates/'.$application->getTemplate().'/css/k2.css');
			else
				$document->addStyleSheet(JURI::root(true).'/components/com_k2/css/k2.css');

			// k2.print.css
			if (JRequest::getInt('print') == 1)
			{
				if (JFile::exists(JPATH_SITE.DS.'templates'.DS.$application->getTemplate().DS.'css'.DS.'k2.print.css'))
					$document->addStyleSheet(JURI::root(true).'/templates/'.$application->getTemplate().'/css/k2.print.css', 'text/css', 'print');
				else
					$document->addStyleSheet(JURI::root(true).'/components/com_k2/css/k2.print.css', 'text/css', 'print');
			}

		}

	}

	// Extend user forms with K2 fields
	public function onAfterDispatch()
	{
		
		return;

		$application = JFactory::getApplication();

		if ($application->isAdmin())
			return;

		$params = JComponentHelper::getParams('com_k2');
		if (!$params->get('K2UserProfile'))
			return;
		$option = JRequest::getCmd('option');
		$view = JRequest::getCmd('view');
		$task = JRequest::getCmd('task');
		$layout = JRequest::getCmd('layout');
		$user = JFactory::getUser();

		if (K2_JVERSION != '15')
		{
			$active = JFactory::getApplication()->getMenu()->getActive();
			if (isset($active->query['layout']))
			{
				$layout = $active->query['layout'];
			}
		}

		if (($option == 'com_user' && $view == 'register') || ($option == 'com_users' && $view == 'registration'))
		{

			if ($params->get('recaptchaOnRegistration') && $params->get('recaptcha_public_key'))
			{
				$document = JFactory::getDocument();
				$document->addScript('https://www.google.com/recaptcha/api/js/recaptcha_ajax.js');
				$js = '
				function showRecaptcha(){
					Recaptcha.create("'.$params->get('recaptcha_public_key').'", "recaptcha", {
						theme: "'.$params->get('recaptcha_theme', 'clean').'"
					});
				}
				$K2(document).ready(function() {
					showRecaptcha();
				});
				';
				$document->addScriptDeclaration($js);
			}

			if (!$user->guest)
			{
				$application->enqueueMessage(JText::_('K2_YOU_ARE_ALREADY_REGISTERED_AS_A_MEMBER'), 'notice');
				$application->redirect(JURI::root());
				$application->close();
			}
			if (K2_JVERSION != '15')
			{
				require_once (JPATH_SITE.DS.'components'.DS.'com_users'.DS.'controller.php');
				$controller = new UsersController;

			}
			else
			{
				require_once (JPATH_SITE.DS.'components'.DS.'com_user'.DS.'controller.php');
				$controller = new UserController;
			}
			$view = $controller->getView($view, 'html');
			$view->addTemplatePath(JPATH_SITE.DS.'components'.DS.'com_k2'.DS.'templates');
			$view->addTemplatePath(JPATH_SITE.DS.'templates'.DS.$application->getTemplate().DS.'html'.DS.'com_k2'.DS.'templates');
			$view->addTemplatePath(JPATH_SITE.DS.'templates'.DS.$application->getTemplate().DS.'html'.DS.'com_k2');
			$view->setLayout('register');

			$K2User = new JObject;

			$K2User->description = '';
			$K2User->gender = 'm';
			$K2User->image = '';
			$K2User->url = '';
			$K2User->plugins = '';

			$wysiwyg = JFactory::getEditor();
			$editor = $wysiwyg->display('description', $K2User->description, '100%', '250px', '', '', false);
			$view->assignRef('editor', $editor);

			$lists = array();
			$genderOptions[] = JHTML::_('select.option', 'm', JText::_('K2_MALE'));
			$genderOptions[] = JHTML::_('select.option', 'f', JText::_('K2_FEMALE'));
			$lists['gender'] = JHTML::_('select.radiolist', $genderOptions, 'gender', '', 'value', 'text', $K2User->gender);

			$view->assignRef('lists', $lists);
			$view->assignRef('K2Params', $params);

			JPluginHelper::importPlugin('k2');
			$dispatcher = JDispatcher::getInstance();
			$K2Plugins = $dispatcher->trigger('onRenderAdminForm', array(
				&$K2User,
				'user'
			));
			$view->assignRef('K2Plugins', $K2Plugins);

			$view->assignRef('K2User', $K2User);
			if (K2_JVERSION != '15')
			{
				$view->assignRef('user', $user);
			}
			$pathway = $application->getPathway();
			$pathway->setPathway(NULL);

			$nameFieldName = K2_JVERSION != '15' ? 'jform[name]' : 'name';
			$view->assignRef('nameFieldName', $nameFieldName);
			$usernameFieldName = K2_JVERSION != '15' ? 'jform[username]' : 'username';
			$view->assignRef('usernameFieldName', $usernameFieldName);
			$emailFieldName = K2_JVERSION != '15' ? 'jform[email1]' : 'email';
			$view->assignRef('emailFieldName', $emailFieldName);
			$passwordFieldName = K2_JVERSION != '15' ? 'jform[password1]' : 'password';
			$view->assignRef('passwordFieldName', $passwordFieldName);
			$passwordVerifyFieldName = K2_JVERSION != '15' ? 'jform[password2]' : 'password2';
			$view->assignRef('passwordVerifyFieldName', $passwordVerifyFieldName);
			$optionValue = K2_JVERSION != '15' ? 'com_users' : 'com_user';
			$view->assignRef('optionValue', $optionValue);
			$taskValue = K2_JVERSION != '15' ? 'registration.register' : 'register_save';
			$view->assignRef('taskValue', $taskValue);
			ob_start();
			$view->display();
			$contents = ob_get_clean();
			$document = JFactory::getDocument();
			$document->setBuffer($contents, 'component');

		}

		if (($option == 'com_user' && $view == 'user' && ($task == 'edit' || $layout == 'form')) || ($option == 'com_users' && $view == 'profile' && ($layout == 'edit' || $task == 'profile.edit')))
		{

			if ($user->guest)
			{
				$uri = JFactory::getURI();

				if (K2_JVERSION != '15')
				{
					$url = 'index.php?option=com_users&view=login&return='.base64_encode($uri->toString());

				}
				else
				{
					$url = 'index.php?option=com_user&view=login&return='.base64_encode($uri->toString());
				}
				$application->enqueueMessage(JText::_('K2_YOU_NEED_TO_LOGIN_FIRST'), 'notice');
				$application->redirect(JRoute::_($url, false));
			}

			if (K2_JVERSION != '15')
			{
				require_once (JPATH_SITE.DS.'components'.DS.'com_users'.DS.'controller.php');
				$controller = new UsersController;
			}
			else
			{
				require_once (JPATH_SITE.DS.'components'.DS.'com_user'.DS.'controller.php');
				$controller = new UserController;
			}

			/*
			 // TO DO - We open the profile editing page in a modal, so let's define some CSS
			 $document = JFactory::getDocument();
			 $document->addStyleSheet(JURI::root(true).'/media/k2/assets/css/k2.frontend.css?v=2.6.8');
			 $document->addStyleSheet(JURI::root(true).'/templates/system/css/general.css');
			 $document->addStyleSheet(JURI::root(true).'/templates/system/css/system.css');
			 if(K2_JVERSION != '15') {
			 $document->addStyleSheet(JURI::root(true).'/administrator/templates/bluestork/css/template.css');
			 $document->addStyleSheet(JURI::root(true).'/media/system/css/system.css');
			 } else {
			 $document->addStyleSheet(JURI::root(true).'/administrator/templates/khepri/css/general.css');
			 }
			 */

			$view = $controller->getView($view, 'html');
			$view->addTemplatePath(JPATH_SITE.DS.'components'.DS.'com_k2'.DS.'templates');
			$view->addTemplatePath(JPATH_SITE.DS.'templates'.DS.$application->getTemplate().DS.'html'.DS.'com_k2'.DS.'templates');
			$view->addTemplatePath(JPATH_SITE.DS.'templates'.DS.$application->getTemplate().DS.'html'.DS.'com_k2');
			$view->setLayout('profile');

			$model = K2Model::getInstance('Itemlist', 'K2Model');
			$K2User = $model->getUserProfile($user->id);
			if (!is_object($K2User))
			{
				$K2User = new Jobject;
				$K2User->description = '';
				$K2User->gender = 'm';
				$K2User->url = '';
				$K2User->image = NULL;
			}
			if (K2_JVERSION == '15')
			{
				JFilterOutput::objectHTMLSafe($K2User);
			}
			else
			{
				JFilterOutput::objectHTMLSafe($K2User, ENT_QUOTES, array(
					'params',
					'plugins'
				));
			}
			$wysiwyg = JFactory::getEditor();
			$editor = $wysiwyg->display('description', $K2User->description, '100%', '250px', '', '', false);
			$view->assignRef('editor', $editor);

			$lists = array();
			$genderOptions[] = JHTML::_('select.option', 'm', JText::_('K2_MALE'));
			$genderOptions[] = JHTML::_('select.option', 'f', JText::_('K2_FEMALE'));
			$lists['gender'] = JHTML::_('select.radiolist', $genderOptions, 'gender', '', 'value', 'text', $K2User->gender);

			$view->assignRef('lists', $lists);

			JPluginHelper::importPlugin('k2');
			$dispatcher = JDispatcher::getInstance();
			$K2Plugins = $dispatcher->trigger('onRenderAdminForm', array(
				&$K2User,
				'user'
			));
			$view->assignRef('K2Plugins', $K2Plugins);

			$view->assignRef('K2User', $K2User);

			// Asssign some variables depending on Joomla! version
			$nameFieldName = K2_JVERSION != '15' ? 'jform[name]' : 'name';
			$view->assignRef('nameFieldName', $nameFieldName);
			$emailFieldName = K2_JVERSION != '15' ? 'jform[email1]' : 'email';
			$view->assignRef('emailFieldName', $emailFieldName);
			$passwordFieldName = K2_JVERSION != '15' ? 'jform[password1]' : 'password';
			$view->assignRef('passwordFieldName', $passwordFieldName);
			$passwordVerifyFieldName = K2_JVERSION != '15' ? 'jform[password2]' : 'password2';
			$view->assignRef('passwordVerifyFieldName', $passwordVerifyFieldName);
			$usernameFieldName = K2_JVERSION != '15' ? 'jform[username]' : 'username';
			$view->assignRef('usernameFieldName', $usernameFieldName);
			$idFieldName = K2_JVERSION != '15' ? 'jform[id]' : 'id';
			$view->assignRef('idFieldName', $idFieldName);
			$optionValue = K2_JVERSION != '15' ? 'com_users' : 'com_user';
			$view->assignRef('optionValue', $optionValue);
			$taskValue = K2_JVERSION != '15' ? 'profile.save' : 'save';
			$view->assignRef('taskValue', $taskValue);

			ob_start();
			if (K2_JVERSION != '15')
			{
				$active = JFactory::getApplication()->getMenu()->getActive();
				if (isset($active->query['layout']) && $active->query['layout'] != 'profile')
				{
					$active->query['layout'] = 'profile';
				}
				$view->assignRef('user', $user);
				$view->display();
			}
			else
			{
				$view->_displayForm();
			}

			$contents = ob_get_clean();
			$document = JFactory::getDocument();
			$document->setBuffer($contents, 'component');

		}

	}

	public function onAfterInitialise()
	{
		// Get user
		$user = JFactory::getUser();

		// Load the K2 classes
		require_once JPATH_ADMINISTRATOR.'/components/com_k2/tables/table.php';
		require_once JPATH_ADMINISTRATOR.'/components/com_k2/models/model.php';
		K2Model::addIncludePath(JPATH_ADMINISTRATOR.'/components/com_k2/models');

		// Use K2 to make Joomla! Varnish-friendly. For more checkout: https://snipt.net/fevangelou/the-perfect-varnish-configuration-for-joomla-websites/
		if (!$user->guest)
		{
			JResponse::setHeader('X-Logged-In', 'True', true);
		}
		else
		{
			JResponse::setHeader('X-Logged-In', 'False', true);
		}

	}

	public function onAfterRender()
	{
		$response = JResponse::getBody();
		$searches = array(
			'<meta name="og:url"',
			'<meta name="og:title"',
			'<meta name="og:type"',
			'<meta name="og:image"',
			'<meta name="og:description"'
		);
		$replacements = array(
			'<meta property="og:url"',
			'<meta property="og:title"',
			'<meta property="og:type"',
			'<meta property="og:image"',
			'<meta property="og:description"'
		);
		if (strpos($response, 'prefix="og: http://ogp.me/ns#"') === false)
		{
			$searches[] = '<html ';
			$searches[] = '<html>';
			$replacements[] = '<html prefix="og: http://ogp.me/ns#" ';
			$replacements[] = '<html prefix="og: http://ogp.me/ns#">';
		}
		$response = str_ireplace($searches, $replacements, $response);
		JResponse::setBody($response);
	}

}
