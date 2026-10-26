<?php

class solr
{
  
  
  
  
  
  const SPELL_MAX_WORTE = 10;

  
  
  private $letzteWortsuche = "";

  
  
  private $spellMoeglich = false;

  
  
  private $letzteAdresse = "";

  
  
  

  
  
  
  private const FIELDS = array(
    "acqdate"            => array("last_changed_iln_str_mv"),
    "erwdatum"           => array("last_changed_iln_str_mv"),
    "author"             => array("author", "author2", "author_os_txtP_mv", "GND_txt_mv"),
    "autor"              => array("author", "author2", "author_os_txtP_mv", "GND_txt_mv"),
    "call"               => array("abrufzeichen_iln_scis_mv"),
    "abruf"              => array("abrufzeichen_iln_scis_mv"),
    "class"              => array("class"),
    "sachgebiet"         => array("class"),
    "classlocal"         => array("notation_local_iln_str_mv"),
    "client"             => array("selectbib_iln_str_mv"),
    "mandant"            => array("selectbib_iln_str_mv"),
    "collection"         => array("collection"),
    "collection_details" => array("collection_details"),
    "details"            => array("collection_details"),
    "package"            => array("collection_details"),
    "paket"              => array("collection_details"),
    "comment"            => array("exemplarkommentar_str_mv"),
    "kommentar"          => array("exemplarkommentar_str_mv"),
    "corporation"        => array("author_corporate", "GND_txt_mv"),
    "koerper"            => array("author_corporate", "GND_txt_mv"),
    "country"            => array("countryofpublication_str_mv"),
    "land"               => array("countryofpublication_str_mv"),
    "doi"                => array("doi_str_mv"),
    "foreignid"          => array("foreignid"),
    "format"             => array("format_phy_str_mv"),
    "format2"            => array("format"),
    "genre"              => array("genre_facet"),
    "id"                 => array("id"),
    "ppn"                => array("id"),
    "isn"                => array("isbn", "issn"),
    "language"           => array("language"),
    "sprache"            => array("language"),
    "langcode"           => array("lang_code"),
    "sprachcode"         => array("lang_code"),
    "location"           => array("standort_iln_str_mv", "standort_weitere_iln_str_mv"),
    "standort"           => array("standort_iln_str_mv", "standort_weitere_iln_str_mv"),
    "new"                => array("selektneu_str_mv"),
    "neu"                => array("selektneu_str_mv"),
    "norm"               => array("normlink_prefix_str_mv"),
    "openaccess"         => array("isfreeaccess_bool"),
    "ppnlink"            => array("ppnlink"),
    "prov"               => array("provenience_txtP_mv"),
    "publisher"          => array("publisher"),
    
    
    
    
    
    
    "verlag"             => array("publisher"),
    "series"             => array("series", "series2"),
    "reihe"              => array("series", "series2"),
    "shelfmark"          => array("signature_iln_scis_mv", "signature_weitere_iln_str_mv"),
    "signature"          => array("signature_iln_scis_mv", "signature_weitere_iln_str_mv"),
    "signatur"           => array("signature_iln_scis_mv", "signature_weitere_iln_str_mv"),
    "subject"            => array("topic", "GND_str_mv", "topic_unstemmed"),
    "schlagwort"         => array("topic", "GND_str_mv", "topic_unstemmed"),
    "title"              => array("title_short", "title_full_unstemmed", "title_full", "title", "title_os_txtP_mv", "title_alt"),
    "titel"              => array("title_short", "title_full_unstemmed", "title_full", "title", "title_os_txtP_mv", "title_alt"),
    "topic"              => array("topic"),
    "thema"              => array("topic"),
    "topicfacet"         => array("topic_facet"),
    "university"         => array("hochschulschrift_txt_mv"),
    "hochschule"         => array("hochschulschrift_txt_mv"),
    "year"               => array("publishDateSort"),
    "jahr"               => array("publishDateSort"),
  );

  
  
  
  
  private const SOUNDGEWICHT = array(
    "title_fullSound" => 690,
    "authorSound"     => 490,
    "allfieldsSound"  => 9,
    "fulltextSound"   => 9,
  );

  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  private const QUERYGEWICHT = array(
    "title_short"             => 770,  "title_full"              => 740,
    "title_alt"               => 710,  "title_new"               => 700,
    "topic"                   => 600,  "author"                  => 520,
    "author2"                 => 510,  "author_fuller"           => 500,
    "genre_facet"             => 200,  "geographic"              => 210,
    "series2"                 => 100,  "series"                  => 110,
    "allfieldsGer"            =>  10,  "allfields"               =>  10,
    "contents"                =>  10,  "description"             =>   5,
    "fulltext"                =>   5,  "GND_txt_mv"              =>   1,
    "isbn"                    =>   1,  "issn"                    =>   1,
  );

  private const FIELDSSOUND = array(
    "author" => "authorSound",
    "autor"  => "authorSound",
    "title"  => "title_fullSound",
    "titel"  => "title_fullSound",
  );

  
  
  
  private const ILNCOLON = array("classlocal", "location", "shelfmark", "signatur", "signature", "standort");
  
  private const ILNAT    = array("abruf", "acqdate", "call", "comment", "erwdatum", "kommentar", "neu", "new");

  
  private const COUNTS   = array("types", "formats", "languages", "topicfacets", "classlocals",
                                 "locations", "formatsmain", "genres", "topicrvks", "topicbkls");

  private $add;
  private $CS;
  private $level;
  private $phonetic;
  private $sortacq;
  private $QueryArr;
  private $SkipID;
  
  
  
  
  private $locfacetkeys;
  
  
  
  
  private $zweiterVersuch;
  
  
  private $unbekannt;
  
  
  
  private $nebenabfrage;

  public function __construct($CS)
  {
    $this->CS            = $CS;
    $this->add           = false;
    $this->phonetic      = false;
    $this->QueryArr      = array();
    $this->locfacetkeys  = array();
    $this->zweiterVersuch = false;
    $this->unbekannt      = null;
    $this->nebenabfrage   = false;

    if ( APPDEBUG["FKT"] ) $this->CS->debug("FKT",__METHOD__);
  }

  
  
  

  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  private function unbekannteFelder()
  {
    if ( $this->unbekannt !== null )  return $this->unbekannt;

    $this->unbekannt = array();
    $Datei = $this->CS->prepareCachePath(array("settings"), "findexfelder");
    if ( file_exists($Datei) )
    {
      $Gelesen = json_decode((string) file_get_contents($Datei), true);
      if ( is_array($Gelesen) )  $this->unbekannt = array_values(array_filter($Gelesen, "is_string"));
    }
    return $this->unbekannt;
  }

  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  private function soundFelder()
  {
    $Erlaubt = array_keys(self::SOUNDGEWICHT);

    $Schalter = trim((string) ($this->CS->configs["init"]["soundfields"] ?? ""));
    if ( $Schalter !== "" )
    {
      $Genannt = preg_split('/[\s,;]+/', $Schalter, -1, PREG_SPLIT_NO_EMPTY);
      
      
      $Erlaubt = array_values(array_intersect($Erlaubt, (array) $Genannt));
    }

    $Unbekannt = $this->unbekannteFelder();
    return array_values(array_diff($Erlaubt, $Unbekannt));
  }

  
  
  private function gewichtungsFelder()
  {
    if ( APPDEBUG["FKT"] ) $this->CS->debug("FKT",__METHOD__);

    $Felder = array();
    foreach ( self::QUERYGEWICHT as $Feld => $Gewicht )  $Felder[] = $Feld . "^" . $Gewicht;
    return $Felder;
  }

  
  
  
  
  
  
  
  
  
  
  
  
  
  
