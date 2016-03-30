<?php

class Vzg_controller extends CI_Controller
{
  private $module;
  private $modules;
  
  public function __construct()
  {
    parent::__construct();

    // Load Session Library
    $this->load->library('session');

    // Load Cookie Library
    $this->load->helper('cookie');

    // Load URL Library
    $this->load->helper('url');

    if ( ! isset($_SESSION["marked"]) )	$_SESSION["marked"]	= array();
  }
  
  // ********************************************
  // ************* Config-Functions *************
  // ********************************************

  private function isSessionAlive()   /// Evtl. unnötig geworden !!!
  {
    // Check/Restart Session
    if ( !isset($_SESSION) || count($_SESSION) == 0 || !isset($_SESSION["config_system"]) || !isset($_SESSION["config_general"])|| !isset($_SESSION["config_discover"]) )
    {  
      echo "Bitte den Browser aktualisieren !";
      echo "<br />Please refresh browser to reactivate system !";
      return false;
    }
    return true;
  }

  private function isUserSessionAlive()
  {
    // Check/Restart Session
    if ( !isset($_SESSION) || !isset($_SESSION["login"]) || !isset($_SESSION["userlogin"])|| !isset($_SESSION["items"]) )
    {  
      echo "Bitte den Browser aktualisieren !";
      echo "<br />Please refresh browser to reactivate system !";
      return false;
    }
    return true;
  }

  private function load_check_system_config()
  {
    // Check, if system-config is available
    if ( ! file_exists(APPPATH.'config.ini'))
    {
      // Whoops, we don't have a page for that!
      show_404();
    }

    // Load system-config
    $config 		= parse_ini_file(APPPATH.'config.ini', true);

    // Check if configured system files are avialable
    foreach ( $config["systemcss"] as $value )
    {
      if ( ! file_exists(LIBRARYPATH. "/systemassets/" . $value) )
      {
        // Whoops, we don't have a page for that!
        show_404();
      }
    }
    foreach ( $config["systemjs"] as $value )
    {
      if ( ! file_exists(LIBRARYPATH. "/systemassets/" . $value) )
      {
        // Whoops, we don't have a page for that!
        show_404();
      }
    }
    $this->modules = explode(",",$config["systemcommon"]["modules"]);
    $_SESSION["config_system"] = $config;
  }
  
  private function load_check_general_config()
  {
    // Check, if general-config is available
    if ( ! file_exists(LIBRARYPATH.'general.ini'))
    {
      // Whoops, we don't have a page for that!
      show_404();
    }

    // Load module-config
    $config 		= parse_ini_file(LIBRARYPATH.'general.ini', true);

    // Check if configured module files are avialable
    foreach ( $config["css"] as $value )
    {
      if ( ! file_exists(LIBRARYPATH. "/assets/" . $value) )
      {
        // Whoops, we don't have a page for that!
        show_404();
      }
    }
    foreach ( $config["js"] as $value )
    {
      if ( ! file_exists(LIBRARYPATH. "/assets/" . $value) )
      {
        // Whoops, we don't have a page for that!
        show_404();
      }
    }
    $_SESSION["config_general"]	= $config;
  }

  private function load_check_module_config($modul)
  {
    // Check, if module is allowed
    if ( ! in_array( $modul, $this->modules ) )
    {
      // Whoops, we don't have a page for that!
      show_404();
    }
    // Check, if modul-config is available
    if ( ! file_exists(LIBRARYPATH.$modul.'.ini'))
    {
      // Whoops, we don't have a page for that!
      show_404();
    }

    // Load module-config
    $config 		= parse_ini_file(LIBRARYPATH.$modul.'.ini', true);

    // Check if configured module files are avialable
    foreach ( $config["css"] as $value )
    {
      if ( ! file_exists(LIBRARYPATH. "/assets/" . $value) )
      {
        // Whoops, we don't have a page for that!
        show_404();
      }
    }
    foreach ( $config["js"] as $value )
    {
      if ( ! file_exists(LIBRARYPATH. "/assets/" . $value) )
      {
        // Whoops, we don't have a page for that!
        show_404();
      }
    }
    $_SESSION["config_" . $modul]	= $config;
  }

  private function load_languages()
  {
    // Read database config & load library
    
    $Database	= (isset($_SESSION["config_general"]["database"]["type"]) && $_SESSION["config_general"]["database"]["type"] != "" ) ? $_SESSION["config_general"]["database"]["type"] : "mysql";
    $this->load->library('databases/'.$Database, "", "database");

    $_SESSION["language_ger"] = $this->database->system_language("german");
    $_SESSION["language_eng"] = $this->database->system_language("english");
  }

  // ********************************************
  // *********** Interface-Functions ************
  // ********************************************
  
