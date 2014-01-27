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
 * Users JSON controller.
 */

class K2ControllerUsers extends K2Controller
{
	protected function getInputData()
	{
		$data = parent::getInputData();
		$data['description'] = JComponentHelper::filterText($this->input->get('description', '', 'raw'));
		return $data;
	}

}
