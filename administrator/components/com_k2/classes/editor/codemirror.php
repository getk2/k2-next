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

class K2EditorCodemirror extends K2Editor
{
	public function __construct($editor = 'none')
	{
		
		// Okay, we're about to load an editor plug-in. Yet, we don't know whether we're called within
		// a HTML document or within a JSON document. The editor plug-in assumes a HTML document and may
		// use constructs that are not applicable to JSON documents. 
		// To avoid issues, we'll just make our own HTML document, use this for initializing the plug-in
		// and copy the results where we need them.
		
		$lang = JFactory::getLanguage();
		$version = new JVersion;
		
		$attributes = array(
				'charset' => 'utf-8',
				'lineend' => 'unix',
				'tab' => '  ',
				'language' => $lang->getTag(),
				'direction' => $lang->isRtl() ? 'rtl' : 'ltr',
				'mediaversion' => $version->getMediaVersion(),
				'private' => 'no other function uses this key; avoids caching effects'
		);
		
		$myDocument = JDocument::getInstance('html', $attributes);
		$originalDocument = JFactory::$document;
		
		JFactory::$document = $myDocument;
				
		$this->_name = $editor;
		
		// Load the editor
		$this->_loadEditor ();
		$this->js = '';
		
		$scriptLength = strlen($myDocument->_script['text/javascript']);
		$this->onDisplay = $this->parentDisplay('K2_REPLACE_NAME', 'K2_REPLACE_HTML', 'K2_REPLACE_WIDTH', 'K2_REPLACE_HEIGHT', 'K2_REPLACE_COL', 'K2_REPLACE_ROW');
		
		$scriptParts = explode('jQuery',substr($myDocument->_script['text/javascript'], $scriptLength));
		$jQueryPart = 'jQuery'.array_pop($scriptParts);
		$jQueryPart = preg_replace('#jQuery[^\{]*\{#', '', $jQueryPart);
		$jQueryPart = preg_replace('#\}\)\;\s*$#', '', $jQueryPart);
		$jQueryPart = str_replace('options', 'CodeMirrorOptions', $jQueryPart);  // eliminate namespace conflict again after onDisplay
		
		$otherParts = implode('jQuery ', $scriptParts);
		$otherParts = str_replace('"K2_REPLACE_NAME"','editor', $otherParts);
		
		$jsWrapStart = 'if (typeof define == "function" && define.amd){var myDefineSaver = define; define = 0;}';
		$jsWrapEnd = 'if (typeof myDefineSaver == "function"){define = myDefineSaver;}';
		
		$this->onDisplay .= "\n<script type='text/javascript'>".$jQueryPart.';</script>';
		$myDocument->_script['text/javascript'] = substr($myDocument->_script['text/javascript'], 0, $scriptLength);
		
		if($originalDocument->getType() == 'html'){
			$originalDocument->addScriptDeclaration($otherParts);
				
			$this->scriptAfterRequireJs = '';
			
			foreach ($myDocument->_scripts as $script => $attributes) {
				$this->scriptAfterRequireJs .= '<script type="text/javascript" src="'.$script.'"></script>'."\n";
				$originalDocument->_scripts[$script] = $attributes;
			}
			foreach ($myDocument->_script as $type => $code) {
				$this->scriptAfterRequireJs .= '<script type="text/javascript">jQuery(function(){'.$code.'});</script>'."\n";				
 				if (isset($originalDocument->_script[$type])){
					$originalDocument->_script[$type] .= $code;
 				}else{
 					$originalDocument->_script[$type] = $code;
 				}
			}	
			
			foreach ($myDocument->_styleSheets as $sheet => $attributes) {
				$originalDocument->_styleSheets[$sheet] = $attributes;
			}
			foreach ($myDocument->_style as $type => $code) {
				if (isset($originalDocument->_style[$type])){
					$originalDocument->_style[$type] .= $code;
				}else{
					$originalDocument->_style[$type] = $code;
				}
			}
		}
		
		JFactory::$document = $originalDocument;
	}
}
