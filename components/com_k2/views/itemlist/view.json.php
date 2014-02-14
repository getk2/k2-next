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

		// Get input
		$task = $application->input->get('task', '', 'cmd');
		$callback = $application->input->get('callback', '', 'cmd');

		// Trigger the corresponding subview
		if (method_exists($this, $task))
		{
			call_user_func(array($this, $task));
		}
		else
		{
			return JError::raiseError(404, JText::_('K2_NOT_FOUND'));
		}

		// Response
		$response = new stdClass;
		$response->site = new stdClass;
		$response->site->url = JURI::root();
		$response->site->name = $application->getCfg('sitename');
		$response->items = array();

		// Add items to JSON
		foreach ($this->items as $item)
		{
			// Add item
			$response->items[] = $this->getJsonItem($item);
		}

		// Encode response
		$response = json_encode($response);

		// Output
		if ($callback)
		{
			$this->document->setMimeEncoding('application/javascript');
			echo $callback.'('.$response.')';
		}
		else
		{
			echo $response;
		}

	}

	private function category()
	{
		// Get application
		$application = JFactory::getApplication();

		// Get input
		$id = $application->input->get('id', 0, 'int');
		$categories = $this->params->get('categories');
		$limit = $this->params->get('feedLimit', 10, 'int');

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
		$limit = $this->params->get('feedLimit', 10, 'int');

		// Get user
		$this->author = K2Users::getInstance($id);

		// Check access
		$this->author->checkSiteAccess();

		// Get items
		$model = K2Model::getInstance('Items');
		$model->setState('site', true);
		$model->setState('author', $id);
		$model->setState('limit', $limit);
		$model->setState('limitstart', 0);
		$this->items = $model->getRows();
	}

	private function tag()
	{
		// Get application
		$application = JFactory::getApplication();

		// Get input
		$id = $application->input->get('id', 0, 'int');
		$limit = $this->params->get('feedLimit', 10, 'int');

		// Get tag
		$this->tag = K2Tags::getInstance($id);

		// Check access and publishing state
		$this->tag->checkSiteAccess();

		// Get items
		$model = K2Model::getInstance('Items');
		$model->setState('site', true);
		$model->setState('tag', $id);
		$model->setState('limit', $limit);
		$model->setState('limitstart', 0);
		$this->items = $model->getRows();
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
		$limit = $this->params->get('feedLimit', 10, 'int');

		// Get items
		$model = K2Model::getInstance('Items');
		$model->setState('site', true);
		$model->setState('year', $year);
		$model->setState('month', $month);
		$model->setState('day', $day);
		$model->setState('category', $category);
		$model->setState('limit', $limit);
		$model->setState('limitstart', 0);
		$this->items = $model->getRows();
	}

	private function search()
	{
		// Get application
		$application = JFactory::getApplication();

		// Get input
		$search = $application->input->get('searchword', '', 'string');
		$limit = $this->params->get('feedLimit', 10, 'int');

		// Get items
		$model = K2Model::getInstance('Items');
		$model->setState('site', true);
		$model->setState('search', $search);
		$model->setState('limit', $limit);
		$model->setState('limitstart', 0);
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
