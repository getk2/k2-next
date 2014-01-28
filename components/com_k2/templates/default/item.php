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
 ?>

<?php if($this->print): ?>
<!-- Print button at the top of the print page only -->
<a class="itemPrintThisPage" rel="nofollow" href="#" onclick="window.print();return false;">
	<span><?php echo JText::_('K2_PRINT_THIS_PAGE'); ?></span>
</a>
<?php endif; ?>

<!-- Start K2 Item Layout -->

<div id="k2Container" class="itemView<?php echo ($this->item->featured) ? ' itemIsFeatured' : ''; ?><?php if ($this->params->get('pageclass_sfx'))echo ' '.$this->params->get('pageclass_sfx');?>">

	<!-- K2 Plugins: K2BeforeDisplay -->
	<?php echo $this->item->events->K2BeforeDisplay; ?>

	<div class="itemHeader">

		<?php if($this->params->get('itemDateCreated')): ?>
		<!-- Date created -->
		<span class="itemDateCreated">
			<?php echo JHtml::_('date', $this->item->created, JText::_('K2_DATE_FORMAT_LC2')); ?>
		</span>
		<?php endif; ?>

	  <?php if($this->params->get('itemTitle')): ?>
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

	  	<?php echo $this->item->title; ?>

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

	  <?php if($this->params->get('itemImage') && $this->item->image): ?>
	  		  	
	  <!-- Item Image -->
	  <div class="itemImageBlock">
		  <span class="itemImage">
		  	<a href="<?php echo $this->item->image->src; ?>" title="<?php echo JText::_('K2_CLICK_TO_PREVIEW_IMAGE'); ?>">
		  		<img src="<?php echo $this->item->image->src; ?>" alt="<?php echo $this->escape($this->item->image->alt); ?>" style="width:<?php echo $this->item->image->width; ?>px; height:auto;" />
		  	</a>
		  </span>

		  <?php if($this->params->get('itemImageMainCaption') && $this->item->image->caption): ?>
		  <!-- Image caption -->
		  <span class="itemImageCaption"><?php echo $this->item->image->caption; ?></span>
		  <?php endif; ?>

		  <?php if($this->params->get('itemImageMainCredits') && $this->item->image->credits): ?>
		  <!-- Image credits -->
		  <span class="itemImageCredits"><?php echo $this->item->image->credits; ?></span>
		  <?php endif; ?>

		  <div class="clr"></div>
	  </div>
	  <?php endif; ?>

	  <?php if(!empty($this->item->fulltext)): ?>
	  <?php if($this->params->get('itemIntroText')): ?>
	  <!-- Item introtext -->
	  <div class="itemIntroText">
	  	<?php echo $this->item->introtext; ?>
	  </div>
	  <?php endif; ?>
	  <?php if($this->params->get('itemFullText')): ?>
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

	  <?php if($this->params->get('itemExtraFields') && count($this->item->extraFields)): ?>
	  <!-- Item extra fields -->
	  <div class="itemExtraFields">
	  	<h3><?php echo JText::_('K2_ADDITIONAL_INFO'); ?></h3>
	  	<?php foreach ($this->item->extraFields as $extraFieldGroup): ?>
	  	<h4><?php echo $extraFieldGroup->name; ?></h4>
	  	<ul>
			<?php foreach ($extraFieldGroup->fields as $key=>$extraField): ?>
			<li class="<?php echo ($key%2) ? "odd" : "even"; ?> type<?php echo ucfirst($extraField->type); ?> group<?php echo $extraField->group; ?>">
				<span class="itemExtraFieldsLabel"><?php echo $extraField->name; ?>:</span>
				<span class="itemExtraFieldsValue"><?php echo $extraField->output; ?></span>
			</li>
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

  <?php if($this->params->get('itemAuthorLatest') && !empty($this->authorLatestItems)): ?>
  <!-- Latest items from author -->
	<div class="itemAuthorLatest">
		<h3><?php echo JText::_('K2_LATEST_FROM'); ?> <?php echo $this->item->author->name; ?></h3>
		<ul>
			<?php foreach($this->authorLatestItems as $key=>$item): ?>
			<li class="<?php echo ($key%2) ? "odd" : "even"; ?>">
				<a href="<?php echo $item->link ?>"><?php echo $item->title; ?></a>
			</li>
			<?php endforeach; ?>
		</ul>
		<div class="clr"></div>
	</div>
	<?php endif; ?>
	
  <?php if($this->params->get('itemRelated') && isset($this->item->related)): ?>
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

				<?php if($this->params->get('itemRelatedMedia') && !empty($item->media)): ?>
				  <div class="itemRelMediaBlock">
				  	<?php foreach ($item->media as $entry) : ?>
					<div class="itemRelMedia">
						<span class="itemRelMediaOutput"><?php echo $entry->output; ?></span>
						<div class="clr"></div>
				  	</div> 
					<?php endforeach; ?>
				  </div>
				<?php endif; ?>

				<?php if($this->params->get('itemRelatedImageGallery') && !empty($item->galleries)): ?>
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

  <?php if(!empty($this->item->media)): ?>
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
  <div class="itemNavigation">
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

  </div>
  <?php endif; ?>

  <!-- K2 Plugins: K2AfterDisplay -->
  <?php echo $this->item->events->K2AfterDisplay; ?>
  
  
  
  <?php if($this->params->get('itemComments') && $this->params->get('comments')): ?>
  <a name="itemCommentsAnchor" id="itemCommentsAnchor"></a>
  <div data-widget="k2comments" data-itemid="<?php echo $this->item->id; ?>" data-site="<?php echo JURI::root(true); ?>"></div>
  
	<script type="text/template" id="k2CommentsTemplate">
		<div class="itemComments">

			<?php if($this->params->get('commentsFormPosition')=='above' && !$this->print && $this->user->canComment): ?>
			<!-- Item comments form -->
			<div class="itemCommentsForm"><?php echo $this->loadTemplate('comments_form'); ?></div>
			<?php endif; ?>
	
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
			
			<?php if($this->params->get('commentsFormPosition')=='below' && !$this->print && $this->user->canComment): ?>
			<!-- Item comments form -->
			<div class="itemCommentsForm"><?php echo $this->loadTemplate('comments_form'); ?></div>
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
			<label class="formRecaptcha"><?php echo JText::_('K2_ENTER_THE_TWO_WORDS_YOU_SEE_BELOW'); ?></label>
			<div id="recaptcha"></div>
			<?php endif; ?>
			
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

</div>
<!-- End K2 Item Layout -->
