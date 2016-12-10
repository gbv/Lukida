<?php

//$this->CI->printArray2File($this->contents["contents"]);

// Leader
$Output .= "<tr><td>Leader</td><td>" .  formatLeader($this->leader) . "</td></tr>";

foreach ($this->contents as $Field => $Record)
{
  $Output .= "<tr><td>" . $Field . "</td>";
  $First = true;
  if ( ! is_array($Record ) )
  {
    if ( $Field == "007" ) $Record = format007($Record);
    if ( $Field == "008" ) $Record = format008($Record);
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

    // Output
    $Output .= "<td valign='top'><table class='table' style='background-color: inherit !important;  white-space: nowrap; width: 1%'>";
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

function formatLeader($Str)
{
  $Tmp = $Str;
  $Tmp = substr_replace($Tmp, "<b>" . substr($Tmp,6,2) . "</b>", 6, 2) ;
  $Tmp = substr_replace($Tmp, "<b>" . substr($Tmp,26,1) . "</b>", 26, 1) ;
  return $Tmp;
}

function format007($Str)
{
  $Tmp = $Str;
  $Tmp = substr_replace($Tmp, "<b>" . substr($Tmp,0,2) . "</b>", 0, 2) ;
  return $Tmp;
}

function format008($Str)
{
  $Tmp = $Str;
  $Tmp = substr_replace($Tmp, "<b>" . substr($Tmp,21,1) . "</b>", 21, 1) ;
  return $Tmp;
}

?>