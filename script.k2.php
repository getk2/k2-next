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
		if($type != 'install')
		{
			// Ensure that we are under Joomla! 3.2 or later
			if(version_compare(JVERSION, '3.2.4', 'lt'))
			{
				$application->enqueueMessage('K2 v3 requires Joomla! 3.2.4 or later.', 'error');
				return false;
			}
						
			// Get installled version
			$query = $db->getQuery(true);
			$query->select($db->quoteName('manifest_cache'))->from($db->quoteName('#__extensions'))->where($db->quoteName('name').' = '.$db->quote('com_k2'));
			$db->setQuery($query);
            $manifest = json_decode($db->loadResult());
			$installedVersion = $manifest->version;
						
			// Detect if we need to perform an upgrade
			if(version_compare($installedVersion, '3.0.0', 'lt'))
			{
				// Ensure that the installed K2 version is not very old. Otherwise the update will fail.
				if(version_compare($installedVersion, '2.6.8', 'lt'))
				{
					$application->enqueueMessage('You cannot update from this version of K2. Please update first your current K2 installation to the latest 2.x series and try again.', 'error');
					return false;
				}
				
				// User is required to put the site offline while upgrading
				if(!$configuration->get('offline'))
				{
					$application->enqueueMessage('Site is not offline. Please put your site offline and try again.', 'error');
					return false;
				}
				
				// User is required to disable Joomla! system debugger while upgrading
				if($configuration->get('debug'))
				{
					$application->enqueueMessage('Joomla! system debugger is enabled. Please disable it and try again.', 'error');
					return false;
				}
				
				// User is required to disable Joomla! language debugger while upgrading
				if($configuration->get('debug_lang'))
				{
					$application->enqueueMessage('Joomla! language debugger is enabled. Please disable it and try again.', 'error');
					return false;
				}
				
				// User is required to set error reporting to "None" or "Simple" while upgrading to avoid breaking JSON response due to PHP notices.
				if($configuration->get('error_reporting') != 'none' && $configuration->get('error_reporting') != 'simple')
				{
					$application->enqueueMessage('Joomla! error reporting is enabled. Please set it to "None" or "Simple" and try again.', 'error');
					return false;
				}
				
				// Ensure that all K2 plugins are disabled
				$k2Plugins = JPluginHelper::getPlugin('k2');
				if(count($k2Plugins))
				{
					$application->enqueueMessage('Third-party K2 plugins found on your system. Please disable them and try again.', 'error');
					return false;					
				}
				
				// Since this is an upgrade rename all K2 2.x tables so the new ones will be created.
				$oldTables = array('#__k2_attachments', '#__k2_categories', '#__k2_comments', '#__k2_extra_fields' , '#__k2_extra_fields_groups', '#__k2_items', '#__k2_rating', '#__k2_tags', '#__k2_tags_xref', '#__k2_users', '#__k2_user_groups');
				foreach($oldTables as $oldTable)
				{
					$newTable = str_replace('#__k2_', '#__k2_v2_', $oldTable);
					$db->setQuery('DROP TABLE IF EXISTS '.$db->quoteName($newTable));
					$db->execute();
					$db->setQuery('RENAME TABLE '.$db->quoteName($oldTable).' TO '.$db->quoteName($newTable));
					if(!$db->execute())
					{
						$application->enqueueMessage(JText::sprintf('JLIB_INSTALLER_ABORT_COMP_INSTALL_SQL_ERROR', $db->stderr(true)), 'error');
						return false;
					}
				}
				
				// Force parsing of SQL file since Joomla! does that only in install mode, not in upgrades
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
							$application->enqueueMessage(JText::sprintf('JLIB_INSTALLER_ERROR_SQL_ERROR', $db->stderr(true)), 'error');
							return false;
						}
					}
				}
				
				// Rename component files to get rid of files we don't need
				if(JFolder::exists(JPATH_SITE.'/components/com_k2'))
				{
					JFolder::move(JPATH_SITE.'/components/com_k2', JPATH_SITE.'/components/com_k2_v2');
					JFolder::create(JPATH_SITE.'/components/com_k2');
					JFolder::copy(JPATH_SITE.'/components/com_k2_v2/templates', JPATH_SITE.'/components/com_k2/templates');
				}
				if(JFolder::exists(JPATH_ADMINISTRATOR.'/components/com_k2'))
				{
					JFolder::move(JPATH_ADMINISTRATOR.'/components/com_k2', JPATH_ADMINISTRATOR.'/components/com_k2_v2');
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
		
        // Initialize status object
        $status = new stdClass;
        $status->modules = array();
        $status->plugins = array();
		
		// Get manifest
        $src = $parent->getParent()->getPath('source');
        $manifest = $parent->getParent()->manifest;
		
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
			if($type == 'install' && $group != 'finder')
			{
				$query = $db->getQuery(true);
				$query->update($db->quoteName('#__extensions'))->set($db->quoteName('enabled').' = 1')->where($db->quoteName('type').' = '.$db->quote('plugin'))->where($db->quoteName('element').' = '.$db->quote($name))->where($db->quoteName('folder').' = '.$db->quote($group));
            	$db->setQuery($query);
            	$db->execute();
			}
			
			// Update status
            $status->plugins[] = array('name' => $name, 'group' => $group, 'result' => $result);
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
            $path = ($client == 'administrator') ?  $src.'/administrator/modules/'.$name : $src.'/modules/'.$name;
			
			// Install
            $installer = new JInstaller;
            $result = $installer->install($path);
			
			// Update status
            $status->modules[] = array('name' => $name, 'client' => $client, 'result' => $result);
			
			// Publish the administrator modules. Only for fresh installations, not updates!
			if($type == 'install' && $client == 'administrator')
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
				if($id)
				{
					$query = $db->getQuery(true);
					$query->insert($db->quoteName('#__modules_menu'))->columns('moduleid, menuid')->values($id.',0');
					$db->setQuery($query);
					$db->execute();
				}
			}
        }

		// Set the default image sizes for new installs
		if($type == 'install')
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
        $this->installationResults($status);
       
    }

    public function uninstall($parent)
    {
    	// Get database
        $db = JFactory::getDBO();
		
		// Initialize status object
        $status = new stdClass;
        $status->modules = array();
        $status->plugins = array();
		
		// Get manifest
        $manifest = $parent->getParent()->manifest;
		
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
				
				// Update status
                $status->plugins[] = array('name' => $name, 'group' => $group, 'result' => $result);
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
				
				// Update status
                $status->modules[] = array('name' => $name, 'client' => $client, 'result' => $result);
            }
            
        }

		// Show results
        $this->uninstallationResults($status);
    }

    private function installationResults($status)
    {
    	JHtml::_('jquery.framework');
        $language = JFactory::getLanguage();
        $language->load('com_k2');
        $rows = 0; ?>
        <img src="<?php echo JURI::root(true); ?>/media/k2/assets/images/system/K2_Logo_126x48_24.png" alt="K2" align="right" />
        <?php if($this->upgrade): ?>
        <h1>Upgrade in Progress. Don't leave this page until the process completes!</h1>
        <span id="k2UpgradeStatus"></span>
        <span>Last updated before <span id="k2UpgradeLastUpdated">0</span> seconds</span>
        <ul id="k2UpgradeErrorLog"></ul>
        <script type="text/javascript">
        	function K2Restore() {
        		jQuery.post('index.php?option=com_k2&task=migrator.restore&format=json', '<?php echo JSession::getFormToken(); ?>=1').done(function(response) {
        			jQuery('#k2UpgradeStatus').html('Restore completed');
        		})
        		.fail(function(response) {
					jQuery('#k2UpgradeStatus').html('Restore failed');
				});
        	}
        	function K2Migrate(type, id) {
        		jQuery.post('index.php?option=com_k2&task=migrator.run&type=' + type + '&id=' + id + '&format=json', '<?php echo JSession::getFormToken(); ?>=1')
        		.done(function(response) {
					if (response) {
						jQuery.each(response.errors, function( index, error ) {
							jQuery('#k2UpgradeErrorLog').append('<li>' + error + '</li>');
						});
						if(response.failed) {
							jQuery('#k2UpgradeStatus').html('Upgrade failed. Trying to restore previous version. Please wait');
							K2Restore();
						} else if(response.completed) {
							jQuery('#k2UpgradeStatus').html('Upgrade completed');
						} else {
							jQuery('#k2UpgradeStatus').html(response.status);
							K2Migrate(response.type, response.id);
						}
					}
				})
				.fail(function(response) {
					jQuery('#k2UpgradeStatus').html('Upgrade failed. Trying to restore previous version. Please wait');
					K2Restore();
				});
        	}
        	K2Migrate('attachments', 0);
        </script>
        <?php endif; ?>
        
        <h2><?php echo JText::_('K2_INSTALLATION_STATUS'); ?></h2>
        <table class="adminlist table table-striped">
            <thead>
                <tr>
                    <th class="title" colspan="2"><?php echo JText::_('K2_EXTENSION'); ?></th>
                    <th width="30%"><?php echo JText::_('K2_STATUS'); ?></th>
                </tr>
            </thead>
            <tfoot>
                <tr>
                    <td colspan="3"></td>
                </tr>
            </tfoot>
            <tbody>
                <tr class="row0">
                    <td class="key" colspan="2"><?php echo 'K2 '.JText::_('K2_COMPONENT'); ?></td>
                    <td><strong><?php echo JText::_('K2_INSTALLED'); ?></strong></td>
                </tr>
                <?php if (count($status->modules)): ?>
                <tr>
                    <th><?php echo JText::_('K2_MODULE'); ?></th>
                    <th><?php echo JText::_('K2_CLIENT'); ?></th>
                    <th></th>
                </tr>
                <?php foreach ($status->modules as $module): ?>
                <tr class="row<?php echo(++$rows % 2); ?>">
                    <td class="key"><?php echo $module['name']; ?></td>
                    <td class="key"><?php echo ucfirst($module['client']); ?></td>
                    <td><strong><?php echo ($module['result'])?JText::_('K2_INSTALLED'):JText::_('K2_NOT_INSTALLED'); ?></strong></td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
                <?php if (count($status->plugins)): ?>
                <tr>
                    <th><?php echo JText::_('K2_PLUGIN'); ?></th>
                    <th><?php echo JText::_('K2_GROUP'); ?></th>
                    <th></th>
                </tr>
                <?php foreach ($status->plugins as $plugin): ?>
                <tr class="row<?php echo(++$rows % 2); ?>">
                    <td class="key"><?php echo ucfirst($plugin['name']); ?></td>
                    <td class="key"><?php echo ucfirst($plugin['group']); ?></td>
                    <td><strong><?php echo ($plugin['result'])?JText::_('K2_INSTALLED'):JText::_('K2_NOT_INSTALLED'); ?></strong></td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    <?php
	}

	private function uninstallationResults($status)
	{
	$language = JFactory::getLanguage();
	$language->load('com_k2');
	$rows = 0; ?>
        <h2><?php echo JText::_('K2_REMOVAL_STATUS'); ?></h2>
        <table class="adminlist table table-striped">
            <thead>
                <tr>
                    <th class="title" colspan="2"><?php echo JText::_('K2_EXTENSION'); ?></th>
                    <th width="30%"><?php echo JText::_('K2_STATUS'); ?></th>
                </tr>
            </thead>
            <tfoot>
                <tr>
                    <td colspan="3"></td>
                </tr>
            </tfoot>
            <tbody>
                <tr class="row0">
                    <td class="key" colspan="2"><?php echo 'K2 '.JText::_('K2_COMPONENT'); ?></td>
                    <td><strong><?php echo JText::_('K2_REMOVED'); ?></strong></td>
                </tr>
                <?php if (count($status->modules)): ?>
                <tr>
                    <th><?php echo JText::_('K2_MODULE'); ?></th>
                    <th><?php echo JText::_('K2_CLIENT'); ?></th>
                    <th></th>
                </tr>
                <?php foreach ($status->modules as $module): ?>
                <tr class="row<?php echo(++$rows % 2); ?>">
                    <td class="key"><?php echo $module['name']; ?></td>
                    <td class="key"><?php echo ucfirst($module['client']); ?></td>
                    <td><strong><?php echo ($module['result'])?JText::_('K2_REMOVED'):JText::_('K2_NOT_REMOVED'); ?></strong></td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
        
                <?php if (count($status->plugins)): ?>
                <tr>
                    <th><?php echo JText::_('K2_PLUGIN'); ?></th>
                    <th><?php echo JText::_('K2_GROUP'); ?></th>
                    <th></th>
                </tr>
                <?php foreach ($status->plugins as $plugin): ?>
                <tr class="row<?php echo(++$rows % 2); ?>">
                    <td class="key"><?php echo ucfirst($plugin['name']); ?></td>
                    <td class="key"><?php echo ucfirst($plugin['group']); ?></td>
                    <td><strong><?php echo ($plugin['result'])?JText::_('K2_REMOVED'):JText::_('K2_NOT_REMOVED'); ?></strong></td>
                </tr>
                <?php endforeach; ?>
				<?php endif; ?>
			</tbody>
		</table>
<?php
}
}
