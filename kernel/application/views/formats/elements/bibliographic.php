<?php

// Title
if ( isset($this->pretty["title"]) && $this->pretty["title"] != "" )
{
  $Output .=  "<tr>";
  $Output .=  "<td>" . $this->CI->database->code2text("title") . "</td>";
  $Output .=  "<td>" . $this->pretty["title"] . "</td>";
  $Output .=  "</tr>";
}

// AddTitle
if ( isset($this->pretty["addtitle"]) && count($this->pretty["addtitle"]) )
{
	foreach ( $this->pretty["addtitle"] as $one)
	{
	  $Output .=  "<tr>";
  	$Output .=  "<td></td>";
	  $Output .=  "<td>" . $one . "</td>";
  	$Output .=  "</tr>";
  }
}

// Uniform Title
if ( isset($this->pretty["uniformtitle"]) && count($this->pretty["uniformtitle"]) )
{
  $Output .=  "<tr>";
  $Output .=  "<td>" . $this->CI->database->code2text("uniformtitle") . "</td>";
  $Output .=  "<td>";
  $First = true;
  if ( isset($this->pretty["uniformtitle"]) && count($this->pretty["uniformtitle"]) )
  {
    foreach ( $this->pretty["uniformtitle"] as $one)
    {
      if ( !$First ) $Output .= " | ";
      $Output .= ( isset($one["norm"]) && $one["norm"] ) ? $this->link("norm",    $one["norm"], $one["name"])
                                                         : $this->link("subject", $one["name"]);
      $First = false;
    }
  }
  $Output .=  "</td></tr>";
}

