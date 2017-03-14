<?php

$MaxRenewals = (isset($_SESSION["config_general"]["lbs"]["maxrenewals"]) && $_SESSION["config_general"]["lbs"]["maxrenewals"] != "" ) ? (integer) $_SESSION["config_general"]["lbs"]["maxrenewals"] : 0;
$AllowAfterTime = (isset($_SESSION["config_general"]["lbs"]["allowrenewalaftertime"]) && $_SESSION["config_general"]["lbs"]["allowrenewalaftertime"] == "1" ) ? true : false;

$Output .= "<tr>";
$Output .= "<td class='tablemiddle' align='center'><button class='btn btn-tiny navbar-panel-color btn-check-all' onClick='javascript:$.mark_check(\"renew\");'>" . $this->CI->database->code2text("ALL") . "</button></td>";
$Output .= "<td>" . $this->CI->database->code2text("TITLE")    . "</td>";
$Output .= "<td align='center'>" . $this->CI->database->code2text("RETURN")   . "</td>";
$Output .= "<td align='center'>" . $this->CI->database->code2text("REMINDERS") . "</td>";
$Output .= "<td align='center'>" . $this->CI->database->code2text("RENEWALS") . "</td>";
$Output .= "</tr>";

foreach ( $_SESSION["items"] as $Item )
{
  // Only show lend media
  if ( $Item["status"] != "3" ) continue;

  // Preparation
  $Tmp    = strtotime($Item["endtime"]);
  $InTime = ( date("Ymd",$Tmp) > date("Ymd") ) ? true : false;
  $Queue  = ( $Item["queue"] >= 1 ) ? true : false;
  $Bar    = substr($Item["item"], strrpos($Item["item"], '$') + 1);

  $Output .= "<tr>";

  // Checkbox
  $Output .= "<td class='tablemiddle' align='center' id='checkbox_renew_" . $Bar . "'>";
  $Output .= ( ( ( !$InTime && $AllowAfterTime) or $InTime ) && ! $Queue && ( ( $Item["renewals"] < $MaxRenewals && $MaxRenewals > 0) || $MaxRenewals == 0 ) ) ?  "<input type='checkbox' class='check_renew' data-item='" . $Item["item"] . "' value=''>" : "";
  $Output .= "</td>";

  // Titel
  $Output .= "<td class='tablemiddle'>" . $Item["about"] . "</td>";
  
  // Return
  $Output .= "<td class='tablemiddle' align='center' id='return_renew_" . $Bar . "'>";
  $Output .= ( $InTime ) ? date("d.m.Y",$Tmp) : "<b><font color='red'>" . date("d.m.Y",$Tmp) . "</font></b>";
  $Output .= "</td>";

  // Reminder
  $Output .= "<td class='tablemiddle' align='center'>" . $Item["reminder"] . "</td>";

  // Renewals
  $Output .= "<td class='tablemiddle' align='center'><span id='renewals_renew_" . $Bar . "'>";
  if ( $Queue )
  {
    $Output .= $this->CI->database->code2text("RESERVED");
  }
  else
  {
    $Output .= ($MaxRenewals > 0) ? $Item["renewals"] . " / " . $MaxRenewals : $Item["renewals"];
  }
  
  $Output .= "</span> <span class='status_renew' id='status_renew_" . $Bar . "'> </span></td>";

  $Output .= "</tr>";
}

$Output .= "<tr><td></td><td></td><td></td><td class='tablemiddle' align='center'><button onClick='$.renew()' class='btn fullview-button-color btn-renew'>" . $this->CI->database->code2text("RENEW") . "</button></td><td></td></tr>";

?>
