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
<input name="value[rows]" type="text" value="<?php echo $this->escape($field->get('rows')); ?>" />
<label><?php echo JText::_('K2_COLUMNS'); ?></label>
<input name="value[columns]" type="text" value="<?php echo $this->escape($field->get('columns')); ?>" />
<div><textarea rows="10" cols="40" name="value[value]"><?php echo $field->get('value'); ?></textarea></div>
<label><?php echo JText::_('K2_USE_EDITOR'); ?></label>
<input value="1" name="value[editor]" type="checkbox" <?php if($field->get('editor')) { echo 'checked="checked"';} ?> />

