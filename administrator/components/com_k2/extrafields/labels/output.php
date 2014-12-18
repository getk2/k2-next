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

<?php $labels = explode(',', $field->get('value')); ?>

<?php if(is_array($labels)): ?>
	<?php foreach ($labels as $label): ?>
		<?php if($label = trim($label)): ?>
		<a href="<?php echo JRoute::_(K2HelperRoute::getSearchRoute().'&searchword='.urlencode($label)).'">'.$label; ?></a>
		<?php endif; ?>
	<?php endforeach; ?>
<?php endif; ?>