  private function ensureInterface($Interfaces, $Modul = "")
  {
    if ( ! is_array($Interfaces) )
    {
      if ( $Interfaces != "" ) $Interfaces = array($Interfaces);
    }
    if ( ! isset($_SESSION["interfaces"]) ) $_SESSION["interfaces"]  = array();

    foreach ( $Interfaces as $Int )
    {
      switch ( strtolower($Int) )
      {
        case "config";
        {
          if ( isset($_SESSION["interfaces"]["config"]) && $_SESSION["interfaces"]["config"] == 1 && $Modul != "" && $Modul != $this->module ) continue;
          // Load configuration from ini-files
          $this->load_check_system_config();
          $this->load_check_general_config();
          $this->load_languages();
          $_SESSION["translation_ger"]    = array();
          $_SESSION["translation_eng"]    = array();
          
          if ( $Modul == "" && count($this->modules)>0 )
          {
            $Modul = $this->modules[0];
          }
          else
          {
            // Whoops, we don't have a page for that!
            show_404();
          }
          $this->module = $Modul;
          $this->load_check_module_config($Modul);

          if ( ! isset($_SESSION["internal"]["marc"]) )   $_SESSION["internal"]["marc"]		= (isset($_SESSION["config_system"]["systemcommon"]["devmode"]) && $_SESSION["config_system"]["systemcommon"]["devmode"] == "1" ) ? 1 : 0;
          if ( ! isset($_SESSION["internal"]["daia"]) )   $_SESSION["internal"]["daia"]		= (isset($_SESSION["config_system"]["systemcommon"]["devmode"]) && $_SESSION["config_system"]["systemcommon"]["devmode"] == "1" ) ? 1 : 0;
          if ( ! isset($_SESSION["internal"]["paia"]) )   $_SESSION["internal"]["paia"]		= (isset($_SESSION["config_system"]["systemcommon"]["devmode"]) && $_SESSION["config_system"]["systemcommon"]["devmode"] == "1" ) ? 1 : 0;

          if ( ! isset($_SESSION["language"]) )           $_SESSION["language"]				    = (isset($_SESSION["config_general"]["general"]["language"]) && $_SESSION["config_general"]["general"]["language"] != "" ) ? $_SESSION["config_general"]["general"]["language"] : "ger";


          if ( ! isset($_SESSION["filter"]["datapool"]) ) $_SESSION["filter"]["datapool"] = (isset($_SESSION["config_discover"]["discover"]["datapool"]) && $_SESSION["config_discover"]["discover"]["datapool"] != "" ) ? $_SESSION["config_discover"]["discover"]["datapool"] : "local";

          if ( ! isset($_SESSION["speech_ger"]) )         $_SESSION["speech_ger"]         = array();
          if ( ! isset($_SESSION["speech_eng"]) )         $_SESSION["speech_eng"]         = array();

          if ( ! isset($_SESSION["layout"]) )             $_SESSION["layout"] 		        = (isset($_SESSION["config_discover"]["discover"]["layout"]) && $_SESSION["config_discover"]["discover"]["layout"] != "" ) ? $_SESSION["config_discover"]["discover"]["layout"] : 3;

          // ILN mal vorhanden und mal nicht vorhanden
          if ( isset($_SESSION["config_general"]["general"]["iln"]) && $_SESSION["config_general"]["general"]["iln"] != "" )  $_SESSION["iln"] = $_SESSION["config_general"]["general"]["iln"];

          $_SESSION["interfaces"]["config"] = 1;
          break;
        }
        case "theme":
        {
          // Read theme config & load library
          $Theme	= (isset($_SESSION["config_general"]["theme"]["type"]) && $_SESSION["config_general"]["theme"]["type"] != "" ) ? $_SESSION["config_general"]["theme"]["type"] : "bootstrap";
          $this->load->library('themes/'.$Theme, "", "theme");
          $_SESSION["interfaces"]["theme"] = 1;
          break;
        }
    
        case "database":
        {
          // Read database config & load library
          $Database	= (isset($_SESSION["config_general"]["database"]["type"]) && $_SESSION["config_general"]["database"]["type"] != "" ) ? $_SESSION["config_general"]["database"]["type"] : "mysql";
          $this->load->library('databases/'.$Database, "", "database");
          $_SESSION["interfaces"]["database"] = 1;
          break;
        }
    
        case "index_system":
        {
          // Read system type & load library and record format
          $IS	= (isset($_SESSION["config_general"]["index_system"]["type"]) && $_SESSION["config_general"]["index_system"]["type"] != "" ) ? $_SESSION["config_general"]["index_system"]["type"] : "solr";
          $this->load->library('index_systems/'.$IS, "", "index_system");
          $_SESSION["interfaces"]["index_system"] = 1;
          break;
        }
    
        case "record_format":
        {
          // Read record format & load library
          $RF	= (isset($_SESSION["config_general"]["record_format"]["type"]) && $_SESSION["config_general"]["record_format"]["type"] != "" ) ? $_SESSION["config_general"]["record_format"]["type"] : "marc21";
          $this->load->library('record_formats/'.$RF, "", "record_format");
          $_SESSION["interfaces"]["record_format"] = 1;
          break;
        }
    
        case "lbs":
        {
          // Read LBS config & load library - if it is available
          if (isset($_SESSION["config_general"]["lbs"]["available"]) && $_SESSION["config_general"]["lbs"]["available"] != "1" )
          {
            $_SESSION["interfaces"]["lbs"] = 0;
          }
          else
          {
            $LBS	= (isset($_SESSION["config_general"]["lbs"]["type"]) && $_SESSION["config_general"]["lbs"]["type"] != "" ) ? $_SESSION["config_general"]["lbs"]["type"] : "paia_daia";
            $this->load->library('lb_systems/'.$LBS, "", "lbs");
            $_SESSION["interfaces"]["lbs"] = 1;
          }
          break;
        }

        case "export":
        {
          // Read export config & load library
          $export	= (isset($_SESSION["config_general"]["export"]["type"]) && $_SESSION["config_general"]["export"]["type"] != "" ) ? $_SESSION["config_general"]["export"]["type"] : "standard";
          $this->load->library('exports/'.$export, "", "export");
          $_SESSION["interfaces"]["export"] = 1;
          break;
        }
      }
    }

    //$Status = true;
    //foreach ( $_SESSION["interfaces"] as $Int )
    //{
    //  if ( $Status == true && $_SESSION["interfaces"][$Int] == 0 ) $Status = false;
    //}
    //$this->printArray2File($Interfaces);
    //$this->printArray2File($_SESSION["interfaces"]);
    //return $Status;
  }

