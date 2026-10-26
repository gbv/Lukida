<?php

class system extends systemdb
{
  
  
  
  protected $dbpw;
  public    $batchconfigs;
  public    $batchtextexcludes;
  public    $batchtexts;
  public    $clientconfigs;
  public    $clienttexts;
  public    $configs;
  public    $texts;

  public function __construct($f3)
  {
    parent::__construct($f3);
    if ( APPDEBUG["FKT"] ) $this->debug("FKT",__METHOD__);

    if ( ACTION != "batch" )
    {
      
      $this->updateClient();
    }
  }

  
  
  
  private function updateClient()
  {
    if ( APPDEBUG["FKT"] ) $this->debug("FKT",__METHOD__);

    
    $this->updSessionDB($this->checkPostedStr("tokens", "session"));

    if ( $this->checkPostedStr("tokens", "device") )  $this->updDeviceDB();
    if ( $this->checkPostedArr("platform") )          $this->updPlatformDB();

    if ( !$this->user ) return array();

    
    
    return array();
  }

  
  
  

  protected function getConfigs($ClientConfigs)
  {
    if ( APPDEBUG["FKT"] ) $this->debug("FKT",__METHOD__);

    
    $File = $this->prepareCachePath(array("settings"), "configs");
    if ( file_exists($File) )
    {
       if ( MODE != "production" ) $this->updStatDB("Cache", "Configs.yes");
       $this->configs = json_decode(file_get_contents($File),true);
    }
    else
    {
      
      if ( MODE != "production" ) $this->updStatDB("Cache", "Configs.no");
      $this->configs  = $this->getAdminConfigsDB();

      
      
      
      
      
      
      
      
      
      $HostCl = $this->getHostingClientDB();
      if ( is_array($HostCl) && count($HostCl) )
      {
        $Vorher = ( isset($this->configs["hosting"]) && is_array($this->configs["hosting"]) )
                    ? $this->configs["hosting"] : array();
        $this->configs["hosting"] = array_merge($Vorher, $HostCl);
      }

      
      
      
      
      
      
      
      
      
      
      
      $Alt = array(
        "solrpath"        => array("solr",    "path"),
        "kisuggest"       => array("project", "kisuggest"),
        "messageprovider" => array("other",   "app.messageprovider"),
        "messagechannel"  => array("other",   "app.messagechannel"),
        "messagekey"      => array("other",   "app.messagekey"),
        "mybibuser"       => array("other",   "delivery.mybib.user"),
        "mybibpass"       => array("other",   "delivery.mybib.pass"),
        "mybibprodhost"   => array("other",   "delivery.mybib.prodhost"),
        "mybibtesthost"   => array("other",   "delivery.mybib.testhost"),
      );
      foreach ( $Alt as $Neu => $Wo )
      {
        if ( isset($this->configs["hosting"][$Neu]) && trim((string) $this->configs["hosting"][$Neu]) !== "" ) continue;
        if ( isset($this->configs[$Wo[0]][$Wo[1]]) && trim((string) $this->configs[$Wo[0]][$Wo[1]]) !== "" )
        {
          $this->configs["hosting"][$Neu] = $this->configs[$Wo[0]][$Wo[1]];
        }
      }

      file_put_contents($File, json_encode($this->configs,true));
      
    }

    
    $this->pruefeClientScope();

    
    $File = $this->prepareCachePath(array("settings"), "Clientconfigs");
    if ( file_exists($File) )
    {
       if ( MODE != "production" ) $this->updStatDB("Cache", "ClientConfigs.yes");
       $this->clientconfigs = json_decode(file_get_contents($File),true);
    }
    else
    {
      
      if ( MODE != "production" ) $this->updStatDB("Cache", "ClientConfigs.no");
      $this->clientconfigs  = $this->getAdminConfigsDB($ClientConfigs, true);
      $this->clientconfigs += $this->getApplConfigs();
      file_put_contents($File, json_encode($this->clientconfigs,true));
      
    }
  }

  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  protected function pruefeClientScope()
  {
    if ( !defined("MODULE") || MODULE != "user" )  return;
    if ( trim((string) ($this->apikey ?? "")) !== "" )  return;

    $Liste = ( isset($this->configs["hosting"]["scopes"]) )
               ? trim((string) $this->configs["hosting"]["scopes"]) : "";
    if ( $Liste === "" )  return;

    $Erlaubt = array("config");
    foreach ( explode(",", $Liste) as $Eins )
    {
      $Eins = strtolower(trim($Eins));
      if ( $Eins !== "" )  $Erlaubt[] = $Eins;
    }

    if ( in_array(strtolower(defined("ACTION") ? ACTION : ""), $Erlaubt, true) )  return;

    
    
    
    
    
    
    
    
    
    
    
    
    
    $Modus = strtolower(trim((string) $this->f3->get("api.scopesmodus")));

    
    
    
    $Melden = $this->nurEinmalProStunde("scope|" . $this->client . "|" . (defined("ACTION") ? ACTION : ""));

    if ( $Melden )
    {
      try
      {
        $Dir = PROJECTDIR . "logs" . DIRECTORY_SEPARATOR . "herkunft" . DIRECTORY_SEPARATOR;
        if ( !is_dir($Dir) )  @mkdir($Dir, 0777, true);
        $Datei = $Dir . "scope_" . date("Ymd") . ".log";
        $Neu   = !file_exists($Datei);
        @file_put_contents($Datei,
          implode("\t", array(date("Y-m-d H:i:s"), (string) $this->client,
                              defined("ACTION") ? ACTION : "", $Liste,
                              ($Modus === "sperren") ? "gesperrt" : "nur gemeldet")) . PHP_EOL,
          FILE_APPEND | LOCK_EX);
        if ( $Neu )  @chmod($Datei, 0666);
      }
      catch ( \Throwable $E ) {  }
    }

    if ( $Modus !== "sperren" )  return;

    if ( APPDEBUG["LOG"] ) $this->debug("LOG", "Einstieg fuer das Haus nicht freigegeben: " . ACTION);
    
    
    if ( $Melden )  $this->logDB("Einstieg nicht freigegeben: " . ACTION, "api", "scope", "ERROR");
    $this->finalize(array("status" => -1, "code" => "clientscope"));
  }

