<?php

// Exemplar-Algorithmen for ALL LIBRARIES

// Initialize vars
$BtnClass     = "col-xs-6 col-sm-4 col-md-3 btn btn-default btn-exemplar";
$EmptyClass   = "col-xs-6 col-sm-4 col-md-3 btn btn-default empty-exemplar";
$ExemplarAll  = array();

// Mail decision area
if ( $this->medium["online"] == 1 )
{
  // eMedia 
  if ( array_key_exists("981", $this->contents) )
  {
    // Prio 1: use 981 records
    $ExemplarAll = ExemplarOnline981($this->contents["981"]);
  }
  else
  {
    if ( array_key_exists("856", $this->contents) )
    {
      // Prio 2: use 856 records
      $ExemplarAll = ExemplarOnline856($this->contents["856"]);
    }
  }
}
else
{
  if ( isset($this->contents[912]) && isset($_SESSION["iln"]) && $_SESSION["iln"] != "" && ( in_array( "GBV_ILN_".$_SESSION["iln"], $this->catalogues) ) )
  {
    // Printmedia, This ILN-Library

    // Process DAIA Data if possible
    $ExemplarDAIA = array();
    if ( isset($_SESSION["interfaces"]["lbs"]) && $_SESSION["interfaces"]["lbs"] == "1" )
    {
      // Local storage (ILN)
      $DAIA = $this->CI->GetLBS($this->PPN);

      if ( isset($DAIA["message"][0]["errno"]) )
      {
        // Fehler passiert zunächst nur ausgeben
        if ( isset($this->medium["parents"][0]) )
        {
          $DAIA = $this->CI->GetLBS($this->medium["parents"][0]);
        }
      }
      $ExemplarDAIA = ProcessDAIA($this->CI, $DAIA, $this->medium);
    }

    //$this->CI->printArray2Screen($ExemplarDAIA);

    // Get MARC Parent
    $ParentMARC = ( isset($this->medium["parents"][0]) ) ? $this->CI->internal_search("id:".$this->medium["parents"][0]) : array();
    $ParentMARC = ( count($ParentMARC) > 0 && isset($ParentMARC["results"][$this->medium["parents"][0]]["contents"])) ? $ParentMARC["results"][$this->medium["parents"][0]]["contents"] : array();

    // Process 980 Data
    $ExemplarMARC = ProcessMARC($this->CI, $this->contents, $this->medium, $ParentMARC);

    // Merge and prepare data
    $ExemplarAll  = MergeAndPrepare($this->CI, $this->contents, $this->medium, $ExemplarDAIA, $ExemplarMARC, $ParentMARC);
  }
  else
  {
    // Printmedia, Other Libraries, Interlibrary loan

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
    
    $ExemplarAll[] = array
    (
      "action" => "link",
      "link"   => "http://gso.gbv.de/DB=" . $FinalDB . "/PPNSET?PPN=" . $this->PPN,
      "name"   => $this->CI->database->code2text("interloan")
    );
  }
}

//$this->CI->printArray2Screen($ExemplarAll);

// Generate Buttons
foreach ( $ExemplarAll as $EPN => $Exemplar )
{ 
  if ( isset($Exemplar["action"]) && $Exemplar["action"] == "link" )
  {
    if ( isset($Exemplar["link"]) )
    {
      $Output .= "<button onclick='window.open(\"" . $Exemplar["link"] . "\",\"_blank\")' class='" . $BtnClass . "'>";
      $Output .= ( isset($Exemplar["name"]) && $Exemplar["name"] != "" ) ? $Exemplar["name"] : $this->CI->database->code2text("online");
      $Output .= " <span class='fa fa-external-link'></span>";
      if ( $Host = parse_url($Exemplar["link"],PHP_URL_HOST) )
      {  
        if ( $Host == "www.bibliothek.uni-regensburg.de" ) $Host = "Elektr. Zeitschriftenbibliothek";
        if ( substr($Host,0,4) == "www.")   $Host = substr($Host,4);
        $Output .= "<br /><small>" . $Host . "</small>";
      }
      $Output .= "</button>";
    }
  }
  else
  {
    // Unique messages
    $Exams  = array_unique($Exemplar, SORT_REGULAR);
    ksort($Exams);
    $_SESSION["exemplar"][$this->PPN][$EPN] = $Exams;
    $Action = (isset($Exemplar["action"])) ? "onclick='$." . $Exemplar["action"] . "(\"" . $this->PPN . "\",\"" . $EPN . "\"," . json_encode($Exams,JSON_HEX_TAG) . ")'" : "";
    $Class  = (isset($Exemplar["action"])) ? $BtnClass : $EmptyClass;
    $Output .= "<button " . $Action . " class='" . $Class . "'>";
    $Output .= (isset($Exemplar["label1"])) ?  addslashes($Exemplar["label1"]) : "";
    $Output .= (isset($Exemplar["label2"])) ?  "<br />" . addslashes($Exemplar["label2"]) : "";
    $Output .= (isset($Exemplar["label3"])) ?  "<br />" . addslashes($Exemplar["label3"]) : "";
    $Output .= "</button>";
  }
}

