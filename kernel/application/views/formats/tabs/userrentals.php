<?php

// Get settings
$MaxRenewals = (isset($_SESSION["config_general"]["lbs"]["maxrenewals"]) && $_SESSION["config_general"]["lbs"]["maxrenewals"] != "" ) ? (integer) $_SESSION["config_general"]["lbs"]["maxrenewals"] : 0;
$AllowAfterTime = (isset($_SESSION["config_general"]["lbs"]["allowrenewalaftertime"]) && $_SESSION["config_general"]["lbs"]["allowrenewalaftertime"] == "1" ) ? true : false;

// Filter and sort items
$Items = array();
foreach ( $_SESSION["items"] as $Item )
{
  if ( $Item["status"] == "3" && isset($Item["endtime"]) && $Item["endtime"] != "" ) $Items[] = $Item;
}
usort($Items, function ($a, $b) { return $a['endtime'] <=> $b['endtime']; });

// Print header
$Output .= "<tr>";
$Output .= "<td align='center'><button class='btn btn-tiny navbar-panel-color btn-check-all' onClick='javascript:$.mark_check(\"renew\");'>" . $this->CI->database->code2text("ALL") . "</button></td>";
$Output .= "<th>" . $this->CI->database->code2text("TITLE")     . "</th>";
$Output .= "<th>" . $this->CI->database->code2text("RETURN")    . "</th>";
$Output .= "<th>" . $this->CI->database->code2text("REMINDERS") . "</th>";
$Output .= "<th>" . $this->CI->database->code2text("RENEWALS")  . "</th>";
$Output .= "</tr>";

// Print data
$Count  = 0;
foreach ( $Items as $Item )
{
  // Preparation
  $Tmp    = strtotime($Item["endtime"]);
  $InTime = ( date("Ymd",$Tmp) >= date("Ymd") ) ? true : false;
  $Queue  = ( $Item["queue"] >= 1 ) ? true : false;
  $Bar    = substr($Item["item"], strrpos($Item["item"], '$') + 1);

  $Output .= "<tr>";

  // Checkbox
  $Output .= "<td align='center' id='checkbox_renew_" . $Bar . "'>";
  if ( ( ( !$InTime && $AllowAfterTime) or $InTime ) && ! $Queue && ( ( $Item["renewals"] < $MaxRenewals && $MaxRenewals > 0) || $MaxRenewals == 0 ) )
  {
    $Count++;
    $Output .=  "<input type='checkbox' class='check_renew' data-item='" . $Item["item"] . "' value=''>";
  }
  $Output .= "</td>";

  // Titel
  $Output .= "<td>" . $Item["about"] . "</td>";
  
  // Return
  $Output .= "<td align='center' id='return_renew_" . $Bar . "'>";
  $Output .= ( $InTime ) ? date("d.m.Y",$Tmp) : "<b><font color='red'>" . date("d.m.Y",$Tmp) . "</font></b>";
  $Output .= "</td>";

  // Reminder
  $Output .= "<td align='center'><span id='reminder_renew_" . $Bar . "'>" . $Item["reminder"] . "</span></td>";

  // Renewals
  $Output .= "<td align='center'><span id='renewals_renew_" . $Bar . "'>";
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

if ( $Count > 0 ) $Output .= "<tr><td colspan='4'><button onClick='$.renew()' class='btn fullview-button-color btn-renew'>" . $this->CI->database->code2text("RENEW") . "</button><td></tr>";

?>
