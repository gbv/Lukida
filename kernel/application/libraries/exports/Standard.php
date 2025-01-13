<?php

class Standard extends General
{
  protected $CI;
  private   $configExport, $configGeneral;

  public function __construct()
  {
    // Assign the CodeIgniter super-object
    $this->CI =& get_instance();
  }

  public function linkresolver($ppn)
  {
    $this->PPN = $ppn;
    $this->contents = $_SESSION["data"]["results"][$this->PPN];
    $this->configExport   = $_SESSION["config_general"]["export"];
    $this->configGeneral  = $_SESSION["config_general"]["general"];

    $linkarray = array();

    $resolver_on = empty($this->configExport["resolverlink"]) ? false : true;
    $jop_on      = empty($this->configExport["joplink"]) ? false : true;

    //Set the variable prio:
    $fulltextPrios =  "";
    if( !empty($this->configExport["fulltextprios"]) )
    {
      $fulltextPrios      = $this->configExport["fulltextprios"] ;
      $fulltextPriosArray = explode(",",$fulltextPrios);
      if( !empty($fulltextPriosArray[0]) && $fulltextPriosArray[0] === "multi" && 
          ( count($fulltextPriosArray) == 1 || ( strpos($fulltextPrios,"resolver")!== false && strpos($fulltextPrios,"jop")!== false ))
        )
      {
        $prio = "multi";
      }
      else
      {
        //Min. array[0]=>"single" or array[0]=>"multi":
        foreach( $fulltextPriosArray as $prio )
        {
          if( strpos("resolver,jop",$prio) !== false )
          {  
            break;
          }
        }
      }
    }
    else 
    {
      $prio = "multi";
    }

    //Build the return variable:
    if( $prio == "resolver" || $prio == "multi"  || $prio == "single" ) 
    {
      if( $resolver_on )
      {
          $linkarray = $this->get_resolver_link($this->contents);  
      }
      if( $jop_on && ( $prio == "multi" || ( empty($linkarray) && ( $prio == "single" || strpos($fulltextPrios,"jop") !== false ) ) ) 
          && ( $Link = $this->get_jop_link($this->contents) ) != "" )
      {
        //Chr(32),%20 space    chr(39), %27 Single quote    chr(34), %22 Double Quotes    Chr(38), %26 &
        $linkarray["jop"] = str_replace(array(chr(39),chr(34)), array("%27","%22"), $Link);
      } 
    } 
    elseif( $prio == "jop" )
    {
      //Single jop (or resolver)
      if( $jop_on && ( $Link = $this->get_jop_link($this->contents) ) != "" )
      {
        //Chr(32),%20 space    chr(39), %27 Single quote    chr(34), %22 Double Quotes    Chr(38), %26 &
        $linkarray["jop"] = str_replace(array(chr(39),chr(34)), array("%27","%22"), $Link); 
      }
      elseif( $resolver_on && strpos($fulltextPrios,"resolver") !== false )
      {
        $linkarray = $this->get_resolver_link($this->contents);
      }
    } 
    return $linkarray;
  }

  public function exportfile($data, $format)
  {
    return $this->getOpenURLmetaData($data,$format);
  }
 
  public function exportlink($data, $format)
  {
    $openurlEntry    = "";    $openurlBase     = "";
    $openurlReferer  = "";    $openurlMetadata = "";    
    
    switch ($format) 
    {
      case "refworks"     : if ($_SESSION["config_general"]["export"]["refworks"] == "1")
								$openurlBase = "https://www.refworks.com/express/expressimport.asp";
        break;
      case "zotero"       : if ($_SESSION["config_general"]["export"]["zotero"] == "1")
								$openurlBase = "ctx_ver=Z39.88-2004";
        break;
    }

    if ($openurlBase != "") 
    {
      $zoteroFormats = array
      (
          "article"      =>"journal&rft.genre=article",     "book"          =>"book",
          "conference"   =>"dc&rft.type=presentation",      "inbook"        =>"book&rft.genre=bookitem",
          "journal"      =>"journal",                       "manuscript"    =>"dc&rft.type=manuscript",
          "mastersthesis"=>"dc&rft.type=thesis",            "map"           =>"dc&rft.type=map",
          "motionpicture"=>"dc&rft.type=film",              "musicalscore"  =>"dc&rft.type=audioRecording",
          "phdthesis"    =>"dc&rft.type=thesis",            "soundrecording"=>"dc&rft.type=audioRecording",
          "techreport"   =>"dc&rft.type=report"        
      );
      $libGenre951   = isset($data["contents"]["951"][0]) ? $this->getArrValue($data["contents"]["951"][0], "a") : "";
      $genreZotero   = isset($zoteroFormats[$data["format"]]) ? $data["format"] : ((substr($data["leader"],7,1) == "m" || $libGenre951 == "ST")
                       ? ((substr($data["leader"],19,1) == "b" || substr($data["leader"],19,1) == "c") ? "inbook" : "book") 
                       : ($libGenre951 == "AR" ? "article" : ($libGenre951 == "JT" ? "journal" : $data["format"] ))); 

      $openurlReferer  = (isset($_SESSION["config_general"]["export"]["openurlreferer"]) &&
                                $_SESSION["config_general"]["export"]["openurlreferer"] != "") 
                              ? $_SESSION["config_general"]["export"]["openurlreferer"] : "Lukida";

      $openurlEntry    = $openurlBase . ($format == "zotero" ? "&rfr_id=info:sid/" : "?sid=") . 
                          "GBV:" . $openurlReferer . "&ctx_enc=info:ofi/enc:UTF-8" . 
                          ($format == "zotero" ? ("&rft_val_fmt=info:ofi/fmt:kev:mtx:" . (isset($zoteroFormats[$genreZotero]) ? $zoteroFormats[$genreZotero] : $genreZotero)) : "") . 
						  ($format == "zotero" ? ("&rft_id=" . base_url() . "id%7Bcolon%7D" . $data["id"]) : "");

      $openurlMetadata = $this->getOpenURLmetaData($data,(($format == "refworks") ? "zotero" : $format));

      $link            = $openurlEntry . (($format == "refworks") ? str_replace("&rft.","&",$openurlMetadata) : $openurlMetadata);
    }
    else 
    { 
      $link = null;
    }
    // Link/URL zurueckliefern:
    return $link;
  }
/* 
**********************************
 * Journal Online & Print (JOP) *
**********************************
*/ 
  protected function get_jop_link($data)
  {
    $zdbid		= "";
    if(empty($data["issn"]) && empty($data["isbn"]))
    {
		if (!empty($data["contents"]["016"]))
		{
			foreach ( $data["contents"]["016"] as $subArray016 )
			{
				if ( isset($subArray016["1"]["a"]) && $subArray016["1"]["a"] != "" && isset($subArray016["2"]["2"]) && $subArray016["2"]["2"] == "DE-600" )
				{	
					$zdbid = $subArray016["1"]["a"];
					break;
				}
			}
		}
		elseif( !empty($data["contents"]["773"][0]) )
		{
			array_walk_recursive($data["contents"]["773"][0], function($value,$key) use (&$zdbid){if($key === "w" && substr($value,0,8) === "(DE-600)") $zdbid = substr($value,8);}, $zdbid);
		}
		elseif( !empty($data["contents"]["830"][0]) )
		{
			array_walk_recursive($data["contents"]["830"][0], function($value,$key) use (&$zdbid){if($key === "w" && substr($value,0,8) === "(DE-600)") $zdbid = substr($value,8);}, $zdbid);
		}
    }
    $ezbLink	= "";
    
    if ( !empty($data["issn"]) || !empty($data["isbn"]) || !empty($zdbid) )			
    {
      $ezbbibid			= empty($this->configGeneral["ezbbibid"]) ? null
    					  : $this->configGeneral["ezbbibid"] ;
    
      $isil             = empty($this->configGeneral["isil"]) ? null
                          : $this->configGeneral["isil"] ;
    					  
      $bibparam			= utf8_encode(isset($ezbbibid) ? ("bibid%3D" . $ezbbibid) : (isset($isil) ? ("%26isil%3D" . $isil) : ""));
    
      $openurlReferer   = empty($this->configExport["openurlreferer"]) ? "Lukida"
                          : $this->configExport["openurlreferer"] ; 
    
      $openurlMetadata  = $this->getOpenURLmetaData($data, "jop");
    
      $ezbLinkExtension = "sid=GBV:" . $openurlReferer . $openurlMetadata .
                          ("&pid=online%3D1%26" . $bibparam . ((empty($data["issn"]) && empty($data["isbn"]) && !empty($zdbid)) ? ("%26zdbid%3D" . $zdbid) : ""));
    					  
      $ezbLink          = "https://services.dnb.de/fize-service/gvr/full.xml?" . $ezbLinkExtension;

      $ezbTarget        = $this->getJOP_Full($ezbLink, str_replace('%3D', '=', $bibparam));	
      return $ezbTarget;
    }
    return "";
  }

