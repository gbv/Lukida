<?php

class Standard
{
  protected $CI;

  public function __construct()
  {
    // Assign the CodeIgniter super-object
    $this->CI =& get_instance();
  }

  public function exportfile($data, $format)
  {
    // Create File Data 
    
    // Um die Daten zu sehen, die folgende Zeile aktivieren:
    // $this->CI->printArray2File($data);

    switch ($format) {
          case "citavi":
              $openurlMetadata = $this->getCitaviMetaData($data);
          break;
          case "endnote":
              $openurlMetadata = $this->getEndnoteMetaData($data);
          break;
          case "bibtex":
              $openurlMetadata = $this->getBibtexMetaData($data);
          break;
    }
    
    // Einen String (Dateinhalt, keine Datei) zusammenbauen und zurueckliefern
    //return $data["id"] ." - " . $data["format"] ." - " . $format;

    return $openurlMetadata;
  }

  public function exportlink($data, $format)
  {
    // Create File Data 
    // Um die Daten zu sehen, die folgende Zeile aktivieren
    // $this->CI->printArray2File($data);
    
    $configBase      = "";
    $openurlBase     = "";
    $openurlEntry    = "";
    $openurlMetadata = "";
    switch ($format) {
          case "refworks": $configBase = "refworksBase";
          break;
          case "sfx"     : $configBase = "openurlBase";
          break;
    }
    $openurlBase = (isset($_SESSION["config_general"]["export"][$configBase]) &&
                    $_SESSION["config_general"]["export"][$configBase] != "") ?
                    $_SESSION["config_general"]["export"][$configBase]        : null;
    if (isset($openurlBase)) {
      $openurlEntry =    $openurlBase . "?sid=GBV&ctx_enc=info%3Aofi%2Fenc%3AUTF-8&rfr_id=info%3Asid%2Fgbv.de:" .
                         ((isset($_SESSION["config_general"]["export"]["openurlReferer"]) &&
                         $_SESSION["config_general"]["export"]["openurlReferer"] != "") ?
                         $_SESSION["config_general"]["export"]["openurlReferer"] : "Lukida");
      $openurlMetadata = $this->getOpenURLmetaData($data);
      $link            = $openurlEntry . ($format == "refworks" ? str_replace("&rft.","&",$openurlMetadata) : $openurlMetadata);
    }
    else { $link = null;
    }
    // Link/URL zurueckliefern:

    return $link;
  }

//base_url("assets/images/Volltext.gif")
  public function exportlinkimage($data)
  {
    $openurlBase     = "";
    $openurlBase = (isset($_SESSION["config_general"]["export"]["openurlBase"]) &&
                    $_SESSION["config_general"]["export"]["openurlBase"] != "") ?
                    $_SESSION["config_general"]["export"]["openurlBase"]        : null;
    if (isset($openurlBase) && isset($data["issn"]) && $data["issn"] != "" && isset($data["year"]) && $data["year"] != "") {
      $issn = str_replace("--","-",$data["issn"]);
      $year = substr(filter_var($data["year"], FILTER_SANITIZE_NUMBER_INT),0,4);
      $openurlImage =    (isset($_SESSION["config_general"]["export"]["sfximagebasedlinking"]) &&
                          $_SESSION["config_general"]["export"]["sfximagebasedlinking"] == true) ?
                         ($openurlBase . "?__service_type=getFullTxt&__response_type=image-small&eissn=" . 
                          $issn . "&date=" . $year . "-")  : "noIBL";
    }
    else { $openurlImage = null;
    }
    // Link/URL zurueckliefern:
    return $openurlImage;
  }

