<?php

/**
* The administration functions for BookX
* 
* @param    object  $wpdb
* @param    array   $options
* @param    string  $baseURL
* @param    string  $pluginURL
* @param    string  $numberPerPage
* @param    array   $bookArray
* @param    string  $status
* @param    array   $filter
*/
  
class bookx_admin {
    
    var $wpdb;
    var $options        = array();
    var $baseURL        = "tools.php?page=bookx/bookx_admin.php";
    var $numberPerPage  = "50";
    var $bookArray      = array();
    var $status         = '';
    var $filter         = array();
    
    /**
    * The contstruct function.  Does nothing other than set up variables.
    *
    * @global object wpdb
    */

    function __construct(){
        global $wpdb;
        
        ini_set('allow_url_fopen', "1");
        $this->wpdb    = $wpdb;
        $this->bookx_checkCode($_GET["code"]);
        
        //$this->wpdb->show_errors(); 
    }
    
    /**
    * Checks to see if there is a status code.  
    * 
    * @param    string  $code           code, usualy from GET but can be passed directly
    * @return   string  $this->status   HTML formatted status update
    * 
    * 
    */

    function bookx_checkCode($code=''){
        $codeArray["a"]  = "Book Added";
        $codeArray["m"]  = "Book Modified";
        $codeArray["d"]  = "Book Deleted";
        $codeArray["c"]  = "Configuration Saved";
        $codeArray["b"]  = "Book List Refreshed";
        $codeArray["as"] = "Books Added";
        $codeArray["i"]  = "Books Imported";
        $codeArray["f"]  = "File Missing";
        
        if ($code){
            $this->status = "<br /><b><span style=\"color:#FF0000;\">&nbsp;" . $codeArray[$code] . "</span></b>";   
        }
    }


    /**
    * Executes the class based on the URI
    *                                                      
    */
    
    function bookx_run(){

        switch($_GET["sub"]){
            case "submit":
                $this->bookx_submit();
                break;
                
            case "form":
                $this->bookx_form();
                break;
                
            case "admin":
                $this->bookx_adminPage();
                break;
                
            case "refresh":
                $this->bookx_refreshAll();
                break;
                
            case "export": 
                $this->bookx_export();
                break;
                
            case "import":
                $this->bookx_import();
                break;
                
            case "list":
            default:
                $this->bookx_list();
                break;
        }    
        
    }
    
    /**
    * Strokes $text to add menu options and return the actual content.
    * 
    * @param mixed $text
    */
    
    function bookx_stroke($text){
        $body = $this->adminHeaderMenu();
        $body .= $text;
        print($body);
        
    }
    
    /**
    * Creates and Export File that can be imported through General Options to restore booklist, or transplant it.
    * 
    */
    
    function bookx_export(){
        if (!wp_verify_nonce($_GET["_wpnonce"])){ die('Security check'); }    
        if (!is_writable(BOOKX_DIR . "export/")){
            $text .= "<div id=\"message\" class=\"error\">In order to create an export file, the directory " . BOOKX_DIR . "export/ must be writable by the webserver.</div>";        
        }
        
        else {
            if (!$this->options["export"]){
                $chars = array("a","A","b","B","c","C","d","D","e","E","f","F","g","G","h","H","i","I","j","J", "k","K","l","L","m","M","n","N","o","O","p","P","q","Q","r","R","s","S","t","T","u","U","v","V","w","W","x","X","y","Y","z","Z","1","2","3","4","5","6","7","8","9","0");
                $max_elements = count($chars) - 1;
                $fileName = srand((double)microtime()*1000000);
                for($i=0;$i<12;$i++){
                    $fileName .= $chars[rand(0,$max_elements)];
                }
                $this->options["export"] = md5($fileName);  
                update_option('bookx_options', $this->options);
            }
            
        
            $results = $this->wpdb->get_results("select bx_item_comments, bx_item_isbn, bx_item_sidebar, bx_item_summary, bx_item_no_update_desc from " . $this->wpdb->prefix . "bx_item");
            foreach($results as $row){
                $body .= "\"" . $row->bx_item_isbn . "\"|\"" . $row->bx_item_comments . "\"|\"" . $row->bx_item_sidebar . "\"|\"" . $row->bx_item_summary  . "\"|\"" . $row->bx_item_no_update_desc . "\"\r\n";
            }
        
            $fp = fopen(BOOKX_DIR . "export/" . $this->options["export"], "w");
            fwrite($fp, $body);
            fclose($fp);
            
            $text = "<div class=\"wrap\"><h2>BookX</h2>";
            $text .= "<strong>Your export file has been created.</strong><br /><br /> ";
            $text .= "<a href=\"" . BOOKX_URL . "bookx_export.php?file=" . $this->options["export"] . "\">Download Book List</a>";
            $text .= "</div>";
            $this->bookx_stroke($text);
        }
    }
    
    /**
    * Imports the imported file created in bookx_export()
    * 
    */
    
