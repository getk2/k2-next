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
 * K2 Extra Fields helper class.
 */

class K2HelperExtraFields
{

	/**
	 * Holds the available extra fields types.
	 *
	 * @var array $types
	 */
	public static $types = null;

	/**
	 * Holds the available extra fields scopes.
	 *
	 * @var array $types
	 */
	public static $scopes = array(
		'item',
		'category',
		'user',
		'tag'
	);

	/**
	 * Holds the available extra fields groups per scope.
	 *
	 * @var array $groups
	 */
	public static $groups = array();

	/**
	 * Holds the available extra fields definitions.
	 *
	 * @var array $definitions
	 */
	public static $definitions = null;

	public static function getTypes()
	{
		if (is_null(self::$types))
		{
			jimport('joomla.filesystem.folder');
			self::$types = JFolder::folders(JPATH_ADMINISTRATOR.'/components/com_k2/extrafields');
		}
		return self::$types;
	}

	public static function getScopes()
	{
		return self::$scopes;
	}

	public static function getGroups($scope = 'item')
	{
		if (!isset(self::$groups[$scope]))
		{
			K2Model::addIncludePath(JPATH_ADMINISTRATOR.'/components/com_k2/models');
			$model = K2Model::getInstance('ExtraFieldsGroups', 'K2Model');
			$model->setState('scope', $scope);
			$model->setState('sorting', 'name');
			$rows = $model->getRows();
			self::$groups[$scope] = array();
			foreach ($rows as $row)
			{
				self::$groups[$scope][$row->id] = $row;
			}
		}
		return self::$groups[$scope];
	}

	public static function getDefinitions()
	{
		if (is_null(self::$definitions))
		{
			jimport('joomla.filesystem.file');
			$definitions = array();
			$types = self::getTypes();
			foreach ($types as $type)
			{
				$extraField = new K2ExtraFields( array(
					'id' => null,
					'type' => '',
					'value' => ''
				));
				$extraField->type = $type;
				$definitions[$type] = $extraField->getDefinition();
			}
			self::$definitions = $definitions;
		}
		return self::$definitions;
	}

	public static function getItemExtraFieldsGroups($categoryId, $values)
	{
		$groups = array();
		$values = json_decode($values);
		$category = K2Categories::getInstance($categoryId);
		$categoryParams = $category->getEffectiveParams();
		$selectedGroups = $categoryParams->get('catExtraFieldGroups', array());
		if(!is_array($selectedGroups))
		{
			$selectedGroups = array($selectedGroups);
		}
		$itemGroups = self::getGroups('item');
		foreach ($selectedGroups as $groupId)
		{
			$group = $itemGroups[$groupId];
			$groups[] = self::renderGroup($group, $values);
		}
		return $groups;
	}

	public static function getCategoryExtraFieldsGroups($values)
	{
		$groups = array();
		$values = json_decode($values);

		foreach (self::getGroups('category') as $group)
		{
			$groups[] = self::renderGroup($group, $values);
		}
		return $groups;
	}

	public static function getUserExtraFieldsGroups($values)
	{
		$groups = array();
		$values = json_decode($values);
		foreach (self::getGroups('user') as $group)
		{
			$groups[] = self::renderGroup($group, $values);
		}
		return $groups;
	}

	public static function getTagExtraFieldsGroups($values)
	{
		$groups = array();
		$values = json_decode($values);

		foreach (self::getGroups('tag') as $group)
		{
			$groups[] = self::renderGroup($group, $values);
		}
		return $groups;
	}

	private static function renderGroup($group, $values)
	{
		if (is_null($values))
		{
			$values = new stdClass;
		}
		foreach ($group->fields as $key => $field)
		{
			if (!$field->state)
			{
				unset($group->fields[$key]);
				continue;
			}
			if (property_exists($values, $field->id))
			{
				$index = $field->id;
				$resourceValues = $values->$index;
				$defaults = json_decode($field->value);
				$activeValues = array_merge((array)$defaults, (array)$resourceValues);
				$field->value = json_encode((object)$activeValues);
			}
			$field->input = $field->getInput();
			$field->output = $field->getOutput();
		}

		return $group;
	}

}