  // ********************************************
  // ************** Tool-Functions **************
  // ********************************************
  
  public function printArray2File($Array)
  {
    file_put_contents('Test_' . microtime() . '.txt', print_r($Array, true));
  }

  public function printArray2Screen($Array)
  {
    echo "<pre>";
    var_dump($Array);
    echo "</pre>";
  }

  public function appendFile($File, $Str)
  {
    if ( is_array($Str) )
    {
      $Str = implode(",",$Str);
    }
    file_put_contents($File, date("Ymd His") . " " . $Str . "\n", FILE_APPEND);
  }

  public function formatArray2Table($Level,$Array)
  {
    $Output = "<table>";
    foreach ( $Array as $Field => $Value )
    {
      $Output .= "<tr><td>" . $Field . "</td><td>";
      if ( ! is_array($Value) )
      {
        $Output .= $Value;
      }
      else
      {
        $Output .= $this->formatArray2Table($Level++,$Value);
      }
      $Output .= "</td></tr>";
    }
    $Output .= "</table>";
    return $Output;
  }
  
  public function date2german($Date) 
  {
    $Tmp = strtotime($Date);
    return  date("d.m.Y",$Tmp);
  }
  
  /**
   * Return $val if it is set, $default otherwise.
   *
   * This shortcut function avoids the repetition of $val in cases like
   *
   * isset($a[$module][$user][$i]) ? $a[$module][$user][$i] : ''
   *
   * @param mixed $val	variable to check whether it is set
   * @param mixed $default	value to return if $val is not set, defaults to null
   * @return mixed	$val or $default
   */
  public function isset_or(&$val, $default = null)
  {
    return isset($val) ? $val : $default;
  }
  
  // ********************************************
  // ******** Control-Functions (AJAX) **********
  // ********************************************

  public function sessclear()
  {
    // Ajax Method => No view will be loaded

    // Receive params
    
    // Check params

    // Save some parameters for next session
    $Container = array();
    foreach ( $_SESSION as $key => $value )
    {
      if ( in_array($key, array('iln','layout') ) || substr($key, 0, 7) == "config_" || substr($key, 0, 8) == "language" || substr($key, 0, 11) == "translation" || substr($key, 0, 6) == "speech")
      {
        $Container[$key]	= $value;
      }
    }
    $tmp = (isset($_SESSION["filter"]["datapool"])) ? $_SESSION["filter"]["datapool"] : "";

    // Clear session
    session_unset();
    // session_start();
    $_SESSION += $Container;
    $_SESSION["filter"]["datapool"] = $tmp;

    echo 0;
  }

