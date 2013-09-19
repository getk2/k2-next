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
	 * Method to get the record form.
	 *
	 * @param   array  $data		An optional array of data for the form to interogate.
	 * @param   boolean	$loadData	True if the form is to load its own data (default case), false if not.
	 * 
	 * @return  JForm	A JForm object on success, false on failure
	 */
	public function getForm($data = array(), $loadData = true)
	{
		return false;
		// Get the form.
		$form = $this->loadForm('com_weblinks.weblink', 'weblink', array('control' => 'jform', 'load_data' => $loadData));
		if (empty($form))
		{
			return false;
		}

		// Determine correct permissions to check.
		if ($this->getState('weblink.id'))
		{
			// Existing record. Can only edit in selected categories.
			$form->setFieldAttribute('catid', 'action', 'core.edit');
		}
		else
		{
			// New record. Can only create in selected categories.
			$form->setFieldAttribute('catid', 'action', 'core.create');
		}

		// Modify the form based on access controls.
		if (!$this->canEditState((object) $data))
		{
			// Disable fields for display.
			$form->setFieldAttribute('ordering', 'disabled', 'true');
			$form->setFieldAttribute('state', 'disabled', 'true');
			$form->setFieldAttribute('publish_up', 'disabled', 'true');
			$form->setFieldAttribute('publish_down', 'disabled', 'true');

			// Disable fields while saving.
			// The controller has already verified this is a record you can edit.
			$form->setFieldAttribute('ordering', 'filter', 'unset');
			$form->setFieldAttribute('state', 'filter', 'unset');
			$form->setFieldAttribute('publish_up', 'filter', 'unset');
			$form->setFieldAttribute('publish_down', 'filter', 'unset');
		}

		return $form;
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
	
	public function save()
	{
		$table = $this->getTable();
		$data = $this->getState('data');
		if (!$table->save($data))
		{
			$this->setError($table->getError());
			return false;
		}
		$this->setState('id', $table->id);
		return $table;
	}
	
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

}
