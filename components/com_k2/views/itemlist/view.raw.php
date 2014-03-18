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

require_once JPATH_SITE.'/components/com_k2/views/view.php';

/**
 * K2 itemlist view class
 */

class K2ViewItemlist extends K2View
{
	public function display($tpl = null)
	{
		// Get application
		$application = JFactory::getApplication();

		// Get input
		$task = $application->input->get('task', '', 'cmd');
		$offset = $application->input->get('limitstart', 0, 'int');
		$limit = $application->input->get('limit', 10, 'int');

		// Trigger the corresponding method
		if (method_exists($this, $task))
		{
			call_user_func(array($this, $task));
		}
		else
		{
			return JError::raiseError(404, JText::_('K2_NOT_FOUND'));
		}

		// Load the comments counters in a single query for all items
		$params = JComponentHelper::getParams('com_k2');
		if ($params->get('comments'))
		{
			K2Items::countComments($this->items);
		}

		// Plugins
		foreach ($this->items as $item)
		{
			$item->events = $item->getEvents('com_k2.itemlist.'.$task, $this->params, $offset);
		}

		// Pagination
		jimport('joomla.html.pagination');
		$this->pagination = new JPagination($this->total, $offset, $limit);

		// Display
		parent::display($tpl);
	}

	private function category()
	{
		// Get application
		$application = JFactory::getApplication();

		// Get input
		$id = $application->input->get('id', 0, 'int');
		$categories = $this->params->get('categories');
		$offset = $application->input->get('limitstart', 0, 'int');
		$limit = $application->input->get('limit', 10, 'int');

		// Get model
		$model = K2Model::getInstance('Items');
		$model->setState('site', true);

		// Single category
		if ($id)
		{
			// Get category
			$this->category = K2Categories::getInstance($id);

			// Check access
			$this->category->checkSiteAccess();

			// Merge menu params with category params
			$effectiveParams = $this->category->getEffectiveParams();
			$this->params->merge($effectiveParams);

			// Set model state
			$model->setState('category', $id);

		}
		// Multiple categories from menu item parameters
		else if ($categories)
		{
			$model->setState('category.filter', $categories);
		}

		// @TODO Apply menu settings. Since they will be common all tasks we need to wait

		// Get items
		$model->setState('limit', $limit);
		$model->setState('limitstart', $offset);
		$this->items = $model->getRows();

		// Count items
		$this->total = $model->countRows();

		// Set the layout
		$this->setLayout('category');
	}

	private function user()
	{
		// Get application
		$application = JFactory::getApplication();

		// Get input
		$id = $application->input->get('id', 0, 'int');
		$offset = $application->input->get('limitstart', 0, 'int');
		$limit = $application->input->get('limit', 10, 'int');

		// Get user
		$this->author = K2Users::getInstance($id);

		// Check access
		$this->author->checkSiteAccess();

		// @TODO Apply menu settings. Since they will be common all tasks we need to wait

		// Get items
		$model = K2Model::getInstance('Items');
		$model->setState('site', true);
		$model->setState('author', $id);
		$model->setState('limit', $limit);
		$model->setState('limitstart', $offset);
		$this->items = $model->getRows();

		// Count items
		$this->total = $model->countRows();

		// Set the layout
		$this->setLayout('user');

	}

	private function tag()
	{
		// Get application
		$application = JFactory::getApplication();

		// Get input
		$id = $application->input->get('id', 0, 'int');
		$offset = $application->input->get('limitstart', 0, 'int');
		$limit = $application->input->get('limit', 10, 'int');

		// Get tag
		$this->tag = K2Tags::getInstance($id);

		// Check access and publishing state
		$this->tag->checkSiteAccess();

		// @TODO Apply menu settings. Since they will be common all tasks we need to wait

		// Get items
		$model = K2Model::getInstance('Items');
		$model->setState('site', true);
		$model->setState('tag', $id);
		$model->setState('limit', $limit);
		$model->setState('limitstart', $offset);
		$this->items = $model->getRows();

		// Count items
		$this->total = $model->countRows();

		// Set the layout
		$this->setLayout('tag');

	}

	private function date()
	{
		// Get application
		$application = JFactory::getApplication();

		// Get input
		$year = $application->input->get('year', 0, 'int');
		$month = $application->input->get('month', 0, 'int');
		$day = $application->input->get('day', 0, 'int');
		$category = $application->input->get('category', 0, 'int');
		$offset = $application->input->get('limitstart', 0, 'int');
		$limit = $application->input->get('limit', 10, 'int');

		// Get items
		$model = K2Model::getInstance('Items');
		$model->setState('site', true);
		$model->setState('year', $year);
		$model->setState('month', $month);
		$model->setState('day', $day);
		$model->setState('category', $category);
		$model->setState('limit', $limit);
		$model->setState('limitstart', $offset);
		$this->items = $model->getRows();

		// Count items
		$this->total = $model->countRows();

		// Set the layout
		$this->setLayout('date');

	}

	private function search()
	{
		// Get application
		$application = JFactory::getApplication();

		// Get input
		$search = trim($application->input->get('searchword', '', 'string'));
		$offset = $application->input->get('limitstart', 0, 'int');
		$limit = $application->input->get('limit', 10, 'int');

		// Get items
		if ($search)
		{
			$model = K2Model::getInstance('Items');
			$model->setState('site', true);
			$model->setState('search', $search);
			$model->setState('limit', $limit);
			$model->setState('limitstart', $offset);
			$this->items = $model->getRows();

			// Count items
			$this->total = $model->countRows();
		}
		else
		{
			$this->items = array();
			$this->total = 0;
			$this->params->set('genericFeedIcon', false);
		}

		// Set the layout
		$this->setLayout('search');

	}

}
