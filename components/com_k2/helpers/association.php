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

abstract class K2HelperAssociation
{

	public static function getAssociations($id = 0, $view = null)
	{
		jimport('helper.route', JPATH_COMPONENT_SITE);
		$application = JFactory::getApplication();
		$view = is_null($view) ? $application->input->get('view') : $view;
		$task = $application->input->get('task');
		$id = empty($id) ? $application->input->getInt('id') : $id;
		if ($view == 'item')
		{
			if ($id)
			{
				$associations = self::getItemAssociations($id);
				$return = array();
				foreach ($associations as $tag => $item)
				{
					$return[$tag] = K2HelperRoute::getItemRoute($item->id, $item->catid, $item->language);
				}
				return $return;
			}
		}
		else if ($view == 'itemlist' && $task == 'category')
		{
			if ($id)
			{
				$associations = self::getCategoryAssociations($id);
				$return = array();
				foreach ($associations as $tag => $category)
				{
					$return[$tag] = K2HelperRoute::getCategoryRoute($category->id, $category->language);
				}
				return $return;
			}
		}
		return array();

	}

	public static function getItemAssociations($id)
	{
		$associations = array();
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select($db->quoteName('c2.language'));
		$query->select($db->quoteName('c2.title'));
		$query->select($query->concatenate(array(
			$db->quoteName('c2.id'),
			$db->quoteName('c2.alias')
		), ':').' AS '.$db->quoteName('id'));
		$query->from($db->quoteName('#__k2_items', 'c'));
		$query->join('INNER', $db->quoteName('#__associations', 'a').' ON '.$db->quoteName('a.id').' = '.$db->quoteName('c.id').' AND '.$db->quoteName('a.context').' = '.$db->quote('com_k2.item'));
		$query->join('INNER', $db->quoteName('#__associations', 'a2').' ON '.$db->quoteName('a.key').' = '.$db->quoteName('a2.key'));
		$query->join('INNER', $db->quoteName('#__k2_items', 'c2').' ON '.$db->quoteName('a2.id').' = '.$db->quoteName('c2.id'));
		$query->join('INNER', $db->quoteName('#__k2_categories', 'ca').' ON '.$db->quoteName('c2.catid').' = '.$db->quoteName('ca.id'));
		$query->select($query->concatenate(array(
			$db->quoteName('ca.id'),
			$db->quoteName('ca.alias')
		), ':').' AS '.$db->quoteName('catid'));
		$query->where($db->quoteName('c.id').' = '.(int)$id);
		$db->setQuery($query);
		try
		{
			$items = $db->loadObjectList('language');
		}
		catch (RuntimeException $e)
		{
			throw new Exception($e->getMessage(), 500);
		}
		if ($items)
		{
			foreach ($items as $tag => $item)
			{
				// Do not return itself as result
				if ((int)$item->id != $id)
				{
					$associations[$tag] = $item;
				}
			}
		}
		return $associations;
	}

	public static function getCategoryAssociations($id)
	{
		$associations = array();
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select(array(
			$db->quoteName('c2.language'),
			$db->quoteName('c2.title'),
			$query->concatenate(array(
				$db->quoteName('c2.id'),
				$db->quoteName('c2.alias')
			), ':').' AS '.$db->quoteName('id')
		));
		$query->from($db->quoteName('#__k2_categories', 'c'));
		$query->join('INNER', $db->quoteName('#__associations').' AS '.$db->quoteName('a').' ON'.$db->quoteName('a.id').' = '.$db->quoteName('c.id').' AND '.$db->quoteName('a.context').' = '.$db->quote('com_k2.category'));
		$query->join('INNER', $db->quoteName('#__associations').' AS '.$db->quoteName('a2').' ON'.$db->quoteName('a.key').' = '.$db->quoteName('a2.key'));
		$query->join('INNER', $db->quoteName('#__k2_categories').' AS '.$db->quoteName('c2').' ON'.$db->quoteName('a2.id').' = '.$db->quoteName('c2.id'));
		$query->where($db->quoteName('c.id').' = '.(int)$id);

		$db->setQuery($query);
		try
		{
			$items = $db->loadObjectList('language');
		}
		catch (RuntimeException $e)
		{
			throw new Exception($e->getMessage(), 500);
		}
		if ($items)
		{
			foreach ($items as $tag => $item)
			{
				// Do not return itself as result
				if ((int)$item->id != $id)
				{
					$associations[$tag] = $item;
				}
			}
		}
		return $associations;
	}

}
