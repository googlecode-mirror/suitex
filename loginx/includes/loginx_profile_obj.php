<?php
class loginXProfile extends loginX {
    
    function __construct(){
        parent::__construct();
    }
    
    public function init(){
        if ($_GET['edit'] == 1){
            $this->editProfile();
        }    
        else { 
            $this->showProfile();
        }
        return $this->text;
    }
    
    function showProfile(){
        global $current_user;
        get_currentuserinfo();
        if (!$_GET['id']){
            $id = $current_user->ID;
        }

        
        $comments = $this->getComments($id);
        $posts = $this->getPosts($id);
        $user = get_userdata($id);    
        $trans['::AVATAR::'] = get_avatar($user->ID, 92);
        $trans['::DISPLAYNAME::'] = $user->display_name;
        $trans['::REGDATE::'] = date(get_option('date_format'), strtotime($user->user_registered));
        $trans['::INFO::'] = $user->user_description;
        if ($id == $current_user->ID){ $trans['::LINKS::'] = '<a href="' . get_permalink($this->options['profile_page']) . '?edit=1">Edit Profile</a>'; }
        $trans['::POSTS::'] = $posts;
        $trans['::COMMENTS::'] = $comments;
        $this->text = strtr(file_get_contents(LOGINX_DIR . 'templates/showProfile.tpl.php'), $trans);        
        

        
    }
    
    function getComments($id){
        
        return $comments;
    }
    
    function getPosts($id){
        
        
        return $posts;
    }
    
    function editProfile(){
        if ($_POST['submit']){
            
        }
        
    }
}
?>
