<?php

class loginXProfile extends loginX {
    
    function __construct(){
        parent::__construct();
    }
    
    public function init(){
        if (!is_user_logged_in() && ($this->options['anon_profiles'] != 'on' || isset($_GET['edit']) || !is_numeric($_GET['id']) || isset($_GET['password']))){
            print('<script language="javascript">window.location = "' .  get_permalink($this->options['login_page']) . '";</script>');
            exit;
        }

        if ($_GET['edit'] == 1){
            $this->editProfile();
        }  
        else if ($_GET['password'] == 1 || $this->password == true){
            $this->showPasswordForm();
        }  
        else { 
            
            $this->showProfile();
        }
        return $this->text;
    }
    
    function showPasswordForm(){
        if ($_GET['c'] == 1){
            $this->text = '<div id="loginx_form"><p class="loginx_success">' . parent::loginx_emailTrans($this->options['password_change_success_message'], array('::LINK::' => get_permalink($this->options['profile_page']))) . '</p></div>';
            return true;
        }
        
        require_once(PHPX_DIR . 'phpx_form.php');
        $form = new phpx_form();
        $form->startForm(get_permalink(), 'loginxPasswordForm');
        $form->hidden('nonce', wp_create_nonce('loginx_password'));
        $form->hidden('loginx_form', 1);
        $form->hidden('loginx_password', 1);
        if (parent::loginx_errorMessage()){
            $form->freeText(parent::loginx_errorMessage('get'), 'loginx_error');
        }     
        
        $form->password('Password', 'user_pass', true, 6);
        $form->password('Confirm Password', 'user_pass_confirm', true, 6, true);
        $this->text .= '<div id="loginx_form">' . $form->endForm() . '</div>';
        
        
        
        
    }
    
    function showProfile(){
        global $current_user;
        get_currentuserinfo();
        $username = (!isset($_GET['u']) || $_GET['u'] == '') ? $current_user->user_nicename : $_GET['u'];
        

        $user = get_user_by('slug', $username);    
        $GLOBALS['avatar_user_id'] = $user->ID;
        

        $trans['::AVATAR::'] = get_avatar($user->user_email, 92);
        $trans['::DISPLAYNAME::'] = $user->display_name;
        $trans['::REGDATE::'] = date(get_option('date_format'), strtotime($user->user_registered));
        $trans['::INFO::'] = $user->user_description;
        if ($user->ID == $current_user->ID){ 
            $trans['::LINKS::'] = '<a href="' . get_permalink($this->options['profile_page']) . '?edit=1">Edit Profile</a> | <a href="' . get_permalink($this->options['profile_page']) . '?password=1">Change Password</a>'; 
        }
        else { 
            $trans['::LINKS::'] = ''; 
        }
        $trans['::POSTS::'] = $this->formatList('Latest Posts', $user, $this->getPosts($user->ID));
        $trans['::COMMENTS::'] = $this->formatList('Latest Comments', $user, $this->getComments($user->ID), 'comment');
        $trans['::PURCHASES::'] = $this->formatList('Latest Purchases', $user, $this->getPurchases($user->ID, 'woo'), 'purchase');
        $this->text = strtr(file_get_contents(LOGINX_DIR . 'templates/showProfile.tpl.php'), $trans);        
    }
    
    function getPurchases($id, $type){
        $data = array();
        if ($this->options['show_purchases'] == 'on'){
            switch($type){
                case 'woo':
                    $results = $this->wpdb->get_results($this->wpdb->prepare('select post_id from ' . $this->wpdb->postmeta . ' where meta_key = %s and meta_value = %d', '_customer_user', $id));
                    if ($results){
                        foreach($results as $row){
                            $meta = get_post_custom($row->post_id);
                            if (in_array('_completed_date', array_keys($meta))){
                                $date = date(get_option('date_format'), strtotime($meta['_completed_date'][0]));
                                foreach($meta['_order_items'] as $item){
                                    
                                    $itemData = unserialize($item);
                                    
                                    foreach($itemData as $i){
                                        $v .= '<a href="' . get_permalink($i['id']) . '">' . $i['name'] . '</a> - <span class="loginx_em"> ' . $date . '</span><br />';
                                    }
                                }
                                $data[] = substr($v, 0, -6);
                            }
                        }
                    }  
                    break;
            }
        } 
        return $data;
    }
    
    function formatList($title, $user='', $data=array(), $type='post'){
        if (count($data) != 0){
            $list = '<div class="loginx_list"><h3>' . $title . '</h3>';
            $list .= '<ul>';
            foreach($data as $d){
                $list .= '<li>' . $d . '</li>';
            }
            $list .= '</ul>';
            switch($type){
                case 'post':
                    $list .= '<a href="/author/' . $user->user_nicename . '">View All Posts</a>';                
                    break;
                    
                case 'comment':
                   // $list .= '<a href="' . get_permalink($this->options['profile_page']) . '?u=' . $user->user_nicename . '&v=c">View All Comments</a>';   
                    break;
            }
            
            
            $list .= '</div>';
        }
        return $list;
    }
    
    function getComments($id){
        $data = array();
        
        $results = $this->wpdb->get_results('select comment_ID, comment_post_ID from ' . $this->wpdb->comments . ' where comment_approved = 1 AND user_id = ' . $id . ' group by comment_post_ID order by comment_date_gmt desc limit 0,10');
        if ($results){
            
            //$data['title'] = __('Latest coments on:', $this->pid);
            foreach($results as $row) {
                $data[] = '<a href="' . clean_url(get_comment_link($row->comment_ID)) . '">' . get_the_title($row->comment_post_ID) . '</a>';
            }
        }
        
        return $data;
    }
    
