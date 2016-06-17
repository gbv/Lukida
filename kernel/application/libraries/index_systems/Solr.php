<?php

class Solr extends General
{
  protected $CI;
  protected $search;
  protected $package;
  protected $facets;
  protected $result;

  public function __construct()
  {
    // Assign the CodeIgniter super-object
    $this->CI =& get_instance();
  }

  // ********************************************
  // ************** Side-Functions **************
  // ********************************************
  private function solr_before($search)
  {
    //$search	= json_encode($search);
    $search	= str_replace("%20", " ", $search);
    //$search	= preg_replace('/\s\s+/', ' ', $search);
    //$search = SolrUtils::queryPhrase($search);
    //$search = SolrUtils::escapeQueryChars($search);
    //$search	= str_replace(" ", "+", $search);
    return ($search);
  }

  private function solr_edismax($search,$package,$facets)
  {
    $options = array
    (
      'hostname'	=> (isset($_SESSION["config_general"]["index_system"]["host"]) && $_SESSION["config_general"]["index_system"]["host"] != "" ) ? $_SESSION["config_general"]["index_system"]["host"] : "findex.gbv.de",
      'port'			=> (isset($_SESSION["config_general"]["index_system"]["port"]) && $_SESSION["config_general"]["index_system"]["port"] != "" ) ? $_SESSION["config_general"]["index_system"]["port"] : "80",
      'path'			=> (isset($_SESSION["config_general"]["index_system"]["path"]) && $_SESSION["config_general"]["index_system"]["path"] != "" ) ? $_SESSION["config_general"]["index_system"]["path"] : "index/100",
      'wt'				=> 'json',
    );
    $client = new SolrClient($options);

    // Determine search type
    $Type = "allfields";
    if ( strpos($search, ":") !== false )
    {
      $Tmp = explode(":", $search);
      if ( in_array(strtolower(trim($Tmp[0])), array("author","autor","id","isn","subject","schlagwort","title","titel","series","reihe","publisher","verlag","year","jahr","toc","inhalt","class","sachgebiet")) )
      {
        $Type = strtolower(trim($Tmp[0]));
        unset($Tmp[0]);
        $search = implode(":", $Tmp);
      }
    }

    // Escape Solr special characters
    if ( $Type != "id" )
    {
      $search = str_replace(array( '+', '-', '&', '|', '!', '(' ,')' ,'{', '}', '[', ']', '^', '~', '?'),
                            array('\+','\-','\&','\|','\!','\(','\)','\{','\}','\[','\]','\^','\~','\?'),
                            $search);
    }
    else
    {
      $search = str_replace(" ","",$search);
      $search = str_replace(",,",",",$search);
      $IDs = explode(",", $search);
      foreach ( $IDs as &$ID )
      {
        $ID = "id:".trim(preg_replace("/[^A-Za-z0-9]/", "", $ID));
      }
      $search = (count($IDs)>1) ? "(" . implode(" OR ",$IDs) . ")" : $IDs[0];
    }
                          
    // Initialize Client
    $dismaxQuery = new SolrDisMaxQuery($search);

    // Set query parser to EDisMax
    $dismaxQuery->useEDisMaxQueryParser();
    
    // Highlighting switched off, because highlighting inside fullrecord not possible
    // $dismaxQuery->setHighlight(true);

    // Field groups & boosting
    switch ($Type)
    {
      case "author":
      case "autor":
      {
        $dismaxQuery
        ->addQueryField("author",100)
        ->addQueryField("author_fuller",50)
        ->addQueryField("author2")
        ->addQueryField("author_additional");
        break;
      }
      case "isn":
      {
        $dismaxQuery
        ->addQueryField("isbn")
        ->addQueryField("issn");
        break;
      }
      case "subject":
      case "schlagwort":
      {
        $dismaxQuery
        ->addQueryField("topic_unstemmed",150)
        ->addQueryField("topic",100)
        ->addQueryField("geographic",50)
        ->addQueryField("genre",50)
        ->addQueryField("era");
        break;
      }
      case "title":
      case "titel":
      {
        $dismaxQuery
        ->addQueryField("title_short",500)
        ->addQueryField("title_full_unstemmed",450)
        ->addQueryField("title_full",400)
        ->addQueryField("title",300)
        ->addQueryField("title_alt",200)
        ->addQueryField("title_new",100)
        ->addQueryField("title_old")
        ->addQueryField("series",100)
        ->addQueryField("series2");
        break;
      }
      case "series":
      case "reihe":
      {
        $dismaxQuery
        ->addQueryField("series",100)
        ->addQueryField("series2");
        break;
      }
      case "publisher":
      case "verlag":
      {
        $dismaxQuery
        ->addQueryField("publisher",100);
        break;
      }
      case "year":
      case "jahr":
      {
        $dismaxQuery
        ->addQueryField("publishDate",100);
        break;
      }
      case "toc":
      case "inhalt":
      {
        $dismaxQuery
        ->addQueryField("contents",100);
        break;
      }
      case "class":
      case "sachgebiet":
      {
        $dismaxQuery
        ->addQueryField("class",100);
        break;
      }
      case "id":
      {
        $dismaxQuery
        ->addQueryField("id",100);
        break;
      }
      default:
      {
        // AllFields
        $dismaxQuery
        ->addQueryField("title_short",750)
        ->addQueryField("title_full_unstemmed",600)
        ->addQueryField("title_full",400)
        ->addQueryField("title",500)
        ->addQueryField("title_alt",200)
        ->addQueryField("title_new",100)
        ->addQueryField("series",50)
        ->addQueryField("series2",30)
        ->addQueryField("author",300)
        ->addQueryField("author_fuller",150)
        ->addQueryField("contents",10)
        ->addQueryField("topic_unstemmed",550)
        ->addQueryField("topic",500)
        ->addQueryField("geographic",300)
        ->addQueryField("genre",300)
        ->addQueryField("allfields_unstemmed",10)
        ->addQueryField("fulltext_unstemmed",10)
        ->addQueryField("allfields")
        ->addQueryField("fulltext")
        ->addQueryField("description")
        ->addQueryField("isbn")
        ->addQueryField("issn");
      }
    }
    
    // Return fields
    $dismaxQuery
    ->addField('id')
    ->addField('fullrecord');

    if ( $Type != "id" )
    {
      // Facet fields (only for new searches (package=1), not for inkremential searches (package=0, package >=2)
      if ( $package == 1 && $facets )
      {
        $dismaxQuery->setFacet(true);
        $dismaxQuery
        ->addFacetField('remote_bool')
        ->addFacetField('format_phy_str_mv');
        $dismaxQuery->setFacetLimit(20);
      }
    
      // Filter 
      foreach ( $_SESSION["filter"] as $key => $value)
      {
        switch ($key )
        {
          case "datapool":
          {
            if ( isset($_SESSION["iln"]) && $value == "local" )
            {
              $dismaxQuery
              ->addFilterQuery('collection_details:GBV_ILN_' . $_SESSION['iln']);
            }
            break;
          }
    
          case "typ":
          {
            if ( $value != "total" && $facets )
            {
              $dismaxQuery
              ->addFilterQuery('remote_bool:'.$value);
            }
            break;
          }
    
          case "yearrange":
          {
            if ( $value != "" && $facets )
            {
              $dismaxQuery
              ->addFilterQuery('publishDate:'.$value);
            }
            break;
          }
          case "formats":
          {
            if ( count((array)$value) > 0 && $facets )
            {
              $dismaxQuery
              ->addFilterQuery('format_phy_str_mv:("' . implode('" OR "', array_keys((array)$value)) . '")');
            }
          }
        }
      }
      $dismaxQuery->setStats(true);
      $dismaxQuery->addStatsField('publishDate');
    }    

    // Package start & end
    if ( $package == 0 )
    {
      $dismaxQuery->setStart(0);
      $dismaxQuery->setRows(1000);
    }
    else
    {
      $dismaxQuery->setStart((($package-1)*50));
      $dismaxQuery->setRows(50);
    }

    // Store query in session
    $_SESSION["query"] = $dismaxQuery;

    // Store query in file
    // $this->CI->appendFile("EDisMax.txt", "http://" . $options["hostname"] . ":" . $options["port"] . "/". $options["path"] . "/select?" . $dismaxQuery);

    // Execute query
    $query_response = $client->query($dismaxQuery);

    // Store answer in file
    //$this->CI->printArray2File($query_response);

    // Store answer
    return ($query_response->getResponse());
  }

