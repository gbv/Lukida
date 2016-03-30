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
  $Output .= "<div class='panel-heading preview-header'><span>";

  // *** Header Fields ***
  $Output .= "<span class='pull-right counter'>" . $this->NR . "</span>";
  $Output .= "<div class='row row-panel title preview-text'>";

  // Show title item
  if ( $this->pretty["title"] != "" )
  {
    $Output .= $this->Mark_Text($this->pretty["title"]);
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


  $Output .= "</div>";
  $Output .= "<div class='row row-panel'><small class='preview-text'>";

  // Show second found item
  $Output .= ($this->pretty["titlesecond"] != "" ) ? $this->Mark_Text($this->pretty["titlesecond"]) : implode(", ", $this->Mark_Text($this->pretty["author"]));

  $Output .= "</small></div>";
  $Output .= "</span></div>";
}

// Body
if ( $Body )
{
  $Output .= "<div class='panel-body preview-body'>";
  $Output .= "<div class='container-fluid'><div class='row row-auto'>";
  $Output .= "<div class='col-xs-3 preview-cover'>";
  $Output .= $this->SetCover("preview");
  $Output .= "</div>";
  $Output .= "<div class='col-xs-9 preview-text'>";

  if ( in_array($this->format, array("article", "earticle")) )
  {
    // Artikel
    $MaxPublisherArticleLen	= (isset($_SESSION["config_discover"]["preview"]["maxpublisherarticlelength"]) ) ? $_SESSION["config_discover"]["preview"]["maxpublisherarticlelength"] : 100;
    $Tmp = $this->Trim_Text($this->pretty["pv_pubarticle"],$MaxPublisherArticleLen);
  }
  else
  {
    // kein Artikel
    $Tmp = $this->pretty["pv_publisher"];
  }

  // PublisherArticle 
  if ( $Tmp == "" )  $Tmp = $this->pretty["physicaldescription"];

  $Output .= $Tmp;

  $Output .= "</div>";
  $Output .= "</div>";
  $Output .= "</div>";
  $Output .= "</div>";

  // Log empty body records
  //if ( $Length == 0 )	$this->LogEmptyPPNs();
}

if ( $Footer )
{
  $Output .= "<div class='panel-footer preview-footer'><span>";

  // *** Footer Fields ***
  $Output .= $this->CI->database->code2text($this->format);

  $Output .= "</span><span class='pull-right'>";

  $Output .= $this->PPN;

  $Output .= "</span></div>";
}

// End Output
?>