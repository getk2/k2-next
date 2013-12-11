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
<div id="k2ModuleBox<?php echo $module->id; ?>" class="k2TagCloudBlock<?php if($params->get('moduleclass_sfx')) echo ' '.$params->get('moduleclass_sfx'); ?>">
	<?php foreach ($tags as $tag): ?>
	<a href="<?php echo $tag->link; ?>" style="font-size:<?php echo $tag->size; ?>%" title="<?php echo htmlspecialchars($tag->counter.' '.JText::_('K2_ITEMS_TAGGED_WITH').' '.$tag->name); ?>">
		<?php echo $tag->name; ?>
	</a>
	<?php endforeach; ?>
	<div class="clr"></div>
</div>
