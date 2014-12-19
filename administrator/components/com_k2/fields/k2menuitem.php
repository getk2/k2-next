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

JFormHelper::loadFieldClass('menuitem');

class JFormFieldK2Menuitem extends JFormFieldMenuitem
{
	public $type = 'K2MenuItem';

	/**
	 * Method to get the field option groups.
	 *
	 * @return  array  The field option objects as a nested array in groups.
	 *
	 * @since   1.6
	 */
	protected function getGroups()
	{
		$this->filterAttribute = (string)$this->element['filter_attribute'];
		$this->filterValue = (string)$this->element['filter_value'];
		$groups = parent::getGroups();
		if ($this->filterAttribute && $this->filterValue)
		{
			$application = JApplication::getInstance('site');
			$menu = $application->getMenu();
			$items = $menu->getItems($this->filterAttribute, $this->filterValue);
			$enabled = array('', '0');
			foreach ($items as $item)
			{
				$enabled[] = $item->id;
			}
			foreach ($groups as $options)
			{
				foreach ($options as $option)
				{
					if (!in_array($option->value, $enabled))
					{
						$option->disable = true;
					}
				}
			}
		}
		return $groups;
	}

}
