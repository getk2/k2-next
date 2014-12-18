<?php
/**
 * @version		3.0.0
 * @package		K2
 * @author		JoomlaWorks http://www.joomlaworks.net
 * @copyright	Copyright (c) 2006 - 2014 JoomlaWorks Ltd. All rights reserved.
 * @license		GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 */

// no direct access
defined('_JEXEC') or die; ?>

<div data-role="k2-toolbar">
	
	<span><?php echo $author->name; ?></span>
	
	<?php if($author->image): ?>
	<img src="<?php echo $author->image; ?>" alt="<?php echo htmlspecialchars($author->name, ENT_QUOTES, 'UTF-8'); ?>" />
	<?php endif; ?>
	
	<a data-role="k2-admin-link" href="<?php echo JURI::root(true); ?>/index.php?option=com_k2&view=admin"><?php echo JText::_('K2_MANAGE_YOUR_CONTENT'); ?></a>
	
</div>
