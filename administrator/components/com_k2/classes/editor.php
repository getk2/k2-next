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
	private $js;
	private $start;
	private $editor;

	public static function getInstance($editor = 'none')
	{
		$signature = serialize($editor);

		if (empty(self::$instances[$signature]))
		{
			self::$instances[$signature] = new K2Editor($editor);
		}

		return self::$instances[$signature];
	}

	public function __construct($editor = 'none')
	{
		parent::__construct($editor);
		$this->init();
		$this->editor = $editor;
	}

	public function init() {

		// Load the editor
		$this->_loadEditor ();

		// Initialize some vars
		$results = array();
		$args = array();

		// Execute the init event on the active editor plugin. Add any returned scripts to our script variable
		$args['event'] = 'onInit';
		$results[] = $this->_editor->update($args);

		// Get the current scripts before executing the onDisplay event of the editor
		$document = JFactory::getDocument();
		$currentInlineScript = isset($document->_script['text/javascript']) ? $document->_script['text/javascript'] : '';
		$currentScripts = array_keys($document->_scripts);

		// Execute the display event on the active editor plugin. Add any returned scripts to our script variable
		$args['name'] = 'REPLACE_NAME';
		$args['content'] = '';
		$args['width'] = '100%';
		$args['height'] = 'auto';
		$args['col'] = 15;
		$args['row'] = 5;
		$args['event'] = 'onDisplay';
		$results[] = $this->_editor->update($args);

		// Get the current scripts after executing the onDisplay event of the editor so we can compare them and find what's added
		$updatedInlineScript = isset($document->_script['text/javascript']) ? $document->_script['text/javascript'] : '';
		$updatedScripts = array_keys($document->_scripts);

		// Find the differences
		$addedInlineScript = substr($updatedInlineScript, strlen($currentInlineScript));
		$addedScripts = array_diff($updatedScripts,$currentScripts);

		// Append the inline scripts added by the editor plugin to our JS variable
		$this->js = $updatedInlineScript;

		// Check for any scripts returned directly by the editor plugin
		$html = implode($results);
		$html = trim($html);
		if($html)
		{
			$doc = new DOMDocument();
			$doc->loadHTML($html);
			$scripts = $doc->getElementsByTagName ( 'script' );
			foreach ( $scripts as $key => $script ) {
				$this->js .= $scripts->item ( $key )->nodeValue;
			}
		}

		// We are done. Return editor scripts
		return $this->js;
	}

	public function getContent($editor){
		if($this->editor == 'tinymce'){
			// override default method for Tiny MCE, as default getContent cannot handle more than one editor window.
			$js = "tinyMCE.get('".$editor."').getContent();";
			return $js;
		}
		return parent::getContent($editor);
	}

	public function setContent($editor, $html){
		if($this->editor == 'tinymce'){
			// override default method for Tiny MCE, as default getContent cannot handle more than one editor window.
			$js = "tinyMCE.get('".$editor."').setContent('".$html."');";
			return $js;
		}
		return parent::setContent($editor, $html);
	}

	public function save($editor){
		if($this->editor == 'tinymce'){
			// override default method for Tiny MCE, as default getContent cannot handle more than one editor window.
			$js = "if (tinyMCE.get('".$editor."').isHidden()) {tinyMCE.get('".$editor."').show();};document.getElementById(".$editor.").value = tinyMCE.get('".$editor."').getContent();";
			return $js;
		}
		return parent::save($editor);
	}
}
