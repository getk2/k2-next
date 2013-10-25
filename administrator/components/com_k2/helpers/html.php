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

	public static function published($name = 'published', $value = null)
	{
		$options = array();
		$options[] = JHTML::_('select.option', '', JText::_('K2_ALL'));
		$options[] = JHTML::_('select.option', 1, JText::_('K2_YES'));
		$options[] = JHTML::_('select.option', 0, JText::_('K2_NO'));
		return JHtml::_('select.radiolist', $options, $name, '', 'value', 'text', $value);
	}

	public static function featured($name = 'featured', $value = null)
	{
		$options = array();
		$options[] = JHTML::_('select.option', '', JText::_('K2_ALL'));
		$options[] = JHTML::_('select.option', 1, JText::_('K2_YES'));
		$options[] = JHTML::_('select.option', 0, JText::_('K2_NO'));
		return JHtml::_('select.radiolist', $options, $name, '', 'value', 'text', $value);
	}

	public static function language($name = 'language', $value = null, $none = false)
	{
		$options = JHtml::_('contentlanguage.existing', true, true);
		if ($none)
		{
			array_unshift($options, JHtml::_('select.option', '', JText::_($none)));
		}
		return JHtml::_('select.genericlist', $options, $name, '', 'value', 'text', $value);
	}

	public static function sorting($options = array(), $name = 'sorting', $value = null)
	{
		$list = array();
		foreach ($options as $optionLabel => $optionValue)
		{
			$list[] = JHTML::_('select.option', $optionValue, JText::_($optionLabel));
		}

		return JHtml::_('select.genericlist', $list, $name, '', 'value', 'text', $value);
	}

	public static function categories($name = 'catid', $value = null, $none = false, $exclude = null, $attributes = '')
	{
		$model = K2Model::getInstance('Categories', 'K2Model');
		$model->setState('sorting', 'ordering');
		$rows = $model->getRows();
		$options = array();
		if ($none)
		{
			$options[] = JHtml::_('select.option', '', JText::_($none));
		}
		foreach ($rows as $row)
		{
			if ($exclude != $row->id)
			{
				$title = str_repeat('-', intval($row->level) - 1).$row->title;
				if ($row->trashed)
				{
					$title .= JText::_('K2_TRASHED_CATEGORY_NOTICE');
				}
				else if (!$row->published)
				{
					$title .= JText::_('K2_UNPUBLISHED_CATEGORY_NOTICE');
				}
				$options[] = JHtml::_('select.option', $row->id, $title);
			}

		}
		return JHtml::_('select.genericlist', $options, $name, $attributes, 'value', 'text', $value);
	}

	public static function search($name = 'search')
	{
		return '<input type="text" class="appActionSearch"  name="'.$name.'" />';
	}

	public static function template($name = 'template', $value = null)
	{
		jimport('joomla.filesystem.folder');
		$application = JFactory::getApplication();
		$componentPath = JPATH_SITE.'/components/com_k2/templates';
		$componentFolders = JFolder::folders($componentPath);
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select($db->quoteName('template'))->from($db->quoteName('#__template_styles'))->where($db->quoteName('client_id').' = 0')->where($db->quoteName('home').' = 1');
		$db->setQuery($query);
		$defaultemplate = $db->loadResult();

		if (JFolder::exists(JPATH_SITE.'/templates/'.$defaultemplate.'/html/com_k2/templates'))
		{
			$templatePath = JPATH_SITE.'/templates/'.$defaultemplate.'/html/com_k2/templates';
		}
		else
		{
			$templatePath = JPATH_SITE.'/templates/'.$defaultemplate.'/html/com_k2';
		}

		if (JFolder::exists($templatePath))
		{
			$templateFolders = JFolder::folders($templatePath);
			$folders = @array_merge($templateFolders, $componentFolders);
			$folders = @array_unique($folders);
		}
		else
		{
			$folders = $componentFolders;
		}

		$exclude = 'default';
		$options = array();
		foreach ($folders as $folder)
		{
			if (preg_match(chr(1).$exclude.chr(1), $folder))
			{
				continue;
			}
			$options[] = JHtml::_('select.option', $folder, $folder);
		}

		array_unshift($options, JHtml::_('select.option', '', '-- '.JText::_('K2_USE_DEFAULT').' --'));
		return JHtml::_('select.genericlist', $options, $name, '', 'value', 'text', $value);
	}

	public static function extraFieldsGroups($name = 'extra_fields_group', $value = null, $none = false, $attributes = '', $scope = null)
	{
		$options = array();
		if ($none)
		{
			$options[] = JHtml::_('select.option', '', JText::_($none));
		}
		$model = K2Model::getInstance('ExtraFieldsGroups', 'K2Model');
		$model->setState('sorting', 'name');
		if (!is_null($scope))
		{
			$model->setState('scope', $scope);
		}
		$rows = $model->getRows();
		foreach ($rows as $row)
		{
			$options[] = JHtml::_('select.option', $row->id, $row->name);
		}
		return JHtml::_('select.genericlist', $options, $name, $attributes, 'value', 'text', $value);
	}

	public static function extraFieldsScopes($name = 'scope', $value = null, $attributes = '')
	{
		$options = array();
		require_once JPATH_ADMINISTRATOR.'/components/com_k2/helpers/extrafields.php';
		$rows = K2HelperExtraFields::getScopes();
		foreach ($rows as $row)
		{
			$options[] = JHtml::_('select.option', $row, JText::_('K2_EXTRA_FIELD_SCOPE_'.strtoupper($row)));
		}
		return JHtml::_('select.genericlist', $options, $name, $attributes, 'value', 'text', $value);
	}

	public static function extraFieldsTypes($name = 'extra_fields_type', $value = null, $none = false, $attributes = '')
	{
		$options = array();
		if ($none)
		{
			$options[] = JHtml::_('select.option', '', JText::_($none));
		}
		require_once JPATH_ADMINISTRATOR.'/components/com_k2/helpers/extrafields.php';

		$rows = K2HelperExtraFields::getTypes();

		foreach ($rows as $row)
		{
			$options[] = JHtml::_('select.option', $row, JText::_('K2_EXTRA_FIELD_TYPE_'.strtoupper($row)));
		}
		return JHtml::_('select.genericlist', $options, $name, $attributes, 'value', 'text', $value);
	}
	
	public static function usergroups($name = 'usergroups', $value = null, $none = false, $attributes = '')
	{
		$options = array();
		if ($none)
		{
			$options[] = JHtml::_('select.option', '', JText::_($none));
		}
		
		$model = K2Model::getInstance('UserGroups', 'K2Model');
		$rows = $model->getRows();

		foreach ($rows as $row)
		{
			$options[] = JHtml::_('select.option', $row->id, str_repeat('-', $row->level).$row->title);
		}
		return JHtml::_('select.genericlist', $options, $name, $attributes, 'value', 'text', $value);
	}

}