  public function config()
  {
    // Ajax Method => No view will be loaded, just data is returned

    // Check params

    // Ensure required interfaces
    $this->ensureInterface(array("config"));

    if ( !isset($_SESSION["language"]) ) $_SESSION["language"] = (isset($_SESSION["config_general"]["general"]["language"]) 	 && $_SESSION["config_general"]["general"]["language"] != "" ) 	 ? $_SESSION["config_general"]["general"]["language"]		: "ger";
    if ( !isset($_SESSION["layout"]) )   $_SESSION["layout"]   = (isset($_SESSION["config_discover"]["discover"]["layout"]) 	 && $_SESSION["config_discover"]["discover"]["layout"] != "" ) 	 ? $_SESSION["config_discover"]["discover"]["layout"]		: "3";
    if ( !isset($_SESSION["filter"]["datapool"]) ) $_SESSION["filter"]["datapool"] = (isset($_SESSION["config_discover"]["discover"]["datapool"]) && $_SESSION["config_discover"]["discover"]["datapool"] != "" ) ? $_SESSION["config_discover"]["discover"]["datapool"] : "local";

    // Collect some settings for javascript-clients
    $container = array
    (
      "devmode"				  => (isset($_SESSION["config_system"]["systemcommon"]["devmode"])				 && $_SESSION["config_system"]["systemcommon"]["devmode"] != "" )  				? $_SESSION["config_system"]["systemcommon"]["devmode"]				  : "0",
      "devuser"					=> (isset($_SESSION["config_system"]["systemcommon"]["devuser"])     		 && $_SESSION["config_system"]["systemcommon"]["devuser"] != "" )					? $_SESSION["config_system"]["systemcommon"]["devuser"]					: "",
      "devpassword"			=> (isset($_SESSION["config_system"]["systemcommon"]["devpassword"]) 		 && $_SESSION["config_system"]["systemcommon"]["devpassword"] != "" )			? $_SESSION["config_system"]["systemcommon"]["devpassword"]			: "",
      "devusername"			=> (isset($_SESSION["config_system"]["systemcommon"]["devusername"]) 		 && $_SESSION["config_system"]["systemcommon"]["devusername"] != "" )			? $_SESSION["config_system"]["systemcommon"]["devusername"]			: "",
      "devusermail"			=> (isset($_SESSION["config_system"]["systemcommon"]["devusermail"]) 		 && $_SESSION["config_system"]["systemcommon"]["devusermail"] != "" )			? $_SESSION["config_system"]["systemcommon"]["devusermail"]			: "",
      "devusermailtext" => (isset($_SESSION["config_system"]["systemcommon"]["devusermailtext"]) && $_SESSION["config_system"]["systemcommon"]["devusermailtext"] != "" )	? $_SESSION["config_system"]["systemcommon"]["devusermailtext"]	: "",
      "librarytitle"		=> (isset($_SESSION["config_general"]["general"]["title"])        			 && $_SESSION["config_general"]["general"]["title"] != "" )        				? $_SESSION["config_general"]["general"]["title"]								: "",
      "softwarename"		=> (isset($_SESSION["config_general"]["general"]["softwarename"]) 			 && $_SESSION["config_general"]["general"]["softwarename"] != "" ) 				? $_SESSION["config_general"]["general"]["softwarename"]				: "GBV Discovery",
      "language"        => $_SESSION["language"],
      "layout"          => $_SESSION["layout"],
      "datapool"        => $_SESSION["filter"]["datapool"]
    );

    // Convert all values to utf8
    foreach ( $container as $key => &$value )
    {
      $value = utf8_encode($value);
    }

    // Return data in jsonformat
    echo json_encode($container);
  }
  
  public function mailto()
  {
    // Ajax Method => No view will be loaded, just data is returned

    // Receive params
    $ppnlist		  = (array)json_decode($this->input->post('ppnlist'));
    $fullbodylist	= (array)json_decode($this->input->post('fullbody'));
    $mailfrom		  = $this->input->post('mailfrom');
    $mailto			  = $this->input->post('mailto');
    $username		  = $this->input->post('username');
    $msg				  = $this->input->post('msg');

    // Check params
    if ( $username == "" || $mailfrom == "" || $mailto == "" ) return(-2);
    if (! isset($_SESSION["config_general"]["general"]["mailfrom"]) || $_SESSION["config_general"]["general"]["mailfrom"] == "" ) return (-3);
    if ( count($ppnlist) == 0 || count($fullbodylist) == 0 ) return(-2);
    
    // Load Mail Library
    $this->load->library('email');

    // Mail Config.
    $config['charset'] 	= 'utf-8';
    $config['mailtype'] = 'html';
    $this->email->initialize($config);

    // Mail Adresses
    $FromName = (isset($_SESSION["config_general"]["general"]["softwarename"])       &&  isset($_SESSION["config_general"]["general"]["title"])) 
         ? utf8_encode($_SESSION["config_general"]["general"]["softwarename"] . "@" . $_SESSION["config_general"]["general"]["title"]) : "";
    $this->email->from($_SESSION["config_general"]["general"]["mailfrom"], $FromName);
    $this->email->to($mailto);
    $this->email->reply_to($mailfrom);

    // Mail subject 
    $this->email->subject(utf8_encode('Empfehlung von ' . $username . ' für Sie!') );

    // Mail body
    $message = "";
    foreach ( $fullbodylist as $ppn  => $fullbody)
    {
      if ( ! in_array($ppn, $ppnlist))  continue;
      
      // Remove Links from message body
      //$fullbody  = $fullbodylist[$ppn];
      $fullbody  = preg_replace("/<a[^>]+\>/i", " ", $fullbody);
      $fullbody  = preg_replace("/<\/a>/i", " ", $fullbody);

      $fullbody .= utf8_encode("<a style='color:blue;background-color:white;text-decoration:none;' href='" 
                         . base_url($ppn."/id") . "'><b>Bitte klicken Sie hier, um diese Empfehlung zu öffnen</b></a>");
      $message  .= "<hr>" . $fullbody;
    }

    $this->email->message(json_decode($username) . " (<a href='mailto:" . $mailfrom . "'>" . $mailfrom 
                        . "</a>) hat diese Empfehlung(en) f&uuml;r Sie:" . "<br /><br />"
                         . json_decode($msg) . "<br /><br />" . $message . "<hr>");

    // Send it away...
    $this->email->send();

    // Return data
    echo "0";
  }

