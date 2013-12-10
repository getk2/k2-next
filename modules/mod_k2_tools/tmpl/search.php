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

/*
Important note:
If you wish to use the live search option, it's important that you maintain the same class names for wrapping elements, e.g. the wrapping div and form.
*/

?>

<div id="k2ModuleBox<?php echo $module->id; ?>" class="k2SearchBlock<?php if($params->get('moduleclass_sfx')) echo ' '.$params->get('moduleclass_sfx'); if($params->get('liveSearch')) echo ' k2LiveSearchBlock'; ?>">
	<form action="<?php echo $search->action; ?>" method="get" autocomplete="off" class="k2SearchBlockForm">

		<input type="text" placeholder="<?php echo $search->text; ?>" name="searchword" maxlength="<?php echo $search->maxLength; ?>" size="<?php echo $search->width; ?>" alt="<?php echo $search->buttonText; ?>" />

		<?php if($search->button): ?>
			
		<?php if($search->imageButton): ?>
		<input type="image" value="<?php echo $search->buttonText; ?>" class="button" onclick="this.form.searchword.focus();" src="<?php echo JURI::base(true); ?>/components/com_k2/images/fugue/search.png" />
		<?php else: ?>
		<input type="submit" value="<?php echo $search->buttonText; ?>" class="button" onclick="this.form.searchword.focus();" />
		<?php endif; ?>
		
		<?php endif; ?>

		<input type="hidden" name="categories" value="<?php echo $search->filter; ?>" />
		<?php if(!$search->sef): ?>
		<input type="hidden" name="option" value="com_k2" />
		<input type="hidden" name="view" value="itemlist" />
		<input type="hidden" name="task" value="search" />
		<?php endif; ?>
		<?php if($params->get('liveSearch')): ?>
		<input type="hidden" name="format" value="html" />
		<input type="hidden" name="t" value="" />
		<input type="hidden" name="tpl" value="search" />
		<?php endif; ?>
	</form>

	<?php if($params->get('liveSearch')): ?>
	<div class="k2LiveSearchResults"></div>
	<?php endif; ?>
</div>