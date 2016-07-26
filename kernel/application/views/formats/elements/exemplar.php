<?php

// Washington specific code

// Initialize vars
$Exemplare    = array();
$Zugaenge     = array();
$Lizenzen     = array();
$RelatedPubs  = array();
$IncJournals  = array();
$IncArticles  = array();
$Interloan    = array();
$SFX          = true;
$Case         = true;

//***************************************
//********* M A I N - P A P  1 **********
//***************************************
if ( substr($this->medium["leader"],7,1) == "m" && substr($this->medium["leader"],19,1) == "a" )
{
  // Mehrbändige Werke
  // $Output .= "Mehrbändige Werke";
  $RelatedPubs = GetRelatedPubs($this->CI,$this,$this->PPN,1);
}

if ( substr($this->medium["leader"],7,1) == "s" && substr(Get008($this->contents),21,1) == "m" )
{
  // Schriftenreihen
  // $Output .= "Schriftenreihen";
  $RelatedPubs = GetRelatedPubs($this->CI,$this,$this->PPN,2);
}

if ( substr($this->medium["leader"],7,1) == "s" && in_array(substr(Get008($this->contents),21,1), array("p","n")) )
{
  // Zeitschriften mit Einzelheften
  // $Output .= "Zeitschriften mit Einzelheften";
  $IncludedPubs = GetIncludedPubs($this->CI,$this,$this->PPN);
  $IncJournals  = $IncludedPubs["journals"];
  $IncArticles  = $IncludedPubs["articles"];
}

//***************************************
//********* M A I N - P A P  2 **********
//***************************************

// Bibliotheks eigener-Bestand ?
if ( isset($this->contents[912]) && isset($_SESSION["iln"]) && $_SESSION["iln"] != "" && ( in_array( "GBV_ILN_".$_SESSION["iln"], $this->catalogues) ) )
{
  // Bibliotheks eigener-Bestand
  if ( substr($this->medium["leader"],6,2) == "ma" 
    || substr($this->medium["leader"],6,2) == "mm" 
    || substr($this->medium["leader"],6,2) == "ms" )
  {
    // Bibliotheks eigener Online Bestand

    // Fetch Link stuff
    $Zugaenge = BestandLinks($this->contents, $this->medium, $this->CI);

    // Fetch License stuff
    $Lizenzen = Get980k($this->contents);
  }
  else
  {
    // Bibliotheks eigener Haptischer Bestand

    // Mehrbändige Werke, Schriftenreihen, Zeitschriften mit Einzelheften
    if ( ( substr($this->medium["leader"],7,1) == "m" && substr($this->medium["leader"],19,1) == "a" )
      || ( substr($this->medium["leader"],7,1) == "s" && substr(Get008($this->contents),21,1) == "m" )
      || ( substr($this->medium["leader"],7,1) == "s" && in_array(substr(Get008($this->contents),21,1), array("p","n")) && Get980($this->contents,"d") == "-" ) )
    {
      $SFX = false;
    }
    else
    {
      if ( Get980($this->contents,"d") != "-" )
      {
        // Haptische Exemplartypen im Bestand
        // Fälle 1-22
        $Exemplare = BestandExemplare($this->CI,$this->leader,$this->contents,$this->medium,$this->PPN);
      }
      else
      {
        if ( substr($this->medium["leader"],7,1) == "a" )
        {
          // Haptische Artikel im Bestand 
          // Fälle 23-26
          $Exemplare = BestandArtikel($this->CI,$this->contents,$this->medium);
        }
      }
    }
  }
}
else
{
  // Fremdbestand
  if ( substr($this->medium["leader"],6,2) == "ma" 
    || substr($this->medium["leader"],6,2) == "mm" 
    || substr($this->medium["leader"],6,2) == "ms" )
  {
    // Fall 32 (Online Fremdbestand)
    if ( array_key_exists("856", $this->contents) )
    {
      // Prio 2: use 856 records
      $Zugaenge = Get856($this->contents["856"], $this->CI);
    }
  }
  else
  {
    // Fall 31 (Haptischer Fremdbestand)

    // Determine database
    if ( isset($_SESSION["config_general"]["interlibraryloan"]) )
    {
      foreach($_SESSION["config_general"]["interlibraryloan"] as $cat => $db )
      {
        if ( $cat != "default" )
        {
          if ( in_array($cat, $this->catalogues) )  $CatDB = $db;
        }
        else
        {
          $DefaultDB = $db;
        }
      }
    }
    $FinalDB = (isset($CatDB) && $CatDB != "") ? $CatDB : $DefaultDB;
    
    $Interloan[] = array
    (
      "link"   => "http://gso.gbv.de/DB=" . $FinalDB . "/PPNSET?PPN=" . $this->PPN,
      "label1" => $this->CI->database->code2text("INTERLOAN")
    );
  }

}

