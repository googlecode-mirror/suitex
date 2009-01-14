<?php

class listingx_projects {
	/**
	* The front-end methods for listingX.
 	* @package WordPress
 	*/

	function __construct($parent){
        global $wpdb;
        $this->wpdb = $wpdb;

        switch($_GET["action"]){
        	case "view":
        		$this->viewProject();
        		break;

        	case "form":
        		$this->projectForm();
        		break;

        	case "submit":
        		$this->submitForm();
        		break;

            case "approve":
            	$this->approveProject();
            	break;

			default:
				$this->listProjects();
				break;


        }
		$parent->stroke($this->text);
	}

	function catForm($current=array()){

	}

	function viewProject(){
        global $filter;
        $query = "select u.user_login, ";
        $query .= "p.lx_project_approved as approved, ";
        $query .= "p.lx_project_name as name, ";
        $query .= "p.lx_project_desc as `desc`, ";
        $query .= "p.lx_project_url as url, ";
        $query .= "p.lx_project_donate_url as donate, ";
        $query .= "p.lx_project_date_added as `date`, ";
        $query .= "p.lx_project_date_updated as updated ";
        $query .= "from " . $this->wpdb->prefix . "lx_project p ";
        $query .= "left join " . $this->wpdb->prefix . "users u on u.ID = p.user_id ";
        $query .= "where p.lx_project_id = '" . $_GET["id"] . "' limit 1";


        $row = $this->wpdb->get_row($query);

        $dateFormat = get_option("date_format") . ", " . get_option("time_format");

		$text = "<div class=\"wrap\">";
		$text .= "<h2>ListingX - Projects</h2>";
		$text .= "<div id=\"poststuff\" class=\"metabox-holder\">";
		$text .= "<div id=\"post-body\" class=\"has-sidebar\">";
		$text .= "<div id=\"post-body-content\" class=\"has-sidebar-content\">";


        $text .= "<div class=\"postbox\">";
        $text .= "<h3><label>View Project : " . $row->name . "</label></h3>";
		$text .= "<div class=\"inside\">";

        $text .= "<table class=\"form-table\">";
        $text .= "<tr class=\"form-field\">";
        $text .= "<td><strong>Project Name:</strong></td>";
        $text .= "<td>" . $row->name . "</td></tr>";
        $text .= "<tr class=\"form-field\">";
        $text .= "<td><strong>Project Owner:</strong></td>";
        $text .= "<td>" . $row->user_login . "</td></tr>";
        $text .= "<tr class=\"form-field\">";
        $text .= "<td><strong>Project Description:</strong></td>";
        $text .= "<td>" . $row->desc . "</td></tr>";

        $text .= "<tr class=\"form-field\">";
        $text .= "<td><strong>Project Categories:</strong></td>";
        $text .= "<td>" . $categories . "</td></tr>";


        $text .= "<tr class=\"form-field\">";
        $text .= "<td><strong>Project URL:</strong></td>";
        $text .= "<td><a href=\"" . $row->url . "\" target=\"_new\">" . $row->url . "</a></td></tr>";
        $text .= "<tr class=\"form-field\">";
        $text .= "<td><strong>Project Donate URL:</strong></td>";
        $text .= "<td><a href=\"" . $row->donate . "\" target=\"_new\">" . $row->donate . "</a></td></tr>";

        $text .= "<tr class=\"form-field\">";
        $text .= "<td><strong>Project Approved:</strong></td>";
        $text .= "<td>" . $filter[$row->approved] . "</td></tr>";
        $text .= "<tr class=\"form-field\">";
        $text .= "<td><strong>Project Added:</strong></td>";
        $text .= "<td>" . date($dateFormat, $row->date) . "</td></tr>";
        $text .= "<tr class=\"form-field\">";
        $text .= "<td><strong>Project Updated:</strong></td>";
        $text .= "<td>" . date($dateFormat, $row->updated) . "</td></tr>";
        $text .= "</table>";


		$nonce = wp_create_nonce();

        $text .= "<p class=\"submit\">";
        $text .= "<input type=\"button\" value=\"Modify\" onClick=\"goToURL('admin.php?page=projects&id=" . $_GET["id"] . "&action=form');\" />";
		$text .= " <input type=\"button\" value=\"Delete\" onClick=\"goToURL('admin.php?page=projects&id=" . $_GET["id"] . "&action=delete&nonce=$nonce');\" />";
		if ($row->approved == 0){
			$text .= " <input type=\"button\" value=\"Approve\" onClick=\"goToURL('admin.php?page=projects&id=" . $_GET["id"] . "&action=approve&nonce=$nonce');\" />";
		}
        $text .= "</p>";

        $text .= "</div></div></div></div></div>";
		$text .= "</div>";
		$this->text = $text;

	}

	function approveProject(){

	}

	function submitForm(){

	}

