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
        
        $client = new SoapClient($this->options['server'] . 'services/SecurityServer?wsdl');
        
        $param = array('in0' => array('credential' => array('credential' => $this->options['app_pass']), 'name' => $this->options['app_name']));
        $resp = $client->authenticateApplication($param);
        
        print_r($resp);
        die();
        
        $param1 = array('in0' => array('name'               => $this->options['app_name'],
                                      'token'               => $resp->out->token),
                       'in1' => array('application'         => $this->options['app_name'],
                                      'credential'          => array('credential' => $_POST['pwd']),
                                      'name'                => $_POST['log'],
                                      'validationFactors'   => array()));
                                      
        try {
            $resp1 = $client->authenticatePrincipal($param1);
            return true;
        }
        catch (SoapFault $fault) {
            wp_clear_auth_cookie(); 
            return false;
        }

        
        
        
        
    }
    
 
}

?>
