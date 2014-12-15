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

class Com_K2InstallerScript
{
	public function preflight($type, $parent)
	{
		$application = JFactory::getApplication();
		$configuration = JFactory::getConfig();
		$installer = $parent->getParent();
		$db = JFactory::getDbo();

		// Init the upgrade flag
		$this->upgrade = false;

		// Proceed only if we are updating
		if ($type != 'install')
		{
			// Ensure that we are under Joomla! 3.2 or later
			if (version_compare(JVERSION, '3.3.6', 'lt'))
			{
				$parent->getParent()->abort('K2 v3 requires Joomla! 3.3.6 or later.');
				return false;
			}

			// Get installled version
			$query = $db->getQuery(true);
			$query->select($db->quoteName('manifest_cache'))->from($db->quoteName('#__extensions'))->where($db->quoteName('name').' = '.$db->quote('com_k2'));
			$db->setQuery($query);
			$manifest = json_decode($db->loadResult());
			$installedVersion = $manifest->version;

			// Detect if we need to perform an upgrade
			if (version_compare($installedVersion, '3.0.0', 'lt'))
			{
				// Ensure that the installed K2 version is not very old. Otherwise the update will fail.
				if (version_compare($installedVersion, '2.6.9', 'lt'))
				{
					$parent->getParent()->abort('You cannot update from this version of K2. Please update first your current K2 installation to the latest 2.x series and try again.');
					return false;
				}

				// User is required to put the site offline while upgrading
				if (!$configuration->get('offline'))
				{
					$parent->getParent()->abort('Site is not offline. Please put your site offline and try again.');
					return false;
				}

				// Since this is an upgrade rename all K2 2.x tables so the new ones will be created.
				$oldTables = array('#__k2_attachments', '#__k2_categories', '#__k2_comments', '#__k2_extra_fields', '#__k2_extra_fields_groups', '#__k2_items', '#__k2_rating', '#__k2_tags', '#__k2_tags_xref', '#__k2_users', '#__k2_user_groups');
				foreach ($oldTables as $oldTable)
				{
					$newTable = str_replace('#__k2_', '#__k2_v2_', $oldTable);
					$db->setQuery('DROP TABLE IF EXISTS '.$db->quoteName($newTable));
					$db->execute();
					$db->setQuery('RENAME TABLE '.$db->quoteName($oldTable).' TO '.$db->quoteName($newTable));
					if (!$db->execute())
					{
						$parent->getParent()->abort(JText::sprintf('JLIB_INSTALLER_ABORT_COMP_INSTALL_SQL_ERROR', $db->stderr(true)));
						return false;
					}
				}

				// Force parsing of SQL file since Joomla! does that only in install mode, not in updates
				$sql = $installer->getPath('source').'/administrator/components/com_k2/install.sql';
				$queries = JDatabaseDriver::splitSql(file_get_contents($sql));
				foreach ($queries as $query)
				{
					$query = trim($query);
					if ($query != '' && $query{0} != '#')
					{
						$db->setQuery($query);
						if (!$db->execute())
						{
							$parent->getParent()->abort(JText::sprintf('JLIB_INSTALLER_ERROR_SQL_ERROR', $db->stderr(true)));
							return false;
						}
					}
				}

				// Rename component files to get rid of files we don't need
				if (JFolder::exists(JPATH_SITE.'/components/com_k2'))
				{
					if (JFolder::exists(JPATH_SITE.'/components/com_k2_v2'))
					{
						if (!JFolder::delete(JPATH_SITE.'/components/com_k2_v2'))
						{
							$parent->getParent()->abort('Could not delete folder '.JPATH_SITE.'/components/com_k2_v2. Check permissions.');
							return false;
						}
					}
					if (!JFolder::move(JPATH_SITE.'/components/com_k2', JPATH_SITE.'/components/com_k2_v2'))
					{
						$parent->getParent()->abort('Could not move folder '.JPATH_SITE.'/components/com_k2. Check permissions.');
						return false;
					}
					if (!JFolder::create(JPATH_SITE.'/components/com_k2'))
					{
						$parent->getParent()->abort('Could not create folder '.JPATH_SITE.'/components/com_k2. Check permissions.');
						return false;
					}
					if (!JFolder::copy(JPATH_SITE.'/components/com_k2_v2/templates', JPATH_SITE.'/components/com_k2/templates'))
					{
						$parent->getParent()->abort('Could not copy folder '.JPATH_SITE.'/components/com_k2_v2/templates. Check permissions.');
						return false;
					}
				}
				if (JFolder::exists(JPATH_ADMINISTRATOR.'/components/com_k2'))
				{

					if (JFolder::exists(JPATH_ADMINISTRATOR.'/components/com_k2_v2'))
					{
						if (!JFolder::delete(JPATH_ADMINISTRATOR.'/components/com_k2_v2'))
						{
							$parent->getParent()->abort('Could not delete folder '.JPATH_ADMINISTRATOR.'/components/com_k2_v2. Check permissions.');
							return false;
						}
					}

					if (!JFolder::move(JPATH_ADMINISTRATOR.'/components/com_k2', JPATH_ADMINISTRATOR.'/components/com_k2_v2'))
					{
						$parent->getParent()->abort('Could not move folder '.JPATH_ADMINISTRATOR.'/components/com_k2. Check permissions.');
						return false;
					}
				}

				// Set a flag that this is an upgrade
				$this->upgrade = true;
			}
		}

	}

