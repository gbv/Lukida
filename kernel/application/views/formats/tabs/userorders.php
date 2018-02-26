<?php

// Get settings

// Filter and sort items
$Items = array();
foreach ( $_SESSION["items"] as $Item )
{
  if ( isset($Item["starttime"]) && $Item["starttime"] != "" && isset($Item["status"]) && $Item["status"] == "2" ) $Items[] = $Item;
}
usort($Items, function ($a, $b) { return $a['starttime'] <=> $b['starttime']; });

// Print header
$Output .= "<tr>";
$Output .= "<th width='75px'>" . $this->CI->database->code2text("DATE")   . "</th>";
$Output .= "<th>" . $this->CI->database->code2text("TITLE")  . "</th>";
$Output .= "</tr>";

// Print data
foreach ( $Items as $Item )
{
  $Output .= "<tr>";
  $Output .= "<td width='75px'>" . $this->CI->date2german($Item["starttime"]) . "</td>";
  $Output .= "<td>" . $Item["about"] . "</td>";
  $Output .= "</tr>";
}

?>
