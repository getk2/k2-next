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

<div id="k2ModuleBox<?php echo $module->id; ?>" class="k2CategoriesListBlock<?php if($params->get('moduleclass_sfx')) echo ' '.$params->get('moduleclass_sfx'); ?>">
	<ul class="level0">
	<?php $level = 0; foreach ($categories as $key => $category): ?>
		<li <?php if($category->active) echo 'class="activeCategory"'; ?>>
			<a href="<?php echo $category->link ; ?>">
				<span class="catTitle"><?php echo $category->title; ?></span>
				<?php if($params->get('categoriesListItemsCounter')): ?>
				<span class="catCounter"><?php echo $category->numOfItems; ?></span>
				<?php endif; ?>
			</a>
			
		<?php if(isset($categories[$key+1]) && $categories[$key]->level < $categories[$key+1]->level): $level++; ?>
			<ul class="level<?php echo $level; ?>">
		<?php endif; ?>
	
		<?php if(isset($categories[$key+1]) && $categories[$key]->level > $categories[$key+1]->level): $level--; ?>
		<?php echo str_repeat('</li></ul>', $categories[$key]->level - $categories[$key+1]->level); ?>
		<?php endif; ?>
	
		<?php if(isset($categories[$key+1]) && $categories[$key]->level == $categories[$key+1]->level): ?>
		</li>
		<?php endif; ?>
		
	<?php endforeach; ?>
	</ul>
</div>
