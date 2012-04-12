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
        //create the post
        $page                   = array();
        $page['post_type']      = 'page';
        $page['post_title']     = 'Login';
        $page['post_name']      = 'login';
        $page['post_status']    = 'publish';
        $page['comment_status'] = 'closed';
        $page['post_content']   = 'This page is used to display your Login Form via LoginX.';

        $page_id = wp_insert_post($page);
        update_option('loginx_options', array('page' => $page_id));
                
        if (!is_plugin_active('phpx/phpx.php')){
            die('LoginX requires the PHPX Framework.  Please install PHPX and then reinstall LoginX.');
        }
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
                    $user = wp_signon(array('user_login' => $_POST['username'], 'user_password' => $_POST['password'], 'remember' => $_POST['remember']), false);
                
                    if (is_wp_error($user)){
                        $this->errorMessage = $user->get_error_message();    
                    }
                    else {
                        if ($_POST['redirect_to'] == $this->loginx_getURL() || $_POST['redirect_to'] == ''){
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
    
    function loginx_content($text){
        global $post;
        if ($post->ID == $this->options['page']){        
            require_once(PHPX_DIR . '/phpx_form.php');
            $form = new phpx_form();
            $form->startForm($this->loginx_getURL());
            if ($this->errorMessage){
                $form->freeText($this->errorMessage, 'loginx_error');
            }
            $form->textField('Username', 'username', '', true);
            $form->password('Password', 'password', true, 4);
            $form->checkBox('Remember Me?', 'remember', 0);
            $form->hidden('nonce', wp_create_nonce('loginx'));
        
            $text = $form->endForm();
        
            
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
            $this->options['profile_page'] = $_POST['profile_page'];           
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
        $text .= $form->dropDown('Profile Page', 'profile_page', $this->options['profile_page'], $pageArray, true);
        $text .= $form->endForm();
        $text .= '</div>';
        print($text);
    }   
    
    function loginx_adminMenu(){
        add_management_page('LoginX', 'LoginX', 5, __FILE__, array($this, 'loginx_admin')); 
    }    
    
    
    
}
        
        

?>