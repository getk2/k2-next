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

require_once JPATH_ADMINISTRATOR.'/components/com_k2/views/view.php';

/**
 * Items JSON view.
 */

class K2ViewItems extends K2View
{

	/**
	 * Builds the response variables needed for rendering a list.
	 * Usually there will be no need to override this function.
	 *
	 * @param string $tpl	The name of the template file to parse. @see JViewLegacy. Not used here at all.
	 *
	 * @return void
	 */

	public function display($tpl = NULL)
	{
		K2Response::setTitle(JText::_('K2_ITEMS'));
		parent::display($tpl);
	}
	
	/**
	 * Builds the response variables needed for rendering a form.
	 * Usually there will be no need to override this function.
	 *
	 * @param integer $id	The id of the resource to load.
	 *
	 * @return void
	 */

	public function edit($id = null)
	{
		
		K2Response::setTitle(JText::_('K2_ITEM'));
		parent::edit($id);
	}

	protected function setUserStates()
	{
		$this->setUserState('limit', 10, 'int');
		$this->setUserState('page', 1, 'int');
		$this->setUserState('search', '', 'string');
		$this->setUserState('access', 0, 'int');
		$this->setUserState('trashed', null, 'int');
		$this->setUserState('published', null, 'int');
		$this->setUserState('featured', null, 'int');
		$this->setUserState('category', 0, 'int');
		$this->setUserState('user', 0, 'int');
		$this->setUserState('language', '', 'string');
		$this->setUserState('sorting', 'item.id DESC', 'string');
	}

	protected function setFilters()
	{

		// Language filter
		K2Response::addFilter('language', JText::_('K2_SELECT_LANGUAGE'), K2HelperHTML::language($this->getUserState('language')));

		// Sorting filter
		$sortingOptions = array(
			'K2_ID' => 'id DESC',
			'K2_TITLE' => 'title ASC',
			'K2_ORDERING' => 'ordering ASC',
			'K2_FEATURED' => 'featured DESC',
			'K2_PUBLISHED' => 'published DESC',
			'K2_CATEGORY' => 'categoryName ASC',
			'K2_AUTHOR' => 'authorName ASC',
			'K2_MODERATOR' => 'moderatorName ASC',
			'K2_ACCESS_LEVEL' => 'accessLevel ASC',
			'K2_DATE_PUBLISHED' => 'created DESC',
			'K2_MODIFIED' => 'modified DESC',
			'K2_HITS' => 'hits DESC'
		);
		K2Response::addFilter('sorting', JText::_('K2_SORT_BY'), K2HelperHTML::sorting($this->getUserState('sorting'), $sortingOptions));

		// Published filter
		K2Response::addFilter('published', JText::_('K2_PUBLISHED'), K2HelperHTML::published($this->getUserState('published')), true);

		// Featured filter
		K2Response::addFilter('featured', JText::_('K2_FEATURED'), K2HelperHTML::featured($this->getUserState('featured')), true);

	}

	protected function setToolbar()
	{
		// Add toolbar buttons
		K2Response::addToolbarButton('featured', K2HelperToolbar::featured());
		K2Response::addToolbarButton('published', K2HelperToolbar::published());
		K2Response::addToolbarButton('batch', K2HelperToolbar::batch());
		K2Response::addToolbarButton('delete', K2HelperToolbar::delete());

		/*
		 // Add batch actions
		 //K2Response::addBatchAction('catid', JText::_('K2_SET_CATEGORY'), JHTML::_('select.genericlist', K2HelperHTML::getCategoryFilterOptions('K2_LEAVE_UNCHANGED'), 'catid'));
		 //K2Response::addBatchAction('created_by', JText::_('K2_SET_USER'), JHTML::_('select.genericlist', K2HelperHTML::getUserFilterOptions('K2_LEAVE_UNCHANGED', false), 'created_by'));
		 $options = JHtml::_('access.assetgroups');
		 array_unshift($options, JHTML::_('select.option', 0, JText::_('K2_LEAVE_UNCHANGED')));
		 K2Response::addBatchAction('access', JText::_('K2_SET_ACCESS_LEVEL'), JHTML::_('select.genericlist', $options, 'access'));
		 $options = JHTML::_('contentlanguage.existing', true, true);
		 array_unshift($options, JHTML::_('select.option', 0, JText::_('K2_LEAVE_UNCHANGED')));
		 K2Response::addBatchAction('language', JText::_('K2_SET_LANGUAGE'), JHTML::_('select.genericlist', $options, 'language'));
		 */
	}

}
