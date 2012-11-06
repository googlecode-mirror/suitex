<?php
/*
Plugin Name: phpX
Plugin URI: http://www.xmtek.net
Description: A development framework plugin
Version: 0.1
Author: XM Tek LLC
Author URI: http://www.xmtek.net

*/

/*  Copyright 2012 XM Tek LLC

This software is released under the Creative Commons Attribution-ShareAlike 3.0 Unported (CC BY-SA 3.0) (http://creativecommons.org/licenses/by-sa/3.0/deed.en_US)
*/

/**
 * 
 * @since 3.0.0
 */

if (!defined('WP_CONTENT_URL')){ define('WP_CONTENT_URL', get_option('siteurl') . '/wp-content'); }
if (!defined('WP_CONTENT_DIR')){ define('WP_CONTENT_DIR', ABSPATH . 'wp-content' ); }
if (!defined('WP_PLUGIN_URL')){  define('WP_PLUGIN_URL', WP_CONTENT_URL. '/plugins' ); }
if (!defined('WP_PLUGIN_DIR')){  define('WP_PLUGIN_DIR', WP_CONTENT_DIR . '/plugins' ); }
      
define(PHPX_DIR, WP_PLUGIN_DIR . '/phpx/');  
define(PHPX_URL, WP_PLUGIN_URL . '/phpx/'); 

if (!session_id()){ session_start(); }


if (!is_admin()){
    wp_deregister_script('jquery');
    wp_deregister_script('jquery-ui-core');
    wp_register_script('jquery', PHPX_URL . 'js/jquery-1.5.1.min.js');
    wp_register_script('jquery-ui-core', PHPX_URL . 'js/jquery-ui-1.8.13.custom.min.js');
    wp_register_script('jquery-validate', PHPX_URL . 'js/jquery.validate.min.js');
    wp_enqueue_script('phpx', PHPX_URL . 'js/phpx.js', array('jquery', 'jquery-ui-core', 'jquery-validate', 'jquery-form'));
    add_action('wp_head', 'phpx_addCSS'); 
}
else {
    add_action('admin_head', 'phpx_addCSS');    
    wp_register_script('jquery-validate', PHPX_URL . 'js/jquery.validate.min.js');
    wp_enqueue_script('phpx', PHPX_URL . 'js/phpx.js', array('jquery', 'jquery-ui-core', 'jquery-validate', 'jquery-form'));
}




register_activation_hook(__FILE__, 'phpx_install');
register_deactivation_hook(__FILE__, 'phpx_uninstall');

function phpx_addCSS(){        
    if (is_admin()){
        print('<link type="text/css" rel="stylesheet" href="' . PHPX_URL . 'css/phpx-admin.css" />');         
        print('<link type="text/css" rel="stylesheet" href="' . PHPX_URL . 'jquery-themes/smoothness/jquery-ui-1.8.20.custom.css" />' . "\n"); 
        
    }
    else {
        print('<link type="text/css" rel="stylesheet" href="' . PHPX_URL . 'css/phpx.css" />' . "\n");  
        print('<link type="text/css" rel="stylesheet" href="' . PHPX_URL . 'jquery-themes/redmond/jquery-theme.css" />' . "\n");   
        print('<meta name="framework" content="phpX Framework 0.1" />' . "\n");
       
    }
}

function phpx_install(){
    update_option('phpx_version', '0.1');
}

function phpx_uninstall(){
    delete_option('phpx_version');    
}








?>
