<?php

class listingx_list {
	/**
	* Global Functions
 	* @package WordPress
 	*/

 	var $filters  = array();
 	var $search   = false;
 	var $orderForm = false;

 	function listHeaders($headerArray, $link, $order, $sort){

 	}

 	function listRows(){

 	}

 	function addFilter($col, $name, $options, $current=''){
 		$text = "<select name=\"$col\">";
        $text .= "<option value=\"\">$name</option>";
        if (is_array($options)){
        	foreach(array_keys($options) as $o){
        		if ($current == $o){ $s = "selected"; }
        		else { $s = ''; }
        		$text .= "<option value=\"$o\">" . $options[$o] . "</option>";
			}
        }
 		$text .= "</select>";
 		$this->filters[] = $text;

 	}

 	function createOrderForm($headers, $current=''){
 		$text = "<select name=\"order\">";
 		$text .= "<option value=\"\">Order by</option>";
 		foreach(array_keys($headers) as $h){
 			if (!in_array($h, $this->omit)){
 				if ($h == $current){ $s = "selected"; }
 				else { $s = ''; }
 				$text .= "<option value=\"$h\">" . $headers[$h] . "</option>";
 			}
 		}
 		$text .= "</select>";
 		$this->filters[] = $text;
 	}


 	function startList($headers, $link, $order, $sort, $rows, $hidden=''){

		if (is_array($hidden)){
			foreach(array_keys($hidden) as $hide){
				$h .= "<input type=\"hidden\" name=\"$hide\" value=\"" . $hidden[$hide] . "\" />\r\n";
			}
		}

		if ($this->search){
			//if ($this->searchLink){ $searchAction = $this->searchLink; }
			//else { $searchAction = $link; }

			$text .= "<form class=\"search-form\" action=\"$link\" method=\"get\">";
			$text .= "<p class=\"search-box\">";
			$text .= "<label class=\"hidden\" for=\"\">" . $this->searchLabel . ":</label>";
			$text .= $h;
			$text .= "<input type=\"text\" class=\"search-input\" id=\"project-search-input\" name=\"s\" value=\"" .  $_GET["s"] . "\" />";
			$text .= "<input type=\"submit\" value=\"" . $this->searchLabel . "\" class=\"button\" />";
			$text .= "</p>";
			$text .= "</form>";
			$text .= "<br class=\"clear\" />";
		}

		if ($this->filters || $this->orderForm){
			if ($this->orderForm){ $this->createOrderForm($headers, $order); }
			$text .= "<form id=\"posts-filter\" action=\"$link\" method=\"get\">";
			$text .= "<div class=\"tablenav\">";

			$text .= "<div class=\"alignleft actions\">";
            foreach($this->filters as $f){
            	$text .= $f . "&nbsp;";
            }
			$text .= $h;
			$text .= "<input type=\"submit\" id=\"post-query-submit\" value=\"Filter\" class=\"button-secondary\" />";
			$text .= "</div><br class=\"clear\" /></div>";
			$text .= "<div class=\"clear\"></div>";


        }

        $text .= "<input type=\"hidden\" id=\"_wpnonce\" name=\"_wpnonce\" value=\"" . wp_create_nonce() . "\" />";
        $text .= "<input type=\"hidden\" name=\"_wp_http_referer\" value=\"" . $_SERVER["PHP_SELF"] . "\" />";
        $text .= "<table class=\"widefat fixed\" cellspacing=\"0\">";

		foreach(array_keys($headers) as $h){
			$cols .= "<th scope=\"col\" id=\"$h\" class=\"manage-column column-" . $h;
			if ($h == "cb"){ $cols .= " check-column"; $cb=true; }
			$cols .= "\" style=\"\">" . $headers[$h] . "</th>";
		}

		$text .= "<thead><tr>$cols</tr></thead><tfoot><tr>$cols</tr></tfoot>";
		$text .= "<tbody>";

        if (!is_array($rows)){
        	$rows = array();
        }
        $x=1;

        foreach(array_keys($rows) as $id){
        	if ($x/2){ $class = "class=\"alternate\""; }
        	else { $x++; }

        	$text .= "<tr id=\"link-$id\" valign=\"middle\" $class>";
        	if ($cb){
        		$text .= "<th scope=\"row\" class=\"check-column\">";
        		$text .= "<input type=\"checkbox\" name=\"linkcheck[]\" value=\"$id\" />";
        		$text .= "</th>";
        	}
        	//else { die("NO CHECK BOX"); }
        	$j=1;
        	foreach($rows[$id] as $r){
            	$text .= "<td>";
            	if ($j == 1){
            		$text .= "<strong><a href=\"$link" . "$id\">";
            		$text .= $r . "</a></strong>";


            	}
            	else { $text .= $r; }
            	$text .= "</td>";
            	$j++;
        	}
        	$text .= "</tr>";

        }


        $text .= "</tbody></table>";



        $this->text = $text;

 	}

 	function endList($listName){

 	}
}
?>
