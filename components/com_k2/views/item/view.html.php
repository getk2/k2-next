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
		$offset = $application->input->get('offset', 0, 'int');
		$limit = $application->input->get('limit', 10, 'int');

		// Get item
		$this->item = K2Items::getInstance($id);
		
		// Check access
		$this->item->checkSiteAccess();

		// Set params
		$this->params->merge($this->item->categoryParams);
		$this->params->merge($this->item->params);

		// Trigger plugins
		$this->item->triggerPlugins('com_k2.item', $this->params, $offset);
		
		// @TODO Trigger comments events
		$this->item->events->K2CommentsBlock = '';
		
		// Comments pagination
		jimport('joomla.html.pagination');
		$this->pagination = new JPagination($this->item->numOfComments, $offset, $limit);

		// Set the layout
		$this->setLayout('item');

		// Display
		parent::display($tpl);
	}

}