  protected function markdownTexts($Prefixes)
  {
    if ( APPDEBUG["FKT"] ) $this->debug("FKT",__METHOD__);

    if ( !is_array($this->clienttexts) )  return;

    foreach ( $this->clienttexts as $Key => $Text )
    {
      $Match = false;
      foreach ( $Prefixes as $Prefix )  if ( strpos($Key, $Prefix) === 0 )  $Match = true;
      if ( !$Match || !is_array($Text) )  continue;

      foreach ( array("german", "english") as $Lang )
      {
        if ( !isset($Text[$Lang]) )  continue;
        $this->clienttexts[$Key][$Lang] = str_replace(array("§", "{newline}"),
                                                      array("#", "\n"),
                                                      $Text[$Lang]);
      }
    }
  }

  protected function getTexts($ClientTexts)
  {
    if ( APPDEBUG["FKT"] ) $this->debug("FKT",__METHOD__);

    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    $ApplTexts = array();
    if ( method_exists($this, "getApplTextsDB") )
    {
      $File = $this->prepareSharedCachePath("settings", "appltexts");
      if ( file_exists($File) )
      {
        if ( MODE != "production" ) $this->updStatDB("Cache", "ApplTexts.yes");
        $ApplTexts = (array) json_decode(file_get_contents($File), true);
      }
      else
      {
        if ( MODE != "production" ) $this->updStatDB("Cache", "ApplTexts.no");
        $ApplTexts = (array) $this->getApplTextsDB();
        
        
        if ( count($ApplTexts) )  file_put_contents($File, json_encode($ApplTexts, true));
      }
    }

    
    $File = $this->prepareCachePath(array("settings"), "texts");
    if ( file_exists($File) )
    {
       if ( MODE != "production" ) $this->updStatDB("Cache", "Texts.yes");
       $this->texts = json_decode(file_get_contents($File), true);
    }
    else
    {
      
      if ( MODE != "production" ) $this->updStatDB("Cache", "Texts.no");
      $this->texts  = $this->getAdminTextsDB();
      file_put_contents($File, json_encode($this->texts, true));
      
    }

    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    if ( count($ApplTexts) )
      $this->texts = array_merge(is_array($this->texts) ? $this->texts : array(), $ApplTexts);

    
    $File = $this->prepareCachePath(array("settings"), "Clienttexts");
    if ( file_exists($File) )
    {
       if ( MODE != "production" ) $this->updStatDB("Cache", "ClientTexts.yes");
       $this->clienttexts = json_decode(file_get_contents($File),true);
    }
    else
    {
      
      if ( MODE != "production" ) $this->updStatDB("Cache", "ClientTexts.no");
      $this->clienttexts  = $this->getAdminTextsDB($ClientTexts, true);
      file_put_contents($File, json_encode($this->clienttexts,true));
      
    }
  }

