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
        wp_register_style('loginx-style', plugins_url('css/loginx.css', __FILE__));
        wp_enqueue_style('loginx-style');        
    }  
    
    function loginx_emailTrans($text, $special=array()){
        $text = strtr($text, array_merge($this->trans, $special));
        return $text;
    }
    
    function useWoo(){
        
        $ret = (is_woocommerce_active() && $this->options['use_woo'] == 'on') ? true : false;
        return $ret;
        
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
    
    function loginx_author_url($text){

        global $post;
        $user = get_userdata($post->post_author);
        $text = '<a href="' . get_permalink($this->options['profile_page']) . '?u=' . $user->user_nicename . '">' . $user->display_name . '</a>';
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
    
    function loginx_successMessage($message = '', $transArray = array()){
        if ($message == ''){
            if ($this->successMessage){ return true; }
            return false;
        }
        else if ($message == 'get'){ return $this->successMessage; }
        
        $this->successMessage = strtr($message, $transArray);
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
        if ($this->useWoo()){
            $url = get_permalink(woocommerce_get_page_id('myaccount'));
        }    
        else { 
            $url = get_permalink($this->options['register_page']) . '/' . $extra;
        }
        return $url;
    }
    
    function loginx_getURL($extra=''){
        //if ($this->useWoo()){
        //    return get_permalink(woocommerce_get_page_id('myaccount'));   
        //}
        return get_permalink($this->options['login_page']) . '/' . $extra;
    }
    
    function loginx_redirect_login(){
        
        if ($this->options['user_login_redirect'] == 'on'){
            if ($_GET['action'] == 'lostpassword'){
                $url = $this->loginx_getURL('?password=1');
            }
            else if ($this->useWoo()){
                $url = get_permalink(woocommerce_get_page_id('myaccount'));
            }    
            else { 
                $url = $this->loginx_getURL();
            }       
            wp_redirect($url);  
            exit; 
        }
             
    } 
    
    function loginx_redirect_admin(){
        global $current_user;
        get_currentuserinfo();
        
        if ($this->options['user_admin_redirect'] == 'on'){
             if ($current_user->user_level < 10){
                 if ($this->useWoo()){
                     $page_id = woocommerce_get_page_id('myaccount');
                 }
                 else { 
                     $page_id = $this->options['redirect_admin_page'];
                 }
                 wp_redirect(get_permalink($page_id));   
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
        
        $form->hidden('loginx_profile', '1');
        
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
        if ($this->options['woo_login_widget'] == 'on'){
            print('<a href="' . $this->loginx_getRegisterURL() . '">Register?</a><br />');
        
            if (function_exists('rpx_init')){
                print('Log in with:' . do_shortcode('[rpxlogin]'));    
            }        
        }
    }    
    
    function wcLoginWidgetLinks($links){
        if ($this->options['woo_login_widget'] == 'on'){
            global $current_user;
            get_currentuserinfo();
        
        
        
            $logout = array_pop($links);
            $password = array_pop($links);
        
        
            $links['My profile'] = $this->loginx_getURL();
        
        
            if ($current_user->user_level > 1){
                $links['Site Admin'] = get_admin_url();    
            }
            $links[__('Course Site', 'woocommerce')] = 'http://course.suitex.com';
            $links[__('Logout', 'woocommerce')] = $logout;
        }
        return $links;
    }   
    
    function loginx_login_hook(){
        if ($this->useWoo()){
            $user_check = get_userdatabylogin($_POST['username']);
            if (!$this->checkActKey($user_check->ID)){
                global $woocommerce;
                $woocommerce->add_error($this->loginx_errorMessage('get'));
                wp_redirect(get_permalink(woocommerce_get_page_id('myaccount')));
                exit;
            }
        } 
             
    } 
    
    function checkActKey($user_id){
        $count = $this->wpdb->get_var('select count(*) from ' . $this->wpdb->prefix . 'loginx_key where act = 1 and user_id = ' . $user_id . ' limit 1');
        if ($count == 0){
            return true;            
        }
        
        $this->wpdb->query('delete from ' . $this->wpdb->prefix . 'loginx_key where user_id = ' . $user_id . ' and act = 2');
        $resendKey = substr(md5(uniqid(NONCE_KEY)), 0, 25);
        $this->wpdb->insert($this->wpdb->prefix . 'loginx_key', array('user_id' => $user_id, 'act' => 2, 'loginx_expire' => 0, 'loginx_key' => $resendKey));
        $this->loginx_errorMessage($this->loginx_emailTrans($this->options['not_active'], array('::LINK::' => get_permalink($this->options['login_page']) . '?resend=' . $resendKey . '&nonce=' . wp_create_nonce('loginx_resend'))));
        return false;
    }
    
    function woo_register($user_id) {
        
        if ($this->useWoo()){
            if ($user_id) {
                
                if (isset($_POST['first_name'])) update_user_meta( $user_id, 'first_name', $_POST['first_name']);
                if (isset($_POST['last_name'])) update_user_meta( $user_id, 'last_name', $_POST['last_name']);
                
                if ($this->options['email_valid'] == 'on'){
                    $actKey = substr(md5(microtime() . NONCE_SALT), 5, 15);
                    $this->wpdb->insert($this->wpdb->prefix . 'loginx_key', array('user_id' => $user_id, 'loginx_key' => $actKey, 'loginx_expire' => 0, 'act' => 1));
                    
                    $subject = $this->loginx_emailTrans($this->options['act_email_subject']);
                    $message = $this->loginx_emailTrans($this->options['act_email_text'], array('::LINK::' => get_permalink($this->options['login_page']) . '?act=' . $actKey));
                    
                    wp_mail($_POST['user_email'], $subject, $message);
                    
                    do_action( 'woo_register_created_prospect', $user_id );
                    
                    $i = wp_nonce_tick(); 
                    $nonce = substr(wp_hash($i . 'dit_logout' . 0, 'nonce'), -12, 10);
                    wp_redirect(get_permalink(woocommerce_get_page_id('myaccount')) . '?cs_error=' . $this->options['register_success_message'] . '&_nonce=' . $nonce);
                        
                    exit;             
                }
                else {
                    wp_redirect(get_permalink($this->options['profile_page']));
                }

            } 
            else {
                return false;  
            }
  
        }
    }    
    
        
}
        
        

?>