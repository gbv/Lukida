<?php

class Mysql extends General
{
  protected $CI;

  public function __construct()
  {
    // Assign the CodeIgniter super-object
    $this->CI =& get_instance();
  }

  public function code2text($code)
  {
    // Clear & check code
    $code = trim($code);
    if ( $code == "" )	return (-1);
    $code = strtoupper(str_replace(' ', '', $code));

    // Return code directly (without accessing the database again), if it has alrady been loaded
    if ( $_SESSION["language"] == "ger" )
    {
      if ( array_key_exists($code,$_SESSION["translation_ger"]) )
      {
        return $_SESSION["translation_ger"][$code];
      }
    }
    else
    {
      if ( array_key_exists($code,$_SESSION["translation_eng"]) )
      {
        return $_SESSION["translation_eng"][$code];
      }
    }

    // Then check if there is a library specific code
    if ( isset($_SESSION["iln"]) )
    {
      $this->CI->db->reset_query();
      if ( $_SESSION["language"] == "ger" )
      {
        $this->CI->db->select('german');
      }
      else
      {
        $this->CI->db->select('english');
      }
      $this->CI->db->from('translation_library');
      $this->CI->db->where('shortcut', $code);
      $this->CI->db->where('iln', $_SESSION["iln"]);
      $this->CI->db->limit(1);
      $results = $this->CI->db->get();
    
      if ($results->num_rows() == 1)
      {
        // Return library specific code found
        if ( $_SESSION["language"] == "ger" )
        {
          $Value = $results->row()->german;
          $_SESSION["translation_ger"][$code]	= $Value;
          return $Value;
        }
        else
        {
          $Value = $results->row()->english;
          $_SESSION["translation_eng"][$code]	= $Value;
          return $Value;
        }
      }
    }

    // Finally check if there is a general code
    $this->CI->db->reset_query();
    if ( $_SESSION["language"] == "ger" )
    {
      $this->CI->db->select('german');
    }
    else
    {
      $this->CI->db->select('english');
    }
    $this->CI->db->from('translation');
    $this->CI->db->where('shortcut', $code);
    $this->CI->db->limit(1);
    $results = $this->CI->db->get();

    // No Code found - return shorthand code
    if ($results->num_rows() != 1)	return ($code);

    // Return general code found
    if ( $_SESSION["language"] == "ger" )
    {
      $Value = $results->row()->german;
      $_SESSION["translation_ger"][$code] = $Value;
      return $Value;
    }
    else
    {
      $Value = $results->row()->english;
      $_SESSION["translation_eng"][$code] = $Value;
      return $Value;
    }
  }

  public function english_countrycode2speech($code)
  {
    $code = trim($code);
    if ( $code == "" )	return (-1);
    if ( strlen($code) != 3 )	return (-2);

    // Return code directly (without accessing the database again), if it has alrady been loaded
    if ( $_SESSION["language"] == "ger" )
    {
      if ( array_key_exists($code,$_SESSION['speech_ger']) )
      {
        return $_SESSION['speech_ger'][$code];
      }
    }
    else
    {
      if ( array_key_exists($code,$_SESSION['speech_eng']) )
      {
        return $_SESSION['speech_eng'][$code];
      }
    }   
    
    $this->CI->db->reset_query();
    if ( $_SESSION["language"] == "ger" )
    {
      $this->CI->db->select('german');
    }
    else
    {
      $this->CI->db->select('english');
    }
    $this->CI->db->from('code_country');
    $this->CI->db->like('code_639-2', $code);
    $this->CI->db->limit(1);
    $results = $this->CI->db->get();

    if ($results->num_rows() != 1)	return (-3);

    if ( $_SESSION["language"] == "ger" )
    {
      $_SESSION['speech_ger'][$code]	= $results->row()->german;
      return ($results->row()->german);
    }
    else
    {
      $_SESSION['speech_eng'][$code]	= $results->row()->english;
      return ($results->row()->english);
    }
  }

