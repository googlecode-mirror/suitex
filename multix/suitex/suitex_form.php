<?php
class suitex_form {
    
    var $idSet = 0;
    var $fieldset = false;
    var $required = false;
    var $formId;
    
    function startForm($action, $id="theForm", $method="post", $files=false){
        $this->text .= "<form method=\"" . $method . "\" action=\"" . $action . "\" id=\"" . $id . "\"";
        if ($files == true){
            $this->text .= " enctype=\"multipart/form-data\"";
        }
        
        $this->text .= ">";
        $this->formId = $id;
        
    }
    
    function endForm($buttonText="Submit"){
        if ($this->fieldset == true){ $this->endFieldSet(); }
        $this->text .= "<p class=\"submit\"><input class=\"submit\" name=\"submit\" type=\"submit\" value=\"" . $buttonText . "\" id=\"" . $this->formId . "_end\" /></p>";        
        $this->text .= "</form>";
        if ($this->required == true){
            $this->text .= "<script>jQuery(document).ready(function(){ jQuery(\"#" . $this->formId . "\").validate(); }); </script>";            
        }
        return $this->text;   
    }
    
    function startFieldSet($legend=''){
        $this->text .= "<fieldset><legend>" . $legend . "</legend>";
        $this->fieldset = true;
        
    }
       
    function endFieldSet(){
        $this->text .= "</fieldset>";
        $this->fieldset = false;
    }
    
    function fileField($label, $name, $required=false){
        
        $this->text .= "<p><label>" . $label . "</label><em>";
        if ($required == true){ 
            $this->required = true;
            $this->text .= "*"; 
        }
        else { $this->text .= "&nbsp;&nbsp;"; }
        $this->text .= "</em><input type=\"file\" id=\"" . $this->idSet . "\" name=\"" . $name . "\"";
        if ($required != false && $required != ''){ 
            $this->text .= " class=\"required\"";  
        }
        $this->text .= " value=\"" . $value . "\" /></p>";      
        $this->idSet++;
    }
    
    function freeText($text, $class=''){
        $text = '<p class="' . $class . '">' . $text . '</p>';
        $this->subText .= $text;
        $this->text .= $text;       
        if ($this->colName){ $col = $this->colName; $this->$col .= $text; } 
    }
    
    function phoneField($label, $name, $list, $value=array(), $required=false){
        
        $this->text .= "<p><label>" . $label . "</label><em>";
        if ($required == true){ 
            $this->required = true;
            $this->text .= "*"; 
        }
        else { $this->text .= "&nbsp;&nbsp;"; }
        $this->text .= "</em><input type=\"text\" id=\"" . $this->idSet . "\" name=\"" . $name . "_text\"";
        if ($required == true){ $this->text .= "class=\"required phone\""; }
        $this->text .= " value=\"" . $value[0] . "\" />&nbsp;";
        $this->text .= "<select name=\"" . $name . "_type\" ";
        if ($required == true){ $this->text .= "class=\"required\""; }
        $this->text .= "><option value=''></option>";
        foreach(array_keys($list) as $p){
            if ($p == $value[1] && $value[1] != ''){ $s = "selected"; }
            else { $s = ''; }
            $this->text .= "<option value=\"" . $p . "\" $s>" . $list[$p] . "</option>";
        }
        $this->text .= "</select>";
        if ($value[2] != ''){ 
            $this->text .= "<input type=\"hidden\" value=\"" . $value[2] . "\" name=\"" . $name . "_id\" />";
        }
        
        
        
        
        $this->text .= "</p>";      
        $this->idSet++;        
    }
    
