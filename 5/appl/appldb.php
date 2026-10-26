<?php

class appldb extends system
{
  
  
  
  
  
  
  
  
  
  
  private static $ApiKeyCols = null;

  
  
  
  public function __construct($f3)
  {
    parent::__construct($f3);
    if ( APPDEBUG["FKT"] ) $this->debug("FKT",__METHOD__);
  }

  
  
  

  
  
  

  
  
  
  
  
  

  
  
  
  
  
  protected function pruefeApiKey()
  {
    if ( trim((string) $this->apikey) === "" )  return;

    try
    {
      
      
      
      
      $Felder = "client, kennung, active, validuntil";
      if ( in_array("ipranges", $this->apiKeyColumnsDB()) )  $Felder .= ", ipranges, scopes";

      $SQL  = "select " . $Felder . " from " . $this->schemaOf(MODULE)
            . ".apikeys where client = :client and kennung = :kennung";
      $Rows = $this->execDB($SQL, array("client" => $this->client, "kennung" => $this->apikey));
    }
    catch ( \Throwable $e ) { $Rows = array(); }

    if ( !is_array($Rows) || count($Rows) != 1 )
    {
      if ( APPDEBUG["LOG"] ) $this->debug("LOG", "API-Key unbekannt (" . $this->client . "/" . $this->apikey . ")");
      $this->finalize(array("status" => -1, "code" => "apikeyunknown"));
    }

    $Row = $Rows[0];
    if ( strtolower(trim((string) $Row["active"])) !== "y" )
      $this->finalize(array("status" => -1, "code" => "apikeyblocked"));

    if ( trim((string) $Row["validuntil"]) !== "" && strtotime((string) $Row["validuntil"]) < time() )
      $this->finalize(array("status" => -1, "code" => "apikeyexpired"));

    
    
    
    
    
    if ( isset($Row["ipranges"]) && trim((string) $Row["ipranges"]) !== ""
      && !$this->ipImBereich($this->clientip, $Row["ipranges"]) )
    {
      if ( APPDEBUG["LOG"] ) $this->debug("LOG", "API-Key von fremder Adresse (" . $this->apikey . "/" . $this->clientip . ")");
      $this->finalize(array("status" => -1, "code" => "apikeyiprange"));
    }

    
    
    
    if ( isset($Row["scopes"]) && trim((string) $Row["scopes"]) !== "" )
    {
      $Erlaubt = array_filter(array_map(function ($E) { return strtolower(trim($E)); },
                                        explode(",", (string) $Row["scopes"])));
      if ( !in_array(strtolower((string) ACTION), $Erlaubt, true) )
      {
        if ( APPDEBUG["LOG"] ) $this->debug("LOG", "API-Key ohne Recht auf " . ACTION . " (" . $this->apikey . ")");
        $this->finalize(array("status" => -1, "code" => "apikeyscope"));
      }
    }
  }

  
  protected function apiKeyColumnsDB()
  {
    if ( self::$ApiKeyCols !== null )  return self::$ApiKeyCols;

    $SQL  = "SELECT column_name AS colname FROM information_schema.columns"
          . " where table_schema = :schema and table_name = 'apikeys'"
          . " and column_name in ('ipranges', 'scopes')";
    $Rows = $this->execDB($SQL, array("schema" => $this->schemaName(MODULE)));

    $Spalten = array();
    foreach ( (array) $Rows as $Row )  $Spalten[] = $Row["colname"];

    return (self::$ApiKeyCols = $Spalten);
  }

  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  public function prepareDaiaCachePath($Endpunkt, $Kennung = "", $Verzeichnis = false)
  {
    $Path = CACHESDIR . date("Ymd") . DIRECTORY_SEPARATOR . "_daia"
          . DIRECTORY_SEPARATOR . substr(md5((string) $Endpunkt), 0, 8);

    if ( $Verzeichnis )
    {
      $this->createPath($Path);
      return $Path;
    }

    $Name  = md5((string) $Kennung);
    $Path .= DIRECTORY_SEPARATOR . substr($Name, 0, 2);
    $this->createPath($Path);

    return $Path . DIRECTORY_SEPARATOR . $Name . ".inc";
  }

  
  
  
  
  
  
  
  
  
  
  
  protected function statsExtraNamesDB()
  {
    return array("bms", "level");
  }

