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

<div id="k2Container" class="itemlistView<?php if($this->params->get('pageclass_sfx')) echo ' '.$this->params->get('pageclass_sfx'); ?>">

	<?php if($this->params->get('show_page_heading')): ?>
	<!-- Page heading -->
	<h1>
		<?php echo $this->escape($this->params->get('page_heading')); ?>
	</h1>
	<?php endif; ?>
	
	<?php if(count($this->items)): ?>
	<div class="itemList">
		<?php foreach($this->items as $item): ?>
			<?php $this->item = $item; echo $this->loadItemlistLayout(); ?>
		<?php endforeach; ?>
	</div>
	<?php endif; ?>	
	
	<?php if($this->pagination->get('pages.total') > 1): ?>
	<!-- Pagination -->
	<div class="k2Pagination pagination">
		<?php echo $this->pagination->getPagesLinks(); ?>
		<div class="clr"></div>
		<?php echo $this->pagination->getPagesCounter(); ?>
	</div>
	<?php endif; ?>

</div>