  protected function getJOP_Full($link, $bibparam)
  {	  
    $joponlyfulltext = (isset($this->configExport["joponlyfulltext"]) &&
                       $this->configExport["joponlyfulltext"] == "1") 
                       ? true : null;
    $ezb_xml = @simplexml_load_file($link);
  	if (!empty($ezb_xml))
    { 
      if (!isset($ezb_xml->Full->Error))
      {
		$ref = ""; $refUrl = ""; $refUrl = "";

		if ( $ezb_xml && isset($ezb_xml->Full->ElectronicData->References->Reference[0]) && 
			 $ref = $ezb_xml->Full->ElectronicData->References->Reference[0] )
		{
			//EZB-website to the title with other possible links:
			$refUrl   = isset($ref->URL) ? $ref->URL : "";
			$refLabel = isset($ref->Label) ? $ref->Label : "";
		}
		if ( $ezb_xml && isset($joponlyfulltext) && isset($ezb_xml->Full->ElectronicData->ResultList->Result) &&
			 !empty($ezb_xml_result = $ezb_xml->Full->ElectronicData->ResultList->Result) )	
		{
          $i = 0; $accessUrl = "";
          foreach( $ezb_xml_result as $aResult )
          { 
			$resultAdditional = ""; $resultStatus = "";  $resultAccessLevel  = ""; $resultAccessURL = ""; 
            $resultAdditional   = !empty((string)$aResult->Additionals->Additional) ? (string)$aResult->Additionals->Additional : "";
            $resultStatus       = !empty((string)$aResult['state']) ? (string)$aResult['state'] : "0";
            $resultAccessLevel  = !empty((string)$aResult->AccessLevel) ? (string)$aResult->AccessLevel : "";
            $resultAccessURL    = !empty((string)$aResult->AccessURL) ? (string)$aResult->AccessURL : "";
		    //Get the results and select AccessURL. 
		    //State "4" = "not on-licence",  "5" except period.
		    //AccessLevel "homepage" = no a good accurate result.
            if( !empty($resultAccessURL) && ($resultStatus == "0" || $resultStatus == "1" || $resultStatus == "2") )
            {
              if( $resultAccessLevel != "homepage" )
              {
                if( strpos($resultAdditional,"DFG-gefördert") !== false )
                {
                  return $resultAccessURL; //Link to the full text
                }
                else
                {
                  $accessUrl = $resultAccessURL; 
                }
              }
            }
          }
          return $accessUrl;
        }
        elseif ( $refUrl != "" && $refLabel == "EZB-Opac" )
        {
          //EZB-website to the title with other possible links:
          return $refUrl . "&" . $bibparam;
        }
        else return "";
	  }
      else return "";
	  //return array_values((array)$returnValue)[0];		
    }
	return "";
  }  
/*
***************************************
 * Linkresolver: SFX, Ovid oder ReDI *
***************************************
*/
  protected function get_resolver_link($data)
  {
    $return = array();

    if (!empty($openurlBase = $this->configExport["resolverbase"]) )
    {
      $openurlReferer   = empty($this->configExport["openurlreferer"]) ? "Lukida"
                           : $this->configExport["openurlreferer"] ;
      $openurlEntry     = $openurlBase . "?sid=GBV:" . $openurlReferer 
                          . (strpos($openurlBase,"redi") === false ? "&ctx_enc=info:ofi/enc:UTF-8" : "");
      $openurlMetadata  = $this->getOpenURLmetaData($data, "resolver");
      $resolverLink     = $openurlEntry . $openurlMetadata;
      $onlyFulltex      = (isset($this->configExport["resolveronlyfulltext"]) && $this->configExport["resolveronlyfulltext"] == "1") ? true : false;

      if( strpos($openurlBase,"redi") !== false )
      {
        $resolver     = "redi";
        $resolverLink = str_replace("&rft.","&",$resolverLink);
        //spaces cause HTTP 400 errors 
        $resolverLink = str_replace(" ","%20",$resolverLink);
      }
      elseif( strpos($openurlBase,"ovid") !== false ) 
        $resolver = "ovid";
      else 
        $resolver = "sfx";
      if( $onlyFulltex !== true )
        $return[$resolver] = $resolverLink;

      if( $resolver == "redi" )
      {
        if( @$headers = get_headers($resolverLink, 1) )
        { 
          if( isset($headers[0]) && (strpos($headers[0],"301 Moved Permanently") !== false || strpos($headers[0],"302 Found") !== false) )
          {
            if( is_array($headers["Location"]) )
            {
                foreach( $headers["Location"] as $aLocation )
                {
                  if( $aLocation != "" && strpos($aLocation,"http") !== false && 
                      strpos($aLocation,".redi-bw.de") === false && strpos($aLocation,"ezb.uni-regensburg.de") === false )
                  { //Direkt location:
                    $return[$resolver] = $aLocation;
                    return $return;
                  }
                }
            }
            elseif( isset($headers["Location"]) && $aLocation = $headers["Location"])
            {
                if( $aLocation != "" && strpos($aLocation,"http") !== false && 
                    strpos($aLocation,".redi-bw.de") === false && strpos($aLocation,"ezb.uni-regensburg.de") === false )
                { //Direkt location:
                  $return[$resolver] = $aLocation;
                  return $return;
                }
            }
          }
        }
      }
      elseif( $resolver == "ovid" )
      { //Ovid:
        $return[$resolver] = $resolverLink;
      }
      else
      { //SFX:
        $sfxFullUrl = $this->getSFX_Full($resolverLink);
        if ( $sfxFullUrl != "")
          $return[$resolver] = $sfxFullUrl;
      }
    }
    return $return;
  }

