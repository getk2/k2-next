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
 * K2 language helper class.
 */

class K2HelperLanguage
{
	public static function transliterate($string, $languageId)
	{
		if ($languageId == '*')
		{
			$params = JComponentHelper::getParams('com_languages');
			$tag = $params->get('site');
		}
		else
		{
			$tag = $languageId;
		}
		$language = JLanguage::getInstance($tag);
		$string = $language->transliterate($string);
		$string = JFilterOutput::stringURLSafe($string);
		return $string;
	}

}
