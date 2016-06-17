<?php

//$this->CI->printArray2File($this->contents["contents"]);

// Leader
$Output .= "<tr><td>Leader</td><td>" . substr($this->leader,0,6) . "<b>"  .  substr($this->leader,6,2) . "</b>" . substr($this->leader,8) . "</td></tr>";

/*
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
*/

// $this->CI->printArray2Screen($this->contents["689"]);

foreach ($this->contents as $Field => $Record)
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
        foreach ( $Subfieldrecord as $Key => $Value )
        {
          if ( !in_array($Key, $SubList) )  $SubList[] = (string) $Key;
        }
      }
    }
//$Output .= "<td>" . implode("",$SubList) . "</td>";

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

            $Output .= ( !$Found ) ? "<td style='line-height:1 !important;'><b>" . $Key . "</b> " : " <b>|</b> ";

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

?>