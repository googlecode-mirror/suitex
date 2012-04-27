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
            $transBin = array('loginx_field_reg', 'loginx_field_req');

            if (!isset($_POST['loginx_field_id'])){
            
                foreach($_POST as $k => $v){
                    if (!in_array($k, $omit)){
                        $this->options[$k] = $v;
                    }
                }

            }
            else { 

                if ($_POST['action'] == 'order'){ 
                    
                }
                else if ($_POST['loginx_field_id'] == 0){
                    $this->wpdb->show_errors();
                    foreach($_POST as $k => $v){
                        if (!in_array($k, $omit)){
                            if (in_array($k, $transBin)){
                                $v = ($v == 'on')? 1 : 0;
                            }
                            $fieldArray[$k] = $v;
                        }
                    }    
                    $max = $this->wpdb->get_var($this->wpdb->prepare('select max(loginx_field_ord) from ' . $this->wpdb->prefix . 'loginx_field'));
                    $fieldArray['loginx_field_ord'] = $max + 1;               
                    $this->wpdb->insert($this->wpdb->prefix . 'loginx_field', $fieldArray);
                }
                else {
                    $this->wpdb->show_errors();
                    foreach($_POST as $k => $v){
                        if (!in_array($k, $omit)){
                            if (in_array($k, $transBin)){
                                $v = ($v == 'on')? 1 : 0;
                            }
                            $fieldArray[$k] = $v;
                        }
                    }                   
                    $this->wpdb->update($this->wpdb->prefix . 'loginx_field', $fieldArray, array('loginx_field_id' => $_POST['loginx_field_id']));                    
                }
                
            }
            $message = true;
            update_option('loginx_options', $this->options);            
        }
        else if ($_GET['nonce']){
            if (!wp_verify_nonce($_GET['nonce'], 'loginx_admin')){
                die('Invalid Security Token');
            }            
            if ($_GET['action'] == 'delete'){
                $this->wpdb->query($this->wpdb->prepare('delete from ' . $this->wpdb->prefix . 'loginx_field where loginx_field_id = %d limit 1', $_GET['id']));
                
            }
            else if ($_GET['action'] == 'up'){
                $this->wpdb->show_errors();
                $this->wpdb->query($this->wpdb->prepare('update ' . $this->wpdb->prefix . 'loginx_field set loginx_field_ord = loginx_field_ord - 1 where loginx_field_id = %d limit 1', $_GET['id']));
                $ord = $this->wpdb->get_var($this->wpdb->prepare('select loginx_field_ord from ' . $this->wpdb->prefix . 'loginx_field where loginx_field_id = %d limit 1', $_GET['id']));
                $this->wpdb->query($this->wpdb->prepare('update ' . $this->wpdb->prefix . 'loginx_field set loginx_field_ord = loginx_field_ord + 1 where loginx_field_ord = %d and loginx_field_id != %d limit 1', $ord, $_GET['id']));
                
            }
            else { 
                $this->wpdb->query($this->wpdb->prepare('update ' . $this->wpdb->prefix . 'loginx_field set loginx_field_ord = loginx_field_ord + 1 where loginx_field_id = %d limit 1', $_GET['id']));
                $ord = $this->wpdb->get_var($this->wpdb->prepare('select loginx_field_ord from ' . $this->wpdb->prefix . 'loginx_field where loginx_field_id = %d limit 1', $_GET['id']));
                $this->wpdb->query($this->wpdb->prepare('update ' . $this->wpdb->prefix . 'loginx_field set loginx_field_ord = loginx_field_ord - 1 where loginx_field_ord = %d and loginx_field_id != %d limit 1', $ord, $_GET['id']));                
                
            }
            $message = true;
        }
        
        
        require_once(PHPX_DIR . 'phpx_form.php');
        $form = new phpx_form();
        $form1 = new phpx_form();
        $pages = get_pages();
        $nonce = wp_create_nonce('loginx_admin');
        foreach($pages as $p){
            $pageArray[$p->ID] = $p->post_title;
        }
        $adminURL = 'tools.php?page=loginx/includes/loginx_admin_obj.php';
        $text = '<div class="wrap" id="phpxContainer"><h2>Login X</h2>';
        if ($message){ $text .= '<p>Options Saved</p>'; }
        $text .= $form->startForm($adminURL, 'loginxForm');        
        $text .= $form->hidden('nonce', $nonce);
        $text .= $form->startFieldSet('General Options');
        $text .= $form->dropDown('Profile Page', 'profile_page', $this->options['profile_page'], $pageArray, false);
        $text .= $form->dropDown('Register Page', 'register_page', $this->options['register_page'], $pageArray, false);
        $text .= $form->textArea('Password Lookup Text', 'password_text', $this->options['password_text']);
        $text .= $form->endForm();
        
        
        
        
        $form1->labels = false;
        $form1->instantReturn = true;
        
        $fieldTypes = array('text' => 'Text', 'drop' => 'Drop Down', 'check' => 'Check Box', 'radio' => 'Radio', 'area' => 'Text Area', 'date' => 'Date');
        
        $text .= $form1->startForm($adminURL, 'loginxFieldForm'); 
        $id = 0;
        $reg = 0;
        $req = 0;
        if ($_GET['action'] == 'edit'){
            $row = $this->wpdb->get_row($this->wpdb->prepare('select * from ' . $this->wpdb->prefix . 'loginx_field where loginx_field_id = %d limit 1', $_GET['id']));
            $id = $row->loginx_field_id;
            $req = $row->loginx_field_req;    
            $reg = $row->loginx_field_reg;
        }
        
        
        
        
        
        $addField = '<table class="inline"><tr><th>Name</th><th>Label</th><th>Type</th><th>Options</th><th>Required</th><th>On Register</th></tr><tr>';
        $addField .= '<td>' . $form1->textField('Name', 'loginx_field_name', $row->loginx_field_name, true) . '</td>';
        $addField .= '<td>' . $form1->textField('Label', 'loginx_field_label', $row->loginx_field_label, true) . '</td>';
        $addField .= '<td>' . $form1->dropDown('Type', 'loginx_field_type', $row->loginx_field_type, $fieldTypes, false, true) . '</td>';
        $addField .= '<td>' . $form1->textArea('Options', 'loginx_field_options', $row->loginx_field_options) . '</td>';
        $addField .= '<td>' . $form1->checkbox('Required', 'loginx_field_req', $req) . '</td>';
        $addField .= '<td>' . $form1->checkbox('On Register', 'loginx_field_reg', $reg) . '</td>';
        $addField .= '</tr></table>';

        $text .= '<fieldset><legend>Custom Fields</legend>';
        $text .= $form1->startFieldSet('Add Custom User Field');
        $text .= $form1->hidden('nonce', $nonce);
        $text .= $form1->hidden('loginx_field_id', $id);
        $text .= $form1->freeText($addField);
        
        $text .= $form1->endForm();    
        $text .= '</fieldset>';
        
        $text .= '<table class="inline"><tr><th>Order</th><th>Name</th><th>Label</th><th>Type</th><th>Required</th><th>On Register</th></tr><tr>';
        $results = $this->wpdb->get_results("select * from " . $this->wpdb->prefix . "loginx_field order by loginx_field_ord asc");
        $x = 1;
        $count = count($results);
        foreach($results as $row){
            $req = ($row->loginx_field_req == 1) ? '<img src="' . LOGINX_URL . 'images/check.png" border="0" width="16" height="16" alt="On" />' : '--';
            $reg = ($row->loginx_field_reg == 1) ? '<img src="' . LOGINX_URL . 'images/check.png" border="0" width="16" height="16" alt="On" />' : '--';
            $edit = '<a href="' . $adminURL . '&action=edit&id=' . $row->loginx_field_id . '"><img src="' . LOGINX_URL . 'images/edit.png" border="0" width="16" height="16" alt="Edit" /></a>';
            $delete = '<a href="javascript:loginx_confirm_delete(\'' . $adminURL . '&action=delete&id=' . $row->loginx_field_id . '&nonce=' . $nonce . '\');"><img src="' . LOGINX_URL . 'images/delete.png" border="0" width="16" height="16" alt="Delete" /></a>';
            if ($x != 1){
                $up = '<a href="' . $adminURL . '&action=up&id=' . $row->loginx_field_id . '&nonce=' . $nonce . '"><img src="' . LOGINX_URL . 'images/arrow_up_blue.png" border="0" width="16" height="16" alt="Move Up" /></a>'; }
            $down = '';    
            if ($x != $count){
                $down = '<a href="' . $adminURL . '&action=down&id=' . $row->loginx_field_id . '&nonce=' . $nonce . '"><img src="' . LOGINX_URL . 'images/arrow_down_blue.png" border="0" width="16" height="16" alt="Move Up" /></a>';
            }

            
            $text .= '<tr id="field_id_' . $row->loginx_field_id . '">';
            $text .= '<td class="field_actions">' . $edit . ' ' .  $delete . ' ' . $down . ' ' . $up . '</td>';
            $text .= '<td class="field_name">' . $row->loginx_field_name . '</td>';
            $text .= '<td class="field_label">' . $row->loginx_field_label . '</td>';
            $text .= '<td class="field_type">' . $fieldTypes[$row->loginx_field_type] . '</td>';
            $text .= '<td class="field_req">' . $req . '</td>';
            $text .= '<td class="field_reg">' . $reg . '</td>';
            
            $text .= '</tr>';
            $x++;
        }
        
        $text .= '</table>';
        
        $text .= '</fieldset>';
        
        
        $text .= '</div>';
        print($text);
    }   
    
    function adminMenu(){
        add_management_page('LoginX', 'LoginX', 5, __FILE__, array($this, 'adminForm')); 
    } 
    
    function install(){
        if (!is_plugin_active('phpx/phpx.php')){
            die('LoginX requires the PHPX Framework.  Please install PHPX and then reinstall LoginX.');
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
        $options =  array('login_page' => $page_id, 'login' => true, 'register_page' => $register_id, 'profile_id' => $profile_id);
        
        
        update_option('loginx_options', $options);
        
        $this->wpdb->query("CREATE TABLE `" . $this->wpdb->prefix . "loginx_field` (`loginx_field_id` INT( 10 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,`loginx_field_name` VARCHAR( 50 ) NOT NULL,`loginx_field_label` VARCHAR( 200 ) NOT NULL ,`loginx_field_options` TEXT NOT NULL DEFAULT '',`loginx_field_type` VARCHAR( 30 ) NOT NULL ,`loginx_field_reg` TINYINT( 1 ) NOT NULL DEFAULT '0',`loginx_rield_req` TINYINT( 1 ) NOT NULL DEFAULT '0',INDEX ( `loginx_field_reg` ) ,UNIQUE (`loginx_field_name`)) ENGINE = MYISAM ;");     
        $this->wpdb->query('CREATE TABLE `' . $this->wpdb->prefix . 'loginx_key` (`user_id` INT( 10 ) NOT NULL ,`loginx_key` VARCHAR( 32 ) NOT NULL ,`loginx_expire` INT( 11 ) NOT NULL ,INDEX ( `loginx_key` , `loginx_expire` )) ENGINE = MYISAM');
                

    }
    
    function uninstall(){
        wp_delete_post($this->options['login_page'], true);
        delete_option('loginx_options');
    }    
    
    
}  

?>
