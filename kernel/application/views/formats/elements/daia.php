<?php

if ( isset($_SESSION["interfaces"]["lbs"]) && $_SESSION["interfaces"]["lbs"] == "1" )
{
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

?>