  public function mailorderto()
  {
    // Ajax Method => No view will be loaded, just data is returned

    // Receive params
    $ppn				= $this->input->post('ppn');
    $mailfrom		= $this->input->post('mailfrom');
    $mailto			= $this->input->post('mailto');
    $fullbody		= $this->input->post('fullbody');
    $exemplar		= (array)json_decode($this->input->post('exemplar'));
    $userinput  = (array)json_decode($this->input->post('userinput'));

    // Check params
    if ( $ppn == "" || $mailfrom == "" || $mailto == "" || $fullbody == "" ) return(-2);
    if ( ! is_array($exemplar) || count($exemplar) == 0 ) return(-2);
    if ( ! $this->isUserSessionAlive() ) return;

    //$this->printArray2File($exemplar);
    //echo json_encode($exemplar);
    //return;

    // Ensure required interfaces
    $this->ensureInterface(array("config","database"));
   
    // Load Mail Library
    $this->load->library('email');

    // Mail Config.
    $config['charset'] 	= 'utf-8';
    $config['mailtype'] = 'html';
    $this->email->initialize($config);

    // Mail Adresses
    $FromName = (isset($_SESSION["config_general"]["general"]["softwarename"])       &&  isset($_SESSION["config_general"]["general"]["title"])) 
         ? utf8_encode($_SESSION["config_general"]["general"]["softwarename"] . "@" . $_SESSION["config_general"]["general"]["title"]) : "";
    $this->email->from($mailfrom);
    $this->email->reply_to($mailfrom);

    if ( strtolower(MODE) == "production" )
    {
      $this->email->to($mailto);
    }
    else
    {
      $this->email->to("karim@gbv.de");
    }
    
    // Username
    $username = $_SESSION["login"]["firstname"]. " " . $_SESSION["login"]["lastname"];
        
    // Mail subject
    $this->email->subject('Magazinbestellung von ' . $username );

    // Remove Links from message body
    $fullbody=preg_replace("/<a[^>]+\>/i", " ", $fullbody);
    $fullbody=preg_replace("/<\/a>/i", " ", $fullbody);

    // Body user part
    $Mess = "<h3>Benutzer</h3>"; 
    $Mess .= "<table>";
    foreach ( $_SESSION["login"] as $key => $value )
    {
      if ( $value == "" )  continue;
      $Mess .= "<tr><td>" . $this->database->code2text($key) . "</td><td>" . $value . "</td></tr>";
    }
    $Mess .= "</table>";

    // Body sample part
    $Mess .= "<h3>Exemplar</h3>"; 
    $Mess .= "<table>";
    foreach ( $exemplar as $key => $value )
    {
      if ( in_array($key, array("action","typ")) )  continue;
      if ( substr($key,0,4) == "data" ) continue;
      $Mess .= "<tr><td>" . $value . "</td></tr>";
    }
    $Mess .= "</table>";

    // Userinput  part
    if ( count($userinput) > 0)
    {
      $Mess .= "<h3>Benutzereingaben</h3>"; 
      $Mess .= "<table>";
      foreach ( $userinput as $key => $value )
      {
        $Mess .= "<tr><td>" .  $this->database->code2text($key) . "</td><td>" . $value . "</td></tr>";
      }
      $Mess .= "</table>";
    }


    // Body media part
    $Mess .= "<h3>Medium</h3>"; 
    $Mess .= "PPN: " . $ppn;
    $Mess .= "<table border=1>" . json_decode($fullbody) . "</table>";
    
    // Body link part
    $Mess .= "<h3>Direkter Link</h3>"; 
    $Mess .= utf8_encode("<a style='color:blue;background-color:white;text-decoration:none;' href='" 
          . base_url($ppn."/id") . "'><b>Bitte klicken Sie hier, um dieses bestellten Medium einzusehen</b></a>");
    
    $this->email->message($Mess);

    // Send it away...
    $this->email->send();

    // Return data
    echo "0";
  }

