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

require_once JPATH_ADMINISTRATOR.'/components/com_k2/resources/users.php';

/**
 * K2 User plugin
 */

class PlgUserK2 extends JPlugin
{

	/**
	 * @param   string     $context  The context for the data
	 * @param   integer    $data     The user id
	 *
	 * @return  boolean
	 *
	 * @since   1.6
	 */
	public function onContentPrepareData($context, $data)
	{

		// Get application
		$application = JFactory::getApplication();

		// Valid contexts
		$contexts = array(
			'com_users.profile',
			'com_users.user',
			'com_users.registration',
			'com_admin.profile'
		);

		// Condition
		if ($application->isSite() && in_array($context, $contexts) && is_object($data))
		{
			$userId = isset($data->id) ? $data->id : 0;
			if (!isset($data->k2Profile) and $userId > 0)
			{
				$k2User = K2Users::getInstance($userId);
				$data->k2Profile = array();
				$data->k2Profile['description'] = $k2User->description;
				$data->k2Profile['image'] = $k2User->image;
				$data->k2Profile['site'] = $k2User->site;
				$data->k2Profile['gender'] = $k2User->gender;
			}
			JHtml::register('users.description', 'PlgUserK2::description');
			JHtml::register('users.image', 'PlgUserK2::image');
			JHtml::register('users.gender', 'PlgUserK2::gender');
			JHtml::register('users.site', 'PlgUserK2::site');
		}
		return true;
	}

	/**
	 * @param   JForm    $form    The form to be altered.
	 * @param   array    $data    The associated data for the form.
	 *
	 * @return  boolean
	 * @since   1.6
	 */
	public function onContentPrepareForm($form, $data)
	{
		// Get application
		$application = JFactory::getApplication();

		// Get settings
		$params = JComponentHelper::getParams('com_k2');

		// Get form name
		$name = $form->getName();

		// Valid forms
		$forms = array(
			'com_admin.profile',
			'com_users.user',
			'com_users.profile',
			'com_users.registration'
		);

		// Rendering condition
		if ($application->isSite() && $params->get('K2UserProfile') == 'native' && in_array($name, $forms))
		{
			// Add K2 profile fields to the form
			JForm::addFormPath(__DIR__.'/forms');
			$form->loadFile('profile', false);
		}

		// Return
		return true;
	}

	public static function description($value)
	{
		return $value;
	}

	public static function image($value)
	{
		return '<img alt="'.htmlspecialchars($value->alt).'" src="'.$value->src.'"/>';
	}

	public static function gender($value)
	{
		if ($value == 'm')
		{
			return JText::_('K2_MALE');
		}
		else if ($value == 'f')
		{
			return JText::_('K2_FEMALE');
		}
		else
		{
			return false;
		}
	}

	public static function site($value)
	{
		if (empty($value))
		{
			return JHtml::_('users.value', $value);
		}
		else
		{
			if (substr($value, 0, 4) == "http")
			{
				return '<a href="'.$value.'">'.$value.'</a>';
			}
			else
			{
				return '<a href="http://'.$value.'">'.$value.'</a>';
			}
		}
	}

	public function onUserAfterSave($data, $isNew, $result, $error)
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
			if (($task == 'activate' || $isNew) && $params->get('stopForumSpam'))
			{
				$this->checkSpammer($data);
			}

			// Save K2 user profile
			if ($task != 'activate' && ($params->get('K2UserProfile') == 'native' || ($params->get('K2UserProfile') == 'legacy' && $isK2UserForm)))
			{
				// Get model
				$model = K2Model::getInstance('Users');

				// Get input data
				$input = $data;

				// Convert plugin data to normal input so our model can save it
				if (isset($data['k2Profile']))
				{
					foreach ($data['k2Profile'] as $name => $value)
					{
						$input[$name] = $value;
					}
				}

				if (!isset($input['image']))
				{
					$input['image'] = array();
					$input['image']['remove'] = 0;
					$input['image']['flag'] = 0;
				}

				if (!$input['image']['remove'])
				{
					// Get files
					$files = $application->input->files->get('jform');
					if (isset($files['k2Profile']) && $files['k2Profile']['image']['tmp_name'])
					{
						$file = $files['k2Profile']['image'];
						$image = K2HelperImages::addUserImage($file, null);
						$input['image']['flag'] = 1;
						$input['image']['temp'] = $image->temp;
					}
				}
				else
				{
					$input['image']['flag'] = 0;
				}

				// Pass data to the model
				$model->setState('data', $input);
				$model->setState('site', true);

				// Save
				if (!$model->save())
				{
					$this->_subject->setError($model->getError());
					return false;
				}

				// Redirect
				if ($params->get('K2UserProfile') == 'legacy')
				{
					$itemid = $params->get('redirect');

					if (!$isNew && $itemid)
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

		return true;

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
