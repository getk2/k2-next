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
 * K2 base view class
 */

class K2View extends JViewLegacy
{
	public function display($tpl = null)
	{
		$this->loadHelper('utilities');
		$application = JFactory::getApplication();
		$template = $application->getTemplate();
		$this->addTemplatePath(JPATH_SITE.'/components/com_k2/templates/default');
		$this->addTemplatePath(JPATH_SITE.'/templates/'.$template.'/html/com_k2/templates/default');
		parent::display($tpl);
	}

}
