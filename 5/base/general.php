<?php

class general
{
  
  
  
  
  public $f3;

  
  protected $avail;

  public function __construct($f3)
  {
    
    $this->f3    = $f3;

    date_default_timezone_set('Europe/Berlin');

    
    define('APPDEBUG', array("PATH" => PROJECTDIR . "logs" . DIRECTORY_SEPARATOR,
                             "FKT"  => $this->f3->get("appdebug.fkt"),
                             "PAY"  => $this->f3->get("appdebug.pay"),
                             "LOG"  => $this->f3->get("appdebug.log"),
                             "SYS"  => $this->f3->get("appdebug.sys"),
                             "SQQ"  => $this->f3->get("appdebug.sqq"),
                             "SQP"  => $this->f3->get("appdebug.sqp"),
                             "SQD"  => $this->f3->get("appdebug.sqd"),
                             "OSC"  => $this->f3->get("appdebug.osc"),
                             "OSR"  => $this->f3->get("appdebug.osr"),
                             "SFC"  => $this->f3->get("appdebug.sfc"),
                             "SLQ"  => $this->f3->get("appdebug.slq"),
                             "SLD"  => $this->f3->get("appdebug.sld"),
                             "MCD"  => $this->f3->get("appdebug.mcd"),
                             "BMI"  => $this->f3->get("appdebug.bmi"),
                             "BMO"  => $this->f3->get("appdebug.bmo"),
                             "MED"  => $this->f3->get("appdebug.med"),
                             "ADD"  => $this->f3->get("appdebug.add"),
                             "IDS"  => $this->f3->get("appdebug.ids")
                           ));

    if ( APPDEBUG["FKT"] ) $this->debug("FKT",__METHOD__);

    
    define('ASSETSDIR',        PROJECTDIR . "assets"  . DIRECTORY_SEPARATOR);

    
    define('BACKUPSDIR',       PROJECTDIR . "backups" . DIRECTORY_SEPARATOR);

    
    define('CACHESDIR',        PROJECTDIR . "caches"  . DIRECTORY_SEPARATOR);

    
    
    
    
    
    
    
    
    
    
    
    
    define('IPCACHEDIR',       PROJECTDIR . "caches" . DIRECTORY_SEPARATOR . "ips" . DIRECTORY_SEPARATOR);

    
    
    
    
    define('BREMSECACHEDIR',   PROJECTDIR . "caches" . DIRECTORY_SEPARATOR . "bremse" . DIRECTORY_SEPARATOR);

    
    define('SYSTEMLIBRARYDIR', PROJECTDIR . "apis/" . API . "/base/_librarys" . DIRECTORY_SEPARATOR);
    define('LIBRARYDIR',       PROJECTDIR . "apis/" . API . "/appl/_librarys" . DIRECTORY_SEPARATOR);

    
    define('TRANSFERSDIR',     PROJECTDIR . "transfers" . DIRECTORY_SEPARATOR);

    
    spl_autoload_register(function($library)
    {
      $ds      = DIRECTORY_SEPARATOR;
      $library = str_replace('\\', $ds, $library);

      
      $file    = PROJECTDIR . "lukida-light/" . API . "/appl/_librarys/" . $library . '.php';
      if (!is_readable($file))
      {
        
        $file    = PROJECTDIR . "lukida-light/" . API . "/base/_librarys/" . $library . '.php';
        if (!is_readable($file))  return;
      }
      require_once $file;
    });

    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    set_error_handler( function ($severity, $message, $file, $line)
    {
      
      if ( !(error_reporting() & $severity) )  return false;

      if ( $severity & (E_DEPRECATED | E_USER_DEPRECATED) )
      {
        static $Gesehen = array();
        $Stelle = $file . ":" . $line;
        if ( !isset($Gesehen[$Stelle]) )
        {
          $Gesehen[$Stelle] = true;
          error_log("PHP-Veraltet: " . $message . " in " . $file . " Zeile " . $line);
        }
        return true;
      }

      throw new ErrorException($message, $severity, $severity, $file, $line);
    });
  }

  
  
  
  private function getLogFile()
  {
    $Modul = (defined('MODULE')) ? MODULE . "_" : "";
    return APPDEBUG["PATH"] . "Log_" . $Modul . date("Y.m.d") . ".txt";
  }

  
  
  
  
  
  
  private function writeLogFile($Text)
  {
    $Datei = $this->getLogFile();
    $Neu   = !file_exists($Datei);

    file_put_contents($Datei, $Text, FILE_APPEND);

    if ( $Neu )  @chmod($Datei, 0666);
  }

  
  
  

  
  
  
  public function print2File($Data)
  {
    $this->writeLogFile(date("Y.m.d H:i:s") . " " . print_r($Data, true) . PHP_EOL);
  }

  
  
  
  
  
  
  
  
  
  
  
  
  const MARK_BOLD   = "**";
  const MARK_ITALIC = "__";

  public function markBold($Text)
  {
    $Text = (string) $Text;
    return trim($Text) === "" ? $Text : self::MARK_BOLD . $Text . self::MARK_BOLD;
  }

  public function markItalic($Text)
  {
    $Text = (string) $Text;
    return trim($Text) === "" ? $Text : self::MARK_ITALIC . $Text . self::MARK_ITALIC;
  }

  
  
  
  
  
  public function markValueBold($Text, $Label = "")
  {
    $Text  = (string) $Text;
    if ( trim($Text) === "" )  return $Text;

    $Label = trim((string) $Label);
    if ( $Label !== "" )
    {
      $Sep = $Label . ":";
      if ( strncasecmp($Text, $Sep, strlen($Sep)) === 0 )
      {
        $Rest = substr($Text, strlen($Sep));
        $Wert = ltrim($Rest);
        if ( trim($Wert) === "" )  return $Text;
        
        $Luft = substr($Rest, 0, strlen($Rest) - strlen($Wert));
        return $Sep . $Luft . $this->markBold($Wert);
      }
    }
    return $this->markBold($Text);
  }

