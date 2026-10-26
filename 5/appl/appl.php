<?php

class appl extends appldb
{
  
  
  
  protected   $appl;
  protected   $areas;
  protected   $formats;
  public      $formatsBandlist;   
  protected   $formatsMulti;
  protected   $getAreas;
  protected   $searchAreas;
  protected   $searchOptions;

  public function __construct($f3)
  {
    parent::__construct($f3);
    if ( APPDEBUG["FKT"] ) $this->debug("FKT",__METHOD__);

    $this->appl            = $this->f3->get('application.type');
    $this->dbpw            = "";
    $this->formats         = array("Article","Book","Serial Volume","Sound Recording",
                                   "Journal","Musical Score","Microform","Map","Motion Picture",
                                   "Monograph Series", "Manuscript","unknown","Game",
                                   "Projected Medium","Mixed Materials","Data Media","Picture");
    $this->formatsBandlist = array("Journal", "Serial Volume", "Monograph Series");
    $this->formatsMulti    = array("Book", "Journal", "Monograph Series", "Serial Volume",
                                   "unknown", "Musical Score", "Sound Recording", "Map",
                                   "Microform", "Motion Picture", "Manuscript");
    $this->getAreas        = array("export", "findex", "hierarchy", "print", "resolve");
    
    $this->searchAreas     = array("collections", "facets", "findex", "isils");
    
    
    
    
    
    $this->searchOptions   = array("", "person", "signature", "title", "isn");

    if ( ACTION != "batch" )
    {
      
      
      
      
      
      
      
      
      $ClientConfigs = array('account', 'api', 'bibliographic', 'client', 'color', 'facet', 'init', 'item',
                             'bms', 'link', 'meta', 'session', 'style');

      $this->getConfigs($ClientConfigs);

      
      if ( MODULE == "user")
      {
        
        
        
        
        $ClientTexts   = array('level.%', 'infos.%');
      }
      else
      {
        $ClientTexts   = array();
      }
      $this->getTexts($ClientTexts);

      
      if ( MODULE == "user" )  $this->markdownTexts(array("infos."));
    }
  }

  
  
  

  
  
  
  protected function getApplConfigs()
  {
    if ( MODULE == "user")
    {
      return array("levels" => $this->getLevelsDB());
    }
    return array();
  }

  
  
  
  public function getCollectionFromPPN($PPN)
  {
     $FirstDigit      = substr(strpbrk($PPN, "0123456789"),0,1);
     $FirstDigitPos   = strpos($PPN, $FirstDigit);
     return ($FirstDigitPos) ? strtoupper(substr($PPN,0,$FirstDigitPos)) : "KXP";
  }

  public function getCollAndIDFromPPN($PPN)
  {
     $FirstDigit      = substr(strpbrk($PPN, "0123456789"),0,1);
     $FirstDigitPos   = strpos($PPN, $FirstDigit);
     return array( "col" => ($FirstDigitPos) ? strtoupper(substr($PPN,0,$FirstDigitPos)) : "KXP",
                   "id"  => ($FirstDigitPos) ? substr($PPN,$FirstDigitPos) : $PPN );
  }

  public function getHost($URL)
  {
    if ( APPDEBUG["FKT"] ) $this->debug("FKT",__METHOD__);

    if ( $Host = parse_url($URL,PHP_URL_HOST) )
    {
      if ( substr($Host,0,4) == "www.")                  return substr($Host,4);
      return $Host;
    }
  }

  
  
  
  
  
  
  
  
  public function batch($Params)
  {
    
    if (ACTION != "batch")  return;

    if ( isset($Params["client"]) ) $this->client = $Params["client"];
    if ( isset($Params["lang"]) )   $this->lang   = $Params["lang"];
    if ( isset($Params["user"]) )   $this->user   = $Params["user"];

    
    $this->configs = $this->getAdminConfigsDB();
    $this->batchconfigs = array('account', 'api',  'bibliographic', 'client', 'color',    'facet',   'init',
                                'item',    'link', 'bms',           'meta',   'resolver', 'session', 'style');

    
    


    
    $this->texts             = $this->getAdminTextsDB();
    $this->batchtexts        = array();
    $this->batchtextexcludes = array("classlocal.%", "event.%", "family.%", "lang.%", "organisation.%", "person.%", "place.%", "role.%");
  }

  
  
  
}

?>
