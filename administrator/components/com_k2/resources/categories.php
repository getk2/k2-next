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
require_once JPATH_SITE.'/components/com_k2/helpers/route.php';
require_once JPATH_ADMINISTRATOR.'/components/com_k2/helpers/images.php';

/**
 * K2 category resource class.
 */

class K2Categories extends K2Resource
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
	 * @return K2Category The category object.
	 */
	public static function getInstance($id)
	{
		if (empty(self::$instances[$id]))
		{
			K2Model::addIncludePath(JPATH_ADMINISTRATOR.'/components/com_k2/models');
			$model = K2Model::getInstance('Categories', 'K2Model');
			if (is_numeric($id))
			{
				$model->setState('id', $id);
			}
			else
			{
				$model->setState('alias', $id);
			}
			$item = $model->getRow();
			self::$instances[$item->id] = $item;
			self::$instances[$item->alias] = $item;
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

		// Edit link
		$this->editLink = '#categories/edit/'.$this->id;

		// Link
		$this->link = $this->getLink();

		// Permisisons
		$user = JFactory::getUser();
		if ($user->guest)
		{
			$this->canEdit = false;
			$this->canEditState = false;
			$this->canDelete = false;
			$this->canSort = false;
			$this->canAddItem = false;
		}
		else
		{
			$this->canEdit = $user->authorise('k2.category.edit', 'com_k2.category.'.$this->id) || ($user->id == $this->created_by && $user->authorise('k2.category.edit.own', 'com_k2.category.'.$this->id));
			$this->canEditState = $user->authorise('k2.category.edit.state', 'com_k2.category.'.$this->id);
			$this->canDelete = $user->authorise('k2.category.delete', 'com_k2.category.'.$this->id);
			$this->canSort = $user->authorise('k2.category.edit', 'com_k2');
			$this->canAddItem = $user->authorise('k2.item.create', 'com_k2.category.'.$this->id);
		}

		// Image
		$this->image = $this->getImage();

	}

	public function getLink()
	{
		return JRoute::_(K2HelperRoute::getCategoryRoute($this->id.':'.$this->alias));
	}

	public function getImage()
	{
		return K2HelperImages::getCategoryImage($this);
	}

	public function getChildren()
	{
		$children = array();
		$model = K2Model::getInstance('Categories');
		$model->setState('site', true);
		$model->setState('root', $this->id);
		$chidlren = $model->getRows();
		foreach ($chidlren as $key => $child)
		{
			if ($child->id == $this->id)
			{
				unset($chidlren[$key]);
				break;
			}
		}
		return $chidlren;
	}

	public function getNumOfItems()
	{
		$numOfItems = 0;
		$model = K2Model::getInstance('Items');
		$model->setState('site', true);
		$model->setState('category', $this->id);
		$numOfItems = $model->countRows();
		return $numOfItems;
	}
	
	public function getExtraFields()
	{
		$extraFields = array();
		if ($this->id)
		{
			$extraFields = K2HelperExtraFields::getCategoryExtraFields($this->id, $this->extra_fields);
		}
		return $extraFields;
	}

	public function getEvents($context = 'com_k2.category', &$params = null, $offset = 0, $k2Plugins = true, $jPlugins = true)
	{
		// Params
		if (is_null($params))
		{
			$params = JComponentHelper::getParams('com_k2');
		}

		// Get dispatcher
		$dispatcher = JDispatcher::getInstance();

		// Create the text variable
		$this->text = $this->description;

		// Create the event object with null values
		$events = new stdClass;
		$events->K2CategoryDisplay = '';

		// Content plugins
		if ($jPlugins)
		{
			// Import content plugins
			JPluginHelper::importPlugin('content');

			$dispatcher->trigger('onContentPrepare', array($context, &$this, &$params, $offset));
			$results = $dispatcher->trigger('onContentAfterTitle', array($context, &$this, &$params, $offset));
			$events->AfterDisplayTitle = trim(implode("\n", $results));
			$results = $dispatcher->trigger('onContentBeforeDisplay', array($context, &$this, &$params, $offset));
			$events->BeforeDisplayContent = trim(implode("\n", $results));
			$results = $dispatcher->trigger('onContentAfterDisplay', array($context, &$this, &$params, $offset));
			$events->AfterDisplayContent = trim(implode("\n", $results));

		}

		// K2 plugins
		if ($k2Plugins)
		{
			// Import K2 plugins
			JPluginHelper::importPlugin('k2');
			
			$dispatcher->trigger('onK2PluginInit', array($this));

			$results = $dispatcher->trigger('onK2CategoryDisplay', array(&$this, &$params, $offset));
			$events->K2CategoryDisplay = trim(implode("\n", $results));

			$dispatcher->trigger('onK2PrepareContent', array(&$this, &$params, $offset));
		}

		// Restore description
		$this->description = $this->text;

		// Unset the text
		unset($this->text);

		// return
		return $events;
	}

	public function getEffectiveParams()
	{
		$effectiveParams = $this->params;
		if ($this->inheritance)
		{
			$effectiveParams = K2Categories::getInstance($this->inheritance)->params;
		}
		return $effectiveParams;
	}

	public function checkSiteAccess()
	{
		// Get date
		$date = JFactory::getDate();
		$now = $date->toSql();

		// State check
		if ($this->state < 1 || (int)$this->id < 1)
		{
			JError::raiseError(404, JText::_('K2_NOT_FOUND'));
			return false;
		}

		// Get user
		$user = JFactory::getUser();
		$viewLevels = $user->getAuthorisedViewLevels();

		// Access check
		if (!in_array($this->access, $viewLevels))
		{
			if ($user->guest)
			{
				// Get application
				$application = JFactory::getApplication();

				// Get document
				$document = JFactory::getDocument();

				// In front end HTML requests redirect the user to the login page
				if ($application->isSite() && $document->getType() == 'html')
				{
					require_once JPATH_SITE.'/components/com_users/helpers/route.php';
					$uri = JFactory::getURI();
					$url = 'index.php?option=com_users&view=login&return='.base64_encode($uri->toString()).'&Itemid='.UsersHelperRoute::getLoginRoute();
					$application->redirect(JRoute::_($url, false), JText::_('K2_YOU_NEED_TO_LOGIN_FIRST'));
				}

				// Return false
				return false;
			}
			else
			{
				JError::raiseError(403, JText::_('K2_NOT_AUTHORISED'));
				return false;
			}
		}

		return true;

	}

}
