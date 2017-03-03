<?php

foreach ( $_SESSION["items"] as $Item )
{
  // Only show ordered media
  if ( ! isset($Item["status"]) ) continue;
  if ( $Item["status"] != "4" ) continue;

  $Output .= "<tr>";
  $Output .= "<td class='tablemiddle'>" . ( ( isset($Item["starttime"]) ) ? $this->CI->date2german($Item["starttime"]) : "" ) . "</td>";
  $Output .= "<td class='tablemiddle'>" . ( ( isset($Item["about"]) )     ? $Item["about"] : "" ) . "</td>";
  $Output .= "<td class='tablemiddle'>" . ( ( $Item["status"] == "4" )    ? "<span class='markfoundtext'>" . $this->CI->database->code2text("Collectable") . "</span>" : "") . "</td>";

  $Output .= "</tr>";

}

?>