//*************************************
//***** S T A R T   O U T P U T *******
//*************************************

// $this->CI->printArray2Screen($Image);
// $this->CI->printArray2Screen($this->contents);

/*
$this->CI->printArray2Screen(array(
  "Zugänge"                  => $Zugaenge,
  "Exemplare"                => $Exemplare,
  "Fernleihe"                => $Interloan,
  "Lizenzen"                 => $Lizenzen,
  "Zugehörige Publikationen" => $RelatedPubs,
  "Zugehörige Einzelhefte"   => $IncJournals,
  "Zugehörige Artikel"       => $IncArticles
));
*/

// Create Access Buttons
if ( count($Zugaenge) > 0 )
{
  $BtnClass     = "col-xs-12 col-sm-6 col-md-4 btn btn-default btn-exemplar";
  $EmptyClass   = "col-xs-12 col-sm-6 col-md-4 btn btn-default empty-exemplar";
  $Output .= "<div>" . $this->CI->database->code2text("ACCESS") . "</div>";
  $Output .= "<div class='container-fluid'><div class='row'>";

  // Generate Buttons
  foreach ( $Zugaenge as $EPN => $Link )
  { 
    $Exams  = array_unique($Zugaenge, SORT_REGULAR);

    $Action = (isset($Link["link"])) ? "onclick='window.open(\"" . $Link["link"] . "\",\"_blank\")'" : "";
    $Class  = (isset($Link["link"])) ? $BtnClass : $EmptyClass;
    $Output .= "<button " . $Action . " class='" . $Class . "'>";
    $Output .= (isset($Link["label1"])) ?  addslashes($Link["label1"]) : "";
    $Output .= ($Case && isset($Link["case"])) ? " <small>" . $Link["case"]  . "</small>" : "";
    if ( isset($Link["link"]) )
    {
      $Output .= " <span class='fa fa-external-link'></span>";
      if ( $Host = parse_url($Link["link"],PHP_URL_HOST) )
      {  
        if ( $Host == "www.bibliothek.uni-regensburg.de" ) $Host = "Elektr. Zeitschriftenbibliothek";
        if ( substr($Host,0,4) == "www.")   $Host = substr($Host,4);
        $Output .= "<br /><small>" . $Host . "</small>";
      }
    }
    $Output .= "</button>";
  }

  // LinkResolver Spaceholder for async js
  if ( $SFX )   $Output .= "<div id='linkresolver'></div>";
  $Output .= "</div></div>";  
}
elseif ( $SFX )
{
  // LinkResolver Spaceholder for async js
  $Output .= "<div id='linkresolvercontainer'></div>";
}

// Create Exemplar Buttons
if ( count($Exemplare) > 0 || count($Interloan) > 0)
{
  if ( count($Zugaenge) > 0 )  $Output .= "<div class='space_buttons'></div>";
  $BtnClass     = "col-xs-6 col-sm-4 col-md-3 btn btn-default btn-exemplar";
  $EmptyClass   = "col-xs-6 col-sm-4 col-md-3 btn btn-default empty-exemplar";
  $Output .= "<div>" . $this->CI->database->code2text("Exemplars") . "</div>";
  $Output .= "<div class='container-fluid'><div class='row'>";

  // Generate Buttons
  foreach ( $Exemplare as $EPN => $Exemplar )
  {
    // Unique messages
    $Exams    = array_unique($Exemplar, SORT_REGULAR);
    $ExamCase = ($Case && isset($Exams["case"])) ? " <small>" . $Exams["case"]  . "</small>" : "";
    unset($Exams["case"]);
    ksort($Exams);
    $_SESSION["exemplar"][$this->PPN][$EPN] = $Exams;
    $Action = (isset($Exemplar["action"])) ? "onclick='$." . $Exemplar["action"] . "(\"" . $this->PPN . "\",\"" . $EPN . "\"," . json_encode($Exams,JSON_HEX_TAG) . ")'" : "";
    $Class  = (isset($Exemplar["action"])) ? $BtnClass : $EmptyClass;
    $Output .= "<button " . $Action . " class='" . $Class . "'>";
    $Output .= (isset($Exemplar["label1"])) ?  addslashes($Exemplar["label1"]) : "";
    $Output .= $ExamCase;
    $Output .= (isset($Exemplar["label2"])) ?  "<br />" . addslashes($Exemplar["label2"]) : "";
    $Output .= (isset($Exemplar["label3"])) ?  "<br />" . addslashes($Exemplar["label3"]) : "";
    $Output .= "</button>";
  }

  foreach ( $Interloan as $EPN => $Link )
  {
    $Action = (isset($Link["link"])) ? "onclick='window.open(\"" . $Link["link"] . "\",\"_blank\")'" : "";
    $Class  = (isset($Link["link"])) ? $BtnClass : $EmptyClass;
    $Output .= "<button " . $Action . " class='" . $Class . "'>";
    $Output .= (isset($Link["label1"])) ?  addslashes($Link["label1"]) : "";
    $Output .= ($Case && isset($Link["case"])) ? " <small>" . $Link["case"]  . "</small>" : "";
    if ( isset($Link["link"]) )
    {
      $Output .= " <span class='fa fa-external-link'></span>";
      if ( $Host = parse_url($Link["link"],PHP_URL_HOST) )
      {  
        if ( $Host == "www.bibliothek.uni-regensburg.de" ) $Host = "Elektr. Zeitschriftenbibliothek";
        if ( substr($Host,0,4) == "www.")   $Host = substr($Host,4);
        $Output .= "<br /><small>" . $Host . "</small>";
      }
    }
    $Output .= "</button>";
  }

  $Output .= "</div></div>";
}

