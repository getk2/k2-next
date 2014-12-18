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

class K2TableExtraFieldsGroups extends K2Table
{
	public function __construct($db)
	{
		parent::__construct('#__k2_extra_fields_groups', 'id', $db);
	}

	public function check()
	{
		if (JString::trim($this->name) == '')
		{
			$this->setError(JText::_('K2_EXTRA_FIELD_GROUP_MUST_HAVE_A_NAME'));
			return false;
		}

		return true;
	}

}