    function bookx_import(){
        
        if ($_POST["action"] == "import" && $_FILES["import"]["tmp_name"] != ''){        
            $file = file($_FILES["import"]["tmp_name"]);
            foreach($file as $f){
                $r = explode("\"|\"", $f);
                $insertArray = array();
                foreach($r as $e){
                    $e = trim(rtrim($e), '"');
                    $e = trim(rtrim($e));
                    $insertArray[] = $e;
                }
                
                $count = $this->wpdb->get_var("select count(*) from " . $this->wpdb->prefix . "bx_item where bx_item_isbn = '" . $insertArray[0] . "' limit 1");
                if ($count == 0){
                    $sql = $this->wpdb->prepare("insert into " . $this->wpdb->prefix . "bx_item (bx_item_isbn, bx_item_comments, bx_item_sidebar, bx_item_summary, bx_item_no_update_desc) values (%s, %s, %d, %s, %d)", $insertArray);

                    $this->wpdb->query($sql);
                    
                }
            }
            $this->bookx_refreshAll(true);
        }
        else {
            if ($_POST["action"] == "import"){
                $this->bookx_checkCode("f");
            }
        
        
            $text = "<div class=\"wrap\"><h2>BookX</h2>";
            $text .= "<form method=\"post\" action=\"\" enctype=\"multipart/form-data\">";
            $text .= "<input type=\"hidden\" name=\"action\" value=\"import\" />";
        
            $text .= $this->status;
            if ($noExport){
                $text .= "<div id=\"message\" class=\"error\">In order to create an export file, the directory " . BOOKX_DIR . "export/ must be writable by the webserver.</div>";
            }
        
            $text .= "<table class=\"form-table\">";
            $text .= "<tr class=\"form-field form-required\">";
            $text .= "<th scope=\"row\" valign=\"top\"><label for=\"image_size\">Import Book List File:</label></th>";
            $text .= "<td><input type=\"file\" name=\"import\" class=\"file\" />";
            $text .= "</td></tr>";     
            $text .= "</table>";
            $text .= "<p class=\"submit\"><input type=\"submit\" name=\"Submit\" value=\"Import Booklist\" />";
        
            $text .= "</p></form></div>";
            $this->bookx_stroke($text);
        }
            
        
        
                
    }    
    
    
    /**
    * Creates the header menu
    * 
    * @return   string  $text
    */
    
    function adminHeaderMenu(){
        if (!$this->nonce){ $this->nonce = wp_create_nonce(); }
        $text = "<a href=\"" . $this->baseURL . "&sub=admin\">General Options</a>";
        $text .= "&nbsp;&nbsp;<a href=\"" . $this->baseURL . "&sub=list\">View Books</a>"; 
        $text .= "&nbsp;&nbsp;<a href=\"" . $this->baseURL . "&sub=form\">Add New Book</a>"; 
        $text .= "&nbsp;&nbsp;<a href=\"" . $this->baseURL . "&sub=refresh&_wpnonce=" . $this->nonce . "\">Refresh Book List</a>";
        if (is_writable(BOOKX_DIR . "export/")){
            $text .= "&nbsp;&nbsp;<a href=\"" . $this->baseURL . "&sub=export&_wpnonce=" . $this->nonce . "\">Export Book List</a>";
        }        
        $text .= "&nbsp;&nbsp;<a href=\"" . $this->baseURL . "&sub=import\">Import Book List</a>";
        $text .= "<script type='text/javascript' src='" . BOOKX_URL. "suitex.js'></script>"; 
        
        $text .= "<link rel='stylesheet' href='" . BOOKX_URL . "style.css' type='text/css' />";

        
        return $text;
    }
    
    /**
    * Refreshes all the books in the list
    * 
    * @param boolean $importSkipNonce
    */
    
    function bookx_refreshAll($importSkipNonce=false){
        
        if ($importSkipNonce != true){ 
            $code = "b";
            if (!wp_verify_nonce($_GET["_wpnonce"])){ die('Security check'); }   
        }
        else { $code = "i"; }
        
        $sql = "select bx_item_id, bx_item_isbn, bx_item_no_update_desc from " . $this->wpdb->prefix . "bx_item";
        $result = $this->wpdb->get_results($sql);
        
        foreach($result as $row){
            $this->bookArray = array();
            $this->bookx_fetchItem($row->bx_item_isbn);

            $sql = "update " . $this->wpdb->prefix . "bx_item set ";
            foreach(array_keys($this->bookArray) as $key){     
                if ($key != "summary"){
                    $sql .= "bx_item_" . $key . " = '" . addslashes($this->bookArray[$key]) . "', ";
                }    
                else if ($row->bx_item_no_update_desc != 1){
                    $sql .= "bx_item_summary = '" . addslashes($this->bookArray["summary"]) . "', ";
                }
            }

            $sql .= "bx_item_date_added = " . time() . " where bx_item_id = " . $row->bx_item_id . " limit 1";
            
            $this->wpdb->query($sql);
            if ($this->die){ print("<br><br>$sql<br><br>"); die();}
            
            
        }
        
        
        $url = $this->baseURL . "&code=$code";
        $text = "<script language=\"javascript\">";
        $text .= "goToURL('$url'); ";
        //$text .= "window.location = '$url';";
        $text .= "</script>";
        $this->bookx_stroke($text);        
        
    }
    
    /**
    * Fetches book information based on the isbn
    * 
    * @param    mixed   $isbn
    * @return   array   $this->bookArray
    */
        
