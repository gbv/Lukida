<?php

class Bootstrap extends General
{
  protected $CI;

  public function __construct()
  {
    // Assign the CodeIgniter super-object
    $this->CI =& get_instance();
  }

  public function preview($container, $params)
  {
    // Check, if modul is available
    if ( ! file_exists(KERNELFORMATS . "preview/" . $_SESSION["config_discover"]["preview"]["preview"] .'.php'))
    {
      // Whoops, we don't have a page for that!
      show_404();
    }

    // Set Format Part Variables
    $Header	= (isset($_SESSION["config_discover"]["preview"]["previewheader"]) && $_SESSION["config_discover"]["preview"]["previewheader"] == "1" ) ? true : false;
    $Body		= (isset($_SESSION["config_discover"]["preview"]["previewbody"])   && $_SESSION["config_discover"]["preview"]["previewbody"]   == "1" ) ? true : false;
    $Footer	= (isset($_SESSION["config_discover"]["preview"]["previewfooter"]) && $_SESSION["config_discover"]["preview"]["previewfooter"] == "1" ) ? true : false;

    // Variablen initialisieren
    $this->NR = $container["start"];
    $this->collgsize = $params["collgsize"];
    $PPNList = (isset($params["useppnlist"])) ? $params["useppnlist"] : false;
    switch ( $this->collgsize )
    {
      case "12":
      {
        $columns = "col-xs-12 col-sm-12 col-md-12 col-lg-12";
        break;
      }
      case "6":
      {
        $columns = "col-xs-12 col-sm-12 col-md-12 col-lg-6";
        break;
      }
      case "4":
      {
        $columns = "col-xs-12 col-sm-12 col-md-6 col-lg-4";
        break;
      }
      default:
      case "3":
      {
        $columns = "col-xs-12 col-sm-6 col-md-4 col-lg-3";
        break;
      }
    }

    $Ausgabe = "";
    foreach ( $container["results"] as $Erg )
    {
      $this->NR++;
      $this->PPN      = $Erg["id"];
      $this->contents = $Erg["contents"];
      $this->format   = $Erg["format"];
      $this->cover    = $Erg["cover"];
      $this->isbn     = $Erg["isbn"];
      $this->pretty   = $Erg;

      $this->words .= " " . $this->pretty["title"] . " " . implode(" ",$this->pretty["author"]);

      // $this->CI->printArray2File($_SESSION["data"]["results"][$this->PPN]);
 
      $Output = "<div id='" . $this->PPN . "' class='medium " . $columns . "'><div class='panel'>";
      if ( $PPNList )
      {
        $Output .= "<a href='javascript:$.open_fullview(\"" . $this->PPN . "\",[\"" . implode("\",\"",$container["ppnlist"]) . "\"]);'>";
      }
      else
      {
        $Output .= "<a href='javascript:$.open_fullview(\"" . $this->PPN . "\");'>";
      }
      include(KERNELFORMATS . "preview/" . $_SESSION["config_discover"]["preview"]["preview"] .'.php');
      $Output .= "</a></div></div>";
      $Ausgabe .= $Output;
    }

    // Transport-Container beladen
    $container["results"] = $Ausgabe;
    $container["words"] = $this->words;

    if ( true )
    {
      // Facetten für Anzeige filtern
      $Filter  = array();

      // Online
      $Count = 0;
      while ( isset($container["facets"]["remote_bool"][$Count] ) )
      {
        if ( $container["facets"]["remote_bool"][$Count+1] > 0 )
        {
          $Filter["online"][$container["facets"]["remote_bool"][$Count]] = $container["facets"]["remote_bool"][$Count+1];
        }
        $Count += 2;
      }
      if ( ! isset($Filter["online"]["false"]) )
      {
        $Filter["online"]["false"]  = 0;
      }
      if ( ! isset($Filter["online"]["true"]) )
      {
        $Filter["online"]["true"]  = 0;
      }
      $Filter["online"]["total"] = $Filter["online"]["false"] + $Filter["online"]["true"];

      // Format
      $Count = 0;
      while ( isset($container["facets"]["format_phy_str_mv"][$Count] ) )
      {
        if ( $container["facets"]["format_phy_str_mv"][$Count+1] > 0 )
        {
          $Filter["format"][$container["facets"]["format_phy_str_mv"][$Count]] = $container["facets"]["format_phy_str_mv"][$Count+1];
        }
        $Count += 2;
      }

      // Jahr
      //$Count = 0;
      //while ( isset($container["facets"]["publishDate"][$Count] ) )
      //{
      //  if ( ( $MinJahr == "" || $MinJahr > $container["facets"]["publishDate"][$Count] ) && ( $container["facets"]["publishDate"][$Count+1] > 0 ) )
      //  {
      //    $MinJahr = $container["facets"]["publishDate"][$Count];
      //  }
      //  if ( ( $MaxJahr == "" || $MaxJahr < $container["facets"]["publishDate"][$Count] ) && ( $container["facets"]["publishDate"][$Count+1] > 0 ) )
      //  {
      //    $MaxJahr = $container["facets"]["publishDate"][$Count];
      //  }
      //  $Count += 2;
      //}

      // Facetten für Anzeige vorbereiten
      $container["yearmin"] = isset($container["stats"]["publishDate"]["min"]) ? $container["stats"]["publishDate"]["min"] : "1900";
      $container["yearmax"] = isset($container["stats"]["publishDate"]["max"]) ? $container["stats"]["publishDate"]["max"] : date("Y");
      $container["online"]  = (isset($Filter["online"])) ? $Filter["online"] : "";
      $container["formats"] = (isset($Filter["format"])) ? $Filter["format"] : "";

    }

    // Transport-Container verschicken
    return ( $container );
  }
  
