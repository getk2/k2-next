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

<label><?php echo JText::_('K2_ROWS'); ?></label>
<input name="<?php echo $field->get('prefix'); ?>[rows]" type="text" value="<?php echo (int)$field->get('rows'); ?>" />
<label><?php echo JText::_('K2_COLUMNS'); ?></label>
<input name="<?php echo $field->get('prefix'); ?>[columns]" type="text" value="<?php echo (int)$field->get('columns'); ?>" />
<div><textarea rows="10" cols="40" name="<?php echo $field->get('prefix'); ?>[value]"><?php echo $field->get('value'); ?></textarea></div>
<label><?php echo JText::_('K2_USE_EDITOR'); ?></label>
<input value="1" name="<?php echo $field->get('prefix'); ?>[editor]" type="checkbox" <?php if($field->get('editor')) { echo 'checked="checked"';} ?> />

