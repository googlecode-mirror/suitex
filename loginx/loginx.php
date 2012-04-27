<?php
/*
Plugin Name: LoginX
Plugin URI: http://www.phpx.org
Description: 
Version: 0.1
Author: Xnuiem
Author URI: http://www.thisrand.com

*/

/*  Copyright 2009-2012 Xnuiem  (email : scripts @T thisrand D07 com)

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
 * 
 * @since 2.6
 */

 
 
 
if (!defined('WP_CONTENT_URL')){ define('WP_CONTENT_URL', get_option( 'siteurl' ) . '/wp-content' ); }
if (!defined('WP_CONTENT_DIR')){ define('WP_CONTENT_DIR', ABSPATH . 'wp-content' ); }
if (!defined('WP_PLUGIN_URL')){ define('WP_PLUGIN_URL', WP_CONTENT_URL. '/plugins' ); }
if (!defined('WP_PLUGIN_DIR')){ define('WP_PLUGIN_DIR', WP_CONTENT_DIR . '/plugins' ); }
      
define(LOGINX_DIR, WP_PLUGIN_DIR . '/loginx/');  
define(LOGINX_URL, WP_PLUGIN_URL . '/loginx/'); 
require_once(LOGINX_DIR . 'includes/loginx_functions.php');

$loginXobj = new loginX(); 



if (!is_admin()){
    add_action('wp_head', array($loginXobj, 'loginx_addCSS'));
    add_filter('the_content', array($loginXobj, 'loginx_content'));
    add_action('wp', array($loginXobj, 'loginx_login'));
}
else {
    require_once(LOGINX_DIR . 'includes/loginx_admin_obj.php');
    $loginXAdminObj = new loginXAdmin();
    
    register_activation_hook(__FILE__, array($loginXAdminObj, 'install'));
    register_deactivation_hook(__FILE__, array($loginXAdminObj, 'uninstall'));    
    add_action('admin_menu', array($loginXAdminObj, 'adminMenu')); 
}

add_action('login_head', array($loginXobj, 'loginx_redirect_login'));


function getLoginXURL($return = false){
    global $loginXobj;
    if ($return == false){ print($loginXobj->loginx_getURL());  }
    else { return $loginXobj->loginx_getURL(); }   
}















?>
