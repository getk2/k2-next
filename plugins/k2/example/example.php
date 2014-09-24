<?php
/**
 * @version		3.0.0b
 * @package		Example K2 Plugin (K2 plugin)
 * @author		JoomlaWorks - http://www.joomlaworks.net
 * @copyright	Copyright (c) 2006 - 2014 JoomlaWorks Ltd. All rights reserved.
 * @license		GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 */

// no direct access
defined('_JEXEC') or die ;

/**
 * Example K2 Plugin to render YouTube URLs entered in backend K2 forms to video players in the frontend.
 */

// Initiate class to hold plugin events
class plgK2Example extends K2Plugin
{

	public function onK2PrepareContent(&$item, &$params, $limitstart)
	{
		$mainframe = JFactory::getApplication();
		//$item->text = 'It works! '.$item->text;
	}

	public function onK2AfterDisplay(&$item, &$params, $limitstart)
	{
		$mainframe = JFactory::getApplication();
		return '';
	}

	public function onK2BeforeDisplay(&$item, &$params, $limitstart)
	{
		$mainframe = JFactory::getApplication();
		return '';
	}

	public function onK2AfterDisplayTitle(&$item, &$params, $limitstart)
	{
		$mainframe = JFactory::getApplication();
		return '';
	}

	public function onK2BeforeDisplayContent(&$item, &$params, $limitstart)
	{
		$mainframe = JFactory::getApplication();
		return '';
	}

	// Event to display (in the frontend) the YouTube URL as entered in the item form
	public function onK2AfterDisplayContent(&$item, &$params, $limitstart)
	{
		$mainframe = JFactory::getApplication();

		$videoURL = $this->getValue('videoURL_item');

		// Check if we have a value entered
		if (empty($videoURL))
			return '';

		// Output
		preg_match('/youtube\.com\/watch\?v=([a-z0-9-_]+)/i', $videoURL, $matches);
		$video_id = $matches[1];

		$output = '
		<p>'.JText::_('Video rendered using the "Example K2 Plugin".').'</p>
		<object width="'.$this->getParam('width').'" height="'.$this->getParam('height').'">
			<param name="movie" value="http://www.youtube.com/v/'.$video_id.'&hl=en&fs=1"></param>
			<param name="allowFullScreen" value="true"></param>
			<param name="allowscriptaccess" value="always"></param>
			<embed src="http://www.youtube.com/v/'.$video_id.'&hl=en&fs=1" type="application/x-shockwave-flash" allowscriptaccess="always" allowfullscreen="true" width="'.$this->getParam('width').'" height="'.$this->getParam('height').'"></embed>
		</object>
		';

		return $output;
	}

	// Event to display (in the frontend) the YouTube URL as entered in the category form
	public function onK2CategoryDisplay(&$category, &$params, $limitstart)
	{
		$mainframe = JFactory::getApplication();

		$output = $this->getValue('videoURL_cat');

		return $output;
	}

	// Event to display (in the frontend) the YouTube URL as entered in the user form
	public function onK2UserDisplay(&$user, &$params, $limitstart)
	{
		$mainframe = JFactory::getApplication();

		$output = $this->getValue('videoURL_user');

		return $output;
	}

	// Example to load custom scripts and styles into K2 admin form
	public function onK2RenderAdminHead($row, $type)
	{
		$this->addScript(JUri::root(true).'/plugins/k2/example/script.js');
		$this->addStyle(JUri::root(true).'/plugins/k2/example/style.css');
	}

} // END CLASS
