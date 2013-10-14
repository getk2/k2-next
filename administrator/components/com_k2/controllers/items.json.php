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

require_once JPATH_ADMINISTRATOR.'/components/com_k2/controller.php';

/**
 * Items JSON controller.
 */

class K2ControllerItems extends K2Controller
{
	public function image()
	{
		// Check for token
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		// Auto load
		require_once JPATH_ADMINISTRATOR.'/components/com_k2/vendor/autoload.php';

		$input = JFactory::getApplication()->input;
		$image = $input->files->get('image');
		$source = $image['file']['tmp_name'];
		try
		{
			$imagine = new Imagine\Gd\Imagine();
			$image = $imagine->open($source)->resize(new \Imagine\Image\Box(80, 80));
		}
		catch(Exception $e)
		{
			jexit($e->getMessage());

		}

		$buffer = $image->__toString();

		try
		{
			$adapter = new Gaufrette\Adapter\Local(JPATH_SITE);
			$filesystem = new Gaufrette\Filesystem($adapter);

			$filesystem->write('media/k2/items/origin/aaa.jpg', $buffer, true);
		}
		catch(Exception $e)
		{
			jexit($e->getMessage());
		}

		echo  'media/k2/items/origin/aaa.jpg';

		//$image = $imagine->open(JPATH_SITE.'/media/k2/items/origin/'.$file['name']);

		//var_dump($image);
		//die ;

		return $this;
	}

}
