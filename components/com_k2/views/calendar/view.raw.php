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

require_once JPATH_SITE.'/components/com_k2/views/view.php';

/**
 * K2 calendar view class
 */

class K2ViewCalendar extends K2View
{
	public function display($tpl = null)
	{
		require_once JPATH_SITE.'/modules/mod_k2_tools/helper.php';
		$application = JFactory::getApplication();
		$params = new JRegistry();
		$params->def('calendarCategory', $application->input->get('category', 0, 'int'));
		$params->def('month', $application->input->get('month', 0, 'int'));
		$params->def('year', $application->input->get('year', 0, 'int'));
		echo ModK2ToolsHelper::getCalendar($params);
	}

}
