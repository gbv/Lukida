<?php

// Title
if ( $this->pretty["title"] != "" )
{
  $Output .=  "<tr>";
  $Output .=  "<td>" . $this->CI->database->code2text("title") . "</td>";
  $Output .=  "<td>" . $this->pretty["title"] . "</td>";
  $Output .=  "</tr>";
}

// Uniform Title
if ( $this->pretty["uniformtitle"] != "" )
{
  $Output .=  "<tr>";
  $Output .=  "<td>" . $this->CI->database->code2text("uniformtitle") . "</td>";
  $Output .=  "<td>" . $this->pretty["uniformtitle"] . "</td>";
  $Output .=  "</tr>";
}

// Part
if ( $this->pretty["part"] != "" )
{
  $Output .=  "<tr>";
  $Output .=  "<td>" . $this->CI->database->code2text("part") . "</td>";
  $Output .=  "<td>" . $this->pretty["part"] . "</td>";
  $Output .=  "</tr>";
}

// Author
if ( count($this->pretty["author"]) > 0 )
{
  $Output .=  "<tr>";
  $Output .=  "<td>" . $this->CI->database->code2text("author") . "</td>";
  $Output .=  "<td>";
  $First = true;
  foreach ( $this->pretty["author"] as $one)
  {
    if ( !$First ) $Output .= " | ";
    $Output .= $this->link("author", $one);
    $First = false;
  }
  $Output .=  "</td></tr>";
}

// Associates
if ( count($this->pretty["associates"]) > 0 )
{
  $Output .=  "<tr>";
  $Output .=  "<td>" . $this->CI->database->code2text("associates") . "</td>";
  $Output .=  "<td>";
  $First = true;
  foreach ( $this->pretty["associates"] as $one)
  {
    if ( !$First ) $Output .= " | ";
    if ( isset($one["a"]) )
    {
      if ( isset($one["4"]) )
      {
        $Output .= $this->link("author", $one["a"]) . " (" . $this->CI->database->code2text($one["4"]) . ")";
      }
      else
      {
        if ( isset($one["e"]) )
        {
          $Output .= $this->link("author", $one["a"]) . " (" . $one["e"] . ")";
        }
        else
        {
          $Output .= $this->link("author", $one["a"]);
        }
      }
    }
    $First = false;
  }
  $Output .=  "</td></tr>";
}

// Corporation
if ( $this->pretty["corporation"] != "" )
{
  $Output .=  "<tr>";
  $Output .=  "<td>" . $this->CI->database->code2text("corporation") . "</td>";
  $Output .=  "<td>" . $this->link("author", $this->pretty["corporation"]) . "</td>";
  $Output .=  "</tr>";
}

// Series
if ( count($this->pretty["serial"]) > 0 )
{
  $Output .=  "<tr>";
  $Output .=  "<td>" . $this->CI->database->code2text("serial") . "</td>";
  $Output .=  "<td>";
  $First = true;
  foreach ( $this->pretty["serial"] as $one)
  {
    if ( !$First ) $Output .= " | ";
    if ( isset($one["a"]) && $one["a"] != "" )
    {
      $AddText = ( isset($one["v"]) && $one["v"] != "" ) ? " " . $one["v"] : "";
      $Output .= $this->link("series", $one["a"]) . $AddText;
      $First = false;
    }
  }
  $Output .=  "</td></tr>";
}

// In (830 sonst 800)
if ( count($this->pretty["in830"]) > 0 )
{
  $Output .=  "<tr>";
  $Output .=  "<td>" . $this->CI->database->code2text("in") . "</td>";
  $Output .=  "<td>";
  $First = true;
  foreach ( $this->pretty["in830"] as $one)
  {
    $In = ( !$First ) ? " | " : "";
    if ( isset($one["a"]) && $one["a"] != "" )  $In .= $one["a"] . " ";
    if ( isset($one["b"]) && $one["b"] != "" )  $In .= $one["b"] . " ";
    if ( isset($one["p"]) && $one["p"] != "" )  $In .= $one["p"] . ". ";
    if ( isset($one["v"]) && $one["v"] != "" )  $In .= $one["v"];
    if ( isset($one["w"]) && substr($one["w"],0,8) == "(DE-601)" )
    {
      $Output .= $this->link("id", trim(substr($one["w"],8)),$In);
    }
    else
    {
      $Output .= $In;
    }
    $First = false;
  }
  $Output .=  "</td></tr>";
}
else
{
  if ( count($this->pretty["in800"]) > 0 )
  {
    $Output .=  "<tr>";
    $Output .=  "<td>" . $this->CI->database->code2text("in") . "</td>";
    $Output .=  "<td>";
    $First = true;
    foreach ( $this->pretty["in800"] as $one)
    {
      $In = ( !$First ) ? " | " : "";
      if ( isset($one["a"]) && $one["a"] != "" )  $In .= $one["a"];
      if ( isset($one["t"]) && $one["t"] != "" )  $In .= " : ". $one["t"];
      if ( isset($one["v"]) && $one["v"] != "" )  $In .= " Band: " . $one["v"];
      if ( isset($one["w"]) && substr($one["w"],0,8) == "(DE-601)" )
      {
        $Output .= $this->link("id", trim(substr($one["w"],8)),$In);
      }
      else
      {
        $Output .= $In;
      }
      $First = false;
    }
    $Output .=  "</td></tr>";
  }
}

