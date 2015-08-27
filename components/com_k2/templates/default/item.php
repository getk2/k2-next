<?php
/**
 * @version		3.0.0
 * @package		K2
 * @author		JoomlaWorks http://www.joomlaworks.net
 * @copyright	Copyright (c) 2006 - 2014 JoomlaWorks Ltd. All rights reserved.
 * @license		GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 */

// no direct access
defined('_JEXEC') or die ;
 ?>

<?php if($this->print): ?>
<!-- Print button at the top of the print page only -->
<a class="itemPrintThisPage" rel="nofollow" href="#" onclick="window.print();return false;">
	<span><?php echo JText::_('K2_PRINT_THIS_PAGE'); ?></span>
</a>
<?php endif; ?>

<!-- Start K2 Item Layout -->

<article id="k2Container" class="itemView<?php echo ($this->item->featured) ? ' itemIsFeatured' : ''; ?><?php if ($this->params->get('pageclass_sfx'))echo ' '.$this->params->get('pageclass_sfx');?>">

	<!-- K2 Plugins: K2BeforeDisplay -->
	<?php echo $this->item->events->K2BeforeDisplay; ?>
	
	<?php if($this->params->get('show_page_heading')): ?>
	<!-- Page heading -->
	<h1>
		<?php echo $this->escape($this->params->get('page_heading')); ?>
	</h1>
	<?php endif; ?>

	<header class="itemHeader">

		<?php if($this->params->get('itemDateCreated')): ?>
		<!-- Date created -->
		<span class="itemDateCreated">
			<?php echo JHtml::_('date', $this->item->created, JText::_('K2_DATE_FORMAT_LC2')); ?>
		</span>
		<?php endif; ?>

	  <?php if($this->params->get('itemTitle')): ?>
	  <!-- Item title -->
	  <h2 class="itemTitle">

	  	<span<?php if($this->item->canEdit && !$this->print): ?> data-k2-editable="title" data-k2-item="<?php echo $this->item->id; ?>" <?php endif; ?>><?php echo $this->item->title; ?></span>

	  	<?php if($this->params->get('itemFeaturedNotice') && $this->item->featured): ?>
	  	<!-- Featured flag -->
	  	<span>
		  	<sup>
		  		<?php echo JText::_('K2_FEATURED'); ?>
		  	</sup>
	  	</span>
	  	<?php endif; ?>

	  </h2>
	  <?php endif; ?>

		<?php if($this->params->get('itemAuthor')): ?>
		<!-- Item Author -->
		<span class="itemAuthor">
			<?php echo K2HelperUtilities::writtenBy($this->item->author->gender); ?>&nbsp;
			<?php if(empty($this->item->created_by_alias)): ?>
			<a rel="author" href="<?php echo $this->item->author->link; ?>"><?php echo $this->item->author->name; ?></a>
			<?php else: ?>
			<?php if($this->item->created_by_alias_url): ?>
				<a rel="author" href="<?php echo $this->item->created_by_alias_url; ?>" target="_blank"><?php echo $this->item->created_by_alias; ?></a>
			<?php else: ?>
				<span rel="author"><?php echo $this->item->created_by_alias; ?></span>
			<?php endif; ?>
			<?php endif; ?>
		</span>
		<?php endif; ?>

  </header>

  <!-- Plugins: AfterDisplayTitle -->
  <?php echo $this->item->events->AfterDisplayTitle; ?>

  <!-- K2 Plugins: K2AfterDisplayTitle -->
  <?php echo $this->item->events->K2AfterDisplayTitle; ?>
  
  <?php if(($this->item->canEdit || $this->params->get('itemPrintButton') || $this->params->get('itemEmailButton')) && !$this->print): ?>
  <div class="itemToolbar">
  	
	<?php if($this->item->canEdit && !$this->print): ?>
	<!-- Edit link -->
	<span class="itemEditLink">
		<a href="<?php echo $this->item->editLink; ?>">
			<?php echo JText::_('K2_EDIT_ITEM'); ?>
		</a>
	</span>
	<?php endif; ?>
  		
  	<?php if($this->params->get('itemPrintButton') && !$this->print): ?>
  	<!-- Print Button -->
	<a class="itemPrintLink" rel="nofollow" href="<?php echo $this->item->printLink; ?>" onclick="window.open(this.href,'printWindow','width=900,height=600,location=no,menubar=no,resizable=yes,scrollbars=yes'); return false;">
		<span><?php echo JText::_('K2_PRINT'); ?></span>
	</a>
	<?php endif; ?>
	
	<?php if($this->params->get('itemEmailButton') && !$this->print): ?>
	<!-- Email Button -->
	<a class="itemEmailLink" rel="nofollow" href="<?php echo $this->item->emailLink; ?>" onclick="window.open(this.href,'emailWindow','width=400,height=350,location=no,menubar=no,resizable=no,scrollbars=no'); return false;">
		<span><?php echo JText::_('K2_EMAIL'); ?></span>
	</a>
	<?php endif; ?>
	
  </div>
  <?php endif; ?>


  <div class="itemBody">

	  <!-- Plugins: BeforeDisplayContent -->
	  <?php echo $this->item->events->BeforeDisplayContent; ?>

	  <!-- K2 Plugins: K2BeforeDisplayContent -->
	  <?php echo $this->item->events->K2BeforeDisplayContent; ?>

	  <?php if($this->params->get('itemImage') && $this->item->image): ?>
	  		  	
	  <!-- Item Image -->
	  <figure class="itemImageBlock">
		  <span class="itemImage">
		  	<a class="k2Modal" href="<?php echo $this->item->images['modal']->src; ?>" title="<?php echo JText::_('K2_CLICK_TO_PREVIEW_IMAGE'); ?>">
		  		<img src="<?php echo $this->item->image->src; ?>" alt="<?php echo $this->escape($this->item->image->alt); ?>" style="width:<?php echo $this->item->image->width; ?>px; height:auto;" />
		  	</a>
		  </span>

		  <?php if($this->params->get('itemImageMainCaption') && $this->item->image->caption): ?>
		  <!-- Image caption -->
		  <figcaption class="itemImageCaption"><?php echo $this->item->image->caption; ?></figcaption>
		  <?php endif; ?>

		  <?php if($this->params->get('itemImageMainCredits') && $this->item->image->credits): ?>
		  <!-- Image credits -->
		  <span class="itemImageCredits"><?php echo $this->item->image->credits; ?></span>
		  <?php endif; ?>

		  <div class="clr"></div>
	  </figure>
	  <?php endif; ?>
		
	  <?php if($this->params->get('itemIntroText') && $this->item->introtext): ?>
	  <!-- Item introtext -->
	  <div class="itemIntroText"<?php if($this->item->canEdit && !$this->print): ?> data-k2-editable="introtext" data-k2-item="<?php echo $this->item->id; ?>" <?php endif; ?>>
	  	<?php echo $this->item->introtext; ?>
	  </div>
	  <?php endif; ?>
	  
	  <?php if($this->params->get('itemFullText') && $this->item->fulltext): ?>
	  <!-- Item fulltext -->
	  <div class="itemFullText"<?php if($this->item->canEdit && !$this->print): ?> data-k2-editable="fulltext" data-k2-item="<?php echo $this->item->id; ?>" <?php endif; ?>>
	  	<?php echo $this->item->fulltext; ?>
	  </div>
	  <?php endif; ?>
	 

	<div class="clr"></div>

	
	  <?php if($this->params->get('itemExtraFields') && count($this->item->extraFieldsGroups)): ?>
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

		<?php if($this->params->get('itemHits') || ($this->params->get('itemDateModified') && intval($this->item->modified)!=0)): ?>
		<div class="itemContentFooter">

			<?php if($this->params->get('itemHits')): ?>
			<!-- Item Hits -->
			<span class="itemHits">
				<?php echo JText::_('K2_READ'); ?> <b><?php echo $this->item->hits; ?></b> <?php echo JText::_('K2_TIMES'); ?>
			</span>
			<?php endif; ?>

			<?php if($this->params->get('itemDateModified') && intval($this->item->modified)!=0): ?>
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

	<?php if($this->params->get('itemTwitterButton',1) || $this->params->get('itemFacebookButton',1) || $this->params->get('itemGooglePlusOneButton',1)): ?>
	<!-- Social sharing -->
	<div class="itemSocialSharing">

		<?php if($this->params->get('itemTwitterButton',1)): ?>
		<!-- Twitter Button -->
		<div class="itemTwitterButton">
			<a href="https://twitter.com/share" class="twitter-share-button" data-count="horizontal"<?php if($this->params->get('twitterUsername')): ?> data-via="<?php echo $this->params->get('twitterUsername'); ?>"<?php endif; ?>>
				<?php echo JText::_('K2_TWEET'); ?>
			</a>
			<script type="text/javascript" src="//platform.twitter.com/widgets.js"></script>
		</div>
		<?php endif; ?>

		<?php if($this->params->get('itemFacebookButton',1)): ?>
		<!-- Facebook Button -->
		<div class="itemFacebookButton">
			<div id="fb-root"></div>
			<script type="text/javascript">
				( function(d, s, id) {
						var js, fjs = d.getElementsByTagName(s)[0];
						if (d.getElementById(id))
							return;
						js = d.createElement(s);
						js.id = id;
						js.src = "//connect.facebook.net/en_US/all.js#xfbml=1";
						fjs.parentNode.insertBefore(js, fjs);
					}(document, 'script', 'facebook-jssdk'));
			</script>
			<div class="fb-like" data-send="false" data-width="200" data-show-faces="true"></div>
		</div>
		<?php endif; ?>

		<?php if($this->params->get('itemGooglePlusOneButton',1)): ?>
		<!-- Google +1 Button -->
		<div class="itemGooglePlusOneButton">
			<g:plusone annotation="inline" width="120"></g:plusone>
			<script type="text/javascript">
				(function() {
					window.___gcfg = {
						lang : 'en'
					};
					// Define button default language here
					var po = document.createElement('script');
					po.type = 'text/javascript';
					po.async = true;
					po.src = 'https://apis.google.com/js/plusone.js';
					var s = document.getElementsByTagName('script')[0];
					s.parentNode.insertBefore(po, s);
				})();
			</script>
		</div>
		<?php endif; ?>

		<div class="clr"></div>
	</div>
	<?php endif; ?>

  <?php if($this->params->get('itemCategory') || $this->params->get('itemTags') || $this->params->get('itemAttachments')): ?>
  <div class="itemLinks">

		<?php if($this->params->get('itemCategory')): ?>
		<!-- Item category -->
		<div class="itemCategory">
			<span><?php echo JText::_('K2_PUBLISHED_IN'); ?></span>
			<a href="<?php echo $this->item->category->link; ?>"><?php echo $this->item->category->title; ?></a>
		</div>
		<?php endif; ?>

	  <?php if($this->params->get('itemTags') && count($this->item->tags)): ?>
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

	  <?php if($this->params->get('itemAttachments') && count($this->item->attachments)): ?>
	  <!-- Item attachments -->
	  <div class="itemAttachmentsBlock">
		  <span><?php echo JText::_('K2_DOWNLOAD_ATTACHMENTS'); ?></span>
		  <ul class="itemAttachments">
		    <?php foreach ($this->item->attachments as $attachment): ?>
		    <li>
			    <a title="<?php echo $this->escape($attachment->title); ?>" href="<?php echo $attachment->link; ?>"><?php echo $attachment->name; ?></a>
			    <?php if($this->params->get('itemAttachmentsCounter')): ?>
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

  <?php if($this->params->get('itemAuthorBlock') && empty($this->item->created_by_alias)): ?>
  <!-- Author Block -->
  <div class="itemAuthorBlock">

  	<?php if($this->params->get('itemAuthorImage') && $this->item->author->image): ?>
  	<img class="itemAuthorAvatar" src="<?php echo $this->item->author->image->src; ?>" alt="<?php echo $this->item->author->name; ?>" />
  	<?php endif; ?>

    <div class="itemAuthorDetails">
      <h3 class="itemAuthorName">
      	<a rel="author" href="<?php echo $this->item->author->link; ?>"><?php echo $this->item->author->name; ?></a>
      </h3>

      <?php if($this->params->get('itemAuthorDescription') && !empty($this->item->author->description)): ?>
      <p><?php echo $this->item->author->description; ?></p>
      <?php endif; ?>

      <?php if($this->params->get('itemAuthorURL') && !empty($this->item->author->site)): ?>
      <span class="itemAuthorUrl"><?php echo JText::_('K2_WEBSITE'); ?> <a rel="me" href="<?php echo $this->item->author->site; ?>" target="_blank"><?php echo str_replace('http://', '', $this->item->author->site); ?></a></span>
      <?php endif; ?>

      <?php if($this->params->get('itemAuthorEmail')): ?>
      <span class="itemAuthorEmail"><?php echo JText::_('K2_EMAIL'); ?> <?php echo JHTML::_('Email.cloak', $this->item->author->email); ?></span>
      <?php endif; ?>

			<div class="clr"></div>

			<!-- K2 Plugins: K2UserDisplay -->
			<?php echo $this->item->author->events->K2UserDisplay; ?>

    </div>
    <div class="clr"></div>
  </div>
  <?php endif; ?>

  <?php if($this->params->get('itemAuthorLatest') && count($this->item->author->latest)): ?>
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
	
  <?php if($this->params->get('itemRelated') && count($this->item->related)): ?>
  <!-- Related items by tag -->
	<div class="itemRelated">
		<h3><?php echo JText::_("K2_RELATED_ITEMS_BY_TAG"); ?></h3>
		<ul>
			<?php foreach($this->item->related as $key=>$item): ?>
			<li class="<?php echo ($key%2) ? "odd" : "even"; ?>">

				<?php if($this->params->get('itemRelatedTitle', 1)): ?>
				<a class="itemRelTitle" href="<?php echo $item->link ?>"><?php echo $item->title; ?></a>
				<?php endif; ?>

				<?php if($this->params->get('itemRelatedCategory')): ?>
				<div class="itemRelCat"><?php echo JText::_("K2_IN"); ?> <a href="<?php echo $item->category->link ?>"><?php echo $item->category->title; ?></a></div>
				<?php endif; ?>

				<?php if($this->params->get('itemRelatedAuthor')): ?>
				<div class="itemRelAuthor"><?php echo JText::_("K2_BY"); ?> <a rel="author" href="<?php echo $item->author->link; ?>"><?php echo $item->author->name; ?></a></div>
				<?php endif; ?>

				<?php if($this->params->get('itemRelatedImageSize') && $item->image): ?>
				<img style="width:<?php echo $item->image->width; ?>px;height:auto;" class="itemRelImg" src="<?php echo $item->image->src; ?>" alt="<?php echo $item->image->alt; ?>" />
				<?php endif; ?>

				<?php if($this->params->get('itemRelatedIntrotext')): ?>
				<div class="itemRelIntrotext"><?php echo $item->introtext; ?></div>
				<?php endif; ?>

				<?php if($this->params->get('itemRelatedFulltext')): ?>
				<div class="itemRelFulltext"><?php echo $item->fulltext; ?></div>
				<?php endif; ?>

				<?php if($this->params->get('itemRelatedMedia') && count($item->media)): ?>
				  <div class="itemRelMediaBlock">
				  	<?php foreach ($item->media as $entry) : ?>
					<div class="itemRelMedia">
						<span class="itemRelMediaOutput"><?php echo $entry->output; ?></span>
						<div class="clr"></div>
				  	</div> 
					<?php endforeach; ?>
				  </div>
				<?php endif; ?>

				<?php if($this->params->get('itemRelatedImageGallery') && count($item->galleries)): ?>
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

  <?php if($this->params->get('itemMedia') && count($this->item->media)): ?>
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

  <?php if(count($this->item->galleries)): ?>
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

  <?php if($this->params->get('itemNavigation') && !$this->print && ($this->item->next || $this->item->previous)): ?>
  <!-- Item navigation -->
  <nav class="itemNavigation">
  	<span class="itemNavigationTitle"><?php echo JText::_('K2_MORE_IN_THIS_CATEGORY'); ?></span>

		<?php if($this->item->previous): ?>
		<a class="itemPrevious" href="<?php echo $this->item->previous->link; ?>">
			&laquo; <?php echo $this->item->previous->title; ?>
		</a>
		<?php endif; ?>

		<?php if($this->item->next): ?>
		<a class="itemNext" href="<?php echo $this->item->next->link; ?>">
			<?php echo $this->item->next->title; ?> &raquo;
		</a>
		<?php endif; ?>

  </nav>
  <?php endif; ?>

  <!-- K2 Plugins: K2AfterDisplay -->
  <?php echo $this->item->events->K2AfterDisplay; ?>
  
  <?php if($this->params->get('itemComments') && $this->params->get('comments')): ?>
  <!-- K2 Plugins: K2CommentsBlock -->
  <?php echo $this->item->events->K2CommentsBlock; ?>
  <?php endif; ?>
  
  <?php if($this->params->get('itemComments') && $this->params->get('comments') && empty($this->item->events->K2CommentsBlock)): ?>
  <a name="itemCommentsAnchor" id="itemCommentsAnchor"></a>
  <div data-widget="k2comments" data-itemid="<?php echo $this->item->id; ?>"></div>
  
	<script type="text/template" id="k2CommentsTemplate">
		<div class="itemComments">
	
			<!-- Item comments -->
			<% if(comments.length) { %>
			<h3 class="itemCommentsCounter">
			<span><%= pagination.total %></span> 
			<% if(pagination.total > 1) { %>
			<?php echo JText::_('K2_COMMENTS'); ?>
			<% } else { %>
			<?php echo JText::_('K2_COMMENT'); ?>
			<% } %>
			</h3>
			
			<ul class="itemCommentsList">
				<% _(comments).each(function(comment) { %>
				<li class="<% if(comment.isAuthorResponse) print('authorResponse'); if(comment.state == 0) print(' unpublishedComment'); %>">
		
			    	<span class="commentLink">
				    	<a href="<?php echo $this->item->link; ?>#comment<%- comment.id %>" name="comment<%- comment.id %>" id="comment<%- comment.id %>">
				    		<?php echo JText::_('K2_COMMENT_LINK'); ?>
				    	</a>
				    </span>
		
					<% if(comment.user.image) { %>
					<img data-image-url="<%= comment.user.image.src %>" alt="<%- comment.user.name %>" width="<?php echo $this->params->get('commenterImgWidth'); ?>" />
					<% } %>
		
					<span class="commentDate"><%- comment.date %></span>
		
				    <span class="commentAuthorName">
					    <?php echo JText::_('K2_POSTED_BY'); ?>
					   <% if(comment.user.link) { %>
					    <a data-user-link="<%= comment.user.link %>" title="<%- comment.user.name %>" target="_blank" rel="nofollow">
					    	<%= comment.user.name %>
					    </a>
					    <% } else { %>
					    <%= comment.user.name %>
					    <% } %>
				    </span>
		
				    <p><%= comment.text %></p>
				    
				    <% if(comment.canEdit || comment.canReport) { %>
					<span class="commentToolbar">
						
						<% if(comment.canEdit) { %>
							
						<% if(comment.state < 1) { %>
						<button data-action="publish" data-id="<%- comment.id %>" class="commentApproveLink"><?php echo JText::_('K2_APPROVE')?></button>
						<% } %>
		
						<button data-action="delete" data-id="<%- comment.id %>" class="commentRemoveLink"><?php echo JText::_('K2_REMOVE')?></button>
		
						<% } %>
						
						<% if(comment.state == 1 && comment.canReport) { %>
						<button data-action="report" data-id="<%- comment.id %>"><?php echo JText::_('K2_REPORT')?></button>
						<% } %>
						
						<% if(comment.canReportUser) { %>
						<button data-action="report.user" data-id="<%- comment.userId %>" class="k2ReportUserButton"><?php echo JText::_('K2_FLAG_AS_SPAMMER'); ?></button>
						<% } %>
		
					</span>
					<% } %>
				    
		
					<div class="clr"></div>
			    </li>					
				<% }); %>
			</ul>
			
			<% if(pagination.total > pagination.limit) { %>
			<div class="itemCommentsPagination" data-role="pagination">
				<ul>
					<li><a data-page="1" href="#" class="k2CommentsPaginationStart"><?php echo JText::_('K2_START'); ?></a></li>
					<% if((pagination.pagesCurrent - 1) > 0) { %>
					<li><a data-page="previous" href="#" class="k2CommentsPaginationPrevious"><?php echo JText::_('K2_PREVIOUS'); ?></a></li>
					<% } %>
					<% for(i = pagination.pagesStart; i <= pagination.pagesStop; i++) { %>
					<li <% if(pagination.pagesCurrent == i) { %> class="active" <% } %>><a data-page="<%= i %>" href="#"><%= i %></a></li>
					<% } %>
					<% if((pagination.pagesCurrent + 1) <= pagination.pagesTotal) { %>
					<li><a data-page="next" href="#" class="k2CommentsPaginationNext"><?php echo JText::_('K2_NEXT'); ?></a></li>
					<% } %>
					<li><a data-page="<%= pagination.pagesTotal %>" href="#" class="k2CommentsPaginationEnd"><?php echo JText::_('K2_END'); ?></a></li>
				</ul>
				<div class="clr"></div>
			</div>
			<% } %>

			<% } %>
			
			<?php if(!$this->print && $this->user->canComment): ?>
			<!-- Item comments form -->
			<div class="itemCommentsForm">
				<h3><?php echo JText::_('K2_LEAVE_A_COMMENT') ?></h3>

				<?php if($this->params->get('commentsFormNotes')): ?>
				<p class="itemCommentsFormNotes">
					<?php if($this->params->get('commentsFormNotesText')): ?>
					<?php echo nl2br($this->params->get('commentsFormNotesText')); ?>
					<?php else: ?>
					<?php echo JText::_('K2_COMMENT_FORM_NOTES') ?>
					<?php endif; ?>
				</p>
				<?php endif; ?>
				
				<form action="<?php echo JRoute::_('index.php'); ?>" method="post" data-form="comments">
					<label for="k2CommentText"><?php echo JText::_('K2_MESSAGE'); ?> *</label>
					<textarea rows="10" cols="20" placeholder="<?php echo JText::_('K2_ENTER_YOUR_MESSAGE_HERE'); ?>" name="text" id="k2CommentText"></textarea>
					
					<label for="k2CommentName"><?php echo JText::_('K2_NAME'); ?> *</label>
					
					<?php if($this->user->guest): ?>
					<input type="text" name="name" id="k2CommentName" placeholder="<?php echo JText::_('K2_ENTER_YOUR_NAME'); ?>" />
					<?php else : ?>
					<input type="text" name="name" id="k2CommentName" value="<?php echo htmlspecialchars($this->user->name); ?>" readonly="readonly" />
					<?php endif; ?>
					
					<label for="k2CommentEmail"><?php echo JText::_('K2_EMAIL'); ?> *</label>
					
					<?php if($this->user->guest): ?>
					<input type="email" name="email" id="k2CommentEmail" placeholder="<?php echo JText::_('K2_ENTER_YOUR_EMAIL_ADDRESS'); ?>"  />
					<?php else : ?>
					<input type="email" name="email" id="k2CommentEmail" value="<?php echo htmlspecialchars($this->user->email); ?>" readonly="readonly" />
					<?php endif; ?>
					
					<label for="k2CommentUrl"><?php echo JText::_('K2_WEBSITE_URL'); ?></label>
					<input type="text" name="url" id="k2CommentUrl" placeholder="<?php echo JText::_('K2_ENTER_YOUR_SITE_URL'); ?>" />
				
					<?php echo K2HelperCaptcha::display(); ?>
				
					<button type="submit" data-action="create"><?php echo JText::_('K2_SUBMIT_COMMENT'); ?></button>
					
					<input type="hidden" name="itemId" value="<?php echo $this->item->id; ?>" />
					
					<span data-role="log"></span>
					
				</form>
			</div>
			<?php endif; ?>
	
			<?php if (!$this->user->canComment && $this->user->guest): ?>
		  	<div><?php echo JText::_('K2_LOGIN_TO_POST_COMMENTS'); ?></div>
		  	<?php endif; ?>

		</div>
		

		<form action="<?php echo JRoute::_('index.php'); ?>" method="post" data-form="report">
			<label for="reportName"><?php echo JText::_('K2_YOUR_NAME'); ?></label>
			<input type="text" id="reportName" name="reportName" value="" />

			<label for="reportReason"><?php echo JText::_('K2_REPORT_REASON'); ?></label>
			<textarea name="reportReason" id="reportReason" cols="60" rows="10"></textarea>

			<?php if($this->params->get('recaptcha') && $this->user->guest): ?>
			<label class="formRecaptcha"><?php echo JText::_('K2_PLEASE_VERIFY_THAT_YOU_ARE_HUMAN'); ?></label>
			<div id="recaptcha"></div>
			<?php endif; ?>
			
			<?php echo K2HelperCaptcha::display(); ?>
			
			<button data-action="report.send"><?php echo JText::_('K2_SEND_REPORT'); ?></button>
			<span data-role="log"></span>
			<input type="hidden" name="id" value="" />
			<input type="hidden" name="task" value="comments.report" />
			<input type="hidden" name="format" value="json" />
			<?php echo JHTML::_('form.token'); ?>
		</form>
  	
  </script>
  <?php endif; ?>
  

<div class="clr"></div>

</article>
<!-- End K2 Item Layout -->
