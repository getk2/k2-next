<?php
/**
 * @version		3.0.0b
 * @package		K2
 * @author		JoomlaWorks http://www.joomlaworks.net
 * @copyright	Copyright (c) 2006 - 2014 JoomlaWorks Ltd. All rights reserved.
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
	 * Returns a Model object, always creating it
	 *
	 * @param   string  $type    The model type to instantiate
	 * @param   string  $prefix  Prefix for the model class name. Optional.
	 * @param   array   $config  Configuration array for model. Optional.
	 *
	 * @return  mixed   A model object or false on failure
	 *
	 * @since   11.1
	 */
	public static function getInstance($type, $prefix = 'K2Model', $config = array())
	{
		return parent::getInstance($type, $prefix, $config);
	}

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
		require_once JPATH_ADMINISTRATOR.'/components/com_k2/tables/table.php';
		K2Table::addIncludePath(JPATH_ADMINISTRATOR.'/components/com_k2/tables');
		return parent::getTable($name, $prefix, $options);
	}

	/**
	 * Shortcut method for retrieving a single row from the model using the list query.
	 *
	 * @return object	The row object.
	 */

	public function getRow()
	{
		$row = null;
		if (method_exists($this, 'getRows'))
		{
			if ($this->getState('id') || $this->getState('alias'))
			{
				$rows = $this->getRows();
				if (isset($rows[0]))
				{
					$row = (object)$rows[0];
				}
			}
		}
		if (is_null($row))
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
		// Get dispatcher
		$dispatcher = JDispatcher::getInstance();

		// Import content plugins
		JPluginHelper::importPlugin('content');

		// Get table
		$table = $this->getTable();

		// Get data
		$data = $this->getState('data');

		// Is new
		$isNew = true;

		// If we have an id we are editting
		if (isset($data['id']) && $data['id'])
		{
			$isNew = false;
			if (!$table->load($data['id']))
			{
				$this->setError($table->getError());
				return false;
			}
			if ($this->getState('patch') && isset($data['checked_out']) && $data['checked_out'] == 0)
			{
				return $this->checkin($data['id']);
			}
			if ($table->isCheckedOut(JFactory::getUser()->get('id')))
			{
				$this->setError(JText::_('K2_ROW_IS_CURRENTLY_BEING_EDITED_BY_ANOTHER_AUTHOR'));
				return false;
			}

		}

		// Before save hook for children models
		if (!$this->onBeforeSave($data, $table))
		{
			return false;
		}

		// Trigger onContentBeforeSave event if this request is not a patch
		if (!$this->getState('patch'))
		{
			$dispatcher->trigger('onContentBeforeSave', array(
				'com_k2.'.$this->getName(),
				&$table,
				$isNew
			));
		}

		// Save
		if (!$table->save($data))
		{
			$this->setError($table->getError());
			return false;
		}

		// Set the id to the state
		$this->setState('id', $table->id);

		// After save hook for children models
		if (!$this->onAfterSave($data, $table))
		{
			return false;
		}

		// Trigger onContentAfterSave event if this request is not a patch
		if (!$this->getState('patch'))
		{
			$dispatcher->trigger('onContentAfterSave', array(
				'com_k2.'.$this->getName(),
				&$table,
				$isNew
			));
		}

		return true;
	}

	/**
	 * onBeforeSave method. Hook for chidlren model to prepare the data.
	 *
	 * @param   array  $data     The data to be saved.
	 * @param   JTable  $table   The table object.
	 *
	 * @return boolean
	 */

	protected function onBeforeSave(&$data, $table)
	{
		return true;
	}

	/**
	 * onAfterSave method. Hook for chidlren model to save extra data.
	 *
	 * @param   array  $data     The data passed to the save function.
	 * @param   JTable  $table   The table object.
	 *
	 * @return boolean
	 */

	protected function onAfterSave(&$data, $table)
	{
		return true;
	}

	/**
	 * Close method.
	 *
	 * @return boolean	True on success false on failure.
	 */

	public function close()
	{
		return true;
	}

	/**
	 * Delete method.
	 *
	 * @return boolean	True on success false on failure.
	 */

	public function delete()
	{
		// Get dispatcher
		$dispatcher = JDispatcher::getInstance();

		// Import content plugins
		JPluginHelper::importPlugin('content');

		// Get table
		$table = $this->getTable();

		// Get id
		$id = $this->getState('id');

		// Load record
		if (!$table->load($id))
		{
			// Record probably has already been deleted, so return true
			return true;
		}

		// Before delete hook for children models
		if (!$this->onBeforeDelete($table))
		{
			return false;
		}

		// Trigger onContentBeforeDelete event
		$dispatcher->trigger('onContentBeforeDelete', array(
			'com_k2.'.$this->getName(),
			&$table
		));

		// Delete
		if (!$table->delete())
		{
			$this->setError($table->getError());
			return false;
		}

		// After delete hook for children models
		if (!$this->onAfterDelete($table))
		{
			return false;
		}

		// Trigger onContentAfterDelete event
		$dispatcher->trigger('onContentAfterDelete', array(
			'com_k2.'.$this->getName(),
			&$table
		));

		return true;
	}

	/**
	 * onBeforeDelete method. 		Hook for chidlren model.
	 *
	 * @param   JTable  $table     	The table object.
	 *
	 * @return boolean
	 */

	protected function onBeforeDelete($table)
	{
		return true;
	}

	/**
	 * onAfterDelete method. Hook for chidlren model.
	 *
	 * @param   JTable  $table     	The table object.
	 *
	 * @return boolean
	 */

	protected function onAfterDelete($table)
	{
		return true;
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
			$context,
			&$query
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
				$row = $className::get($entry);
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
		if ($table->checked_out > 0 && $table->checked_out != $user->get('id') && !$user->authorise('core.manage', 'com_checkin'))
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
