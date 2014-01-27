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

$name = 'extra_fields['.$this->id.'][value]';
$id = 'extraFieldId'.$this->id;
?>

<?php foreach($field->get('options') as $option): ?>
	<label>
		<?php echo $option; ?>
		<input type="radio" name="<?php echo $name; ?>" value="<?php echo htmlspecialchars($option, ENT_QUOTES, 'UTF-8'); ?>" />
	</label>
<?php endforeach; ?>
