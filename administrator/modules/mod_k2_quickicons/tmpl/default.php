<?php
/**
 * @version		3.0.0
 * @package		K2
 * @author		JoomlaWorks http://www.joomlaworks.net
 * @copyright	Copyright (c) 2006 - 2014 JoomlaWorks Ltd. All rights reserved.
 * @license		GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 */

// no direct access
defined('_JEXEC') or die ; ?>

<div class="clr"></div>

<?php if($modLogo): ?>
<div id="k2QuickIconsTitle">
	<a href="<?php echo JRoute::_('index.php?option=com_k2#items'); ?>" title="<?php echo JText::_('K2_DASHBOARD'); ?>">
		<span>K2</span>
	</a>
</div>
<?php endif; ?>

<div id="k2QuickIcons"<?php if(!$modLogo): ?> class="k2NoLogo"<?php endif; ?>>
  <div class="icon-wrapper">
    <div class="icon">
	    <a href="<?php echo JRoute::_('index.php?option=com_k2#items/add'); ?>">
		    <img alt="<?php echo JText::_('K2_ADD_NEW_ITEM'); ?>" src="<?php echo JURI::root(true); ?>/media/k2app/assets/images/dashboard/item-new.png" />
		    <span><?php echo JText::_('K2_ADD_NEW_ITEM'); ?></span>
	    </a>
    </div>
  </div>
  <div class="icon-wrapper">
    <div class="icon">
	    <a href="<?php echo JRoute::_('index.php?option=com_k2#items'); ?>">
		    <img alt="<?php echo JText::_('K2_ITEMS'); ?>" src="<?php echo JURI::root(true); ?>/media/k2app/assets/images/dashboard/items.png" />
		    <span><?php echo JText::_('K2_ITEMS'); ?></span>
	    </a>
    </div>
  </div>
