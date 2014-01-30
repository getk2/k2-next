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

/**
 * K2 base view class
 */

class K2View extends JViewLegacy
{
	protected $isActive = true;

	public function __construct($config = array())
	{
		// Parent constructor
		parent::__construct($config);

		// Get application
		$application = JFactory::getApplication();

		// Set print variable
		$this->print = $application->input->getBool('print');

		// Load helpers
		$this->loadHelper('utilities');

		// Set the params
		$this->params = $application->getParams('com_k2');

		// Set the user
		$this->user = JFactory::getUser();

		// Get document
		$document = JFactory::getDocument();

		// Add CSS
		if ($document->getType() == 'html')
		{
			$document->addStyleSheet(JURI::root(true).'/components/com_k2/css/site.k2.css');
			if (version_compare(JVERSION, '3.2', 'ge'))
			{
				JHtml::_('jquery.framework');
			}
			else
			{
				$document->addScript('//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js');
			}
		}

		// Add template paths
		$template = $application->getTemplate();
		$this->addTemplatePath(JPATH_SITE.'/components/com_k2/templates/default');
		$this->addTemplatePath(JPATH_SITE.'/templates/'.$template.'/html/com_k2/default');
		if ($theme = $this->params->get('theme'))
		{
			$this->addTemplatePath(JPATH_SITE.'/components/com_k2/templates/'.$theme);
			$this->addTemplatePath(JPATH_SITE.'/templates/'.$template.'/html/com_k2/'.$theme);
		}

		// Set active (determine if the current menu Itemid is the same of the current view)
		$this->setActive();

		// Set page metadata and options correctly since Joomla! does not take care of it
		if ($this->isActive)
		{
			// Get menu
			$menu = $application->getMenu();

			// Get active
			$active = $menu->getActive();

			// Set page heading
			$this->params->def('page_heading', $this->params->get('page_title', $active->title));

			// Set browser title
			$this->setTitle($this->params->get('page_title'));

			// Set meta description
			if ($this->params->get('menu-meta_description'))
			{
				$document->setDescription($this->params->get('menu-meta_description'));
			}

			// Set meta keywords
			if ($this->params->get('menu-meta_keywords'))
			{
				$document->setMetadata('keywords', $this->params->get('menu-meta_keywords'));
			}

			// Set robots
			if ($this->params->get('robots'))
			{
				$document->setMetadata('robots', $this->params->get('robots'));
			}
		}
	}

	private function setActive()
	{
		$application = JFactory::getApplication();
		$menu = $application->getMenu();
		$active = $menu->getActive();
		if ($active)
		{
			foreach ($active->query as $key => $value)
			{
				if ($application->input->get($key) != $value)
				{
					$this->isActive = false;
					break;
				}
			}
		}
		else
		{
			$this->isActive = false;
		}
	}

	protected function setTitle($title)
	{
		$application = JFactory::getApplication();
		$document = JFactory::getDocument();
		if (empty($title))
		{
			$title = $application->getCfg('sitename');
		}
		elseif ($application->getCfg('sitename_pagetitles', 0) == 1)
		{
			$title = JText::sprintf('JPAGETITLE', $application->getCfg('sitename'), $title);
		}
		elseif ($application->getCfg('sitename_pagetitles', 0) == 2)
		{
			$title = JText::sprintf('JPAGETITLE', $title, $application->getCfg('sitename'));
		}
		$document->setTitle($title);
	}

