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

/**
 * K2 base helper class.
 */

class K2Helper
{
	public static function prepare($row)
	{

		if (property_exists($row, 'created'))
		{
			$row->created = JHTML::_('date', $row->created, JText::_('K2_DATE_FORMAT'));
		}

		if (property_exists($row, 'modified'))
		{
			if ((int)$row->modified)
			{
				$row->modified = JHTML::_('date', $row->modified, JText::_('K2_DATE_FORMAT'));
			}
			else
			{
				$row->modified = JText::_('K2_NEVER');
			}
		}

		return $row;
	}

}
