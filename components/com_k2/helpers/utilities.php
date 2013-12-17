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
 * K2 utilities helper class.
 */

class K2HelperUtilities
{
	public static function writtenBy($gender)
	{
		if ($gender == 'm')
		{
			return JText::_('K2_WRITTEN_BY_MALE');
		}
		else if ($gender == 'f')
		{
			return JText::_('K2_WRITTEN_BY_FEMALE');
		}
		else
		{
			return JText::_('K2_WRITTEN_BY');
		}
	}

	public static function wordLimit($string, $length = 100, $endCharacter = '&#8230;')
	{
		// If the string is empty return
		if (trim($string) == '')
		{
			return $string;
		}

		// Strip HTML tags
		$string = strip_tags($string);

		// Get words
		$words = str_word_count($string, 1);

		// Truncate
		if (count($words) > $length)
		{
			$words = array_slice($words, $length);
			$string = implode(' ', $words);

			// Append end character
			if ($endCharacter)
			{
				$string .= ' '.$endCharacter;
			}
		}

		// Return
		return $string;
	}

}
