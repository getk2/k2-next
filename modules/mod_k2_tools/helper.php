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

require_once JPATH_SITE.'/components/com_k2/helpers/route.php';
require_once JPATH_SITE.'/components/com_k2/helpers/utilities.php';
require_once JPATH_ADMINISTRATOR.'/components/com_k2/models/model.php';
K2Model::addIncludePath(JPATH_ADMINISTRATOR.'/components/com_k2/models');

class ModK2ToolsHelper
{
	public static function getArchive($params)
	{
		$model = K2Model::getInstance('Items');
		$model->setState('site', true);
		$filter = $params->get('archiveCategory');
		$model->setState('category.filter', $filter);
		$model->setState('sorting', 'created.reverse');
		$rows = $model->getArchive();
		$months = array(JText::_('K2_JANUARY'), JText::_('K2_FEBRUARY'), JText::_('K2_MARCH'), JText::_('K2_APRIL'), JText::_('K2_MAY'), JText::_('K2_JUNE'), JText::_('K2_JULY'), JText::_('K2_AUGUST'), JText::_('K2_SEPTEMBER'), JText::_('K2_OCTOBER'), JText::_('K2_NOVEMBER'), JText::_('K2_DECEMBER'), );
		$archives = array();
		$root = isset($filter->categories[0]) ? $filter->categories[0] : 0;
		foreach ($rows as $row)
		{
			$row->numOfItems = '';
			if ($params->get('archiveItemsCounter'))
			{
				$row->numOfItems = self::countArchiveItems($row->month, $row->year, $root);
			}
			$row->name = $months[($row->month) - 1];
			$row->link = JRoute::_(K2HelperRoute::getDateRoute($row->year, $row->month, null, $root));
			// Legacy
			$row->y = $row->year;
			$archives[] = $row;
		}
		return $archives;

	}

	private static function countArchiveItems($month, $year, $categoryId)
	{
		$model = K2Model::getInstance('Items');
		$model->setState('site', true);
		$model->setState('category', $categoryId);
		$model->setState('month', $month);
		$model->setState('year', $year);
		$result = $model->countRows();
		return $result;
	}

	public static function getAuthors($params)
	{
		$model = K2Model::getInstance('Items');
		$model->setState('site', true);
		// Category filter
		$model->setState('category.filter', $params->get('authors_module_category'));
		$rows = $model->getAuthors();
		$authors = array();
		if (count($rows))
		{
			foreach ($rows as $row)
			{
				$author = K2Users::getInstance($row->created_by);
				if ($params->get('authorLatestItem'))
				{
					$model->setState('site', true);
					$model->setState('author', $author->id);
					$model->setState('limit', 1);
					$model->setState('sorting', 'created.reverse');
					$latest = $model->getRows();
					$author->latest = $latest[0];
				}
				if ($params->get('authorItemsCounter'))
				{
					$model->setState('site', true);
					$model->setState('author', $author->id);
					$author->items = $model->countRows();
				}

				$authors[] = $author;
			}
		}
		return $authors;
	}

	public static function getCalendar($params)
	{
		require_once dirname(__FILE__).'/includes/k2calendar.php';

		$month = $params->get('month');
		$year = $params->get('year');
		$filter = $params->get('calendarCategory');

		$months = array(JText::_('K2_JANUARY'), JText::_('K2_FEBRUARY'), JText::_('K2_MARCH'), JText::_('K2_APRIL'), JText::_('K2_MAY'), JText::_('K2_JUNE'), JText::_('K2_JULY'), JText::_('K2_AUGUST'), JText::_('K2_SEPTEMBER'), JText::_('K2_OCTOBER'), JText::_('K2_NOVEMBER'), JText::_('K2_DECEMBER'), );
		$days = array(JText::_('K2_SUN'), JText::_('K2_MON'), JText::_('K2_TUE'), JText::_('K2_WED'), JText::_('K2_THU'), JText::_('K2_FRI'), JText::_('K2_SAT'), );

		$calendar = new K2Calendar();
		$root = isset($filter->categories[0]) ? $filter->categories[0] : 0;
		$calendar->category = $root;
		$calendar->setStartDay(1);
		$calendar->setMonthNames($months);
		$calendar->setDayNames($days);

		if (($month) && ($year))
		{
			return $calendar->getMonthView($month, $year);
		}
		else
		{
			return $calendar->getCurrentMonthView();
		}
	}

