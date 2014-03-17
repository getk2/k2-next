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

		// Merge menu params with category params
		$effectiveParams = $this->item->category->getEffectiveParams();
		$this->params->merge($effectiveParams);

		// Merge params with item params
		$this->params->merge($this->item->params);

		// Get the image depending on params
		$this->item->image = $this->item->getImage($this->params->get('itemImgSize'));

		// Trigger plugins. We need to do this there in order to provide the correct context
		$this->item->events = $this->item->getEvents('com_k2.item', $this->params, 0);

		// Get comments
		if ($this->params->get('itemComments') && $this->params->get('comments') && empty($this->item->events->K2CommentsCounter) && empty($this->item->events->K2CommentsBlock))
		{
			// Check if user can comment
			$this->user->canComment = $this->user->authorise('k2.comment.create', 'com_k2');

			// Load comments requirements
			$this->document->addScriptDeclaration('var K2SessionToken = "'.JSession::getFormToken().'";');
			$this->document->addScript(JURI::root(true).'/administrator/components/com_k2/js/lib/underscore-min.js');
			$this->document->addScript(JURI::root(true).'/administrator/components/com_k2/js/lib/backbone-min.js');
			$this->document->addScript(JURI::root(true).'/administrator/components/com_k2/js/lib/backbone.marionette.min.js');
			$this->document->addScript(JURI::root(true).'/administrator/components/com_k2/js/sync.js');
			require_once JPATH_SITE.'/components/com_k2/helpers/captcha.php';
			K2HelperCaptcha::initialize();

		}

		// Get related items. We need to do this here since the parameter is related with the view
		if ($this->params->get('itemRelated'))
		{
			$this->item->related = $this->item->getRelated($this->params->get('itemRelatedLimit'));
		}

		// Get latest from same author. We need to do this here since the parameter is related with the view
		if ($this->params->get('itemAuthorLatest'))
		{
			$this->item->author->latest = $this->item->getLatestByAuthor($this->params->get('itemAuthorLatestLimit'), $this->item->id);
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
			$document->setMetaData('og:image', $facebookImage->url);
		}

		// Set the layout
		$this->setLayout('item');

		// Display
		parent::display($tpl);
	}

}