// Work Title
if ( isset($this->pretty["worktitle"]) && $this->pretty["worktitle"] != "" )
{
  $Output .=  "<tr>";
  $Output .=  "<td>" . $this->CI->database->code2text("worktitle") . "</td>";
  $Output .=  "<td>" . $this->pretty["worktitle"] . "</td>";
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
    $Output .= ( isset($one["norm"]) && $one["norm"] ) ? $this->link("norm",  $one["norm"], $one["name"])
                                                       : $this->link("author", $one["name"]);
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
    $Output .= ( isset($one["norm"]) && $one["norm"] ) ? $this->link("norm",  $one["norm"], $one["name"])
                                                       : $this->link("author", $one["name"]);
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
    $Output .= ( isset($one["norm"]) && $one["norm"] ) ? $this->link("norm",        $one["norm"], $one["name"])
                                                       : $this->link("corporation", $one["name"]);
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
    if ( isset($one["w"]) && in_array(substr($one["w"],0,8), array("(DE-601)","(DE-627)") ) )
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
      if ( isset($one["w"]) && in_array(substr($one["w"],0,8), array("(DE-601)","(DE-627)") ) )
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

// Edition
if ( isset($this->pretty["edition"]) && count($this->pretty["edition"]) )
{
  $Output .=  "<tr>";
  $Output .=  "<td>" . $this->CI->database->code2text("edition") . "</td>";
  $Output .=  "<td>";
  $First   = true;
  foreach ( $this->pretty["edition"] as $one)
  {
    if ( !$First ) $Output .= " | ";
    $Output .= str_ireplace(array(", Musikalische Ausgabeform", "Musikalische Ausgabeform"), ".", $one);
    $First = false;
  }
  $Output .=  "</td></tr>";
}

// Recording
if ( isset($this->pretty["recording"]) && count($this->pretty["recording"]) > 0 )
{
  $Output .=  "<tr>";
  $Output .=  "<td>" . $this->CI->database->code2text("recording") . "</td>";
  $Output .=  "<td>";
  $First = true;
  foreach ( $this->pretty["recording"] as $one)
  {
    if ( !$First ) $Output .= " | ";
    $Output .= $one;
    $First = false;
  }
  $Output .=  "</td></tr>";
}

// Published
if ( isset($this->pretty["publisherarticle"]) && count($this->pretty["publisherarticle"]) > 0 )
{
  $Output .=  "<tr>";
  $Output .=  "<td>" . $this->CI->database->code2text("published") . "</td>";
  $Output .=  "<td>";
  $In      = "";
  $one = $this->pretty["publisherarticle"][0];
  if ( isset($one["i"]) && $one["i"] != "" )  $In .= $one["i"] . " ";
  if ( isset($one["w"]) && in_array(substr($one["w"],0,8), array("(DE-601)","(DE-627)") ) )
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
  else
  {
    if ( isset($one["t"]) && $one["t"] != "" )  $In .= $one["t"] . " ";
  }
  if ( isset($one["a"]) && $one["a"] != "" )  $In .= $one["a"] . ", ";
  // if ( isset($one["d"]) && $one["d"] != "" )  $In .= $one["d"] . " ";
  if ( isset($one["g"]) && $one["g"] != "" )  $In .= $one["g"] . ". ";
  if ( isset($one["q"]) && $one["q"] != "" )  $In .= "Band: " . $one["q"];
  if ( isset($one["h"]) && $one["h"] != "" )  $In .= $one["h"];
  $Output .= $In;
  $Output .=  "</td></tr>";
}
else
{
  if ( isset($this->pretty["publisher"]) && count($this->pretty["publisher"]) > 0 )
  {
    $Output .=  "<tr>";
    $Output .=  "<td>" . $this->CI->database->code2text("published") . "</td>";
    $Output .=  "<td>";
    $First   = true;
    foreach ( $this->pretty["publisher"] as $one)
    {
      $In = ( !$First ) ? " | " : "";
      if ( isset($one["a"]) && count($one["a"]) )
      {
      	$FO = true;
      	foreach ($one["a"] as $O)
      	{
      		if ( !$FO )	$In .= ", ";
      		$In .= $O;
      		$FO  = false;
      	}
      }
      if ( isset($one["b"]) && count($one["b"]) )
      {
      	$In .= " : ";
      	$FO  = true;
      	foreach ($one["b"] as $O)
      	{
      		if ( !$FO )	$In .= ", ";
      		$In .= $this->link("publisher", $O);
      		$FO  = false;
      	}
      }
      if ( isset($one["c"]) && count($one["c"]) )
      {
      	$In .= ", ";
      	$FO  = true;
      	foreach ($one["c"] as $O)
      	{
      		if ( !$FO )	$In .= ", ";
      		$In .= $this->link("year", $O); // filter_var($O, FILTER_SANITIZE_NUMBER_INT));
      		$FO  = false;
      	}
      }
      $Output .= $In;
      $First   = false;
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
    $Tmp = $this->CI->database->english_countrycode2speech($one);
    $Output .=  ( intval($Tmp) <0 ) ? $one : $Tmp;
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

// Fingerprint
if ( isset($this->pretty["fingerprint"]) && count($this->pretty["fingerprint"]) > 0 )
{
  $Output .=  "<tr>";
  $Output .=  "<td>" . $this->CI->database->code2text("FINGERPRINT") . "</td>";
  $Output .=  "<td>";
  $First = true;
  foreach ( $this->pretty["fingerprint"] as $one)
  {
    if ( !$First ) $Output .= "<br />";
    $Output .=  print_r($one, true);
    $First = false;
  }
  $Output .=  "</td></tr>";
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
  $Output .=  "<td>" . $this->CI->database->code2text("includes");
  $CutPos  = $this->CI->CutPos($this->pretty["includes"], 380);
  if ( $CutPos >= 0 )
  {
    $Output .=  "<br /><br /><button class='btn btn-tiny navbar-panel-color' onclick='javascript:$.toggle_area(&quot;bibinc_" . $this->dlgid . "&quot;);'><span class='bibinc_" . $this->dlgid . "down'><i class='fa fa-caret-down'></i></span><span class='bibinc_" . $this->dlgid . "up collapse'><i class='fa fa-caret-up'></i></span></button>";
  }
  $Output .=  "</td><td>";
  if ( $CutPos >= 0 )
  {
    $Output .=  trim(substr($this->pretty["includes"],0,$CutPos)) . " ...";
    $Output .=  "<tr class='discoverbibinc_" . $this->dlgid . " collapse'><td>&nbsp;</td><td>";
    $Output .=  "... " . substr($this->pretty["includes"],$CutPos);
  }
  else
  {
    $Output .=  $this->pretty["includes"];
  }
  $Output .=  "</td></tr>";
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
  $Output .=  "<tr>";
  $Output .=  "<td>" . $this->CI->database->code2text("othereditions") . "</td>";
  $Output .=  "<td>";
  $Count = 0;
  $Total = count($this->pretty["othereditions"]);
  $First = true;
  foreach ( $this->pretty["othereditions"] as $one)
  {
    $In = ( !$First ) ? " | " : "";
    if ( isset($one["i"]) && $one["i"] != "" )  $In .= $one["i"] . " ";
    if ( isset($one["w"]) && in_array(substr($one["w"],0,8), array("(DE-601)","(DE-627)") ) )
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
    $Tmp = array();
    if ( isset($one["z"]) && $one["z"] != "" )  $Tmp[] = $one["z"];
    if ( isset($one["a"]) && $one["a"] != "" )  $Tmp[] = $one["a"];
    if ( isset($one["d"]) && $one["d"] != "" )  $Tmp[] = $one["d"];
    if ( isset($one["g"]) && $one["g"] != "" )  $Tmp[] = $one["g"];
    if ( isset($one["q"]) && $one["q"] != "" )  $Tmp[] = "Band: " . $one["q"];
    if ( isset($one["h"]) && $one["h"] != "" )  $Tmp[] = $one["h"];
    $Output .= $In;
    if ( count($Tmp) )  $Output .= implode(", ", $Tmp);
    $First = false;
  }
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
				if ( $key == "w" && in_array(substr(trim($value),0,8), array("(DE-601)","(DE-627)") ) ) $FoundLink  = trim(substr(trim($value),8));
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
        if ( $key == "i" && trim($value) != "" )                      $FoundText .= trim($value)    . ": ";
        if ( $key == "i" && is_array($value) && isset($value[0]) )    $FoundText .= trim($value[0]) . ": ";
        if ( $key == "a" && trim($value) != "" )                      $FoundText .= trim($value);
        if ( $key == "a" && is_array($value) && isset($value[0]) )    $FoundText .= trim($value[0]);
        if ( $key == "t" && trim($value) != "" )                      $FoundText .= ( $FoundText != "" ) ? ": " . trim($value)    : trim($value);
        if ( $key == "t" && is_array($value) && isset($value[0]) )    $FoundText .= ( $FoundText != "" ) ? ": " . trim($value[0]) : trim($value[0]);
        if ( $key == "d" && trim($value) != "" )                      $FoundText .= ", " . trim($value);
        if ( $key == "d" && is_array($value) && isset($value[0]) )    $FoundText .= ", " . trim($value[0]);
        if ( $key == "w" && substr(trim($value),0,8) == "(DE-600)" )  { $FoundLink  = $value; $Intern = false;}
        if ( $key == "w" && in_array(substr(trim($value),0,8), array("(DE-601)","(DE-627)") ) )  { $FoundLink  = trim(substr(trim($value),8)); $Intern = true;}
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
  $Output .=  "<td>" . $this->CI->database->code2text("summary");
  $CutPos  = $this->CI->CutPos($this->pretty["summary"], 380);
  if ( $CutPos >= 0 )
  {
    $Output .=  "<br /><br /><button class='btn btn-tiny navbar-panel-color' onclick='javascript:$.toggle_area(&quot;bibsum_" . $this->dlgid . "&quot;);'><span class='bibsum_" . $this->dlgid . "down'><i class='fa fa-caret-down'></i></span><span class='bibsum_" . $this->dlgid . "up collapse'><i class='fa fa-caret-up'></i></span></button>";
  }
  $Output .=  "</td><td>";
  if ( $CutPos >= 0 )
  {
    $Output .=  trim(substr($this->pretty["summary"],0,$CutPos)) . " ...";
    $Output .=  "<tr class='discoverbibsum_" . $this->dlgid . " collapse'><td>&nbsp;</td><td>";
    $Output .=  "... " . substr($this->pretty["summary"],$CutPos);
  }
  else
  {
    $Output .=  $this->pretty["summary"];
  }
  $Output .=  "</td></tr>";
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
          $Output .=  $this->link("web", $one["u"], $Text );
          $First = false;
          break;
        }
        case "Cover":
        {
          if ( !$First ) $Output .= " | ";
          $Output .=  $this->link("web", $one["u"], "Cover");
          $First = false;
          break;
        }
        default:
        {
          if ( !$First ) $Output .= " | ";
          $Output .=  $this->link("web", $one["u"], "Online");
          $First = false;
          break;
        }
      }
    }
  }
  $Output .=  "</td></tr>";
}

