<?php

foreach ( $_SESSION["items"] as $Item )
{
  // Only show lend media
  if ( $Item["status"] != "3" ) continue;

  $Output .= "<tr>";
  $Output .= "<td class='tablemiddle'>" . $Item["about"] . "</td>";
  $Output .= "<td class='tablemiddle'>" . $this->CI->database->code2text("RENEWALS") . " " . $Item["renewals"] . " / 5</td>";

  $Tmp = strtotime($Item["endtime"]);
  if ( date("Ymd",$Tmp) <= date("Ymd") )
  {
    $Output .= "<td class='tablemiddle'>" . $this->CI->database->code2text("RETURNSINCE") . " " . date("d.m.Y",$Tmp) . "</td>";
  }
  else
  {
    $Output .= "<td class='tablemiddle'>" . $this->CI->database->code2text("RETURNUNTIL") . " " . date("d.m.Y",$Tmp) . "</td>";
  }

  $Output .= "<td align='right'><button onClick='$.renew(\"" . $Item["item"] . "\")' class='btn fullview-button-color'>" . $this->CI->database->code2text("RENEW") . "</button></td>";
  $Output .= "</tr>";
}

?>
