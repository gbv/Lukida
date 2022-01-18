<?php

// Initialize vars
$Exemplars     = array();
$ButtonSize    = "col-xs-12 col-sm-6";
$LineLength    = 60;

//*******************************
//********* S T A R T  **********
//*******************************

// Bibliotheks eigener-Bestand 
if ( $this->isOwner() || $this->ParentisOwner() )
{
  // Bibliotheks eigener-Bestand
  if ( $this->isOnlineNew() )
  {
    // Bibliotheks eigener Online Bestand
    $Exemplars[]   = OwnOnline($this->CI, $this->medium);
  }
  else
  {
    // Bibliotheks eigener Haptischer Bestand
    $Exemplars[] = OwnHaptic($this->CI, $this->medium);
  }
  $Exemplars   = array_merge($Exemplars, $this->getMulti(true));
}
else
{
  // Fremdbestand
  if ( $this->isOnlineNew() )
  {
    // Online Fremdbestand
    $Exemplars[] = OtherOnline($this->CI, $this->medium);
  }
  else
  {
    // Haptischer Fremdbestand
    $Exemplars[] = OtherHaptic($this->CI, $this->medium);
  }

}

//*************************************
//***** S T A R T   O U T P U T *******
//*************************************

// $this->CI->printArray2Screen($this->medium);
// $this->CI->printArray2Screen($this->medium["collection"]);
// $this->CI->printArray2Screen($this->medium["collection_details"]);
// $this->CI->printArray2Screen($Exemplars);
// $this->CI->printArray2Screen($this->catalogues);
// $this->CI->printArray2Screen($_SESSION["iln"]);
// $this->CI->printArray2Screen($this->CI->internal_linkresolver("236452940"));

$Output .= $this->OutPutButtons($Exemplars, $ButtonSize, $LineLength);


//*************************************
//******** F U N C T I O N S **********
//*************************************

// PPN:          $Medium["id"];
// Format:       $Medium["format"];
// Leader:       $Medium["format"];
// MARC:         $Medium["contents"];
// Parents:      $Medium["parents"];
// Collections:  $Medium["collection"] bzw. $Medium["collection_details"];
// Alle Prettys: $Medium["collection"]

function OwnOnline($CI, $Medium)
{
  $Exemplare = array();
  $Lizenzen  = array();

  // eBooks und eJournals
  if ( $Medium["format"] == "book" || $Medium["format"] == "journal" )
  {
    $M981rs = $CI->record_format->GetMARCSubfield($Medium["contents"], "981", "r");
    if ( count($M981rs) )
    {
      foreach ( $M981rs as $M981r )
      {
        $Exemplar = array(
                          "case"   => ( $Medium["format"] == "book" ) ? "MO-1a" : "MO-2a",
                          "link"   => $M981r,
                          "type"   => "external",
                          "label1" => $CI->database->code2text("ONLINEACCESS"),
                          "label2" => "<small>" . $CI->record_format->getHost($M981r) . "</small>"
                         );
        $Exemplare[] = $Exemplar;
      }
      $Lizenzen = $CI->record_format->GetMARCSubfield($Medium["contents"], "980", "k");
    }
    else
    {
      $M856us = $CI->record_format->GetMARCSubfield($Medium["contents"], "856", "u");
      foreach ( $M856us as $M856u )
      {
        $Exemplar = array(
                          "case"   => ( $Medium["format"] == "book" ) ? "MO-1b" : "MO-2b",
                          "link"   => $M856u,
                          "type"   => "external",
                          "label1" => $CI->database->code2text("ACCESSCHARGEPOSSIBLE"),
                          "label2" => "<small>" . $CI->record_format->getHost($M856u) . "</small>"
                         );
        $Exemplare[] = $Exemplar;
      }
    }
  }

  // eArticles
  if ( $Medium["format"] == "article" )
  {
    // Keine Schleife über 981 bei earticle
    $M981r = $CI->record_format->GetMARCSubfieldFirstString($Medium["contents"], "981", "r");
    if ( $M981r != "" )
    {
      $Exemplar = array(
                        "case"   => "MO-3a",
                        "link"   => $M981r,
                        "type"   => "external",
                        "label1" => $CI->database->code2text("ONLINEACCESS"),
                        "label2" => "<small>" . $CI->record_format->getHost($M981r) . "</small>"
                       );
      $Exemplare[] = $Exemplar;
    }
    else
    {
      $M856us = $CI->record_format->GetMARCSubfield($Medium["contents"], "856", "u");
      foreach ( $M856us as $M856u )
      {
        $Exemplar = array(
                          "case"   => "MO-3b",
                          "link"   => $M856u,
                          "type"   => "external",
                          "label1" => $CI->database->code2text("ONLINEACCESS"),
                          "label2" => "<small>" . $CI->record_format->getHost($M856u) . "</small>"
                         );
        $Exemplare[] = $Exemplar;
      }

      if ( !count($M856us) )
      {
        $Exemplar = array(
                          "case"   => "MO-3c",
                          "label1" => $CI->database->code2text("SEEPUBLISHED")
                         );
        $Exemplare[] = $Exemplar;
      }
    }
  }

  return array("label"    => $CI->database->code2text("Online"),
               "rembef"   => array(),
               "data"     => $Exemplare,
               "remaft"   => array($CI->database->code2text("license") => $Lizenzen));
}

