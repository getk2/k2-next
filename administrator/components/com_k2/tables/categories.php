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
	
	public function check()
	{
		if (JString::trim($this->title) == '')
		{
			$this->setError(JText::_('K2_CATEGORY_MUST_HAVE_A_TITLE'));
			return false;
		}

		if (JString::trim($this->alias) == '')
		{
			$this->alias = $this->title;
		}

		if (JFactory::getConfig()->get('unicodeslugs') == 1)
		{
			$this->alias = JFilterOutput::stringURLUnicodeSlug($this->alias);
		}
		else
		{
			$this->alias = JFilterOutput::stringURLSafe($this->alias);
		}

		return true;
	}
}
