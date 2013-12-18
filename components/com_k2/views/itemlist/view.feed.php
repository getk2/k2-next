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
			call_user_func(array(
				$this,
				$task
			));
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

		// Get input
		$id = $application->input->get('id', 0, 'int');
		$offset = $application->input->get('offset', 0, 'int');
		$limit = $application->input->get('limit', 10, 'int');

		// Get category
		$this->category = K2Categories::getInstance($id);

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

	private function getFeedItem($item)
	{
		// Get configuration
		$configuration = JFactory::getConfig();
		
		// Get params
		$params = JComponentHelper::getParams('com_k2');

		// Create the entry
		$entry = new JFeedItem;

		// Title
		$entry->title = $this->escape($item->title);

		// Link
		$entry->link = $item->link;

		// Build description
		$entry->description = '';

		// Image
		if ($params->get('feedItemImage') && $item->image)
		{
			$entry->description .= '<div class="K2FeedImage"><img src="'.$item->image.'" alt="'.$item->image_caption.'" /></div>';
		}

		// Introtext
		if ($params->get('feedItemIntroText'))
		{
			//Introtext word limit
			if ($params->get('feedTextWordLimit') && $item->introtext)
			{
				$item->introtext = K2HelperUtilities::wordLimit($item->introtext, $params->get('feedTextWordLimit'));
			}
			$entry->description .= '<div class="K2FeedIntroText">'.$item->introtext.'</div>';
		}

		// Fulltext
		if ($params->get('feedItemFullText') && $item->fulltext)
		{
			$entry->description .= '<div class="K2FeedFullText">'.$item->fulltext.'</div>';
		}

		// Tags
		if ($params->get('feedItemTags') && count($item->tags))
		{
			$entry->description .= '<div class="K2FeedTags"><ul>';
			foreach ($item->tags as $tag)
			{
				$entry->description .= '<li>'.$tag->name.'</li>';
			}
			$entry->description .= '<ul></div>';
		}

		// Media
		if ($params->get('feedItemVideo') && count($item->media))
		{
			$entry->description .= '<div class="K2FeedMedia"><ul>';
			foreach ($item->media as $video)
			{
				$entry->description .= '<li>'.$video->output.'</li>';
			}
			$entry->description .= '<ul></div>';
		}

		// Gallery
		if ($params->get('feedItemGallery') && $item->gallery)
		{
			$entry->description .= '<div class="K2FeedGallery">'.$item->gallery.'</div>';
		}

		// Attachments
		if ($params->get('feedItemAttachments') && count($item->attachments))
		{
			$entry->description .= '<div class="K2FeedAttachments"><ul>';
			foreach ($item->attachments as $attachment)
			{
				$entry->description .= '<li><a title="'.htmlspecialchars($attachment->title).'" href="'.$attachment->link.'">'.$attachment->name.'</a></li>';
			}
			$entry->description .= '<ul></div>';
		}
		
		// Creation date
		$entry->date = $item->created;
		
		// Category
		$entry->category = $item->category->name;
		
		// Author
		$entry->author = $item->author->name;
		if ($params->get('feedBogusEmail'))
		{
			$entry->authorEmail = $params->get('feedBogusEmail');
		}
		else
		{
			if ($configuration->get('feed_email') == 'author')
			{
				$entry->authorEmail = $item->author->email;
			}
			else
			{
				$entry->authorEmail = $configuration->get('mailfrom');
			}
		}

	

		return $entry;
	}

}
