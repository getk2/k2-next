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
<label><?php echo JText::_('K2_TEXT'); ?></label>
<input type="text" name="extra_fields[<?php echo $this->id; ?>][text]" value="<?php echo htmlspecialchars($field->get('text'), ENT_QUOTES, 'UTF-8'); ?>" />
<label><?php echo JText::_('K2_URL'); ?></label>
<input type="text" name="extra_fields[<?php echo $this->id; ?>][url]" value="<?php echo htmlspecialchars($field->get('url'), ENT_QUOTES, 'UTF-8'); ?>" />
<label><?php echo JText::_('K2_OPEN_IN'); ?></label>
<select id="extraFieldLinkTarget" name="extra_fields[<?php echo $this->id; ?>][target]">
    <option value="same"><?php echo JText::_('K2_SAME_WINDOW'); ?></option>
    <option value="new"><?php echo JText::_('K2_NEW_WINDOW'); ?></option>
    <option value="popup"><?php echo JText::_('K2_CLASSIC_JAVASCRIPT_POPUP'); ?></option>
    <option value="lightbox"><?php echo JText::_('K2_LIGHTBOX_POPUP'); ?></option>
</select>
<script type="text/javascript">
	jQuery(document).on('K2ExtraFields', function() {
		jQuery('#extraFieldLinkTarget').val('<?php echo $field->get('target'); ?>');
	});
</script>