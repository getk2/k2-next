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

/**
 * K2 captcha helper class.
 */
class K2HelperCaptcha
{

	public static function initialize()
	{
		$params = JComponentHelper::getParams('com_k2');
		$user = JFactory::getUser();
		if (($params->get('antispam') == 'recaptcha' || $params->get('antispam') == 'both') && $params->get('recaptcha_public_key') && ($user->guest || $params->get('recaptchaForRegistered')))
		{
			$document = JFactory::getDocument();
			$document->addScript('https://www.google.com/recaptcha/api.js?render=explicit');
			$js = '
			var k2TimeoutId;
			function K2ShowRecaptcha(){
				if(typeof(grecaptcha) != "undefined") {
					grecaptcha.render("K2Recaptcha", {
						"sitekey" : "'.trim($params->get('recaptcha_public_key')).'",
						"theme": "'.$params->get('recaptcha_theme', 'light').'"
					});
					window.clearTimeout(k2TimeoutId);
				} else {
					k2TimeoutId = window.setTimeout(K2ShowRecaptcha, 1000);
				}
			}
			';
			$document->addScriptDeclaration($js);
		}

	}

	public static function display()
	{
		$params = JComponentHelper::getParams('com_k2');
		$user = JFactory::getUser();
		$output = '';
		if (($params->get('antispam') == 'recaptcha' || $params->get('antispam') == 'both') && $params->get('recaptcha_public_key') && ($user->guest || $params->get('recaptchaForRegistered')))
		{
			$output .= '<label class="K2CommentsCaptcha">'.JText::_('K2_PLEASE_VERIFY_THAT_YOU_ARE_HUMAN').'</label><div id="K2Recaptcha"></div>';
		}
		return $output;
	}

	public static function check($input, &$model)
	{
		$application = JFactory::getApplication();
		$params = JComponentHelper::getParams('com_k2');
		$user = JFactory::getUser();

		// Google reCAPTCHA
		if ($params->get('antispam') == 'recaptcha' || $params->get('antispam') == 'both')
		{
			if ($user->guest || $params->get('recaptchaForRegistered'))
			{
				$data = array();
				$data['secret'] = trim($params->get('recaptcha_private_key'));
				$data['remoteip'] = $_SERVER["REMOTE_ADDR"];
				$data['response'] = $application->input->post->get('g-recaptcha-response', '', 'raw');

				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, 'https://www.google.com/recaptcha/api/siteverify?'.http_build_query($data));
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				$response = curl_exec($ch);
				$error = curl_error($ch);
				curl_close($ch);

				if ($response === false)
				{
					$model->setError($error);
					return false;
				}

				$json = json_decode($response);
				if (!$json->success)
				{
					$model->setError(JText::_('K2_WE_COULD_NOT_VERIFY_THAT_YOU_ARE_HUMAN'));
					return false;
				}

			}
		}
		// Akismet
		if ($params->get('antispam') == 'akismet' || $params->get('antispam') == 'both')
		{

			if ($user->guest || $params->get('akismetForRegistered'))
			{
				if ($params->get('akismetApiKey'))
				{
					require_once JPATH_ADMINISTRATOR.'components/com_k2/classes/akismet.class.php';
					$akismetApiKey = trim($params->get('akismetApiKey'));
					$akismet = new Akismet(JURI::root(false), $akismetApiKey);
					$akismet->setCommentAuthor($input['name']);
					$akismet->setCommentAuthorEmail($input['email']);
					$akismet->setCommentAuthorURL($input['url']);
					$akismet->setCommentContent($input['text']);
					$akismet->setPermalink(JURI::root(false).'index.php?option=com_k2&view=item&id='.$input['itemId']);
					try
					{
						if ($akismet->isCommentSpam())
						{
							$model->setError(JText::_('K2_SPAM_ATTEMPT_HAS_BEEN_DETECTED_THE_COMMENT_HAS_BEEN_REJECTED'));
							return false;
						}
					}
					catch(Exception $e)
					{
						$model->setError($e->getMessage());
						return false;
					}

				}
			}

		}

		return true;
	}

}
