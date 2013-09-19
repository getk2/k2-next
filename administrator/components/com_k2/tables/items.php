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

require_once JPATH_ADMINISTRATOR.'/components/com_k2/tables/table.php';

class K2TableItems extends K2Table
{
	public function __construct($db)
	{
		parent::__construct('#__k2_items', 'id', $db);
	}

	public function check()
	{
		if (JString::trim($this->title) == '')
		{
			$this->setError(JText::_('K2_ITEM_MUST_HAVE_A_TITLE'));
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
