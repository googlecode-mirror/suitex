<?php
class loginXAdmin extends loginX {
    
    function __construct(){
        parent::__construct();
    }
        
    function adminForm(){
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
    
    function adminMenu(){
        add_management_page('LoginX', 'LoginX', 5, __FILE__, array($this, 'loginx_admin')); 
    } 
    
    function install(){
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
        $options =  array('login_page' => $page_id, 'login' => true, 'register_page' => $register_id, 'profile_id' => $profile_id);
        
        update_option('loginx_options', $options);
                

    }
    
    function uninstall(){
        wp_delete_post($this->options['login_page'], true);
        delete_option('loginx_options');
    }    
    
    
}  

?>
