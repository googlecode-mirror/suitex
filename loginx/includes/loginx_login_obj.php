<?php
class loginXLogin extends loginX {
    
    function __construct(){
        parent::__construct();
    }
    
    function loginForm(){
        do_action('loginx_before_login_form');
        
        if (parent::useWoo() && !$_GET['password'] && !$_POST['reset'] && !$_GET['reset'] && !$_GET['resend'] && !$_GET['act']){
            print('<script>window.location.href = "' . get_permalink(woocommerce_get_page_id('myaccount')) . '";</script>');
            exit;
        }          
        
        
        
        
        require_once(PHPX_DIR . '/phpx_form.php');
        $form = new phpx_form();
        if (parent::loginx_successMessage()){
            $text = '<div class="loginx_success">' . parent::loginx_successMessage('get') . '</div>';
        }
        else if ($_GET['password'] || $_POST['reset']){

            $form->startForm(parent::loginx_getURL() . '?password=1');
            if (parent::loginx_errorMessage()){
                $form->freeText(parent::loginx_errorMessage('get'), 'loginx_error');
            }
            $form->freeText($this->options['password_text']);
            $form->textField('Email/Username', 'email', '', true);    
            $form->hidden('nonce', wp_create_nonce('loginx'));     
            $text = '<div id="loginx_password">' . $form->endForm() . '</div>';                            
        }
        else if ($_GET['reset']){
            $user_id = $this->wpdb->get_var($this->wpdb->prepare('select user_id from ' . $this->wpdb->prefix . 'loginx_key where loginx_key = %s and loginx_expire > %d limit 1', $_GET['reset'], time()));
            if (!$user_id){
                $text = '<div class="loginx_error">' . $this->options['bad_key'] . '</div>';
            }
            else { 
                $form->startForm(parent::loginx_getURL());
                if (parent::loginx_errorMessage()){
                    $form->freeText(parent::loginx_errorMessage('get'), 'loginx_error');
                }    
                $form->freeText($this->options['password_reset_text']);                
                $form->password('Password', 'pass', true, 6);
                $form->password('Confirm Password', 'pass_confirm', true, 6, true);
                $form->hidden('nonce', wp_create_nonce('loginx'));
                $form->hidden('reset', $_GET['reset']);
                $text = '<div id="loginx_password">' . $form->endForm() . '</div>';  
            }
        }
        else { 
            $form->startForm($this->loginx_getURL());

            if (parent::loginx_errorMessage()){
                $form->freeText(parent::loginx_errorMessage('get'), 'loginx_error');
            }
            
            $form->textField('Username', 'username', '', true);
            $form->password('Password', 'password', true, 4);
            //$form->checkBox('Remember Me?', 'remember', 0);
            $form->hidden('remember', 'forever');
            $form->hidden('nonce', wp_create_nonce('loginx'));
            $form->freeText('<div id="loginx_password_link"><a href="' . get_permalink() . '?password=1">Forgot Login/Password?</a></div>');
            $form->freeText('<div id="loginx_register_link"><a href="' . get_permalink($this->options['register_page']) . '">Register</a></div>');
            if (function_exists('rpx_init')){
                $form->freeText(do_shortcode('[rpxlogin]'));    
            }
            
            $text = '<div id="loginx_form">' . $form->endForm() . '</div>';
        } 
        do_action('loginx_after_login_form');
        return $text;   
    }
    

    
    
    
    function login(){
        global $post;
        if ($post->ID == $this->options['login_page']){
            if ($_POST['nonce']){
                if (!wp_verify_nonce($_POST['nonce'], 'loginx')){
                    parent::loginx_errorMessage('Security Token Mismatch');
                }  
                else { 
                    if ($_GET['password']){
                        $email_user_id = email_exists($_POST['email']);
                        $user_user_id = username_exists($_POST['email']);

                        if ($user_user_id || $email_user_id){
                            $user_id = ($user_user_id > 0)? $user_user_id : $email_user_id;
                            if (parent::checkActKey($user_id)){
                                $user = get_userdata($user_id);
                                parent::loginx_successMessage($this->options['check_email_password'], array('::EMAIL::' => $user->user_email));     
                                $key = substr(md5(microtime() . NONCE_SALT), 5, 25);
                                $this->wpdb->query($this->wpdb->prepare('insert into ' . $this->wpdb->prefix . 'loginx_key (user_id, loginx_key, loginx_expire) values (%d, %s, %d)', $user_id, $key, time() + 86400));
                                $subject = parent::loginx_emailTrans($this->options['email_password_reset_subject']);
                                $message = parent::loginx_emailTrans($this->options['email_password_reset'], array('::LINK::' => get_permalink($this->options['login_page']) . '?reset=' . $key));                     
                                wp_mail($user->user_email, $subject, $message, $headers);
                            }
                        }   
                        else {
                            parent::loginx_errorMessage('Email/Username not Found.');
                        }
                    }
                    else if ($_POST['reset']){
                        $user_id = $this->wpdb->get_var($this->wpdb->prepare('select user_id from ' . $this->wpdb->prefix . 'loginx_key where loginx_key = %s and loginx_expire > %d limit 1', $_POST['reset'], time()));
                        if (!$user_id){
                            parent::loginx_errorMessage('Bad Key or Key as Expired.  Please try to reset your password again.');
                        } 
                        else if (parent::checkActKey($user_id)){
                            wp_update_user(array('ID' => $user_id, 'user_pass' => $_POST['pass']));
                            $this->wpdb->query($this->wpdb->prepare('delete from ' . $this->wpdb->prefix . 'loginx_key where user_id = %d', $user_id));
                            parent::loginx_successMessage($this->options['password_change_success_message'], array('::LINK::' => get_permalink(parent::loginx_getURL())));
                        }                       
                    }
                    else { 
                        $user_check = get_userdatabylogin($_POST['username']);
                        
                        if (parent::checkActKey($user_check->ID)){
                        
                            $user = wp_signon(array('user_login' => $_POST['username'], 'user_password' => $_POST['password'], 'remember' => $_POST['remember']), false);
                        
                            if (is_wp_error($user)){
                                 parent::loginx_errorMessage($user->get_error_message());    
                            }
                            else {
                                
                                if (!in_array('subscriber', array($user->roles))){
                                    wp_redirect('/wp-admin');
                                }
                                else if ($_POST['redirect_to'] == parent::loginx_getURL() || $_POST['redirect_to'] == ''){
                                    wp_redirect(get_permalink($this->options['profile_page']));    
                                }
                                else { 
                                    wp_redirect($_POST['redirect_to']);
                                }
                                exit;
                            }
                        }
                     
                    }
                }
            } 
            else if ($_GET['act'] == 'logout'){
                
                die("HERE");
                
                
                
            }
            else if ($_GET['act']){
                $user_id = $this->wpdb->get_var($this->wpdb->prepare('select user_id from ' . $this->wpdb->prefix . 'loginx_key where loginx_key = %s and act = 1', $_GET['act']));
                if ($user_id > 0){
                    $this->wpdb->query($this->wpdb->prepare('delete from ' . $this->wpdb->prefix . 'loginx_key where loginx_key = %s and user_id = %d and act = 1', $_GET['act'], $user_id));
                    parent::loginx_successMessage($this->options['act_success']);    
                }
                else { 
                    parent::loginx_errorMessage($this->options['act_fail']);
                }
                
            }
            else if ($_GET['resend']){

                if (!wp_verify_nonce($_GET['nonce'], 'loginx_resend')){
                    parent::loginx_errorMessage('Security Token Mismatch');
                }             
                else { 
                    $user_id = $this->wpdb->get_var($this->wpdb->prepare('select user_id from ' . $this->wpdb->prefix . 'loginx_key where loginx_key = %s and act = 2 limit 1', $_GET['resend']));
                    $this->wpdb->query($this->wpdb->prepare('delete from ' . $this->wpdb->prefix . 'loginx_key where user_id = %d and act = 2', $user_id));
                    $actKey = $this->wpdb->get_var($this->wpdb->prepare('select loginx_key from ' . $this->wpdb->prefix . 'loginx_key where user_id = %d and act = 1 limit 1', $user_id));
                    $subject = parent::loginx_emailTrans($this->options['act_email_subject']);
                    $message = parent::loginx_emailTrans($this->options['act_email_text'], array('::LINK::' => get_permalink($this->options['login_page']) . '?act=' . $actKey));

                    wp_mail($_POST['user_email'], $subject, $message);                    
                    parent::loginx_successMessage($this->options['act_key_resent']);                
                }
            } 
        }
   
    }   
    
   
}
?>