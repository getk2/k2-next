<?php
/**
 * @version		3.0.0b
 * @package		K2
 * @author		JoomlaWorks http://www.joomlaworks.net
 * @copyright	Copyright (c) 2006 - 2014 JoomlaWorks Ltd. All rights reserved.
 * @license		GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 */

// no direct access
defined('_JEXEC') or die ;
 ?>

<div>
	<!-- K2 Plugins: K2BeforeDisplay -->
	<?php echo $this->item->events->K2BeforeDisplay; ?>
	
	<div class="itemHeader">

		<?php if($this->params->get('listItemDateCreated')): ?>
		<!-- Date created -->
		<span class="itemDateCreated">
			<?php echo JHtml::_('date', $this->item->created, JText::_('K2_DATE_FORMAT_LC2')); ?>
		</span>
		<?php endif; ?>

	  <?php if($this->params->get('listItemTitle')): ?>
	  <!-- Item title -->
	  <h2 class="itemTitle">
			<?php if($this->item->canEdit): ?>
			<!-- Item edit link -->
			<span class="itemEditLink">
				<a href="<?php echo $this->item->editLink; ?>">
					<?php echo JText::_('K2_EDIT_ITEM'); ?>
				</a>
			</span>
			<?php endif; ?>
		
		<?php if ($this->params->get('listItemTitleLinked')): ?>
			<a href="<?php echo $this->item->link; ?>"><?php echo $this->item->title; ?></a>
		<?php else: ?>
	  		<?php echo $this->item->title; ?>
		<?php endif; ?>
		
	  	<?php if($this->params->get('listItemFeaturedNotice') && $this->item->featured): ?>
	  	<!-- Featured flag -->
	  	<span>
		  	<sup>
		  		<?php echo JText::_('K2_FEATURED'); ?>
		  	</sup>
	  	</span>
	  	<?php endif; ?>

	  </h2>
	  <?php endif; ?>

		<?php if($this->params->get('listItemAuthor')): ?>
		<!-- Item Author -->
		<span class="itemAuthor">
			<?php echo K2HelperUtilities::writtenBy($this->item->author->gender); ?>&nbsp;<?php if(empty($this->item->created_by_alias)): ?>
			<a rel="author" href="<?php echo $this->item->author->link; ?>"><?php echo $this->item->author->name; ?></a>
			<?php else: ?>
			<?php echo $this->item->author->name; ?>
			<?php endif; ?>
		</span>
		<?php endif; ?>

  </div>

  <!-- Plugins: AfterDisplayTitle -->
  <?php echo $this->item->events->AfterDisplayTitle; ?>

  <!-- K2 Plugins: K2AfterDisplayTitle -->
  <?php echo $this->item->events->K2AfterDisplayTitle; ?>


  <div class="itemBody">

	  <!-- Plugins: BeforeDisplayContent -->
	  <?php echo $this->item->events->BeforeDisplayContent; ?>

	  <!-- K2 Plugins: K2BeforeDisplayContent -->
	  <?php echo $this->item->events->K2BeforeDisplayContent; ?>

	  <?php if($this->params->get('listItemImage') && $this->item->image): ?>
	  		  	
	  <!-- Item Image -->
	  <div class="itemImageBlock">
		  <span class="itemImage">
		  	<a href="<?php echo $this->item->link; ?>" title="<?php echo $this->escape($this->item->image->alt); ?>">
		  		<img src="<?php echo $this->item->image->src; ?>" alt="<?php echo $this->escape($this->item->image->alt); ?>" style="width:<?php echo $this->item->image->width; ?>px; height:auto;" />
		  	</a>
		  </span>

		  <?php if($this->params->get('listItemImageMainCaption') && $this->item->image->caption): ?>
		  <!-- Image caption -->
		  <span class="itemImageCaption"><?php echo $this->item->image->caption; ?></span>
		  <?php endif; ?>

		  <?php if($this->params->get('listItemImageMainCredits') && $this->item->image->credits): ?>
		  <!-- Image credits -->
		  <span class="itemImageCredits"><?php echo $this->item->image->credits; ?></span>
		  <?php endif; ?>

		  <div class="clr"></div>
	  </div>
	  <?php endif; ?>

	  <?php if(!empty($this->item->fulltext)): ?>
	  <?php if($this->params->get('listItemIntroText')): ?>
	  <!-- Item introtext -->
	  <div class="itemIntroText">
	  	<?php echo $this->item->introtext; ?>
	  </div>
	  <?php endif; ?>
	  <?php if($this->params->get('listItemFullText')): ?>
	  <!-- Item fulltext -->
	  <div class="itemFullText">
	  	<?php echo $this->item->fulltext; ?>
	  </div>
	  <?php endif; ?>
	  <?php else: ?>
	  <!-- Item text -->
	  <div class="itemFullText">
	  	<?php echo $this->item->introtext; ?>
	  </div>
	  <?php endif; ?>

		<div class="clr"></div>

	  <?php if($this->params->get('listItemExtraFields') && count($this->item->extraFieldsGroups)): ?>
	  <!-- Item extra fields -->
	  <div class="itemExtraFields">
	  	<h3><?php echo JText::_('K2_ADDITIONAL_INFO'); ?></h3>
	  	<?php foreach ($this->item->extraFieldsGroups as $extraFieldGroup): ?>
	  	<h4><?php echo $extraFieldGroup->name; ?></h4>
	  	<ul>
			<?php foreach ($extraFieldGroup->fields as $key=>$extraField): ?>
			<?php if($extraField->output): ?>
			<li class="<?php echo ($key%2) ? "odd" : "even"; ?> type<?php echo ucfirst($extraField->type); ?> group<?php echo $extraField->group; ?>">
				<span class="itemExtraFieldsLabel"><?php echo $extraField->name; ?>:</span>
				<span class="itemExtraFieldsValue"><?php echo $extraField->output; ?></span>
			</li>
			<?php endif; ?>
			<?php endforeach; ?>
		</ul>
		<?php endforeach; ?>
	    <div class="clr"></div>
	  </div>
	  <?php endif; ?>

		<?php if($this->params->get('listItemHits') || ($this->params->get('listItemDateModified') && intval($this->item->modified)!=0)): ?>
		<div class="itemContentFooter">

			<?php if($this->params->get('listItemHits')): ?>
			<!-- Item Hits -->
			<span class="itemHits">
				<?php echo JText::_('K2_READ'); ?> <b><?php echo $this->item->hits; ?></b> <?php echo JText::_('K2_TIMES'); ?>
			</span>
			<?php endif; ?>

			<?php if($this->params->get('listItemDateModified') && intval($this->item->modified)!=0): ?>
			<!-- Item date modified -->
			<span class="itemDateModified">
				<?php echo JText::_('K2_LAST_MODIFIED_ON'); ?> <?php echo JHTML::_('date', $this->item->modified, JText::_('K2_DATE_FORMAT_LC2')); ?>
			</span>
			<?php endif; ?>

			<div class="clr"></div>
		</div>
		<?php endif; ?>

	  <!-- Plugins: AfterDisplayContent -->
	  <?php echo $this->item->events->AfterDisplayContent; ?>

	  <!-- K2 Plugins: K2AfterDisplayContent -->
	  <?php echo $this->item->events->K2AfterDisplayContent; ?>

	  <div class="clr"></div>
  </div>


  <?php if($this->params->get('listItemCategory') || $this->params->get('listItemTags') || $this->params->get('listItemAttachments')): ?>
  <div class="itemLinks">

		<?php if($this->params->get('listItemCategory')): ?>
		<!-- Item category -->
		<div class="itemCategory">
			<span><?php echo JText::_('K2_PUBLISHED_IN'); ?></span>
			<a href="<?php echo $this->item->category->link; ?>"><?php echo $this->item->category->title; ?></a>
		</div>
		<?php endif; ?>

	  <?php if($this->params->get('listItemTags') && count($this->item->tags)): ?>
	  <!-- Item tags -->
	  <div class="itemTagsBlock">
		  <span><?php echo JText::_('K2_TAGGED_UNDER'); ?></span>
		  <ul class="itemTags">
		    <?php foreach ($this->item->tags as $tag): ?>
		    <li><a href="<?php echo $tag->link; ?>"><?php echo $tag->name; ?></a></li>
		    <?php endforeach; ?>
		  </ul>
		  <div class="clr"></div>
	  </div>
	  <?php endif; ?>

	  <?php if($this->params->get('listItemAttachments') && count($this->item->attachments)): ?>
	  <!-- Item attachments -->
	  <div class="itemAttachmentsBlock">
		  <span><?php echo JText::_('K2_DOWNLOAD_ATTACHMENTS'); ?></span>
		  <ul class="itemAttachments">
		    <?php foreach ($this->item->attachments as $attachment): ?>
		    <li>
			    <a title="<?php echo $this->escape($attachment->title); ?>" href="<?php echo $attachment->link; ?>"><?php echo $attachment->name; ?></a>
			    <?php if($this->params->get('listItemAttachmentsCounter')): ?>
			    <span>(<?php echo $attachment->downloads; ?> <?php echo ($attachment->downloads==1) ? JText::_('K2_DOWNLOAD') : JText::_('K2_DOWNLOADS'); ?>)</span>
			    <?php endif; ?>
		    </li>
		    <?php endforeach; ?>
		  </ul>
	  </div>
	  <?php endif; ?>

		<div class="clr"></div>
  </div>
  <?php endif; ?>

  <?php if($this->params->get('listItemAuthorBlock') && empty($this->item->created_by_alias)): ?>
  <!-- Author Block -->
  <div class="itemAuthorBlock">

  	<?php if($this->params->get('listItemAuthorImage') && $this->item->author->image): ?>
  	<img class="itemAuthorAvatar" src="<?php echo $this->item->author->image->src; ?>" alt="<?php echo $this->item->author->name; ?>" />
  	<?php endif; ?>

    <div class="itemAuthorDetails">
      <h3 class="itemAuthorName">
      	<a rel="author" href="<?php echo $this->item->author->link; ?>"><?php echo $this->item->author->name; ?></a>
      </h3>

      <?php if($this->params->get('listItemAuthorDescription') && !empty($this->item->author->description)): ?>
      <p><?php echo $this->item->author->description; ?></p>
      <?php endif; ?>

      <?php if($this->params->get('listItemAuthorURL') && !empty($this->item->author->site)): ?>
      <span class="itemAuthorUrl"><?php echo JText::_('K2_WEBSITE'); ?> <a rel="me" href="<?php echo $this->item->author->site; ?>" target="_blank"><?php echo str_replace('http://', '', $this->item->author->site); ?></a></span>
      <?php endif; ?>

      <?php if($this->params->get('listItemAuthorEmail')): ?>
      <span class="itemAuthorEmail"><?php echo JText::_('K2_EMAIL'); ?> <?php echo JHTML::_('Email.cloak', $this->item->author->email); ?></span>
      <?php endif; ?>

			<div class="clr"></div>

			<!-- K2 Plugins: K2UserDisplay -->
			<?php echo $this->item->author->events->K2UserDisplay; ?>

    </div>
    <div class="clr"></div>
  </div>
  <?php endif; ?>

  <?php if($this->params->get('listItemAuthorLatest') && trim($this->item->created_by_alias) == '' && count($this->item->author->latest)): ?>
  <!-- Latest items from author -->
	<div class="itemAuthorLatest">
		<h3><?php echo JText::_('K2_LATEST_FROM'); ?> <?php echo $this->item->author->name; ?></h3>
		<ul>
			<?php foreach($this->item->author->latest as $key=>$item): ?>
			<li class="<?php echo ($key%2) ? "odd" : "even"; ?>">
				<a href="<?php echo $item->link ?>"><?php echo $item->title; ?></a>
			</li>
			<?php endforeach; ?>
		</ul>
		<div class="clr"></div>
	</div>
	<?php endif; ?>
	
  <?php if($this->params->get('listItemRelated') && count($this->item->related)): ?>
  <!-- Related items by tag -->
	<div class="itemRelated">
		<h3><?php echo JText::_("K2_RELATED_ITEMS_BY_TAG"); ?></h3>
		<ul>
			<?php foreach($this->item->related as $key=>$item): ?>
			<li class="<?php echo ($key%2) ? "odd" : "even"; ?>">

				<?php if($this->params->get('listItemRelatedTitle', 1)): ?>
				<a class="itemRelTitle" href="<?php echo $item->link ?>"><?php echo $item->title; ?></a>
				<?php endif; ?>

				<?php if($this->params->get('listItemRelatedCategory')): ?>
				<div class="itemRelCat"><?php echo JText::_("K2_IN"); ?> <a href="<?php echo $item->category->link ?>"><?php echo $item->category->title; ?></a></div>
				<?php endif; ?>

				<?php if($this->params->get('listItemRelatedAuthor')): ?>
				<div class="itemRelAuthor"><?php echo JText::_("K2_BY"); ?> <a rel="author" href="<?php echo $item->author->link; ?>"><?php echo $item->author->name; ?></a></div>
				<?php endif; ?>

				<?php if($this->params->get('listItemRelatedImageSize') && $item->image): ?>
				<img style="width:<?php echo $item->image->width; ?>px;height:auto;" class="itemRelImg" src="<?php echo $item->image->src; ?>" alt="<?php echo $item->image->alt; ?>" />
				<?php endif; ?>

				<?php if($this->params->get('listItemRelatedIntrotext')): ?>
				<div class="itemRelIntrotext"><?php echo $item->introtext; ?></div>
				<?php endif; ?>

				<?php if($this->params->get('listItemRelatedFulltext')): ?>
				<div class="itemRelFulltext"><?php echo $item->fulltext; ?></div>
				<?php endif; ?>

				<?php if($this->params->get('listItemRelatedMedia') && count($item->media)): ?>
				  <div class="itemRelMediaBlock">
				  	<?php foreach ($item->media as $entry) : ?>
					<div class="itemRelMedia">
						<span class="itemRelMediaOutput"><?php echo $entry->output; ?></span>
						<div class="clr"></div>
				  	</div> 
					<?php endforeach; ?>
				  </div>
				<?php endif; ?>

				<?php if($this->params->get('listItemRelatedImageGallery') && count($item->galleries)): ?>
					<div class="itemRelImageGalleries">
				  	<?php foreach ($item->galleries as $gallery): ?>
				  		<div class="itemRelImageGallery">
				  			<?php echo $gallery->output; ?>
				  		</div>
				  	<?php endforeach; ?>
  					</div>
				<?php endif; ?>
			</li>
			<?php endforeach; ?>
			<li class="clr"></li>
		</ul>
		<div class="clr"></div>
	</div>
	<?php endif; ?>

	<div class="clr"></div>

  <?php if($this->params->get('listItemMedia') && count($this->item->media)): ?>
  <!-- Item media -->
  <a name="itemMediaAnchor" id="itemMediaAnchor"></a>
  
  <div class="itemMediaBlock">
  	<h3><?php echo JText::_('K2_MEDIA'); ?></h3>
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

  <?php if($this->params->get('listItemGalleries') && count($this->item->galleries)): ?>
  <!-- Item image galleries -->
  <a name="itemImageGalleriesAnchor" id="itemImageGalleriesAnchor"></a>
  <div class="itemImageGalleries">
  	<h3><?php echo JText::_('K2_IMAGE_GALLERIES'); ?></h3>
  	<?php foreach ($this->item->galleries as $gallery): ?>
  		<div class="itemImageGallery">
  			<?php echo $gallery->output; ?>
  		</div>
  	<?php endforeach; ?>
  </div>
  <?php endif; ?>
  
  <?php if ($this->params->get('listItemReadMore')): ?>
	<!-- Item "read more..." link -->
	<div class="itemReadMore">
		<a class="k2ReadMore" href="<?php echo $this->item->link; ?>">
			<?php echo JText::_('K2_READ_MORE'); ?>
		</a>
	</div>
   <?php endif; ?>

  <!-- K2 Plugins: K2AfterDisplay -->
  <?php echo $this->item->events->K2AfterDisplay; ?>
    
<div class="clr"></div>

</div>
