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

abstract class K2HelperAssociation
{

	public static function getAssociations($id = 0, $view = null)
	{
		jimport('helper.route', JPATH_COMPONENT_SITE);
		$application = JFactory::getApplication();
		$view = is_null($view) ? $application->input->get('view') : $view;
		$id = empty($id) ? $application->input->getInt('id') : $id;
		if ($view == 'item')
		{
			if ($id)
			{
				$associations = JLanguageAssociations::getAssociations('com_k2', '#__k2_items', 'com_k2.item', $id, 'id', '', '');
				$return = array();
				foreach ($associations as $tag => $item)
				{
					$return[$tag] = K2HelperRoute::getItemRoute($item->id.':'.$item->alias, $item->catid, $item->language);
				}
				return $return;
			}
		}
		return array();

	}

}
