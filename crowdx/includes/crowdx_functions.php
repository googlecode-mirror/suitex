<?php

/**
 * The functions for crowdX
 *
 * @package WordPress
 * @author  Xnuiem
 */

class crowdx_functions {
    
    var $options;
    var $wpdb;
    
    

    /**
    * The construct function for the crowdX class. 
    *
    * @param NULL
    * @return NULL
    */

    function __construct(){
        global $wpdb;
        $this->wpdb = $wpdb;
        
    }
    
    function crowdx_login(){
        print_r($_POST);
        $url = $this->options['server'] . '/rest/usermanagement/latest/authentication?username=' . $_POST['log'];
        <?xml version="1.0" encoding="UTF-8"?>
<password>
  <value>Password</value>
</password>
        print($url);
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_VERBOSE, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fieldString);
        $response = curl_exec($ch);
        print($response);
        flush();
        curl_close($ch);
        
        die("HERE");
        
    }
    
 
}

?>
