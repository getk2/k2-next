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

$uniqueId = uniqid('k2ExtraField');
?>
<span id="<?php echo $uniqueId; ?>">
	<input type="file" name="csv[]" class="k2ExtraFieldsCsv" />
	<input type="hidden" name="<?php echo $field->get('prefix'); ?>[value]" value="<?php echo htmlspecialchars($field->get('value'), ENT_QUOTES, 'UTF-8'); ?>" />
	<?php if($value = $field->get('value')): ?>
	<button><?php echo JText::_('K2_DELETE_CSV_DATA'); ?></button>
	<div>
		<table>
		<?php foreach(json_decode($value) as $key => $row): ?>
			<tr>
			<?php foreach($row as $column): ?>
				<?php if($key): ?>
				<td><?php echo $column; ?></td>
				<?php else: ?>
				<th><?php echo $column; ?></th>
				<?php endif; ?>
			<?php endforeach; ?>
			</tr>
		<?php endforeach; ?>
		</table>
	</div>
<?php endif; ?>
</span>

<script type="text/javascript">

<?php if($this->required): ?>
	jQuery(document).bind('K2ExtraFieldsValidate', function(event, K2ExtraFields) {
		var element = jQuery('input[name="<?php echo $field->get('prefix'); ?>[value]"]');
		if(element.val() == '') {
			K2ExtraFields.addValidationError(<?php echo $this->id; ?>);
		}
	});
<?php endif; ?>

	jQuery('#<?php echo $uniqueId; ?> button').click(function(event) {
		event.preventDefault();
		jQuery('#<?php echo $uniqueId; ?> input[type="hidden"]').val('');
		jQuery('#<?php echo $uniqueId; ?> table').remove();
	});

	jQuery('#<?php echo $uniqueId; ?> input[type="file"]').change(function(event) {
		
		jQuery('#<?php echo $uniqueId; ?> input[type="hidden"]').val('');
		
		var file = jQuery(this).get(0).files[0];
		if (file.type == 'text/comma-separated-values' || file.type == 'text/csv') {
			var reader = new FileReader();
			reader.onload = function(event) {
				var data = [];
				var csv = event.target.result;
				var lines = csv.split(/\r\n|\n/);
				jQuery.each(lines, function(index, line) {
					var columns = line.split(',');
					data.push(columns);
				});
				jQuery('#<?php echo $uniqueId; ?> input[type="hidden"]').val(JSON.stringify(data));

				/*var content = '<table class="table table-striped"><tbody>'
				for ( i = 0; i < data.length; i++) {
					content += '<tr>';
					for (var j = 0; j < data[i].length; j++) {
						content += '<td>' + data[i][j] + '</td>';
					}
					content += '</tr>'
				}
				content += "</tbody></table>"
				jQuery('#preview').html(content);*/
			};
			reader.readAsText(file);
		} else {
			// This is not a CSV file
		}

	}); 
</script>