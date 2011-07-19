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
        
    
        
        $param1 = array('in0' => array('name'               => $this->options['app_name'],
                                      'token'               => $resp->out->token),
                       'in1' => array('application'         => $this->options['app_name'],
                                      'credential'          => array('credential' => $_POST['pwd']),
                                      'name'                => $_POST['log'],
                                      'validationFactors'   => array()));

									                                    
        try {
            $resp1 = $client->authenticatePrincipal($param1);
            

        }
        catch (SoapFault $fault) {
            wp_clear_auth_cookie(); 
            return false;
        }
        
        $username = sanitize_user($_POST['log']);
        $user = get_userdatabylogin($username);
        
        if (!$user && $this->options['add_users'] == 1){
			$param2 = array('in0' => array('name'               => $this->options['app_name'],
            							   'token'               => $resp->out->token),
                            'in1' => $resp1->out);
			$resp2 = $client->findPrincipalByToken($param2);
            
            print_r($resp2);
            print("<BR><BR><BR>");
            print("HERE");
            //print_r($resp2->out->attributes->SOAPAttribute);
            foreach($resp2->out->attributes->SOAPAttribute as $attr){
                print_r($attr);
                print("<br><br><br>");
            }
            die();
            
            
            
            $id = wp_create_user($_POST['log'], $_POST['pwd'], $resp2->mail->values->string);
            
			
			
			
			//ADD THE USER AND THEN LOGIN
        }
        else if (!$user){
        	//make pretty error message
			return false;
        }
		
		
		if ($_SERVER["SERVER_PORT"] == "443"){ $secure = true; }
        else { $secure = false; }
        
        
        wp_set_auth_cookie($user->ID, true, $secure);
        do_action('wp_login', $user->user_login);                
        
        $url = ($_POST['redirect_to'] != '') ? urldecode($_POST['redirect_to']) : 'wp-admin/index.php';
        
           
        header("Location: $url");
        exit();        
    }
    
    function crowdx_addUser(){
		
    }
    
    function multix_checkUser(){
        $user = get_userdatabylogin($_POST["log"]);
        return new WP_User($user->ID); 
    }
    
 
}

?>
