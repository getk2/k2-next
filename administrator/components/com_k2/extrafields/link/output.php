<?php
/**
 * @version		3.0.0
 * @package		K2
 * @author		JoomlaWorks http://www.joomlaworks.net
 * @copyright	Copyright (c) 2006 - 2013 JoomlaWorks Ltd. All rights reserved.
 * @license		GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 */

// no direct access
defined('_JEXEC') or die ; ?>
<?php
switch ($field->get('target'))
{
	case 'same' :
	default :
		$attributes = '';
		break;

	case 'new' :
		$attributes = 'target="_blank"';
		break;

	case 'popup' :
		$attributes = 'class="classicPopup"';
		break;

	case 'lightbox' :
		$attributes = 'class="modal"';
		break;
}
?>
<a <?php echo $attributes; ?> href="<?php echo htmlspecialchars($field->get('url'), ENT_QUOTES, 'UTF-8'); ?>"><?php echo $field->get('text'); ?></a>