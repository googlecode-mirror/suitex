<?php

class listingx_front {
	/**
	* Front End
 	* @package WordPress
 	*/

	function listingx_run(){

        global $wpdb;
		global $post;

        $this->options = get_option('listingx_options');
    	$this->wpdb = $wpdb;

    	if ($_GET["action"]){
    		$action = $_GET["action"];
    	}
    	else if ($_POST["action"]){
    		$action = $_POST["action"];
    	}

    	if ($post->ID == $this->options["download_page_id"]){
        	$action = "getFile";
    	}
		else if ($this->options["page_id"] != $post->ID){
			$query = "select lx_project_page_id from " . $this->wpdb->prefix . "lx_project where lx_project_approved = '1'";
			$row = $this->wpdb->get_row($query);

			foreach($row as $r){
				$idArray[] = $r;
				if ($post->ID == $r){
					$action = "view";
					break;
				}
			}
		}
		/*else {
        	$this->text = $this->wpdb->get_var("select post_content from " . $this->wpdb->prefix . "posts where ID = '$id' limit 1");
        	return $this->text;
		}*/

        $this->projectPage = $this->wpdb->get_var("select guid from " . $this->wpdb->prefix . "posts where ID = '" . $this->options["page_id"] . "' limit 1");

    	$this->pluginBase = 'wp-content' . DIRECTORY_SEPARATOR . 'plugins' . DIRECTORY_SEPARATOR . 'listingx';
    	require_once(ABSPATH . $this->pluginBase . DIRECTORY_SEPARATOR . 'listingx_list_front.php');

       	switch($action){
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

       	    case "view":
       	    	$this->listingx_viewProject($post->ID);
       	    	break;

       	    case "getFile":
       	    	$this->listingx_getFile();
       	    	break;

       	    default:
       	    	$this->listingx_page();
       	    	break;
    	}
    	if ($this->text){
		   	add_filter('the_content', array($this, 'stroke'));
		}

	}

	function stroke(){
		$text = "<script type=\"text/javascript\" src=\"wp-content/plugins/listingx/listingx.js\"></script>";
		$this->text = $text . $this->text;
		return $this->text;
	}

	function listingx_searchProjects(){
    	require_once(ABSPATH . $this->pluginBase . DIRECTORY_SEPARATOR . 'listingx_projects.php');
    	$this->project = new listingx_projects($this, false);
    	$this->project->frontEnd = true;
    	$this->project->projectPage = $this->projectPage;
    	global $filter;


//		$text = "<div class=\"wrap\">";
//		$text .= "<h2>ListingX - Projects</h2>";
//		$text .= "<a href=\"?page=lx_projects&action=form&sub=add\">Add Project</a>";
//		$text .= $this->parent->message;
        $text = "<table>";

		$order = "p.lx_project_name";
		$sort  = "asc";

     	$query  = "select p.lx_project_desc, post.guid, p.lx_project_id, p.lx_project_name, u.user_login from ";
     	$query .= "(" . $this->wpdb->prefix . "lx_project p, " . $this->wpdb->prefix . "posts post) ";
     	$query .= "left join " . $this->wpdb->prefix . "users u on u.ID = p.user_id";
     	if ($_GET["category_id"]){
     		$query .= " left join " . $this->wpdb->prefix . "lx_project_cat_link pcl on p.lx_project_id = pcl.lx_project_id ";
     		$query .= "where pcl.lx_project_cat_id = '" . $_GET["category_id"] . "' and ";

     	}
     	else {
     		$query .= "where";
     	}
     	$query .= "  p.lx_project_page_id = post.ID and p.lx_project_approved = 1 ";
     	$query .= " order by $order $sort";
print($query);
     	$result = $this->wpdb->get_results($query);

     	foreach($result as $row){
        	if (strlen($row->lx_project_desc) > 500){
        		$desc = substr($row->lx_project_desc, 0, 500) . "...";
        	}
        	else {
        		$desc = $row->lx_project_desc;
        	}


        	$categories = $this->project->catForm("list", $row->lx_project_id);
        	$text .= "<tr>";
        	$text .= "<td>";
        	$text .= "<strong><a href=\"" . $row->guid . "\">" . $row->lx_project_name . "</a></strong><br />";
        	$text .= $desc . "<br /><i>$categories</i>";
        	$text .= "</td></tr>";
     	}



        $text .= "</table>";
		$this->text = $text;

	}