function OwnHaptic($CI, $Medium)
{
  // $CI->printArray2Screen($Medium);
  $Items     = $CI->GetCombinedItems($Medium["id"]);
  // $CI->printArray2Screen($Items);
  $Exemplare = array();
  foreach ( $Items as $EPN => $One ) 
  {
    // $CI->printArray2Screen($One);
    $Exemplar = array();
     
    // Item main properties
    $Sig  = (isset($One["d"])  && trim($One["d"])  != "" )                                                 ? trim(explode("|",$One["d"])[0])  : "";
    $ALI  = (isset($One["e"])  && trim($One["e"])  != "" )                                                 ? trim($One["e"])                  : "";
    $Loc  = (isset($One["f"])  && trim($One["f"])  != "" )                                                 ? trim($One["f"])                  : "";
    $Vol  = (isset($One["g"])  && trim($One["g"])  != "" )                                                 ? trim($One["g"])                  : "";
    $Com  = (isset($One["k"])  && trim($One["k"])  != "" )                                                 ? trim($One["k"])                  : "";
    $Com2 = (isset($One["l"])  && trim($One["l"])  != "" )                                                 ? trim($One["l"])                  : "";
    $Y    = (isset($One["y"])  && trim($One["y"])  != "" )                                                 ? trim($One["y"])                  : "";
    $DAIA = (isset($One["id"]) && trim($One["id"]) != "" )                                                 ? true                             : false;
    $LOAN = ($DAIA && isset($One["loan"]))                                                                 ? $One["loan"]                     : false;
    $PRES = ($DAIA && isset($One["presentation"]))                                                         ? $One["presentation"]             : false;
    $LABL = ($DAIA && isset($One["label"]))                                                                ? $One["label"]                    : "";
    $STOR = ($DAIA && isset($One["storage"]))                                                              ? $One["storage"]                  : "";
    $STID = ($DAIA && isset($One["storageid"]))                                                            ? $One["storageid"]                : "";
    $PID  = ($DAIA && isset($One["id"]))                                                                   ? $One["id"]                       : "";
    $DEP  = ($DAIA && isset($One["department"]))                                                           ? $One["department"]               : "";
    $DID  = ($DAIA && isset($One["departmentid"]))                                                         ? $One["departmentid"]             : "";
    $EXPD = ($DAIA && isset($One["loanitems"][0]["expected"]) && $One["loanitems"][0]["expected"] != "-" ) ? $One["loanitems"][0]["expected"] : "";
    $QUEU = ($DAIA && isset($One["loanitems"][0]["queue"])    && $One["loanitems"][0]["queue"]    != "-" ) ? $One["loanitems"][0]["queue"]    : "";

    $Exemplar = array(
                      "case"   => "MH-0", 
                      "action" => "shelve",
                      "label1" => $STOR,
                      "label2" => $Sig,
                      "label3" => $CI->database->code2text("AVAILABLE"),
                      "main1"  => $Vol,
                      "note1"  => $Com,
                      "note2"  => $Com2
                    );
    $Exemplare[$EPN]    = $Exemplar;
  }
  
  return array("label"    => $CI->database->code2text("Exemplars"),
               "rembef"   => array(),
               "data"     => $Exemplare,
               "remaft"   => array());
}

function OtherOnline($CI, $Medium)
{
  // $CI->printArray2Screen($CI->record_format->Get856URLs($Medium["contents"]));

  $Exemplare = array();
  foreach ( $CI->record_format->Get856URLs($Medium["contents"]) as $One )
  {
    if ( $One["oa"] )
    {
      $Exemplar = array
      (
        "case"   => "OO-1",
        "link"   => $One["u"],
        "type"   => "external",
        "label1" => "<small>" . $CI->database->code2text("FREEAVAILABLE") . "</small>",
        "label2" => "<small>" . $CI->record_format->getHost($One["u"]) . "</small>"
      );
    }
    else
    {
      $Exemplar = array
      (
        "case"   => "OO-2",
        "link"   => $One["u"],
        "type"   => "external",
        "label1" => "<small>" . $CI->database->code2text("ACCESSCHARGEPOSSIBLE") . "</small>",
        "label2" => "<small>" . $CI->record_format->getHost($One["u"]) . "</small>"
      );
    }
    $Exemplare[] = $Exemplar;
  }

  if ( count($Exemplare) == 0 )
  {
    $Links[] = array
    (
      "case"   => "OO-3",
      "label1" => $CI->database->code2text("NOTONSTOCK")
    );
  }

  return array("label"    => $CI->database->code2text("Online"),
               "rembef"   => array(),
               "data"     => $Exemplare,
               "remaft"   => array());
}

function OtherHaptic($CI, $Medium)
{
  $Exemplar = array();
  if ( isset($_SESSION["config_general"]["interlibraryloan"]) )
  {
    foreach($_SESSION["config_general"]["interlibraryloan"] as $cat => $db )
    {
      if ( in_array($cat, $Medium["collection_details"]) )  $CatDB = $db;
    }
    if ( isset($CatDB) && $CatDB != "" )
    {
      $FLPPN = ( strpos("GVK,OLC,OEV,KXP",substr($Medium["id"],0,3)) !== false && preg_match("/\d{4,}/",$Medium["id"],$firstdigit) ) ? 
                                                substr($Medium["id"], strpos($Medium["id"], $firstdigit[0])) : $Medium["id"];
      $Exemplar[] = array
      (
        "case"   => "OH-1",
        "type"   => "external",
        "link"   => $CatDB . "/CMD?ACT=SRCHA&IKT=12&TRM=" . $FLPPN . "&LNG=" . ( $_SESSION["language"] == "ger" ? "DU" : "EN" ),
        "label1" => $CI->database->code2text("INTERLOAN")
      );
    }
  }

  return array("label"    => $CI->database->code2text("Exemplars"),
               "rembef"   => array(),
               "data"     => $Exemplar,
               "remaft"   => array());
}

?>