	public function postflight($type, $parent)
	{
		// Get database
		$db = JFactory::getDbo();

		// Get manifest
		$src = $parent->getParent()->getPath('source');
		$manifest = $parent->getParent()->manifest;

		// Install media/k2app
		if (JFolder::exists(JPATH_SITE.'/media/k2app'))
		{
			JFolder::delete(JPATH_SITE.'/media/k2app');
		}
		JFolder::copy($src.'/media/k2app', JPATH_SITE.'/media/k2app');

		// Install plugins
		$plugins = $manifest->xpath('plugins/plugin');

		foreach ($plugins as $plugin)
		{
			// Get plugin variables
			$name = (string)$plugin->attributes()->plugin;
			$group = (string)$plugin->attributes()->group;
			$path = $src.'/plugins/'.$group.'/'.$name;

			// Install
			$installer = new JInstaller;
			$result = $installer->install($path);

			// Enable the plugin. Only for fresh installations, not updates!
			if ($type == 'install' && $group != 'finder')
			{
				$query = $db->getQuery(true);
				$query->update($db->quoteName('#__extensions'))->set($db->quoteName('enabled').' = 1')->where($db->quoteName('type').' = '.$db->quote('plugin'))->where($db->quoteName('element').' = '.$db->quote($name))->where($db->quoteName('folder').' = '.$db->quote($group));
				$db->setQuery($query);
				$db->execute();
			}
		}

		// Install modules
		$modules = $manifest->xpath('modules/module');
		foreach ($modules as $module)
		{
			// Get module variables
			$name = (string)$module->attributes()->module;
			$client = (string)$module->attributes()->client;

			// Set client if it's null
			if (is_null($client))
			{
				$client = 'site';
			}

			// Detect path
			$path = ($client == 'administrator') ? $src.'/administrator/modules/'.$name : $src.'/modules/'.$name;

			// Install
			$installer = new JInstaller;
			$result = $installer->install($path);

			// Publish the administrator modules. Only for fresh installations, not updates!
			if ($type == 'install' && $client == 'administrator')
			{
				// Detect target position
				$position = 'cpanel';

				// Publish the module
				$query = $db->getQuery(true);
				$query->update($db->quoteName('#__modules'))->set($db->quoteName('position').' = '.$db->quote($position))->set($db->quoteName('published').' = 1')->where($db->quoteName('module').' = '.$db->quote($name));
				$db->setQuery($query);
				$db->execute();

				$query = $db->getQuery(true);
				$query->select($db->quoteName('id'))->from($db->quoteName('#__modules'))->where($db->quoteName('module').' = '.$db->quote($name));
				$db->setQuery($query);
				$id = (int)$db->loadResult();
				if ($id)
				{
					$query = $db->getQuery(true);
					$query->insert($db->quoteName('#__modules_menu'))->columns('moduleid, menuid')->values($id.',0');
					$db->setQuery($query);
					$db->execute();
				}
			}
		}

		// Set the default image sizes for new installs
		if ($type == 'install')
		{
			$params = JComponentHelper::getParams('com_k2');

			$imageSizes = array();

			$size = new stdClass;
			$size->id = 'XS';
			$size->name = 'Extra Small';
			$size->width = 100;
			$size->quality = 100;
			$imageSizes[] = $size;

			$size = new stdClass;
			$size->id = 'S';
			$size->name = 'Small';
			$size->width = 200;
			$size->quality = 100;
			$imageSizes[] = $size;

			$size = new stdClass;
			$size->id = 'M';
			$size->name = 'Medium';
			$size->width = 400;
			$size->quality = 100;
			$imageSizes[] = $size;

			$size = new stdClass;
			$size->id = 'L';
			$size->name = 'Large';
			$size->width = 600;
			$size->quality = 100;
			$imageSizes[] = $size;

			$size = new stdClass;
			$size->id = 'XL';
			$size->name = 'Extra Large';
			$size->width = 900;
			$size->quality = 100;
			$imageSizes[] = $size;

			$params->set('imageSizes', $imageSizes);

			$query = $db->getQuery(true);
			$query->update($db->quoteName('#__extensions'));
			$query->set($db->quoteName('params').' = '.$db->quote($params->toString()));
			$query->where($db->quoteName('element').' = '.$db->quote('com_k2'));
			$db->setQuery($query);
			$db->execute();
		}

		// Show results
		$this->installationResults();

	}

