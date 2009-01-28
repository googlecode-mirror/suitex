<?php


class listingx_getFile {

    function __construct(){
		global $wpdb;

        if (!$_GET["file"]){ die("Invalid File"); }

        $q = "select lx_file_name, lx_file_type, lx_file_data, lx_file_size from " . $this->wpdb->prefix . "lx_file where lx_file_id = %d limit 1";
        $row = $this->wpdb->get_row($this->wpdb->prepare($q, $_GET["file"]));

        $q = "update " . $this->wpdb->prefix . "lx_file set lx_file_download = lx_file_download + 1 where lx_file_id = %d";
        $this->wpdb->query($this->wpdb->prepare($q, $_GET["file"]));



        header("Content-length: " . $row->lx_file_size);
		header("Content-type: " . $row->lx_file_tpe);
		header("Content-Disposition: attachment; filename=" . $row->lx_file_name);
		print($row->lx_file_data);
		die();

    }
}
?>
