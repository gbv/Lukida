<?php

foreach ( $_SESSION["items"] as $Item )
{
  // Only show ordered media
  if ( $Item["status"] != "2" ) continue;

  $Output .= "<tr>";
  $Output .= "<td class='tablemiddle'>" . $this->CI->date2german($Item["starttime"]) . "</td>";
  $Output .= "<td class='tablemiddle'>" . $Item["about"] . "</td>";
  $Output .= "</tr>";
}

?>