	public static function getBreadcrumbs($params)
	{
		$application = JFactory::getApplication();
		$option = $application->input->get('option', '', 'cmd');
		$view = $application->input->get('view', '', 'cmd');
		$task = $application->input->get('task', '', 'cmd');
		$id = $application->input->get('id', 0, 'int');
		$menu = $application->getMenu();
		$active = $menu->getActive();

		$breadcrumbs = new stdClass;
		$breadcrumbs->title = '';
		$breadcrumbs->path = array();
		$breadcrumbs->home = $params->get('home', JText::_('K2_HOME'));
		$breadcrumbs->separator = $params->get('seperator', '&raquo;');

		$pathway = $application->getPathway();
		$pathwayItems = $pathway->getPathWay();

		foreach ($pathwayItems as $pathwayItem)
		{
			$pathwayItem->title = $pathwayItem->name;
		}

		if ($option == 'com_k2' && $view == 'item' || ($view == 'itemlist' && $task == 'category'))
		{

			switch ($view)
			{
				case 'item' :
					// Get item
					$item = K2Items::getInstance($id);

					// Check for menu link
					$matchItem = $active && isset($active->query['view']) && $active->query['view'] == 'item' && isset($active->query['id']) && $active->query['id'] == $id;
					$matchCategory = $active && isset($active->query['view']) && $active->query['view'] == 'itemlist' && isset($active->query['task']) && $active->query['task'] == 'category' && isset($active->query['id']) && $active->query['id'] == $item->catid;

					// Handle depending on matches
					if ($matchItem)
					{
						$breadcrumbs->title =   end($pathwayItems)->title;
						foreach ($pathwayItems as $pathwayItem)
						{
							$breadcrumbs->path[] = $pathwayItem;
						}
						array_pop($breadcrumbs->path);
					}
					else if ($matchCategory)
					{
						$breadcrumbs->title = $item->title;
						foreach ($pathwayItems as $pathwayItem)
						{
							$breadcrumbs->path[] = $pathwayItem;
						}
						array_pop($breadcrumbs->path);
					}
					else
					{
						$breadcrumbs->title = $item->title;
						$categories = explode('/', $item->category->path);
						foreach ($categories as $alias)
						{
							$breadcrumbs->path[] = K2Categories::getInstance($alias);
						}
					}
					break;

				case 'itemlist' :

					// Check for menu link
					$matchCategory = $active && isset($active->query['view']) && $active->query['view'] == 'itemlist' && isset($active->query['task']) && $active->query['task'] == 'category' && isset($active->query['id']) && $active->query['id'] == $id;

					// Handle depending on matches
					if ($matchCategory)
					{
						$breadcrumbs->title =   end($pathwayItems)->title;
						foreach ($pathwayItems as $pathwayItem)
						{
							$breadcrumbs->path[] = $pathwayItem;
						}
					}
					else
					{
						$category = K2Categories::getInstance($id);
						$breadcrumbs->title = $category->title;
						$categories = explode('/', $category->path);
						foreach ($categories as $alias)
						{
							$breadcrumbs->path[] = K2Categories::getInstance($alias);
						}
					}
					array_pop($breadcrumbs->path);
					break;
			}
		}
		else
		{

			$breadcrumbs->title = count($pathwayItems) ?   end($pathwayItems)->title : '';
			foreach ($pathwayItems as $pathwayItem)
			{
				$breadcrumbs->path[] = $pathwayItem;
			}
			array_pop($breadcrumbs->path);
		}

		return $breadcrumbs;
	}

	public static function getCategories($params, $type)
	{
		$application = JFactory::getApplication();
		$option = $application->input->get('option', '', 'cmd');
		$view = $application->input->get('view', '', 'cmd');
		$task = $application->input->get('task', '', 'cmd');
		$id = $application->input->get('id', 0, 'int');
		$endLevel = $params->get('end_level', NULL);
		$filter = ($type == 'default') ? $params->get('root_id') : $params->get('root_id2');
		$root = isset($filter->categories[0]) ? $filter->categories[0] : 0;

		$model = K2Model::getInstance('Categories');
		$model->setState('site', true);
		$model->setState('root', $root);
		$model->setState('sorting', 'ordering');
		$rows = $model->getRows();

		$categories = array();
		foreach ($rows as $row)
		{
			if ($row->id != $root)
			{
				$row->active = ($option == 'com_k2' && $view == 'itemlist' && $task == 'category' && $id == $row->id);
				$categories[] = $row;
			}

		}

		return $categories;
	}