  public function system_language($language)
  {
    $language = trim($language);
    if ( $language == "" )	return (-1);

    $this->CI->db->reset_query();
    $this->CI->db->select('translation.shortcut');
    $this->CI->db->select('translation.'.$language);
    $this->CI->db->from('translation');
    if ( isset($_SESSION["iln"]) && $_SESSION["iln"]  != "" )
    {
      $this->CI->db->select('translation_library.'.$language.' as lib_'.$language);
      $this->CI->db->join('translation_library', 'translation_library.shortcut = translation.shortcut and translation_library.iln = "' . $_SESSION["iln"] . '"','left');
    }
    $this->CI->db->where('translation.init','1');

    $results = $this->CI->db->get();

    if ($results->num_rows() < 1)	return (-3);

    $Data = array();
    foreach ($results->result_array() as $row)
    {
      if ( isset($row['lib_'.$language]) && $row['lib_'.$language] != "" )
      {
        $Data[$row['shortcut']]	= $row['lib_'.$language];
      }
      else
      {
        $Data[$row['shortcut']]	= $row[$language];
      }
    }
    return ($Data);
  }
  
  public function store_words($words)
  {      
    $this->CI->db->reset_query();
    $this->CI->db->query("insert into words_unsolved (worte, status, datumzeit) values ('" . $this->CI->db->escape_str($words) . "', 0, now())");
  
    return 0;
  }

  public function get_words($phrase)
  {    
    $Worte = explode(" ", $phrase);
    $Wort  = array_pop($Worte);
    $Ready = implode(" ", $Worte);

    if ( substr($Wort,0,1) == "+" || substr($Wort,0,1) == "-" )
    {
      $AddOn = substr($Wort,0,1);
      $Wort  = substr($Wort,1);
    }
    else
    {
      $AddOn = "";
    }
    
    $this->CI->db->reset_query();
    $this->CI->db->select('wort');
    $this->CI->db->from('words');
    $this->CI->db->like('wort', $Wort, 'after');
    $this->CI->db->order_by('anzahl', 'DESC');
    $this->CI->db->order_by('datumzeit', 'DESC');
    $this->CI->db->limit(6);
    $results = $this->CI->db->get();

    $Data = array();
    foreach ($results->result_array() as $row)
    {
      $Data[] = $Ready . " " . $AddOn . $row['wort'];
    }
    return ($Data);
  }

  /**
  * System Counter Function
  *
  * @author  Alexander Karim <Alexander.Karim@gbv.de>
  */
  public function counter($name)
  {
    if ( $name == "" )  return (-1);

    $this->CI->db->reset_query();
    $this->CI->db->query("insert into counter (name, value) values ('" . $name . "', 0) ON DUPLICATE KEY UPDATE value=value+1");

    $this->CI->db->reset_query();
    $this->CI->db->select('value');
    $this->CI->db->from('counter');
    $this->CI->db->where('name', $name);
    return ($this->CI->db->get()->row()->value);
  }

  /**
  * Library specific counter function
  *
  * @author  Alexander Karim <Alexander.Karim@gbv.de>
  */
  public function counter_library($name)
  {
    $iln = ( isset($_SESSION["iln"]) ) ? $_SESSION["iln"] : "";
    if ( $name == "" || $iln == "" )  return (-1);

    $this->CI->db->reset_query();
    $this->CI->db->query("insert into counter_library (name, iln, value) values ('" . $name . "'," . $iln . ", 0) ON DUPLICATE KEY UPDATE value=value+1");

    $this->CI->db->reset_query();
    $this->CI->db->select('value');
    $this->CI->db->from('counter_library');
    $this->CI->db->where('name', $name);
    $this->CI->db->where('iln', $iln);
    return ($this->CI->db->get()->row()->value);
  }

  public function stats($name, $total = "day")
  {
    $iln = ( isset($_SESSION["iln"]) ) ? $_SESSION["iln"] : "";
    if ( $name == "" || $iln == "" )  return (-1);

    $this->CI->db->reset_query();
    switch ($total)
    {
      case "year":
      {
        // Storage per year / month
        $Month = date('m');
        $this->CI->db->query("insert into stats_year_library (iln, area, year, month_" . $Month . ") values (" . $iln . ", '" . $name . "'," . date('Y') . ", 1) ON DUPLICATE KEY UPDATE month_" . $Month . "=month_" . $Month . "+1");
        break;
      }
      case "month":
      {
        // Storage per month / day
        $Day = date('d');
        $this->CI->db->query("insert into stats_month_library (iln, area, month, day_" . $Day . ") values (" . $iln . ", '" . $this->CI->db->escape_str($name) . "','" . date('Y-m') . "', 1) ON DUPLICATE KEY UPDATE day_" . $Day . "=day_" . $Day . "+1");
        break;
      }
      case "day":
      default:
      {
        // Storage per day / hour
        $Hour = date('H');
        $this->CI->db->query("insert into stats_day_library (iln, area, day, hour_" . $Hour . ") values (" . $iln . ", '" . $name . "','" . date("y-m-d") . "', 1) ON DUPLICATE KEY UPDATE hour_" . $Hour . "=hour_" . $Hour . "+1");
      }
    }
    return (0);
  }

