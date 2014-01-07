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

<div id="k2ModuleBox<?php echo $module->id; ?>" class="k2UsersBlock<?php if($params->get('moduleclass_sfx')) echo ' '.$params->get('moduleclass_sfx'); ?>">
	<ul>
		<?php foreach($users as $key => $user): ?>
		<li class="<?php echo ($key%2) ? "odd" : "even"; if(count($users) == $key+1) echo ' lastItem'; ?>">

			<?php if($params->get('userAvatar') && $user->image): ?>
			<a class="k2Avatar ubUserAvatar" rel="author" href="<?php echo $user->link; ?>" title="<?php echo htmlspecialchars($user->name); ?>">
				<img src="<?php echo $user->image->src; ?>" alt="<?php echo htmlspecialchars($user->name); ?>" style="width:<?php echo $avatarWidth; ?>px;height:auto;" />
			</a>
			<?php endif; ?>

			<?php if($params->get('userName')): ?>
			<a class="ubUserName" rel="author" href="<?php echo $user->link; ?>" title="<?php echo htmlspecialchars($user->name); ?>">
				<?php echo $user->name; ?>
			</a>
			<?php endif; ?>

			<?php if($params->get('userDescription') && $user->description): ?>
			<div class="ubUserDescription">
				<?php if($params->get('userDescriptionWordLimit')): ?>
				<?php echo K2HelperUtilities::wordLimit($user->description, $params->get('userDescriptionWordLimit')) ?>
				<?php else: ?>
				<?php echo $user->description; ?>
				<?php endif; ?>
			</div>
			<?php endif; ?>

			<?php if($params->get('userFeed') || ($params->get('userURL') && $user->site) || $params->get('userEmail')): ?>
			<div class="ubUserAdditionalInfo">

				<?php if($params->get('userFeed')): ?>
				<!-- RSS feed icon -->
				<a class="ubUserFeedIcon" href="<?php echo $user->feedLink; ?>" title="<?php echo JText::_('K2_SUBSCRIBE_TO_THIS_USERS_RSS_FEED'); ?>">
					<span><?php echo JText::_('K2_SUBSCRIBE_TO_THIS_USERS_RSS_FEED'); ?></span>
				</a>
				<?php endif; ?>

				<?php if($params->get('userURL') && $user->site): ?>
				<a class="ubUserURL" rel="me" href="<?php echo $user->site; ?>" title="<?php echo JText::_('K2_WEBSITE'); ?>" target="_blank">
					<span><?php echo JText::_('K2_WEBSITE'); ?></span>
				</a>
				<?php endif; ?>

				<?php if($params->get('userEmail')): ?>
				<span class="ubUserEmail" title="<?php echo JText::_('K2_EMAIL'); ?>">
					<?php echo JHtml::_('email.cloak', $user->email); ?>
				</span>
				<?php endif; ?>
			</div>
			<?php endif; ?>

			<?php if($params->get('userItemCount') && count($user->items)): ?>
			<h3><?php echo JText::_('K2_RECENT_ITEMS'); ?></h3>
			<ul class="ubUserItems">
				<?php foreach ($user->items as $item): ?>
				<li>
					<a href="<?php echo $item->link; ?>" title="<?php echo htmlspecialchars($item->title); ?>">
						<?php echo $item->title; ?>
					</a>
				</li>
				<?php endforeach; ?>
			</ul>
			<?php endif; ?>

			<div class="clr"></div>
		</li>
		<?php endforeach; ?>
	</ul>
</div>
