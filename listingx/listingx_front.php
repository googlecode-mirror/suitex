<?php

class listingx_front {
	/**
	* Front End
 	* @package WordPress
 	*/

	function listingx_run(){

        global $wpdb;

        $this->options = get_option('listingx_options');
    	$this->wpdb = $wpdb;

    	switch($_GET["action"]){
    		case "addProject":
    			$this->listingx_addProject();
    	        break;

    	    case "modifyProject":
    	    	$this->listingx_modifyProject();
    	    	break;

    	    case "addRelease":
    	    	$this->listingx_addRelease();
    	    	break;

    	    case "modifyRelease":
    	    	$this->listingx_modifyRelease();
    	    	break;

    	    case "profile":
    	    	$this->listingx_profile();
    	    	break;

    	    case "users":
    	    	$this->listingx_users();
    	    	break;

    	    case "projectUser":
    	    	$this->listingx_projectUser();
    	    	break;

    	    case "search":
    	    	$this->listingx_searchProjects();
    	    	break;

    	    default:
    	    	$this->listingx_page();
    	    	break;
		}
		return $this->text;
	}

	function listingx_page(){
		global $id;
		if ($this->options["page_id"] == $id){
			$this->listingx_defaultPage();
		}
		else {
			$query = "select lx_project_page_id from " . $this->wpdb->prefix . "lx_project where lx_project_approved = '1'";
			$row = $this->wpdb->get_row($query);

			foreach($row as $r){
				$idArray[] = $r;
			}
			if (in_array($id, $idArray)){
				$this->listingx_viewProject($id);
			}
		}
	}

	function listingx_viewProject($id){

    	$project_id = $this->wpdb->get_var("select lx_project_id from " . $this->wpdb->prefix . "lx_project where lx_project_page_id = '$id' limit 1");

    	$pluginBase = 'wp-content' . DIRECTORY_SEPARATOR . 'plugins' . DIRECTORY_SEPARATOR . 'listingx';
    	require_once(ABSPATH . $pluginBase . DIRECTORY_SEPARATOR . 'listingx_projects.php');
    	$this->projects = new listingx_projects($this, false);
    	$this->projects->viewProject($project_id);
    	$this->text = $this->projects->text;
	}
}
?>
