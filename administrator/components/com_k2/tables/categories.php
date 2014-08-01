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

require_once JPATH_ADMINISTRATOR.'/components/com_k2/tables/nested.php';

class K2TableCategories extends K2TableNested
{
	public function __construct($db)
	{
		parent::__construct('#__k2_categories', 'id', $db);
	}

	/**
	 * Method to compute the default name of the asset.
	 * The default name is in the form table_name.id
	 * where id is the value of the primary key of the table.
	 *
	 * @return  string
	 *
	 * @since   11.1
	 */

	protected function _getAssetName()
	{
		return 'com_k2.category.'.(int)$this->id;
	}

	/**
	 * Method to return the title to use for the asset table.
	 *
	 * @return  string
	 *
	 * @since   11.1
	 */
	protected function _getAssetTitle()
	{
		return $this->title;
	}

	/**
	 * Get the parent asset id for the record
	 *
	 * @param   JTable   $table  A JTable object for the asset parent.
	 * @param   integer  $id     The id for the asset
	 *
	 * @return  integer  The id of the asset's parent
	 *
	 * @since   11.1
	 */
	protected function _getAssetParentId(JTable $table = null, $id = null)
	{
		$assetId = null;

		// This is a category under a category.
		if ($this->parent_id > 1)
		{
			// Build the query to get the asset id for the parent category.
			$query = $this->_db->getQuery(true)->select($this->_db->quoteName('asset_id'))->from($this->_db->quoteName('#__k2_categories'))->where($this->_db->quoteName('id').' = '.$this->parent_id);

			// Get the asset id from the database.
			$this->_db->setQuery($query);
			if ($result = $this->_db->loadResult())
			{
				$assetId = (int)$result;
			}
		}
		// This is a category that needs to parent with the extension.
		elseif ($assetId === null)
		{
			// Build the query to get the asset id for the parent category.
			$query = $this->_db->getQuery(true)->select($this->_db->quoteName('id'))->from($this->_db->quoteName('#__assets'))->where($this->_db->quoteName('name').' = '.$this->_db->quote('com_k2'));

			// Get the asset id from the database.
			$this->_db->setQuery($query);
			if ($result = $this->_db->loadResult())
			{
				$assetId = (int)$result;
			}
		}
		// Return the asset id.
		if ($assetId)
		{
			return $assetId;
		}
		else
		{
			return parent::_getAssetParentId($table, $id);
		}
	}

	public function check()
	{
		if (JString::trim($this->title) == '')
		{
			$this->setError(JText::_('K2_CATEGORY_MUST_HAVE_A_TITLE'));
			return false;
		}

		if (JString::trim($this->alias) == '')
		{
			$autoAlias = true;
			$this->alias = $this->title;
		}
		else
		{
			$autoAlias = false;
		}
		
		if(JFactory::getApplication()->input->get('task') == 'run')
		{
			$autoAlias = true;
		}

		if (!$this->parent_id)
		{
			$this->parent_id = 1;
		}

		if (JFactory::getConfig()->get('unicodeslugs') == 1)
		{
			$this->alias = JFilterOutput::stringURLUnicodeSlug($this->alias);
		}
		else
		{
			require_once JPATH_ADMINISTRATOR.'/components/com_k2/helpers/language.php';
			$this->alias = K2HelperLanguage::transliterate($this->alias, $this->language);
		}

		$db = $this->getDbo();
		$query = $db->getQuery(true);
		$query->select($db->quoteName('id'))->from($db->quoteName('#__k2_categories'))->where($db->quoteName('alias').' = '.$db->quote($this->alias));
		if ($this->id)
		{
			$query->where($db->quoteName('id').' != '.(int)$this->id);
		}
		$db->setQuery($query);
		if ($db->loadResult())
		{
			if ($autoAlias)
			{
				$this->alias .= '-'.uniqid();
			}
			else
			{
				$this->setError(JText::_('K2_DUPLICATE_ALIAS'));
				return false;
			}
		}

		return true;
	}

	/**
	 * Method to bind an associative array or object to the JTable instance.This
	 * method only binds properties that are publicly accessible and optionally
	 * takes an array of properties to ignore when binding.
	 *
	 * @param   mixed  $src     An associative array or object to bind to the JTable instance.
	 * @param   mixed  $ignore  An optional array or space separated list of properties to ignore while binding.
	 *
	 * @return  boolean  True on success.
	 *
	 * @link    http://docs.joomla.org/JTable/bind
	 * @since   11.1
	 * @throws  InvalidArgumentException
	 */
	public function bind($src, $ignore = array())
	{
		if (is_object($src))
		{
			$src = get_object_vars($src);
		}
		if (isset($src['metadata']) && is_array($src['metadata']))
		{
			$registry = new JRegistry;
			$registry->loadArray($src['metadata']);
			$src['metadata'] = $registry->toString();
		}
		if (isset($src['params']) && is_array($src['params']))
		{
			$registry = new JRegistry;
			$registry->loadArray($src['params']);
			$src['params'] = $registry->toString();
		}
		if (isset($src['plugins']) && is_array($src['plugins']))
		{
			$registry = new JRegistry;
			$registry->loadArray($src['plugins']);
			$src['plugins'] = $registry->toString();
		}
		if (isset($src['rules']) && is_array($src['rules']))
		{
			$rules = array();
			foreach ((array) $src['rules'] as $action => $ids)
			{
				$rules[$action] = array();
				foreach ($ids as $id => $p)
				{
					if ($p !== '')
					{
						$rules[$action][$id] = ($p == '1' || $p == 'true') ? true : false;
					}
				}
			}
			$this->setRules(new JAccessRules($rules));
		}
		return parent::bind($src, $ignore);
	}

}
