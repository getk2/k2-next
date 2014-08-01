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

require_once JPATH_ADMINISTRATOR.'/components/com_k2/views/view.php';

/**
 * Media JSON view.
 */

class K2ViewMedia extends K2View
{

	public function edit($id)
	{
		// Set title
		$this->setTitle('K2_MEDIA_MANAGER');

		// Set menu
		$this->setMenu();

		// Render
		$this->render();
	}

}
