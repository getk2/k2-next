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

require_once dirname(__FILE__).'/calendar.php';

class K2Calendar extends Calendar
{

	var $category = null;

	function getDateLink($day, $month, $year)
	{
		$model = K2Model::getInstance('Items');
		$model->setState('site', true);
		$model->setState('day', $day);
		$model->setState('month', $month);
		$model->setState('year', $year);
		if ($this->category)
		{
			$model->setState('category', $this->category);
		}
		$result = $model->countRows();
		if ($result > 0)
		{
			return JRoute::_(K2HelperRoute::getDateRoute($year, $month, $day, $this->category));
		}
		else
		{
			return false;
		}
	}

	function getCalendarLink($month, $year)
	{
		$application = JFactory::getApplication();
		$itemId = $application->input->get('itemId', 0, 'int');
		if ($this->category)
		{
			return JURI::root(true)."/index.php?option=com_k2&amp;view=itemlist&amp;task=calendar&amp;month={$month}&amp;year={$year}&amp;catid={$this->category}&amp;Itemid={$itemId}";
		}
		else
		{
			return JURI::root(true)."/index.php?option=com_k2&amp;view=itemlist&amp;task=calendar&amp;month=$month&amp;year=$year&amp;Itemid={$itemId}";
		}
	}

}