  protected static function prepareSearch($String)
  {
    if ( APPDEBUG["FKT"] ) self::debug("FKT",__METHOD__);

    $String = preg_replace('!\s+!', '*', trim($String));
    $String = $String . "*";
    return $String;
  }

  
  
  
  
  
  
  
  
  
  
  
  
  
  
  public function conf($Category, $Setting, $Default = "")
  {
    if ( !isset($this->configs[$Category][$Setting]) )  return $Default;
    $Wert = trim((string) $this->configs[$Category][$Setting]);
    return ( $Wert === "" ) ? $Default : $Wert;
  }

  public function confIsYes($Category, $Setting)
  {
    return $this->conf($Category, $Setting, "n") == "y";
  }

  
  
  
  
  public function confPairs($Category, $Setting)
  {
    $Aus = array();
    foreach ( explode(",", $this->conf($Category, $Setting)) as $Paar )
    {
      $Paar = trim($Paar);
      if ( $Paar === "" || strpos($Paar, ":") === false )  continue;
      list($K, $V) = explode(":", $Paar, 2);
      if ( trim($K) !== "" )  $Aus[trim($K)] = trim($V);
    }
    return $Aus;
  }

  
  
  
  
  
  
  
  
  
  
  
  public function action()
  {
    if ( APPDEBUG["FKT"] ) $this->debug("FKT",__METHOD__);

    
    $Type   = $this->checkPostedStr("type");
    $Action = $this->checkPostedArr($Type);
    if ( !$Type || !count($Action) ) $this->finalize(array("status" => -1, "code" => "missingvalue"));

    $Type   = strtolower($Type);
    
    if ( !in_array($Type,array("mail")) ) $this->finalize(array("status" => -1, "code" => "missingvalue"));

    

    
    $this->updStatDB("Action", $Type);

    
    switch ($Type)
    {
      case "mail":
      {
        $Mail    = $this->checkPostedArr("mail");
        $Results = $this->sendMail($Mail);
        break;
      }
      default:
      {
        $Results = array();
      }


      
























    }

    
    $this->finalize($Results, __FUNCTION__);
  }

  
  
  
  
  
  
  
  
  
  
