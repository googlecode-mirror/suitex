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
        $this->bookx_checkCode();
    }
    
    /**
    * Checks to see if there is a status code.  
    * 
    * @return   string  $this->status
    * 
    */

    function bookx_checkCode(){
        $codeArray["a"] = "Book Added";
        $codeArray["m"] = "Book Modified";
        $codeArray["d"] = "Book Deleted";
        $codeArray["c"] = "Configuration Saved";
        $codeArray["b"] = "Book List Refreshed";
        
        if ($_GET["code"]){
            $this->status = "<br /><b><span style=\"color:#FF0000;\">" . $codeArray[$_GET["code"]] . "</span></b>";   
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
        $text .= "<script type='text/javascript' src='" . BOOKX_URL. "suitex.js'></script>"; 
        return $text;
    }
    
    /**
    * Refreshes all the books in the list
    * 
    */
    
    function bookx_refreshAll(){
        
        if (!wp_verify_nonce($_GET["_wpnonce"])){ die('Security check'); }        
        $sql = "select bx_item_id, bx_item_isbn from " . $this->wpdb->prefix . "bx_item";
        $result = $this->wpdb->get_results($sql);
        
        foreach($result as $row){
            $this->bookArray = array();
            $this->bookx_fetchItem($row->bx_item_isbn);

            $sql = "update " . $this->wpdb->prefix . "bx_item set ";
            foreach(array_keys($this->bookArray) as $key){     
                $sql .= "bx_item_" . $key . " = '" . addslashes($this->bookArray[$key]) . "', ";
            }

            $sql .= "bx_item_date_added = " . time() . " where bx_item_id = " . $row->bx_item_id . " limit 1";
            
            $this->wpdb->query($sql);
            if ($this->die){ print("<br><br>$sql<br><br>"); die();}
            
            
        }
        $url = $this->baseURL . "&code=b";
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

        $sql = "CREATE TABLE `" . $this->wpdb->prefix . "bx_item` (`bx_item_id` int(10) NOT NULL AUTO_INCREMENT,`bx_item_name` varchar(255) NOT NULL,`bx_item_author` varchar(255) NOT NULL,`bx_item_comments` text,`bx_item_date` int(10) NOT NULL,`bx_item_date_added` int(10) NOT NULL,`bx_item_format` varchar(255) NOT NULL,`bx_item_image` text NOT NULL,`bx_item_isbn` varchar(15) DEFAULT NULL, `bx_item_link` text NOT NULL, `bx_item_pages` int(10) NOT NULL DEFAULT '0', `bx_item_price` float(4,2) NOT NULL DEFAULT '0.00',`bx_item_sidebar` tinyint(1) NOT NULL DEFAULT '0',`bx_item_summary` text,`bx_item_publisher` varchar(255) NOT NULL,PRIMARY KEY (`bx_item_id`),UNIQUE KEY `bx_item_isbn` (`bx_item_isbn`), KEY `bx_item_sidebar` (`bx_item_sidebar`));";
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

        $text = "<div class=\"wrap\"><h2>BookX</h2>";
        $text .= "<form method=\"post\" action=\"\">";
        $text .= "<input type=\"hidden\" name=\"action\" value=\"update\" />";
        
        $text .= $this->status;
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
                $_GET["id"] = $_POST["id"];
                $label = "Modify Book : " . $row->name; 
                $action = "modify";
            }
            else {
                $row->isbn = $_POST["isbn"];
                $row->sidebar = $_POST["sidebar"];
                $row->comments = $_POST["comments"];
                $label = "Add Book";
                $action = "add";                 
            }

            $status = "<span style=\"font-weight: bold; color: #FF0000;\">Import Failed</span><br />";            
        }
        else if ($_GET["id"]){
            $query = "select bx_item_name as name, bx_item_isbn as isbn, bx_item_comments as comments, bx_item_sidebar as sidebar from " . $this->wpdb->prefix . "bx_item where bx_item_id = %d limit 1";
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
        $text .= "</table>";
        $text .= "<p class=\"submit\"><input type=\"submit\" name=\"Submit\" value=\"Save Changes\" />";
        if ($action == "modify"){
            $deleteURL = $this->baseURL . "&sub=submit&id=" . $_GET["id"] . "&_wpnonce=" . $this->nonce;
            $text .= "&nbsp;<input type=\"button\" value=\"Delete\" onClick=\"confirmAction('Are you sure you want to delete this book?', '$deleteURL');\" />";
        }
        $text .= "</p></form>";
        $text .= "</div></div></div></div>";
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
        $this->bookx_fetchItem(strip_tags($_POST["isbn"]));        
        

        if ($_POST["action"] == "add"){
            if ($this->bookArray["name"] == ''){
                $this->bookx_form("Import Failed");
                return false;
            }            
            $sql = "insert into " . $this->wpdb->prefix . "bx_item ";
            foreach(array_keys($this->bookArray) as $key){
                $fields .= "bx_item_" . $key . ", ";
                $values .= "'" . addslashes($this->bookArray[$key]) . "', ";
            }
            $fields .= "bx_item_comments, bx_item_sidebar, bx_item_date_added";
            $values .= "'$comments', " . $_POST["sidebar"] . ", " . time();
            $sql .= "($fields) values ($values)";
            $code = "a";
        }
        else if ($_POST["action"] == "modify"){
            if ($this->bookArray["name"] == ''){
                $this->bookx_form("Import Failed");
                return false;
            }   
            $sql = "update " . $this->wpdb->prefix . "bx_item set ";
            foreach(array_keys($this->bookArray) as $key){     
                $sql .= "bx_item_" . $key . " = '" . addslashes($this->bookArray[$key]) . "', ";
            }
            $sql .= "bx_item_comments = '$comments', bx_item_sidebar = " . $_POST["sidebar"] . ", ";
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

        $this->wpdb->query($sql);
        
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