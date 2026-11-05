<?php


class com extends general
{
  public $client;
  public $clientip;
  public $lang;
  public $posted;
  public $user;
  public $apikey;

  public function __construct($f3)
  {
    parent::__construct($f3);
    if ( APPDEBUG["FKT"] ) $this->debug("FKT", __METHOD__);

    $this->posted = (array) json_decode(file_get_contents("php://input"), true);
    if ( APPDEBUG["PAY"] ) $this->debug("PAY", $this->posted);

    $this->lang = ( isset($this->posted["lang"])
                    && trim($this->posted["lang"])
                    && in_array(trim($this->posted["lang"]), array("e", "g")) )
                  ? trim($this->posted["lang"]) : "";

    $this->clientip = "";
    if (!$this->clientip && !empty($_SERVER['HTTP_X_REAL_IP']))       $this->clientip = $_SERVER['HTTP_X_REAL_IP'];
    if (!$this->clientip && !empty($_SERVER['HTTP_X_FORWARDED_FOR'])) $this->clientip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    if (!$this->clientip && !empty($_SERVER['REMOTE_ADDR']))          $this->clientip = $_SERVER['REMOTE_ADDR'];

    $ConfigKey  = trim((string) $this->f3->get("application.apikey"));
    $PostedKey  = $this->checkPostedStr("apikey");

    if ( !$ConfigKey )
    {
      if ( APPDEBUG["LOG"] ) $this->debug("LOG", "API Access ERROR: no apikey configured");
      $this->finalize(array("status" => -1, "code" => "apikeynotconfigured"));
    }

    if ( !$PostedKey || !hash_equals($ConfigKey, $PostedKey) )
    {
      if ( APPDEBUG["LOG"] ) $this->debug("LOG", "API Access ERROR: invalid apikey");
      $this->finalize(array("status" => -1, "code" => "apikeyinvalid"));
    }

    $Client = $this->checkPostedStr("client");
    if ( !$Client )
    {
      if ( APPDEBUG["LOG"] ) $this->debug("LOG", "API Access ERROR: no client");
      $this->finalize(array("status" => -1, "code" => "paramsmissing"));
    }

    $this->client      = $Client;

    if ( APPDEBUG["LOG"] ) $this->debug("LOG", "API Access OK (" . $this->client . ")");
  }

  protected function checkPostedStr($Index, $Index2="", $Index3="")
  {
    if ( !$Index  || !isset($this->posted[$Index]) )                     return "";
    if (  $Index2 && !isset($this->posted[$Index][$Index2]) )            return "";
    if (  $Index3 && !isset($this->posted[$Index][$Index2][$Index3]) )   return "";

    if ( $Index3 && isset($this->posted[$Index][$Index2][$Index3]) && $this->isPostedScalar($this->posted[$Index][$Index2][$Index3]) )
      return trim((string) $this->posted[$Index][$Index2][$Index3]);
    elseif ( $Index2 && isset($this->posted[$Index][$Index2]) && $this->isPostedScalar($this->posted[$Index][$Index2]) )
      return trim((string) $this->posted[$Index][$Index2]);
    elseif ( $Index && isset($this->posted[$Index]) && $this->isPostedScalar($this->posted[$Index]) )
      return trim((string) $this->posted[$Index]);
    else
      return "";
  }

  private function isPostedScalar($Value)
  {
    return is_string($Value) || is_int($Value) || is_float($Value);
  }

  protected function checkDecryptedPostedStr($Index, $Index2="", $Index3="")
  {
    if ( !$Index  || !isset($this->posted[$Index]) )                     return "";
    if (  $Index2 && !isset($this->posted[$Index][$Index2]) )            return "";
    if (  $Index3 && !isset($this->posted[$Index][$Index2][$Index3]) )   return "";

    if ( $Index3 )      return (string) json_decode($this->posted[$Index][$Index2][$Index3]);
    elseif ( $Index2 )  return (string) json_decode($this->posted[$Index][$Index2]);
    else                return (string) json_decode($this->posted[$Index]);
  }

