<?php

if ( ! isset($_SESSION["login"]) ) return;

$Output .= "<div class='col-xs-12'>";
$Output .= "<table class='table rowheight-reduced table-hover borderless small'><tbody>";

foreach ( $_SESSION["login"] as $key => $value )
{
  // Avoid empty entries
  if ( $value == "" )	continue;

  // Format Date
  if ( $key == "expires" )  $value =  $this->CI->date2german($value);
  $Output .="<tr><td>" . $this->CI->database->code2text($key) . "</td><td>" . $value . "</td></tr>";
}

$Output .= "</tbody></table>";
$Output .= "</div>";

?>