	public function uninstall($parent)
	{
		// Get database
		$db = JFactory::getDBO();

		// Get manifest
		$manifest = $parent->getParent()->manifest;

		// Install media/k2app
		if (JFolder::exists(JPATH_SITE.'/media/k2app'))
		{
			JFolder::delete(JPATH_SITE.'/media/k2app');
		}

		// Uninstall plugins
		$plugins = $manifest->xpath('plugins/plugin');
		foreach ($plugins as $plugin)
		{
			// Get plugin variables
			$name = (string)$plugin->attributes()->plugin;
			$group = (string)$plugin->attributes()->group;

			// Get extension id
			$query = $db->getQuery(true);
			$query->select($db->quoteName('extension_id'))->from($db->quoteName('#__extensions'))->where($db->quoteName('type').' = '.$db->quote('plugin'))->where($db->quoteName('element').' = '.$db->quote($name))->where($db->quoteName('folder').' = '.$db->quote($group));
			$db->setQuery($query);
			$id = $db->loadResult();
			if ($id)
			{
				// Uninstall
				$installer = new JInstaller;
				$result = $installer->uninstall('plugin', $id);
			}

		}

		// Uninstall modules
		$modules = $manifest->xpath('modules/module');
		foreach ($modules as $module)
		{
			// Get module variables
			$name = (string)$module->attributes()->module;
			$client = (string)$module->attributes()->client;

			// Get all module instances ids
			$query = $db->getQuery(true);
			$query->select($db->quoteName('extension_id'))->from($db->quoteName('#__extensions'))->where($db->quoteName('type').' = '.$db->quote('module'))->where($db->quoteName('element').' = '.$db->quote($name));
			$db->setQuery($query);
			$instances = $db->loadColumn();

			// Uninstall all module instances
			if (count($instances))
			{
				foreach ($instances as $id)
				{
					// Unistall module instance
					$installer = new JInstaller;
					$result = $installer->uninstall('module', $id);
				}
			}
		}
	}

