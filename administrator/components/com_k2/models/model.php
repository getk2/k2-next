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
	public static function addIncludePath($path = '', $prefix = '')
	{
		return parent::addIncludePath($path, $prefix);
	}

	/**
	 * Shortcut method for retrieving a single row from the model using the list query.
	 *
	 * @return mixed	The row object or null.
	 */

	public function getRow()
	{
		$row = null;
		if (method_exists($this, 'getRows') && $this->getState('id'))
		{
			$rows = $this->getRows();
			if (isset($rows[0]))
			{
				$row = (object)$rows[0];
			}
		}
		return $row;
	}

	/**
	 * Save method.
	 *
	 * @param   boolean   $patch	Flag to indicate if we are patching or performing a normal save.
	 *
	 * @return boolean	True on success false on failure.
	 */

	public function save($patch = false)
	{
		$table = $this->getTable();
		$data = $this->getState('data');
		if ($patch)
		{
			$table->load($data['id']);
		}
		if (!$table->save($data))
		{
			$this->setError($table->getError());
			return false;
		}
		$this->setState('id', $table->id);
		return true;
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
		if (!$table->delete($id))
		{
			$this->setError($table->getError());
			return false;
		}
		return true;
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

	protected function getResources($data, $type)
	{
		require_once JPATH_ADMINISTRATOR.'/components/com_k2/resources/'.$type.'.php';
		$rows = array();
		foreach ($data as $entry)
		{
			$className = 'K2'.ucfirst($type);
			$row = new $className($entry);
			$rows[] = $row;
		}
		return $rows;
	}

	/**
	 * Method to checkin a row.
	 *
	 * @param   integer  $pk  The numeric id of the primary key.
	 *
	 * @return  boolean  False on failure or error, true otherwise.
	 *
	 */
	public function checkin($pk = null)
	{
		// Only attempt to check the row in if it exists.
		if ($pk)
		{
			$user = JFactory::getUser();

			// Get an instance of the row to checkin.
			$table = $this->getTable();

			if (!$table->load($pk))
			{
				$this->setError($table->getError());
				return false;
			}

			// Check if this is the user having previously checked out the row.
			if ($table->checked_out > 0 && $table->checked_out != $user->get('id') && !$user->authorise('core.admin', 'com_checkin'))
			{
				$this->setError(JText::_('JLIB_APPLICATION_ERROR_CHECKIN_USER_MISMATCH'));
				return false;
			}

			// Attempt to check the row in.
			if (!$table->checkin($pk))
			{
				$this->setError($table->getError());
				return false;
			}
		}

		return true;
	}

	/**
	 * Method to check-out a row for editing.
	 *
	 * @param   integer  $pk  The numeric id of the primary key.
	 *
	 * @return  boolean  False on failure or error, true otherwise.
	 *
	 */
	public function checkout($pk = null)
	{
		// Only attempt to check the row in if it exists.
		if ($pk)
		{
			// Get an instance of the row to checkout.
			$table = $this->getTable();

			if (!$table->load($pk))
			{
				$this->setError($table->getError());
				return false;
			}

			// If there is no checked_out or checked_out_time field, just return true.
			if (!property_exists($table, 'checked_out') || !property_exists($table, 'checked_out_time'))
			{
				return true;
			}

			$user = JFactory::getUser();

			// Check if this is the user having previously checked out the row.
			if ($table->checked_out > 0 && $table->checked_out != $user->get('id'))
			{
				$this->setError(JText::_('JLIB_APPLICATION_ERROR_CHECKOUT_USER_MISMATCH'));
				return false;
			}

			// Attempt to check the row out.
			if (!$table->checkout($user->get('id'), $pk))
			{
				$this->setError($table->getError());
				return false;
			}
		}

		return true;
	}

}
