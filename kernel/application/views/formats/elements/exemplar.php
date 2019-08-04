<?php

// General code

// Initialize vars
$Exemplare     = array();
$Zugaenge      = array();
$Lizenzen      = array();
$RelatedPubs   = array();
$IncludedMedia = array();
$IncJournals   = array();
$IncArticles   = array();
$Interloan     = array();
$LinkResolver  = true;

//***************************************
//********* M A I N - P A P  1 **********
//***************************************
if ( substr($this->medium["leader"],7,1) == "m" && substr($this->medium["leader"],19,1) == "a" )
{
  // Mehrbändige Werke
   $RelatedPubs = GetRelatedPubs($this->CI,$this,$this->PPN,1);
}

if ( substr($this->medium["leader"],7,1) == "s" && substr(Get008($this->contents),21,1) == "m" )
{
  // Schriftenreihen
   $RelatedPubs = GetRelatedPubs($this->CI,$this,$this->PPN,2);
}

if ( substr($this->medium["leader"],7,1) == "s" && in_array(substr(Get008($this->contents),21,1), array("p","n")) )
{
  // Zeitschriften mit Einzelheften
   $IncludedPubs = GetIncludedPubs($this->CI,$this,$this->PPN);
  $IncJournals  = $IncludedPubs["journals"];
  $IncArticles  = $IncludedPubs["articles"];
}

if ( Get951b($this->contents) )
{
  // Enthaltene Werke
  $IncludedMedia = GetRelatedPubs($this->CI,$this,$this->PPN,1);
}

//***************************************
//********* M A I N - P A P  2 **********
//***************************************

// Bibliotheks eigener-Bestand ?
if ( isset($this->contents[912]) && isset($_SESSION["iln"]) && $_SESSION["iln"] != "" && ( in_array( "GBV_ILN_".$_SESSION["iln"], $this->catalogues) ) )
{
  // Bibliotheks eigener-Bestand
  if ( ( substr($this->medium["leader"],6,2) == "ma"
  || substr($this->medium["leader"],6,2) == "mm"
  || substr($this->medium["leader"],6,2) == "ms" 
  || substr($this->medium["leader"],6,2) == "aa" 
  || substr($this->medium["leader"],6,2) == "am" 
  || substr($this->medium["leader"],6,2) == "as" )
  && substr(Get007($this->contents),0,2) == "cr" )
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
      $LinkResolver = false;
    }
    else
    {
      if ( Get980($this->contents,"d") != "-" )
      {
        // Haptische Exemplartypen im Bestand
        $Exemplare = BestandExemplare($this->CI,$this->leader,$this->contents,$this->medium,$this->PPN);
      }
      else
      {
        if ( substr($this->medium["leader"],7,1) == "a" )
        {
          // Haptische Artikel im Bestand
          $Exemplare = BestandArtikel($this->CI,$this->contents,$this->medium);
        }
      }
    }
  }
}
else
{
  // Fremdbestand
  if ( ( ( substr($this->medium["leader"],6,2) == "ma"
  || substr($this->medium["leader"],6,2) == "mm"
  || substr($this->medium["leader"],6,2) == "ms"
  || substr($this->medium["leader"],6,2) == "aa"
  || substr($this->medium["leader"],6,2) == "am"
  || substr($this->medium["leader"],6,2) == "as" )
  && substr(Get007($this->contents),0,2) == "cr" ) 
  || ( in_array("Gutenberg", $this->collection) ) )
  {
    // Online Fremdbestand
    if ( array_key_exists("856", $this->contents) )
    {
      // Prio 2: use 856 records
      $Zugaenge = Get856($this->contents["856"], $this->CI);
    }
  }
  else
  {
    // Haptischer Fremdbestand

    // Determine database
    if ( isset($_SESSION["config_general"]["interlibraryloan"]) )
    {
      foreach($_SESSION["config_general"]["interlibraryloan"] as $cat => $db )
      {
        if ( in_array($cat, $this->catalogues) )  $CatDB = $db;
      }
      if ( isset($CatDB) && $CatDB != "" )
      {
        $FLPPN = (substr($this->PPN,0,4) == "OEVK") ? substr($this->PPN,4) : $this->PPN;
        $Interloan[] = array
        (
          "link"   => "http://gso.gbv.de/DB=" . $CatDB . "/PPNSET?PPN=" . $FLPPN,
          "label1" => $this->CI->database->code2text("INTERLOAN")
        );
      }
    }
  }

}

