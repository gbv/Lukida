<?php

// Every web request (any API version/module) goes through this single front
// controller (apis/index.php requires this). Without this, files created during
// a request (logs/Log_*.txt via general.php::print2File(), ...) inherit
// PHP-FPM/Apache's own restrictive default umask and end up owned by www-data
// with no group/other write bit - blocking the SAME file being written later by
// a CLI-run batch script (as alex), and vice versa. Same fix/reasoning as
// phps/UpdateClients.php's own `umask(0);` for its child processes (vite/7z/...),
// just covering the web-request side of the same "whoever writes first as which
// user locks the other out" bug class instead.
umask(0);

// Function WelcomeBrowser
function WelcomeBrowser($Name)
{
  echo 'Welcome to the ' . $Name . ' API<br />';
  echo '<hr>';
  echo 'Please use at least these parameters' . '<br />';
  echo '/{API-Version}/{Module}/{Action}'     . '<br />';
  echo '<br />';
  echo 'The API is only avialble by using <b>POST</b> requests containing parameters in the JSON-body.';
  echo '<br />';
  header("HTTP/1.1 200 OK");
  header("Content-Type: application/json");
  header("Access-Control-Allow-Methods: POST");
  header("Access-Control-Allow-Headers: Origin, Accept, X-Requested-With, Content-Type, Access-Control-Request-Method, Access-Control-Request-Headers, Access-Control-Allow-Origin, Access-Control-Max-Age");
  header("Access-Control-Allow-Origin: *");
}

// Function Error
function Error($Error)
{
  $Results = array(
                    "status" => -1,
                    "code"   => $Error
                  );

  // Return $this->posted as JSON
  header("HTTP/1.1 200 OK");
  header("Content-Type: application/json");
  header("Access-Control-Allow-Methods: POST");
  header("Access-Control-Allow-Headers: Origin, Accept, X-Requested-With, Content-Type, Access-Control-Request-Method, Access-Control-Request-Headers, Access-Control-Allow-Origin, Access-Control-Max-Age");
  header("Access-Control-Allow-Origin: *");
  die (json_encode($Results));
}

// Send Options Header
function SendOptionsHeader()
{
  header("HTTP/1.1 200 OK");
  header("Content-Type: application/json");
  header("Access-Control-Allow-Methods: POST");
  header("Access-Control-Allow-Headers: Origin, Accept, X-Requested-With, Content-Type, Access-Control-Request-Method, Access-Control-Request-Headers, Access-Control-Allow-Origin, Access-Control-Max-Age");
  header("Access-Control-Allow-Origin: *");
  header("Access-Control-Max-Age: 86400");
  die();
}

// Define project directory
define('PROJECTDIR', realpath(__DIR__.'/..') . DIRECTORY_SEPARATOR);

// Check & define MODE
if ( !in_array(strtolower(trim($_SERVER["MODE"])),
               array("local","development","test","production")))                               die(Error('Mode not set/unknown!'));
define('MODE', strtolower(trim($_SERVER["MODE"])));

// Load application configuration
if ( !file_exists(PROJECTDIR . 'configs') )                                                      die(Error('configs directory missing!'));
if ( !file_exists(PROJECTDIR . 'configs/application.ini') )                                      die(Error('application.ini not found!'));
$f3->config(PROJECTDIR . 'configs/application.ini');

// Send options header for preflight requests
if ( $f3->get("SERVER.REQUEST_METHOD") == "OPTIONS" )                                           SendOptionsHeader();

// Send welcome message to browsers
if ( $f3->get("SERVER.REQUEST_METHOD") == "GET" )                                               WelcomeBrowser($f3->get("application.name"));

// Send welcome message to browsers
if ( $f3->get("SERVER.REQUEST_METHOD") != "POST" )                                              die(Error('Method should be post'));

// Send welcome answer to wrong api calls
if ( count(explode("/",trim($f3->get("SERVER.REQUEST_URI"),"/"))) != 3 )                        die(Error('url params not correct'));

// Check & define version
if ( !file_exists(explode("/",trim($f3->get("SERVER.REQUEST_URI"),"/"))[0]) )                   die(Error('Version not found!'));
// define('API', 5);
define('API', explode("/",trim($f3->get("SERVER.REQUEST_URI"),"/"))[0]);

// Check & define module
if ( !file_exists(API . '/appl/' . explode("/",trim($f3->get("SERVER.REQUEST_URI"),"/"))[1])
  || !file_exists(API . '/base/' . explode("/",trim($f3->get("SERVER.REQUEST_URI"),"/"))[1]) )       die(Error('Module not found!'));
if ( !file_exists(PROJECTDIR . 'configs/' . MODE . '/'
                  . explode("/",trim($f3->get("SERVER.REQUEST_URI"),"/"))[1] . '.ini') )        die(Error(explode("/",trim(
                                                                                                    $f3->get("SERVER.REQUEST_URI"),"/"))[1] 
                                                                                                    . '.ini not found!'));
define('MODULE', explode("/",trim($f3->get("SERVER.REQUEST_URI"),"/"))[1]);

// Der EINE Datenbankschalter dieses Servers (2026-08-14).
//
// "database.ini" liegt je Umgebung genau einmal und gilt fuer alle Module und
// BEIDE APIs - deshalb wird sie VOR der Modul-INI geladen: F3 fuehrt die
// Abschnitte schluesselweise zusammen, die Modul-INI steuert danach nur noch
// host/name/user/pass bei. Sie steht bewusst nicht in application.ini, denn
// die geht unveraendert an alle Server; der Treiber soll aber je Server
// umgelegt werden koennen.
//
// Fehlt die Datei, bleibt es bei MySQL - so laeuft ein Stand ohne sie weiter.
if ( file_exists(PROJECTDIR . 'configs/' . MODE . '/database.ini') )
  $f3->config(PROJECTDIR . 'configs/' . MODE . '/database.ini');

$f3->config(PROJECTDIR . 'configs/' . MODE . '/' . MODULE . '.ini');

// Set security
if ( !$f3->get("security.app") )                                                                die(Error('Module sucurity app not found!'));
if ( !in_array($f3->get("security.app"), array("none", "token")) )                              die(Error('Module sucurity app wrong!'));
if ( !$f3->get("security.user") )                                                               die(Error('Module sucurity user not found!'));
if ( !in_array($f3->get("security.user"), array("none", "token")) )                             die(Error('Module sucurity user wrong!'));

// Define Action
define('ACTION', explode("/",trim($f3->get("SERVER.REQUEST_URI"),"/"))[2]);

// Load Controllers
$f3->set('AUTOLOAD', implode("/;" , array(API . "/appl",
                                          API . "/appl/" . MODULE,
                                          API . "/base",
                                          API . "/base/" . MODULE,
                                          API)) . "/;");

$f3->run($f3);

?>