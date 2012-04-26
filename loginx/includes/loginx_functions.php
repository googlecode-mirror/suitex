<?php
class LoginX {
    /**
    * The functions for LoginX
    * @global   array   $options
    */
    
    function __construct(){
        global $wpdb;
        $this->wpdb = $wpdb;
        
        $this->options = get_option('loginx_options');

    }

    function loginx_install(){
        if (!is_plugin_active('phpx/phpx.php')){
            die('LoginX requires the PHPX Framework.  Please install PHPX and then reinstall LoginX.');
        }
        $this->wpdb->query('CREATE TABLE `' . $this->wpdb->prefix . 'loginx_key` (`user_id` INT( 10 ) NOT NULL ,`loginx_key` VARCHAR( 32 ) NOT NULL ,`loginx_expire` INT( 11 ) NOT NULL ,INDEX ( `loginx_key` , `loginx_expire` )) ENGINE = MYISAM');
        
        
        
        $page                   = array();
        $page['post_type']      = 'page';
        $page['post_title']     = 'Login';
        $page['post_name']      = 'login';
        $page['post_status']    = 'publish';
        $page['comment_status'] = 'closed';
        $page['post_content']   = 'This page is used to display your Login Form via LoginX.';

        $login_id = wp_insert_post($page);
        $page                   = array();
        $page['post_type']      = 'page';
        $page['post_title']     = 'Register';
        $page['post_name']      = 'register';
        $page['post_status']    = 'publish';
        $page['comment_status'] = 'closed';
        $page['post_content']   = 'This page is used to display your Register via LoginX.';

        $register_id = wp_insert_post($page);
        $page                   = array();
        $page['post_type']      = 'page';
        $page['post_title']     = 'Profile';
        $page['post_name']      = 'profile';
        $page['post_status']    = 'publish';
        $page['comment_status'] = 'closed';
        $page['post_content']   = 'This page is used to display your Profile via LoginX.';

        $profile_id = wp_insert_post($page);        
        $options =  array('login_page' => $page_id, 'login' => true, 'register' => true, 'profile' => true, 'register_page' => $register_id, 'profile_id' => $profile_id);
        
        update_option('loginx_options', $options);
                

    }
    
    function loginx_uninstall(){
        wp_delete_post($this->options['page'], true);
        delete_option('loginx_options');
    }
    
    function loginx_addCSS(){
        print("<link rel='stylesheet' href='" . LOGINX_URL . "css/loginx.css' type='text/css' media='all' />");      
    }  
    
    
    
