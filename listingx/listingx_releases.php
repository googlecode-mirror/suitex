<?php

class listingx_releases {
	/**
	* The front-end methods for listingX.
 	* @package WordPress
 	*/
    var $parent;
    var $wpdb;
    
	function __construct($parent){
		$this->wpdb   = $parent->wpdb;
		$this->parent = $parent;

	}

	function run(){

        switch($_GET["releaseAction"]){
        	case "form":
        		$this->releaseForm();
        		break;

        	case "add":
        	case "modify":
        	case "approve":
        	case "delete":
        		$this->submitForm();
        		break;

        }
		$this->parent->text = $this->text;
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
        //$url = "admin.php?page=lx_projects&action=view&id=";
        $list->startList($headers, $url, '', '', $rows, array("page" => "lx_projects"));
        $text .= $list->text . "</div>";
		return $text;

	}

	function submitForm(){
	    if ($_POST["_wpnonce"]){ $nonce = $_POST["_wpnonce"]; }
        else if ($_GET["_wpnonce"]){ $nonce = $_GET["_wpnonce"]; }

        if (!wp_verify_nonce($nonce)){ die('Security check'); }
        
        if ($_GET["releaseAction"] == "approve"){
            $q = "select p.lx_project_id as project_id, ";
            $q .= "p.lx_project_page_id as page_id, ";
            $q .= "p.lx_project_desc as project_desc, ";
            $q .= "p.lx_project_name as name, ";
            $q .= "r.lx_release_version as version, ";
            $q .= "r.lx_release_public as public, ";
            $q .= "r.lx_release_log as log, ";
            $q .= "r.user_id as user ";
            $q .= "from  " . $this->wpdb->prefix . "lx_project p, " . $this->wpdb->prefix . "lx_release r where r.lx_project_id = p.lx_project_id and r.lx_release_id = %d";
            $row = $this->wpdb->get_row($this->wpdb->prepare($q, $_GET["id"]));

            $link = $this->wpdb->get_var("select guid from " . $this->wpdb->prefix . "post where ID = '" . $row->page_id . "' limit 1");
            $link = "<a href=\"" . $link . "\">Project Homepage</a>";

			$q = "update " . $this->wpdb->prefix . "lx_release set lx_release_approved = 1 where lx_release_id = %d limit 1";
			$this->wpdb->query($this->wpdb->prepare($q, $_GET["id"]));

  			if ($row->public == 1){
    			$body = $this->options["newReleaseText"];
				$body = str_replace("::PROJECTPAGE::", $link, $body);
				$body = str_replace("::DESC::", $row->project_desc, $body);
				$body = str_replace("::LOG::", $row->log, $body);
				$body = str_replace("::CATEGORIES::", $this->parent->catForm("list", $row->project_id));

                $cat_id = $this->wpdb->get_var("select term_id from " . $this->wpdb->prefix . "terms where slug = 'new-release' limit 1");

                $name = $row->name . " " . $row->version;

				$page = array();
        		$page['post_type']      = 'post';
        		$page['post_title']     = $name;
        		$page['post_name']      = $name;
        		$page['post_status']    = 'publish';
    	    	$page['comment_status'] = 'open';
	        	$page['post_content']   = $body;
        		$page['post_category']  = array($cat_id);
        		$page['post_author']    = $row->user;
				$page_id = wp_insert_post($page);

				wp_publish_post($page_id);

            }

			$url = "admin.php?page=lx_projects&code=rap&action=release&releaseAction=modify&id=" . $_GET["id"];
		}
		else if ($_GET["releaseAction"] == "delete"){
			$q = "select lx_project_id from " . $this->wpdb->prefix . "lx_relase where lx_release_id = %d limit 1";
			$project_id = $this->wpdb->get_var($this->wpdb->prepare($q, $_GET["id"]));
			$q = "delete from " . $this->wpdb->prefix . "lx_release where lx_release_id = %d limit 1";
			$q2 = "delete from " . $this->wpdb->prefix . "lx_file where lx_release_id = %d";
			$this->wpdb->query($this->wpdb->prepare($q, $_GET["id"]));
			$this->wpdb->query($this->wpdb->prepare($q2, $_GET["id"]));
			$url = "admin.php?page=lx_projects&action=view&id=$project_id&code=rd";

		}
		else if ($_GET["releaseAction"] == "add"){
		    global $user_ID; 
            $q = "select p.lx_project_id as project_id, ";
            $q .= "p.lx_project_page_id as page_id, ";
            $q .= "p.lx_project_desc as project_desc, ";
            $q .= "p.lx_project_name as name ";
            $q .= "from " . $this->wpdb->prefix . "lx_project where lx_project_id = '" . $_POST["projec_id"] . "' limit 1";
            $row = $this->wpdb->get_row($q);

            $link = $this->wpdb->get_var("select guid from " . $this->wpdb->prefix . "post where ID = '" . $row->page_id . "' limit 1");
            $link = "<a href=\"" . $link . "\">Project Homepage</a>";
            
            $version = strip_tags(htmlentities($_POST["version"]));
            $log = str_replace("\r\n", "<br />", strip_tags(htmlentities($_POST["log"])));
            $notes = str_replace("\r\n", "<br />", strip_tags(htmlentities($_POST["notes"]))); 
            
            $name = $row->name . " " . $version;
            
            $q = "insert into " . $this->wpdb->prefix . "lx_release ";
            $q .= "(lx_project_id, user_id, lx_release_date, lx_release_version, lx_release_public, lx_release_approved, lx_release_notes, lx_release_log) ";
            $q .= "values (%d, %d, %d, %s, %d, %d, %s, %s)";
            $this->wpdb->query($this->wpdb->prepare($q, $_POST["project_id"], $user_ID, time(), $version, $_POST["public"], 1, $notes, $log));
            $release_id = $this->wpdb->insert_id;           
            
            if ($_FILES){
                for($i=1;$i<5;$i++){
                    $arrayName = "file" . $i;
                    if ($_FILES[$arrayName]["name"] != ''){
                        $fileName = $_FILES[$arrayName]["name"];
                        $fileType = $_FILES[$arrayName]["type"];
                        $fileSize = $_FILES[$arrayName]["size"];
                        $fp       = fopen($_FILES[$arrayName]["tmp_name"], 'r');
                        $fileData = fread($fp, filesize($_FILES[$arrayName]["tmp_name"]));
                        $fileData = addslashes($fileData);
                        fclose($fp);
                        
                        $q = "insert into " . $this->wpdb->prefix . "lx_file";
                        $q .= "(lx_release_id, user_id, lx_file_name, lx_file_type, lx_file_size, lx_file_data, lx_file_date_added, lx_file_date_updated, lx_file_download) ";
                        $q .= "values (%d, %d, %s, %s, %d, %s, %d, %d, %d)";
                        $this->wpdb->query($this->wpdb->prepare($q, $release_id, $user_ID, $fileName, $fileType, $fileSize, $fileData, time(), time(), 0));                        
                    }
                }
            }
			
			if ($_POST["public"] == 1){
    			$body = $this->options["newReleaseText"];
				$body = str_replace("::PROJECTPAGE::", $link, $body);
				$body = str_replace("::DESC::", $row->project_desc, $body);
				$body = str_replace("::LOG::", $log, $body);
				$body = str_replace("::CATEGORIES::", $this->parent->catForm("list", $row->project_id));

                $cat_id = $this->wpdb->get_var("select term_id from " . $this->wpdb->prefix . "terms where slug = 'new-release' limit 1");

                $name = $row->name . " " . $row->version;

				$page = array();
        		$page['post_type']      = 'post';
        		$page['post_title']     = $name;
        		$page['post_name']      = $name;
        		$page['post_status']    = 'publish';
    	    	$page['comment_status'] = 'open';
	        	$page['post_content']   = $body;
        		$page['post_category']  = array($cat_id);
        		$page['post_author']    = $user_ID;
				$page_id = wp_insert_post($page);

				wp_publish_post($page_id);

            }
            $url = "admin.php?page=lx_projects&action=view&id=$project_id&code=ra"; 

		}
		else if ($_GET["releaseAction"] == "modify"){

		}

		$this->parent->parent->pageDirect($url);
	}
    
