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
    	$list->fold		 = true;

    	$rows = array();

		$headers["cb"]                    = "<input type=\"checkbox\" />";
		$headers["r.lx_release_version"]  = "Version";
		$headers["u.user_login"]          = "Owner";
		$headers["r.lx_release_notes"]    = "Notes";
		$headers["r.lx_release_log"]      = "Log";
		$headers["r.lx_release_public"]   = "Public";
		$headers["r.lx_release_approved"] = "Approved";

		$query = "select r.lx_release_version as version, ";
		$query .= "r.lx_release_id as id, ";
		$query .= "u.user_login as owner as user, ";
		$query .= "r.lx_release_notes as notes, ";
		$query .= "r.lx_release_log as log, ";
		$query .= "r.lx_release_public as public, ";
		$query .= "r.lx_release_approved as approved ";
		$query .= "from " . $this->wpdb->prefix . "lx_release ";
		$query .= "left join " . $this->wpdb->prefix . "users on u.ID = r.user_id ";
		$query .= "where r.project_id = '$project_id' order by r.lx_release_version asc";

     	$result = $this->wpdb->get_results($query);

     	foreach($result as $row){
        	$approved = $filter[$row->approved];
        	$public   = $filter[$row->public];
        	$rows[$row->id] = array($row->version, $row->user, $row->notes, $row->log, $public, $approved);
     	}
        //$url = "admin.php?page=projects&action=view&id=";
        $list->startList($headers, $url, '', '', $rows, array("page" => "projects"));
        $text .= $list->text . "</div>";
		return $text;

	}

	function submitForm(){
		if ($_GET["releaseAction"] == "approve"){
			$this->wpdb->query($this->wpdb->prepare($q, %d));//stopped

		}


	}

	function releaseForm(){

	}


}
?>
