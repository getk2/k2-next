<?php
/**
 * @version		3.0.0
 * @package		K2
 * @author		JoomlaWorks http://www.joomlaworks.net
 * @copyright	Copyright (c) 2006 - 2014 JoomlaWorks Ltd. All rights reserved.
 * @license		GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 */

// no direct access
defined('_JEXEC') or die;

require_once dirname(__FILE__).'/calendar.php';

class K2Calendar extends Calendar
{
    public $category = null;
    public $cache = null;

    public function getDateLink($day, $month, $year)
    {
        if (is_null($this->cache))
        {
            $model = K2Model::getInstance('Items');
            $model->setState('site', true);
            $model->setState('month', $month);
            $model->setState('year', $year);
            if ($this->category)
            {
                $model->setState('category', $this->category);
            }
            $rows = $model->getCalendar();
            foreach ($rows as $row)
            {
                $this->cache[$row->day] = $row->counter;
            }
        }

        $result = isset($this->cache[$day]) ? $this->cache[$day] : 0;

        if ($result > 0)
        {
            return JRoute::_(K2HelperRoute::getDateRoute($year, $month, $day, $this->category));
        } else
        {
            return false;
        }
    }

    public function getCalendarLink($month, $year)
    {
        $application = JFactory::getApplication();

        return JRoute::_('index.php?option=com_k2&view=calendar&year='.$year.'&month='.$month.'&category='.$this->category.'&format=raw&Itemid=');
    }
}
