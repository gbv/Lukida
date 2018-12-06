<?php

// Merge LBS
if ( $this->countLBS() == 2 )
{
  $Total = array_merge($_SESSION[$_SESSION["info"]["1"]["isil"]]["items"],$_SESSION[$_SESSION["info"]["2"]["isil"]]["items"]);
}
else
{
  $Total = $_SESSION[$_SESSION["info"]["1"]["isil"]]["items"];
}
 
// Get settings
$CancelReservation = (isset($_SESSION["config_general"]["lbs"]["cancelreservation"]) && $_SESSION["config_general"]["lbs"]["cancelreservation"] == "1" ) ? true : false;

// Filter and sort items
$Items = array();
foreach ( $Total as $Item )
{
  if ( isset($Item["starttime"]) && $Item["starttime"] != "" && isset($Item["status"]) && $Item["status"] == "1" ) $Items[] = $Item;
}
usort($Items, function ($a, $b) { return $a['starttime'] <=> $b['starttime']; });

// Print header
$Output .= "<tr>";
$Output .= "<th width='75px'>" . $this->CI->database->code2text("DATE")   . "</th>";
if ( $this->countLBS() == 2 ) $Output .= "<th>" . $this->CI->database->code2text("LIBRARY")   . "</th>";
$Output .= "<th>" . $this->CI->database->code2text("TITLE")  . "</th>";
$Output .= "</tr>";

// Print data
$Count = 0;
foreach ( $Items as $Item )
{
  $Count++;
  $Output .= "<tr id='reservation_" . $Count . "'>";
  $Output .= "<td width='75px' class='tablemiddle'>" . ( ( isset($Item["starttime"]) ) ? $this->CI->date2german($Item["starttime"]) : "" ) . "</td>";
  if ( $this->countLBS() == 2 ) $Output .= "<td>" .$this->getLBSName($Item["isil"])   . "</td>";
  $Output .= "<td class='tablemiddle'>" . ( ( isset($Item["about"]) )     ? $Item["about"] : "" ) . "</td>";

  // Cancel Button 
  if ( $CancelReservation )
  {
    if ( $Item["cancancel"] == "1" )
    {
      $Output .= "<td align='right'><button onClick='$.cancel(\"" . $this->getLBSILN($Item["isil"]) . "\",\"" . $Item["item"] . "\"," . $Count . ")' class='btn fullview-button-color'>" . $this->CI->database->code2text("CANCELRESERVATION") . "</button></td>";
    }
  }
  $Output .= "</tr>";
}

?>
