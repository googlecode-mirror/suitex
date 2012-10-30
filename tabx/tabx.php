<?php
/*
Plugin Name: TabX
Plugin URI: http://www.xmtek.net
Description: 
Version: 0.1
Author: Ryan C. Meinzer
Author URI: http://www.xmtek.net

*/

/*  Copyright 1997-2012 Xnuiem  (email : scripts @T thisrand D07 com)

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

if (!defined('WP_CONTENT_URL')){ define('WP_CONTENT_URL', get_option( 'siteurl' ) . '/wp-content' ); }
if (!defined('WP_CONTENT_DIR')){ define('WP_CONTENT_DIR', ABSPATH . 'wp-content' ); }
if (!defined('WP_PLUGIN_URL')){ define('WP_PLUGIN_URL', WP_CONTENT_URL. '/plugins' ); }
if (!defined('WP_PLUGIN_DIR')){ define('WP_PLUGIN_DIR', WP_CONTENT_DIR . '/plugins' ); }
      
define(TABX_DIR, WP_PLUGIN_DIR . '/tabx/');  
define(TABX_URL, WP_PLUGIN_URL . '/tabx/'); 


if (!is_admin()){
    wp_enqueue_script('tabx', TABX_URL . 'jquery.tabSlideOut.v1.3.js', 'jquery');
    wp_enqueue_style('tabx', TABX_URL . 'tabx.css');
    add_action('wp_footer', 'tabX_footer');
}

function tabX_footer(){

    $text = "<script type=\"text/javascript\">
    jQuery(function(){
        jQuery('.tabx-div').tabSlideOut({
            tabHandle: '.handle',                     //class of the element that will become your tab
            pathToTabImage: '" . TABX_URL . "contact_tab.gif', //path to the image for the tab //Optionally can be set using css
            imageHeight: '122px',                     //height of tab image           //Optionally can be set using css
            imageWidth: '40px',                       //width of tab image            //Optionally can be set using css
            tabLocation: 'left',                      //side of screen where tab lives, top, right, bottom, or left
            speed: 300,                               //speed of animation
            action: 'click',                          //options: 'click' or 'hover', action to trigger animation
            topPos: '200px',                          //position from the top/ use if tabLocation is left or right
            leftPos: '20px',                          //position from left/ use if tabLocation is bottom or top
            fixedPosition: false                      //options: true makes it stick(fixed position) on scroll
        });

    });

    </script>";
    $text .= '<div class="tabx-div">
        <a class="handle" href="http://link-for-non-js-users.html">Content</a>
        <div class="tabx-content" id="tab1">
            <h2>Phone</h2>
            
        </div>
        <div class="tabx-content" id="tab2">
            <h2>Email</h2>
            
        </div>
        <div class="tabx-content" id="tab3">
            <h2>Chat</h2>
            
        </div>        
    </div>';
    print($text);
    
    

     
}







  
?>