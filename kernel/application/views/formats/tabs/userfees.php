<?php

// Get settings

// Filter and sort items
$Items = array();
foreach ( $_SESSION["fees"]["fee"] as $Item )
{
  if ( isset($Item["date"]) && $Item["date"] != "" ) $Items[] = $Item;
}
usort($Items, function ($a, $b) { return $a['date'] <=> $b['date']; });

// Print header
$Output .= "<tr>";
$Output .= "<th width='75px'>" . $this->CI->database->code2text("DATE")        . "</th>";
$Output .= "<th>" . $this->CI->database->code2text("DESCRIPTION") . "</th>";
$Output .= "<th>" . $this->CI->database->code2text("AMOUNT")      . "</th>";
$Output .= "</tr>";

// Print data
foreach ( $Items as $Item )
{
  $Output .= "<tr>";
  $Output .= "<td width='75px'>" . $this->CI->date2german($Item["date"]) . "</td>";
  $Output .= "<td>";
  if ( isset($Item["feetype"]) ) $Output .= $Item["feetype"];
  if ( isset($Item["about"]) ) $Output .= "<br />" . $Item["about"];
  $Output .= "</td><td  align='right'>";
  if ( isset($Item["amount"]) ) $Output .= $Item["amount"];
  $Output .= "</td></tr>";
}

// Add saldo
if ( isset($_SESSION["fees"]["amount"]) )
{
	$Output .= "<tr>";
	$Output .= "<td></td>";
	$Output .= "<td align='right'>" . $this->CI->database->code2text("FEETOTAL") . "</td>";
	$Output .= "<td width='80px' align='right'>" . $_SESSION["fees"]["amount"] . "</td>";
	$Output .= "</tr>";
}

?>