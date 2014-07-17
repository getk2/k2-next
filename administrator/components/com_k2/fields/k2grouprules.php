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

jimport('joomla.form.formfield');
require_once JPATH_SITE.'/libraries/joomla/form/fields/rules.php';

class JFormFieldK2GroupRules extends JFormFieldRules
{
	var $type = 'K2GroupRules';

	/**
	 * Method to get the field input markup for Access Control Lists.
	 * Optionally can be associated with a specific component and section.
	 *
	 * @return  string  The field input markup.
	 *
	 * @since   11.1
	 * @todo:   Add access check.
	 */
	protected function getInput()
	{
		JHtml::_('bootstrap.tooltip');

		// Initialise some field attributes.
		$section = 'category';
		$component = 'com_k2';
		$groupId = (int)(string)$this->element->attributes()->groupId;

		// Get the actions for the asset.
		$actions = JAccess::getActionsFromFile(JPATH_ADMINISTRATOR . '/components/' . $component . '/access.xml', "/access/section[@name='" . $section . "']/");

		// Iterate over the children and add to the actions.
		foreach ($this->element->children() as $el)
		{
			if ($el->getName() == 'action')
			{
				$actions[] = (object) array(
					'name' => (string)$el['name'],
					'title' => (string)$el['title'],
					'description' => (string)$el['description']
				);
			}
		}

		// Get database
		$db = JFactory::getDBO();

		// Get group
		$query = $db->getQuery(true);

		// Select rows
		$query->select('*');
		$query->from($db->quoteName('#__usergroups'));
		$query->where($db->quoteName('id').' = '.(int)$groupId);
		$db->setQuery($query);
		$group = $db->loadObject();

		// Get categories assets
		$query = $db->getQuery(true);

		// Select rows
		$query->select('*');
		$query->from($db->quoteName('#__assets'));
		$query->where($db->quoteName('name').' LIKE '.$db->quote('%'.$db->escape('com_k2.category').'%'));
		$query->order($db->quoteName('lft').' ASC');

		// Set query
		$db->setQuery($query);
		$assets = $db->loadObjectList();

		// Prepare output
		$html = array();

		// Description
		$html[] = '<p class="rule-desc">'.JText::_('JLIB_RULES_SETTINGS_DESC').'</p>';

		$html[] = '<table class="table table-striped"><thead>';
		$html[] = '<tr><th></th>';

		foreach ($actions as $action)
		{
			$html[] = '<th>'.JText::_($action->title).'</th>';
		}
		$html[] = '</tr>';
		$html[] = '<tbody>';
		foreach ($assets as $asset)
		{
			$assetRules = JAccess::getAssetRules($asset->id);

			$html[] = '<tr>';

			$html[] = '<td>';
			$html[] = '<span>'.str_repeat('-', $asset->level - 2).$asset->title.'</span><input type="hidden" name="'.$this->name.'[assets][]" value="'.$asset->id.'" />';
			$html[] = '</td>';

			foreach ($actions as $action)
			{
				$inheritedRule = JAccess::checkGroup($group->id, $action->name, $asset->id);
				$assetRule = $assetRules->allow($action->name, $group->id);

				$html[] = '<td>';
				$html[] = '<select class="input-small" name="'.$this->name.'[actions]['.$asset->id.']['.$action->name.']" id="'.$this->id.'_'.$action->name.'_'.$group->id.'" title="'.JText::sprintf('JLIB_RULES_SELECT_ALLOW_DENY_GROUP', JText::_($action->title), trim($group->title)).'">';
				$html[] = '<option value=""'.($assetRule === null ? ' selected="selected"' : '').'>'.JText::_(empty($group->parent_id) && empty($component) ? 'JLIB_RULES_NOT_SET' : 'JLIB_RULES_INHERITED').'</option>';
				$html[] = '<option value="1"'.($assetRule === true ? ' selected="selected"' : '').'>'.JText::_('JLIB_RULES_ALLOWED').'</option>';
				$html[] = '<option value="0"'.($assetRule === false ? ' selected="selected"' : '').'>'.JText::_('JLIB_RULES_DENIED').'</option>';
				$html[] = '</select>&#160; ';
				// If this asset's rule is allowed, but the inherited rule is deny, we have a conflict.
				if (($assetRule === true) && ($inheritedRule === false))
				{
					$html[] = JText::_('JLIB_RULES_CONFLICT');
				}

				// This is where we show the current effective settings considering currrent group, path and cascade.
				// Check whether this is a component or global. Change the text slightly.

				if (JAccess::checkGroup($group->id, 'core.admin', $asset->id) !== true)
				{
					if ($inheritedRule === null)
					{
						$html[] = '<span class="label label-important">'.JText::_('JLIB_RULES_NOT_ALLOWED').'</span>';
					}
					elseif ($inheritedRule === true)
					{
						$html[] = '<span class="label label-success">'.JText::_('JLIB_RULES_ALLOWED').'</span>';
					}
					elseif ($inheritedRule === false)
					{
						if ($assetRule === false)
						{
							$html[] = '<span class="label label-important">'.JText::_('JLIB_RULES_NOT_ALLOWED').'</span>';
						}
						else
						{
							$html[] = '<span class="label"><i class="icon-lock icon-white"></i> '.JText::_('JLIB_RULES_NOT_ALLOWED_LOCKED').'</span>';
						}
					}
				}
				elseif (!empty($component))
				{
					$html[] = '<span class="label label-success"><i class="icon-lock icon-white"></i> '.JText::_('JLIB_RULES_ALLOWED_ADMIN').'</span>';
				}
				else
				{
					// Special handling for  groups that have global admin because they can't  be denied.
					// The admin rights can be changed.
					if ($action->name === 'core.admin')
					{
						$html[] = '<span class="label label-success">'.JText::_('JLIB_RULES_ALLOWED').'</span>';
					}
					elseif ($inheritedRule === false)
					{
						// Other actions cannot be changed.
						$html[] = '<span class="label label-important"><i class="icon-lock icon-white"></i> '.JText::_('JLIB_RULES_NOT_ALLOWED_ADMIN_CONFLICT').'</span>';
					}
					else
					{
						$html[] = '<span class="label label-success"><i class="icon-lock icon-white"></i> '.JText::_('JLIB_RULES_ALLOWED_ADMIN').'</span>';
					}
				}

				$html[] = '</td>';
			}
			$html[] = '</tr>';
		}

		$html[] = '</tbody>';
		$html[] = '</table>';

		$html[] = '<div class="alert">';

		if ($section == 'component' || $section == null)
		{
			$html[] = JText::_('JLIB_RULES_SETTING_NOTES');
		}
		else
		{
			$html[] = JText::_('JLIB_RULES_SETTING_NOTES_ITEM');
		}

		$html[] = '</div>';

		return implode("\n", $html);
	}

}
