<?php

if ( ! isset($_SESSION["login"]) ) return;

$Output .= "<div class='col-xs-12'><div class='col-md-10'>";
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
$Output .= "</div><div class='col-md-2'>";

if ( isset($_SESSION["config_general"]["lbs"]["changepassword"]) && $_SESSION["config_general"]["lbs"]["changepassword"] == "1" )
{
	$Output .= "<button onclick='$.open_password()' class='btn fullview-button-color'>" . $this->CI->database->code2text("CHANGEPASSWORD") . "</button>";
}

$Output .= "</div></div>";

if ( isset($_SESSION["userstatus"]["message"]) && $_SESSION["userstatus"]["message"] == true )
{
	$Output .= "<div class='col-xs-12'><div class='alert alert-danger'>";
	$Output .= $_SESSION["userstatus"]["messagetext"];
	$Output .= "</div></div>";
}
?>