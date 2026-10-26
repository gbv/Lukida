<?php

use PHPMailer\PHPMailer;
require_once __DIR__ . '/_librarys/PHPMailer/Exception.php';
require_once __DIR__ . '/_librarys/PHPMailer/PHPMailer.php';
require_once __DIR__ . '/_librarys/PHPMailer/SMTP.php';

class com extends general
{
  public $adminclient;
  public $avatar;
  public $client;
  public $clientip;
  public $lang;
  public $posted;
  public $user;
  protected $UserTokenPayload = null;
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
    $this->adminclient = $this->checkPostedStr("adminclient");
    define("CLIENTDIR", PROJECTDIR . "clients" . DIRECTORY_SEPARATOR . $this->client);

    if ( APPDEBUG["LOG"] ) $this->debug("LOG", "API Access OK (" . $this->client . ")");

    if ( in_array(ACTION, array("config", "login"))
      || $this->f3->get("security.user") == "none" )
    {
      if ( APPDEBUG["LOG"] ) $this->debug("LOG", "User Access OK (no auth required for: " . ACTION . ")");
      return (0);
    }

    $Tokn = $this->checkPostedStr("tokens", "user");
    if ( $Tokn )
    {
      $UserToken = $this->decodeToken($Tokn, "user");
      if ( is_array($UserToken) && count($UserToken) )
      {
        if ( isset($UserToken["cl"]) && $UserToken["cl"] == $this->client )
        {
          $this->user          = $UserToken["un"];
          $this->avatar        = $UserToken["av"];
          $this->UserTokenPayload = $UserToken;
          if ( APPDEBUG["LOG"] ) $this->debug("LOG", "User Access OK: " . $this->user);
          return (0);
        }
      }
    }

