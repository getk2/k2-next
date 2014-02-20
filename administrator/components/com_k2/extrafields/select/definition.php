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
?>
<label><?php echo JText::_('K2_SHOW_NULL_OPTION'); ?></label>
<input value="1" name="<?php echo $field->get('prefix'); ?>[null]" type="checkbox" <?php if($field->get('null')) { echo 'checked="checked"';} ?> />

<label><?php echo JText::_('K2_MULTIPLE'); ?></label>
<input value="1" name="<?php echo $field->get('prefix'); ?>[multiple]" type="checkbox" <?php if($field->get('multiple')) { echo 'checked="checked"';} ?> />

<button id="extraFieldSelectAddOption"><?php echo JText::_('K2_ADD_OPTION'); ?></button>
<div id="extraFieldSelectOptions">
	<?php if(is_array($field->get('options'))) : ?>
	<?php foreach($field->get('options') as $option): ?>
	<div class="extraFieldSelectOption">
		<input type="text" name="<?php echo $field->get('prefix'); ?>[options][]" value="<?php echo htmlspecialchars($option, ENT_QUOTES, 'UTF-8'); ?>"> <button class="extraFieldSelectRemoveOption"><?php echo JText::_('K2_REMOVE'); ?></button>
	</div>
	<?php endforeach; ?>
	<?php endif; ?>
</div>

<script type="text/javascript">
	jQuery('body').on('click', '.extraFieldSelectRemoveOption', function(event) {
		event.preventDefault();
		jQuery(this).parent().remove();
	});
	jQuery('#extraFieldSelectAddOption').click(function(event) {
		event.preventDefault();
		var container = jQuery('<div>').attr('class', 'extraFieldSelectOption');
		var option = jQuery('<input>').attr('type', 'text').attr('name', '<?php echo $field->get('prefix'); ?>[options][]');
		var button = jQuery('<button>').text('<?php echo JText::_('K2_REMOVE'); ?>').attr('class', 'extraFieldSelectRemoveOption');
		container.append(option);
		container.append(button);
		jQuery('#extraFieldSelectOptions').append(container);
	});
</script>