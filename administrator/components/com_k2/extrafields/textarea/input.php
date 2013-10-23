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
require_once JPATH_ADMINISTRATOR.'/components/com_k2/classes/editor.php';
$config = JFactory::getConfig();
$editor = K2Editor::getInstance($config->get('editor'));
?>

<?php if($field->get('editor')) : ?>
	<?php echo $editor->display('extra_fields['.$this->id.']', $field->get('value'), '100%', '300', (int)$field->get('rows', '10'), (int)$field->get('columns', '40')); ?>
	<script type="text/javascript">
	K2Editor.init();
	</script>
<?php else : ?>
	<textarea rows="<?php echo (int)$field->get('rows', '10'); ?>" cols="<?php echo (int)$field->get('columns', '40'); ?>" name="extra_fields[<?php echo $this->id; ?>]"><?php echo $field->get('value'); ?></textarea>
<?php endif; ?>
