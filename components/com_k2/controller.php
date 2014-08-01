<?php
/**
 * @version		3.0.0b
 * @package		K2
 * @author		JoomlaWorks http://www.joomlaworks.net
 * @copyright	Copyright (c) 2006 - 2014 JoomlaWorks Ltd. All rights reserved.
 * @license		GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 */

// no direct access
defined('_JEXEC') or die ;

/**
 * K2 base front-end controller class.
 */

class K2Controller extends JControllerLegacy
{

	/**
	 * Method to display a view.
	 *
	 * @param   boolean			If true, the view output will be cached
	 * @param   array  An array of safe url parameters and their variable types, for valid values see {@link JFilterInput::clean()}.
	 *
	 * @return  JController		This object to support chaining.
	 * @since   1.5
	 */
	public function display($cachable = false, $urlparams = false)
	{

		$application = JFactory::getApplication();
		$user = JFactory::getUser();

		if ($user->guest && $application->input->getMethod() == 'GET')
		{
			$cachable = true;
		}

		$urlparams = array();
		$urlparams['limit'] = 'UINT';
		$urlparams['limitstart'] = 'UINT';
		$urlparams['id'] = 'INT';
		$urlparams['tag'] = 'STRING';
		$urlparams['searchword'] = 'STRING';
		$urlparams['day'] = 'INT';
		$urlparams['year'] = 'INT';
		$urlparams['month'] = 'INT';
		$urlparams['print'] = 'INT';
		$urlparams['lang'] = 'CMD';
		$urlparams['Itemid'] = 'INT';

		parent::display($cachable, $urlparams);

		return $this;
	}

}
