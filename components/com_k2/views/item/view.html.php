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
require_once JPATH_ADMINISTRATOR.'/components/com_k2/resources/items.php';

/**
 * K2 item view class
 */

class K2ViewItem extends K2View
{
	public function display($tpl = null)
	{
		// Get application
		$application = JFactory::getApplication();

		// Get input
		$id = $application->input->get('id', 0, 'int');

		// Get item
		$this->item = K2Items::getInstance($id);

		// Check access
		$this->item->checkSiteAccess();

		// Merge menu params with category params. Take care of inheritance
		if ($this->item->category->inheritance)
		{
			$masterCategory = K2Categories::getInstance($this->item->category->inheritance);
			$this->params->merge($masterCategory->params);
		}
		else
		{
			$this->params->merge($this->category->params);
		}

		// Merge params with item params
		$this->params->merge($this->item->params);

		// Get the image depending on params
		$this->item->image = $this->item->getImage($this->params->get('itemImgSize'));
		
		// Trigger plugins
		$this->item->events = $this->item->getEvents('com_k2.item', $this->params, 0);

		// Get comments
		if ($this->params->get('itemComments') && $this->params->get('comments'))
		{
			// Check if user can comment
			$this->user->canComment = $this->user->authorise('k2.comment.create', 'com_k2');

			// Load comments requirements
			$document = JFactory::getDocument();
			$document->addScriptDeclaration('var K2SessionToken = "'.JSession::getFormToken().'";');
			$document->addScript(JURI::root(true).'/administrator/components/com_k2/js/lib/underscore-min.js');
			$document->addScript(JURI::root(true).'/administrator/components/com_k2/js/lib/backbone-min.js');
			$document->addScript(JURI::root(true).'/administrator/components/com_k2/js/lib/backbone.marionette.min.js');
			$document->addScript(JURI::root(true).'/administrator/components/com_k2/js/sync.js');

			// @TODO Trigger comments events
			$this->item->events->K2CommentsBlock = '';
		}
		
		// @TODO Trigger user events
		
		// Get related items
		$this->item->related = $this->item->getRelated($this->params->get('itemRelatedLimit'));
		
		// Get latest from same author
		$this->authorLatestItems = $this->item->getLatestByAuthor($this->params->get('itemAuthorLatestLimit'));
		
		// Increase hits counter
		$model = K2Model::getInstance('Statistics');
		$model->increaseItemHitsCounter($this->item->id);
		
		// Set title, metadata and pathway if the current menu is different from our page
		if (!$this->isActive)
		{
			$this->setTitle($this->item->title);
			$this->params->set('page_heading', $this->item->title);
			if ($this->item->metadata->get('description'))
			{
				$this->document->setDescription($this->item->metadata->get('description'));
			}
			if ($this->item->metadata->get('kewords'))
			{
				$this->document->setMetadata('keywords', $this->item->metadata->get('kewords'));
			}
			if ($this->item->metadata->get('robots'))
			{
				$this->document->setMetadata('robots', $this->item->metadata->get('robots'));
			}
			if ($this->item->metadata->get('author'))
			{
				$this->document->setMetadata('author', $this->item->metadata->get('author'));
			}
			$pathway = $application->getPathWay();
			$pathway->addItem($this->item->title, '');
		}

		// Set the layout
		$this->setLayout('item');

		// Display
		parent::display($tpl);
	}

}
