<?php

// *****************************************************
// **** Section ONE: Get & Formats required Fields *****
// *****************************************************

// Bereits verfügbare Parameter
// $this->PPN					          PPN des Mediums
// $this->contents			        Komplettes Array des Mediums
// $this->contents[""format]  	Format des Mediums (internes Format)

// Parameter aus INI-Datei einlesen
$TabGen	= (isset($_SESSION["config_discover"]["fullview"]["fullelement"])		&& $_SESSION["config_discover"]["fullview"]["fullelement"]    != "" )  ? $_SESSION["config_discover"]["fullview"]["fullelement"] : "";
$TabGens = explode(",",$TabGen);
if (count($TabGens) != 2 )  $TabGens = array($TabGen, $TabGen);



// *****************************************************
// ************ Section TWO: Create Output *************
// *****************************************************

// Start Output

// Tabs Header
$Tab = ( ( isset($_SESSION["internal"]["marc"]) && $_SESSION["internal"]["marc"] == "1" ) || ( isset($_SESSION["internal"]["daia"]) && $_SESSION["internal"]["daia"] == "1" ) ) ? true : false;
  
if ( $Tab )  
{
  $Output .= "<ul class='nav nav-tabs' role='tablist'>";
  $Output .= "<li role='presentation' class='active'><a href='#general_" . $this->dlgid . "' aria-controls='general' role='tab' data-toggle='tab'>" . $this->CI->database->code2text('GENERAL') . "</a></li>";
  if ( isset($_SESSION["internal"]["marc"]) && $_SESSION["internal"]["marc"] == "1" )
  {
    $Output .= "<li role='presentation'><a href='#marc_" . $this->dlgid . "' aria-controls='marc' role='tab' data-toggle='tab'>" . $this->CI->database->code2text('RECORDMARC21') . "</a></li>";
  }
  if ( isset($_SESSION["internal"]["daia"]) && $_SESSION["internal"]["daia"] == "1" )
  {
    $Output .= "<li role='presentation'><a href='#daia_" . $this->dlgid . "' aria-controls='daia' role='tab' data-toggle='tab'>" . $this->CI->database->code2text('RECORDDAIA') . "</a></li>";
  }
  $Output .= "</ul><!-- tab panes --><div class='tab-content'>";
}

// ****** Start Tab 1 ******
$Output .= "<div role='tabpanel' class='tab-pane fade in active' id='general_" . $this->dlgid . "'>";
$Output .= "<div class='row'>";
$Output .= "<div class='col-md-10'>";
$Output .= "<table id='mail_" . $this->dlgid . "' class='table rowheight-reduced table-hover borderless small'><tbody>";

// Show elements based on ini-file
$Output .= $this->LoadTabElements("tab1_elements");

$Output .= "</tbody></table>";
$Output .= "</div><div class='col-md-2'>";
$Output .= "<table class='table rowheight-reduced table-hover borderless small'><tbody>";
$Output .= "<tr><td>" . $this->SetCover("fullview") . "</td></tr>";
$Output .= "<tr><td class='text-center'>" . $this->CI->database->code2text($this->format) . "</td></tr>";
$Output .= "</tbody></table></div></div>";
$Output .= "</div>";
// ****** Ende Tab 1 ******

// ****** Start Tab 2 ******
if ( isset($_SESSION["internal"]["marc"]) && $_SESSION["internal"]["marc"] == "1" )
{
  $Output .= "<div role='tabpanel' class='tab-pane fade' id='marc_" . $this->dlgid . "'>";
  $Output .= "<table class='table rowheight-reduced table-hover borderless small'><tbody>";

  // Show elements based on ini-file
  $Output .= $this->LoadTabElements("tab2_elements");
  $Output .= "</tbody></table>";
  $Output .= "</div>";
  // ****** Ende Tab 2 ******
}

// ****** Start Tab 3 ******
if ( isset($_SESSION["internal"]["daia"]) && $_SESSION["internal"]["daia"] == "1" )
{
  $Output .= "<div role='tabpanel' class='tab-pane fade' id='daia_" . $this->dlgid . "'>";
  $Output .= "<table class='table rowheight-reduced table-hover borderless small'><tbody>";

  // Show elements based on ini-file
  $Output .= $this->LoadTabElements("tab3_elements");

  $Output .= "</tbody></table>";
  $Output .= "</div>";
  // ****** Ende Tab 3 ******
}

// End Tabbody
$Output .= "</div>";

// Final Table
if ( $TabGen[0] != "" )
{
  // Show general area element above all user tabs
  if ( $this->ppnlink == "1" )
  {
    $Out = trim($this->LoadElement($TabGens[1]));
    if ( $Out != "" )
    {
      $Output .= "<p>Zugeh&ouml;rige Publikationen</p>";
      $Output .= "<div class='container-fluid'><div class='row'>";
      $Output .= "<tr><td>" . $Out . "</tr></td>";
      $Output .= "</div></div>";
    }

  }
  else
  {
    $Out = trim($this->LoadElement($TabGens[0]));
    if ( $Out != "" )
    {
      $Output .= ($this->online == "1") ? "<p>Links</p>" : "<p>Exemplare</p>";
      $Output .= "<div class='container-fluid'><div class='row'>";
      $Output .= "<tr><td>" . $Out . "</tr></td>";
      $Output .= "</div></div>";
    }
  }
}

$Output .= "<p></p><div id='fullview_" . $this->dlgid . "_messagebar'></div>";

?>
