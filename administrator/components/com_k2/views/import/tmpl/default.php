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
	jQuery(document).ready(function() {
		function _import(id, processed) {
			var self = this;
			jQuery.post('index.php?option=com_k2&task=items.import&id=' + id + '&format=json', K2SessionToken + '=1', function(data) {
				if (id > 0) {
					processed = processed + 10;
				}
				var percentage;
				if(data) {
					percentage = Math.round((processed/data.total)*100);
				} else {
					percentage = 100;
				}
				jQuery('#k2ImportPercentage').text(percentage + '%');
				jQuery('#k2ImportStatusBar').animate({
					'width' : (percentage) + '%'
				}, 'slow', 'linear', function() {
					if (data && data.lastId) {
						_import(data.lastId, processed);
					} else {
						setTimeout(function() {
							window.close();
						}, 1000);
					}
				});
			}).fail(function(jqXHR, textStatus, error) {
				alert(jqXHR.responseText);
				window.close();
			});
		}
		_import(0, 1);
	}); 
</script>
<span id="k2ImportPercentage">0%</span>
<div id="k2ImportStatus"><div id="k2ImportStatusBar" style="width: 0%; height: 40px; background: red;"></div></div>
<div class="k2ImportNote"><?php echo JText::_('K2_IMPORT_DO_NOT_CLOSE_THIS_WINDOW'); ?></div>