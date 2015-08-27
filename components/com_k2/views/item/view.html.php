<?php
/**
 * @version		3.0.0
 * @package		K2
 * @author		JoomlaWorks http://www.joomlaworks.net
 * @copyright	Copyright (c) 2006 - 2014 JoomlaWorks Ltd. All rights reserved.
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

		// Merge menu params with category params
		$effectiveParams = $this->item->category->getEffectiveParams();
		$this->params->merge($effectiveParams);

		// Merge params with item params
		$this->params->merge($this->item->params);

		// Get the image depending on params
		$this->item->image = $this->item->getImage($this->params->get('itemImgSize'));

		// Trigger plugins. We need to do this there in order to provide the correct context
		$this->item->events = $this->item->getEvents('com_k2.item', $this->params, 0);

		// Load head data for comments and inline editing if required
		if (($this->item->canEdit) || ($this->params->get('itemComments') && $this->params->get('comments') && empty($this->item->events->K2CommentsCounter) && empty($this->item->events->K2CommentsBlock)))
		{
			// Common
			JHtml::_('behavior.keepalive');
			$this->document->addScriptDeclaration('var K2SitePath = "'.JUri::root(true).'";');
			$this->document->addScriptDeclaration('var K2SessionToken = "'.JSession::getFormToken().'";');

			// Comments
			if ($this->params->get('itemComments') && $this->params->get('comments') && empty($this->item->events->K2CommentsCounter) && empty($this->item->events->K2CommentsBlock))
			{
				// Check if user can comment
				$this->user->canComment = $this->user->authorise('k2.comment.create', 'com_k2');

				// Load comments requirements
				$this->document->addScript(JURI::root(true).'/media/k2app/vendor/underscore/underscore-min.js');
				$this->document->addScript(JURI::root(true).'/media/k2app/vendor/backbone/backbone-min.js');
				$this->document->addScript(JURI::root(true).'/media/k2app/vendor/marionette/backbone.marionette.min.js');
				$this->document->addScript(JURI::root(true).'/media/k2app/app/sync.js');
				require_once JPATH_SITE.'/components/com_k2/helpers/captcha.php';
				K2HelperCaptcha::initialize();
			}

			// Inline editing
			if ($this->item->canEdit)
			{
				$this->document->addScript('//cdn.ckeditor.com/4.4.6/standard/ckeditor.js');
			}

		}

		// Get related items. We need to do this here since the parameter is related with the view
		if ($this->params->get('itemRelated'))
		{
			$this->item->related = $this->item->getRelated($this->params->get('itemRelatedLimit', 5));
			foreach($this->item->related as $related)
			{
				$related->image = $related->getImage($this->params->get('itemRelatedImageSize'));
			}
		}

		// Get latest from same author. We need to do this here since the parameter is related with the view
		if ($this->params->get('itemAuthorLatest'))
		{
			$this->item->author->latest = $this->item->getLatestByAuthor($this->params->get('itemAuthorLatestLimit', 5));
		}

		// Increase hits counter
		$this->item->hit();

		// Set metadata
		$this->setMetadata($this->item);

		// Set Facebook meta data
		if ($this->params->get('facebookMetadata'))
		{
			$this->document->setMetaData('og:url', $this->item->url);
			$this->document->setMetaData('og:title', $this->document->getTitle());
			$this->document->setMetaData('og:type', 'article');
			$this->document->setMetaData('og:description', $this->document->getDescription());
			$facebookImage = $this->item->getImage($this->params->get('facebookMetadataImageSize'));
			if ($facebookImage)
			{
				$this->document->setMetaData('og:image', $facebookImage->url);
			}

		}

		// Set the layout
		$this->setLayout('item');

		// Add the template path
		$this->addTemplatePath(JPATH_SITE.'/components/com_k2/templates/'.$this->item->category->template);
		$this->addTemplatePath(JPATH_SITE.'/templates/'.JFactory::getApplication()->getTemplate().'/html/com_k2/'.$this->item->category->template);

		// Display
		parent::display($tpl);
	}

}