  public function config()
  {
    if ( APPDEBUG["FKT"] ) $this->debug("FKT",__METHOD__);

    
    $this->updStatDB("Session", "-");

    
    
    
    
    
    
    
    
    
    
    $Source = trim($this->checkPostedStr("source"));
    if ( $Source )
    {
      $Source = preg_replace('/[^\p{L}\p{N} ._+-]/u', "", $Source);
      $Source = trim(mb_substr($Source, 0, 50));
      if ( $Source )  $this->updStatDB("Source", $Source);
    }

    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    $Texts = array();
    
    
    
    $Excludes = ( is_array($this->batchtextexcludes) && count($this->batchtextexcludes) )
                ? $this->batchtextexcludes
                : array("classlocal.%", "event.%", "family.%", "lang.%", "organisation.%", "person.%", "place.%", "role.%");
    foreach ( (is_array($this->texts) ? $this->texts : array()) as $Key => $Val )
    {
      $Skip = false;
      foreach ( $Excludes as $Ex )
      {
        $Prefix = rtrim($Ex, "%");
        if ( $Prefix !== "" && strpos($Key, $Prefix) === 0 ) { $Skip = true; break; }
      }
      if ( !$Skip )  $Texts[$Key] = $Val;
    }
    if ( is_array($this->clienttexts) )  $Texts = array_merge($Texts, $this->clienttexts);

    $Configs = is_array($this->clientconfigs) ? $this->clientconfigs : array();

    
    
    
    
    if ( method_exists($this, "getClientOptions") )
    {
      $Options = $this->getClientOptions();
      if ( is_array($Options) && count($Options) )  $Configs["options"] = $Options;
    }

    
    
    
    
    
    if ( method_exists($this, "getMaintenance") )
    {
      $Configs["maintenance"] = $this->getMaintenance();
    }

    
    
    if ( method_exists($this, "getMessages") )
    {
      $Configs["messages"] = $this->getMessages();
    }

    
    
    
    
    
    
    
    
    
    
    
    
    $Configs["module"] = array(
      "fachlichetests" => ( (int) $this->f3->get("tests.fachlichetests") === 1 ),
    );

    $Results  = array("configs" => $Configs,
                      "texts"   => $Texts);

    
    $this->finalize($Results, __FUNCTION__);
  }

  
  
  
  
  public function dash()
  {
    if ( APPDEBUG["FKT"] ) $this->debug("FKT",__METHOD__);

    $UserClients = $this->checkPostedArr("userclients");

    

    
    $Results = $this->getDashboardDB($UserClients);

    
    $this->finalize($Results, __FUNCTION__);
  }

  
  
  
  
  
  public function getMultipleTexts($ShortcutArray)
  {
    $Lang = ($this->lang == "g") ? "german" : "english";

    $Texts = array();
    foreach( $ShortcutArray as $Shortcut )
    {
      $Texts[$Shortcut] = ( isset($this->texts[$Shortcut][$Lang]) ) ? $this->texts[$Shortcut][$Lang] : "";
    }
    return $Texts;
  }

  public function getText($Shortcut)
  {
    $Lang = ($this->lang == "g") ? "german" : "english";
    return ( isset($this->texts[$Shortcut][$Lang]) ) ? $this->texts[$Shortcut][$Lang] : "";
  }

  
  
  public function getTextBothLang($Shortcut)
  {
    return ( isset($this->texts[$Shortcut]) ) ? $this->texts[$Shortcut] : array("german" => "", "english" => "");
  }

  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  private function loginBremsePfad($User)
  {
    $Dir = PROJECTDIR . "logs" . DIRECTORY_SEPARATOR . "loginthrottle" . DIRECTORY_SEPARATOR;
    if ( !file_exists($Dir) )  @mkdir($Dir, 0777, true);
    return $Dir . hash("sha256", mb_strtolower(trim($User))) . ".json";
  }

  
  
  
  
  
  
  private function loginBremse($User)
  {
    $FENSTER = 900;   
    $FREI    = 4;     
    $SCHLUSS = 10;

    $Datei = $this->loginBremsePfad($User);
    if ( !is_file($Datei) )  return 0;

    $Inhalt = file_get_contents($Datei);
    $Stand  = ($Inhalt === false) ? null : json_decode((string) $Inhalt, true);
    if ( !is_array($Stand) || !isset($Stand["n"], $Stand["t"]) )  return 0;

    
    if ( (time() - (int) $Stand["t"]) > $FENSTER ) { unlink($Datei); return 0; }

    $N = (int) $Stand["n"];
    if ( $N >= $SCHLUSS )  return -1;
    if ( $N <= $FREI )     return 0;

    return min(pow(2, $N - $FREI), 8);
  }