  private function istBekannterBefehl($Befehl)
  {
    $B = strtolower(trim((string) $Befehl));
    if ( $B === "" )  return false;
    if ( isset(self::FIELDS[$B]) )  return true;
    
    
    return in_array($B, array("geo", "from", "von", "until", "bis"), true);
  }

  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  private function entschaerfeResttext($Text)
  {
    $Text = (string) $Text;
    if ( $Text === "" )  return $Text;

    
    $Tiefe = 0; $Schief = false; $InZitat = "";
    $Len = strlen($Text);
    for ( $i = 0; $i < $Len; $i++ )
    {
      $C = $Text[$i];
      if ( $InZitat !== "" ) { if ( $C === $InZitat ) $InZitat = ""; continue; }
      if ( $C === '"' || $C === "'" )  { $InZitat = $C; continue; }
      if ( $C === "(" )  $Tiefe++;
      if ( $C === ")" )  { $Tiefe--; if ( $Tiefe < 0 ) { $Schief = true; break; } }
    }
    if ( $Tiefe !== 0 )  $Schief = true;

    
    
    $Aus = ""; $InZitat = "";
    for ( $i = 0; $i < $Len; $i++ )
    {
      $C = $Text[$i];
      if ( $InZitat !== "" )
      {
        $Aus .= $C;
        if ( $C === $InZitat )  $InZitat = "";
        continue;
      }
      if ( $C === '"' || $C === "'" )  { $InZitat = $C; $Aus .= $C; continue; }
      if ( $C === ":" )                       { $Aus .= "\\:"; continue; }
      if ( $Schief && ($C === "(" || $C === ")") )  { $Aus .= "\\" . $C; continue; }
      $Aus .= $C;
    }
    return $Aus;
  }


  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  private function istEigenesFeld($Feld)
  {
    if ( isset(self::SOUNDGEWICHT[$Feld]) )  return true;
    foreach ( self::FIELDS as $Liste )
      if ( in_array($Feld, (array) $Liste, true) )  return true;
    return false;
  }

  private function merkeUnbekanntesFeld($Feld)
  {
    $Felder = $this->unbekannteFelder();
    if ( in_array($Feld, $Felder, true) )  return;

    $Felder[]        = $Feld;
    $this->unbekannt = $Felder;    

    
    
    
    $Datei = $this->CS->prepareCachePath(array("settings"), "findexfelder");
    $Alt   = umask(0);
    @file_put_contents($Datei, json_encode($Felder), LOCK_EX);
    @chmod($Datei, 0666);
    umask($Alt);

    $this->CS->debug("ERR", array("type"   => "findex",
                                  "client" => (string) $this->CS->client,
                                  "error"  => "Kern kennt dieses Feld nicht, es wird ab jetzt weggelassen",
                                  "field"  => $Feld));
  }

  private function searchSolr($URL, $Query = "")
  {
    if ( APPDEBUG["FKT"] ) $this->CS->debug("FKT",__METHOD__);

    
    
    
    static $ch = null;
    if ( $ch === null )  $ch = curl_init();

    curl_setopt_array($ch, array(
      CURLOPT_URL            => $URL . "&",
      CURLOPT_POST           => true,
      CURLOPT_POSTFIELDS     => $Query,     
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_CONNECTTIMEOUT => 10,
      CURLOPT_TIMEOUT        => 60,         
      
      
      CURLOPT_ENCODING       => "",
      CURLOPT_SSL_VERIFYHOST => false,
      CURLOPT_SSL_VERIFYPEER => false,
    ));
    $Response = curl_exec($ch);

    if ( $CurlErrNo = curl_errno($ch) )
    {
      
      if ( $CurlErrNo == 28 )
      {
        file_put_contents(TRANSFERSDIR . "SolrTimeouts.txt", date("Y.m.d H:i:s") . " " . $URL . $Query . PHP_EOL, FILE_APPEND);
      }
      else
      {
        $this->CS->debug("ERR", array("type"      => "Solr Query",
                                      "errorCurl" => $CurlErrNo,
                                      "errorHTTP" => curl_getinfo($ch, CURLINFO_HTTP_CODE),
                                      "url"       => $URL,
                                      "query"     => $Query));
      }
      return array();
    }

    
    
    
    $Result = json_decode($Response, true);
    if ( !is_array($Result) )
    {
      
      
      
      
      
      
      
      
      
      
      
      
      
      
      
      
      $Code   = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
      $Sperre = ( $Code === 429 || $Code === 403 );
      $this->CS->debug("ERR", array("type"      => "Solr Response",
                                    "errorHTTP" => $Code,
                                    "errorJSON" => $Sperre ? "findex-Sperre: zu viele Abfragen" : json_last_error_msg(),
                                    "url"       => $URL,
                                    "query"     => $Query,
                                    "response"  => $Sperre ? "(Sperrseite der VZG, nicht mitgeschrieben)"
                                                           : substr((string) $Response, 0, 500)));
      return array();
    }
    return $Result;
  }

  
  
  
  
  
  
  private function buildQueryString()
  {
    $Parts = array();
    foreach ( $this->QueryArr as $Key => $Values )
    {
      foreach ( (array) $Values as $Value )
      {
        $Parts[] = rawurlencode($Key) . "=" . rawurlencode((string) $Value);
      }
    }
    return "&" . implode("&", $Parts);
  }

  
  
  
  
  
  
  
  
  
  
  
  
  
  private function ohneWortsuche($SQuery)
  {
    $Q = trim((string) $SQuery);
    if ( $Q === "" || $Q === "*" )  return true;
    foreach ( array("id:", "id(", "ppnlink:", "ppnlink(") as $Anfang )
      if ( strpos($Q, $Anfang) === 0 )  return true;
    return false;
  }

  
  
  
  
  
  
  
  
  
  
  
  private function wortzahl($SQuery)
  {
    $Q = trim((string) $SQuery);
    if ( $Q === "" )  return 0;
    return count(preg_split('/\s+/', $Q, -1, PREG_SPLIT_NO_EMPTY));
  }

  
  
  
  
  
  private function nurZaehler($Search)
  {
    return isset($Search["limit"]) && (int) $Search["limit"] === 0;
  }

  private function addFilter($Filter)
  {
    if ( trim($Filter) !== "" )  $this->QueryArr["fq"][] = $Filter;
  }

  
  
  

  private function getFieldPhrase($Val, $Fields)
  {
    return implode($Val . " OR ", $Fields) . $Val;
  }

  
  
  
  
  
  private function getLocFacetQuery($Vals, $Fields)
  {
    $Plain = array();
    $Regex = array();
    foreach ( $Vals as $Val )
    {
      if ( LocRule::isRegexRule($Val) )
      {
        $R = LocRule::toSolr($Val);
        if ( $R !== "" )  $Regex[] = $R;
      }
      else
      {
        $Plain[] = $Val;
      }
    }
    if ( !count($Regex) )  return $this->getFacetQuery($Vals, $Fields);

    $Tmp = array();
    foreach ( $Fields as $Field )
    {
      $Or = array();
      if ( count($Plain) )  $Or[] = $Field . ":(" . implode(" OR ", array_map(fn($Val) => $this->CleanVal($Val, true), $Plain)) . ")";
      foreach ( $Regex as $R )  $Or[] = $Field . ":" . $R;
      $Tmp[] = implode(" OR ", $Or);
    }
    return implode(" OR ", $Tmp);
  }
  private function getFacetQuery($Vals, $Fields)
  {
    $Vals = array_map(fn($Val) => $this->CleanVal($Val, true), $Vals);
    $Or   = implode(" OR ", $Vals);

    $Tmp = array();
    foreach ( $Fields as $Field )
    {
      $Tmp[] = $Field . ":(" . $Or . ")";
    }
    return implode(" OR ", $Tmp);
  }