// Edition & Reproduction
if ( $this->pretty["edition"] != "" )
{
  $Output .=  "<tr>";
  $Output .=  "<td>" . $this->CI->database->code2text("edition") . "</td>";
  $Output .=  "<td>" . $this->pretty["edition"];
  if ( count($this->pretty["reproduction"]) > 0 )
  {
    $First = true;
    foreach ( $this->pretty["reproduction"] as $one)
    {
      $In = ( !$First ) ? "" : "<br />" . $this->CI->database->code2text("reproduction");
      if ( isset($one["a"]) && $one["a"] != "" )  $In .= " " . $one["a"] . ".";
      if ( isset($one["b"]) && $one["b"] != "" )  $In .= " " . $one["b"] . ". ";
      if ( isset($one["c"]) && $one["c"] != "" )  $In .= " " . $one["c"] . ". ";
      if ( isset($one["d"]) && $one["d"] != "" )  $In .= " " . $one["d"] . ".";
      if ( isset($one["e"]) && $one["e"] != "" )  $In .= " " . $one["e"] . ". ";
      if ( isset($one["f"]) && $one["f"] != "" )  $In .= " " . $one["f"] . ". ";
      if ( isset($one["n"]) && $one["n"] != "" )  $In .= " " . $one["n"] . ". ";
      $Output .= $In;
      $First = false;
    }
    $Output .=  "</td></tr>";
  }
}

// Published
if ( count($this->pretty["publisherarticle"]) > 0 )
{
  $Output .=  "<tr>";
  $Output .=  "<td>" . $this->CI->database->code2text("published") . "</td>";
  $Output .=  "<td>";
  $First = true;
  foreach ( $this->pretty["publisherarticle"] as $one)
  {
    $In = ( !$First ) ? " | " : "";
    if ( isset($one["i"]) && $one["i"] != "" )  $In .= $one["i"] . " ";
    if ( isset($one["t"]) && $one["t"] != "" )
    {
      if ( isset($one["w"]) && substr($one["w"],0,8) == "(DE-601)" )
      {
        $In .= $this->link("id", trim(substr($one["w"],8)),$one["t"]) . " ";
      }
      else
      {
        $In .= $one["t"] . " ";
      }
    }
    if ( isset($one["d"]) && $one["d"] != "" )  $In .= $one["d"] . " ";
    if ( isset($one["g"]) && $one["g"] != "" )  $In .= $one["g"] . ". ";
    if ( isset($one["q"]) && $one["q"] != "" )  $In .= "Band: " . $one["q"];
    $Output .= $In;
    $First = false;
  }
  $Output .=  "</td></tr>";
}
else
{
  if ( count($this->pretty["publisher"]) > 0 )
  {
    $Output .=  "<tr>";
    $Output .=  "<td>" . $this->CI->database->code2text("published") . "</td>";
    $Output .=  "<td>";
    $First = true;
    foreach ( $this->pretty["publisher"] as $one)
    {
      $In = ( !$First ) ? " | " : "";
      if ( isset($one["a"]) && $one["a"] != "" )  $In .= $one["a"];
      if ( isset($one["b"]) && $one["b"] != "" )  $In .= " : " . $this->link("publisher", $one["b"]);
      if ( isset($one["c"]) && $one["c"] != "" )  $In .= ", " . $this->link("year", filter_var($one["c"], FILTER_SANITIZE_NUMBER_INT));
      $Output .= $In;
      $First = false;
    }
    $Output .=  "</td></tr>";
  }
}

// Language
if ( $this->pretty["language"] != "" )
{
  $Output .=  "<tr>";
  $Output .=  "<td>" . $this->CI->database->code2text("language") . "</td>";
  $Output .=  "<td>";
  $First = true;
  foreach ( $this->pretty["language"] as $one)
  {
    if ( !$First )  $Output .= " | ";
    $Output .=  $this->CI->database->english_countrycode2speech($one);
    $First = false;
  }
  $Output .=  "</td></tr>";
}

// Physical description
if ( $this->pretty["physicaldescription"] != "" )
{
  $Output .=  "<tr>";
  $Output .=  "<td>" . $this->CI->database->code2text("physicaldescription") . "</td>";
  $Output .=  "<td>" . $this->pretty["physicaldescription"] . "</td>";
  $Output .=  "</tr>";
}

// Notes
if ( $this->pretty["notes"] != "" )
{
  $Output .=  "<tr>";
  $Output .=  "<td>" . $this->CI->database->code2text("notes") . "</td>";
  $Output .=  "<td>" . $this->pretty["notes"] . "</td>";
  $Output .=  "</tr>";
}

// Language Notes
if ( $this->pretty["languagenotes"] != "" )
{
  $Output .=  "<tr>";
  $Output .=  "<td>" . $this->CI->database->code2text("languagenotes") . "</td>";
  $Output .=  "<td>" . $this->pretty["languagenotes"] . "</td>";
  $Output .=  "</tr>";
}

