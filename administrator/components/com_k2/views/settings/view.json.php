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

require_once JPATH_ADMINISTRATOR.'/components/com_k2/views/view.php';

/**
 * Attachments JSON view.
 */

class K2ViewSettings extends K2View
{

	public function edit($id)
	{
		// Set title
		$this->setTitle('K2_SETTINGS');

		// Set row
		$this->setRow(null);

		// Set form
		$this->setForm();

		// Set menu
		$this->setMenu('edit');

		// Set Actions
		$this->setFormActions();

		// Render
		$this->render();
	}

	/**
	 * Helper method for fetching a single row and pass it to K2 response.
	 * This is triggered by the edit function.
	 * Usually there will be no need to override this function.
	 *
	 * @param   integer  $id  The id of the row to edit.
	 *
	 * @return void
	 */
	protected function setRow($id)
	{
		$component = JComponentHelper::getComponent('com_k2');
		$row = new stdClass;
		$row->id = $component->id;
		K2Response::setRow($row);
	}

	/**
	 * Loads the XML form and pass the fields to the response.
	 * Children may need to override this method.
	 *
	 * @return void
	 */
	protected function setForm()
	{
		$component = JComponentHelper::getComponent('com_k2');
		$extension = JTable::getInstance('extension');
		$extension->load($component->id);
		JForm::addFormPath(JPATH_ADMINISTRATOR.'/components/com_k2');
		$form = JForm::getInstance('com_k2.settings', 'config', array('control' => 'jform'), false, '/config');
		$values = new JRegistry($extension->params);
		$form->bind($values);
		$_form = new stdClass;
		foreach ($form->getFieldsets() as $fieldset)
		{
			$array = array();
			foreach ($form->getFieldset($fieldset->name) as $field)
			{
				$tmp = new stdClass;
				$tmp->label = $field->label;
				$tmp->input = $field->input;
				$array[$field->name] = $tmp;
			}
			$name = $fieldset->name;
			$_form->$name = $array;
		}
		K2Response::setForm($_form);
	}

	/**
	 * Hook for children views to allow them set the menu for the edit requests.
	 * Children views usually will not need to override this method.
	 *
	 * @return void
	 */
	protected function setFormActions()
	{
		K2Response::addAction('save', 'K2_SAVE', array('data-action' => 'save', 'data-resource' => $this->getName()));
		K2Response::addAction('saveAndClose', 'K2_SAVE_AND_CLOSE', array('data-action' => 'save-and-close'));
		K2Response::addAction('close', 'K2_CLOSE', array('data-action' => 'close'));
	}

}
