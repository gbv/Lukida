<?php

// *****************************************************
// **** Section ONE: Get & Formats required Fields *****
// *****************************************************


// *****************************************************
// ************ Section TWO: Create Output *************
// *****************************************************

// Start Output
$Output .= $this->CI->database->code2text('InputByline');

// Tabs Header
$Output .= "<ul class='nav nav-tabs' role='tablist'>";
$Output .= "<li role='presentation' class='active'><a href='#title_" . $this->dlgid . "' aria-controls='p1' role='tab' data-toggle='tab'>" . $this->CI->database->code2text('Title') . "</a></li>";
$Output .= "<li role='presentation'><a href='#author_" . $this->dlgid . "' aria-controls='p2' role='tab' data-toggle='tab'>" . $this->CI->database->code2text('Author') . "</a></li>";
$Output .= "<li role='presentation'><a href='#subject_" . $this->dlgid . "' aria-controls='p3' role='tab' data-toggle='tab'>" . $this->CI->database->code2text('Subject') . "</a></li>";
$Output .= "<li role='presentation'><a href='#class_" . $this->dlgid . "' aria-controls='p4' role='tab' data-toggle='tab'>" . $this->CI->database->code2text('Class') . "</a></li>";
$Output .= "<li role='presentation'><a href='#toc_" . $this->dlgid . "' aria-controls='p5' role='tab' data-toggle='tab'>" . $this->CI->database->code2text('TOC') . "</a></li>";
$Output .= "<li role='presentation'><a href='#year_" . $this->dlgid . "' aria-controls='p6' role='tab' data-toggle='tab'>" . $this->CI->database->code2text('Year') . "</a></li>";
$Output .= "<li role='presentation'><a href='#publisher_" . $this->dlgid . "' aria-controls='p7' role='tab' data-toggle='tab'>" . $this->CI->database->code2text('Publisher') . "</a></li>";
$Output .= "<li role='presentation'><a href='#isn_" . $this->dlgid . "' aria-controls='p8' role='tab' data-toggle='tab'>" . $this->CI->database->code2text('ISN') . "</a></li>";
$Output .= "<li role='presentation'><a href='#series_" . $this->dlgid . "' aria-controls='p9' role='tab' data-toggle='tab'>" . $this->CI->database->code2text('Series') . "</a></li>";
$Output .= "<li role='presentation'><a href='#id_" . $this->dlgid . "' aria-controls='p9' role='tab' data-toggle='tab'>" . $this->CI->database->code2text('ID') . "</a></li>";
$Output .= "</ul>";

// Start Tabbody
$Output .= "<div class='tab-content'>";

// ****** Start Tab 1 ******
$Output .= "<div role='tabpanel' class='tab-pane fade in active' id='title_" . $this->dlgid . "'>";
$Output .= "<textarea class='form-control' rows='5'></textarea>";
$Output .= "</div>";
// ****** Ende Tab 1 ******

// ****** Start Tab 2 ******
$Output .= "<div role='tabpanel' class='tab-pane fade' id='author_" . $this->dlgid . "'>";
$Output .= "<textarea class='form-control' rows='5'></textarea>";
$Output .= "</div>";
// ****** Ende Tab 2 ******
 
// ****** Start Tab 3 ******
$Output .= "<div role='tabpanel' class='tab-pane fade' id='subject_" . $this->dlgid . "'>";
$Output .= "<textarea class='form-control' rows='5'></textarea>";
$Output .= "</div>";
// ****** Ende Tab 3 ******

// ****** Start Tab 4 ******
$Output .= "<div role='tabpanel' class='tab-pane fade' id='class_" . $this->dlgid . "'>";
$Output .= "<textarea class='form-control' rows='5'></textarea>";
$Output .= "</div>";
// ****** Ende Tab 4 ******

// ****** Start Tab 5 ******
$Output .= "<div role='tabpanel' class='tab-pane fade' id='toc_" . $this->dlgid . "'>";
$Output .= "<textarea class='form-control' rows='5'></textarea>";
$Output .= "</div>";
// ****** Ende Tab 5 ******
// 
// // ****** Start Tab 6 ******
$Output .= "<div role='tabpanel' class='tab-pane fade' id='year_" . $this->dlgid . "'>";
$Output .= "<textarea class='form-control' rows='5'></textarea>";
$Output .= "</div>";
// ****** Ende Tab 6 ******

// ****** Start Tab 7 ******
$Output .= "<div role='tabpanel' class='tab-pane fade' id='publisher_" . $this->dlgid . "'>";
$Output .= "<textarea class='form-control' rows='5'></textarea>";
$Output .= "</div>";
// ****** Ende Tab 7 ******

// ****** Start Tab 8 ******
$Output .= "<div role='tabpanel' class='tab-pane fade' id='isn_" . $this->dlgid . "'>";
$Output .= "<textarea class='form-control' rows='5'></textarea>";
$Output .= "</div>";
// ****** Ende Tab 8 ******  
 
// ****** Start Tab 9 ******
$Output .= "<div role='tabpanel' class='tab-pane fade' id='series_" . $this->dlgid . "'>";
$Output .= "<textarea class='form-control' rows='5'></textarea>";
$Output .= "</div>";
// ****** Ende Tab 9 ******  

// ****** Start Tab 10 ******
$Output .= "<div role='tabpanel' class='tab-pane fade' id='id_" . $this->dlgid . "'>";
$Output .= "<textarea class='form-control' rows='5'></textarea>";
$Output .= "</div>";
// ****** Ende Tab 10 ******

// End Tabbody
$Output .= "</div>";

// Output Count
$Output .= "<div id='assistantcount' class='text-center'></div>";

// Message Bar
$Output .= "<p></p><div id='assistant_" . $this->dlgid . "_messagebar'></div>";

$Output .= "<script>";
$Output .= "$('a[data-toggle=\"tab\"]').on('shown.bs.tab', function (e) {";
$Output .= "  $(\"textarea\").focus();";
$Output .= "});";
$Output .= "</script>";
?>