  private function CleanVal($Val, $FormatSpace = false)
  {
    
    
    
    
    
    
    
    
    
    
    $From = array("\\",   "-",  "+",  ":",  "/",  "(",  ")",  "&&",  "||",  "!",  "{",  "}",  "[",  "]",  "^",  "~",  '"');
    $To   = array("\\\\", "\-", "\+", "\:", "\/", "\(", "\)", "\&&", "\||", "\!", "\{", "\}", "\[", "\]", "\^", "\~", '\"');
    if ( $FormatSpace )
    {
      $From[] =  " ";
      $To[]   = "\ ";
    }
    $Val = str_replace($From, $To, $Val);

    
    if ( strpos($Val, " ") !== false )  $Val = "(" . $Val . ")";

    return $Val;
  }

  private function splitPhraseValues($Phrase)
  {
    $Parts     = array();
    $Buffer    = "";
    $Depth     = 0;
    $QuoteChar = "";
    $Len       = strlen($Phrase);

    for ($i = 0; $i < $Len; $i++) {
      $Char = $Phrase[$i];

      
      if ($QuoteChar !== "") {
        $Buffer .= $Char;
        if ($Char == $QuoteChar) $QuoteChar = "";
        continue;
      }

      if ($Char == "\"" || $Char == "'") {
        $QuoteChar = $Char;
      } elseif ($Char == "(") {
        $Depth++;
      } elseif ($Char == ")") {
        if ($Depth > 0) $Depth--;
      } elseif ($Char == "," && $Depth == 0) {
        
        $Parts[] = $Buffer;
        $Buffer  = "";
        continue;
      }

      $Buffer .= $Char;
    }
    $Parts[] = $Buffer;

    return $Parts;
  }

  private function getFieldQuery($IntCom, $Query)
  {
    $Com  = strtolower(trim($IntCom));
    $ILNs = $this->CS->configs["ilns"] ?? array();

    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    $PhVals = array_values(array_filter(
                array_map("trim", $this->splitPhraseValues(preg_replace('/\s+/', ' ', trim($Query)))),
                fn($Val) => $Val !== ""
              ));

    
    
    
    if ( !count($PhVals) )  return "";

    
    
    if ( $Com == "geo" )
    {
      if ( count($PhVals) < 3 || !is_numeric($PhVals[0]) || !is_numeric($PhVals[1]) || !is_numeric($PhVals[2]) )  return "";
      return $this->getGeoFilter($PhVals[0], $PhVals[1], $PhVals[2]);
    }

    $NewVals = array();
    foreach ( $PhVals as $Val )
    {
      
      if ( in_array($Com, self::ILNCOLON) )
      {
        foreach ( $ILNs as $ILN )
        {
          $NewVals[] = ( substr($Val,0,1) == "\"" )
                     ? "\"" . $ILN . ":" . substr($Val,1)
                     : $this->CleanVal($ILN . ":" . $Val, true);
        }
      }
      
      elseif ( in_array($Com, self::ILNAT) )
      {
        foreach ( $ILNs as $ILN )
        {
          $NewVals[] = $this->CleanVal($ILN . "@" . $Val, true);
        }
      }
      
      elseif ( $Com == "publisher" )
      {
        $NewVals[] = $this->CleanVal($Val, true);
      }
      
      elseif ( $Com == "norm" )
      {
        $NewVals[] = "\(DE\-627\)" . $Val;
        $NewVals[] = "\(DE\-588\)" . $Val;
      }
      
      else
      {
        $NewVals[] = $this->CleanVal($Val, false);
      }
    }

    switch ( $Com )
    {
      case "from":
      case "von":
        return "hierarchy_sort_str:[" . ($NewVals[0] ?? "*") . " TO *]";
      case "until":
      case "bis":
        return "hierarchy_sort_str:[* TO " . ($NewVals[0] ?? "*") . "]";
    }

    $Phrase = ":(" . implode(" OR ", $NewVals) . ")";
    $Fields = self::FIELDS[$Com] ?? array($IntCom);
    
    
    
    
    
    if ( $this->phonetic && isset(self::FIELDSSOUND[$Com])
         && in_array(self::FIELDSSOUND[$Com], $this->soundFelder(), true) )
      $Fields[] = self::FIELDSSOUND[$Com];

    $FQuery = $this->getFieldPhrase($Phrase, $Fields);
    if ( $Com == "ppnlink" )  $FQuery .= " OR id" . $Phrase;  

    return $FQuery;
  }

  private function getGeoFilter($Lat, $Lng, $Radius)
  {
    
    
    
    return "{!geofilt sfield=coordinates_geo2 pt=" . $Lat . "," . $Lng . " d=" . $Radius . "}";
  }

  
  
  
  
  
  
  
  
  
  
  
  
  
  private function locationFacetGroups()
  {
    if ( APPDEBUG["FKT"] ) $this->CS->debug("FKT",__METHOD__);

    if ( !isset($this->CS->locations["exp"]) || !is_array($this->CS->locations["exp"]) )  return array();

    $Groups = array();
    foreach ( $this->CS->locations["exp"] as $Code => $Conds )
    {
      if ( !isset($Conds["loc"]) || !is_array($Conds["loc"]) || !count($Conds["loc"]) )  continue;
      $Groups[$Code] = $Conds["loc"];
    }

    
    
    
    
    foreach ( $this->mergeGroups() as $Sammel => $Teile )
    {
      $Vals  = array();
      foreach ( $Teile as $M )
      {
        if ( isset($Groups[$M]) )  $Vals = array_merge($Vals, $Groups[$M]);
        unset($Groups[$M]);
      }
      if ( count($Vals) )  $Groups[$Sammel] = array_values(array_unique($Vals));
    }

    return $Groups;
  }

  
  
  
  
  
  
  
  
  
  
  
  
  private function mergeGroups()
  {
    $Aus = array();
    foreach ( preg_split('/[\r\n;]+/', $this->CS->conf("facet", "locations.merge")) as $Zeile )
    {
      $Zeile = trim($Zeile);
      if ( $Zeile === "" || strpos($Zeile, "=") === false )  continue;
      list($Sammel, $Teile) = explode("=", $Zeile, 2);
      $Sammel = trim($Sammel);
      $Teile  = array_values(array_filter(array_map("trim", explode(",", $Teile)), "strlen"));
      if ( $Sammel !== "" && count($Teile) )  $Aus[$Sammel] = $Teile;
    }
    return $Aus;
  }

  private function isFlag($Arr, $Key)
  {
    return isset($Arr[$Key]) && $Arr[$Key];
  }

  
  
  
  
  
  
  
  
  private function countLimit($Arr, $Key, $Vorgabe)
  {
    $Wert = isset($Arr[$Key]) ? $Arr[$Key] : true;
    if ( is_numeric($Wert) && (int) $Wert > 0 )  return min((int) $Wert, 2000);
    return $Vorgabe;
  }

  private function isConfigYes($Category, $Setting)
  {
    return isset($this->CS->configs[$Category][$Setting]) && $this->CS->configs[$Category][$Setting] == "y";
  }

  private function hasValues($Arr, $Key)
  {
    return isset($Arr[$Key]) && is_array($Arr[$Key]) && count($Arr[$Key]);
  }

  
  
  

