<?php

abstract class bms
{
  protected $CS;
  protected $bmsid;
  protected $config;
  protected $lang;
  protected $link;

  public function __construct($BMSID, $CS)
  {
    $this->bmsid  = $BMSID;
    $this->CS     = $CS;
    $this->lang   = $CS->lang;
    $this->config = ( isset($CS->bms[$BMSID]) && is_array($CS->bms[$BMSID]) ) ? $CS->bms[$BMSID] : array();

    $Api          = ( MODE == "production" ) ? "apiprod" : "apitest";
    $this->link   = ( isset($this->config[$Api]) && trim($this->config[$Api]) ) ? rtrim(trim($this->config[$Api]), "/") : "";
  }

  abstract public function checkUser($User, $Pass);
  abstract public function ReadUser($Login, $Token);

  public function prepareItems($Map) { }

  abstract public function getPPN($PPN, $Format);
  abstract public function getEPN($EPN, $Format);
  abstract public function Request($Login, $ID, $Token, $Cond, $Type);
  abstract public function Renew($Login, $ID, $Token);
  abstract public function Cancel($Login, $ID, $Token);
  abstract public function ChangeMail($Login, $Token, $New);
  abstract public function ChangePW($Login, $Token, $Old, $New);
  abstract public function ResetPW($Login);

  protected const BESTANDFRISCH = 600;

  protected function bmsBestandLesen($Schluessel)
  {
    $Datei = $this->CS->prepareDaiaCachePath($this->link . "|" . $this->lang, $Schluessel);
    if ( !file_exists($Datei) )  return null;

    $Alter = time() - (int) @filemtime($Datei);
    if ( $Alter < 0 || $Alter > static::BESTANDFRISCH )  return null;

    $Inhalt = @file_get_contents($Datei);
    if ( $Inhalt === false || $Inhalt === "" )  return null;

    $Daten = json_decode($Inhalt, true);
    if ( !is_array($Daten) )  return null;

    if ( APPDEBUG["BMO"] ) $this->CS->debug("BMO", array("bestand" => "gelesen", "alter" => $Alter, "id" => $Schluessel));
    return $Daten;
  }

  protected function bmsBestandSchreiben($Schluessel, $Daten)
  {
    if ( !is_array($Daten) || !count($Daten) )  return;

    $Datei = $this->CS->prepareDaiaCachePath($this->link . "|" . $this->lang, $Schluessel);

    $Temp = $Datei . "." . getmypid() . ".tmp";
    if ( @file_put_contents($Temp, json_encode($Daten)) === false )  return;
    @chmod($Temp, 0666);
    if ( !@rename($Temp, $Datei) )  { @unlink($Temp); return; }

    $this->bmsBestandAufraeumen($Datei);
  }

  protected function bmsBestandVerwerfen()
  {
    $Ordner = $this->CS->prepareDaiaCachePath($this->link . "|" . $this->lang, "", true);
    foreach ( (array) glob($Ordner . DIRECTORY_SEPARATOR . "*" . DIRECTORY_SEPARATOR . "*.inc") as $Datei )
    {
      try { unlink($Datei); }
      catch ( \Throwable $e )
      {
        if ( file_exists($Datei) )  throw $e;
      }
    }
  }

  private function bmsBestandAufraeumen($Datei)
  {
    if ( random_int(1, 25) !== 1 )  return;

    foreach ( (array) glob(dirname($Datei) . DIRECTORY_SEPARATOR . "*.inc") as $Alt )
    {
      try
      {
        $Zeit = filemtime($Alt);
        if ( $Zeit !== false && (time() - $Zeit) > 3600 )  unlink($Alt);
      }
      catch ( \Throwable $e )
      {
        if ( file_exists($Alt) )  throw $e;
      }
    }
  }

