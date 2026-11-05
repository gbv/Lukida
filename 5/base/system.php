<?php

class system extends systemdb
{
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