  protected function statsExtraValuesDB(array $Spalten)
  {
    if ( MODULE != "user" )  return array();

    $Aus = array();

    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    if (in_array("bms", $Spalten)) {
      $Login = isset($this->posted["login"]) ? trim((string) $this->posted["login"]) : "";
      $Aus["bms"] = ($Login !== "" && $this->bmsid)
                          ? substr((string) $this->bmsid, 0, 50)
                          : "-";
    }

    
    
    
    
    
    
    
    
    
    
    
    
    if (in_array("level", $Spalten)) {
      $Raum = "";
      if (isset($this->posted["search"]["userlevel"]) && trim((string) $this->posted["search"]["userlevel"]) != "")
        $Raum = trim((string) $this->posted["search"]["userlevel"]);
      elseif (isset($this->posted["level"]) && trim((string) $this->posted["level"]) != "")
        $Raum = trim((string) $this->posted["level"]);
      $Aus["level"] = ($Raum !== "") ? substr($Raum, 0, 50) : "-";
    }

    return $Aus;
  }

  protected function getLevelsDB()
  {
    if ( APPDEBUG["FKT"] ) $this->debug("FKT",__METHOD__);

    $Data = array('client' => $this->client);
    
    
    
    
    
    
    
    
    $SQL  = "select level from adminlevels where client = :client and sort > 0 order by sort";

    $Rows = $this->execDB($SQL, $Data);
    $Levl = array();
    foreach ($Rows as $Row)
    {
      $Levl[] = $Row["level"];
    }

    
    return $Levl;
  }


  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  protected function getApplTextsDB()
  {
    if ( APPDEBUG["FKT"] ) $this->debug("FKT",__METHOD__);

    $SQL = "select shortcut, german, english from " . $this->schemaOf(MODULE)
         . (MODULE == "admin" ? ".bibtexts" : ".adminbibtexts");

    try {
      $Rows = $this->execDB($SQL);
    } catch (\Throwable $Fehler) {
      return [];
    }

    return $this->textZeilen($Rows);
  }

  
  
  
  
  
  
  
  protected function getApplBatchTextsDB($Selection = [], $NotSelection = [], $Complete = false)
  {
    if ( APPDEBUG["FKT"] ) $this->debug("FKT",__METHOD__);

    $SQL = "SELECT shortcut, german, english FROM " . $this->schemaOf("hosting") . ".adminbibtexts";
    $Wo  = array();
    if (!$Complete && count($Selection))    $Wo[] = "( shortcut     like '" . implode("' OR shortcut like '", $Selection) . "')";
    if (!$Complete && count($NotSelection)) $Wo[] = "( shortcut not like '" . implode("' AND shortcut not like '", $NotSelection) . "')";
    if (count($Wo))  $SQL .= " WHERE " . implode(" AND ", $Wo);

    try {
      return $this->textZeilen($this->ExecDB($SQL));
    } catch (\Throwable $Fehler) {
      return [];
    }
  }

  
  
  private function textZeilen($Rows)
  {
    $Texts = [];
    foreach ((array) $Rows as $Row) {
      if (!isset($Row["shortcut"]))  continue;
      $Texts[$Row["shortcut"]] = array(
        "german"  => $Row["german"]  !== null && trim($Row["german"])  !== '' ? trim($Row["german"])  : "",
        "english" => $Row["english"] !== null && trim($Row["english"]) !== '' ? trim($Row["english"]) : ""
      );
    }
    return $Texts;
  }


  
  
  

  
  
  
}

?>
