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

$Output .= "<li class='list-group-item editli col-sm-12 clear-right'>";
$Output .= "<div>" . $this->CI->database->code2text('FACETYEAR') . " <span class='yearstart2 editable' data-type='number' data-mode='popup' data-placement='top' data-inputclass='yearinput'> </span> - <span class='yearend2 editable' data-type='number' data-mode='popup' data-placement='top' data-inputclass='yearinput'> </span></div>";
$Output .= "<div class='text-right'>";
$Output .= "<div id='pubyear3'></div>";
$Output .= "</div>";
$Output .= "</li>";

$Output .= "<div id='FlexFilters'>";

$Output .= "<li id='titleArea' data-filter='title' class='list-group-item editli col-sm-12 col-md-6 clear-left hide'>";
$Output .= $this->CI->database->code2text('TITLE');
$Output .= "<div class='text-right'>";
$Output .= "<div id='titleFilterValue' class='editable' data-type='textarea' data-mode='inline' data-rows='3' data-emptytext='(" . $this->CI->database->code2text('EMPTY') . ")' data-emptyclass='editempty'> </div>";
$Output .= "</div>";
$Output .= "</li>";

$Output .= "<li id='authorArea' data-filter='author' class='list-group-item editli col-sm-12 col-md-6 clear-right hide'>";
$Output .= $this->CI->database->code2text('PARTICIPANTS');
$Output .= "<div class='text-right'>";
$Output .= "<div id='authorFilterValue' class='editable' data-type='textarea' data-mode='inline' data-rows='3' data-emptytext='(" . $this->CI->database->code2text('EMPTY') . ")' data-emptyclass='editempty'> </div>";
$Output .= "</div>";
$Output .= "</li>";

$Output .= "<li id='subjectArea' data-filter='subject' class='list-group-item editli col-sm-12 col-md-6 clear-left hide'>";
$Output .= $this->CI->database->code2text('SUBJECT');
$Output .= "<div class='text-right'>";
$Output .= "<div id='subjectFilterValue' class='editable' data-type='textarea' data-mode='inline' data-rows='3' data-emptytext='(" . $this->CI->database->code2text('EMPTY') . ")' data-emptyclass='editempty'> </div>";
$Output .= "</div>";
$Output .= "</li>";

$Output .= "<li id='classArea' data-filter='class' class='list-group-item editli col-sm-12 col-md-6 clear-right hide'>";
$Output .= $this->CI->database->code2text('CLASS');
$Output .= "<div class='text-right'>";
$Output .= "<div id='classFilterValue' class='editable' data-type='textarea' data-mode='inline' data-rows='3' data-emptytext='(" . $this->CI->database->code2text('EMPTY') . ")' data-emptyclass='editempty'> </div>";
$Output .= "</div>";
$Output .= "</li>";

$Output .= "<li id='publisherArea' data-filter='publisher' class='list-group-item editli col-sm-12 col-md-6 clear-left hide'>";
$Output .= $this->CI->database->code2text('PUBLISHER');
$Output .= "<div class='text-right'>";
$Output .= "<div id='publisherFilterValue' class='editable' data-type='textarea' data-mode='inline' data-rows='3' data-emptytext='(" . $this->CI->database->code2text('EMPTY') . ")' data-emptyclass='editempty'> </div>";
$Output .= "</div>";
$Output .= "</li>";

$Output .= "<li id='tocArea' data-filter='toc' class='list-group-item editli col-sm-12 col-md-6 clear-right hide'>";
$Output .= $this->CI->database->code2text('TOC');
$Output .= "<div class='text-right'>";
$Output .= "<div id='tocFilterValue' class='editable' data-type='textarea' data-mode='inline' data-rows='3' data-emptytext='(" . $this->CI->database->code2text('EMPTY') . ")' data-emptyclass='editempty'> </div>";
$Output .= "</div>";
$Output .= "</li>";

$Output .= "<li id='idArea' data-filter='id' class='list-group-item editli col-sm-12 col-md-6 clear-left hide'>";
$Output .= $this->CI->database->code2text('ID');
$Output .= "<div class='text-right'>";
$Output .= "<div id='idFilterValue' class='editable' data-type='textarea' data-mode='inline' data-rows='3' data-emptytext='(" . $this->CI->database->code2text('EMPTY') . ")' data-emptyclass='editempty'> </div>";
$Output .= "</div>";
$Output .= "</li>";

$Output .= "<li id='seriesArea' data-filter='series' class='list-group-item editli col-sm-12 col-md-6 clear-right hide'>";
$Output .= $this->CI->database->code2text('SERIES');
$Output .= "<div class='text-right'>";
$Output .= "<div id='seriesFilterValue' class='editable' data-type='textarea' data-mode='inline' data-rows='3' data-emptytext='(" . $this->CI->database->code2text('EMPTY') . ")' data-emptyclass='editempty'> </div>";
$Output .= "</div>";
$Output .= "</li>";

$Output .= "<li id='isnArea' data-filter='isn' class='list-group-item editli col-sm-12 col-md-6 clear-left hide'>";
$Output .= $this->CI->database->code2text('ISN');
$Output .= "<div class='text-right'>";
$Output .= "<div id='isnFilterValue' class='editable' data-type='textarea' data-mode='inline' data-rows='3' data-emptytext='(" . $this->CI->database->code2text('EMPTY') . ")' data-emptyclass='editempty'> </div>";
$Output .= "</div>";
$Output .= "</li>";
$Output .= "</div>";

$Output .= "</ul>";

// Message Bar
$Output .= "<p>&nbsp;</p><div id='settings_messagebar'></div>";

?>