// Show License stuff
if ( count($Lizenzen)>0)
{
  if ( count($Zugaenge) > 0 || count($Exemplare) > 0 )  $Output .= "<div class='space_buttons'></div>";
  $Output .= "<div>";
  $Output .= "<div>" . $this->CI->database->code2text("License") . "</div><small>";
  foreach ( $Lizenzen as $Lizenz)
  {
    $Output .= $Lizenz . "</br>";
  }
  $Output .= "</small>";
  $Output .= "</div>";
}

// Create Related Publications
if ( count($RelatedPubs) > 0 )
{
  $BtnClass = "col-xs-12 col-sm-6 btn btn btn-default publication";
  if ( count($Zugaenge) > 0 || count($Exemplare) > 0 || count($Lizenzen) > 0 )  $Output .= "<div class='space_buttons'></div>";
  
  $Output .= "<div>" . $this->CI->database->code2text("RELATEDPUBLICATIONS") . "</div>";
  $Output .= "<div class='container-fluid'><div class='row'>";

  // Generate Buttons
  foreach ( $RelatedPubs as $PPN => $Exemplar )
  {
    $Action = "onclick='$.open_fullview(\"" . $PPN . "\"," . json_encode(array_keys($RelatedPubs)) . ",\"publications\")'";
    $Output .= "<button " . $Action . " class='" . $BtnClass . "'>";
    $Output .= "<div id='related_" . $PPN . "'>";
    $Output .= "<table><tr><td data-toggle='tooltip' title='" . $this->CI->database->code2text($Exemplar["format"]) . "' class='publication-icon'>";
    $Output .= "<span class='gbvicon'>" . $Exemplar["cover"] . "</span>";
    $Output .= "</td><td>";  
    $Output .= $this->trim_text($Exemplar["title"],60);
    $Output .= "<br /><small>" . $this->trim_text($Exemplar["publisher"],50) . "</small>";
    $Output .= "</td></tr></table></div>";
    $Output .= "</button>";
  }

  // Close div
  $Output .= "</div></div>";  
}

// Create Included Journals
if ( count($IncJournals) > 0 )
{
  $BtnClass = "col-xs-12 col-sm-6 btn btn btn-default publication";
  if ( count($Zugaenge) > 0 || count($Exemplare) > 0 || count($Lizenzen) > 0 )  $Output .= "<div class='space_buttons'></div>";
  
  $Output .= "<div>" . $this->CI->database->code2text("RelatedJournals") . "</div>";
  $Output .= "<div class='container-fluid'><div class='row'>";

  // Generate Buttons
  foreach ( $IncJournals as $PPN => $Exemplar )
  {
    $Action = "onclick='$.open_fullview(\"" . $PPN . "\"," . json_encode(array_keys($IncJournals)) . ",\"publications\")'";
    $Output .= "<button " . $Action . " class='" . $BtnClass . "'>";
    $Output .= "<div id='related_" . $PPN . "'>";
    $Output .= "<table><tr><td data-toggle='tooltip' title='" . $this->CI->database->code2text($Exemplar["format"]) . "' class='publication-icon'>";
    $Output .= "<span class='gbvicon'>" . $Exemplar["cover"] . "</span>";
    $Output .= "</td><td>";  
    $Output .= $this->trim_text($Exemplar["title"],60);
    $Output .= "<br /><small>" . $this->trim_text($Exemplar["publisher"],50) . "</small>";
    $Output .= "</td></tr></table></div>";
    $Output .= "</button>";
  }

  // Close div
  $Output .= "</div></div>";  
}