    function bookx_fetchItem($isbn){

    
        $url = 'http://search.barnesandnoble.com/booksearch/isbninquiry.asp?ean=' . $isbn;

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

        $start = "<div id=\"product-top\">";

        $lines = substr($lines, strpos($lines, $start));
        //print($lines); 

        if (substr_count($lines, "<ul class=\"reviewBox\">")){
            $end = "<ul class=\"reviewBox\">";
        }        
        else if (substr_count($lines, "<h3 class=\"pr-selected\">")){
            $end = "<h3 class=\"pr-selected\">";
        }
        
        
        
        
        $lines = substr($lines, 0, strpos($lines, $end));
        //print($lines);
        
        $titleLine = substr($lines, strpos($lines, "<div id=\"product-info\">"));
        $titleLine = substr($titleLine, 0, strpos($titleLine, "<div class=\"pb\">"));
        $titleLine = strip_tags($titleLine);
        
        
        $title = substr($titleLine, 0, strpos($titleLine, "by"));
        $title = trim(rtrim($title));
        
        $author = substr($titleLine, strpos($titleLine, "by") + 2);
        $author = trim(rtrim($author));
        
        $price = substr($lines, strpos($lines, "\$"));
        $price = substr($price, 0, strpos($price, ".") + 3);
        $price = str_replace("\$", '', $price);
        
        
        $publisher = substr($lines, strpos($lines, "Publisher:"));
        $publisher = substr($publisher, 0, strpos($publisher, "</li>"));
        $publisher = str_replace("Publisher:", '', $publisher);
        
        $pubDate = substr($lines, strpos($lines, "Pub. Date:"));
        $pubDate = substr($pubDate, 0, strpos($pubDate, "</li>"));
        $pubDate = str_replace("Pub. Date:", '', $pubDate);
        $pubDate = strtotime($pubDate);
        
        $pages = substr($lines, strpos($lines, "pp</li>") - 5);
        $pages = substr($pages, 0, strpos($pages, "</li>"));
        $pages = str_replace("i", '', $pages);
        $pages = str_replace(">", '', $pages);
        $pages = str_replace("l", '', $pages);
        $pages = str_replace("<", '', $pages);
        $pages = str_replace("pp", '', $pages);
      
        if (!is_numeric($pages)){ $pages = 0; }
        
        $format = substr($lines, strpos($lines, "<p class=\"format\">"));
        $format = substr($format, 0, strpos($format, "</p>"));
        $format = str_replace("(", '', $format);
        $format = str_replace(")", '', $format);
        //print($lines);
        //print("<br><br><br><br><br>\r\n\r\n");
        $summary = substr($lines, strpos($lines, "<h3>Synopsis</h3>"));
        //print($summary);
        //die();
        $summary = substr($summary, 0, strpos($summary, "</p>"));
        $summary = str_replace("<h3>Synopsis</h3>", '', $summary);
        $summary = str_replace("—", "-", $summary);
        //$summary = htmlentities($summary);
        
        $image = substr($lines, strpos($lines, "<img border=\"0\" src=\"http://images.barnesandnoble.com/images/"));
        $image = substr($image, 0, strpos($image, ">")) . " />";
        
        /*
        $source = substr($image, strpos($image, "src=") + 5);
        $source = substr($source, 0, strpos($source, "alt"));
        $source = trim(rtrim(str_replace('"', '', $source)));
        
        $sourceTest = strtolower($source);
        
        if (substr_count($sourceTest, ".jpg") || substr_count($source, ".jpeg") || substr_count($source, ".jpe")){ 
            $imageType = "image/jpeg"; 
        }
        else if (substr_count($sourceTest, ".gif")){
            $imageType = "image/gif";
        }
        else if (substr_count($sourceTest, ".png")){
            $imageType = "image/png";
        }
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $source );
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $imageData = curl_exec($ch);
        curl_close($ch);
        */

        //if (substr_count($title, "Knuffle")){ $this->die = true; }                
        if ($title != ''){ print("Working on $title <br />"); }
        flush();
        
        
        $this->addBookToArray("publisher", $publisher);
        $this->addBookToArray("price", $price);
        $this->addBookToArray("author", $author);
        $this->addBookToArray("name", $title);
        $this->addBookToArray("date", $pubDate);
        $this->addBookToArray("pages", $pages);
        $this->addBookToArray("format", $format);
        $this->addBookToArray("summary", $summary, "<br>");
        $this->addBookToArray("image", $image, true);
        //$this->addBookToArray("image_type", $imageType);
        $this->addBookToArray("link", $url, true);
        $this->addBookToArray("isbn", $isbn);
        
        //print_r($this->bookArray);
        //die();

    }
    
    /**
    * Adds a value to the bookarray
    * 
    * @param    mixed   $key
    * @param    mixed   $value
    * @param    mixed   $noStrip
    */
                             
    function addBookToArray($key, $value, $noStrip = false){
        if ($noStrip == true){ 
            $this->bookArray[$key] = trim(rtrim($value)); 
        }
        else if ($noStrip == false){ 
            $this->bookArray[$key] = trim(rtrim(strip_tags($value))); 
        }
        else { 
            $this->bookArray[$key] = trim(rtrim(strip_tags($value, "$noStrip"))); 
        }
        
    }

    /**
    * Installs the plugin by creating the page and options
    *
    * @param NULL
    * @return NULL
    */

    function bookx_install(){
        if (!get_option('bookx_options')){
            $sql = "CREATE TABLE `" . $this->wpdb->prefix . "bx_item` (
  `bx_item_id` int(10) NOT NULL AUTO_INCREMENT,
  `bx_item_name` varchar(255) DEFAULT NULL,
  `bx_item_author` varchar(255) DEFAULT NULL,
  `bx_item_comments` text,
  `bx_item_date` int(10) DEFAULT NULL,
  `bx_item_date_added` int(10) DEFAULT NULL,
  `bx_item_format` varchar(255) DEFAULT NULL,
  `bx_item_image` text,
  `bx_item_isbn` varchar(15) DEFAULT NULL,
  `bx_item_link` text,
  `bx_item_pages` int(10) NOT NULL DEFAULT '0',
  `bx_item_price` float(4,2) NOT NULL DEFAULT '0.00',
  `bx_item_sidebar` tinyint(1) NOT NULL DEFAULT '0',
  `bx_item_summary` text,
  `bx_item_publisher` varchar(255) DEFAULT NULL,
  `bx_item_no_update_desc` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`bx_item_id`),
  UNIQUE KEY `bx_item_isbn` (`bx_item_isbn`),
  KEY `bx_item_sidebar` (`bx_item_sidebar`)
);";
            $this->wpdb->query($sql);
        