  private function formatClasslocalCode($Shortcut)
  {
    return str_replace(" ", "§", $this->formatClasslocalLabel($Shortcut));
  }

  private function formatClasslocalLabel($Shortcut)
  {
    $Tmp = explode(":", $Shortcut);
    if (count($Tmp) < 2) return "";

    return trim($Tmp[1]);
  }

  private function formatLabelTF($Label)
  {
    return str_replace(",", "{c}", trim($Label));
  }

  
  
  private function facetPairs($Result, $Field)
  {
    $List = $Result["facet_counts"]["facet_fields"][$Field] ?? null;
    if ( !is_array($List) )  return array();

    $Pairs = array();
    for ( $Count = 0; isset($List[$Count]) && isset($List[$Count+1]); $Count += 2 )
    {
      $Pairs[] = array($List[$Count], $List[$Count+1]);
    }
    return $Pairs;
  }

  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  private function frageSchreibvorschlag()
  {
    if ( APPDEBUG["FKT"] ) $this->CS->debug("FKT",__METHOD__);

    $Text = trim((string) $this->letzteWortsuche);
    if ( $Text === "" || $this->letzteAdresse === "" )  return null;

    $Alt = $this->QueryArr;
    $Neu = array();
    foreach ( array("q", "qf") as $Schluessel )
      if ( isset($Alt[$Schluessel]) )  $Neu[$Schluessel] = $Alt[$Schluessel];
    $Neu["rows"]        = array("0");
    $Neu["spellcheck"]  = array("true");
    $Neu["shards.qt"]   = array("/spell");

    $this->QueryArr = $Neu;
    $Abfrage        = $this->buildQueryString();
    $this->QueryArr = $Alt;

    if ( APPDEBUG["SLQ"] ) $this->CS->debug("SLQ", "[spell] " . $this->letzteAdresse . $Abfrage);

    
    
    $Ergebnis = $this->searchSolr($this->letzteAdresse, $Abfrage);
    if ( !is_array($Ergebnis) || isset($Ergebnis["error"]) )  return null;
    return $Ergebnis["spellcheck"] ?? null;
  }

  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  const STARTCACHE_SEKUNDEN = 300;

  
  
  
  private const STARTCACHE_SEITEN = array(0, 25);

  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  private function startCacheSchluessel($Url, $Query)
  {
    if ( $this->add || $this->nebenabfrage )  return "";

    $Sekunden = $this->CS->f3->get("api.findexcache");
    $Sekunden = ($Sekunden === null || $Sekunden === "") ? self::STARTCACHE_SEKUNDEN : (int) $Sekunden;
    if ( $Sekunden <= 0 )  return "";

    $Alles = (string) $Url . "|" . (string) $Query;

    
    if ( !preg_match('/[?&]q=([^&]*)/', (string) $Query, $T)
      && !preg_match('/[?&]q=([^&]*)/', (string) $Url,   $T) )  return "";
    if ( trim(urldecode($T[1])) !== "*" )  return "";

    
    
    
    
    
    
    
    
    $Datumsfilter = false;
    if ( preg_match_all('/[?&]fq=([^&]*)/', $Alles, $F) )
      foreach ( $F[1] as $Eine )
        if ( strpos(urldecode($Eine), "last_changed_iln_str_mv") !== false )  { $Datumsfilter = true; break; }
    if ( !$Datumsfilter )  return "";

    
    $Start = 0;
    if ( preg_match('/[?&]start=([0-9]+)/', $Alles, $S) )  $Start = (int) $S[1];
    if ( !in_array($Start, self::STARTCACHE_SEITEN, true) )  return "";

    return hash("sha256", $Alles);
  }

  private function ausStartCache($Schluessel, $Sekunden)
  {
    $Datei = $this->CS->prepareCachePath(array("findex"), $Schluessel);
    if ( !is_file($Datei) )                                return null;
    if ( (time() - (int) @filemtime($Datei)) > $Sekunden ) return null;

    $Inhalt = @file_get_contents($Datei);
    if ( $Inhalt === false )  return null;
    $Gelesen = json_decode((string) $Inhalt, true);
    
    return is_array($Gelesen) && isset($Gelesen["response"]) ? $Gelesen : null;
  }

  private function inStartCache($Schluessel, $Ergebnis)
  {
    if ( !is_array($Ergebnis) || !isset($Ergebnis["response"]) )  return;

    $Datei = $this->CS->prepareCachePath(array("findex"), $Schluessel);
    $Neu   = !file_exists($Datei);
    
    
    if ( @file_put_contents($Datei, json_encode($Ergebnis), LOCK_EX) !== false && $Neu )
      @chmod($Datei, 0666);
  }

  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  private function startCacheZaehlen($Feld)
  {
    try
    {
      $Datei = $this->CS->prepareCachePath(array("findex"), "_zaehler");
      $Neu   = !file_exists($Datei);
      $fh    = @fopen($Datei, "c+");
      if ( $fh === false )  return;
      if ( !@flock($fh, LOCK_EX) ) { @fclose($fh); return; }

      $Inhalt = stream_get_contents($fh);
      $Stand  = json_decode((string) $Inhalt, true);
      if ( !is_array($Stand) )  $Stand = array();
      $Stand[$Feld] = (int) ($Stand[$Feld] ?? 0) + 1;

      rewind($fh);
      ftruncate($fh, 0);
      fwrite($fh, json_encode($Stand));
      fflush($fh);
      @flock($fh, LOCK_UN);
      @fclose($fh);
      if ( $Neu )  @chmod($Datei, 0666);
    }
    catch ( \Throwable $E ) {  }
  }

