<?php
/**
 * @package amoForms
 */

/*
Plugin Name: amoForms
Plugin URI:  https://wordpress.org/plugins/amoforms/
Description: Create forms and manage submissions easily with a simple interface. Contact forms, subscription forms, or any other form for WordPress.
Version:     3.1.19
Author:      amoCRM
Author URI:  http://www.amocrm.com
License:     GPLv2 or later
Text Domain: amoforms

amoForms is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
any later version.

amoForms is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with amoForms. If not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/

if (!function_exists('add_action')) {
	die("Hi there! I'm just a plugin, not much I can do when called directly.");
}

if (version_compare(PHP_VERSION, '5.4.0', '<')) {
	echo '<h3 style="margin-left: 185px; color: #f00;">Plugin amoForms needs to PHP 5.4 or greater.</h3>';
	return;
}

require_once __DIR__ . '/bootstrap.php';
