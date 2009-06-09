<?php
/*
Plugin Name: MultiX
Plugin URI: http://www.thisrand.com/scripts/multix
Description: A lightweight script to allow for the seemless administration of multiple wordpress websites that can reside on different servers and databases.
Version: 0.1
Author: Xnuiem
Author URI: http://www.thisrand.com

*/

/*  Copyright 2009 Xnuiem  (email : scripts @T thisrand D07 com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

/**
 * A lightweight script to allow for the seemless administration of multiple wordpress websites that can reside on different servers and databases.
 * @since 2.6
 */

//$pluginBase = 'wp-content' . DIRECTORY_SEPARATOR . 'plugins' . DIRECTORY_SEPARATOR . 'multix';
//require_once(ABSPATH . $pluginBase . DIRECTORY_SEPARATOR . 'multix_functions.php');

$pluginBase = 'wp-content' . DIRECTORY_SEPARATOR . 'plugins' . DIRECTORY_SEPARATOR . 'multix';      
require_once(ABSPATH . $pluginBase . DIRECTORY_SEPARATOR . 'multix_functions.php');  

$obj = new multiX(); 
$obj->pluginBase = $pluginBase;
$obj->baseURL    = "tools.php?page=multix/multix_functions.php";
$obj->pluginURL  = "../wp-content/plugins/multix/";;

register_activation_hook(__FILE__, array($obj, 'multix_install'));
register_deactivation_hook(__FILE__, array($obj, 'multix_uninstall'));

add_action('admin_menu', array($obj, 'multix_admin_menu'));
add_action('plugins_loaded', array($obj, 'multix_login'));






?>
