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

<!-- Start K2 Item Layout -->
<article class="latestItemView">

	<!-- K2 Plugins: K2BeforeDisplay -->
	<?php echo $this->item->events->K2BeforeDisplay; ?>

	<header class="latestItemHeader">
	  <?php if($this->params->get('latestItemTitle')): ?>
	  <!-- Item title -->
	  <h2 class="latestItemTitle">
	  	<?php if ($this->params->get('latestItemTitleLinked')): ?>
			<a href="<?php echo $this->item->link; ?>">
	  		<?php echo $this->item->title; ?>
	  	</a>
	  	<?php else: ?>
	  	<?php echo $this->item->title; ?>
	  	<?php endif; ?>
	  </h2>
	  <?php endif; ?>
  	</header>
  
	<?php if($this->params->get('latestItemDateCreated')): ?>
	<!-- Date created -->
	<span class="latestItemDateCreated">
		<?php echo JHtml::_('date', $this->item->created , JText::_('K2_DATE_FORMAT_LC2')); ?>
	</span>
	<?php endif; ?>

  <!-- Plugins: AfterDisplayTitle -->
  <?php echo $this->item->events->AfterDisplayTitle; ?>

  <!-- K2 Plugins: K2AfterDisplayTitle -->
  <?php echo $this->item->events->K2AfterDisplayTitle; ?>

  <div class="latestItemBody">

	  <!-- Plugins: BeforeDisplayContent -->
	  <?php echo $this->item->events->BeforeDisplayContent; ?>

	  <!-- K2 Plugins: K2BeforeDisplayContent -->
	  <?php echo $this->item->events->K2BeforeDisplayContent; ?>

	  <?php if($this->params->get('latestItemImage') && $this->item->image): ?>
	  <!-- Item Image -->
	  <div class="latestItemImageBlock">
		  <span class="latestItemImage">
		  	<a href="<?php echo $this->item->link; ?>" title="<?php echo $this->escape($this->item->image->alt); ?>">
		  		<img src="<?php echo $this->item->image->src; ?>" alt="<?php echo $this->escape($this->item->image->alt); ?>" style="width:<?php echo $this->item->image->width; ?>px; height:auto;" />
		    </a>
		  </span>
		  <div class="clr"></div>
	  </div>
	  <?php endif; ?>

	  <?php if($this->params->get('latestItemIntroText')): ?>
	  <!-- Item introtext -->
	  <div class="latestItemIntroText">
	  	<?php echo $this->item->introtext; ?>
	  </div>
	  <?php endif; ?>

		<div class="clr"></div>

	  <!-- Plugins: AfterDisplayContent -->
	  <?php echo $this->item->events->AfterDisplayContent; ?>

	  <!-- K2 Plugins: K2AfterDisplayContent -->
	  <?php echo $this->item->events->K2AfterDisplayContent; ?>

	  <div class="clr"></div>
  </div>

  <?php if($this->params->get('latestItemCategory') || $this->params->get('latestItemTags')): ?>
  <div class="latestItemLinks">

		<?php if($this->params->get('latestItemCategory')): ?>
		<!-- Item category name -->
		<div class="latestItemCategory">
			<span><?php echo JText::_('K2_PUBLISHED_IN'); ?></span>
			<a href="<?php echo $this->item->category->link; ?>"><?php echo $this->item->category->title; ?></a>
		</div>
		<?php endif; ?>

	  <?php if($this->params->get('latestItemTags') && count($this->item->tags)): ?>
	  <!-- Item tags -->
	  <div class="latestItemTagsBlock">
		  <span><?php echo JText::_('K2_TAGGED_UNDER'); ?></span>
		  <ul class="latestItemTags">
		    <?php foreach ($this->item->tags as $tag): ?>
		    <li><a href="<?php echo $tag->link; ?>"><?php echo $tag->name; ?></a></li>
		    <?php endforeach; ?>
		  </ul>
		  <div class="clr"></div>
	  </div>
	  <?php endif; ?>

		<div class="clr"></div>
  </div>
  <?php endif; ?>

	<div class="clr"></div>

  <?php if($this->params->get('latestItemVideo') && count($this->item->media)): ?>
  <!-- Item video -->
  <div class="latestItemVideoBlock">
  	<h3><?php echo JText::_('K2_RELATED_MEDIA'); ?></h3>
  	
  	<?php foreach ($this->item->media as $entry) : ?>
	<div class="itemMedia">
		<span class="itemMediaOutput"><?php echo $entry->output; ?></span>
	
		<?php if(!empty($entry->caption)): ?>
		<span class="itemMediaCaption"><?php echo $entry->caption; ?></span>
		<?php endif; ?>
	
		<?php if(!empty($entry->credits)): ?>
		<span class="itemMediaCredits"><?php echo $entry->credits; ?></span>
		<?php endif; ?>
	
		<div class="clr"></div>
  	</div> 
	<?php endforeach; ?>
  </div>
  <?php endif; ?>

	<?php if($this->params->get('latestItemCommentsAnchor') && $this->params->get('comments')): ?>
	<!-- Anchor link to comments below -->
	<div class="latestItemCommentsLink">
		<?php if(!empty($this->item->events->K2CommentsCounter)): ?>
			<!-- K2 Plugins: K2CommentsCounter -->
			<?php echo $this->item->events->K2CommentsCounter; ?>
		<?php else: ?>
			<?php if($this->item->numOfComments > 0): ?>
			<a href="<?php echo $this->item->link; ?>#itemCommentsAnchor">
				<?php echo $this->item->numOfComments; ?> <?php echo ($this->item->numOfComments>1) ? JText::_('K2_COMMENTS') : JText::_('K2_COMMENT'); ?>
			</a>
			<?php else: ?>
			<a href="<?php echo $this->item->link; ?>#itemCommentsAnchor">
				<?php echo JText::_('K2_BE_THE_FIRST_TO_COMMENT'); ?>
			</a>
			<?php endif; ?>
		<?php endif; ?>
	</div>
	<?php endif; ?>

	<?php if ($this->params->get('latestItemReadMore')): ?>
	<!-- Item "read more..." link -->
	<div class="latestItemReadMore">
		<a class="k2ReadMore" href="<?php echo $this->item->link; ?>">
			<?php echo JText::_('K2_READ_MORE'); ?>
		</a>
	</div>
	<?php endif; ?>

	<div class="clr"></div>

  <!-- K2 Plugins: K2AfterDisplay -->
  <?php echo $this->item->events->K2AfterDisplay; ?>

	<div class="clr"></div>
</article>
<!-- End K2 Item Layout -->