  public function get_resolved_link($ppn)
  {
    $iln = ( isset($_SESSION["iln"]) ) ? $_SESSION["iln"] : "";
    if ( $ppn == "" || $iln == "" )  return (-1);

    $this->CI->db->reset_query();
    $this->CI->db->select('resolved');
    $this->CI->db->from('links_resolved_library');
    $this->CI->db->where('iln',$iln);
    $this->CI->db->where('ppn',$ppn);
    $this->CI->db->where('checkdate >=', date("Y-m-d", mktime(0, 0, 0, date("m")-3, date("d"),date("Y"))));
    $this->CI->db->limit(1);
    $results = $this->CI->db->get();

    if ( isset($results->result_array()[0]["resolved"]) )
    {
      return array("status" => 1, "links" => $results->result_array()[0]["resolved"]);
    }
    else
    {
      return array("status" => -1, "links" => "");
    }
  }

  public function store_resolved_link($ppn, $links)
  {
    $iln = ( isset($_SESSION["iln"]) ) ? $_SESSION["iln"] : "";
    if ( $ppn == "" || $iln == "" )  return (-1);

    $this->CI->db->query("replace into links_resolved_library (iln, ppn, resolved,checkdate) values (" . $iln . ", '" . $ppn . "', '" .  addslashes($links) . "','" . date("y-m-d") . "')");
  }

  public function getCollections()
  {
    $this->CI->db->reset_query();
    $this->CI->db->select('shortcut,name,link');
    $this->CI->db->from('collections');
    $results = $this->CI->db->get();
    $Data = array();
    foreach ($results->result_array() as $row)
    {
      $Data[$row['shortcut']] = array("name" => $row['name'], "link" => $row['link']);
    }
    return ($Data);
  }

  public function existsCentralDB()
  {
    return ( isset($_SESSION["config_system"]["central.db"]["host"]) && isset($_SESSION["config_system"]["central.db"]["name"])
          && isset($_SESSION["config_system"]["central.db"]["user"]) && isset($_SESSION["config_system"]["central.db"]["pass"]) ) ? true : false;
  }

  public function getCentralDB($Type, $Filter=array())
  {
    if ( !isset($_SESSION["config_system"]["central.db"]["host"]) || !isset($_SESSION["config_system"]["central.db"]["name"])
      || !isset($_SESSION["config_system"]["central.db"]["user"]) || !isset($_SESSION["config_system"]["central.db"]["pass"]) ) return array();

    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT); // Set MySQLi to throw exceptions 
    try 
    {
      $CDB = mysqli_connect($_SESSION["config_system"]["central.db"]["host"], $_SESSION["config_system"]["central.db"]["user"], 
                            $_SESSION["config_system"]["central.db"]["pass"], $_SESSION["config_system"]["central.db"]["name"]);
    } 
    catch (mysqli_sql_exception $e) 
    {
      return array();
    }

    mysqli_set_charset($CDB,"utf8");

    if ( $Type == "classification" )
    {
      $ROWS = array( "classifications" => array(), "details" => array() );

      $SQL  = "SELECT shortcut, name, link FROM classifications";
      $RES  = mysqli_query($CDB, $SQL);
      while ($ROW = mysqli_fetch_assoc($RES)) 
      {
        $ROWS["classifications"][$ROW["shortcut"]] = array("name" => $ROW["name"], "link" => $ROW["link"]);
      }

      $SQL = "SELECT classification, code, description, parents FROM classificationstructures";
      $CNT = 0;
      foreach ( $Filter as $One )
      {
        if ( isset($One["classification"]) && isset($One["code"]) && in_array(strtoupper($One["classification"]), array("BBK", "BKL", "DDC", "RVK")) )
        {
          $CNT++;
          $SQL .= ($CNT == 1) ? " where" : " or";
          $SQL .= " (classification = '" . $One["classification"] . "' and code='" . $One["code"] . "')";
        }
      }

      if ( $CNT )
      {
        $RES  = mysqli_query($CDB, $SQL);
        // $ROWS["details"]         = mysqli_fetch_all($RES, MYSQLI_ASSOC);
        while ($ROW = mysqli_fetch_assoc($RES)) 
        {
          $ROWS["details"][] = array("classification" => $ROW["classification"],
                                     "code"           => $ROW["code"],
                                     "description"    => $ROW["description"],
                                     "parents"        => json_decode($ROW["parents"],true));
        }      
      }
    }