  public function getOpenURLmetaData($data)
  {
   $metadataOU = "";
   if (isset($data["format"]) && $data["format"] != "") {
     $metadataOU = "&rft.genre=" . $data["format"];
   }
   if (isset($data["title"]) && $data["title"] != "") {
     if (isset($data["publisherarticle"])) {
       if (is_array($data["publisherarticle"])) {
         if (count($data["publisherarticle"]) >= 1 && isset($data["publisherarticle"][0]["t"]) && $data["publisherarticle"][0]["t"] != "") {
           $publisherarticle = $data["publisherarticle"][0]["t"];
         }
       }
       else { $publisherarticle = $data["publisherarticle"];
       }
       if (!empty($publisherarticle)) {
         $metadataOU .= "&rft.atitle=" . $data["title"] . "&rft.title=" . (stripos($publisherarticle, "in:") !== false ?   
                         trim(substr($publisherarticle,stripos($publisherarticle, "in:") + 3)) :
                         $publisherarticle);
       }
       else { $metadataOU .= "&rft.title=" . $data["title"];
       }
     }
     else { $metadataOU .= "&rft.title=" . $data["title"];
     }
   }
   if (isset($data["serial"])) { 
     if (is_array($data["serial"])) { 
       if (count($data["serial"]) >= 1) {
         foreach($data["serial"] as $serial) {
           foreach($serial as $sKey=>$sValue) {
              if (!empty($sValue) && $sKey == "a") {
                $metadataOU .= "&rft.jtitle=" . ((stripos($sValue, "in:") !== false) ? trim(substr($sValue,stripos($sValue, "in:") + 3)) : $sValue);
              }
              else { $metadataOU .= " " . $sValue; 
              }
           }
         }
       }
     }
     elseif ($data["serial"] != "") {
       $metadataOU .= "&rft.jtitle=" . ((stripos($data["serial"], "in:") !== false) ? trim(substr($data["serial"],stripos($data["serial"], "in:") + 3)) : $data["serial"]);
     }
   }
   if (isset($data["author"])) {
     if (is_array($data["author"])) {
       if (count($data["author"]) >= 1) {
         $aNr = 0;
         foreach($data["author"] as $author) {
           $aNr += 1;
           $metadataOU .= "&rft.author=" . $author;
           if ($aNr == 1 && !empty($author) && strpos($author, ', ') !== false) {
             $metadataOU .= "&rft.aulast=" . strstr($author, ', ', true);
             $metadataOU .= "&rft.aufirst=" . substr(strstr($author, ", "), 2);
           }
         }
       }
     }
     elseif ($data["author"] != "") {
       $metadataOU .= "&rft.author=" . $data["author"];
       if (strpos($data["author"], ", ")) {
         $metadataOU .= "&rft.aulast=" . strstr($data["author"], ', ', true);
         $metadataOU .= "&rft.aufirst=" . substr(strstr($data["author"], ", "), 2);
       }
     }
   }
   if (isset($data["isbn"])) {
     if (is_array($data["isbn"])) {
       if (count($data["isbn"]) >= 1) {
         foreach($data["isbn"] as $isbn) {
           if ($isbn != "") {
             $metadataOU .= "&rft.isbn=" . $isbn;
             break;
           }
         }
       }
     }
     elseif ($data["isbn"] != "") {
       $metadataOU .= "&rft.isbn=" . $data["isbn"];
     }
   }
   if (isset($data["issn"])) { 
     if (is_array($data["issn"])) {
       if (count($data["issn"]) >= 1) {
         foreach($data["issn"] as $issn) {
           if ($issn != "") {
             $metadataOU .= "&rft.issn=" . $issn;
             break;
           }
         }
       }
     }
     elseif ($data["issn"] != "") {
       $metadataOU .= "&rft.issn=" . $data["issn"];
     }
   }
   if (isset($data["edition"]) && $data["edition"] != "") {
     $metadataOU .= "&rft.edition=" . $data["edition"];
   }
   if (isset($data["part"]) && $data["part"] != "") {
     $metadataOU .= "&rft.part=" . $data["part"];
   }
   if (isset($data["year"]) && $data["year"] != "") {
     $metadataOU .= "&rft.date=" . $data["year"];
   }
   if (isset($data["volume"]) && $data["volume"] != "") {
     $metadataOU .= "&rft.volume=" . $data["volume"];
   }
   if (isset($data["issue"]) && $data["issue"] != "") {
     $metadataOU .= "&rft.issue=" . $data["issue"];
   }
   if (isset($data["pages"]) && $data["pages"] != "") {
     $metadataOU .= "&rft.pages=" . $data["pages"];
     if ( strpos($data["pages"], "-") !== false ) {
       $metadataOU .= "&rft.spage=" . strstr($data["pages"], '-', true);
       $metadataOU .= "&rft.epage=" . substr(strstr($data["pages"], "-"), 1);
     }
   }
   if (isset($data["place"]) && $data["place"] != "") {
     $metadataOU .= "&rft.place=" . $data["place"];
   }
   if (isset($data["publisherOnly"]) && $data["publisherOnly"] != "") {
     $metadataOU .= "&rft.pub=" . $data["publisherOnly"];
   }
   return $metadataOU;
  }

public function getCitaviMetaData($data)
  {
   $metadataOU = "";
   if (isset($data["format"]) && $data["format"] != "") {
     $metadataOU = "TY  - " . $data["format"] . "\r\n";
   }
   if (isset($data["id"]) && $data["id"] != "") {
     $metadataOU .= "ID  - " . $data["id"] . "\r\n"; 
   }
   if (isset($data["title"]) && $data["title"] != "") {
     $metadataOU .= "T1  - " . ($data["title"]) . "\r\n"; 
   }
   if (isset($data["publisherarticle"])) {
     if (is_array($data["publisherarticle"])) {
       if (count($data["publisherarticle"]) >= 1 && $data["publisherarticle"][0]["t"] != "") {
         $publisherarticle = $data["publisherarticle"][0]["t"];
       }
     }
     else { $publisherarticle = $data["publisherarticle"]; 
     }
     if (!empty($publisherarticle)) {
       $metadataOU .= "JF  - " . (stripos($publisherarticle, "in:") !== false ?     
                       trim(substr($publisherarticle,stripos($publisherarticle, "in:") + 3)) :
                       $publisherarticle) . "\r\n";
     }
   }
   if (isset($data["serial"])) {
     if (is_array($data["serial"])) {
       if (count($data["serial"]) >= 1) {
         foreach($data["serial"] as $serial) {
           foreach($serial as $sKey=>$sValue) {
              if (!empty($sValue) && $sKey == "a") {
                $metadataOU .= "JF  - " . ((stripos($sValue, "in:") !== false) ? trim(substr($sValue,stripos($sValue, "in:") + 3)) : $sValue);
              }
              else { $metadataOU .= " " . $sValue; 
              }
           }
           $metadataOU .= "\r\n";
         }
       }
     }
     elseif ($data["serial"] != "") {
       $metadataOU .= "JF  - " . ((stripos($data["serial"], "in: ") !== false) ? trim(substr($data["serial"],stripos($data["serial"], "in:") + 3)) : $data["serial"]) . "\r\n";
     }
   }
   if (isset($data["author"])) {
     if (is_array($data["author"])) {
       if (count($data["author"]) >= 1) {
         $aNr = 0;
         foreach($data["author"] as $author) {
           $aNr += 1;
           $metadataOU .= "A" . $aNr . "  - " . $author . "\r\n";
         }
       }
     }
     elseif ($data["author"] != "") {
       $metadataOU .= "A1  - " . $data["author"] . "\r\n";
     }
   }
   if (isset($data["notes"]) && $data["notes"] != "") {
     if (strpos($data["notes"]," - ")!==false) {
       foreach (explode(" - ", $data["notes"]) as $note) {
              $metadataOU .= "N1  - " . $note . "\r\n";
       }
     }
     else { $metadataOU .= "N1  - " . $data["notes"] . "\r\n"; 
     }
   }
   if (isset($data["isbn"])) {
     if (is_array($data["isbn"])) {
       if (count($data["isbn"]) >= 1) {
         foreach($data["isbn"] as $isbn) {
           if ($isbn != "") {
             $metadataOU .= "BN  - " . $isbn . "\r\n";
             break;
           }
         }
       }
     }
     elseif ($data["isbn"] != "") {
       $metadataOU .= "BN  - " . $data["isbn"] . "\r\n";
     }
   }
   if (isset($data["issn"])) {
     if (is_array($data["issn"])) {
       if (count($data["issn"]) >= 1) {
         foreach($data["issn"] as $issn) {
           if ($issn != "") {
             $metadataOU .= "SN  - " . $issn . "\r\n";
             break;
           }
         }
       }
     }
     elseif ($data["issn"] != "") {
       $metadataOU .= "SN  - " . $data["issn"] . "\r\n";
     }
   }
   if (isset($data["edition"]) && $data["edition"] != "") {
    $metadataOU .= "ED  - " . $data["edition"] . "\r\n";
   }
   /*if (isset($data["part"]) && $data["part"] != "") {
    $metadataOU .= "  - " . $data["part"] . "\r\n";
   }
   */
   if (isset($data["year"]) && $data["year"] != "") {
    $metadataOU .= "PY  - " . $data["year"] . "\r\n";
   }
   if (isset($data["volume"]) && $data["volume"] != "") {
    $metadataOU .= "VL  - " . $data["volume"] . "\r\n";
   }
   if (isset($data["issue"]) && $data["issue"] != "") {
    $metadataOU .= "IS  - " . $data["issue"] . "\r\n";
   }
   if (isset($data["place"]) && $data["place"] != "") {
     $metadataOU .= "CY  - " . $data["place"] . "\r\n";
   }
   if (isset($data["publisherOnly"]) && $data["publisherOnly"] != "") {
     $metadataOU .= "PB  - " . $data["publisherOnly"] . "\r\n";
   }
   if (isset($data["pages"]) && $data["pages"] != "") {
    if ( strpos($data["pages"], "-") !== false ) {
      $metadataOU .= "SP  - " . strstr($data["pages"], '-', true) . "\r\n";
      $metadataOU .= "EP  - " . substr(strstr($data["pages"], "-"), 1) . "\r\n";
    }
   $metadataOU .= "S1  - Gemeinsamer Bibliotheksverbund (GBV) / Verbundzentrale des GBV (VZG)\r\n";
   $metadataOU .= "S2  - OPAC Magdeburg\r\n";
   $metadataOU .= "S3  - Lukida.ub_md\r\n";
   $metadataOU .= "L3  - " . base_url() . $data["id"] . "/id\r\n"; 
   }
   return $metadataOU;
  }

public function getEndnoteMetaData($data)
  {
   $metadataOU = "";
   if (isset($data["format"]) && $data["format"] != "") {
     $metadataOU = "%0 " . $data["format"] . "\r\n";
   }
   if (isset($data["id"]) && $data["id"] != "") {
     $metadataOU .= "%M " . $data["id"] . "\r\n";
   }
   if (isset($data["title"]) && $data["title"] != "") {
     $metadataOU .= "%T " . $data["title"] . "\r\n";
   }
   if (isset($data["publisherarticle"])) {
     if (is_array($data["publisherarticle"])) {
       if (count($data["publisherarticle"]) >= 1 && $data["publisherarticle"][0]["t"] != "") {
         $publisherarticle = $data["publisherarticle"][0]["t"];
       }
     }
     else { $publisherarticle = $data["publisherarticle"]; 
     }
     if (!empty($publisherarticle)) {
       $metadataOU .= "%J " . (stripos($publisherarticle, "in:") !== false ?                                         
                       trim(substr($publisherarticle,stripos($publisherarticle, "in:") + 3)) :
                       $publisherarticle) . "\r\n";
     }
   }
   if (isset($data["serial"])) {
     if (is_array($data["serial"])) {
       if (count($data["serial"]) >= 1) {
         foreach($data["serial"] as $serial) {
           foreach($serial as $sKey=>$sValue) {
              if (!empty($sValue) && $sKey == "a") {
                $metadataOU .= "%J " . ((stripos($sValue, "in:") !== false) ? trim(substr($sValue,stripos($sValue, "in:") + 3)) : $sValue);
              }
              else { $metadataOU .= " " . $sValue;
              }
           }
           $metadataOU .= "\r\n";
         }
       }
     }
     elseif ($data["serial"] != "") {
       $metadataOU .= "%J " . ((stripos($data["serial"], "in:") !== false) ? trim(substr($data["serial"],stripos($data["serial"], "in:") + 3)) : $data["serial"]) . "\r\n";
     }
   }
   if (isset($data["author"])) {
     if (is_array($data["author"])) {
       if (count($data["author"]) >= 1) {
         foreach($data["author"] as $author) {
           $metadataOU .= "%A " . $author . "\r\n";
         }
       }
     }
     elseif ($data["author"] != "") {
       $metadataOU .= "%A " . $data["author"] . "\r\n";
     }
   }
   if (isset($data["isbn"])) {
     if (is_array($data["isbn"])) {
       if (count($data["isbn"]) >= 1) {
         foreach($data["isbn"] as $isbn) {
           if ($isbn != "") {
             $metadataOU .= "%@ " . $isbn . "\r\n";
             break;
           }
         }
       }
     }
     elseif ($data["isbn"] != "") {
       $metadataOU .= "%@ " . $data["isbn"] . "\r\n";
     }
   }
   if (isset($data["issn"])) {
     if (is_array($data["issn"])) {
       if (count($data["issn"]) >= 1) {
         foreach($data["issn"] as $issn) {
           if ($issn != "") {
             $metadataOU .= "%@ " . $issn . "\r\n";
             break;
           }
         }
       }
     }
     elseif ($data["issn"] != "") {
       $metadataOU .= "%@ " . $data["issn"] . "\r\n";
     }
   }
   if (isset($data["edition"]) && $data["edition"] != "") {
     $metadataOU .= "%7 " . $data["edition"] . "\r\n";
   }
   /*if (isset($data["part"]) && $data["part"] != "") {
     $metadataOU .= "% " . $data["part"] . "\r\n";
   }
   */
   if (isset($data["year"]) && $data["year"] != "") {
     $metadataOU .= "%D " . $data["year"] . "\r\n";
   }
   if (isset($data["volume"]) && $data["volume"] != "") {
     $metadataOU .= "%V " . $data["volume"] . "\r\n";
   }
   if (isset($data["issue"]) && $data["issue"] != "") {
     $metadataOU .= "%N " . $data["issue"] . "\r\n";
   }
   if (isset($data["pages"]) && $data["pages"] != "") {
     $metadataOU .= "%P " . $data["pages"] . "\r\n";
   }
   if (isset($data["place"]) && $data["place"] != "") {
     $metadataOU .= "%C " . $data["place"] . "\r\n";
   }
   if (isset($data["publisherOnly"]) && $data["publisherOnly"] != "") {
     $metadataOU .= "%I " . $data["publisherOnly"] . "\r\n";
   }
   if (isset($data["notes"]) && $data["notes"] != "") {
     if (strpos($data["notes"]," - ")!==false) {
       foreach (explode(" - ", $data["notes"]) as $note) {
              $metadataOU .= "%Z " . $note . "\r\n";
       }
     }
     else { $metadataOU .= "%Z " . $data["notes"] . "\r\n";
     }
   }
   $metadataOU .= "%W Gemeinsamer Bibliotheksverbund (GBV) / Verbundzentrale des GBV (VZG)\r\n";
   $metadataOU .= "%U " . base_url() . $data["id"] . "/id\r\n";
   return $metadataOU;
  }

public function getBibtexMetaData($data)
  {
   $metadataOU = "";
   if (isset($data["format"]) && $data["format"] != "") {
     $metadataOU = "@" . $data["format"];
   }
   $metadataOU .= "{GBV";
   if (isset($data["id"]) && $data["id"] != "") {
     $metadataOU .= "-" . $data["id"] . ",\r\n";
   }
   if (isset($data["title"]) && $data["title"] != "") {
     $metadataOU .= "\ttitle = {" . $data["title"] . "},\r\n";
   }
   if (isset($data["publisherarticle"])) {
     if (is_array($data["publisherarticle"])) {
       if (count($data["publisherarticle"]) >= 1 && $data["publisherarticle"][0]["t"] != "") {
         $publisherarticle = $data["publisherarticle"][0]["t"];
       }
     }
     else { $publisherarticle = $data["publisherarticle"]; 
     }
     if (!empty($publisherarticle)) {
       $metadataOU .= "\tjournal = {" . (stripos($publisherarticle, "in:") !== false ?     
                       trim(substr($publisherarticle,stripos($publisherarticle, "in:") + 3)) :
                       $publisherarticle) . "},\r\n";
     }
   }
   if (isset($data["serial"])) {
     if (is_array($data["serial"])) {
       if (count($data["serial"]) >= 1) {
         foreach($data["serial"] as $serial) {
           foreach($serial as $sKey=>$sValue) {
              if (!empty($sValue) && $sKey == "a") {
                $metadataOU .= "\tjournal = {" . ((stripos($sValue, "in:") !== false) ? trim(substr($sValue,stripos($sValue, "in:") + 3)) : $sValue);
              }
              else { $metadataOU .= " " . $sValue;
              }
           }
           $metadataOU .= "},\r\n";
         }
       }
     }
     elseif ($data["serial"] != "") {
       $metadataOU .= "\tjournal = {" . ((stripos($data["serial"], "in:") !== false) ? trim(substr($data["serial"],stripos($data["serial"], "in:") + 3)) : $data["serial"]) . "},\r\n";
     }
   }
   if (isset($data["author"])) {
     if (is_array($data["author"])) {
       if (count($data["author"]) >= 1) {
         foreach($data["author"] as $author) {
           $metadataOU .= "\tauthor = {" . $author . "},\r\n";
         }
       }
     }
     elseif ($data["author"] != "") {
       $metadataOU .= "\tauthor = {" . $data["author"] . "},\r\n";
     }
   }
   if (isset($data["edition"]) && $data["edition"] != "") {
    $metadataOU .= "\tedition = {" . $data["edition"] . "},\r\n";
   }
   /*if (isset($data["part"]) && $data["part"] != "") {
    $metadataOU .= "\t = {" . $data["part"] . "},\r\n";
   }
   */
   if (isset($data["year"]) && $data["year"] != "") {
    $metadataOU .= "\tyear = {" . $data["year"] . "},\r\n";
   }
   if (isset($data["volume"]) && $data["volume"] != "") {
    $metadataOU .= "\tvolume = {" . $data["volume"] . "},\r\n";
   }
   if (isset($data["issue"]) && $data["issue"] != "") {
    $metadataOU .= "\tnumber = {" . $data["issue"] . "},\r\n";
   }
   if (isset($data["pages"]) && $data["pages"] != "") {
     $metadataOU .= "\tpages = {" . $data["pages"] . "}\r\n";
   }
   if (isset($data["notes"]) && $data["notes"] != "") {
     if (strpos($data["notes"]," - ")!==false) {
       foreach (explode(" - ", $data["notes"]) as $note) {
              $metadataOU .= "\tnote = {" . $note . "}\r\n";
       }
     }
     else { $metadataOU .= "\tnote = {" . $data["notes"] . "}\r\n";
     }
   }
   $metadataOU .= "}";
   return $metadataOU;
  }

}

?>