    function dateField($label, $name, $value, $required=false, $useCalendar=false){
        
        if ($value > 1000){ $value = date("m/d/Y", $value); }
        else { $value = ''; }
        $text .= "<p id=\"p" . $this->idSet . "\" class=\"date\"><label class=\"date\">" . $label . "</label><em>";
        if ($required == true){ 
            $this->required = true;
            $text .= "*"; 
        }
        else { $text .= "&nbsp;&nbsp;"; }
        $text .= "</em><input type=\"text\" id=\"f" . $this->idSet . "\" name=\"" . $name . "\"";
        if ($required != false && $required != ''){ 
            $text .= " class=\"required date\"";      
        }
        $text .= " value=\"" . $value . "\" /></p>";  
        if ($useCalendar == true){   
            if ($value != ''){
                $defaultDate = '{ defaultDate: ' . $value . '}';
            } 
            $text .= '<script language="javascript">$(\'#f' . $this->idSet . '\').datepicker(' . $defaultDate . ');</script>';
        }
        $this->idSet++;  
        $this->subText .= $text;
        $this->text .= $text;
        if ($this->colName){ $col = $this->colName; $this->$col .= $text; }
        $this->date = true;
    }
    
    function dateRange($label, $name, $value=array(), $required=false, $useCalendar=true){
        if (!$this->ajax){
            $name1 = 1;
            $name2 = 2;
        }
        if ($value == ''){ $value = array(); }
        $x=1;
        foreach($value as $v){
            $fName = 'v' . $x;
            $$fName = ($v > 1000) ? date('m/d/Y', $v) : '';
            $x++;
        }    
        $text .= "<p id=\"p" . $this->idSet . "\" class=\"date\"><label class=\"date\">" . $label . "</label><em>";
        if ($required == true){ 
            $this->required = true;
            $text .= "*"; 
        }
        else { $text .= "&nbsp;&nbsp;"; }        
        if ($this->ajax){ $name1 = $this->idSet; }
        $text .= "</em><input type=\"text\" id=\"f" . $this->idSet . "\" name=\"$name" . "$name1\"";
        if ($required != false && $required != ''){ 
            $text .= " class=\"required date\"";      
        }
        $text .= " value=\"" . $v1 . "\" />&nbsp;&nbsp;to&nbsp;&nbsp;";  
        $cal1 = $this->idSet;        
        $this->idSet++;  
        if ($this->ajax){ $name2 = $this->idSet; }
        $text .=  "<input type=\"text\" id=\"f" . $this->idSet . "\" name=\"" . $name . "$name2\"";
        if ($required != false && $required != ''){ 
            $text .= " class=\"required date\"";      
        }
        $text .= " value=\"" . $v2 . "\" /></p>";          
        $cal2 = $this->idSet;
        $this->idSet++;  
        if ($useCalendar == true){
            
            $text .= '<script language="javascript"> 
                var dates = $("#f' . $cal1 . ', #f' . $cal2 . '").datepicker({
                    defaultDate: "+1w",
                    changeMonth: true,
                    numberOfMonths: 2,
                    onSelect: function( selectedDate ) {
                        var option = this.id == "f' . $cal1 . '" ? "minDate" : "maxDate",
                        instance = $( this ).data( "datepicker" ),
                        date = $.datepicker.parseDate(
                        instance.settings.dateFormat ||
                        $.datepicker._defaults.dateFormat,
                        selectedDate, instance.settings );
                        dates.not( this ).datepicker( "option", option, date );
                    }
                });
                </script>';
        }
        $this->subText .= $text;
        $this->text .= $text;
        if ($this->colName){ 
            $col = $this->colName; 
            $this->$col .= $text; 
        }
        $this->date = true;        
    }
    
    function calendarSetup(){
        
    }
    
