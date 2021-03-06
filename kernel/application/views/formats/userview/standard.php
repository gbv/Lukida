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
$Add  = array("userfees"=> 0);
if ( isset($_SESSION["info"]["1"]["isil"]) && isset($_SESSION[$_SESSION["info"]["1"]["isil"]]["items"]) && is_array($_SESSION[$_SESSION["info"]["1"]["isil"]]["items"]) )
{
  foreach ( $_SESSION[$_SESSION["info"]["1"]["isil"]]["items"] as $Item )
  {
    if ( $Item["status"] == "1" ) (isset($Add["userreservations"])) ? $Add["userreservations"]++ : $Add["userreservations"] = 1;
    if ( $Item["status"] == "2" ) (isset($Add["userorders"]))       ? $Add["userorders"]++       : $Add["userorders"]       = 1;
    if ( $Item["status"] == "3" ) (isset($Add["userrentals"]))      ? $Add["userrentals"]++      : $Add["userrentals"]      = 1;
    if ( $Item["status"] == "4" ) (isset($Add["usercollectables"])) ? $Add["usercollectables"]++ : $Add["usercollectables"] = 1;
  }
}
if ( isset($_SESSION["info"]["1"]["isil"]) && isset($_SESSION[$_SESSION["info"]["1"]["isil"]]["fees"]["amount"]) && is_array($_SESSION[$_SESSION["info"]["1"]["isil"]]["fees"]["amount"]) )
{
  if ( $_SESSION[$_SESSION["info"]["1"]["isil"]]["fees"]["amount"] != "0.00 EUR" ) 
  { 
    $Add["userfees"] += (float) explode(" ",$_SESSION[$_SESSION["info"]["1"]["isil"]]["fees"]["amount"])[0]; 
  }
}
if ( $this->countLBS() == 2 )
{
  if ( isset($_SESSION["info"]["2"]["isil"]) && isset($_SESSION[$_SESSION["info"]["2"]["isil"]]["items"]) && is_array($_SESSION[$_SESSION["info"]["2"]["isil"]]["items"]) )
  {
    foreach ( $_SESSION[$_SESSION["info"]["2"]["isil"]]["items"] as $Item )
    {
      if ( $Item["status"] == "1" ) (isset($Add["userreservations"])) ? $Add["userreservations"]++ : $Add["userreservations"] = 1;
      if ( $Item["status"] == "2" ) (isset($Add["userorders"]))       ? $Add["userorders"]++       : $Add["userorders"]       = 1;
      if ( $Item["status"] == "3" ) (isset($Add["userrentals"]))      ? $Add["userrentals"]++      : $Add["userrentals"]      = 1;
      if ( $Item["status"] == "4" ) (isset($Add["usercollectables"])) ? $Add["usercollectables"]++ : $Add["usercollectables"] = 1;
    }
  }
  if ( isset($_SESSION["info"]["2"]["isil"]) && isset($_SESSION[$_SESSION["info"]["2"]["isil"]]["fees"]["amount"]) && is_array($_SESSION[$_SESSION["info"]["2"]["isil"]]["fees"]["amount"]) )
  {
    if ( $_SESSION[$_SESSION["info"]["2"]["isil"]]["fees"]["amount"] != "0.00 EUR" ) 
    { 
      $Add["userfees"] += (float) explode(" ",$_SESSION[$_SESSION["info"]["2"]["isil"]]["fees"]["amount"])[0]; 
    }
  }
}
$Add["userfees"] = ( $Add["userfees"] == 0 ) ? "" : $this->formatEuro($Add["userfees"]);
if ( isset($_SESSION["searches"]) && count($_SESSION["searches"])>0)             $Add["usersearches"]   = count($_SESSION["searches"]);
if ( isset($_SESSION["usermailorders"]) && count($_SESSION["usermailorders"])>0) $Add["userordermails"] = count($_SESSION["usermailorders"]);

// *****************************************************
// ************ Section TWO: Create Output *************
// *****************************************************

// Start Output
if ( $TabGen != "" )
{
  // Show general area element above all user tabs
  $Output .= $this->LoadElement($TabGen);
}

// Tabs Header
$Output .= "<ul class='nav nav-tabs' role='tablist'>";
foreach ( $Tabs as $Tab )
{
  $Output .= "<li role='presentation'";
  $Output .= ( $Action == $Tab ) ? " class='active small'" : " class='small'";
  $Output .= "><a href='#" . $Tab . "' aria-controls='general' role='tab' data-toggle='tab'>" . $this->CI->database->code2text($Tab);
  $Output .= (isset($Add[$Tab])) ? " <span class='badge'>" . $Add[$Tab] : "</span>";
  $Output .= "</a></li>";
}
foreach ( $TabsOpt as $Tab )
{
  if ( isset($_SESSION["internal"][$Tab]) && $_SESSION["internal"][$Tab] == "1" )
  {
    $Output .= "<li class='small' role='presentation'><a href='#" . $Tab . "' aria-controls='general' role='tab' data-toggle='tab'>" . $this->CI->database->code2text("record" . $Tab) . "</a></li>";
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
  $Output .= "<table width='100%' class='table-responsive rowheight-reduced table-hover borderless small'><tbody>";
  $Output .= $this->LoadTab($Tab);
  $Output .= "</tbody></table>";
  $Output .= "</div>";
}
foreach ( $TabsOpt as $Tab )
{
  $Output .= "<div role='tabpanel' class='tab-pane fade' id='" . $Tab . "'>";
  $Output .= "<table width='100%' class='table-responsive rowheight-reduced table-hover borderless small'><tbody>";
  $Output .= $this->LoadTab($Tab);
  $Output .= "</tbody></table>";
  $Output .= "</div>";
}

// ****** Ende Tab ******
$Output .= "</div>";
$Output .= "<p></p><div id='user_messagebar'></div>";

// End Output

?>
