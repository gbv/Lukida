<?php

$Combined = $this->CI->GetCombinedItems($this->PPN);   
if ( !count($Combined) )
{
  $ParentPPN = ( isset($this->medium["parents"][0]) ) ? $this->medium["parents"][0]                   : "";
  $Combined  = $this->CI->GetCombinedItems($ParentPPN);   
  if ( !count($Combined) )  return;
  $Output   .= "<tr><td class='tabcell' style='color:red'>Parent PPN</td><td class='tabcell' style='color:red'>" . $ParentPPN . "</td></tr>";
}
// $this->CI->printArray2Screen($Combined);

$LeftSide = array();
$Count = 0;
foreach ($Combined as $LID => $Item)
{
  $Count++;

  $LeftSide += array_keys($Item);

  if ( $Count == 6 ) break;
}
sort($LeftSide);

$LIDs = array_keys($Combined);

// First Line
$Output .= "<tr><td class='tabcell'>LukidaID</td>";
foreach ( $LIDs as $LID )
{
  $Output .= "<td class='tabcell'>" . $LID . "</td>";
}
$Output .= "</tr>";

// Following lines
foreach ($LeftSide as $Left)
{
  if ( !in_array($Left,array("loanitems","openaccessitems","presentationitems","remoteitems")) )
  {
    // First column
    if ( !in_array($Left,array("loan","openaccess","presentation","remote")) )
    {
      $Output .= "<tr><td class='tabcell'>" . $Left . "</td>";
    }
    else
    {
      $Output .= "<tr><td class='tabcell'><a data-toggle='collapse' href='#" . $Left . "items_" . $this->dlgid 
              . "' aria-expanded='false' aria-controls='" . $Left . "items'>" . $Left . "</a></td>";
    }

    // Next columns
    foreach ( $Combined as $LID => $Item2 )
    {
      if (isset($Item2[$Left]) && is_bool($Item2[$Left]))  { $Item2[$Left] = ( $Item2[$Left] ) ? "true" : "false";  }
      $Output .= "<td class='tabcell'>" . ( (isset($Item2[$Left]) && !is_array($Item2[$Left]) ) 
                                          ? $this->CI->CutTextHTML($Item2[$Left],30,true)
                                          : "&nbsp;") . "</td>";
    }
    $Output .= "</tr>";
  }
  else
  {
    // First column
    $Output .= "<tr id='" . $Left . "_" . $this->dlgid . "' class='collapse'><td class='tabcell'>&nbsp;</td>";

    // Next columns
    foreach ( $Combined as $LID => $Item2 )
    {
      // $this->CI->printArray2Screen($Item2);

      if ( isset( $Item2[$Left]) && is_array($Item2[$Left]) )
      {
        $Output .= "<td class='tabcell'><table>";
        foreach ( $Item2[$Left] as $K1 => $V1 )
        {
          $Output .= "<tr><td class='tabcell'>" . html_entity_decode($K1,ENT_QUOTES) . "Ö</td><td class='tabcell'><table>";
          foreach ( $V1 as $K2 => $V2 )
          {
            $Output .= "<tr><td class='tabcell'>" . html_entity_decode($K2,ENT_QUOTES) . "</td><td class='tabcell'>";
            $Tmp = ( is_array($V2) ) ? implode(" | ", $V2) : $V2;
            $Output .= html_entity_decode($this->CI->CutText($Tmp,60,true),ENT_QUOTES);
            $Output .= "</td></tr>";
          }
          $Output .= "</table></td></tr>";
        }
        $Output .= "</table></td>";
      }
      else
      {
        $Output .= "<td class='tabcell'>-</td>";
      }
    }
    $Output .= "</tr>";
  }
}

?>