// Add SFX-Button
if ( isset($_SESSION["config_general"]["export"]["openurlLink"]) && $_SESSION["config_general"]["export"]["openurlLink"] == "1" &&
     isset($this->pretty["issn"]) && $this->pretty["issn"] != "" && isset($this->pretty["year"]) && $this->pretty["year"] != "" ) 
{
  $Link  = $this->CI->internal_exportlink($this->PPN, "sfx");
  if (!empty($Link)) 
  {
    $Image = (isset($_SESSION["config_general"]["export"]["sfximagebasedlinking"]) &&
             $_SESSION["config_general"]["export"]["sfximagebasedlinking"] == true) ?
             $this->CI->internal_exportimage($this->PPN) : "noIBL";
    if (!empty($Image)) 
    {
      if ($Image == "noIBL") 
      {
        $Output .= "<a href=\"" . $Link . "\" target=\"_sfx\"><button class=\"" . $BtnClass . "\">Volltext pr&uuml;fen</button></a>";
      }
      else 
      { 
        $Output .= "<a href=\"" . $Link . "\" target=\"_sfx\"><img class='' src=\"" . $Image . "\"></a>";
      }
    }
  }
}

function ExemplarOnline981($Area)
{
  $ExemplarOnline = array();
  foreach ( $Area as $Record )
  {
    $Link = array("action"=>"link");
    foreach ( $Record as $Subrecord )
    {
      if ( isset($Subrecord["r"]) && $Subrecord["r"] != "" )  $Link["link"] = $Subrecord["r"];
      if ( isset($Subrecord["y"]) && $Subrecord["y"] != "" )  $Link["name"] = $Subrecord["y"];
    }
    $ExemplarOnline[] = $Link;
  }
  return $ExemplarOnline;
}

function ExemplarOnline856($Area)
{
  $ExemplarOnline = array();

  foreach ( $Area as $Record )
  {
    $Link = array("action"=>"link");
    foreach ( $Record as $Subrecord )
    {
      if ( isset($Subrecord["u"]) && $Subrecord["u"] != "" )  $Link["link"] = $Subrecord["u"];
      if ( isset($Subrecord["y"]) && $Subrecord["y"] != "" )  $Link["name"] = $Subrecord["y"];
      if ( isset($Subrecord["3"]) && $Subrecord["3"] != "" )  $Link["name"] = $Subrecord["3"];
    }
    $ExemplarOnline[] = $Link;
  }
  return $ExemplarOnline;
}

