<?php

// Start Output
$Output .= "<ul class='list-group editul'>";

$Output .= "<li class='list-group-item editli col-sm-12 col-md-6 clear-left'>";
$Output .= $this->CI->database->code2text('COMMUNITYLIBRARIES');
$Output .= "<div class='material-switch pull-right'>";
$Output .= "<input id='communitylibraries' class='communitylibraries' type='checkbox'/>";
$Output .= "<label for='communitylibraries' class='noUi-connect'></label>";
$Output .= "</div>";
$Output .= "</li>";

$Output .= "<li class='list-group-item editli col-sm-12 col-md-6 clear-right'>";
$Output .= $this->CI->database->code2text('PHONETICSEARCH');
$Output .= "<div class='material-switch pull-right'>";
$Output .= "<input id='phoneticSwitch' class='phoneticSwitch' type='checkbox'/>";
$Output .= "<label for='phoneticSwitch' class='noUi-connect'></label>";
$Output .= "</div>";
$Output .= "</li>";

$Output .= "</ul>";

// Message Bar
$Output .= "<p>&nbsp;</p><div id='settings_messagebar'></div>";

?>