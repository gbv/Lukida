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

        // Load URL Library
    $this->load->helper('url');

    // Load General Library
    $this->load->library('general');

    if ( ! isset($_SESSION["marked"]) )	$_SESSION["marked"]	= array();
  }
  
  // ********************************************
  // ************* Config-Functions *************
  // ********************************************

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
      if ( ! file_exists(LIBRARYPATH. "systemassets/" . $value) )
      {
        // Whoops, we don't have a page for that!
        show_404();
      }
    }
    foreach ( $config["systemjs"] as $value )
    {
      if ( ! file_exists(LIBRARYPATH. "systemassets/" . $value) )
      {
        // Whoops, we don't have a page for that!
        show_404();
      }
    }
    $this->modules = explode(",",$config["systemcommon"]["modules"]);
    $_SESSION["config_system"] = $config;

    // Check, if addition configuration is available
    if ( file_exists(LIBRARYCODE . 'system.php') )
    {
      include(LIBRARYCODE . 'system.php');
    }
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
      if ( ! file_exists(LIBRARYPATH. "assets/" . $value) )
      {
        // Whoops, we don't have a page for that!
        show_404();
      }
    }
    foreach ( $config["js"] as $value )
    {
      if ( ! file_exists(LIBRARYPATH. "assets/" . $value) )
      {
        // Whoops, we don't have a page for that!
        show_404();
      }
    }
    $_SESSION["config_general"]	= $config;

    // Check, if addition configuration is available
    if ( file_exists(LIBRARYCODE. 'general.php') )
    {
      include(LIBRARYCODE . 'general.php');
    }
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
      if ( ! file_exists(LIBRARYPATH. "assets/" . $value) )
      {
        // Whoops, we don't have a page for that!
        show_404();
      }
    }
    foreach ( $config["js"] as $value )
    {
      if ( ! file_exists(LIBRARYPATH. "assets/" . $value) )
      {
        // Whoops, we don't have a page for that!
        show_404();
      }
    }
    $_SESSION["config_" . $modul]	= $config;

    // Check, if addition configuration is available
    if ( file_exists(LIBRARYCODE . $modul.'.php') )
    {
      include(LIBRARYCODE . $modul.'.php');
    }
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
  
  private function ensureInterface($Interfaces)
  {
    if ( ! is_array($Interfaces) )
    {
      if ( $Interfaces != "" ) $Interfaces = array($Interfaces);
    }
    if ( ! isset($_SESSION["interfaces"]) || ! isset($this->modules) ) $_SESSION["interfaces"]  = array();

    // Remove already checked interfaces from desired interfaces
    $Interfaces = array_diff($Interfaces, array_keys($_SESSION["interfaces"]));

    // Loop over open interfaces
    foreach ( $Interfaces as $Int )
    {
      switch ( strtolower($Int) )
      {
        case "config";
        {
          // Avoid multiple Configs
          if ( isset($_SESSION["interfaces"]["config"]) && $_SESSION["interfaces"]["config"] == 1 ) break;

          // Load configuration from ini-files
          $this->load_check_system_config();
          $this->load_check_general_config();

          // ILN mal vorhanden und mal nicht vorhanden
          if ( isset($_SESSION["config_general"]["general"]["iln"]) && $_SESSION["config_general"]["general"]["iln"] != "" )  $_SESSION["iln"] = $_SESSION["config_general"]["general"]["iln"];

          // Also load ILN dependant translations
          $this->load_languages(); // Wichtig für ersten Seitenaufbau
          if (!isset($_SESSION["translation_ger"]))  $_SESSION["translation_ger"] = array();
          if (!isset($_SESSION["translation_eng"]))  $_SESSION["translation_eng"] = array();
          
          if ( ! isset($_SESSION["language"]) )           $_SESSION["language"]           = (isset($_SESSION["config_general"]["general"]["language"]) && $_SESSION["config_general"]["general"]["language"] != "" ) ? $_SESSION["config_general"]["general"]["language"] : "ger";

          if ( ! isset($_SESSION["speech_ger"]) )         $_SESSION["speech_ger"]         = array();
          if ( ! isset($_SESSION["speech_eng"]) )         $_SESSION["speech_eng"]         = array();

          $_SESSION["interfaces"]["config"] = 1;
          break;
        }
        case "discover":
        {
          // Avoid multiple Configs
          if ( isset($_SESSION["interfaces"]["discover"]) && $_SESSION["interfaces"]["discover"] == 1 ) break;

          $this->load_check_module_config("discover");

          if ( ! isset($_SESSION["internal"]["marc"]) )   $_SESSION["internal"]["marc"]   = (isset($_SESSION["config_discover"]["dev"]["devmode"]) && $_SESSION["config_discover"]["dev"]["devmode"] == "1" ) ? 1 : 0;
          if ( ! isset($_SESSION["internal"]["daia"]) )   $_SESSION["internal"]["daia"]   = (isset($_SESSION["config_discover"]["dev"]["devmode"]) && $_SESSION["config_discover"]["dev"]["devmode"] == "1" ) ? 1 : 0;
          if ( ! isset($_SESSION["internal"]["paia"]) )   $_SESSION["internal"]["paia"]   = (isset($_SESSION["config_discover"]["dev"]["devmode"]) && $_SESSION["config_discover"]["dev"]["devmode"] == "1" ) ? 1 : 0;

          if ( ! isset($_SESSION["filter"]["datapool"]) ) $_SESSION["filter"]["datapool"] = (isset($_SESSION["config_discover"]["discover"]["datapool"]) && $_SESSION["config_discover"]["discover"]["datapool"] != "" ) ? $_SESSION["config_discover"]["discover"]["datapool"] : "local";

          if ( ! isset($_SESSION["layout"]) )             $_SESSION["layout"]             = (isset($_SESSION["config_discover"]["discover"]["layout"]) && $_SESSION["config_discover"]["discover"]["layout"] != "" ) ? $_SESSION["config_discover"]["discover"]["layout"] : 3;

          if ( ! isset($_SESSION["statistics"]) )         $_SESSION["statistics"]         = (isset($_SESSION["config_discover"]["discover"]["statistics"]) && $_SESSION["config_discover"]["discover"]["statistics"] == 1 ) ? 1 : 0;

          $_SESSION["interfaces"]["discover"] = 1;
          break;
        }
        case "library":
        {
          // Avoid multiple Configs
          if ( isset($_SESSION["interfaces"]["library"]) && $_SESSION["interfaces"]["library"] == 1 ) break;

          $this->load_check_module_config("library");

          $_SESSION["interfaces"]["library"] = 1;
          break;
        }        
        case "theme":
        {
          // Read theme config & load library
          $Theme	= (isset($_SESSION["config_general"]["theme"]["type"]) && $_SESSION["config_general"]["theme"]["type"] != "" ) 
                    ? $_SESSION["config_general"]["theme"]["type"] : "bootstrap";
          $this->load->library('themes/'.$Theme, "", "theme");
          break;
        }
    
        case "database":
        {
          // Read database config & load library
          $Database	= (isset($_SESSION["config_general"]["database"]["type"]) && $_SESSION["config_general"]["database"]["type"] != "" ) 
                      ? $_SESSION["config_general"]["database"]["type"] : "mysql";
          $this->load->library('databases/'.$Database, "", "database");
          break;
        }
    
        case "index_system":
        {
          // Read system type & load library and record format
          $IS    = (isset($_SESSION["config_general"]["index_system"]["type"]) && $_SESSION["config_general"]["index_system"]["type"] != "" ) 
                   ? $_SESSION["config_general"]["index_system"]["type"] : "solr";
          $this->load->library('index_systems/'.$IS, "", "index_system");
          break;
        }

        case "record_format":
        {
          // Read record format & load library
          $RF	= (isset($_SESSION["config_general"]["record_format"]["type"]) && $_SESSION["config_general"]["record_format"]["type"] != "" ) 
                ? $_SESSION["config_general"]["record_format"]["type"] : "marc21";
          $this->load->library('record_formats/'.$RF, "", "record_format");
          break;
        }

        case "export":
        {
          // Read export config & load library
          $export	= (isset($_SESSION["config_general"]["export"]["type"]) && $_SESSION["config_general"]["export"]["type"] != "" ) 
                    ? $_SESSION["config_general"]["export"]["type"] : "standard";
          $this->load->library('exports/'.$export, "", "export");
          break;
        }
    
        case "lbs":
        {
          // Read LBS config & load library - if it is available
          if (isset($_SESSION["config_general"]["lbs"]["available"]) && $_SESSION["config_general"]["lbs"]["available"] == "1" )
          {
            $LBS    = (isset($_SESSION["config_general"]["lbs"]["type"]) && $_SESSION["config_general"]["lbs"]["type"] != "" ) 
                      ? $_SESSION["config_general"]["lbs"]["type"] : "paia_daia";
            $this->load->library('lb_systems/'.$LBS, "", "lbs");
          }
          break;
        }
      }
    }
  }

  private function ensurePPN($PPN)
  {
    if ( $PPN == "" ) return false;

    if ( !isset($_SESSION["data"])  ||  ! array_key_exists($PPN, $_SESSION["data"]["results"]) )
    {
       $this->internal_search("id", $PPN);
    }
    return (isset($_SESSION["data"]["results"][$PPN]) ? true : false );
  }

  private function ensureEPN($PPN, $EPN)
  {
    if ( $PPN == "" || $EPN == "" ) return false;

    if ( !$this->ensurePPN($PPN)) return(-2);
    return (isset($_SESSION["exemplar"][$PPN][$EPN]) ? true : false );
  }
 
  private function ajaxreturn($code, $data)
  {
    $this->output->set_status_header($code); //Triggers the jQuery error callback
    $this->data['message'] = $data;
    echo json_encode($this->data);
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
    $dp = (isset($_SESSION["filter"]["datapool"])) ? $_SESSION["filter"]["datapool"] : "";
    $iln = (isset($_SESSION["filter"]["iln"])) ? $_SESSION["filter"]["iln"] : "";

    // Clear session
    $this->session->sess_destroy();
    // session_unset();
    // session_start();
    $_SESSION += $Container;
    if ( $dp != "" )  $_SESSION["filter"]["datapool"] = $dp;
    if ( $iln != "" ) $_SESSION["filter"]["iln"]      = $iln;

    echo 0;
  }

  public function config()
  {
    // Ajax Method => No view will be loaded, just data is returned

    // Receive params
    $platform = (array)json_decode($this->input->post("platform"));
    $screen   = (array)json_decode($this->input->post("screen"));

    // Check params

    // Ensure required interfaces
    $this->ensureInterface(array("config"));

    if ( !isset($_SESSION["language"]) ) $_SESSION["language"] = (isset($_SESSION["config_general"]["general"]["language"]) 	 && $_SESSION["config_general"]["general"]["language"] != "" ) 	 ? $_SESSION["config_general"]["general"]["language"]		: "ger";
    if ( !isset($_SESSION["layout"]) )   $_SESSION["layout"]   = (isset($_SESSION["config_discover"]["discover"]["layout"]) 	 && $_SESSION["config_discover"]["discover"]["layout"] != "" ) 	 ? $_SESSION["config_discover"]["discover"]["layout"]		: "3";
    if ( !isset($_SESSION["filter"]["datapool"]) ) $_SESSION["filter"]["datapool"] = (isset($_SESSION["config_discover"]["discover"]["datapool"]) && $_SESSION["config_discover"]["discover"]["datapool"] != "" ) ? $_SESSION["config_discover"]["discover"]["datapool"] : "local";

    // Collect some settings for javascript-clients
    $container = array
    (
      "devmode"				  => (isset($_SESSION["config_discover"]["dev"]["devmode"])				 && $_SESSION["config_discover"]["dev"]["devmode"] != "" )  				? $_SESSION["config_discover"]["dev"]["devmode"]				  : "0",
      "devuser"					=> (isset($_SESSION["config_discover"]["dev"]["devuser"])     		 && $_SESSION["config_discover"]["dev"]["devuser"] != "" )					? $_SESSION["config_discover"]["dev"]["devuser"]					: "",
      "devpassword"			=> (isset($_SESSION["config_discover"]["dev"]["devpassword"]) 		 && $_SESSION["config_discover"]["dev"]["devpassword"] != "" )			? $_SESSION["config_discover"]["dev"]["devpassword"]			: "",
      "devusername"			=> (isset($_SESSION["config_discover"]["dev"]["devusername"]) 		 && $_SESSION["config_discover"]["dev"]["devusername"] != "" )			? $_SESSION["config_discover"]["dev"]["devusername"]			: "",
      "devusermail"			=> (isset($_SESSION["config_discover"]["dev"]["devusermail"]) 		 && $_SESSION["config_discover"]["dev"]["devusermail"] != "" )			? $_SESSION["config_discover"]["dev"]["devusermail"]			: "",
      "devusermailtext" => (isset($_SESSION["config_discover"]["dev"]["devusermailtext"]) && $_SESSION["config_discover"]["dev"]["devusermailtext"] != "" )	? $_SESSION["config_discover"]["dev"]["devusermailtext"]	: "",
      "button_checklist" => (isset($_SESSION["config_discover"]["fullview"]["checklist"]) && $_SESSION["config_discover"]["fullview"]["checklist"] == 1 ) ? true  : false,
      "button_export" => (isset($_SESSION["config_discover"]["fullview"]["export"]) && $_SESSION["config_discover"]["fullview"]["export"] == 1 ) ? true  : false,
      "button_mail" => (isset($_SESSION["config_discover"]["fullview"]["mail"]) && $_SESSION["config_discover"]["fullview"]["mail"] == 1 && isset($_SESSION["config_general"]["lbs"]["available"]) && $_SESSION["config_general"]["lbs"]["available"] != "") ? true  : false,
      "button_print" => (isset($_SESSION["config_discover"]["fullview"]["print"]) && $_SESSION["config_discover"]["fullview"]["print"] == 1 ) ? true  : false,

      "librarytitle"		=> (isset($_SESSION["config_general"]["general"]["title"])        			 && $_SESSION["config_general"]["general"]["title"] != "" )        				? $_SESSION["config_general"]["general"]["title"]								: "",
      "softwarename"		=> (isset($_SESSION["config_general"]["general"]["softwarename"]) 			 && $_SESSION["config_general"]["general"]["softwarename"] != "" ) 				? $_SESSION["config_general"]["general"]["softwarename"]				: "GBV Discovery",
      "language"        => $_SESSION["language"],
      "layout"          => $_SESSION["layout"],
      "datapool"        => $_SESSION["filter"]["datapool"],
      "datapoolicons"  => isset($_SESSION["config_discover"]["datapoolicons"]) ? $_SESSION["config_discover"]["datapoolicons"] : array(),
      "time2warn" => (isset($_SESSION["config_general"]["lbs"]["time2warn"]) && $_SESSION["config_general"]["lbs"]["time2warn"] != "" ) ? $_SESSION["config_general"]["lbs"]["time2warn"]  : "",
      "time2kill" => (isset($_SESSION["config_general"]["lbs"]["time2kill"]) && $_SESSION["config_general"]["lbs"]["time2kill"] != "" ) ? $_SESSION["config_general"]["lbs"]["time2kill"]  : "",
      "discover" => isset($_SESSION["discover"]) ? $_SESSION["discover"] : true,
      "library" => isset($_SESSION["library"]) ? $_SESSION["library"] : false,
      "library_name" => isset($_SESSION["library_name"]) ? $_SESSION["library_name"] : "",
      "producer" => isset($_SESSION["producer"]) ? $_SESSION["producer"] : false,
      "iln" => isset($_SESSION["iln"]) ? $_SESSION["iln"] : ""
    );

    // Set stats
    $this->stats("Config");

    // Set Screen Resolution Stat
    if ( isset($screen['Width']) && $screen['Width'] != "" && isset($screen['Height']) && $screen['Height'] != "")
      $this->stats("Screen_" . $screen['Width'] . "x" . $screen['Height'], "year");

    // Set Browser & Version Stat
    if ( isset($platform['name']) && $platform['name'] != "" && isset($platform['version']) && $platform['version'] != "")
      $this->stats("Browser_" . $platform['name'] . " " . substr($platform['version'],0,strpos($platform['version'],".")), "year");

    // Set Render Stat
    if ( isset($platform['layout']) && $platform['layout'] != "" )
      $this->stats("Render_" . $platform['layout'], "year");

    // Set Product Stat
    if ( isset($platform['manufacturer']) && $platform['manufacturer'] != "" && isset($platform['product']) && $platform['product'] != "")
      $this->stats("Product_" . $platform['manufacturer'] . "_" . $platform['product'], "year");

    // Set OS Name & Version Stat
    if ( isset($platform['os']) && ( $Tmp = implode("_",array_values((array)$platform['os']))) != "" )
      $this->stats("OS_" . $Tmp, "year");

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
    if ( $username == "" ) return ($this->ajaxreturn("400","username is missing"));
    if ( $mailfrom == "" ) return ($this->ajaxreturn("400","mailfrom is missing"));
    if ( $mailto == "" )   return ($this->ajaxreturn("400","mailto is missing"));
    if (! isset($_SESSION["config_general"]["general"]["mailfrom"]) || $_SESSION["config_general"]["general"]["mailfrom"] == "" )  return ($this->ajaxreturn("400","Setting mailfrom is missing"));
    if ( count($ppnlist) == 0 )      return ($this->ajaxreturn("400","ppnlist is missing or empty"));
    if ( count($fullbodylist) == 0 ) return ($this->ajaxreturn("400","fullbodylist is missing or empty"));

    // Ensure required ppn data
    foreach ($ppnlist as $ppn)
    {
      if ( !$this->ensurePPN($ppn)) return(-2);
    }

    // Set stats
    $this->stats("Mail");
    
    // Load Mail Library
    $this->load->library('email');

    // Mail Config.
    $config['charset'] 	= 'utf-8';
    $config['mailtype'] = 'html';
    $this->email->initialize($config);

    // Mail Adresses
    $FromName = (isset($_SESSION["config_general"]["general"]["softwarename"]) 
              && isset($_SESSION["config_general"]["general"]["title"])) 
          ? $_SESSION["config_general"]["general"]["softwarename"] . " | " . $_SESSION["config_general"]["general"]["title"] 
          : "";
    $this->email->from($_SESSION["config_general"]["general"]["mailfrom"], iconv('UTF-8', 'ASCII//TRANSLIT', $FromName));
    $this->email->to($mailto);
    $this->email->reply_to($mailfrom);

    // Mail subject 
    $this->email->subject('Empfehlung von ' . $username . ' für Sie! | Recommendation for you');

    // Mail body
    $message = "";
    foreach ( $fullbodylist as $ppn  => $fullbody)
    {
      if ( ! in_array($ppn, $ppnlist))  continue;
      
      // Remove Links from message body
      //$fullbody  = $fullbodylist[$ppn];
      $fullbody  = preg_replace("/<a[^>]+\>/i", " ", $fullbody);
      $fullbody  = preg_replace("/<\/a>/i", " ", $fullbody);

      $fullbody .= "<a style='color:blue;background-color:white;text-decoration:none;' href='" 
                         . base_url("id%7Bcolon%7D".$ppn) . "'><b>Bitte klicken Sie hier, um diese Empfehlung zu öffnen | Please click here to open this recommendation</b></a>";
      $message  .= "<hr>" . $fullbody;
    }

    if ( count($ppnlist) > 1 )
    {
      $this->email->message(json_decode($username) . " (<a href='mailto:" . $mailfrom . "'>" . $mailfrom 
                            . "</a>)<br />hat mehrere Empfehlungen f&uuml;r Sie. | has several recommendations for you.<hr>"
                            . json_decode($msg) . $message);
    }
    else
    {
      $this->email->message(json_decode($username) . " (<a href='mailto:" . $mailfrom . "'>" . $mailfrom 
                            . "</a>)<br />hat eine Empfehlung f&uuml;r Sie. | has a recommendation for you.<hr>"
                            . json_decode($msg) . $message);
    }

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
    if ( $ppn == "" )      return ($this->ajaxreturn("400","ppn is missing"));
    if ( $mailfrom == "" ) return ($this->ajaxreturn("400","mailfrom is missing"));
    if ( $mailto == "" )   return ($this->ajaxreturn("400","mailto is missing"));
    if ( $fullbody == "" ) return ($this->ajaxreturn("400","mailbody is missing"));
    if ( ! is_array($exemplar) || count($exemplar) == 0 ) return ($this->ajaxreturn("400","exemplar is missing"));
    if ( ! $this->isUserSessionAlive() ) return ($this->ajaxreturn("400","timeout user session"));

    //$this->printArray2File($exemplar);
    //echo json_encode($exemplar);
    //return;

    // Ensure required interfaces
    $this->ensureInterface(array("config","database"));

    // Ensure required ppn data
    if ( !$this->ensurePPN($ppn)) return ($this->ajaxreturn("400","ppn not found"));

    // Set stats
    $this->stats("MailOrder");

    // Load Mail Library
    $this->load->library('email');

    // Mail Config.
    $config['charset'] 	= 'utf-8';
    $config['mailtype'] = 'html';
    $this->email->initialize($config);

    // Mail Adresses
    $FromName = (isset($_SESSION["config_general"]["general"]["softwarename"])       &&  isset($_SESSION["config_general"]["general"]["title"])) 
         ? $_SESSION["config_general"]["general"]["softwarename"] . "@" . $_SESSION["config_general"]["general"]["title"] : "";
    $this->email->from($mailfrom);
    $this->email->reply_to($mailfrom);

    if ( strtolower(MODE) == "development" && isset($_SESSION["config_discover"]["dev"]["mailto"]) 
                                                 && $_SESSION["config_discover"]["dev"]["mailto"] != "" )
    {
      // Development Mode
      $this->email->to($_SESSION["config_discover"]["dev"]["mailto"]);
    }
    else
    {
      // Test & Production Mode
      $this->email->to($mailto);
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
      if ( in_array($key, array("action","typ","form","case")) )  continue;
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
    $Mess .= "<a style='color:blue;background-color:white;text-decoration:none;' href='" 
          . base_url($ppn."/id") . "'><b>Bitte klicken Sie hier, um dieses bestellten Medium einzusehen</b></a>";
    
    $this->email->message($Mess);

    // Send it away...
    $this->email->send();

    // Return data
    echo "0";
  }

  /**
  * Special activities for a specific library
  *
  * @author  Alexander Karim <Alexander.Karim@gbv.de>
  */
  public function libraryspecial()
  {
    // Ajax Method => No view will be loaded, just data is returned

    // Receive params
    $action       = $this->input->post('action');
    $ppnlist      = (array)json_decode($this->input->post('ppnlist'));
    $fields       = (array)json_decode($this->input->post('fields'));

    // Check params
    if ( $action == "" )          return ($this->ajaxreturn("400","action is missing"));
    if ( count($ppnlist) == 0 )   return ($this->ajaxreturn("400","ppnlist is missing or empty"));

    // Ensure required ppn data
    foreach ($ppnlist as $ppn)
    {
      if ( !$this->ensurePPN($ppn)) return(-2);
    }

    // Set stats
    $this->stats("LibrarySpecial");

    // Load Special Library of Library
    $this->load->library('/special/special', NULL, 'special');
    $container = $this->special->$action($ppnlist, $fields);

    // Return data
    echo json_encode($container);
  }

  public function command()
  {
    // Ajax Method => No view will be loaded, just data is returned

    // Receive params
    $cmd		= strtolower($this->input->post('cmd'));

    // Check params
    if ( $cmd == "" ) return ($this->ajaxreturn("400","cmd is missing"));

    // Ensure required interfaces
    $this->ensureInterface(array("config"));

    // Set stats
    $this->stats("Command");

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
          $_SESSION["internal"]["paia"]	= ( !isset($_SESSION["internal"]["paia"]) || $_SESSION["internal"]["paia"] == "0" ) ? "1" : "0";
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
    if ( $language == "" ) return ($this->ajaxreturn("400","language is missing"));

    // Set stats
    $this->stats("Lanuguage_" . ucfirst($language));

    // Ensure required interfaces
    $this->ensureInterface(array("config"));

    // Set Session Language
    $_SESSION["language"] = $language;

    // Return data in jsonformat
    echo json_encode($_SESSION['language_'.$_SESSION["language"]]);
  }

  public function linkresolver()
  {
    // Ajax Method => No view will be loaded, just data is returned

    // Receive params
    $ppn = $this->input->post('ppn');

    // Check params
    if ( $ppn == "" ) return ($this->ajaxreturn("400","PPN is missing"));

    // Ensure required interfaces
    $this->ensureInterface(array("config","database","export"));

    // Check link already resolved in database
    $Resolved = $this->database->get_resolved_link($ppn);

    //  Resolve New PPN
    if ( $Resolved["status"] != "1" )
    {
      $Resolved["links"] = json_encode($this->export->linkresolver($ppn));

      // Store resolved link ( even if empty )
      $this->database->store_resolved_link($ppn, $Resolved["links"]);
    }

    // Call export & Return data in jsonformat
    echo $Resolved["links"];
  }

  public function exportlink()
  {
    // Ajax Method => No view will be loaded, just data is returned

    // Receive params
    $ppnlist = (array)json_decode($this->input->post('ppnlist'));
    $format	 = $this->input->post('format');

    // Check params
    if ( $format == "" ) return ($this->ajaxreturn("400","format is missing"));
    if ( count($ppnlist) == 0 ) return ($this->ajaxreturn("400","ppnlist is empty"));

    // Set stats
    $this->stats("Export_" . ucfirst($format));

    // Ensure required interfaces
    $this->ensureInterface(array("config","export"));

    // Call export & Return data in jsonformat
    $container = array();
    foreach ($ppnlist as $ppn ) 
    {
      // Ensure required ppn data
      if ( !$this->ensurePPN($ppn)) return(-2);

      $container[$ppn] = $this->export->exportlink($_SESSION["data"]["results"][$ppn], $format);
    }
    echo json_encode($container);
  }
  
  public function internal_exportlink($ppn,$format)
  {
    // Check params
    if ( $ppn == "" || $format == "" ) return(-2);

    // Ensure required interfaces
    $this->ensureInterface(array("config","export"));

    // Ensure required ppn data
    if ( !$this->ensurePPN($ppn)) return(-2);

    // Call export & Return data in jsonformat
    return $this->export->exportlink($_SESSION["data"]["results"][$ppn], $format);
  }

  public function exportfile($ppn, $format)
  {
    // Check params
    if ( $ppn == "" || $format == "" ) return(-2);

    // Set stats
    $this->stats("Export_" . ucfirst($format));

    // Ensure required interfaces
    $this->ensureInterface(array("config","export"));

    // Ensure required ppn data
    if ( !$this->ensurePPN($ppn)) return(-2);

    // Load helper
    $this->load->helper('download');

    // Call export
    $data = $this->export->exportfile($_SESSION["data"]["results"][$ppn],$format, $ppn);
    $name = $format . "-" . $ppn . ".txt";
    force_download($name, $data);
  }
  
  public function getwords()
  {
    // Ajax Method => No view will be loaded, just data is returned

    // Receive params
    $query		= $this->input->get('query');

    // Check params
    if ( $query == "" ) return ($this->ajaxreturn("400","query is missing"));

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
    if ( $search == "" )  return ($this->ajaxreturn("400","search is missing"));
    if ( $user == "" )    return ($this->ajaxreturn("400","user is missing"));

    // Set stats
    $this->stats("StoreUserSearch");
    
    // Ensure required interfaces
    $this->ensureInterface(array("config","database"));

    // Invoke database driver
    echo json_encode($this->database->store_user_search($search, $user, $facets));
  }

  public function statsclient()
  {
    // Ajax Method => No view will be loaded, just data is returned

    // Receive params
    $typ  = $this->input->post('typ');
    $name = $this->input->post('name');
    $tot  = $this->input->post('range');

    // Check params
    if ( $typ == "" )  return ($this->ajaxreturn("400","typ is missing"));
    if ( $name == "" ) return ($this->ajaxreturn("400","name is missing"));
    if ( $tot == "" )  return ($this->ajaxreturn("400","range is missing"));

    $name = ( $typ == "Link" ) ? parse_url($name,PHP_URL_HOST) : trim($name);

    // Set stats
    $this->stats(ucfirst($typ) . "_" . $name, $tot);

    // Return 
    echo json_encode(0);
  }  

  public function getdiscoverybibs()
  {
    // Ensure required interfaces
    $this->ensureInterface(array("config","database"));

    if ( ! isset($_SESSION["DiscoveryBibs"]) || count($_SESSION["DiscoveryBibs"]) == 0 )
    {
      $_SESSION["DiscoveryBibs"] = $this->database->get_discovery_bibs();
    }

    // Invoke database driver
    return $_SESSION["DiscoveryBibs"];
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
    if ( $pw == "" )    return ($this->ajaxreturn("400","pw is missing"));
    if ( $user == "" )  return ($this->ajaxreturn("400","user is missing"));

    // Set stats
    $this->stats("LBS_Login");

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

    // Set stats
    $this->stats("LBS_Logout");

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

    // Set stats
    $this->stats("LBS_Document");

    // Call LBS
    return $this->lbs->document($PPN);
  }

  public function request()
  {
    // Receive params
    $uri	= $this->input->post('uri');

    // Check params
    if ( $uri == "" )    return ($this->ajaxreturn("400","uri is missing"));

    // Set stats
    $this->stats("LBS_Request");

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
    if ( $uri == "" )    return ($this->ajaxreturn("400","uri is missing"));

    // Set stats
    $this->stats("LBS_Cancel");

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
    if ( $uri == "" )    return ($this->ajaxreturn("400","uri is missing"));

    // Set stats
    $this->stats("LBS_Renew");

    // Ensure required interfaces
    $this->ensureInterface(array("config","lbs"));

    // Call LBS
    echo json_encode($this->lbs->renew($uri));
  }  

  // ********************************************
  // ********** Database-Functions **************
  // ********************************************
  public function stats($name, $total="day")
  {
    // Check params
    if ( $name == "" ) return (-1);
    if ( !isset($_SESSION["statistics"]) || ! $_SESSION["statistics"] ) return (-1);

    // Ensure required interfaces
    $this->ensureInterface(array("config","database"));

    return ($this->database->stats($name, $total));
  }

  public function counter($name, $global=true)
  {
    // Check params
    if ( $name == "" ) return (-1);

    // Ensure required interfaces
    $this->ensureInterface(array("config","database"));

    if ( $global ) 
    {
      return ($this->database->counter($name));
    }
    else
    {
      return ($this->database->counter_library($name));
    }
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
    if ( trim($search) != "*" && trim($search) != "" 
      && substr(trim($search),0,3) !== "id:" && substr(trim($search),0,8) !== "ppnlink:" )
    {
      $this->stats("Search_" . $search,"month");
    }

    // Invoke index system
    $container = $this->index_system->search($search, $package, $facets);

    // Store session data
    // $_SESSION["data"]["index_system"][$package]	= $container;
    
    // Invoke record format driver
    $container = $this->record_format->convert($container);

    // Store session data
    // $_SESSION["data"]["record_format"]	= $container;

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
    if ( $search == "" )                     return ($this->ajaxreturn("400","search is missing"));
    if ( $package == "" || $package == "0" ) return ($this->ajaxreturn("400","package is missing or 0"));

    // Ensure required interfaces
    $this->ensureInterface(array("config","discover","theme"));

    // Set stats
    $this->stats("Search");

    // Set facet
    $_SESSION["filter"] = $facets;
    
    // Erase session data when iln is changed
    if ( isset($facets["iln"]) && $facets["iln"] > "0" && $facets["iln"] != "" )
    {
      if ( !isset($_SESSION["iln"]) || ( isset($_SESSION["iln"]) && $_SESSION["iln"] != $facets["iln"] ) )
      {
        $_SESSION['data'] = array();
        $_SESSION["iln"]  = $facets["iln"];
      }
    }

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
    //$this->printArray2File($container);      

    echo json_encode($container);
  }

  // Search media data (invoked by system without optical stuff and includes caching)
  public function internal_search($type, $ppn)
  {
    // Check params
    if ( $type == "" || $ppn == "" ) return(-2);
    $type = strtolower($type);

    // Set stats
    $this->stats("Search_Internal_" . $type);

    if ( $type == "id" && isset($_SESSION["data"]["results"])  && array_key_exists($ppn, $_SESSION["data"]["results"]) )
    {
      $container = $_SESSION["data"]["results"][$ppn];
    }
    else
    {
      $container = $this->dosearch($type . ":" . $ppn,"0",false);
    }
    return ($container);
  }

  public function searchsimularpubs()
  {
    // Ajax Method => No view will be loaded, just data is returned

    // Receive params
    $PPN     = $this->input->post('ppn');

    // Check params
    if ( $PPN == "" ) return ($this->ajaxreturn("400","ppn is missing"));

    // Set stats
    $this->stats("Search_Simular");

    try
    {
      // Ensure required interfaces
      $this->ensureInterface(array("config","index_system","theme"));

      // Invoke index system to get simular pubs
      $SimularPubs = $this->index_system->getSimilarPublications($PPN);

      // Now invoke again to catch all data
      $container = $this->dosearch("id:(".implode(",",$SimularPubs).")","0",false);

      // Create PPN list
      $container["ppnlist"] = array_keys($container["results"]);

      // Invoke theme format driver
      $container = $this->theme->preview($container, array('collgsize' => '6','useppnlist' => true));
    
      echo json_encode($container);
    }
    catch (Exception $e) 
    {
      // Fehler dokumentieren 
      echo json_encode(array());
    }
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
    if ( $layout == "" ) return ($this->ajaxreturn("400","layout is missing"));

    // Set stats
    $this->stats("Layout_" . (12 / $layout));

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

    // Check params
    if ( $PPN == "" )   return ($this->ajaxreturn("400","ppn is missing"));
    if ( $dlgid == "" ) return ($this->ajaxreturn("400","dlgid is missing"));

    // Set stats
    $this->stats("FullView");

    // Ensure required interfaces
    $this->ensureInterface(array("config","theme","lbs"));

    // Ensure required ppn data
    if ( !$this->ensurePPN($PPN)) return ($this->ajaxreturn("400","ppn not found"));

    // Invoke theme format driver
    echo $this->theme->fullview(array('ppn'=>$PPN,'dlgid'=>$dlgid));
  }

  // Show user data large 
  public function userview($Action)
  {
    // Ajax Method => No view will be loaded, just data is returned

    // Receive params

    // Check params
    if ( $Action == "" )                 return ($this->ajaxreturn("400","action is missing"));
    if ( ! $this->isUserSessionAlive() ) return ($this->ajaxreturn("400","timeout user session"));;

    // Set stats
    $this->stats("UserView");

    // Ensure required interfaces
    $this->ensureInterface(array("config","theme","database","lbs"));

    // Refresh data
    $this->lbs->userdata();

    // Load Stored search
    $_SESSION['searches'] = $this->database->load_user_search($_SESSION["userlogin"]);

    // Display view
    echo $this->theme->userview(array('action'=>$Action));
  }

  // Show user data large 
  public function assistant($dlgid)
  {
    // Ajax Method => No view will be loaded, just data is returned

    // Receive params
    if ( $dlgid == "" ) return ($this->ajaxreturn("400","dlgid is missing"));

    // Check params

    // Set stats
    $this->stats("Assistant");

    // Ensure required interfaces
    $this->ensureInterface(array("config","theme","lbs"));

    // Display view
    echo $this->theme->assistant(array('dlgid'=>$dlgid));
  }
  
  // Show user data large 
  public function mailorderview($PPN,$EPN)
  {
    // Ajax Method => No view will be loaded, just data is returned

    // Receive params

    // Check params
    if ( $PPN == "" ) return ($this->ajaxreturn("400","ppn is missing"));
    if ( $EPN == "")  return ($this->ajaxreturn("400","epn is missing"));;

    // Set stats
    $this->stats("MailOrderView");

    // Ensure required interfaces
    $this->ensureInterface(array("config","theme"));

    // Ensure required ppn and epn data
    if ( !$this->ensureEPN($PPN,$EPN)) return ($this->ajaxreturn("400","timeout exemplar data"));;

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
    $this->ensureInterface(array("config","database"));

    // Set stats
    $this->stats("NoJavaScript");

    // Set params

    // Show Frontpage
    $this->load->view(DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . 'nojavascript');
  }

  public function view($modul="discover", $search="", $facets="")
  {
    // Check params


    // Reset Configuration
    if ( $modul != $this->module )  $_SESSION["interfaces"] = array();

    // Receive params
    $this->module = $modul;

    // Ensure required interfaces
    $this->ensureInterface(array("config",$this->module));

    // Set stats
    $this->stats("ViewInit");

    // Set params
    $param["modul"] = $this->module;
    $param["initsearch"] = $search;
    $param["initfacets"] = $facets;
    if ( isset($_SESSION["library_name"]) && $_SESSION["library_name"] != "" )
    {
      $param["discoverybibs"] = $this->getdiscoverybibs();
    }

    // Show Frontpage
    if ( isset($_SESSION["config_general"]["general"]["frontpage"]) && $_SESSION["config_general"]["general"]["frontpage"] == 1 
       && $search == "" && $facets == "" && $this->module == "discover" )
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
        if ( file_exists(LIBRARYCODE . $block.'.php'))
        {
          $param["blockpath"] = LIBRARYCODE . $block.'.php';
          $this->load->library_view(LIBRARYCODE, $block, $param);
        }
        elseif ( file_exists(KERNELBLOCKS . $block.'.php'))
        {
          $param["blockpath"] = KERNELBLOCKS . $block.'.php';
          $this->load->view(DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . 'block', $param);
        }
        else
        {
          // Whoops, we don't have a page for that!
          show_404();
        }     
      }
  
      // Load Footer
      $this->load->view(DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . 'footer', $param);

    }
  }

  public function directopen($search = "*", $facets = "")
  {
    // Receive params

    // Check params

    // Convert characters
    $search = ( urldecode($search) == "{star}" ) ? "*" : urldecode($search);
    $search = str_replace("{slash}", "/", $search);
    $search = str_replace("{st}", "<", $search);
    $search = str_replace("{gt}", ">", $search);
    $search = str_replace("{colon}", ":", $search);
    
    // Call main method with parameters
    $this->view("discover",$search,$facets);    
  }  

}
