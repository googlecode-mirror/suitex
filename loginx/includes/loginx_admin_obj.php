<?php
class loginXAdmin extends loginX {
    
    var $omit = array();
    var $adminPageID = '';
    var $fieldTypes = array();
    
    
    function __construct(){
        parent::__construct();
        $this->omit = array('submit', 'nonce', 'action', 'tab', 'checkFields');   
        $this->fieldTypes = array('text' => 'Text', 'drop' => 'Drop Down', 'check' => 'Check Box', 'radio' => 'Radio', 'area' => 'Text Area', 'date' => 'Date', 'captcha' => 'Captcha', 'pass' => 'Password');
        //do_action('wp_ajax_' . $_POST['action']);
    }

    
    function adminScreen(){
        
        if ($_POST['nonce']){
            $this->checkNonce('loginx_admin');
            
            
            if (!isset($_POST['loginx_field_id'])){
                
                
                foreach($_POST as $k => $v){
                    if (!in_array($k, $this->omit)){  
                        $this->options[$k] = $v;
                    }
                }
                if ($_POST['checkFields'] == 1){
                    $checkFields = array('user_admin_redirect', 'user_login_redirect', 'email_valid', 'anon_fields', 'show_purchases', 'use_woo', 'woo_login_widget');
                    foreach($checkFields as $c){
                        if (!in_array($c, array_keys($_POST))){
                            $this->options[$c] = '';
                        }
                    }
                }
                
                update_option('loginx_options', $this->options);  
                $message = true;

            }   
       
        }        
        
        $text = '<div class="wrap" id="phpxContainer"><h2>Login X</h2>';
        $text .= '<div id="loginx_tabs">
            <ul>
                <li><a href="#tabs-1">General Settings</a></li>
                <li><a href="#tabs-2">Field Configuration</a></li>
                <li><a href="#tabs-3">Templates</a></li>
            </ul>
            <div id="tabs-1">' . $this->adminForm() . '</div>
            <div id="tabs-2">' . $this->fieldForm() . '</div>
            <div id="tabs-3">' . $this->templateForm() . '</div>
        </div></div>';
        
        $text .= '<script language="javascript">
            jQuery(document).ready(function(){
                loginxTabs = jQuery("#loginx_tabs").tabs();';
        if ($_POST['tab']){    
            $text .= 'loginxTabs.tabs(\'select\', ' . $_POST['tab'] . ');';
            
        }
        $text .= '});</script>';
        
        
        print($text);
    }
    
    function templateForm(){

        /*print('array(');
        foreach($this->options as $k => $v){
            print("'$k' => '$v',");
        }
        print(');');
        exit;*/
        require_once(PHPX_DIR . 'phpx_form.php');
        $form = new phpx_form();
        
        $pages = get_pages();
        
        foreach($pages as $p){
            $pageArray[$p->ID] = $p->post_title;
        }
        $adminURL = 'tools.php?page=loginx/includes/loginx_admin_obj.php';
        
        if ($message || $_GET['message']){ $text .= '<p>Options Saved</p>'; }
        $text .= $form->startForm($adminURL, 'loginxForm');        
        $text .= $form->hidden('nonce', wp_create_nonce('loginx_admin'));
        $text .= $form->startFieldSet('Site Messages');
        $text .= $form->textArea('Bad Key/Expired Key Password Reset', 'bad_key', $this->options['bad_key']);
        $text .= $form->textArea('Captcha Fail', 'captcha_fail', $this->options['captcha_fail']);
        $text .= $form->textArea('Check Email for Password Reset', 'check_email_password', $this->options['check_email_password']);
        $text .= $form->textArea('Password Lookup Text', 'password_text', $this->options['password_text']);
        $text .= $form->textArea('Password Reset Text', 'password_reset_text', $this->options['password_reset_text']);
        $text .= $form->textArea('Register Success', 'register_success_message', $this->options['register_success_message']);
        $text .= $form->textArea('Activation Success', 'act_success', $this->options['act_success']);
        $text .= $form->textArea('Activation Failure', 'act_fail', $this->options['act_fail']);
        $text .= $form->textArea('User Not Active', 'not_active', $this->options['not_active']);
        $text .= $form->textArea('Activation Key Resent', 'act_key_resent', $this->options['act_key_resent']);
        
        $text .= $form->textArea('Profile Email Verify Message', 'profile_email_verify_message', $this->options['profile_email_verify_message']);
        $text .= $form->textArea('Profile Success Message', 'profile_success_message', $this->options['profile_success_message']);
        $text .= $form->textArea('Password Change Success Message', 'password_change_success_message', $this->options['password_change_success_message']);
       
        $text .= $form->endFieldSet();
        $text .= $form->startFieldSet('Emails');
        $text .= $form->textField('Password Reset Email Subject', 'email_password_reset_subject', $this->options['email_password_reset_subject']);
        $text .= $form->textArea('Password Reset Email Message', 'email_password_reset', $this->options['email_password_reset']);
        $text .= $form->textField('Activation Email Subject', 'act_email_subject', $this->options['act_email_subject']);
        $text .= $form->textArea('Activation Email Text', 'act_email_text', $this->options['act_email_text']);
        $text .= $form->textField('Password WAS Reset Subject', 'email_password_was_reset_subject', $this->options['email_password_was_reset_subject']);
        $text .= $form->textArea('Password WAS Reset Email', 'email_password_was_reset', $this->options['email_password_was_reset']);
        $text .= $form->hidden('tab', 2);
        $text .= $form->endFieldSet();
        $text .= $form->endForm();
        return $text;        
    }
    
    function adminAjaxFieldList(){
        
        $nonce = wp_create_nonce('loginx_fields');
        $text .= '<a name="customFieldsList"></a><table class="inline"><tr><th>Order</th><th>Name</th><th>Label</th><th>Type</th><th>Required</th><th>On Register</th><th>Profile</th></tr>';
        $results = $this->wpdb->get_results("select * from " . $this->wpdb->prefix . "loginx_field order by loginx_field_ord asc");
        $x = 1;
        $count = count($results);
        foreach($results as $row){
            
            
            
            
            if ($row->loginx_field_mand == 1){
                
                $reg = '<img src="' . LOGINX_URL . 'images/lock.png" border="0" width="16" height="16" alt="Locked" />';  
                $req = '<img src="' . LOGINX_URL . 'images/lock.png" border="0" width="16" height="16" alt="Locked" />';   
                $profile = '<img src="' . LOGINX_URL . 'images/lock.png" border="0" width="16" height="16" alt="Locked" />';   
            }   
            else {

                
                $req = ($row->loginx_field_req == 1) ? '<img src="' . LOGINX_URL . 'images/nav_plain_green.png" border="0" width="16" height="16" alt="Required" />' : '<img src="' . LOGINX_URL . 'images/nav_plain_red.png" border="0" width="16" height="16" alt="Required" />';
                $req = '<a href="javascript:loginx_admin_ajax(\'req\', \'' . $nonce . '\', \'' . $row->loginx_field_id . '\');">' . $req . '</a>';

                $reg = ($row->loginx_field_reg == 1) ? '<img src="' . LOGINX_URL . 'images/nav_plain_green.png" border="0" width="16" height="16" alt="On Register" />' : '<img src="' . LOGINX_URL . 'images/nav_plain_red.png" border="0" width="16" height="16" alt="On Register" />';
                $reg = '<a href="javascript:loginx_admin_ajax(\'reg\', \'' . $nonce . '\', \'' . $row->loginx_field_id . '\');">' . $reg . '</a>';                
                
                $profile = ($row->loginx_field_profile == 1) ? '<img src="' . LOGINX_URL . 'images/nav_plain_green.png" border="0" width="16" height="16" alt="Profile" />' : '<img src="' . LOGINX_URL . 'images/nav_plain_red.png" border="0" width="16" height="16" alt="Profile" />';
                $profile = '<a href="javascript:loginx_admin_ajax(\'profile\', \'' . $nonce . '\', \'' . $row->loginx_field_id . '\');">' . $profile . '</a>';                                
            }
            
            $edit = '<img src="' . LOGINX_URL . 'images/blank.gif" width="16" height="16" />';
            $delete = '<img src="' . LOGINX_URL . 'images/blank.gif" width="16" height="16" />';
            $up = '<img src="' . LOGINX_URL . 'images/blank.gif" width="16" height="16" />';
            $down = '<img src="' . LOGINX_URL . 'images/blank.gif" width="16" height="16" />';
            
            
            
            
            if ($row->loginx_field_wp != 1 && $row->loginx_field_lock != 1){ 
                $edit = '<a href="javascript:loginx_populateAdminForm(\'' . $row->loginx_field_id . '\')"><img src="' . LOGINX_URL . 'images/edit.png" border="0" width="16" height="16" alt="Edit" /></a>';
            }
            if ($row->loginx_field_wp != 1 && $row->loginx_field_lock != 1){ 
                $delete = '<a href="javascript:loginx_confirm_delete(\'' . $nonce . '\', \'' . $row->loginx_field_id . '\');"><img src="' . LOGINX_URL . 'images/delete.png" border="0" width="16" height="16" alt="Delete" /></a>';
            }
            if ($x != 1){
                $up = '<a href="javascript:loginx_admin_ajax(\'up\', \'' . $nonce . '\', \'' . $row->loginx_field_id . '\');"><img src="' . LOGINX_URL . 'images/arrow_up_blue.png" border="0" width="16" height="16" alt="Move Up" /></a>'; 
            }
            
            if ($x != $count){
                $down = '<a href="javascript:loginx_admin_ajax(\'down\', \'' . $nonce . '\', \'' . $row->loginx_field_id . '\');"><img src="' . LOGINX_URL . 'images/arrow_down_blue.png" border="0" width="16" height="16" alt="Move Up" /></a>';
            }

            
            $text .= '<tr id="field_id_' . $row->loginx_field_id . '">';
            $text .= '<td class="field_actions">' . $edit . ' ' .  $delete . ' ' . $down . ' ' . $up . '</td>';
            $text .= '<td class="field_name">' . $row->loginx_field_name . '</td>';
            $text .= '<td class="field_label">' . $row->loginx_field_label . '</td>';
            $text .= '<td class="field_type">' . $this->fieldTypes[$row->loginx_field_type] . '</td>';
            $text .= '<td class="field_req">' . $req . '</td>';
            $text .= '<td class="field_reg">' . $reg . '</td>';
            $text .= '<td class="field_profile">' . $profile . '</td>';
            
            
            
            
            
            $text .= '</tr>';
            $x++;
        }
        
        $text .= '</table>';
        print($text);
        exit;        
    }
    function checkNonce($action){
        if (!wp_verify_nonce($_POST['nonce'], $action)){
            die('Invalid Security Token: ' . $action . ' ' . $_POST['nonce']);
        }        
    }    

    function adminAjaxSubmit(){
        if ($_POST['nonce']){
                        
            if ($_POST['sub'] == 'delete'){
                $this->checkNonce('loginx_fields');
                $this->wpdb->query($this->wpdb->prepare('delete from ' . $this->wpdb->prefix . 'loginx_field where loginx_field_id = %d limit 1', $_POST['id']));
                
            }
            else if ($_POST['sub'] == 'req'){
                $this->checkNonce('loginx_fields');
                $old = $this->wpdb->get_var($this->wpdb->prepare('select loginx_field_req from ' . $this->wpdb->prefix . 'loginx_field where loginx_field_id = %d limit 1', $_POST['id']));
                $data['loginx_field_req'] = ($old == 0) ? 1 : 0;
                $this->wpdb->update($this->wpdb->prefix . 'loginx_field', $data, array('loginx_field_id' => $_POST['id']));                    
            }
            else if ($_POST['sub'] == 'profile'){
                $this->checkNonce('loginx_fields');
                $old = $this->wpdb->get_var($this->wpdb->prepare('select loginx_field_profile from ' . $this->wpdb->prefix . 'loginx_field where loginx_field_id = %d limit 1', $_POST['id']));
                $data['loginx_field_profile'] = ($old == 0) ? 1 : 0;
                $this->wpdb->update($this->wpdb->prefix . 'loginx_field', $data, array('loginx_field_id' => $_POST['id']));                    
            }            
            else if ($_POST['sub'] == 'reg'){
                $this->checkNonce('loginx_fields');
                $old = $this->wpdb->get_var($this->wpdb->prepare('select loginx_field_reg from ' . $this->wpdb->prefix . 'loginx_field where loginx_field_id = %d limit 1', $_POST['id']));
                $data['loginx_field_reg'] = ($old == 0) ? 1 : 0;
                $this->wpdb->update($this->wpdb->prefix . 'loginx_field', $data, array('loginx_field_id' => $_POST['id']));                
            }
            else if ($_POST['sub'] == 'active'){
                $this->checkNonce('loginx_fields');
                $current = $this->wpdb->get_var($this->wpdb->prepare('select loginx_field_active from ' . $this->wpdb->prefix . 'loginx_field where loginx_field_id = %d limit 1', $_POST['id']));
                
                $set = ($current == 1)? 0 : 1;
                $this->wpdb->update($this->wpdb->prefix . 'loginx_field', array('loginx_field_active' => $set), array('loginx_field_id' => $_POST['id']));
            }
            else if ($_POST['sub'] == 'up'){
                $this->checkNonce('loginx_fields');
                $this->wpdb->query($this->wpdb->prepare('update ' . $this->wpdb->prefix . 'loginx_field set loginx_field_ord = loginx_field_ord - 1 where loginx_field_id = %d limit 1', $_POST['id']));
                $ord = $this->wpdb->get_var($this->wpdb->prepare('select loginx_field_ord from ' . $this->wpdb->prefix . 'loginx_field where loginx_field_id = %d limit 1', $_POST['id']));
                $this->wpdb->query($this->wpdb->prepare('update ' . $this->wpdb->prefix . 'loginx_field set loginx_field_ord = loginx_field_ord + 1 where loginx_field_ord = %d and loginx_field_id != %d limit 1', $ord, $_POST['id']));
                
            }
            else if ($_POST['sub'] == 'down'){ 
                $this->checkNonce('loginx_fields');
                $this->wpdb->query($this->wpdb->prepare('update ' . $this->wpdb->prefix . 'loginx_field set loginx_field_ord = loginx_field_ord + 1 where loginx_field_id = %d limit 1', $_POST['id']));
                $ord = $this->wpdb->get_var($this->wpdb->prepare('select loginx_field_ord from ' . $this->wpdb->prefix . 'loginx_field where loginx_field_id = %d limit 1', $_POST['id']));
                $this->wpdb->query($this->wpdb->prepare('update ' . $this->wpdb->prefix . 'loginx_field set loginx_field_ord = loginx_field_ord - 1 where loginx_field_ord = %d and loginx_field_id != %d limit 1', $ord, $_POST['id']));                
                
            } 
            else { 
                
                
                $this->checkNonce('loginx_manage_fields');
                if ($_POST['loginx_field_id'] == 0){
                    foreach($_POST as $k => $v){
                        if (!in_array($k, $this->omit)){  
                            $fieldArray[$k] = $v;
                        }
                    }  
                    $this->wpdb->show_errors();  
                    $max = $this->wpdb->get_var($this->wpdb->prepare('select max(loginx_field_ord) from ' . $this->wpdb->prefix . 'loginx_field'));
                    $fieldArray['loginx_field_ord'] = $max + 1;               
                    $this->wpdb->insert($this->wpdb->prefix . 'loginx_field', $fieldArray);
                }
                else {
                    foreach($_POST as $k => $v){
                        if (!in_array($k, $this->omit)){
                            $fieldArray[$k] = $v;
                        }
                    }                   
                    $this->wpdb->update($this->wpdb->prefix . 'loginx_field', $fieldArray, array('loginx_field_id' => $_POST['loginx_field_id']));                    
                }
            }                        
        }
        else if ($_GET['id']){
            $row = $this->wpdb->get_row($this->wpdb->prepare('select loginx_field_name, loginx_field_label, loginx_field_options, loginx_field_type from ' . $this->wpdb->prefix . 'loginx_field where loginx_field_id = %d limit 1', $_GET['id']), ARRAY_A);
            header('Content-type: application/json');
            print(json_encode($row));
        }
        else {
            print(wp_create_nonce('loginx_manage_fields'));
        }
        exit;  
    }
    
    function adminForm(){
        require_once(PHPX_DIR . 'phpx_form.php');
        $form = new phpx_form();
        $pages = get_pages();
        
        foreach($pages as $p){
            $pageArray[$p->ID] = $p->post_title;
        }
        $adminURL = 'tools.php?page=loginx/includes/loginx_admin_obj.php';
        
        if ($message || $_GET['message']){ $text .= '<p>Options Saved</p>'; }
        $text .= $form->startForm($adminURL, 'loginxForm');        
        $text .= $form->hidden('nonce', wp_create_nonce('loginx_admin'));
        $text .= $form->hidden('checkFields', 1);
        
        $text .= $form->dropDown('Profile Page', 'profile_page', $this->options['profile_page'], $pageArray, true);
        $text .= $form->dropDown('Register Page', 'register_page', $this->options['register_page'], $pageArray, true);
        $text .= $form->dropDown('Login Page', 'login_page', $this->options['login_page'], $pageArray, true);
        $text .= $form->checkbox('Restrict Admin Area', 'user_admin_redirect', $this->options['user_admin_redirect']);
        $text .= $form->dropDown('Redirect Admin Area To', 'redirect_admin_page', $this->options['redirect_admin_page'], $pageArray, true);
        $text .= $form->checkbox('Restrict Wordpress Login Page', 'user_login_redirect', $this->options['user_login_redirect']);
        $text .= $form->checkbox('Require Email Validation on Register', 'email_valid', $this->options['email_valid']);
        $text .= $form->checkbox('Allow Guests to view Profiles', 'anon_profiles', $this->options['anon_profiles']);
        $text .= $form->checkbox('Show Purchases on Profile', 'show_purchases', $this->options['show_purchases']);
        $text .= $form->textField('ReCaptcha Public Key', 'captcha_public', $this->options['captcha_public']);
        $text .= $form->textField('ReCaptcha Private Key', 'captcha_private', $this->options['captcha_private']);
        
        $text .= $form->startFieldSet('WooCommerce');
        $text .= $form->checkbox('Use WooCommerce Login/Registration', 'use_woo', $this->options['use_woo']);
        $text .= $form->checkbox('Add Links to WooCommerce Login Widget', 'woo_login_widget', $this->options['woo_login_widget']);
        $text .= $form->endFieldSet();
        
        
        $text .= $form->endForm();
        return $text;
    }
    
    function fieldform(){        
        require_once(PHPX_DIR . 'phpx_form.php');
        
        $form1 = new phpx_form();        
        
        $form1->labels = false;
        $form1->instantReturn = true;
        
        
        
        
        
        $text .= $form1->startForm($adminURL, 'loginxFieldForm', 'post', false, 'false'); 
        $id = 0;
        $reg = 0;
        $req = 0;
        if ($_GET['action'] == 'edit'){
            $row = $this->wpdb->get_row($this->wpdb->prepare('select * from ' . $this->wpdb->prefix . 'loginx_field where loginx_field_id = %d limit 1', $_GET['id']));
            $id = $row->loginx_field_id;
            $req = $row->loginx_field_req;    
            $reg = $row->loginx_field_reg;
        }
        
        $addField = '<table class="inline"><tr><th>Name</th><th>Label</th><th>Type</th><th>Options</th></tr><tr>';
        $addField .= '<td>' . $form1->textField('Name', 'loginx_field_name', $row->loginx_field_name, true) . '</td>';
        $addField .= '<td>' . $form1->textField('Label', 'loginx_field_label', $row->loginx_field_label, true) . '</td>';
        $addField .= '<td>' . $form1->dropDown('Type', 'loginx_field_type', $row->loginx_field_type, $this->fieldTypes, false, true) . '</td>';
        $addField .= '<td>' . $form1->textArea('Options', 'loginx_field_options', $row->loginx_field_options) . '</td>';
        $addField .= '</tr></table>';

        $text .= '<a name="customFields"></a><fieldset><legend>Custom Fields</legend>';
        $text .= $form1->startFieldSet('Add Custom User Field');
        $text .= $form1->hidden('nonce', wp_create_nonce('loginx_manage_fields'));
        $text .= $form1->hidden('loginx_field_id', $id);
        $text .= $form1->freeText($addField);
        
        $text .= $form1->endForm();    
        $text .= '</fieldset>';
        $text .= '<div id="customFieldsList">';

        $text .= '</div>';
        $text .= '</fieldset>';

        return $text;
    }   
    
    function adminMenu(){
        $this->adminPageID = add_management_page('LoginX', 'LoginX', 5, __FILE__, array($this, 'adminScreen')); 
        wp_enqueue_script('jquery-ui-tabs');        
        wp_enqueue_script('loginx_admin', LOGINX_URL . 'js/loginx_admin.js');
        add_action('load-' . $this->adminPageID, array($this, 'loadHelpTab'));
    } 
    
    function loadHelpTab(){
        $screen = get_current_screen();

        if ($screen->id == $this->adminPageID){
            $screen->add_help_tab(array(
                'id' => 'loginx_admin_help', 
                'title' => 'LoginX Help', 
                'content' => '
                The list of tags that can be included in your email templates:
                <ul>
                    <li><div style="font-weight: bold; width:150px; float:left;">::URL::</div> - Website URL</li>
                    <li><div style="font-weight: bold; width:150px; float:left;">::BLOGURL::</div> - Blog URL</li>
                    <li><div style="font-weight: bold; width:150px; float:left;">::BLOGDESC::</div> - Blog Description</li>
                    <li><div style="font-weight: bold; width:150px; float:left;">::BLOGNAME::</div> - Blog Name</li>
                    <li><div style="font-weight: bold; width:150px; float:left;">::ADMINEMAIL::</div> - Admin Email (Set in General Settings)</li>
                    <li><div style="font-weight: bold; width:150px; float:left;">::DATE::</div> - Current Date (Format set in General Settings)</li>
                    <li><div style="font-weight: bold; width:150px; float:left;">::TIME::</div> - Current Time (Format set in General Settings)</li>
                    <li><div style="font-weight: bold; width:150px; float:left;">::LINK::</div> - A dynamic tag.  If the email produces a link, like verification or password reset, this will be the link.</li>
                    
                </ul>'
            ));             
        }        
    }
    
    function install(){

        if (!is_plugin_active('phpx/phpx.php')){
            die('LoginX requires the PHPX Framework.  Please install PHPX and then reinstall LoginX.');
        }
        else if (isset($this->options['login'])){
            return true;
        }
        
        
        
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
        $options =  array('login_page' => $login_id, 'login' => true, 'register_page' => $register_id, 'profile_page' => $profile_id,'user_admin_redirect' => 'on','redirect_admin_page' => '153','user_login_redirect' => 'on','email_valid' => 'on','captcha_public' => '','captcha_private' => '','anon_fields' => '','show_purchases' => '','bad_key' => 'Token expired.  Please try again.','captcha_fail' => 'Image verification failed.','check_email_password' => 'An email was set to ::EMAIL:: with instructions to complete your password reset.','password_text' => 'Please enter your username or email address and an email will be sent to you with instructions to reset your password.','register_success_message' => 'Registration successful.','act_success' => 'Activation successful.','act_fail' => 'Activation Failed.  Please check your link and try again.','not_active' => 'User not active.','profile_email_verify_message' => 'Your profile has been updated, but your email will need to be re-verified before you can login.','profile_success_message' => 'Profile Updated.','password_change_success_message' => 'Password Updated.','email_password_reset_subject' => '::BLOGNAME:: - Password Reset Request','email_password_reset' => 'A request was processed at ::BLOGNAME:: to reset your password.  In order to reset your password, please follow this link:<br /><br />::LINK::<br /><br />
If you did not request this email, please contact us at ::URL::','act_email_subject' => '::BLOGNAME:: - Activate User (Action Required)','act_email_text' => 'Please verify your email address by following this link: <br /><br />::LINK::<br /><br />Your user account will be inactive until this is completed.<br /><br />If you did not request this email, please contact us at ::URL::','email_password_was_reset_subject' => '::BLOGNAME:: - Password Reset Notification','email_password_was_reset' => 'Your password at ::BLOGNAME:: has been reset.<br /><br />If you did not request this email, please contact us at ::URL::', 'act_key_resent' => 'Activation Key Re-sent.  Please check your email.', 'password_reset_text' => 'Please enter your new password and confirm.');
        
        update_option('loginx_options', $options);
        
        $this->wpdb->query("CREATE TABLE IF NOT EXISTS `" . $this->wpdb->prefix . "loginx_field` (
  `loginx_field_id` int(10) NOT NULL AUTO_INCREMENT,
  `loginx_field_name` varchar(50) NOT NULL,
  `loginx_field_label` varchar(200) NOT NULL,
  `loginx_field_options` text NOT NULL,
  `loginx_field_type` varchar(30) NOT NULL,
  `loginx_field_reg` tinyint(1) NOT NULL DEFAULT '0',
  `loginx_field_req` tinyint(1) NOT NULL DEFAULT '0',
  `loginx_field_ord` tinyint(3) NOT NULL DEFAULT '0',
  `loginx_field_wp` tinyint(1) NOT NULL DEFAULT '0',
  `loginx_field_mand` tinyint(1) NOT NULL DEFAULT '0',
  `loginx_field_active` tinyint(1) NOT NULL DEFAULT '0',
  `loginx_field_lock` tinyint(1) NOT NULL DEFAULT '0',
  `loginx_field_no_edit` tinyint(1) NOT NULL DEFAULT '0',
  `loginx_field_profile` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`loginx_field_id`),
  UNIQUE KEY `loginx_field_name` (`loginx_field_name`),
  KEY `loginx_field_reg` (`loginx_field_reg`),
  KEY `loginx_field_wp` (`loginx_field_wp`,`loginx_field_mand`),
  KEY `loginx_field_active` (`loginx_field_active`),
  KEY `loginx_field_lock` (`loginx_field_lock`),
  KEY `loginx_field_no_edit` (`loginx_field_no_edit`),
  KEY `loginx_field_profile` (`loginx_field_profile`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;");  
        $this->wpdb->query("INSERT INTO `" . $this->wpdb->prefix . "_loginx_field` (`loginx_field_id`, `loginx_field_name`, `loginx_field_label`, `loginx_field_options`, `loginx_field_type`, `loginx_field_reg`, `loginx_field_req`, `loginx_field_ord`, `loginx_field_wp`, `loginx_field_mand`, `loginx_field_active`, `loginx_field_lock`, `loginx_field_no_edit`, `loginx_field_profile`) VALUES
(1, 'user_login', 'Username', '', 'text', 1, 1, 1, 1, 1, 1, 1, 1, 0),
(2, 'user_pass', 'Password', '', 'pass', 1, 1, 2, 1, 1, 1, 1, 0, 0),
(3, 'user_pass_confirm', 'Confirm Password', 'confirm:true', 'pass', 1, 1, 3, 1, 0, 1, 1, 0, 0),
(4, 'user_email', 'Email', 'req:email', 'text', 1, 1, 4, 1, 1, 1, 1, 0, 1),
(5, 'user_url', 'Website', '', 'text', 0, 0, 5, 1, 0, 1, 1, 0, 1),
(6, 'display_name', 'Display Name', '', 'text', 0, 0, 6, 1, 0, 1, 1, 0, 1),
(7, 'first_name', 'First Name', '', 'text', 0, 0, 7, 1, 0, 1, 1, 0, 1),
(8, 'last_name', 'Last Name', '', 'text', 0, 0, 8, 1, 0, 1, 1, 0, 1),
(9, 'nickname', 'Nickname', '', 'text', 0, 0, 9, 1, 0, 1, 1, 0, 1),
(10, 'description', 'Bio', '', 'area', 0, 0, 10, 1, 0, 1, 1, 0, 1),
(11, 'rich_editing', 'Rich Editing', '', 'check', 0, 0, 11, 1, 0, 0, 1, 0, 0),
(12, 'show_admin_bar_front', 'Show Admin Bar', '', 'check', 0, 0, 12, 1, 0, 0, 1, 0, 0),
(13, 'aim', 'AOL', '', 'text', 0, 0, 13, 1, 0, 0, 1, 0, 1),
(14, 'yim', 'Yahoo', '', 'text', 0, 0, 14, 1, 0, 0, 1, 0, 1),
(15, 'jabber', 'Jabber/Google Talk', '', 'text', 0, 0, 15, 1, 0, 0, 1, 0, 1),
(16, 'captcha', 'Captcha', '', 'captcha', 1, 1, 17, 0, 0, 1, 1, 0, 0);");   
        $this->wpdb->query('CREATE TABLE `' . $this->wpdb->prefix . 'loginx_key` (`user_id` INT( 10 ) NOT NULL ,`loginx_key` VARCHAR( 32 ) NOT NULL ,`loginx_expire` INT( 11 ) NOT NULL ,INDEX ( `loginx_key` , `loginx_expire` )) ENGINE = MYISAM');
        $this->wpdb->query('ALTER TABLE `' . $this->wpdb->prefix . 'loginx_key` ADD `act` TINYINT( 1 ) NOT NULL DEFAULT \'0\',ADD INDEX ( `act` ) ');
        $this->wpdb->query('ALTER TABLE `' . $this->wpdb->prefix . 'loginx_field` ADD `loginx_field_profile` TINYINT( 1 ) NOT NULL DEFAULT \'0\',ADD INDEX ( `loginx_field_profile` ) ');
        
                

    }
    
    function uninstall(){
        delete_option('loginx_options');
    }   
    
    function removeData(){
        
    }
    
    function adminCSS(){
        print("<link rel='stylesheet' href='" . LOGINX_URL . "css/loginx_admin.css' type='text/css' media='all' />");   
    } 
    
    
}  

?>