//*************************************
//***** S T A R T   O U T P U T *******
//*************************************

/*
$this->CI->printArray2Screen(array(
"Zugänge"                  => $Zugaenge,
"Exemplare"                => $Exemplare,
"Fernleihe"                => $Interloan,
"Lizenzen"                 => $Lizenzen,
"Enthaltene Werke"         => $IncludedMedia,
"Zugehörige Publikationen" => $RelatedPubs,
"Zugehörige Einzelhefte"   => $IncJournals,
"Zugehörige Artikel"       => $IncArticles
));
*/

// Set javascript variable
$LinksResolved = array();
if ( $LinkResolver )
{
  if ( ($LinksResolved=$this->CI->internal_linkresolver($this->PPN)) != "" )
  {
    if ( isset($LinksResolved["status"]) && $LinksResolved["status"] == 1 )
    {
      $LinkResolver  = false;
    }
    if ( isset($LinksResolved["links"]) )
    {
      if ( $LinksResolved["links"] == "[]" || $LinksResolved["links"] == "" || count($LinksResolved["links"]) == 0 )
      {
        $LinksResolved = array();
      }
      else
      {
        $LinksResolved = (array) json_decode($LinksResolved["links"],true);
      }
    }
  }
}
if ( $LinkResolver )
{
  $Output .= "<script>linkresolver=true;linkresolverclass='col-xs-12 col-sm-6 col-md-4';</script>";
}
else
{
  $Output .= "<script>linkresolver=false;</script>";
}

// Create Access Buttons for secure links
if ( count($Zugaenge) > 0 || count($LinksResolved) > 0 )
{
  $BtnClass     = "col-xs-12 col-sm-6 col-md-4 btn btn-default btn-exemplar";
  $EmptyClass   = "col-xs-12 col-sm-6 col-md-4 btn btn-default empty-exemplar";
  $Output .= "<div>" . $this->CI->database->code2text("ACCESS") . "</div>";
  $Output .= "<div class='container-fluid'><div class='row'>";

  // Generate Buttons
  foreach ( $Zugaenge as $EPN => $Link )
  {
    $Exams  = array_unique($Zugaenge, SORT_REGULAR);

    $Action = (isset($Link["link"])) ? "onclick='$.openLink(\"" . $Link["link"] . "\")'" : "";
    $Class  = (isset($Link["link"])) ? $BtnClass : $EmptyClass;
    $Output .= "<button " . $Action . " class='" . $Class . "'>";
    $Output .= (isset($Link["label1"])) ?  addslashes($Link["label1"]) : "";
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

  if ( count($LinksResolved) > 0 )
  {
    foreach ( $LinksResolved as $Solver => $Lk )
    {
      $Output .= "<button onclick='$.openLink(\"" . $Lk . "\")' class='". $BtnClass . "'>" . $this->CI->database->code2text("FULLTEXT") . " (" . $this->CI->database->code2text( $Solver)  . ")</button>";
    }
  }
  elseif ( $LinkResolver )
  {
    // LinkResolver Spaceholder for async js
    $Output .= "<div id='linkresolver_" . $this->dlgid . "'></div>";
  }
  $Output .= "</div></div>";
}

if ( count($Zugaenge) == 0 && count($LinksResolved) == 0 && $LinkResolver )
{
  $Output .= "<div id='linkresolvercontainer_" . $this->dlgid . "'></div>";
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
    ksort($Exams);
    $_SESSION["exemplar"][$this->PPN][$EPN] = $Exams;
    $ILN    = (isset($_SESSION["iln"])) ? $_SESSION["iln"] : "";
    $Action = (isset($Exemplar["action"])) ? "onclick='$." . $Exemplar["action"] . "(\"" . $ILN . "\",\"" . $this->PPN . "\",\"" . $EPN . "\"," . json_encode($Exams,JSON_HEX_TAG) . ")'" : "";
    $Class  = (isset($Exemplar["action"])) ? $BtnClass : $EmptyClass;
    $Output .= "<button " . $Action . " class='" . $Class . "'>";
    $Output .= (isset($Exemplar["label1"])) ?  addslashes($Exemplar["label1"]) : "";
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
    $Output .= $Lizenz . "<br />";
  }
  $Output .= "</small>";
  $Output .= "</div>";
}

