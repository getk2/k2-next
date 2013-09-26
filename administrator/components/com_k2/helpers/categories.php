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

require_once JPATH_ADMINISTRATOR.'/components/com_k2/helpers/helper.php';

/**
 * K2 items helper class.
 */

class K2HelperCategories extends K2Helper
{

	public static function prepare($row)
	{
		// Prepare generic properties like dates and authors
		$row = parent::prepare($row);

		// Prepare specific properties
		$row->link = '#categories/edit/'.$row->id;
		JFilterOutput::objectHTMLSafe($row, ENT_QUOTES, array(
			'plugins',
			'params',
			'rules'
		));
		return $row;
	}

}
