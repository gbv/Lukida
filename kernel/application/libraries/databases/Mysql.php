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
          return ($Value);
        }
        else
        {
          $Value = $results->row()->english;
          $_SESSION["translation_eng"][$code]	= $Value;
          return ($Value);
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
      return ($Value);
    }
    else
    {
      $Value = $results->row()->english;
      $_SESSION["translation_eng"][$code] = $Value;
      return ($Value);
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
                  "city"   => $this->CI->database->code2text("ALL"), 
                  "title"  => $this->CI->database->code2text("DATAPOOLGLOBAL"),
                  "iln"    => "",
                  "street" => ""
                 );
    foreach ($results->result_array() as $row)
    {
      $Data[] = $row;
    }
    return ($Data);
  }
}