<?php
/**
 * @version		3.0.0
 * @package		K2
 * @author		JoomlaWorks http://www.joomlaworks.net
 * @copyright	Copyright (c) 2006 - 2013 JoomlaWorks Ltd. All rights reserved.
 * @license		GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 */

// no direct access
defined('_JEXEC') or die ; ?>

<div id="k2ModuleBox<?php echo $module->id; ?>" class="k2BreadcrumbsBlock<?php if($params->get('moduleclass_sfx')) echo ' '.$params->get('moduleclass_sfx'); ?>">
	
	<span class="bcTitle"><?php echo JText::_('K2_YOU_ARE_HERE'); ?></span>
	<?php if ($params->get('home')): ?>
		<a href="<?php echo JURI::root(); ?>"><?php echo $breadcrumbs->home; ?></a><span class="bcSeparator"><?php echo $breadcrumbs->separator; ?></span>
	<?php endif; ?>
	
	<?php foreach($breadcrumbs->path as $entry): ?>
		<a href="<?php echo $entry->link; ?>"><?php echo $entry->title; ?></a><span class="bcSeparator"><?php echo $breadcrumbs->separator; ?></span>
	<?php endforeach; ?>
	
	<?php echo $breadcrumbs->title; ?>

</div>