    if ( APPDEBUG["LOG"] ) $this->debug("LOG", "User Access ERROR");
    $this->finalize(array("status" => -1, "code" => "loginerror"));
  }

  private function base64url_encode($str)
  {
    return rtrim(strtr(base64_encode($str), '+/', '-_'), '=');
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

  protected function checkDecryptedPostedArr($Index)
  {
    if ( !$Index || !isset($this->posted[$Index]) ) return [];
    return (array) json_decode($this->posted[$Index]);
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

  protected function decodeToken($token, $type="api")
  {
    if ( APPDEBUG["FKT"] ) $this->debug("FKT", __METHOD__);

    $tokenParts = explode('.', $token);
    if ( count($tokenParts) != 3 )  return [];

    $header             = base64_decode($tokenParts[0]);
    $payload            = base64_decode($tokenParts[1]);
    $signature_provided = $tokenParts[2];
    $dt                 = json_decode($payload)->dt;

    if ( $type == "user" && ((time() - $dt) > (60 * $this->f3->get("application.minsvalidtoken"))) )  return [];

    $base64_url_header  = $this->base64url_encode($header);
    $base64_url_payload = $this->base64url_encode($payload);
    $signature          = hash_hmac('SHA256',
                                    $base64_url_header . "." . $base64_url_payload,
                                    base64_encode($this->f3->get("application.secret")),
                                    true);
    $base64_url_signature = $this->base64url_encode($signature);
    if ( !hash_equals($base64_url_signature, $signature_provided) )  return [];

    return (array) json_decode($payload, true);
  }

  protected function encodeToken($payload)
  {
    if ( APPDEBUG["FKT"] ) $this->debug("FKT", __METHOD__);
    $headers_encoded   = $this->base64url_encode(json_encode(array('alg'=>'HS256','typ'=>'JWT')));
    $payload_encoded   = $this->base64url_encode(json_encode($payload));
    $signature         = hash_hmac('SHA256',
                                   $headers_encoded . "." . $payload_encoded,
                                   base64_encode($this->f3->get("application.secret")),
                                   true);
    $signature_encoded = $this->base64url_encode($signature);
    return "$headers_encoded.$payload_encoded.$signature_encoded";
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

    if ( is_array($this->UserTokenPayload) && count($this->UserTokenPayload)
      && ( !isset($Results["status"]) || $Results["status"] >= 0 ) )
    {
      $Payload       = $this->UserTokenPayload;
      $Payload["dt"] = time();
      $Results["tokenrefresh"] = $this->encodeToken($Payload);
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

  public function prepareLookupCachePath($System, $Code)
  {
    $Path = CACHESDIR . "nachschlag";
    $this->createPath($Path);
    $Path .= DIRECTORY_SEPARATOR . strtolower(preg_replace("/[^A-Za-z0-9]/", "", (string) $System));
    $this->createPath($Path);
    $Name = strtolower(preg_replace("/[^A-Za-z0-9]/", "_", (string) $Code));
    $Name = substr($Name, 0, 40) . "_" . substr(md5((string) $Code), 0, 8);
    return $Path . DIRECTORY_SEPARATOR . $Name . ".inc";
  }

  public function prepareSharedCachePath($Dir, $File)
  {
    $Path = CACHESDIR . date("Ymd");
    $this->createPath($Path);
    $Path .= DIRECTORY_SEPARATOR . "_system";
    $this->createPath($Path);
    $Path .= DIRECTORY_SEPARATOR . strtolower($Dir);
    $this->createPath($Path);
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

  protected function smtpVerschluesselung()
  {
    $Wahl = strtolower(trim((string) $this->f3->get("smtp.secure")));
    if ( $Wahl === "" )
      $Wahl = ((int) $this->f3->get("smtp.port") === 465) ? "ssl" : "tls";
    if ( $Wahl === "ssl" || $Wahl === "smtps" )  return PHPMailer::ENCRYPTION_SMTPS;
    if ( $Wahl === "none" || $Wahl === "no" )    return "";
    return PHPMailer::ENCRYPTION_STARTTLS;
  }

  protected function smtpBlindkopie()
  {
    if ( !$this->f3->exists("smtp.bcc") )
      return trim((string) $this->f3->get("author.mail"));
    return trim((string) $this->f3->get("smtp.bcc"));
  }

  public function sendMail($Data)
  {
    if ( !isset($Data["subject"]) || empty($Data["subject"])
      || !isset($Data["to"])      || empty($Data["to"])
      || !isset($Data["toname"])  || empty($Data["toname"])
      || !isset($Data["message"]) || empty($Data["message"]) )
    {
      return array("status" => -1, "code" => "mailerror", "more" => "missingvalue");
    }

    $mail = new PHPMailer(true);
    $mail->SMTPSecure = $this->smtpVerschluesselung();
    $mail->CharSet    = 'UTF-8';
    $mail->Encoding   = 'base64';
    $mail->Timeout    = 20;
    $mail->isSMTP(true);
    $mail->isHTML(true);
    $mail->Host       = $this->f3->get("smtp.host");
    $mail->SMTPAuth   = $this->f3->get("smtp.auth");
    $mail->Username   = $this->f3->get("smtp.user");
    $mail->Password   = $this->f3->get("smtp.pass");
    $mail->Port       = $this->f3->get("smtp.port");

    if ( isset($Data["from"]) && trim($Data["from"]) && isset($Data["fromname"]) && trim($Data["fromname"]) )
      $mail->setFrom($Data["from"], $Data["fromname"]);
    else
      $mail->setFrom($this->f3->get("smtp.from"), $this->f3->get("smtp.fromname"));

    if ( $this->isWin() )
      $mail->SMTPOptions = array('ssl' => array('verify_peer' => false, 'verify_peer_name' => false, 'allow_self_signed' => true));

    if ( in_array(substr(MODE, 0, 4), array("test", "prod")) || ( defined("REALMAIL") && REALMAIL ) )
    {
      if ( is_array($Data["to"]) )
        foreach ( $Data["to"] as $To )  $mail->addAddress($To);
      else
        $mail->addAddress($Data["to"]);

      $Blindkopie = $this->smtpBlindkopie();
      if ( $Blindkopie !== "" )  $mail->addBcc($Blindkopie);
    }
    else
    {
      $mail->addAddress($this->f3->get("author.mail"));
    }

    $mail->Subject = $Data["subject"];
    $mail->Body    = $Data["message"];
    $mail->AltBody = $this->htmlToPlainText($Data["message"]);

    if ( isset($Data["attach"]) && is_array($Data["attach"]) && count($Data["attach"]) )
      foreach ( $Data["attach"] as $File )
      {
        if ( !isset($File) || empty($File) )  continue;
        $FilePath = TRANSFERSDIR . $File;
        if ( file_exists($FilePath) )  $mail->addAttachment($FilePath, $File);
      }

    try
    {
      if ( !$mail->send() )
      {
        $this->debug("Mail Error", print_r($mail->ErrorInfo, true));
        return array("status" => -1, "code" => "mailerror", "more" => preg_split('/\r\n|\r|\n/', $mail->ErrorInfo));
      }
      return array("status" => 0, "code" => "mailsuccessful");
    }
    catch (Exception $e)
    {
      $this->debug("Mail Error", print_r($e, true));
      return array("status" => -1, "code" => "mailerror");
    }
  }

  public function sendErrorMail($Data)
  {
    if ( !isset($Data["subject"]) || empty($Data["subject"])
      || !isset($Data["message"]) || empty($Data["message"]) )
      return array("status" => -1, "code" => "mailerror", "more" => "missingvalue");

    $mail = new PHPMailer();
    $mail->SMTPSecure = $this->smtpVerschluesselung();
    $mail->CharSet    = 'UTF-8';
    $mail->Encoding   = 'base64';
    $mail->Timeout    = 20;
    $mail->isSMTP(true);
    $mail->isHTML(true);
    $mail->Host       = $this->f3->get("smtp.host");
    $mail->SMTPAuth   = $this->f3->get("smtp.auth");
    $mail->Username   = $this->f3->get("smtp.user");
    $mail->Password   = $this->f3->get("smtp.pass");
    $mail->Port       = $this->f3->get("smtp.port");
    $mail->setFrom($this->f3->get("smtp.from"), $this->f3->get("smtp.fromname"));

    if ( $this->isWin() )
      $mail->SMTPOptions = array('ssl' => array('verify_peer' => false, 'verify_peer_name' => false, 'allow_self_signed' => true));

    $mail->addAddress($this->f3->get("author.mail"));
    $mail->Subject = $Data["subject"];

    $Message = "";
    if ( is_array($Data["message"]) && count($Data["message"]) )
    {
      $Message = "<table>";
      foreach ( $Data["message"] as $Key => $Val )
        $Message .= "<tr><td>" . $Key . "</td><td>" . $Val . "</td></tr>";
      $Message .= "</table>";
    }

    $mail->Body    = $Message;
    $mail->AltBody = $this->htmlToPlainText($Message);

    if ( isset($Data["attach"]) && is_array($Data["attach"]) && count($Data["attach"]) )
      foreach ( $Data["attach"] as $File )
      {
        if ( !isset($File) || empty($File) )  continue;
        $FilePath = TRANSFERSDIR . $File;
        if ( file_exists($FilePath) )  $mail->addAttachment($FilePath, $File);
      }

    if ( !$mail->send() )
    {
      $this->debug("Mail Error", print_r($mail->ErrorInfo, true));
      return array("status" => -1, "code" => "mailerror", "more" => preg_split('/\r\n|\r|\n/', $mail->ErrorInfo));
    }
    return array("status" => 0, "code" => "mailsuccessful");
  }

  public function nurEinmalProStunde($Schluessel, $Sekunden = 3600)
  {
    if ( !defined('PROJECTDIR') )  return false;

    $ThrottleDir = PROJECTDIR . "logs" . DIRECTORY_SEPARATOR . "mailthrottle" . DIRECTORY_SEPARATOR;
    if ( !file_exists($ThrottleDir) )  @mkdir($ThrottleDir, 0777, true);

    $Key    = md5((defined('MODULE') ? MODULE : "") . "|" . $Schluessel);
    $Marker = $ThrottleDir . $Key . ".marker";
    if ( file_exists($Marker) && (time() - filemtime($Marker)) < $Sekunden )  return false;
    $Neu = !file_exists($Marker);
    @touch($Marker);
    if ( $Neu )  @chmod($Marker, 0666);

    foreach ( (array) @glob($ThrottleDir . "*.marker") as $Alt )
      if ( (time() - @filemtime($Alt)) > 7 * 24 * 3600 )  @unlink($Alt);

    return true;
  }

  public function mailFehlerGedrosselt($Context, $Client, $Message, $Data = array())
  {
    if ( !$this->nurEinmalProStunde($Context . "|" . $Client . "|" . $Message) )  return;

    $this->sendErrorMail(array(
      "subject" => $Context . " (" . $Client . ")",
      "message" => array("Zeitpunkt" => date("Y-m-d H:i:s"), "Client" => $Client) + (array) $Data,
    ));
  }

  public function mailDBError($Context, $Message, $SQL = "")
  {
    if ( !$this->nurEinmalProStunde($Context . "|" . $Message, 1800) )  return;

    $MailMessage = array(
      "Zeitpunkt" => date("Y-m-d H:i:s"),
      "Server"    => (defined('MODE') ? MODE : "-") . " / " . php_uname("n"),
      "Modul"     => defined('MODULE') ? MODULE : "-",
      "Aktion"    => defined('ACTION') ? ACTION : "-",
      "Kontext"   => $Context,
      "Fehler"    => $Message,
    );
    if ( $SQL !== "" )  $MailMessage["SQL"] = $SQL;

    $Anwendung = trim((string) $this->f3->get("application.name"));
    if ( $Anwendung === "" )  $Anwendung = "App";

    $this->sendErrorMail(array(
      "subject" => "[" . $Anwendung . (defined('MODULE') ? " " . strtoupper(MODULE) : "") . "] DB-Fehler: " . $Context,
      "message" => $MailMessage,
    ));
  }
}
?>
