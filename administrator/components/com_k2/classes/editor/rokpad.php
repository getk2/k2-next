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

class K2EditorRokpad extends K2Editor
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
				
		parent::__construct($editor);
		$this->editor = $editor;
		
		// Load the editor
		$this->_loadEditor ();

		$this->onDisplay = $this->parentDisplay('K2_REPLACE_NAME', 'K2_REPLACE_HTML', 'K2_REPLACE_WIDTH', 'K2_REPLACE_HEIGHT', 'K2_REPLACE_COL', 'K2_REPLACE_ROW');	

		$this->js = '';
		
		if($originalDocument->getType() == 'html'){
			foreach ($myDocument->_scripts as $script => $attributes) {
				$originalDocument->_scripts[$script] = $attributes;
			}
			foreach ($myDocument->_script as $type => $code) {
				$this->js .= $code;
			}	
			
			foreach ($myDocument->_styleSheets as $sheet => $attributes) {
				$originalDocument->_styleSheets[$sheet] = $attributes;
			}
			foreach ($myDocument->_style as $type => $code) {
				if (isset($originalDocument->_script[$type])){
					$originalDocument->_style[$type] .= $code;
				}else{
					$originalDocument->_style[$type] = $code;
				}
			}
			foreach ($myDocument->_custom as $cid => $html)
			{
				$doc = new DOMDocument();
				$doc->loadHTML($html);
				$scripts = $doc->getElementsByTagName ('script');
				foreach ( $scripts as $key => $script ) {
					if($script->hasAttribute('type') && ($script->getAttribute('type') == 'text/javascript')){
						if ($script->hasAttribute('src')){
							$originalDocument->_scripts[$script->getAttribute('src')] = array('mime' => 'text/javascript', 'defer' => 'false', 'async' => 'false');
						}else{				
							$this->js .= $scripts->item ( $key )->nodeValue;
						}
					}else{
						$this->js .= $scripts->item ( $key )->nodeValue;		
					}
				}
				
				$links = $doc->getElementsByTagName ('link');
				foreach ( $links as $key => $link ) {
					if($link->hasAttribute('type') && ($link->getAttribute('type') == 'text/css')){
						$originalDocument->_styleSheets[$link->getAttribute('href')] = array('mime' => 'text/css', 'media' => null, 'attribs' => array());
					}
				}
			}

			$originalDocument->addCustomTag("<script type='text/javascript'>".$this->js."</script>");
			$this->js = '';
		}
		
		JFactory::$document = $originalDocument;
		$this->js = "RokPad.initialize()";
	}
}
