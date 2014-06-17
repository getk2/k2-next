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
			$document->addScript('https://www.google.com/recaptcha/api/js/recaptcha_ajax.js');
			$js = '
			function K2ShowRecaptcha(){
				Recaptcha.create("'.$params->get('recaptcha_public_key').'", "K2Recaptcha", {
					theme: "'.$params->get('recaptcha_theme', 'clean').'"
				});
			}';
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
			$output .= '<label class="K2CommentsCaptcha">'.JText::_('K2_ENTER_THE_TWO_WORDS_YOU_SEE_BELOW').'</label><div id="K2Recaptcha"></div>';
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
				$data['privatekey'] = $params->get('recaptcha_private_key');
				$data['remoteip'] = $_SERVER["REMOTE_ADDR"];
				$data['challenge'] = $application->input->post->get('recaptcha_challenge_field', '', 'raw');
				$data['response'] = $application->input->post->get('recaptcha_response_field', '', 'raw');

				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, 'http://www.google.com/recaptcha/api/verify');
				curl_setopt($ch, CURLOPT_POST, 1);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
				$response = curl_exec($ch);
				$error = curl_error($ch);
				curl_close($ch);

				if ($response === false)
				{
					$model->setError($error);
					return false;
				}

				$lines = explode("\n", $response);
				if (trim($lines[0]) != 'true')
				{
					$model->setError(JText::_('K2_THE_WORDS_YOU_TYPED_DID_NOT_MATCH_THE_ONES_DISPLAYED_PLEASE_TRY_AGAIN'));
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
					$akismetApiKey = $params->get('akismetApiKey');
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
