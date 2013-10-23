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
?>
<select name="extra_fields[<?php echo $this->id; ?>]" <?php if($field->get('multiple')) { echo 'multiple="multiple"';} ?>>
<?php if($field->get('null')): ?>
	<option value=""><?php echo JText::_('K2_SELECT_AN_OPTION'); ?></option>
<?php endif; ?>
<?php foreach($field->get('options') as $option): ?>
	<option value="<?php echo $this->escape($option); ?>"><?php echo $option; ?></option>
<?php endforeach; ?>
</select>