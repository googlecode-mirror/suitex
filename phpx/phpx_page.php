<?php


class phpx_page {
	
	var $scope = 'admin';
	
	public function startPage($title='', $status=''){
        $text = '<div class="wrap">';
        $text .= '<h2>' . $title . '</h2>';
        $text .= '<div id="poststuff" class="metabox-holder">';
        $text .= '<div id="post-body" class="has-sidebar">';
        $text .= '<div id="post-body-content" class="has-sidebar-content">';
        $text .= '<div class="postbox" id="phpxContainer">';
        $text .= $status;
        $text .= '<div class="inside">';     		
        return $text;
	}
	
	public function endPage(){
		$text = '</div></div></div></div></div></div>';
		return $text;
	}
	
	
	
}
?>
