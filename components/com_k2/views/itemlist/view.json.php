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

		// Get input
		$task = $application->input->get('task', '', 'cmd');
		$this->offset = $application->input->get('limitstart', 0, 'int');
		$this->limit = $application->input->get('limit', 10, 'int');
		$callback = $application->input->get('callback', '', 'cmd');

		// Trigger the corresponding subview
		if (method_exists($this, $task))
		{
			call_user_func(array($this, $task));
		}
		else
		{
			return JError::raiseError(404, JText::_('K2_NOT_FOUND'));
		}

		// Response
		$response = new stdClass;
		$response->site = new stdClass;
		$response->site->url = JURI::root();
		$response->site->name = $application->getCfg('sitename');
		$response->items = array();

		// Load the comments counters in a single query for all items
		$params = JComponentHelper::getParams('com_k2');
		if ($params->get('comments'))
		{
			K2Items::countComments($this->items);
		}

		// Add items to JSON
		foreach ($this->items as $item)
		{
			// Add item
			$response->items[] = $this->getJsonItem($item);
		}

		// Encode response
		$response = json_encode($response);

		// Output
		if ($callback)
		{
			$this->document->setMimeEncoding('application/javascript');
			echo $callback.'('.$response.')';
		}
		else
		{
			echo $response;
		}

	}

	private function category()
	{
		$this->getCategoryItems();
	}

	private function user()
	{
		$this->getUserItems();
	}

	private function tag()
	{
		$this->getTagItems();
	}

	private function date()
	{
		$this->getDateItems();
	}

	private function search()
	{
		$this->getSearchItems();
	}

	private function module()
	{
		$this->getModuleItems();
	}

}
