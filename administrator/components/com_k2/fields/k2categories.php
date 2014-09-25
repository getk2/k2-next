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

jimport('joomla.form.formfield');
require_once JPATH_ADMINISTRATOR.'/components/com_k2/helpers/html.php';

class JFormFieldK2Categories extends JFormField
{
	var $type = 'K2Categories';

	public function getInput()
	{
		// Load javascript
		JHtml::_('jquery.framework');
		$document = JFactory::getDocument();
		$document->addScript(JURI::root(true).'/administrator/components/com_k2/js/fields.js');

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

		// Get some variables from XML
		$this->multiple = (bool)$this->element['k2multiple'];
		$this->recursive = (string)$this->element['k2recursive'];
		$this->mode = (string)$this->element['k2mode'];
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
		if($this->mode == 'menu')
		{
			$attributes.= ' data-mode="k2categoriesmenu"';
		}

		// Init output
		$output = '';

		// First show the category filter switch for multiple instances
		if ($this->multiple)
		{
			$options = array();
			$options[] = JHtml::_('select.option', '0', JText::_('K2_ALL'));
			$options[] = JHtml::_('select.option', '1', JText::_('K2_SELECT'));
			$output .= K2HelperHTML::radiolist($options, $this->name.'[enabled]', $this->value['enabled'], false, 'data-categories="'.$this->name.'[categories][]"');
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
			$options = array();
			$options[] = JHtml::_('select.option', '0', JText::_('K2_NO'));
			$options[] = JHtml::_('select.option', '1', JText::_('K2_YES'));
			$output .= '<label>'.JText::_('K2_APPLY_RECUSRIVELY').'</label>'.K2HelperHTML::radiolist($options, $this->name.'[recursive]', $this->value['recursive'], true);
		}
		else
		{
			$output .= '<input type="hidden" name="'.$this->name.'[recursive]" value="'.$this->recursive.'" />';
		}

		// Return
		return $output;
	}

}