// Provenance
if ( isset($this->pretty["provenance"]) && count($this->pretty["provenance"]) )
{
  $Output .=  "<tr>";
  $Output .=  "<td>" . $this->CI->database->code2text("PROVENANCE") . "</td>";
  $Output .=  "<td>";
  $First   = true;
  foreach ( $this->pretty["provenance"] as $one)
  {
    if ( !$First ) $Output .= " <br /> ";
    $Output .= $one;
    $First = false;
  }
  $Output .=  "</td></tr>";
}

// Subject
if ( ( isset($this->pretty["subject"]) && count($this->pretty["subject"]) )
  || ( isset($this->pretty["subjectheadings"]) && count($this->pretty["subjectheadings"]) ) )
{
  $Output .=  "<tr>";
  $Output .=  "<td>" . $this->CI->database->code2text("subject") . "</td>";
  $Output .=  "<td>";
  $First = true;
  if ( isset($this->pretty["subject"]) && count($this->pretty["subject"]) )
  {
    foreach ( $this->pretty["subject"] as $one)
    {
      if ( !$First ) $Output .= " | ";
      $Output .= ( isset($one["norm"]) && $one["norm"] ) ? $this->link("norm",    $one["norm"], $one["name"])
                                                         : $this->link("subject", $one["name"]);
      $First = false;
    }
  }
  if ( isset($this->pretty["subjectheadings"]) && count($this->pretty["subjectheadings"]) )
  {
    foreach ( $this->pretty["subjectheadings"] as $one)
    {
      if ( !$First ) $Output .= " | ";
      $Output .= ( isset($one["norm"]) && $one["norm"] ) ? $this->link("norm",    $one["norm"], $one["name"])
                                                         : $this->link("subject", $one["name"]);
      $First = false;
    }
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
    $Output .= ( isset($one["norm"]) && $one["norm"] ) ? $this->link("norm",  $one["norm"], $one["name"])
                                                       : $this->link("genre", $one["name"]);
    $First = false;
  }
  $Output .=  "</td></tr>";
}

