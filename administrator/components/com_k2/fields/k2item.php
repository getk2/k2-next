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

jimport('joomla.form.formfield');

class JFormFieldK2Item extends JFormField
{
	var $type = 'K2Item';

	public function getInput()
	{
		if ($this->value)
		{
			require_once JPATH_ADMINISTRATOR.'/components/com_k2/resources/items.php';
			$item = K2Items::getInstance($this->value);
			$title = $item->title;
		}
		else
		{
			$title = JText::_('K2_SELECT_AN_ITEM');
		}

		$js = "
		function jSelectItem(id, title, object) {
			document.getElementById('".$this->name."' + '_id').value = id;
			document.getElementById('".$this->name."' + '_name').value = title;
			if(typeof(window.parent.SqueezeBox.close=='function')){
				window.parent.SqueezeBox.close();
			}
			else {
				document.getElementById('sbox-window').close();
			}
		}
		";
		$document = JFactory::getDocument();
		$document->addScriptDeclaration($js);
		$link = 'index.php?option=com_k2&tmpl=component#items';
		JHtml::_('behavior.modal', 'a.k2Modal');

		$html = '<span class="input-append">
            <input type="text" id="'.$this->name.'_name" value="'.htmlspecialchars($title, ENT_QUOTES, 'UTF-8').'" disabled="disabled" />
            <a class="k2Modal btn" title="'.JText::_('K2_SELECT_AN_ITEM').'"  href="'.$link.'" rel="{handler: \'iframe\', size: {x: 700, y: 450}}"><i class="icon-file"></i>'.JText::_('K2_SELECT').'</a>
            <input type="hidden" class="required modal-value" id="'.$this->name.'_id" name="'.$this->name.'" value="'.( int )$this->value.'" />
            </span>';

		return $html;
	}

}
