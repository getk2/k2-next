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
		$callback = $application->input->get('callback', '', 'cmd');

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

		// Increase hits counter
		$this->item->hit();

		// Response
		$response = new stdClass;
		$response->site = new stdClass;
		$response->site->url = JURI::root();
		$response->site->name = $application->getCfg('sitename');
		$response->item = $this->getJsonItem($this->item);

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

}