// Dissertation
if ( $this->pretty["dissertation"] != "" )
{
  $Output .=  "<tr>";
  $Output .=  "<td>" . $this->CI->database->code2text("dissertation") . "</td>";
  $Output .=  "<td>" . $this->pretty["dissertation"] . "</td>";
  $Output .=  "</tr>";
}

// Citation
if ( $this->pretty["citation"] != "" )
{
  $Output .=  "<tr>";
  $Output .=  "<td>" . $this->CI->database->code2text("citation") . "</td>";
  $Output .=  "<td>" . $this->pretty["citation"] . "</td>";
  $Output .=  "</tr>";
}

// Computer File
if ( $this->pretty["computerfile"] != "" )
{
  $Output .=  "<tr>";
  $Output .=  "<td>" . $this->CI->database->code2text("computerfile") . "</td>";
  $Output .=  "<td>" . $this->pretty["computerfile"] . "</td>";
  $Output .=  "</tr>";
}

// System Details
if ( $this->pretty["systemdetails"] != "" )
{
  $Output .=  "<tr>";
  $Output .=  "<td>" . $this->CI->database->code2text("systemdetails") . "</td>";
  $Output .=  "<td>" . $this->pretty["systemdetails"] . "</td>";
  $Output .=  "</tr>";
}

// ISSN
if ( $this->pretty["issn"] != "" )
{
  $Output .=  "<tr>";
  $Output .=  "<td>" . $this->CI->database->code2text("issn") . "</td>";
  $Output .=  "<td>" . $this->pretty["issn"] . "</td>";
  $Output .=  "</tr>";
}

// ISBN
if ( $this->pretty["isbn"] != "" )
{
  $Output .=  "<tr>";
  $Output .=  "<td>" . $this->CI->database->code2text("isbn") . "</td>";
  $Output .=  "<td>" . $this->pretty["isbn"] . "</td>";
  $Output .=  "</tr>";
}

// Summary
if ( $this->pretty["summary"] != "" )
{
  $Output .=  "<tr>";
  $Output .=  "<td>" . $this->CI->database->code2text("summary") . "</td>";
  $Output .=  "<td>" . $this->pretty["summary"] . "</td>";
  $Output .=  "</tr>";
}

// Additionalinfo
//if ( count($this->pretty["additionalinfo"]) > 0 )
//{
//  $Output .=  "<tr>";
//  $Output .=  "<td>" . $this->CI->database->code2text("additionalinformations") . "</td>";
//  $Output .=  "<td>";
//  $First = true;
//  $Links = array();
//  foreach ( $this->pretty["additionalinfo"] as $one)
//  {
//    if ( isset($one["3"]) && isset($one["u"]) )
//    {     
//      if ( in_array($one["u"],$Links) ) continue;
//      $Links[]  = $one["u"];
//      
//      switch (trim($one["3"]))
//      {
//        case "Rezension":
//        case "Ausfuehrliche Beschreibung":
//        case "Volltext":
//        case "Inhaltsverzeichnis":
//        case "Inhaltstext":
//        {
//          if ( !$First ) $Output .= " | ";
//          $Output .=  $this->link($one["3"], $one["u"]);
//          $First = false;
//          break; 
//        }
//        case "Cover":
//        {
//          if ( !$First ) $Output .= " | ";
//          $Output .=  $this->link("Cover", $one["u"]);
//          $First = false;
//          break; 
//        }
//      }
//    }
//  }
//  $Output .=  "</td></tr>";   
//}

// Subject
if ( count($this->pretty["subject"]) > 0 )
{
  $Output .=  "<tr>";
  $Output .=  "<td>" . $this->CI->database->code2text("subject") . "</td>";
  $Output .=  "<td>";
  $First = true;
  foreach ( $this->pretty["subject"] as $one)
  {
    if ( !$First ) $Output .= " | ";
    $Output .= $this->link("subject", $one);
    $First = false;
  }
  $Output .=  "</td></tr>";
}

// Classification
if ( $this->pretty["classification"] != "" )
{
  $Output .=  "<tr>";
  $Output .=  "<td>" . $this->CI->database->code2text("classification") . "</td>";
  $Output .=  "<td>" . $this->pretty["classification"] . "</td>";
  $Output .=  "</tr>";
}

// Source
if ( count($this->catalogues) > 1 || ( count($this->catalogues) == 1 && isset($_SESSION["iln"]) && $this->catalogues[0] != "GBV_ILN_" . $_SESSION["iln"]) )
{
  $Output .=  "<tr>";
  $Output .=  "<td>" . $this->CI->database->code2text("source") . "</td>";
  $Output .=  "<td>";
  $First = true;
  foreach ( $this->catalogues as $one)
  {
    if ( isset($_SESSION["iln"]) && $one == "GBV_ILN_" . $_SESSION["iln"] ) continue;
    if ( !$First ) $Output .= " | ";
    $Output .= $this->CI->database->code2text($one);
    $First = false;
  }
  $Output .=  "</td></tr>";
}


?>