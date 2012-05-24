<?php
class loginXRegister extends loginX {
    
    
    
    function __construct(){
        parent::__construct();
    }
        
    function registerForm(){

        if ($_POST['submit']){
            $cont = true;    
            if (!wp_verify_nonce($_POST['nonce'], 'loginx_register')){
                parent::loginx_errorMessage('Security Token Mismatch');
                $cont = false;
            }            
            else if (username_exists($_POST['user_login'])){ 
                parent::loginx_errorMessage('Username Exists.  Do you want to <a href="' . $this->loginx_getURL() . '">Login?</a>');
                $_POST['user_login'] = '';
                $cont = false;
            }
            else if (email_exists($_POST['user_email'])){
                parent::loginx_errorMessage('Email Exists.  Do you want to <a href="' . $this->loginx_getURL() . '">Login?</a>');
                $_POST['user_email'] = '';
                $cont = false;
            }
            else if ($_POST['recaptcha_challenge_field']){
                $data['privatekey'] = $this->options['captcha_private'];
                $data['remoteip'] = $_SERVER['REMOTE_ADDR'];
                $data['challenge'] = $_POST['recaptcha_challenge_field'];
                $data['response'] = $_POST['recaptcha_response_field'];

                
                $c = curl_init();
                curl_setopt($c, CURLOPT_URL, 'http://www.google.com/recaptcha/api/verify');    
                curl_setopt($c, CURLOPT_POST, true);
                curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($c, CURLOPT_POSTFIELDS, $data);
                $response = curl_exec($c);
                $r = explode("\n", $response);
                if ($r[0] == 'true'){
                    
                }
                else { 
                    parent::loginx_errorMessage($this->option['captcha_fail']); 
                    $cont = false;
                       
                }
            }
            
            if ($cont == true) { 
                $omit = array('submit', 'nonce', 'user_pass_confirm', 'captcha', 'recaptcha_challenge_field', 'recaptcha_response_field');
                $wpFields = array();
                $createArray = array();
                $metaArray = array();
                $results = $this->wpdb->get_results('select loginx_field_name from ' . $this->wpdb->prefix . 'loginx_field where loginx_field_wp = 1');
                foreach($results as $row){
                    $wpFields[] = $row->loginx_field_name;                   
                }
                foreach($_POST as $k => $v){
                    if (!in_array($k, $omit)){
                        if (in_array($k, $wpFields)){
                            $createArray[$k] = $v;
                        }    
                        else { 
                            $metaArray[$k] = $v;
                        }
                    }
                }
                
                $user_id = wp_insert_user($createArray);
                foreach($metaArray as $k => $v){
                    add_user_meta($user_id, $k, $v, true);
                }
                
                if ($this->options['email_valid'] == 'on'){
                    $actKey = substr(md5(microtime() . NONCE_SALT), 5, 15);
                    $this->wpdb->insert($this->wpdb->prefix . 'loginx_key', array('user_id' => $user_id, 'loginx_key' => $actKey, 'loginx_expire' => 0, 'act' => 1));
                    
                    $subject = parent::loginx_emailTrans($this->options['act_email_subject']);
                    $message = parent::loginx_emailTrans($this->options['act_email_text'], array('::LINK::' => get_permalink($this->options['login_page']) . '?act=' . $actKey));
                    
                    
                    
                    wp_mail($_POST['user_email'], $subject, $message);
                    
                    parent::loginx_successMessage($this->options['register_success_message']);
                    $text = '<div id="loginx_form">' . parent::loginx_successMessage() . '</div>';
                    return $text;                 
                }
                else {
                    wp_redirect(get_permalink($this->options['profile_page']));
                }
            }
           
            
        }   

        require_once(PHPX_DIR . 'phpx_form.php');
        $form = new phpx_form();
        $form->startForm(get_permalink(), 'loginxRegisterForm');
        $form->hidden('nonce', wp_create_nonce('loginx_register'));
        if (parent::loginx_errorMessage()){
            $form->freeText(parent::loginx_errorMessage('get'), 'loginx_error');
        }            
        $results = $this->wpdb->get_results('select loginx_field_name, loginx_field_label, loginx_field_options, loginx_field_type, loginx_field_req from ' . $this->wpdb->prefix . 'loginx_field where loginx_field_reg = 1 order by loginx_field_ord asc');        
        
        $form = parent::publicForm($form, $results);        
        
        
        
        

        $text = '<div id="loginx_form">' . $form->endForm() . '</div>';
        return $text;
    }   
    

}  
?>
