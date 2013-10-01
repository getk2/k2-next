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

class K2Table extends JTable
{

	/**
	 * Method to store a row in the database from the JTable instance properties.
	 * If a primary key value is set the row with that primary key value will be
	 * updated with the instance property values.  If no primary key value is set
	 * a new row will be inserted into the database with the properties from the
	 * JTable instance.
	 *
	 * @param   boolean  $updateNulls  True to update fields even if they are null.
	 *
	 * @return  boolean  True on success.
	 *
	 * @link    http://docs.joomla.org/JTable/store
	 * @since   11.1
	 */
	public function store($updateNulls = false)
	{
		$user = JFactory::getUser();
		$date = JFactory::getDate();
		if (!$this->id && property_exists($this, 'created'))
		{
			$this->created = $date->toSql();
		}
		if (property_exists($this, 'created_by'))
		{
			$this->created_by = $user->get('id');
		}
		if ($this->id && property_exists($this, 'modified'))
		{
			$this->modified = $date->toSql();
		}
		if ($this->id && property_exists($this, 'modified_by'))
		{
			$this->modified_by = $user->get('id');
		}
		if (property_exists($this, 'language') && !$this->language)
		{
			$this->language = '*';
		}
		return parent::store($updateNulls);
	}

}
