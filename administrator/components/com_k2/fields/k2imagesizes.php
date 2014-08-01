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

class JFormFieldK2ImageSizes extends JFormField
{
	var $type = 'K2ImageSizes';

	public function getInput()
	{
		// Get the rendering mode.
		$this->mode = (string)$this->element['k2mode'];
		$this->inheritance = (string)$this->element['k2inheritance'];
		$this->none = (string)$this->element['k2none'];

		// Initialize output variable
		$output = '';

		// Override mode. Appears in K2 category settings to allow user to override global configuration image dimensions
		if ($this->mode == 'overrides')
		{
			$params = JComponentHelper::getParams('com_k2');
			$sizes = (array)$params->get('imageSizes');
			$overrides = (array)$this->value;
			$overrides = array_filter($overrides);
			$values = array();
			foreach ($overrides as $override)
			{
				$values[$override->id] = $override->width;
			}
			if (count($sizes))
			{
				$output .= '<ul class="jw--imagesize">';
				foreach ($sizes as $key => $size)
				{
					$value = isset($values[$size->id]) ? $values[$size->id] : '';
					$output .= '<li>
					<label>'.$size->name.'</label> 
					<input type="number" placeholder="'.htmlspecialchars(JText::_('K2_CURRENT_VALUE').' '.$size->width).'px" value="'.$value.'" name="'.$this->name.'['.$key.'][width]" /> <label>px</label>
					<input type="hidden" value="'.$size->id.'" name="'.$this->name.'['.$key.'][id]" />
					<input type="hidden" value="'.$size->name.'" name="'.$this->name.'['.$key.'][name]" />
					<input type="hidden" value="'.$size->quality.'" name="'.$this->name.'['.$key.'][quality]" />
					</li>';
				}
				$output .= '</ul>';
			}

		}
		// Definition mode. Appears in K2 global settings to allow user to define image dimensions
		else if ($this->mode == 'definition')
		{
			$output .= '
			<div id="'.$this->id.'">
			<div class="ov-hidden jw--imgplaceholder k2ImageSizesPlaceholder" style="display:none;">
				<div class="jw--setting--field__small ov-hidden">
					<input class="left jw--sizeid" disabled="disabled" type="text" placeholder="'.htmlspecialchars(JText::_('K2_ID')).'" name="'.$this->name.'[COUNTER][id]" value="" />
					<input class="left" disabled="disabled" type="text" placeholder="'.htmlspecialchars(JText::_('K2_NAME')).'" name="'.$this->name.'[COUNTER][name]" value="" />
				</div>
				
				<div class="jw--setting--field__small ov-hidden">
					<input class="left" disabled="disabled" type="number" placeholder="'.htmlspecialchars(JText::_('K2_WIDTH')).'" name="'.$this->name.'[COUNTER][width]" size="4" maxlength="4" value="" /> 
					<label class="left">px</label>
					
					<input class="left" disabled="disabled" type="number" placeholder="'.htmlspecialchars(JText::_('K2_QUALITY')).'" name="'.$this->name.'[COUNTER][quality]" size="3" maxlength="3" value="" /> 
					<label class="left">%</label>
					
					<div class="clr"></div>
					<button class="jw--imgremove k2ImageSizesRemove"><i class="fa fa-ban"></i> <span class="visuallyhidden>"'.JText::_('K2_REMOVE').'</span></button>

				</div>
			</div>
			<ul class="jw--imagesize">';
			$counter = 0;
			if ($this->value)
			{

				foreach ($this->value as $entry)
				{
					$entry = (object)$entry;
					$counter++;
					$output .= '
					<li>
						<div class="ov-hidden jw--imgplaceholder">
							<div class="jw--setting--field__small ov-hidden">
								<input class="left jw--sizeid" type="text" placeholder="'.htmlspecialchars(JText::_('K2_ID')).'" name="'.$this->name.'['.$counter.'][id]" value="'.htmlspecialchars($entry->id).'" />
								<input class="left" type="text" placeholder="'.htmlspecialchars(JText::_('K2_NAME')).'" name="'.$this->name.'['.$counter.'][name]" value="'.htmlspecialchars($entry->name).'" />
							</div>
							
							<input class="left" type="number" placeholder="'.htmlspecialchars(JText::_('K2_WIDTH')).'" name="'.$this->name.'['.$counter.'][width]" size="4" maxlength="4" value="'.(int)$entry->width.'" />
							<label class="left">px</label>
							
							<input class="left" type="number" placeholder="'.htmlspecialchars(JText::_('K2_QUALITY')).'" name="'.$this->name.'['.$counter.'][quality]" size="3" maxlength="3" value="'.(int)$entry->quality.'" /> 
							<label class="left">%</label>
							
							<button class="jw--imgremove k2ImageSizesRemove"><i class="fa fa-ban"></i> <span class="visuallyhidden>"'.JText::_('K2_REMOVE').'</span></button>
						</div>
					</li>';
				}
			}

			$output .= '</ul>
			<button class="jw--btn k2ImageSizesAdd">'.JText::_('K2_ADD').'</button>
			<input type="hidden" name="counter" class="k2ImageSizesCounter" value="'.$counter.'" />
			</div>
			<script type="text/javascript">
			var container = jQuery("#'.$this->id.'");
			container.off("click", "button");
			container.on("click", ".k2ImageSizesAdd", function(event) {
				event.preventDefault();
				var counter = parseInt(container.find(".k2ImageSizesCounter").val()) + 1;
				var template = container.find(".k2ImageSizesPlaceholder").html();
				var rendered = template.replace(/COUNTER/g, counter);
				var element = jQuery("<li></li>").html(rendered);
				element.find("input").prop("disabled", false);
				container.find("ul").append(element);
				container.find("input[name=counter]").val(counter);
			});
			container.on("click", ".k2ImageSizesRemove", function(event) {
				event.preventDefault();
				jQuery(this).parent().remove();
				var counter = parseInt(container.find(".k2ImageSizesCounter").val()) - 1;
				container.find("input[name=counter]").val(counter);
			});
			</script>
		';

		}
		// List mode. Appears in module, menu etc. settings to allow user to select the desired image size for output
		else
		{
			$params = JComponentHelper::getParams('com_k2');
			$sizes = (array)$params->get('imageSizes');
			if ($this->none)
			{
				$option = new stdClass;
				$option->id = '';
				$option->name = JText::_('K2_NONE');
				array_unshift($sizes, $option);
			}
			if ($this->inheritance)
			{
				$option = new stdClass;
				$option->id = '';
				$option->name = JText::_('K2_INHERIT_FROM_CATEGORY');
				array_unshift($sizes, $option);
			}
			if (count($sizes))
			{
				$output .= JHtml::_('select.genericlist', $sizes, $this->name, '', 'id', 'name', $this->value);
			}
		}

		return $output;

	}

}
