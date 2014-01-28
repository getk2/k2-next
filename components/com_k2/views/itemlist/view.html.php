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
		$offset = $application->input->get('offset', 0, 'int');
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

		// Plugins
		foreach ($this->items as $item)
		{
			$item->events = $item->getEvents('com_k2.itemlist.'.$task, $this->params, $offset);
		}

		// Pagination
		jimport('joomla.html.pagination');
		$this->pagination = new JPagination($this->total, $offset, $limit);

		// Set the layout
		$this->setLayout('itemlist');

		// Display
		parent::display($tpl);
	}

	private function category()
	{
		// Get application
		$application = JFactory::getApplication();

		// Get input
		$id = $application->input->get('id', 0, 'int');
		$offset = $application->input->get('offset', 0, 'int');
		$limit = $application->input->get('limit', 10, 'int');

		// Get category
		$this->category = K2Categories::getInstance($id);
		
		// Merge menu params with category params. Take care of inheritance
		if ($this->category->inheritance)
		{
			$masterCategory = K2Categories::getInstance($this->category->inheritance);
			$this->params->merge($masterCategory->params);
		}
		else
		{
			$this->params->merge($this->category->params);
		}

		// Get items
		$model = K2Model::getInstance('Items');
		$model->setState('site', true);
		$model->setState('category', $id);
		$model->setState('limit', $limit);
		$model->setState('limitstart', $offset);
		$this->items = $model->getRows();

		// Count items
		$this->total = $model->countRows();

		// Set title, metadata and pathway if the current menu is different from our page
		if (!$this->isActive)
		{
			$this->setTitle($this->category->title);
			$this->params->set('page_heading', $this->category->title);
			if ($this->category->metadata->get('description'))
			{
				$this->document->setDescription($this->category->metadata->get('description'));
			}
			if ($this->category->metadata->get('kewords'))
			{
				$this->document->setMetadata('keywords', $this->category->metadata->get('kewords'));
			}
			if ($this->category->metadata->get('robots'))
			{
				$this->document->setMetadata('robots', $this->category->metadata->get('robots'));
			}
			if ($this->category->metadata->get('author'))
			{
				$this->document->setMetadata('author', $this->category->metadata->get('author'));
			}
			$pathway = $application->getPathWay();
			$pathway->addItem($this->category->title, '');
		}
	}

	private function user()
	{
		// Get application
		$application = JFactory::getApplication();

		// Get input
		$id = $application->input->get('id', 0, 'int');
		$offset = $application->input->get('offset', 0, 'int');
		$limit = $application->input->get('limit', 10, 'int');

		// Get user
		$this->user = K2Users::getInstance($id);

		// Get items
		$model = K2Model::getInstance('Items');
		$model->setState('site', true);
		$model->setState('author', $id);
		$model->setState('limit', $limit);
		$model->setState('limitstart', $offset);
		$this->items = $model->getRows();

		// Count items
		$this->total = $model->countRows();

	}

	private function tag()
	{
		// Get application
		$application = JFactory::getApplication();

		// Get input
		$id = $application->input->get('id', 0, 'int');
		$offset = $application->input->get('offset', 0, 'int');
		$limit = $application->input->get('limit', 10, 'int');

		// Get tag
		$this->tag = K2Tags::getInstance($id);
		
		// Check access and publishing state
		$this->tag->checkSiteAccess();

		// Get items
		$model = K2Model::getInstance('Items');
		$model->setState('site', true);
		$model->setState('tag', $id);
		$model->setState('limit', $limit);
		$model->setState('limitstart', $offset);
		$this->items = $model->getRows();

		// Count items
		$this->total = $model->countRows();

		if (!$this->isActive)
		{
			$this->setTitle(JText::_('K2_DISPLAYING_ITEMS_BY_TAG').' '.$this->tag->name);
		}
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
		$offset = $application->input->get('offset', 0, 'int');
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

	}

	private function search()
	{
		// Get application
		$application = JFactory::getApplication();

		// Get input
		$search = $application->input->get('searchword', '', 'string');
		$offset = $application->input->get('offset', 0, 'int');
		$limit = $application->input->get('limit', 10, 'int');

		// Get items
		$model = K2Model::getInstance('Items');
		$model->setState('site', true);
		$model->setState('search', $search);
		$model->setState('limit', $limit);
		$model->setState('limitstart', $offset);
		$this->items = $model->getRows();
	}

}
