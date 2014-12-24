/**
 * @version		3.0.0
 * @package		Example K2 Plugin (K2 plugin)
 * @author		JoomlaWorks - http://www.joomlaworks.net
 * @copyright	Copyright (c) 2006 - 2014 JoomlaWorks Ltd. All rights reserved.
 * @license		GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 */

// Keep in mind that this file is loaded only once! This means that you need to use delegated event binding. Example follows:
jQuery('body').on('change', '#videoURL_item', function() {
	alert('Field was changed!');
});

