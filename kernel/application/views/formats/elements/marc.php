<?php

//$this->CI->printArray2File($this->contents["contents"]);

// Leader
$Output .= "<tr><td>Leader</td><td>" . $this->leader . "</td></tr>";

foreach ($this->contents as $Field => $Record)
{
  $Output .= "<tr><td>" . $Field . "</td><td>";
  $First = true;
  if ( ! is_array($Record ) )
  {
    $Output .= $Record;
  }
  else
  {
    foreach ( $Record as $Subrecord )
    {
      foreach ( $Subrecord as $Subfieldrecord )
      {
        foreach ( $Subfieldrecord as $Key => $Value )
        {
          if ( !is_array($Value) )
          {
            if ( !$First )
            {
              $Output .= " | <b>" . $Key . "</b> " . htmlspecialchars($Value, ENT_QUOTES, "UTF-8");
            }
            else
            {
              $Output .= "<b>" . $Key . "</b> " . htmlspecialchars($Value, ENT_QUOTES, "UTF-8");
              $First = false;
            }
          }
        }
      }
    }
  }
  $Output .= "</td></tr>";
}

?>