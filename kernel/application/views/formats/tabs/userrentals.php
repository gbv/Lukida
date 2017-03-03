<?php

$maxrenewals = (isset($_SESSION["config_general"]["lbs"]["maxrenewals"]) && $_SESSION["config_general"]["lbs"]["maxrenewals"] != "" ) ? (integer) $_SESSION["config_general"]["lbs"]["maxrenewals"] : 0;

$Output .= "<tr>";
$Output .= "<td class='tablemiddle' align='center'><button class='btn btn-tiny navbar-panel-color btn-check-all' onClick='javascript:$.mark_check(\"renew\");'>" . $this->CI->database->code2text("ALL") . "</button></td>";
$Output .= "<td>" . $this->CI->database->code2text("TITLE")    . "</td>";
$Output .= "<td align='center'>" . $this->CI->database->code2text("RETURN")   . "</td>";
$Output .= "<td align='center'>" . $this->CI->database->code2text("RENEWALS") . "</td>";
$Output .= "</tr>";

foreach ( $_SESSION["items"] as $Item )
{
  // Only show lend media
  if ( $Item["status"] != "3" ) continue;

  $Tmp   = strtotime($Item["endtime"]);
  $Renew = ( date("Ymd",$Tmp) > date("Ymd") ) ? true : false;
  $Queue = ( $Item["queue"] >= 1 ) ? true : false;
  $Bar   = substr($Item["item"], strrpos($Item["item"], '$') + 1);

  $Output .= "<tr>";
  $Output .= "<td class='tablemiddle' align='center'>";
  $Output .= ( $Renew && ! $Queue && $Item["renewals"] < $maxrenewals ) ?  "<input type='checkbox' class='check_renew' data-item='" . $Item["item"] . "' value=''>" : "";
  $Output .= "</td>";

  $Output .= "<td class='tablemiddle'>" . $Item["about"] . "</td>";
  
  $Output .= "<td class='tablemiddle' align='center' id='return_renew_" . $Bar . "'>";
  $Output .= ( $Renew ) ? date("d.m.Y",$Tmp) : "<b><font color='red'>" . date("d.m.Y",$Tmp) . "</font></b>";
  $Output .= "</td>";

  $Output .= "<td class='tablemiddle' align='center'><span id='renewals_renew_" . $Bar . "'>";
  $Output .= ( $Queue ) ? $this->CI->database->code2text("RESERVED") : $Item["renewals"] . " / " . $maxrenewals;
  $Output .= "</span> <span class='status_renew' id='status_renew_" . $Bar . "'> </span></td>";

  $Output .= "</tr>";
}

$Output .= "<tr><td></td><td></td><td></td><td class='tablemiddle' align='center'><button onClick='$.renew()' class='btn fullview-button-color btn-renew'>" . $this->CI->database->code2text("RENEW") . "</button></td><td></td></tr>";

?>
