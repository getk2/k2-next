<?php
/**
 * @version		3.0.0b
 * @package		K2
 * @author		JoomlaWorks http://www.joomlaworks.net
 * @copyright	Copyright (c) 2006 - 2014 JoomlaWorks Ltd. All rights reserved.
 * @license		GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 */

// no direct access
defined('_JEXEC') or die ;

require_once JPATH_ADMINISTRATOR.'/components/com_k2/models/model.php';
K2Model::addIncludePath(JPATH_ADMINISTRATOR.'/components/com_k2/models');

class ModK2StatsHelper
{
	public static function getLatestItems()
	{
		$model = K2Model::getInstance('Items');
		$model->setState('state', 1);
		$model->setState('limit', 10);
		$rows = $model->getRows();
		return $rows;
	}

	public static function getPopularItems()
	{
		$model = K2Model::getInstance('Items');
		$model->setState('state', 1);
		$model->setState('sorting', 'hits.reverse');
		$model->setState('limit', 10);
		$rows = $model->getRows();
		return $rows;
	}

	public static function getMostCommentedItems()
	{
		$model = K2Model::getInstance('Items');
		$model->setState('state', 1);
		$model->setState('sorting', 'comments.reverse');
		$model->setState('limit', 10);
		$rows = $model->getRows();
		return $rows;
	}

	public static function getLatestComments()
	{
		$model = K2Model::getInstance('Comments');
		$model->setState('sorting', 'created.reverse');
		$model->setState('limit', 10);
		$rows = $model->getRows();
		return $rows;
	}

	public static function getStatistics()
	{
		$statistics = new stdClass;
		$statistics->numOfItems = self::countItems();
		$statistics->numOfTrashedItems = self::countTrashedItems();
		$statistics->numOfFeaturedItems = self::countFeaturedItems();
		$statistics->numOfComments = self::countComments();
		$statistics->numOfCategories = self::countCategories();
		$statistics->numOfTrashedCategories = self::countTrashedCategories();
		$statistics->numOfUsers = self::countUsers();
		$statistics->numOfUserGroups = self::countUserGroups();
		$statistics->numOfTags = self::countTags();
		return $statistics;
	}

	public static function countItems()
	{
		$model = K2Model::getInstance('Items');
		$result = $model->countRows();
		return $result;
	}

	public static function countTrashedItems()
	{
		$model = K2Model::getInstance('Items');
		$model->setState('state', -1);
		$result = $model->countRows();
		return $result;
	}

	public static function countFeaturedItems()
	{
		$model = K2Model::getInstance('Items');
		$model->setState('featured', 1);
		$result = $model->countRows();
		return $result;
	}

	public static function countComments()
	{
		$model = K2Model::getInstance('Comments');
		$result = $model->countRows();
		return $result;
	}

	public static function countCategories()
	{
		$model = K2Model::getInstance('Categories');
		$result = $model->countRows();
		return $result;
	}

	public static function countTrashedCategories()
	{
		$model = K2Model::getInstance('Categories');
		$model->setState('state', -1);
		$result = $model->countRows();
		return $result;
	}

	public static function countUsers()
	{
		$model = K2Model::getInstance('Users');
		$result = $model->countRows();
		return $result;
	}

	public static function countUserGroups()
	{
		$model = K2Model::getInstance('UserGroups');
		$result = $model->countRows();
		return $result;
	}

	public static function countTags()
	{
		$model = K2Model::getInstance('Tags');
		$result = $model->countRows();
		return $result;
	}

}
