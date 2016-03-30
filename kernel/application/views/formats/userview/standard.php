<?php

// *****************************************************
// **** Section ONE: Get & Formats required Fields *****
// *****************************************************

// Bereits verfügbare Parameter
// $Action = Name des Tabs, der Aktiv werden soll

// Parameter aus INI-Datei einlesen
$TabGen	= (iSset($_SESSION["config_discover"]["userview"]["userelement"])		&& $_SESSION["config_discover"]["userview"]["userelement"]    != "" )  ? $_SESSION["config_discover"]["userview"]["userelement"] : "";
$Tabs		= (iSset($_SESSION["config_discover"]["userview"]["usertabs"]) 			&& $_SESSION["config_discover"]["userview"]["usertabs"]       != "" )  ? explode(",", $_SESSION["config_discover"]["userview"]["usertabs"]) : array();
$TabsOpt= (iSset($_SESSION["config_discover"]["userview"]["optionaltabs"])	&& $_SESSION["config_discover"]["userview"]["optionaltabs"]   != "" )  ? explode(",", $_SESSION["config_discover"]["userview"]["optionaltabs"]) : array();

// Prepare additional tab header info
$Add  = array();
foreach ( $_SESSION["items"] as $Item )
{
  if ( $Item["status"] == "1" ) (isset($Add["userreservations"])) ? $Add["userreservations"]++ : $Add["userreservations"] = 1;
  if ( $Item["status"] == "2" ) (isset($Add["userorders"]))       ? $Add["userorders"]++       : $Add["userorders"]       = 1;
  if ( $Item["status"] == "3" ) (isset($Add["userrentals"]))      ? $Add["userrentals"]++      : $Add["userrentals"]      = 1;
  if ( $Item["status"] == "4" ) (isset($Add["usercollectables"])) ? $Add["usercollectables"]++ : $Add["usercollectables"] = 1;
}
if ( $_SESSION["fees"]["amount"] != "0.00 EUR" ) $Add["userfees"] = $_SESSION["fees"]["amount"];
if ( isset($_SESSION["searches"]) && count($_SESSION["searches"])>0) $Add["usersearches"] = count($_SESSION["searches"]);

// *****************************************************
// ************ Section TWO: Create Output *************
// *****************************************************

// Start Output
if ( $TabGen != "" )
{
  // Show general area element above all user tabs
  $Output .= "<div class='col-xs-12'><div class='col-md-10'>";
  $Output .= "<table class='table rowheight-reduced table-hover borderless small'><tbody>";
  $Output .= $this->LoadElement($TabGen);
  $Output .= "</tbody></table>";
  $Output .= "</div><div class='col-md-2'>";
  $Output .= "<button onclick='window.open(\"https://opac.lbs-magdeburg.gbv.de/loan/DB=1/LNG=DU/USERINFO_LOGIN\",\"_blank\")' class='btn fullview-button-color'>" . $this->CI->database->code2text("CHANGEPASSWORT") . "</button>";
  $Output .= "</div></div>";
  
}
if ( isset($Add["usercollectables"]) )
{
  $Output .= "<div class='col-xs-12 search'>" . $this->CI->database->code2text("Collectableremark") . "</div>";
  $Add["userreservations"] = (isset($Add["userreservations"])) ?  $Add["userreservations"] + $Add["usercollectables"] : $Add["usercollectables"];
}

// Tabs Header
$Output .= "<ul class='nav nav-tabs' role='tablist'>";
foreach ( $Tabs as $Tab )
{
  $Output .= "<li role='presentation'";
  if ( $Action == $Tab )	$Output .= " class='active'";
  $Output .= "><a href='#" . $Tab . "' aria-controls='general' role='tab' data-toggle='tab'>" . $this->CI->database->code2text($Tab);
  $Output .= (isset($Add[$Tab])) ? " <span class='badge'>" . $Add[$Tab] : "</span>";
  $Output .= "</a></li>";
}
foreach ( $TabsOpt as $Tab )
{
  if ( isset($_SESSION["internal"][$Tab]) && $_SESSION["internal"][$Tab] == "1" )
  {
    $Output .= "<li role='presentation'><a href='#" . $Tab . "' aria-controls='general' role='tab' data-toggle='tab'>" . $this->CI->database->code2text("record" . $Tab) . "</a></li>";
  }
}
$Output .= "</ul>";

// ****** Start Tab ******
$Output .= "<div class='tab-content'>";
foreach ( $Tabs as $Tab )
{
  $Output .= "<div role='tabpanel' class='tab-pane fade";
  if ( $Action == $Tab ) $Output .= " in active";
  $Output .= "' id='" . $Tab . "'>";
  $Output .= "<table class='table rowheight-reduced table-hover borderless small'><tbody>";
  $Output .= $this->LoadTab($Tab);
  $Output .= "</tbody></table>";
  $Output .= "</div>";
}
foreach ( $TabsOpt as $Tab )
{
  $Output .= "<div role='tabpanel' class='tab-pane fade' id='" . $Tab . "'>";
  $Output .= "<table class='table rowheight-reduced table-hover borderless small'><tbody>";
  $Output .= $this->LoadTab($Tab);
  $Output .= "</tbody></table>";
  $Output .= "</div>";
}

// ****** Ende Tab ******
$Output .= "</div>";
$Output .= "<p></p><div id='user_messagebar'></div>";

// End Output

?>
