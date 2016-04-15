<?php
/**
 * @version		3.0.0
 * @package		K2
 * @author		JoomlaWorks http://www.joomlaworks.net
 * @copyright	Copyright (c) 2006 - 2014 JoomlaWorks Ltd. All rights reserved.
 * @license		GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 */

// no direct access
defined('_JEXEC') or die ; 

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
		$attributes = 'class="k2ClassicPopUp" data-width="'.(int)$field->get('popupWidth', 900).'" data-height="'.(int)$field->get('popupWidth', 600).'"';
		break;

	case 'lightbox' :
		$attributes = 'class="k2Modal"';
		break;
}

$url = htmlspecialchars($field->get('url'), ENT_QUOTES, 'UTF-8');
if (stripos($url, 'http:') == false){
	$url = 'http://' . $url;
}
?>
<?php if($field->get('url')): ?>
<a <?php echo $attributes; ?> href="<?php echo $url; ?>"><?php echo $field->get('text'); ?></a>
<?php endif; ?>