  public function command()
  {
    // Ajax Method => No view will be loaded, just data is returned

    // Receive params
    $cmd		= strtolower($this->input->post('cmd'));

    // Check params
    if ( $cmd == "" ) return(-2);

    // Ensure required interfaces
    $this->ensureInterface(array("config"));

    // Parse command
    $cmd = explode(":",$cmd);

    switch ( $cmd[0] )
    {
      case "marc":
      {
        if ( ! isset($cmd[1] ) )
        {
          $_SESSION["internal"]["marc"]	= ( !isset($_SESSION["internal"]["marc"]) || $_SESSION["internal"]["marc"] == "0" ) ? "1" : "0";
        }
        else
        {
          $_SESSION["internal"]["marc"]	= ($cmd[1]== "off") ? "0" : "1";
        }
        break;
      }
      case "daia":
      {
        if ( ! isset($cmd[1] ) )
        {
          $_SESSION["internal"]["daia"]	= ( !isset($_SESSION["internal"]["daia"]) || $_SESSION["internal"]["daia"] == "0" ) ? "1" : "0";
        }
        else
        {
          $_SESSION["internal"]["daia"]	= ($cmd[1]== "off") ? "0" : "1";
        }
        break;
      }
      case "paia":
      {
        if ( ! isset($cmd[1] ) )
        {
          $_SESSION["internal"]["paia"]	= ( !isset($_SESSION["internal"]["paia"]) || $_SESSION["internal"]["paia"] == "0" ) ? "1" : "0";
        }
        else
        {
          $_SESSION["internal"]["paia"]	= ($cmd[1]== "off") ? "0" : "1";
        }
        break;
      }
      case "dev":
      {
        if ( ! isset($cmd[1] ) )
        {
          $_SESSION["internal"]["marc"]	= ( !isset($_SESSION["internal"]["marc"]) || $_SESSION["internal"]["marc"] == "0" ) ? "1" : "0";
          $_SESSION["internal"]["daia"]	= ( !isset($_SESSION["internal"]["daia"]) || $_SESSION["internal"]["daia"] == "0" ) ? "1" : "0";
          $_SESSION["internal"]["paia"]	= ( !isset($_SESSION["internal"]["paia"]) || $_SESSION["internal"]["daia"] == "0" ) ? "1" : "0";
        }
        else
        {
          $_SESSION["internal"]["marc"]	= ($cmd[1]== "off") ? "0" : "1";
          $_SESSION["internal"]["daia"]	= ($cmd[1]== "off") ? "0" : "1";
          $_SESSION["internal"]["paia"]	= ($cmd[1]== "off") ? "0" : "1";
        }
        break;
      }
      default:
      {
        // Change INI-Setting dynamically
        if ( count($cmd) == 4 )
        {
          if ( isset($_SESSION["config_" . $cmd[0]][$cmd[1]][$cmd[2]]) )
          {
            $_SESSION["config_" . $cmd[0]][$cmd[1]][$cmd[2]] = $cmd[3];
          }
        }
      }
    }

    echo "0";
  }
  
  public function language()
  {
    // Ajax Method => No view will be loaded, just data is returned

    // Receive params
    $language		= $this->input->post('language');

    // Check params
    if ( $language == "" ) return(-2);

    // Ensure required interfaces
    $this->ensureInterface(array("config"));

    // Set Session Language
    $_SESSION["language"] = $language;

    // Return data in jsonformat
    echo json_encode($_SESSION['language_'.$_SESSION["language"]]);
  }

  public function exportlink()
  {
    // Ajax Method => No view will be loaded, just data is returned

    // Receive params
    $ppnlist = (array)json_decode($this->input->post('ppnlist'));
    $format	 = $this->input->post('format');

    // Check params
    if ( $format == "" ) return(-2);
    if ( count($ppnlist) == 0 ) return(-2);

    // Ensure required interfaces
    $this->ensureInterface(array("config","export"));

    // Call export & Return data in jsonformat
    $container = array();
    foreach ($ppnlist as $ppn ) 
    {
      $container[$ppn] = $this->export->exportlink($_SESSION["data"]["results"][$ppn] + $_SESSION["data"]["pretty"][$ppn], $format);
    }
    echo json_encode($container);
  }
  
  public function internal_exportlink($ppn,$format)
  {
    // Check params
    if ( $ppn == "" || $format == "" ) return(-2);

    // Ensure required interfaces
    $this->ensureInterface(array("config","export"));

    // Call export & Return data in jsonformat
    return $this->export->exportlink($_SESSION["data"]["results"][$ppn] + $_SESSION["data"]["pretty"][$ppn], $format);
  }

  public function internal_exportimage($ppn)
  {
    // Check params
    if ( $ppn == "" ) return(-2);

    // Ensure required interfaces
    $this->ensureInterface(array("config","export"));

    // Call export & Return data in jsonformat
    return $this->export->exportlinkimage($_SESSION["data"]["results"][$ppn] + $_SESSION["data"]["pretty"][$ppn]);
  }

  public function exportfile($ppn, $format)
  {
    // Check params
    if ( $ppn == "" || $format == "" ) return(-2);

    // Ensure required interfaces
    $this->ensureInterface(array("config","export"));

    // Load helper
    $this->load->helper('download');

    // Call export
    $data = $this->export->exportfile($_SESSION["data"]["results"][$ppn] + $_SESSION["data"]["pretty"][$ppn], $format);
    $name = 'mytext.txt';
    force_download($name, $data);
  }
  
  public function getwords()
  {
    // Ajax Method => No view will be loaded, just data is returned

    // Receive params
    $query		= $this->input->get('query');

    // Check params
    if ( $query == "" ) return;
    
    // Ensure required interfaces
    $this->ensureInterface(array("config","database"));

    // Invoke database driver
    echo json_encode($this->database->get_words($query));
  }

  public function storeusersearch()
  {
    // Ajax Method => No view will be loaded, just data is returned

    // Receive params
    $search		= $this->input->post('search');
    $user 		= $this->input->post('user');
    $facets   = $this->input->post('facets');

    // Check params
    if ( $search == "" || $user == "" ) return;
    
    // Ensure required interfaces
    $this->ensureInterface(array("config","database"));

    // Invoke database driver
    echo json_encode($this->database->store_user_search($search, $user, $facets));
  }

    
  // ********************************************
  // *********** LBS-Functions (AJAX) ***********
  // ********************************************