	private function installationResults()
	{
		$language = JFactory::getLanguage();
		$language->load('com_k2');
		$document = JFactory::getDocument();
		$document->addStyleSheet(JURI::root(true).'/media/k2app/assets/css/installation.css');

		echo '<div class="tab-content-k2">';
		echo '<div class="container-fluid installation-wrap">';
		echo '	<div class="span8 offset2">';
		echo '		<div class="row">';
		echo '			<div class="span12 installation-block">';
		echo '				<div class="left">';
		echo '					<img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAMgAAABPCAYAAACu7Yr+AAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAADTZpVFh0WE1MOmNvbS5hZG9iZS54bXAAAAAAADw/eHBhY2tldCBiZWdpbj0i77u/IiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+Cjx4OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUCBDb3JlIDQuMi4yLWMwNjMgNTMuMzUyNjI0LCAyMDA4LzA3LzMwLTE4OjA1OjQxICAgICAgICAiPgogPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4KICA8cmRmOkRlc2NyaXB0aW9uIHJkZjphYm91dD0iIgogICAgeG1sbnM6ZGM9Imh0dHA6Ly9wdXJsLm9yZy9kYy9lbGVtZW50cy8xLjEvIgogICAgeG1sbnM6eG1wUmlnaHRzPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvcmlnaHRzLyIKICAgIHhtbG5zOnBob3Rvc2hvcD0iaHR0cDovL25zLmFkb2JlLmNvbS9waG90b3Nob3AvMS4wLyIKICAgIHhtbG5zOklwdGM0eG1wQ29yZT0iaHR0cDovL2lwdGMub3JnL3N0ZC9JcHRjNHhtcENvcmUvMS4wL3htbG5zLyIKICAgeG1wUmlnaHRzOldlYlN0YXRlbWVudD0iIgogICBwaG90b3Nob3A6QXV0aG9yc1Bvc2l0aW9uPSIiPgogICA8ZGM6cmlnaHRzPgogICAgPHJkZjpBbHQ+CiAgICAgPHJkZjpsaSB4bWw6bGFuZz0ieC1kZWZhdWx0Ii8+CiAgICA8L3JkZjpBbHQ+CiAgIDwvZGM6cmlnaHRzPgogICA8ZGM6Y3JlYXRvcj4KICAgIDxyZGY6U2VxPgogICAgIDxyZGY6bGkvPgogICAgPC9yZGY6U2VxPgogICA8L2RjOmNyZWF0b3I+CiAgIDxkYzp0aXRsZT4KICAgIDxyZGY6QWx0PgogICAgIDxyZGY6bGkgeG1sOmxhbmc9IngtZGVmYXVsdCI+QmFzaWMgQ01ZSzwvcmRmOmxpPgogICAgPC9yZGY6QWx0PgogICA8L2RjOnRpdGxlPgogICA8eG1wUmlnaHRzOlVzYWdlVGVybXM+CiAgICA8cmRmOkFsdD4KICAgICA8cmRmOmxpIHhtbDpsYW5nPSJ4LWRlZmF1bHQiLz4KICAgIDwvcmRmOkFsdD4KICAgPC94bXBSaWdodHM6VXNhZ2VUZXJtcz4KICAgPElwdGM0eG1wQ29yZTpDcmVhdG9yQ29udGFjdEluZm8KICAgIElwdGM0eG1wQ29yZTpDaUFkckV4dGFkcj0iIgogICAgSXB0YzR4bXBDb3JlOkNpQWRyQ2l0eT0iIgogICAgSXB0YzR4bXBDb3JlOkNpQWRyUmVnaW9uPSIiCiAgICBJcHRjNHhtcENvcmU6Q2lBZHJQY29kZT0iIgogICAgSXB0YzR4bXBDb3JlOkNpQWRyQ3RyeT0iIgogICAgSXB0YzR4bXBDb3JlOkNpVGVsV29yaz0iIgogICAgSXB0YzR4bXBDb3JlOkNpRW1haWxXb3JrPSIiCiAgICBJcHRjNHhtcENvcmU6Q2lVcmxXb3JrPSIiLz4KICA8L3JkZjpEZXNjcmlwdGlvbj4KIDwvcmRmOlJERj4KPC94OnhtcG1ldGE+CiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAKPD94cGFja2V0IGVuZD0idyI/PgzQVtUAAA6gSURBVHja7F0LcFTVGf6zSQiP1AQIICmvIBAbIA7WaCwgiKIglCgPSxQQlZcBIVEULK8QgnkgYkXRqfVVW6cdX1MHrVWqI62vTtspWqe12Gnqo1bbqqAgkCzp/909y4SY3f3v5t7dveeeb+aboPk35+6957vn/8/5z3/SMrKyiLFcMZ8MnMaHzEbmw+ZWeA9pLJAK/nm3uRWu40rmo+Y2eE8g+/nnMHMrXMcfmaXMZt37FDNDl+8JgXzBP7NN/3UduM8DmQei2GQydzKHMo9HsElnPsO8I4HXvpg5m9kqsO3KvIX5mg4PDUoPmr6bEBwX2ASYFzJPi2H37wRe92TlgmcK7R9h/sHF/pqtYuW+zG5tRIuR6zPmR8z/Mg851aBBauErgc3RBF1LAfMBG+L4lRptjjnUPkajUcwJzDHMQjW6dmdmKVG0BV72R5ifMvcpvsR8PV7BGIEYREK2Gg0GCO3fYs53QLzo9Ocql+4SNZpK+ynczx6KcGenM9cx32U+xnyQud/OxQRMPzCIgB3MsUJbuHxXMP/TifYCShS/Zu5lVqkRw4mX+DAVF/2OeTvZWM4wAjHoCDcwFwltMWJcxfxzJ9qboUSBt/z5aiRwA7lKeK8wZxmBGMSDqcx6G/aVzOfjbGuEEsUvbIxWTmAI83FmtRGIgd0Oe7+NoBxCujfOthCvvKzcqmRhE/M2IxADCU5RQXl/of2TzA1xtIPZp+3MHzNPTYHvfSOzJtIvzSyWQRg/YJ4ttH2DeS2zpRMxRzwIT+F+qiYEEKv0UuxJoXWReAChv6XcPSMQg69hLXOh0PZ9Cs1YfR5nW0eVGHcK7d9h7mG+yvwT8z0KpbFgrSVNuYPgN5nnqFimjNnP5nUhMwGr/x+0/Z9INcEXzTF9xHUgxWQwRU81gfvxewotjkUD4oRFDl3Xd5W7JHlZHlRB/KudbBNve8x6RZpuPa4Cd2RAv8A8bPPvQxzXMFer0UWKr91XE4P4GyNVp5B6EiscEAeAlJAHI/zuaeZ5zJlKJIfj+PsfM+vU37FzvVe2fzkZgfgXOSpQ7mPDT3/EwfZ/pEakMP7GnKPco1ccauNtCq2mvyi0R2rLciMQA2AX80yhLfKxah1uv4n5lPo39slMpNDahNPAaDVXCVCCmW3dMiMQf2K9CrQleLH9W9VBYA3lJuXafOTi98WMVyXJMqqRJXyREYh/gRSLGqEtEvsWUGh61Q0gy/a2BH3vXzJ3C20v1FIgLUePUmtrq5FAZBQz76Ovp4l3BKw1XE6hPfW64AGh3Rmk8sG0EAgkEQwGqbCoiNLS0oxIOkZPFWT3lLxrmFdTaN1BJ7wodOWQRdxbG4EEeeQ4vbCQnnv2WRo7diwFjx0zcjgZATVyFAvtsX7wtIb34Qvl1sUCNmQVaCGQ48ePU1p6OtXdeisNGTyY6vln1+7drRHF4AQ2kjC9m3EXhVa6dcVfBDbhFBYNBNLcTOXl5VQ2I5TeU3rOObRy5UpqbWkh42hZuEIJRAKMGjdofj/+IbTzvkBa2JXKHzDAGj3a4vtr11LRqFGW6+VzYJ1jlzAo36fiDt3LEn0qtOvhaYFYgThzc3U1DRo48KTf5eTkUGNjIwUyMiwXzKfACjlWyiV5dpipmmuj83g9DpHgoKcFgkB88pQpdM3ChR3+ftrUqTR/3jzLBfMhkFuFHKuRAltUUcFax199cm++IbT7zLMCCXJ8cUpuLt3W0ECB9Mjbl2/dutVywVr8N6tVTaEsXQlWkTxXSQf0lnjvzP95UiBwrVqDQbpp9WoqLo4+a5mfn0+1W7ZYrpiP1kYwpK4T2iK/6j6fvTwGC0fV9zwpELhW3y4podU33iiyv2rBApoybZpf1kZKSD5FiwTBjeQ/jBDYNHnSxcLaRkaXLrSNA/CuXbuKPhMIBKixro5yeva0XDONkcd8iEJ7y2MB6eTXEfluJhxbcksFdm+Sms1LqkCQOyWdZcKTxNrG0mXL6PyJE221M3r0aFpz882Wa6axq4Xp3CKB3T8ptDZy0IejB8qXSipF/ubECzYpcQTE0dxMI7njduERQdJp4SINLyyk6g0b4mqzctUqKikt1dXVQjG0OQI7pH1/L+xf+xDYPBVrTQiLZy8lVSDopCNU7tQlU6fG7LThUWZrbS3l5eXFN7Z260bbt22jTBakZmkoqESyRWiLsptv+FQcPdTLIRawRXd/0gQS7uy1NTU0YMAAq9Pn9upFwSjrFVjLmDV7Ns2Z3bkaY+PHjaOK5ct1SkPBIuB96uFLgPT1XJ8KBFt5hwrsHm8bmyVeIO06O7Jw169fT60snI5cLbhief36UWNDgyPtb1i3joaffrrX01DCgRsqGxbb+NwYG6ONTkA/v15gh3NFnmr/wcQF5RE6+4qKCirtIE3dEgwLZxPHHUMLChy5ht69e1sZv2mBgJfTUJAuMY25NI7P4kzKyT4TCNJoJLNX2C/zUVIEEq2zZ2VlWavi7dPUIZiJF1xASxYvdvRaZl52Gc0tL/dyGgqSEHeSLAmxo7fpTh+5Wkgt2SSww6zenR3drIQF5hMmTYrY2bHRafmKFSfiA6xZdM/Opts5sMZMl9NA7NOvf39rVPMgJpLa0BMnCn3kaq0h2eIgyhA1JUUgGBW69+hB23iUiNbZ199yC31r5EhLTFizqKqspDFjxrhyTQVDhtBGTBlHiH00iE+MqxWa4ZPsb0GhuW2RhlvXXSuMClVVVVRy1llRbXNzc6m+rs7KnRpVXExr16xx9dqWLllCkyZP1mltBHvIMftxwLha1swedkdKClqjykuHB6O6XpsXq+Vn8Cjw2717KTtbdtr0goULrTjh0rIy1+/ivjffpLHjx9PhQ4coPcPVWt5O1ubtCMg+nUShNAkUo64Tfu4u4QyP14A1nyqB3V41kh5LuECs/eI8GuzevZumXHyx+HPH+I3uRtwRCZuqq6lm82ZK5zZRFcWDAsHDRUXAZ9R/I1HtZZIdZwCXbAqFikTrAqz3/Fxgh5Nvv6NeKpRQFwtePWaJrr72WlviABIpDgCp82PY/fOwq7WqjTiAI2pUkBR8083VwrqQ9NSrDdHE4apA0NkGcSBcs2lTyt9RuH5Ym/FoGkpdhA6BE12lq6u6zGqhbOhPSFb7CwUq7pC8PVwJzBFo19bWUv/+/T1xZy+cNIkWLV7stTQU7OmItjmqXglFAsxqXeRhccDtwJEKowW2qGyyjATp/q7EIAjMyzjIfuqJJ9z06R3Hx598QmeXltJ7TU3WvpMUj0GQko3s1Fhp62ereESygebvzHOpc+edJwv3qE4fC0dUzPWy5I86PoIg6RDJh4319Z4SB9Cvb1+q27r1xARDCgPHkkn3dNhxtU4j+exXKmG9UBxAlVQcjgvEWvPgjoXkwxEjRpAXcUV5Oc2cNSvV01DwgD+wYW/H1cLRZZd66JEtI3vp/raOrXZUIAjMzx03zko+9DKQzJjHo0kwdUWSbtPezqwWhn0c09zHA48Ko6h0Dz7OYVxrtwHHBILZHyQbIncKyYdexvDhw6Om4HsUdlytoR5wtXCY6AMqOI8FbILCsdXNSRFIeL84kg1LS0u16E3LliyhcRMm6LZFVxdXCwfc/FRNasQCTtNFuntcx1Y7IhBsPkKSIZINdQFGQYyG3fSqFB92tSS7xVLV1YI4HiPZWgf23mOv/vvxNtZpgWC2BzVw6+vrrWRDnVBSUkIrKyt1qxSPEeROj7paZylxSDoakg9R8r9TJVU7LxAOZOfNm0czpk8nHYFREdVXNKsUv5lipFikoKuFY9EeF4rjcxXA7+tso50SSPj4gS2bN5OuQBpKQ0MDpWdm6lQp/pBytSQBViq4WhAHUkMGC8UBt+olJxqOWyAnHT8waBDpDFSKRwlTzSrFI81bOkWaTFcrLI5BNsSxx6nG4xYIZndQ8zbS8QO6YUtNDQ0cMkS3SvF2Xa2yBF9fiQ1xHHBaHHELJHz8AGreRjt+QCegUnwNj5aaVYq362rtYJ6aoGvDbNVzQnF8pQS8x+mLsC2QtscPoOatn7Bg/nyaOn26bmsjdlytAiWSRIgDs1W9hG4VJhGedONCbAsEneNMG8cP6ARUikd5opwYlSA1d7XmKrotDulsFdyq51175rbEwSMHNhXt2L5dfPyAbigqKrIOCdUsDcWOqwVgVis/RcSxx80bIxZIOJ0EtW3PGz+e/IyV11/fYSVIH7la+UokTqKc7K1zuC4OWwLBQtmwwkKrtq3fgdHTqgTZrZtuleLhakkX15x0ta5kPkyyjXtIHylLhDjEArGqk7D/XVdba9W2NQhVgryuokK3NBS4Wii0Jl0RdcLVgjiQlZspsG2iUPrI3oTFnSKBcEA65/LLaXYnjx/QDRuxMcz7leLbAyfe7kqQqxUWhyRl/S0KbZXdl8ibEVMgqF3bLz/f2kRkcDKQnNmojqLWKA0FQDmcd1x2teyI4zUVwL+T6BsRtZQgZmoyMjIsf7vAoeMHdEPZjBm0ZOlSuveeeyiQ4HpeLgJBMGa1nhN6GduV2/MvF8TxLoUKUCM+6Zvg+xDMiBV75PXpQ18eOkQPPvSQn84alw/BHJshLsvMygrFah4rVBEFLyhXa4UNV6tcYHsV84dCcQA4dPNZYYziNA6Iyv606OVju6ESysiM+fycLPtzP3NRIrxI5usUKiwnAQTysyi/h9h2eujJfimq1pyuj+tg4K6rhaoh2P8d6RTdWR77/kGRQDRyGwzcdbVQRhOLjSik3ZE/7jlXJGCev4EAdma1kDh4jTbes3n2BjZcLelcNjZXDdVFIOnm+afUy0hSyiYZMzpwte4W2vZRtu2/i9eC2SzEIKj+MMz0X9exn3k4hg389reZLQgQo4ijKUnfAWdZjFKxRksM2+FMZLXuaXcPJJ9NlRfaQUzzVth4MxjEDyyOPSqw66IeTmuUB9ecxE4WUNfYKrCDzZF24k4XfDZlAIHg53LFfNOPHceHzEYKZasaeAz/F2AAawpOLjFPO0AAAAAASUVORK5CYII="/>';
		echo '				</div>';
		echo JText::_('K2_POST_INSTALLATION_MESSAGE');
		echo '			<div class="call-to-action">';
		echo '				<a class="jw--btn jw--btn__item" href="index.php?option=com_k2">'.JText::_('K2_GO_TO_K2').'</a>';
		if ($this->upgrade)
		{
			echo '			<a class="jw--btn jw--btn__item" href="#" onclick="window.open(\'index.php?option=com_k2&view=migrate&tmpl=component\', \'K2\', \'width=640,height=480\'); return false;">'.JText::_('K2_INITIATE_DATA_MIGRATION_PROCESS').'</a>';
		}
		echo'			</div>';
		echo '</div></div></div></div></div>';
	}
}
