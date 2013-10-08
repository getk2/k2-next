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

require_once JPATH_ADMINISTRATOR.'/components/com_k2/controller.php';

/**
 * Tags JSON controller.
 */

class K2ControllerTags extends K2Controller
{
	public function search()
	{
		$model = $this->getModel($this->resourceType);
		$model->setState('search', $this->input->get('search'));
		$model->setState('sorting', $this->input->get('sorting'));
		$limit = $this->input->get('limit', 50);
		$page = $this->input->get('page', 1);
		$limitstart = ($page * $limit) - $limit;
		$model->setState('limit', $limit);
		$model->setState('limitstart', $limitstart);
		
		$response = new stdClass;
		$response->rows = $model->getRows();
		$response->total = $model->countRows();
		
		echo json_encode($response);
		return $this;
	}

}
