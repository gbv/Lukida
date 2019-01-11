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
$Output .= "<tr><td class='tabcell'>driver <i class='fa fa-arrow-right' aria-hidden='true'></i> host</td><td class='tabcell'><font color='red'>" . $_SESSION["info"]["1"]["driver"] . " <i class='fa fa-arrow-right' aria-hidden='true'></i> " . $_SESSION["info"]["1"]["host"] . "/" . $_SESSION["info"]["1"]["isil"] . "</font></td></tr>";

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

// _________________________________________________________________________________________

if ( array_key_exists("daia2", $DAIA) )
{
  $DAIA = $DAIA["daia2"];

  // Part Lukida Driver & Host
  $Output .= "<tr><td class='tabcell'>driver <i class='fa fa-arrow-right' aria-hidden='true'></i> host</td><td class='tabcell'><font color='red'>" . $_SESSION["info"]["2"]["driver"] . " <i class='fa fa-arrow-right' aria-hidden='true'></i> " . $_SESSION["info"]["2"]["host"] . "/" . $_SESSION["info"]["2"]["isil"] . "</font></td></tr>";

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

}


?>