	function projectForm(){
        if ($_GET["id"]){
        	$query = "select p.lx_project_name as name ";
        	$query .= "from " . $this->wpdb->prefix . "lx_project p ";
        	$query .= "where p.lx_project_id = '" . $_GET["id"] . "' limit 1";


        	$row = $this->wpdb->get_row($query);
            print($query);
            print("ROW");
            print_r($row);

        	$action = "modify";
        	$label = "Modify Project:" . $row->name;


        }
        else {
        	$action = "add";
        	$label = "Add Project";
        }



        $text .= "<div class=\"wrap\">";


        $text .= "<h2>ListingX - Projects</h2>";
        $text .= "Use this page to manage your projects.";
        $text .= "<br />";

















        if ($message){ $text .= "<br /><b><span style=\"color:#FF0000;\">$message</span></b>"; }
		$text .= "<div id=\"poststuff\" class=\"metabox-holder\">";
		$text .= "<div id=\"post-body\" class=\"has-sidebar\">";
		$text .= "<div id=\"post-body-content\" class=\"has-sidebar-content\">";


        $text .= "<div class=\"postbox\">";
        $text .= "<h3><label>$label</label></h3>";
		$text .= "<div class=\"inside\">";
        $text .= "<form method=\"post\" action=\"\">";
        $text .= "<input type=\"hidden\" name=\"action\" value=\"$action\" />";

        $text .= "<table class=\"form-table\">";
        $text .= "<tr class=\"form-field form-required\">";
        $text .= "<th>";
        $text .= "<tr class=\"form-field form-required\">";
        $text .= "<th scope=\"row\" valign=\"top\"><label for=\"project_name\">Project Name:</label></th>";
        $text .= "<td>";
		$text .= "<input type=\"text\" name=\"project_name\" value=\"" . $row->name . "\" />";


        $text .= "</td></tr>";
        $text .= "</table>";
        $text .= "<p class=\"submit\"><input type=\"submit\" name=\"Submit\" value=\"Save Changes\" />";
        $text .= "</p></form>";
		$text .= "</div></div></div></div>";
		$text .= "</div></div>";
		$this->text = $text;
	}

	function listProjects(){
    	$pluginBase = 'wp-content' . DIRECTORY_SEPARATOR . 'plugins' . DIRECTORY_SEPARATOR . 'listingx';
    	require_once(ABSPATH . $pluginBase . DIRECTORY_SEPARATOR . 'listingx_list.php');
    	global $filter;

    	$list            = new listingx_list();
    	$list->search    = true;
    	$list->orderForm = true;
    	$list->omit      = array("cb");

    	$list->addFilter("p.lx_project_approved", "Approved", array("0" => "No", "1" => "Yes"));



		$text = "<div class=\"wrap\">";
		$text .= "<h2>ListingX - Projects</h2>";
		$text .= "<a href=\"?page=projects&action=form&sub=add\">Add Project</a>";

		$headers["cb"]                    = "<input type=\"checkbox\" />";
		$headers["p.lx_project_name"]     = "Project Name";
		$headers["u.user_login"]          = "Owner";
		$headers["c.lx_project_cat_name"] = "Categories";
		$headers["p.lx_project_approved"] = "Approved";


		$order = "p.lx_project_name";
		$sort  = "asc";

     	$query  = "select p.lx_project_id, p.lx_project_name, u.user_login, p.lx_project_approved from ";
     	$query .= $this->wpdb->prefix . "lx_project p left join " . $this->wpdb->prefix . "users u on u.ID = p.user_id order by $order $sort";

     	$result = $this->wpdb->get_results($query);


     	foreach($result as $row){
        	$approved = $filter[$row->lx_project_approved];

			$categories = '';
        	$query2 = "select c.lx_project_cat_id, c.lx_project_cat_name from " . $this->wpdb->prefix . "lx_project_cat c left join ";
        	$query2 .= $this->wpdb->prefix . "lx_project_cat_link l on l.lx_project_cat_id = c.lx_project_cat_id ";
        	$query2 .= "where l.lx_project_id = '" . $row->lx_project_id . "' order by c.lx_project_cat_name asc";
        	$cats = $this->wpdb->get_results($query2);
        	foreach($cats as $c){
        		$categories .= "<a href=\"admin.php?page=categories&id=" . $c->lx_project_cat_id . "&action=form\">";
        		$categories .= $c->lx_project_cat_name . "</a>, ";
        	}
        	$categories = substr($categories, 0, -2);
        	$rows[$row->lx_project_id] = array($row->lx_project_name, $row->user_login, $categories, $approved);

     	}
        $url = "admin.php?page=projects&action=view&id=";

        $list->startList($headers, $url, $order, $sort, $rows, array("page" => "projects"));

        $text .= $list->text . "</div>";

		$this->text = $text;




	}

}
?>
