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

class ModK2ContentHelper
{
	public static function getItems($params)
	{
		// Get items
		$model = K2Model::getInstance('Items');
		$model->setState('site', true);
		$model->setState('limit', 2);
		$model->setState('limitstart', 0);
		$items = $model->getRows();

		// Plugins
		foreach ($items as $item)
		{
			$item->triggerPlugins('mod_k2_content', $params, 0);
		}

		return $items;
	}

}
