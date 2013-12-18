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
require_once JPATH_ADMINISTRATOR.'/components/com_k2/helpers/html.php';

class JFormFieldK2Categories extends JFormField
{
	var $type = 'K2Categories';

	public function getInput()
	{
		// Load javascript
		$document = JFactory::getDocument();
		$document->addScript(JURI::root(true).'/media/k2/assets/js/k2.fields.js');

		// Set values if are not set
		if (!isset($this->value['enabled']))
		{
			$this->value['enabled'] = '0';
		}
		if (!isset($this->value['categories']))
		{
			$this->value['categories'] = '';
		}
		if (!isset($this->value['recursive']))
		{
			$this->value['recursive'] = '';
		}

		// Get some variables from XML markup
		$this->multiple = $this->element['k2-multiple'];
		$this->size = (int)$this->element['k2-size'];

		// Build attributes string
		$attributes = '';
		if ($this->multiple)
		{
			$attributes .= ' multiple="multiple"';
		}
		if ($this->size)
		{
			$attributes .= ' size="'.$this->size.'"';
		}

		// First show the category filter switch
		$options = array();
		$options[] = JHtml::_('select.option', '0', JText::_('K2_ALL'));
		$options[] = JHtml::_('select.option', '1', JText::_('K2_SELECT'));
		$output = JHtml::_('select.radiolist', $options, $this->name.'[enabled]', 'class="k2FieldCategoriesFilterEnabled" data-categories="'.$this->name.'[categories][]"', 'value', 'text', $this->value['enabled'], $this->id);

		// Then the categories list
		$output .= K2HelperHTML::categories($this->name.'[categories][]', $this->value['categories'], false, null, $attributes);

		// And finally the recursive switch
		$output .= '<label>'.JText::_('K2_APPLY_RECUSRIVELY').'</label>'.JHtml::_('select.booleanlist', $this->name.'[recursive]', null, $this->value['recursive']);

		// Return
		return $output;
	}

}
