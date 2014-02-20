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

<button id="extraFieldRadioAddOption"><?php echo JText::_('K2_ADD_OPTION'); ?></button>
<div id="extraFieldRadioOptions">
	<?php if(is_array($field->get('options'))) : ?>
	<?php foreach($field->get('options') as $option): ?>
	<div class="extraFieldRadioOption">
		<input type="text" name="<?php echo $field->get('prefix'); ?>[options][]" value="<?php echo htmlspecialchars($option, ENT_QUOTES, 'UTF-8'); ?>"> <button class="extraFieldRadioRemoveOption"><?php echo JText::_('K2_REMOVE'); ?></button>
	</div>
	<?php endforeach; ?>
	<?php endif; ?>
</div>

<script type="text/javascript">
	jQuery('body').on('click', '.extraFieldRadioRemoveOption', function(event) {
		event.preventDefault();
		jQuery(this).parent().remove();
	});
	jQuery('#extraFieldRadioAddOption').click(function(event) {
		event.preventDefault();
		var container = jQuery('<div>').attr('class', 'extraFieldRadioOption');
		var option = jQuery('<input>').attr('type', 'text').attr('name', '<?php echo $field->get('prefix'); ?>[options][]');
		var button = jQuery('<button>').text('<?php echo JText::_('K2_REMOVE'); ?>').attr('class', 'extraFieldRadioRemoveOption');
		container.append(option);
		container.append(button);
		jQuery('#extraFieldRadioOptions').append(container);
	});
</script>