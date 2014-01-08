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

require_once JPATH_SITE.'/components/com_k2/helpers/utilities.php';
require_once JPATH_ADMINISTRATOR.'/components/com_k2/resources/users.php';
require_once JPATH_ADMINISTRATOR.'/components/com_k2/models/model.php';
K2Model::addIncludePath(JPATH_ADMINISTRATOR.'/components/com_k2/models');

class ModK2CommentsHelper
{

	public static function getLatestComments($params)
	{
		$model = K2Model::getInstance('Comments');
		$model->setState('filter.items', true);
		$model->setState('state', 1);
		$filter = $params->get('category_id');
		if ($filter && isset($filter->enabled) && $filter->enabled)
		{
			$model->setState('category', $filter->categories);
		}
		$model->setState('limit', (int)$params->get('comments_limit', '5'));
		$model->setState('sorting', 'id');
		$comments = $model->getRows();

		foreach ($comments as $comment)
		{
			if ((int)$params->get('comments_word_limit'))
			{
				$comment->text = K2HelperUtilities::wordLimit($comment->text, $params->get('comments_word_limit'));
			}
			$comment->user->displayName = ($params->get('commenterName', 1) == 2) ? $comment->user->username : $comment->user->name;
		}
		return $comments;
	}

	public static function getTopCommenters($params)
	{
		$model = K2Model::getInstance('Users');
		$rows = $model->getTopCommenters();
		$commenters = array();
		foreach ($rows as $row)
		{
			if ($row->comments > 0)
			{
				$commenter = K2Users::getInstance($row->userId);
				$commenter->comments = $row->comments;
				$commenter->displayName = ($params->get('commenterNameOrUsername', 1) == 2) ? $commenter->username : $commenter->name;
				if ($params->get('commenterLatestComment'))
				{
					$model = K2Model::getInstance('Comments');
					$model->setState('userId', $commenter->id);
					$model->setState('state', 1);
					$model->setState('limit', 1);
					$model->setState('sorting', 'id');
					$commenter->comment = $model->getRow();
				}
				$commenters[] = $commenter;
			}
		}
		return $commenters;
	}

}
