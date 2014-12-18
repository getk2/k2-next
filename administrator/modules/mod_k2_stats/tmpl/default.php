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

// Import Joomla! tabs
jimport('joomla.html.pane');

// Quick and dirty fix for Joomla! 3.0 missing CSS tabs when creating tabs using the API.
// Should be removed when Joomla! fixes that...

$document = JFactory::getDocument();
$document->addStyleDeclaration('
		dl.tabs {float:left;margin:10px 0 -1px 0;z-index:50;}
		dl.tabs dt {float:left;padding:4px 10px;border:1px solid #ccc;margin-left:3px;background:#e9e9e9;color:#666;}
		dl.tabs dt.open {background:#F9F9F9;border-bottom:1px solid #f9f9f9;z-index:100;color:#000;}
		div.current {clear:both;border:1px solid #ccc;padding:10px 10px;}
		dl.tabs h3 {font-size:12px;line-height:12px;margin:4px;}
');

?>

<div class="clr"></div>

<?php echo JHtml::_('tabs.start'); ?>

<?php if($params->get('latestItems', 1)): ?>
<?php echo JHtml::_('tabs.panel', JText::_('K2_LATEST_ITEMS'), 'latestItemsTab'); ?>
<!--[if lte IE 7]>
<br class="ie7fix" />
<![endif]-->
<table class="adminlist table table-striped">
	<thead>
		<tr>
			<td class="title"><?php echo JText::_('K2_TITLE'); ?></td>
			<td class="title"><?php echo JText::_('K2_CREATED'); ?></td>
			<td class="title"><?php echo JText::_('K2_AUTHOR'); ?></td>
		</tr>
	</thead>
	<tbody>
		<?php foreach($latestItems as $item): ?>
		<tr>
			<td><a href="<?php echo JRoute::_('index.php?option=com_k2#items/edit/'.$item->id); ?>"><?php echo $item->title; ?></a></td>
			<td><?php echo JHTML::_('date', $item->created , JText::_('K2_DATE_FORMAT')); ?></td>
			<td><?php echo $item->author->name; ?></td>
		</tr>
		<?php endforeach; ?>
	</tbody>
</table>
<?php endif; ?>

<?php if($params->get('popularItems', 1)): ?>
<?php echo JHtml::_('tabs.panel', JText::_('K2_POPULAR_ITEMS'), 'popularItemsTab'); ?>
<!--[if lte IE 7]>
<br class="ie7fix" />
<![endif]-->
<table class="adminlist table table-striped">
	<thead>
		<tr>
			<td class="title"><?php echo JText::_('K2_TITLE'); ?></td>
			<td class="title"><?php echo JText::_('K2_HITS'); ?></td>
			<td class="title"><?php echo JText::_('K2_CREATED'); ?></td>
			<td class="title"><?php echo JText::_('K2_AUTHOR'); ?></td>
		</tr>
	</thead>
	<tbody>
		<?php foreach($popularItems as $item): ?>
		<tr>
			<td><a href="<?php echo JRoute::_('index.php?option=com_k2#items/edit/'.$item->id); ?>"><?php echo $item->title; ?></a></td>
			<td><?php echo $item->hits; ?></td>
			<td><?php echo JHTML::_('date', $item->created , JText::_('K2_DATE_FORMAT')); ?></td>
			<td><?php echo $item->author->name; ?></td>
		</tr>
		<?php endforeach; ?>
	</tbody>
</table>
<?php endif; ?>

<?php if($params->get('mostCommentedItems', 1)): ?>
<?php echo JHtml::_('tabs.panel', JText::_('K2_MOST_COMMENTED_ITEMS'), 'mostCommentedItemsTab'); ?>
<!--[if lte IE 7]>
<br class="ie7fix" />
<![endif]-->
<table class="adminlist table table-striped">
	<thead>
		<tr>
			<td class="title"><?php echo JText::_('K2_TITLE'); ?></td>
			<td class="title"><?php echo JText::_('K2_COMMENTS'); ?></td>
			<td class="title"><?php echo JText::_('K2_CREATED'); ?></td>
			<td class="title"><?php echo JText::_('K2_AUTHOR'); ?></td>
		</tr>
	</thead>
	<tbody>
		<?php foreach($mostCommentedItems as $item): ?>
		<tr>
			<td><a href="<?php echo JRoute::_('index.php?option=com_k2#items/edit/'.$item->id); ?>"><?php echo $item->title; ?></a></td>
			<td><?php echo $item->numOfComments; ?></td>
			<td><?php echo JHTML::_('date', $item->created , JText::_('K2_DATE_FORMAT')); ?></td>
			<td><?php echo $item->author->name; ?></td>
		</tr>
		<?php endforeach; ?>
	</tbody>
</table>
<?php endif; ?>

<?php if($params->get('latestComments', 1)): ?>
<?php echo JHtml::_('tabs.panel', JText::_('K2_LATEST_COMMENTS'), 'latestCommentsTab'); ?>
<!--[if lte IE 7]>
<br class="ie7fix" />
<![endif]-->
<table class="adminlist table table-striped">
	<thead>
		<tr>
			<td class="title"><?php echo JText::_('K2_COMMENT'); ?></td>
			<td class="title"><?php echo JText::_('K2_ADDED_ON'); ?></td>
			<td class="title"><?php echo JText::_('K2_POSTED_BY'); ?></td>
		</tr>
	</thead>
	<tbody>
		<?php foreach($latestComments as $comment): ?>
		<tr>
			<td><?php echo $comment->text; ?></td>
			<td><?php echo JHTML::_('date', $comment->date , JText::_('K2_DATE_FORMAT')); ?></td>
			<td><?php echo $comment->user->name; ?></td>
		</tr>
		<?php endforeach; ?>
	</tbody>
</table>
<?php endif; ?>

<?php if($params->get('statistics', 1)): ?>
<?php echo JHtml::_('tabs.panel', JText::_('K2_STATISTICS'), 'statsTab'); ?>
<!--[if lte IE 7]>
<br class="ie7fix" />
<![endif]-->
<table class="adminlist table table-striped">
	<thead>
		<tr>
			<td class="title"><?php echo JText::_('K2_TYPE'); ?></td>
			<td class="title"><?php echo JText::_('K2_COUNT'); ?></td>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td><?php echo JText::_('K2_ITEMS'); ?></td>
			<td><?php echo $statistics->numOfItems; ?> (<?php echo $statistics->numOfFeaturedItems.' '.JText::_('K2_FEATURED').' - '.$statistics->numOfTrashedItems.' '.JText::_('K2_TRASHED'); ?>)</td>
		</tr>
		<tr>
			<td><?php echo JText::_('K2_CATEGORIES'); ?></td>
			<td><?php echo $statistics->numOfCategories; ?> (<?php echo $statistics->numOfTrashedCategories.' '.JText::_('K2_TRASHED'); ?>)</td>
		</tr>
		<tr>
			<td><?php echo JText::_('K2_TAGS'); ?></td>
			<td><?php echo $statistics->numOfTags; ?></td>
		</tr>
		<tr>
			<td><?php echo JText::_('K2_COMMENTS'); ?></td>
			<td><?php echo $statistics->numOfComments; ?></td>
		</tr>
		<tr>
			<td><?php echo JText::_('K2_USERS'); ?></td>
			<td><?php echo $statistics->numOfUsers; ?></td>
		</tr>
		<tr>
			<td><?php echo JText::_('K2_USER_GROUPS'); ?></td>
			<td><?php echo $statistics->numOfUserGroups; ?></td>
		</tr>
	</tbody>
</table>
<?php endif; ?>

<?php echo JHtml::_('tabs.end'); ?>
