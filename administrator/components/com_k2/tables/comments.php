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

require_once JPATH_ADMINISTRATOR.'/components/com_k2/tables/table.php';

class K2TableComments extends K2Table
{
	public function __construct($db)
	{
		parent::__construct('#__k2_comments', 'id', $db);
	}

	public function check()
	{
		if (JString::trim($this->text) == '')
		{
			$this->setError(JText::_('K2_COMMENT_MUST_HAVE_TEXT'));
			return false;
		}

		return true;
	}

}
