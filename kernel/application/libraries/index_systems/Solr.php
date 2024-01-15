<?php

class Solr extends General
{
  protected $CI;
  protected $search;
  protected $package;
  protected $facets;
  protected $result;
  protected $config;
  protected $phoneticsearch = false;
  protected $prefix;
  protected $postfix;
  protected $shards;

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
                     ? $_SESSION["config_general"]["index_system"]["wt"] : "json",
      'timeout'   => 120
    );

    $this->shards = (isset($_SESSION["config_general"]["index_system"]["shards"])   && $_SESSION["config_general"]["index_system"]["shards"] != "" ) 
                     ? $_SESSION["config_general"]["index_system"]["shards"] : "";

    $this->prefix = (isset($_SESSION["config_discover"]["datapoolfilter"]["prefix"])   && $_SESSION["config_discover"]["datapoolfilter"]["prefix"] != "" ) 
                     ? $_SESSION["config_discover"]["datapoolfilter"]["prefix"] : "";
    $this->postfix= (isset($_SESSION["config_discover"]["datapoolfilter"]["postfix"])   && $_SESSION["config_discover"]["datapoolfilter"]["postfix"] != "" ) 
                     ? $_SESSION["config_discover"]["datapoolfilter"]["postfix"] : "";
  }

  // ********************************************
  // ************** Side-Functions **************
  // ********************************************
  private function solr_before($search)
  {
    $search = str_replace(array("%20"), " ", $search);
    return ($search);
  }

  private function CleanID($string)
  {
    if ( is_array($string) )
    {
      foreach ($string as &$one)
      {
        $one = $this->CleanID($one);
      }
      return $string;
    }
    else
    {
      $string = trim($string, " '\"");
      return substr($string,0,8) . preg_replace("/[^A-Za-z0-9_]/", "", substr($string,8));
    }
  }

  private function solr_edismax($search,$package,$facets)
  {

    $client = new SolrClient($this->config);

    // Add prefix & postfix
    if ( strpos($search, "(") === false && strpos($search, ")") === false && strpos($search, ":") === false )
    {
      $search = $this->prefix . trim($search) . $this->postfix;
    }

    if ( substr($search,0,10) == "foreignid(" && substr($search,strlen($search)-1,1) == ")" )
    {
      $Tmp = explode(",",substr($search, 10, strlen($search)-11));
      foreach ($Tmp as &$One) 
      {
        $One = $this->CleanID($One);
      }
      $matches = array
      (
        array($search),
        array("foreignid"),
        array(implode(",",$Tmp))
      );
      $search = "";
    }
    else
    {
      // Support DOI brackets
      $search .= " ";

      // Find complex search phrases and move them to $matches
      // First: field(phrase)
      preg_match_all("/([A-Za-z0-9_]+)\(([^))]+)\)\s/", $search, $matches);
      foreach ( $matches[0] as $one )
      {
        $search = str_replace($one, "", $search);
      }
  
      // Second: field:(phrase)
      preg_match_all("/([A-Za-z0-9_]+):\(([^))]+)\)\s/", $search, $matchescolon);
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
      preg_match_all("/([A-Za-z0-9_]+):([^\s]+)\s/", $search, $matchescolon2);
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
    }

    // $this->CI->printArray2File($matches);

    // Remove not allowed complex phrases based on used key
    foreach ( $matches[1] as $index => $key )
    {
      if ( ! in_array(strtolower(trim($key)), array("abruf", "acqdate","author","autor","call","class", "classlocal",
        "client","collection","collection_details","contents","corporation","country","erwdatum","foreignid","format","format2",
        "genre","id","inhalt","isn","jahr","koerper","land","langcode","language","location","mandant","norm","ppn","ppnlink","prov","publisher",
        "reihe","sachgebiet","schlagwort","series","signatur","signature","sprache","sprachcode","standort","subject","thema","titel",
        "title","topic","verlag","year")) )
      {
        unset($matches[0][$index]);
      }
    }

    // Now loop over complex search phrased and add simple search word
    // to build query string MainSearch
    $MainSearch = "";
    $search     = trim($search);
    // $this->CI->printArray2File($search);
    foreach ( $matches[0] as $index => $complex )
    {
      $CType = strtolower(trim($matches[1][$index]));
      $CText = trim($matches[2][$index]);

      //First get phrases in "" and remove them
      preg_match_all("/\"([^\"]+)\"/", $CText, $Cmatches);
      foreach ( $Cmatches[0] as $one )
      {
        $CText = str_replace($one, "", $CText);
      }
      
      // Second get remaininhg phrases split by ,
      $Phrases = $Cmatches[1] + array_map('trim', array_filter(explode(",", $CText)));

      if ( $index > 0 ) $MainSearch .= " AND ";
      
      foreach ( $Phrases as &$P )
      {
        $P = str_replace(" ", "\ ", $P);
      }

      if ( count($Phrases) == 1)
      {
        switch ($CType)
        {
          case "author":
          case "autor":
            if ( $this->phoneticsearch )
            {
              $MainSearch .= "(author:"      . $Phrases[0] . " OR author2:"           . $Phrases[0] . "" 
                        . " OR authorSound:" . $Phrases[0] . " OR author_os_txtP_mv:" . $Phrases[0] . ")";
            }
            else
            {
              $MainSearch .= "(author:" . $Phrases[0] . " OR author2:" . $Phrases[0] . " OR author_os_txtP_mv:" . $Phrases[0] . ")";
            }
            break;
          case "foreignid":
            $MainSearch .= "(foreign_ids_str_mv:" . $Phrases[0] . ")";
            break;
          case "series":
          case "reihe":
            $MainSearch .= "(series:" . $Phrases[0] . " OR series2:" . $Phrases[0] . ")";
            break;
          case "subject":
          case "schlagwort":
            $MainSearch .= "(topic:" . $Phrases[0] . " OR GND_str_mv:" . $Phrases[0] . " OR topic_unstemmed:" . $Phrases[0] . ")";
            break;
          case "koerper":
          case "corporation":
            $MainSearch .= "(author_corporate:" . $Phrases[0] . ")";
            break;
          case "class":
            $MainSearch .= "(class:" . $Phrases[0] . ")";
            break;
          case "classlocal":
            if ( isset($_SESSION["iln"]) )  $MainSearch .= "(notation_local_iln_str_mv:" . $_SESSION["iln"] . "\:" . $Phrases[0] . ")";
            break;
          case "isn":
            $MainSearch .= "(issn:" . $Phrases[0] . " OR isbn:" . $Phrases[0] . ")";
            break;
          case "format":
            $MainSearch .= "(format_phy_str_mv:" . $Phrases[0] . ")";
            break;
          case "format2":
            $MainSearch .= "(format:" . $Phrases[0] . ")";
            break;
          case "norm":
            $MainSearch .= "(normlink_prefix_str_mv:\(DE\-627\)" . $this->CleanID($Phrases[0]) . ")";
            break;
          case "topic":
          case "thema":
            if ( strpos($Phrases[0], "-") !== false )
            {
              // topic_browse:["sfb ERD 350" TO "sfb ERD 376"]
              // topic(SFB POL 965 - 999)
              $Tmp = explode("-", $Phrases[0]);
              if ( strpos($Tmp[0], " ") !== false )
              {
                $Wmp    = explode(" ", trim($Tmp[0]));
                $Wmp[0] = strtolower($Wmp[0]);
                $Left   = trim(array_pop($Wmp));
                $Base   = trim(implode(" ", $Wmp));
              }
              else
              {
                $Base = trim($Tmp[0]);
                $Left = "";
              }
              if ( strpos($Tmp[1], " ") !== false )
              {
                $Wmp    = explode(" ", trim($Tmp[1]));
                $Right  = trim(array_pop($Wmp));
              }
              else
              {
                $Right = trim($Tmp[1]);
              }
              $MainSearch .= "(topic_browse:[" . $Base . " " . $Left. " TO " . $Base . " " . $Right . "])";
            }
            else
            {
              $MainSearch .= "(topic:" . $Phrases[0] . ")";
            }
            break;
          case "country":
          case "land":
            $MainSearch .= "(countryofpublication_str_mv:" . $Phrases[0] . ")";
            break;
          case "language":
          case "sprache":
            $MainSearch .= "(language:" . $Phrases[0] . ")";
            break;
          case "langcode":
          case "sprachcode":
            $MainSearch .= "(lang_code:" . $Phrases[0] . ")";
            break;
          case "erwdatum":
          case "acqdate":
            if ( isset($_SESSION["iln"]) )  $MainSearch .= "(selektneu_str_mv:" . $_SESSION["iln"] . "@" . $Phrases[0] . ")";
            break;
          case "abruf":
          case "call":
            if ( isset($_SESSION["iln"]) )  $MainSearch .= "(abrufzeichen_iln_scis_mv:" . $_SESSION["iln"] . "@" . $Phrases[0] . ")";
            break;
          case "mandant":
          case "client":
            if ( isset($_SESSION["iln"]) )  $MainSearch .= "(selectbib_iln_str_mv:" . $_SESSION["iln"] . "@" . $Phrases[0] . ")";
            break;
          case "genre":
            $MainSearch .= "(genre_facet:" . $Phrases[0] . ")";
            break;
          case "prov":
            $MainSearch .= "(provenience_txtP_mv:" . $Phrases[0] . ")";
            break;
          case "signatur":
          case "signature":
            $MainSearch .= "(signature_iln_str_mv:" . $_SESSION["iln"] . "\:" . $Phrases[0] . ")";
            break;
          case "title":
          case "titel":
            if ( $this->phoneticsearch )
            {
              $MainSearch .= "(title_short:" . $Phrases[0] . " OR title_full_unstemmed:" . $Phrases[0] . " OR title_full:" . $Phrases[0] . " OR title:" . $Phrases[0] . " OR title_fullSound:" . $Phrases[0] . ")";
            }
            else
            {
              $MainSearch .= "(title_short:" . $Phrases[0] . " OR title_full_unstemmed:" . $Phrases[0] . " OR title_full:" . $Phrases[0] . " OR title:" . $Phrases[0] . ")";
            }
            break;
          case "standort":
          case "location":
            $MainSearch .= "(standort_iln_str_mv:" . $_SESSION["iln"] . "\:" . $Phrases[0] . ")";
            break;
          case "jahr":
          case "year":
            $MainSearch .= "(publishDateSort:" . $Phrases[0] . ")";
            break;
          case "ppnlink":
            $MainSearch .= "(id:" . $this->CleanID($Phrases[0]) . ")";
            break;
          case "id":
          case "ppn":
            $MainSearch .= "(id:" . $this->CleanID($Phrases[0]) . ")";
            break;
          default:
            $MainSearch .= $CType . "\:" . $Phrases[0] . "";
        }
      }
      else
      {
        switch ($CType)
        {
          case "author":
          case "autor":
            if ( $this->phoneticsearch )  
            {
              $MainSearch .= "(author:"            . implode(" OR author:", $Phrases)           . " OR "
                           . " author2:"           . implode(" OR author2:",$Phrases)           . " OR "
                           . " author_os_txtP_mv:" . implode(" OR author_os_txtP_mv:",$Phrases) . " OR "
                           . " authorSound:"       . implode(" OR authorSound:",$Phrases)       . ")";
            }
            else
            {
              $MainSearch .= "(author:"            . implode(" OR author:", $Phrases)           . " OR "
                           . " author2:"           . implode(" OR author2:",$Phrases)           . " OR "
                           . " author_os_txtP_mv:" . implode(" OR author_os_txtP_mv:",$Phrases) . ")";
            }
            break;
          case "foreignid":
            $MainSearch .= "(foreign_ids_str_mv:" . implode(" OR foreign_ids_str_mv:", $this->CleanID($Phrases)) . ")";
            break;
          case "series":
          case "reihe":
            $MainSearch .= "(series:" .  implode(" OR series:", $Phrases) . " OR "
                         . " series2:" . implode(" OR series2:",$Phrases) . ")";
            break;
          case "subject":
          case "schlagwort":
            $MainSearch .= "(topic:"           . implode(" OR topic:", $Phrases)           . " OR "
                         . " GND_str_mv:"      . implode(" OR topic_unstemmed:",$Phrases)  . " OR "
                         . " topic_unstemmed:" . implode(" OR topic_unstemmed:",$Phrases)  . ")";
            break;
          case "koerper":
          case "corporation":
            $MainSearch .= "(author_corporate:" . implode(" OR author_corporate:",$Phrases) . ")";
            break;
          case "class":
            $MainSearch .= "(class:" . implode(" OR class:", $Phrases) . ")";
            break;
          case "classlocal":
            if ( isset($_SESSION["iln"]) )  $MainSearch .= "(notation_local_iln_str_mv:" . $_SESSION["iln"] . "\:" . implode(" OR notation_local_iln_str_mv:" . $_SESSION["iln"] . "\:", $Phrases) . ")";
            break;
          case "isn":
            $MainSearch .= "(issn:" . implode(" OR issn:", $Phrases) . " OR "
                         . " isbn:" . implode(" OR isbn:",$Phrases) . ")";
            break;
          case "format":
            $MainSearch .= "(format_phy_str_mv:" . implode(" OR format_phy_str_mv:", $Phrases) . ")";
            break;
          case "format2":
            $MainSearch .= "(format:" . implode(" OR format:", $Phrases) . ")";
            break;
          case "norm":
            $MainSearch .= "(normlink_prefix_str_mv:\(DE\-627\)" . implode(" OR normlink_prefix_str_mv:\(DE\-627\)", $this->CleanID($Phrases)) . ")";
            break;
          case "thema":
          case "topic":
            $MainSearch .= "(topic:" . implode(" OR topic:", $Phrases) . ")";
            break;
          case "country":
          case "land":
            $MainSearch .= "(countryofpublication_str_mv:" . implode(" OR countryofpublication_str_mv:", $Phrases) . ")";
            break;
          case "language":
          case "sprache":
            $MainSearch .= "(language:" . implode(" OR language:", $Phrases) . ")";
            break;
          case "langcode":
          case "sprachcode":
            $MainSearch .= "(lang_code:" . implode(" OR lang_code:", $Phrases) . ")";
            break;
          case "erwdatum":
          case "acqdate":
            if ( isset($_SESSION["iln"]) )  $MainSearch .= "(selektneu_str_mv:" . $_SESSION["iln"] . "@" . implode(" OR selektneu_str_mv:" . $_SESSION["iln"] . "@", $Phrases) . ")";
            break;
          case "abruf":
          case "call":
            if ( isset($_SESSION["iln"]) )  $MainSearch .= "(abrufzeichen_iln_scis_mv:" . $_SESSION["iln"] . "@" . implode(" OR abrufzeichen_iln_scis_mv:" . $_SESSION["iln"] . "@", $Phrases) . ")";
            break;
          case "mandant":
          case "client":
            if ( isset($_SESSION["iln"]) )  $MainSearch .= "(selectbib_iln_str_mv:" . $_SESSION["iln"] . "@" . implode(" OR selectbib_iln_str_mv:" . $_SESSION["iln"] . "@", $Phrases) . ")";
            break;
          case "genre":
            $MainSearch .= "(genre_facet:" . implode(" OR genre_facet:", $Phrases) . ")";
            break;
          case "prov":
            $MainSearch .= "(provenience_txtP_mv:" . implode(" OR provenience_txtP_mv:", $Phrases) . ")";
            break;
          case "signatur":
          case "signature":
            $MainSearch .= "(signature_iln_str_mv:" . $_SESSION["iln"] . "\:" . implode(" OR signature_iln_str_mv:" . $_SESSION["iln"] . "\:", $Phrases) . ")";
            break;
          case "title":
          case "titel":
            if ( $this->phoneticsearch )
            {
              $MainSearch .= "(title_short:" .          implode(" OR title_short:", $Phrases) . " OR "
                           . " title_full_unstemmed:" . implode(" OR title_full_unstemmed:",$Phrases) . " OR "
                           . " title_full:" .           implode(" OR title_full:",$Phrases) . " OR "
                           . " title:" .                implode(" OR title:",$Phrases) . " OR "
                           . " title_fullSound:" .      implode(" OR title_fullSound:",$Phrases) . ")";
            }
            else
            {
              $MainSearch .= "(title_short:" .          implode(" OR title_short:", $Phrases) . " OR "
                           . " title_full_unstemmed:" . implode(" OR title_full_unstemmed:",$Phrases) . " OR "
                           . " title_full:" .           implode(" OR title_full:",$Phrases) . " OR "
                           . " title:" .                implode(" OR title:",$Phrases) . ")";
            }
            break;
          case "standort":
          case "location":
            $MainSearch .= "(standort_iln_str_mv:" . $_SESSION["iln"] . "\:" . implode(" OR standort_iln_str_mv:" . $_SESSION["iln"] . "\:", $Phrases) . ")";
            break;
          case "jahr":
          case "year":
            $MainSearch .= "(publishDateSort:" . implode(" OR publishDateSort:", $Phrases) . ")";
            break;
          case "ppnlink":
            $MainSearch .= "(ppnlink:" . implode(" OR ppnlink:", $this->CleanID($Phrases)) . ")";
            break;
          case "ppn":
          case "id":
            $MainSearch .= "(id:" . implode(" OR id:", $this->CleanID($Phrases)) . ")";
            break;
          default:
            $MainSearch .= "(" . $CType . ":" . implode(" OR " . $CType . ":",$Phrases) . ")";
        }
      }
    }

    $MainSearch .= ( trim($MainSearch) != "" && trim($search) != "" && trim($search) != "*" ) ? " AND " . $search : $search;

    // Initialize Client
    $dismaxQuery = new SolrDisMaxQuery($MainSearch);

    // Set query parser to EDisMax
    $dismaxQuery->useEDisMaxQueryParser();

    // Field groups & boosting
    if ( $search != "" )
    {
        // All fields for normal searches
        $dismaxQuery
        ->addQueryField("title_short",770)
        ->addQueryField("title_shortGer",760)
        ->addQueryField("title_full_unstemmed",750)
        ->addQueryField("title_full",740)
        ->addQueryField("title_fullGer",730)
        ->addQueryField("title",720)
        ->addQueryField("title_alt",710)
        ->addQueryField("title_new",700)
        ->addQueryField("topic_unstemmed",610)
        ->addQueryField("topic",600)
        ->addQueryField("author",530)
        ->addQueryField("author2",520)
        ->addQueryField("author_fuller",510)
        ->addQueryField("author_os_txtP_mv",500)
        ->addQueryField("geographic",210)
        ->addQueryField("genre_facet",200)
        ->addQueryField("series",110)
        ->addQueryField("series2",100)
        ->addQueryField("contents",10)
        ->addQueryField("allfields_unstemmed",10)
        ->addQueryField("fulltext_unstemmed",10)
        ->addQueryField("allfields_whitespace",10)
        ->addQueryField("allfields",10)
        ->addQueryField("allfieldsGer",10)
        ->addQueryField("fulltext",5)
        ->addQueryField("description",5)
        ->addQueryField("GND_txt_mv",1)
        ->addQueryField("isbn",1)
        ->addQueryField("issn",1);

        if ( $this->phoneticsearch )
        {
          $dismaxQuery
          ->addQueryField("title_fullSound",390)
          ->addQueryField("authorSound",2900)
          ->addQueryField("allfieldsSound",9)
          ->addQueryField("fulltextSound",9);
        }
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
            ->addQueryField("author2",50)
            ->addQueryField("author_os_txtP_mv",50)
            ->addQueryField("author_additional",10);

            if ( $this->phoneticsearch )
            {
              $dismaxQuery
              ->addQueryField("authorSound",90);
            }
            break;
          }
          case "isn":
          {
            $dismaxQuery
            ->addQueryField("isbn",1)
            ->addQueryField("issn",1);
            break;
          }
          case "subject":
          case "schlagwort":
          {
            $dismaxQuery
            ->addQueryField("topic_unstemmed",150)
            ->addQueryField("topic",100)
            ->addQueryField("geographic",50)
            ->addQueryField("genre_facet",50)
            ->addQueryField("era",40)
            ->addQueryField("GND_str_mv", 10);
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
            ->addQueryField("series2",100);

            if ( $this->phoneticsearch )
            {
              $dismaxQuery
              ->addQueryField("title_fullSound",2);
            } 
            break;
          }
          case "series":
          case "reihe":
          {
            $dismaxQuery
            ->addQueryField("series",100)
            ->addQueryField("series2",100);
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
            ->addQueryField("publishDateSort",100);
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
            ->addQueryField("class",100);
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
    ->addField('collection')
    ->addField('collection_details')
    ->addField('format_phy_str_mv')
    ->addField('remote_bool')
    ->addField('fullrecord_marcxml');

    // Add shards
    if ( $this->shards != "" )
    {
      $dismaxQuery->addParam("shards", $this->shards);
    }

    if ( $search != "" || count(array_values($matches[0])) >  1 || 
       ( count(array_values($matches[0])) == 1 && !in_array($matches[1][0], array("id","foreignid") ) ) )
    {
      // Facet fields (only for new searches (package=1), not for inkremential searches (package=0, package >=2)
      if ( $package == 1 && $facets )
      {
        $dismaxQuery->setFacet(true);
        $dismaxQuery
        ->addFacetField('remote_bool')
        ->addFacetField('format_phy_str_mv');
        $dismaxQuery
        ->setFacetLimit(20);

        $dismaxQuery
        ->setParam('spellcheck','true')
        ->setParam('shards.qt','/spell');
      }

      // Filter 
      foreach ( $_SESSION["filter"] as $key => $value)
      {
        switch ($key )
        {
          case "datapool":
          {
            if ( isset($_SESSION["config_discover"]["datapoolfilter"][$value]) && $_SESSION["config_discover"]["datapoolfilter"][$value] != "" )
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
              if (isset($_SESSION["config_discover"]["filter"]["excludenopublishdate"]) && $_SESSION["config_discover"]["filter"]["excludenopublishdate"] == "1")
              {
                $dismaxQuery
                ->addFilterQuery('(publishDateSort:' . $value . ')');
              }
              else
              {
                $dismaxQuery
                ->addFilterQuery('(publishDateSort:' . $value . ') OR (*:* NOT publishDateSort:*)');
              }
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
          case "sort":
          {
            if ( $value == "yearasc" )
            {
              $dismaxQuery
              ->addSortField('publishDateSort', SolrQuery::ORDER_ASC);
            }
            if ( $value == "yeardesc" )
            {
              $dismaxQuery
              ->addSortField('publishDateSort', SolrQuery::ORDER_DESC);
            }
          }
        }
      }

      if ( ! isset($matches[1][0]) || ( isset($matches[1][0]) && $matches[1][0] != "ppnlink" ) )
      {
        $dismaxQuery->setStats(true);
        $dismaxQuery->addStatsField('publishDateSort');
      }
    }    

    // Package start & end
    if ( $package == 0 )
    {
      $dismaxQuery->setStart(0);
      $dismaxQuery->setRows(100);
    }
    else
    {
      $dismaxQuery->setStart((($package-1)*50));
      $dismaxQuery->setRows(50);
    }

    // Store query in session
    $_SESSION["query"] = (string) $dismaxQuery;

    // $this->CI->printArray2File($_SESSION["query"]);

    // Execute query
    try 
    {
      $query_response = $client->query($dismaxQuery);

      // Store answer
      return (array_merge((array) $query_response->getResponse(), array("query"=>"http://" . $this->config["hostname"] . "/" . $this->config["path"] . "/select?" . $dismaxQuery)));
    }
    catch (Exception $e) 
    {
      return (array("query"=>"http://" . $this->config["hostname"] . "/" . $this->config["path"] . "/select?" . $dismaxQuery));
    }
  }

  private function solr_after($result,$package)
  {
    // fill container
    if ( isset($result["error"]) )
    {
      $container = array
      (
        "status"  => (isset($result["error"]["code"]))           ? $result["error"]["code"]           : "0",
        "message" => (isset($result["error"]["msg"]))            ? $result["error"]["msg"]            : "",
        "time"    => (isset($result["responseHeader"]["QTime"])) ? $result["responseHeader"]["QTime"] : "0"
      );
    }
    else
    {
      $container = array
      (
        "status"  => (isset($result["error"]["code"]))              ? $result["error"]["code"]                : "0",
        "results" => isset($result["response"]["docs"])             ? $result["response"]["docs"]             : array(),
        "qtime"   => isset($result["responseHeader"]["QTime"])      ? str_replace(".", ",",round($result["responseHeader"]["QTime"] / 1000,2)) : "",
        "hits"    => isset($result["response"]["numFound"])         ? $result["response"]["numFound"]         : "0",
        "hitspack"=> isset($result["response"]["docs"])             ? count($result["response"]["docs"])          : "0",
        "facets"  => isset($result["facet_counts"]["facet_fields"]) ? $result["facet_counts"]["facet_fields"] : "",
        "stats"   => isset($result["stats"]["stats_fields"])        ? $result["stats"]["stats_fields"]        : "",
        "start"   => ($package != 0 )                               ? ($package-1)*50                         : 0,
        "query"   => isset($result["query"])                        ? htmlentities($result["query"])          : "",
        "spell"   => isset($result["spellcheck"]["suggestions"])    ? $result["spellcheck"]["suggestions"]    : ""
      );
    }

    // return container
    return $container;
  }

  // ********************************************
  // ************** Main-Functions **************
  // ********************************************

  public function search($search, $package, $facets, $params)
  {
    // Check params
    if ( $search == "" )
    {
      echo "Es ist ein Fehler passiert (NO_SEARCH) !";
      return false;
    }
    $this->search = $search;

    if ( $package == "" )
    {
      echo "Es ist ein Fehler passiert (NO_PACKAGE) !";
      return false;
    }
    $this->package = $package;        
    
    if ( $package == "" )
    {
      echo "Es ist ein Fehler passiert (NO_FACETS) !";
      return false;
    }
    $this->facets = $facets;        

    if ( isset($params['phonetic'] ) )  $this->phoneticsearch = $params['phonetic'];
    
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
    // $this->CI->printArray2File((string) $dismaxQuery);

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

    // Return Data
    return ( $PPNList );
  }
}