  private function loginBremseZaehlen($User)
  {
    $FENSTER = 900;
    $Datei = $this->loginBremsePfad($User);

    $N = 0;
    if ( is_file($Datei) )
    {
      $Inhalt = file_get_contents($Datei);
      $Stand  = ($Inhalt === false) ? null : json_decode((string) $Inhalt, true);
      if ( is_array($Stand) && isset($Stand["n"], $Stand["t"])
        && (time() - (int) $Stand["t"]) <= $FENSTER )  $N = (int) $Stand["n"];
    }

    file_put_contents($Datei, json_encode(array("n" => $N + 1, "t" => time())), LOCK_EX);
    chmod($Datei, 0666);
  }

  
  
  
  
  
  
  
  private function loginBremseFrei($User)
  {
    $Datei = $this->loginBremsePfad($User);
    if ( is_file($Datei) )  unlink($Datei);
  }

  
  
  
  
  
  public function login()
  {
    if ( APPDEBUG["FKT"] ) $this->debug("FKT",__METHOD__);

    
    $User = $this->checkPostedStr("userid");
    $Pass = $this->checkPostedStr("userpw");
    $Tokn = $this->checkPostedStr("tokens", "user");
    if ( ( !$User || !$Pass ) && !$Tokn )  $this->finalize(array("status" => -1, "code" => "missingvalue"));

    
    
    
    
    
    
    
    
    
    if ( $User && $Pass )
    {
      $Wartezeit = $this->loginBremse($User);
      if ( $Wartezeit < 0 )
        $this->finalize(array("status" => -1, "code" => "toomanyattempts"));
      if ( $Wartezeit > 0 )
        sleep($Wartezeit);
    }

    
    $Results = array();
    if ( $Tokn )
    {
      $UserToken = $this->decodeToken($Tokn, "user");
      if ( is_array($UserToken) && count($UserToken) )
      {
        if ( isset($UserToken["cl"]) && $UserToken["cl"] == $this->client )
        {
          $Results["account"]  = $this->getUserDB($UserToken["un"]);
        }
      }
    }
    else
    {
      $Tmp = $this->checkGetUserDB($User, $Pass);
      if ( is_array($Tmp) && count($Tmp) )  $Results["account"] = $Tmp;
    }

    if ( isset($Results["account"]["username"]) && $Results["account"]["username"] )
    {
      $this->user        = $Results["account"]["username"];
      $this->avatar      = isset($Results["account"]["avatar"]) ? $Results["account"]["avatar"] : "";
      $Results["texts"]  = $this->getMultipleTexts(array("loginsuccessful", "loginerror", "logoutgranted"));
      if ( APPDEBUG["LOG"] ) $this->debug("LOG", "User Access OK (for user: " . $this->user . ")");
      $this->logDB("User Access OK");
      if ( $User )  $this->loginBremseFrei($User);

      
      $this->updStatDB("Action", "login");
    }
    else
    {
      if ( APPDEBUG["LOG"] ) $this->debug("LOG", "User Access ERROR (for user: " . $User . ")");
      $this->logDB("User Access ERROR");
      if ( $User && $Pass )  $this->loginBremseZaehlen($User);
    }

    
    $this->finalize($Results, __FUNCTION__);
  }

  
  
  
  
  
  
  
  public function read()
  {
    if ( APPDEBUG["FKT"] ) $this->debug("FKT",__METHOD__);

    

    
    $AdminClient = $this->checkPostedStr("adminclient");
    $Area        = $this->checkPostedStr("area");
    $Client      = $this->checkPostedStr("client");
    $Start       = $this->checkPostedInt("start");
    $Limit       = $this->checkPostedInt("limit");
    $Sorts       = $this->checkPostedArr("sorts");
    $Filters     = $this->checkPostedArr("filters");
    $Texts       = $this->checkPostedArr("texts");
    $Group       = $this->checkPostedStr("group");
    $Env         = $this->checkPostedStr("env");
    $Special     = $this->checkPostedStr("special");
    
    
    
    
    $Search      = $this->checkPostedStr("search");

    
    
    
    
    
    
    
    
    $SpecialFelder = array("day"        => "day",
                           "month"      => "month",
                           "year"       => "year",
                           "monthhours" => "month",
                           "yearhours"  => "year");
    if ( isset($SpecialFelder[$Special]) )
    {
      $SpecialValue = $this->checkPostedStr($SpecialFelder[$Special]);
      
      
      
      
      
      
      
      
      
      
      
      
      $Special .= ":" . $SpecialValue;
    }

    
    
    
    
    
    if ( MODULE == "user" && !$Client )  $Client = $this->client;

    
    if ( !$Area || ( !$Client && MODULE != "hosting" ) )  $this->finalize(array("status" => -1, "code" => "missingvalue"));
    if ( !$AdminClient && MODULE == "admin" )             $this->finalize(array("status" => -1, "code" => "missingvalue"));

    if ( MODULE == "admin" ) $Client = $AdminClient;

    if ( !$Start )  $Start = 0;
    if ( !$Limit )  $Limit = 25;



    
    $this->updStatDB("Read", $Area);

    
    $Results = array();
    $Results["data"]  = $this->readDB($Area, $Client, $Start, $Limit, $Filters, $Sorts, $Group, $Env, $Special, $Search);
    if ( is_array($Texts) && count($Texts) ) $Results["texts"] = $this->getAdminTextsDB($Texts, true, $AdminClient);

    
    if ( !count($Results["data"]) ) $this->finalize(array("status" => -1, "code" => "nodata"));

    
    $this->finalize($Results, __FUNCTION__);
  }

  
  
  
  
