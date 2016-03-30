<?php

foreach ( $_SESSION["items"] as $Item )
{
  // Only show ordered media
  if ( $Item["status"] != "1" && $Item["status"] != "4" ) continue;

  $Output .= "<tr>";
  $Output .= "<td class='tablemiddle'>" . $this->CI->date2german($Item["starttime"])  . "</td>";
  $Output .= "<td class='tablemiddle'>" . $Item["about"] . "</td>";
  $Output .= "<td class='tablemiddle'>" . ( ( $Item["status"] == "4" ) ? "<span class='search'>" . $this->CI->database->code2text("Collectable") . "</span>" : "") . "</td>";

  // No Cancel Button 
  //if ( $Item["cancancel"] == "1" )
  //{
  //  $Output .= "<td align='right'><button onClick='$.cancel(\"" . $Item["item"] . "\")' class='btn btn-default btn-exemplar'>" . $this->CI->database->code2text("CANCELRESERVATION") . "</button></td>";
  //}
  $Output .= "</tr>";

}

?>
