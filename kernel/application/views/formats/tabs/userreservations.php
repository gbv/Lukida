<?php

foreach ( $_SESSION["items"] as $Item )
{
  // Only show ordered media
  if ( ! isset($Item["status"]) ) continue;
  if ( $Item["status"] != "1" ) continue;

  $Output .= "<tr>";
  $Output .= "<td class='tablemiddle'>" . ( ( isset($Item["starttime"]) ) ? $this->CI->date2german($Item["starttime"]) : "" ) . "</td>";
  $Output .= "<td class='tablemiddle'>" . ( ( isset($Item["about"]) )     ? $Item["about"] : "" ) . "</td>";

  // No Cancel Button 
  //if ( $Item["cancancel"] == "1" )
  //{
  //  $Output .= "<td align='right'><button onClick='$.cancel(\"" . $Item["item"] . "\")' class='btn btn-default btn-exemplar'>" . $this->CI->database->code2text("CANCELRESERVATION") . "</button></td>";
  //}
  $Output .= "</tr>";

}

?>