  public function readoptions()
  {
    if ( APPDEBUG["FKT"] ) $this->debug("FKT",__METHOD__);

    

    
    $Client  = $this->checkPostedStr("client");
    $Area    = $this->checkPostedStr("area");
    $Values  = $this->checkPostedArr("values");
    $Labels  = $this->checkPostedArr("labels");
    $Filters = $this->checkPostedArr("filters");
    $Sorts   = $this->checkPostedArr("sorts");

    
    if ( !$Area || ( !$Client && MODULE != "hosting" ) )  $this->finalize(array("status" => -1, "code" => "missingvalue"));

    
    $this->updStatDB("ReadOption", $Area);

    
    $Rows    = $this->readDB($Area, $Client, 0, 1000, $Filters, $Sorts);

    
    if ( !count($Rows) ) $this->finalize(array("status" => -1, "code" => "nodata"));

    $Results         = array();
    $Results["data"] = array();
    foreach ($Rows as $Row)
    {
      
      
      
      
      

      $Results["data"][] = array(
                                 "value" => implode(", ", array_intersect_key($Row, array_flip($Values))),
                                 "label" => implode(", ", array_intersect_key($Row, array_flip($Labels)))
                                );
    }

    

    
    $this->finalize($Results, __FUNCTION__);
  }

  
  
  
  
