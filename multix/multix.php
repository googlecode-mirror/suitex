<?php
/*
Plugin Name: MultiX
Plugin URI: http://www.thisrand.com/scripts/multix
Description: A lightweight script to allow for the seemless administration of multiple Wordpress websites that can reside on different servers and databases.
Version: 0.4
Author: Xnuiem
Author URI: http://www.thisrand.com

*/

/*  Copyright 2009-2011 Xnuiem  (email : scripts @T thisrand D07 com)

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
 * A lightweight script to allow for the seemless administration of multiple Wordpress websites that can reside on different servers and databases.
 * @since 2.6
 */

 
 
 
if (!defined('WP_CONTENT_URL')){ define('WP_CONTENT_URL', get_option( 'siteurl' ) . '/wp-content' ); }
if (!defined('WP_CONTENT_DIR')){ define('WP_CONTENT_DIR', ABSPATH . 'wp-content' ); }
if (!defined('WP_PLUGIN_URL')){  define('WP_PLUGIN_URL', WP_CONTENT_URL. '/plugins' ); }
if (!defined('WP_PLUGIN_DIR')){  define('WP_PLUGIN_DIR', WP_CONTENT_DIR . '/plugins' ); }
      
define(MULTIX_DIR, WP_PLUGIN_DIR . '/multix/');  
define(MULTIX_URL, WP_PLUGIN_URL . '/multix/'); 
require_once(MULTIX_DIR . 'includes/multix_functions.php');


global $suiteXLoaded;

if (!$suiteXLoaded){
        $suiteXLoaded = true;
        //wp_deregister_script('jquery');
        //wp_deregister_script('jquery-ui-core');
        //wp_register_script('jquery', MULTIX_URL . 'suitex/js/jquery-1.5.1.min.js');
        //wp_register_script('jquery-ui-core', MULTIX_URL . 'suitex/js/jquery-ui-1.8.13.custom.min.js');
        //wp_register_script('jquery-validate', MULTIX_URL . 'suitex/js/jquery.validate.min.js');
        wp_enqueue_script('suitex', MULTIX_URL . 'suitex/js/suitex.js');
        //wp_localize_script('suitex', 'SuiteXAjax', array('ajaxurl' => admin_url('admin-ajax.php')));
}


$obj = new multiX(); 
$obj->pluginBase = $pluginBase;
$obj->baseURL    = "tools.php?page=multix/includes/multix_functions.php";


register_activation_hook(__FILE__, array($obj, 'multix_install'));
register_deactivation_hook(__FILE__, array($obj, 'multix_uninstall'));

add_action('admin_menu', array($obj, 'multix_admin_menu'));
add_action('plugins_loaded', array($obj, 'multix_api'));
add_action('admin_head', array($obj, 'suitex_addCSS')); 
add_action('wp_dashboard_setup', array($obj, 'multix_dashboard_setup'));






?>
