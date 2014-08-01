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
?>
<div class="jw--multiple--fields">
	<div class="ov-hidden repeater--action">
		<button id="extraFieldRadioAddOption" class="jw--btn jw--btn_apply" ><?php echo JText::_('K2_ADD_OPTION'); ?></button>
	</div>
	<div class="clr"></div>
	<div id="extraFieldRadioOptions" class="jw--label--row">
		<?php foreach($field->get('options', array()) as $option): ?>
		<div class="extraFieldRadioOption">
		
			<input type="text" name="<?php echo $field->get('prefix'); ?>[options][]" value="<?php echo htmlspecialchars($option, ENT_QUOTES, 'UTF-8'); ?>"> 			
			<button class="right jw--btn jw--btn__item extraFieldRadioRemoveOption">
				<i class="fa fa-ban"></i>
				<span class="visuallyhidden"><?php echo JText::_('K2_REMOVE'); ?></span>
			</button>
		</div>
		<?php endforeach; ?>
	</div>
</div>

<script type="text/javascript">
	jQuery('body').on('click', '.extraFieldRadioRemoveOption', function(event) {
		event.preventDefault();
		jQuery(this).parent().remove();
	});
	jQuery('#extraFieldRadioAddOption').click(function(event) {
		event.preventDefault();
		var container = jQuery('<div>').attr('class', 'extraFieldRadioOption repeater--field');
		var option = jQuery('<input>').attr('type', 'text').attr('name', '<?php echo $field->get('prefix'); ?>[options][]', 'class', 'left');
		var button = jQuery('<button>').html('<i class="fa fa-ban"></i><span class="visuallyhidden"><?php echo JText::_('K2_REMOVE'); ?></span>').attr('class', 'left repeater--remove extraFieldRadioRemoveOption');
		container.append(option);
		container.append(button);
		jQuery('#extraFieldRadioOptions').append(container);
	});
</script>