<?php

function printMARC($Prefix, $Leader, $Contents)
{
  // Leader
  $Output = "<tr><td>Lead</td><td>"   .  formatLeader($Leader)        . "</td></tr>";
  foreach ($Contents as $Field => $Record)
  {
    if ( substr($Field, -1) == "}" && ( !isset($_SESSION["internal"]["marcfull"]) || $_SESSION["internal"]["marcfull"] == "0" ) ) continue;
  
    $Output .= "<tr>";
    $Output .= ( substr($Field, -1) == "}" ) ? "<td style='color:red'>" : "<td>"; 
    $Output .= $Prefix . $Field . "</td>";
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
      $Indys   = array();
      $Count   = -1;
      foreach ( $Record as $Subrecord )
      {
        $Count++;
        foreach ( $Subrecord as $Subfieldrecord )
        {
          foreach ( $Subfieldrecord as $Key => $Value )
          {
            if ( $Key == "I1" || $Key == "I2" )
            {
              $Indys[$Count][$Key] = $Value;
              continue;
            }
            if ( !in_array($Key, $SubList))  $SubList[] = (string) $Key;
          }
        }
      }
  
      // Output
      $Output .= "<td valign='top'><table class='table' style='background-color: inherit !important;  white-space: nowrap; width: 1%'>";
      $Count = -1;
      foreach ( $Record as $Subrecord )
      {
        $Count++;
        $Output .= "<tr>";
        $Cols = count(array_values(array_values($Subrecord)));
  
        $Output .= "<td style='line-height:1;min-width:12px;'>" . ((isset($Indys[$Count]["I1"])) ? $Indys[$Count]["I1"] : "&nbsp;") . "</td>";
        $Output .= "<td style='line-height:1;min-width:12px;'>" . ((isset($Indys[$Count]["I2"])) ? $Indys[$Count]["I2"] : "&nbsp;") . "</td>";
  
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
  return $Output;
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

$Output  = "<tr><td>ID</td><td>"     . $this->PPN                          . "</td></tr>";
$Output .= "<tr><td>Format</td><td>" . $this->format                       . "</td></tr>";
$Output .= "<tr><td>Online</td><td>" . (($this->online==1)?"true":"false") . "</td></tr>";
$Output .= printMARC("M", $this->leader, $this->contents);

$ParentPPN = ( isset($this->medium["parents"][0]) ) ? $this->medium["parents"][0] : "";
if ( $ParentPPN != "" && $this->CI->EnsurePPN($ParentPPN) ) 
{
  $ParentLeader   = ( isset($_SESSION["data"]["results"][$ParentPPN]["leader"]) ) ? $_SESSION["data"]["results"][$ParentPPN]["leader"] : "";
  $ParentContents = ( isset($_SESSION["data"]["results"][$ParentPPN]["contents"]) ) ? $_SESSION["data"]["results"][$ParentPPN]["contents"] : "";

  if ( $ParentLeader != "" && $ParentContents != "" )
  {
    $Output .= "<tr><td class='tabcell' style='color:red'>Parent PPN</td><td class='tabcell' style='color:red'>" . $ParentPPN . "</td></tr>";
    $Output .= printMARC("PM", $ParentLeader, $ParentContents);
  }

}

?>