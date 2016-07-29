<?php

if ( ! isset($_SESSION["interfaces"]["lbs"]) || ! $_SESSION["interfaces"]["lbs"] == "1" )
{
  return;
}


// Local storage (ILN)
$DAIA = $this->CI->GetLBS($this->PPN);   
if ( isset($DAIA["message"][0]["errno"]) )
{
  // Fehler passiert zunächst nur ausgeben
  if ( isset($this->medium["parents"][0]) )
  {
    $DAIA = $this->CI->GetLBS($this->medium["parents"][0]);
  }
}

// $this->CI->printArray2Screen($DAIA["document"]);


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



// Output
// 
/*// 
  if ( count($DAIA) > 0 )
  {

    foreach ( $DAIA as $Field => $Value )
    {
      $Output .= "<tr><td>" . $Field . "</td><td>";
      if ( ! is_array($Value) )
      {
        $Output .= $Value;
      }
      else
      {
        $Output .= $this->printarray(0,$Value);
      }
      $Output .= "</td></tr>";
    }
  }
}


foreach ($DAIA as $Field => $Record)
{
  $Output .= "<tr><td>" . $Field . "</td>";
  $First = true;
  if ( ! is_array($Record ) )
  {
    $Output .= "<td>" . $Record . "</td>";
  }
  else
  {
    // Counts
    $SubList = array();
    foreach ( $Record as $Subrecord )
    {
      foreach ( $Subrecord as $Subfieldrecord )
      {
        if ( is_array($Subfieldrecord))
        {
          foreach ( $Subfieldrecord as $Key => $Value )
          {
            if ( !in_array($Key, $SubList) )  $SubList[] = (string) $Key;
          }
        }
      }
    }

    // Output
    $Output .= "<td><table class='table' style='background-color: inherit !important;  white-space: nowrap; width: 1%'>";
    foreach ( $Record as $Subrecord )
    {
      $Output .= "<tr>";
      $Cols = count(array_values(array_values($Subrecord)));

      foreach ($SubList as $SortKey)
      {
        $Found = false;
        foreach ( $Subrecord as $Subfieldrecord )
        {
          foreach ( $Subfieldrecord as $Key => $Value )
          {
            if ( (string) $Key != (string) $SortKey ) continue;

            $Output .= ( !$Found ) ? "<td class='tabcell'><b>" . $Key . "</b> " : " <b>|</b> ";

            $Value = htmlspecialchars($Value, ENT_QUOTES, "UTF-8") ;
            if ( strlen($Value) > ( 130 / $Cols ) )
            {
              $Output .= "<a data-toggle='tooltip' title='" . $Value . "'>" . substr($Value,0,floor(130 / $Cols)) . "...</a>";
            }
            else
            {
              $Output .= $Value;
            }

            $Found = true;
          }
        }
        $Output .= ( !$Found ) ? "<td></td>" : "</td>";
      }
      $Output .= "</tr>";
    }
    $Output .= "</table></td>";
  }
  $Output .= "</tr>";
}
*/
?>