  public function login()
  {
    // Ajax Method => No view will be loaded, just data is returned

    // Receive params  
    $user	= $this->input->post('user');
    $pw		= $this->input->post('pw');

    // Check params
    if ( $user == "" || $pw == "" ) return(-2);

    // Ensure required interfaces
    $this->ensureInterface(array("config","lbs"));

    // Login lbs & echo
    echo json_encode($this->lbs->login($user, $pw));
  }

  public function logout()
  {
    // Ajax Method => No view will be loaded, just data is returned

    // Receive params

    // Check params

    // Ensure required interfaces
    $this->ensureInterface(array("config","lbs"));

    if ( isset($_SESSION["userlogin"]) )
    {
      // Logout lbs & echo
      echo  json_encode($this->lbs->logout());
    }
    return (0);
  }

  public function GetLBS($PPN)
  {
    // Receive params
    
    // Check params

    // Ensure required interfaces
    $this->ensureInterface(array("config","lbs"));

    // Call LBS
    return $this->lbs->daia($PPN);
  }

  public function request()
  {
    // Receive params
    $uri	= $this->input->post('uri');

    // Check params
    if ( $uri == "" )	return(-2);

    // Ensure required interfaces
    $this->ensureInterface(array("config","lbs"));

    // Call LBS
    echo json_encode($this->lbs->request($uri));
  }  
  
  public function cancel()
  {
    // Ajax Method => No view will be loaded, just data is returned

    // Receive params
    $uri	= $this->input->post('uri');

    // Check params
    if ( $uri == "" )	return(-2);

    // Ensure required interfaces
    $this->ensureInterface(array("config","lbs"));

    // Call LBS
    echo json_encode($this->lbs->cancel($uri));
  }  

  public function renew()
  {
    // Ajax Method => No view will be loaded, just data is returned

    // Receive params
    $uri	= $this->input->post('uri');

    // Check params
    if ( $uri == "" )	return(-2);

    // Ensure required interfaces
    $this->ensureInterface(array("config","lbs"));

    // Call LBS
    echo json_encode($this->lbs->renew($uri));
  }  

  // ********************************************
  // ********* Main-Functions (AJAX) ************
  // ********************************************

  private function dosearch($search, $package, $facets)
  {
    // Ensure required interfaces
    $this->ensureInterface(array("config","database","index_system","record_format"));

    // Store session data
    $_SESSION["data"]["search"]	= $search;

    // Invoke database, store search 
    $this->database->log_search($search);

    // Invoke index system
    $container = $this->index_system->main(array('search'=>$search,'package'=>$package,'facets'=>$facets));

    // Store session data
    $_SESSION["data"]["index_system"][$package]	= $container;
    
    // Invoke record format driver
    $container = $this->record_format->convert($container);

    // Store session data
    $_SESSION["data"]["record_format"]	= $container;

    // Merge und store loaded and converted data
    if ( !isset($_SESSION['data']['results']) ) $_SESSION['data']['results']  = array();

    if ($package != 1 )
    {
      $_SESSION['data']['results']	+= $container["results"];
    }
    else
    {
      $_SESSION['data']['results']	= $container["results"];
    }

    // Return data
    return ($container);    
  }

  // Search media data (invoked by user)
  public function search()
  {
    // Ajax Method => No view will be loaded, just data is returned

    // Receive params
    $search			= $this->input->post('search');
    $package		= $this->input->post('package');
    //$typ		    = strtolower($this->input->post('typ'));
    $facets 		= (array) json_decode($this->input->post('facets'));

    // Check params
    if ( $search == "" ) return(-2);
    if ( $package == "" || $package == "0" ) return(-2);
    //if ( $typ == "" ) $typ = "normal";

    // Ensure required interfaces
    $this->ensureInterface(array("config","theme"));

    // Set facet
    $_SESSION["filter"] = $facets;
    
    // Invoke search engine
    $container = $this->dosearch($search,$package,true);

    // Transfer records to file
    //$this->printArray2File($container["results"]);

    // Create PPN list
    $container["ppnlist"] = array_keys($container["results"]);

    // Invoke theme format driver
    $container = $this->theme->preview($container, array('collgsize' => $_SESSION['layout']));

    // Invoke database, store search phrases 
    if ( isset($container["words"]) )
    {
      if ( trim($container["words"]) != "" )
      {
        $this->database->store_words($container["words"]);
      }
      unset($container["words"]);
    }
   
    // Store session data
    $_SESSION["data"]["theme"]	= $container;
    
    // Transfer records to file
    // $this->printArray2File($container);      

    echo json_encode($container);
  }

  // Search media data (invoked by system without optical stuff)
  public function internal_search($search)
  {
    // Check params
    if ( $search == "" ) return(-2);

    $container = $this->dosearch($search,"0",false);
    
    return ($container);
  }

