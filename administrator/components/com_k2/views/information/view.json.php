<?php
/**
 * @version		3.0.0b
 * @package		K2
 * @author		JoomlaWorks http://www.joomlaworks.net
 * @copyright	Copyright (c) 2006 - 2014 JoomlaWorks Ltd. All rights reserved.
 * @license		GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 */

// no direct access
defined('_JEXEC') or die ;

require_once JPATH_ADMINISTRATOR.'/components/com_k2/views/view.php';

/**
 * Information JSON view.
 */

class K2ViewInformation extends K2View
{

	public function edit($id)
	{
		// Set title
		$this->setTitle('K2_INFORMATION');

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
		$row = new stdClass;
		$db = JFactory::getDBO();
		$row->dbVersion = $db->getVersion();
		$row->phpVersion = phpversion();
		$row->server = $this->getServer();
		$row->gd = extension_loaded('gd');
		if ($row->gd)
		{
			$gdinfo = gd_info();
			$row->gdVersion = $gdinfo['GD Version'];
		}
		$row->mediaFolder = is_writable(JPATH_SITE.'/media/k2');
		$row->attachmentsFolder = is_writable(JPATH_SITE.'/media/k2/attachments');
		$row->categoriesFolder = is_writable(JPATH_SITE.'/media/k2/categories');
		$row->galleriesFolder = is_writable(JPATH_SITE.'/media/k2/galleries');
		$row->itemsFolder = is_writable(JPATH_SITE.'/media/k2/items');
		$row->usersFolder = is_writable(JPATH_SITE.'/media/k2/users');
		$row->mediaFolder = is_writable(JPATH_SITE.'/media/k2/media');
		$row->cacheFolder = is_writable(JPATH_SITE.'/cache');
		$row->maxFileUploadSize = ini_get('upload_max_filesize');
		$row->memoryLimit = ini_get('memory_limit');
		$row->allowURLFopen = ini_get('allow_url_fopen');
		$row->mod_k2_comments = JFile::exists(JPATH_SITE.'/modules/mod_k2_comments/mod_k2_comments.php');
		$row->mod_k2_content = JFile::exists(JPATH_SITE.'/modules/mod_k2_content/mod_k2_content.php');
		$row->mod_k2_tools = JFile::exists(JPATH_SITE.'/modules/mod_k2_tools/mod_k2_tools.php');
		$row->mod_k2_user = JFile::exists(JPATH_SITE.'/modules/mod_k2_user/mod_k2_user.php');
		$row->mod_k2_users = JFile::exists(JPATH_SITE.'/modules/mod_k2_users/mod_k2_users.php');
		$row->mod_k2_quickicons = JFile::exists(JPATH_ADMINISTRATOR.'/modules/mod_k2_quickicons/mod_k2_quickicons.php');
		$row->mod_k2_stats = JFile::exists(JPATH_ADMINISTRATOR.'/modules/mod_k2_stats/mod_k2_stats.php');
		$row->plg_finder_k2 = JFile::exists(JPATH_SITE.'/plugins/finder/k2/k2.php');
		$row->plg_search_k2 = JFile::exists(JPATH_SITE.'/plugins/search/k2/k2.php');
		$row->plg_system_k2 = JFile::exists(JPATH_SITE.'/plugins/system/k2/k2.php');
		$row->plg_user_k2 = JFile::exists(JPATH_SITE.'/plugins/user/k2/k2.php');
		$row->plg_finder_k2_enabled = JPluginHelper::isEnabled('finder', 'k2');
		$row->plg_search_k2_enabled = JPluginHelper::isEnabled('search', 'k2');
		$row->plg_system_k2_enabled = JPluginHelper::isEnabled('system', 'k2');
		$row->plg_user_k2_enabled = JPluginHelper::isEnabled('user', 'k2');
		$row->plg_content_allvideos = JFile::exists(JPATH_SITE.'/plugins/content/jw_allvideos/jw_allvideos.php');
		$row->plg_content_sigpro = JFile::exists(JPATH_SITE.'/plugins/content/jw_sigpro/jw_sigpro.php');
		K2Response::setRow($row);
	}

	private function getServer()
	{
		if (isset($_SERVER['SERVER_SOFTWARE']))
		{
			return $_SERVER['SERVER_SOFTWARE'];
		}
		else if ($server = getenv('SERVER_SOFTWARE'))
		{
			return $server;
		}
		else
		{
			return JText::_('K2_NA');
		}
	}

}
