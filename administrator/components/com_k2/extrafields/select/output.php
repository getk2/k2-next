<?php
/**
 * @version		3.0.0
 * @package		K2
 * @author		JoomlaWorks http://www.joomlaworks.net
 * @copyright	Copyright (c) 2006 - 2014 JoomlaWorks Ltd. All rights reserved.
 * @license		GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 */

// no direct access
defined('_JEXEC') or die ; ?>

<?php if($field->get('multiple')): ?>
<div>	
	<?php foreach($field->get('value', array()) as $value): ?>
		<?php echo $value; ?>
	<?php endforeach; ?>
</div>	
<?php else : ?>
	<?php if($value = $field->get('value')): ?>
	<div>
		<?php echo $field->get('value'); ?>
	</div>	
	<?php endif; ?>
<?php endif; ?>
