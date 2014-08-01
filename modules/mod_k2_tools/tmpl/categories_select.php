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

<div id="k2ModuleBox<?php echo $module->id; ?>" class="k2CategorySelectBlock<?php if($params->get('moduleclass_sfx')) echo ' '.$params->get('moduleclass_sfx'); ?>">
	<form action="<?php echo JRoute::_('index.php'); ?>" method="get">
		<select name="category" onchange="window.location = this.form.category.value;">
			<option value="<?php echo JURI::base(true); ?>"><?php echo JText::_('K2_SELECT_CATEGORY'); ?></option>
			<?php foreach ($categories as $key => $category): ?>
			<option <?php if($category->active) echo 'selected="selected"'; ?> value="<?php echo $category->link; ?>"><?php echo str_repeat('&ndash; ', $category->level); ?><?php echo $category->title; ?></option>
			<?php endforeach; ?>
		</select>
	</form>
</div>