  protected function http($Method, $Url, $Body = null, $Headers = array())
  {
    static $ch = null;
    if ( $ch === null )  $ch = curl_init();
    curl_reset($ch);

    if ( APPDEBUG["BMI"] ) $this->CS->debug("BMI", array("method"  => $Method,
                                                         "url"     => $Url,
                                                         "headers" => $this->maskHeaders($Headers),
                                                         "body"    => $this->mask(is_string($Body) ? (json_decode($Body, true) ?? $Body) : $Body)));

    $Opts = array(
      CURLOPT_URL            => $Url,
      CURLOPT_CUSTOMREQUEST  => $Method,
      CURLOPT_HTTPHEADER     => $Headers,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_HEADER         => true,
      CURLOPT_CONNECTTIMEOUT => 10,
      CURLOPT_TIMEOUT        => 60,
      CURLOPT_ENCODING       => "",

      CURLOPT_SSL_VERIFYPEER => $this->option("sslverify") == "y",
      CURLOPT_SSL_VERIFYHOST => $this->option("sslverify") == "y" ? 2 : 0,
    );
    if ( $Body !== null )  $Opts[CURLOPT_POSTFIELDS] = is_string($Body) ? $Body : json_encode($Body);
    curl_setopt_array($ch, $Opts);

    $Raw = curl_exec($ch);
    if ( $Raw === false )
    {
      $Result = array("ok" => false, "http" => 0, "headers" => "", "body" => "", "json" => null, "error" => curl_error($ch));
      if ( APPDEBUG["BMO"] ) $this->CS->debug("BMO", $Result);
      return $Result;
    }

    $HeaderSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $Body       = substr($Raw, $HeaderSize);
    $Json       = json_decode($Body, true);
    $Result     = array("ok"      => true,
                        "http"    => curl_getinfo($ch, CURLINFO_HTTP_CODE),
                        "headers" => substr($Raw, 0, $HeaderSize),
                        "body"    => $Body,
                        "json"    => is_array($Json) ? $Json : null,
                        "error"   => "");

    if ( APPDEBUG["BMO"] ) $this->CS->debug("BMO", array("http" => $Result["http"], "body" => $this->mask($Result["json"] ?? $Body)));
    return $Result;
  }

  protected function mask($Data)
  {
    if ( !is_array($Data) )  return $Data;
    foreach ( $Data as $Key => $Val )
    {
      if ( is_string($Key) && preg_match('/pass|token|secret/i', $Key) )  $Data[$Key] = "***";
      elseif ( is_array($Val) )                                           $Data[$Key] = $this->mask($Val);
    }
    return $Data;
  }

  protected function maskHeaders($Headers)
  {
    return array_map(fn($H) => preg_replace('/^(Authorization|Cookie):.*$/i', '$1: ***', $H), $Headers);
  }

  protected function option($Key, $Default = "")
  {
    return ( isset($this->config["options"][$Key]) && $this->config["options"][$Key] !== "" ) ? $this->config["options"][$Key] : $Default;
  }

  protected function isConfigYes($Setting)
  {
    return isset($this->CS->configs["bms"][$Setting]) && $this->CS->configs["bms"][$Setting] == "y";
  }

  protected function actionError($MessCode, $More = array())
  {
    return array("status" => -1,
                 "stat"   => "error",
                 "mess"   => array($this->CS->getText($MessCode)),
                 "more"   => array_values(array_filter((array) $More, fn($M) => $M !== "" && $M !== null)));
  }

  protected function actionSuccess($MessCode, $More = array())
  {
    return array("status" => 0,
                 "stat"   => "success",
                 "mess"   => array($this->CS->getText($MessCode)),
                 "more"   => (array) $More);
  }

  protected function answerLines($Kind, $EndTime = "", $Storage = "")
  {
    $Lines = array();
    if ( $EndTime && $this->isConfigYes("answer" . $Kind . "endtime") )
    {
      $Lines[] = $this->CS->getText("action.requestreturn") . ": " . $this->CS->format2German($EndTime);
    }
    if ( $Storage && $Kind != "renew" && $this->isConfigYes("answer" . $Kind . "storage") )
    {
      $Lines[] = $this->CS->getText("action.requestpickup") . ": " . $Storage;
    }
    if ( $EndTime && $this->isConfigYes("answer" . $Kind . "daysremaining") )
    {
      $Lines[] = $this->CS->getText("action.daysremaining") . ": " . $this->CS->daysLeft($EndTime);
    }
    return $Lines;
  }
}

?>
