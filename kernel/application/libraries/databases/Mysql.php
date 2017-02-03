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
        return htmlspecialchars ($_SESSION["translation_ger"][$code], ENT_QUOTES);
      }
    }
    else
    {
      if ( array_key_exists($code,$_SESSION["translation_eng"]) )
      {
        return htmlspecialchars ($_SESSION["translation_eng"][$code], ENT_QUOTES);
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
          return htmlspecialchars ($Value, ENT_QUOTES);
        }
        else
        {
          $Value = $results->row()->english;
          $_SESSION["translation_eng"][$code]	= $Value;
          return htmlspecialchars ($Value, ENT_QUOTES);
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
      return htmlentities($Value, ENT_QUOTES);
    }
    else
    {
      $Value = $results->row()->english;
      $_SESSION["translation_eng"][$code] = $Value;
      return htmlentities($Value, ENT_QUOTES);
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
  
  public function store_user_search($search,$user,$facets)
  {
    $this->CI->db->reset_query();
    $this->CI->db->query("insert into search_user (suche, user, datumzeit,facetten) values ('" . $this->CI->db->escape_str($search) . "', '" . $user . "',now(),'" . $facets . "')");
    return array("status" => 0);
  }

  public function load_user_search($user)
  {
    $this->CI->db->reset_query();
    $this->CI->db->select('suche');
    $this->CI->db->select('datumzeit');
    $this->CI->db->select('facetten');
    $this->CI->db->from('search_user');
    $this->CI->db->where('user', $user);
    $this->CI->db->order_by('datumzeit', 'DESC');
    ;$this->CI->db->limit(6);
    $results = $this->CI->db->get();

    $Data = array();
    foreach ($results->result_array() as $row)
    {
      $Data[] = $row;
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
    $this->CI->db->limit(1);
    $results = $this->CI->db->get();

    if ( isset($results->result_array()[0]["resolved"]) )
    {
      return array("status" => 1, "links" => $results->result_array()[0]["resolved"]);
    }
    else
    {
      return array("status" => -1, "links" => array());
    }
  }

  public function store_resolved_link($ppn, $links)
  {
    $iln = ( isset($_SESSION["iln"]) ) ? $_SESSION["iln"] : "";
    if ( $ppn == "" || $iln == "" )  return (-1);

    $this->CI->db->query("insert into links_resolved_library (iln, ppn, resolved) values (" . $iln . ", '" . $ppn . "', '" .  $links . "')");
  }

  public function get_discovery_bibs()
  {
    $this->CI->db->reset_query();
    $this->CI->db->select('city');
    $this->CI->db->select('title');
    $this->CI->db->select('title_short');
    $this->CI->db->select('iln');
    $this->CI->db->select('street');
    $this->CI->db->select('zip');
    $this->CI->db->from('discovery_bibs');
    $this->CI->db->where('iln > 0');
    $this->CI->db->order_by('city');
    $this->CI->db->order_by('title');
    $results = $this->CI->db->get();

    $Data = array();

    // Add all locations
    $Data[] = array(
                  "city"        => $this->CI->database->code2text("ALL"), 
                  "title"       => $this->CI->database->code2text("DATAPOOLGLOBAL"),
                  "title_short" => "",
                  "iln"         => "",
                  "street"      => ""
                 );
    foreach ($results->result_array() as $row)
    {
      $Data[] = $row;
    }
    return ($Data);
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
    }
    return (array("data" => $Data, "title" => $Title));
  }
}
