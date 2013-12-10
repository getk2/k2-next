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

require_once JPATH_ADMINISTRATOR.'/components/com_k2/models/items.php';

/**
 * K2 base view class
 */

class K2View extends JViewLegacy
{

	public function __construct($config = array())
	{
		// Parent constructor
		parent::__construct($config);

		// Get application
		$application = JFactory::getApplication();

		// Load helpers
		$this->loadHelper('utilities');

		// Set the params
		$this->params = $application->getParams('com_k2');

		// Set the user
		$this->user = JFactory::getUser();

		// Add CSS
		$document = JFactory::getDocument();
		if ($document->getType() == 'html')
		{
			$document->addStyleSheet(JURI::root(true).'/media/k2/assets/css/k2.site.css');
			if (version_compare(JVERSION, '3.2', 'ge'))
			{
				JHtml::_('jquery.framework');
			}
			else
			{
				$document->addScript('//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js');
			}
			$document->addScript(JURI::root(true).'/media/k2/assets/js/k2.site.js');
		}

		// Add template paths
		$template = $application->getTemplate();
		$this->addTemplatePath(JPATH_SITE.'/components/com_k2/templates/default');
		$this->addTemplatePath(JPATH_SITE.'/templates/'.$template.'/html/com_k2/templates/default');
	}

}