// Create Included Articles
if ( count($IncArticles) > 0 )
{
  $BtnClass = "col-xs-12 col-sm-6 btn btn btn-default publication";
  if ( count($Zugaenge) > 0 || count($Exemplare) > 0 || count($Lizenzen) > 0 )  $Output .= "<div class='space_buttons'></div>";
  
  $Output .= "<div>" . $this->CI->database->code2text("RelatedArticles") . "</div>";
  $Output .= "<div class='container-fluid'><div class='row'>";

  // Generate Buttons
  foreach ( $IncArticles as $PPN => $Exemplar )
  {
    $Action = "onclick='$.open_fullview(\"" . $PPN . "\"," . json_encode(array_keys($IncArticles)) . ",\"publications\")'";
    $Output .= "<button " . $Action . " class='" . $BtnClass . "'>";
    $Output .= "<div id='related_" . $PPN . "'>";
    $Output .= "<table><tr><td data-toggle='tooltip' title='" . $this->CI->database->code2text($Exemplar["format"]) . "' class='publication-icon'>";
    $Output .= "<span class='gbvicon'>" . $Exemplar["cover"] . "</span>";
    $Output .= "</td><td>";  
    $Output .= $this->trim_text($Exemplar["title"],60);
    $Output .= "<br /><small>" . $this->trim_text($Exemplar["publisher"],50) . "</small>";
    $Output .= "</td></tr></table></div>";
    $Output .= "</button>";
  }

  // Close div
  $Output .= "</div></div>";  
}



//*************************************
//******** F U N C T I O N S **********
//*************************************

function BestandLinks($Contents, $Medium, $CI)
{
  $Format = $Medium["format"];

  // Collect 981 Links
  $Links = array();
  if ( array_key_exists("981", $Contents) )
  {
    foreach ( $Contents["981"] as $Record )
    {
      $Link = array();
      foreach ( $Record as $Subrecord )
      {
        if ( isset($Subrecord["r"]) && $Subrecord["r"] != "" )  $Link["link"] = $Subrecord["r"];
        if ( isset($Subrecord["y"]) && $Subrecord["y"] != "" )  $Link["name"] = $Subrecord["y"];
      }
      $Links[] = $Link;
    }
  }

  // Prepare Buttons
  $Zugaenge = array();
  foreach ( $Links as $One )
  {
    $Exemplar = array();
    if ( $Format == "ebook" && isset($One["link"]) && $One["link"] != "" )
    {
      // Fall 27
      $Exemplar["case"]   = "27";
      $Exemplar["label1"] = (isset($One["name"]) && $One["name"] != "") ? $One["name"] : "Online";
      $Exemplar["link"]   = $One["link"];
      $Zugaenge[] = $Exemplar;
    }
    if ( $Format == "ejournal" && isset($One["link"]) && $One["link"] != "" )
    {
      // Fall 28
      $Exemplar["case"]   = "28";
      $Exemplar["label1"] = $CI->database->code2text("Online");
      $Exemplar["label2"] = ( Get980($Contents,"g") != "-" ) ? Get980($Contents,"g") : "";
      $Exemplar["link"]   = $One["link"] ;
      $Zugaenge[] = $Exemplar;
    }
  }

  if ( count($Zugaenge) == 0 )
  {
    $Exemplar = array();
    if ( $Format == "ebook" )
    {
      // Fall 27a
      $Exemplar["case"]   = "27a";
      $Exemplar["label1"] = $CI->database->code2text("SEEADDINFO");
      $Zugaenge[] = $Exemplar;
    }
    if ( $Format == "ejournal" )
    {
      // Fall 28a
      $Exemplar["case"]   = "28a";
      $Exemplar["label1"] = $CI->database->code2text("SEEADDINFO");
      $Zugaenge[] = $Exemplar;
    }
  }

  if ( $Format == "earticle" )
  {
    // Fall 29
    // Lese Eltern-Infos aus / Get MARC Parent
    $ParentData = ( isset($Medium["parents"][0]) ) ? $CI->internal_search("id:".$Medium["parents"][0]) : array();
    $ParentData = ( count($ParentData) > 0 && isset($ParentData["results"][$Medium["parents"][0]])) ? $ParentData["results"][$Medium["parents"][0]] : array();

    $Exemplar["case"]   = "29";
    $Exemplar["label1"] = $CI->database->code2text("SEEPUBLISHED");
    $Zugaenge[] = $Exemplar;
  }

  return ($Zugaenge);
}