  private function processSolr($Url, $Query)
  {
    if ( APPDEBUG["FKT"] ) $this->CS->debug("FKT",__METHOD__);

    
    
    $this->letzteAdresse = $Url;

    
    $Schluessel = $this->startCacheSchluessel($Url, $Query);
    $Sekunden   = $this->CS->f3->get("api.findexcache");
    $Sekunden   = ($Sekunden === null || $Sekunden === "") ? self::STARTCACHE_SEKUNDEN : (int) $Sekunden;
    $Result     = $Schluessel ? $this->ausStartCache($Schluessel, $Sekunden) : null;

    if ( is_array($Result) )
    {
      $this->startCacheZaehlen("treffer");
      if ( APPDEBUG["SLQ"] ) $this->CS->debug("SLQ", "[cache] " . $Url . $Query);
    }
    else
    {
      $Result = $this->searchSolr($Url, $Query);
      if ( $Schluessel )
      {
        $this->startCacheZaehlen("neu");
        $this->inStartCache($Schluessel, $Result);
      }
    }

    
    $Container = isset($Result["response"]["docs"]) ? $Result["response"]["docs"] : array();

    
    if ( $this->add )  return $Container;

    
    if ( isset($Result["error"]) )
    {
      
      
      
      
      $Fehlercode = isset($Result["error"]["code"]) ? (int) $Result["error"]["code"] : 0;
      $Meldung    = isset($Result["error"]["msg"]) ? (string) $Result["error"]["msg"] : "";

      
      
      
      
      
      
      
      
      
      
      
      
      
      $Treffer = array();
      if ( !preg_match("/Query Field '([^']+)' is not a valid field name/", $Meldung, $Treffer) )
             preg_match("/undefined field ([A-Za-z0-9_]+)/i", $Meldung, $Treffer);

      if ( !$this->zweiterVersuch && !empty($Treffer[1]) )
      {
        $this->zweiterVersuch = true;
        $Feld = $Treffer[1];
        
        if ( $this->istEigenesFeld($Feld) )  $this->merkeUnbekanntesFeld($Feld);

        $Geaendert = false;

        
        if ( isset($this->QueryArr["qf"]) )
        {
          $Vorher = $this->QueryArr["qf"];
          foreach ( $this->QueryArr["qf"] as $i => $Liste )
          {
            $Rest = array_values(array_filter(explode(" ", (string) $Liste),
                      function($F) use ($Feld) { return $F !== "" && strtok($F, "^") !== $Feld; }));
            $this->QueryArr["qf"][$i] = implode(" ", $Rest);
          }
          if ( $this->QueryArr["qf"] !== $Vorher && trim(implode(" ", $this->QueryArr["qf"])) !== "" )
            $Geaendert = true;
          else
            $this->QueryArr["qf"] = $Vorher;    
        }

        
        
        
        
        
        foreach ( $this->QueryArr as $Schluessel => $Werte )
        {
          if ( $Schluessel === "qf" )  continue;
          foreach ( (array) $Werte as $i => $Wert )
          {
            if ( strpos((string) $Wert, $Feld) === false )  continue;
            $Neu = preg_replace('/(?:\s+OR\s+)?\b' . preg_quote($Feld, "/") . ':\([^()]*\)/', "", (string) $Wert);
            $Neu = trim(preg_replace('/^\s*OR\s+|\s+OR\s*$/', "", (string) $Neu));
            if ( $Neu !== "" && $Neu !== (string) $Wert )
            {
              $this->QueryArr[$Schluessel][$i] = $Neu;
              $Geaendert = true;
            }
          }
        }

        
        
        if ( $Geaendert )  return $this->processSolr($Url, $this->buildQueryString());
      }

      
      
      
      
      
      
      
      
      
      
      
      
      
      $Haus = (string) $this->CS->client;
      $Kern = preg_match("#/solr/([^/?]+)#", (string) $Url, $K) ? $K[1]
              : (preg_match("#findex\.gbv\.de/([^?]+?)/select#", (string) $Url, $K2) ? $K2[1] : "");

      
      
      
      
      
      
      
      
      
      
      
      
      
      
      
      
      
      
      
      
      
      
      
      
      
      
      
      
      if ( stripos((string) $Meldung, "maxClauseCount") !== false
        || stripos((string) $Meldung, "too many nested clauses") !== false )
      {
        $this->CS->debug("ERR", array(
          "type"    => "findex",
          "client"  => $Haus,
          "core"    => $Kern,
          "http"    => $Fehlercode,
          "error"   => "Suche hat zu viele Woerter - der Index kann sie nicht beantworten",
          "woerter" => $this->wortzahl($this->letzteWortsuche),
          "query"   => substr((string) $Query, 0, 500)
        ));
        $this->CS->results["code"] = "toomanywords";
        return array();
      }

      $this->CS->debug("ERR", array(
        "type"    => "findex",
        "client"  => $Haus,
        "core"    => $Kern,
        "http"    => $Fehlercode,
        "message" => $Meldung,
        "query"   => substr((string) $Query, 0, 500)
      ));

      
      
      
      
      
      
      
      
      
      
      
      
      
      
      
      $this->CS->mailFehlerGedrosselt("findex-Fehler", $Haus, $Meldung, array(
        "Kern"     => $Kern,
        "HTTP"     => $Fehlercode,
        "Meldung"  => $Meldung,
        "Abfrage"  => substr((string) $Query, 0, 500)
      ));

      $this->CS->results["status"]  = $Fehlercode > 0 ? -$Fehlercode : -1;
      $this->CS->results["message"] = $Meldung;
      $this->CS->results["qtime"]   = (isset($Result["responseHeader"]["QTime"])) ? $Result["responseHeader"]["QTime"] : "0";
      return array();
    }

    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    if ( !isset($Result["response"]) && !$this->nebenabfrage )
    {
      
      
      
      
      
      
      
      
      
      
      
      
      
      
      $Agent = trim((string) ($_SERVER["HTTP_USER_AGENT"] ?? ""));
      $this->CS->debug("ERR", array(
        "type"    => "findex",
        "client"  => (string) $this->CS->client,
        "core"    => preg_match("#findex\.gbv\.de/([^?]+?)/select#", (string) $Url, $K3) ? $K3[1] : "",
        "error"   => "Antwort ohne \"response\" - die Abfrage wurde nicht beantwortet",
        "herkunft" => (string) $this->CS->clientip,
        "abrufer" => ($Agent === "" ? "-" : substr($Agent, 0, 120)),
        "antwort" => substr(json_encode($Result), 0, 300)
      ));
      
      
      
      
      
      
      if ( !empty($Result) )
        $this->CS->mailFehlerGedrosselt("findex-Fehler", (string) $this->CS->client,
          "ohne response: " . substr(json_encode($Result), 0, 120),
          array("Antwort" => substr(json_encode($Result), 0, 300),
                "Abfrage" => substr((string) $Query, 0, 500)));

      
      
      
      
      
      
      
      
      
      
      $Fremd = isset($Result["status"]) && is_numeric($Result["status"]) ? (int) $Result["status"] : 0;
      $this->CS->results["status"]  = $Fremd > 0 ? -$Fremd : -1;
      $this->CS->results["code"]    = "findexerror";
      $this->CS->results["message"] = isset($Result["message"])
                                      ? (string) $Result["message"]
                                      : "findex hat die Abfrage nicht beantwortet";

      
      
      
      
      
      
      
      
      
      
      
      
      
      $this->CS->results["hits"]     = 0;
      $this->CS->results["hitspack"] = 0;
      $this->CS->results["start"]    = 0;
      $this->CS->results["qtime"]    = "";
      return array();
    }

    
    
    
    
    $this->CS->results["status"]   = 0;
    $this->CS->results["qtime"]    = isset($Result["responseHeader"]["QTime"]) ? str_replace(".", ",", round($Result["responseHeader"]["QTime"] / 1000, 2)) : "";
    $this->CS->results["hits"]     = isset($Result["response"]["numFound"])    ? $Result["response"]["numFound"]    : "0";
    $this->CS->results["hitspack"] = isset($Result["response"]["docs"])        ? count($Result["response"]["docs"]) : "0";
    $this->CS->results["start"]    = isset($Result["response"]["start"])       ? $Result["response"]["start"]       : "0";

    
    if ( $this->SkipID )
    {
      $Idx = array_search($this->SkipID, array_column($Container, "id"), true);
      if ( $Idx !== false )
      {
        array_splice($Container, $Idx, 1);
        $this->CS->results["hitspack"] = $this->CS->results["hitspack"] - 1;
        $this->CS->results["hits"]     = $this->CS->results["hits"] - 1;
      }
    }

    
    
    
    
    
    
    
    
    
    if ( $this->spellMoeglich && (int) $this->CS->results["hits"] === 0
      && (int) $this->CS->results["start"] === 0 )
    {
      $Vorschlag = $this->frageSchreibvorschlag();
      if ( is_array($Vorschlag) )  $Result["spellcheck"] = $Vorschlag;
    }

    
    $Suggestions = $Result["spellcheck"]["suggestions"] ?? null;
    if ( is_array($Suggestions) )
    {
      for ( $Count = 0; isset($Suggestions[$Count]) && isset($Suggestions[$Count+1]); $Count += 2 )
      {
        $this->CS->results["spell"][$Suggestions[$Count]] = array_column($Suggestions[$Count+1]["suggestion"] ?? array(), "word");
      }
    }

    
    if ( isset($Result["highlighting"]) && is_array($Result["highlighting"]) )
    {
      foreach ( $Result["highlighting"] as $HID => $Snip )
      {
        if ( !isset($Snip["fulltext"]) || !is_array($Snip["fulltext"]) )  continue;
        $this->CS->results["highlight"][$HID] = array_reverse(array_values($Snip["fulltext"]));
        if ( isset($this->CS->results["highlight"][$HID][9]) )  $this->CS->results["highlight"][$HID][9] = "+";
      }
    }

    $this->readFacets($Result);

    return $Container;
  }

