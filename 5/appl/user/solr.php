<?php

// Solr-Treiber: eine Abfrage je Seite, Zwischenspeicher, Drossel ([api] indexcache/indexlimit)
class solr
{
  // Suchbefehle, z. B. titel:faust, id(123,456)
  private const FIELDS = array(
    "author"             => array("author", "author2", "author_os_txtP_mv", "GND_txt_mv"),
    "autor"              => array("author", "author2", "author_os_txtP_mv", "GND_txt_mv"),
    "class"              => array("class"),
    "classlocal"         => array("notation_local_iln_str_mv"),
    "genre"              => array("genre_facet"),
    "topic"              => array("topic"),
    "thema"              => array("topic"),
    "sachgebiet"         => array("class"),
    "corporation"        => array("author_corporate", "GND_txt_mv"),
    "koerper"            => array("author_corporate", "GND_txt_mv"),
    "doi"                => array("doi_str_mv"),
    "format"             => array("format_phy_str_mv"),
    "id"                 => array("id"),
    "ppn"                => array("id"),
    "isn"                => array("isbn", "issn"),
    "language"           => array("language"),
    "sprache"            => array("language"),
    "location"           => array("standort_iln_str_mv", "standort_weitere_iln_str_mv"),
    "standort"           => array("standort_iln_str_mv", "standort_weitere_iln_str_mv"),
    "norm"               => array("normlink_prefix_str_mv"),
    "ppnlink"            => array("ppnlink"),
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
    "year"               => array("publishDateSort"),
    "jahr"               => array("publishDateSort"),
  );

  private const QUERYGEWICHT = array(
    "title_short"  => 770, "title_full"   => 740, "title_alt"     => 710, "title_new"   => 700,
    "topic"        => 600, "author"       => 520, "author2"       => 510, "author_fuller" => 500,
    "genre_facet"  => 200, "geographic"   => 210, "series2"       => 100, "series"      => 110,
    "allfieldsGer" =>  10, "allfields"    =>  10, "contents"      =>  10, "description" =>   5,
    "fulltext"     =>   5, "GND_txt_mv"   =>   1, "isbn"          =>   1, "issn"        =>   1,
  );

  // Werte mit vorangestellter ILN
  private const ILNCOLON = array("classlocal", "location", "shelfmark", "signatur", "signature", "standort");

  private const FL = "id,fullrecord_marcxml,format_phy_str_mv,remote_bool,collection,collection_details,publishDateSort,last_changed_iln_str_mv,up_date";

  const CACHE_SEKUNDEN = 600;
  const LIMIT_JE_MINUTE = 60;
  const LIMIT_WARTEN_HOECHSTENS = 5;

  private $add;
  private $CS;
  private $QueryArr;
  private $SkipID;
  private $zweiterVersuch;
  private $unbekannt;
  private $letzteWortsuche = "";

  public function __construct($CS)
  {
    $this->CS             = $CS;
    $this->add            = false;
    $this->QueryArr       = array();
    $this->zweiterVersuch = false;
    $this->unbekannt      = null;

    if ( APPDEBUG["FKT"] ) $this->CS->debug("FKT",__METHOD__);
  }

  // Vom Index abgelehnte Felder merken und künftig weglassen

  private function unbekannteFelder()
  {
    if ( $this->unbekannt !== null )  return $this->unbekannt;

    $this->unbekannt = array();
    $Datei = $this->CS->prepareCachePath(array("settings"), "indexfelder");
    if ( file_exists($Datei) )
    {
      $Gelesen = json_decode((string) file_get_contents($Datei), true);
      if ( is_array($Gelesen) )  $this->unbekannt = array_values(array_filter($Gelesen, "is_string"));
    }
    return $this->unbekannt;
  }

