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

		// Generate the view params depedning on the task prefix. This let's us have one common layout file for listing items
		$this->generateItemlistParams($task);

		// Load the comments counters in a single query for all items
		if ($this->params->get('comments'))
		{
			K2Items::countComments($this->items);
		}

		// Plugins
		foreach ($this->items as $item)
		{
			$item->events = $item->getEvents('com_k2.itemlist.'.$task, $this->params, $this->offset);
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
		$this->pagination = new JPagination($this->total, $this->offset, $this->limit);

		// Display
		parent::display($tpl);
	}

	private function category()
	{
		// Get and count items using parent function
		$this->getCategoryItems(true);

		if (isset($this->category))
		{
			// Set metadata
			$this->setMetadata($this->category);

			// Get children
			if ($this->params->get('subCategories'))
			{
				$this->category->children = $this->category->getChildren();
			}

			// Add the template path
			$this->addTemplatePath(JPATH_SITE.'/components/com_k2/templates/'.$this->category->template);
			$this->addTemplatePath(JPATH_SITE.'/templates/'.JFactory::getApplication()->getTemplate().'/html/com_k2/'.$this->category->template);

		}

		// Leading items
		$offset = 0;
		$length = (int)$this->params->get('num_leading_items');
		$this->leading = array_slice($this->items, $offset, $length);
		foreach ($this->leading as &$item)
		{
			$item->itemGroup = 'leading';
			$item->image = $item->getImage($this->params->get('leadingImgSize'));
		}

		// Primary
		$offset = (int)$this->params->get('num_leading_items');
		$length = (int)$this->params->get('num_primary_items');
		$this->primary = array_slice($this->items, $offset, $length);
		foreach ($this->primary as &$item)
		{
			$item->itemGroup = 'primary';
			$item->image = $item->getImage($this->params->get('primaryImgSize'));
		}

		// Secondary
		$offset = (int)($this->params->get('num_leading_items') + $this->params->get('num_primary_items'));
		$length = (int)$this->params->get('num_secondary_items');
		$this->secondary = array_slice($this->items, $offset, $length);
		foreach ($this->secondary as &$item)
		{
			$item->itemGroup = 'secondary';
			$item->image = $item->getImage($this->params->get('secondaryImgSize'));
		}

		// Links
		$offset = (int)($this->params->get('num_leading_items') + $this->params->get('num_primary_items') + $this->params->get('num_secondary_items'));
		$length = (int)$this->params->get('num_links');
		$this->links = array_slice($this->items, $offset, $length);
		foreach ($this->links as &$item)
		{
			$item->itemGroup = 'links';
			$item->image = $item->getImage($this->params->get('linksImgSize'));
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

		// Set metadata
		$this->setMetadata($this->date);

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
