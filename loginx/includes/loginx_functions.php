<?php
class loginX {
    /**
    * The functions for LoginX
    * @global   array   $options
    */
    
    function __construct(){
        global $wpdb;
        $this->wpdb = $wpdb;
        $this->options = get_option('loginx_options');

    }
    
    function loginx_addCSS(){
        print("<link rel='stylesheet' href='" . LOGINX_URL . "css/loginx.css' type='text/css' media='all' />");      
    }  
    
    function loginx_login(){
        require_once(LOGINX_DIR . 'includes/loginx_login_obj.php');
        $this->loginObj = new loginXLogin();
        $this->loginObj->login();
        
    }
    
    function loginx_errorMessage($message = ''){
        if ($message == ''){
            if ($this->errorMessage){ return true; }
            return false;
        }
        else if ($message == 'get'){ return $this->errorMessage; }
        $this->errorMessage = str_replace(get_bloginfo('wpurl') . '/wp-login.php?action=lostpassword', $this->loginx_getURL() . '?password=1', $message);
    }
    
    function loginx_successMessage($message = ''){
        if ($message == ''){
            if ($this->successMessage){ return true; }
            return false;
        }
        else if ($message == 'get'){ return $this->successMessage; }
        
        $this->successMessage = $message;
    }
    
    function loginx_content($text){
        global $post;
        if ($post->ID == $this->options['login_page']){        
            if (!is_object($this->loginObj)){
                require_once(LOGINX_DIR . 'includes/loginx_login_obj.php');
                $this->loginObj = new loginXLogin();
            }
            $text = $this->loginObj->loginForm();
            
        }
        else if ($post->ID == $this->options['register_page']){ 
        
        }
        else if ($post->ID == $this->options['profile_page']){   
            
        }
        return $text;
    }
    
    function loginx_getURL(){
        return get_permalink($this->options['login_page']);
    }
    
    function loginx_redirect_login(){
        wp_redirect($this->loginx_getURL());  
        exit;      
    } 
}
        
        

?>