  public function format2German($Str)
  {
    return date("d.m.Y", strtotime($Str));
  }

  public function daysLeft($strDate)
  {
    return date_diff(date_create($strDate), date_create())->format('%a');
  }

  public function print2Screen($Data)
  {
    if ( APPDEBUG["FKT"] ) $this->debug("FKT",__METHOD__);

    if ( php_sapi_name() != "cli" ) echo "<pre>";
    var_dump($Data);
    if ( php_sapi_name() != "cli" ) echo "</pre>";
  }

  public function debug($Type, $Data, $Full = false)
  {
    if ( $Full )
    {
      $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 10); 
      $reducedTrace = [];
      foreach ($trace as $step) {
        if ( isset($step['function']) && $step['function'] == "call" ) break;
        if ( isset($step['function']) && $step['function'] == "debug" ) continue;
        $reducedTrace[] = ($step['class']        ?? 'unknown')                  . " "
                        . (str_pad($step['line'] ?? '0', 5, " ", STR_PAD_LEFT)) . ": "
                        . ($step['function']     ?? 'unknown');
      }
      $this->writeLogFile(date("Y.m.d H:i:s") . " " . $Type . "_TRACE " . print_r(array_reverse($reducedTrace), true) . PHP_EOL);
    }

    if ( !is_string($Data)  )
    {
      $this->writeLogFile(date("Y.m.d H:i:s") . " " . $Type . " " . print_r($Data, true) . PHP_EOL);
    }
    else
    {
      $this->print2File($Type . " " . $Data);
    }
  }

  public function isWin()
  {
    return ((strtolower(substr(PHP_OS,0,3)) == "win") ? true : false);
  }

  public function getUID()
  {
    if ( APPDEBUG["FKT"] ) $this->debug("FKT",__METHOD__);

    
    $data = random_bytes(16);
    assert(strlen($data) == 16);

    
    $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
    
    $data[8] = chr(ord($data[8]) & 0x3f | 0x80);

    
    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
  }

  public function writeFile($Files, $Line)
  {
    if ( APPDEBUG["FKT"] ) $this->debug("FKT",__METHOD__);

    foreach ( $Files as $File )
    {
      file_put_contents($File, $Line . "\n", FILE_APPEND);
    }
  }

  public function removeFile($Files)
  {
    if ( APPDEBUG["FKT"] ) $this->debug("FKT",__METHOD__);

    foreach ( $Files as $File )
    {
      if (file_exists($File)) unlink($File);
    }
  }

  
  
  
  
  
  
  public function cmd2System($Command, $Quiet = false)
  {
    if ( APPDEBUG["FKT"] ) $this->debug("FKT",__METHOD__);

    if ( $Quiet )
    {
      exec($Command . " 2>&1", $Output, $RetVal);
      if ( $RetVal !== 0 && count($Output) )  echo implode(PHP_EOL, $Output) . PHP_EOL;
    }
    else
    {
      system($Command, $RetVal);
    }
    if ( APPDEBUG["SYS"] ) $this->debug("SYS",getcwd() . ": " . $Command);
    
    
    
    
    
    if ( $RetVal !== 0 )
    {
      throw new \RuntimeException("cmd2System failed (exit " . var_export($RetVal, true) . "): " . $Command);
    }
    return $RetVal;
  }


  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  private function scheiternGemeldet($Path, $Grund)
  {
    
    
    
    $Eltern = dirname($Path);
    while ( $Eltern !== "" && $Eltern !== "/" && $Eltern !== "." && !is_dir($Eltern) )
    {
      $Weiter = dirname($Eltern);
      if ( $Weiter === $Eltern )  break;
      $Eltern = $Weiter;
    }
    $Hinweis = is_dir($Eltern)
      ? ("uebergeordnet: " . $Eltern . " (" . substr(sprintf("%o", @fileperms($Eltern)), -4)
         . ", Eigentuemer " . (function_exists("posix_getpwuid")
             ? (posix_getpwuid(@fileowner($Eltern))["name"] ?? @fileowner($Eltern))
             : @fileowner($Eltern)) . ")")
      : ("kein vorhandener uebergeordneter Ordner gefunden: " . $Eltern);

    $Wer = function_exists("posix_geteuid") && function_exists("posix_getpwuid")
      ? (posix_getpwuid(posix_geteuid())["name"] ?? "?") : "?";

    $this->debug("LOG", "createPath: " . $Grund . " | als Benutzer " . $Wer . " | " . $Hinweis);
  }

  public function createPath($Path)
  {
    if ( !file_exists($Path) )
    {
      try {
        if ( !mkdir($Path, 0777, true) && !is_dir($Path) ) {
          throw new \RuntimeException("Verzeichnis konnte nicht erstellt werden: $Path");
        }
        else {
          if ( !$this->isWin() ) system('chmod o+w ' . escapeshellarg($Path));
        }
      } catch (\RuntimeException $e) {
        $this->scheiternGemeldet($Path, $e->getMessage());
      } catch (\ErrorException $e) {
        
        $this->scheiternGemeldet($Path, $e->getMessage());
      }
    }
  }

  public function getHash($Str)
  {
    return hash("sha256", trim((string) $Str));
  }

  public function verifyHash($Str, $Hash)
  {
    return ( hash("sha256", trim($Str)) == $Hash );
  }

  
  
  
  
  public function getUserHash($Str)
  {
    
    
    
    return hash_hmac("sha256", trim((string) $Str), $this->f3->get("application.pepper"));
  }

  
  
  


  































}

?>
