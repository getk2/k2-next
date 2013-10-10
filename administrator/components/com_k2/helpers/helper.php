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
			$row->createdDate = JHtml::_('date', $row->created, 'Y-m-d');
			$row->createdTime = JHtml::_('date', $row->created, 'H:i');
			$row->createdOn = JHtml::_('date', $row->created, JText::_('K2_DATE_FORMAT'));
		}

		if (property_exists($row, 'modified'))
		{
			if ((int)$row->modified > 0)
			{
				$row->modifiedOn = JHtml::_('date', $row->modified, JText::_('K2_DATE_FORMAT'));
			}
			else
			{
				$row->modifiedOn = JText::_('K2_NEVER');
			}
		}

		if (property_exists($row, 'publish_up'))
		{
			$row->publishUpDate = JHtml::_('date', $row->publish_up, 'Y-m-d');
			$row->publishUpTime = JHtml::_('date', $row->publish_up, 'H:i');
		}

		if (property_exists($row, 'publish_down'))
		{
			if ((int)$row->publish_down > 0)
			{
				$row->publishDownDate = JHtml::_('date', $row->publish_down, 'Y-m-d');
				$row->publishDownTime = JHtml::_('date', $row->publish_down, 'H:i');
			}
			else
			{
				$row->publishDownDate = '';
				$row->publishDownTime = '';
			}
		}

		if (property_exists($row, 'language') && property_exists($row, 'languageTitle') && empty($row->languageTitle))
		{

			$row->languageTitle = JText::_('K2_ALL');
		}

		return $row;
	}

}
