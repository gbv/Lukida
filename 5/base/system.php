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
  }

  protected function getConfigs($ClientConfigs)
  {
    if ( APPDEBUG["FKT"] ) $this->debug("FKT",__METHOD__);

    $File = $this->prepareCachePath(array("settings"), "configs");
    if ( file_exists($File) )
    {
       $this->configs = json_decode(file_get_contents($File),true);
    }
    else
    {
      $this->configs  = $this->getAdminConfigsDB();

      $Alt = array(
        "solrpath"        => array("solr",    "path"),
        "kisuggest"       => array("project", "kisuggest"),
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
       $this->clientconfigs = json_decode(file_get_contents($File),true);
    }
    else
    {
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

    $File = $this->prepareCachePath(array("settings"), "texts");
    if ( file_exists($File) )
    {
       $this->texts = json_decode(file_get_contents($File), true);
    }
    else
    {
      $this->texts  = $this->getAdminTextsDB();
      file_put_contents($File, json_encode($this->texts, true));
    }

    $File = $this->prepareCachePath(array("settings"), "Clienttexts");
    if ( file_exists($File) )
    {
       $this->clienttexts = json_decode(file_get_contents($File),true);
    }
    else
    {
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


    $Source = trim($this->checkPostedStr("source"));
    if ( $Source )
    {
      $Source = preg_replace('/[^\p{L}\p{N} ._+-]/u', "", $Source);
      $Source = trim(mb_substr($Source, 0, 50));
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
