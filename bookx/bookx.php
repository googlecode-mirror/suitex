<?php
/*
Plugin Name: bookX
Plugin URI: http://www.thisrand.com/scripts/bookx
Description: Creates a recommended book list for both a sidebar widget and page based solely on ISBN numbers.
Version: 0.2
Author: Xnuiem
Author URI: http://www.thisrand.com

*/

/*  Copyright 2009 Xnuiem  (email : scripts @T thisrand D07 com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

/**
 * A recommended book plugin
 * @since 2.6
 */
 
 
$sortArray  = array("asc" => "Ascending", "desc" => "Descending");

$fieldArray["bx_item_id"]           = "ID";
$fieldArray["bx_item_name"]         = "Title";
$fieldArray["bx_item_isbn"]         = "ISBN";
$fieldArray["bx_item_author"]       = "Author";
$fieldArray["bx_item_publisher"]    = "Publisher";
$fieldArray["bx_item_date"]         = "Publish Date";
$fieldArray["bx_item_pages"]        = "Pages";
$fieldArray["bx_item_format"]       = "Format";
$fieldArray["bx_item_price"]        = "Price";

$filter     = array("No", "Yes");  
$options    = get_option('bookx_options');

$pluginBase = 'wp-content' . DIRECTORY_SEPARATOR . 'plugins' . DIRECTORY_SEPARATOR . 'bookx';      

require_once(ABSPATH . $pluginBase . DIRECTORY_SEPARATOR . 'bookx_functions.php');  

require_once(ABSPATH . $pluginBase . DIRECTORY_SEPARATOR . 'bookx_widget.php');   


$obj                    = new bookx_functions();
$obj->options           = $options;
$obj->filter            = $filter;
$obj->fieldArray        = $fieldArray;
$obj->sortArray         = $sortArray;
$obj->pluginBase        = $pluginBase;

if (substr_count($_SERVER["REQUEST_URI"], "wp-admin") != 0){  
    require_once(ABSPATH . $pluginBase . DIRECTORY_SEPARATOR . 'bookx_admin.php');
    $adminObj               = new bookx_admin();
    $adminObj->pluginBase   = $pluginBase;
    $adminObj->options      = $options;
    $adminObj->sortArray    = $sortArray;
    $adminObj->fieldArray   = $fieldArray;
    $adminObj->filter       = $filter;
    add_action('admin_menu', array($adminObj, 'bookx_adminMenu')); 
    register_activation_hook(__FILE__, array($adminObj, 'bookx_install'));
    register_deactivation_hook(__FILE__, array($adminObj, 'bookx_uninstall'));

}
$widgetObj              = new bookx_widget();
$widgetObj->options     = $options;
$widgetObj->sortArray   = $sortArray;
$widgetObj->fieldArray  = $fieldArray;
 


add_action('widgets_init', array($widgetObj, 'bookx_widget_init'));   
add_action('wp', array($obj, 'bookx_init'));


?>