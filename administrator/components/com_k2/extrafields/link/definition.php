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
<input type="text" name="value[text]" value="<?php echo $this->escape($field->get('text')); ?>" />
<label><?php echo JText::_('K2_URL'); ?></label>
<input type="text" name="value[url]" value="<?php echo $this->escape($field->get('url')); ?>" />
<label><?php echo JText::_('K2_OPEN_IN'); ?></label>
<select id="extraFieldLinkTarget" name="value[target]">
    <option value="same"><?php echo JText::_('K2_SAME_WINDOW'); ?></option>
    <option value="new"><?php echo JText::_('K2_NEW_WINDOW'); ?></option>
    <option value="popup"><?php echo JText::_('K2_CLASSIC_JAVASCRIPT_POPUP'); ?></option>
    <option value="lightbox"><?php echo JText::_('K2_LIGHTBOX_POPUP'); ?></option>
</select>
<script type="text/javascript">
	jQuery('#extraFieldLinkTarget').val('<?php echo $this->escape($field->get('target')); ?>');
</script>