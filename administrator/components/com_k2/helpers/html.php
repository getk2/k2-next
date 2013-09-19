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

require_once JPATH_ADMINISTRATOR.'/components/com_k2/helpers/helper.php';

/**
 * K2 HTML helper class.
 */

class K2HelperHTML extends K2Helper
{

	public static function published($value = null, $name = 'published')
	{
		$options = array();
		$options[] = JHTML::_('select.option', -1, JText::_('K2_ALL'));
		$options[] = JHTML::_('select.option', 1, JText::_('K2_YES'));
		$options[] = JHTML::_('select.option', 0, JText::_('K2_NO'));
		return JHtml::_('select.radiolist', $options, $name, '', 'value', 'text', $value);
	}

	public static function featured($value = null, $name = 'featured')
	{
		$options = array();
		$options[] = JHTML::_('select.option', -1, JText::_('K2_ALL'));
		$options[] = JHTML::_('select.option', 1, JText::_('K2_YES'));
		$options[] = JHTML::_('select.option', 0, JText::_('K2_NO'));
		return JHtml::_('select.radiolist', $options, $name, '', 'value', 'text', $value);
	}

	public static function language($value = null, $name = 'language')
	{
		$options = JHtml::_('contentlanguage.existing', true, true);
		array_unshift($options, JHtml::_('select.option', '', JText::_('K2_ANY')));
		return JHtml::_('select.genericlist', $options, $name, '', 'value', 'text', $value);
	}

	public static function sorting($value = null, $options = array(), $name = 'sorting')
	{
		$list = array();
		foreach ($options as $optionLabel => $optionValue)
		{
			$list[] = JHTML::_('select.option', $optionValue, JText::_($optionLabel));
		}

		return JHtml::_('select.genericlist', $list, $name, '', 'value', 'text', $value);
	}

}
