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

require_once JPATH_SITE.'/components/com_k2/helpers/route.php';
require_once JPATH_SITE.'/components/com_k2/helpers/utilities.php';
require_once JPATH_ADMINISTRATOR.'/components/com_k2/models/model.php';
K2Model::addIncludePath(JPATH_ADMINISTRATOR.'/components/com_k2/models');

class ModK2ContentHelper
{
	public static function getItems($params)
	{
		// Component params
		$componentParams = JComponentHelper::getParams('com_k2');

		// Get model
		$model = K2Model::getInstance('Items');

		// Set site state
		$model->setState('site', true);

		// Set states depending on source
		if ($params->get('source') == 'specific')
		{
			$items = array();

			if ($params->get('items'))
			{
				$itemIds = array_filter((array)$params->get('items'));
				if (count($itemIds))
				{
					// Apply sorting
					foreach ($itemIds as $itemId)
					{
						// Fetch item
						$model->setState('id', $itemId);
						$items[] = $model->getRow();
					}
				}
			}

		}
		else
		{
			// Category filter
			$model->setState('category.filter', $params->get('filter'));

			// Featured
			if ($params->get('featured') == 2)
			{
				$model->setState('featured', 1);
			}
			else if ($params->get('featured') == 0)
			{
				$model->setState('featured', 0);
			}

			// Set time range if sorting is comments or hits
			if ($params->get('timeRange') && ($params->get('sorting') == 'comments' || $params->get('sorting') == 'hits'))
			{
				$now = JFactory::getDate();
				switch ($params->get('timeRange'))
				{
					case '1' :
						$interval = 'P1D';
						break;
					case '3' :
						$interval = 'P3D';
						break;
					case '7' :
						$interval = 'P1W';
						break;
					case '15' :
						$interval = 'P2W';
						break;
					case '30' :
						$interval = 'P1M';
						break;
					case '90' :
						$interval = 'P3M';
						break;
					case '180' :
						$interval = 'P6M';
						break;
				}
				$date = $now->sub(new DateInterval($interval));
				$model->setState('created.value', $date->toSql());
				$model->setState('created.operator', '>');
			}

			// Fetch only items with media
			if ($params->get('media'))
			{
				$model->setState('media', true);
			}

			// Set limit
			$model->setState('limit', $params->get('limit'));

			// Set sorting
			$model->setState('sorting', $params->get('sorting'));

			// Get items
			$items = $model->getRows();
		}

		// Prepare data
		foreach ($items as $item)
		{
			// Plugins
			$item->events = $item->getEvents('mod_k2_content', $params, 0, $params->get('k2Plugins'), $params->get('jPlugins'));

			// Introtext word limit
			if ($params->get('itemIntroTextWordLimit'))
			{
				$item->introtext = K2HelperUtilities::wordLimit($item->introtext, $params->get('itemIntroTextWordLimit'));
			}

			// Set the selected image as default
			$item->image = $item->getImage($params->get('itemImgSize'));
		}

		// Load the comments counters in a single query for all items
		if ($params->get('itemCommentsCounter') && $componentParams->get('comments'))
		{
			K2Items::countComments($items);
		}

		// Set the avatar width if it's inherited from component settings
		if ($params->get('itemAuthorAvatarWidthSelect') == 'custom')
		{
			$params->set('itemAuthorAvatarWidth', $componentParams->get('userImageWidth'));
		}

		// Set the custom link url if user has selected a menu link item
		if ($params->get('itemCustomLinkMenuItem') && $params->get('itemCustomLink'))
		{
			$application = JFactory::getApplication();
			$menu = $application->getMenu();
			$menuLink = $menu->getItem($params->get('itemCustomLinkMenuItem'));
			if ($menuLink)
			{
				if (!$params->get('itemCustomLinkTitle'))
				{
					$params->set('itemCustomLinkTitle', $menuLink->title);
				}
				$params->set('itemCustomLinkURL', JRoute::_('index.php?&Itemid='.$menuLink->id));
			}

		}

		// Return
		return $items;
	}

}
