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
		if (!is_array($this->value))
		{
			$this->value = array();
		}
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
		$this->multiple = (bool)$this->element['k2multiple'];
		$this->recursive = (string)$this->element['k2recursive'];
		$this->size = (int)$this->element['size'];

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

		// Init output
		$output = '';

		// First show the category filter switch for multiple instances
		if ($this->multiple)
		{
			$options = array();
			$options[] = JHtml::_('select.option', '0', JText::_('K2_ALL'));
			$options[] = JHtml::_('select.option', '1', JText::_('K2_SELECT'));
			$output .= JHtml::_('select.radiolist', $options, $this->name.'[enabled]', 'class="k2FieldCategoriesFilterEnabled" data-categories="'.$this->name.'[categories][]"', 'value', 'text', $this->value['enabled'], $this->id);
			$placeholder = null;
		}
		else
		{
			$output .= '<input type="hidden" name="'.$this->name.'[enabled]" value="1" />';
			$placeholder = 'K2_NONE_ONSELECTLISTS';
		}

		// Then the categories list
		$output .= K2HelperHTML::categories($this->name.'[categories][]', $this->value['categories'], $placeholder, null, $attributes);

		// And finally the recursive switch
		if ($this->recursive == 'select')
		{
			$output .= '<label>'.JText::_('K2_APPLY_RECUSRIVELY').'</label>'.JHtml::_('select.booleanlist', $this->name.'[recursive]', null, $this->value['recursive']);
		}
		else
		{
			$output .= '<input type="hidden" name="'.$this->name.'[recursive]" value="'.$this->recursive.'" />';
		}

		// Return
		return $output;
	}

}
