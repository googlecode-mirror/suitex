<?php
/*
Plugin Name: SpreadX
Plugin URI: http://www.thisrand.com/scripts/spreadx
Description: A very easy way to get your site onto Digg, Sumble, Deli.cous, and Technocrati.
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
 * A very easy way to get your site onto Digg, Stumble, Del.icio.us, Slashdot, and Technorati.
 * @package WordPress
 * @since 2.6
 */

$pages["digg"] = array("Digg", "http://digg.com/", "http://digg.com/submit?phase=2&url=::URL::");
$pages["facebook"] = array("Facebook", "http://www.facebook.com", "http://www.facebook.com/share.php?u=::URL::");
$pages["stumble"] = array("StumbleUpon", "http://www.stumbleupon.com", "http://www.stumbleupon.com/submit?url=::URL::&title=::TITLE::");
$pages["technorati"] = array("Technorati", "http://www.technorati.com", "http://technorati.com/faves?add=::URL::");
$pages["delicious"] = array("Deli.cio.us", "http://www.delicious.com", "http://del.icio.us/post?url=::URL::&title=::TITLE::"); 
$pages["slashdot"] = array("Slashdot", "http://www.slashdot.org", "http://slashdot.org/submit.pl?url=::URL::");
 
$pluginBase = 'wp-content' . DIRECTORY_SEPARATOR . 'plugins' . DIRECTORY_SEPARATOR . 'spreadx';
require_once(ABSPATH . $pluginBase . DIRECTORY_SEPARATOR . 'spreadx_functions.php');

$obj = new spreadX();
$obj->pages = $pages;
$obj->pluginBase = $pluginBase;

add_filter('the_content', array($obj, 'spreadx_insert_buttons'));

register_activation_hook(__FILE__, array($obj, 'spreadx_install'));
register_deactivation_hook(__FILE__, array($obj, 'spreadx_uninstall'));

add_action('admin_menu', array($obj, 'spreadx_admin_menu'));





?>
