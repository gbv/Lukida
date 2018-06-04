<?php

if ( ! isset($_SESSION["interfaces"]["lbs"]) || ! $_SESSION["interfaces"]["lbs"] == "1" )
{
  return;
}

// Local storage (ILN)
$DAIA = $this->CI->GetLBS($this->PPN);   
if ( isset($DAIA["document"]) && empty($DAIA["document"]) )
{
  // Fehler passiert zunächst nur ausgeben
  if ( isset($this->medium["parents"][0]) )
  {
    $DAIA = $this->CI->GetLBS($this->medium["parents"][0]);
    $Output   .= "<tr><td class='tabcell' style='color:red'>Parent PPN</td><td class='tabcell' style='color:red'>" . $this->medium["parents"][0] . "</td></tr>";
  }
}

if (!is_array($DAIA)) return;

// $this->CI->printArray2Screen($DAIA["document"]);
// $this->CI->printArray2Screen($DAIA);

// Part Lukida Driver & Host
$Driver = (isset($_SESSION["config_general"]["lbs"]["type"]) && $_SESSION["config_general"]["lbs"]["type"] != "" ) ? $_SESSION["config_general"]["lbs"]["type"] : "";
if ( strtolower(MODE) == "production" )
{
  $Host = (isset($_SESSION["config_general"]["lbsprod"]["daia"]) && $_SESSION["config_general"]["lbsprod"]["daia"] != "" ) ? $_SESSION["config_general"]["lbsprod"]["daia"] : "";
}
else
{
  $Host = (isset($_SESSION["config_general"]["lbsdevtest"]["daia"]) && $_SESSION["config_general"]["lbsdevtest"]["daia"] != "" ) ? $_SESSION["config_general"]["lbsdevtest"]["daia"] : "";
}
$Output .= "<tr><td class='tabcell'>driver <i class='fa fa-arrow-right' aria-hidden='true'></i> host</td><td class='tabcell'><font color='red'>" . $Driver . " <i class='fa fa-arrow-right' aria-hidden='true'></i> " . $Host . "</font></td></tr>";

// Part Institution
if ( array_key_exists("institution", $DAIA) )
{
  $Output .= "<tr><td class='tabcell'>institution</td><td class='tabcell'><table>";
  foreach ( $DAIA["institution"] as $Field => $Value )
  {
    $Output .= "<tr><td class='tabcell'>" . $Field. "</td><td class='tabcell'>" . $Value . "</td></tr>";
  }
  $Output .= "</table></td></tr>";
}

// Part Timestamp
if ( array_key_exists("timestamp", $DAIA) )
{
  $Output .= "<tr><td class='tabcell'>timestamp</td><td class='tabcell'>" . $DAIA["timestamp"] . "</td></tr>";
}

// Part Version
if ( array_key_exists("version", $DAIA) )
{
  $Output .= "<tr><td class='tabcell'>version</td><td class='tabcell'>" . $DAIA["version"] . "</td></tr>";
}

// Part Document
if ( array_key_exists("document", $DAIA) )
{
  $Output .= "<tr><td class='tabcell'>document</td><td class='tabcell'><table>";
  $Output .= $this->printtable(0,$DAIA["document"]);
  $Output .= "</table></td></tr>";
}

?>
