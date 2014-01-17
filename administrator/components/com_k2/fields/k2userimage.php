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

class JFormFieldK2UserImage extends JFormField
{
	var $type = 'K2UserImage';

	public function getInput()
	{
		// Initialize some field attributes.
		$accept = !empty($this->accept) ? ' accept="'.$this->accept.'"' : '';
		$size = !empty($this->size) ? ' size="'.$this->size.'"' : '';
		$class = !empty($this->class) ? ' class="'.$this->class.'"' : '';
		$disabled = $this->disabled ? ' disabled' : '';
		$required = $this->required ? ' required aria-required="true"' : '';
		$autofocus = $this->autofocus ? ' autofocus' : '';
		$multiple = $this->multiple ? ' multiple' : '';

		// Initialize JavaScript field attributes.
		$onchange = $this->onchange ? ' onchange="'.$this->onchange.'"' : '';

		$output = '<input type="file" name="'.$this->name.'" id="'.$this->id.'" value=""'.$accept.$disabled.$class.$size.$onchange.$required.$autofocus.$multiple.' />';

		if ($this->value && is_object($this->value) && isset($this->value->flag) && $this->value->flag)
		{
			$output .= 
			'<div>
			<img src="'.$this->value->src.'" alt="'.$this->value->alt.'" />
			<input type="checkbox" name="'.$this->name.'[remove]" id="k2UserImageRemove" />
			<label for="k2UserImageRemove">'.JText::_('K2_CHECK_THIS_BOX_TO_DELETE_CURRENT_IMAGE_OR_JUST_UPLOAD_A_NEW_IMAGE_TO_REPLACE_THE_EXISTING_ONE').'</label>
			</div>';
		}
		else 
		{
			$output .= '<input type="hidden" name="'.$this->name.'[remove]" value="" />';
		}

		return $output;
	}

}
