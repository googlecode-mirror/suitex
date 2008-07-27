<?php

/**
 * The functions for WineX, both admin and front-end
 *
 * @package WordPress
 */

class wineX {
    var $basePath;

    /**
    * The construct function for the wineX class.  It adds a path variable to class.
    *
    * @param NULL
    * @return NULL
    */

    function __construct(){
        global $pluginBase;
        $this->contentFile = $pluginBase . DIRECTORY_SEPARATOR . 'winex.contents';
    }

    /**
    * Fetches the wine list from CellarTracker and creates the cache file
    *
    * @param array $wArray winex options array
    * @return string $text page content
    */

    function winex_fetchWineList($wArray){

    	if ($wArray['user_id']){
        	$date = date('Ymd');
    		$url = 'http://www.cellartracker.com/list.asp?iUserOverride=' . $wArray['user_id'] . '&Page=0';

    		if (function_exists('curl_init')){
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url );
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                $lines = curl_exec($ch);
                curl_close($ch);
            }
            else {
                $lines = file_get_contents($url);
            }


            $start = "<table width='100%' class='editList'>";
            $end = "<!-- END MAIN PAGE -->";
            $lines = substr($lines, strpos($lines, $start));
            $lines = substr($lines, 0, strpos($lines, $end));
            $trans["href='wine.asp"] = " target='_new' href='http://www.cellartracker.com/wine.asp";
            $trans["href='list.asp"] = " target='_new' href='http://www.cellartracker.com/list.asp";
            $trans['/images/'] = "http://www.cellartracker.com/images/";
            $trans['images/camera.gif'] = "http://www.cellartracker.com/images/camera.gif";
            $trans['lbl_disp.asp'] = "http://www.cellartracker.com/lbl_disp.asp";
            $trans['<i>'] = "<br><i>";
            $trans['</th>'] = "</th>\r\n";
            $trans['</tr>'] = "</tr>\r\n";
            $trans['</td>'] = "</td>\r\n";
            $trans["<span style='background:#FFFFCC'>"] = '';
            $trans['</span>'] = '';
            $text = strtr($lines,$trans);



            if (touch($this->contentFile)){
                $fp = fopen($this->contentFile, 'w' );
                fwrite($fp, $text);
                fclose($fp);
                $wArray['date'] = $date;
                update_option('winex_options', $wArray);
            }
        }
        else {
            $text = 'WineX needs your CellarTracker user_id before it can download your cellar listing';

        }
        return $text;

    }

    /**
    * Checks against date to see if a new cache file is required, otherwise returns
    * the contents of the current one.
    *
    * @param string $content page content, incoming
    * @return string $content page content
    */

    function winex_showWineList($content){
		global $id;

        $wArray = get_option('winex_options');
        if ($id == $wArray['page_id']){
            if ($wArray['date'] != date('Ymd')){
                $content = $this->winex_fetchWineList($wArray);
            }
            else {
                $content = file_get_contents($this->contentFile);
            }
        }
        return $content;
    }

    /**
    * Installs the plugin by creating the page and options
    *
    * @param NULL
    * @return NULL
    */

    function winex_install(){

    	$page                   = array();
    	$page['post_type']      = 'page';
    	$page['post_title']     = 'Wine Cellar';
    	$page['post_name']      = 'winecellar';
    	$page['post_status']    = 'publish';
    	$page['comment_status'] = 'closed';
    	$page['post_content']   = 'This page is used to display your CellarTracker wine cellar via WineX.<br /><br /><!--WINEX-->';

    	$page_id = wp_insert_post($page);
    	$wArray['page_id'] = $page_id;
    	$wArray['user_id'] = '';
    	$wArray['date']    = '';

    	update_option('winex_options', $wArray);

    }

    /**
    * Uninstalls the plugin by deleting the options and page
    *
    * @param NULL
    * @return NULL
    */

    function winex_uninstall(){
        $wArray = get_option('winex_options');

        global $wpdb;
        $sql = "delete from `" . $wpdb->prefix . "posts` where `ID` = '" . $wArray['page_id'] . "' limit 1";
		$wpdb->query($sql);
    	delete_option('winex_options');

    }

    /**
    * The hook for the admin menu
    *
    * @param NULL
    * @return NULL
    */

    function winex_admin_menu(){
        add_management_page('WineX', 'WineX', 1, __FILE__, array($this, 'winex_admin_page'));
    }

    /**
    * The administration page for updating options
    *
    * @param NULL
    * @return NULL
    */

    function winex_admin_page(){
        clearstatcache();

        $wArray = get_option('winex_options');
		if ($_POST['action'] == "update"){
            $user_id = trim(rtrim($_POST['user_id']));

            if (!is_numeric($user_id)){
                $message = "Invalid Member ID";
            }


            if (!$message){
                if ($user_id != $wArray['user_id']){
                    $wArray['user_id'] = $user_id;
                    $wArray['date'] = 0;
                }

                if ($_POST['winex_date'] == 0){
                    $wArray['date'] = 0;
                }
                update_option('winex_options', $wArray);
				$message = 'Options Updated';
            }
		}

		if (!is_writable(".." . DIRECTORY_SEPARATOR . $this->contentFile)){
		    if ($message){ $message .= "<br /><br />"; }
		    $message .= "The cache file is not writable by the webserver.  Most likely this is a simple";
		    $message .= " permissions problem.  The plugin will still run, but will probably be very slow for";
		    $message .= " your users.  It is HIGHLY recommended you resolve this permission problem.";
		}

		if ($wArray["date"] == 0){ $lastUpdate = "No Results Cached"; }
		else { $lastUpdate = $wArray["date"]; }

        $text .= "<div class=\"wrap\"><h2>WineX</h2>";
        $text .= "WineX enables you to import your <a href=\"http://www.cellartracker.com\" target=\"_new\">CellarTracker</a>";
        $text .= " into your WP installation.  <br /><br />WineX downloads your cellar";
        $text .= " once a day and saves it.  This keeps it running fast for both your users and your server.";
        //$text .= "  If you would like WineX to reset the cache contents, use the check box below.";
        $text .= "<form method=\"post\" action=\"\">";
        $text .= "<input type=\"hidden\" name=\"action\" value=\"update\" />";
        if ($message){ $text .= "<br /><b><span style=\"color:#FF0000;\">$message</span></b>"; }
        $text .= "<table class=\"form-table\">";
        $text .= "<tr class=\"form-field form-required\">";
        $text .= "<th scope=\"row\" valign=\"top\"><label for=\"member_number\">CellarTracker Member #:</label></th>";
        $text .= "<td><input type=\"text\" name=\"user_id\" value=\"" . $wArray['user_id'] . "\" />";
        $text .= "</td></th></tr>";
        $text .= "<tr class=\"form-field\">";
        $text .= "<th scope=\"row\" valign=\"top\"><label for=\"reset_cache\">Reset Cache:</label></th>";
        $text .= "<td><input type=\"checkbox\" name=\"winex_date\" value=\"0\" />";
        $text .= "&nbsp;&nbsp;(Last Updated: " . $lastUpdate . ")";
        $text .= "</td></th></tr>";
        $text .= "</table>";



        $text .= "<p class=\"submit\"><input type=\"submit\" name=\"Submit\" value=\"Save Changes\" />";
        $text .= "</p></form></div>";
        print($text);
    }


}

?>
