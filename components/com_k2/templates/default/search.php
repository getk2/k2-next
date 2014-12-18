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

<div id="k2Container" class="searchView<?php if($this->params->get('pageclass_sfx')) echo ' '.$this->params->get('pageclass_sfx'); ?>">

	<?php if($this->params->get('show_page_heading')): ?>
	<!-- Page heading -->
	<h1>
		<?php echo $this->escape($this->params->get('page_heading')); ?>
	</h1>
	<?php endif; ?>
	
	<?php if($this->params->get('genericFeedIcon')): ?>
	<!-- RSS feed icon -->
	<div class="k2FeedIcon">
		<a href="<?php echo $this->feedLink; ?>" title="<?php echo JText::_('K2_SUBSCRIBE_TO_THIS_RSS_FEED'); ?>">
			<span><?php echo JText::_('K2_SUBSCRIBE_TO_THIS_RSS_FEED'); ?></span>
		</a>
		<div class="clr"></div>
	</div>
	<?php endif; ?>
	
	<form action="<?php echo $this->action; ?>" method="get" autocomplete="off">
		<input type="text" placeholder="<?php echo JText::_('K2_SEARCH'); ?>" name="searchword" alt="<?php echo JText::_('K2_SEARCH'); ?>" value="<?php echo $this->escape($this->searchword); ?>" />
		<input type="submit" value="<?php echo JText::_('K2_SEARCH'); ?>" />
	</form>

	<?php if(count($this->items)): ?>
	<div class="itemList">
		<?php foreach($this->items as $item): ?>
			<?php $this->item = $item; echo $this->loadItemlistLayout(); ?>
		<?php endforeach; ?>
	</div>
	<?php elseif($this->searchword): ?>
	<div id="genericItemListNothingFound">
		<p><?php echo JText::_('K2_NO_RESULTS_FOUND'); ?></p>
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