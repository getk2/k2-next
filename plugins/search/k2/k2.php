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

/**
 * K2 Search plugin
 */

class PlgSearchK2 extends JPlugin
{

	/**
	 * @return array An array of search areas
	 */
	public function onContentSearchAreas()
	{
		$this->loadLanguage('plg_search_k2', JPATH_ADMINISTRATOR);
		static $areas = array('k2' => 'K2_K2_ITEMS');
		return $areas;
	}

	/**
	 * K2 Search method
	 * The sql must return the following fields that are used in a common display
	 * routine: href, title, section, created, text, browsernav
	 * @param string Target search string
	 * @param string mathcing option, exact|any|all
	 * @param string ordering option, newest|oldest|popular|alpha|category
	 * @param mixed  An array if the search it to be restricted to areas, null if search all
	 */
	public function onContentSearch($text, $phrase = '', $ordering = '', $areas = null)
	{
		// If K2 items are not enabled in this search operation return now
		if (is_array($areas))
		{
			if (!array_intersect($areas, array_keys($this->onContentSearchAreas())))
			{
				return array();
			}
		}

		// Trim text. If it is empty return now
		$text = trim($text);
		if ($text == '')
		{
			return array();
		}

		// Load K2 Model class
		require_once JPATH_ADMINISTRATOR.'/components/com_k2/models/model.php';

		// Add include path
		K2Model::addIncludePath(JPATH_ADMINISTRATOR.'/components/com_k2/models');

		// Search items
		$model = K2Model::getInstance('Items');
		$model->setState('site', true);
		$model->setState('search', $text);
		$model->setState('search.mode', $phrase);

		switch ($ordering)
		{
			case 'oldest' :
				$model->setState('sorting', 'created');
				break;

			case 'popular' :
				$model->setState('sorting', 'hits.reverse');
				break;

			case 'alpha' :
				$model->setState('sorting', 'title');
				break;

			case 'category' :
				$model->setState('sorting', 'category');
				break;

			case 'newest' :
				$model->setState('sorting', 'created.reverse');
				break;

			default :
				$model->setState('sorting', 'id.reverse');
				break;
		}

		$limit = $this->params->def('search_limit', 50);
		$model->setState('limit', $limit);
		$rows = $model->getRows();
		$results = array();
		foreach ($rows as $item)
		{
			$item->browsernav = '';
			$item->section = $item->category->name;
			$item->href = $item->link;
			$item->text = $item->introtext.' '.$item->fulltext;
			$item->extra_fields_search = '';
			foreach ($item->extraFieldsGroups as $extraFieldGroup)
			{
				foreach ($extraFieldGroup->fields as $field)
				{
					$item->extra_fields_search .= $field->output.' ';
				}
			}
			$item->tags_search = '';
			foreach ($item->tags as $tag)
			{
				$item->tags_search .= $tag->name.' ';
			}
			if (SearchHelper::checkNoHTML($item, $text, array(
				'title',
				'introtext',
				'fulltext',
				'extra_fields_search',
				'tags_search'
			)))
			{
				$results[] = $item;
			}
		}
		return $results;
	}

}
