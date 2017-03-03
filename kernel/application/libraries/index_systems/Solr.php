<?php

class Solr extends General
{
  protected $CI;
  protected $search;
  protected $package;
  protected $facets;
  protected $result;
  protected $config;

  public function __construct()
  {
    // Assign the CodeIgniter super-object
    $this->CI =& get_instance();

    $this->config = array
    (
      'hostname'  => (isset($_SESSION["config_general"]["index_system"]["host"]) && $_SESSION["config_general"]["index_system"]["host"] != "" ) 
                     ? $_SESSION["config_general"]["index_system"]["host"] : "findex.gbv.de",
      'port'      => (isset($_SESSION["config_general"]["index_system"]["port"]) && $_SESSION["config_general"]["index_system"]["port"] != "" ) 
                     ? $_SESSION["config_general"]["index_system"]["port"] : "80",
      'path'      => (isset($_SESSION["config_general"]["index_system"]["path"]) && $_SESSION["config_general"]["index_system"]["path"] != "" ) 
                     ? $_SESSION["config_general"]["index_system"]["path"] : "index/100",
      'wt'        => (isset($_SESSION["config_general"]["index_system"]["wt"])   && $_SESSION["config_general"]["index_system"]["wt"] != "" ) 
                     ? $_SESSION["config_general"]["index_system"]["wt"] : "json"
    );

  }

  // ********************************************
  // ************** Side-Functions **************
  // ********************************************
  private function solr_before($search)
  {
    $search = str_replace("%20", " ", $search);
    return ($search);
  }

