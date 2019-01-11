<?php

// PPN
$Output .=  "<tr><td>ID</td><td>" . $this->PPN . "</td></tr>";

// Title
if ( isset($this->pretty["title"]) && $this->pretty["title"] != "" )
{
  $Output .=  "<tr>";
  $Output .=  "<td>" . $this->CI->database->code2text("title") . "</td>";
  $Output .=  "<td>" . $this->pretty["title"] . "</td>";
  $Output .=  "</tr>";
}

// Uniform Title
if ( isset($this->pretty["uniformtitle"]) && $this->pretty["uniformtitle"] != "" )
{
  $Output .=  "<tr>";
  $Output .=  "<td>" . $this->CI->database->code2text("uniformtitle") . "</td>";
  $Output .=  "<td>" . $this->pretty["uniformtitle"] . "</td>";
  $Output .=  "</tr>";
}

// Part
if ( isset($this->pretty["part"]) && $this->pretty["part"] != "" )
{
  $Output .=  "<tr>";
  $Output .=  "<td>" . $this->CI->database->code2text("part") . "</td>";
  $Output .=  "<td>" . $this->pretty["part"] . "</td>";
  $Output .=  "</tr>";
}

// Author
if ( isset($this->pretty["author"]) && count($this->pretty["author"]) > 0 )
{
  $Output .=  "<tr>";
  if ( isset($this->pretty["author"][0]["role"]) && $this->pretty["author"][0]["role"] != "" )
  {
	  $Output .=  "<td>" . ucfirst($this->pretty["author"][0]["role"]) . "</td>";
  }
  $Output .=  "<td>";
  $First = true;
  foreach ( $this->pretty["author"] as $one)
  {
    if ( !$First ) $Output .= " | ";
    $Output .= $this->link("author", $one["name"]);
    $First = false;
  }
  $Output .=  "</td></tr>";
}

// Associates
if ( isset($this->pretty["associates"]) && count($this->pretty["associates"]) > 0 )
{
  $Output .=  "<tr>";
  $Output .=  "<td>" . $this->CI->database->code2text("associates") . "</td>";
  $Output .=  "<td>";
  $First = true;
  foreach ( $this->pretty["associates"] as $one)
  {
    if ( !$First ) $Output .= " | ";
    $Output .= $this->link("author", $one["name"]);
    if ( $one["role"] != "" ) $Output .= " (" . $one["role"] . ")";
    $First = false;
  }
  $Output .=  "</td></tr>";
}

// Format
if ( $this->format != "" )
{
  $Output .=  "<tr>";
  $Output .=  "<td>" . $this->CI->database->code2text("format") . "</td>";
  $Output .=  "<td>" . $this->CI->database->code2text($this->format) . "</td>";
  $Output .=  "</tr>";
}  

// Corporation
if ( isset($this->pretty["corporation"]) && count($this->pretty["corporation"]) > 0 )
{
  $Output .=  "<tr>";
  $Output .=  "<td>" . $this->CI->database->code2text("corporation") . "</td>";
  $Output .=  "<td>";
  $First = true;
  foreach ( $this->pretty["corporation"] as $one)
  {
    if ( !$First ) $Output .= " | ";
    $Output .= $this->link("author", trim($one));
    $First = false;
  }
  $Output .=  "</td></tr>";
}

