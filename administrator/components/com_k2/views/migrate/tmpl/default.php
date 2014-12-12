<?php
/**
 * @version		3.0.0b
 * @package		K2
 * @author		JoomlaWorks http://www.joomlaworks.net
 * @copyright	Copyright (c) 2006 - 2014 JoomlaWorks Ltd. All rights reserved.
 * @license		GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 */

// no direct access
defined('_JEXEC') or die ; ?>
<script type="text/javascript">
	window.onbeforeunload = function(e) {
		return '<?php echo JText::_('K2_PROCESS_ARE_YOU_SURE_YOU_WANT_TO_LEAVE_THIS_PAGE'); ?>';
	};
	function _restore() {
		jQuery.post('index.php?option=com_k2&task=migrator.restore&format=json', '<?php echo JSession::getFormToken(); ?>=1').done(function(response) {
			jQuery('#k2UpgradeStatus').html('<?php echo JText::_('K2_RESTORE_COMPLETED'); ?>');
		}).fail(function(response) {
			jQuery('#k2UpgradeStatus').html('<?php echo JText::_('K2_RESTORE_FAILED'); ?>');
		});
	}
	function _migrate(type, id) {
		jQuery.post('index.php?option=com_k2&task=migrator.run&type=' + type + '&id=' + id + '&format=json', '<?php echo JSession::getFormToken(); ?>=1').done(function(response) {
			if (response) {
				jQuery.each(response.errors, function( index, error ) {
					jQuery('#k2UpgradeErrorLog').append('<li>' + error + '</li>');
				});
				if(response.failed) {
					jQuery('#k2UpgradeStatus').html('<?php echo JText::_('K2_UPGRADE_FAILED_TRYING_TO_RESTORE'); ?>');
					_restore();
				} else if(response.completed) {
					jQuery('#k2UpgradeStatus').html('<?php echo JText::_('K2_UPGRADE_COMPLETED'); ?>');
				} else {
					jQuery('#k2UpgradeStatus').html(response.status);
					_migrate(response.type, response.id);
				}
			}
		}).fail(function(response) {
			jQuery('#k2UpgradeStatus').html('<?php echo JText::_('K2_UPGRADE_FAILED_TRYING_TO_RESTORE'); ?>');
				_restore();
			});
	}
	_migrate('attachments', 0);
</script>

<span id="k2UpgradeStatus"></span>
<ul id="k2UpgradeErrorLog"></ul>