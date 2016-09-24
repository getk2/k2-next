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

jimport('joomla.html.editor');

/**
 * K2 Editor class.
 * Overrides the default editor class in order to get the required scripts for the editor.
 */

class K2Editor extends JEditor
{
	protected $js;
	protected $onDisplay;

	public static function getInstance($editor = 'none')
	{
		$signature = serialize($editor);

		if (empty(self::$instances[$signature]))
		{
			self::$instances[$signature] = self::_loadHelper($editor);
		}

		return self::$instances[$signature];
	}
	
	protected function __setInstanceName($editor){
		parent::__construct($editor);
	}
	
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
				
		$this->__setInstanceName($editor);
		
		// Load the editor
		$this->_loadEditor ();
		$this->js = '';
	
		$this->onDisplay = parent::display('K2_REPLACE_NAME', 'K2_REPLACE_HTML', 'K2_REPLACE_WIDTH', 'K2_REPLACE_HEIGHT', 'K2_REPLACE_COL', 'K2_REPLACE_ROW');
		
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
					}elseif($editor == 'arkeditor'){
						if($script->hasAttribute('src')){	
							$originalDocument->addCustomTag('<script type="text/javascript" src="'.$script->getAttribute('src').'"></script>');
						}else{
							$originalDocument->addCustomTag("<script type='text/javascript'>".$scripts->item ( $key )->nodeValue."</script>");
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
		}
		
		JFactory::$document = $originalDocument;
	}

	public function init() {
		// Return editor scripts
		return $this->js;
	}

	public function getContent($editor){
		return parent::getContent($editor);
	}

	public function setContent($editor, $html){
		return parent::setContent($editor, $html);
	}

	public function save($editor){
		return parent::save($editor);
	}
	
	/**
	 * Display the editor area.
	 *
	 * @param   string   $name     The control name.
	 * @param   string   $html     The contents of the text area.
	 * @param   string   $width    The width of the text area (px or %).
	 * @param   string   $height   The height of the text area (px or %).
	 * @param   integer  $col      The number of columns for the textarea.
	 * @param   integer  $row      The number of rows for the textarea.
	 * @param   boolean  $buttons  True and the editor buttons will be displayed.
	 * @param   string   $id       An optional ID for the textarea (note: since 1.6). If not supplied the name is used.
	 * @param   string   $asset    The object asset
	 * @param   object   $author   The author.
	 * @param   array    $params   Associative array of editor parameters.
	 *
	 * @return  string
	 *
	 * @since   1.5
	 */
	public function display($name, $html, $width, $height, $col, $row, $buttons = true, $id = null, $asset = null, $author = null, $params = array())
	{
		$result = $this->onDisplay;
		$result = str_replace('K2_REPLACE_NAME', $name, $result);
		$result = str_replace('K2_REPLACE_HTML', $html, $result);
		$result = str_replace('K2_REPLACE_WIDTH', $width, $result);
		$result = str_replace('K2_REPLACE_HEIGHT', $height, $result);
		$result = str_replace('K2_REPLACE_COL', $col, $result);
		$result = str_replace('K2_REPLACE_ROW', $row, $result);
		
		return $result;
	}
	
	protected function parentDisplay($name, $html, $width, $height, $col, $row, $buttons = true, $id = null, $asset = null, $author = null, $params = array()){
		return parent::display($name, $html, $width, $height, $col, $row, $buttons, $id, $asset, $author, $params);		
	}
	
	private function _loadHelper($editor){
		$helperdir = dirname(__FILE__).'/editor/';
			
		$path = $helperdir . $editor. '.php';
		
		if (is_file($path))	{
			require_once $path;
			$class = 'K2Editor'.ucwords($editor);
		}else{
			$class = 'K2Editor';
		}
		return new $class($editor);
	}
}
