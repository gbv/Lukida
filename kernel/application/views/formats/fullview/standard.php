<?php

// *****************************************************
// **** Section ONE: Get & Formats required Fields *****
// *****************************************************

// Bereits verfügbare Parameter
// $this->PPN					          PPN des Mediums
// $this->contents			        Komplettes Array des Mediums
// $this->contents["format"]  	Format des Mediums (internes Format)

// Parameter aus INI-Datei einlesen
$Tab2 = (isset($_SESSION["config_discover"]["fullview"]["tab2_available"])   
            && $_SESSION["config_discover"]["fullview"]["tab2_available"] == "1" )  
             ? true : false;

$Tab5 = (isset($_SESSION["config_discover"]["fullview"]["tab5_elements"])   
            && $_SESSION["config_discover"]["fullview"]["tab5_elements"] != "" )  
             ? true : false;
$Tab5Name = (isset($_SESSION["config_discover"]["fullview"]["tab5_name"])   
            && $_SESSION["config_discover"]["fullview"]["tab5_name"] != "" )  
             ? $_SESSION["config_discover"]["fullview"]["tab5_name"] : "Tab5";

$Buttom	= (isset($_SESSION["config_discover"]["fullview"]["buttomelement"])
              && $_SESSION["config_discover"]["fullview"]["buttomelement"] != "" )
               ? $_SESSION["config_discover"]["fullview"]["buttomelement"] : "";
$TabGens = ( trim($Buttom) != "" )  ? explode(",",trim($Buttom)) : array();

// *****************************************************
// ************ Section TWO: Create Output *************
// *****************************************************

// Start Output

// Tabs Header
$Tab = ( ( isset($_SESSION["internal"]["marc"]) && $_SESSION["internal"]["marc"] == "1" ) 
      || ( isset($_SESSION["internal"]["daia"]) && $_SESSION["internal"]["daia"] == "1" )
      || ( isset($_SESSION["internal"]["marcfull"]) && $_SESSION["internal"]["marcfull"] == "1" )
      || $Tab2 ) 
      ? true : false;

if ( $Tab )  
{
  $Output .= "<ul class='nav nav-tabs' role='tablist'>";
  $Output .= "<li role='presentation' class='active'><a href='#tab1_" . $this->dlgid . "' aria-controls='tab1' role='tab' data-toggle='tab'>" . $this->CI->database->code2text('TITLE') . "</a></li>";

  if ( $Tab2 )
  {
    $Output .= "<li role='presentation'><a href='#tab2_" . $this->dlgid . "' aria-controls='tab2' role='tab' data-toggle='tab'>" . $this->CI->database->code2text('SIMULARPUBS') . "</a></li>";
  }
  if ( ( isset($_SESSION["internal"]["marc"]) && $_SESSION["internal"]["marc"] == "1" ) || ( isset($_SESSION["internal"]["marcfull"]) && $_SESSION["internal"]["marcfull"] == "1" ) )
  {
    $Output .= "<li role='presentation'><a href='#tab3_" . $this->dlgid . "' aria-controls='tab3' role='tab' data-toggle='tab'>" . $this->CI->database->code2text('RECORDMARC21') . "</a></li>";
  }
  if ( isset($_SESSION["internal"]["daia"]) && $_SESSION["internal"]["daia"] == "1" )
  {
    $Output .= "<li role='presentation'><a href='#tab4_" . $this->dlgid . "' aria-controls='tab4' role='tab' data-toggle='tab'>" . $this->CI->database->code2text('RECORDDAIA') . "</a></li>";
  }
  if ( $Tab5 )
  {
    $Output .= "<li role='presentation'><a href='#tab5_" . $this->dlgid . "' aria-controls='tab5' role='tab' data-toggle='tab'>" . $this->CI->database->code2text($Tab5Name) . "</a></li>";
  }
  $Output .= "</ul><!-- tab panes --><div class='tab-content'>";
}

// ****** Start Tab 1 ******
$Output .= "<div role='tabpanel' class='tab-pane fade in active' id='tab1_" . $this->dlgid . "'>";
$Output .= "<table id='mail_" . $this->dlgid . "' class='table rowheight-reduced table-hover borderless small'><tbody>";

// Show elements based on ini-file
$Output .= $this->LoadTabElements("tab1_elements");

$Output .= "</tbody></table>";

// Final Block
foreach ( $TabGens as $TabGen )
{
  $Output .= $this->LoadElement(trim($TabGen));
}

$Output .= "</div>";
// ****** Ende Tab 1 ******

// ****** Start Tab 2 ******
if ( $Tab2 )
{
  $Output .= "<div role='tabpanel' class='tab-pane fade' id='tab2_" . $this->dlgid . "'>";
  $Output .= "<div class='row row-auto' id='simularpubscontent_" . $this->dlgid . "'>";
  $Output .= "<div class='space'></div>";
  $Output .= "<div class='outercircle'></div><div class='innercircle'></div>";
  $Output .= "</div>";
  $Output .= "</div>";
}
// ****** Ende Tab 2 ******
 
// ****** Start Tab 3 ******
if ( ( isset($_SESSION["internal"]["marc"]) && $_SESSION["internal"]["marc"] == "1" ) || ( isset($_SESSION["internal"]["marcfull"]) && $_SESSION["internal"]["marcfull"] == "1" ) )
{
  $Output .= "<div role='tabpanel' class='tab-pane fade' id='tab3_" . $this->dlgid . "'>";
  $Output .= "<div class='table-responsive'><table class='table table-striped rowheight-reduced table-hover borderless small'><tbody>";

  // Show elements based on ini-file
  $Output .= $this->LoadTabElements("tab3_elements");

  $Output .= "</tbody></table></div>";
  $Output .= "</div>";
  // ****** Ende Tab 3 ******
}

// ****** Start Tab 4 ******
if ( isset($_SESSION["internal"]["daia"]) && $_SESSION["internal"]["daia"] == "1" )
{
  $Output .= "<div role='tabpanel' class='tab-pane fade' id='tab4_" . $this->dlgid . "'>";
  $Output .= "<div class='table-responsive'><table class='table table-striped rowheight-reduced table-hover borderless small'><tbody>";

  // Show elements based on ini-file
  $Output .= $this->LoadTabElements("tab4_elements");

  $Output .= "</tbody></table></div>";
  $Output .= "</div>";
  // ****** Ende Tab 4 ******
}

// ****** Start Tab 5 ******
if ( $Tab5 )
{
  $Output .= "<div role='tabpanel' class='tab-pane fade' id='tab5_" . $this->dlgid . "'>";
$Output .= "<div class='table-responsive'><table class='table table-striped rowheight-reduced table-hover borderless small'><tbody>";

  // Show elements based on ini-file
  $Output .= $this->LoadTabElements("tab5_elements");

  $Output .= "</tbody></table></div>";
  $Output .= "</div>";
}
// ****** Ende Tab 5 ******
 
// End Tabbody
$Output .= "</div>";

// Message Bar
$Output .= "<p></p><div id='fullview_" . $this->dlgid . "_messagebar'></div>";

?>
