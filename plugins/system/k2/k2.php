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

		// Get input
		$option = $application->input->get('option');
		$view = $application->input->get('view');
		$task = $application->input->get('task');

		// Redirect settings editing from com_config to K2
		if ($application->isAdmin() && $option == 'com_config' && $view == 'component' && $application->input->get('component') == 'com_k2')
		{
			$application->redirect('index.php?option=com_k2#settings');
		}

		// Front-end
		if ($application->isSite())
		{
			// Get params
			$params = JComponentHelper::getParams('com_k2');

			// Get document
			$document = JFactory::getDocument();
			
			// Enforce system template for editing
			if($view == 'admin')
			{
				$application->input->set('template', 'system');
			}

			// jQuery and K2 JS loading
			require_once JPATH_ADMINISTRATOR.'/components/com_k2/helpers/html.php';
			K2HelperHTML::jQuery();
			$document->addScript(JURI::root(true).'/components/com_k2/js/site.k2.js?v3.0.0&amp;sitepath='.JURI::root(true).'/');

			// Google search integration
			if ($view == 'itemlist' && $task == 'search' && $params->get('googleSearch'))
			{
				$language = JFactory::getLanguage();
				$languageTag = $language->getTag();
				// Fallback to the new container ID without breaking things
				$googleSearchContainerID = trim($params->get('googleSearchContainer', 'k2GoogleSearchContainer'));
				if ($googleSearchContainerID == 'k2Container')
				{
					$googleSearchContainerID = 'k2GoogleSearchContainer';
				}
				$document->addScript('http://www.google.com/jsapi');
				$js = '
				//<![CDATA[
				google.load("search", "1", {"language" : "'.$languageTag.'"});
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
					searchControl.execute("'.$application->input->get('searchword', '', 'string').'");
				}
				google.setOnLoadCallback(OnLoad);
				//]]>
	 			';
				$document->addScriptDeclaration($js);
			}

			// Add base CSS
			if ($document->getType() == 'html' && $params->get('enable_css'))
			{
				// Import filesystem
				jimport('joomla.filesystem.file');

				// k2.css
				if (JFile::exists(JPATH_SITE.'/templates/'.$application->getTemplate().'/css/k2.css'))
				{
					$document->addStyleSheet(JURI::root(true).'/templates/'.$application->getTemplate().'/css/k2.css');
				}
				else
				{
					$document->addStyleSheet(JURI::root(true).'/components/com_k2/css/site.k2.css');
				}

				// k2.print.css
				$print = $application->input->get('print', false, 'bool');
				if ($print)
				{
					if (JFile::exists(JPATH_SITE.'/templates/'.$application->getTemplate().'/css/k2.print.css'))
					{
						$document->addStyleSheet(JURI::root(true).'/templates/'.$application->getTemplate().'/css/k2.print.css', 'text/css', 'print');
					}
					else
					{
						$document->addStyleSheet(JURI::root(true).'/components/com_k2/css/k2.print.css', 'text/css', 'print');
					}
				}
			}
		}
	}

	// Extend user forms with K2 fields
	public function onAfterDispatch()
	{
		// Get application
		$application = JFactory::getApplication();

		// Get params
		$params = JComponentHelper::getParams('com_k2');

		// Process only in front-end and only if K2 user profiles are enabled
		if ($application->isSite() && $params->get('K2UserProfile') == 'legacy')
		{

			// Get user
			$user = JFactory::getUser();

			// Get layout from menu
			$active = JFactory::getApplication()->getMenu()->getActive();
			$default = isset($active->query['layout']) ? $active->query['layout'] : '';

			// Get input
			$option = $application->input->get('option', '', 'cmd');
			$view = $application->input->get('view', '', 'cmd');
			$task = $application->input->get('task', '', 'cmd');
			$layout = $application->input->get('layout', $default, 'cmd');

			// Registration page override
			if ($option == 'com_users' && $view == 'registration')
			{

				// Add reCapctha if it is enabled
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
					});';
					$document->addScriptDeclaration($js);
				}

				// Ensure that user is not logged in
				if (!$user->guest)
				{
					$application->enqueueMessage(JText::_('K2_YOU_ARE_ALREADY_REGISTERED_AS_A_MEMBER'), 'notice');
					$application->redirect(JURI::root());
					$application->close();
				}

				// Get controller
				$controller = JControllerLegacy::getInstance('Users');

				// Get view
				$view = $controller->getView('registration', 'html');

				// Add K2 layout paths to the core users view
				$view->addTemplatePath(JPATH_SITE.'/components/com_k2/templates');
				$view->addTemplatePath(JPATH_SITE.'/templates/'.$application->getTemplate().'/html/com_k2/templates');
				$view->addTemplatePath(JPATH_SITE.'/templates/'.$application->getTemplate().'/html/com_k2');

				// Set the layout
				$view->setLayout('register');

				// Get K2 user profile
				$model = K2Model::getInstance('Users');
				$K2User = $model->getRow();

				// Editor for user text field
				$wysiwyg = JFactory::getEditor();
				$editor = $wysiwyg->display('jform[k2Profile][description]', $K2User->description, '100%', '250px', '', '', false);

				// Gender field
				$lists = array();
				$genderOptions[] = JHTML::_('select.option', 'm', JText::_('K2_MALE'));
				$genderOptions[] = JHTML::_('select.option', 'f', JText::_('K2_FEMALE'));
				$lists['gender'] = JHTML::_('select.radiolist', $genderOptions, 'jform[k2Profile][gender]', '', 'value', 'text');

				// Assign variables to view
				$view->assignRef('editor', $editor);
				$view->assignRef('lists', $lists);
				$view->assignRef('K2Params', $params);
				$view->assignRef('user', $user);

				// Trigger K2 plugins
				JPluginHelper::importPlugin('k2');
				$dispatcher = JDispatcher::getInstance();
				$K2Plugins = $dispatcher->trigger('onRenderAdminForm', array(&$K2User, 'user'));
				$view->assignRef('K2Plugins', $K2Plugins);
				$view->assignRef('K2User', $K2User);

				// More variables for the view
				$nameFieldName = 'jform[name]';
				$view->assignRef('nameFieldName', $nameFieldName);
				$usernameFieldName = 'jform[username]';
				$view->assignRef('usernameFieldName', $usernameFieldName);
				$emailFieldName = 'jform[email1]';
				$view->assignRef('emailFieldName', $emailFieldName);
				$passwordFieldName = 'jform[password1]';
				$view->assignRef('passwordFieldName', $passwordFieldName);
				$passwordVerifyFieldName = 'jform[password2]';
				$view->assignRef('passwordVerifyFieldName', $passwordVerifyFieldName);
				$optionValue = 'com_users';
				$view->assignRef('optionValue', $optionValue);
				$taskValue = 'registration.register';
				$view->assignRef('taskValue', $taskValue);

				// Get buffer
				ob_start();
				$view->display();
				$contents = ob_get_clean();

				// Override the component output
				$document = JFactory::getDocument();
				$document->setBuffer($contents, 'component');

			}
			// Profile page override
			else if ($option == 'com_users' && $view == 'profile' && ($layout == 'edit' || $task == 'profile.edit'))
			{

				// Process only if user is not guest
				if (!$user->guest)
				{
					// Get controller
					$controller = JControllerLegacy::getInstance('Users');

					// Get view
					$view = $controller->getView('profile', 'html');

					// Add K2 layout paths to the core users view
					$view->addTemplatePath(JPATH_SITE.'/components/com_k2/templates');
					$view->addTemplatePath(JPATH_SITE.'/templates/'.$application->getTemplate().'/html/com_k2/templates');
					$view->addTemplatePath(JPATH_SITE.'/templates/'.$application->getTemplate().'/html/com_k2');

					// Set the layout
					$view->setLayout('profile');

					// Get K2 user profile
					require_once JPATH_ADMINISTRATOR.'/components/com_k2/resources/users.php';
					$K2User = K2Users::getInstance($user->id);

					// Editor for user text field
					$wysiwyg = JFactory::getEditor();
					$editor = $wysiwyg->display('jform[k2Profile][description]', $K2User->description, '100%', '250px', '', '', false);

					// Gender field
					$lists = array();
					$genderOptions[] = JHTML::_('select.option', 'm', JText::_('K2_MALE'));
					$genderOptions[] = JHTML::_('select.option', 'f', JText::_('K2_FEMALE'));
					$lists['gender'] = JHTML::_('select.radiolist', $genderOptions, 'jform[k2Profile][gender]', '', 'value', 'text', $K2User->gender);

					// Assign variables to view
					$view->assignRef('editor', $editor);
					$view->assignRef('lists', $lists);
					$view->assignRef('K2Params', $params);
					$view->assignRef('user', $user);

					// Trigger K2 plugins
					JPluginHelper::importPlugin('k2');
					$dispatcher = JDispatcher::getInstance();
					$K2Plugins = $dispatcher->trigger('onRenderAdminForm', array(&$K2User, 'user'));
					$view->assignRef('K2Plugins', $K2Plugins);
					$view->assignRef('K2User', $K2User);

					// More variables for the view
					$nameFieldName = 'jform[name]';
					$view->assignRef('nameFieldName', $nameFieldName);
					$emailFieldName = 'jform[email1]';
					$view->assignRef('emailFieldName', $emailFieldName);
					$passwordFieldName = 'jform[password1]';
					$view->assignRef('passwordFieldName', $passwordFieldName);
					$passwordVerifyFieldName = 'jform[password2]';
					$view->assignRef('passwordVerifyFieldName', $passwordVerifyFieldName);
					$usernameFieldName = 'jform[username]';
					$view->assignRef('usernameFieldName', $usernameFieldName);
					$idFieldName = 'jform[id]';
					$view->assignRef('idFieldName', $idFieldName);
					$optionValue = 'com_users';
					$view->assignRef('optionValue', $optionValue);
					$taskValue = 'profile.save';
					$view->assignRef('taskValue', $taskValue);

					// Get buffer
					ob_start();
					$view->display();
					$contents = ob_get_clean();

					// Override the component output
					$document = JFactory::getDocument();
					$document->setBuffer($contents, 'component');
				}

			}

		}

	}

	public function onAfterInitialise()
	{
		// Get user
		$user = JFactory::getUser();

		// Load the K2 classes
		require_once JPATH_ADMINISTRATOR.'/components/com_k2/tables/table.php';
		require_once JPATH_ADMINISTRATOR.'/components/com_k2/models/model.php';
		require_once JPATH_ADMINISTRATOR.'/components/com_k2/resources/attachments.php';
		require_once JPATH_ADMINISTRATOR.'/components/com_k2/resources/categories.php';
		require_once JPATH_ADMINISTRATOR.'/components/com_k2/resources/comments.php';
		require_once JPATH_ADMINISTRATOR.'/components/com_k2/resources/extrafields.php';
		require_once JPATH_ADMINISTRATOR.'/components/com_k2/resources/extrafieldsgroups.php';
		require_once JPATH_ADMINISTRATOR.'/components/com_k2/resources/items.php';
		require_once JPATH_ADMINISTRATOR.'/components/com_k2/resources/tags.php';
		require_once JPATH_ADMINISTRATOR.'/components/com_k2/resources/users.php';
		K2Model::addIncludePath(JPATH_ADMINISTRATOR.'/components/com_k2/models');

		// Load K2 language
		$language = JFactory::getLanguage();
		$language->load('com_k2');

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
		$searches = array('<meta name="og:url"', '<meta name="og:title"', '<meta name="og:type"', '<meta name="og:image"', '<meta name="og:description"');
		$replacements = array('<meta property="og:url"', '<meta property="og:title"', '<meta property="og:type"', '<meta property="og:image"', '<meta property="og:description"');
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
