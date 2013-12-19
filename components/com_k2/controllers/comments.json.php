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

require_once JPATH_ADMINISTRATOR.'/components/com_k2/controller.php';

/**
 * Comments JSON controller.
 */

class K2ControllerComments extends K2Controller
{

	public function read($mode = 'row', $id = null)
	{
		// Get application
		$application = JFactory::getApplication();

		// Get params
		$params = JComponentHelper::getParams('com_k2');

		// Get input
		$itemId = $application->input->get('itemId', 0, 'int');
		$offset = $application->input->get('limitstart', 0, 'int');

		if ($itemId)
		{
			// Get Item
			require_once JPATH_ADMINISTRATOR.'/components/com_k2/resources/items.php';
			$item = K2Items::getInstance($itemId);

			// Check access
			$item->checkSiteAccess();

			// Get comments model
			$model = K2Model::getInstance('Comments');
			$model->setState('itemId', $item->id);
			$model->setState('limit', (int)$params->get('commentsLimit', 10));
			$model->setState('limitstart', $offset);
			$model->setState('sorting', 'id');
			if ($params->get('commentsOrdering') == 'ASC')
			{
				$model->setState('sorting', 'id.asc');
			}

			// If user cannot edit comments load only the published
			$user = JFactory::getUser();
			if (!$user->authorise('k2.comment.edit', 'com_k2'))
			{
				$model->setState('state', 1);
			}

			// Load comments
			$comments = $model->getRows();

			// Pagination
			jimport('joomla.html.pagination');
			$pagination = new JPagination($model->countRows(), $offset, (int)$params->get('commentsLimit', 10));

			// Response
			K2Response::setRows($comments);
			K2Response::setPagination($pagination);
		}

		echo K2Response::render();

		// Return
		return $this;
	}

}
