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

require_once JPATH_ADMINISTRATOR.'/components/com_k2/resources/items.php';

class JFormFieldK2Items extends JFormField
{
	var $type = 'K2Items';

	public function getInput()
	{
		JHtml::_('jquery.framework');

		// Load required scripts
		$document = JFactory::getDocument();
		$document->addStyleSheet(JURI::root(true).'/administrator/components/com_k2/js/widgets/magnific/magnific-popup.css');
		$document->addScript(JURI::root(true).'/administrator/components/com_k2/js/widgets/magnific/jquery.magnific-popup.min.js');
		$document->addScript(JURI::root(true).'/administrator/components/com_k2/js/widgets/sortable/jquery-sortable-min.js');
		$document->addScript(JURI::root(true).'/media/k2/assets/js/k2.fields.js');

		$this->multiple = (bool)$this->element['k2multiple'];
		$link = JURI::root(true).'/administrator/index.php?option=com_k2&tmpl=component#modal/items';

		if ($this->multiple)
		{
			$title = JText::_('K2_ADD_ITEMS');
			$items = array();
			if ($this->value)
			{
				foreach ($this->value as $itemId)
				{
					$items[] = K2Items::getInstance($itemId);
				}
			}
			$js = "
			function K2SelectRow(row) {
				var itemAlreadyInList = false;
				jQuery('#".$this->id." input').each(function(){
					if(jQuery(this).val() == row.get('id')){
						alert('".JText::_('K2_THE_SELECTED_ITEM_IS_ALREADY_IN_THE_LIST')."');
						itemAlreadyInList = true;
					}
				});
				if(!itemAlreadyInList){
					var li = '<li><button class=\"k2FieldItemsRemove\">".JText::_('K2_REMOVE_ENTRY_FROM_LIST')."</button><span class=\"k2FieldItemsHandle\">' + row.get('title') + '</span><input type=\"hidden\" value=\"' + row.get('id') + '\" name=\"".$this->name."[]\"/></li>';
					jQuery('#".$this->id." .k2FieldItemsMultiple').append(li);
					jQuery('#".$this->id." ul').sortable('refresh');
					alert('".JText::_('K2_ITEM_ADDED_IN_THE_LIST', true)."');
				}
			}
			";
			$document->addScriptDeclaration($js);

			$html = '<div id="'.$this->id.'"><a class="k2Modal btn" title="'.JText::_('K2_ADD_ITEMS').'"  href="'.$link.'"><i class="icon-file"></i>'.JText::_('K2_ADD_ITEMS').'</a>';
			$html .= '<ul class="k2FieldItemsMultiple">';
			foreach ($items as $item)
			{
				$html .= '
				<li>
					<button class="k2FieldItemsRemove">'.JText::_('K2_REMOVE_ENTRY_FROM_LIST').'</button>
					<span class="k2FieldItemsHandle">'.$item->title.'</span>
					<input type="hidden" value="'.$item->id.'" name="'.$this->name.'[]"/>
				</li>
				';
			}

			$html .= '</ul></div>';

		}
		else
		{
			$title = JText::_('K2_SELECT_AN_ITEM');
			if ($this->value)
			{
				$item = K2Items::getInstance($this->value);
				$title = $item->title;
			}

			$js = "
			function K2SelectRow(row) {
				document.getElementById('".$this->name."' + '_id').value = row.get('id');
				document.getElementById('".$this->name."' + '_name').value = row.get('title');
				jQuery.magnificPopup.close();
			}
			";
			$document->addScriptDeclaration($js);

			$html = '<span class="input-append">
            <input type="text" id="'.$this->name.'_name" value="'.htmlspecialchars($title, ENT_QUOTES, 'UTF-8').'" disabled="disabled" />
            <a class="k2Modal btn" title="'.JText::_('K2_SELECT_AN_ITEM').'"  href="'.$link.'"><i class="icon-file"></i>'.JText::_('K2_SELECT').'</a>
            <input type="hidden" class="required modal-value" id="'.$this->name.'_id" name="'.$this->name.'" value="'.( int )$this->value.'" />
            </span>';

		}

		return $html;
	}

}