  public function fullview ( $params )
  {
    // Check params
    if ( ! $this->ParamExits("param[ppn]", $params,"ppn") ) return false;
    if ( ! $this->ParamExits("param[dlgid]", $params,"dlgid") ) return false;
    if ( ! $this->FileExits(KERNELFORMATS . "fullview/" . $_SESSION["config_discover"]["fullview"]["fullview"] . ".php") ) return false;

    // Prepare variables for loaded code
    $this->PPN               = $params['ppn'];
    $this->dlgid             = $params['dlgid'];
    $this->medium            = $_SESSION["data"]["results"][$this->PPN];
    $this->contents          = $this->medium["contents"];
    $this->proofofpossession = $this->medium["proofofpossession"];
    $this->leader            = $this->medium["leader"];
    $this->format            = $this->medium["format"];
    $this->online            = $this->medium["online"];
    $this->ppnlink           = $this->medium["ppnlink"];
    $this->cover             = $this->medium["cover"];
    $this->catalogues        = $this->medium["catalogues"];
    $this->isbn              = $this->medium["isbn"];
    $_SESSION["data"]["results"][$this->PPN] += $this->SetContents("fullview");
    $this->pretty     = $_SESSION["data"]["results"][$this->PPN];

    // $this->CI->printArray2File($_SESSION["data"]["results"][$this->PPN]);

    // Start Output
    $Output = $this->header();

    // Load module inside div
    $Output = "<div id='fullview'>";
    include(KERNELFORMATS . "fullview/" . $_SESSION["config_discover"]["fullview"]["fullview"] .'.php');
    $Output .= "</div>";

    // End Output
    $Output .= $this->footer();

    // Return Output
    return ( $Output );
  }

  public function userview ( $params )
  {
    // Check Session & Parameters
    if ( ! $this->ParamExits("param[action]", $params,"action") ) return false;
    if ( ! $this->ParamExits("_SESSION[config_discover][userview][userview]",$_SESSION,"config_discover","userview","userview") ) return false;
    if ( ! $this->FileExits(KERNELFORMATS . "userview/" . $_SESSION["config_discover"]["userview"]["userview"] . ".php") ) return false;

    // Prepare variables for loaded code
    $Action = $params['action'];

    // Start Output
    $Output = $this->header();

    // Load module inside div
    $Output = "<div id='userview'>";
    include(KERNELFORMATS . "userview/" . $_SESSION["config_discover"]["userview"]["userview"] .'.php');
    $Output .= "</div>";

    // End Output
    $Output .= $this->footer();

    // Return Output
    return ( $Output );
  }

  public function assistant( $params )
  {
    // Check Session & Parameters
    if ( ! $this->ParamExits("_SESSION[config_discover][assistant][assistant]",$_SESSION,"config_discover","userview","userview") ) return false;
    if ( ! $this->FileExits(KERNELFORMATS . "assistant/" . $_SESSION["config_discover"]["assistant"]["assistant"] . ".php") ) return false;
    if ( ! $this->ParamExits("param[dlgid]", $params,"dlgid") ) return false;

    // Prepare variables for loaded code
    $this->dlgid      = $params['dlgid'];

    // Start Output
    $Output = $this->header();

    // Load module inside div
    $Output = "<div id='assistant'>";
    include(KERNELFORMATS . "assistant/" . $_SESSION["config_discover"]["assistant"]["assistant"] .'.php');
    $Output .= "</div>";

    // End Output
    $Output .= $this->footer();

    // Return Output
    return ( $Output );
  }

  public function mailorderview ( $params )
  {
    // Check Session & Parameters
    if ( ! $this->ParamExits("param[ppn]", $params,"ppn") ) return false;
    if ( ! $this->ParamExits("_SESSION[config_discover][mailorderview][mailorderview]",$_SESSION,"config_discover","mailorderview","mailorderview") ) return false;
    if ( ! $this->FileExits(KERNELFORMATS . "mailorderview/" . $_SESSION["config_discover"]["mailorderview"]["mailorderview"] . ".php") ) return false;

    // Prepare variables for loaded code
    $this->PPN        = $params['ppn'];
    $this->exemplar   = $params['exemplar'];
    $this->medium     = $_SESSION["data"]["results"][$this->PPN];
    $this->contents   = $this->medium["contents"];
    $this->leader     = $this->medium["leader"];
    $this->format     = $this->medium["format"];
    $this->ppnlink    = $this->medium["ppnlink"];
    $this->cover      = $this->medium["cover"];
    $this->catalogues = $this->medium["catalogues"];
    $this->isbn       = $this->medium["isbn"];
    $this->pretty     = $_SESSION["data"]["results"][$this->PPN];

    // Start Output
    $Output = $this->header();

    // Load module inside div
    $Output = "<div id='mailorderview'>";
    include(KERNELFORMATS . "mailorderview/" . $_SESSION["config_discover"]["mailorderview"]["mailorderview"] .'.php');
    $Output .= "</div>";

    // End Output
    $Output .= $this->footer();

    // Return Output
    return ( $Output );
  }
}