    // Free & Close
    mysqli_free_result($RES);
    mysqli_close($CDB);    

    return $ROWS;
  }

  public function get_chart_data($typ, $params=array())
  {
    $iln = ( isset($_SESSION["iln"]) ) ? $_SESSION["iln"] : "";
    $this->CI->db->reset_query();

    // 6 defined Colors 
    $backgroundColor = array('rgba(255, 99, 132, 0.2)','rgba(54, 162, 235, 0.2)','rgba(255, 206, 86, 0.2)','rgba(75, 192, 192, 0.2)','rgba(153, 102, 255, 0.2)','rgba(255, 159, 64, 0.2)');
    $borderColor = array( 'rgba(255,99,132,1)','rgba(54, 162, 235, 1)','rgba(255, 206, 86, 1)','rgba(75, 192, 192, 1)','rgba(153, 102, 255, 1)','rgba(255, 159, 64, 1)');

    switch ($typ)
    {
      case "searches":
      {
        $Labels = array();
        $Values = array();
        $ClBack = array();
        $ClBord = array();
        $Start  = ( isset($params["start"]) && $params["start"] != "" ) ? $params["start"] : "2017-01-01";
        $End    = ( isset($params["end"])   && $params["end"]   != "" ) ? $params["end"]   : "2017-01-30";

        // Create Range Area with 0
        $begin    = new DateTime( $Start );
        $end      = new DateTime( $End );
        $end->add( new DateInterval( "P1D" ) );
        $interval = DateInterval::createFromDateString('1 day');
        $period   = new DatePeriod($begin, $interval, $end);
        foreach ( $period as $dt )
        {
          $Labels[$dt->format("Y-m-d")] = $dt->format("d.m.Y");
          $Values[$dt->format("Y-m-d")] = 0;
          if ( $dt->format("w") == 0 || $dt->format("w") == 6 )
          {
            // Sa / So
            $ClBack[$dt->format("Y-m-d")] = $backgroundColor[0];
            $ClBord[$dt->format("Y-m-d")] = $borderColor[0];
          }
          else
          {
            // Mo - Fr
            $ClBack[$dt->format("Y-m-d")] = $backgroundColor[1];
            $ClBord[$dt->format("Y-m-d")] = $borderColor[1];
          }
        }

        $query = $this->CI->db->query("select day, hour_00 + hour_01 + hour_02 + hour_03 + hour_04 + hour_05 + hour_06 + hour_07 + hour_08 + hour_09 + hour_10 + hour_11 + hour_12 + hour_13 + hour_14 + hour_15 + hour_16 + hour_17 + hour_18 + hour_19 + hour_20 + hour_21 + hour_22 + hour_23 as summe from stats_day_library where iln = " . $iln . " and area = 'Search' and day >= '" . $Start . "' and day <= '" . $End . "' order by day");
        foreach ($query->result() as $row)
        {
          $Values[date("Y-m-d", strtotime($row->day))] = (integer) $row->summe;
        }
        $Data = array
        (
          "labels" => array_values($Labels),
          "datasets" => array
          (
            array
            (
              "label"           => $this->code2text("SEARCHESDONE"),
              "data"            => array_values($Values),
              "borderWidth"     => 1,
              "backgroundColor" => array_values($ClBack),
              "borderColor"     => array_values($ClBord)
            )
          ),
        );
        $Replacements = array
        (
          "{start}" => date("d.m.Y", strtotime($Start)),
          "{end}"   => date("d.m.Y", strtotime($End))
        );
        $Title = array
        (
          "display"  => true,
          //"text"     => str_ireplace("{days}", 22, $this->code2text("USERSEARCHESDAYS")),
          "text"     => strtr($this->code2text("USERSEARCHESPERIOD"), $Replacements),
          "fontSize" => 24 
        );
        break;        
      }
      case "usage":
      {
        $Labels  = array();
        $Values  = array();
        $Start   = ( isset($params["start"]) && $params["start"] != "" ) ? $params["start"] : "2017-01-01";
        $End     = ( isset($params["end"])   && $params["end"]   != "" ) ? $params["end"]   : "2017-01-30";
        $Areas   = ( isset($params["areas"]) && $params["areas"] != "" ) ? explode(",",$params["areas"]) : array();

        // Create Range Area with 0
        $begin    = new DateTime( $Start );
        $end      = new DateTime( $End );
        $end->add( new DateInterval( "P1D" ) );
        $interval = DateInterval::createFromDateString('1 day');
        $period   = new DatePeriod($begin, $interval, $end);
        foreach ( $period as $dt )
        {
          foreach ($Areas as $Area)
          {
            $Labels[$dt->format("Y-m-d")]        = $dt->format("d.m.Y");
            $Values[$Area][$dt->format("Y-m-d")] = 0;
          }
        }

        $query = $this->CI->db->query("select day, area, hour_00 + hour_01 + hour_02 + hour_03 + hour_04 + hour_05 + hour_06 + hour_07 + hour_08 + hour_09 + hour_10 + hour_11 + hour_12 + hour_13 + hour_14 + hour_15 + hour_16 + hour_17 + hour_18 + hour_19 + hour_20 + hour_21 + hour_22 + hour_23 as summe from stats_day_library where iln = " . $iln . " and area in ('" . implode("','", $Areas) . "') and day >= '" . $Start . "' and day <= '" . $End . "' order by day, area");
        foreach ($query->result() as $row)
        {
          $Values[$row->area][date("Y-m-d", strtotime($row->day))] = (integer) $row->summe;
        }

        $Counter = -1;
        foreach ($Areas as $Area) 
        {
          $Counter++;
          if ( $Counter >= count($backgroundColor) ) $Counter = 0;
          $Datasets[] = array
          (
            "type"            => "line",
            "label"           => $Area, //this->code2text("SEARCHESDONE"),
            "data"            => array_values($Values[$Area]),
            "borderWidth"     => 1,
            "backgroundColor" => $backgroundColor[$Counter],
            "borderColor"     => $borderColor[$Counter]
          );
        }

        $Data = array
        (
          "labels"   => array_values($Labels),
          "datasets" => $Datasets,
        );
        $Replacements = array
        (
          "{start}" => date("d.m.Y", strtotime($Start)),
          "{end}"   => date("d.m.Y", strtotime($End))
        );
        $Title = array
        (
          "display"  => true,
          //"text"     => str_ireplace("{days}", 22, $this->code2text("USERSEARCHESDAYS")),
          "text"     => strtr($this->code2text("USERUSAGEPERIOD"), $Replacements),
          "fontSize" => 24 
        );
        break;                
      }
      case "usageyear":
      {
        $Labels  = array();
        $Values  = array();
        $Year   = ( isset($params["year"]) && $params["year"] != "" ) ? $params["year"] : date("Y");
        $Areas   = ( isset($params["areas"]) && $params["areas"] != "" ) ? explode(",",$params["areas"]) : array();

        // Create Range Area with 0
        $begin    = $Year . "-01-01";
        $end      = ($Year+1) . "-01-01";

        $Labels = array("1" => "Januar", "2" => "Februar", "3" => "März",       "4" => "April",    "5" => "Mai",      "16" => "Juni", 
                        "7" => "Juli",   "8" => "August",  "9" => "September", "10" => "Oktober", "11" => "November", "12" => "Dezember");

        foreach ($Areas as $Area)
        {
          $Values[$Area] = array("1" => 0, "2" => 0, "3" => 0, "4" => 0, "5" => 0, "6" => 0, "7" => 0, "8" => 0, "9" => 0, "10" => 0, "11" => 0, "12" => 0);
        }


        $query = $this->CI->db->query("select month(day) as month, area, sum(hour_00 + hour_01 + hour_02 + hour_03 + hour_04 + hour_05 + hour_06 + hour_07 + hour_08 + hour_09 + hour_10 + hour_11 + hour_12 + hour_13 + hour_14 + hour_15 + hour_16 + hour_17 + hour_18 + hour_19 + hour_20 + hour_21 + hour_22 + hour_23) as summe from stats_day_library where iln = " . $iln . " and area in ('" . implode("','", $Areas) . "') and day >= '" . $begin . "' and day < '" . $end . "' group by month(day),area order by month(day),area");
        foreach ($query->result() as $row)
        {
          $Values[$row->area][$row->month] = (integer) $row->summe;
        }

        $Counter = -1;
        foreach ($Areas as $Area) 
        {
          $Counter++;
          if ( $Counter >= count($backgroundColor) ) $Counter = 0;
          $Datasets[] = array
          (
            "label"           => $Area, //this->code2text("SEARCHESDONE"),
            "data"            => array_values($Values[$Area]),
            "borderWidth"     => 1,
            "backgroundColor" => $backgroundColor[$Counter],
            "borderColor"     => $borderColor[$Counter]
          );
        }

        $Data = array
        (
          "labels"   => array_values($Labels),
          "datasets" => $Datasets,
        );
        $Replacements = array
        (
          "{year}" => $Year
        );
        $Title = array
        (
          "display"  => true,
          "text"     => strtr($this->code2text("USERUSAGEYEAR"), $Replacements),
          "fontSize" => 24 
        );
        break;                
      }
      case "devicescreens":
      {
        $Year   = ( isset($params["year"]) && $params["year"] != "" ) ? $params["year"] : date("Y");

        $query = $this->CI->db->query("select substring(area,8,20) as Screen, month_01 + month_02 + month_03 + month_04 + month_05 + month_06 + month_07 + month_08 + month_09 + month_10 + month_11 + month_12 as Summe FROM stats_year_library  where iln = " . $iln . " and area like 'Screen_%' and year = '" . $Year . "'");
        foreach ($query->result() as $row)
        {
          $Tmp = explode("x",$row->Screen);
          $X   = $Tmp[0]; 
          $Y   = $Tmp[1]; 

          if ( $row->Summe > 5000)
          { 
            $Col = 0;
            $Rad = 20;
          }
          else
          {
            if ( $row->Summe > 1000)
            { 
              $Col = 1;
              $Rad = 15;
            }
            else
            {
              if ( $row->Summe > 100)
              { 
                $Col = 2;
                $Rad = 10;
              }
              else
              {
                $Col = 3;
                $Rad = 5;
              }
            }
          }

          $Datasets[] = array
          (
            "label"           => ($X)."x".($Y) . " Screen: " . $row->Summe,
            "data"            => array(array('x' => $X,'y' => $Y, 'r' => $Rad)),
            "backgroundColor" => $backgroundColor[$Col],
            "borderColor"     => $borderColor[$Col]
          );
        }

        $Data = array
        (
          "datasets" => $Datasets,
        );


        $Title = array
        (
          "display"  => true,
          "text"     => $this->code2text("DEVICESCREEN") . " " . $Year,
          "fontSize" => 24 
        );
        break;        
      }
    }
    return (array("data" => $Data, "title" => $Title));
  }

  public function store_settings($userid, $name, $settings)
  {      
    $iln = ( isset($_SESSION["iln"]) ) ? $_SESSION["iln"] : "";
    if ( $userid == "" ||  $name == "" || $iln == "" )  return (-1);

    $this->CI->db->reset_query();
    $this->CI->db->query("replace into searches_library_user (iln, userid, name, settings, created) values ('" . $iln . "', '" . md5($userid) . "', '" . $this->CI->db->escape_str($name) . "', '" . serialize($settings) . "', now())");
  
    return 0;
  }

  public function load_settings($userid, $id)
  {      
    $iln = ( isset($_SESSION["iln"]) ) ? $_SESSION["iln"] : "";
    if ( $userid == "" ||  $id == "" || $iln == "" )  return (-1);

    $this->CI->db->reset_query();
    $this->CI->db->select('settings');
    $this->CI->db->from('searches_library_user');
    $this->CI->db->where('id',$id);
    $this->CI->db->where('iln',$iln);
    $results = $this->CI->db->get();
    if($results->num_rows() == 1)
    {
        return array('settings' => unserialize($results->row()->settings));
    }
  }

  public function list_settings($userid)
  {      
    $iln = ( isset($_SESSION["iln"]) ) ? $_SESSION["iln"] : "";
    if ( $userid == "" || $iln == "" )  return (-1);

    $this->CI->db->reset_query();
    $this->CI->db->select('id');
    $this->CI->db->select('name');    
    $this->CI->db->select('created');
    $this->CI->db->from('searches_library_user');
    $this->CI->db->where('iln',$iln);
    $this->CI->db->where('userid',md5($userid));
    $this->CI->db->order_by('name');
    $results = $this->CI->db->get();

    $Data = array();
    foreach ($results->result_array() as $row)
    {
      $Data[] = $row;
    }
    return ($Data);
  }  

  public function delete_settings($userid, $ids)
  {      
    $iln = ( isset($_SESSION["iln"]) ) ? $_SESSION["iln"] : "";
    if ( $userid == "" ||  $ids == "" || $iln == "" )  return (-1);

    $this->CI->db->reset_query();
    $this->CI->db->from('searches_library_user');
    $this->CI->db->where_in('id', $ids);
    $this->CI->db->where('iln',$iln);
    if ( ! $this->CI->db->delete() )
    {
      return ($this->db->error());
    }
    return (0);
  }

  public function store_logs($header, $body="", $userid="", $ppn="", $title="", $username="", $serialdata="")
  {      
    $iln = ( isset($_SESSION["iln"]) ) ? $_SESSION["iln"] : "";
    if ( $header == "" || $iln == "" )  return (-1);

    $User = ($userid != "") ? md5($userid) : "";
    $this->CI->db->reset_query();
    $this->CI->db->query("replace into logs_library (iln, userid, username, ppn, title, serialdata, header, body, created) values ('" . $iln . "', '" . $User . "', '" . $username . "','" . $ppn . "', '" .  $this->CI->db->escape_str($title) . "', '" . $serialdata . "', '" . $this->CI->db->escape_str($header) . "', '" . $this->CI->db->escape_str($body) . "', now())");
  
    return 0;
  }

  public function get_log_data($params=array())
  {
    $iln = ( isset($_SESSION["iln"]) ) ? $_SESSION["iln"] : "";
    if ( $iln == "" )  return (-1);

    $Start  = ( isset($params["start"]) && $params["start"] != "" ) ? $params["start"] : "2017-01-01";
    $End    = ( isset($params["end"])   && $params["end"]   != "" ) ? $params["end"]   : "2017-01-30";
    $End    = new DateTime($End);
    $End    = $End->modify('+1 day')->format("Y-m-d");

    $this->CI->db->reset_query();
    $this->CI->db->select('id');
    $this->CI->db->select('DATE_FORMAT(created, "%d.%m.%Y %H:%i:%s") as Zeitpunkt');    
    $this->CI->db->select('header as Bezeichnung');    
    $this->CI->db->select('username as Username');
    $this->CI->db->select('ppn as PPN');
    $this->CI->db->select('title as Titel');
    $this->CI->db->select('serialdata as Daten');
    $this->CI->db->select('body as Details');
    $this->CI->db->from('logs_library');
    $this->CI->db->where('iln',$iln);
    $this->CI->db->where('created >=',$Start);
    $this->CI->db->where('created <',$End);
    $this->CI->db->order_by('created desc');
    //$query = $this->CI->db->get_compiled_select('logs_library', false );
    $results = $this->CI->db->get();

    $Data = array();
    foreach ($results->result_array() as $row)
    {
      $Satz   = $row;
      $Daten  = isset($Satz["Daten"]) ? $Satz["Daten"] : "";
      unset($Satz["Daten"]);
      $Tmp    = unserialize($Daten);
      $Satz["Theke"] = (isset($Tmp["mailtoname"]) && $Tmp["mailtoname"] != "" ) ? $Tmp["mailtoname"] : "";
      $Satz["Band"]  = (isset($Tmp["volume"]) && $Tmp["volume"] != "" ) ? $Tmp["volume"] : "";
      $Data[] = $Satz;
    }
    return (array("data" => $Data, "status" => 0));
  }

  public function get_log_data_user($User)
  {
    $iln = ( isset($_SESSION["iln"]) ) ? $_SESSION["iln"] : "";
    if ( $User == "" || $iln == "" )  return (-1);

    $Start  = date("Y-m-d", strtotime('-1 month'));;
    // $End    = date("Y-m-d");

    $this->CI->db->reset_query();
    $this->CI->db->select('id');
    $this->CI->db->select('DATE_FORMAT(created, "%d.%m.%Y %H:%i:%s") as Zeitpunkt');    
    $this->CI->db->select('header as Bezeichnung');    
    $this->CI->db->select('body as Details');
    $this->CI->db->select('ppn as PPN');
    $this->CI->db->select('title as Titel');
    $this->CI->db->select('serialdata as Daten');
    $this->CI->db->select('username as Username');
    $this->CI->db->from('logs_library');
    $this->CI->db->where('iln',$iln);
    $this->CI->db->where('userid',md5($User));
    $this->CI->db->where('created >=',$Start);
    // $this->CI->db->where('created <=',$End);
    $this->CI->db->order_by('created desc');
    $results = $this->CI->db->get();
    $Data = array();
    foreach ($results->result_array() as $row)
    {
      $Data[] = $row;
    }
    $_SESSION["usermailorders"]  = $Data;
  }

  private function CockpitExec($StatQuery)
  {
    return number_format($this->CI->db->query($StatQuery)->row()->anzahl,0,",",".");
  }

  private function CockpitYear($Year, $Query)
  {
    $Data     = array();
    $Data[$Year-2] = $this->CockpitExec(str_replace("{year}", ($Year-2), $Query));
    $Data[$Year-1] = $this->CockpitExec(str_replace("{year}", ($Year-1), $Query));
    $Data[$Year]   = $this->CockpitExec(str_replace("{year}", ($Year),   $Query));
    return $Data;
  }

  public function get_cockpit_data($params=array())
  {
    $Year   = ( isset($params["year"]) && $params["year"] != "" ) ? $params["year"] : date("Y");

    $iln = ( isset($_SESSION["iln"]) ) ? $_SESSION["iln"] : "";
    if ( $iln == "" )  return (-1);

    $Data = array();
    $Data[] = array("label" =>"Suchen", 
                    "icon"  =>"fa fa-search fa-5x",
                    "values"=>$this->CockpitYear($Year,"select sum(hour_00+hour_01+hour_02+hour_03+hour_04+hour_05+hour_06+hour_07+hour_08+hour_09+hour_10+hour_11+hour_12+hour_13+hour_14+hour_15+hour_16+hour_17+hour_18+hour_19+hour_20+hour_21+hour_22+hour_23) as anzahl from stats_day_library where iln=" . $iln . "  and day>='{year}-01-01' and day<='{year}-12-31' and area = 'Search'"));
    $Data[] = array("label" =>"Große Kachel", 
                    "icon"  =>"fa fa-square fa-5x",
                    "values"=>$this->CockpitYear($Year,"select sum(hour_00+hour_01+hour_02+hour_03+hour_04+hour_05+hour_06+hour_07+hour_08+hour_09+hour_10+hour_11+hour_12+hour_13+hour_14+hour_15+hour_16+hour_17+hour_18+hour_19+hour_20+hour_21+hour_22+hour_23) as anzahl from stats_day_library where iln=" . $iln . "  and day>='{year}-01-01' and day<='{year}-12-31' and area = 'FullView'"));
    $Data[] = array("label" =>"Benutzerkonto", 
                    "icon"  =>"fa fa-user fa-5x",
                    "values"=>$this->CockpitYear($Year,"select sum(hour_00+hour_01+hour_02+hour_03+hour_04+hour_05+hour_06+hour_07+hour_08+hour_09+hour_10+hour_11+hour_12+hour_13+hour_14+hour_15+hour_16+hour_17+hour_18+hour_19+hour_20+hour_21+hour_22+hour_23) as anzahl from stats_day_library where iln=" . $iln . "  and day>='{year}-01-01' and day<='{year}-12-31' and area = 'UserView'"));
    $Data[] = array("label" =>"Exporte", 
                    "icon"  =>"fa fa-share-square fa-5x",
                    "values"=>$this->CockpitYear($Year,"select sum(hour_00+hour_01+hour_02+hour_03+hour_04+hour_05+hour_06+hour_07+hour_08+hour_09+hour_10+hour_11+hour_12+hour_13+hour_14+hour_15+hour_16+hour_17+hour_18+hour_19+hour_20+hour_21+hour_22+hour_23) as anzahl from stats_day_library where iln=" . $iln . "  and day>='{year}-01-01' and day<='{year}-12-31' and area like 'Export_%'"));
    $Data[] = array("label" =>"Bildschirm-Auflösungen", 
                    "icon"  =>"fa fa-desktop fa-5x",
                    "values"=>$this->CockpitYear($Year,"select distinct count(*) as anzahl from stats_year_library where iln=" . $iln . " and year={year} and area like 'Screen_%'"));
    $Data[] = array("label" =>"Browser und Versionen", 
                    "icon"  =>"fa fa-fire fa-5x",
                    "values"=>$this->CockpitYear($Year,"select distinct count(*) as anzahl from stats_year_library where iln=" . $iln . " and year={year} and area like 'Browser_%'"));
    $Data[] = array("label" =>"Betriebssysteme und Versionen", 
                    "icon"  =>"fa fa-laptop fa-5x",
                    "values"=>$this->CockpitYear($Year,"select distinct count(*) as anzahl from stats_year_library where iln=" . $iln . " and year={year} and area like 'OS%'"));
    $Data[] = array("label" =>"Produkte und Version", 
                    "icon"  =>"fa fa-mobile fa-5x",
                    "values"=>$this->CockpitYear($Year,"select distinct count(*) as anzahl from stats_year_library where iln=" . $iln . " and year={year} and area like 'Product%'"));
     //$query = $this->CI->db->get_compiled_select('logs_library', false );
    return (array("data" => $Data, "status" => 0));
  }

}