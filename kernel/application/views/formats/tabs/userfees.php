<?php

foreach ( $_SESSION["fees"]["fee"] as $Item )
{
  $Output .= "<tr>";
  $Output .= "<td class='tablemiddle'>" . $this->CI->date2german($Item["date"]) . "</td>";
  $Output .= "<td class='tablemiddle'>";
  if ( isset($Item["feetype"]) ) $Output .= $Item["feetype"];
  if ( isset($Item["about"]) ) $Output .= "<br />" . $Item["about"];
  $Output .= "</td><td class='tablemiddle' align='right'>";
  if ( isset($Item["amount"]) ) $Output .= $Item["amount"];
  $Output .= "</td></tr>";
}

// Saldo anzeigen
if ( isset($_SESSION["fees"]["amount"]) )
{
	$Output .= "<tr>";
	$Output .= "<td></td>";
	$Output .= "<td align='right'>" . $this->CI->database->code2text("FEETOTAL") . "</td>";
	$Output .= "<td align='right'>" . $_SESSION["fees"]["amount"] . "</td>";
	$Output .= "</tr>";
}

?>