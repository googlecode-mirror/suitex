<?php
class loginX {
    /**
    * The functions for LoginX
    * @global   array   $options
    */
    
    var $fieldOptions = array();
    
    function __construct(){
        global $wpdb;
        $this->wpdb = $wpdb;
        $this->options = get_option('loginx_options');
        
        $this->trans['::URL::'] = get_bloginfo('url');
        $this->trans['::BLOGURL::'] = get_bloginfo('wpurl');
        $this->trans['::BLOGNAME::'] = get_bloginfo('name');
        $this->trans['::BLOGDESC::'] = get_bloginfo('description');
        $this->trans['::DATE::'] = date(get_option('date_format'));
        $this->trans['::TIME::'] = date(get_option('time_format'));
        $this->trans['::ADMINEMAIL::'] = get_bloginfo('admin_email');
    }
    
    function loginx_addCSS(){
        print("<link rel='stylesheet' href='" . LOGINX_URL . "css/loginx.css' type='text/css' media='all' />");      
    }  
    
    function loginx_emailTrans($text, $special=array()){
        $text = strtr($text, array_merge($this->trans, $special));
        return $text;
    }
    
    function loginx_login(){
        global $post;
        if ($post->ID == $this->options['login_page'] || $post->ID == $this->options['register_page']){ 
            wp_redirect(get_permalink($this->options['profile_page']));
            exit;
        }
       
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
            require_once(LOGINX_DIR . 'includes/loginx_register_obj.php');
            $this->registerObj = new loginXRegister();
            $text = $this->registerObj->registerForm();
        
        }
        else if ($post->ID == $this->options['profile_page']){   
            $text = "PROFILE PGE";    
        }
        return $text;
    }
    
    function loginx_getURL(){
        return get_permalink($this->options['login_page']);
    }
    
    function loginx_redirect_login(){
        
        if ($this->options['user_login_redirect'] == 'on'){
            wp_redirect($this->loginx_getURL());  
            exit; 
        }
             
    } 
    
    function loginx_redirect_admin(){
        if ($this->options['user_admin_redirect'] == 'on'){
             if (in_array('subscriber', array($user->roles))){
                wp_redirect(get_permalink($this->options['redirect_admin_page']));   
                exit;
             }
        }
              
    }  
    
    function publicForm($form, $results){

        foreach($results as $row){
            $this->createFieldOptions($row->loginx_field_options);
            $req = $this->getReq($row, $options);
            $min = $this->getMin($row, $options);
            $confirm = $this->getConfirm($row, $options);
                
                
            switch($row->loginx_field_type){
                case 'text':
                    $form->textField($row->loginx_field_label, $row->loginx_field_name, $_POST[$row->loginx_field_name], $req, $min);
                    break;
                    
                case 'pass':
                    $form->password($row->loginx_field_label, $row->loginx_field_name, $req, $min, $confirm);
                    break;
                    
                case 'captcha':
                    $form->reCaptcha($this->options['captcha_public']);
                    break;
                        
                case 'date':
                    $form->dateField($row->loginx_field_label, $loginx_field_name, $_POST[$row->loginx_field_name], $req, true);
                    break;
                        
            }                        
        }        
        return $form;
    } 
    
    function createFieldOptions($opts){
        $this->fieldOptions = array();
        if ($opts != ''){
            $rows = explode("\r\n", $opts);
            foreach($rows as $r){
                $exp = explode(':', $r);
            
                $this->fieldOptions[$exp[0]] = $exp[1];
            }
        }
        
        
    }
    
    function getReq($row){

        if (in_array('req', array_keys($this->fieldOptions))){
            $req = $this->fieldOptions['req'];    
        }
        else { 
            $req = ($row->loginx_field_req == 1) ? true : false;            
        }
        return $req;                
    }
    
    function getMin($row){
        if (in_array('min', array_keys($this->fieldOptions))){
            $min = $this->fieldOptions['min'];    
        }
        else { 
            $min = 6;          
        }
        return $min;
        
    }
    
    function getConfirm($row){
        if (in_array('confirm', array_keys($this->fieldOptions))){
            
            $confirm = true;    
        }
        else { 
            $confirm = false;          
        }
        return $confirm;        
    }        
}
        
        

?>