// Series
if ( isset($this->pretty["serial"]) && count($this->pretty["serial"]) > 0 )
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
if ( isset($this->pretty["in830"]) && count($this->pretty["in830"]) > 0 )
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
  if ( isset($this->pretty["in800"]) && count($this->pretty["in800"]) > 0 )
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
if ( isset($this->pretty["edition"]) && $this->pretty["edition"] != "" )
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
if ( isset($this->pretty["publisherarticle"]) && count($this->pretty["publisherarticle"]) > 0 )
{
  $Output .=  "<tr>";
  $Output .=  "<td>" . $this->CI->database->code2text("published") . "</td>";
  $Output .=  "<td>";
  $First = true;
  foreach ( $this->pretty["publisherarticle"] as $one)
  {
    $In = ( !$First ) ? " | " : "";
    if ( isset($one["i"]) && $one["i"] != "" )  $In .= $one["i"] . " ";
    if ( isset($one["w"]) && substr($one["w"],0,8) == "(DE-601)" )
    {
      if ( isset($one["t"]) && $one["t"] != "" )
      {
        $In .= $this->link("id", trim(substr($one["w"],8)),$one["t"]) . " ";
      }
      else
      {
        $In .= $this->link("id", trim(substr($one["w"],8)),$this->pretty["title"]) . " ";
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
  if ( isset($this->pretty["publisher"]) && count($this->pretty["publisher"]) > 0 )
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
      if ( isset($one["c"]) && $one["c"] != "" )  $In .= ", " . $this->link("year", $one["c"]); // filter_var($one["c"], FILTER_SANITIZE_NUMBER_INT));
      $Output .= $In;
      $First = false;
    }
    $Output .=  "</td></tr>";
  }
}

// OriginalYear
if ( isset($this->pretty["originalyear"]) && $this->pretty["originalyear"] != "" )
{
  $Output .=  "<tr>";
  $Output .=  "<td>" . $this->CI->database->code2text("originalyear") . "</td>";
  $Output .=  "<td>" . $this->pretty["originalyear"] . "</td>";
  $Output .=  "</tr>";
}

// Language
if ( isset($this->pretty["language"]) && count($this->pretty["language"]) > 0 )
{
  $Output .=  "<tr>";
  $Output .=  "<td>" . $this->CI->database->code2text("LANGUAGE") . "</td>";
  $Output .=  "<td>";
  $First = true;
  foreach ( $this->pretty["language"] as $one)
  {
    if ( !$First ) $Output .= " | ";
    $Output .=  $this->CI->database->english_countrycode2speech($one);
    $First = false;
  }
  $Output .=  "</td></tr>";
}

// Language Origin
if ( isset($this->pretty["languageorigin"]) && count($this->pretty["languageorigin"]) > 0 )
{
  $Output .=  "<tr>";
  $Output .=  "<td>" . $this->CI->database->code2text("LANGUAGEORIGINAL") . "</td>";
  $Output .=  "<td>";
  $First = true;
  foreach ( $this->pretty["languageorigin"] as $one)
  {
    if ( !$First ) $Output .= " | ";
    $Output .=  $this->CI->database->english_countrycode2speech($one);
    $First = false;
  }
  $Output .=  "</td></tr>";
}

// Physical description
if ( isset($this->pretty["physicaldescription"]) && $this->pretty["physicaldescription"] != "" )
{
  $Output .=  "<tr>";
  $Output .=  "<td>" . $this->CI->database->code2text("physicaldescription") . "</td>";
  $Output .=  "<td>" . $this->pretty["physicaldescription"] . "</td>";
  $Output .=  "</tr>";
}

// Notes
if ( isset($this->pretty["notes"]) && count($this->pretty["notes"]) > 0 )
{
  $Output .=  "<tr>";
  $Output .=  "<td>" . $this->CI->database->code2text("NOTES") . "</td>";
  $Output .=  "<td>";
  $First = true;
  foreach ( $this->pretty["notes"] as $one)
  {
    if ( !$First ) $Output .= "<br />";
    $Output .=  $one;
    $First = false;
  }
  $Output .=  "</td></tr>";
}

// Includes
if ( isset($this->pretty["includes"]) && $this->pretty["includes"] != "" )
{
  $Output .=  "<tr>";
  $Output .=  "<td>" . $this->CI->database->code2text("includes") . "</td>";
  $Output .=  "<td>" . $this->pretty["includes"] . "</td>";
  $Output .=  "</tr>";
}

// PublishedJournal
if ( isset($this->pretty["publishedjournal"]) && $this->pretty["publishedjournal"] != "" )
{
  $Output .=  "<tr>";
  $Output .=  "<td>" . $this->CI->database->code2text("publishedjournal") . "</td>";
  $Output .=  "<td>" . $this->pretty["publishedjournal"] . "</td>";
  $Output .=  "</tr>";
}

// Footnote
if ( isset($this->pretty["footnote"]) && $this->pretty["footnote"] != "" )
{
  $Output .=  "<tr>";
  $Output .=  "<td>" . $this->CI->database->code2text("footnote") . "</td>";
  $Output .=  "<td>" . $this->pretty["footnote"] . "</td>";
  $Output .=  "</tr>";
}

// Other editions
if ( isset($this->pretty["othereditions"]) && count($this->pretty["othereditions"]) > 0 )
{
  $Count = 0;
  $Total = count($this->pretty["othereditions"]);
  foreach ( $this->pretty["othereditions"] as $record)
  {
    $FoundText = "";
    $FoundLink = "";
    foreach ( $record as $key => $onesubfield )
    {
      foreach ( $onesubfield as $value )
      {
        if ( $key == "i" && trim($value) != "" )  $FoundText .= trim($value);
        if ( $key == "t" && trim($value) != "" )  $FoundText .= ( $FoundText != "" ) ? ": " . trim($value) : trim($value);
        if ( $key == "w" && substr(trim($value),0,8) == "(DE-600)" )  $FoundLink = trim($value);
      }
    }

    if ( $FoundText != "" && $FoundLink != "" )
    {
      $Count++;
      if ( $Count == 1 )
      {
        $Output .=  "<tr>";
        $Output .=  "<td>" . $this->CI->database->code2text("EarlierLater");
        if ( $Total > 3 ) $Output .=  "<br /><br /><button class='btn btn-tiny navbar-panel-color' onclick='javascript:$.toggle_area(&quot;biboth_" . $this->dlgid . "&quot;);'><span class='biboth_" . $this->dlgid . "down'><i class='fa fa-caret-down'></i></span><span class='biboth_" . $this->dlgid . "up collapse'><i class='fa fa-caret-up'></i></span></button>";
        $Output .=  "</td><td>";
        $Output .= $this->link("foreignid", $FoundLink, $FoundText);
      }
      if ( $Count >= 2 && $Count <= 3 )
      {
        $Output .=  "<br />";
        $Output .= $this->link("foreignid", $FoundLink, $FoundText);
      }
      if ( $Count == 4 )
      {
        $Output .=  "</td></tr>";
        $Output .=  "<tr class='discoverbiboth_" . $this->dlgid . " collapse'><td>&nbsp;</td><td>";
        $Output .= $this->link("foreignid", $FoundLink, $FoundText);
      }
      if ( $Count >= 5 )
      {
        $Output .=  "<br />";
        $Output .= $this->link("foreignid", $FoundLink, $FoundText);
      }
    }
  }
  if ( $Count > 0 ) $Output .=  "</td></tr>";
}

// Remarks
if ( isset($this->pretty["remarks"]) && count($this->pretty["remarks"]) > 0 )
{
  $Count = 0;
  $Total = count($this->pretty["remarks"]);
  foreach ( $this->pretty["remarks"] as $record)
  {
    $FoundText = "";
    $FoundLink = "";
    foreach ( $record as $key => $onesubfield )
    {
      foreach ( $onesubfield as $value )
      {
        if ( $key == "i" && trim($value) != "" )  $FoundText .= trim($value);
        if ( $key == "t" && trim($value) != "" )  $FoundText .= ( $FoundText != "" ) ? ": " . trim($value) : trim($value);
        if ( $key == "w" && substr(trim($value),0,8) == "(DE-600)" )  $FoundLink = trim($value);
      }
    }

    if ( $FoundText != "" && $FoundLink != "" )
    {
      $Count++;
      if ( $Count == 1 )
      {
        $Output .=  "<tr>";
        $Output .=  "<td>" . $this->CI->database->code2text("remarks");
        if ( $Total > 3 ) $Output .=  "<br /><br /><button class='btn btn-tiny navbar-panel-color' onclick='javascript:$.toggle_area(&quot;bibrem_" . $this->dlgid . "&quot;);'><span class='bibrem_" . $this->dlgid . "down'><i class='fa fa-caret-down'></i></span><span class='bibrem_" . $this->dlgid . "up collapse'><i class='fa fa-caret-up'></i></span></button>";
        $Output .=  "</td><td>";
        $Output .= $this->link("foreignid", $FoundLink, $FoundText);
      }
      if ( $Count >= 2 && $Count <= 3 )
      {
        $Output .=  "<br />";
        $Output .= $this->link("foreignid", $FoundLink, $FoundText);
      }
      if ( $Count == 4 )
      {
        $Output .=  "</td></tr>";
        $Output .=  "<tr class='discoverbibrem_" . $this->dlgid . " collapse'><td>&nbsp;</td><td>";
        $Output .= $this->link("foreignid", $FoundLink, $FoundText);
      }
      if ( $Count >= 5 )
      {
        $Output .=  "<br />";
        $Output .= $this->link("foreignid", $FoundLink, $FoundText);
      }
    }
  }
  if ( $Count > 0 ) $Output .=  "</td></tr>";
}

// See also
if ( isset($this->pretty["seealso"]) && count($this->pretty["seealso"]) > 0 )
{
  $Count = 0;
  $Total = count($this->pretty["seealso"]);
  foreach ( $this->pretty["seealso"] as $record)
  {
    $FoundText = "";
    $FoundLink = "";
    foreach ( $record as $key => $onesubfield )
    {
      foreach ( $onesubfield as $value )
      {
        if ( $key == "i" && trim($value) != "" )                      $FoundText .= trim($value);
        if ( $key == "i" && is_array($value) && isset($value[0]) )    $FoundText .= trim($value[0]);
        if ( $key == "t" && trim($value) != "" )                      $FoundText .= ( $FoundText != "" ) ? ": " . trim($value) : trim($value);
        if ( $key == "t" && is_array($value) && isset($value[0]) )    $FoundText .= ( $FoundText != "" ) ? ": " . trim($value[0]) : trim($value[0]);
        if ( $key == "w" && substr(trim($value),0,8) == "(DE-600)" )  { $FoundLink  = trim(substr(trim($value),8)); $Intern = false;}
        if ( $key == "w" && substr(trim($value),0,8) == "(DE-601)" )  { $FoundLink  = trim(substr(trim($value),8)); $Intern = true;}
      }
    }
    if ( $FoundText != "" && $FoundLink != "" )
    {
      $Count++;
      if ( $Count == 1 )
      {
        $Output .=  "<tr>";
        $Output .=  "<td>" . $this->CI->database->code2text("seealso");
        if ( $Total > 3 ) $Output .=  "<br /><br /><button class='btn btn-tiny navbar-panel-color' onclick='javascript:$.toggle_area(&quot;bibsee_" . $this->dlgid . "&quot;);'><span class='bibsee_" . $this->dlgid . "down'><i class='fa fa-caret-down'></i></span><span class='bibsee_" . $this->dlgid . "up collapse'><i class='fa fa-caret-up'></i></span></button>";
        $Output .=  "</td><td>";
      }
      if ( $Count >= 2 && $Count <= 3 )
      {
        $Output .=  "<br />";
      }
      if ( $Count == 4 )
      {
        $Output .=  "</td></tr>";
        $Output .=  "<tr class='discoverbibsee_" . $this->dlgid . " collapse'><td>&nbsp;</td><td>";
      }
      if ( $Count >= 5 )
      {
        $Output .=  "<br />";
      }
      $Output .= ( $Intern) ? $this->link("id", $FoundLink, $FoundText) : $this->link("foreignid", $FoundLink, $FoundText);
    }
  }
  if ( $Count > 0 ) $Output .=  "</td></tr>";
}

// Language Notes
if ( isset($this->pretty["languagenotes"]) && $this->pretty["languagenotes"] != "" )
{
  $Output .=  "<tr>";
  $Output .=  "<td>" . $this->CI->database->code2text("languagenotes") . "</td>";
  $Output .=  "<td>" . $this->pretty["languagenotes"] . "</td>";
  $Output .=  "</tr>";
}

// Dissertation
if ( isset($this->pretty["dissertation"]) && $this->pretty["dissertation"] != "" )
{
  $Output .=  "<tr>";
  $Output .=  "<td>" . $this->CI->database->code2text("dissertation") . "</td>";
  $Output .=  "<td>" . $this->pretty["dissertation"] . "</td>";
  $Output .=  "</tr>";
}

// Citation
if ( isset($this->pretty["citation"]) && $this->pretty["citation"] != "" )
{
  $Output .=  "<tr>";
  $Output .=  "<td>" . $this->CI->database->code2text("citation") . "</td>";
  $Output .=  "<td>" . $this->pretty["citation"] . "</td>";
  $Output .=  "</tr>";
}

// Computer File
if ( isset($this->pretty["computerfile"]) && $this->pretty["computerfile"] != "" )
{
  $Output .=  "<tr>";
  $Output .=  "<td>" . $this->CI->database->code2text("computerfile") . "</td>";
  $Output .=  "<td>" . $this->pretty["computerfile"] . "</td>";
  $Output .=  "</tr>";
}

// System Details
if ( isset($this->pretty["systemdetails"]) && $this->pretty["systemdetails"] != "" )
{
  $Output .=  "<tr>";
  $Output .=  "<td>" . $this->CI->database->code2text("systemdetails") . "</td>";
  $Output .=  "<td>" . $this->pretty["systemdetails"] . "</td>";
  $Output .=  "</tr>";
}

// ISSN
if ( isset($this->pretty["issn"]) && $this->pretty["issn"] != "" )
{
  $Output .=  "<tr>";
  $Output .=  "<td>" . $this->CI->database->code2text("issn") . "</td>";
  $Output .=  "<td>" . $this->pretty["issn"] . "</td>";
  $Output .=  "</tr>";
}

// ISBN
if ( isset($this->pretty["isbn"]) && $this->pretty["isbn"] != "" )
{
  $Output .=  "<tr>";
  $Output .=  "<td>" . $this->CI->database->code2text("isbn") . "</td>";
  $Output .=  "<td>" . $this->pretty["isbn"] . "</td>";
  $Output .=  "</tr>";
}

// ISMN
if ( isset($this->pretty["ismn"]) && $this->pretty["ismn"] != "" )
{
  $Output .=  "<tr>";
  $Output .=  "<td>" . $this->CI->database->code2text("ismn") . "</td>";
  $Output .=  "<td>" . $this->pretty["ismn"] . "</td>";
  $Output .=  "</tr>";
}

// Summary
if ( isset($this->pretty["summary"]) && $this->pretty["summary"] != "" )
{
  $Output .=  "<tr>";
  $Output .=  "<td>" . $this->CI->database->code2text("summary") . "</td>";
  $Output .=  "<td>" . $this->pretty["summary"] . "</td>";
  $Output .=  "</tr>";
}

// Additionalinfo
if ( isset($this->pretty["additionalinfo"]) && count($this->pretty["additionalinfo"]) > 0 )
{
  $Output .=  "<tr>";
  $Output .=  "<td>" . $this->CI->database->code2text("additionalinformations") . "</td>";
  $Output .=  "<td>";
  $First = true;
  $Links = array();
  foreach ( $this->pretty["additionalinfo"] as $one)
  {
    if ( isset($one["u"]) && $one["u"] != "" )
    {
      // Do not repeat identical links
      if ( in_array($one["u"],$Links) ) continue;
      $Links[]  = $one["u"];

      $Text = (isset($one["y"]) && trim($one["y"]) != "" ) ? trim($one["y"]) : "";
      if ( $Text == "") $Text = (isset($one["3"]) && trim($one["3"]) != "" ) ? trim($one["3"]) : "";
      switch ($Text)
      {
        case "Rezension":
        case "Ausfuehrliche Beschreibung":
        case "Volltext":
        case "Inhaltsverzeichnis":
        case "Inhaltstext":
        {
          if ( !$First ) $Output .= " | ";
          $Output .=  $this->link($Text, $one["u"]);
          $First = false;
          break; 
        }
        case "Cover":
        {
          if ( !$First ) $Output .= " | ";
          $Output .=  $this->link("Cover", $one["u"]);
          $First = false;
          break; 
        }
        default:
        {
          if ( !$First ) $Output .= " | ";
          $Output .=  $this->link("Online", $one["u"]);
          $First = false;
          break; 
        }
      }
    }
  }
  $Output .=  "</td></tr>";   
}

// Subject
if ( isset($this->pretty["subject"]) && count($this->pretty["subject"]) > 0 )
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

// Genre
if ( isset($this->pretty["genre"]) && count($this->pretty["genre"]) > 0 )
{
  $Output .=  "<tr>";
  $Output .=  "<td>" . $this->CI->database->code2text("genre") . "</td>";
  $Output .=  "<td>";
  $First = true;
  foreach ( $this->pretty["genre"] as $one)
  {
    if ( !$First ) $Output .= " | ";
    $Output .= $this->link("genre", $one);
    $First = false;
  }
  $Output .=  "</td></tr>";
}

// Classification
if ( isset($this->pretty["classification"]) && $this->pretty["classification"] != "" )
{
  $Output .=  "<tr>";
  $Output .=  "<td>" . $this->CI->database->code2text("classification") . "</td>";
  $Output .=  "<td>" . $this->pretty["classification"] . "</td>";
  $Output .=  "</tr>";
}

// Collections / Source
if ( count($this->collection) > 0 )
{
  $Output .=  "<tr>";
  $Output .=  "<td>" . $this->CI->database->code2text("source") . "</td>";
  $Output .=  "<td>";
  $First = true;
  foreach ( $this->collection as $one)
  {
    if ( !isset($_SESSION["collections"][strtoupper($one)]) )  continue;
    if ( !$First ) $Output .= " | ";
    $Output .= $this->link("web", $_SESSION["collections"][strtoupper($one)]["link"], $_SESSION["collections"][strtoupper($one)]["name"]);
    $First = false;
  }
  $Output .=  "</td></tr>";
}

// Class
if ( isset($this->pretty["class"]) && count($this->pretty["class"]) > 0 )
{
  $Output .=  "<tr>";
  $Output .=  "<td>" . $this->CI->database->code2text("class") . "</td>";
  $Output .=  "<td>";
  $First = true;
  foreach ( $this->pretty["class"] as $one)
  {
    if ( !$First ) $Output .= " | ";
    $Output .= $this->link("class", trim($one));
    $First = false;
  }
  $Output .=  "</td></tr>";
}

// Bibliographic context
if ( isset($this->pretty["siblings"]) && count($this->pretty["siblings"]) > 0 )
{
  $Found = false;
  foreach ( $this->pretty["siblings"] as $one)
  {
    if ( ( ( isset($one["i"]) && $one["i"] != "" ) || ( isset($one["n"]) && $one["n"] != "" ) ) 
          && isset($one["w"]) && $one["w"] != "" && substr($one["w"],0,8) == "(DE-601)" )  $Found = true;
  }

  if ( $Found )
  {
    $Output .=  "<tr>";
    $Output .=  "<td>" . $this->CI->database->code2text("BIBLIOGRAPHICCONTEXT") . "</td>";
    $Output .=  "<td>";
    $First = true;
    foreach ( $this->pretty["siblings"] as $one)
    {
      if ( !$First ) $Output .= " | ";
      
      if ( isset($one["n"]) && $one["n"] != "" )
      {
      	$Text = $one["n"];
      }
      else
      {
	      if ( isset($one["i"]) && $one["i"] != "" )
      	{
          if ( stripos($one["i"],"von") !== false && isset($one["t"]) && $one["t"] != "" )
          {
            $Text = $one["t"];
          }
          else
          {
            $Text = $one["i"];
          }
       	}
       	else
       	{
       		$Text = "";
       	}
      }
      if ( $Text != "" && isset($one["w"]) && $one["w"] != "" && substr($one["w"],0,8) == "(DE-601)" )
      {
        $Output .= $this->link("id", trim(substr($one["w"],8)),$Text);
        $First = false;
      }
    }
    $Output .=  "</td></tr>";
  }
}

?>