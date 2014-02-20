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
<label><?php echo JText::_('K2_SOURCE'); ?></label>
<input type="text" name="<?php echo $field->get('prefix'); ?>[src]" value="<?php echo htmlspecialchars($field->get('src'), ENT_QUOTES, 'UTF-8'); ?>" data-widget="browser" /> 
<label><?php echo JText::_('K2_ALT'); ?></label>
<input type="text" name="<?php echo $field->get('prefix'); ?>[alt]" value="<?php echo htmlspecialchars($field->get('alt'), ENT_QUOTES, 'UTF-8'); ?>" />

<?php if($this->required): ?>
<script type="text/javascript">
	jQuery(document).bind('K2ExtraFieldsValidate', function(event, K2ExtraFields) {
		var element = jQuery('input[name="<?php echo $field->get('prefix'); ?>[src]"]');
		if(element.val() == '') {
			K2ExtraFields.addValidationError(<?php echo $this->id; ?>);
		}
	});
</script>
<?php endif; ?>
