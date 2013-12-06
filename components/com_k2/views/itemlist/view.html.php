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
require_once JPATH_ADMINISTRATOR.'/components/com_k2/models/items.php';

/**
 * K2 item view class
 */

class K2ViewItemlist extends K2View
{
	public function display($tpl = null)
	{
		// Get application
		$application = JFactory::getApplication();

		// Get input
		$offset = $application->input->get('offset', 0, 'int');
		$limit = $application->input->get('limit', 10, 'int');
		
		// Get items
		$model = K2Model::getInstance('Items');
		$model->setState('limit', 100);
		$model->setState('state', 1);
		$this->items = $model->getRows();

		// Plugins
		foreach ($this->items as $item)
		{
			$item->triggerPlugins('itemlist', $this->params, $offset);
		}

		// Set the layout
		$this->setLayout('itemlist');

		// Display
		parent::display($tpl);
	}

}