  protected function checkPostedArr($Index, $Index2="", $Index3="")
  {
    if ( !$Index  || !isset($this->posted[$Index]) )                    return [];
    if (  $Index2 && !isset($this->posted[$Index][$Index2]) )           return [];
    if (  $Index3 && !isset($this->posted[$Index][$Index2][$Index3]) )  return [];

    if ( $Index3 )      return $this->posted[$Index][$Index2][$Index3];
    elseif ( $Index2 )  return $this->posted[$Index][$Index2];
    else                return $this->posted[$Index];
  }

  protected function finalize($Results = array(), $Function = "")
  {
    if ( APPDEBUG["FKT"] ) $this->debug("FKT", __METHOD__);

    if ( ( !isset($Results["status"]) && !isset($Results["stat"]) )
      || ( !isset($Results["code"])   && !isset($Results["mess"]) ) )
    {
      if ( is_array($Results) && count($Results) )
      {
        if ( !isset($Results["hitspack"]) && isset($Results["data"]) )
          $Results += array("hitspack" => count($Results["data"]));
        $Results += array("status" => 1, "code" => ($Function ? $Function : "unknown") . "successful");
      }
      else
      {
        $Results = array("status" => 0, "code" => ($Function ? $Function : "unknown") . "error");
      }
    }

    header("HTTP/1.1 200 OK");
    if ( isset($Results["content"]) && trim($Results["content"]) )
      header("Content-Type: " . $Results["content"]);
    else
      header("Content-Type: application/json");

    header("Access-Control-Allow-Methods: POST");
    header("Access-Control-Allow-Headers: Origin, Accept, X-Requested-With, Content-Type, Access-Control-Request-Method, Access-Control-Request-Headers, Access-Control-Allow-Origin, Access-Control-Max-Age");
    header("Access-Control-Allow-Origin: *");

    die(json_encode($Results));
  }

  public function prepareCachePath($PathArray, $File, $Year = false)
  {
    $Path = $Year ? CACHEYEARDIR : CACHESDIR . date("Ymd");
    $this->createPath($Path);
    $Path .= DIRECTORY_SEPARATOR . $this->client;
    $this->createPath($Path);
    foreach ( $PathArray as $Dir )
    {
      $Path .= DIRECTORY_SEPARATOR . strtolower($Dir);
      $this->createPath($Path);
    }
    return $Path . DIRECTORY_SEPARATOR . strtolower($File) . ".inc";
  }

  public function prepareIdCachePath($ID, $File)
  {
    $Path = CACHESDIR . "ids";
    $this->createPath($Path);
    foreach ( str_split(preg_replace("/[^A-Za-z0-9_-]/", "", (string) $ID), 4) as $Teil )
    {
      $Path .= DIRECTORY_SEPARATOR . $Teil;
      $this->createPath($Path);
    }
    return $Path . DIRECTORY_SEPARATOR . strtolower($File) . ".inc";
  }

  public function htmlToPlainText($Html)
  {
    $Text = preg_replace('/<head\b[^>]*>.*?<\/head>/is', '', $Html);
    $Text = preg_replace('/<br\s*\/?>/i', "\n", $Text);
    $Text = preg_replace('/<\/(p|div|tr|h[1-6]|li)>/i', "\n", $Text);
    $Text = preg_replace('/<\/td>/i', "\t", $Text);
    $Text = strip_tags($Text);
    $Text = html_entity_decode($Text, ENT_QUOTES, 'UTF-8');
    $Text = preg_replace('/\n{3,}/', "\n\n", $Text);
    return trim($Text);
  }

  // Datenbankfehler ins Log
  public function meldeDBFehler($Context, $Message, $SQL = "")
  {
    $this->debug("ERR", array_filter(array(
      "type"    => "DB",
      "modul"   => defined('MODULE') ? MODULE : "-",
      "aktion"  => defined('ACTION') ? ACTION : "-",
      "kontext" => $Context,
      "fehler"  => $Message,
      "sql"     => $SQL,
    ), fn($v) => $v !== ""));
  }
}
?>
