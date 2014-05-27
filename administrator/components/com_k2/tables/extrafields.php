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

require_once JPATH_ADMINISTRATOR.'/components/com_k2/tables/table.php';

class K2TableExtraFields extends K2Table
{
	public function __construct($db)
	{
		parent::__construct('#__k2_extra_fields', 'id', $db);
	}

	public function check()
	{
		if (JString::trim($this->name) == '')
		{
			$this->setError(JText::_('K2_EXTRA_FIELD_MUST_HAVE_A_NAME'));
			return false;
		}

		if (JString::trim($this->type) == '')
		{
			$this->setError(JText::_('K2_EXTRA_FIELD_MUST_HAVE_A_TYPE'));
			return false;
		}

		if (JString::trim($this->alias) == '')
		{
			$this->alias = $this->name;
			$autoAlias = true;
		}
		else
		{
			$autoAlias = false;
		}
		
		if(JFactory::getApplication()->input->get('task') == 'run')
		{
			$autoAlias = true;
		}

		$searches = array('À', 'Á', 'Â', 'Ã', 'Ä', 'Å', 'à', 'á', 'â', 'ã', 'ä', 'å', 'Ā', 'ā', 'Ă', 'ă', 'Ą', 'ą', 'Ç', 'ç', 'Ć', 'ć', 'Ĉ', 'ĉ', 'Ċ', 'ċ', 'Č', 'č', 'Ð', 'ð', 'Ď', 'ď', 'Đ', 'đ', 'È', 'É', 'Ê', 'Ë', 'è', 'é', 'ê', 'ë', 'Ē', 'ē', 'Ĕ', 'ĕ', 'Ė', 'ė', 'Ę', 'ę', 'Ě', 'ě', 'Ĝ', 'ĝ', 'Ğ', 'ğ', 'Ġ', 'ġ', 'Ģ', 'ģ', 'Ĥ', 'ĥ', 'Ħ', 'ħ', 'Ì', 'Í', 'Î', 'Ï', 'ì', 'í', 'î', 'ï', 'Ĩ', 'ĩ', 'Ī', 'ī', 'Ĭ', 'ĭ', 'Į', 'į', 'İ', 'ı', 'Ĵ', 'ĵ', 'Ķ', 'ķ', 'ĸ', 'Ĺ', 'ĺ', 'Ļ', 'ļ', 'Ľ', 'ľ', 'Ŀ', 'ŀ', 'Ł', 'ł', 'Ñ', 'ñ', 'Ń', 'ń', 'Ņ', 'ņ', 'Ň', 'ň', 'ŉ', 'Ŋ', 'ŋ', 'Ò', 'Ó', 'Ô', 'Õ', 'Ö', 'Ø', 'ò', 'ó', 'ô', 'õ', 'ö', 'ø', 'Ō', 'ō', 'Ŏ', 'ŏ', 'Ő', 'ő', 'Ŕ', 'ŕ', 'Ŗ', 'ŗ', 'Ř', 'ř', 'Ś', 'ś', 'Ŝ', 'ŝ', 'Ş', 'ş', 'Š', 'š', 'ſ', 'Ţ', 'ţ', 'Ť', 'ť', 'Ŧ', 'ŧ', 'Ù', 'Ú', 'Û', 'Ü', 'ù', 'ú', 'û', 'ü', 'Ũ', 'ũ', 'Ū', 'ū', 'Ŭ', 'ŭ', 'Ů', 'ů', 'Ű', 'ű', 'Ų', 'ų', 'Ŵ', 'ŵ', 'Ý', 'ý', 'ÿ', 'Ŷ', 'ŷ', 'Ÿ', 'Ź', 'ź', 'Ż', 'ż', 'Ž', 'ž', 'α', 'β', 'γ', 'δ', 'ε', 'ζ', 'η', 'θ', 'ι', 'κ', 'λ', 'μ', 'ν', 'ξ', 'ο', 'π', 'ρ', 'σ', 'τ', 'υ', 'φ', 'χ', 'ψ', 'ω', 'Α', 'Β', 'Γ', 'Δ', 'Ε', 'Ζ', 'Η', 'Θ', 'Ι', 'Κ', 'Λ', 'Μ', 'Ξ', 'Ο', 'Π', 'Ρ', 'Σ', 'Τ', 'Υ', 'Φ', 'Χ', 'Ψ', 'Ω', 'ά', 'έ', 'ή', 'ί', 'ό', 'ύ', 'ώ', 'Ά', 'Έ', 'Ή', 'Ί', 'Ό', 'Ύ', 'Ώ', 'ϊ', 'ΐ', 'ϋ', 'ς', '1', '2', '3', '4', '5', '6', '7', '8', '9', '0', 'А', 'Ӑ', 'Ӓ', 'Ә', 'Ӛ', 'Ӕ', 'Б', 'В', 'Г', 'Ґ', 'Ѓ', 'Ғ', 'Ӷ', 'y', 'Д', 'Е', 'Ѐ', 'Ё', 'Ӗ', 'Ҽ', 'Ҿ', 'Є', 'Ж', 'Ӂ', 'Җ', 'Ӝ', 'З', 'Ҙ', 'Ӟ', 'Ӡ', 'Ѕ', 'И', 'Ѝ', 'Ӥ', 'Ӣ', 'І', 'Ї', 'Ӏ', 'Й', 'Ҋ', 'Ј', 'К', 'Қ', 'Ҟ', 'Ҡ', 'Ӄ', 'Ҝ', 'Л', 'Ӆ', 'Љ', 'М', 'Ӎ', 'Н', 'Ӊ', 'Ң', 'Ӈ', 'Ҥ', 'Њ', 'О', 'Ӧ', 'Ө', 'Ӫ', 'Ҩ', 'П', 'Ҧ', 'Р', 'Ҏ', 'С', 'Ҫ', 'Т', 'Ҭ', 'Ћ', 'Ќ', 'У', 'Ў', 'Ӳ', 'Ӱ', 'Ӯ', 'Ү', 'Ұ', 'Ф', 'Х', 'Ҳ', 'Һ', 'Ц', 'Ҵ', 'Ч', 'Ӵ', 'Ҷ', 'Ӌ', 'Ҹ', 'Џ', 'Ш', 'Щ', 'Ъ', 'Ы', 'Ӹ', 'Ь', 'Ҍ', 'Э', 'Ӭ', 'Ю', 'Я', 'а', 'ӑ', 'ӓ', 'ә', 'ӛ', 'ӕ', 'б', 'в', 'г', 'ґ', 'ѓ', 'ғ', 'ӷ', 'y', 'д', 'е', 'ѐ', 'ё', 'ӗ', 'ҽ', 'ҿ', 'є', 'ж', 'ӂ', 'җ', 'ӝ', 'з', 'ҙ', 'ӟ', 'ӡ', 'ѕ', 'и', 'ѝ', 'ӥ', 'ӣ', 'і', 'ї', 'Ӏ', 'й', 'ҋ', 'ј', 'к', 'қ', 'ҟ', 'ҡ', 'ӄ', 'ҝ', 'л', 'ӆ', 'љ', 'м', 'ӎ', 'н', 'ӊ', 'ң', 'ӈ', 'ҥ', 'њ', 'о', 'ӧ', 'ө', 'ӫ', 'ҩ', 'п', 'ҧ', 'р', 'ҏ', 'с', 'ҫ', 'т', 'ҭ', 'ћ', 'ќ', 'у', 'ў', 'ӳ', 'ӱ', 'ӯ', 'ү', 'ұ', 'ф', 'х', 'ҳ', 'һ', 'ц', 'ҵ', 'ч', 'ӵ', 'ҷ', 'ӌ', 'ҹ', 'џ', 'ш', 'щ', 'ъ', 'ы', 'ӹ', 'ь', 'ҍ', 'э', 'ӭ', 'ю', 'я', '-', ' ');
		$replacements = array('A', 'A', 'A', 'A', 'A', 'A', 'a', 'a', 'a', 'a', 'a', 'a', 'A', 'a', 'A', 'a', 'A', 'a', 'C', 'c', 'C', 'c', 'C', 'c', 'C', 'c', 'C', 'c', 'D', 'd', 'D', 'd', 'D', 'd', 'E', 'E', 'E', 'E', 'e', 'e', 'e', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'G', 'g', 'G', 'g', 'G', 'g', 'G', 'g', 'H', 'h', 'H', 'h', 'I', 'I', 'I', 'I', 'i', 'i', 'i', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'J', 'j', 'K', 'k', 'k', 'L', 'l', 'L', 'l', 'L', 'l', 'L', 'l', 'L', 'l', 'N', 'n', 'N', 'n', 'N', 'n', 'N', 'n', 'n', 'N', 'n', 'O', 'O', 'O', 'O', 'O', 'O', 'o', 'o', 'o', 'o', 'o', 'o', 'O', 'o', 'O', 'o', 'O', 'o', 'R', 'r', 'R', 'r', 'R', 'r', 'S', 's', 'S', 's', 'S', 's', 'S', 's', 's', 'T', 't', 'T', 't', 'T', 't', 'U', 'U', 'U', 'U', 'u', 'u', 'u', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'W', 'w', 'Y', 'y', 'y', 'Y', 'y', 'Y', 'Z', 'z', 'Z', 'z', 'Z', 'z', 'a', 'b', 'g', 'd', 'e', 'z', 'h', 'th', 'i', 'k', 'l', 'm', 'n', 'x', 'o', 'p', 'r', 's', 't', 'y', 'f', 'ch', 'ps', 'w', 'A', 'B', 'G', 'D', 'E', 'Z', 'H', 'Th', 'I', 'K', 'L', 'M', 'X', 'O', 'P', 'R', 'S', 'T', 'Y', 'F', 'Ch', 'Ps', 'W', 'a', 'e', 'h', 'i', 'o', 'y', 'w', 'A', 'E', 'H', 'I', 'O', 'Y', 'W', 'i', 'i', 'y', 's', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine', 'Zero', 'A', 'A', 'A', 'E', 'E', 'E', 'B', 'V', 'G', 'G', 'G', 'G', 'G', 'Y', 'D', 'E', 'E', 'YO', 'E', 'E', 'E', 'YE', 'ZH', 'DZH', 'ZH', 'DZH', 'Z', 'Z', 'DZ', 'DZ', 'DZ', 'I', 'I', 'I', 'I', 'I', 'JI', 'I', 'Y', 'Y', 'J', 'K', 'Q', 'Q', 'K', 'Q', 'K', 'L', 'L', 'L', 'M', 'M', 'N', 'N', 'N', 'N', 'N', 'N', 'O', 'O', 'O', 'O', 'O', 'P', 'PF', 'P', 'P', 'S', 'S', 'T', 'TH', 'T', 'K', 'U', 'U', 'U', 'U', 'U', 'U', 'U', 'F', 'H', 'H', 'H', 'TS', 'TS', 'CH', 'CH', 'CH', 'CH', 'CH', 'DZ', 'SH', 'SHT', 'A', 'Y', 'Y', 'Y', 'Y', 'E', 'E', 'YU', 'YA', 'a', 'a', 'a', 'e', 'e', 'e', 'b', 'v', 'g', 'g', 'g', 'g', 'g', 'y', 'd', 'e', 'e', 'yo', 'e', 'e', 'e', 'ye', 'zh', 'dzh', 'zh', 'dzh', 'z', 'z', 'dz', 'dz', 'dz', 'i', 'i', 'i', 'i', 'i', 'ji', 'i', 'y', 'y', 'j', 'k', 'q', 'q', 'k', 'q', 'k', 'l', 'l', 'l', 'm', 'm', 'n', 'n', 'n', 'n', 'n', 'n', 'o', 'o', 'o', 'o', 'o', 'p', 'pf', 'p', 'p', 's', 's', 't', 'th', 't', 'k', 'u', 'u', 'u', 'u', 'u', 'u', 'u', 'f', 'h', 'h', 'h', 'ts', 'ts', 'ch', 'ch', 'ch', 'ch', 'ch', 'dz', 'sh', 'sht', 'a', 'y', 'y', 'y', 'y', 'e', 'e', 'yu', 'ya', '', '');
		$this->alias = str_replace($searches, $replacements, $this->alias);
		$this->alias = JFilterOutput::stringURLSafe($this->alias);
		if (JString::trim($this->alias) == '')
		{
			$this->setError(JText::_('K2_EXTRA_FIELD_PLEASE_PROVIDE_A_VALID_ALIAS'));
			return false;
		}

		$db = $this->getDbo();
		$query = $db->getQuery(true);
		$query->select($db->quoteName('id'))->from($db->quoteName('#__k2_extra_fields'))->where($db->quoteName('alias').' = '.$db->quote($this->alias));
		if ($this->id)
		{
			$query->where($db->quoteName('id').' != '.(int)$this->id);
		}
		$db->setQuery($query);
		if ($db->loadResult())
		{
			if ($autoAlias)
			{
				$this->alias .= '-'.uniqid();
			}
			else
			{
				$this->setError(JText::_('K2_DUPLICATE_ALIAS'));
				return false;
			}
		}

		return true;
	}

	/**
	 * Method to bind an associative array or object to the JTable instance.This
	 * method only binds properties that are publicly accessible and optionally
	 * takes an array of properties to ignore when binding.
	 *
	 * @param   mixed  $src     An associative array or object to bind to the JTable instance.
	 * @param   mixed  $ignore  An optional array or space separated list of properties to ignore while binding.
	 *
	 * @return  boolean  True on success.
	 *
	 * @link    http://docs.joomla.org/JTable/bind
	 * @since   11.1
	 * @throws  InvalidArgumentException
	 */
	public function bind($src, $ignore = array())
	{
		if (is_object($src))
		{
			$src = get_object_vars($src);
		}
		if (isset($src['value']) && is_array($src['value']))
		{
			$registry = new JRegistry;
			$registry->loadArray($src['value']);
			$src['value'] = $registry->toString();
		}

		return parent::bind($src, $ignore);
	}

}
