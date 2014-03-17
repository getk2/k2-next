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

class K2ViewLatest extends K2View
{
	public function display($tpl = null)
	{
		// Get source
		$source = $this->params->get('source');

		// Trigger the corresponding method
		if (method_exists($this, $source))
		{
			call_user_func(array($this, $source));
		}
		else
		{
			return JError::raiseError(404, JText::_('K2_NOT_FOUND'));
		}

		// Plugins
		foreach ($this->blocks as $block)
		{
			foreach ($block->items as $item)
			{
				$item->events = $item->getEvents('com_k2.latest', $this->params, 0, $this->params->get('latestItemK2Plugins'));
			}
		}

		// Set the layout
		$this->setLayout('latest');

		// Display
		parent::display($tpl);
	}

	private function categories()
	{
		// Get application
		$application = JFactory::getApplication();

		// Get filter
		$filter = $this->params->get('categoryIDs');

		// Get categories model
		$model = K2Model::getInstance('Categories');
		$model->setState('site', true);
		$model->setState('limit', 0);
		$model->setState('limitstart', 0);

		// Apply filtering if enabled
		if ($filter['enabled'])
		{
			$categoryIds = K2ModelCategories::getCategoryFilter($filter['categories'], $filter['recursive'], true);
			if (count($categoryIds))
			{
				$model->setState('id', $categoryIds);
			}
			else
			{
				$this->categories = array();
				return;
			}

		}
		$this->categories = $model->getRows();

		foreach ($this->categories as $category)
		{
			// Get items model
			$model = K2Model::getInstance('Items');
			$model->setState('site', true);
			$model->setState('recursive', false);
			$model->setState('limit', $this->params->get('latestItemsLimit', 10));
			$model->setState('limitstart', 0);
			$model->setState('category', $category->id);
			$category->items = $model->getRows();
		}

		$this->blocks = $this->categories;
	}

	private function users()
	{
		// Get application
		$application = JFactory::getApplication();

		// Get filter
		$userIds = $this->params->get('userIDs');

		// Get categories model
		$model = K2Model::getInstance('Users');
		$model->setState('site', true);
		$model->setState('limit', 0);
		$model->setState('limitstart', 0);
		$model->setState('id', $userIds);
		$this->users = $model->getRows();

		foreach ($this->users as $user)
		{
			// Get items model
			$model = K2Model::getInstance('Items');
			$model->setState('site', true);
			$model->setState('recursive', false);
			$model->setState('limit', $this->params->get('latestItemsLimit', 10));
			$model->setState('limitstart', 0);
			$model->setState('author', $user->id);
			$user->items = $model->getRows();
		}

		$this->blocks = $this->users;

	}

}
