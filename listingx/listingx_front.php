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
		if ($this->options["page_id"] != $id){
			$query = "select lx_project_page_id from " . $this->wpdb->prefix . "lx_project where lx_project_approved = '1'";
			$row = $this->wpdb->get_row($query);

			foreach($row as $r){
				$idArray[] = $r;
			}
			if (in_array($id, $idArray)){
				$this->listingx_viewProject($id);
			}
		}
		else {
        	$this->text = $this->wpdb->get_var("select post_content from " . $this->wpdb->prefix . "posts where ID = '$id' limit 1");
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

		global $post;

		$options = get_option('listingx_options');
		if ($post->ID == $options["download_page_id"]){
			global $wpdb;

        	if (!$_GET["file"]){ die("Invalid File"); }

        	$q = "select lx_file_name, lx_file_type, lx_file_data, lx_file_size from " . $wpdb->prefix . "lx_file where lx_file_id = %d limit 1";
        	$row = $wpdb->get_row($wpdb->prepare($q, $_GET["file"]));

        	$q = "update " . $wpdb->prefix . "lx_file set lx_file_download = lx_file_download + 1 where lx_file_id = %d";
        	$wpdb->query($wpdb->prepare($q, $_GET["file"]));



       		header("Content-length: " . $row->lx_file_size);
			header("Content-type:" . $row->lx_file_type . ";name='" . $fileTypeArray[$row->lx_file_type] . "'");
			header("Content-Disposition:attachment;filename='" . $row->lx_file_name . "'");
			print($row->lx_file_data);
			die();
		}

	}
}
?>