  protected function getSFX_Full($link)
  {
    //Build the url for the sfx answer
    $sfx_xml_url = $link . "&sfx.response_type=simplexml";

    //Set the default stream context
    //for the Error: SSL routines:ssl3_get_server_certificate:certificate verify failed:
    stream_context_set_default(['ssl'=> ['verify_peer' => false, 'verify_peer_name' => false]]);
    //Get the xml answer
	if ($sfx_xml = @simplexml_load_file($sfx_xml_url)) 
	{
        //Go on xml tag 'targets'
		if (!empty($sfx_xml_targets = $sfx_xml->targets)) 
		{
			//Loop through results and select target_url for service_type 'getFullTxt'
			foreach ($sfx_xml_targets->target as $target)
			{
				if ($target->target_name == 'MESSAGE_NO_FULLTXT')
				{
					return "";
				}
				elseif ($target->service_type == 'getFullTxt')
				{
					if (!empty($target->target_url))
					{
						return (string)$target->target_url;
					}
				}
			}
		}
        else return "";
	}
    else return "";
  }

  protected function getArrValue($arr, $arrKey)
  {
    $return = "";
    if( !empty($arr) )
    {
        $arr_values=array("key"=>$arrKey, "val"=>"");
        array_walk_recursive($arr, function($value,$key) use (&$arr_values) {if($key === $arr_values["key"]) $arr_values["val"] = $value;}, $arr_values);
        $return = $arr_values["val"];
    }
    return $return;
  } 

