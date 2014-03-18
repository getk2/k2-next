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

		// Add feed link
		$this->feedLink = JRoute::_('&format=feed&limitstart=');

		// Add feed links to head
		if ($this->feedLinkToHead)
		{
			$attributes = array('type' => 'application/rss+xml', 'title' => 'RSS 2.0');
			$this->document->addHeadLink(JRoute::_('&format=feed&limitstart=&type=rss'), 'alternate', 'rel', $attributes);
			$attributes = array('type' => 'application/atom+xml', 'title' => 'Atom 1.0');
			$this->document->addHeadLink(JRoute::_('&format=feed&limitstart=&type=atom'), 'alternate', 'rel', $attributes);
		}

		// Pagination
		jimport('joomla.html.pagination');
		$this->pagination = new JPagination($this->total, $offset, $limit);

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

			// Set metadata
			$this->setMetadata($this->category);
		}

		// Set the flag for sending feed links to head
		$this->feedLinkToHead = $this->params->get('catFeedLink');

		// Set the layout
		$this->setLayout('category');
	}

	private function user()
	{
		// Get and count items using parent function
		$this->getUserItems(true);

		// Set the flag for sending feed links to head
		$this->feedLinkToHead = $this->params->get('userFeedLink');

		// Set metadata
		$this->setMetadata($this->author);

		// Set the layout
		$this->setLayout('user');
	}

	private function tag()
	{
		// Get and count items using parent function
		$this->getTagItems(true);

		// Set the flag for sending feed links to head
		$this->feedLinkToHead = $this->params->get('tagFeedLink');

		// Set metadata
		$this->setMetadata($this->tag);

		// Set the layout
		$this->setLayout('tag');

	}

	private function date()
	{
		// Get and count items using parent function
		$this->getDateItems(true);

		// Set the flag for sending feed links to head
		$this->feedLinkToHead = $this->params->get('genericFeedLink');

		// Set the layout
		$this->setLayout('date');

	}

	private function search()
	{
		// Get and count items using parent function
		$this->getSearchItems(true);

		// Set the flag for sending feed links to head
		$this->feedLinkToHead = $this->params->get('genericFeedLink');

		// Set the layout
		$this->setLayout('search');
	}

}
