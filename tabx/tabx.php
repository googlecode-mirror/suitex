<?php
/*
Plugin Name: Slide Out Tab
Plugin URI: http://www.xmtek.net/software/wordpress-plugins/slide-out-tab/
Description: A quick way to add a slide out tab to your website, useful for Contact, Support, Help, or any other information you may want to let your visitors easily find.
Version: 0.1
Author: XM Tek LLC
Author URI: http://www.xmtek.net

*/

/*  Copyright 2012 XM Tek LLC 

This software is released under the Creative Commons Attribution-ShareAlike 3.0 Unported (CC BY-SA 3.0) (http://creativecommons.org/licenses/by-sa/3.0/deed.en_US)
*/

if (!defined('WP_CONTENT_URL')){ define('WP_CONTENT_URL', get_option( 'siteurl' ) . '/wp-content' ); }
if (!defined('WP_CONTENT_DIR')){ define('WP_CONTENT_DIR', ABSPATH . 'wp-content' ); }
if (!defined('WP_PLUGIN_URL')){ define('WP_PLUGIN_URL', WP_CONTENT_URL. '/plugins' ); }
if (!defined('WP_PLUGIN_DIR')){ define('WP_PLUGIN_DIR', WP_CONTENT_DIR . '/plugins' ); }
      
define(TABX_DIR, WP_PLUGIN_DIR . '/tabx/');  
define(TABX_URL, WP_PLUGIN_URL . '/tabx/'); 

$tabXObj = new tabXObj();

if (!is_admin()){
    wp_enqueue_script('tabx', TABX_URL . 'jquery.tabSlideOut.v1.3.js', 'jquery');
    wp_enqueue_style('tabx', TABX_URL . 'tabx.css');
    wp_enqueue_style('tabx-custom', TABX_URL . 'custom.css');
    add_action('wp_footer', array($tabXObj, 'footer'));
}
else { 
    register_activation_hook(__FILE__, array($tabXObj, 'install'));
    register_deactivation_hook(__FILE__, array($tabXObj, 'uninstall'));    
    add_action('admin_menu', array($tabXObj, 'adminMenu'));    
}

class tabXObj { 
    
    
    function __construct(){
        $this->options = get_option('tabx_options');    
    }
    
    function install(){
        if (!is_plugin_active('phpx/phpx.php')){
            die('Slide Out Tab requires the PHPX Framework.  Please install PHPX and then reinstall Slide Out Tab.');
        }
        
        if (count($this->options) == 0 || !is_array($this->options)){
            $this->options['image'] = TABX_URL . 'contact_tab.gif';
            $this->options['link_text'] = 'Contact';
            $this->options['content'] = '<h3>Contact Us</h3><br />This is where you can put your contact information.';
            $this->options['height'] = 122;
            $this->options['width'] = 40;
            $this->options['location'] = 'left';
            $this->options['speed'] = 300;
            $this->options['action'] = 'click';
            $this->options['top'] = 200;
            $this->options['left'] = 20;
            $this->options['fixed'] = 'false';           
            update_option('tabx_options', $this->options);
        }
    }

    function uninstall(){
        
    }
    
    function adminMenu(){
        if (current_user_can('publish_pages')){
            add_management_page('Slide Out Tab', 'Slide Out Tab', 2, __FILE__, array($this, 'adminPage')); 
        }        
    }
    
    function adminPage(){
        if (current_user_can('publish_pages')){
            if ($_POST['submit']){
                $omit = array('submit', 'wp_nonce');
                if (!wp_verify_nonce($_POST['wp_nonce'], 'tabx')){ die('Invalid Token'); }
                foreach($_POST as $k => $v){
                    if (!in_array($k, $omit)){
                        $this->options[$k] = $v;
                    }
                }
                if ($_FILES['image']['name'] != ''){
                    $file = wp_handle_upload($_FILES['image'], array('test_form' => false));
                    $this->options['image'] = $file['url'];
                }
                
                update_option('tabx_options', $this->options);
                $message = '<p>Options Saved</p>';
                                
            }
            
            $envArray = array('Development', 'Production');
            
            require_once(PHPX_DIR . 'phpx_form.php');
            $form = new phpx_form();
  
            $text = '<div class="wrap" id="phpxContainer"><h2>Slide Out Tab</h2>';
     
            if ($message || $_GET['message']){ $text .= $message; }
            $text .= $form->startForm('tools.php?page=tabx/tabx.php', 'tabxForm', 'post', true);  
            $text .= $form->hidden('wp_nonce', wp_create_nonce('tabx'));
            $text .= $form->textField('Link Text', 'link_text', $this->options['link_text']);
            
            ob_start();

            wp_editor(stripslashes($this->options['content']), 'tabxcontent', array('textarea_name' => 'content'));
            $text .= $form->freeText(ob_get_contents());
            ob_end_clean();
            
            $locationArray = array('top' => 'top', 'bottom' => 'bottom', 'left' => 'left', 'right' => 'right');
            $actionArray = array('click' => 'click', 'hover' => 'hover');
            $fixedArray = array('true' => 'True', 'false' => 'False');
            $text .= $form->fileField('Image', 'image');
            $text .= $form->freeText('<strong>Current Image: </strong><br /><img src="' . $this->options['image'] . '" />');
            $text .= $form->textField('Image Height', 'height', $this->options['height']);
            $text .= $form->textField('Image Width', 'width', $this->options['width']);
            $text .= $form->dropDown('Location', 'location', $this->options['location'], $locationArray);
            $text .= $form->textField('Speed', 'speed', $this->options['speed']);
            $text .= $form->dropDown('Action', 'action', $this->options['action'], $actionArray);
            $text .= $form->textField('Top Position', 'top', $this->options['top']);
            $text .= $form->textField('Left Position', 'left', $this->options['left']);
            $text .= $form->dropDown('Fixed Position', 'fixed', $this->options['fixed'], $fixedArray);
            
            $text .= $form->endForm();
            $text .= '</div>';
            print($text);  
        }        
    }
    
    function footer(){

        $text = "<script type=\"text/javascript\">
            jQuery(function(){
                jQuery('.tabx-div').tabSlideOut({
                    tabHandle: '.handle',                     //class of the element that will become your tab
                    pathToTabImage: '" . $this->options['image'] . "', //path to the image for the tab //Optionally can be set using css
                    imageHeight: '" . $this->options['height'] . "px',   //height of tab image        //Optionally can be set using css
                    imageWidth: '" . $this->options['width'] . "px',    //width of tab image         //Optionally can be set using css
                    tabLocation: '" . $this->options['location'] . "',  //side of screen where tab lives, top, right, bottom, or left
                    speed: " . $this->options['speed'] . ",               //speed of animation
                    action: '" . $this->options['action'] . "',     //options: 'click' or 'hover', action to trigger animation
                    topPos: '" . $this->options['top'] . "px',     //position from the top/ use if tabLocation is left or right
                    leftPos: '" . $this->options['left'] . "px',  //position from left/ use if tabLocation is bottom or top
                    fixedPosition: " . $this->options['fixed'] . "  //options: true makes it stick(fixed position) on scroll
                });
            });
            </script>";
        $text .= '<div class="tabx-div">
            <a class="handle" href="#">' . $this->options['link_text'] . '</a>
            ' . stripslashes($this->options['content']) . '
      
        </div>';
        print($text);
    }
}




  
?>