function ProcessDAIA($CI, $DAIA, $Medium)
{
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
            // Magazin verfügbar
            $Exemplars[$ExpID] = array
            (
              "action"  => "order",
              "typ"     => "magazine",
              "id"      => $Exp["id"],
              "label1"  => $CI->database->code2text("Magazine"),
              "label2"  => (isset($Exp["label"])) ?  $CI->database->code2text("Signature") . " " . $Exp["label"] : "",
              "label3"  => $CI->database->code2text("Order")
            );
          }

          if ( isset($Services["presentation"]) && $Services["presentation"] == true && ! isset($Exp["available"][0]["href"]) )
          {
            $Exemplars[$ExpID] = array
            (
              "action" => "shelve",
              "id"      => $Exp["id"],
              "label1" => ($Medium["online"] == 0 && $Medium["format"] == "journal") ? "" : $CI->database->code2text("Shelve"),
              "label2" => (isset($Exp["label"])) ?  $CI->database->code2text("Signature") . " " . $Exp["label"] : ""
            );
            if ( isset($Services["loan"]) && $Services["loan"] == false )
            {
              $Exemplars[$ExpID]["typ"]     = "shelve_not_lendable";
              $Exemplars[$ExpID]["label3"]  = $CI->database->code2text("Referencecollection");
              $Exemplars[$ExpID]["remark1"] = $CI->database->code2text("ReferencecollectionLong");
            }
            else
            {
              $Exemplars[$ExpID]["typ"]     = "shelve_lendable";
              $Exemplars[$ExpID]["label3"]  = $CI->database->code2text("Lendable");
            }
          }

          if ( isset($Services["loan"]) && $Services["loan"] == false && isset($Exp["unavailable"][0]["href"]) && $Exp["unavailable"][0]["href"] != "" )
          {
            // Magazin vorbestellbar
            $Exemplars[$ExpID] = array
            (
              "action" => "reservation",
              "id"     => $Exp["id"],
              "typ"    => "magazine",
              "label1" => $CI->database->code2text("reservation"),
              "label2" => (isset($Exp["label"])) ?  $CI->database->code2text("Signature") . " " . $Exp["label"] : "",
              "label3" => (isset($Exp["unavailable"][0]["expected"])) ? $CI->database->code2text("AvailableFrom") . " " . date("d.m.Y", strtotime($Exp["unavailable"][0]["expected"])) : ""
            );
          }
        }
      }
    }
  }
  return ($Exemplars);
}

function ProcessMARC($CI, $Contents, $Medium, $Parent)
{
  // Check order progress
  $ExemplarMARC = array();
  if ( array_key_exists("980", $Contents) )
  {
    foreach ( $Contents["980"] as $Record )
    {
      $One = array();
      foreach ( $Record as $Subrecord )
      {
        foreach ( $Subrecord as $Key => $Value )
        {
          $One[$Key] = $Value;
        }
      }
      // Use or create ExpID
      $ExpID = ( isset($One["b"]) ) ? $One["b"] : $X++;
      $ExemplarMARC[$ExpID] = $One;
    }
  }

  if ( count($ExemplarMARC) == 0 && count($Parent) > 0 )
  {
    if ( array_key_exists("980", $Parent) )
    {
      $X = 0;
      foreach ( $Parent["980"] as $Record )
      {
        $One = array();
        foreach ( $Record as $Subrecord )
        {
          foreach ( $Subrecord as $Key => $Value )
          {
            $One[$Key] = $Value;
          }
        }
        // Use or create ExpID
        $ExpID = ( isset($One["b"]) ) ? $One["b"] : $X++;
        $ExemplarMARC[$ExpID] = $One;
      }
    }
  }
  return $ExemplarMARC;
}

function Process983($CI, $Contents)
{
  $Location = array();
//  $Count = 10;
//  if ( array_key_exists("983", $Contents) )
//  {
//    foreach ( $Contents["983"] as $Record )
//    {
//      foreach ( $Record as $Subrecord )
//      {
//        foreach ( $Subrecord as $Key => $Value )
//        {
//          if ( $Key == "a" )
//          {
//            $Ortexp = "";
//            if (substr($Value,0,2) == 'F/')
//            {
//              $Ortexp = $CI->database->code2text("983aF/");
//            }
//
//            //$CI->printArray2File($Ortexp);
//            if ( $Ortexp != "" )
//            {
//              $Count++;
//              $Location["location".$Count] = prepareStr($Ortexp);
//            }
//            $Count++;
//            $Location["location".$Count] = prepareStr($Value);
//          }
//        }
//      }
//    }
//  }
  return ($Location);
}

