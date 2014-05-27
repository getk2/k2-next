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
		$this->offset = $application->input->get('limitstart', 0, 'int');
		$this->limit = $application->input->get('limit', 10, 'int');

		// Trigger the corresponding method
		if (method_exists($this, $task))
		{
			call_user_func(array($this, $task));
		}
		else
		{
			throw new Exception(JText::_('K2_NOT_FOUND'), 404);
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
		$this->pagination = new JPagination($this->total, $this->offset, $this->limit);

		// Display
		parent::display($tpl);
	}

	private function category()
	{
		// Get and count items using parent function
		$this->getCategoryItems(true);

		// If we have a single category, merge the params and set metadata
		if (isset($this->category))
		{
			// Merge menu params with category params
			$effectiveParams = $this->category->getEffectiveParams();
			$this->params->merge($effectiveParams);
		}

		// Set the layout
		$this->setLayout('category');
	}

	private function user()
	{
		// Get and count items using parent function
		$this->getUserItems(true);

		// Set the layout
		$this->setLayout('user');
	}

	private function tag()
	{
		// Get and count items using parent function
		$this->getTagItems(true);

		// Set the layout
		$this->setLayout('tag');

	}

	private function date()
	{
		// Get and count items using parent function
		$this->getDateItems(true);

		// Set the layout
		$this->setLayout('date');

	}

	private function search()
	{
		// Get and count items using parent function
		$this->getSearchItems(true);

		// Set the layout
		$this->setLayout('search');
	}

}