  protected function getText($text, $format)
  {
    return (($format == "citavi" ||  $format == "endnote" || $format == "bibtex") ? htmlspecialchars_decode($text) : urlencode(html_entity_decode($text)));
  } 
/*  
*****************************
 * METADATA                *
*****************************
*/
  public function getOpenURLmetaData($data,$expFormat)
  {
    $exportTags = array( 
        "format"         => array("citavi" => "TY  - ", "endnote" => "%0 ", "bibtex" => "@",               "jop" => "&genre=",                                     "resolver" => "&rft.genre="  ),
        "id"             => array("citavi" => "ID  - ", "endnote" => "%M ", "bibtex" => ",-"                                                                                                    ),
        "title"          => array("citavi" => "T1  - ", "endnote" => "%T ", "bibtex" => "\ttitle = {",     "jop" => "&title=",    "zotero" => "&rft.title=",       "resolver" => "&rft.title="  ),
        "booktitle"      => array("citavi" => "BT  - ", "endnote" => "%B ", "bibtex" => "\tbooktitle = {",                        "zotero" => "&rft.btitle=",      "resolver" => "&rft.btitle=" ),
        "subtitle"       => array("citavi" => "T2  - ", "endnote" => "%Q ", "bibtex" => "\tnote = {"                                                                                            ),
        "series"         => array("citavi" => "T3  - ", "endnote" => "%B ", "bibtex" => "\tseries = {",                           "zotero" => "&rft.series=",      "resolver" => "&rft.series=" ),
        "article"        => array("citavi" => "T1  - ", "endnote" => "%T ", "bibtex" => "\ttitle = {",     "jop" => "&atitle=",   "zotero" => "&rft.atitle=",      "resolver" => "&rft.atitle=" ),
        "journal"        => array("citavi" => "JF  - ", "endnote" => "%J ", "bibtex" => "\tjournal = {",   "jop" => "&title=",    "zotero" => "&rft.title=",       "resolver" => "&rft.title="  ),
        "author"         => array("citavi" => "A1  - ", "endnote" => "%A ", "bibtex" => "\tauthor = {",                           "zotero" => "&rft.au=",          "resolver" => "&rft.au="     ),
        "aulast"         => array(                                                                         "jop" => "&aulast=",   "zotero" => "&rft.aulast=",      "resolver" => "&rft.aulast=" ),
        "aufirst"        => array(                                                                         "jop" => "&aufirst=",  "zotero" => "&rft.aufirst=",     "resolver" => "&rft.aufirst="),
        "associates"     => array("citavi" => "A2  - ", "endnote" => "%E ", "bibtex" => "\teditor = {",                           "zotero" => "&rft.au=",                                       ),
        "language"       => array("citavi" => "LA  - ", "endnote" => "%G ", "bibtex" => "\tlanguage = {",                         "zotero" => "&rft.language="                                  ),
        "note"           => array("citavi" => "N1  - ", "endnote" => "%Z ", "bibtex" => "\tnote = {"                                                                                            ),
        "school"         => array(                                          "bibtex" => "\tschool = {"                                                                                          ),
        "type"           => array(                                          "bibtex" => "\ttype = {"                                                                                            ),
        "summary"        => array("citavi" => "N2  - ", "endnote" => "%X ", "bibtex" => "\tabstract = {"                                                                                        ),
        "issn"           => array("citavi" => "SS  - ", "endnote" => "%@ ", "bibtex" => "\tissn = {",      "jop" => "&issn=",     "zotero" => "&rft.issn=",        "resolver" => "&rft.issn="   ),
        "isbn"           => array("citavi" => "SN  - ", "endnote" => "%@ ", "bibtex" => "\tisbn = {",      "jop" => "&isbn=",     "zotero" => "&rft.isbn=",        "resolver" => "&rft.isbn="   ),
        "edition"        => array("citavi" => "ET  - ", "endnote" => "%7 ", "bibtex" => "\tedition = {",                          "zotero" => "&rft.edition=",     "resolver" => "&rft.edition="),
        "phydescription" => array("citavi" => "U1  - ", "endnote" => "%P ", "bibtex" => "\tnote = {",                             "zotero" => "&rft.tpages="                                    ),
        "placepublished" => array("citavi" => "CY  - ", "endnote" => "%C ", "bibtex" => "\taddress = {",                          "zotero" => "&rft.place=",       "resolver" => "&rft.place="  ),
        "publisher"      => array("citavi" => "PB  - ", "endnote" => "%I ", "bibtex" => "\tpublisher = {",                        "zotero" => "&rft.pub=",         "resolver" => "&rft.pub="    ),
        "year"           => array("citavi" => "PY  - ", "endnote" => "%D ", "bibtex" => "\tyear = {",      "jop" => "&date=",     "zotero" => "&rft.date=",        "resolver" => "&rft.date="   ),
        "part"           => array(                                                                         "jop" => "&part=",     "zotero" => "&rft.part=",        "resolver" => "&rft.part="   ),
        "volume"         => array("citavi" => "VL  - ", "endnote" => "%V ", "bibtex" => "\tvolume = {",    "jop" => "&volume=",   "zotero" => "&rft.volume=",      "resolver" => "&rft.volume=" ),
        "issue"          => array("citavi" => "IS  - ", "endnote" => "%N ", "bibtex" => "\tnumber = {",    "jop" => "&issue=",    "zotero" => "&rft.issue=",       "resolver" => "&rft.issue="  ),
        "startpage"      => array("citavi" => "SP  - ",                                                    "jop" => "&spage=",    "zotero" => "&rft.spage=",       "resolver" => "&rft.spage="  ),
        "endpage"        => array("citavi" => "EP  - ",                                                    "jop" => "&epage=",    "zotero" => "&rft.epage=",       "resolver" => "&rft.epage="  ),
        "pages"          => array(                      "endnote" => "%P ", "bibtex" => "\tpages = {",     "jop" => "&pages=",    "zotero" => "&rft.pages=",       "resolver" => "&rft.pages="  ),
        "doi"            => array("citavi" => "DO  - ", "endnote" => "%R ", "bibtex" => "\tdoi = {",       "jop" => "&id=doi%3A", "zotero" => "&rft_id=info:doi/", "resolver" => "&rft.doi="    ),
        "subject"        => array("citavi" => "KW  - ", "endnote" => "%K ", "bibtex" => "\tkeywords = {"                                                                                        ),
        "volltext"       => array("citavi" => "UR  - ", "endnote" => "%U ", "bibtex" => "\turl = {"                                                                                             ),
        "institute"      => array("citavi" => "S1  - ", "endnote" => "%W "                                                                                                                      ),
        "database"       => array("citavi" => "S2  - ", "endnote" => "%~ "                                                                                                                      ),
        "sid"            => array("citavi" => "S3  - "                                                                                                                                          ),
        "url"            => array("citavi" => "L3  - "                                                                                                                                          ),
        "endtag"         => array("citavi" => "ER  - ",                     "bibtex" => "}"                                                                                                     )
    );

    $exportFormats = array
    (
        "article"        => array("bibtex" => "Article",       "endnote" => "Journal Article",        "citavi" => "Journal Article"                       ),
        "book"           => array("bibtex" => "Book",          "endnote" => "Book",                   "citavi" => "Book"                                  ),
        "journal"        => array("bibtex" => "Periodical",    "endnote" => "Journal Article",        "citavi" => "Journal Article"                       ),
        "manuscript"     => array("bibtex" => "Unpublished",   "endnote" => "Manuscript",             "citavi" => "Manuscript"                            ),
        "map"            => array("bibtex" => "Misc",          "endnote" => "Map",                    "citavi" => "Map"                                   ),
        "motionpicture"  => array("bibtex" => "Misc",          "endnote" => "Film or Broadcast",      "citavi" => "Movie"                                 ),
        "musicalscore"   => array("bibtex" => "Misc",          "endnote" => "Generic",                "citavi" => "Musical Work"                          ),
        "unknown"        => array("bibtex" => "Misc",          "endnote" => "Generic",                "citavi" => "Unknown"                               ),
        "booklet"        => array("bibtex" => "Booklet",       "endnote" => "Edited Book",            "citavi" => "Edited Book"                           ),
        "collection"     => array("bibtex" => "Collection",    "endnote" => "Edited Book",            "citavi" => "Edited Book"                           ),
        "conference"     => array("bibtex" => "Conference",    "endnote" => "Conference Paper",       "citavi" => "Contribution in Conference Proceedings"),
        "inbook"         => array("bibtex" => "Inbook",        "endnote" => "Book Section",           "citavi" => "Contribution in an Edited Book"        ),
        "incollection"   => array("bibtex" => "Incollection",  "endnote" => "Book Section",           "citavi" => "Contribution in an Edited Book"        ),
        "mastersthesis"  => array("bibtex" => "Mastersthesis", "endnote" => "Thesis",                 "citavi" => "Thesis"                                ),
        "phdthesis"      => array("bibtex" => "Phdthesis",     "endnote" => "Thesis",                 "citavi" => "Thesis"                                ),
        "proceedings"    => array("bibtex" => "Proceedings",   "endnote" => "Conference Proceedings", "citavi" => "Conference Proceedings"                ),
        "techreport"     => array("bibtex" => "Techreport",    "endnote" => "Report",                 "citavi" => "Report"                                ),
        "unpublished"    => array("bibtex" => "Unpublished",   "endnote" => "Unpublished Work",       "citavi" => "Report"                                ),
    );
    $expClass     = ($expFormat == "zotero" || $expFormat == "resolver") ? "resolver" : "other";
    $tagExtention = ($expFormat == "citavi" || $expFormat == "endnote") ? "\r\n" : (($expFormat == "bibtex") ? "},\r\n" : "");
    $metadataOU   = "";

    $libGenre951 = isset($data["contents"]["951"][0]) ? $this->getArrValue($data["contents"]["951"][0], "a") : "";
    $libGenre    = (substr($data["leader"],7,1) == "m"  || $libGenre951 == "ST")
                   ? ((substr($data["leader"],19,1) == "b" || substr($data["leader"],19,1) == "c") ? "bookitem" : "book") 
                   : ($libGenre951 == "AR" ? "article" : ($libGenre951 == "JT" ? "journal" : $data["format"] ));  
    if( $expFormat == "bibtex" || $expFormat == "endnote" || $expFormat == "citavi" )
    {
        if (substr($data["leader"],7,1) == "m" && substr($data["leader"],19,1) == "a")
            $data["format"] = $exportFormats["collection"][$expFormat];
        elseif (!empty($data["genre"][0]["name"]) && strpos($data["genre"][0]["name"], "Hochschul") !== false) 
        {
            if (!empty($data["contents"][502][0][0]["b"]) || !empty($data["contents"][502][0][0]["a"]))
            {
                if(strpos($data["contents"][502][0][0]["b"], "Diss") !== false) 
                     $data["format"] = $exportFormats["phdthesis"][$expFormat];
                elseif(strpos($data["contents"][502][0][0]["a"], "Studienarb") !== false)
                     $data["format"] = $exportFormats["techreport"][$expFormat];
                else $data["format"] = $exportFormats["mastersthesis"][$expFormat];
            }
        }
        elseif (!empty($data["genre"][0]["name"]) && strpos($data["genre"][0]["name"], "Konferenz") !== false) 
        {
            if(strpos($data["contents"][338][0][0]["a"], "Band") !== false)
                 $data["format"] = $exportFormats["proceedings"][$expFormat];
            else $data["format"] = $exportFormats["conference"][$expFormat];
        }
        elseif (!empty($data["format"]) && $data["format"] != "article" && empty($data["publisher"][0]["b"]))
        {
            $data["format"] = $exportFormats["booklet"][$expFormat];
        }
        elseif (!empty($libGenre) && $libGenre == "bookitem")
        {
            $data["format"] = $exportFormats["inbook"][$expFormat];
        }
        elseif(!empty($data["isbn"]) && !empty($data["contents"][338][0][0]["a"]) && strpos($data["contents"][338][0][0]["a"], "Band") !== false)
        {
            $data["format"] = $exportFormats["incollection"][$expFormat];
        }
        elseif (!empty($data["format"]) && $data["format"] == "journal")
            $data["format"] = $exportFormats["journal"][$expFormat];
        elseif (!empty($data["format"]) && $data["format"] == "unknown")
            $data["format"] = $exportFormats["unpublished"][$expFormat];
        elseif (!empty($data["format"]) && strpos("article,book,datamedia,game,manuscript,map,microform,mixedmaterials,monographseries,motionpicture,musicalscore,picture,projectedmedium,serialvolume,soundrecording,", $data["format"]) === false)
            $data["format"] = $exportFormats["unknown"][$expFormat]; 
        elseif (isset($exportFormats[$data["format"]])) 
            $data["format"] = $exportFormats[$data["format"]][$expFormat];
    }

    //***** FORMAT ***********************************************************
    if (isset($data["format"]) && !empty($exportTags["format"][$expFormat])) 
    {
	    $metadataOU = $exportTags["format"][$expFormat] . ($expFormat == "jop" ? ((strpos(strtolower($data["format"]),"article") !== false) ? "article" : "journal")
                      : (($expFormat == "resolver") ? $libGenre : ucfirst($data["format"])))
                      . ( $expFormat == "bibtex" ? "{GBV" : $tagExtention );
    }

    //***** ID ***************************************************************
    if (!empty($exportTags["id"][$expFormat]) && isset($data["id"]) && $data["id"] != "") 
    {
        $metadataOU .= $exportTags["id"][$expFormat] . $data["id"] . ( $expFormat == "bibtex" ? ",\r\n" : $tagExtention );
    }

    //***** ISBN, ISSN *******************************************************
    $isbn = isset($data["contents"]["020"][0][0]["a"]) ? $data["contents"]["020"][0][0]["a"] : (isset($data["contents"]["773"][0]) ? $this->getArrValue($data["contents"]["773"][0], "z") : "");
    $metadataOU .= empty($isbn) ? "" : ($exportTags["isbn"][$expFormat] . $isbn . $tagExtention);

    $issn = isset($data["contents"]["022"][0][0]["a"]) ? $data["contents"]["022"][0][0]["a"] : (isset($data["contents"]["022"][0][0]["y"]) ? $data["contents"]["022"][0][0]["y"] : (isset($data["contents"]["776"][0]) ? $this->getArrValue($data["contents"]["776"][0], "x") : (isset($data["contents"]["773"][0]) ? $this->getArrValue($data["contents"]["773"][0], "x") : "")));
    $metadataOU .= empty($issn) ? "" : ($exportTags["issn"][$expFormat] . ((strpos($issn,"-") === false) ? (substr($issn,0,4) . '-' . substr($issn,4)) : ((strpos($issn," ") === false) ? $issn : strstr($issn, ' ', true))) . $tagExtention);

    //***** TITLE ************************************************************
    $tit         = empty($data["contents"]["245"][0]) ? "" : array_walk_recursive($data["contents"]["245"][0], function($value,$key) use (&$title){if($key === "a") $title = $value; elseif($key === "b") $title = ($title . " : " . $value);}, $title);
    $mainTit     = !empty($data["contents"]["772"][0]) ? $this->getArrValue($data["contents"]["772"][0], "t") : (empty($data["contents"]["773"][0]) ? "" : $this->getArrValue($data["contents"]["773"][0], "t"));
    $partTit     = empty($data["contents"]["245"][0]) ? "" : array_walk_recursive($data["contents"]["245"][0], function($value,$key) use (&$pTitle){if($key === "n") $pTitle = $value; elseif($key === "p") $pTitle = ($pTitle . " : " . $value);}, $pTitle);
    $collTit     = empty($data["contents"]["800"][0]) ? "" : array_walk_recursive($data["contents"]["800"][0], function($value,$key) use (&$collTitle){if($key === "t") $collTitle = $value; elseif($key === "v") $collTitle = ($collTitle . " ; " . $value);}, $collTitle);

    if( !empty($pTitle) )
    {
        $metadataOU .= (($expClass == "resolver") ? $exportTags["article"][$expFormat] : $exportTags["title"][$expFormat]) . $this->getText($pTitle,$expFormat) . $tagExtention;
        if( !empty($title) && isset($exportTags["booktitle"][$expFormat]) )
        {
            $metadataOU .= $exportTags["booktitle"][$expFormat] . $this->getText($title,$expFormat) . $tagExtention;   
        }
    }
    elseif( !empty($title) )
    {
        $metadataOU .= ((ucfirst($libGenre) == "Article" || ($expClass == "resolver" && !empty($collTitle))) ? $exportTags["article"][$expFormat] 
                        : $exportTags["title"][$expFormat]) . $this->getText($title,$expFormat) . $tagExtention;   
    }
    if( !empty($mainTit) )
    {
        $metadataOU .= (($expClass == "resolver") ? $exportTags["title"][$expFormat] : (ucfirst($libGenre) == "Article" ? $exportTags["journal"][$expFormat] : (isset($exportTags["booktitle"][$expFormat]) ? $exportTags["booktitle"][$expFormat] : "")))
                        . $this->getText(((stripos($mainTit, "in:") !== false) ? trim(substr($mainTit,stripos($mainTit, "in:") + 3)) : $mainTit),$expFormat) . $tagExtention;
    }
    if( !empty($collTitle) && isset($exportTags["booktitle"][$expFormat]) )
    {
        $metadataOU .= $exportTags["booktitle"][$expFormat] . $this->getText($collTitle,$expFormat) . $tagExtention;
    }

    if( isset($exportTags["series"][$expFormat]) )
    {
        $seriesTit   = !empty($data["contents"]["830"][0]) ? array_walk_recursive($data["contents"]["830"][0], function($value,$key) use (&$series){if($key === "a") $series = ((stripos($value, "in:") !== false) ? trim(substr($value,stripos($value, "in:") + 3)) : $value); elseif($key === "v") $series = ($series . " ; " . $value);}, $series) : 
                       (empty($data["contents"]["490"][0]) ? "" : array_walk_recursive($data["contents"]["490"][0], function($value,$key) use (&$series){if($key === "a") $series = $value; elseif($key === "v") $series = ($series . " ; " . $value);}, $series));
        if( !empty($series) )
        {
            $metadataOU .= $exportTags["series"][$expFormat] . $this->getText($series,$expFormat) . $tagExtention;
        }
    }
    $subTitle    = !empty($data["contents"]["240"][0]) ? $this->getArrValue($data["contents"]["240"][0], "a") : (empty($data["contents"]["246"][0]) ? "" : $this->getArrValue($data["contents"]["246"][0], "a"));
    $metadataOU .= (empty($subTitle) || empty($exportTags["subtitle"][$expFormat])) ? "" : ($exportTags["subtitle"][$expFormat] . $this->getText($subTitle,$expFormat) . $tagExtention);

    //***** EDITION **********************************************************
    if( isset($exportTags["edition"][$expFormat]) )
    {
        $edition  = isset($data["contents"]["348"][0]) ? $this->getArrValue($data["contents"]["348"][0], "a") : (isset($data["contents"]["250"][0]) ? $this->getArrValue($data["contents"]["250"][0], "a") : "");
        if( !empty($edition) )
        {
            $metadataOU .= $exportTags["edition"][$expFormat] . $edition . $tagExtention;
        }
    }
    //***** PLACE, PUB, DATE *************************************************
    if( !empty($data["contents"]["264"][0]) )
    {
        foreach( $data["contents"]["264"][0] as $value264)
        {
            if( array_key_first($value264) == "a" )  $place = $value264["a"];
            if( array_key_first($value264) == "b" )  $pub   = $value264["b"];
            if( array_key_first($value264) == "c" )  $date  = $value264["c"];
        }
    }
	if( empty($date) && !empty($data["contents"]["008"]) && ctype_digit(substr($data["contents"]["008"],7,4)) ) 
	{
		$date = str_replace(array("[","]"),array("",""),substr($data["contents"]["008"],7,4));
	}
	if( empty($date) ) 
	{
        $year = (isset($data["contents"]["772"][0]) ? $this->getArrValue($data["contents"]["772"][0], "g") : (isset($data["contents"]["773"][0]) ? $this->getArrValue($data["contents"]["773"][0], "g") : ""));
		preg_match("#\((.*?)\)#", $year, $yearArr);
		$date = empty($yearArr[1]) ? "" : ("date=" . trim(str_replace(array("[","]"),array("",""),$yearArr[1]), " -"));
    }
    if( (empty($place) || empty($pub) || empty($date)) && !empty($data["contents"]["773"][0]) )
    {
		array_walk_recursive($data["contents"]["773"][0], function($value,$key) use (&$placePub){if($key === "d") $placePub = $value;}, $placePub);
        $place = (empty($place) && strpos($placePub, " : ") !== false) ? strstr($placePub, " : ", true) : "";
        if( empty($pub) )
        {
            $pub   = (strpos($placePub, " : ") === false) ? $placePub : substr(strstr($placePub, " : "),3);
            $pub   = (strpos($pub, ", ") === false) ? $pub : strstr($pub, ", ", true);
        }
        $date  = (empty($date) && strpos($date, ", ") !== false) ? substr(strstr($date, ", "),2) : $date;
    }
    $metadataOU .= (empty($exportTags["placepublished"][$expFormat]) || empty($place)) ? "" : ($exportTags["placepublished"][$expFormat] . $place . $tagExtention);
    $metadataOU .= (empty($exportTags["publisher"][$expFormat]) || empty($pub)) ? "" : ($exportTags["publisher"][$expFormat] . $this->getText($pub, $expFormat) . $tagExtention);
    $metadataOU .= empty($date) ? "" : ($exportTags["year"][$expFormat] . trim($date, " -") . $tagExtention);

    //***** AUTHOR ***********************************************************
    $authorContent = empty($data["contents"]["100"]) ? "" : $data["contents"]["100"];
    if( !empty($data["contents"]["700"]) )
    {
        $authorContent = empty($authorContent) ? $data["contents"]["700"] : array_merge($authorContent, $data["contents"]["700"]);
    }
	if (!empty($authorContent))
	{
        $ai = 1; $metadataAu = "";
        foreach( $authorContent as $contentAu )
        {
        	array_walk_recursive($contentAu, function($value,$key) use (&$author){if($key === "a") $author["name"] = $value; if($key === 4 || $key=== "e") $author["spec"] = $value;}, $author=array("name"=>"", "spec"=>""));    
            if( !empty($author["name"]) )
            {
                $tagPrefixAu = ($author["spec"] == "aut" || strpos($author["spec"],"verfasser") !== false  || empty($exportTags["associates"][$expFormat])) ? (empty($exportTags["author"][$expFormat]) ? "" : $exportTags["author"][$expFormat]) : $exportTags["associates"][$expFormat];
		        $metadataAu .= empty($tagPrefixAu) ? "" : (($expFormat == "bibtex") ? ((($ai > 1) ? " and " : ($tagPrefixAu)) . $author["name"]) : 
                                                                                                    ($tagPrefixAu . $author["name"] . $tagExtention));
		        if (($author["spec"] == "aut" || strpos($author["spec"],"verfasser") !== false) && strpos($author["name"], ', ') !== false && !empty($exportTags["aulast"][$expFormat]) && ($ai == 1 || $expFormat != "zotero")) 
		        {
		            $metadataAu .= empty($exportTags["aulast"][$expFormat]) ? "" : ($exportTags["aulast"][$expFormat] . strstr($author["name"], ', ', true) . $tagExtention);
		            $metadataAu .= empty($exportTags["aufirst"][$expFormat]) ? "" : ($exportTags["aufirst"][$expFormat] . substr(strstr($author["name"], ", "), 2) . $tagExtention);
		        }
 		        if ($expFormat == "resolver" || $expFormat == "jop") break;           
                $ai++;
		    }
		}
		if( !empty($metadataAu) )
        {
            $metadataOU .= $metadataAu . (($expFormat == "bibtex") ? $tagExtention : "");
        }
    }

    //***** DATE, PART, VOLUME, ISSUE, PAGES *********************************
	if (!empty($data["contents"]["952"][0]))
	{
		foreach($data["contents"]["952"][0] as $detailValue)
		{
			foreach($detailValue as $dKey=>$dValue) 
			{
				switch ($dKey)
				{
					case "j":
						$metadataOU .= empty($date) ? ($exportTags["year"][$expFormat] . trim(str_replace(array("[","]"),array("",""),$dValue), " -") . $tagExtention) : "";
						break;
					case "a":
						$metadataOU .= empty($exportTags["part"][$expFormat]) ? "" : ($exportTags["part"][$expFormat] . $dValue . $tagExtention);
						break;
					case "d":
						$metadataOU .= $exportTags["volume"][$expFormat] . $dValue . $tagExtention;
						break;
					case "e":
						$metadataOU .= $exportTags["issue"][$expFormat] . $dValue . $tagExtention;
						break;
					case "h":
						if ( strpos($dValue, "-") !== false ) 
						{
							$metadataOU .= empty($exportTags["startpage"][$expFormat]) ? "" : ($exportTags["startpage"][$expFormat] . strstr($dValue, '-', true) . $tagExtention);
							$metadataOU .= empty($exportTags["endpage"][$expFormat]) ? "" : ($exportTags["endpage"][$expFormat] . substr(strstr($dValue, "-"), 1) . $tagExtention);
						}
						else
						{
							if( strpos($dValue, ".") === false )
							{
								$metadataOU .= empty($exportTags["startpage"][$expFormat]) ? "" : ($exportTags["startpage"][$expFormat] . $dValue . $tagExtention);
							}
							$metadataOU .= empty($exportTags["pages"][$expFormat]) ? "" : ($exportTags["pages"][$expFormat] . $dValue . $tagExtention);
						}
						break;
				}
			}
		}
	}
	if( empty($volume) && !empty($data["contents"]["830"][0]) )
	{
		array_walk_recursive($data["contents"]["830"][0], function($value,$key) use (&$volume){if($key === "v") $volume = $value;}, $volume);
		$metadataOU .= $exportTags["volume"][$expFormat] . $volume . $tagExtention;
	}

    //***** LANGUAGE *********************************************************
	if( isset($exportTags["language"][$expFormat]) )
    {
        $language     = empty($data["contents"]["041"]) ? "" : $this->getArrValue($data["contents"]["041"][0], "a");
		$metadataOU .= empty($language) ? "" : $exportTags["language"][$expFormat] . htmlspecialchars_decode($language) . $tagExtention;
    }

    //***** NOTE *************************************************************
    if( isset($exportTags["note"][$expFormat]) )
    {
        array_walk_recursive($data["contents"]["500"], function($value,$key) use (&$notes){if($key === "a") $notes[] = $value;}, $notes=array());
        foreach( $notes as $note)
        {
            $metadataOU .= empty($note) ? "" : $exportTags["note"][$expFormat] . htmlspecialchars_decode($note) . $tagExtention;
        }
        $dissertation = empty($data["contents"]["502"]) ? "" : $this->getArrValue($data["contents"]["502"][0], "a");
		$metadataOU  .= empty($dissertation) ? "" : $exportTags["note"][$expFormat] . htmlspecialchars_decode($dissertation) . $tagExtention;
        $computerfile = empty($data["contents"]["256"]) ? "" : $this->getArrValue($data["contents"]["256"][0], "a");
		$metadataOU  .= empty($computerfile) ? "" : $exportTags["note"][$expFormat] . htmlspecialchars_decode($computerfile) . $tagExtention;
    }

    //***** SUMMARY **********************************************************
	if( isset($exportTags["summary"][$expFormat]) )
    {
        $summary     = empty($data["contents"]["520"]) ? "" : $this->getArrValue($data["contents"]["520"][0], "a");
		$metadataOU .= empty($summary) ? "" : $exportTags["summary"][$expFormat] . htmlspecialchars_decode($summary) . $tagExtention;
    }

    //***** PHYSICALDESCRIPTION **********************************************
	if( isset($exportTags["phydescription"][$expFormat]) )
    {
        $phydescription     = empty($data["contents"]["300"]) ? "" : $this->getArrValue($data["contents"]["300"][0], "a");
		$metadataOU .= empty($phydescription) ? "" : $exportTags["phydescription"][$expFormat] . htmlspecialchars_decode($phydescription) . $tagExtention;
    }

    //***** SCHOL ************************************************************
	if( isset($exportTags["school"][$expFormat]) )
    {
        $school     = empty($data["contents"]["502"]) ? "" : $this->getArrValue($data["contents"]["502"][0], "c");
		$metadataOU .= empty($school) ? "" : $exportTags["school"][$expFormat] . htmlspecialchars_decode($school) . $tagExtention;
    }

    //***** TYPE *************************************************************
    if (isset($exportTags["type"][$expFormat]) && (!empty($data["contents"][502][0][0]["a"]) ||
        !empty($data["contents"][502][0][0]["b"]) || !empty($data["contents"][338][0][0]["a"])))
    {
       $metadataOU .= $exportTags["type"][$expFormat] . 
       (!empty($data["contents"][502][0][0]["a"]) ? $data["contents"][502][0][0]["a"] : (
        !empty($data["contents"][502][0][0]["b"]) ? $data["contents"][502][0][0]["b"] : (
        !empty($data["contents"][338][0][0]["a"]) ? $data["contents"][338][0][0]["a"] : ""))) . $tagExtention;
    }

    //***** SUBJECT **********************************************************
	if (isset($exportTags["subject"][$expFormat]) && isset($data["subject"][0]) && $data["subject"][0] != "") 
    {
		$metadataOU .= $exportTags["subject"][$expFormat];
		foreach($data["subject"] as $aSubjectKey => $aSubject) 
        {
			if ($aSubject['name'] != "") 
			{
			$metadataOU .= $aSubject['name'] . ((count($data["subject"]) > 1 && $aSubjectKey < count($data["subject"]) - 1) ? " / " : "" );
            }
        }
		$metadataOU .= $tagExtention;
    }

    //***** VOLLTEXT *********************************************************
	if (isset($exportTags["volltext"][$expFormat])) 
    {
        $addinfo     = empty($data["contents"]["856"]) ? "" : $this->getArrValue($data["contents"]["856"][0], "u");
        $metadataOU .= empty($addinfo) ? "" : ($exportTags["volltext"][$expFormat] . $addinfo . $tagExtention);
    }

    //***** DOI **************************************************************
    if (!empty($data["contents"]["024"]))
	{
	    foreach($data["contents"]["024"] as $key24=>$value24) 
	    {
            if (!empty($value24[2][2]) && $value24[2][2] == "doi")
            {
               $doiBase = "https://doi.org/";
               $doi     = $value24[1]["a"];
               $doi     = (strpos(strtolower($doi), "http") === false ) ? ((($expFormat == "resolver" || $expFormat == "jop") ? "" : $doiBase) . $doi) 
                                                                        :  (($expFormat == "resolver" || $expFormat == "jop") ? strstr($doi,$doiBase,true) : $doi);
               $metadataOU .= $exportTags["doi"][$expFormat] . $doi . $tagExtention;
            }
        }
    }

    //***** INSTITUTE ********************************************************
	if (isset($exportTags["institute"][$expFormat]))
		$metadataOU .= $exportTags["institute"][$expFormat] . "Gemeinsamer Bibliotheksverbund (GBV) / Verbundzentrale des GBV (VZG)\r\n";

    //***** DATABASE *********************************************************
	if (isset($exportTags["database"][$expFormat]))
		$metadataOU .= $exportTags["database"][$expFormat] . $_SESSION["config_general"]["general"]["title"] . $tagExtention;

    //***** SID **************************************************************
	if (isset($exportTags["sid"][$expFormat]))
		$metadataOU .= $exportTags["sid"][$expFormat] . $_SESSION["config_general"]["export"]["openurlreferer"] . $tagExtention;

    //***** URL **************************************************************
	if (isset($exportTags["url"][$expFormat]))
		$metadataOU .= $exportTags["url"][$expFormat] . base_url() . "id%7Bcolon%7D" . $data["id"] . $tagExtention;

	if ($expFormat == "bibtex")
		// Delete the last comma
		$metadataOU = substr($metadataOU, 0, -3) . "\r\n";

    //***** ENDTAG ***********************************************************
	if (isset($exportTags["endtag"][$expFormat]))
		$metadataOU .= $exportTags["endtag"][$expFormat];

    return $metadataOU;
  }
  
}

?>