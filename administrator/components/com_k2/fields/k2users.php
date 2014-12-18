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

jimport('joomla.form.formfield');

require_once JPATH_ADMINISTRATOR.'/components/com_k2/resources/users.php';

class JFormFieldK2Users extends JFormField
{
	var $type = 'K2Users';

	public function getInput()
	{
		JHtml::_('jquery.framework');

		// Load required scripts
		$document = JFactory::getDocument();
		$document->addStyleSheet(JURI::root(true).'/media/k2app/vendor/magnific/magnific-popup.css?v=3.0.0');
		$document->addScript(JURI::root(true).'/media/k2app/vendor/magnific/jquery.magnific-popup.min.js?v=3.0.0');
		$document->addScript(JURI::root(true).'/media/k2app/vendor/sortable/jquery-sortable-min.js?v=3.0.0');
		$document->addScript(JURI::root(true).'/media/k2app/assets/js/fields.js?v=3.0.0');

		$this->multiple = (bool)$this->element['k2multiple'];
		$link = JURI::root(true).'/administrator/index.php?option=com_k2&tmpl=component#modal/users';

		if ($this->multiple)
		{
			$title = JText::_('K2_ADD_USERS');
			$users = array();
			if ($this->value)
			{
				foreach ($this->value as $userId)
				{
					$users[] = K2Users::getInstance($userId);
				}
			}
			$js = "
			function K2SelectRow(row) {
				var userAlreadyInList = false;
				jQuery('#".$this->id." input').each(function(){
					if(jQuery(this).val() == row.get('id')){
						alert('".JText::_('K2_THE_SELECTED_USER_IS_ALREADY_IN_THE_LIST')."');
						userAlreadyInList = true;
					}
				});
				if(!userAlreadyInList){
					var li = '<li><a class=\"k2FieldResourceRemove\">".JText::_('K2_REMOVE_ENTRY_FROM_LIST')."</a><span class=\"k2FieldResourceMultipleHandle\">' + row.get('name') + '</span><input type=\"hidden\" value=\"' + row.get('id') + '\" name=\"".$this->name."[]\"/></li>';
					jQuery('#".$this->id." .k2FieldUsersMultiple').append(li);
					jQuery('#".$this->id." ul').sortable('refresh');
					alert('".JText::_('K2_USER_ADDED_IN_THE_LIST', true)."');
				}
			}
			";
			$document->addScriptDeclaration($js);

			$html = '<div id="'.$this->id.'"><a class="k2Modal btn" title="'.JText::_('K2_ADD_USERS').'"  href="'.$link.'"><i class="icon-list"></i>'.JText::_('K2_ADD_USERS').'</a>';
			$html .= '<ul class="k2FieldResourceMultiple k2FieldUsersMultiple">';
			foreach ($users as $user)
			{
				$html .= '
				<li>
					<a class="k2FieldResourceRemove">'.JText::_('K2_REMOVE_ENTRY_FROM_LIST').'</a>
					<span class="k2FieldResourceMultipleHandle">'.$user->name.'</span>
					<input type="hidden" value="'.$user->id.'" name="'.$this->name.'[]"/>
				</li>
				';
			}

			$html .= '</ul></div>';

		}
		else
		{
			$title = JText::_('K2_SELECT_A_USER');
			if ($this->value)
			{
				$user = K2Users::getInstance($this->value);
				$title = $user->name;
			}

			$js = "
			function K2SelectRow(row) {
				document.getElementById('".$this->name."' + '_id').value = row.get('id');
				document.getElementById('".$this->name."' + '_name').value = row.get('name');
				jQuery.magnificPopup.close();
			}
			";
			$document->addScriptDeclaration($js);

			$html = '<span class="input-append">
            <input type="text" id="'.$this->name.'_name" value="'.htmlspecialchars($title, ENT_QUOTES, 'UTF-8').'" disabled="disabled" />
            <a class="k2Modal btn" title="'.JText::_('K2_SELECT_A_CATEGORY').'"  href="'.$link.'"><i class="icon-list"></i>'.JText::_('K2_SELECT').'</a>
            <input type="hidden" class="required modal-value" id="'.$this->name.'_id" name="'.$this->name.'" value="'.( int )$this->value.'" />
            </span>';

		}

		return $html;
	}

}
