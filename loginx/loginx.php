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
    
    add_action('admin_head', array($loginXAdminObj, 'adminCSS'));
    add_action('wp_ajax_loginx_admin', array($loginXAdminObj, 'adminAjaxSubmit'));
    add_action('wp_ajax_loginx_fields', array($loginXAdminObj, 'adminAjaxFieldList'));
    wp_localize_script('loginx_admin', 'loginxAjax', array('ajaxurl' => admin_url('admin-ajax.php')));


}

add_action('login_head', array($loginXobj, 'loginx_redirect_login'));
add_action('admin_init', array($loginXobj, 'loginx_redirect_admin'));

function getLoginXURL($return = false){
    global $loginXobj;
    if ($return == false){ print($loginXobj->loginx_getURL());  }
    else { return $loginXobj->loginx_getURL(); }   
}















?>
