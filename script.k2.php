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

class Com_K2InstallerScript
{
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
        $language = JFactory::getLanguage();
        $language->load('com_k2');
        $rows = 0; ?>
        <img src="<?php echo JURI::root(true); ?>/media/k2/assets/images/system/K2_Logo_126x48_24.png" alt="K2" align="right" />
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
