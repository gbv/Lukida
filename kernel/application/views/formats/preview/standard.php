<?php

// *****************************************************
// **** Section ONE: Get & Formats required Fields *****
// *****************************************************

// Read all important & available MARC-Data into associative array

// *****************************************************
// ************ Section TWO: Create Output *************
// *****************************************************

// Start Output

// Header
if ( $Header )
{
  $Output .= "<div class='panel-heading preview-header'>";
  $Output .= "<div class='table-layout'><div class='table-cell preview-first-col'>";
  $Output .= "<span data-toggle='tooltip' title='" . $this->CI->database->code2text($this->format) . "' class='preview-icon'>" . $this->SetCover() . "</span>";
  $Output .= "<br />";
  $Output .= "<span class='preview-counter'>" . $this->NR . "</span>";
  $Output .= "</div><div class='table-cell preview-second-col'>";
  
  // Show title item
  if ( $this->pretty["title"] != "" )
  {
    $Output .= $this->Mark_Text($this->pretty["title"]);
    if ( $this->pretty["part"] != "" ) $Output .= " " . $this->Mark_Text($this->pretty["part"]);
  }
  else
  {
    if ( count($this->pretty["serial"]) > 0 )
    {
      $First = true;
      foreach ( $this->pretty["serial"] as $one)
      {
        if ( !$First ) $Output .= " | ";
        if ( isset($one["a"]) && $one["a"] != "" )
        {
          $AddText = ( isset($one["v"]) && $one["v"] != "" ) ? " " . $one["v"] : "";
          $Output .= $one["a"] . $AddText;
          $First = false;
        }
      }
    }
  }

  // Show second found item
  $Output .= "<small>";

  if (isset($this->pretty["titlesecond"]) && $this->pretty["titlesecond"] != "" ) $Output .= "<br />" . $this->Mark_Text($this->pretty["titlesecond"]);
  if (isset($this->pretty["author"])      && count($this->pretty["author"]) > 0)  $Output .= "<br />" . implode(", ", $this->Mark_Text(array_column($this->pretty["author"], "name")));

  if ( in_array($this->format, array("article")) )
  {
    // Artikel
    $MaxPublisherArticleLen = (isset($_SESSION["config_discover"]["preview"]["maxpublisherarticlelength"]     ) ) ? $_SESSION["config_discover"]["preview"]["maxpublisherarticlelength"] : 100;
    $Tmp = $this->Trim_Text($this->pretty["pv_pubarticle"],$MaxPublisherArticleLen);
  }
  else
  {
    // kein Artikel
    $Tmp = $this->pretty["pv_publisher"];
  }

  // PublisherArticle 
  if ( $Tmp == "" )  $Tmp = $this->pretty["physicaldescription"];

  $Output .= "<br />" . $Tmp . "</small>";
  $Output .= "</div></div></div>";

  $Output .= "</div>";
}

// Body
if ( $Body )
{
  $Output .= "<div class='panel-body preview-body preview-text'>";
  $Output .= "</div>";
}

if ( $Footer )
{
  $Output .= "<div class='panel-footer preview-footer'><span>";
  $Output .= $this->CI->database->code2text($this->format);
  $Output .= "</span><span class='pull-right'>";
  $Output .= $this->PPN;
  $Output .= "</span></div>";
}

// End Output
?>