  // ********************************************
  // ********* Theme-Functions (AJAX) ***********
  // ********************************************

  /**
   * Get layout container for number of columns
   * Input variable "layout": number of columns (1-4)
   * Echos layout container 
   */
  public function layout()
  {
    // Ajax Method => No view will be loaded, just data is returned

    // Receive params
    $layout		= $this->input->post('layout');

    // Check params
    if ( $layout == "" ) return(-2);

    // Ensure required interfaces
    $this->ensureInterface(array("config", "theme"));

    // Set Session Layout
    $_SESSION["layout"] = $layout;
    
    $container = array
    (
      "start" => 0,
      "results" => $_SESSION['data']['results']
    );

    // Invoke theme format driver
    $container = $this->theme->preview($container, array('collgsize' => $layout,'facets' => false));

    // Echos container in jsonformat
    echo json_encode($container);
  }

  // Show media data large 
  public function fullview($PPN,$dlgid)
  {
    // Ajax Method => No view will be loaded, just data is returned

    // Receive params

    // Check params
    if ( $PPN == "" ) return(-2);
    if ( $dlgid == "" ) return(-2);

    // Ensure required interfaces
    $this->ensureInterface(array("config","lbs","theme"));

    // Ensure required ppn data
    if ( !isset($_SESSION["data"])  ||  ! array_key_exists($PPN, $_SESSION["data"]["results"]) )
    {
       $this->internal_search("id:" . $PPN);
    }

    // Invoke theme format driver
    echo $this->theme->fullview(array('ppn'=>$PPN,'dlgid'=>$dlgid));
  }

  // Show user data large 
  public function userview($Action)
  {
    // Ajax Method => No view will be loaded, just data is returned

    // Receive params

    // Check params
    if ( $Action == "" )  return;
    if ( ! $this->isUserSessionAlive() ) return;

    // Ensure required interfaces
    $this->ensureInterface(array("config","lbs","theme","database"));

    // Refresh data
    $this->lbs->userdata();

    // Load Stored search
    $_SESSION['searches'] = $this->database->load_user_search($_SESSION["userlogin"]);

    // Display view
    echo $this->theme->userview(array('action'=>$Action));
  }
  
  // Show user data large 
  public function mailorderview($PPN,$EPN)
  {
    // Ajax Method => No view will be loaded, just data is returned

    // Receive params

    // Check params
    if ( $PPN == "" ||$EPN == "")  return;
    //if ( ! $this->isSessionAlive() ) return;

    // Ensure required interfaces
    $this->ensureInterface(array("config","theme"));

    // Display view
    echo $this->theme->mailorderview(array('ppn'=>$PPN,'exemplar'=>$_SESSION['exemplar'][$PPN][$EPN]));
  }  

  // ********************************************
  // ******* Direct-Functions (NO AJAX) *********
  // ********************************************

  public function nojavascript()
  {
    // Receive params
    
    // Check params
    
    // Ensure required interfaces

    // Set params

    // Show Frontpage
    $this->load->view(DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . 'nojavascript');
  }

  public function view($modul = "", $search="", $facets="")
  {
    // Receive params
    
    // Check params
    
    // Ensure required interfaces
    $this->ensureInterface("config",$modul);

    // Set params
    $param["modul"] = $this->module;
    $param["initsearch"] = $search;
    $param["initfacets"] = $facets;

    // Show Frontpage
    if ( isset($_SESSION["config_general"]["general"]["frontpage"]) && $_SESSION["config_general"]["general"]["frontpage"] == 1 
       && $search == "" && $facets == "" )
    {
      $param["front"] = true;

      // Load Header
      $this->load->view(DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . 'header',$param);

      // Load Frontpage
      $this->load->view(DIRECTORY_SEPARATOR . 'blocks' . DIRECTORY_SEPARATOR . 'front',$param);

      // Load Footer
      $this->load->view(DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . 'footer', $param);
    }
    else
    {  
      $param["front"] = false;

      // Load Header
      $this->load->view(DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . 'header',$param);
  
      // Main area loading & printing blocks
      $blocks = explode(",",$_SESSION['config_'. $this->module][$this->module]["blocks"]);
      foreach ( $blocks as $block )
      {
        // Check, if block is available
        if ( ! file_exists(KERNELBLOCKS . DIRECTORY_SEPARATOR . $block.'.php'))
        {
          // Whoops, we don't have a page for that!
          show_404();
        }
        $param["blockpath"] = KERNELBLOCKS . DIRECTORY_SEPARATOR . $block.'.php';
        $this->load->view(DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . 'block', $param);
      }
  
      // Load Footer
      $this->load->view(DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . 'footer', $param);
    }
  }

  public function directopen($search = "*", $group = "", $facets = "")
  {
    // Receive params
    
    // Check params

    // Convert characters
    $search = ( urldecode($search) == "{star}" ) ? "*" : urldecode($search);
    if ( urldecode($group) != "{}" && urldecode($group) != "" ) $search = urldecode($group) . ":" . $search;

    // Call main method with parameters
    $this->view("",$search,$facets);    
  }  

}

?>