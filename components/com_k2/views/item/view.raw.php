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

		// Get related items. We need to do this here since the parameter is related with the view
		if ($this->params->get('itemRelated'))
		{
			$this->item->related = $this->item->getRelated($this->params->get('itemRelatedLimit', 5));
		}

		// Get latest from same author. We need to do this here since the parameter is related with the view
		if ($this->params->get('itemAuthorLatest'))
		{
			$this->item->author->latest = $this->item->getLatestByAuthor($this->params->get('itemAuthorLatestLimit', 5));
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
