<?php
/**
 * @version		3.0.0b
 * @package		K2
 * @author		JoomlaWorks http://www.joomlaworks.net
 * @copyright	Copyright (c) 2006 - 2014 JoomlaWorks Ltd. All rights reserved.
 * @license		GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 */

// no direct access
defined('_JEXEC') or die ; ?>


<div id="K2SettingsContainer" class="jw">

	<?php if(!$isMenu): ?>
	<div id="K2SettingsMenu">
		<ul class="jw--tabs jw--tabs__top">
			<?php foreach ($this->form->getFieldsets() as $name => $fieldset): ?>				
			<?php if($name != 'basic' && strpos($name, 'K2GROUP_') === false): ?>
			<li><a href="#<?php echo $name; ?>"><?php echo JText::_($fieldset->label); ?></a></li>
			<?php endif; ?>
			<?php endforeach; ?>
		</ul>
	</div>
	<?php endif; ?>
	
	
	<div id="K2Settings" class="jw--settings jw--generic--form">
		<?php foreach ($this->form->getFieldsets() as $name => $fieldset): ?>
		<?php if((!$isMenu && $name != 'basic') || ($isMenu && $name == 'k2basic')): ?>
		<?php $attributes = strpos($name, 'K2GROUP_') === 0 ? 'style="display:none;" data-k2group="'.substr($name, 8).'"' : ''; ?>
		<div class="jw--setting--field K2SettingsSection" <?php echo $attributes;?>>
		
			<?php if($fieldset->label): ?>
			<h3 class="K2SettingsSectionHeader" id="<?php echo $name; ?>"><?php echo JText::_($fieldset->label); ?></h3>
			<?php endif; ?>
			
			<?php if (isset($fieldset->description) && !empty($fieldset->description)) : ?>
			<p><?php echo JText::_($fieldset->description); ?></p>
			<?php endif; ?>

			<?php foreach ($this->form->getFieldset($name) as $field): ?>
			
			<?php if($field->type != 'K2Settings'): ?>
			
			
			<?php if($field->type == 'K2Header'): ?>
			
			<?php echo $field->getInput(); ?>
			
			<?php else: ?>
			
			<div class="jw--setting--single ov-hidden K2SettingsRow K2SettingsField<?php echo $field->type; ?>">
				<div class="left">
					<?php echo $field->getLabel(); ?>
				</div>
				<div class="right">
					<?php echo $field->getInput(); ?>
				</div>
			</div>
			
			<?php endif; ?>
			<?php endif; ?>
			<?php endforeach; ?>
		</div>
		<?php endif; ?>
		<?php endforeach; ?>
	
	
	</div>
</div>