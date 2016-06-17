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

//  $Output .= "<table class='table rowheight-reduced table-hover borderless'>";
//  $Output .= "<tbody><tr>";
//  $Output .= "<td width='1%'>";
  $Output .= "<div class='table-layout'><div class='table-cell preview-first-col'>";
  $Output .= "<span data-toggle='tooltip' title='" . $this->CI->database->code2text($this->format) . "' class='preview-icon'>" . $this->SetCover() . "</span>";
  $Output .= "<br />";
  $Output .= "<span class='preview-counter'>" . $this->NR . "</span>";
//  $Output .= "</td>";
//  $Output .= "<td class='preview-text'>";
  $Output .= "</div><div class='table-cell preview-second-col'>";
  
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
      
        // Show second found item
        $Output .= "<br />";
        $Output .= "<small>";
        $Output .= ($this->pretty["titlesecond"] != "" ) ? $this->Mark_Text($this->pretty["titlesecond"]) :       implode(", ", $this->Mark_Text($this->pretty["author"]));
      
        $Output .= "</small><br />";
      
       if ( in_array($this->format, array("article", "earticle")) )
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
      
        $Output .= $Tmp;
  //$Output .= "</td>";
  //$Output .= "</tr></tbody>";
  //$Output .= "</table>";
  $Output .= "</div></div></div>";

  $Output .= "</div>";
}

// Body
if ( $Body )
{
  $Output .= "<div class='panel-body preview-body preview-text'>";
/*

  $Output .= "<div class='col-xs-3 preview-cover'>";
  $Output .= $this->SetCover("preview");
  $Output .= "</div>";
*/

 

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