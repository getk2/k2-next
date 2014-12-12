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
			jQuery('.k2ProcessStatusText').html('<?php echo JText::_('K2_RESTORE_COMPLETED'); ?>');
		}).fail(function(response) {
			jQuery('.k2ProcessStatusText').html('<?php echo JText::_('K2_RESTORE_FAILED'); ?>');
		});
	}
	function _migrate(type, id) {
		jQuery.post('index.php?option=com_k2&task=migrator.run&type=' + type + '&id=' + id + '&format=json', '<?php echo JSession::getFormToken(); ?>=1').done(function(response) {
			if (response) {
				jQuery.each(response.errors, function( index, error ) {
					jQuery('.k2ProcessErrorLog').append('<li>' + error + '</li>');
				});
				if(response.failed) {
					jQuery('.k2ProcessStatusText').html('<?php echo JText::_('K2_UPGRADE_FAILED_TRYING_TO_RESTORE'); ?>');
					_restore();
				} else if(response.completed) {
					jQuery('.k2ProcessStatusText').html('<?php echo JText::_('K2_UPGRADE_COMPLETED'); ?>');
				} else {
					jQuery('.k2ProcessStatusText').html(response.status);
					jQuery('.k2ProcessPercentage').text(response.percentage + '%');
					if(response.percentage == 0) {
						jQuery('.k2ProcessStatusBar').css('width', '0%');
					}
					jQuery('.k2ProcessStatusBar').animate({'width' : (response.percentage) + '%'}, 'slow', 'linear', function() {
						console.info(response);
						if (response && !response.completed) {
							_migrate(response.type, response.id);
						} else {
							window.onbeforeunload = null;
							setTimeout(function() {
								window.close();
							}, 1000);
						}
					});
				}
			}
		}).fail(function(response) {
			jQuery('.k2ProcessStatusText').html('<?php echo JText::_('K2_UPGRADE_FAILED_TRYING_TO_RESTORE'); ?>');
				_restore();
			});
	}
	_migrate('attachments', 0);
</script>
<span class="k2ProcessStatusText"></span>
<span class="k2ProcessPercentage">0%</span>
<div class="k2ProcessStatus"><div class="k2ProcessStatusBar" style="width: 0%; height: 40px; background: red;"></div></div>
<div class="k2ProcessNote"><?php echo JText::_('K2_PROCESS_DO_NOT_CLOSE_THIS_WINDOW'); ?></div>

<ul class="k2ProcessErrorLog"></ul>