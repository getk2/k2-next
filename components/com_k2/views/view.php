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
	protected $isActive = true;

	public function __construct($config = array())
	{
		// Parent constructor
		parent::__construct($config);

		// Get application
		$application = JFactory::getApplication();

		// Set print variable
		$this->print = $application->input->getBool('print');

		// Load helpers
		$this->loadHelper('utilities');

		// Set the params
		$this->params = $application->getParams('com_k2');

		// Set the user
		$this->user = JFactory::getUser();

		// Get document
		$document = JFactory::getDocument();

		// Add CSS
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
		$this->addTemplatePath(JPATH_SITE.'/templates/'.$template.'/html/com_k2/default');
		if ($theme = $this->params->get('theme'))
		{
			$this->addTemplatePath(JPATH_SITE.'/components/com_k2/templates/'.$theme);
			$this->addTemplatePath(JPATH_SITE.'/templates/'.$template.'/html/com_k2/'.$theme);
		}

		// Set active (determine if the current menu Itemid is the same of the current view)
		$this->setActive();

		// Set page metadata and options correctly since Joomla! does not take care of it
		if ($this->isActive)
		{
			// Get menu
			$menu = $application->getMenu();

			// Get active
			$active = $menu->getActive();

			// Set page heading
			$this->params->def('page_heading', $this->params->get('page_title', $active->title));

			// Set browser title
			$this->setTitle($this->params->get('page_title'));

			// Set meta description
			if ($this->params->get('menu-meta_description'))
			{
				$document->setDescription($this->params->get('menu-meta_description'));
			}

			// Set meta keywords
			if ($this->params->get('menu-meta_keywords'))
			{
				$document->setMetadata('keywords', $this->params->get('menu-meta_keywords'));
			}

			// Set robots
			if ($this->params->get('robots'))
			{
				$document->setMetadata('robots', $this->params->get('robots'));
			}
		}
	}

	private function setActive()
	{
		$application = JFactory::getApplication();
		$menu = $application->getMenu();
		$active = $menu->getActive();
		if ($active)
		{
			foreach ($active->query as $key => $value)
			{
				if ($application->input->get($key) != $value)
				{
					$this->isActive = false;
					break;
				}
			}
		}
		else
		{
			$this->isActive = false;
		}
	}

	protected function setTitle($title)
	{
		$application = JFactory::getApplication();
		$document = JFactory::getDocument();
		if (empty($title))
		{
			$title = $application->getCfg('sitename');
		}
		elseif ($application->getCfg('sitename_pagetitles', 0) == 1)
		{
			$title = JText::sprintf('JPAGETITLE', $application->getCfg('sitename'), $title);
		}
		elseif ($application->getCfg('sitename_pagetitles', 0) == 2)
		{
			$title = JText::sprintf('JPAGETITLE', $title, $application->getCfg('sitename'));
		}
		$document->setTitle($title);
	}

}