            $page                   = array();
            $page['post_type']      = 'page';                                       
            $page['post_title']     = 'Recommended Books';
            $page['post_name']      = 'booklist';
            $page['post_status']    = 'publish';
            $page['comment_status'] = 'closed';
            $page['post_content']   = 'This page displays your BookX front end.';

            $page_id = wp_insert_post($page);
            $options = array();
            $options['page_id']                 = $page_id;
            $options['widget_title']            = "Recommended Books";
            $options['per_page']                = "10";         
            $options['widget_image_height']     = "45"; 
            $options['widget_image_width']      = "45";
            $options['list_image_height']       = "100";
            $options['list_image_width']        = "100";
            $options['detail_image_height']     = "250";
            $options['detail_image_width']      = "250";
            $options['listTemplate']            = "<a href=\"::LINK::\">::IMAGE::</a> <h2><a href=\"::LINK::\">::TITLE::</a></h2> by ::AUTHOR:: <br />\r\n::SUMMARY::::MORE::";
            $options['detailTemplate']          = "<a href=\"::ELINK::\">::IMAGE::</a> <strong><a href=\"::ELINK::\">::TITLE::</a></strong> by ::AUTHOR:: <br />\r\n(::FORMAT::)<br />\r\n<strong>Price: </strong>\$::PRICE::<br />\r\n<strong>Pages: </strong>::PAGES::<br />\r\n<br />::SUMMARY::<br /><br />::COMMENTS::\r\n<br />";
            $options['widgetTemplate']          = "<a href=\"::LINK::\">::IMAGE::</a> <strong><a href=\"::LINK::\">::TITLE::</a></strong> by ::AUTHOR::";
            $options['widget_sort']             = "desc";
            $options['widget_order']            = "bx_item_name";
            $options['list_characters']         = "250";
            $options['css']                     = ".bookx_list_entry img,\r\n.bookx_detail_entry img {\r\n padding: 5px;\r\n }\r\n.bookx_detail_entry {\r\n height: 100%;\r\n }\r\n.bookx_list_entry {\r\nheight: 250px;\r\nborder-bottom: 1px solid #000000;\r\nmargin-bottom: 10px;\r\n}\r\n"; 
            $options['list_image_align']        = "left";
            $options['detail_image_align']      = "left";
            $options['list_search']             = "0";
            $options['list_filter']             = "1";
            $options['list_order_default']      = "bx_item_name";
            $options['list_sort_default']       = "asc";  
        }
        else {
            $sql = "SHOW INDEXES IN " . $this->wpdb->prefix . "bx_item";

            $indexes = $this->wpdb->get_results($sql);
            foreach($indexes as $ind){
                if ($ind->Key_name == "bx_item_name"){
                    $gt04 = true;
                    break;
                }
            }
            if (!$gt04){
                $sql = "ALTER TABLE `" . $this->wpdb->prefix . "bx_item` ADD INDEX ( `bx_item_name` , `bx_item_author` , `bx_item_publisher` );";
                $this->wpdb->query($sql);
            }
        
            $result = $this->wpdb->get_row("show columns from " . $this->wpdb->prefix . "bx_item where Field = 'bx_item_no_update_desc'");
            
            if (!$result){
                $this->wpdb->query("ALTER TABLE `" . $this->wpdb->prefix . "bx_item` ADD `bx_item_no_update_desc` TINYINT( 1 ) NOT NULL DEFAULT '0', CHANGE `bx_item_name` `bx_item_name` VARCHAR( 255 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL, CHANGE `bx_item_author` `bx_item_author` VARCHAR( 255 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL ,
CHANGE `bx_item_format` `bx_item_format` VARCHAR( 255 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL ,
CHANGE `bx_item_publisher` `bx_item_publisher` VARCHAR( 255 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL, CHANGE `bx_item_date` `bx_item_date` INT( 10 ) NULL DEFAULT NULL ,
CHANGE `bx_item_date_added` `bx_item_date_added` INT( 10 ) NULL DEFAULT NULL, CHANGE `bx_item_image` `bx_item_image` TEXT CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL ,
CHANGE `bx_item_link` `bx_item_link` TEXT CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL  ");
                   
            }
        }
        update_option('bookx_options', $options);
    }

    /**
    * Uninstalls the plugin by deleting the options and page
    */

    function bookx_uninstall(){
        
        
        $sql = "delete from `" . $this->wpdb->prefix . "posts` where `ID` = '" . $this->options['page_id'] . "' limit 1";
        $this->wpdb->query($sql);
        $sql = "drop table " . $this->wpdb->prefix . "bx_item";
        $this->wpdb->query($sql);
        delete_option('bookx_options');

    }

    /**
    * The administration page for updating options
    *
    * @param NULL
    * @return NULL
    */

    function bookx_adminPage(){
        
        $alignArray['left']     = "Left";
        $alignArray['right']    = "Right";
        $alignArray['']         = '';
        
        if ($_POST['action'] == "update"){
            $this->options['per_page']              = $_POST['per_page'];
            $this->options['per_widget']            = $_POST['per_widget'];
            $this->options['listTemplate']          = $_POST['list'];
            $this->options['widgetTemplate']        = $_POST['widget'];
            $this->options['list_image_height']     = $_POST['list_image_height'];
            $this->options['list_image_width']      = $_POST['list_image_width'];
            $this->options['detail_image_height']   = $_POST['detail_image_height'];
            $this->options['detail_image_width']    = $_POST['detail_image_width'];
            $this->options['detailTemplate']        = $_POST['detail'];
            $this->options['list_characters']       = $_POST['list_characters'];
            $this->options['css']                   = $_POST['css'];
            $this->options['list_image_align']      = $_POST['list_image_align'];
            $this->options['detail_image_align']    = $_POST['detail_image_align'];
            $this->options['list_search']           = $_POST['list_search'];
            $this->options['list_filter']           = $_POST['list_filter'];
            $this->options['list_order_default']   = $_POST['list_order_default'];
            $this->options['list_sort_default']     = $_POST['list_sort_default'];
            
            update_option('bookx_options', $this->options);
            
                        

        }
        
        if (!is_writable(BOOKX_DIR . "export/")){
            $noExport = true;
        }
        
        
        //import and export goes HERE...check directory for export
        $text = "<div class=\"wrap\"><h2>BookX</h2>";
        $text .= "<form method=\"post\" action=\"\">";
        $text .= "<input type=\"hidden\" name=\"action\" value=\"update\" />";
        
        $text .= $this->status;
        if ($noExport){
            $text .= "<div id=\"message\" class=\"error\">In order to create an export file, the directory " . BOOKX_DIR . "export/ must be writable by the webserver.</div>";
        }
        
        $text .= "<table class=\"form-table\">";
        $text .= "<tr class=\"form-field form-required\">";
        $text .= "<th scope=\"row\" valign=\"top\"><label for=\"per_page\"># per page:</label></th>";
        $text .= "<td><input type=\"text\" name=\"per_page\" value=\"" . $this->options['per_page'] . "\" />";
        $text .= "</td></tr>";
        $text .= "<tr class=\"form-field form-required\">";
        $text .= "<th scope=\"row\" valign=\"top\"><label for=\"image_size\">List Default Order Field:</label></th>";
        $text .= "<td><select name=\"list_order_default\">";
        foreach(array_keys($this->fieldArray) as $f){
            if ($f == $this->options["list_order_default"]){ $s = "selected"; }
            else { $s = ''; }
            $text .= "<option value=\"$f\" $s>" . $this->fieldArray[$f] . "</option>";
        }
        $text .= "</select></td></tr>";  
        $text .= "<tr class=\"form-field form-required\">";
        $text .= "<th scope=\"row\" valign=\"top\"><label for=\"image_size\">List Sort Default:</label></th>";
        $text .= "<td><select name=\"list_sort_default\">";
        foreach(array_keys($this->sortArray) as $sort){
            if ($sort == $this->options["list_sort_default"]){ $s = "selected"; }
            else { $s = ''; }
            $text .= "<option value=\"$sort\" $s>" . $this->sortArray[$sort] . "</option>";
        }
        $text .= "</select></td></tr>";          

        $text .= "<tr class=\"form-field form-required\">";
        $text .= "<th scope=\"row\" valign=\"top\"><label for=\"image_size\">Allow Users to Change Order Field:</label></th>";
        $text .= "<td><select name=\"list_filter\">";
        foreach(array_keys($this->filter) as $f){
            if ($f == $this->options['list_filter']){ $s = "selected"; }
            else { $s = ''; }
            $text .= "<option value=\"$f\" $s>" . $this->filter[$f] . "</option>";
        }
        $text .= "</select></td></tr>";  

        $text .= "<tr class=\"form-field form-required\">";
        $text .= "<th scope=\"row\" valign=\"top\"><label for=\"image_size\">Enable Search:</label></th>";
        $text .= "<td><select name=\"list_search\">";
        foreach(array_keys($this->filter) as $f){
            if ($a == $this->options['list_search']){ $s = "selected"; }
            else { $s = ''; }
            $text .= "<option value=\"$f\" $s>" . $this->filter[$f] . "</option>";
        }
        $text .= "</select></td></tr>";          
        
        $text .= "<tr class=\"form-field form-required\">";
        $text .= "<th scope=\"row\" valign=\"top\"><label for=\"image_size\">List Image Width:</label></th>";
        $text .= "<td><input type=\"text\" name=\"list_image_width\" value=\"" . $this->options['list_image_width'] . "\" />";
        $text .= "</td></tr>";
        $text .= "<tr class=\"form-field form-required\">";
        $text .= "<th scope=\"row\" valign=\"top\"><label for=\"image_size\">List Image Height:</label></th>";
        $text .= "<td><input type=\"text\" name=\"list_image_height\" value=\"" . $this->options['list_image_height'] . "\" />";
        $text .= "</td></tr>"; 
        
        $text .= "<tr class=\"form-field form-required\">";
        $text .= "<th scope=\"row\" valign=\"top\"><label for=\"image_size\">List Image Align:</label></th>";
        $text .= "<td><select name=\"list_image_align\">";
        foreach(array_keys($alignArray) as $a){
            if ($a == $this->options['list_image_align']){ $s = "selected"; }
            else { $s = ''; }
            $text .= "<option value=\"$a\" $s>" . $alignArray[$a] . "</option>";
        }
        $text .= "</select></td></tr>";           
        
        $text .= "<tr class=\"form-field form-required\">";
        $text .= "<th scope=\"row\" valign=\"top\"><label for=\"image_size\">Detail Image Width:</label></th>";
        $text .= "<td><input type=\"text\" name=\"detail_image_width\" value=\"" . $this->options['detail_image_width'] . "\" />";
        $text .= "</td></tr>";
        $text .= "<tr class=\"form-field form-required\">";
        $text .= "<th scope=\"row\" valign=\"top\"><label for=\"image_size\">Detail Image Height:</label></th>";
        $text .= "<td><input type=\"text\" name=\"detail_image_height\" value=\"" . $this->options['detail_image_height'] . "\" />";
        $text .= "</td></tr>";         

        $text .= "<tr class=\"form-field form-required\">";
        $text .= "<th scope=\"row\" valign=\"top\"><label for=\"image_size\">Detail Image Align:</label></th>";
        $text .= "<td><select name=\"detail_image_align\">";
        foreach(array_keys($alignArray) as $a){
            if ($a == $this->options['detail_image_align']){ $s = "selected"; }
            else { $s = ''; }
            $text .= "<option value=\"$a\" $s>" . $alignArray[$a] . "</option>";
        }
        
        $text .= "</select></td></tr>";   


        $text .= "<tr class=\"form-field form-required\">";
        $text .= "<th scope=\"row\" valign=\"top\"><label for=\"image_size\"># Characters for Summary & Comments in List View:</label></th>";
        $text .= "<td><input type=\"text\" name=\"list_characters\" value=\"" . $this->options['list_characters'] . "\" />";
        $text .= "</td></tr>";                 
        
        $text .= "<tr class=\"form-field\">";
        $text .= "<td><strong>CSS:</strong></td>";
        $text .= "<td><textarea name=\"css\">" . stripslashes($this->options['css']) . "</textarea>";
        $text .= "</td></tr>"; 
        $text .= "<tr><td colspan=\"2\">";
        $text .= "The following fields are to create the look & field for three display areas, the Widget, List, and the Detail.<br /><br />";
        $text .= "</td></tr>";
        $text .= "<tr class=\"form-field\">";
        $text .= "<td><strong>Widget Template:</strong></td>";
        $text .= "<td><textarea name=\"widget\">" . stripslashes($this->options["widgetTemplate"]) . "</textarea>";
        $text .= "</td></tr>";
        $text .= "<tr class=\"form-field\">";
        $text .= "<td><strong>List Template:</strong></td>";
        $text .= "<td><textarea name=\"list\">" . stripslashes($this->options['listTemplate']) . "</textarea>";
        $text .= "</td></tr>";  
        $text .= "<tr class=\"form-field\">";
        $text .= "<td><strong>Detail Template:</strong></td>";
        $text .= "<td><textarea name=\"detail\">" . stripslashes($this->options['detailTemplate']) . "</textarea>";
        $text .= "</td></tr>";         
        $text .= "<tr><td colspan=\"2\">";  
        $text .= "In addition to HTML, the three template fields will accept the following field subsitution tags: <ul>";
        $text .= "<li>::TITLE:: - The title of the book</li>";
        $text .= "<li>::AUTHOR:: - The author(s)</li>";
        $text .= "<li>::ISBN:: - The ISBN (13)</li>";
        $text .= "<li>::PUBLISHER:: - The Publisher</li>";
        $text .= "<li>::DATE:: - The publish date</li>";
        $text .= "<li>::PAGES:: - Number of pages</li>";
        $text .= "<li>::FORMAT:: - Publish Format</li>";
        $text .= "<li>::ELINK:: - External Link</li>";
        $text .= "<li>::LINK:: - Link to the Detail view of Book.  Not available in Detail View.</li>";
        $text .= "<li>::IMAGE:: - Image of Cover (scaled)</li>";
        $text .= "<li>::PRICE:: - Price</li>";
        $text .= "<li>::SUMMARY:: - Summary from external source.  Not available in the Widget.</li>";
        $text .= "<li>::COMMENTS:: - Your comments.  Not available in the Widget.</li>";
        $text .= "<li>::MORE:: - More link to detail view.  Only available in List View.</li>";
        $text .= "</ul>";
        $text .= "</td></tr>";
              
  

        
        $text .= "</table>";



        $text .= "<p class=\"submit\"><input type=\"submit\" name=\"Submit\" value=\"Save Changes\" />";
        
        $text .= "</p></form></div>";
        $this->bookx_stroke($text);
    }   
    
    /**
    * The form to add or modify a book.
    * 
    */
    
    function bookx_form($code=''){
        
        if ($code != ''){

            
            if ($_POST["id"]){
                $query = "select bx_item_name as name from " . $this->wpdb->prefix . "bx_item where bx_item_id = %d limit 1";
                $row = $this->wpdb->get_row($this->wpdb->prepare($query, $_POST["id"]));
                $row->isbn = $_POST["isbn"];
                $row->sidebar = $_POST["sidebar"];
                $row->comments = $_POST["comments"];
                $row->summary  = $_POST["summary"];
                $row->no_update = $_POST["no_update"];                
                $_GET["id"] = $_POST["id"];
                $label = "Modify Book : " . $row->name; 
                $action = "modify";
            }
            else {
                $row->isbn = $_POST["isbn"];
                $row->sidebar = $_POST["sidebar"];
                $row->comments = $_POST["comments"];
                $row->summary  = $_POST["summary"];
                $row->no_update = $_POST["no_update"];
                $label = "Add Book";
                $action = "add";                 
            }

            $status = "<span style=\"font-weight: bold; color: #FF0000;\">" . $code . "</span><br />";            
        }
        else if ($_GET["id"]){
            $query = "select bx_item_name as name, bx_item_isbn as isbn, bx_item_comments as comments, bx_item_sidebar as sidebar, bx_item_summary as summary, bx_item_no_update_desc as no_update from " . $this->wpdb->prefix . "bx_item where bx_item_id = %d limit 1";
            $row = $this->wpdb->get_row($this->wpdb->prepare($query, $_GET["id"]));
            
        
            $label = "Modify Book : " . $row->name;            
            $action = "modify";
            
        }
        else {
            $label = "Add Book";
            $action = "add";    
        }
        
        
        
        
        $this->nonce = wp_create_nonce();
        
        $text = "<div class=\"wrap\">";
        $text .= "<h2>BookX - Books</h2>";
        $text .= "<br />";


        $text .= "<div id=\"poststuff\" class=\"metabox-holder\">";
        $text .= "<div id=\"post-body\" class=\"has-sidebar\">";
        $text .= "<div id=\"post-body-content\" class=\"has-sidebar-content\">";
        $text .= "<div class=\"postbox\">";
        $text .= "<h3><label>$label</label></h3>";
        $text .= $status;
        
        $text .= "<div class=\"inside\">";
        $text .= "<form method=\"post\" action=\"" . $this->baseURL . "&sub=submit\">";
        $text .= "<input type=\"hidden\" name=\"_wpnonce\" value=\"" . $this->nonce . "\" />";
        $text .= "<input type=\"hidden\" name=\"action\" value=\"$action\" />";
        
        if ($_GET["id"]){
            $text .= "<input type=\"hidden\" name=\"id\" value=\"" . $_GET["id"] . "\" />";
        }
        $text .= "<table class=\"form-table\">";
        $text .= "<tr class=\"form-field\">";
        $text .= "<td><strong>ISBN:</strong></td>";
        $text .= "<td><input type=\"text\" name=\"isbn\" value=\"" . $row->isbn . "\" />";
        $text .= "</td></tr>";
        $text .= "<tr class=\"form-field\">";
        $text .= "<td><strong>Add to Sidebar:</strong></td>";
        $text .= "<td><select name=\"sidebar\">";
        foreach(array_keys($this->filter) as $f){
            if ($f == $row->sidebar){ $s = "selected"; }
            else { $s = ''; }
            $text .= "<option value=\"$f\" $s>" . $this->filter[$f] . "</option>";
        }
        
        
        $text .= "</select></td></tr>";        
        $text .= "<tr class=\"form-field\">";
        $text .= "<td><strong>Comments:</strong></td>";
        $text .= "<td><textarea name=\"comments\">" . $row->comments . "</textarea>";
        $text .= "</td></tr>";
        
        $text .= "<tr class=\"form-field\">";
        $text .= "<td><strong>Summary from Source:</strong></td>";
        $text .= "<td><textarea name=\"summary\">" . $row->summary . "</textarea>";
        $text .= "</td></tr>";            
        
        $text .= "<tr class=\"form-field\">";
        $text .= "<td><strong>Protect Summary from Updating:</strong></td>";
        $text .= "<td><select name=\"no_update\">";
        foreach(array_keys($this->filter) as $f){
            if ($f == $row->no_update){ $s = "selected"; }
            else { $s = ''; }
            $text .= "<option value=\"$f\" $s>" . $this->filter[$f] . "</option>";
        }
        
        
        $text .= "</select></td></tr>";            
        
        
        $text .= "</table>";
        $text .= "<p class=\"submit\"><input type=\"submit\" name=\"Submit\" value=\"Save Changes\" />";
        if ($action == "modify"){
            $deleteURL = $this->baseURL . "&sub=submit&id=" . $_GET["id"] . "&_wpnonce=" . $this->nonce;
            $text .= "&nbsp;<input type=\"button\" value=\"Delete\" onClick=\"confirmAction('Are you sure you want to delete this book?', '$deleteURL');\" />";
        }
        $text .= "</p></form>";
        $text .= "</div></div>";
        
        
        if ($action == "add"){
            $text .= "<div class=\"postbox\">";
            $text .= "<h3><label>Add Books</label></h3>";
        
            $text .= "<div class=\"inside\">";
            $text .= "<form method=\"post\" action=\"" . $this->baseURL . "&sub=submit\">";
            $text .= "<input type=\"hidden\" name=\"_wpnonce\" value=\"" . $this->nonce . "\" />";
            $text .= "<input type=\"hidden\" name=\"action\" value=\"adds\" />";
            $text .= "<table class=\"form-table\">";
      
            $text .= "<tr class=\"form-field\">";
            $text .= "<td><strong>Multiple ISBNs (one per line):</strong></td>";
            $text .= "<td><textarea name=\"books\" rows=\"20\"></textarea>";
            $text .= "</td></tr>";
            $text .= "</table>";
            $text .= "<p class=\"submit\"><input type=\"submit\" name=\"Submit\" value=\"Save Changes\" />";
            $text .= "</p></form>";
            $text .= "</div></div>";        
        }    
        $text .= "</div></div>";
        $text .= "</div></div>";
        $this->bookx_stroke($text);
    }
    
    /**
    * form actions to alter a book
    * 
    */
    
    function bookx_submit(){
        if ($_POST["_wpnonce"]){ $nonce = $_POST["_wpnonce"]; }
        else if ($_GET["_wpnonce"]){ $nonce = $_GET["_wpnonce"]; }

        if (!wp_verify_nonce($nonce)){ die('Security check'); }
        
        $comments = str_replace("\r\n", "<br />", strip_tags(htmlentities($_POST["comments"])));
        if ($_POST["action"] != "adds"){ $this->bookx_fetchItem(strip_tags($_POST["isbn"])); }        
        
        if ($_POST["action"] == "adds"){
            $books = explode("\r\n", $_POST["books"]);
            foreach($books as $b){
                $this->bookx_fetchItem(strip_tags($b));
                $sql = "insert into " . $this->wpdb->prefix . "bx_item ";
                $fields = '';
                $values = '';
                foreach(array_keys($this->bookArray) as $key){
                    $fields .= "bx_item_" . $key . ", ";
                    $values .= "'" . addslashes($this->bookArray[$key]) . "', ";
                }
                $fields .= "bx_item_comments, bx_item_sidebar, bx_item_date_added";
                $values .= "'$comments', 0, " . time();
                $sql .= "($fields) values ($values)";
                
                $this->wpdb->query($sql);
                //$this->wpdb->print_error();
                $code = "as";                
                
                
                
                
                
            } 


        
        
        }
        else if ($_POST["action"] == "add"){
            
            if ($this->bookArray["name"] == ''){
                $this->bookx_form("ISBN Number Not Found.");
                return false;
            }   
                     
            $sql = "insert into " . $this->wpdb->prefix . "bx_item ";
            foreach(array_keys($this->bookArray) as $key){
                if ($key != "summary"){
                    $fields .= "bx_item_" . $key . ", ";
                    $values .= "'" . addslashes($this->bookArray[$key]) . "', ";
                }
            }
            if ($_POST["no_update"] == 1){
                $fields .= "bx_item_summary, ";
                $values .= "'" . addslashes($_POST["summary"]) . "', ";
            }  
            else {
                $fields .= "bx_item_summary,";
                $values .= "'" . addslashes($this->bookArray["summary"]) . ", ";                
            }          
            $fields .= "bx_item_comments, bx_item_sidebar, bx_item_date_added, bx_item_no_update_desc";
            $values .= "'$comments', " . $_POST["sidebar"] . ", " . time() . ", '" . $_POST["no_update"] . "' ";
            $sql .= "($fields) values ($values)";

            $code = "a";
        }
        else if ($_POST["action"] == "modify"){
            if ($this->bookArray["name"] == ''){
                $this->bookx_form("ISBN Number Not Found.");
                return false;
            }   
            $sql = "update " . $this->wpdb->prefix . "bx_item set ";
            foreach(array_keys($this->bookArray) as $key){     
                if ($key != "bx_item_summary"){
                    $sql .= "bx_item_" . $key . " = '" . addslashes($this->bookArray[$key]) . "', ";
                }                        
            }
            if ($_POST["no_update"] == 1){
                $sql .= "bx_item_summary = '" . $_POST["summary"] . "', ";     
            }
            else {
                $sql .= "bx_item_summary = '" . addslashes($this->bookArray["summary"]) . "', ";    
            }
            $sql .= "bx_item_comments = '$comments', bx_item_sidebar = " . $_POST["sidebar"] . ", bx_item_no_update_desc = '" . $_POST["no_update"] . "', ";
            $sql .= "bx_item_date_added = " . time() . " where bx_item_id = " . $_POST["id"] . " limit 1";

            $code = "m";
                        
        }
        else {
            $sql = "delete from " . $this->wpdb->prefix . "bx_item where bx_item_id = %d limit 1";
            $sql = $this->wpdb->prepare($sql, $_GET["id"]);        
            $code = "d";    
        }
        //print_r($this->bookArray);
        //die();
        if ($code != "as"){
            if (!$this->wpdb->query($sql)){
                $this->bookx_form("SQL Query Failed"); 
                return false;    
            }
            //$this->wpdb->print_error(); 
            //die("HERE");
        }
        
        $url = $this->baseURL . "&code=$code";
        $text = "<script language=\"javascript\">";
        $text .= "goToURL('$url'); ";
        //$text .= "window.location = '$url';";
        $text .= "</script>";
        $this->bookx_stroke($text);
        
    }
    
    /**
    * The administration view of the book list.
    * 
    */
    
    function bookx_list(){
        
        
        require_once(BOOKX_DIR . 'suitex_list.php'); 
        



        $text .= "<div class=\"wrap\">";
        $text .= "<h2>BookX</h2>";
        $text .= $this->status;

        $headers["bx_item_name"]        = "Title";
        $headers["bx_item_author"]      = "Author";
        $headers["bx_item_isbn"]        = "ISBN";
        $headers["bx_item_sidebar"]     = "Sidebar";

        $order = "bx_item_name";
        $sort  = "asc";
        
        if ($_GET["limit"]){ $limit = $_GET["limit"]; }
        else { $limit = 0; }

        $query = "select count(bx_item_id) from " . $this->wpdb->prefix . "bx_item";
        $count = $this->wpdb->get_var($query); 
        
        $query = "select ";
        $query .= "bx_item_name as item, ";
        $query .= "bx_item_author as author, ";
        $query .= "bx_item_isbn as isbn, ";
        $query .= "bx_item_sidebar as sidebar, ";
        $query .= "bx_item_id as id ";
        $query .= "from " . $this->wpdb->prefix . "bx_item ";
        $query .= "order by $order $sort limit $limit, " . $this->numberPerPage;
        
        $result = $this->wpdb->get_results($query);

        foreach($result as $row){
            $sidebar = $this->filter[$row->sidebar];
            
            if ($row->item){ $itemName = $row->item; }
            else { $itemName = "Import Failed"; }
            $rows[$row->id] = array($itemName, $row->author, $row->isbn, $sidebar);
        }
        $url = $this->baseURL . "&sub=form&id=";
        



        $list = new suitex_list();
        $list->search       = false;
        $list->orderForm    = false;
        $list->filters      = false;
        $list->omit         = array("cb");
        $this->paging       = true;
        $this->pluginPath   = BOOKX_URL;
        
        
        
        $list->startList($headers, $url, $order, $sort, $rows, $limit, $this->numberPerPage);
        $text .= $list->text;
        $text .= "</div>";
        
        $this->bookx_stroke($text);       
        
    }
    
    /**
    * Addes the admin menu option using the WP hook.
    * 
    */
    
    
    function bookx_adminMenu(){
        add_management_page('BookX', 'BookX', 5, __FILE__, array($this, 'bookx_run')); 
    } 
}
?>