    function password($label, $name, $required=false, $minLength="8", $confirm=false){
        $this->text .= "<p><label>" . $label . "</label><em>";
        if ($required == true){ 
            $this->required = true;
            $this->text .= "*"; 
        }
        else { $this->text .= "&nbsp;&nbsp;"; }
        $this->text .= "</em><input type=\"password\" id=\"" . $this->idSet . "\" name=\"" . $name . "\"";
        if ($confirm != false){
            $this->text .= " equalTo=\"#" . ($this->idSet - 1) . "\"";
        }
        else if ($required != false && $required != ''){ 
            if (!is_string($required)){ 
                $this->text .= " class=\"required\" minlength=\"" . $minLength . "\"";  
            }
            else {
                $this->text .= " class=\"required " . $required . "\" minlength=\"" . $minLength . "\"";      
            }
        }
        

        $this->text .= " value=\"" . $value . "\" /></p>";      
        $this->idSet++;        
    }
    
    
    function textField($label, $name, $value='', $required=false, $minLength="3"){
        
        $this->text .= "<p><label>" . $label . "</label><em>";
        if ($required == true){ 
            $this->required = true;
            $this->text .= "*"; 
        }
        else { $this->text .= "&nbsp;&nbsp;"; }
        $this->text .= "</em><input type=\"text\" id=\"" . $this->idSet . "\" name=\"" . $name . "\"";
        if ($required != false && $required != ''){ 
            if (!is_string($required)){ 
                $this->text .= " class=\"required\" minlength=\"" . $minLength . "\"";  
            }
            else {
                $this->text .= " class=\"required " . $required . "\" minlength=\"" . $minLength . "\"";      
            }
        }
        $this->text .= " value=\"" . $value . "\" /></p>";      
        $this->idSet++;
    }
    
    function dropDown($label, $name, $value='', $list, $blank=false, $required=false, $multiple=false, $onChange=''){
        
        $this->text .= "<p><label>" . $label . "</label><em>";
        if ($required == true){
            $this->required = true;
            $this->text .= "*";
        }
        else { $this->text .= "&nbsp;&nbsp;"; }
        $this->text .= "</em><select name=\"" . $name . "\" ";
        if ($multiple == true){
            $this->text .= " multiple size=\"8\" ";
        }
        if ($required == true){ $this->text .= "class=\"required\""; }
        $this->text .= " id=\"" . $this->idSet . "\" ";
        if ($onChange != ''){
            $this->text .= "onChange=\"$onChange\"";
        }
        
        
        $this->text .= " >";
        if ($blank == true){
            $this->text .= "<option value=''></option>";
        }
        foreach(array_keys($list) as $l){
            if (is_array($value)){
                if (in_array($l, $value)){ $s = "selected"; } 
                else { $s = ''; }   
            }
            else {
                if ($l == $value && ($value != '')){ $s = "selected"; }
                else { $s = ''; }
            }
            $this->text .= "<option value=\"" . $l . "\" $s>" . $list[$l] . "</option>";
        }
        
        
        $this->text .= "</select></p>";
        $this->idSet++;    
    }
    
    function textArea($label, $name, $value, $required=false, $minLength=8){
        $this->text .= "<p><label>" . $label . "</label><em>";
        if ($required == true){ 
            $this->required = true;
            $this->text .= "*"; 
        }
        else { $this->text .= "&nbsp;&nbsp;"; }
        $this->text .= "</em><textarea id=\"" . $this->idSet . "\" name=\"" . $name . "\"";
        if ($required != false && $required != ''){ 
            if (!is_string($required)){ 
                $this->text .= " class=\"required\" minlength=\"" . $minLength . "\"";  
            }
            else {
                $this->text .= " class=\"required " . $required . "\" minlength=\"" . $minLength . "\"";      
            }
        }
        $this->text .= ">" . stripslashes($value) . "</textarea></p>";      
        $this->idSet++;        
    }
    
    function hidden($name, $value=''){
        $this->text .= "<input type=\"hidden\" name=\"" . $name . "\" value=\"" . $value . "\" />";
    }
    
    function freeField($label, $value){
        $text .= '<p><label>' . $label . '</label><em>&nbsp;</em>' . $value . '</p>';
        $this->subText .= $text;
        $this->text .= $text;   
        if ($this->colName){ $col = $this->colName; $this->$col .= $text; }     
        //$this->idSet++;    
    }    
}
?>