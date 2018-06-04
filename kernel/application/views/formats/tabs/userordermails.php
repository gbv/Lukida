<?php

// $this->CI->printArray2Screen( $_SESSION["usermailorders"] );

$Output .= "<tr><td colspan='5'>" . $this->CI->database->code2text("MAILORDER30") . "</td></tr>";
$Output .= "<tr><td colspan='5'>" . $this->CI->database->code2text("ORDERSTATUS") . "</td></tr>";

// Print header
$Output .= "<tr>";
$Output .= "<th width='150px'>" . $this->CI->database->code2text("DATE")   . "</th>";
$Output .= "<th>" . $this->CI->database->code2text("TITLE")  . "</th>";
$Output .= "<th>" . $this->CI->database->code2text("ID")  . "</th>";
$Output .= "<th>" . $this->CI->database->code2text("VOLUME")  . "</th>";
$Output .= "<th>" . $this->CI->database->code2text("DESK")  . "</th>";
$Output .= "</tr>";

// Print data
foreach ( $_SESSION["usermailorders"] as $Item )
{
  $Output .= "<tr id='" . $Item["id"] . "'>";
  $Output .= "<td width='75px'>" . $Item["Zeitpunkt"] . "</td>";
  $Output .= "<td>" . $Item["Bezeichnung"] . ":" . (isset($Item["Titel"])?$Item["Titel"]:"")  . "</td>";
  $Output .= "<td>" . $Item["PPN"] . "</td>";

  $Tmp    = (isset($Item["Daten"]) && $Item["Daten"] != "" ) ? unserialize($Item["Daten"]) : array();
  $MailTo = (isset($Tmp["mailtoname"]) && $Tmp["mailtoname"] != "" ) ? $Tmp["mailtoname"] : "";
  $Volume = (isset($Tmp["volume"]) && $Tmp["volume"] != "" ) ? $Tmp["volume"] : "";
  $Output .= "<td>" . $Volume . "</td>";
  $Output .= "<td>" . $MailTo . "</td>";
  $Output .= "</tr>";
}

?>