  private function solr_after($result,$package)
  {
    // fill container
    $container = array
    (
      "results" => $result["response"]["docs"],
      "qtime"   => str_replace(".", ",",round($result["responseHeader"]["QTime"] / 1000,2)),
      "hits"    => $result["response"]["numFound"],
      "hitspack"=> count($result["response"]["docs"]),
      "facets"  => $result["facet_counts"]["facet_fields"],
      "stats"   => $result["stats"]["stats_fields"],
      "start"   => ($package != 0 ) ? ($package-1)*50 : 0,
    );

    // return container
    return $container;
  }

  // ********************************************
  // ************** Main-Functions **************
  // ********************************************

  public function main($params)
  {
    // Check params
    if ( isset($params['search'] ) )	$search = $params['search'];
    if ( $search == "" )
    {
      echo "Es ist ein Fehler passiert (NO_SEARCH) !";
      return false;
    }
    $this->search = $search;

    if ( isset($params['package'] ) )	$package = $params['package'];
    if ( $package == "" )
    {
      echo "Es ist ein Fehler passiert (NO_PACKAGE) !";
      return false;
    }
    $this->package = $package;        
    
    if ( isset($params['facets'] ) )	$facets = $params['facets'];
    if ( $package == "" )
    {
      echo "Es ist ein Fehler passiert (NO_FACETS) !";
      return false;
    }
    $this->facets = $facets;        
    
  
    // Before search
    $this->search = $this->solr_before($this->search);

    // Execute search
    $this->result = $this->solr_edismax($this->search, $this->package, $this->facets);

    // After search
    $container = $this->solr_after($this->result, $this->package);

    // Return Data
    return ( $container );
  }

