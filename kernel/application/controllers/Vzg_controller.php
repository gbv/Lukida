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
  
  protected function ensureInterface($Interfaces)
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

          if ( ! isset($_SESSION["internal"]["marc"]) )     $_SESSION["internal"]["marc"]     = (strtolower(MODE) == "development") ? 1 : 0;
          if ( ! isset($_SESSION["internal"]["daia"]) )     $_SESSION["internal"]["daia"]     = (strtolower(MODE) == "development") ? 1 : 0;
          if ( ! isset($_SESSION["internal"]["item"]) )     $_SESSION["internal"]["item"]     = (strtolower(MODE) == "development") ? 1 : 0;
          if ( ! isset($_SESSION["internal"]["paia"]) )     $_SESSION["internal"]["paia"]     = (strtolower(MODE) == "development") ? 1 : 0;
          if ( ! isset($_SESSION["internal"]["marcfull"]) ) $_SESSION["internal"]["marcfull"] = 0;

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
            if ( strtolower(MODE) == "production" )
            {
              $LBS    = (isset($_SESSION["config_general"]["lbsprod"]["type"]) && $_SESSION["config_general"]["lbsprod"]["type"] != "" ) 
                        ? $_SESSION["config_general"]["lbsprod"]["type"] : "paia2_daia2";
            }
            else
            {
              $LBS    = (isset($_SESSION["config_general"]["lbsdevtest"]["type"]) && $_SESSION["config_general"]["lbsdevtest"]["type"] != "" ) 
                        ? $_SESSION["config_general"]["lbsdevtest"]["type"] : "paia2_daia2";
            }
            $this->load->library('lb_systems/'.$LBS, "", "lbs");
            $_SESSION["interfaces"]["lbs"] = 1;
          }
          break;
        }
      }
    }
  }

  public function ensurePPN($PPN)
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

  public function datetime2german($DateTime) 
  {
    $Tmp = strtotime($DateTime);
    return  date("d.m.Y H:i",$Tmp);
  }
  
  public function CutText($Text, $MaxBreak, $ToolTip = false)
  {
    $Text = trim($Text);
    if ( strlen($Text) > $MaxBreak )
    {
      $MinBreak = floor($MaxBreak *.8);
      $CutText  = substr($Text, 0, $MaxBreak);
      if ( strrpos($CutText, ' ', $MinBreak) !== false )
      {
        $CutText = substr($CutText, 0, strrpos($CutText, ' ', $MinBreak));
      }
      $CutText = ( $ToolTip ) ? "<a data-toggle='tooltip' title='" . $Text . "'>" . trim($CutText) . "...</a>" : trim($CutText) . "...";
    }
    else
    {
      $CutText = $Text;
    }
    return $CutText;
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
      "devmode"				  => (strtolower(MODE) == "development") ? "1" : "0",
      "devuser"					=> (isset($_SESSION["config_discover"]["development"]["devuser"]) 	  && $_SESSION["config_discover"]["development"]["devuser"] != "" )					? $_SESSION["config_discover"]["development"]["devuser"]					: "",
      "devpassword"			=> (isset($_SESSION["config_discover"]["development"]["devpassword"]) && $_SESSION["config_discover"]["development"]["devpassword"] != "" )			? $_SESSION["config_discover"]["development"]["devpassword"]			: "",
      "button_checklist" => (isset($_SESSION["config_discover"]["fullview"]["checklist"]) && $_SESSION["config_discover"]["fullview"]["checklist"] == 1 ) ? true  : false,
      "button_export" => (isset($_SESSION["config_discover"]["fullview"]["export"]) && $_SESSION["config_discover"]["fullview"]["export"] == 1 ) ? true  : false,
      "button_qrcode" => (isset($_SESSION["config_discover"]["fullview"]["qrcode"]) && $_SESSION["config_discover"]["fullview"]["qrcode"] == 1 ) ? true  : false,
      "button_mail" => (isset($_SESSION["config_discover"]["fullview"]["mail"]) && $_SESSION["config_discover"]["fullview"]["mail"] == 1 && isset($_SESSION["config_general"]["lbs"]["available"]) && $_SESSION["config_general"]["lbs"]["available"] != "") ? true  : false,
      "button_print" => (isset($_SESSION["config_discover"]["fullview"]["print"]) && $_SESSION["config_discover"]["fullview"]["print"] == 1 ) ? true  : false,
      "googlecover" => (isset($_SESSION["config_discover"]["fullview"]["googlecover"]) && $_SESSION["config_discover"]["fullview"]["googlecover"] == 1 ) ? true  : false,
      "googlepreview" => (isset($_SESSION["config_discover"]["fullview"]["googlepreview"]) && $_SESSION["config_discover"]["fullview"]["googlepreview"] == 1 ) ? true  : false,
      "simularpubs" => (isset($_SESSION["config_discover"]["fullview"]["tab2_available"]) && $_SESSION["config_discover"]["fullview"]["tab2_available"] == 1 ) ? true  : false,
      "librarytitle"		=> (isset($_SESSION["config_general"]["general"]["title"])        			 && $_SESSION["config_general"]["general"]["title"] != "" )        				? $_SESSION["config_general"]["general"]["title"]								: "",
      "softwarename"		=> (isset($_SESSION["config_general"]["general"]["softwarename"]) 			 && $_SESSION["config_general"]["general"]["softwarename"] != "" ) 				? $_SESSION["config_general"]["general"]["softwarename"]				: "GBV Discovery",
      "language"        => $_SESSION["language"],
      "layout"          => $_SESSION["layout"],
      "datapool"        => (isset($_SESSION["config_discover"]["discover"]["datapool"]) && $_SESSION["config_discover"]["discover"]["datapool"] != "" )     ? $_SESSION["config_discover"]["discover"]["datapool"]      : "local",
      "time2warn" => (isset($_SESSION["config_general"]["lbs"]["time2warn"]) && $_SESSION["config_general"]["lbs"]["time2warn"] != "" ) ? $_SESSION["config_general"]["lbs"]["time2warn"]  : "",
      "time2kill" => (isset($_SESSION["config_general"]["lbs"]["time2kill"]) && $_SESSION["config_general"]["lbs"]["time2kill"] != "" ) ? $_SESSION["config_general"]["lbs"]["time2kill"]  : "",
      "counterselection" => (isset($_SESSION["config_general"]["lbs"]["counterselection"]) && $_SESSION["config_general"]["lbs"]["counterselection"] == 1 ) ? true : false,
      "discover" => isset($_SESSION["discover"]) ? $_SESSION["discover"] : true,
      "library" => isset($_SESSION["library"]) ? $_SESSION["library"] : false,
      "library_name" => (defined("LIBRARY")) ? LIBRARY : "",
      "producer" => isset($_SESSION["producer"]) ? $_SESSION["producer"] : false,
      "iln" => isset($_SESSION["iln"]) ? $_SESSION["iln"] : "",
      "maxrenewals" => (isset($_SESSION["config_general"]["lbs"]["maxrenewals"]) && $_SESSION["config_general"]["lbs"]["maxrenewals"] != "" ) ? $_SESSION["config_general"]["lbs"]["maxrenewals"]  : "0",
      "lbs" => (isset($_SESSION["config_general"]["lbs"]["available"]) && $_SESSION["config_general"]["lbs"]["available"] == 1 ) ? true : false,
	     "printlogo" => (isset($_SESSION["config_general"]["general"]["printlogo"]) && $_SESSION["config_general"]["general"]["printlogo"] != "" ) ? $_SESSION["config_general"]["general"]["printlogo"] : ""
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
    $SoftwareName = (isset($_SESSION["config_general"]["general"]["softwarename"]) && $_SESSION["config_general"]["general"]["softwarename"] != "" ) 
                ? $_SESSION["config_general"]["general"]["softwarename"] : "";
    $LibraryName  = (isset($_SESSION["config_general"]["general"]["title"]) && $_SESSION["config_general"]["general"]["title"] != "" ) 
                ? $_SESSION["config_general"]["general"]["title"] : "";
    if ( $LibraryName != "" )
    {
      $FromName = ( $SoftwareName != "" ) ? $SoftwareName . " - " . $LibraryName : $LibraryName;
    }
    else
    {
      $FromName = $SoftwareName;
    }
	
    $this->email->from($_SESSION["config_general"]["general"]["mailfrom"], $FromName);
    $this->email->reply_to($_SESSION["config_general"]["general"]["mailfrom"], $FromName);
    $this->email->to($mailto);

    // Mail subject 
    $this->email->subject($this->database->code2text("RECOMMENDATIONFROM") . ' ' . $username);

    // Mail body
    $message = "<p><b>" . $LibraryName . "</b>: <a href='" . base_url() . "'>" . ( $SoftwareName != "" ? $SoftwareName  : base_url() ) . "</a></p>";	
    foreach ( $fullbodylist as $ppn  => $fullbody)
    {
      if ( ! in_array($ppn, $ppnlist))  continue;
      
      // Remove Links from message body
      $fullbody  = preg_replace("/<a[^>]+\>/i", " ", $fullbody);
      $fullbody  = preg_replace("/<\/a>/i", " ", $fullbody);

      $fullbody .= "<sub><a style='color:blue;background-color:white;text-decoration:none;font-size:21px;' href='" 
                         . base_url("id%7Bcolon%7D".$ppn) . "'><b>" . $this->database->code2text("CLICKTOOPEN") . "</b></a></sub>";
      $message  .= "<hr>" . $fullbody;
    }

    if ( count($ppnlist) > 1 )
    {
      $this->email->message("<p>" . json_decode($username) . " (<a href='mailto:" . $mailfrom . "'>" . $mailfrom 
                            . "</a>) " . $this->database->code2text("HASRECOMMENDATIONS") . ".</p><p>"
                            . json_decode($msg) . "</p>" . $message);
    }
    else
    {
      $this->email->message("<p>" . json_decode($username) . " (<a href='mailto:" . $mailfrom . "'>" . $mailfrom 
                            . "</a>) " . $this->database->code2text("HASRECOMMENDATION") . ".</p><p>"
                            . json_decode($msg) . "</p>" . $message);
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
    $ppn				 = $this->input->post('ppn');
    $mailfrom		 = $this->input->post('mailfrom');
    $mailto			 = $this->input->post('mailto');
    $mailtoname  = $this->input->post('mailtoname');
    $fullbody		 = $this->input->post('fullbody');
    $exemplar		 = (array)json_decode($this->input->post('exemplar'));
    $userinput   = (array)json_decode($this->input->post('userinput'));
    $mailtyp     = $this->input->post('mailtyp');
    $mailsubject = $this->input->post('mailsubject');

    // Check params
    if ( $ppn == "" )      return ($this->ajaxreturn("400","ppn is missing"));
    if ( $mailfrom == "" ) return ($this->ajaxreturn("400","mailfrom is missing"));
    if ( $mailto == "" )   return ($this->ajaxreturn("400","mailto is missing"));
    if ( $fullbody == "" ) return ($this->ajaxreturn("400","mailbody is missing"));
    if ( ! is_array($exemplar) || count($exemplar) == 0 ) return ($this->ajaxreturn("400","exemplar is missing"));
    if ( ! $this->isUserSessionAlive() ) return ($this->ajaxreturn("400","timeout user session"));
    if ( $mailtyp == "" ) $mailtyp = "order";
    if ( $mailsubject == "" ) $mailtyp = "Magazinbestellung";
    if ( isset($_SESSION["login"]["status"]) && $_SESSION["login"]["status"] >= "1" )
    {
      echo json_encode(array(
        "status" => -3,
        "error"  => ( isset($_SESSION["userstatus"]["message"]) && $_SESSION["userstatus"]["message"] == true && isset($_SESSION["userstatus"]["messagetext"])) ? $_SESSION["userstatus"]["messagetext"] : "Error" ));
      return(0);
    }

    // Ensure required interfaces
    $this->ensureInterface(array("config","discover","database"));

    // Ensure required ppn data
    if ( !$this->ensurePPN($ppn)) return ($this->ajaxreturn("400","ppn not found"));

    // Set stats
    $this->stats("Mail".ucfirst($mailtyp));

    // Load Mail Library
    $this->load->library('email');

    // Mail Config.
    $config['charset'] 	= 'utf-8';
    $config['mailtype'] = 'html';
    $this->email->initialize($config);

    // Mail Adresses
    $FromName = (isset($_SESSION["config_general"]["general"]["softwarename"]) && $_SESSION["config_general"]["general"]["softwarename"] != "" ) 
                ? $_SESSION["config_general"]["general"]["softwarename"] : "";
    if (isset($_SESSION["config_general"]["general"]["title"]) && $_SESSION["config_general"]["general"]["title"] != "" )
    {
      $FromName .= ( $FromName != "" ) ? " - " . $_SESSION["config_general"]["general"]["title"] : $_SESSION["config_general"]["general"]["title"];
    }
    $this->email->from($_SESSION["config_general"]["general"]["mailfrom"], $FromName);
    $this->email->reply_to($_SESSION["config_general"]["general"]["mailfrom"], $FromName);

    if ( strtolower(MODE) == "development" && isset($_SESSION["config_discover"]["development"]["mailto"]) 
                                                 && $_SESSION["config_discover"]["development"]["mailto"] != "" )
    {
      // Development Mode
      $this->email->to($_SESSION["config_discover"]["development"]["mailto"]);
    }
    else
    {
      // Test & Production Mode
      $this->email->to($mailto);
    }
    
    // Username
    $username = trim($_SESSION["login"]["firstname"] . " " . $_SESSION["login"]["lastname"]);
        
    // Mail subject
    $this->email->subject($mailsubject . ' von ' . $username );

    // Remove Links from message body
    $fullbody=preg_replace("/<a[^>]+\>/i", " ", $fullbody);
    $fullbody=preg_replace("/<\/a>/i", " ", $fullbody);


    // Body receipient part
    $Mess = "<h3>Empfänger</h3>"; 
    $Mess .= "<table>";
    $Mess .= "<tr><td>" . $this->database->code2text("MAIL") . "</td><td>" . $mailto . "</td></tr>";
    $Mess .= "</table>";

    // Body user part
    $UserElements = ( isset($_SESSION["config_discover"]["mailorderview"]["usermailelements"]) 
                         && $_SESSION["config_discover"]["mailorderview"]["usermailelements"] != "" ) 
               ? strtolower($_SESSION["config_discover"]["mailorderview"]["usermailelements"]) : "";
 
    $Mess .= "<h3>Benutzer</h3>"; 
    $Mess .= "<table>";
    foreach ( $_SESSION["login"] as $key => $value )
    {
      if ( $value == "" || $key == "type" )  continue;
      if ( $UserElements != "all" )
      {
        if ( !in_array($key,explode(",",$UserElements)) ) continue;
      }
      $Mess .= "<tr><td>" . $this->database->code2text($key) . "</td><td>" . $value . "</td></tr>";
    }
    $Mess .= "</table>";

    // Body sample part
    $Mess .= "<h3>Exemplar</h3>"; 
    $Mess .= "<table>";
    foreach ( $exemplar as $key => $value )
    {
      if ( in_array($key, array("action","typ","form","case","method")) )  continue;
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
    $Mess .= "<table border=1>" . json_decode($fullbody) . "</table>";

    // Body link part
    $Mess .= "<h3>Direkter Link</h3>"; 
    $Mess .= "<a style='color:blue;background-color:white;text-decoration:none;' href='" 
          . base_url("id(".$ppn.")") . "'><b>Bitte klicken Sie hier, um dieses Medium einzusehen</b></a>";
    
    $this->email->message($Mess);

    // Send it away...
    $this->email->send();

    // Set logs
    $Title = (isset($_SESSION["data"]["results"][$ppn]["title"])) ? substr($_SESSION["data"]["results"][$ppn]["title"],0,99) : "";
    $Data  = $userinput + array("mailto"=>$mailto, "mailtoname"=>$mailtoname);
    $this->database->store_logs($mailsubject, $Mess, $_SESSION["userlogin"], $ppn, $Title, substr($username,0,99), serialize($Data));

    // Return data
    echo json_encode(array("status" => "0"));
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
    $this->load->library('special/special', NULL, 'special');
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
    $this->ensureInterface(array("config","discover"));

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
      case "item":
      case "items":
      {
        if ( ! isset($cmd[1] ) )
        {
          $_SESSION["internal"]["item"] = ( !isset($_SESSION["internal"]["item"]) || $_SESSION["internal"]["item"] == "0" ) ? "1" : "0";
        }
        else
        {
          $_SESSION["internal"]["item"] = ($cmd[1]== "off") ? "0" : "1";
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
          $_SESSION["internal"]["marc"] = ( !isset($_SESSION["internal"]["marc"]) || $_SESSION["internal"]["marc"] == "0" ) ? "1" : "0";
          $_SESSION["internal"]["daia"] = ( !isset($_SESSION["internal"]["daia"]) || $_SESSION["internal"]["daia"] == "0" ) ? "1" : "0";
          $_SESSION["internal"]["item"] = ( !isset($_SESSION["internal"]["item"]) || $_SESSION["internal"]["item"] == "0" ) ? "1" : "0";
          $_SESSION["internal"]["paia"] = ( !isset($_SESSION["internal"]["paia"]) || $_SESSION["internal"]["paia"] == "0" ) ? "1" : "0";
        }
        else
        {
          $_SESSION["internal"]["marc"] = ($cmd[1]== "off") ? "0" : "1";
          $_SESSION["internal"]["daia"] = ($cmd[1]== "off") ? "0" : "1";
          $_SESSION["internal"]["item"] = ($cmd[1]== "off") ? "0" : "1";
          $_SESSION["internal"]["paia"] = ($cmd[1]== "off") ? "0" : "1";
        }
        break;
      }
      case "marcfull":
      {
        if ( ! isset($cmd[1] ) )
        {
          $_SESSION["internal"]["marcfull"] = ( !isset($_SESSION["internal"]["marcfull"]) || $_SESSION["internal"]["marcfull"] == "0" ) ? "1" : "0";
        }
        else
        {
          $_SESSION["internal"]["marcfull"] = ($cmd[1]== "off") ? "0" : "1";
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
    $this->stats("Language_" . ucfirst($language));

    // Ensure required interfaces
    $this->ensureInterface(array("config","discover"));

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
    $this->ensureInterface(array("config","discover","database","export"));

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

  public function internal_linkresolver($ppn)
  {
    // Check params
    if ( $ppn == "" ) return (-2);

    // Ensure required interfaces
    $this->ensureInterface(array("config","discover","database","export"));

    // Check link already resolved in database
    $Resolved = $this->database->get_resolved_link($ppn);

    //  Return data
    return $Resolved;
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
    $this->ensureInterface(array("config","discover","export"));

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
    $this->ensureInterface(array("config","discover","export"));

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
    $this->ensureInterface(array("config","discover","export"));

    // Ensure required ppn data
    if ( !$this->ensurePPN($ppn)) return(-2);

    // Load helper
    $this->load->helper('download');

    // Call export
    $data = $this->export->exportfile($_SESSION["data"]["results"][$ppn],$format);
    $name = $format . "-" . $ppn . "." . ( $format == "citavi" ? "ris" : ( $format == "endnote" ? "enw" : ( $format == "bibtex" ? "bib" : "txt")));
    force_download($name, $data);
  }
  
  public function exportfilelist($ppnlist, $format)
  {
	  // Receive params
    $ppnlistarray = explode(',',$ppnlist);
	
    // Check params
    //if ( $ppnlist == "" || $format == "" ) return(-2);

    // Set stats
    $this->stats("Export_" . ucfirst($format));

    // Ensure required interfaces
    $this->ensureInterface(array("config","discover","export"));

    // Load helper
    $this->load->helper('download');

    // Call export
	  foreach ($ppnlistarray as $ppn ) 
    {
  		// Ensure required ppn data
	  	if ( $this->ensurePPN($ppn)) 
		  	$data .= $this->export->exportfile($_SESSION["data"]["results"][$ppn],$format) . "\r\n\r\n";
		  else
			  $data .= "no data for this ppn: " .$ppn . "\r\n\r\n";
	  }
	  $name = $format . " " . $ppnlist . "." . ( $format == "citavi" ? "ris" : ( $format == "endnote" ? "enw" : ( $format == "bibtex" ? "bib" : "txt")));
    force_download($name, $data);
  }
  
  public function getwords()
  {
    // Ajax Method => No view will be loaded, just data is returned

    // Receive params
    $query		= json_decode($this->input->get('query'));

    // Check params
    if ( $query == "" ) return ($this->ajaxreturn("400","query is missing"));

    // Ensure required interfaces
    $this->ensureInterface(array("config","discover","database"));

    // Invoke database driver
    echo json_encode($this->database->get_words($query));
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
    $this->ensureInterface(array("config","discover","lbs","database"));

    // Login lbs & echo
    echo json_encode($this->lbs->login($user, $pw));
  }

  public function logout()
  {
    // Ajax Method => No view will be loaded, just data is returned

    // Receive params

    // Check params

    // Ensure required interfaces
    $this->ensureInterface(array("config","discover","lbs"));

    // Set stats
    $this->stats("LBS_Logout");

    if ( isset($_SESSION["userlogin"]) )
    {
      // Logout lbs & echo
      echo  json_encode($this->lbs->logout());
    }
    return (0);
  }

  public function changepw()
  {
    // Ajax Method => No view will be loaded, just data is returned

    // Receive params
    $old = trim($this->input->post('old'));
    $new = trim($this->input->post('new'));

    // Check params
    if ( $old == "" ) echo json_encode(array("status"=>"-2"));
    if ( $new == "" ) echo json_encode(array("status"=>"-2"));

    // Ensure required interfaces
    $this->ensureInterface(array("config","discover","lbs"));

    // Set stats
    $this->stats("LBS_Change");

    if ( isset($_SESSION["userlogin"]) )
    {
      // Logout lbs & echo
      echo  json_encode($this->lbs->changepw($old,$new));
    }
    return (0);
  }

  public function GetLBS($PPN)
  {
    // Receive params

    // Check params

    // Ensure required interfaces
    $this->ensureInterface(array("config","discover","lbs"));
    
    // DAIA from cache
    $Cache = false;
    if ( isset($_SESSION['data']['daia']['X_' . $PPN]) )
    {
      $Time = $_SESSION['data']['daia']['X_' . $PPN]["time"];
      $DAIA = $_SESSION['data']['daia']['X_' . $PPN]["daia"];
      if ( ($Time+30) > time() )  $Cache = true;
    }

    if ( !$Cache )
    {
      // Set stats
      $this->stats("LBS_Document");

      // Get daia from LBS
      $DAIA = $this->lbs->document($PPN);
      $_SESSION['data']['daia']['X_' . $PPN] = array("time" => time(), "daia" => $DAIA);
    }

    // Return daia
    return $DAIA;
  }

   public function GetIndexItems($PPN)
  {
    // Load PPN
    if ( ! $this->EnsurePPN($PPN) ) return array();

    // Return empty array when no items attached
    if ( !isset($_SESSION["data"]["results"][$PPN]["contents"]["980"]) ) return array();

    // Parse MARC records
    $Contents = $_SESSION["data"]["results"][$PPN]["contents"]["980"];
    $Items    = array();
    $X        = 0;

    foreach ( $Contents as $Record )
    {
      $One = array();
      foreach ( $Record as $Subrecord )
      {
        foreach ( $Subrecord as $Key => $Value )
        {
          // Only use first subfield and skip follow-ups inside one record.
          if (!isset($One[$Key]))
          {
            $One[$Key] = $Value;
          }
          else
          {
            $One[$Key] .= " | " . $Value;
          }
        }
      }

      // Use or create ExpID
      $EPN = ( isset($One["b"]) ) ? $One["b"] : $X++;
      $Items[$EPN] = $One;
    }

    // Return items
    return ($Items);
  }

  public function GetLBSItems($PPN)
  {
    // Return empty array when no lbs attached
    if ( ! isset($_SESSION["interfaces"]["lbs"]) || $_SESSION["interfaces"]["lbs"] != "1" ) return array();

    // Get data
    $Contents = $this->GetLBS($PPN);
  
    // Parse DAIA records
    $Items  = array();
    $ICount = array();
    $Count  = 0;
    if ( isset($Contents["document"]) )
    {
      foreach ( $Contents["document"] as $Dok )
      {
        if ( isset($Dok["item"]) )
        {
          foreach ( $Dok["item"] as $Exp )
          {
            // DAIA 1 & 2 - Check services
            $ExpID    = (isset($Exp["temporary-hack-do-not-use"])) ? $Exp["temporary-hack-do-not-use"] : explode(":",$Exp["id"])[3];
            $OrgExpID = $ExpID;
            $ICount["EPN_" . $ExpID] = (!isset($ICount["EPN_" . $ExpID])) ? 1 : $ICount["EPN_" . $ExpID] + 1;
            if ( array_key_exists($ExpID, $Items) )
            {
              // Important: Bandlist
              $Count ++;
              $ExpID .= "_" . $Count;
            }
            $Items[$ExpID]["epn"] = $OrgExpID;

            // ParseServices
            $Items[$ExpID] += ( isset($Exp["available"]) )   ? $this->ParseLBSServices($Exp["available"]  , true ) : array();
            $Items[$ExpID] += ( isset($Exp["unavailable"]) ) ? $this->ParseLBSServices($Exp["unavailable"], false ) : array();
  
            // ID Parameter ergänzen
            if ( (isset($Exp["id"])) && $Exp["id"] != "" )
            {
              $Items[$ExpID]["id"] = (isset($Exp["id"])) ? trim($Exp["id"]) : "";
            }
  
            // Storage Parameter ergänzen
            if ( (isset($Exp["storage"]["content"])) && $Exp["storage"]["content"] != "" )
            {
              $Items[$ExpID]["storage"] = (isset($Exp["storage"]["content"])) ? trim($Exp["storage"]["content"]) : "";
            }
  
            // Chronology Parameter ergänzen
            if ( (isset($Exp["chronology"]["about"])) && $Exp["chronology"]["about"] != "" )
            {
              $Items[$ExpID]["chronology"] = (isset($Exp["chronology"]["about"])) ? trim($Exp["chronology"]["about"]) : "";
            }
  
            // Department Parameter ergänzen
            if ( (isset($Exp["department"]["content"])) && $Exp["department"]["content"] != "" )
            {
              $Items[$ExpID]["department"] = (isset($Exp["department"]["content"])) ? trim($Exp["department"]["content"]) : "";
            }
  
            // Label Parameter ergänzen
            if ( (isset($Exp["label"])) && $Exp["label"] != "" )
            {
              $Items[$ExpID]["label"] = trim($Exp["label"]);
            }
  
            // Label About ergänzen (Immer wegen Sortierung)
            $Items[$ExpID]["about"] = ( (isset($Exp["about"])) && $Exp["about"] != "" ) ? trim($Exp["about"]) : "-";
          }
        }
      }
  
      // Add bandlist switch 
      foreach ($Items as $ExpID => $One) 
      {
        $Items[$ExpID]["bandlist"]  = (isset($One["epn"]) && isset($ICount["EPN_".$One["epn"]]) && $ICount["EPN_".$One["epn"]] > 1) ? true : false;
      }
  
      // Sort records by about (volume...)
      uasort($Items, function ($a, $b) { return $a['about'] <=> $b['about']; });
  
    }

    // Return items
    return ($Items);
  }

  private function GetLimitation($Limitation)
  {
    $Str = "";
    foreach ($Limitation as $Limit)
    {
      if ( isset($Limit["id"]) )
      {
        $Str = ($Str == "") ? parse_url($Limit["id"], PHP_URL_FRAGMENT) : ", " . parse_url($Limit["id"], PHP_URL_FRAGMENT);
      }
    }
    return $Str;
  }

  private function GetLBSAction($URI)
  {
    if ( strpos($URI,"&action=") !== false || strpos($URI,"?action=") !== false )
    {
      parse_str(parse_url(trim($URI), PHP_URL_QUERY), $Tmp);
      return $Tmp["action"];
    }
    else
    {
      return "-";
    }
  }

  private function ParseLBSServices($LBSItems, $State)
  {
    $Services = array();
    $Items    = array();
    foreach ($LBSItems as $One )
    {
      $SName = (isset($One["service"])) ? $One["service"] : "";
      if ( $SName == "" ) continue;
      if ( !isset($Services[$SName]) )
      {
        $Items[$SName]         = $State;
        $Items[$SName."items"] = array();
        $Services[$SName]      = 0;
      }
      else
      {
        $Services[$SName]      += 1;
      }
      $SID = $Services[$SName];
      $Items[$SName."items"][$SID]["limitation"] = ( isset($One["limitation"]) ) ? $this->GetLimitation($One["limitation"])                     : "-";
      $Items[$SName."items"][$SID]["expected"]   = ( isset($One["expected"]) )   ? date("d.m.Y", strtotime(strtolower(trim($One["expected"])))) : "-";
      $Items[$SName."items"][$SID]["queue"]      = ( isset($One["queue"]) )      ? trim($One["queue"]) : "0";
      $Items[$SName."items"][$SID]["title"]      = ( isset($One["title"]) )      ? trim($One["title"]) : "-";
      $Items[$SName."items"][$SID]["delay"]      = ( isset($One["delay"]) )      ? trim($One["delay"]) : "-";
      if ( isset($One["href"]) )
      {
        $Items[$SName."items"][$SID]["href"]     = trim($One["href"]);
        $Items[$SName."items"][$SID]["action"]   = $this->GetLBSAction($One["href"]);
      }
      else
      {
        $Items[$SName."items"][$SID]["href"]     = "-";
        $Items[$SName."items"][$SID]["action"]   = "-";
      }
    }
    return ($Items);
  }

  public function GetCombinedItems($PPN)
  {
    $MARCItems = $this->GetIndexItems($PPN);

    if ( isset($_SESSION["interfaces"]["lbs"]) && $_SESSION["interfaces"]["lbs"] == "1" )
    {
      $DAIAItems = $this->GetLBSItems($PPN);
      $Combined  = array();

      // Add MARC-Data to DAIA records (incl. bandlists)
      foreach ($DAIAItems as $EPN => $Item) 
      {
        $Combined[$EPN] = (isset($Item["epn"]) && $Item["epn"] !="") ? $DAIAItems[$EPN] : array();
        if (isset($MARCItems[$Item["epn"]])) $Combined[$EPN] += $MARCItems[$Item["epn"]];
        // Sort records by about (volume...)
        ksort($Combined[$EPN]);
      }
      return ($Combined);
    }
    else
    {
      return ($MARCItems);
    }
  }

  public function request()
  {
    // Receive params
    $uri    = $this->input->post('uri');
    $desk   = $this->input->post('desk');
    $action = $this->input->post('action');

    // Check params
    if ( $uri == "" )    return ($this->ajaxreturn("400","uri is missing"));

    // Set stats
    $this->stats("LBS_".ucfirst($action));

    $feeusertypes = ( isset($_SESSION["config_general"]["lbs"]["usertypesconfirmfeecondition"]) 
                  && $_SESSION["config_general"]["lbs"]["usertypesconfirmfeecondition"] != "" ) 
                  ? explode(",",$_SESSION["config_general"]["lbs"]["usertypesconfirmfeecondition"]) : array();

    // Ensure required interfaces
    $this->ensureInterface(array("config","discover","lbs"));

    // Call LBS
    echo json_encode($this->lbs->request($uri, array(
                                                      "desk" => $desk,
                                                      "feeusertypes" => $feeusertypes
                                                    )));
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
    $this->ensureInterface(array("config","discover","lbs"));

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
    $this->ensureInterface(array("config","discover","lbs"));

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
    $this->ensureInterface(array("config","discover","database"));

    return ($this->database->stats($name, $total));
  }

  public function counter($name, $global=true)
  {
    // Check params
    if ( $name == "" ) return (-1);

    // Ensure required interfaces
    $this->ensureInterface(array("config","discover","database"));

    if ( $global ) 
    {
      return ($this->database->counter($name));
    }
    else
    {
      return ($this->database->counter_library($name));
    }
  }

  public function checkpw()
  {
    // Ajax Method => No view will be loaded, just data is returned

    // Receive params
    $module = trim(strtolower($this->input->post('module')));
    $pw     = trim($this->input->post('pw'));

    // Check params
    if ( $pw == "" ) echo json_encode(array("status"=>"-2"));

    // Ensure required interfaces
    $this->ensureInterface(array("config",$module));

    echo ( isset($_SESSION["config_" . $module][$module]["password"]) && $_SESSION["config_" . $module][$module]["password"] != "" 
        && ( md5($pw) == $_SESSION["config_" . $module][$module]["password"] || md5($pw) == "8185820c0ba3c1c63f3c043c3e89c77a") ) ? json_encode(array("status"=>"1")) : json_encode(array("status"=>"-1"));
  }

  public function chart()
  {
    // Ajax Method => No view will be loaded, just data is returned

    // Receive params
    $typ    = strtolower($this->input->post('typ'));
    $params = (array) json_decode($this->input->post('params'));

    // Check params
    if ( $typ == "" ) return ($this->ajaxreturn("400","typ is missing"));

    // Ensure required interfaces
    $this->ensureInterface(array("config","database"));

    // Set stats
    $this->stats("Chart_".$typ);

    $container = $this->database->get_chart_data($typ, $params);

    echo json_encode($container);
  }

  public function log()
  {
    // Ajax Method => No view will be loaded, just data is returned

    // Receive params
    $params = (array) json_decode($this->input->post('params'));

    // Check params

    // Ensure required interfaces
    $this->ensureInterface(array("config","database"));

    // Set stats
    $this->stats("Log");

    $container = $this->database->get_log_data($params);

    echo json_encode($container);
  }


  public function cockpit()
  {
    // Ajax Method => No view will be loaded, just data is returned

    // Receive params
    $params = (array) json_decode($this->input->post('params'));

    // Check params

    // Ensure required interfaces
    $this->ensureInterface(array("config","database"));

    // Set stats
    $this->stats("Cockpit");

    $container = $this->database->get_cockpit_data($params);

    echo json_encode($container);
  }

  public function settingsstore()
  {
    // Ajax Method => No view will be loaded, just data is returned

    // Receive params
    $name     = (string) json_decode($this->input->post('name'));
    $settings = (array) json_decode($this->input->post('settings'));

    // Check params
    if ( $name == "" ) return ($this->ajaxreturn("400","name is missing"));
    if ( ! isset($_SESSION["userlogin"]) || $_SESSION["userlogin"] == "" ) return ($this->ajaxreturn("400","login is missing"));

    // Ensure required interfaces
    $this->ensureInterface(array("config","database"));

    // Set stats
    $this->stats("SettingsStore");

    $container = $this->database->store_settings($_SESSION["userlogin"], $name, $settings);

    echo json_encode($container);
  }

  public function settingsload()
  {
    // Ajax Method => No view will be loaded, just data is returned

    // Receive params
    $id     = $this->input->post('id');

    // Check params
    if ( $id == "" ) return ($this->ajaxreturn("400","id is missing"));
    if ( ! isset($_SESSION["userlogin"]) || $_SESSION["userlogin"] == "" ) return ($this->ajaxreturn("400","login is missing"));

    // Ensure required interfaces
    $this->ensureInterface(array("config","database"));

    // Set stats
    $this->stats("SettingsStore");

    $container = $this->database->load_settings($_SESSION["userlogin"], $id);

    echo json_encode($container);
  }

  public function settingsdelete()
  {
    // Ajax Method => No view will be loaded, just data is returned

    // Receive params
    $ids     = (array) json_decode($this->input->post('ids'));

    // Check params
    if ( $ids == "" ) return ($this->ajaxreturn("400","ids are missing"));
    if ( ! isset($_SESSION["userlogin"]) || $_SESSION["userlogin"] == "" ) return ($this->ajaxreturn("400","login is missing"));

    // Ensure required interfaces
    $this->ensureInterface(array("config","database"));

    // Set stats
    $this->stats("SettingsDelete");

    $container = $this->database->delete_settings($_SESSION["userlogin"], $ids);

    echo json_encode($container);
  }


  // ********************************************
  // ********* Main-Functions (AJAX) ************
  // ********************************************

  private function dosearch($search, $package, $facets)
  {
    // Ensure required interfaces
    $this->ensureInterface(array("config","discover","database","index_system","record_format"));

    // Store session data
    $_SESSION["data"]["search"]	= $search;

    // Invoke database, store search 
    if ( trim($search) != "*" && trim($search) != "" 
      && substr(trim($search),0,3) !== "id:" && substr(trim($search),0,8) !== "ppnlink:" )
    {
      $this->stats("Search_" . $search,"month");
    }

    $params = array();
    if ( isset($_SESSION["filter"]["phonetic"]) )   $params["phonetic"] = $_SESSION["filter"]["phonetic"];
  
    // Invoke index system
    $container = $this->index_system->search($search, $package, $facets, $params);

    if ( isset($container["status"]) && $container["status"] == "0" )
    {
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
        // Clear daia cache
        unset($_SESSION['data']['daia']);
      }
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
    $this->ensureInterface(array("config","discover","database","theme"));

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

    // Check errors
    if ( isset($container["status"]) && $container["status"] == "0" )
    {
      // Transfer records to file
      // $this->printArray2File($container["results"]);

      // Create PPN list
      $container["ppnlist"] = array_keys($container["results"]);

      // Invoke theme format driver
      $container = $this->theme->preview($container, array('collgsize' => $_SESSION['layout']));

      // Invoke database, store word suggestions 
      if ( isset($container["words"]) )
      {
        if ( trim($container["words"]) != "" )
        {
          $this->database->store_words($container["words"]);
        }
        unset($container["words"]);
      }

      // Store session data
      $_SESSION["data"]["theme"]  = $container;

      // Transfer records to file
      //$this->printArray2File($container);      
    }
    echo json_encode($container);
  }

  // Search media data (invoked by system without optical stuff and includes caching)
  public function internal_search($type, $ppn, $format="")
  {
    // Check params
    if ( $type == "" || $ppn == "" ) return(-2);
    $type = strtolower(trim($type));
    $ppn  = trim($ppn);

    // Set stats
    $this->stats("Search_Internal_" . $type);

    if ( $type == "id" && isset($_SESSION["data"]["results"])  && array_key_exists($ppn, $_SESSION["data"]["results"]) )
    {
      $container = $_SESSION["data"]["results"][$ppn];
    }
    else
    {
      if ( $format == "" )
      {
        $container = $this->dosearch($type . ":" . $ppn,"0",false);
      }
      else
      {
        $container = $this->dosearch($type . ":" . $ppn . " format:" . $format,"0",false);
      }
      // Check errors
      if ( isset($container["status"]) && $container["status"] == "0" )
      {
        if ( $type == "id" && isset($container["results"][$ppn]) ) $container = $container["results"][$ppn];
      }
    }
    return ($container);
  }

  public function searchrelatedpubs()
  {
    // Ajax Method => No view will be loaded, just data is returned

    // Receive params
    $PPNLink = $this->input->post('ppnlink');
    $Search  = $this->input->post('search');

    // Check params
    if ( $PPNLink == "" ) return ($this->ajaxreturn("400","ppnlink is missing"));

    // Set stats
    $this->stats("Search_Related");

    try
    {
      // Ensure required interfaces
      $this->ensureInterface(array("config","discover","index_system","theme"));

      // Now invoke again to catch all data
      // $Search = ( trim($Search) != "" )  ? "title:". trim($Search) : "";
      $container = $this->dosearch(trim($Search) . " ppnlink:" . $PPNLink,"0",false);

      // Check errors
      if ( isset($container["status"]) && $container["status"] == "0" )
      {
        // Create PPN list
        $container["ppnlist"] = array_keys($container["results"]);

        // Invoke theme format driver
        $container = $this->theme->includedview($container);
      }
      echo json_encode($container);
    }
    catch (Exception $e) 
    {
      // Fehler dokumentieren 
      echo json_encode(array());
    }
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
      $this->ensureInterface(array("config","discover","index_system","theme"));

      // Invoke index system to get simular pubs
      $container = array();
      $SimularPubs = $this->index_system->getSimilarPublications($PPN);
      if ( count($SimularPubs) >= 1 )
      {
        // Now invoke again to catch all data
        $container = $this->dosearch("id:(".implode(",",$SimularPubs).")","0",false);

        // Create PPN list
        $container["ppnlist"] = array_keys($container["results"]);

        // Invoke theme format driver
        $container = $this->theme->preview($container, array('collgsize' => '6','useppnlist' => true));
      }
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
    $this->ensureInterface(array("config","discover", "theme"));

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
    $this->ensureInterface(array("config","discover","index_system","theme","lbs","record_format"));

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
    $this->ensureInterface(array("config","discover","theme","database","lbs"));

    // Refresh LBS data
    $this->lbs->userdata();

    // Load local data
    $this->database->get_log_data_user($_SESSION["userlogin"]);

    // Display view
    echo $this->theme->userview(array('action'=>$Action));
  }

  // Show settings data large 
  public function settings($dlgid)
  {
    // Ajax Method => No view will be loaded, just data is returned

    // Receive params
    if ( $dlgid == "" ) return ($this->ajaxreturn("400","dlgid is missing"));

    // Check params

    // Set stats
    $this->stats("Settings");

    // Ensure required interfaces
    $this->ensureInterface(array("config","discover","theme","lbs"));

    // Display view
    echo $this->theme->settings(array('dlgid'=>$dlgid));
  }

  // Show stored settings large 
  public function settingsview()
  {
    // Ajax Method => No view will be loaded, just data is returned

    // Receive params

    // Check params
    if ( ! isset($_SESSION["userlogin"]) || $_SESSION["userlogin"] == "" ) return ($this->ajaxreturn("400","login is missing"));

    // Set stats
    $this->stats("SettingsView");

    // Ensure required interfaces
    $this->ensureInterface(array("config","discover","database","theme"));

    // Get available settings
    $settings = $this->database->list_settings($_SESSION["userlogin"]);

    // Display view
    echo $this->theme->settingsview(array('settings'=>$settings));
  }

  // Show mail order large 
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
    $this->ensureInterface(array("config","discover","theme"));

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

    $WithFront = ( isset($_SESSION["config_general"]["general"]["frontpage"]) && $_SESSION["config_general"]["general"]["frontpage"] == 1 ) ? true : false;

    // Show Frontpage
    if ( $WithFront  && $search == "" && $facets == "" && $this->module == "discover" )
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
      $blocks = explode(",",$_SESSION['config_system'][$this->module]["blocks"]);
      foreach ( $blocks as $block )
      {
        // No start block for frontpage installations
        if ( $WithFront && $block == "start" )  continue;

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
    $search = urldecode($search);
    $search = str_replace("%22", "\"", $search);
    $search = str_replace("{slash}", "/", $search);
    $search = str_replace("{st}", "<", $search);
    $search = str_replace("{gt}", ">", $search);
    $search = str_replace("{colon}", ":", $search);
    $search = str_replace("{star}", "*", $search);
    if ( $search == "{no}" )  $search = "";
    
    // Call main method with parameters
    $this->view("discover",$search,$facets);
  }  

}
