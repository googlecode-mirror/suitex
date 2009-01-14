<?php

class listingx_admin {


    function listingx_install(){
	    /**
	    * Installs the plugin by creating the page and options
	    */

		if (!get_option('listingx_options')){
	        $options				= array();
	        $page                   = array();
        	$page['post_type']      = 'page';
        	$page['post_title']     = 'ListingX';
        	$page['post_name']      = 'listingx';
        	$page['post_status']    = 'publish';
        	$page['comment_status'] = 'closed';
        	$page['post_content']   = 'This is your ListingX Page.';
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



    function listingx_admin_page(){
    	/**
    	* Creates the Admin page
    	*/

        clearstatcache();

        $options = get_option('listingx_options');
		if ($_POST['action'] == "update"){


		}




        $text .= "<div class=\"wrap\">";


        $text .= "<h2>ListingX</h2>";


        $text .= "</div>";

        //Projects awaiting Approval
        //Releases awaiting approval for announcement
        //Stats
        $this->stroke($text);




    }
}

?>
