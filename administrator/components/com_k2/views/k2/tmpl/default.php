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
<div data-application="k2" class="jw">
<div class="jw--header" data-region="header"></div>

    <div class="left" data-region="sidebar"></div>
    <div class="jw--transition jw--component">
		<div class="jw--messages" data-region="messages"></div>
				
        <div class="jw--transition jw--subheader" data-region="subheader"></div>
        <div data-region="content"></div>
    </div>
    <div class="clr"></div>
    <div data-region="modal"></div>
</div>
