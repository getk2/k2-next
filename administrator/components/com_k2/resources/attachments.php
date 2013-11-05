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

/**
 * K2 item resource class.
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
		$hash = JApplication::getHash($this->id);
		$this->link = JRoute::_('index.php?option=com_k2&task=attachments.download&id='.$this->id.'&hash='.$hash);

	}

	public function track()
	{
		$model = K2Model::getInstance('Attachments', 'K2Model');
		$model->setState('id', $this->id);
		$model->download();
	}

	public function delete()
	{
		// First check if we have any files to delete
		$this->deleteFile();

		// Delete
		$model = K2Model::getInstance('Attachments', 'K2Model');
		$model->setState('id', $this->id);
		$model->delete();
	}

	public function deleteFile()
	{
		if ($this->file)
		{
			// Filesystem
			require_once JPATH_ADMINISTRATOR.'/components/com_k2/classes/filesystem.php';
			$filesystem = K2FileSystem::getInstance();

			$path = 'media/k2/attachments';
			if ($this->itemId)
			{
				$folder = $this->itemId;
				$key = $path.'/'.$folder.'/'.$this->file;
			}
			else
			{
				list($folder, $file) = explode('/', $this->file);
				$key = $path.'/'.$folder.'/'.$file;
			}

			if ($filesystem->has($key))
			{
				$filesystem->delete($key);
			}

			$keys = $filesystem->listKeys($path.'/'.$folder.'/');

			if (empty($keys['keys']) && $filesystem->has($path.'/'.$folder))
			{
				$filesystem->delete($path.'/'.$folder);
			}

		}
	}

}