function BestandExemplare($CI, $Leader, $Contents, $Medium, $PPN)
{
  // Parse MARC records
  $E980 = array();
  if ( array_key_exists("980", $Contents) )
  {
    $X = 0;
    foreach ( $Contents["980"] as $Record )
    {
      $One = array();
      foreach ( $Record as $Subrecord )
      {
        foreach ( $Subrecord as $Key => $Value )
        {
          // Only use first subfield and slip follow-ups inside one record.
          if (!isset($One[$Key])) $One[$Key] = $Value;
        }
      }
      // Use or create ExpID
      $ExpID = ( isset($One["b"]) ) ? $One["b"] : $X++;
      $E980[$ExpID] = $One;
    }
  }

  $ExemplarMARC = array();
  $ExemplarDAIA = array();

  $ExemplarMARC = array();
  foreach ($E980 as $ExpID => $One) 
  {
    $ExemplarMARC[$ExpID] = array();
    if ( isset($One["e"]) && $One["e"] == "a")
    {
      // Fall 1 - Geschäftsgang
      $ExemplarMARC["label1"]  = $CI->database->code2text("ORDERED");
      continue;
    }

    if ( isset($One["e"]) && in_array($One["e"], array("b","c","d","i","s","u") ) )
    {
      $ExemplarMARC[$ExpID]["action"]    = "shelve";
      $ExemplarMARC[$ExpID]["label1"] = ( isset($One["f"]) && $One["f"] != "" ) ? addslashes($CI->database->code2text(strtoupper($One["f"]))) : "";
      $ExemplarMARC[$ExpID]["label2"] = ( isset($One["d"]) ) ? addslashes($CI->database->code2text("Signature")) . " " . $One["d"] : "";
      $ExemplarMARC[$ExpID]["label3"] = addslashes($CI->database->code2text(strtoupper("Available")));
      continue;
    }

    if ( isset($One["e"]) && in_array($One["e"], array("g","o","z") ) )
    {
      $ExemplarMARC[$ExpID]["label1"] = ( isset($One["f"]) && $One["f"] != "" ) ? addslashes($CI->database->code2text(strtoupper($One["f"]))) : "";
      $ExemplarMARC[$ExpID]["label2"] = ( isset($One["d"]) ) ? addslashes($CI->database->code2text("Signature")) . " " . $One["d"] : "";
      $ExemplarMARC[$ExpID]["label3"] = addslashes($CI->database->code2text(strtoupper("NotAvailable")));
      continue;
    }
  }
  return $ExemplarMARC;
}

