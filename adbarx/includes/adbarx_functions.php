<?php
class adBarX {
    /**
    * The functions for AdBarX
    * @global   array   $options
    */
    
    function __construct(){
        global $wpdb;
        $this->wpdb = $wpdb;
        
        $this->options = get_option('adbarx_options');

    }

    function adbarx_install(){
        update_option('adbarx_options', array('remember' => 1, 'cookie' => 'abx_234alk3469ohaelf34986hol', 'content' => ''));
                
        if (!is_plugin_active('phpx/phpx.php')){
            die('AdBarX requires the PHPX Framework.  Please install PHPX and then reinstall AdBarX.');
        }
    }
    
    function adbarx_uninstall(){
        delete_option('adbarx_options');
    }
    
    function adbarx_admin(){
        if ($_POST['nonce']){
            if (!wp_verify_nonce($_POST['nonce'], 'adbarx_admin')){
                die('Invalid Security Token');
            }
            $this->options['remember'] = ($_POST['showOnce'] == 'on') ? 1 : 0;
            $this->options['content'] = $_POST['content'];
            
            if ($_POST['resetViews'] == 'on'){
                $this->options['cookie'] = 'adx_' . substr(md5(microtime()), 5, 20);
            }
            update_option('adbarx_options', $this->options);
        }
        
        
        require_once(PHPX_DIR . 'phpx_form.php');
        $form = new phpx_form();
        $form->instantReturn = true;
        
        $text = '<div class="wrap"><h2>Ad Bar X</h2>';
        $text .= $form->startForm('tools.php?page=adbarx/includes/adbarx_functions.php', 'adbarxForm');        
        $text .= $form->hidden('nonce', wp_create_nonce('adbarx_admin'));
        print($text);
        
        the_editor($this->options['content'], 'content');
        $text = '<br /><br />';
        $text .= $form->checkBox('Show Adbar Once', 'showOnce', 1);
        $text .= $form->checkBox('Reset All Views', 'resetViews', 0);
        $text .= $form->endForm();
        
        
        
        $text .= '</div>';
        print($text);
    }   
    
    function adbarx_adminMenu(){
        add_management_page('AdBarX', 'AdBarX', 5, __FILE__, array($this, 'adbarx_admin')); 
    }
    
    

    function adbarx_addContent(){
        
        
    }
    
    function adbarx_addCSS(){
        print("<link rel='stylesheet' href='" . ADBARX_URL . "css/adbarx.css' type='text/css' media='all' />");      
    }
    
    
    
    
}
        
        

?>