	function listingx_page(){
		//default page
	}

	function listingx_viewProject($id){

    	require_once(ABSPATH . $this->pluginBase . DIRECTORY_SEPARATOR . 'listingx_projects.php');

    	$this->project = new listingx_projects($this, false);
    	$this->project->frontEnd = true;
    	$this->project->projectPage = $this->projectPage;
    	$project_id = $this->wpdb->get_var("select lx_project_id from " . $this->wpdb->prefix . "lx_project where lx_project_page_id = '$id' limit 1");
        global $user_ID;
        global $filter;
        $query = "select u.user_login, ";
        $query .= "p.lx_project_approved as approved, ";
        $query .= "p.lx_project_name as name, ";
        $query .= "p.lx_project_desc as `desc`, ";
        $query .= "p.lx_project_url as url, ";
        $query .= "p.lx_project_donate_url as donate, ";
        $query .= "p.lx_project_date_added as `date`, ";
        $query .= "p.lx_project_date_updated as updated, ";
        $query .= "u.ID as user ";
        $query .= "from " . $this->wpdb->prefix . "lx_project p ";
        $query .= "left join " . $this->wpdb->prefix . "users u on u.ID = p.user_id ";
        $query .= "where p.lx_project_id = '" . $project_id . "' limit 1";


        $row = $this->wpdb->get_row($query);
        $categories = $this->project->catForm("list", $project_id);
        $users = $this->project->getUsers($project_id);


        $dateFormat = get_option("date_format") . ", " . get_option("time_format");

		$text = "<div class=\"wrap\">";

        //$text .= "<div id=\"download\">DOWNLOAD</div>";

        $text .= "<h3><label>View Project : " . $row->name . "</label></h3>";
        $text .= str_replace("\r\n", "<br />", $row->desc);
        $text .= "<br /><br />";
        $text .= "<a name=\"tabs\" />";

        $text .= "<ul class=\"menu\">";
        $text .= "<li id=\"tab1\" class=\"selected\" onClick=\"showTab('1');\"><a href=\"#tabs\">Details</a></li>";
        $text .= "<li id=\"tab2\" onClick=\"showTab('2');\"><a href=\"#tabs\">Releases</a></li>";
        if ($user_ID == $row->user){
        	$text .= "<li id=\"tab3\" onClick=\"showTab('3');\"><a href=\"#tabs\">Admin</a></li>";
        }
        $text .= "</ul>";


        $text .= "<div id=\"vtab1\">";
        $text .= "<br />";
        $text .= "<table class=\"form-table\">";
        $text .= "<tr class=\"form-field\">";
        $text .= "<td><strong>Project Owner:</strong></td>";
        $text .= "<td>" . $row->user_login . "</td></tr>";

        $text .= "<tr class=\"form-field\">";
        $text .= "<td><strong>Project Developers:</strong></td>";
        $text .= "<td>" . $users . "</td></tr>";

        $text .= "<tr class=\"form-field\">";
        $text .= "<td><strong>Project Added:</strong></td>";
        $text .= "<td>" . date($dateFormat, $row->date) . "</td></tr>";
        $text .= "<tr class=\"form-field\">";
        $text .= "<td><strong>Project Updated:</strong></td>";
        $text .= "<td>" . date($dateFormat, $row->updated) . "</td></tr>";



        $text .= "<tr class=\"form-field\">";
        $text .= "<td colspan=\"2\">";
        $text .= "<a href=\"" . $row->url . "\" target=\"_new\">Project Homepage</a></td></tr>";
        $text .= "<tr class=\"form-field\">";
        $text .= "<td colspan=\"2\">";
        $text .= "<a href=\"" . $row->donate . "\" target=\"_new\">Donate to this Project</a></td></tr>";

        $text .= "<tr class=\"form-field\">";
        $text .= "<td colspan=\"2\">";
        $text .= "<strong>Categories:</strong><br />$categories";
        $text .= "</td></tr>";



        $text .= "</table>";



        $text .= "</div>";
        $text .= "<div id=\"vtab2\">";



       	$releaseList = $this->listReleases($project_id);

        $text .= $releaseList;
        $text .= "</div>";


        if ($user_ID == $row->user){
	        $text .= "<div id=\"vtab3\">";
			$nonce = wp_create_nonce();

	        $text .= "<p class=\"submit\"><br /><br />";
        	$text .= "<input type=\"button\" value=\"Modify Project\" onClick=\"goToURL('" . $this->projectPage . "?project_id=" . $id . "&action=modifyProject');\" />";
        	$text .= "<br /><br /> <input type=\"button\" value=\"Change Users\" onClick=\"goToURL('" . $this->projectPage . "?project_id=" . $id . "&action=projectUser');\" />";
			$text .= "<br /><br /> <input type=\"button\" value=\"Add Release\" onClick=\"goToURL('" . $this->projectPage . "?project_id=" . $id . "&action=addRelease');\" />";
        	$text .= "</p>";
        	$text .= "</div>";

        }


		$text .= "</div>";
		$this->text = $text;
	}

