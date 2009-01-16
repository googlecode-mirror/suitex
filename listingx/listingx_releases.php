<?php

class listingx_releases {
	/**
	* The front-end methods for listingX.
 	* @package WordPress
 	*/
	function __construct($parent){
		global $wpdb;
		$this->wpdb   = $wpdb;
		$this->parent = $parent;

	}

	function run(){

        switch($_GET["releaseAction"]){
        	case "form":
        		$this->releaseForm();
        		break;

        	case "submit":
        	case "approve":
        	case "delete":
        		$this->submitForm();
        		break;

        }
		$this->parent->stroke($this->text);
	}

	function listReleases($project_id){
    	$pluginBase = 'wp-content' . DIRECTORY_SEPARATOR . 'plugins' . DIRECTORY_SEPARATOR . 'listingx';
    	require_once(ABSPATH . $pluginBase . DIRECTORY_SEPARATOR . 'listingx_list.php');
    	global $filter;

    	$list            = new listingx_list();
    	$list->search    = false;
    	$list->orderForm = false;
    	$list->omit      = array("cb");

    	$list->addFilter("r.lx_release_approved", "Approved", array("0" => "No", "1" => "Yes"));
    	$list->addFilter("r.lx_release_public", "Approved", array("0" => "No", "1" => "Yes"));

		$headers["cb"]                    = "<input type=\"checkbox\" />";
		$headers["r.lx_release_version"]  = "Version";
		$headers["u.user_login"]          = "Owner";
		$headers["r.lx_release_notes"]    = "Notes";
		$headers["r.lx_release_log"]      = "Log";
		$headers["r.lx_release_public"]   = "Public";
		$headers["r.lx_release_approved"] = "Approved";

		$order = "r.lx_release_version";
		$sort  = "asc";

		$query = "select r.lx_release_version as version, ";
		$query .= "u.user_login as owner, ";
		$query .= "r.lx_release_notes, ";
		$query .= "r.lx_release_log, ";
		$query .= "r.lx_release_public, ";
		$query .= "r.lx_release_approved ";
		$query .= "from " . $this->wpdb->prefix . "lx_release ";
		$query .= "left join " . $this->wpdb->prefix . "users on u.ID = r.user_id ";
		$query .= "where r.project_id = '$project_id' order by $order $sort";

     	$result = $this->wpdb->get_results($query);

     	foreach($result as $row){
        	$approved = $filter[$row->lx_project_approved];
           	$categories = $this->catForm("list", $row->lx_project_id);
        	$rows[$row->lx_project_id] = array($row->lx_project_name, $row->user_login, $categories, $approved);
     	}
        $url = "admin.php?page=projects&action=view&id=";
        $list->startList($headers, $url, $order, $sort, $rows, array("page" => "projects"));
        $text .= $list->text . "</div>";
		$this->text = $text;

	}

	function submitForm(){

	}

	function releaseForm(){

	}


}
?>