// Create Included Media
if ( count($IncludedMedia) > 0 )
{
  $BtnClass = "col-xs-12 col-sm-6 btn btn btn-default publication";
  if ( count($Exemplare) > 0 || count($Lizenzen) > 0 )  $Output .= "<div class='space_buttons'></div>";

  $Output .= "<div>" . $this->CI->database->code2text("INCLUDEDMEDIA") . "</div>";
  $Output .= "<div class='container-fluid'><div id='includedmediacontent_" . $this->dlgid . "' class='row'>";

  // Generate Buttons
  foreach ( $IncludedMedia as $PPN => $Exemplar )
  {
    $Action = "onclick='$.open_fullview(\"" . $PPN . "\"," . json_encode(array_keys($IncludedMedia)) . ",\"publications\")'";
    $Output .= "<button " . $Action . " class='" . $BtnClass . "'>";
    $Output .= "<div id='related_" . $PPN . "'>";
    $Output .= "<table><tr><td data-toggle='tooltip' title='" . $this->CI->database->code2text($Exemplar["format"]) . "' class='publication-icon'>";
    $Output .= "<span class='gbvicon'>" . $Exemplar["cover"] . "</span>";
    $Output .= "</td><td id='title'>";
    $Output .= $this->trim_text($Exemplar["title"],60);
    $Output .= "<br /><small id='date'>" . $this->trim_text($Exemplar["publisher"],50) . "</small>";
    $Output .= "</td></tr></table></div>";
    $Output .= "</button>";
  }

  // Close div
  $Output .= "</div></div>";
}