    function getPosts($id){
        $postArray = get_posts(array('numberposts' => 10, 'author' => $id));
        foreach($postArray as $p){
            $posts[] = '<a href="' . get_permalink($p->ID) . '">' . $p->post_title . '</a>';
        }
        
        return $posts;
    }
    
    function editProfile(){
        global $current_user;
        get_currentuserinfo();
        
        if ($_POST['submit']){
            $cont = true;    
            if (!wp_verify_nonce($_POST['nonce'], 'loginx_profile')){
                parent::loginx_errorMessage('Security Token Mismatch');
                $cont = false;
            }   

            else if ($current_user->user_email != $_POST['user_email']){
                if (email_exists($_POST['user_email'])){
                    parent::loginx_errorMessage('Email already exists.');
                    $cont = false;
                }
                else if ($this->options['email_valid'] == 'on'){
                    $emailVerify = true;
                    $actKey = substr(md5(microtime() . NONCE_SALT), 5, 15);
                    $this->wpdb->insert($this->wpdb->prefix . 'loginx_key', array('user_id' => $user_id, 'loginx_key' => $actKey, 'loginx_expire' => 0, 'act' => 1));
                    
                    $subject = parent::loginx_emailTrans($this->options['act_email_subject']);
                    $message = parent::loginx_emailTrans($this->options['act_email_text'], array('::LINK::' => get_permalink($this->options['login_page']) . '?act=' . $actKey));
                    print($message);
                    //wp_mail($_POST['user_email'], $subject, $message);
                }
                
            }    
            
            if ($cont == true){
                $_POST['user_login'] = $current_user->user_login;
                
                $omit = array('submit', 'nonce', 'user_pass_confirm', 'captcha', 'recaptcha_challenge_field', 'recaptcha_response_field');
                $wpFields = array();
                $createArray = array();
                $metaArray = array();
                $results = $this->wpdb->get_results('select loginx_field_name, loginx_field_wp from ' . $this->wpdb->prefix . 'loginx_field');
                foreach($results as $row){
                    if ($row->loginx_field_wp == 1){
                        $wpFields[] = $row->loginx_field_name;                   
                    }
                    else { 
                        $metaFields[] = $row->loginx_field_name;
                    }
                }
                foreach($_POST as $k => $v){
                    if (!in_array($k, $omit)){
                        if (in_array($k, $wpFields)){
                            if ($_POST[$k] != ''){
                                $updateArray[$k] = $v;
                            }
                        }    
                        else { 
                            $metaArray[$k] = $v;
                        }
                    }
                }
                
                $updateArray['ID'] = $current_user->ID;
                wp_update_user($updateArray);
                
                foreach($metaArray as $k => $v){
                    update_user_meta($current_user->ID, $k, $v);
                }
                
                foreach($metaFields as $m){
                    if (!in_array($m, array_keys($metaArray))){
                        delete_user_meta($current_user->ID, $m);    
                    }
                }
                
                $message = ($emailVerify == true) ? $this->options['profile_email_verify_message'] : '';
                $message .= $this->options['profile_success_message'];
                parent::loginx_successMessage($message);
                
                
            }        
        }
        require_once(PHPX_DIR . 'phpx_form.php');
        $form = new phpx_form();
        $form->startForm(get_permalink() . '?edit=1', 'loginxProfileForm');
        $form->hidden('nonce', wp_create_nonce('loginx_profile'));
        $form->hidden('loginx_form', 1);
        if (parent::loginx_errorMessage()){
            $form->freeText(parent::loginx_errorMessage('get'), 'loginx_error');
        }     
        else if (parent::loginx_successMessage()){
            $form->freeText(parent::loginx_successMessage('get'), 'loginx_success');
        }       
        $results = $this->wpdb->get_results('select loginx_field_name, loginx_field_label, loginx_field_options, loginx_field_type, loginx_field_req, loginx_field_no_edit from ' . $this->wpdb->prefix . 'loginx_field where loginx_field_profile = 1 order by loginx_field_ord asc');        
        
        
        
        
        parent::setFormValue($current_user);
        $form = parent::publicForm($form, $results, false);        
        
        $form->freeText($this->rpx_user_profile());
        
        
        $this->text .= '<div id="loginx_form">' . $form->endForm() . '</div>';
        
        
    }
    
    function rpx_user_profile(){
        if (function_exists('rpx_init')){
            $user_data = rpx_user_data();
            if (!empty($user_data->rpx_provider)){
                $provider = htmlentities($user_data->rpx_provider);
                $text .= "<h3>Currently connected to echo $provider</h3>";
                $removable = get_option(RPX_REMOVABLE_OPTION);
                if ($removable == 'true'){ 
                    $text .= "<p>You can remove all $provider data and disconnect your account from $provider by clicking <a href=\"?action=" . RPX_REMOVE_ACTION . "\">remove</a>.
                    <br><strong>Be certain before you click \"remove\" and set a password for this account so you can use it without social sign in.</strong></p>";
                }
            }
            $ret = $text . rpx_buttons(RPX_BUTTONS_STYLE_LARGE, RPX_CONNECT_PROMPT);
            return $ret;
        }
    }    
}
?>
