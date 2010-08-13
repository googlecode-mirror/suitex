<?php
class bookx_fetch extends bookx_admin {

    function __construct($parent){
        $this->parent = $parent;
    }
    
    
    
    /** 
    * Fetches book information based on the isbn
    * 
    * @param    mixed   $isbn
    * @return   array   $this->bookArray
    */
        
    function bookx_fetchItem($isbn){
        $url = 'http://www.openlibrary.org/search?isbn=' . $isbn;
        
        print($url . "<br><br>");
        if (function_exists('curl_init')){
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url );
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            $lines = curl_exec($ch);
            curl_close($ch);
        }
        else {
            $lines = file_get_contents($url);
        }
        
        print($lines);
        die();
        
        $start = "<h6 class=\"title\">Open Library</h6>";
        $lines = substr($lines, strpos($lines, $start));
        $end = "</span>";
        $lines = substr($lines, 0, strpos($lines, $end));
        $lines = strip_tags($lines);
        $objNumber = trim(rtrim(str_replace("Open Library", '', $lines)));
        
        $xmlUrl = "http://openlibrary.org/books/" . $objNumber . ".rdf";
        
        
        if (function_exists('curl_init')){
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $xmlUrl );
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            $xmlData = curl_exec($ch);
            curl_close($ch);
        }
        else {
            $xmlData = simplexml_load_file($xmlUrl);
        } 
        
        $replaceArray = array("rdf", "bibo", "dcterms", "dc", "dcam", "@");
        
        foreach($replaceArray as $r){
            $xmlData = str_replace($r . ":", '', $xmlData);
        }
        
        print($xmlData);
        die();
        
        //$xml = new SimpleXMLElement($xmlData);    
                
        $title = $xml->Description->title;


        //print_r($xml);

        $image = "http://covers.openlibrary.org/b/id/" . $isbn . "-M.jpg";




        if ($title != ''){ print("Working on $title <br />"); }
        flush();
        
        
        $this->parent->addBookToArray("publisher", $xml->Description->publisher);
        $this->parent->addBookToArray("price", '');
        $this->parent->addBookToArray("author", $xml->Description->authorList->Description->value);
        $this->parent->addBookToArray("name", $title);
        $this->parent->addBookToArray("date", $xml->Description->issued);
        $this->parent->addBookToArray("pages", $pages);
        $this->parent->addBookToArray("format", '');
        $this->parent->addBookToArray("summary", $xml->Description->description, "<br>");
        $this->parent->addBookToArray("image", $image, true);
        //$this->parent->addBookToArray("image_type", $imageType);
        $this->parent->addBookToArray("link", $xml->Description->attributes["about"], true);
        $this->parent->addBookToArray("isbn", $isbn);
        print_r($this->parent->bookArray);
        die();
    }    
    
}  
?>
