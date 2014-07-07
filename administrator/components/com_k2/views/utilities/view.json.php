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
 * Tags JSON view.
 */

class K2ViewUtilities extends K2View
{

	/**
	 * Builds the response variables needed for rendering a form.
	 * Usually there will be no need to override this function.
	 *
	 * @param integer $id	The id of the resource to load.
	 *
	 * @return void
	 */

	public function edit($id)
	{
		// Set title
		$this->setTitle('K2_UTILITIES');

		// Set menu
		$this->setMenu();

		// Set row
		$this->setRow(null);

		// Render
		$this->render();
	}

	/**
	 * Helper method for fetching a single row and pass it to K2 response.
	 * This is triggered by the edit function.
	 * Usually there will be no need to override this function.
	 *
	 * @param   integer  $id  The id of the row to edit.
	 *
	 * @return void
	 */
	protected function setRow($id)
	{

		// Create row
		$row = new stdClass;

		$row->importArticles = false;

		$user = JFactory::getUser();
		$params = JComponentHelper::getParams('com_k2');
		if ($user->authorise('core.admin', 'com_k2') && !$params->get('hideImportButton'))
		{
			$row->importArticles = true;
		}

		K2Response::setRow($row);
	}

}
