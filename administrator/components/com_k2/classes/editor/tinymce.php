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

class K2EditorTinymce extends K2Editor
{
	public function init() {
		// Launch editors 
		$enableEditor = "jQuery('#k2-items-form').find('.editor textarea').each(function(){
				if (tinyMCE.get(jQuery(this).attr('id')) == null) {tinyMCE.execCommand('mceAddEditor',0,jQuery(this).attr('id'))}});";
		// Return editor scripts
		return $enableEditor.$this->js;
	}

	public function getContent($editor){
		return  "tinyMCE.get('".$editor."').getContent();";
	}

	public function setContent($editor, $html){
		// override default method for Tiny MCE, as default getContent cannot handle more than one editor window.
		return "if (tinyMCE.get('".$editor."') == null) {tinyMCE.execCommand('mceAddEditor',0,'".$editor."');} tinyMCE.get('".$editor."').setContent('".$html."');";
	}

	public function save($editor){
		// override default method for Tiny MCE, as default getContent cannot handle more than one editor window.
		return "if (tinyMCE.get('".$editor."') == null) {tinyMCE.execCommand('mceAddEditor',0,'".$editor."');} document.getElementById(".$editor.").value = tinyMCE.get('".$editor."').getContent();";
	}
}
