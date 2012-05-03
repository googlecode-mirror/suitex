<?php
class loginXLogin extends loginX {
    
    function __construct(){
        parent::__construct();
    }
    
    function loginForm(){
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
            $form->textField('Email', 'email', '', true);    
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
            $form->checkBox('Remember Me?', 'remember', 0);
            $form->hidden('nonce', wp_create_nonce('loginx'));
            $form->freeText('<div id="loginx_password_link"><a href="' . get_permalink() . '?password=1">Forgot Login/Password?</a></div>');
            $form->freeText('<div id="loginx_register_link"><a href="' . get_permalink($this->options['register_page']) . '">Register</a></div>');
            if (function_exists('rpx_init')){
                $form->freeText(do_shortcode('[rpxlogin]'));    
            }
            
            $text = '<div id="loginx_form">' . $form->endForm() . '</div>';
        } 
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
                    ///REIVEW_**********************************
                    //check for activated user, and show form to resend email if they  havent activated
                    if ($_GET['password']){
                        if ($user_id = email_exists($_POST['email'])){
                            parent::loginx_successMessage($this->options['check_email_password']);     
                            $key = substr(md5(microtime() . '984ail23623436adsf$$ad34qKLJKLJ$jo3i4kjhlkaklj6t'), 5, 25);
                            $this->wpdb->query($this->wpdb->prepare('insert into ' . $this->wpdb->prefix . 'loginx_key (user_id, loginx_key, loginx_expire) values (%d, %s, %d)', $user_id, $key, time() + 86400));
                            $subject = parent::loginx_emailTrans($this->options['email_password_reset_subject']);
                            $message = parent::loginx_emailTrans($this->options['email_password_reset'], array('::LINK::' => get_permalink($this->options['page_id']) . '?reset=' . $key));                                  wp_mail($_POST['email'], $subject, $message, $headers);
                        }   
                        else {
                            
                            parent::loginx_errorMessage('Email not Found.');
                        }
                    }
                    else if ($_POST['reset']){
                        $user_id = $this->wpdb->get_var($this->wpdb->prepare('select user_id from ' . $this->wpdb->prefix . 'loginx_key where loginx_key = %s and loginx_expire > %d limit 1', $_POST['reset'], time()));
                        if (!$user_id){
                            parent::loginx_errorMessage('Bad Key or Key as Expired.  Please try to reset your password again.');
                        } 
                        else {
                            wp_update_user(array('ID' => $user_id, 'user_pass' => $_POST['pass']));
                            $this->wpdb->query($this->wpdb->prepare('delete from ' . $this->wpdb->prefix . 'loginx_key where user_id = %d', $user_id));
                            parent::loginx_successMessage('Your password has been updated');
                        }                       
                    }
                    else { 
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
   
    }   
    
   
}
?>