// Classification
if ( isset($this->pretty["classification"]) && count($this->pretty["classification"]) > 0 )
{
  $Output .=  "<tr>";
  $Output .=  "<td>" . $this->CI->database->code2text("classification") . "</td>";
  $Output .=  "<td>";

  $Classes = array();
  foreach ($this->pretty["classification"] as $One)
  {
    if ( !isset($One["a"]["0"]) || !trim($One["a"]["0"]) ) continue;
    foreach ( $One["a"] as $OneA )
    {
      if ( isset($One["2"]["0"]) && trim($One["2"]["0"]) )
      {
        $Classes[strtoupper($One["2"]["0"])."|".$OneA] = array("classification" => strtoupper($One["2"]["0"]), "code" => $OneA);
      }
      else
      {
        $Classes["DDC|".$OneA] = array("classification" => "DDC", "code" => $OneA);
      }
    }
  }

  $CDBC = ( $this->CI->database->existsCentralDB() && $Classes )
          ? $this->CI->database->getCentralDB("classification", $Classes)
          : array();

  if ( isset($CDBC["details"]) )
  {
    foreach ( $CDBC["details"] as $One )
    {
			if ( !isset($One["classification"]) || !$One["classification"] ) continue;
      $Output .= $this->link("topic", $One["classification"] . " " . $One["code"], $One["description"]);
      $Output .= " (" . $One["classification"] . " <a tabindex='0' role='button'";
      $Output .= " class='btn btn-tiny navbar-panel-color' data-toggle='popover' data-trigger='focus'";
      $Output .= " data-placement='top' data-container='body' data-title='" . $CDBC["classifications"][$One["classification"]]["name"] . "'";
      $Output .= " data-html='true' data-content='<a href=\"" . $CDBC["classifications"][$One["classification"]]["link"] . "\" target=\"_blank\">Homepage <i class=\"fa fa-external-link\"></i></a>";
      if ( $One["parents"] ) $Output .= "<br /><br />" . $this->CI->database->code2text("HIERARCHY") . ":";

      if ( $One["parents"] ) $Output .= "<ul>";
      foreach ($One["parents"] as $Code => $Parent)
      {
        $Output .= "<li><small><a target=\"_blank\" href=\"/topic(" . $One["classification"] . " " . $Code . ")\">" . $Parent . " <i class=\"fa fa-external-link\"></i></a></small></li>";
      }
      if ( $One["parents"] )
      {
        $Output .= "<li><small><a target=\"_blank\" href=\"/topic(" .$One["classification"] . " " . $One["code"] . ")\">" . $One["description"] . " <i class=\"fa fa-external-link\"></i></a></small></li>";
        $Output .= "</ul>";
      }
      $Output .= "'><i class='fa fa-caret-up'></i></a>)<br />";
      unset($Classes[$One["classification"]."|".$One["code"]]);
    }

    $First = true;
    foreach ( $Classes as $One)
    {
      $Output .= ( !$First ) ? " | " : "";
			$Output .= $this->link("topic", $One["classification"] . " " . $One["code"]);
      if ( array_key_exists($One["classification"], $CDBC["classifications"]) )
      {
        $Output .= " (" . $One["classification"] . " <a tabindex='0' role='button'";
        $Output .= " class='btn btn-tiny navbar-panel-color' data-toggle='popover' data-trigger='focus'";
        $Output .= " data-placement='top' data-container='body' data-title='" . $CDBC["classifications"][$One["classification"]]["name"] . "'";
        $Output .= " data-html='true' data-content='<a href=\"" . $CDBC["classifications"][$One["classification"]]["link"] . "\" target=\"_blank\">Homepage <i class=\"fa fa-external-link\"></i></a>";
        $Output .= "'><i class='fa fa-caret-up'></i></a>)";
      }
      else
      {
        $Output .= " (" . $One["classification"] . ")";
      }
   		$First = false;
    }

    // Activate Popovers
    $Output .= "<script>$('[data-toggle=popover]').popover();</script>";
  }
  else
  {
    $First = true;
    foreach ( $this->pretty["classification"] as $One)
    {
      $Nm = "";
      if ( isset($One["9"]) )
      {
        foreach($One["9"] as $Single)
        {
          $Nm .= $Single . " ";
        }
      }
      $Sy = ( isset($One["2"]["0"]) && $One["2"]["0"] != "" ) ? " (" . strtoupper($One["2"]["0"]) . ")" : "";

      if ( isset($One["a"]) )
      {
        foreach($One["a"] as $Single)
        {
          $In = ( !$First ) ? " | " : "";
          $Cl = ( $Single != "" ) ? $this->link("class", $Single, trim($Nm)) : "";
          if ( $Cl != "" )
          {
            $Output .= $In . $Cl . $Sy;
            $First = false;
          }
        }
      }
    }
  }

  $Output .=  "</td></tr>";
}