	function listingx_getFile(){

        $fileTypeArray['image/gif'] = "GIF Image";
        $fileTypeArray['application/vnd.ms-excel'] = "MS Excel";
        $fileTypeArray['text/plain'] = "ASCII Text";
        $fileTypeArray['application/pdf'] = "Adobe PDF";
        $fileTypeArray['application/x-zip-compressed'] = "ZIP";
        $fileTypeArray['text/html'] = "HTML";
        $fileTypeArray['image/x-photoshop'] = "Adobe PSD";
        $fileTypeArray['video/x-mpeg'] = "MPEG Video";
        $fileTypeArray['video/mpeg'] = "MPEG Video";
        $fileTypeArray['video/msvideo'] = "AVI Video";
        $fileTypeArray['video/x-msvideo'] = "AVI Video";
        $fileTypeArray['video/quicktime'] = "QuickTime Video";
        $fileTypeArray['video/x-quicktime'] = "QuickTime Video";
        $fileTypeArray['audio/mpeg3'] = "MP3 Audio";
        $fileTypeArray['audio/x-mpeg3'] = "MP3 Audio";
        $fileTypeArray['audio/mpeg'] = "MP3 Audio";
        $fileTypeArray['audio/x-mpeg'] = "MP3 Audio";
        $fileTypeArray['audio/wav'] = "WAV Audio";
        $fileTypeArray['audio/x-wav'] = "WAV Audio";
        $fileTypeArray['image/tiff'] = "TIFF Image";
        $fileTypeArray['image/x-tiff'] = "TIFF Image";
        $fileTypeArray['image/jpeg'] = "JPEG Image";
        $fileTypeArray['image/pjpeg'] = "JPEG Image";
        $fileTypeArray['image/x-MS-bmp'] = "Bitmap Image";
        $fileTypeArray['image/x-bmp'] = "Bitmap Image";
        $fileTypeArray['image/png'] = "PNG Image";
        $fileTypeArray['application/msword'] = "MS Word";
        $fileTypeArray['application/wordperfect'] = "Word Perfect";
        $fileTypeArray['application/rtf'] = "Rich Text Format";
        $fileTypeArray['application/vnd.ms-powerpoint'] = "MS Powerpoint";
        $fileTypeArray['application/x-tar'] = "TAR";
        $fileTypeArray['application/x-gzip'] = "GZIP";
        $fileTypeArray['application/x-gzip-compressed'] = "Tarball";
        $fileTypeArray['application/x-shockwave-flash'] = "Macromedia Flash";
        $fileTypeArray['application/x-director'] = "Macromedia Director";
        $fileTypeArray['application/x-pilot'] = "Palm OS File";
        $fileTypeArray['video/vnd.rn-realvideo'] = "Real Audio";
        $fileTypeArray['application/vnd.rn-realaudio'] = "Real Audio";
        $fileTypeArray['application/msaccess'] = "MS Access";
        $fileTypeArray['image/x-png'] = "PNG Image";
        $fileTypeArray['application/octet-stream'] = "Unspecified Application";
        $fileTypeArray['application/vnd.visio'] = "Visio";
        $fileTypeArray['application/acad'] = "AutoCad";
        $fileTypeArray['application/java-archive'] = "JAVA Archive";
        $fileTypeArray['application/msproject'] = "MS Project";
        $fileTypeArray['application/vnd.ms-project'] = "MS Project";
        $fileTypeArray['application/postscript'] = "Adobe Postscript File (eps)";
        $fileTypeArray['application/x-dwf'] = "AutoCad";
        $fileTypeArray['application/x-javascript'] = "JavaScript";
        $fileTypeArray['text/xml'] = "JavaScript";

       	if (!$_GET["file"]){ die("Invalid File"); }

       	$q = "select lx_file_name, lx_file_type, lx_file_data, lx_file_size from " . $this->wpdb->prefix . "lx_file where lx_file_id = %d limit 1";
       	$row = $this->wpdb->get_row($this->wpdb->prepare($q, $_GET["file"]));

       	$q = "update " . $this->wpdb->prefix . "lx_file set lx_file_download = lx_file_download + 1 where lx_file_id = %d";
       	$this->wpdb->query($this->wpdb->prepare($q, $_GET["file"]));



   		header("Content-length: " . $row->lx_file_size);
		header("Content-type:" . $row->lx_file_type . ";name='" . $fileTypeArray[$row->lx_file_type] . "'");
		header("Content-Disposition:attachment;filename='" . $row->lx_file_name . "'");
		print($row->lx_file_data);
		die();

	}