	public static function getSearch($params)
	{
		$application = JFactory::getApplication();
		$search = new stdClass;
		$search->action = JRoute::_(K2HelperRoute::getSearchRoute());
		$search->text = $params->get('text', JText::_('K2_SEARCH'));
		$search->width = intval($params->get('width', 20));
		$search->maxLength = $search->width > 20 ? $search->width : 20;
		$search->button = $params->get('button');
		$search->buttonText = htmlspecialchars($params->get('button_text', JText::_('K2_SEARCH')));
		$search->imageButton = $params->get('imagebutton');
		$search->buttonPosition = $params->get('button_pos', 'left');
		$search->sef = $application->getCfg('sef');
		$search->filter = '';

		$filter = $params->get('category_id');

		if ($filter && isset($filter->enabled) && $filter->enabled)
		{
			$model = K2Model::getInstance('Categories');
			$categories = K2ModelCategories::getCategoryFilter($filter->categories, $filter->recursive, true);
			$search->filter = implode(',', $categories);
		}

		if ($params->get('liveSearch'))
		{
			$document = JFactory::getDocument();
			if ($document->getType() == 'html')
			{
				$document->addScript(JURI::root(true).'/administrator/components/com_k2/js/lib/underscore-min.js');
				$document->addScriptDeclaration('var K2Site = "'.JURI::root(true).'";');
			}
		}

		return $search;

	}

	public static function getTagCloud($params)
	{

		$model = K2Model::getInstance('Tags');
		$filter = $params->get('cloud_category');
		if ($filter)
		{
			$model->setState('categories', $filter->categories);
			$model->setState('recursive', $filter->recursive);
		}
		$model->setState('limit', $params->get('cloud_limit'));
		$tags = $model->getTagCloud();

		if (!count($tags))
		{
			return $tags;
		}

		$maximumFontSize = $params->get('max_size');
		$minimumFontSize = $params->get('min_size');
		$maximumOccurencies = $tags[0]->counter;
		$minimumOccurencies = $tags[count($tags) - 1]->counter;
		$spread = $maximumOccurencies - $minimumOccurencies;
		if ($spread == 0)
		{
			$spread = 1;
		}
		$step = ($maximumFontSize - $minimumFontSize) / ($spread);

		foreach ($tags as $tag)
		{
			$size = $minimumFontSize + (($tag->counter - $minimumOccurencies) * $step);
			$tag->size = ceil($size);
			// Legacy
			$tag->count = $tag->counter;
		}

		usort($tags, 'self::sortTagsByName');
		return $tags;
	}

	private static function sortTagsByName($a, $b)
	{
		return strcmp($a->name, $b->name);
	}

	public static function getCustomCode($params)
	{
		jimport('joomla.filesystem.file');
		$document = JFactory::getDocument();
		if ($params->get('parsePhp'))
		{
			$filename = tempnam(JPATH_SITE.'/cache/mod_k2_tools', 'tmp');
			$customCode = $params->get('customCode');
			JFile::write($filename, $customCode);
			ob_start();
			include ($filename);
			$output = ob_get_contents();
			ob_end_clean();
			JFile::delete($filename);
		}
		else
		{
			$output = $params->get('customCode');
		}
		if ($document->getType() != 'feed')
		{
			$dispatcher = JDispatcher::getInstance();
			if ($params->get('JPlugins'))
			{
				JPluginHelper::importPlugin('content');
				$row = new stdClass;
				$row->text = $output;
				$dispatcher->trigger('onContentPrepare', array('mod_k2_tools', &$row, &$params));
				$output = $row->text;
			}
			if ($params->get('K2Plugins'))
			{
				JPluginHelper::importPlugin('k2');
				$row = new stdClass;
				$row->text = $output;
				$dispatcher->trigger('onK2PrepareContent', array(&$row, &$params));
				$output = $row->text;
			}

		}
		return $output;
	}

}
