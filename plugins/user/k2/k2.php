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
 * K2 User plugin
 */

class PlgUserK2 extends JPlugin
{

	public function onUserAfterSave($user, $isnew, $success, $msg)
	{
		// Get application
		$application = JFactory::getApplication();

		// Get params
		$params = JComponentHelper::getParams('com_k2');

		// Get input
		$task = $application->input->get('task', '', 'cmd');
		$isK2UserForm = $application->input->get('K2UserForm', 0, 'int');

		// Process only in front-end
		if ($application->isSite())
		{
			// Check spammer for activation and registrations. Only in front-end
			if (($task == 'activate' || $isnew) && $params->get('stopForumSpam'))
			{
				$this->checkSpammer($user);
			}

			// Save K2 user profile for new users
			if ($task != 'activate' && $isK2UserForm)
			{
				// Load K2 language file
				$this->loadLanguage('com_k2');

				// Get model
				$model = K2Model::getInstance('Users');

				// Get input data
				$data = $application->input->getArray();

				// Pass data to the model
				$this->model->setState('data', $data);

				// Save
				$result = $this->model->save();

				// Redirect
				$itemid = $params->get('redirect');

				if (!$isnew && $itemid)
				{
					$menu = $application->getMenu();
					$item = $menu->getItem($itemid);
					$url = JRoute::_($item->link.'&Itemid='.$itemid, false);
					if (JURI::isInternal($url))
					{
						$application->enqueueMessage(JText::_('K2_YOUR_SETTINGS_HAVE_BEEN_SAVED'));
						$application->redirect($url);
					}
				}
			}
		}

	}

	public function onUserLogin($user, $options)
	{
		// Get application
		$application = JFactory::getApplication();

		// Get params
		$params = JComponentHelper::getParams('com_k2');

		// Process only in front-end
		if ($application->isSite())
		{
			// Get the user id
			$id = JUserHelper::getUserId($user['username']);

			// If K2 profiles are enabled update profile with last used ip and hostname.
			if ($params->get('K2UserProfile') && $id)
			{
				// Get database
				$db = JFactory::getDbo();

				// Get query
				$query = $db->getQuery(true);

				// Update
				$query->update($db->quoteName('#__k2_users'));
				$query->set($db->quoteName('ip').' = '.$db->quote($_SERVER['REMOTE_ADDR']));
				$query->set($db->quoteName('hostname').' = '.$db->quote(gethostbyaddr($_SERVER['REMOTE_ADDR'])));
				$query->where($db->quoteName('id').' = '.(int)$id);
				$db->setQuery($query);
				$db->execute();

			}

			// Set the Cookie domain for user based on K2 parameters
			if ($params->get('cookieDomain') && $id)
			{
				setcookie('userID', $id, 0, '/', $params->get('cookieDomain'), 0);
			}
		}

		// Return
		return true;
	}

	public function onUserLogout($user)
	{
		// Get application
		$application = JFactory::getApplication();

		// Get params
		$params = JComponentHelper::getParams('com_k2');

		// Expire the Cookie domain for user based on K2 parameters. Only in front-end
		if ($application->isSite() && $params->get('cookieDomain'))
		{
			setcookie('userID', '', time() - 3600, '/', $params->get('cookieDomain'), 0);
		}

		// Return
		return true;
	}

	public function onUserAfterDelete($user, $success, $msg)
	{
		// Get database
		$db = JFactory::getDbo();

		// Get query
		$query = $db->getQuery(true);

		// Delete
		$query->delete($db->quoteName('#__k2_users'));
		$query->where($db->quoteName('id').' = '.(int)$user['id']);
		$db->setQuery($query);
		$db->execute();
	}

	public function onUserBeforeSave($user, $isNew)
	{
		// Get application
		$application = JFactory::getApplication();

		// Get params
		$params = JComponentHelper::getParams('com_k2');

		// Get input
		$isK2UserForm = $application->input->get('K2UserForm', 0, 'int');

		// Process only in front-end. Check all conditions
		if ($params->get('K2UserProfile') && $isNew && $params->get('recaptchaOnRegistration') && $application->isSite() && $isK2UserForm)
		{
			// @TODO Implement captcha based on the new settings....
			if (!function_exists('_recaptcha_qsencode'))
			{
				require_once (JPATH_ADMINISTRATOR.DS.'components'.DS.'com_k2'.DS.'lib'.DS.'recaptchalib.php');
			}
			$privatekey = $params->get('recaptcha_private_key');
			$recaptcha_challenge_field = isset($_POST["recaptcha_challenge_field"]) ? $_POST["recaptcha_challenge_field"] : '';
			$recaptcha_response_field = isset($_POST["recaptcha_response_field"]) ? $_POST["recaptcha_response_field"] : '';
			$resp = recaptcha_check_answer($privatekey, $_SERVER["REMOTE_ADDR"], $recaptcha_challenge_field, $recaptcha_response_field);
			if (!$resp->is_valid)
			{
				$url = 'index.php?option=com_users&view=registration';
				$application->enqueueMessage(JText::_('K2_THE_WORDS_YOU_TYPED_DID_NOT_MATCH_THE_ONES_DISPLAYED_PLEASE_TRY_AGAIN'), 'error');
				$application->redirect($url);
			}
		}
	}

	private function checkSpammer(&$user)
	{
		// Process only if user is not already blocked
		if (!$user['block'])
		{
			// Get data
			$ip = $_SERVER['REMOTE_ADDR'];
			$email = urlencode($user['email']);
			$username = urlencode($user['username']);

			// Use cURL to check user with stopforumspam.com
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, 'http://www.stopforumspam.com/api?ip='.$ip.'&email='.$email.'&username='.$username.'&f=json');
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_TIMEOUT, 5);
			$response = curl_exec($ch);
			$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			curl_close($ch);

			// Go further only if we have response from the service and it is 200
			if ($httpCode == 200)
			{
				// Convert response to object
				$response = json_decode($response);

				// Check if user is in spam lists
				if ($response->ip->appears || $response->email->appears || $response->username->appears)
				{
					// User is in spam lists so block him
					$db = JFactory::getDbo();
					$query = $db->getQuery(true);
					$query->update($db->quoteName('#__users'))->set($db->quoteName('block').' = 1')->where($db->quoteName('id').' = '.(int)$user['id']);
					$db->setQuery($query);
					$db->execute();

					// Add the relative note
					$user['notes'] = JText::_('K2_POSSIBLE_SPAMMER_DETECTED_BY_STOPFORUMSPAM');
				}
			}
		}
	}

}
