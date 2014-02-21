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

<?php foreach($field->get('options', array()) as $option): ?>
	<label>
		<?php echo $option; ?>
		<input type="radio" name="<?php echo $field->get('prefix'); ?>[value]" value="<?php echo htmlspecialchars($option, ENT_QUOTES, 'UTF-8'); ?>" <?php if($field->get('value') == $option) { echo 'checked="checked"';} ?> />
	</label>
<?php endforeach; ?>

<?php if($this->required): ?>
<script type="text/javascript">
	jQuery(document).bind('K2ExtraFieldsValidate', function(event, K2ExtraFields) {
		var element = jQuery('input[name="<?php echo $field->get('prefix'); ?>[value]"]');
		if(!element.is(':checked')) {
			K2ExtraFields.addValidationError(<?php echo $this->id; ?>);
		}
	});
</script>
<?php endif; ?>