  public function silmularpubs($params)
  {
    // Check params
    if ( isset($params['ppn'] ) )  $PPN = $params['ppn'];
    if ( $PPN == "" )
    {
      echo "Es ist ein Fehler passiert (NO_PPN) !";
      return false;
    }

    $options = array
    (
      'hostname'  => (isset($_SESSION["config_general"]["index_system"]["host"]) && $_SESSION["config_general"]["index_system"]["host"] != "" ) ? $_SESSION["config_general"]["index_system"]["host"] : "findex.gbv.de",
      'port'      => (isset($_SESSION["config_general"]["index_system"]["port"]) && $_SESSION["config_general"]["index_system"]["port"] != "" ) ? $_SESSION["config_general"]["index_system"]["port"] : "80",
      'path'      => (isset($_SESSION["config_general"]["index_system"]["path"]) && $_SESSION["config_general"]["index_system"]["path"] != "" ) ? $_SESSION["config_general"]["index_system"]["path"] : "index/100",
      'wt'        => 'json',
    );
    $client = new SolrClient($options);

    // Initialize Client
    $dismaxQuery = new SolrDisMaxQuery("id:".$PPN);

    // Set query parser to EDisMax
    $dismaxQuery->useEDisMaxQueryParser();

    $dismaxQuery
    ->addQueryField("id",100)
    ->addField('id');

    $dismaxQuery
    ->addMltField("title")
    ->addMltField("title_short")
    ->addMltQueryField("title",75)
    ->addMltQueryField("title_short",100);
          //topic^300%20language^30%20author^75%20publishDate
    $dismaxQuery
    ->setMlt(true)
    ->setMltBoost(true)
    ->setMltMinDocFrequency(1)
    ->setMltCount(6);

    if ( isset($_SESSION["iln"]) && isset($_SESSION["filter"]["datapool"]) && 
                                          $_SESSION["filter"]["datapool"] == "local" )
    {
      $dismaxQuery
      ->addFilterQuery('collection_details:GBV_ILN_' . $_SESSION['iln']);
    }    

    // Store query in file
    // $this->CI->appendFile("EDisMax.txt", "http://" . $options["hostname"] . ":" . $options["port"] . "/". $options["path"] . "/select?" . $dismaxQuery);

    // Execute query
    $query_response = $client->query($dismaxQuery);

    $container = $query_response->getResponse();

    // Store answer in file
    // $this->CI->printArray2File($container);

    $PPNList = array();
    if ( isset($container["moreLikeThis"][1]["docs"]))
    {
      $container = $container["moreLikeThis"][1]["docs"];

      foreach ( $container as $one )
      {
        $PPNList[]  = $one["id"];
      }
    }

    // Store PPNList in file
    //$this->CI->printArray2File($PPNList);

    // Return Data
    return ( $PPNList );

  }

}

?>