function MergeAndPrepare($CI, $Contents, $Medium, $ExemplarDAIA, $ExemplarMARC, $ParentMARC)
{
  $ExemplarBOTH = $ExemplarDAIA;

  $ParentData = array();
  if ( count($ParentMARC) > 0 && isset($ParentMARC["980"]["0"]) )
  {
    foreach ( $ParentMARC["980"]["0"] as $Record )
    {
      foreach ( $Record as $key => $value )
      {
        $ParentData[$key] = $value;
      }
    }
  }

  // Add or update ordered or non existing data
  foreach ( $ExemplarMARC as $ExpID => $One )
  {  
    if ( isset($One["e"]) && in_array($One["e"], array("a","b","c","d","e","f","g","i","s","u") ) )
    {
      // Step 1 - Add ordered data ( replace data )
      if ( isset($One["e"]) && $One["e"] == "a" )
      {
        if ( ! array_key_exists($ExpID, $ExemplarBOTH) )
        {
          $ExemplarBOTH[$ExpID] = array
          (
            "label1" => $CI->database->code2text("Ordered"),
            "typ"    => "ordered"
          );
        }
      }

      // Step 2 - Update locked data
      if ( isset($One["e"]) && in_array($One["e"], array("b","g") ) && isset($One["f"]) && $One["f"] != "" )
      {
        if ( array_key_exists($ExpID, $ExemplarBOTH) )
        {
          if ( $One["e"] == "g" && $One["f"] == "FH" )
          {
            $ExemplarBOTH[$ExpID] = array
            (
              "typ"     => "shelve_not_lendable",
              "action"  => "shelve",
              "label1"  => $CI->database->code2text("Shelve"),
              "label2"  => ( isset($One["d"]) && $One["d"] != "" ) ? $CI->database->code2text("Signature") . " " . $One["d"] : "",
              "label3"  => $CI->database->code2text("Referencecollection"),
              "remark1" => $CI->database->code2text("ReferencecollectionLong")
            );
          }
          else
          {
            // b and g, not 980f = FH
            $ExemplarBOTH[$ExpID] = array
            (
              "label1" => $One["f"],
              "label2" => ( isset($One["d"]) && $One["d"] != "" ) ? $CI->database->code2text("Signature") . " " . $One["d"] : "",
              "label3" => $CI->database->code2text("Locked"),
              "typ"    => "Locked"
            );
          }
        }
      }  

      // Step 3 - Add new data to list with default behaviour (will be updated in later steps)
      if ( ! array_key_exists($ExpID, $ExemplarBOTH) )
      {
        $ExemplarBOTH[$ExpID] = array
        (
          "action"  => "shelve",
          "typ"     => "shelve_lendable",
          "label1"  => ($Medium["online"] == 0 && $Medium["format"] == "journal") ? "" : $CI->database->code2text("Shelve"),
          "label2"  => ( isset($One["d"]) && $One["d"] != "" ) ? $CI->database->code2text("Signature") . " " . $One["d"] : "",
          "label3"  => $CI->database->code2text("Lendable")
        );
      }
    }
  }

  // Now both data sources have been merged.

  // Loop over merged list and check for additionally required data
  $GetLocation = false;
  $Location = array();
  foreach ( $ExemplarBOTH as $ExpID => &$One )
  {
    if ( substr($One["typ"],0,6) == "shelve" || $One["typ"] == "mailorder_referencecollection" )
    {
      
      if ( $Medium["online"] == 0 && $Medium["format"] == "journal" ) continue;
      
      // Magdeburg specific
      // Process 983 location data
      if ( !$GetLocation )
      {
        $Location     = Process983($CI, $Contents);
        $GetLocation  = true;
      }

      if (count($Location) > 0 )
      { 
        $One["location0"] = $CI->database->code2text("ITEMLOCATION");
        $One += $Location;
      }
    }
    
    // Add storage infos from parent
    if ( count($ParentData ) > 0 )
    {
      if ( isset($ParentData["g"]) && $ParentData["g"] != "" )
      {
        if ( !isset($One["location0"]) )   $One["location0"] = $CI->database->code2text("ITEMLOCATION");
        $One["location95x"] = $ParentData["g"];
      }
      if ( isset($ParentData["k"]) && $ParentData["k"] != "" )
      {
        if ( !isset($One["location0"]) )   $One["location0"] = $CI->database->code2text("ITEMLOCATION");
        $One["location96x"] = $ParentData["k"];
      }
    }
  }
  return ($ExemplarBOTH);
}        

function prepareStr($Str)
{
  $Str = str_ireplace("<","%lt%",$Str);
  $Str = str_ireplace(">","%gt%",$Str);
  return $Str;
}

?>