// Identifier
if ( isset($this->pretty["idents"]) && count($this->pretty["idents"]) > 0 )
{
  foreach ( $this->pretty["idents"] as $one)
  {
    if ( !isset($one["a"]) || !$one["a"] )  continue;

    $Output .=  "<tr>";
    if ( isset($one["2"]) && substr($one["2"],0,2) == "vd" )
    {
      $Output .=  "<td>" . $this->CI->database->code2text(strtoupper($one["2"])) . "</td>";
    }
    elseif ( isset($one["2"]) && $one["2"] == "urn" )
    {
      $Output .=  "<td>" . $this->CI->database->code2text("IDENTIFIER") . "</td>";
    }
    else
    {
      continue;
    }
    $Output .=  "<td>" . $one["a"] . "</td>";
    $Output .=  "</td></tr>";
  }
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
    $Link    = $_SESSION["collections"][strtoupper($one)]["link"];
    if ( in_array($one, array("KXP", "OLC", "SWB")) )
    {
			$Pos   = strpos($this->PPN, substr(strpbrk($this->PPN, "0123456789"),0,1));
    	$Col   = ($Pos) ? strtoupper(substr($this->PPN,0,$Pos)) : "KXP";
    	$Link .= "PPNSET?PPN=" . substr($this->PPN,$Pos);
    }
    $Output .= $this->link("web", $Link, $_SESSION["collections"][strtoupper($one)]["name"]);
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
    $Text = ""; $Link = "";
    if ( isset($one["a"]) )  { $Text  = $one["a"]; $Link = $one["a"]; }
    if ( isset($one["b"]) )  { $Text .= " " . $one["b"];}

    $Output .= $this->link("classlocal", $Link, $Text);
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
          && isset($one["w"]) && $one["w"] != "" && in_array(substr($one["w"],0,8), array("(DE-601)","(DE-627)" ) ) )  $Found = true;
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
      $tmp = array();
      if ( isset($one["n"]) && $one["n"] != "" )  $tmp[] = $one["n"];
      if ( isset($one["t"]) && $one["t"] != "" )  $tmp[] = $one["t"];
      $Text = implode(", ", $tmp);
      if ( $Text != "" && isset($one["w"]) && $one["w"] != "" && in_array(substr($one["w"],0,8),array("(DE-601)","(DE-627)") ) )
      {
        if ( isset($one["i"]) && $one["i"] != "" )  $Output .= $one["i"] . " ";
        $Output .= $this->link("id", trim(substr($one["w"],8)),$Text);
        if ( isset($one["d"]) && $one["d"] != "" )  $Output .= ", ". $one["d"];
        if ( isset($one["h"]) && $one["h"] != "" )  $Output .= ", ". $one["h"];
        $First = false;
      }
    }
    $Output .=  "</td></tr>";
  }
}

// License
if ( isset($this->pretty["license"]) && count($this->pretty["license"]) > 0 )
{
  $Output .=  "<tr>";
  $Output .=  "<td>" . $this->CI->database->code2text("license") . "</td>";
  $Output .=  "<td>";
  $First = true;
  foreach ( $this->pretty["license"] as $one)
  {
    if ( !$First ) $Output .= " | ";
    $Text = ""; $Link = "";
    if ( isset($one["f"]) )  { $Text  = $one["f"]; }
    if ( isset($one["u"]) )  { $Link  = $one["u"];}
    $Output .= $this->link("web", $Link, $Text);
    $First = false;
  }
  $Output .=  "</td></tr>";
}

?>