<?php
/**
 * @version		3.0.0
 * @package		K2
 * @author		JoomlaWorks http://www.joomlaworks.net
 * @copyright	Copyright (c) 2006 - 2014 JoomlaWorks Ltd. All rights reserved.
 * @license		GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 */

// no direct access
defined('_JEXEC') or die;

?>

<div id="k2UserToolbar" data-role="k2-toolbar">
	<div id="k2UserToolbarInnerContainer">
		<?php if($author->image): ?>
		<img src="<?php echo $author->image; ?>" alt="<?php echo htmlspecialchars($author->name, ENT_QUOTES, 'UTF-8'); ?>" />
		<?php endif; ?>
		<span><?php echo $author->name; ?></span>
		<a data-role="k2-admin-link" href="<?php echo JURI::root(true); ?>/index.php?option=com_k2&amp;view=admin">
			<?php echo JText::_('K2_MANAGE_YOUR_CONTENT'); ?>
		</a>
		<div id="k2InlineEditControls">
			<input id="auto-save" type="submit" name="toggleAutoSave" class="btn btn-primary" value="<?php echo JText::_('K2_AUTO_SAVE'); ?>">
			<input id="save" type="submit" name="Save" class="btn btn-primary" value="<?php echo JText::_('K2_SAVE'); ?>">
		</div>
	</div>
</div>
