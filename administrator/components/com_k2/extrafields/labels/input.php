<?php
/**
 * @version		3.0.0
 * @package		K2
 * @author		JoomlaWorks http://www.joomlaworks.net
 * @copyright	Copyright (c) 2006 - 2014 JoomlaWorks Ltd. All rights reserved.
 * @license		GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 */

// no direct access
defined('_JEXEC') or die ; ?>
<div class="jw--block--field">
	<label><?php echo JText::_('K2_COMMA_SEPARATED_VALUES'); ?></label>
	<input type="text" name="<?php echo $field->get('prefix'); ?>[value]" value="<?php echo htmlspecialchars($field->get('value'), ENT_QUOTES, 'UTF-8'); ?>" />  
</div>
<?php if($this->required): ?>
<script type="text/javascript">
	jQuery(document).bind('K2ExtraFieldsValidate', function(event, K2ExtraFields) {
		var element = jQuery('input[name="<?php echo $field->get('prefix'); ?>[value]"]');
		if(element.val() == '') {
			K2ExtraFields.addValidationError(<?php echo $this->id; ?>);
		}
	});
</script>
<?php endif; ?>