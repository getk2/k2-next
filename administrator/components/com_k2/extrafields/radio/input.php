<?php
/**
 * @version		3.0.0
 * @package		K2
 * @author		JoomlaWorks http://www.joomlaworks.net
 * @copyright	Copyright (c) 2006 - 2014 JoomlaWorks Ltd. All rights reserved.
 * @license		GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 */

// no direct access
defined('_JEXEC') or die ;

?>
<div class="jw--label--row">
<?php foreach($field->get('options', array()) as $option): ?>
	<label class="jw--radio<?php if($field->get('value') == $option) { echo ' jw--radio__checked"';} ?>">
		<?php echo $option; ?>
		<input type="radio" name="<?php echo $field->get('prefix'); ?>[value]" value="<?php echo htmlspecialchars($option, ENT_QUOTES, 'UTF-8'); ?>" <?php if($field->get('value') == $option) { echo 'checked="checked"';} ?> />
	</label>
<?php endforeach; ?>
</div>

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
