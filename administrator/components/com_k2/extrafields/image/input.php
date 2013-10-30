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
<input type="text" name="extra_fields[<?php echo $this->id; ?>][src]" value="<?php echo htmlspecialchars($field->get('src'), ENT_QUOTES, 'UTF-8'); ?>" id="extraFieldImageSrc" /> <button id="extraFieldImageBrowseServer"><?php echo JText::_('K2_BROWSE_SERVER'); ?></button>
<label><?php echo JText::_('K2_ALT'); ?></label>
<input type="text" name="extra_fields[<?php echo $this->id; ?>][alt]" value="<?php echo htmlspecialchars($field->get('alt'), ENT_QUOTES, 'UTF-8'); ?>" />
<script type="text/javascript">
	jQuery(document).on('K2ExtraFieldsRender', function() {
		jQuery('#extraFieldImageBrowseServer').click(function(event) {
			event.preventDefault();
			require(['dispatcher'], function(K2Dispatcher) {
				K2Dispatcher.on('app:extraField:selectImage', function(path) {
					jQuery('#extraFieldImageSrc').val(path);
				});
				K2Dispatcher.trigger('app:controller:browseServer', {
					callback : 'app:extraField:selectImage',
					modal : true
				});
			});
		});
	});
</script>