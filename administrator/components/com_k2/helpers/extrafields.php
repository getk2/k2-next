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

	/*
	 * Locate the ID of the ExtraFields Group. Create a group on the fly in case we need one
	 */
	public static function findExtraFieldsGroups ($db, $name, $scope = 'item')
	{
		$groups = self::getGroups();
		foreach ($groups as $group){
			if($group->name == $name){
				return $group->id;
			}
		}
		
		// User
		$user = JFactory::getUser();
		
		// Permissions check
		if (!$user->authorise('k2.extrafields.manage'))
		{
			$this->setError(JText::_('K2_YOU_ARE_NOT_AUTHORIZED_TO_PERFORM_THIS_OPERATION'));
			return false;
		}
		
		require_once JPATH_ADMINISTRATOR.'/components/com_k2/models/extrafieldsgroups.php';
		require_once JPATH_ADMINISTRATOR.'/components/com_k2/tables/extrafieldsgroups.php';
		
		$row = new K2TableExtraFieldsGroups($db);
		
		$row->name = $name;
		$row->scope = $scope;
		
		if(!$row->check()) {
			return false;
		}
		if(!$row->store()) {
			return false;
		}
		
		unset(self::$groups[$scope]);
		$groups = self::getGroups();
		foreach ($groups as $group){
			if($group->name == $name){
				return $group->id;
			}
		}
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
			$group = clone $itemGroups[$groupId];
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
		$fields = array();
		foreach ($group->fields as $key => $field)
		{
			if (!$field->state)
			{
				unset($group->fields[$key]);
				continue;
			}
			$clone = clone $field;
			unset($group->fields[$key]);
			if (property_exists($values, $clone->id))
			{
				$index = $clone->id;
				$resourceValues = $values->$index;
				$defaults = json_decode($clone->value);
				$activeValues = array_merge((array)$defaults, (array)$resourceValues);
				$clone->value = json_encode((object)$activeValues);
			}
			$clone->input = $clone->getInput();
			$clone->output = $clone->getOutput();
			$fields[] = $clone;
		}
		$group->fields = $fields;
		return $group;
	}

}
