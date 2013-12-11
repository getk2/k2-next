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

class ModK2ToolsHelper
{
	public static function getArchive($params)
	{
		$model = K2Model::getInstance('Items');
		$model->setState('site', true);
		$model->setState('category', $params->get('archiveCategory', 0));
		$model->setState('sorting', 'created');
		$rows = $model->getArchive();
		$months = array(
			JText::_('K2_JANUARY'),
			JText::_('K2_FEBRUARY'),
			JText::_('K2_MARCH'),
			JText::_('K2_APRIL'),
			JText::_('K2_MAY'),
			JText::_('K2_JUNE'),
			JText::_('K2_JULY'),
			JText::_('K2_AUGUST'),
			JText::_('K2_SEPTEMBER'),
			JText::_('K2_OCTOBER'),
			JText::_('K2_NOVEMBER'),
			JText::_('K2_DECEMBER'),
		);
		foreach ($rows as $row)
		{
			$row->numOfItems = '';
			if ($params->get('archiveItemsCounter'))
			{
				$row->numOfItems = self::countArchiveItems($row->month, $row->year, $params->get('archiveCategory'));
			}
			$row->name = $months[($row->month) - 1];
			$row->link = JRoute::_(K2HelperRoute::getDateRoute($row->year, $row->month, null, $params->get('archiveCategory')));
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
		$model->setState('category', $params->get('authors_module_category', 0));
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
					$model->setState('sorting', 'created');
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

		$application = JFactory::getApplication();
		$month = $application->input->get('month', 0, 'int');
		$year = $application->input->get('year', 0, 'int');

		$months = array(
			JText::_('K2_JANUARY'),
			JText::_('K2_FEBRUARY'),
			JText::_('K2_MARCH'),
			JText::_('K2_APRIL'),
			JText::_('K2_MAY'),
			JText::_('K2_JUNE'),
			JText::_('K2_JULY'),
			JText::_('K2_AUGUST'),
			JText::_('K2_SEPTEMBER'),
			JText::_('K2_OCTOBER'),
			JText::_('K2_NOVEMBER'),
			JText::_('K2_DECEMBER'),
		);
		$days = array(
			JText::_('K2_SUN'),
			JText::_('K2_MON'),
			JText::_('K2_TUE'),
			JText::_('K2_WED'),
			JText::_('K2_THU'),
			JText::_('K2_FRI'),
			JText::_('K2_SAT'),
		);

		$calendar = new K2Calendar();
		$calendar->category = $params->get('calendarCategory', 0);
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

		$breadcrumbs = new stdClass;
		$breadcrumbs->title = '';
		$breadcrumbs->path = array();
		$breadcrumbs->home = $params->get('home', JText::_('K2_HOME'));
		$breadcrumbs->separator = $params->get('seperator', '&raquo;');

		if ($option == 'com_k2' && $view == 'item' || ($view == 'itemlist' && $task == 'category'))
		{

			switch ($view)
			{
				case 'item' :
					$item = K2Items::getInstance($id);
					$breadcrumbs->title = $item->title;
					$categories = explode('/', $item->category->path);
					foreach ($categories as $alias)
					{
						$breadcrumbs->path[] = K2Categories::getInstance($alias);
					}
					break;

				case 'itemlist' :
					$category = K2Categories::getInstance($id);
					$breadcrumbs->title = $category->title;
					$categories = explode('/', $category->path);
					foreach ($categories as $alias)
					{
						$breadcrumbs->path[] = K2Categories::getInstance($alias);
					}
					break;
			}

		}
		else
		{
			$document = JFactory::getDocument();
			$breadcrumbs->title = $document->getTitle();
			$pathway = $application->getPathway();
			$items = $pathway->getPathWay();
			foreach ($items as $item)
			{
				$item->title = $item->name;
				$breadcrumbs->path[] = $item;
			}
			array_pop($breadcrumbs->path);
		}

		return $breadcrumbs;
	}

	public static function getCategories($params)
	{
		$application = JFactory::getApplication();
		$option = $application->input->get('option', '', 'cmd');
		$view = $application->input->get('view', '', 'cmd');
		$task = $application->input->get('task', '', 'cmd');
		$id = $application->input->get('id', 0, 'int');
		$endLevel = $params->get('end_level', NULL);

		K2Model::addIncludePath(JPATH_ADMINISTRATOR.'/components/com_k2/models');
		$model = K2Model::getInstance('Categories');
		$model->setState('site', true);
		$model->setState('root', $params->get('root_id', 1));
		$model->setState('sorting', 'ordering');
		$categories = $model->getRows();
		foreach ($categories as $category)
		{
			$category->active = ($option == 'com_k2' && $view == 'itemlist' && $task == 'category' && $id == $category->id);
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

		if ($params->get('catfilter'))
		{
			$categoryId = $params->get('category_id', NULL);
			if (!is_null($categoryId))
			{
				if (!is_array($categoryId))
				{
					$categories = array($categoryId);
				}
				else
				{
					$categories = $categoryId;
				}
				$model = K2Model::getInstance('Categories');
				$model->setState('site', true);
				$model->setState('id', $categories);
				$filter = $model->getRows();
				$search->filter = implode(',', $filter);
			}
		}

		return $search;

	}

	public static function getTagCloud($params)
	{
		$categories = $params->get('cloud_category');
		$categories = array_filter($categories);
		if ($categories)
		{
			if ($params->get('cloud_category_recursive'))
			{
				$children = array();
				$model = K2Model::getInstance('Categories');
				foreach ($categories as $categoryId)
				{
					$model->setState('site', true);
					$model->setState('id', $categories);
					$rows = $model->getRows();
					foreach ($rows as $row)
					{
						$children[] = $row->id;
					}
				}
				$categories = array_merge($categories, $children);
				$categories = array_unique($categories);

			}
			$filter = array_intersect($categories, K2ModelCategories::getAuthorised());
		}
		else
		{
			$filter = K2ModelCategories::getAuthorised();
		}

		if (empty($filter))
		{
			return array();
		}
		$model = K2Model::getInstance('Tags');
		$model->setState('categories', $filter);
		$tags = $model->getTagCloud();

		usort($tags, 'self::sortTags');
		$limit = (int)$params->get('cloud_limit');
		if ($limit)
		{
			$tags = array_slice($tags, 0, $params->get('cloud_limit'));
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
			$tag->link = K2HelperRoute::getTagRoute($tag->id.':'.$tag->alias);
			$size = $minimumFontSize + (($tag->counter - $minimumOccurencies) * $step);
			$tag->size = ceil($size);
		}
		return $tags;
	}

	private static function sortTags($a, $b)
	{
		if ((int)$a->counter == (int)$b->counter)
		{
			return 0;
		}
		return ((int)$a->counter > (int)$b->counter) ? -1 : 1;
	}

	/*

	 public static function getSearchCategoryFilter(&$params)
	 {
	 $result = '';
	 $cid = $params->get('category_id', NULL);
	 if ($params->get('catfilter'))
	 {
	 if (!is_null($cid))
	 {
	 if (is_array($cid))
	 {
	 if ($params->get('getChildren'))
	 {
	 $itemListModel = K2Model::getInstance('Itemlist', 'K2Model');
	 $categories = $itemListModel->getCategoryTree($cid);
	 $result = @implode(',', $categories);
	 }
	 else
	 {
	 JArrayHelper::toInteger($cid);
	 $result = implode(',', $cid);
	 }

	 }
	 else
	 {
	 if ($params->get('getChildren'))
	 {
	 $itemListModel = K2Model::getInstance('Itemlist', 'K2Model');
	 $categories = $itemListModel->getCategoryTree($cid);
	 $result = @implode(',', $categories);
	 }
	 else
	 {
	 $result = (int)$cid;
	 }

	 }
	 }
	 }

	 return $result;
	 }

	 public static function hasChildren($id)
	 {

	 $mainframe = JFactory::getApplication();
	 $user = JFactory::getUser();
	 $aid = (int)$user->get('aid');
	 $id = (int)$id;
	 $db = JFactory::getDBO();
	 $query = "SELECT * FROM #__k2_categories  WHERE parent={$id} AND published=1 AND trash=0 ";
	 if (K2_JVERSION != '15')
	 {
	 $query .= " AND access IN(".implode(',', $user->getAuthorisedViewLevels()).") ";
	 if ($mainframe->getLanguageFilter())
	 {
	 $languageTag = JFactory::getLanguage()->getTag();
	 $query .= " AND language IN (".$db->Quote($languageTag).", ".$db->Quote('*').") ";
	 }

	 }
	 else
	 {
	 $query .= " AND access <= {$aid}";
	 }

	 $db->setQuery($query);
	 $rows = $db->loadObjectList();
	 if ($db->getErrorNum())
	 {
	 echo $db->stderr();
	 return false;
	 }

	 if (count($rows))
	 {
	 return true;
	 }
	 else
	 {
	 return false;
	 }
	 }

	 public static function treerecurse(&$params, $id = 0, $level = 0, $begin = false)
	 {

	 static $output;
	 if ($begin)
	 {
	 $output = '';
	 }
	 $mainframe = JFactory::getApplication();
	 $root_id = (int)$params->get('root_id');
	 $end_level = $params->get('end_level', NULL);
	 $id = (int)$id;
	 $catid = JRequest::getInt('id');
	 $option = JRequest::getCmd('option');
	 $view = JRequest::getCmd('view');

	 $user = JFactory::getUser();
	 $aid = (int)$user->get('aid');
	 $db = JFactory::getDBO();

	 switch ($params->get('categoriesListOrdering'))
	 {

	 case 'alpha' :
	 $orderby = 'name';
	 break;

	 case 'ralpha' :
	 $orderby = 'name DESC';
	 break;

	 case 'order' :
	 $orderby = 'ordering';
	 break;

	 case 'reversedefault' :
	 $orderby = 'id DESC';
	 break;

	 default :
	 $orderby = 'id ASC';
	 break;
	 }

	 if (($root_id != 0) && ($level == 0))
	 {
	 $query = "SELECT * FROM #__k2_categories WHERE parent={$root_id} AND published=1 AND trash=0 ";

	 }
	 else
	 {
	 $query = "SELECT * FROM #__k2_categories WHERE parent={$id} AND published=1 AND trash=0 ";
	 }

	 if (K2_JVERSION != '15')
	 {
	 $query .= " AND access IN(".implode(',', $user->getAuthorisedViewLevels()).") ";
	 if ($mainframe->getLanguageFilter())
	 {
	 $languageTag = JFactory::getLanguage()->getTag();
	 $query .= " AND language IN (".$db->Quote($languageTag).", ".$db->Quote('*').") ";
	 }

	 }
	 else
	 {
	 $query .= " AND access <= {$aid}";
	 }

	 $query .= " ORDER BY {$orderby}";

	 $db->setQuery($query);
	 $rows = $db->loadObjectList();
	 if ($db->getErrorNum())
	 {
	 echo $db->stderr();
	 return false;
	 }

	 if ($level < intval($end_level) || is_null($end_level))
	 {
	 $output .= '<ul class="level'.$level.'">';
	 foreach ($rows as $row)
	 {
	 if ($params->get('categoriesListItemsCounter'))
	 {
	 $row->numOfItems = ' ('.modK2ToolsHelper::countCategoryItems($row->id).')';
	 }
	 else
	 {
	 $row->numOfItems = '';
	 }

	 if (($option == 'com_k2') && ($view == 'itemlist') && ($catid == $row->id))
	 {
	 $active = ' class="activeCategory"';
	 }
	 else
	 {
	 $active = '';
	 }

	 if (modK2ToolsHelper::hasChildren($row->id))
	 {
	 $output .= '<li'.$active.'><a href="'.urldecode(JRoute::_(K2HelperRoute::getCategoryRoute($row->id.':'.urlencode($row->alias)))).'"><span class="catTitle">'.$row->name.'</span><span class="catCounter">'.$row->numOfItems.'</span></a>';
	 modK2ToolsHelper::treerecurse($params, $row->id, $level + 1);
	 $output .= '</li>';
	 }
	 else
	 {
	 $output .= '<li'.$active.'><a href="'.urldecode(JRoute::_(K2HelperRoute::getCategoryRoute($row->id.':'.urlencode($row->alias)))).'"><span class="catTitle">'.$row->name.'</span><span class="catCounter">'.$row->numOfItems.'</span></a></li>';
	 }
	 }
	 $output .= '</ul>';
	 }

	 return $output;
	 }

	 public static function treeselectbox(&$params, $id = 0, $level = 0)
	 {

	 $mainframe = JFactory::getApplication();
	 $root_id = (int)$params->get('root_id2');
	 $option = JRequest::getCmd('option');
	 $view = JRequest::getCmd('view');
	 $category = JRequest::getInt('id');
	 $id = (int)$id;
	 $user = JFactory::getUser();
	 $aid = (int)$user->get('aid');
	 $db = JFactory::getDBO();
	 if (($root_id != 0) && ($level == 0))
	 {
	 $query = "SELECT * FROM #__k2_categories WHERE parent={$root_id} AND published=1 AND trash=0 ";
	 }
	 else
	 {
	 $query = "SELECT * FROM #__k2_categories WHERE parent={$id} AND published=1 AND trash=0 ";
	 }

	 if (K2_JVERSION != '15')
	 {
	 $query .= " AND access IN(".implode(',', $user->getAuthorisedViewLevels()).") ";
	 if ($mainframe->getLanguageFilter())
	 {
	 $languageTag = JFactory::getLanguage()->getTag();
	 $query .= " AND language IN (".$db->Quote($languageTag).", ".$db->Quote('*').") ";
	 }
	 }
	 else
	 {
	 $query .= " AND access <= {$aid}";
	 }

	 $query .= " ORDER BY ordering";

	 $db->setQuery($query);
	 $rows = $db->loadObjectList();
	 if ($db->getErrorNum())
	 {
	 echo $db->stderr();
	 return false;
	 }

	 if ($level == 0)
	 {
	 echo '
	 <div class="k2CategorySelectBlock '.$params->get('moduleclass_sfx').'">
	 <form action="'.JRoute::_('index.php').'" method="get">
	 <select name="category" onchange="window.location=this.form.category.value;">
	 <option value="'.JURI::base(true).'/">'.JText::_('K2_SELECT_CATEGORY').'</option>
	 ';
	 }
	 $indent = "";
	 for ($i = 0; $i < $level; $i++)
	 {
	 $indent .= '&ndash; ';
	 }

	 foreach ($rows as $row)
	 {
	 if (($option == 'com_k2') && ($category == $row->id))
	 {
	 $selected = ' selected="selected"';
	 }
	 else
	 {
	 $selected = '';
	 }
	 if (modK2ToolsHelper::hasChildren($row->id))
	 {
	 echo '<option value="'.urldecode(JRoute::_(K2HelperRoute::getCategoryRoute($row->id.':'.urlencode($row->alias)))).'"'.$selected.'>'.$indent.$row->name.'</option>';
	 modK2ToolsHelper::treeselectbox($params, $row->id, $level + 1);
	 }
	 else
	 {
	 echo '<option value="'.urldecode(JRoute::_(K2HelperRoute::getCategoryRoute($row->id.':'.urlencode($row->alias)))).'"'.$selected.'>'.$indent.$row->name.'</option>';
	 }
	 }

	 if ($level == 0)
	 {

	 echo '
	 </select>
	 <input name="option" value="com_k2" type="hidden" />
	 <input name="view" value="itemlist" type="hidden" />
	 <input name="task" value="category" type="hidden" />
	 <input name="Itemid" value="'.JRequest::getInt('Itemid').'" type="hidden" />';

	 // For Joom!Fish compatibility
	 if (JRequest::getCmd('lang'))
	 {
	 echo '<input name="lang" value="'.JRequest::getCmd('lang').'" type="hidden" />';
	 }

	 echo '
	 </form>
	 </div>
	 ';

	 }
	 }

	 public static function breadcrumbs($params)
	 {

	 $mainframe = JFactory::getApplication();
	 $array = array();
	 $view = JRequest::getCmd('view');
	 $id = JRequest::getInt('id');
	 $option = JRequest::getCmd('option');
	 $task = JRequest::getCmd('task');

	 $db = JFactory::getDBO();
	 $user = JFactory::getUser();
	 $aid = (int)$user->get('aid');

	 if ($option == 'com_k2')
	 {

	 switch ($view)
	 {

	 case 'item' :
	 if (K2_JVERSION != '15')
	 {
	 $languageCheck = '';
	 if ($mainframe->getLanguageFilter())
	 {
	 $languageTag = JFactory::getLanguage()->getTag();
	 $languageCheck = " AND language IN (".$db->Quote($languageTag).", ".$db->Quote('*').") ";
	 }
	 $query = "SELECT * FROM #__k2_items  WHERE id={$id} AND published=1 AND trash=0 AND access IN(".implode(',', $user->getAuthorisedViewLevels()).") {$languageCheck} AND EXISTS (SELECT * FROM #__k2_categories WHERE #__k2_categories.id= #__k2_items.catid AND published=1 AND access IN(".implode(',', $user->getAuthorisedViewLevels()).")  {$languageCheck} )";
	 }
	 else
	 {
	 $query = "SELECT * FROM #__k2_items  WHERE id={$id} AND published=1 AND trash=0 AND access<={$aid} AND EXISTS (SELECT * FROM #__k2_categories WHERE #__k2_categories.id= #__k2_items.catid AND published=1 AND access<={$aid})";
	 }
	 $db->setQuery($query);
	 $row = $db->loadObject();
	 if ($db->getErrorNum())
	 {
	 echo $db->stderr();
	 return false;
	 }
	 $title = $row->title;
	 $path = modK2ToolsHelper::getCategoryPath($row->catid);

	 break;

	 case 'itemlist' :
	 if ($task == 'category')
	 {

	 $query = "SELECT * FROM #__k2_categories  WHERE id={$id} AND published=1 AND trash=0 ";
	 if (K2_JVERSION != '15')
	 {
	 $query .= " AND access IN(".implode(',', $user->getAuthorisedViewLevels()).") ";
	 if ($mainframe->getLanguageFilter())
	 {
	 $languageTag = JFactory::getLanguage()->getTag();
	 $query .= " AND language IN (".$db->Quote($languageTag).", ".$db->Quote('*').") ";
	 }
	 }
	 else
	 {
	 $query .= " AND access <= {$aid}";
	 }

	 $db->setQuery($query);
	 $row = $db->loadObject();
	 if ($db->getErrorNum())
	 {
	 echo $db->stderr();
	 return false;
	 }
	 $title = $row->name;
	 $path = modK2ToolsHelper::getCategoryPath($row->parent);

	 }
	 else
	 {
	 $document = JFactory::getDocument();
	 $title = $document->getTitle();
	 $path = modK2ToolsHelper::getSitePath();
	 }
	 break;

	 case 'latest' :
	 $document = JFactory::getDocument();
	 $title = $document->getTitle();
	 $path = modK2ToolsHelper::getSitePath();
	 break;
	 }

	 }
	 else
	 {
	 $document = JFactory::getDocument();
	 $title = $document->getTitle();
	 $path = modK2ToolsHelper::getSitePath();
	 }

	 return array(
	 $path,
	 $title
	 );
	 }

	 public static function getSitePath()
	 {

	 $mainframe = JFactory::getApplication();
	 $pathway = $mainframe->getPathway();
	 $items = $pathway->getPathway();
	 $count = count($items);
	 $path = array();
	 for ($i = 0; $i < $count; $i++)
	 {
	 if (!empty($items[$i]->link))
	 {
	 $items[$i]->name = stripslashes(htmlspecialchars($items[$i]->name, ENT_QUOTES, 'UTF-8'));
	 $items[$i]->link = JRoute::_($items[$i]->link);
	 array_push($path, '<a href="'.JRoute::_($items[$i]->link).'">'.$items[$i]->name.'</a>');
	 }

	 }
	 return $path;

	 }

	 public static function getCategoryPath($catid)
	 {

	 static $array = array();
	 $mainframe = JFactory::getApplication();
	 $user = JFactory::getUser();
	 $aid = (int)$user->get('aid');
	 $catid = (int)$catid;
	 $db = JFactory::getDBO();
	 $query = "SELECT * FROM #__k2_categories WHERE id={$catid} AND published=1 AND trash=0 ";

	 if (K2_JVERSION != '15')
	 {
	 $query .= " AND access IN(".implode(',', $user->getAuthorisedViewLevels()).") ";
	 if ($mainframe->getLanguageFilter())
	 {
	 $languageTag = JFactory::getLanguage()->getTag();
	 $query .= " AND language IN (".$db->Quote($languageTag).", ".$db->Quote('*').") ";
	 }
	 }
	 else
	 {
	 $query .= " AND access <= {$aid}";
	 }

	 $db->setQuery($query);
	 $rows = $db->loadObjectList();
	 if ($db->getErrorNum())
	 {
	 echo $db->stderr();
	 return false;
	 }

	 foreach ($rows as $row)
	 {
	 array_push($array, '<a href="'.urldecode(JRoute::_(K2HelperRoute::getCategoryRoute($row->id.':'.urlencode($row->alias)))).'">'.$row->name.'</a>');
	 modK2ToolsHelper::getCategoryPath($row->parent);
	 }

	 return array_reverse($array);
	 }

	 public static function getCategoryChildren($catid)
	 {

	 static $array = array();
	 $mainframe = JFactory::getApplication();
	 $user = JFactory::getUser();
	 $aid = (int)$user->get('aid');
	 $catid = (int)$catid;
	 $db = JFactory::getDBO();
	 $query = "SELECT * FROM #__k2_categories WHERE parent={$catid} AND published=1 AND trash=0 ";
	 if (K2_JVERSION != '15')
	 {
	 $query .= " AND access IN(".implode(',', $user->getAuthorisedViewLevels()).") ";
	 if ($mainframe->getLanguageFilter())
	 {
	 $languageTag = JFactory::getLanguage()->getTag();
	 $query .= " AND language IN (".$db->Quote($languageTag).", ".$db->Quote('*').") ";
	 }
	 }
	 else
	 {
	 $query .= " AND access <= {$aid}";
	 }
	 $query .= " ORDER BY ordering ";

	 $db->setQuery($query);
	 $rows = $db->loadObjectList();
	 if ($db->getErrorNum())
	 {
	 echo $db->stderr();
	 return false;
	 }
	 foreach ($rows as $row)
	 {
	 array_push($array, $row->id);
	 if (modK2ToolsHelper::hasChildren($row->id))
	 {
	 modK2ToolsHelper::getCategoryChildren($row->id);
	 }
	 }
	 return $array;
	 }

	 public static function countArchiveItems($month, $year, $catid = 0)
	 {

	 $mainframe = JFactory::getApplication();
	 $user = JFactory::getUser();
	 $aid = (int)$user->get('aid');
	 $month = (int)$month;
	 $year = (int)$year;
	 $db = JFactory::getDBO();

	 $jnow = JFactory::getDate();
	 $now = K2_JVERSION == '15' ? $jnow->toMySQL() : $jnow->toSql();

	 $nullDate = $db->getNullDate();

	 $query = "SELECT COUNT(*) FROM #__k2_items WHERE MONTH(created)={$month} AND YEAR(created)={$year} AND published=1 AND ( publish_up = ".$db->Quote($nullDate)." OR publish_up <= ".$db->Quote($now)." ) AND ( publish_down = ".$db->Quote($nullDate)." OR publish_down >= ".$db->Quote($now)." ) AND trash=0 ";
	 if (K2_JVERSION != '15')
	 {
	 $query .= " AND access IN(".implode(',', $user->getAuthorisedViewLevels()).") ";
	 if ($mainframe->getLanguageFilter())
	 {
	 $languageTag = JFactory::getLanguage()->getTag();
	 $query .= " AND language IN (".$db->Quote($languageTag).", ".$db->Quote('*').") ";
	 }
	 }
	 else
	 {
	 $query .= " AND access <= {$aid}";
	 }
	 if ($catid > 0)
	 {
	 $query .= " AND catid={$catid}";
	 }
	 $db->setQuery($query);
	 $total = $db->loadResult();
	 return $total;

	 }

	 public static function countCategoryItems($id)
	 {

	 $mainframe = JFactory::getApplication();
	 $user = JFactory::getUser();
	 $aid = (int)$user->get('aid');
	 $id = (int)$id;
	 $db = JFactory::getDBO();

	 $jnow = JFactory::getDate();
	 $now = K2_JVERSION == '15' ? $jnow->toMySQL() : $jnow->toSql();

	 $nullDate = $db->getNullDate();

	 $query = "SELECT COUNT(*) FROM #__k2_items WHERE catid={$id} AND published=1 AND ( publish_up = ".$db->Quote($nullDate)." OR publish_up <= ".$db->Quote($now)." ) AND ( publish_down = ".$db->Quote($nullDate)." OR publish_down >= ".$db->Quote($now)." ) AND trash=0 ";
	 if (K2_JVERSION != '15')
	 {
	 $query .= " AND access IN(".implode(',', $user->getAuthorisedViewLevels()).") ";
	 if ($mainframe->getLanguageFilter())
	 {
	 $languageTag = JFactory::getLanguage()->getTag();
	 $query .= " AND language IN (".$db->Quote($languageTag).", ".$db->Quote('*').") ";
	 }
	 }
	 else
	 {
	 $query .= " AND access <= {$aid}";
	 }
	 $db->setQuery($query);
	 $total = $db->loadResult();
	 return $total;
	 }

	 public static function calendar($params)
	 {

	 $month = JRequest::getInt('month');
	 $year = JRequest::getInt('year');

	 $months = array(
	 JText::_('K2_JANUARY'),
	 JText::_('K2_FEBRUARY'),
	 JText::_('K2_MARCH'),
	 JText::_('K2_APRIL'),
	 JText::_('K2_MAY'),
	 JText::_('K2_JUNE'),
	 JText::_('K2_JULY'),
	 JText::_('K2_AUGUST'),
	 JText::_('K2_SEPTEMBER'),
	 JText::_('K2_OCTOBER'),
	 JText::_('K2_NOVEMBER'),
	 JText::_('K2_DECEMBER'),
	 );
	 $days = array(
	 JText::_('K2_SUN'),
	 JText::_('K2_MON'),
	 JText::_('K2_TUE'),
	 JText::_('K2_WED'),
	 JText::_('K2_THU'),
	 JText::_('K2_FRI'),
	 JText::_('K2_SAT'),
	 );

	 $cal = new MyCalendar;
	 $cal->category = $params->get('calendarCategory', 0);
	 $cal->setStartDay(1);
	 $cal->setMonthNames($months);
	 $cal->setDayNames($days);

	 if (($month) && ($year))
	 {
	 return $cal->getMonthView($month, $year);
	 }
	 else
	 {
	 return $cal->getCurrentMonthView();
	 }
	 }

	 public static function renderCustomCode($params)
	 {
	 jimport('joomla.filesystem.file');
	 $document = JFactory::getDocument();
	 if ($params->get('parsePhp'))
	 {
	 $filename = tempnam(JPATH_SITE.DS.'cache'.DS.'mod_k2_tools', 'tmp');
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
	 $row = new JObject();
	 $row->text = $output;
	 if (K2_JVERSION != '15')
	 {
	 $dispatcher->trigger('onContentPrepare', array(
	 'mod_k2_tools',
	 &$row,
	 &$params
	 ));
	 }
	 else
	 {
	 $dispatcher->trigger('onPrepareContent', array(
	 &$row,
	 &$params
	 ));
	 }
	 $output = $row->text;
	 }
	 if ($params->get('K2Plugins'))
	 {
	 JPluginHelper::importPlugin('k2');
	 $row = new JObject();
	 $row->text = $output;
	 $dispatcher->trigger('onK2PrepareContent', array(
	 &$row,
	 &$params
	 ));
	 $output = $row->text;
	 }

	 }
	 return $output;
	 }

	 }

	 class MyCalendar extends Calendar
	 {

	 var $category = null;

	 function getDateLink($day, $month, $year)
	 {

	 $mainframe = JFactory::getApplication();
	 $user = JFactory::getUser();
	 $aid = $user->get('aid');
	 $db = JFactory::getDBO();

	 $jnow = JFactory::getDate();
	 $now = K2_JVERSION == '15' ? $jnow->toMySQL() : $jnow->toSql();

	 $nullDate = $db->getNullDate();

	 $languageCheck = '';
	 if (K2_JVERSION != '15')
	 {
	 $accessCheck = " access IN(".implode(',', $user->getAuthorisedViewLevels()).") ";
	 if ($mainframe->getLanguageFilter())
	 {
	 $languageTag = JFactory::getLanguage()->getTag();
	 $languageCheck = " AND language IN (".$db->Quote($languageTag).", ".$db->Quote('*').") ";
	 }
	 }
	 else
	 {
	 $accessCheck = " access <= {$aid}";
	 }

	 $query = "SELECT COUNT(*) FROM #__k2_items WHERE YEAR(created)={$year} AND MONTH(created)={$month} AND DAY(created)={$day} AND published=1 AND ( publish_up = ".$db->Quote($nullDate)." OR publish_up <= ".$db->Quote($now)." ) AND ( publish_down = ".$db->Quote($nullDate)." OR publish_down >= ".$db->Quote($now)." ) AND trash=0 AND {$accessCheck} {$languageCheck} AND EXISTS(SELECT * FROM #__k2_categories WHERE id= #__k2_items.catid AND published=1 AND trash=0 AND {$accessCheck} {$languageCheck})";

	 $catid = $this->category;
	 if ($catid > 0)
	 $query .= " AND catid={$catid}";

	 $db->setQuery($query);
	 $result = $db->loadResult();
	 if ($db->getErrorNum())
	 {
	 echo $db->stderr();
	 return false;
	 }

	 if ($result > 0)
	 {
	 if ($catid > 0)
	 return JRoute::_(K2HelperRoute::getDateRoute($year, $month, $day, $catid));
	 else
	 return JRoute::_(K2HelperRoute::getDateRoute($year, $month, $day));
	 }
	 else
	 {
	 return false;
	 }
	 }

	 function getCalendarLink($month, $year)
	 {
	 $itemID = JRequest::getInt('Itemid');
	 if ($this->category > 0)
	 return JURI::root(true)."/index.php?option=com_k2&amp;view=itemlist&amp;task=calendar&amp;month={$month}&amp;year={$year}&amp;catid={$this->category}&amp;Itemid={$itemID}";
	 else
	 return JURI::root(true)."/index.php?option=com_k2&amp;view=itemlist&amp;task=calendar&amp;month=$month&amp;year=$year&amp;Itemid={$itemID}";
	 }*/

}
