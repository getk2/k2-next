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

class K2TableTags extends K2Table
{
	public function __construct($db)
	{
		parent::__construct('#__k2_tags', 'id', $db);
	}

	public function check()
	{
		if (JString::trim($this->name) == '')
		{
			$this->setError(JText::_('K2_TAG_MUST_HAVE_A_NAME'));
			return false;
		}

		$this->alias = $this->name;

		if (JFactory::getConfig()->get('unicodeslugs') == 1)
		{
			$this->alias = JFilterOutput::stringURLUnicodeSlug($this->alias);
		}
		else
		{
			$this->alias = JFilterOutput::stringURLSafe($this->alias);
		}

		$db = $this->getDbo();
		$query = $db->getQuery(true);
		$query->select($db->quoteName('id'))->from($db->quoteName('#__k2_tags'))->where($db->quoteName('alias').' = '.$db->quote($this->alias));
		if ($this->id)
		{
			$query->where($db->quoteName('id').' != '.(int)$this->id);
		}
		$db->setQuery($query);
		if ($db->loadResult())
		{
			$this->setError(JText::_('K2_DUPLICATE_ALIAS'));
			return false;
		}

		return true;
	}

}
