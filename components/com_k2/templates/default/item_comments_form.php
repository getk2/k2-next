<?php
/**
 * @version		3.0.0
 * @package		K2
 * @author		JoomlaWorks http://www.joomlaworks.net
 * @copyright	Copyright (c) 2006 - 2013 JoomlaWorks Ltd. All rights reserved.
 * @license		GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 */

// no direct access
defined('_JEXEC') or die ; ?>

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

<form action="<?php echo JRoute::_('index.php'); ?>" method="post" id="k2CommentsForm">
	<label for="k2CommentText"><?php echo JText::_('K2_MESSAGE'); ?> *</label>
	<textarea rows="10" cols="20" placeholder="<?php echo JText::_('K2_ENTER_YOUR_MESSAGE_HERE'); ?>" name="text" id="k2CommentText"></textarea>

	<label for="k2CommentName"><?php echo JText::_('K2_NAME'); ?> *</label>
	<input type="text" name="name" id="k2CommentName" placeholder="<?php echo JText::_('K2_ENTER_YOUR_NAME'); ?>" />

	<label for="k2CommentEmail"><?php echo JText::_('K2_EMAIL'); ?> *</label>
	<input type="email" name="email" id="k2CommentEmail" placeholder="<?php echo JText::_('K2_ENTER_YOUR_EMAIL_ADDRESS'); ?>"  />

	<label for="k2CommentUrl"><?php echo JText::_('K2_WEBSITE_URL'); ?></label>
	<input type="text" name="url" id="k2CommentUrl" placeholder="<?php echo JText::_('K2_ENTER_YOUR_SITE_URL'); ?>" />

	<?php if($this->params->get('recaptcha') && ($this->user->guest || $this->params->get('recaptchaForRegistered', 1))): ?>
	<label><?php echo JText::_('K2_ENTER_THE_TWO_WORDS_YOU_SEE_BELOW'); ?></label>
	<div id="k2Recaptcha"></div>
	<?php endif; ?>

	<button type="submit"><?php echo JText::_('K2_SUBMIT_COMMENT'); ?></button>

	<span id="k2CommentsFormLog"></span>

	<input type="hidden" name="option" value="com_k2" />
	<input type="hidden" name="task" value="comments.sync" />
	<input type="hidden" name="format" value="json" />
	<input type="hidden" name="_method" value="POST" />
	<input type="hidden" name="itemId" value="<?php echo $this->item->id; ?>" />
	<?php echo JHtml::_('form.token'); ?>
</form>