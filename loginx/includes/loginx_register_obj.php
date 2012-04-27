<?php
class loginXRegister extends loginX {
    function __construct(){
        parent::__construct();
    }
        
    function registerForm(){
        if ($_POST['']){
            //check username
            //check email
            
            
            
            //email_valid
            //otherwise go to profile
        }   
        else {
            require_once(PHPX_DIR . 'phpx_form.php');
            $form = new phpx_form();
            $form->startForm(get_permalink(), 'loginxRegisterForm');
            //START WITH DATABASE
            
            
            
            $form->textField('Username', 'username', '', true, 6);
            $form->textField('Email', 'email', '', 'email', 6);
            $form->password('Password', 'password', true, 6);
            $form->password('Confirm Password', 'password_1', true, 6, true);
            if ($this->options['captcha_public'] != ''){ 
                $form->reCaptcha($this->options['captcha_public']);
            }
            
            
            //WP Fields??
            
            //CUSTOM FIELDS
            
            
            
            
            
            
            $text = '<div id="loginx_form">' . $form->endForm() . '</div>';
            
            
            
        } 
        return $text;
    }    
}  
?>
