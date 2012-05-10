<?php

class loginXProfile extends loginX {
    
    function __construct(){
        parent::__construct();
    }
    
    public function init(){
        if (!is_user_logged_in() && ($this->options['anon_profiles'] != 'on' || isset($_GET['edit']))){
            print('<script language="javascript">window.location = "' .  get_permalink($this->options['login_page']) . '";</script>');
            exit;
        }        
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
        $username = (!isset($_GET['u']) || $_GET['u'] == '') ? $current_user->user_nicename : $_GET['u'];
        

        $user = get_user_by('slug', $username);    
        $GLOBALS['avatar_user_id'] = $user->ID;
        

        $trans['::AVATAR::'] = get_avatar($user->user_email, 92);
        $trans['::DISPLAYNAME::'] = $user->display_name;
        $trans['::REGDATE::'] = date(get_option('date_format'), strtotime($user->user_registered));
        $trans['::INFO::'] = $user->user_description;
        if ($user->ID == $current_user->ID){ 
            $trans['::LINKS::'] = '<a href="' . get_permalink($this->options['profile_page']) . '?edit=1">Edit Profile</a>'; 
        }
        else { 
            $trans['::LINKS::'] = ''; 
        }
        $trans['::POSTS::'] = $this->formatList('Latest Posts', $user, $this->getPosts($user->ID));
        $trans['::COMMENTS::'] = $this->formatList('Latest Comments', $user, $this->getComments($user->ID), 'comment');
        $this->text = strtr(file_get_contents(LOGINX_DIR . 'templates/showProfile.tpl.php'), $trans);        
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
                    $list .= '<a href="' . get_permalink($this->options['profile_page']) . '?u=' . $user->user_nicename . '&v=c">View All Comments</a>';   
                    break;
            }
            //VIEW ALL *****REIVEW
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
        if ($_POST['submit']){
            
        }
        
    }
}
?>