function BestandArtikel($CI, $Contents, $Medium)
{
  // Get MARC Parent
  $ParentMARC = ( isset($Medium["parents"][0]) ) ? $CI->internal_search("id:".$Medium["parents"][0]) : array();

  $ParentLeader = ( count($ParentMARC) > 0 && isset($ParentMARC["results"][$Medium["parents"][0]]["leader"])) ? $ParentMARC["results"][$Medium["parents"][0]]["leader"] : "";

  $ParentContents = ( count($ParentMARC) > 0 && isset($ParentMARC["results"][$Medium["parents"][0]]["contents"])) ? $ParentMARC["results"][$Medium["parents"][0]]["contents"] : array();

  // Collect Parent 980
  $E980 = array();
  if ( array_key_exists("980", $ParentContents) )
  {
    $X = 0;
    foreach ( $ParentContents["980"] as $Record )
    {
      $One = array();
      foreach ( $Record as $Subrecord )
      {
        foreach ( $Subrecord as $Key => $Value )
        {
          // Only use first subfield and slip follow-ups inside one record.
          if (!isset($One[$Key])) $One[$Key] = $Value;
        }
      }
      // Use or create ExpID
      $ExpID = ( isset($One["b"]) ) ? $One["b"] : $X++;
      $E980[$ExpID] = $One;
    }
  }

  $Artikels = array();
  if ( substr($ParentLeader,7,1) == "s" )
  {
    foreach ($E980 as $ExpID => $One) 
    {
      $Artikels[$ExpID] = array();
      if ( isset($One["f"]) && strtolower(substr($One["f"],0,5)) == "zs-fh" )
      {
        // Fall 23 - Artikel aus Zeitschrift Freihand
        $Artikels[$ExpID]["case"]    = "23";
        $Artikels[$ExpID]["action"]  = "mailorder";
        $Artikels[$ExpID]["form"]    = "journal";
        $Artikels[$ExpID]["data1"]   = Get773gq($Contents);
        $Artikels[$ExpID]["label1"]  = ( isset($One["f"]) ) ? $One["f"] : "";
        $Artikels[$ExpID]["label2"]  = ( isset($One["d"]) ) ? $CI->database->code2text("SIGNATURE") . " " . $One["d"] : "";
        $Artikels[$ExpID]["label3"]  = $CI->database->code2text("REFERENCECOLLECTION");
        $Artikels[$ExpID]["label4"]  = ( isset($One["g"]) ) ? $One["g"] : "";
        $Artikels[$ExpID]["label5"]  = ( isset($One["k"]) ) ? $One["k"] : "";
        $Artikels[$ExpID]["remark0"] = $CI->database->code2text("NOTE");
        $Artikels[$ExpID]["remark1"] = $CI->database->code2text("EXAMPLES10ONSHELVE");
        $Artikels[$ExpID]["remark2"] = $CI->database->code2text("ONLYMAGAZINEORDERS");
      }
      else
      {
        if ( isset($One["f"]) && strtolower(substr($One["f"],0,7)) == "magazin" )
        {
          // Fall 24 - Artikel aus Zeitschrift Magazin
          $Artikels[$ExpID]["case"]   = "24";
          $Artikels[$ExpID]["action"] = "mailorder";
          $Artikels[$ExpID]["form"]   = "journal";
          $Artikels[$ExpID]["data1"]  = Get773gq($Contents);
          $Artikels[$ExpID]["label1"] = $CI->database->code2text("MAGAZINE");
          $Artikels[$ExpID]["label2"] = ( isset($One["d"]) ) ? $CI->database->code2text("SIGNATURE") . " " . $One["d"] : "";
          $Artikels[$ExpID]["label3"] = $CI->database->code2text("ORDER");
          $Artikels[$ExpID]["label4"] = ( isset($One["g"]) ) ? $One["g"] : "";
          $Artikels[$ExpID]["label5"] = ( isset($One["k"]) ) ? $One["k"] : "";
        }
        else
        {
          // Fall 25 - Artikel aus Zeitschrift außerhalb UB
          $Artikels[$ExpID]["case"]    = "25";
          $Artikels[$ExpID]["label1"]  = ( isset($One["f"]) ) ? $One["f"] : "";
          $Artikels[$ExpID]["label2"]  = ( isset($One["d"]) ) ? $CI->database->code2text("SIGNATURE") . " " . $One["d"] : "";
          $Artikels[$ExpID]["label3"] = $CI->database->code2text("LOCKED");
        }
      }
    }
  }
  else
  {
    // Fall 26 - Enthaltenes Werk
    $Artikels[0] = array();
    $Artikels[0]["case"]   = "26";
    $Artikels[0]["label1"] = $CI->database->code2text("SEEPUBLISHED");
  }
  return $Artikels;
}

function GetRelatedPubs($CI, $T, $PPN, $Modus)
{
  // Modus
  // 1: Mehrbändige Werke
  // 2: Schriftenreihen
  
  $RelatedPubs = array();
  $PPNLink = $CI->internal_search("ppnlink:".$PPN);
  if ( ! isset($PPNLink["results"]) ) return ($RelatedPubs);

  //$CI->printArray2Screen($PPNLink);

  $PPNStg = json_encode(array_keys($PPNLink["results"]));

  foreach ( $PPNLink["results"] as $One )
  {
    $CI->contents = $One["contents"];
    $Pretty = $T->SetContents("preview");  

    $Title = "";
    if ( $Modus == 1 )
    {
      $Title = Get245npa($One["contents"]);
    }
    else
    {
      $Title = Get245an($One["contents"]);
    }

    $Publisher = "";
    $Publisher  = Get250a($One["contents"]);
    $Publisher  = ($Publisher != "" ) ? $Publisher . ", " . Get260c($One["contents"]) :  Get260c($One["contents"]);

    $RelatedPubs[$One["id"]] = array
    (
      "format"    => $One["format"],
      "cover"     => $One["cover"],
      "title"     => $Title,
      "publisher" => $Publisher
    );
  }
  return ($RelatedPubs);
}

