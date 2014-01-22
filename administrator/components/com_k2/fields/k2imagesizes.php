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

class JFormFieldK2ImageSizes extends JFormField
{
	var $type = 'K2ImageSizes';

	public function getInput()
	{
		// Get the rendering mode.
		$this->mode = (string)$this->element['k2mode'];

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
				$output .= '<ul>';
				foreach ($sizes as $key => $size)
				{
					$output .= '<li>
					<label>'.$size->name.'</label> 
					<input type="number" placeholder="'.htmlspecialchars(JText::_('K2_CURRENT_VALUE').' '.$size->width).'px" value="'.$values[$size->id].'" name="'.$this->name.'['.$key.'][width]" /> px
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
			<span class="k2ImageSizesPlaceholder" style="display:none;">
			<input disabled="disabled" type="text" placeholder="'.htmlspecialchars(JText::_('K2_ID')).'" name="'.$this->name.'[COUNTER][id]" value="" />
			<input disabled="disabled" type="text" placeholder="'.htmlspecialchars(JText::_('K2_NAME')).'" name="'.$this->name.'[COUNTER][name]" value="" />
			<input disabled="disabled" type="number" placeholder="'.htmlspecialchars(JText::_('K2_WIDTH')).'" name="'.$this->name.'[COUNTER][width]" size="4" maxlength="4" value="" />px
			<input disabled="disabled" type="number" placeholder="'.htmlspecialchars(JText::_('K2_QUALITY')).'" name="'.$this->name.'[COUNTER][quality]" size="3" maxlength="3" value="" />%
			<button class="k2ImageSizesRemove">'.JText::_('K2_REMOVE').'</button>
			</span>
			<ul>';
			$counter = 0;
			if ($this->value)
			{

				foreach ($this->value as $entry)
				{
					$entry = (object)$entry;
					$counter++;
					$output .= '
					<li>
					<input type="text" placeholder="'.htmlspecialchars(JText::_('K2_ID')).'" name="'.$this->name.'['.$counter.'][id]" value="'.htmlspecialchars($entry->id).'" />
					<input type="text" placeholder="'.htmlspecialchars(JText::_('K2_NAME')).'" name="'.$this->name.'['.$counter.'][name]" value="'.htmlspecialchars($entry->name).'" />
					<input type="number" placeholder="'.htmlspecialchars(JText::_('K2_WIDTH')).'" name="'.$this->name.'['.$counter.'][width]" size="4" maxlength="4" value="'.(int)$entry->width.'" />px
					<input type="number" placeholder="'.htmlspecialchars(JText::_('K2_QUALITY')).'" name="'.$this->name.'['.$counter.'][quality]" size="3" maxlength="3" value="'.(int)$entry->quality.'" />%
					<button class="k2ImageSizesRemove">'.JText::_('K2_REMOVE').'</button>
					</li>';
				}
			}

			$output .= '</ul>
			<button class="k2ImageSizesAdd">'.JText::_('K2_ADD').'</button>
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
			if (count($sizes))
			{
				$output .= JHtml::_('select.genericlist', $sizes, $this->name, '', 'id', 'name', $this->value);
			}
		}

		return $output;

	}

}
