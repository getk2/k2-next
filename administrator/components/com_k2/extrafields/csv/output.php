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

<?php if($value = $field->get('value')): ?>
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