    function loginx_login(){
        global $post;
        if ($post->ID == $this->options['page']){
        
            if ($_POST['nonce']){
                if (!wp_verify_nonce($_POST['nonce'], 'loginx')){
                    $this->errorMessage = 'Security Token Mismatch';
                }  
                else { 
                    
                    if ($_GET['password']){
                        if ($user_id = email_exists($_POST['email'])){
                            $this->successMessage = 'Check your email for a link with which to reset your password.  The link only be valid for 24 hours.';     
                            $key = substr(md5(microtime() . '984ail23623436adsf$$ad34qKLJKLJ$jo3i4kjhlkaklj6t'), 5, 25);
                            $this->wpdb->query($this->wpdb->prepare('insert into ' . $this->wpdb->prefix . 'loginx_key (user_id, loginx_key, loginx_expire) values (%d, %s, %d)', $user_id, $key, time() + 86400));
                            
                            $link = get_permalink($this->options['page_id']) . '?reset=' . $key;
                            //$message = str_replace('::LINK::', $link, $this->options['email_text']);
                            $message = 'A request has been processed to reset your email.  If you did not request this, you can safely disregard this email.  Otherwise, please follow the link below within the next 24 hours in order to select a new password:<br /><br /><a href="' . $link . '">' . $link . '</a><br /><br />Thank you.';
                            $headers = 'From: ' . get_bloginfo('name') . ' <' . get_bloginfo('admin_email') . '>' . "\r\ncontent-type: text/html\r\n";
                            print($message);
                            //wp_mail($_POST['email'], get_bloginfo('name') . ' Password Reset Request', $message, $headers);
                        }   
                        else {
                            
                            $this->errorMessage = 'Email not Found.';
                        }
                    }
                    else if ($_POST['reset']){
                        $user_id = $this->wpdb->get_var($this->wpdb->prepare('select user_id from ' . $this->wpdb->prefix . 'loginx_key where loginx_key = %s and loginx_expire > %d limit 1', $_POST['reset'], time()));
                        if (!$user_id){
                            $this->errorMessage = 'Bad Key or Key as Expired.  Please try to reset your password again.';
                        } 
                        else {
                            wp_update_user(array('ID' => $user_id, 'user_pass' => $_POST['pass']));
                            $this->wpdb->query($this->wpdb->prepare('delete from ' . $this->wpdb->prefix . 'loginx_key where user_id = %d', $user_id));
                            $this->successMessage = 'Your password has been updated';
                        }                       
                    }
                    else { 
                        $user = wp_signon(array('user_login' => $_POST['username'], 'user_password' => $_POST['password'], 'remember' => $_POST['remember']), false);
                
                        if (is_wp_error($user)){
                            $this->errorMessage = $user->get_error_message();    
                        }
                        else {
                            if (!in_array('subscriber', array($user->roles))){
                                wp_redirect('/wp-admin');
                            }
                            else if ($_POST['redirect_to'] == $this->loginx_getURL() || $_POST['redirect_to'] == ''){
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
    
    function loginx_content($text){
        global $post;
        if ($post->ID == $this->options['page']){        
            require_once(PHPX_DIR . '/phpx_form.php');
            $form = new phpx_form();
            if ($this->successMessage){
                $text = '<div class="loginx_success">' . $this->successMessage . '</div>';
            }
            else if ($_GET['password'] || $_POST['reset']){

                $form->startForm($this->loginx_getURL() . '?password=1');
                if ($this->errorMessage){
                    $form->freeText($this->errorMessage, 'loginx_error');
                }
                $form->freeText($this->options['password_text']);
                $form->textField('Email', 'email', '', true);    
                $form->hidden('nonce', wp_create_nonce('loginx'));     
                $text = '<div id="loginx_password">' . $form->endForm() . '</div>';                            
            }
            else if ($_GET['reset']){
                $user_id = $this->wpdb->get_var($this->wpdb->prepare('select user_id from ' . $this->wpdb->prefix . 'loginx_key where loginx_key = %s and loginx_expire > %d limit 1', $_GET['reset'], time()));
                if (!$user_id){
                    $text = '<div class="loginx_error">Bad Key or Key as Expired.  Please try to reset your password again.</div>';
                }
                else { 
                    $form->startForm($this->loginx_getURL());
                    if ($this->errorMessage){
                        $form->freeText($this->errorMessage, 'loginx_error');
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
                if ($this->errorMessage){
                    $form->freeText($this->errorMessage, 'loginx_error');
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
            
        }
        
        
        return $text;
    }
    
    function loginx_getURL(){
        return get_permalink($this->options['page']);
    }
    
    function loginx_admin(){
        if ($_POST['nonce']){
            if (!wp_verify_nonce($_POST['nonce'], 'loginx_admin')){
                die('Invalid Security Token');
            }
            $omit = array('submit', 'nonce');
            
            foreach($_POST as $k => $v){
                if (!in_array($k, $omit)){
                    
                    $this->options[$k] = $v;
                }
            }
            
            
            
            $message = true;


            update_option('loginx_options', $this->options);
        }
        
        
        require_once(PHPX_DIR . 'phpx_form.php');
        $form = new phpx_form();
        $pages = get_pages();
        
        foreach($pages as $p){
            $pageArray[$p->ID] = $p->post_title;
        }
        
        $text = '<div class="wrap"><h2>Login X</h2>';
        if ($message){ $text .= '<p>Options Saved</p>'; }
        $text .= $form->startForm('tools.php?page=loginx/includes/loginx_functions.php', 'adbarxForm');        
        $text .= $form->hidden('nonce', wp_create_nonce('loginx_admin'));
        $text .= $form->dropDown('Profile Page', 'profile_page', $this->options['profile_page'], $pageArray, false);
        $text .= $form->dropDown('Register Page', 'register_page', $this->options['register_page'], $pageArray, false);
        $text .= $form->textArea('Password Lookup Text', 'password_text', $this->options['password_text']);
        $text .= $form->endForm();
        $text .= '</div>';
        print($text);
    }   
    
    function loginx_adminMenu(){
        add_management_page('LoginX', 'LoginX', 5, __FILE__, array($this, 'loginx_admin')); 
    }   
    
    function loginx_redirect_login(){
        wp_redirect(get_permalink($this->options['page']));  
        exit;      
    } 
    
    
    
}
        
        

?>