	protected function setMetadata($resource)
	{
		$params = JComponentHelper::getParams('com_k2');

		if (!$this->isActive)
		{
			// Detect title
			$title = isset($resource->title) ? $resource->title : $resource->name;

			// Set the browser title according to the settings
			$this->setTitle($title);

			// Hide page heading since the current menu item is inherited
			$this->params->set('show_page_heading', false);

			// Update pathway
			$application = JFactory::getApplication();
			$pathway = $application->getPathWay();
			$pathway->addItem($title, '');

		}

		// Detect and set metadata
		if (isset($resource->metadata) && $metadata = $resource->metadata)
		{
			if ($metadata->get('description'))
			{
				$this->document->setDescription($metadata->get('description'));
			}
			if ($metadata->get('kewords'))
			{
				$this->document->setMetadata('keywords', $metadata->get('kewords'));
			}
			if ($metadata->get('robots'))
			{
				$this->document->setMetadata('robots', $metadata->get('robots'));
			}
			if ($metadata->get('author'))
			{
				$this->document->setMetadata('author', $metadata->get('author'));
			}
		}

		// If meta description is empty ( this means it was not set in the menu or item/category form ) then use the content of the resource
		if (!$this->document->getDescription())
		{
			$resourceType = get_class($resource);
			if ($resourceType == 'K2Items')
			{
				$description = $resource->introtext.' '.$resource->fulltext;
			}
			else if ($resourceType == 'K2Categories')
			{
				$description = $resource->description;
			}
			$description = strip_tags($description);
			$description = K2HelperUtilities::characterLimit($description, $params->get('metaDescLimit', 150));
			$this->document->setDescription($description);

		}
	}

	protected function getJsonItem($item)
	{
		$row = new stdClass;
		$row->id = $item->id;
		$row->title = $item->title;
		$row->alias = $item->alias;
		$row->link = $item->link;
		$row->url = $item->url;
		$row->catid = $item->catid;
		$row->introtext = $item->introtext;
		$row->fulltext = $item->fulltext;
		$row->extra_fields = $item->extra_fields;
		$row->created = $item->created;
		$row->created_by_alias = $item->created_by_alias;
		$row->modified = $item->modified;
		$row->featured = $item->featured;
		$row->image = $item->image;
		$row->images = $item->images;
		$row->media = $item->media;
		$row->galleries = $item->galleries;
		$row->hits = $item->hits;
		$row->category = new stdClass;
		$row->category->id = $item->category->id;
		$row->category->title = $item->category->title;
		$row->category->alias = $item->category->alias;
		$row->category->link = $item->category->link;
		$row->category->url = $item->category->url;
		$row->category->description = $item->category->description;
		$row->category->extra_fields = $item->category->extra_fields;
		$row->category->image = $item->category->image;
		$row->tags = $item->tags;
		$row->attachments = $item->attachments;
		$row->author = new stdClass;
		$row->author->name = $item->author->name;
		$row->author->link = $item->author->link;
		$row->author->url = $item->author->url;
		$row->author->image = $item->author->image;
		$row->author->description = $item->author->description;
		$row->author->site = $item->author->site;
		$row->author->gender = $item->author->gender;
		$row->author->extra_fields = $item->author->extra_fields;
		$row->numOfComments = $item->numOfComments;
		$row->events = $item->events;
		$row->language = $item->language;
		return $row;
	}

	protected function getFeedItem($item)
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
		$entry->link = $item->url;

		// Build description
		$entry->description = '';

		// Image
		if ($params->get('feedItemImage'))
		{
			$image = $item->getImage($params->get('feedImgSize'));
			if ($image)
			{
				$entry->description .= '<div class="K2FeedImage"><img src="'.$image->url.'" alt="'.$image->alt.'" /></div>';
			}
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
				$entry->description .= '<li><a href="'.$tag->url.'">'.$tag->name.'</a></li>';
			}
			$entry->description .= '</ul></div>';
		}

		// Media
		if ($params->get('feedItemVideo') && count($item->media))
		{
			$entry->description .= '<div class="K2FeedMedia"><ul>';
			foreach ($item->media as $video)
			{
				$entry->description .= '<li>'.$video->output.'</li>';
			}
			$entry->description .= '</ul></div>';
		}

		// Galleries
		if ($params->get('feedItemGallery') && count($item->galleries))
		{
			$entry->description .= '<div class="K2FeedGalleries"><ul>';
			foreach ($item->galleries as $gallery)
			{
				$entry->description .= '<li>'.$gallery->output.'</li>';
			}
			$entry->description .= '</ul></div>';
		}

		// Attachments
		if ($params->get('feedItemAttachments') && count($item->attachments))
		{
			$entry->description .= '<div class="K2FeedAttachments"><ul>';
			foreach ($item->attachments as $attachment)
			{
				$entry->description .= '<li><a title="'.htmlspecialchars($attachment->title).'" href="'.$attachment->url.'">'.$attachment->name.'</a></li>';
			}
			$entry->description .= '</ul></div>';
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

		// Return feed item
		return $entry;
	}

}
