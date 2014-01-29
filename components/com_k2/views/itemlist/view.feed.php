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
 * K2 itemlist feed view class
 */

class K2ViewItemlist extends K2View
{
	public function display($tpl = null)
	{
		// Get application
		$application = JFactory::getApplication();

		// Get document
		$document = JFactory::getDocument();

		// Get params
		$params = $application->getParams('com_k2');

		// Get global configuration
		$configuration = JFactory::getConfig();

		// Get input
		$task = $application->input->get('task', '', 'cmd');
		$offset = $application->input->get('offset', 0, 'int');
		$limit = $application->input->get('limit', 10, 'int');

		// Trigger the corresponding subview
		if (method_exists($this, $task))
		{
			call_user_func(array($this, $task));
		}
		else
		{
			return JError::raiseError(404, JText::_('K2_NOT_FOUND'));
		}

		// Add items to feed
		foreach ($this->items as $item)
		{
			// Add item
			$document->addItem($this->getFeedItem($item));
		}
	}

	private function category()
	{
		// Get application
		$application = JFactory::getApplication();

		// Get params
		$params = JComponentHelper::getParams('com_k2');

		// Get input
		$id = $application->input->get('id', 0, 'int');
		$categories = $this->params->get('categories');
		$limit = $params->get('limit', 10, 'int');

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
		$model->setState('limitstart', 0);
		$this->items = $model->getRows();
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

	private function module()
	{
		// Import module helper
		jimport('joomla.application.module.helper');

		// Get application
		$application = JFactory::getApplication();

		// Get input
		$id = $application->input->get('id', 0, 'int');
		$offset = $application->input->get('offset', 0, 'int');
		$limit = $application->input->get('limit', 10, 'int');

		if ($id)
		{
			$module = K2HelperUtilities::getModule($id);
			if ($module)
			{
				require_once JPATH_SITE.'/modules/mod_k2_content/helper.php';
				$this->items = ModK2ContentHelper::getItems($module->params);
			}
		}
	}


}