    function listReleases($project_id){
    	global $filter;

    	$list            = new listingx_list_front();
    	$list->search    = false;
    	$list->orderForm = false;
    	$list->omit      = array("cb");
    	$list->fold      = true;

    	$rows = array();

        $headers["r.lx_release_version"]  = "Version";
        $headers["u.user_login"]          = "Owner";
        $headers["r.lx_release_notes"]    = "Notes";
        $headers["r.lx_release_log"]      = "Log";
        $headers["r.lx_release_public"]   = "Public";
        $headers["r.lx_release_approved"] = "Approved";

        $query = "select r.lx_release_version as version, ";
        $query .= "r.lx_release_id as id, ";
        $query .= "u.user_login as owner, ";
        $query .= "r.lx_release_notes as notes, ";
        $query .= "r.lx_release_log as log, ";
        $query .= "r.lx_release_public as public, ";
        $query .= "r.lx_release_approved as approved ";
        $query .= "from " . $this->wpdb->prefix . "lx_release r ";
        $query .= "left join " . $this->wpdb->prefix . "users u on u.ID = r.user_id ";
        $query .= "where r.lx_project_id = '$project_id' order by r.lx_release_version desc";

    	$result = $this->wpdb->get_results($query);

        $x=1;
    	foreach($result as $row){
            $approved = $filter[$row->approved];
            $public   = $filter[$row->public];
            $rows[$row->id] = array($row->version, $row->owner, $row->notes, $row->log, $public, $approved);

            $query = "select lx_file_id as id, lx_file_name as name, lx_file_size as size, ";
            $query .= "lx_file_type as type, lx_file_download as download from " . $this->wpdb->prefix . "lx_file where ";
            $query .= "lx_release_id = '" . $row->id . "'";
            $result1 = $this->wpdb->get_results($query);

            $s = array();
            foreach($result1 as $r){
            	$s[] = array("id" => $r->id, "name" => $r->name, "size" => $r->size, "type" => $r->type, "download" => $r->download);
            }
            $rows[$row->id]["sub"] = $s;
            if ($x == 1){
            	$openSub .= "openSub('release-" . $row->id . "', 'image-" . $row->id . "', 'http://localhost/wp/wp-content/plugins/listingx');";
            }

            $x++;
    	}
    	$url = $this->projectPage . "?action=modifyRelease&id=";
    	$list->startList($headers, $url, '', '', $rows, array("page" => "lx_projects"));
    	$text .= $list->text;
    	$text .= "<script langauge=\"javascript\">$openSub</script>";


        return $text;

    }
}
?>
