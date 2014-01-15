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
		$output = '
		<div id="k2ImageSizes">
		
		<ul>';
		if ($this->value)
		{
			foreach ($this->value as $entry)
			{

				$output .= '<li>
		<input type="text" placeholder="ID" name="'.$this->name.'[1][id]" value="'.htmlspecialchars($entry->id).'" />
		<input type="text" placeholder="Name" name="'.$this->name.'[1][name]" value="'.htmlspecialchars($entry->name).'" />
		<input type="number" placeholder="Width" name="'.$this->name.'[1][width]" size="4" maxlength="4" value="'.(int)$entry->width.'" />px
		<input type="number" placeholder="Quality" name="'.$this->name.'[1][quality]" size="3" maxlength="3" value="'.(int)$entry->quality.'" />%
		</li>';

			}
		}

	
		return $output;

	}

}