    function processFile($release_id, $fileNumber){
        
        
    }

	function releaseForm(){
		global $filter;
		if ($_GET["id"]){
			$label = "Modify Release";
			$action = "modify";
			
			
			
		}   
		else {
			$label = "Add Release";
			$action = "add";
			
			
		}     


		$text = "<div class=\"wrap\">";
        $text .= "<h2>ListingX - Release</h2>";
        $text .= "<br />";


		$text .= "<div id=\"poststuff\" class=\"metabox-holder\">";
		$text .= "<div id=\"post-body\" class=\"has-sidebar\">";
		$text .= "<div id=\"post-body-content\" class=\"has-sidebar-content\">";
        $text .= "<div class=\"postbox\">";
        $text .= "<h3><label>$label</label></h3>";
		$text .= "<div class=\"inside\">";
        $text .= "<form enctype=\"multipart/form-data\" method=\"post\" action=\"admin.php?page=lx_projects&action=release&releaseAction=$action\">";
        $text .= "<input type=\"hidden\" name=\"_wpnonce\" value=\"" . wp_create_nonce() . "\" />";
        $text .= "<input type=\"hidden\" name=\"action\" value=\"$action\" />";
        $text .= "<input type=\"hidden\" name=\"project_id\" value=\"" . $_GET["project_id"] . "\" />";
        if ($_GET["id"]){
        	$text .= "<input type=\"hidden\" name=\"id\" value=\"" . $_GET["id"] . "\" />";
        }
        $text .= "<table class=\"form-table\">";
        $text .= "<tr class=\"form-field\">";
        $text .= "<td><strong>Release Version:</strong></td>";
        $text .= "<td><input type=\"text\" name=\"version\" value=\"" . $row->version . "\" />";
        $text .= "</td></tr>";
        $text .= "<tr class=\"form-field\">";
        $text .= "<td><strong>Release Notes:</strong></td>";
        $text .= "<td><textarea name=\"notes\">" . $row->notes . "</textarea>";
        $text .= "</td></tr>";
        $text .= "<tr class=\"form-field\">";
        $text .= "<td><strong>Release ChangeLog:</strong></td>";
        $text .= "<td><textarea name=\"log\">" . $row->log . "</textarea>";
        $text .= "</td></tr>";      
        
        $text .= "<tr class=\"form-field\">";
        $text .= "<td><strong>Release Public:</strong></td>";
        $text .= "<td>";
        $text .= "<select name=\"public\">";
        for($i=0;$i<2;$i++){
        	if ($row->public == $i){ $s = "selected"; }
        	else { $s = ''; }
        	$text .= "<option value=\"$i\">" . $filter[$i] . "</option>";
        }
        $text .= "</select></td></tr>";            


        $text .= "<tr class=\"form-field\">";
        $text .= "<td><strong>File 1:</strong></td>";
        $text .= "<td><input type=\"file\" name=\"file1\" /><br />";
        $text .= $file1;
        $text .= "</td></tr>";

        $text .= "<tr class=\"form-field\">";
        $text .= "<td><strong>File 2:</strong></td>";
        $text .= "<td><input type=\"file\" name=\"file2\" /><br />";
        $text .= $file2;
        $text .= "</td></tr>";
        
        $text .= "<tr class=\"form-field\">";
        $text .= "<td><strong>File 3:</strong></td>";
        $text .= "<td><input type=\"file\" name=\"file3\" /><br />";
        $text .= $file3;
        $text .= "</td></tr>";

        $text .= "<tr class=\"form-field\">";
        $text .= "<td><strong>File 4:</strong></td>";
        $text .= "<td><input type=\"file\" name=\"file4\" /><br />";
        $text .= $file4;
        $text .= "</td></tr>";        
        $text .= "</table>";       
        
        
        $text .= "<p class=\"submit\"><input type=\"submit\" name=\"Submit\" value=\"Save Changes\" />";
        $text .= "</p></form>";
		$text .= "</div></div></div></div>";
		$text .= "</div></div>";
		$this->text = $text;
        
        
	}


}
?>
