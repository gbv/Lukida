<?php

foreach ( $_SESSION["fees"]["fee"] as $Item )
{
  $Output .= "<tr>";
  $Output .= "<td class='tablemiddle'>" . $this->CI->date2german($Item["date"]) . "</td>";
  $Output .= "<td class='tablemiddle'>" . $Item["feetype"] . "<br />" . $Item["about"] . "</td>";
  $Output .= "<td class='tablemiddle' align='right'>" . $Item["amount"] . "</td>";
  $Output .= "</tr>";
  
}

// Saldo anzeigen
$Output .= "<tr>";
$Output .= "<td></td>";
$Output .= "<td align='right'>" . $this->CI->database->code2text("FEETOTAL") . "</td>";
$Output .= "<td align='right'>" . $_SESSION["fees"]["amount"] . "</td>";
$Output .= "</tr>";

?>