  private function istEigenesFeld($Feld)
  {
    if ( isset(self::QUERYGEWICHT[$Feld]) )  return true;
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

    $Datei = $this->CS->prepareCachePath(array("settings"), "indexfelder");
    $Alt   = umask(0);
    @file_put_contents($Datei, json_encode($Felder), LOCK_EX);
    @chmod($Datei, 0666);
    umask($Alt);

    $this->CS->debug("ERR", array("type"   => "index",
                                  "client" => (string) $this->CS->client,
                                  "error"  => "Kern kennt dieses Feld nicht, es wird ab jetzt weggelassen",
                                  "field"  => $Feld));
  }

  private function gewichtungsFelder()
  {
    $Felder = array();
    $Unbekannt = $this->unbekannteFelder();
    foreach ( self::QUERYGEWICHT as $Feld => $Gewicht )
      if ( !in_array($Feld, $Unbekannt, true) )  $Felder[] = $Feld . "^" . $Gewicht;
    return $Felder;
  }

  private function istBekannterBefehl($Befehl)
  {
    $B = strtolower(trim((string) $Befehl));
    return $B !== "" && isset(self::FIELDS[$B]);
  }

  // Doppelpunkte und unausgeglichene Klammern maskieren
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

  private function addFilter($Filter)
  {
    if ( trim($Filter) !== "" )  $this->QueryArr["fq"][] = $Filter;
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

  // An Kommas trennen, außer in Klammern und Zitaten
  private function splitPhraseValues($Phrase)
  {
    $Parts = array(); $Buffer = ""; $Depth = 0; $QuoteChar = "";
    $Len   = strlen($Phrase);

    for ( $i = 0; $i < $Len; $i++ )
    {
      $Char = $Phrase[$i];
      if ( $QuoteChar !== "" )
      {
        $Buffer .= $Char;
        if ( $Char == $QuoteChar )  $QuoteChar = "";
        continue;
      }
      if     ( $Char == "\"" || $Char == "'" )  $QuoteChar = $Char;
      elseif ( $Char == "(" )                   $Depth++;
      elseif ( $Char == ")" )                   { if ( $Depth > 0 ) $Depth--; }
      elseif ( $Char == "," && $Depth == 0 )    { $Parts[] = $Buffer; $Buffer = ""; continue; }
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

    $NewVals = array();
    foreach ( $PhVals as $Val )
    {
      if ( in_array($Com, self::ILNCOLON) )
      {
        foreach ( $ILNs as $ILN )
          $NewVals[] = ( substr($Val,0,1) == "\"" ) ? "\"" . $ILN . ":" . substr($Val,1) : $this->CleanVal($ILN . ":" . $Val, true);
      }
      elseif ( $Com == "publisher" || $Com == "verlag" )  $NewVals[] = $this->CleanVal($Val, true);
      elseif ( $Com == "norm" )
      {
        $NewVals[] = "\(DE\-627\)" . $Val;
        $NewVals[] = "\(DE\-588\)" . $Val;
      }
      else  $NewVals[] = $this->CleanVal($Val, false);
    }

    $Phrase = ":(" . implode(" OR ", $NewVals) . ")";
    $Fields = self::FIELDS[$Com] ?? array($IntCom);
    $FQuery = implode($Phrase . " OR ", $Fields) . $Phrase;
    if ( $Com == "ppnlink" )  $FQuery .= " OR id" . $Phrase;

    return $FQuery;
  }

  private function buildQueryString()
  {
    $Parts = array();
    foreach ( $this->QueryArr as $Key => $Values )
      foreach ( (array) $Values as $Value )
        $Parts[] = rawurlencode($Key) . "=" . rawurlencode((string) $Value);
    return "&" . implode("&", $Parts);
  }

  // Abfragen der Installation je 60 s begrenzen; kurz warten, sonst ablehnen

  private function drossel()
  {
    $Limit = (int) ($this->CS->f3->get("api.indexlimit") ?: self::LIMIT_JE_MINUTE);
    if ( $Limit <= 0 )  return true;

    $Datei = CACHESDIR . "index-drossel.json";
    $Ende  = microtime(true) + self::LIMIT_WARTEN_HOECHSTENS;
    do
    {
      $fh = @fopen($Datei, "c+");
      if ( $fh === false )  return true;
      @chmod($Datei, 0666);
      flock($fh, LOCK_EX);
      $Jetzt  = microtime(true);
      $Zeiten = json_decode((string) stream_get_contents($fh), true);
      $Zeiten = array_values(array_filter(is_array($Zeiten) ? $Zeiten : array(), fn($t) => $t > $Jetzt - 60));
      $Frei   = count($Zeiten) < $Limit;
      if ( $Frei )  $Zeiten[] = $Jetzt;
      rewind($fh); ftruncate($fh, 0); fwrite($fh, json_encode($Zeiten)); fflush($fh);
      flock($fh, LOCK_UN); fclose($fh);
      if ( $Frei )  return true;

      $Warten = min($Zeiten) + 60 - $Jetzt;
      if ( $Jetzt + $Warten > $Ende )  return false;
      usleep((int) (max(0.05, $Warten) * 1000000));
    }
    while ( true );
  }

  private function searchSolr($URL, $Query = "")
  {
    if ( APPDEBUG["FKT"] ) $this->CS->debug("FKT",__METHOD__);

    if ( !$this->drossel() )
    {
      $this->CS->debug("ERR", array("type" => "index", "error" => "Drossel: Kontingent je Minute erschöpft"));
      return array("drossel" => true);
    }

    static $ch = null;
    if ( $ch === null )  $ch = curl_init();

    curl_setopt_array($ch, array(
      CURLOPT_URL            => $URL . "&",
      CURLOPT_POST           => true,
      CURLOPT_POSTFIELDS     => $Query,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_CONNECTTIMEOUT => 10,
      CURLOPT_TIMEOUT        => 30,
      CURLOPT_ENCODING       => "",
      CURLOPT_USERAGENT      => "Lukida Essential",
    ));
    $Response = curl_exec($ch);

    if ( $CurlErrNo = curl_errno($ch) )
    {
      $this->CS->debug("ERR", array("type"      => "Solr Query",
                                    "errorCurl" => $CurlErrNo,
                                    "errorHTTP" => curl_getinfo($ch, CURLINFO_HTTP_CODE),
                                    "url"       => $URL,
                                    "query"     => $Query));
      return array();
    }

    $Result = json_decode($Response, true);
    if ( !is_array($Result) )
    {
      $Code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
      $this->CS->debug("ERR", array("type"      => "Solr Response",
                                    "errorHTTP" => $Code,
                                    "errorJSON" => ( $Code === 429 || $Code === 403 ) ? "Index-Sperre: zu viele Abfragen" : json_last_error_msg(),
                                    "url"       => $URL,
                                    "query"     => $Query,
                                    "response"  => substr((string) $Response, 0, 500)));
      return array();
    }
    return $Result;
  }

  private function cacheDatei($Url, $Query)
  {
    $Sekunden = $this->CS->f3->get("api.indexcache");
    $Sekunden = ( $Sekunden === null || $Sekunden === "" ) ? self::CACHE_SEKUNDEN : (int) $Sekunden;
    if ( $Sekunden <= 0 )  return array("", 0);
    return array($this->CS->prepareCachePath(array("index"), hash("sha256", $Url . "|" . $Query)), $Sekunden);
  }

  private function ausCache($Datei, $Sekunden)
  {
    if ( $Datei === "" || !is_file($Datei) )                return null;
    if ( (time() - (int) @filemtime($Datei)) > $Sekunden )  return null;
    $Gelesen = json_decode((string) @file_get_contents($Datei), true);
    return is_array($Gelesen) && isset($Gelesen["response"]) ? $Gelesen : null;
  }

  private function inCache($Datei, $Ergebnis)
  {
    if ( $Datei === "" || !is_array($Ergebnis) || !isset($Ergebnis["response"]) )  return;
    $Neu = !file_exists($Datei);
    if ( @file_put_contents($Datei, json_encode($Ergebnis), LOCK_EX) !== false && $Neu )  @chmod($Datei, 0666);
  }

  private function processSolr($Url, $Query)
  {
    if ( APPDEBUG["FKT"] ) $this->CS->debug("FKT",__METHOD__);

    [$Datei, $Sekunden] = $this->cacheDatei($Url, $Query);
    $Result = $this->ausCache($Datei, $Sekunden);
    if ( !is_array($Result) )
    {
      $Result = $this->searchSolr($Url, $Query);
      $this->inCache($Datei, $Result);
    }

    // Elternsätze abgewiesen: Treffer trotzdem liefern
    if ( isset($Result["drossel"]) && $this->add )  return array();
    if ( isset($Result["drossel"]) )
    {
      $this->CS->results["status"] = -1;
      $this->CS->results["code"]   = "indexbusy";
      return array();
    }

    $Container = isset($Result["response"]["docs"]) ? $Result["response"]["docs"] : array();
    if ( $this->add )  return $Container;

    if ( isset($Result["error"]) )
    {
      $Fehlercode = isset($Result["error"]["code"]) ? (int) $Result["error"]["code"] : 0;
      $Meldung    = isset($Result["error"]["msg"]) ? (string) $Result["error"]["msg"] : "";

      // Unbekanntes Feld entfernen und einmal neu versuchen
      $Treffer = array();
      if ( !preg_match("/Query Field '([^']+)' is not a valid field name/", $Meldung, $Treffer) )
             preg_match("/undefined field ([A-Za-z0-9_]+)/i", $Meldung, $Treffer);
      if ( !$this->zweiterVersuch && !empty($Treffer[1]) )
      {
        $this->zweiterVersuch = true;
        $Feld = $Treffer[1];
        if ( $this->istEigenesFeld($Feld) )  $this->merkeUnbekanntesFeld($Feld);

        $Geaendert = false;
        foreach ( $this->QueryArr as $Schluessel => $Werte )
        {
          foreach ( (array) $Werte as $i => $Wert )
          {
            if ( $Schluessel === "qf" )
            {
              $Rest = implode(" ", array_filter(explode(" ", (string) $Wert),
                        fn($F) => $F !== "" && strtok($F, "^") !== $Feld));
              if ( $Rest !== "" && $Rest !== (string) $Wert )  { $this->QueryArr["qf"][$i] = $Rest; $Geaendert = true; }
              continue;
            }
            if ( strpos((string) $Wert, $Feld) === false )  continue;
            $Neu = preg_replace('/(?:\s+OR\s+)?\b' . preg_quote($Feld, "/") . ':\([^()]*\)/', "", (string) $Wert);
            $Neu = trim(preg_replace('/^\s*OR\s+|\s+OR\s*$/', "", (string) $Neu));
            if ( $Neu !== "" && $Neu !== (string) $Wert )  { $this->QueryArr[$Schluessel][$i] = $Neu; $Geaendert = true; }
          }
        }
        if ( $Geaendert )  return $this->processSolr($Url, $this->buildQueryString());
      }

      if ( stripos($Meldung, "maxClauseCount") !== false || stripos($Meldung, "too many nested clauses") !== false )
      {
        $this->CS->results["code"] = "toomanywords";
        return array();
      }

      $this->CS->debug("ERR", array("type"    => "index",
                                    "client"  => (string) $this->CS->client,
                                    "http"    => $Fehlercode,
                                    "message" => $Meldung,
                                    "query"   => substr((string) $Query, 0, 500)));
      $this->CS->results["status"]  = $Fehlercode > 0 ? -$Fehlercode : -1;
      $this->CS->results["message"] = $Meldung;
      return array();
    }

    if ( !isset($Result["response"]) )
    {
      $this->CS->results["status"]   = -1;
      $this->CS->results["code"]     = "indexerror";
      $this->CS->results["hits"]     = 0;
      $this->CS->results["hitspack"] = 0;
      $this->CS->results["start"]    = 0;
      return array();
    }

    $this->CS->results["status"]   = 0;
    $this->CS->results["qtime"]    = isset($Result["responseHeader"]["QTime"]) ? str_replace(".", ",", round($Result["responseHeader"]["QTime"] / 1000, 2)) : "";
    $this->CS->results["hits"]     = $Result["response"]["numFound"] ?? 0;
    $this->CS->results["hitspack"] = count($Container);
    $this->CS->results["start"]    = $Result["response"]["start"] ?? 0;

    if ( $this->SkipID )
    {
      $Idx = array_search($this->SkipID, array_column($Container, "id"), true);
      if ( $Idx !== false )
      {
        array_splice($Container, $Idx, 1);
        $this->CS->results["hitspack"]--;
        $this->CS->results["hits"]--;
      }
    }
    return $Container;
  }

  private function doSearch()
  {
    if ( APPDEBUG["FKT"] ) $this->CS->debug("FKT",__METHOD__);

    $Search = $this->CS->search;
    $SQuery = ( isset($Search["search"]["query"]) && trim($Search["search"]["query"]) ) ? trim($Search["search"]["query"]) : "*";
    $SUrl   = ( isset($Search["url"]) && trim($Search["url"]) ) ? trim($Search["url"]) : "";
    $this->SkipID = ( isset($Search["search"]["skipid"]) && trim($Search["search"]["skipid"]) ) ? trim($Search["search"]["skipid"]) : "";

    // Befehle werden Filter, der Rest ist freie Suche
    $SQuery .= " ";
    $BracketsRegex = "/([a-zA-Z]+)(?:\((?P<paren>(?:[^()\"']*|\"[^\"]*\"|'[^']*'|\([^()]*\))*)\)|:(?P<colon>\"[^\"]*\"|'[^']*'|[a-zA-Z0-9_\-*]+))/";
    $Filters = array();
    $SQuery  = preg_replace_callback($BracketsRegex, function ($m) use (&$Filters) {
      $Wert = ( ($m['paren'] ?? "") !== "" ) ? $m['paren'] : ($m['colon'] ?? "");
      if ( !$this->istBekannterBefehl($m[1]) )  return $m[0];
      if ( trim($Wert) !== "" )  $Filters[] = array($m[1], $Wert);
      return "";
    }, $SQuery) ?? $SQuery;
    $SQuery = trim($this->entschaerfeResttext($SQuery));

    foreach ( $Filters as [$Command, $Filter] )  $this->addFilter($this->getFieldQuery($Command, $Filter));
    if ( isset($this->QueryArr["fq"]) )  $this->QueryArr["fq"] = array_values(array_unique($this->QueryArr["fq"]));

    $this->QueryArr["fl"][] = self::FL;
    if ( $SQuery )  $this->QueryArr["qf"][] = implode(" ", $this->gewichtungsFelder());
    $this->QueryArr["q"][] = $SQuery ? $SQuery : "*";

    if ( isset($Search["start"]) )  $this->QueryArr["start"][] = max(0, (int) $Search["start"]);
    if ( isset($Search["limit"]) )  $this->QueryArr["rows"][]  = min(50, max(0, (int) $Search["limit"]));

    $this->QueryArr["sort"][] = "score desc,publishDateSort desc,id asc";
    $this->QueryArr["shards.preference"][] = "replica.base:stable:hash:q";
    $this->letzteWortsuche = $SQuery;

    $Q = $this->buildQueryString();
    if ( APPDEBUG["SLQ"] ) $this->CS->debug("SLQ", $SUrl . $Q);

    $Data = $this->processSolr($SUrl, $Q);
    if ( APPDEBUG["SLD"] ) $this->CS->debug("SLR", $Data);
    return $Data;
  }

  public function getSearch($Add)
  {
    if ( APPDEBUG["FKT"] ) $this->CS->debug("FKT",__METHOD__);

    $this->add = $Add;
    $this->CS->works["solr"] = $this->doSearch();
  }
}

?>
