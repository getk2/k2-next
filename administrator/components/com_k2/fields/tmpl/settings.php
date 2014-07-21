<?php
/**
 * @version		3.0.0
 * @package		K2
 * @author		JoomlaWorks http://www.joomlaworks.net
 * @copyright	Copyright (c) 2006 - 2014 JoomlaWorks Ltd. All rights reserved.
 * @license		GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 */

// no direct access
defined('_JEXEC') or die ; ?>


<div id="K2SettingsContainer">
	<div id="K2SettingsMenu">
		<ul>
			<?php foreach ($this->form->getFieldsets() as $name => $fieldset): ?>				
			<?php if($name != 'basic' && strpos($name, 'K2GROUP_') === false): ?>
				<li><a href="#<?php echo $name; ?>"><?php echo JText::_($fieldset->label); ?></a></li>
			<?php endif; ?>
			<?php endforeach; ?>
		</ul>
	</div>
	<div id="K2Settings">
	<?php foreach ($this->form->getFieldsets() as $name => $fieldset): ?>
	<?php if($name != 'basic'): ?>
		<?php $attributes = strpos($name, 'K2GROUP_') === 0 ? 'style="display:none;" data-k2group="'.substr($name, 8).'"' : ''; ?>
		<div class="K2SettingsSection" <?php echo $attributes;?>>
			<h3 class="K2SettingsSectionHeader" id="<?php echo $name; ?>"><?php echo JText::_($fieldset->label); ?></h3>
			<?php if (isset($fieldset->description) && !empty($fieldset->description)) : ?>
				<p><?php echo JText::_($fieldset->description); ?></p>
			<?php endif; ?>

			<?php foreach ($this->form->getFieldset($name) as $field): ?>
				<div class="K2SettingsRow K2SettingsField<?php echo $field->type; ?>">
					<?php echo $field->getLabel(); ?>
					<?php echo $field->getInput(); ?>
				</div>
				<div class="clr"></div>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>
	<?php endforeach; ?>
	</div>
</div>