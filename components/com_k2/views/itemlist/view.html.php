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
 * K2 item view class
 */

class K2ViewItemlist extends K2View
{
	public function display($tpl = null)
	{
		// Get application
		$application = JFactory::getApplication();

		// Get input
		$task = $application->input->get('task', '', 'cmd');
		$id = $application->input->get('id', 0, 'int');
		$offset = $application->input->get('offset', 0, 'int');
		$limit = $application->input->get('limit', 10, 'int');

		// Get items
		$model = K2Model::getInstance('Items');
		$model->setState('site', true);
		$model->setState('limit', $limit);
		$model->setState('limitstart', $offset);
		if ($task == 'category')
		{
			$model->setState('category', $id);
		}
		else if ($task == 'tag')
		{
			$model->setState('tag', $id);
		}
		else if ($task == 'user')
		{
			$model->setState('author', $id);
		}
		$this->items = $model->getRows();

		// Plugins
		foreach ($this->items as $item)
		{
			$item->triggerPlugins('com_k2.itemlist', $this->params, $offset);
		}

		// Set the layout
		$this->setLayout('itemlist');

		// Display
		parent::display($tpl);
	}

}
