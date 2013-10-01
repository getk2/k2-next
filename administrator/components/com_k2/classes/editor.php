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

jimport('joomla.html.editor');

/**
 * K2 Editor class.
 * Overrides the default editor class in order to get the required scripts for the editor.
 */

class K2Editor extends JEditor
{
	public static function getInstance($editor = 'none')
	{
		$signature = serialize($editor);

		if (empty(self::$instances[$signature]))
		{
			self::$instances[$signature] = new K2Editor($editor);
		}

		return self::$instances[$signature];
	}

	public function init()
	{
		$plugin = JPluginHelper::getPlugin('editors', $this->_name);
		require_once JPATH_SITE.'/plugins/editors/'.$plugin->name.'/'.$plugin->name.'.php';
		$className = 'plgEditor'.JString::ucfirst($plugin->name);
		$editor = new $className($this, (array)$plugin);
		$onInit = $editor->onInit();

		if (empty($onInit))
		{
			return '';
		}

		// We only need to fetch the script declarations since other scripts are already loaded
		$doc = new DOMDocument();
		$doc->loadHTML($onInit);
		$scripts = $doc->getElementsByTagName('script');
		$js = '';
		foreach ($scripts as $key => $script)
		{
			$js .= $scripts->item($key)->nodeValue;
		}
		return $js;
	}
	public function initialise()
	{
		// Check if editor is already loaded
		if (is_null(($this->_editor)))
		{
			return;
		}

		$args['event'] = 'onInit';

		$return = '';
		$results[] = $this->_editor->update($args);

		foreach ($results as $result)
		{
			if (trim($result))
			{
				// @todo remove code: $return .= $result;
				$return = $result;
			}
		}

		$document = JFactory::getDocument();
		if($document->getType() == 'html')
		{
			$document->addCustomTag($return);
		}
		
	}
}