  private function readFacets($Result)
  {
    $Counts = &$this->CS->results["counts"];

    
    foreach ( $this->facetPairs($Result, "remote_bool") as [$Val, $Cnt] )
    {
      $Type          = ( $Val == "true" ) ? "online" : "physical";
      $Counts["types"][$Type] = array("label" => $this->CS->getText("type." . $Type),
                                      "count" => $Cnt);
    }

    
    foreach ( $this->facetPairs($Result, "format_phy_str_mv") as [$Val, $Cnt] )
    {
      $Counts["formats"][$Val] = array("label" => $this->CS->getText("format." . $Val),
                                       "count" => $Cnt);
    }

    
    foreach ( $this->facetPairs($Result, "lang_code") as [$Val, $Cnt] )
    {
      $L = $this->CS->getText("lang." . $Val);
      if ( $L )  $Counts["languages"][] = array("code" => $Val, "label" => $L, "count" => $Cnt);
    }

    
    foreach ( $this->facetPairs($Result, "topic_facet") as [$Val, $Cnt] )
    {
      $Counts["topicfacets"][] = array("label" => $this->formatLabelTF($Val), "count" => $Cnt);
    }

    
    $Code2Expand = $this->isConfigYes("facet", "classlocalsexpandcode");
    foreach ( $this->CS->configs["ilns"] ?? array() as $ILN )
    {
      foreach ( $this->facetPairs($Result, "locclass_" . $ILN) as [$Val, $Cnt] )
      {
        if ( $Code2Expand )
        {
          $C   = $this->formatClasslocalCode($Val);
          $C2E = $this->CS->getText("classlocal." . strtolower(str_replace("§", "", $C)));
          if ( $C2E )  $Counts["classlocals"][] = array("code" => $C, "label" => $C2E, "count" => $Cnt);
          continue;
        }

        $Label = $this->formatClasslocalLabel($Val);
        
          
          
          
          
          if ( $this->isConfigYes("facet", "classlocals.yearsonly")
            && !preg_match('/^\d{4}\/\d{2,4}$/', $Label) )  continue;
        $Counts["classlocals"][] = array("code" => $Label, "label" => $Label, "count" => $Cnt);
      }
    }

    
    if ( count($this->locfacetkeys) && isset($Result["facet_counts"]["facet_queries"])
      && is_array($Result["facet_counts"]["facet_queries"]) )
    {
      foreach ( $this->locfacetkeys as $Key => $Code )
      {
        if ( !isset($Result["facet_counts"]["facet_queries"][$Key]) )  continue;
        $Cnt = (int) $Result["facet_counts"]["facet_queries"][$Key];
        
        if ( $Cnt < 1 )  continue;
        $Counts["locations"][$Code] = array("code"  => $Code,
                                            "count" => $Cnt);
      }
    }

    
    foreach ( $this->facetPairs($Result, "format_main_str_mv") as [$Val, $Cnt] )
    {
      $Counts["formatsmain"][] = array("label" => $Val, "count" => $Cnt);
    }

    
    foreach ( $this->facetPairs($Result, "format_details_str_mv") as [$Val, $Cnt] )
    {
      $Counts["genres"][] = array("label" => $Val, "count" => $Cnt);
    }

    
    foreach ( $this->facetPairs($Result, "topic_bkl") as [$Val, $Cnt] )
    {
      $Code = substr($Val, 4, 5);  
      $CRec = $this->CS->getCDBClass("bkl", $Code);
      if ( isset($CRec["description"]) )  $Counts["topicbkls"][] = array("label" => $CRec["description"], "code" => "bkl " . $Code, "count" => $Cnt);
    }

    
    foreach ( $this->facetPairs($Result, "topic_rvk") as [$Val, $Cnt] )
    {
      $Code = substr($Val, 4);  
      $CRec = $this->CS->getCDBClass("rvk", $Code);
      if ( isset($CRec["description"]) )  $Counts["topicrvks"][] = array("label" => $CRec["description"], "code" => "rvk " . $Code, "count" => $Cnt);
    }

    
    
    unset($Counts);
    if ( array_key_exists("counts", $this->CS->results) && $this->CS->results["counts"] === null )  unset($this->CS->results["counts"]);
  }

  
  
  
  
  
  
  
  
  
  private function finishLocationFacet()
  {
    if ( $this->add || !isset($this->CS->results["counts"]["locations"])
      || !is_array($this->CS->results["counts"]["locations"])
      || !count($this->CS->results["counts"]["locations"]) )  return;

    $New = $this->CS->results["counts"]["locations"];

    
    
    
    
    
    foreach ( $New as $K => $Loc )
    {
      if ( !is_array($Loc) || !isset($Loc["code"]) )  continue;
      $Txt = $this->CS->getText("location." . strtolower($Loc["code"]));
      if ( $Txt !== "" )  $New[$K]["label"] = $Txt;
    }

    $Counts = array_column($New, 'count');
    array_multisort($Counts, SORT_DESC, $New);
    $this->CS->results["counts"]["locations"] = $New;
  }

  
  
  
  
  
  
  
  
  
  
  private function acquisitionDate($Date)
  {
    $Parts = explode("-", trim($Date));
    if ( count($Parts) != 3 )  return "";

    list($Day, $Month, $Year) = $Parts;
    if ( strlen($Year) == 2 )  $Year = ( (int) $Year > (int) date("y") ? "19" : "20" ) . $Year;
    return $Year . "-" . str_pad($Month, 2, "0", STR_PAD_LEFT) . "-" . str_pad($Day, 2, "0", STR_PAD_LEFT);
  }

  private function sortByAcquisition($Data)
  {
    $ILNs    = $this->CS->configs["ilns"] ?? array();
    $ACQSort = array();
    foreach ( $Data as $i => $R )
    {
      if ( !isset($R["last_changed_iln_str_mv"]) || !is_array($R["last_changed_iln_str_mv"]) )  continue;

      foreach ( $R["last_changed_iln_str_mv"] as $S )
      {
        foreach ( $ILNs as $ILN )
        {
          if ( strncmp($ILN . "@", $S, strlen($ILN) + 1) !== 0 )  continue;
          $Date = $this->acquisitionDate(substr($S, strlen($ILN) + 1));
          if ( $Date === "" )  continue;
          if ( !isset($ACQSort[$i]) || $Date < $ACQSort[$i] )  $ACQSort[$i] = $Date;
        }
      }
    }
    arsort($ACQSort, SORT_STRING);

    $Sorted = array();
    foreach ( array_keys($ACQSort) as $i )  $Sorted[] = $Data[$i];
    foreach ( $Data as $i => $R )
    {
      if ( !isset($ACQSort[$i]) )  $Sorted[] = $R;
    }
    return $Sorted;
  }

  
  
  

