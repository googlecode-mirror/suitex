<?php
class loginXRegister extends loginX {
    
    var $fieldOptions = array();
    
    function __construct(){
        parent::__construct();
    }
        
    function registerForm(){
        if ($_POST['submit']){
            if (!wp_verify_nonce($_POST['nonce'], 'loginx_register')){
                parent::loginx_errorMessage('Security Token Mismatch');
            }            
            else if (username_exists($_POST['user_login'])){ 
                parent::loginx_errorMessage('Username Exists.  Do you want to <a href="' . $this->loginx_getURL() . '">Login?</a>');
                $_POST['user_login'] = '';
            }
            else if (email_exists($_POST['user_email'])){
                parent::loginx_errorMessage('Email Exists.  Do you want to <a href="' . $this->loginx_getURL() . '">Login?</a>');
                $_POST['user_email'] = '';
            }
            
            
            else { 
                //CAPTCHA
                $omit = array('submit', 'nonce', 'user_pass_confirm', 'captcha');
                $wpFields = array();
                $createArray = array();
                $metaArray = array();
                $results = $this->wpdb->get_results('select loginx_field_name from ' . $this->wpdb->prefix . 'loginx_field where loginx_field_wp = 1');
                foreach($results as $row){
                    $wpFields[] = $row->loginx_field_name;                   
                }
                foreach($_POST as $k => $v){
                    if (!in_array($k, $omit)){
                        if (in_array($k, $wpFields)){
                            $createArray[$k] = $v;
                        }    
                        else { 
                            $metaArray[$k] = $v;
                        }
                    }
                }
                
                $user_id = wp_insert_user($createArray);
                foreach($metaArray as $k => $v){
                    add_user_meta($user_id, $k, $v, true);
                }
                
                if ($this->options['email_valid'] == 'on'){
                    $actKey = substr(md5(microtime() . NONCE_SALT), 5, 15);
                    add_user_meta($user_id, 'act_key', $actKey, true);
                    
                    
                    
                    
                    wp_mail($_POST['user_email'], get_bloginfo('name') . ' ' . $this->options['act_email_subject'], $this->options['act_email_text']);
                    
                    parent::loginx_successMessage('You have been registered<br />Please check your email for activation instructions.');
                    $text = '<div id="loginx_form">' . parent::loginx_successMessage() . '</div>';
                    return $text;                 
                }
                else {
                    wp_redirect(get_permalink($this->options['profile_page']));
                }
            }
           
            
        }   
        else if ($_GET['act']){
            //check activation key for user
            
        }
        
        require_once(PHPX_DIR . 'phpx_form.php');
        $form = new phpx_form();
        $form->startForm(get_permalink(), 'loginxRegisterForm');
        $form->hidden('nonce', wp_create_nonce('loginx_register'));
        if (parent::loginx_errorMessage()){
            $form->freeText(parent::loginx_errorMessage('get'), 'loginx_error');
        }            
        $results = $this->wpdb->get_results('select loginx_field_name, loginx_field_label, loginx_field_options, loginx_field_type, loginx_field_req from ' . $this->wpdb->prefix . 'loginx_field where loginx_field_reg = 1 order by loginx_field_ord asc');
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
        $text = '<div id="loginx_form">' . $form->endForm() . '</div>';
        return $text;
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