  public function regis()
  {
    if ( APPDEBUG["FKT"] ) $this->debug("FKT",__METHOD__);

    
    $Regis = $this->checkPostedStr("regis");
    if ( !$Regis )  $this->finalize(array("status" => -1, "code" => "regiserror"));

    
    $Results = $this->getRegisDB($Regis);

    
    $this->finalize($Results, __FUNCTION__);
  }

  
  
  
  
  
  public function remove()
  {
    if ( APPDEBUG["FKT"] ) $this->debug("FKT",__METHOD__);

    
    $Area = $this->checkPostedStr("area");
    $ID   = $this->checkPostedStr("data", "id");
    if ( !$Area || !$ID )  $this->finalize(array("status" => -1, "code" => "removeerror"));
    if ( MODULE == "hosting" ) $Client = "";
    if ( MODULE == "user" )    $Client = $this->client;
    if ( MODULE == "admin" )
    {
      $Client = $this->checkPostedStr("data", "client");
      if ( !$Client ) $this->finalize(array("status" => -1, "code" => "removeerror"));
    }

    
    $this->updStatDB("System", "remove");

    
    $Results  = $this->removeRowDB($Area, $ID, $Client);
    $this->nachStore($Area, $Client);

    
    $this->finalize($Results, __FUNCTION__);
  }

  
  
  
  
  
  public function store()
  {
    if ( APPDEBUG["FKT"] ) $this->debug("FKT",__METHOD__);

    
    $Area = $this->checkPostedStr("area");
    $Data = $this->checkPostedArr("data");
    $Text = $this->checkPostedArr("texts");
    if ( !$Area || ( !count($Data) && !in_array($Area, array("favors", "historys", "profiles", "settings")) ) )  $this->finalize(array("status" => -1, "code" => "storeerror"));
    if ( MODULE == "hosting" ) $Client = "";
    if ( MODULE == "user" )    $Client = $this->client;
    if ( MODULE == "admin" )
    {
      $Client = $this->checkPostedStr("data", "client");
      if ( !$Client ) $this->finalize(array("status" => -1, "code" => "storeerror"));
    }

    
    $this->updStatDB("System", "store");

    
    $Results  = $this->storeRowDB($Area, $Data, $Client);

    if ( is_array($Text) && count($Text) )
    {
      $Results["textids"] = array();
      foreach ( $Text as $T )
      {
        $Tmp = $this->storeRowDB("texts", $T, $Client, true); 
        if ( isset($Tmp["id"]) && isset($T["shortcut"]) )  $Results["textids"][$T["shortcut"]] = $Tmp["id"];
      }
    }

    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    $Children = $this->checkPostedArr("children");
    if ( is_array($Children) && count($Children) )
    {
      $Results["children"] = array();
      foreach ( $Children as $Kind )
      {
        $KArea = isset($Kind["area"]) ? trim((string) $Kind["area"]) : "";
        $KKey  = isset($Kind["key"])  ? trim((string) $Kind["key"])  : "";
        $KWert = isset($Kind["parentvalue"]) ? (string) $Kind["parentvalue"] : "";
        if ( !$KArea )  continue;

        $Bericht = array("area" => $KArea, "stored" => 0, "removed" => 0, "failed" => 0);

        foreach ( (array) ($Kind["rows"] ?? array()) as $Zeile )
        {
          if ( !is_array($Zeile) )  continue;
          
          
          if ( $KKey !== "" )  $Zeile[$KKey] = $KWert;
          $Tmp = $this->storeRowDB($KArea, $Zeile, $Client);
          
          
          
          if ( isset($Tmp["status"]) && $Tmp["status"] >= 0 )  $Bericht["stored"]++;
          else                                                $Bericht["failed"]++;
        }

        foreach ( (array) ($Kind["removeids"] ?? array()) as $RID )
        {
          $RID = trim((string) $RID);
          if ( $RID === "" )  continue;
          
          
          $Tmp = $this->removeRowDB($KArea, $RID, $Client);
          if ( isset($Tmp["status"]) && $Tmp["status"] >= 0 )  $Bericht["removed"]++;
          else                                                $Bericht["failed"]++;
        }

        $Results["children"][] = $Bericht;
        if ( $Bericht["failed"] > 0 )
        {
          $Results["status"] = -1;
          $Results["code"]   = "storechilderror";
        }
      }
    }

    $this->nachStore($Area, $Client);

    
    $this->finalize($Results, __FUNCTION__);
  }

  
  
  
  
  
  
  
  
  
  protected function nachStore($Area, $Client)
  {
  }

  
  
  
  
  public function storeregis()
  {
    if ( APPDEBUG["FKT"] ) $this->debug("FKT",__METHOD__);

    
    $Data = $this->checkPostedArr("data");
    if ( !count($Data) )  $this->finalize(array("status" => -1, "code" => "storeeregiserror"));

    
    $this->updStatDB("System", "storeregis");

    
    $Results  = $this->storeRegisDB($Data);

    
    $this->finalize($Results, __FUNCTION__);
  }

  public function version()
  {
    if ( APPDEBUG["FKT"] ) $this->debug("FKT",__METHOD__);

    

    
    $this->updStatDB("System", "version");

    
    $Results  = $this->getVersionDB();

    
    $this->finalize($Results, __FUNCTION__);
  }

  
  
  
}

?>
