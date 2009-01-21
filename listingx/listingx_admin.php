<?php

class listingx_admin {

    function __construct(){
    	$this->getMessage();


    	/*$options = get_option('listingx_options');

        $page                   = array();
        $page['post_type']      = 'page';
        $page['post_title']     = 'ListingX';
        $page['post_name']      = 'listingx';
        $page['post_status']    = 'publish';
        $page['comment_status'] = 'closed';
        $page['post_content']   = 'This is your ListingX Page.';
        //$page_id = wp_insert_post($page);
        $options['page_id'] = $page_id;

        //update_option('listingx_options', $options);
        */

    }


    function listingx_install(){
	    /**
	    * Installs the plugin by creating the page and options
	    */

		if (!get_option('listingx_options')){
	        $options				= array();
	        $page                   = array();
        	$page['post_type']      = 'page';
        	$page['post_title']     = 'Projects';
        	$page['post_name']      = 'listingx';
        	$page['post_status']    = 'publish';
        	$page['comment_status'] = 'closed';
        	$page['post_content']   = 'This is your ListingX Top level page.  All projects will be sub pages underneath this page.';
        	$page_id = wp_insert_post($page);
        	$options['page_id'] = $page_id;

    	    update_option('listingx_options', $options);
        }

    }

    function listingx_uninstall(){
    	/**
    	* Uninstalls the plugin by deleting the options and page
    	*/

    	delete_option('listingx_options');
    }

    function listingx_admin_menu(){
    	/**
    	* The hook for the admin menu
    	*/
        add_menu_page('ListingX', 'ListingX', 5, __FILE__, array($this, 'listingx_admin_page'));
        add_submenu_page(__FILE__, 'ListingX Settings', 'Settings', 5, 'settings', array($this, 'listingx_settings'));
        add_submenu_page(__FILE__, 'ListingX Project Admin', 'Projects', 5, 'projects', array($this, 'listingx_projects'));
        add_submenu_page(__FILE__, 'ListingX Category Admin', 'Categories', 5, 'categories', array($this, 'listingx_categories'));
    }

    function listingx_projects(){
    	$pluginBase = 'wp-content' . DIRECTORY_SEPARATOR . 'plugins' . DIRECTORY_SEPARATOR . 'listingx';
    	require_once(ABSPATH . $pluginBase . DIRECTORY_SEPARATOR . 'listingx_projects.php');
    	$this->projects = new listingx_projects($this);

    }

    function listingx_categories(){
    	$pluginBase = 'wp-content' . DIRECTORY_SEPARATOR . 'plugins' . DIRECTORY_SEPARATOR . 'listingx';
    	require_once(ABSPATH . $pluginBase . DIRECTORY_SEPARATOR . 'listingx_categories.php');
    	$this->categories = new listingx_categories($this);
    }

    function stroke($text){
    	$body = "<script type='text/javascript' src='../wp-content/plugins/listingx/listingx.js'></script>";

    	$body .= $text;
    	print($body);

    }

    function listingx_settings(){
        clearstatcache();

        $options = get_option('listingx_options');
        if ($_POST['action'] == "update"){
        	$options["newReleaseText"] = htmlentities($_POST["newReleaseText"]);
        	$options["newProjectPageText"] = htmlentities($_POST["newProjectPageText"]);
        	$options["newProjectPostText"] = htmlentities($_POST["newProjectPostText"]);
        	update_option('listingx_options', $options);
        	$this->getMessage("sc");
        }

        $text .= "<div class=\"wrap\">";
        $text .= "<h2>ListingX - Settings</h2>";
        $text .= $this->message;

		$text .= "<div id=\"poststuff\" class=\"metabox-holder\">";
		$text .= "<div id=\"post-body\" class=\"has-sidebar\">";
		$text .= "<div id=\"post-body-content\" class=\"has-sidebar-content\">";
        $text .= "<div class=\"postbox\">";
        $text .= "<h3><label>ListingX Settings</label></h3>";
		$text .= "<div class=\"inside\">";
        $text .= "<form method=\"post\" action=\"\">";
        $text .= "<input type=\"hidden\" name=\"_wpnonce\" value=\"" . wp_create_nonce() . "\" />";
        $text .= "<input type=\"hidden\" name=\"action\" value=\"update\" />";

        $text .= "<table class=\"form-table\">";
        $text .= "<tr><td colspan=\"2\"><strong>Template Labels</strong><br />";
        $text .= "::NAME::";
        $text .= ", ::DESC::";
        $text .= ", ::OWNER::";
        $text .= ", ::USERS::";
        $text .= ", ::CATEGORIES::";
        $text .= ", ::ADDED::";
        $text .= ", ::MODIFIED::";
        $text .= ", ::URL::";
        $text .= ", ::DONATE::";
        $text .= ", ::RELEASES::";
        $text .= ", ::FILES::";
        $text .= ", ::DATE::";
        $text .= ", ::VERSION::";
        $text .= ", ::NOTES::";
        $text .= ", ::LOG::";
        $text .= "</td></tr>";
        $text .= "<tr class=\"form-field\">";
        $text .= "<td valign=\"top\"><strong>Default Project Page:</strong>";
        $text .= "</td>";
        $text .= "<td><textarea name=\"newProjectPageText\">" . stripslashes($options["newProjectPageText"]) . "</textarea>";
        $text .= "</td></tr>";
        $text .= "<tr class=\"form-field\">";
        $text .= "<td valign=\"top\"><strong>New Project Post:</strong>";
        $text .= "</td>";
        $text .= "<td><textarea name=\"newProjectPostText\">" . stripslashes($options["newProjectPostText"]) . "</textarea>";
        $text .= "</td></tr>";
        $text .= "<tr class=\"form-field\">";
        $text .= "<td valign=\"top\"><strong>New Release:</strong>";
        $text .= "</td>";
        $text .= "<td><textarea name=\"newReleaseText\">" . stripslashes($options["newReleaseText"]) . "</textarea>";
        $text .= "</td></tr>";


        $text .= "</table>";
        $text .= "<p class=\"submit\"><input type=\"submit\" name=\"Submit\" value=\"Save Changes\" />";
        $text .= "</p></form>";
		$text .= "</div></div></div></div>";
		$text .= "</div></div>";
		$this->stroke($text);
    }

    function listingx_admin_page(){
    	/**
    	* Creates the Admin page
    	*/







        $text .= "<div class=\"wrap\">";
        $text .= "<h2>ListingX</h2>";
        $text .= "</div>";



        //Projects awaiting Approval
        //Releases awaiting approval for announcement
        //Stats
        $this->stroke($text);




    }

    function getMessage($code=''){
		if ($_GET["code"]){ $code = $_GET["code"]; }
		if ($code != ''){
		    switch($code){
		    	case "a":
		    		$message = "Project Added";
		    		break;

		    	case "ap":
		    		$message = "Project Approved";
		    		break;

		    	case "m":
		    		$message = "Project Modified";
		    		break;

		    	case "d":
		    		$message = "Project Deleted";
		    		break;

		    	case "sc":
		    		$message = "Settings Saved";
		    		break;
		    }
			$this->message = "<br /><b><span style=\"color:#FF0000;\">$message</span></b>";
		}




    }

    function pageDirect($url){
    	$text = "<script language=\"javascript\"> window.location = '$url'; </script>";
    	print($text);

    }
}

?>