  private function solr_edismax($search,$package,$facets)
  {

    $client = new SolrClient($this->config);

    // Find complex search phrases and move them to $matches
    // First: field(phrase)
    preg_match_all("/([A-Za-z0-9]+)\(([^)]+)\)/", $search, $matches);
    foreach ( $matches[0] as $one )
    {
      $search = str_replace($one, "", $search);
    }

    // Second: field:(phrase)
    preg_match_all("/([A-Za-z0-9]+):\(([^)]+)\)/", $search, $matchescolon);
    foreach ( $matchescolon[0] as $one )
    {
      $search = str_replace($one, "", $search);
    }
    foreach ($matchescolon as $index => $one)
    {
      foreach ($one as $value)
      {
        $matches[$index][] = $value;
      }
    }
    
    // Third: field:phrase
    preg_match_all("/([A-Za-z0-9]+):([^\s]+)/", $search, $matchescolon2);
    foreach ( $matchescolon2[0] as $one )
    {
      $search = str_replace($one, "", $search);
    }
    foreach ($matchescolon2 as $index => $one)
    {
      foreach ($one as $value)
      {
        $matches[$index][] = $value;
      }
    }
    
    // Remove not allowed complex phrases based on used key
    foreach ( $matches[1] as $index => $key )
    {
      if ( ! in_array(strtolower(trim($key)), array("author","autor","id","isn","subject","schlagwort","title","titel","series","reihe","publisher","verlag","year","jahr","contents","inhalt","class","sachgebiet","ppnlink")) )
      {
        unset($matches[0][$index]);
      }
    }
    
    // $this->CI->printArray2File($matches);

    // Now loop over complex search phrased and add simple search word
    // to build query string MainSearch
    $MainSearch = "";
    $search     = trim($search);
    foreach ( $matches[0] as $index => $complex )
    {
      $CType = strtolower(trim($matches[1][$index]));
      $CText = trim($matches[2][$index]);

/*
      // Mask solr special characters
      $CText = str_replace(array( '+', '-', '&&', '||', '!', '(', ')', '{', '}', '[', ']', '^', '"', '~', ':'),
                           array('\+','\-','\&&','\||','\!','\(','\)','\{','\}','\[','\]','\^','\"','\~','\:'),
                           $CText); 
*/
      //First get phrases in "" and remove them
      preg_match_all("/\"([^\"]+)\"/", $CText, $Cmatches);
      foreach ( $Cmatches[0] as $one )
      {
        $CText = str_replace($one, "", $CText);
      }
      
      // Second get remaininhg phrases split by ,
      $Phrases = $Cmatches[1] + array_map('trim', array_filter(explode(",", $CText)));

      if ( $index > 0 ) $MainSearch .= " AND ";
      
      if ( count($Phrases) == 1)
      {
        switch ($CType)
        {
          case "author":
          case "autor":
            $MainSearch .= "(author:\"" . $Phrases[0] . "\" OR author2:\"" . $Phrases[0] . "\")";
            break;
          case "series":
          case "reihe":
            $MainSearch .= "(series:\"" . $Phrases[0] . "\" OR series2:\"" . $Phrases[0] . "\")";
            break;
          case "subject":
          case "schlagwort":
            $MainSearch .= "(topic:\"" . $Phrases[0] . "\" OR topic_unstemmed:\"" . $Phrases[0] . "\")";
            break;
          case "class":
            $MainSearch .= "(class_local:\"" . $Phrases[0] . "\" )";
            break;
          case "isn":
            $MainSearch .= "(issn:\"" . $Phrases[0] . "\" OR isbn:\"" . $Phrases[0] . "\")";
            break;
          case "title":
          case "titel":
            $MainSearch .= "(title_short:" . $Phrases[0] . " OR title_full_unstemmed:" . $Phrases[0] . " OR title_full:" . $Phrases[0] . " OR title:" . $Phrases[0] . ")";
            break;
          case "jahr":
          case "year":
            $MainSearch .= "(publishDate:\"" . $Phrases[0] . "\")";
            break;
          default:
            $MainSearch .= $CType . ":\"" . $Phrases[0] . "\"";
        }
      }
      else
      {
        switch ($CType)
        {
          case "author":
          case "autor":
            $MainSearch .= "(author:\"" .  implode("\" OR author:\"", $Phrases) . "\" OR "
                         . " author2:\"" . implode("\" OR author2:\"",$Phrases) . "\")";
            break;
          case "series":
          case "reihe":
            $MainSearch .= "(series:\"" .  implode("\" OR series:\"", $Phrases) . "\" OR "
                         . " series2:\"" . implode("\" OR series2:\"",$Phrases) . "\")";
            break;
          case "subject":
          case "schlagwort":
            $MainSearch .= "(topic:\"" .           implode("\" OR topic:\"", $Phrases) . "\" OR "
                         . " topic_unstemmed:\"" . implode("\" OR topic_unstemmed:\"",$Phrases) . "\")";
            break;
          case "class":
            $MainSearch .= "(class_local:\"" . implode("\" OR class_local:\"", $Phrases) . "\")";
            break;
          case "isn":
            $MainSearch .= "(issn:\"" . implode("\" OR issn:\"", $Phrases) . "\" OR "
                         . " ibsn:\"" . implode("\" OR ibsn:\"",$Phrases) . "\")";
            break;
          case "title":
          case "titel":
            $MainSearch .= "(title_short:\"" .          implode("\" OR title_short:\"", $Phrases) . "\" OR "
                         . " title_full_unstemmed:\"" . implode("\" OR title_full_unstemmed:\"",$Phrases) . "\" OR "
                         . " title_full:\"" .           implode("\" OR title_full:\"",$Phrases) . "\" OR "
                         . " title:\"" .                implode("\" OR title:\"",$Phrases) . "\")";
            break;
          case "jahr":
          case "year":
            $MainSearch .= "(publishDate:\"" . implode("\" OR publishDate:\"", $Phrases) . "\")";
            break;
          default:
            $MainSearch .= "(" . $CType . ":\"" . implode("\" OR " . $CType . ":\"",$Phrases) . "\")";
        }
      }
    }

    $MainSearch .= ( trim($MainSearch) != "" && trim($search) != "" ) ? " AND " . $search : $search;

    // Initialize Client
    $dismaxQuery = new SolrDisMaxQuery($MainSearch);

    // Set query parser to EDisMax
    $dismaxQuery->useEDisMaxQueryParser();

    // Field groups & boosting
    if ( $search != "" )
    {
        // All fields for normal searches
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
        ->addQueryField("allfields_whitespace",10)
        ->addQueryField("allfields")
        ->addQueryField("fulltext")
        ->addQueryField("description")
        ->addQueryField("isbn")
        ->addQueryField("issn");      
    }
    else
    {
      // Add Fields based on types
      foreach ( $matches[0] as $index => $complex )
      {
        $CType = strtolower(trim($matches[1][$index]));
        switch ($CType)
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
          case "contents":
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
            ->addQueryField("class_local",100);
            break;
          }
          case "id":
          {
            $dismaxQuery
            ->addQueryField("id",100);
            break;
          }
        }
      }
    }

    // Return fields
    $dismaxQuery
    ->addField('id')
    ->addField('fullrecord');

    if ( $search != "" || count(array_values($matches[0])) >  1 || 
       ( count(array_values($matches[0])) == 1 && $matches[1][0] != "id" ) )
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
            if ( $value != "global" && isset($_SESSION["config_discover"]["datapoolfilter"][$value]) )
            {
              $dismaxQuery
              ->addFilterQuery($_SESSION["config_discover"]["datapoolfilter"][$value]);
            }
            break;
          }

          case "iln":
          {
            if ( $value != "" && $value != "0" && $value != "")
            {
              $dismaxQuery
              ->addFilterQuery("collection_details:GBV_ILN_" . $value);
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

      if ( ! isset($matches[1][0]) || ( isset($matches[1][0]) && $matches[1][0] != "ppnlink" ) )
      {
        $dismaxQuery->setStats(true);
        $dismaxQuery->addStatsField('publishDate');
      }
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
    // $this->CI->appendFile("EDisMax.txt", "http://" . $this->config["hostname"] . "/" . $this->config["path"] . "/select?" . $dismaxQuery);

    // Execute query
    $query_response = $client->query($dismaxQuery);

    // Store answer in file
    // $this->CI->printArray2File($query_response);

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

  public function search($search, $package, $facets)
  {
    // Check params
    if ( isset($params['search'] ) )  $search = $params['search'];
    if ( $search == "" )
    {
      echo "Es ist ein Fehler passiert (NO_SEARCH) !";
      return false;
    }
    $this->search = $search;

    if ( isset($params['package'] ) ) $package = $params['package'];
    if ( $package == "" )
    {
      echo "Es ist ein Fehler passiert (NO_PACKAGE) !";
      return false;
    }
    $this->package = $package;        
    
    if ( isset($params['facets'] ) )  $facets = $params['facets'];
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

  public function getSimilarPublications($ppn)
  {
    // Check params
    if ( $ppn == "" )
    {
      echo "Es ist ein Fehler passiert (NO_PPN) !";
      return false;
    }
    
    $client = new SolrClient($this->config);

    // Initialize Client
    $dismaxQuery = new SolrDisMaxQuery("id:".$ppn);

    // Set query parser to EDisMax
    $dismaxQuery->useEDisMaxQueryParser();

    $dismaxQuery
    ->addQueryField("id",100)
    ->addField('id');

    $dismaxQuery
    ->addMltField("topic")
    ->addMltField("abstract")
    ->addMltField("fulltext")
    ->addMltField("title")
    ->addMltField("title_short")
    ->addMltQueryField("topic",300)
    ->addMltQueryField("abstract",200)
    ->addMltQueryField("fulltext",200)
    ->addMltQueryField("title",75)
    ->addMltQueryField("title_short",100);

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
    // $this->CI->appendFile("EDisMax.txt", "http://" . $this->config["hostname"] . ":" . $this->config["port"] . "/". $this->config["path"] . "/select?" . $dismaxQuery);

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
