<?php

/**
* The administration functions for CrowdX
* 
* @param    object  $wpdb
* @param    array   $options
* @param    string  $baseURL
* @param    string  $pluginURL
* @param    string  $numberPerPage
* @param    array   $bookArray
* @param    string  $status
* @param    array   $filter
*/
  
class crowdx_admin {
    
    var $version        = "0.1";
    var $wpdb;

    var $baseURL        = "tools.php?page=crowdx/includes/crowdx_admin.php";

    
    /**
    * The contstruct function.  Does nothing other than set up variables.
    *
    * @global object wpdb
    */

    function __construct(){
        global $wpdb;
        $this->wpdb    = $wpdb;
    }
    
    function crowdx_upgrade(){

        if ($this->var->options["version"] != $this->version){
            require_once(CROWDX_DIR . "includes/crowdx_upgrade.php");           
            foreach($upgradeArray[$this->var->options["version"]] as $sql){
                $this->wpdb->query($sql);
            }
            update_option('crowdx_options', $this->var->options);
        }
    }

    /**
    * Installs the plugin by creating the page and options
    *
    * @param NULL
    * @return NULL
    */

    function crowdx_install(){
        if (!get_option('crowdx_options')){
            $sql = 'CREATE TABLE IF NOT EXISTS `' . $this->wpdb->prefix . '_cx_user` (  `wp_user_id` int(10) NOT NULL,  `cx_user_active` tinyint(1) NOT NULL DEFAULT \'0\',  UNIQUE KEY `wp_user_id` (wp_user_id`),  KEY `cx_user_active` (`cx_user_active`)) ENGINE=MyISAM DEFAULT CHARSET=latin1;';
            $this->wpdb->query($sql);
        
            $options = array();
            $options['enable'] = false;
            $options['all_users'] = false;
            $options['server'] = 'http://yourserver';
            
            
            update_option('crowdx_options', $options);
        }
    }

    /**
    * Uninstalls the plugin by deleting the options and page
    */

    function crowdx_uninstall(){

    }
    
    function crowdx_run(){
        if ($_POST['submit']){
            
            if (!wp_verify_nonce($_POST["_wpnonce"])){ die('Security check'); }   
            $omit = array('_wpnonce', 'submit');
            
            foreach(array_keys($_POST) as $p){
                if (!in_array($p, $omit)){
                    $this->options[$p] = $_POST[$p];
                }
                
            }
            update_option('crowdx_options', $this->options);
            $status = '<div class="suitexStatus">Options Updated</div>';
        
        }
        $text .= "<link rel='stylesheet' href='" . CROWDX_URL . "suitex/suitex.css' type='text/css' media='all' />";
        $text .= "<div class=\"wrap\">";
        $text .= "<h2>CrowdX Configuration</h2>";
        $text .= "<div id=\"poststuff\" class=\"metabox-holder\">";
        $text .= "<div id=\"post-body\" class=\"has-sidebar\">";
        $text .= "<div id=\"post-body-content\" class=\"has-sidebar-content\">";
        $text .= "<div class=\"postbox\">$status";
        
        $text .= "<div class=\"inside\">";         
        
        require_once(CROWDX_DIR . 'suitex/suitex_form.php');
        $form = new suitex_form();
        $form->startForm($this->baseURL, "crowdxForm");
        $form->hidden("_wpnonce", wp_create_nonce());
        $form->dropDown('Enabled', 'enable', $this->options['enable'], array('Off', 'On'));
        $form->dropDown('All Users', 'all_users', $this->options['all_users'], array('Off', 'On'));
        $form->textField('URL to Crowd Server', 'server', $this->options['server']);
        $form->endForm('Submit');
        $text .= $form->text;
        $text .= '</div></div></div></div></div></div>';
        print($text);
    }
    
    function crowdx_adminMenu(){
        add_management_page('CrowdX', 'CrowdX', 10, __FILE__, array($this, 'crowdx_run')); 
    } 
}
?>