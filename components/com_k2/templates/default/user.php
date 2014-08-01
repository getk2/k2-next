<?php
/**
 * @version		3.0.0b
 * @package		K2
 * @author		JoomlaWorks http://www.joomlaworks.net
 * @copyright	Copyright (c) 2006 - 2014 JoomlaWorks Ltd. All rights reserved.
 * @license		GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 */

// no direct access
defined('_JEXEC') or die; ?>

<div id="k2Container" class="userView<?php if($this->params->get('pageclass_sfx')) echo ' '.$this->params->get('pageclass_sfx'); ?>">

	<?php if($this->params->get('show_page_heading')): ?>
	<!-- Page heading -->
	<h1>
		<?php echo $this->escape($this->params->get('page_heading')); ?>
	</h1>
	<?php endif; ?>
	
	<?php if($this->params->get('userFeedIcon')): ?>
	<!-- RSS feed icon -->
	<div class="k2FeedIcon">
		<a href="<?php echo $this->feedLink; ?>" title="<?php echo JText::_('K2_SUBSCRIBE_TO_THIS_RSS_FEED'); ?>">
			<span><?php echo JText::_('K2_SUBSCRIBE_TO_THIS_RSS_FEED'); ?></span>
		</a>
		<div class="clr"></div>
	</div>
	<?php endif; ?>
	
	<?php if ($this->params->get('userImage') || $this->params->get('userName') || $this->params->get('userDescription') || $this->params->get('userURL') || $this->params->get('userEmail')): ?>
	<div class="userBlock">
	
		<?php if(isset($this->addLink) && JRequest::getInt('id') == $this->user->id): ?>
		<!-- Item add link -->
		<span class="userItemAddLink">
			<a href="<?php echo $this->addLink; ?>">
				<?php echo JText::_('K2_POST_A_NEW_ITEM'); ?>
			</a>
		</span>
		<?php endif; ?>
	
		<?php if ($this->params->get('userImage') && $this->author->image): ?>
		<img src="<?php echo $this->author->image->src; ?>" alt="<?php echo htmlspecialchars($this->author->image->alt, ENT_QUOTES, 'UTF-8'); ?>" style="width:<?php echo $this->params->get('userImageWidth'); ?>px; height:auto;" />
		<?php endif; ?>
		
		<?php if ($this->params->get('userName')): ?>
		<h2><?php echo $this->author->name; ?></h2>
		<?php endif; ?>
		
		<?php if ($this->params->get('userDescription') && $this->author->description): ?>
		<div class="userDescription"><?php echo $this->author->description; ?></div>
		<?php endif; ?>
		
		<?php if (($this->params->get('userURL') && $this->author->site) || $this->params->get('userEmail')): ?>
		<div class="userAdditionalInfo">
			<?php if ($this->params->get('userURL') && $this->author->site): ?>
			<span class="userURL">
				<?php echo JText::_('K2_WEBSITE_URL'); ?>: <a href="<?php echo $this->author->site; ?>" target="_blank" rel="me"><?php echo $this->author->site; ?></a>
			</span>
			<?php endif; ?>

			<?php if ($this->params->get('userEmail')): ?>
			<span class="userEmail">
				<?php echo JText::_('K2_EMAIL'); ?>: <?php echo JHtml::_('email.cloak', $this->author->email); ?>
			</span>
			<?php endif; ?>	
		</div>
		<?php endif; ?>
		
		<?php if($this->params->get('userExtraFields') && count($this->author->extraFieldsGroups)): ?>
		<!-- Tag extra fields -->
		<div class="userExtraFields">
			<h3><?php echo JText::_('K2_ADDITIONAL_INFO'); ?></h3>
			<?php foreach ($this->author->extraFieldsGroups as $extraFieldGroup): ?>
			<h4><?php echo $extraFieldGroup->name; ?></h4>
			<ul>
			<?php foreach ($extraFieldGroup->fields as $key=>$extraField): ?>
			<?php if($extraField->output): ?>
				<li class="<?php echo ($key%2) ? "odd" : "even"; ?> type<?php echo ucfirst($extraField->type); ?> group<?php echo $extraField->group; ?>">
					<span class="userExtraFieldsLabel"><?php echo $extraField->name; ?>:</span>
					<span class="userExtraFieldsValue"><?php echo $extraField->output; ?></span>
				</li>
			<?php endif; ?>
			<?php endforeach; ?>
			</ul>
			<?php endforeach; ?>
			<div class="clr"></div>
		</div>
		<?php endif; ?>

		<div class="clr"></div>
		
		<?php echo $this->author->events->K2UserDisplay; ?>
		
		<div class="clr"></div>
	</div>
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