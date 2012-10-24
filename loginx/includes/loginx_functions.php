<?php
class loginX {
    /**
    * The functions for LoginX
    * @global   array   $options
    */
    
    var $fieldOptions = array();
    var $showPasswordForm = false;
    
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
        if ($post){
            if (($post->ID == $this->options['login_page'] || $post->ID == $this->options['register_page']) && is_user_logged_in()){ 
                print('<script language="javascript">window.location = "' .  get_permalink($this->options['profile_page']) . '";</script>');
                exit;
            }            
        }        
        
        $this->updateUserPassword();
        
        require_once(LOGINX_DIR . 'includes/loginx_login_obj.php');
        $this->loginObj = new loginXLogin();
        $this->loginObj->login();
        
    }
    
    function updateUserPassword(){
        if ($_POST['loginx_password'] != 1){
            return false;
        }
        if (!wp_verify_nonce($_POST['nonce'], 'loginx_password')){
            $this->loginx_errorMessage('Security Token Mismatch');
            $this->showPasswordForm = true;
            return false;
        }  
        
        if ($_POST['user_pass'] != $_POST['user_pass_confirm']){
            $this->loginx_errorMessage('Passwords did not match');
            $this->showPasswordForm = true;
            return false;
        }
        
        global $current_user;
        get_currentuserinfo();        
        
        wp_update_user(array('ID' => $current_user->ID, 'user_pass' => $_POST['user_pass']));
        
        wp_mail($current_user->user_email, $this->options['email_password_was_reset_subject'], $this->loginx_emailTrans($this->options['email_password_was_reset']));
        wp_redirect(get_permalink($this->options['profile_page']) . '?password=1&c=1');
    }
    
    function loginx_comment_url($text){
        global $comment;
        $user = get_userdata($comment->user_id); 
        $text = get_permalink($this->options['profile_page']) . '?u=' . $user->user_nicename;
        return $text;
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
            require_once(LOGINX_DIR . 'includes/loginx_profile_obj.php');
            $this->profileObj = new loginXProfile();
            $this->profileObj->password = $this->showPasswordForm;
            $this->profileObj->errorMessage = $this->errorMessage;
            $text = $this->profileObj->init();
        }
        return $text;
    }

    function loginx_getRegisterURL($extra=''){
        return get_permalink($this->options['register_page']) . '/' . $extra;
    }
    
    function loginx_getURL($extra=''){
        return get_permalink($this->options['login_page']) . '/' . $extra;
    }
    
    function loginx_redirect_login(){
        
        if ($this->options['user_login_redirect'] == 'on'){
            if ($_GET['action'] == 'lostpassword'){
                $extra = '?password=1';
            }
            
            
            wp_redirect($this->loginx_getURL($extra));  
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
    
    function setFormValue($obj){
        $this->userObj = $obj;
    }
    
    function getFormValue($field){
        if (isset($_POST['loginx_form'])){
            $usePost = true;
            $value = $_POST[$field];
        }
        else { 
            $value = $this->userObj->$field;
        }
        return $value;
    }
    
    function publicForm($form, $results, $register=true){
        
        foreach($results as $row){
            $this->createFieldOptions($row->loginx_field_options);
            $req = $this->getReq($row);
            $min = $this->getMin();
            $disabled = ($row->loginx_field_no_edit == 1) ? true : false;

                
                
            switch($row->loginx_field_type){
                case 'text':
                    $form->textField($row->loginx_field_label, $row->loginx_field_name, $this->getFormValue($row->loginx_field_name), $req, $min, $disabled);
                    break;
                    
                case 'pass':
                    if ($register != true){ break; }
                    $form->password($row->loginx_field_label, $row->loginx_field_name, $req, $min, $confirm);
                    break;
                    
                case 'captcha':
                    $form->reCaptcha($this->options['captcha_public']);
                    break;
                        
                case 'date':
                    $form->dateField($row->loginx_field_label, $row->loginx_field_name, $this->getFormValue($row->loginx_field_name), $req, true);
                    break;
                
                case 'drop':
                    $list = $this->createList($row->loginx_field_options);
                    $blank = $this->getBlank();
                    $multi = $this->getMulti();
                    $form->dropDown($row->loginx_field_label, $row->loginx_field_name, $this->getFormValue($row->loginx_field_name), $this->listOptions, $blank, $req, $multi);
                    break;
                
                case 'area':
                    $form->textArea($row->loginx_field_label, $row->loginx_field_name, $this->getFormValue($row->loginx_field_name));
                    break;
                    
                case 'check':
                    $form->checkBox($row->loginx_field_label, $row->loginx_field_name, $this->getFormValue($row->loginx_field_name), $req);
                    break;
                
                case 'radio':
                    break;
                        
            }                        
        }        
        return $form;
    } 
    
    function createList($opts){
        $checkArray = array('multi', 'blank', 'req');
        $this->listOptions = array();
        if ($opts != ''){
            $rows = explode("\r\n", $opts);
            foreach($rows as $r){
                if (substr($r, 0, 5) != 'multi' && substr($r, 0, 5) != 'blank' && substr($r, 0, 3) != 'req' && substr_count($r, '|') != 0){
                    $e = explode('|', $r);
                    $this->listOptions[$e[0]] = $e[1];
                }
            }
        }
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
    
    function getMulti(){
        $multi = false;
        if (in_array('multi', array_keys($this->fieldOptions))){
            $multi = true;
        }
        return $multi;
    }
    
    function getBlank(){
        $blank = false;
        if (in_array('blank', array_keys($this->fieldOptions))){
            $blank = true;
        }
        return $blank;
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
    
    function getMin(){
        if (in_array('min', array_keys($this->fieldOptions))){
            $min = $this->fieldOptions['min'];    
        }
        else { 
            $min = 6;          
        }
        return $min;
        
    }
    
    function getConfirm(){
        if (in_array('confirm', array_keys($this->fieldOptions))){
            $confirm = true;    
        }
        else { 
            $confirm = false;          
        }
        return $confirm;        
    }  
    
    function loginx_rpx_avatar_filter($avatar){   
        
        if (!function_exists('rpx_configured')){ return $avatar; }
        $rpx_avatar_option = get_option(RPX_AVATAR_OPTION);
        if ($rpx_avatar_option != 'true'){
            return $avatar;
        } 
        
        $rpx_avatar = $avatar;
        $rpx_photo = '';
        if (in_the_loop() != false){  
            $zero = 0;
            $comment = get_comment($zero);  
            
            $user_id = ($comment == '') ? $GLOBALS['avatar_user_id'] : $comment->user_id;
            if (!is_wp_error($user_id)){  

                $user = get_userdata($user_id);
                if (!is_wp_error($user)){ 
                    if (isset($user->rpx_photo)){
                        $rpx_photo = $user->rpx_photo;
                    }
                }
            }
        }    
        if ( !empty($rpx_photo) ) {     
            $avatar = str_replace("'", '"', $avatar);
            $pattern = '/src="[^"]*"/';
            $replace = 'src="'.$rpx_photo.'"';
            $rpx_avatar = preg_replace($pattern, $replace, $avatar);
        }
        return $rpx_avatar;
    }
    
    function wcLoginWidget(){
        print('<a href="' . $this->loginx_getRegisterURL() . '">Register?</a><br />');
        
        if (function_exists('rpx_init')){
            print('Log in with:' . do_shortcode('[rpxlogin]'));    
        }        
    }    
    
    function wcLoginWidgetLinks($links){
        $logout = array_pop($links);
        
        
        
        $links['My Profile'] = $this->loginx_getURL();
        $links[__('Logout', 'woocommerce')] = $logout;
        
        return $links;
    }     
}
        
        

?>