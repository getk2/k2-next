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
		$this->recursive = $this->element['recursive'];
		$this->multiple = true;
		$this->size = (int)$this->element['size'];
		$attributes = '';
		if ($this->multiple)
		{
			$attributes .= ' multiple="multiple"';
		}
		if ($this->size)
		{
			$attributes .= ' size="'.$this->size.'"';
		}
		
		$document = JFactory::getDocument();
		$js = '
		jQuery(document).ready(function() {
			jQuery(".k2FieldCategoriesFilter").change(function() {
				console.info(jQuery(this).val());
				if(jQuery(this).val() == "all") {
					var value = jQuery(this).closest("select").find("option").prop("selected", "selected");
					//jQuery(this).next().val(value);
				}
				else {
					jQuery(this).closest("select").val("");
				}
			});
		});
		';
		$document->addScriptDeclaration($js);
		
		$options = array();
		$options[] = JHtml::_('select.option', 'all', JText::_('K2_ALL'));
		$options[] = JHtml::_('select.option', 'specific', JText::_('K2_SELECT'));
		$output = JHtml::_('select.radiolist', $options, $this->name.'[filter]', 'class="k2FieldCategoriesFilter"', 'value', 'text', $this->value['filter']);
		$output .= K2HelperHTML::categories($this->name.'[categories][]', $this->value['categories'], false, null, $attributes);
		return $output;
	}

}
