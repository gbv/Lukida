<?php

// Merge LBS
if ( $this->countLBS() == 2 )
{
  $Total = array_merge($_SESSION[$_SESSION["info"]["1"]["isil"]]["fees"]["fee"],$_SESSION[$_SESSION["info"]["2"]["isil"]]["fees"]["fee"]);
}
else
{
  $Total = $_SESSION[$_SESSION["info"]["1"]["isil"]]["fees"]["fee"];
}

// Filter and sort items
$Items = array();
foreach ( $Total as $Item )
{
  if ( isset($Item["date"]) && $Item["date"] != "" ) $Items[] = $Item;
}
usort($Items, function ($a, $b) { return $a['date'] <=> $b['date']; });

// Print header
$Output .= "<tr>";
$Output .= "<th width='75px'>" . $this->CI->database->code2text("DATE")        . "</th>";
if ( $this->countLBS() == 2 ) $Output .= "<th>" . $this->CI->database->code2text("LIBRARY")   . "</th>";
$Output .= "<th>" . $this->CI->database->code2text("DESCRIPTION") . "</th>";
$Output .= "<th>" . $this->CI->database->code2text("AMOUNT")      . "</th>";
$Output .= "</tr>";

// Print data
foreach ( $Items as $Item )
{
  $Sum = 0;
  $Output .= "<tr>";
  $Output .= "<td width='75px'>" . $this->CI->date2german($Item["date"]) . "</td>";
  if ( $this->countLBS() == 2 ) $Output .= "<td>" .$this->getLBSName($Item["isil"])   . "</td>";
  $Output .= "<td>";
  if ( isset($Item["feetype"]) ) $Output .= $Item["feetype"];
  if ( isset($Item["about"]) ) $Output .= "<br />" . $Item["about"];
  $Output .= "</td><td align='right'>";
  if ( isset($Item["amount"]) )
  {
    $Wert    = (float) explode(" ",$Item["amount"])[0];
    $Output .= $this->formatEuro($Wert);
    $Sum    += $Wert;
  } 
  $Output .= "</td></tr>";
}

// Add saldo
if ( isset($_SESSION["info"]["1"]["isil"]) && isset($_SESSION[$_SESSION["info"]["1"]["isil"]]["fees"]["amount"]) )
{
	$Output .= "<tr>";
  $Output .= "<td></td>";
  if ( $this->countLBS() == 2 ) $Output .= "<td></td>";
	$Output .= "<td align='right'>" . $this->CI->database->code2text("FEETOTAL") . "</td>";
	$Output .= "<td width='80px' align='right'>";
  $Wert    = (float) explode(" ",$_SESSION[$_SESSION["info"]["1"]["isil"]]["fees"]["amount"])[0];
  $Output .= $this->formatEuro($Wert);
	$Output .= "</td></tr>";
}

?>