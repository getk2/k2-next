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

require_once JPATH_ADMINISTRATOR.'/components/com_k2/helpers/helper.php';

/**
 * K2 Toolbar helper class.
 */

class K2HelperToolbar extends K2Helper
{

	public static function published($text = 'K2_TOGGLE_PUBLISHED_STATE', $state = 'published')
	{
		return '<a id="jwBatchPublishedToggler" class="jwBatchStateToggler" data-state="'.$state.'">'.JText::_($text).'</a>';
	}

	public static function featured($text = 'K2_TOGGLE_FEATURED_STATE', $state = 'featured')
	{
		return '<a id="jwBatchFeaturedToggler" class="jwBatchStateToggler" data-state="'.$state.'">'.JText::_($text).'</a>';
	}

	public static function batch($text = 'K2_BATCH')
	{
		return '<a id="jwBatchButton">'.JText::_($text).'</a>';
	}

	public static function delete($text = 'K2_DELETE')
	{
		return '<a id="jwDeleteButton">'.JText::_($text).'</a>';
	}

}
