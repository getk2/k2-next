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

jimport('joomla.form.formfield');
require_once JPATH_ADMINISTRATOR.'/components/com_k2/helpers/html.php';

class JFormFieldK2ImageProcessor extends JFormField
{
	var $type = 'K2ImageProcessor';

	public function getInput()
	{
		$options = array();
		if (class_exists('Gmagick'))
		{
			$options[] = JHtml::_('select.option', 'Gmagick', 'Gmagick');
		}
		if (class_exists('Imagick'))
		{
			$options[] = JHtml::_('select.option', 'Imagick', 'Imagick');
		}
		if (function_exists('gd_info'))
		{
			$options[] = JHtml::_('select.option', 'Gd', 'Gd');
		}
		return JHtml::_('select.genericlist', $options, $this->name, '', 'value', 'text', $this->value);

	}

}