function GetIncludedPubs($CI, $T, $PPN)
{
  // Zeitschriften mit Einzelheften

  $PPNLink = $CI->internal_search("ppnlink:".$PPN);
  if ( ! isset($PPNLink["results"]) ) return ($Pubs);

  //$CI->printArray2Screen($PPNLink);

  $PPNStg   = json_encode(array_keys($PPNLink["results"]));
  $Journals = array();
  $Articles = array();
  $Counter  = 0;
  foreach ( $PPNLink["results"] as $One )
  {
    $CI->contents = $One["contents"];
    $Pretty = $T->SetContents("preview");  

    if ( substr($One["leader"],7,1) == "m" || substr($One["leader"],7,1) == "d" )
    {
      $Counter++;
      $Title = Get245ab($One["contents"]);
      if ( $Title == "" )  $Title = Get490av($One["contents"]);
      if ( $Title == "" )  $Title = "Nr." . $Counter;

      $Journals[$One["id"]] = array
      (
        "format"    => $One["format"],
        "cover"     => $One["cover"],
        "title"     => $Title,
        "publisher" => Get260c($One["contents"])
      );
    }
    if ( substr($One["leader"],7,1) == "a" )
    {
      $Articles[$One["id"]] = array
      (
        "format"    => $One["format"],
        "cover"     => $One["cover"],
        "title"     => Get245ab($One["contents"]),
        "publisher" => Get260c($One["contents"])
      );
    }
  }
  return (array("articles" => $Articles, "journals" => $Journals ));
}

function GetDAIA($CI, $Medium, $PPN)
{
  if ( isset($_SESSION["interfaces"]["lbs"]) && $_SESSION["interfaces"]["lbs"] == "1" )
  {
    // Local storage (ILN)
    $DAIA = $CI->GetLBS($PPN);
  }

  $Exemplars = array();
  if ( isset($DAIA["document"]) )
  {
    foreach ( $DAIA["document"] as $Dok )
    {
      if ( isset($Dok["item"]) )
      {
        foreach ( $Dok["item"] as $Exp )
        {
          $Services = array();
          $ExpID = explode(":",$Exp["id"])[3];

          if ( isset($Exp["available"]) )
          {
            foreach ($Exp["available"] as $One )
            {
              $Services[$One["service"]]  = true;
            }
          }
          if ( isset($Exp["unavailable"]) )
          {
            foreach ($Exp["unavailable"] as $One )
            {
              $Services[$One["service"]]  = false;
            }
          }

          if ( isset($Services["loan"]) && $Services["loan"] == true && isset($Exp["available"][0]["href"]) && $Exp["available"][0]["href"] != "" )
          {
            // Sofort verfügbar für Magazin-Ausleihe
            $Exemplars[$ExpID] = array
            (
              "action"  => "order",
            );
          }

          if ( isset($Services["presentation"]) && $Services["presentation"] == true && ! isset($Exp["available"][0]["href"]) )
          {
            // Freihand
            $Exemplars[$ExpID] = array
            (
              "action" => "shelve",
            );
          }

          if ( isset($Services["loan"]) && $Services["loan"] == false && isset($Exp["unavailable"][0]["href"]) && $Exp["unavailable"][0]["href"] != "" )
          {
            // Vorbestellbar für Magazin-Ausleihe
            $Datum = (isset($Exp["unavailable"][0]["expected"])) ? strtolower(trim($Exp["unavailable"][0]["expected"])) : "-";
            if ( $Datum != "unknown" && $Datum != "-") $Datum = date("d.m.Y", strtotime($Datum));

            $Exemplars[$ExpID] = array
            (
              "action" => "reservation",
              "date" => $Datum,
              "queue" => (isset($Exp["unavailable"][0]["queue"])) ? strtolower(trim($Exp["unavailable"][0]["queue"])) : "0"
            );
          }
        }
      }
    }
  }
  return ($Exemplars);
}

function Get008($Contents)
{
  if ( array_key_exists("008", $Contents) )
  {
    return $Contents["008"];
  }
  else
  {
    return "-";
  }  
}

function Get245ab($Contents)
{
  $Titel = "";
  if ( array_key_exists("245", $Contents) )
  {
    foreach ( $Contents["245"] as $Record )
    {
      foreach ( $Record as $Subrecord )
      {
        foreach ( $Subrecord as $Key => $Value )
        {
          if ( $Key == "a" )    $Titel .= ($Titel != "" ) ? " | " . $Value : $Value;
          if ( $Key == "b" )    $Titel .= ($Titel != "" ) ? " : " . $Value : $Value;
        }
      }
    }
  }
  return ($Titel);  
}

function Get245an($Contents)
{
  $Titel = "";
  if ( array_key_exists("245", $Contents) )
  {
    foreach ( $Contents["245"] as $Record )
    {
      foreach ( $Record as $Subrecord )
      {
        foreach ( $Subrecord as $Key => $Value )
        {
          if ( $Key == "a" )    $Titel .= ($Titel != "" ) ? " | " . $Value : $Value;
          if ( $Key == "n" )    $Titel .= ($Titel != "" ) ? " : " . $Value : $Value;
        }
      }
    }
  }
  return ($Titel);  
}