// Create Related Publications
if ( count($RelatedPubs) > 0 )
{
  $BtnClass = "col-xs-12 col-sm-6 btn btn btn-default publication";
  if ( count($Zugaenge) > 0 || count($Exemplare) > 0 || count($Lizenzen) > 0 )  $Output .= "<div class='space_buttons'></div>";

  $Output .= "<div>" . $this->CI->database->code2text("RELATEDPUBLICATIONS") . "</div>";
  $Output .= "<div class='container-fluid'><div id='relatedpubscontent_" . $this->dlgid . "' class='row'>";

  // Generate Buttons
  foreach ( $RelatedPubs as $PPN => $Exemplar )
  {
    $Action = "onclick='$.open_fullview(\"" . $PPN . "\"," . json_encode(array_keys($RelatedPubs)) . ",\"publications\")'";
    $Output .= "<button " . $Action . " class='" . $BtnClass . "'>";
    $Output .= "<div id='related_" . $PPN . "'>";
    $Output .= "<table><tr><td data-toggle='tooltip' title='" . $this->CI->database->code2text($Exemplar["format"]) . "' class='publication-icon'>";
    $Output .= "<span class='gbvicon'>" . $Exemplar["cover"] . "</span>";
    $Output .= "</td><td id='title'>";
    $Output .= $this->trim_text($Exemplar["title"],60);
    $Output .= "<br /><small id='date'>" . $this->trim_text($Exemplar["publisher"],50) . "</small>";
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
  $Output .= "<div class='container-fluid'><div id='relatedjournalscontent_" . $this->dlgid . "' class='row'>";

  // Generate Buttons
  foreach ( $IncJournals as $PPN => $Exemplar )
  {
    $Action = "onclick='$.open_fullview(\"" . $PPN . "\"," . json_encode(array_keys($IncJournals)) . ",\"publications\")'";
    $Output .= "<button " . $Action . " class='" . $BtnClass . "'>";
    $Output .= "<div id='related_" . $PPN . "'>";
    $Output .= "<table><tr><td data-toggle='tooltip' title='" . $this->CI->database->code2text($Exemplar["format"]) . "' class='publication-icon'>";
    $Output .= "<span class='gbvicon'>" . $Exemplar["cover"] . "</span>";
    $Output .= "</td><td id='title'>";
    $Output .= $this->trim_text($Exemplar["title"],60);
    $Output .= "<br /><small id='date'>" . $this->trim_text($Exemplar["publisher"],50) . "</small>";
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
  $Output .= "<div class='container-fluid'><div id='relatedarticlescontent_" . $this->dlgid . "' class='row'>";

  // Generate Buttons
  foreach ( $IncArticles as $PPN => $Exemplar )
  {
    $Action = "onclick='$.open_fullview(\"" . $PPN . "\"," . json_encode(array_keys($IncArticles)) . ",\"publications\")'";
    $Output .= "<button " . $Action . " class='" . $BtnClass . "'>";
    $Output .= "<div id='related_" . $PPN . "'>";
    $Output .= "<table><tr><td data-toggle='tooltip' title='" . $this->CI->database->code2text($Exemplar["format"]) . "' class='publication-icon'>";
    $Output .= "<span class='gbvicon'>" . $Exemplar["cover"] . "</span>";
    $Output .= "</td><td id='title'>";
    $Output .= $this->trim_text($Exemplar["title"],60);
    if ( isset($Exemplar["publisher"]) && $Exemplar["publisher"] != "" )
    {
      $Output .= "<br /><small id='date'>" . $this->trim_text($Exemplar["publisher"],50) . "</small>";
    }
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
      $Exemplar["label1"] = (isset($One["name"]) && $One["name"] != "") ? $One["name"] : "Online";
      $Exemplar["link"]   = $One["link"];
      $Zugaenge[] = $Exemplar;
    }
    if ( $Format == "ejournal" && isset($One["link"]) && $One["link"] != "" )
    {
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
      $Exemplar["label1"] = $CI->database->code2text("SEEADDINFO");
      $Zugaenge[] = $Exemplar;
    }
    if ( $Format == "ejournal" )
    {
      $Exemplar["label1"] = $CI->database->code2text("SEEADDINFO");
      $Zugaenge[] = $Exemplar;
    }
  }

  if ( $Format == "earticle" )
  {
    // Lese Eltern-Infos aus / Get MARC Parent
    $ParentData = ( isset($Medium["parents"][0]) ) ? $CI->internal_search("id",$Medium["parents"][0]) : array();
    $ParentData = ( count($ParentData) > 0 && isset($ParentData["results"][$Medium["parents"][0]])) ? $ParentData["results"][$Medium["parents"][0]] : array();

    $Exemplar["label1"] = $CI->database->code2text("SEEPUBLISHED");
    $Zugaenge[] = $Exemplar;
  }

  return ($Zugaenge);
}

function BestandExemplare($CI, $Leader, $Contents, $Medium, $PPN)
{
  // Parse MARC records
  $E980 = array();
  $X    = 0;
  if ( array_key_exists("980", $Contents) )
  {
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
  $ExemplarDAIA = ( isset($_SESSION["interfaces"]["lbs"]) && $_SESSION["interfaces"]["lbs"] == "1" ) ? GetDAIA($CI, $Medium, $PPN) : array();

  foreach ($E980 as $ExpID => $One)
  {
    $ExemplarMARC[$ExpID] = array();

    if ( isset($ExemplarDAIA[$ExpID] ) )
    {
      // DAIA Daten zum Exemplar vorhanden
      if ( isset($ExemplarDAIA[$ExpID]["services"]["loan"]) && $ExemplarDAIA[$ExpID]["services"]["loan"] == true )
      {
        // Beginn ausleihbare, verfuegbare Exemplare:
        if (isset($ExemplarDAIA[$ExpID]["action"]) && $ExemplarDAIA[$ExpID]["action"] == "order")
        {
          // ausleihbar, verfuegbar, Magazin (geschlossener Standort)
          // zu erkennen an: available loan + request-Link
          $ExemplarMARC[$ExpID]["action"]  = (isset($ExemplarDAIA[$ExpID]["action"]))  ? $ExemplarDAIA[$ExpID]["action"]  : "order";
          $ExemplarMARC[$ExpID]["id"]      = (isset($ExemplarDAIA[$ExpID]["id"]))      ? $ExemplarDAIA[$ExpID]["id"]      : "";
          $ExemplarMARC[$ExpID]["label1"]  = (isset($ExemplarDAIA[$ExpID]["storage"])) ? $ExemplarDAIA[$ExpID]["storage"] : "";
          $ExemplarMARC[$ExpID]["label2"]  = (isset($One["d"]) )                       ? $CI->database->code2text("SIGNATURE") . " " . $One["d"] : "";
          $ExemplarMARC[$ExpID]["label3"]  = $CI->database->code2text("ORDER") . " (" . $CI->database->code2text("MAGAZINE") . ")";
        }
        else
        {
          // ausleihbar, verfuegbar, Freihand (offener Standort)
          // zu erkennen an: available loan OHNE request-Link
          $ExemplarMARC[$ExpID]["label1"]  = (isset($ExemplarDAIA[$ExpID]["storage"])) ? $ExemplarDAIA[$ExpID]["storage"] : "";
          $ExemplarMARC[$ExpID]["label2"]  = (isset($One["d"]) ) ? $CI->database->code2text("SIGNATURE") . " " . $One["d"] : "";
          $ExemplarMARC[$ExpID]["label3"]  = $CI->database->code2text("LENDABLE") . " (" . $CI->database->code2text("SHELVE") . ")";
        }
      }
      else
      {
        // Beginn nicht fuer die Ausleihe verfuegbare Exemplare:
        if ( isset($ExemplarDAIA[$ExpID]["services"]["loan"]) && $ExemplarDAIA[$ExpID]["services"]["loan"] == false )
        {
          // Praesenzexemplare
          // zu erkennen an: unavailable loan + available presentation
          if ( isset($ExemplarDAIA[$ExpID]["services"]["presentation"]) && $ExemplarDAIA[$ExpID]["services"]["presentation"] == true )
          {
            // Praesenzexemplare im Magazin (geschlossener Standort)
            // zu erkennen an: unavailable loan + available presentation + request-Link
            if (isset($ExemplarDAIA[$ExpID]["action"]) && $ExemplarDAIA[$ExpID]["action"] == "order")
            {
              $ExemplarMARC[$ExpID]["action"]  = ( isset($ExemplarDAIA[$ExpID]["action"]) ) ? $ExemplarDAIA[$ExpID]["action"] : "order";
              $ExemplarMARC[$ExpID]["id"]      = (isset($ExemplarDAIA[$ExpID]["id"]))       ? $ExemplarDAIA[$ExpID]["id"] : "";
              $ExemplarMARC[$ExpID]["label1"]  = $CI->database->code2text("MAGAZINE");
              $ExemplarMARC[$ExpID]["label2"]  = $CI->database->code2text("REFERENCECOLLECTION");
              $ExemplarMARC[$ExpID]["label3"]  = $CI->database->code2text("ORDER");
            }
            else
            {
              // Praesenzexemplare Freihand (offener Standort)
              // zu erkennen an: unavailable loan + available presentation OHNE request-Link
              $ExemplarMARC[$ExpID]["label1"]  = ( isset($ExemplarDAIA[$ExpID]["storage"]) ) ? $ExemplarDAIA[$ExpID]["storage"] : "";
              $ExemplarMARC[$ExpID]["label2"]  = (isset($One["d"]) ) ? $CI->database->code2text("SIGNATURE") . " " . $One["d"] : "";
              $ExemplarMARC[$ExpID]["label3"]  = $CI->database->code2text("REFERENCECOLLECTION");
            }
          }
          else
          {
            if ( isset($ExemplarDAIA[$ExpID]["services"]["presentation"]) && $ExemplarDAIA[$ExpID]["services"]["presentation"] == false )
            {
              // Nicht verfuegbare Exemplare
              // zu erkennen an: unavailable loan + unavailable presentation
              if (isset($ExemplarDAIA[$ExpID]["action"]) && $ExemplarDAIA[$ExpID]["action"] == "reservation")
              {
                // ausgeliehene Exemplare
                // zu erkennen an: unavailable loan + unavailable presentation + reserve-Link
                $ExemplarMARC[$ExpID]["action"]  = ( isset($ExemplarDAIA[$ExpID]["action"]) ) ? $ExemplarDAIA[$ExpID]["action"] : "reservation";
                $ExemplarMARC[$ExpID]["id"]      = (isset($ExemplarDAIA[$ExpID]["id"]))       ? $ExemplarDAIA[$ExpID]["id"] : "";
                $ExemplarMARC[$ExpID]["label1"]  = $CI->database->code2text("RESERVATION");
                $ExemplarMARC[$ExpID]["label2"]  = (isset($One["d"]) ) ? $CI->database->code2text("SIGNATURE") . " " . $One["d"] : "";
                $ExemplarMARC[$ExpID]["label3"]  = (isset($ExemplarDAIA[$ExpID]["date"])) ? $ExemplarDAIA[$ExpID]["date"] : $CI->database->code2text("ORDER");
              }
              else
              {
                // grundsaetzlich nicht verfuegbare Exemplare, z. B. bestellt, in Bearbeitung
                // zu erkennen an: unavailable loan + unavailable presentation OHNE reserve-Link
                $ExemplarMARC[$ExpID]["label1"]  = ( isset($ExemplarDAIA[$ExpID]["storage"]) ) ? $ExemplarDAIA[$ExpID]["storage"] : "";
                $ExemplarMARC[$ExpID]["label2"]  = (isset($One["d"]) ) ? $CI->database->code2text("SIGNATURE") . " " . $One["d"] : "";
                $ExemplarMARC[$ExpID]["label3"]  = $CI->database->code2text("NOTAVAILABLE");
              }
            }
          }
        }
      }
    }
    else
    {
      // Anzeige ohne DAIA-Daten
      if ( isset($One["e"]) && $One["e"] == "a")
      {
        // Geschäftsgang
        $ExemplarMARC[$ExpID]["label1"]  = $CI->database->code2text("ORDERED");
        continue;
      }

      if ( isset($One["e"]) && $One["e"] == "b")
      {
        // Gesperrt
        $ExemplarMARC[$ExpID]["label1"] = ( isset($One["f"]) ) ? $One["f"] : "";
        $ExemplarMARC[$ExpID]["label2"] = ( isset($One["d"]) ) ? $CI->database->code2text("SIGNATURE") . " " . $One["d"] : "";
        $ExemplarMARC[$ExpID]["label3"] = $CI->database->code2text("LOCKEDLEND");
        continue;
      }

      if ( isset($One["e"]) && ( in_array( $One["e"], array("c","d","f","g","i","u") ) ) )
      {
        // Präsenzbestand
        $ExemplarMARC[$ExpID]["label1"]   = $CI->database->code2text("SHELVE");
        $ExemplarMARC[$ExpID]["label2"]   = ( isset($One["d"]) ) ? $CI->database->code2text("SIGNATURE") . " " . $One["d"] : "";

        if ( isset($One["k"]) && $One["k"] != "" )
        {
          $ExemplarMARC[$ExpID]["remark0"] = $CI->database->code2text("NOTES");
          $ExemplarMARC[$ExpID]["remark1"] = $One["k"];
        }
        continue;
      }

      if ( isset($One["e"]) && $One["e"] == "o")
      {
        // Verbrauchsmaterial
        $ExemplarMARC[$ExpID]["label1"] = ( isset($One["f"]) ) ? $One["f"] : "";
        $ExemplarMARC[$ExpID]["label2"] = ( isset($One["d"]) ) ? $CI->database->code2text("SIGNATURE") . " " . $One["d"] : "";
        $ExemplarMARC[$ExpID]["label3"] = ( isset($One["k"]) ) ? $One["k"] : "";
        continue;
      }

      if ( isset($One["k"]) && $One["k"] != "" )
      {
        $ExemplarMARC[$ExpID]["remark0"] = $CI->database->code2text("NOTES");
        $ExemplarMARC[$ExpID]["remark1"] = $One["k"];
      }
    }
  }
  return $ExemplarMARC;
}

function BestandArtikel($CI, $Contents, $Medium)
{
  // Get MARC Parent
  $ParentMARC     = ( isset($Medium["parents"][0]) ) ? $CI->internal_search("id",$Medium["parents"][0]) : array();
  $ParentLeader   = ( count($ParentMARC) > 0 && isset($ParentMARC["leader"])) ? $ParentMARC["leader"] : "";
  $ParentContents = ( count($ParentMARC) > 0 && isset($ParentMARC["contents"])) ? $ParentMARC["contents"] : array();

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
        // Artikel aus Zeitschrift Freihand
        $Artikels[$ExpID]["action"]  = "shelve";
        $Artikels[$ExpID]["form"]    = "journal";
        $Artikels[$ExpID]["data1"]   = Get773gq($Contents);
        $Artikels[$ExpID]["label1"]  = ( isset($One["f"]) ) ? $One["f"] : "";
        $Artikels[$ExpID]["label2"]  = ( isset($One["d"]) ) ? $CI->database->code2text("SIGNATURE") . " " . $One["d"] : "";
        $Artikels[$ExpID]["label3"]  = $CI->database->code2text("REFERENCECOLLECTION");
        $Artikels[$ExpID]["label4"]  = ( isset($One["g"]) ) ? $One["g"] : "";
        $Artikels[$ExpID]["label5"]  = ( isset($One["k"]) ) ? $One["k"] : "";
      }
      else
      {
        if ( isset($One["f"]) && strtolower(substr($One["f"],0,7)) == "magazin" )
        {
          // Artikel aus Zeitschrift Magazin
          $Artikels[$ExpID]["action"] = "shelve";
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
          // Artikel aus Zeitschrift außerhalb UB
          $Artikels[$ExpID]["label1"]  = ( isset($One["f"]) ) ? $One["f"] : "";
          $Artikels[$ExpID]["label2"]  = ( isset($One["d"]) ) ? $CI->database->code2text("SIGNATURE") . " " . $One["d"] : "";
          $Artikels[$ExpID]["label3"] = $CI->database->code2text("LOCKED");
        }
      }
    }
  }
  else
  {
    // Enthaltenes Werk
    $Artikels[0] = array();
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
  $PPNLink = $CI->internal_search("ppnlink",$PPN);
  if ( ! isset($PPNLink["results"]) ) return ($RelatedPubs);

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

  $PPNLink = $CI->internal_search("ppnlink",$PPN);
  if ( ! isset($PPNLink["results"]) ) return ($Pubs);

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
      "publisher" => Get952j($One["contents"])
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

          // DAIA 1 & 2
          $ExpID = (isset($Exp["temporary-hack-do-not-use"])) ? $Exp["temporary-hack-do-not-use"] : explode(":",$Exp["id"])[3];

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
            "action"  => "order"
            );
          }

          if ( isset($Services["presentation"]) && $Services["presentation"] == true && ! isset($Exp["available"][0]["href"]) )
          {
            // Freihand
            $Exemplars[$ExpID] = array
            (
            "action" => "shelve"
            );
          }

          if ( isset($Services["loan"]) && $Services["loan"] == false && isset($Exp["unavailable"][0]["href"]) && $Exp["unavailable"][0]["href"] != "" )
          {
            // Vorbestellbar für Magazin-Ausleihe
            $Datum = (isset($Exp["unavailable"][0]["expected"])) ? strtolower(trim($Exp["unavailable"][0]["expected"])) : "-";
            if ( $Datum != "unknown" && $Datum != "-")
            {
              $Datum = $CI->database->code2text("AvailableFrom") . " " . date("d.m.Y", strtotime($Datum));
            }
            else
            {
              $Datum = $CI->database->code2text("OrderedByUser");
            }

            $Exemplars[$ExpID] = array
            (
            "action"  => "reservation",
            "date"    => $Datum,
            "queue"   => (isset($Exp["unavailable"][0]["queue"])) ? strtolower(trim($Exp["unavailable"][0]["queue"])) : "0"
            );
          }

          // ID Parameter ergänzen
          if ( (isset($Exp["id"])) && $Exp["id"] != "" )
          {
            $Exemplars[$ExpID]["id"] = (isset($Exp["id"])) ? trim($Exp["id"]) : "";
          }

          // Storage Parameter ergänzen
          if ( (isset($Exp["storage"]["content"])) && $Exp["storage"]["content"] != "" )
          {
            $Exemplars[$ExpID]["storage"] = (isset($Exp["storage"]["content"])) ? trim($Exp["storage"]["content"]) : "";
          }

          // Label Parameter ergänzen
          if ( (isset($Exp["label"])) && $Exp["label"] != "" )
          {
            $Exemplars[$ExpID]["label"] = (isset($Exp["label"])) ? trim($Exp["label"]) : "";
          }

          // Services Parameter ergänzen
          if ( isset($Services) )
          {
            $Exemplars[$ExpID]["services"] = $Services;
          }
        }
      }
    }
  }
  return ($Exemplars);
}

function Get007($Contents)
{
  if ( array_key_exists("007", $Contents) )
  {
    return $Contents["007"];
  }
  else
  {
    return "-";
  }
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

function Get951b($Contents)
{
  if ( array_key_exists("951", $Contents) )
  {
    foreach ( $Contents["951"] as $Record )
    {
      foreach ( $Record as $Subrecord )
      {
        foreach ( $Subrecord as $Key => $Value )
        {
          if ( $Key == "b" && $Value == "j" )    return true;
        }
      }
    }
  }
  return false;
}

function Get952j($Contents)
{
  $Jahr = "";
  if ( array_key_exists("952", $Contents) )
  {
    foreach ( $Contents["952"] as $Record )
    {
      foreach ( $Record as $Subrecord )
      {
        foreach ( $Subrecord as $Key => $Value )
        {
          if ( $Key == "j" )    $Jahr .= ($Jahr != "" ) ? " | " . $Value : $Value;
        }
      }
    }
  }
  return ($Jahr);
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