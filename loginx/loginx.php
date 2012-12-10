<?php
/*
Plugin Name: Login Register Profile
Plugin URI: http://www.xmtek.net
Description: Keep users on your page, out of the administration area for login, profile updates, and registration
Version: 0.1
Author: Xnuiem
Author URI: http://www.thisrand.com

*/

/*  Copyright 2012 XM Tek LLC
This software is released under the Creative Commons Attribution-ShareAlike 3.0 Unported (CC BY-SA 3.0) (http://creativecommons.org/licenses/by-sa/3.0/deed.en_US)

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
    add_filter('get_comment_author_url', array($loginXobj, 'loginx_comment_url'));
    add_filter('the_author_posts_link', array($loginXobj, 'loginx_author_url'));
    
    add_action('wp', array($loginXobj, 'loginx_login'));    
    add_filter('get_avatar', array($loginXobj, 'loginx_rpx_avatar_filter'), 12);
    add_action('woocommerce_login_widget_logged_out_after_form', array($loginXobj, 'wcLoginWidget'));
    add_filter('woocommerce_login_widget_logged_in_links', array($loginXobj, 'wcLoginWidgetLinks'));
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

add_action('wp_authenticate', array($loginXobj, 'loginx_login_hook'), 1);
add_action('woocommerce_created_customer', array($loginXobj, 'woo_register'), 100);


add_action('login_head', array($loginXobj, 'loginx_redirect_login'));
add_action('admin_head', array($loginXobj, 'loginx_redirect_admin'), 100);

function getLoginXURL($return = false){
    global $loginXobj;
    if ($return == false){ print($loginXobj->loginx_getURL());  }
    else { return $loginXobj->loginx_getURL(); }   
}





add_filter('wp_mail_content_type', 'loginXSetContentType');

function loginXSetContentType(){
    return 'text/html';
}







?>
