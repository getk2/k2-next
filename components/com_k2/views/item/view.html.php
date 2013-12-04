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
		$application = JFactory::getApplication();
		$id = $application->input->get('id', 0, 'int');
		$this->item = K2Items::getInstance($id);
		$this->print = $application->input->get('id', 0, 'int');
		$this->params = $application->getParams('com_k2');
		$this->params->merge($this->item->categoryParams);
		$this->params->merge($this->item->params);
		$this->setLayout('item');
		parent::display($tpl);
	}

}
