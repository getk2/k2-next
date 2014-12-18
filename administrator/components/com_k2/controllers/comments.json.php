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

require_once JPATH_ADMINISTRATOR.'/components/com_k2/controller.php';

/**
 * Comments JSON controller.
 */

class K2ControllerComments extends K2Controller
{

	protected function onBeforeRead($mode, $id)
	{
		$user = JFactory::getUser();
		return $user->authorise('k2.comment.edit', 'com_k2');
	}

	/**
	 * Read function.
	 * Handles all the read requests ( lists and forms ) and triggers the appropriate view method.
	 *
	 * @param string $mode		The mode of the read function. Pass 'row' for retrieving a single row or 'list' to retrieve a collection of rows.
	 * @param mixed $id			The id of the row to load when we are retrieving a single row.
	 *
	 * @return void
	 */
	protected function read($mode = 'row', $id = null)
	{
		$application = JFactory::getApplication();
		$itemId = $application->input->get('itemId', 0, 'int');
		if ($itemId)
		{
			$this->embed($id);
		}
		else
		{
			parent::read($mode, $id);
		}
	}

	protected function embed($id)
	{
		// Get application
		$application = JFactory::getApplication();

		// Get user
		$user = JFactory::getUser();

		// Get params
		$params = JComponentHelper::getParams('com_k2');

		// Check that comments are enabled
		if (!$params->get('comments'))
		{
			K2Response::throwError(JText::_('K2_ALERTNOTAUTH'), 404);
		}

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

			// Get model
			$model = K2Model::getInstance('Comments');

			// Set itemId state
			$model->setState('itemId', $item->id);

			// If user cannot edit comments load only the published
			if (!$user->authorise('k2.comment.edit', 'com_k2'))
			{
				$model->setState('state', 1);
			}

			if ($id)
			{
				// Calculate the offset of the requested comment
				$model->setState('id', $id);
				$operator = $params->get('commentsOrdering') == 'ASC' ? '<' : '>';
				$model->setState('id.operator', $operator);
				$offset = $model->countRows();

				$page = $offset / (int)$params->get('commentsLimit', 10);
				$page = floor($page);
				$offset = $page * (int)$params->get('commentsLimit', 10);

				// Now get comments of the detected page
				$model->setState('id', false);
				$model->setState('limit', (int)$params->get('commentsLimit', 10));
				$model->setState('limitstart', $offset);
				$model->setState('sorting', 'id.reverse');
				if ($params->get('commentsOrdering') == 'ASC')
				{
					$model->setState('sorting', 'id');
				}
				$comments = $model->getRows();

				// Pagination
				jimport('joomla.html.pagination');
				$pagination = new JPagination($model->countRows(), $offset, (int)$params->get('commentsLimit', 10));

			}
			else
			{
				// Get comments
				$model->setState('id', false);
				$model->setState('limit', (int)$params->get('commentsLimit', 10));
				$model->setState('limitstart', $offset);
				$model->setState('sorting', 'id.reverse');
				if ($params->get('commentsOrdering') == 'ASC')
				{
					$model->setState('sorting', 'id');
				}
				$comments = $model->getRows();

				// Pagination
				jimport('joomla.html.pagination');
				$pagination = new JPagination($model->countRows(), $offset, (int)$params->get('commentsLimit', 10));

			}

			// Response
			K2Response::setRows($comments);
			K2Response::setPagination($pagination);

		}

		echo K2Response::render();

		// Return
		return $this;
	}

	public function deleteUnpublished()
	{
		// Check for token
		JSession::checkToken() or K2Response::throwError(JText::_('JINVALID_TOKEN'));

		// User
		$user = JFactory::getUser();

		// Permissions check
		if (!$user->authorise('k2.comment.edit', 'com_k2'))
		{
			K2Response::throwError(JText::_('K2_YOU_ARE_NOT_AUTHORIZED_TO_PERFORM_THIS_OPERATION'));
		}

		// Get model
		$model = K2Model::getInstance('Comments');
		$model->deleteUnpublished();

		$application = JFactory::getApplication();
		$application->enqueueMessage(JText::_('K2_DELETE_COMPLETED'));
		echo json_encode(K2Response::render());
		return $this;

	}

	public function report()
	{
		// Check for token
		JSession::checkToken() or K2Response::throwError(JText::_('JINVALID_TOKEN'));

		// Get application
		$application = JFactory::getApplication();

		// Get configuration
		$configuration = JFactory::getConfig();

		// Get input
		$id = $application->input->get('id', 0, 'int');
		$reportName = $application->input->get('reportName', '', 'string');
		$reportReason = $application->input->get('reportReason', '', 'string');

		// Get params
		$params = JComponentHelper::getParams('com_k2');

		// Get user
		$user = JFactory::getUser();

		// Check if user can report
		if (!$params->get('comments') || !$params->get('commentsReporting') || ($params->get('commentsReporting') == '2' && $user->guest))
		{
			K2Response::throwError(JText::_('K2_ALERTNOTAUTH'), 403);
		}

		// Get comment
		$comment = K2Comments::getInstance($id);

		// Check comment is published
		if (!$comment->state)
		{
			K2Response::throwError(JText::_('K2_COMMENT_NOT_FOUND'));
		}

		// Get item
		$item = K2Items::getInstance($comment->itemId);

		// Check access to the item
		$item->checkSiteAccess();

		// Check input
		if (trim($reportName) == '')
		{
			K2Response::throwError(JText::_('K2_PLEASE_TYPE_YOUR_NAME'));
		}
		if (trim($reportReason) == '')
		{
			K2Response::throwError(JText::_('K2_PLEASE_TYPE_THE_REPORT_REASON'));
		}

		// Check captcha depending on settings
		require_once JPATH_SITE.'/components/com_k2/helpers/captcha.php';
		$data = $this->getInputData();
		if (!$result = K2HelperCaptcha::check($data, $this))
		{
			K2Response::throwError($this->getError());
		}

		$mailer = JFactory::getMailer();
		$senderEmail = $configuration->get('mailfrom');
		$senderName = $configuration->get('fromname');

		$mailer->setSender(array(
			$senderEmail,
			$senderName
		));
		$mailer->setSubject(JText::_('K2_COMMENT_REPORT'));
		$mailer->IsHTML(true);

		$body = "
        <strong>".JText::_('K2_NAME')."</strong>: ".$reportName." <br/>
        <strong>".JText::_('K2_REPORT_REASON')."</strong>: ".$reportReason." <br/>
        <strong>".JText::_('K2_COMMENT')."</strong>: ".nl2br($comment->text)." <br/>
        ";

		$mailer->setBody($body);
		$mailer->ClearAddresses();
		$mailer->AddAddress($params->get('commentsReportRecipient', $configuration->get('mailfrom')));
		$mailer->Send();

		$application->enqueueMessage(JText::_('K2_REPORT_SUBMITTED'));
		echo json_encode(K2Response::render());

		return $this;
	}

}
