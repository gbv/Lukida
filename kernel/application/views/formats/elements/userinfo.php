<?php

if ( ! isset($_SESSION["login"]) ) return;
foreach ( $_SESSION["login"] as $key => $value )
{
  // Avoid empty entries
  if ( $value == "" )	continue;

  // Format Date
  if ( $key == "expires" )  $value =  $this->CI->date2german($value);
  $Output .="<tr><td>" . $this->CI->database->code2text($key) . "</td><td>" . $value . "</td></tr>";
}

?>