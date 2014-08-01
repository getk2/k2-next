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
<div>
<?php if($field->get('multiple')): ?>
	
	<?php foreach($field->get('value', array()) as $value): ?>
		<?php echo $value; ?>
	<?php endforeach; ?>
	
<?php else : ?>

	<?php echo $field->get('value'); ?>
	
<?php endif; ?>
</div>