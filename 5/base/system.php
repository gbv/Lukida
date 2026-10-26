<?php

class system extends systemdb
{
  protected $dbpw;
  public    $clientconfigs;
  public    $clienttexts;
  public    $configs;
  public    $texts;

  public function __construct($f3)
  {
    parent::__construct($f3);
    if ( APPDEBUG["FKT"] ) $this->debug("FKT",__METHOD__);

    $this->updateClient();
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

    $Excludes = array("classlocal.%", "event.%", "family.%", "lang.%", "organisation.%", "person.%", "place.%", "role.%");
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

  public function getText($Shortcut)
  {
    $Lang = ($this->lang == "g") ? "german" : "english";
    return ( isset($this->texts[$Shortcut][$Lang]) ) ? $this->texts[$Shortcut][$Lang] : "";
  }

  public function getTextBothLang($Shortcut)
  {
    return ( isset($this->texts[$Shortcut]) ) ? $this->texts[$Shortcut] : array("german" => "", "english" => "");
  }
}

?>