<div class="icon-wrapper">
    <div class="icon">
	    <a href="<?php echo JRoute::_('index.php?option=com_k2#categories'); ?>">
		    <img alt="<?php echo JText::_('K2_CATEGORIES'); ?>" src="<?php echo JURI::root(true); ?>/media/k2app/assets/images/dashboard/categories.png" />
		    <span><?php echo JText::_('K2_CATEGORIES'); ?></span>
	    </a>
    </div>
  </div>
	<?php if($user->authorise('k2.tags.manage', 'com_k2')): ?>
	<div class="icon-wrapper">
    <div class="icon">
	    <a href="<?php echo JRoute::_('index.php?option=com_k2#tags'); ?>">
		    <img alt="<?php echo JText::_('K2_TAGS'); ?>" src="<?php echo JURI::root(true); ?>/media/k2app/assets/images/dashboard/tags.png" />
		    <span><?php echo JText::_('K2_TAGS'); ?></span>
	    </a>
    </div>
  </div>
  <?php endif; ?>
	<div class="icon-wrapper">
    <div class="icon">
	    <a href="<?php echo JRoute::_('index.php?option=com_k2#comments'); ?>">
		    <img alt="<?php echo JText::_('K2_COMMENTS'); ?>" src="<?php echo JURI::root(true); ?>/media/k2app/assets/images/dashboard/comments.png" />
		    <span><?php echo JText::_('K2_COMMENTS'); ?></span>
	    </a>
    </div>
  </div>
  <?php if ($user->authorise('core.admin', 'com_k2')): ?>
  <div class="icon-wrapper">
    <div class="icon">
	    <a href="<?php echo JRoute::_('index.php?option=com_k2#extrafields'); ?>">
		    <img alt="<?php echo JText::_('K2_EXTRA_FIELDS'); ?>" src="<?php echo JURI::root(true); ?>/media/k2app/assets/images/dashboard/extra-fields.png" />
		    <span><?php echo JText::_('K2_EXTRA_FIELDS'); ?></span>
	    </a>
    </div>
  </div>
	<div class="icon-wrapper">
    <div class="icon">
	    <a href="<?php echo JRoute::_('index.php?option=com_k2#extrafieldsgroups'); ?>">
		    <img alt="<?php echo JText::_('K2_EXTRA_FIELD_GROUPS'); ?>" src="<?php echo JURI::root(true); ?>/media/k2app/assets/images/dashboard/extra-field-groups.png" />
		    <span><?php echo JText::_('K2_EXTRA_FIELD_GROUPS'); ?></span>
	    </a>
    </div>
  </div>
  <?php endif; ?>
	<div class="icon-wrapper">
    <div class="icon">
	    <a href="<?php echo JRoute::_('index.php?option=com_k2#media'); ?>">
		    <img alt="<?php echo JText::_('K2_MEDIA_MANAGER'); ?>" src="<?php echo JURI::root(true); ?>/media/k2app/assets/images/dashboard/mediamanager.png" />
		    <span><?php echo JText::_('K2_MEDIA_MANAGER'); ?></span>
	    </a>
    </div>
  </div>
	<div class="icon-wrapper">
    <div class="icon">
    	<a id="k2OnlineImageEditor" target="_blank" href="<?php echo $onlineImageEditorLink; ?>">
		    <img alt="<?php echo JText::_('K2_ONLINE_IMAGE_EDITOR'); ?>" src="<?php echo JURI::root(true); ?>/media/k2app/assets/images/dashboard/image-editing.png" />
		    <span><?php echo JText::_('K2_ONLINE_IMAGE_EDITOR'); ?></span>
	    </a>
    </div>
  </div>
  <div class="icon-wrapper">
    <div class="icon">
    	<a class="modal" rel="{handler:'iframe', size:{x:(document.documentElement.clientWidth)*0.9, y:(document.documentElement.clientHeight)*0.95}}" target="_blank" href="http://getk2.org/documentation/">
    		<img alt="<?php echo JText::_('K2_DOCS_AND_TUTORIALS'); ?>" src="<?php echo JURI::root(true); ?>/media/k2app/assets/images/dashboard/documentation.png" />
    		<span><?php echo JText::_('K2_DOCS_AND_TUTORIALS'); ?></span>
    	</a>
    </div>
  </div>
  <?php if ($user->authorise('core.manage', 'com_k2')): ?>
  <div class="icon-wrapper">
    <div class="icon">
    	<a class="modal" rel="{handler:'iframe', size:{x:(document.documentElement.clientWidth)*0.9, y:(document.documentElement.clientHeight)*0.95}}" target="_blank" href="http://getk2.org/extend/">
    		<img alt="<?php echo JText::_('K2_EXTEND'); ?>" src="<?php echo JURI::root(true); ?>/media/k2app/assets/images/dashboard/extend.png" />
    		<span><?php echo JText::_('K2_EXTEND'); ?></span>
    	</a>
    </div>
  </div>
  <div class="icon-wrapper">
    <div class="icon">
    	<a class="modal" rel="{handler:'iframe', size:{x:(document.documentElement.clientWidth)*0.9, y:(document.documentElement.clientHeight)*0.95}}" target="_blank" href="http://getk2.org/community/">
    		<img alt="<?php echo JText::_('K2_COMMUNITY'); ?>" src="<?php echo JURI::root(true); ?>/media/k2app/assets/images/dashboard/help.png" />
    		<span><?php echo JText::_('K2_COMMUNITY'); ?></span>
    	</a>
    </div>
  </div>
  <div class="icon-wrapper">
    <div class="icon">
	    <a class="modal" rel="{handler:'iframe', size:{x:(document.documentElement.clientWidth)*0.9, y:(document.documentElement.clientHeight)*0.95}}" href="http://joomlareader.com/" title="<?php echo JText::_('K2_JOOMLA_NEWS_FROM_MORE_THAN_200_SOURCES_WORLDWIDE'); ?>">
		    <img alt="<?php echo JText::_('K2_JOOMLA_NEWS_FROM_MORE_THAN_200_SOURCES_WORLDWIDE'); ?>" src="<?php echo JURI::root(true); ?>/media/k2app/assets/images/dashboard/joomlareader.png" />
		    <span><?php echo JText::_('K2_JOOMLAREADERCOM'); ?></span>
	    </a>
    </div>
  </div>
  <div style="clear: both;"></div>
  <?php endif; ?>
</div>