function Get245npa($Contents)
{
  $Titel = "";
  $A = "";
  if ( array_key_exists("245", $Contents) )
  {
    foreach ( $Contents["245"] as $Record )
    {
      foreach ( $Record as $Subrecord )
      {
        foreach ( $Subrecord as $Key => $Value )
        {
          if ( $Key == "n" )    $Titel .= ($Titel != "" ) ? " | " . $Value : $Value;
          if ( $Key == "p" )    $Titel .= ($Titel != "" ) ? ", " . $Value : $Value;
          if ( $Key == "a" )    $A = $Value;
        }
      }
    }
  }

  if ( $Titel == "" && $A != "" ) $Titel = $A;

  return ($Titel);  
}

function Get250a($Contents)
{
  if ( array_key_exists("250", $Contents) )
  {
    foreach ( $Contents["250"] as $Record )
    {
      foreach ( $Record as $Subrecord )
      {
        foreach ( $Subrecord as $Key => $Value )
        {
          if ( $Key == "a" )    return ($Value);
        }
      }
    }
  }
  return ("");  
}

function Get260c($Contents)
{
  $Jahr = "";
  if ( array_key_exists("260", $Contents) )
  {
    foreach ( $Contents["260"] as $Record )
    {
      foreach ( $Record as $Subrecord )
      {
        foreach ( $Subrecord as $Key => $Value )
        {
          if ( $Key == "c" )    $Jahr .= ($Jahr != "" ) ? " | " . $Value : $Value;
        }
      }
    }
  }
  return ($Jahr);  
}

function Get490av($Contents)
{
  $Titel = "";
  if ( array_key_exists("490", $Contents) )
  {
    foreach ( $Contents["490"] as $Record )
    {
      foreach ( $Record as $Subrecord )
      {
        foreach ( $Subrecord as $Key => $Value )
        {
          if ( $Key == "a" )    $Titel .= ($Titel != "" ) ? " | " . $Value : $Value;
          if ( $Key == "v" )    $Titel .= ($Titel != "" ) ? " ; " . $Value : $Value;
        }
      }
    }
  }
  return ($Titel);  
}

function Get773gq($Contents)
{
  if ( array_key_exists("773", $Contents) )
  {
    foreach ( $Contents["773"] as $Record )
    {
      foreach ( $Record as $Subrecord )
      {
        foreach ( $Subrecord as $Key => $Value )
        {
          if ( $Key == "g" )
          {
            return $Value;
          }
          if ( $Key == "q" )
          {
            return $Value;
          }

        }
      }
    }
  }
  return "-";
}

function Get856($Area, $CI)
{
  $ExemplarOnline = array();

  foreach ( $Area as $Record )
  {
    foreach ( $Record as $Subrecord )
    {
      if ( isset($Subrecord["u"]) && $Subrecord["u"] != "" )  $Link["link"] = $Subrecord["u"];
      // if ( isset($Subrecord["y"]) && $Subrecord["y"] != "" )  $Link["label1"] = $Subrecord["y"];
      // if ( isset($Subrecord["3"]) && $Subrecord["3"] != "" )  $Link["label1"] = $Subrecord["3"];
      $Link["label1"] = $CI->database->code2text("Online");
    }
    $ExemplarOnline[] = $Link;
  }
  return $ExemplarOnline;
}

function Get980($Contents,$Subfield)
{
  if ( array_key_exists("980", $Contents) )
  {
    foreach ( $Contents["980"] as $Record )
    {
      foreach ( $Record as $Subrecord )
      {
        foreach ( $Subrecord as $Key => $Value )
        {
          if ( $Key == $Subfield )
          {
            return $Value;
          }
        }
      }
    }
  }
  return "-";
}

function Get980k($Contents)
{
  $Lizenzen = array();
  if ( array_key_exists("980", $Contents) )
  {
    foreach ( $Contents["980"] as $Record )
    {
      foreach ( $Record as $Subrecord )
      {
        foreach ( $Subrecord as $Key => $Value )
        {
          if ( $Key == "k" )
          {
            $Lizenzen[] = prepareStr($Value);
          }
        }
      }
    }
  }
  return ($Lizenzen);  
}

function prepareStr($Str)
{
  $Str = str_ireplace("<","%lt%",$Str);
  $Str = str_ireplace(">","%gt%",$Str);
  return $Str;
}

?>