  private function doSearch()
  {
    if ( APPDEBUG["FKT"] ) $this->CS->debug("FKT",__METHOD__);

    $Search         = $this->CS->search;
    $SType          = (isset($Search["search"]["type"])   && trim($Search["search"]["type"]) == "lukida") ? "lukida" : "findex";
    $SQuery         = (isset($Search["search"]["query"])  && trim($Search["search"]["query"]))        ? trim($Search["search"]["query"])  : "*";
    $SUrl           = (isset($Search["url"])              && trim($Search["url"]))                    ? trim($Search["url"])              : "";
    $this->phonetic = $this->isFlag($Search, "phonetic");
    $this->SkipID   = (isset($Search["search"]["skipid"]) && trim($Search["search"]["skipid"]))       ? trim($Search["search"]["skipid"]) : "";
    $Facets         = ( isset($Search["facets"]) && is_array($Search["facets"]) ) ? $Search["facets"] : array();
    $Counts         = ( isset($Search["counts"]) && is_array($Search["counts"]) ) ? $Search["counts"] : array();
    $ILNs           = $this->CS->configs["ilns"] ?? array();

    
    if ( $SType == "lukida" )
    {
      
      $SQuery .= " ";
      $BracketsRegex = "/([a-zA-Z]+)(?:\((?P<paren>(?:[^()\"']*|\"[^\"]*\"|'[^']*'|\([^()]*\))*)\)|:(?P<colon>\"[^\"]*\"|'[^']*'|[a-zA-Z0-9_\-*]+))/";

      
      
      
      
      
      $Filters = array();
      $SQuery  = preg_replace_callback($BracketsRegex, function ($m) use (&$Filters) {
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        $Paren = isset($m['paren']) ? $m['paren'] : "";
        $Colon = isset($m['colon']) ? $m['colon'] : "";
        $Wert  = ($Paren !== "") ? $Paren : $Colon;

        
        
        
        
        
        
        
        
        
        
        if ( !$this->istBekannterBefehl($m[1]) )  return $m[0];

        
        
        
        
        
        
        
        
        
        if ( trim($Wert) === "" )  return "";

        
        
        
        
        
        $Filters[] = array($m[1], $Wert);
        return "";
      }, $SQuery) ?? $SQuery;
      
      $SQuery = trim($this->entschaerfeResttext($SQuery));

      foreach ( $Filters as [$Command, $Filter] )
      {
        $this->addFilter($this->getFieldQuery($Command, $Filter));
      }
      if ( isset($this->QueryArr["fq"]) )  $this->QueryArr["fq"] = array_values(array_unique($this->QueryArr["fq"]));

      
      
      
      
      
      
      $this->QueryArr["fl"][] = $this->nurZaehler($Search)
        ? "id"
        : "id,fullrecord_marcxml,format_phy_str_mv,remote_bool,collection,collection_details,publishDateSort,last_changed_iln_str_mv,up_date";

      
      if ( $SQuery )
      {
        if ( isset($Facets["target"]) && $Facets["target"] == "fulltext" )
        {
          $QueryFields = array("fulltext^100");
        }
        else
        {
          
          
          $QueryFields = $this->gewichtungsFelder();
          
          
          
          
          
          if ( $this->phonetic )
            foreach ( $this->soundFelder() as $Feld )
              $QueryFields[] = $Feld . "^" . self::SOUNDGEWICHT[$Feld];
        }
        $this->QueryArr["qf"][] = implode(" ", $QueryFields);
      }
    }
    $this->QueryArr["q"][] = trim($SQuery) ? trim($SQuery) : "*";

    
    if ( isset($Search["start"]) )  $this->QueryArr["start"][] = $Search["start"];
    if ( isset($Search["limit"]) )  $this->QueryArr["rows"][]  = $Search["limit"];

    $NewSearch     = isset($Search["start"]) && $Search["start"] == 0;
    $this->level   = isset($Search["level"]) ? $Search["level"] : "";

    
    $Sort          = isset($Search["sort"]) ? $Search["sort"] : "";
    $this->sortacq = ( $Sort == "acqdesc" );
    
    
    
    $Levels        = isset($this->CS->configs["levels"]) && is_array($this->CS->configs["levels"]) ? $this->CS->configs["levels"] : array();
    $EigenSort     = ( $this->level !== "" && isset($Levels[$this->level]["sortfield"]) ) ? trim((string) $Levels[$this->level]["sortfield"]) : "";
    $SortField     = ( $EigenSort !== "" ) ? $EigenSort : "publishDateSort";
    if     ( $Sort == "yeardesc" )  $SortBy = $SortField . " desc";
    elseif ( $Sort == "yearasc"  )  $SortBy = $SortField . " asc";
    
    
    
    
    
    
    
    
    elseif ( $Sort == "authorasc" )  $SortBy = "author_sort asc";
    
    
    
    
    
    
    else                            $SortBy = "score desc," . $SortField . " desc";
    
    
    
    
    $this->QueryArr["sort"][] = $SortBy . ",id asc";
    
    
    
    
    
    $this->QueryArr["shards.preference"][] = "replica.base:stable:hash:q";

    
    
    
    $this->letzteWortsuche = $SQuery;

    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    $this->spellMoeglich = ( isset($Search["spell"]) && $NewSearch
                             && !$this->ohneWortsuche($SQuery)
                             && !$this->nurZaehler($Search)
                             && $this->wortzahl($SQuery) >= 1
                             && $this->wortzahl($SQuery) <= self::SPELL_MAX_WORTE
                             && (int) ($Search["start"] ?? 0) === 0 );

    
    if ( isset($Facets["type"]) && in_array($Facets["type"], array("online","physical")) )
    {
      $this->addFilter("remote_bool:" . ( $Facets["type"] == "online" ? "true" : "false" ));
    }

    
    $Range = array("min" => 785, "max" => intval(date("Y")));
    $Min   = ( isset($Facets["yearmin"]) && is_numeric($Facets["yearmin"])
            && $Facets["yearmin"] >= $Range["min"] && $Facets["yearmin"] <= $Range["max"] ) ? $Facets["yearmin"] : $Range["min"];
    $Max   = ( isset($Facets["yearmax"]) && is_numeric($Facets["yearmax"])
            && $Facets["yearmax"] >= $Range["min"] && $Facets["yearmax"] <= $Range["max"] ) ? $Facets["yearmax"] : $Range["max"];
    if ( $Min != $Range["min"] || $Max != $Range["max"] )
    {
      $this->addFilter("publishDateSort:[" . str_pad($Min, 4, "0", STR_PAD_LEFT) . " TO " . str_pad($Max, 4, "0", STR_PAD_LEFT) . "]");
    }

    
    if ( $this->hasValues($Facets, "formats") )    $this->addFilter($this->getFacetQuery($Facets["formats"], array("format_phy_str_mv")));

    
    if ( isset($Facets["maps"]["lat"]) && isset($Facets["maps"]["lng"]) && isset($Facets["maps"]["radius"])
      && is_numeric($Facets["maps"]["lat"]) && is_numeric($Facets["maps"]["lng"]) && is_numeric($Facets["maps"]["radius"]) )
    {
      $this->addFilter($this->getGeoFilter($Facets["maps"]["lat"], $Facets["maps"]["lng"], $Facets["maps"]["radius"]));
    }

    
    if ( $this->hasValues($Facets, "languages") )  $this->addFilter($this->getFacetQuery($Facets["languages"], array("lang_code")));

    
    if ( $this->hasValues($Facets, "topicfacets") )
    {
      $this->addFilter($this->getFacetQuery(str_replace("{c}", ",", $Facets["topicfacets"]), array("topic_facet")));
    }

    
    if ( $this->hasValues($Facets, "classlocals") )
    {
      $Code2Expand = $this->isConfigYes("facet", "classlocalsexpandcode");
      $Ss = array();
      foreach ( $ILNs as $ILN )
      {
        foreach ( $Facets["classlocals"] as $I )
        {
          $Ss[] = $ILN . ":" . ($Code2Expand ? str_replace("§", " ", $I) : $I);
        }
      }
      $this->addFilter($this->getFacetQuery($Ss, array("notation_local_iln_str_mv")));
    }

    
    $LocWOnline = $this->isConfigYes("facet", "locationswithonlines");
    if ( $this->hasValues($Facets, "locations") )
    {
      $Codes = $Facets["locations"];
      
      
      
      foreach ( $this->mergeGroups() as $Sammel => $Teile )
      {
        $K = array_search($Sammel, $Codes);
        if ( $K === false )  continue;
        unset($Codes[$K]);
        $Codes = array_merge($Codes, $Teile);
      }

      
      
      $NewL = array();
      foreach ( $Codes as $Code )
      {
        if ( isset($this->CS->locations["exp"][$Code]["loc"]) && is_array($this->CS->locations["exp"][$Code]["loc"]) )
        {
          $NewL = array_merge($NewL, $this->CS->locations["exp"][$Code]["loc"]);
        }
      }
      if ( count($NewL) )
      {
        $Q = $this->getLocFacetQuery(array_values(array_unique($NewL)), array("standort_iln_str_mv"));
        $this->addFilter($LocWOnline ? "(" . $Q . " OR remote_bool:true)" : $Q);
      }
    }

    
    if ( $this->hasValues($Facets, "formatsmain") )  $this->addFilter($this->getFacetQuery($Facets["formatsmain"], array("format_main_str_mv")));

    
    if ( $this->hasValues($Facets, "genres") )       $this->addFilter($this->getFacetQuery($Facets["genres"], array("format_details_str_mv")));

    
    if ( $this->hasValues($Facets, "topicbkls") )    $this->addFilter($this->getFacetQuery($Facets["topicbkls"], array("topic_browse")));

    
    if ( $this->hasValues($Facets, "topicrvks") )    $this->addFilter($this->getFacetQuery($Facets["topicrvks"], array("topic_browse")));

    
    if ( $this->hasValues($Facets, "links") )
    {
      $LinkGroups = array();
      foreach ( $Facets["links"] as $Link )
      {
        if ( preg_match('/^([a-zA-Z]+)\((.*)\)$/', trim($Link), $LinkMatch) )
        {
          $LinkGroups[$LinkMatch[1]][] = $LinkMatch[2];
        }
      }
      foreach ( $LinkGroups as $LinkCommand => $LinkValues )
      {
        $this->addFilter($this->getFieldQuery($LinkCommand, implode(",", $LinkValues)));
      }
    }

    
    
    
    
    
    
    
    $WantCounts = false;
    foreach ( self::COUNTS as $C )
    {
      if ( $this->isFlag($Counts, $C) )  $WantCounts = true;
    }
    if ( $NewSearch && $WantCounts )
    {
      
      $this->QueryArr["facet"][]      = "true";
      $this->QueryArr["facet.sort"][] = "count";

      
      $Limit = 25;
      if ( $this->isFlag($Counts, "types") )        $this->QueryArr["facet.field"][] = "remote_bool";
      if ( $this->isFlag($Counts, "formats") )      $this->QueryArr["facet.field"][] = "format_phy_str_mv";
      foreach ( array("languages" => "lang_code", "topicfacets" => "topic_facet", "formatsmain" => "format_main_str_mv", "genres" => "format_details_str_mv") as $C => $Field )
      {
        if ( !$this->isFlag($Counts, $C) )  continue;
        $this->QueryArr["facet.field"][]                     = $Field;
        $this->QueryArr["f." . $Field . ".facet.limit"][]    = $this->countLimit($Counts, $C, $Limit);
        
        
        
        $this->QueryArr["f." . $Field . ".facet.mincount"][] = 1;
      }

      
      if ( isset($Counts["classlocals"]) )
      {
        foreach ( $ILNs as $ILN )
        {
          $this->QueryArr["facet.field"][] = "{!key=locclass_" . $ILN . " facet.prefix=" . $ILN . ":}notation_local_iln_str_mv";
        }
        $this->QueryArr["f.notation_local_iln_str_mv.facet.mincount"][] = 1;
        $this->QueryArr["f.notation_local_iln_str_mv.facet.limit"][]    = $this->countLimit($Counts, "classlocals", $Limit);
      }

      
      
      
      
      
      
      
      
      
      
      
      
      
      
      
      
      
      
      
      
      
      
      
      
      
      
      
      
      
      
      
      
      if ( isset($Counts["locations"]) )
      {
        $this->locfacetkeys = array();

        
        
        
        foreach ( $this->locationFacetGroups() as $Code => $Vals )
        {
          $Key = "loc" . count($this->locfacetkeys);
          $this->locfacetkeys[$Key] = $Code;

          $Q = $this->getLocFacetQuery($Vals, array("standort_iln_str_mv"));
          if ( $LocWOnline )  $Q = "(" . $Q . " OR remote_bool:true)";

          $this->QueryArr["facet.query"][] = "{!key=" . $Key . "}" . $Q;
        }
      }

      
      foreach ( array("topicbkls" => "bkl", "topicrvks" => "rvk") as $C => $Prefix )
      {
        if ( !isset($Counts[$C]) )  continue;
        $this->QueryArr["facet.field"][]                   = "{!key=topic_" . $Prefix . " facet.prefix=" . $Prefix . "}topic_browse";
        $this->QueryArr["f.topic_browse.facet.mincount"][] = 1;
        $this->QueryArr["f.topic_browse.facet.limit"][]    = $Limit;
      }
    }

    
    if ( $this->isConfigYes("init", "highlight") && !$this->ohneWortsuche($SQuery)
         && !$this->nurZaehler($Search) )
    {
      $this->QueryArr["hl"][]                  = "true";
      $this->QueryArr["hl.fl"][]               = "fulltext";
      $this->QueryArr["hl.snippets"][]         = "10";
      $this->QueryArr["hl.simple.pre"][]       = "<em>";
      $this->QueryArr["hl.simple.post"][]      = "</em>";
      $this->QueryArr["hl.maxAnalyzedChars"][] = 100000;
    }

    $Q = $this->buildQueryString();

    
    if ( APPDEBUG["SLQ"] ) $this->CS->debug("SLQ", $SUrl . $Q);

    
    $Data = $this->processSolr($SUrl, $Q);

    
    if ( APPDEBUG["IDS"] )
    {
      $DataIDs = array_column($Data, "id");
      foreach ( is_array(APPDEBUG["IDS"]) ? APPDEBUG["IDS"] : array(trim(APPDEBUG["IDS"])) as $ID )
      {
        if ( ($Key = array_search($ID, $DataIDs)) !== false )
        {
          $this->CS->debug("IDS", $ID);
          $this->CS->debug("SLD", $Data[$Key]);
        }
      }
    }

    
    if ( APPDEBUG["SLD"] ) $this->CS->debug("SLR", $Data);

    $this->finishLocationFacet();

    if ( $this->sortacq && count($Data) )  $Data = $this->sortByAcquisition($Data);

    return $Data;
  }

  
  
  
  public function getSearch($Add)
  {
    if ( APPDEBUG["FKT"] ) $this->CS->debug("FKT",__METHOD__);

    $this->add = $Add;
    $this->CS->works["solr"] = $this->doSearch();
  }

  public function getProject($URL, $Query, $Project)
  {
    if ( APPDEBUG["FKT"] ) $this->CS->debug("FKT",__METHOD__);

    
    
    
    
    
    $this->nebenabfrage = true;
    $this->CS->works["project"][$Project] = $this->processSolr($URL, $Query);
    $this->nebenabfrage = false;
  }
}

?>
