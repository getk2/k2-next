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

/**
 * K2Model is the base K2 model class.
 * Methods here are inherited to all K2 models.
 */

class K2Model extends JModelLegacy
{

	/**
	 * Add a directory where JModelLegacy should search for models.
	 * You may either pass a string or an array of directories.
	 * We need to override this since K2Model::getInstance will always refer to the parent.
	 *
	 * @param   mixed   $path    A path or array[sting] of paths to search.
	 * @param   string  $prefix  A prefix for models.
	 *
	 * @return  array  An array with directory elements. If prefix is equal to '', all directories are returned.
	 *
	 */
	public static function addIncludePath($path = '', $prefix = 'K2Model')
	{
		return parent::addIncludePath($path, $prefix);
	}

	/**
	 * Shortcut method for retrieving a single row from the model using the list query.
	 *
	 * @return object	The row object.
	 */

	public function getRow()
	{
		if (method_exists($this, 'getRows') && $this->getState('id'))
		{
			$rows = $this->getRows();
			if (isset($rows[0]))
			{
				$row = (object)$rows[0];
			}
		}
		else
		{
			$data = array($this->getTable());
			$rows = $this->getResources($data, $this->getName());
			$row = (object)$rows[0];
		}
		return $row;
	}

	/**
	 * Save method.
	 *
	 * @return boolean	True on success false on failure.
	 */

	public function save()
	{
		$table = $this->getTable();
		$data = $this->getState('data');
		if (isset($data['id']) && $data['id'])
		{
			if (!$table->load($data['id']))
			{
				$this->setError($table->getError());
				return false;
			}
			if ($table->isCheckedOut(JFactory::getUser()->get('id')))
			{
				$this->setError(JText::_('K2_ROW_IS_CURRENTLY_BEING_EDITED_BY_ANOTHER_AUTHOR'));
				return false;
			}

		}
		$this->onBeforeSave($data, $table);
		if (!$table->save($data))
		{
			$this->setError($table->getError());
			return false;
		}
		$this->setState('id', $table->id);
		$this->onAfterSave($data, $table);
		return true;
	}

	/**
	 * onBeforeSave method. Hook for chidlren model to prepare the data.
	 * @param   array  $data     The data to be saved.
	 *
	 * @return void
	 */

	protected function onBeforeSave(&$data, $table)
	{
	}

	/**
	 * onAfterSave method. Hook for chidlren model to save extra data.
	 *
	 * @return void
	 */

	protected function onAfterSave(&$data, $table)
	{
	}

	/**
	 * Delete method.
	 *
	 * @return boolean	True on success false on failure.
	 */

	public function delete()
	{
		$table = $this->getTable();
		$id = $this->getState('id');
		if (!$table->load($id))
		{
			$this->setError($table->getError());
			return false;
		}
		$this->onBeforeDelete($table);
		if (!$table->delete())
		{
			$this->setError($table->getError());
			return false;
		}
		$this->onAfterDelete($table);
		return true;
	}

	/**
	 * onBeforeDelete method. 		Hook for chidlren model.
	 *
	 * @param   JTable  $table     	The table object.
	 *
	 * @return void
	 */

	protected function onBeforeDelete($table)
	{
	}

	/**
	 * onAfterDelete method. Hook for chidlren model.
	 *
	 * @param   JTable  $table     	The table object.
	 *
	 * @return void
	 */

	protected function onAfterDelete($table)
	{
	}

	/**
	 * Method to get a table object, load it if necessary.
	 *
	 * @param   string  $name     The table name. Optional.
	 * @param   string  $prefix   The class prefix. Optional.
	 * @param   array   $options  Configuration array for model. Optional.
	 *
	 * @return  JTable  A JTable object
	 */
	public function getTable($name = '', $prefix = 'K2Table', $options = array())
	{
		return parent::getTable($name, $prefix, $options);
	}

	/**
	 * Proxy function which triggers the onBeforeSetQuery plugin event.
	 *
	 * @param   JDatabaseQuery $query	The current query object.
	 * @param   string $context			The context of the query passed to plugins.
	 *
	 * @return void
	 */

	protected function onBeforeSetQuery(&$query, $context)
	{
		$dispatcher = JDispatcher::getInstance();
		JPluginHelper::importPlugin('k2');
		$dispatcher->trigger('onBeforeSetQuery', array(
			&$query,
			$context
		));
	}

	/**
	 * Converts the raw data from the database to K2 resources.
	 *
	 * @param   array $data			Array which contains the raw data from the database.
	 * @param   string $type		The resource type that will be generated.
	 *
	 * @return array The array of the generated resources.
	 */

	protected function getResources($data)
	{
		jimport('joomla.filesystem.file');
		if (JFile::exists(JPATH_ADMINISTRATOR.'/components/com_k2/resources/'.$this->getName().'.php'))
		{
			require_once JPATH_ADMINISTRATOR.'/components/com_k2/resources/'.$this->getName().'.php';
			$rows = array();
			foreach ($data as $entry)
			{
				$className = 'K2'.ucfirst($this->getName());
				$row = new $className($entry);
				$rows[] = $row;
			}
			return $rows;
		}
		else
		{
			return $data;
		}
	}

	/**
	 * Method to checkin a row.
	 *
	 * @param   integer  $id  The id of the row.
	 *
	 * @return  boolean  False on failure or error, true otherwise.
	 *
	 */
	public function checkin($id)
	{
		// Get table
		$table = $this->getTable();

		// Check if row supports check in
		if (!property_exists($table, 'checked_out') || !property_exists($table, 'checked_out_time'))
		{
			return true;
		}

		// Get user
		$user = JFactory::getUser();

		// Load row
		if (!$table->load($id))
		{
			$this->setError($table->getError());
			return false;
		}

		// Check if this is the user having previously checked out the row
		if ($table->checked_out > 0 && $table->checked_out != $user->get('id') && !$user->authorise('core.admin', 'com_checkin'))
		{
			$this->setError(JText::_('JLIB_APPLICATION_ERROR_CHECKIN_USER_MISMATCH'));
			return false;
		}

		// Attempt to check the row in
		if (!$table->checkin($id))
		{
			$this->setError($table->getError());
			return false;
		}

		return true;
	}

	/**
	 * Method to check-out a row for editing.
	 *
	 * @param   integer  $id  The id of the row.
	 *
	 * @return  boolean  False on failure or error, true otherwise.
	 *
	 */
	public function checkout($id)
	{

		// Get table
		$table = $this->getTable();

		// Check if row supports check in
		if (!property_exists($table, 'checked_out') || !property_exists($table, 'checked_out_time'))
		{
			return true;
		}

		if (!$table->load($id))
		{
			$this->setError($table->getError());
			return false;
		}

		// Get user
		$user = JFactory::getUser();

		// Check if this is the user having previously checked out the row
		if ($table->checked_out > 0 && $table->checked_out != $user->get('id'))
		{
			$this->setError(JText::_('JLIB_APPLICATION_ERROR_CHECKOUT_USER_MISMATCH'));
			return false;
		}

		// Attempt to check the row out
		if (!$table->checkout($user->get('id'), $id))
		{
			$this->setError($table->getError());
			return false;
		}

		return true;
	}

}
