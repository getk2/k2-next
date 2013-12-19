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

require_once JPATH_ADMINISTRATOR.'/components/com_k2/resources/resource.php';
require_once JPATH_ADMINISTRATOR.'/components/com_k2/classes/filesystem.php';

/**
 * K2 attachment resource class.
 */

class K2Attachments extends K2Resource
{

	/**
	 * @var array	Items instances container.
	 */
	protected static $instances = array();

	/**
	 * Constructor.
	 *
	 * @param object $data
	 *
	 * @return void
	 */

	public function __construct($data)
	{
		parent::__construct($data);
		self::$instances[$this->id] = $this;
	}

	/**
	 * Gets an item instance.
	 *
	 * @param integer $id	The id of the item to get.
	 *
	 * @return K2Attachment The attachment object.
	 */
	public static function getInstance($id)
	{
		if (empty(self::$instances[$id]))
		{
			K2Model::addIncludePath(JPATH_ADMINISTRATOR.'/components/com_k2/models');
			$model = K2Model::getInstance('Attachments', 'K2Model');
			$model->setState('id', $id);
			$item = $model->getRow();
			self::$instances[$id] = $item;
		}
		return self::$instances[$id];
	}

	/**
	 * Prepares the row for output
	 *
	 * @param string $mode	The mode for preparing data. 'site' for fron-end data, 'admin' for administrator operations.
	 *
	 * @return void
	 */
	public function prepare($mode = null)
	{
		// Prepare generic properties like dates and authors
		parent::prepare($mode);

		// Prepare specific properties
		$this->link = $this->getLink();

		// Prepare specific properties
		$this->url = $this->getUrl();

	}

	public function getLink()
	{
		$application = JFactory::getApplication();
		$hash = JApplication::getHash($this->id);
		$link = JRoute::_(JURI::root().'/index.php?option=com_k2&task=attachments.download&id='.$this->id.'&hash='.$hash);
		if ($application->isAdmin())
		{
			$link = str_replace(JURI::base(true), JURI::root(true), $link);
		}
		return $link;
	}

	public function getUrl()
	{
		$application = JFactory::getApplication();
		$hash = JApplication::getHash($this->id);
		$url = JRoute::_('index.php?option=com_k2&task=attachments.download&id='.$this->id.'&hash='.$hash, true, -1);
		return $url;
	}

	public function track()
	{
		$model = K2Model::getInstance('Attachments', 'K2Model');
		$model->setState('id', $this->id);
		$model->download();
	}

}
