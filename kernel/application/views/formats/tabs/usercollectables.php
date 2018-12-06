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

// Filter and sort items
$Items = array();
foreach ( $Total as $Item )
{
  if ( isset($Item["starttime"]) && $Item["starttime"] != "" && isset($Item["status"]) && $Item["status"] == "4" ) $Items[] = $Item;
}
usort($Items, function ($a, $b) { return $a['starttime'] <=> $b['starttime']; });

// Print header
$Output .= "<tr>";
$Output .= "<th width='75px'>" . $this->CI->database->code2text("DATE")   . "</th>";
if ( $this->countLBS() == 2 ) $Output .= "<th>" . $this->CI->database->code2text("LIBRARY")   . "</th>";
$Output .= "<th>" . $this->CI->database->code2text("TITLE")  . "</th>";
$Output .= "<th>" . $this->CI->database->code2text("STATUS") . "</th>";
$Output .= "</tr>";

// Print data
foreach ( $Items as $Item )
{
  $Output .= "<tr>";
  $Output .= "<td width='75px'>" . $this->CI->date2german($Item["starttime"]) . "</td>";
  if ( $this->countLBS() == 2 ) $Output .= "<td>" .$this->getLBSName($Item["isil"])   . "</td>";
  $Output .= "<td>" . $Item["about"] . "</td>";
  $Output .= "<td>" . "<span class='markfoundtext'>" . $this->CI->database->code2text("Collectable") . "</span>" . "</td>";
  $Output .= "</tr>";
}

?>
