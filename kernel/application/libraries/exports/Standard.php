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
    $_SESSION["data"]["results"][$this->PPN] += $this->SetContents("export");
    $this->contents = $_SESSION["data"]["results"][$this->PPN];
    $this->configExport   = $_SESSION["config_general"]["export"];
    $this->configGeneral  = $_SESSION["config_general"]["general"];
    //$this->CI->printArray2File($this->contents);
    //return (array("sfx"=>"http://www.handball.de","jop"=>"http://www.fussball.de"));

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
      //If count($fulltextPriosArray) == 1 then possible here $prio is "single"
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
        $linkarray["jop"] = str_replace(array(chr(39),chr(32)), array("%27","%22"), $Link);
      } 
    } 
    elseif( $prio == "jop" )
    {
      //Single jop (or resolver)
      if( $jop_on && ( $Link = $this->get_jop_link($this->contents) ) != "" )
      {
        $linkarray["jop"] = str_replace(array(chr(39),chr(32)), array("%27","%22"), $Link); 
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
    // Create File Data
    // Um die Daten zu sehen, die folgende Zeile aktivieren:
    // $this->CI->printArray2File($data);
    $exportTags = array( 
        "format"         => array("citavi" => "TY  - ", "endnote" => "%0 ", "bibtex" => "@"            ),
        "id"             => array("citavi" => "ID  - ", "endnote" => "%M ", "bibtex" => "-"            ),
        "title"          => array("citavi" => "T1  - ", "endnote" => "%T ", "bibtex" => "title = {"    ),
        "subtitle"       => array("citavi" => "T2  - ", "endnote" => "%Q ", "bibtex" => "note = {"     ),
        "series"         => array("citavi" => "T3  - ", "endnote" => "%B ", "bibtex" => "series = {"   ),
        "journal"        => array("citavi" => "JF  - ", "endnote" => "%J ", "bibtex" => "journal = {"  ),
        "author"         => array("citavi" => "A1  - ", "endnote" => "%A ", "bibtex" => "author = {"   ),
        "associates"     => array("citavi" => "A2  - ", "endnote" => "%E ", "bibtex" => "editor = {"   ),
        "language"       => array("citavi" => "LA  - ", "endnote" => "%G ", "bibtex" => "language = {" ),
        "note"           => array("citavi" => "N1  - ", "endnote" => "%Z ", "bibtex" => "school = {"   ),
        "summary"        => array("citavi" => "N2  - ", "endnote" => "%X ", "bibtex" => "abstract = {" ),
        "issn"           => array("citavi" => "SN  - ", "endnote" => "%@ ", "bibtex" => "issn = {"     ),
        "isbn"           => array("citavi" => "SN  - ", "endnote" => "%@ ", "bibtex" => "isbn = {"     ),
        "edition"        => array("citavi" => "ED  - ", "endnote" => "%7 ", "bibtex" => "edition = {"  ),
        "phydescription" => array("citavi" => "U1  - ", "endnote" => "%P ", "bibtex" => "note = {"     ),
        "placepublished" => array("citavi" => "CY  - ", "endnote" => "%C ", "bibtex" => "address = {"  ),
        "publisher"      => array("citavi" => "PB  - ", "endnote" => "%I ", "bibtex" => "publisher = {"),
        "year"           => array("citavi" => "PY  - ", "endnote" => "%D ", "bibtex" => "year = {"     ),
        "volume"         => array("citavi" => "VL  - ", "endnote" => "%V ", "bibtex" => "volume = {"   ),
        "issue"          => array("citavi" => "IS  - ", "endnote" => "%N ", "bibtex" => "number = {"   ),
        "startpage"      => array("citavi" => "SP  - "                                                 ),
        "endpage"        => array("citavi" => "EP  - "                                                 ),
        "pages"          => array(                      "endnote" => "%P ", "bibtex" => "pages = {"    ),
        "subject"        => array("citavi" => "KW  - ", "endnote" => "%K ", "bibtex" => "type = {"     ),
        "volltext"       => array("citavi" => "UR  - ", "endnote" => "%U "                             ),
        "institute"      => array("citavi" => "S1  - ", "endnote" => "%W "                             ),
        "database"       => array("citavi" => "S2  - ", "endnote" => "%~ "                             ),
        "sid"            => array("citavi" => "S3  - "                                                 ),
        "url"            => array("citavi" => "L3  - "                                                 ),
        "endtag"         => array("citavi" => "ER  - ",                     "bibtex" => "}"            )
    )
    ;
    $tagPrefix       = $format == "bibtex" ? "\t" : "";
    $tagExtention    = $format == "bibtex" ? "},\r\n" : "\r\n";

    $metadataOU = "";
    if (isset($data["format"]) && $data["format"] != "") 
    {
      $metadataOU = $exportTags["format"][$format] . $data["format"] . ( $format == "bibtex" ? "" : $tagExtention );
    }	
	if ($format === "bibtex") { $metadataOU .= "{GBV"; }
    if (isset($data["id"]) && $data["id"] != "") 
    {
      $metadataOU .= $exportTags["id"][$format] . $data["id"] . ( $format == "bibtex" ? ",\r\n" : $tagExtention );
    }
    if (isset($data["title"]) && $data["title"] != "") 
    {
      $metadataOU .= $tagPrefix . $exportTags["title"][$format] . ($data["title"]) . $tagExtention;
    }
    if (isset($data["publisherarticle"])) 
    {
      if (is_array($data["publisherarticle"])) 
      {
        if (count($data["publisherarticle"]) >= 1 && isset($data["publisherarticle"][0]["t"]) && $data["publisherarticle"][0]["t"] != "") 
        {
          $publisherarticle = $data["publisherarticle"][0]["t"];
        }
      }
      else $publisherarticle = $data["publisherarticle"];
      if (!empty($publisherarticle)) 
      {
        $metadataOU .= $tagPrefix . $exportTags["journal"][$format] . (stripos($publisherarticle, "in:") !== false ?
        trim(substr($publisherarticle,stripos($publisherarticle, "in:") + 3)) :
        $publisherarticle) . $tagExtention;
      }
    }
	if (isset($data["contents"]["240"][0][0]["a"]) && $data["contents"]["240"][0][0]["a"] != "")
	{
		$metadataOU .= $tagPrefix . $exportTags["subtitle"][$format] . $data["contents"]["240"][0][0]["a"] . $tagExtention;
	}
	elseif (isset($data["contents"]["246"][0][1]["a"]) && $data["contents"]["246"][0][1]["a"] != "") {
		$metadataOU .= $tagPrefix . $exportTags["subtitle"][$format] . $data["contents"]["246"][0][1]["a"] . $tagExtention;
	}
    if (isset($data["serial"])) 
    {
      if (is_array($data["serial"])) 
      {
        if (count($data["serial"]) >= 1) 
        {
          foreach($data["serial"] as $serial) 
          {
            foreach($serial as $sKey=>$sValue) 
            {
              if (!empty($sValue) && $sKey == "a") 
              {
                $metadataOU .= $tagPrefix . $exportTags["series"][$format] . ((stripos($sValue, "in:") !== false) ? trim(substr($sValue,stripos($sValue, "in:") + 3)) : $sValue);
              }
              else 
			  { $metadataOU .= $tagPrefix . " " . $sValue;
              }
            }
            $metadataOU .= $tagExtention;
          }
        }
      }
      elseif ($data["serial"] != "") 
      {
        $metadataOU .= $tagPrefix . $exportTags["series"][$format] . ((stripos($data["serial"], "in: ") !== false) ? trim(substr($data["serial"],stripos($data["serial"], "in:") + 3)) : $data["serial"]) . $tagExtention;
      }
    }
    if (isset($data["author"])) 
    {
      if (is_array($data["author"])) 
      {
        if (count($data["author"]) >= 1) 
        {
          foreach($data["author"] as $author) 
          {
            $metadataOU .= $tagPrefix . $exportTags["author"][$format] . $author["name"] . $tagExtention;
          }
        }
      }
      elseif ($data["author"] != "") 
      {
        $metadataOU .= $tagPrefix . $exportTags["author"][$format] . $data["author"] . $tagExtention;
      }
    }
	if (!empty($data["language"][0]))
	{
		$metadataOU .= $tagPrefix . $exportTags["language"][$format] . $data["language"][0] . $tagExtention;
	}
	if (is_array($data["notes"]) && count($data["notes"]) >= 1) 
    {
        foreach($data["notes"] as $note) 
        {
          if (!empty($note) && $note != "") 
          {
             $metadataOU .= $tagPrefix . $exportTags["note"][$format] . $note . $tagExtention;
          }
        }
    }
	if (isset($data["dissertation"]) && $data["dissertation"] != "")
    {
		$metadataOU .= $tagPrefix . $exportTags["note"][$format] . $data["dissertation"] . $tagExtention;
    }
	if (isset($data["summary"]) && $data["summary"] != "")
    {
		$metadataOU .= $tagPrefix . $exportTags["summary"][$format] . $data["summary"] . $tagExtention;
    }
	if (isset($data["associates"])) 
    {
      if (is_array($data["associates"])) 
      {
        if (count($data["associates"]) >= 1) 
        { 
		  $counter = 0; 
		  $associates = "";
          foreach($data["associates"] as $associate) 
          {
            $metadataOU .= $tagPrefix . $exportTags["associates"][$format] . $associate["name"] . $tagExtention;
          }
        }
      }
      elseif ($data["associates"] != "") 
      {
        $metadataOU .= $tagPrefix . $exportTags["associates"][$format] . $data["associates"]["name"] . $tagExtention;
      }
    }
	if (isset($data["computerfile"]) && $data["computerfile"] != "")
	{
		$metadataOU .= $tagPrefix . $exportTags["note"][$format] . $data["computerfile"] . $tagExtention;
	}
	if (!empty($data["isbn"])) 
	{
		if (is_array($data["isbn"])) 
		{
			if (count($data["isbn"]) >= 1) 
			{
				foreach($data["isbn"] as $isbn) 
				{
					if ($isbn != "") 
					{
						$metadataOU .= $tagPrefix . $exportTags["isbn"][$format] . $isbn . $tagExtention;
						break;
					}
				}
			}
		}
		elseif ($data["isbn"] != "") 
		{
			$metadataOU .= $tagPrefix . $exportTags["isbn"][$format] . $data["isbn"] . $tagExtention;
		}
	}
	elseif (!empty($data["contents"]["020"][0])) {
		foreach($data["contents"]["020"][0] as $isbnGroup) 
		{
			foreach($isbnGroup as $isbnKey => $isbnValue) 
			{
				if ($isbnKey == "a" || $isbnKey == "9") 
				{
					$isbnVal = $isbnValue;	
				}
			}
		}
		$metadataOU .= $tagPrefix . $exportTags["isbn"][$format] . $isbnVal . $tagExtention;	
	}
    if (isset($data["issn"])) 
    {
      if (is_array($data["issn"])) 
      {
        if (count($data["issn"]) >= 1) 
        {
          foreach($data["issn"] as $issn) 
          {
            if ($issn != "") 
            {
              $metadataOU .= $tagPrefix . $exportTags["issn"][$format] . $issn . $tagExtention;
              break;
            }
          }
        }
      }
      elseif ($data["issn"] != "") 
      {
        $metadataOU .= $tagPrefix . $exportTags["issn"][$format] . $data["issn"] . $tagExtention;
      }
    }
    if (isset($data["edition"]) && $data["edition"] != "") 
    {
      $metadataOU .= $tagPrefix . $exportTags["edition"][$format] . $data["edition"] . $tagExtention;
    }
	if (isset($data["physicaldescription"]) && $data["physicaldescription"] != "") 
    {
      $metadataOU .= $tagPrefix . $exportTags["phydescription"][$format] . $data["physicaldescription"] . $tagExtention;
    }
    if (isset($data["publisher"][0]) && $data["publisher"][0] != "") 
    {
	  foreach($data["publisher"][0] as $publisherKey => $publisherValue)
	  {
		if ($publisherKey == "a" && !empty($publisherValue[0])) 
		{ 
			$metadataOU .= $tagPrefix . $exportTags["placepublished"][$format] . $publisherValue[0] . $tagExtention;
		}
		elseif ($publisherKey == "b" && !empty($publisherValue[0])) 
		{ 
			$metadataOU .= $tagPrefix . $exportTags["publisher"][$format] . $publisherValue[0] . $tagExtention;
		} 
	  }
    }
	elseif (isset($data["publisherarticle"][0]["d"]) && $data["publisherarticle"][0]["d"] != "") 
    {
		$tmp = explode(" : ", $data["publisherarticle"][0]["d"]);
		if (isset($tmp[0]) && $tmp[0] != "")
		{
			$exportTags["placepublished"][$format] . $tmp[0] . $tagExtention;
		}
		if (isset($tmp[1]) && $tmp[1] != "")
		{
			$exportTags["publisher"][$format] . $tmp[1] . $tagExtention;
		}
    }
	if (!empty($data["contents"]["952"][0]))
	{
		foreach($data["contents"]["952"][0] as $detailValue) 
		{
			foreach($detailValue as $dKey=>$dValue) 
			{
				switch ($dKey)
				{
				case "j":
					$metadataOU .= $tagPrefix . $exportTags["year"][$format] . $dValue . $tagExtention;
					break;
				case "d":
					$metadataOU .= $tagPrefix . $exportTags["volume"][$format] . $dValue . $tagExtention;
					break;
				case "e":
					$metadataOU .= $tagPrefix . $exportTags["issue"][$format] . $dValue . $tagExtention;
					break;
				case "h":
					if ( $format == "citavi" )
					{
						if ( strpos($dValue, "-") !== false )
						{
						 $metadataOU .= $tagPrefix . $exportTags["startpage"][$format] . strstr($dValue, '-', true) . $tagExtention;
						 $metadataOU .= $tagPrefix . $exportTags["endpage"][$format] . substr(strstr($dValue, "-"), 1) . $tagExtention;
						}
						else $metadataOU .= $tagPrefix . $exportTags["startpage"][$format] . $dValue . $tagExtention;
					}
					else $metadataOU .= $tagPrefix . $exportTags["pages"][$format] . $dValue . $tagExtention;
					break;
				}
			}
		}
	}
	else
	{
		if (!empty($data["in830"][0]["v"]))
		{
			$metadataOU .= $tagPrefix . $exportTags["volume"][$format] . $data["in830"][0]["v"] . $tagExtention;
		}
		if (!empty($data["publisherarticle"][0]["g"]))
		{ 
			if (preg_match("#\((.*?)\)#", $data["publisherarticle"][0]["g"], $year))
				$metadataOU .= $tagPrefix . $exportTags["year"][$format] . $year[1] . $tagExtention;
		}
		elseif (isset($data["publisher"][0]["c"][0]) && $data["publisher"][0]["c"][0] != "") 
		{
			$metadataOU .= $tagPrefix . $exportTags["year"][$format] . $data["publisher"][0]["c"][0] . $tagExtention;
		}
		elseif (!empty($data["contents"]["008"]) && ctype_digit(substr($data["contents"]["008"],7,4))) 
		{
			$metadataOU .= $tagPrefix . $exportTags["year"][$format] . substr($data["contents"]["008"],7,4) . $tagExtention;
		}
	}
	if (isset($data["subject"][0]) && $data["subject"][0] != "") 
    {
		$metadataOU .= $tagPrefix . $exportTags["subject"][$format];
		foreach($data["subject"] as $aSubjectKey => $aSubject) 
        {
			if ($aSubject != "") 
			{
			$metadataOU .= $aSubject . ((count($data["subject"]) > 1 && $aSubjectKey < count($data["subject"]) - 1) ? " / " : "" );
            }
        }
		$metadataOU .= $tagExtention;
    }
	if (isset($exportTags["volltext"][$format]) && isset($data["additionalinfo"][0]["u"]) && $data["additionalinfo"][0]["u"] != "") 
    {
      $metadataOU .= $tagPrefix . $exportTags["volltext"][$format] . $data["additionalinfo"][0]["u"] . $tagExtention;
    }
	if (isset($exportTags["institute"][$format]))
		$metadataOU .= $tagPrefix . $exportTags["institute"][$format] . "Gemeinsamer Bibliotheksverbund (GBV) / Verbundzentrale des GBV (VZG)\r\n";
	if (isset($exportTags["database"][$format]))
		$metadataOU .= $tagPrefix . $exportTags["database"][$format] . $_SESSION["config_general"]["general"]["title"] . $tagExtention;
	if (isset($exportTags["sid"][$format]))
		$metadataOU .= $tagPrefix . $exportTags["sid"][$format] . $_SESSION["config_general"]["export"]["openurlreferer"] . $tagExtention;
	if (isset($exportTags["url"][$format]))
		$metadataOU .= $tagPrefix . $exportTags["url"][$format] . base_url() . "id%7Bcolon%7D" . $data["id"] . $tagExtention;
	if ($format == "bibtex")
		// Delete the last comma
		$metadataOU = substr($metadataOU, 0, -3) . "\r\n";
	if (isset($exportTags["endtag"][$format]))
		$metadataOU .= $tagPrefix . $exportTags["endtag"][$format];
    return $metadataOU;	
  }
 
  public function exportlink($data, $format)
  {
    // Create File Data
    // Um die Daten zu sehen, die folgende Zeile aktivieren
    // $this->CI->printArray2File($data);
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
      $openurlReferer  = (isset($_SESSION["config_general"]["export"]["openurlreferer"]) &&
                                $_SESSION["config_general"]["export"]["openurlreferer"] != "") 
                              ? $_SESSION["config_general"]["export"]["openurlreferer"] : "Lukida";

      $openurlEntry    = $openurlBase . ($format == "zotero" ? "&" : "?") . 
                          "sid=GBV:" . $openurlReferer . "&ctx_enc=info:ofi/enc:UTF-8" . 
                          ($format == "zotero" ? ("&rft_val_fmt=info:ofi/fmt:kev:mtx:" .                        
                          ((isset($data["format"]) && (strpos($data["format"], 'article') !== false ||
						                               strpos($data["format"], 'journal') !== false)) ? "journal" : "book")) : "") . 
						  ($format == "zotero" ? ("&rft_id=" . base_url() . "id%7Bcolon%7D" . $data["id"]) : "");

      $openurlMetadata = $this->getOpenURLmetaData($data,$format);

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
    if ( empty($metadataISSN) && !empty($data["contents"]["016"]))
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
                          ("&pid=" . $bibparam . ((empty($data["issn"]) && empty($data["isbn"]) && !empty($zdbid)) ? ("%26zdbid%3D" . $zdbid) : ""));
    					  
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
		if ( $ezb_xml && isset($ezb_xml->Full->ElectronicData->ResultList->Result) &&
			 !empty($ezb_xml_result = $ezb_xml->Full->ElectronicData->ResultList->Result) )	
		{
          $i = 0;
          foreach( $ezb_xml_result as $aResult )
          { 
			$resultAdditional = ""; $resultStatus = "";  $accessLevel  = "";
            $resultAdditional   = !empty($aResult->Additionals->Additional) ? (string)$aResult->Additionals->Additional : "";
            $resultStatus       = !empty($aResult['state']) ? json_decode($aResult['state']) : 0;
            $accessLevel        = !empty($aResult->Additionals->AccessLevel) ? $ezb_xml_result->AccessLevel : "";
		    //Get the results and select AccessURL. 
		    //State "4" = "not on-licence".
		    //AccessLevel "homepage" = no a good accurate result.
            if( strpos($resultAdditional,"DFG-gefördert") !== false && $resultStatus != "4" && $accessLevel != "homepage")
            {
              return (string)$aResult->AccessURL;
            }
            else  
            {
              $ezbArray[$i]['state']       = $resultStatus;
              $ezbArray[$i]['AccessLevel'] = (string)$aResult->AccessLevel;
              $ezbArray[$i]['AccessURL']   = (string)$aResult->AccessURL;
              $ezbArray[$i]['JournalURL']  = (string)$aResult->JournalURL;
              $i++;
            }
          }
          //Get the results and select AccessURL. 
          //State "4" = "not on-licence".
          //AccessLevel "homepage" = no a good accurate result.
          if ( $ezbArray[0]['state'] != "4" && $ezbArray[0]['AccessLevel'] != "homepage" )
          {
            if ( !empty($ezbArray[0]['AccessURL']) )
            {
            	//Link to the full text
            	return $ezbArray[0]['AccessURL'];
            }
            elseif ( !isset($joponlyfulltext) && !empty($ezbArray[0]['JournalURL']) )
            {
            	//link to the Journal
            	return $ezbArray[0]['JournalURL'];
            }
          }
        }
        elseif ( !isset($joponlyfulltext) && $refUrl != "" && $refLabel == "EZB-Opac" )
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
          if( strpos($headers[1],"301 Moved Permanently") !== false || strpos($headers[1],"302 Found") !== false )
          {
            foreach( $headers["Location"] as $aLocation )
            {
              if( $aLocation != "" && strpos($aLocation,"http") !== false && 
                  strpos($aLocation,"www-fr.redi-bw.de") === false && strpos($aLocation,"ezb.uni-regensburg.de") === false )
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
  
/*  
*****************************
 * METADATA                *
*****************************
*/
    /*
    // Die folgenden DInge sind im Array $pretty["details"] enthalten
      $pretty["part"]             "952" => array("a" => " | ")
      $pretty["year"]             "952" => array("j" => " | ")
      $pretty["volume"]           "952" => array("d" => " | ")
      $pretty["issue"]            "952" => array("e" => " | ")
      $pretty["pages"]            "952" => array("h" => " | ")

    // Die folgenden Dinge sind im Array $pretty["publisher"] enthalten
      $pretty["place"]            "260" => array("a" => " | ")
      $pretty["publisherOnly"]    "260" => array("b" => " | ")

    // Die folgenden Dinge sind im Array $pretty["publisher"] enthalten
      $pretty["publisherOnly"]    "773" => array("d" => " | ")
 */
 
  public function getOpenURLmetaData($data,$exportformat)
  {
    $metadataOU = "";
    if (isset($data["format"])) 
    {
	  $metadataOU = $exportformat == "jop" ? ( "&genre=" . ((strpos(strtolower($data["format"]),"article") !== false) ? "article" : "journal") ) :
                    ( "&rft.genre=" . $data["format"] );
    }
	if (isset($data["issn"])) 
    {
      $metadataISSN = "";
      if (is_array($data["issn"])) 
      {
        if (count($data["issn"]) >= 1) 
        {
          foreach($data["issn"] as $issn) 
          {
            if ($issn != "") {
              $metadataISSN = $issn;
              break;
            }
          }
        }
      }
      elseif ($data["issn"] != "") 
      {
        $metadataISSN = $data["issn"];
      }
      if ($metadataISSN != "") 
      {
        if (strpos($metadataISSN," ") !== false) 
        {
          $metadataISSN = strstr($metadataISSN, ' ', true);
        }
        if ($metadataISSN != "" && strpos($metadataISSN,"-") === false) 
        {
          $metadataISSN = substr($metadataISSN,0,4) . '-' . substr($metadataISSN,4);
        }
        $metadataOU .= ( $exportformat == "jop" ? "&"  : "&rft." ) . "issn=" . $metadataISSN;
      }
    }
	if (!empty($data["contents"]["952"][0]))
	{
		foreach($data["contents"]["952"][0] as $detailValue) {
			foreach($detailValue as $dKey=>$dValue) 
			{
				switch ($dKey)
				{
				case "j":
					$metadataOU .= ( $exportformat == "jop" ? "&"  : "&rft." ) . "date=" . $dValue;
					break;
				case "a":
					$metadataOU .= ( $exportformat == "jop" ? "&"  : "&rft." ) . "part=" . $dValue;
					break;
				case "d":
					$metadataOU .= ( $exportformat == "jop" ? "&"  : "&rft." ) . "volume=" . $dValue;
					break;
				case "e":
					$metadataOU .= ( $exportformat == "jop" ? "&"  : "&rft." ) . "issue=" . $dValue;
					break;
				case "h":
					if ( strpos($dValue, "-") !== false ) 
					{
						 $metadataOU .= ( $exportformat == "jop" ? "&"  : "&rft." ) . "spage=" . strstr($dValue, '-', true);
						 $metadataOU .= ( $exportformat == "jop" ? "&"  : "&rft." ) . "epage=" . substr(strstr($dValue, "-"), 1);
					}
					else $metadataOU .= ( $exportformat == "jop" ? "&"  : "&rft." ) . "pages=" . $dValue;
					break;
				}
			}
		}
	}
	else
	{
		if (!empty($data["in830"][0]["v"]))
		{
			$metadataOU .= ( $exportformat == "jop" ? "&"  : "&rft." ) . "volume=" . $data["in830"][0]["v"];
		}
		if (!empty($data["publisherarticle"][0]["g"]))
		{ 
			if (preg_match("#\((.*?)\)#", $data["publisherarticle"][0]["g"], $year))
				$metadataOU .= ( $exportformat == "jop" ? "&"  : "&rft." ) . "date=" . $year[1];
		}
		elseif (!empty($data["publisher"][0]["c"][0])) 
		{
			$metadataOU .= ( $exportformat == "jop" ? "&"  : "&rft." ) . "date=" . $data["publisher"][0]["c"][0];
		}	
		elseif (!empty($data["contents"]["008"]) && ctype_digit(substr($data["contents"]["008"],7,4))) 
		{
			$metadataOU .= ( $exportformat == "jop" ? "&"  : "&rft." ) . "date=" . substr($data["contents"]["008"],7,4);
		}
	}
    if ( $exportformat != "jop" )
	{
		if (isset($data["title"]) && $data["title"] != "") 
		{
			if (isset($data["publisherarticle"])) 
			{
				if (is_array($data["publisherarticle"])) 
				{
				if (count($data["publisherarticle"]) >= 1 && isset($data["publisherarticle"][0]["t"]) && $data["publisherarticle"][0]["t"] != "") 
				{
					$publisherarticle = str_replace('&', '%22', $data["publisherarticle"][0]["t"]);
				}
				}
				else { $publisherarticle = $data["publisherarticle"];
				}
				if (!empty($publisherarticle)) 
				{
					$metadataOU .= "&rft.atitle=" . str_replace('&', '%22', $data["title"]) . "&rft.title=" . (stripos($publisherarticle, "in:") !== false ?
					trim(substr($publisherarticle,stripos($publisherarticle, "in:") + 3)) :
					$publisherarticle);
				}
				else 
				{ 
					$metadataOU .= "&rft.title=" . addslashes($data["title"]);
				}
			}
			else 
			{ 
				$metadataOU .= "&rft.title=" . addslashes($data["title"]);
			}
		}
		if (isset($data["isbn"])) 
		{
			if (is_array($data["isbn"])) 
			{
				if (count($data["isbn"]) >= 1) 
				{
				foreach($data["isbn"] as $isbn) 
				{
					if ($isbn != "") 
					{
						$metadataOU .= "&rft.isbn=" . (strpos($data["isbn"]," ") !== false ?
						strstr($data["isbn"], ' ', true) : $data["isbn"]);
						break;
					}
				}
				}
			}
			elseif ($data["isbn"] != "") 
			{
				$metadataOU .= "&rft.isbn=" . (strpos($data["isbn"]," ") !== false ?
				strstr($data["isbn"], ' ', true) : $data["isbn"]);
			}
		}
		elseif (!empty($data["contents"]["020"][0])) 
		{
			foreach($data["contents"]["020"][0] as $isbnGroup) 
			{
				foreach($isbnGroup as $isbnKey => $isbnValue) 
				{
					if ($isbnKey == "a" || $isbnKey == "9") 
					{
						$isbnVal = $isbnValue;	
					}
				}
			}
			$metadataOU .= "&rft.isbn=" . $isbnVal;	
		}
		if ( $exportformat != "resolver" )
		{
			if (isset($data["edition"]) && $data["edition"] != "") 
			{
				$metadataOU .= "&rft.edition=" . $data["edition"];
			}	
			if (isset($data["author"]))
			{
				if (is_array($data["author"])) 
				{
					if (count($data["author"]) >= 1) 
					{
					$aNr = 0;
					foreach($data["author"] as $author) 
					{
						$aNr += 1;
						$metadataOU .= "&rft.author=" . $author["name"];
						if ($aNr == 1 && !empty($author["name"]) && strpos($author["name"], ', ') !== false) 
						{
							$metadataOU .= "&rft.aulast=" . strstr($author["name"], ', ', true);
							$metadataOU .= "&rft.aufirst=" . substr(strstr($author["name"], ", "), 2);
						}
					}
					}
				}
				elseif ($data["author"] != "") 
				{
					$metadataOU .= "&rft.author=" . $data["author"];
					if (strpos($data["author"], ", ")) 
					{
						$metadataOU .= "&rft.aulast=" . strstr($data["author"], ', ', true);
						$metadataOU .= "&rft.aufirst=" . substr(strstr($data["author"], ", "), 2);
					}
				}
			}
			if (isset($data["serial"])) 
			{
				if (is_array($data["serial"])) 
				{
					if (count($data["serial"]) >= 1) 
					{
						foreach($data["serial"] as $serial) 
						{
							foreach($serial as $sKey=>$sValue) 
							{
							if (!empty($sValue) && $sKey == "a") 
							{
								$metadataOU .= "&rft.series=" . ((stripos($sValue, "in:") !== false) ? trim(substr($sValue,stripos($sValue, "in:") + 3)) : $sValue);
							}
							else 
							{ $metadataOU .= " " . $sValue;
							}
							}
						}
					}
				}
				elseif ($data["serial"] != "") 
				{
					$metadataOU .= "&rft.series=" . ((stripos($data["serial"], "in:") !== false) ? trim(substr($data["serial"],stripos($data["serial"], "in:") + 3)) : $data["serial"]);
				}
			}
			if (isset($data["publisher"][0]) && $data["publisher"][0] != "") 
			{
				foreach($data["publisher"][0] as $publisherKey => $publisherValue)
				{
					if ($publisherKey == "a" && !empty($publisherValue[0])) 
					{ 
						$metadataOU .= "&rft.place=" . $publisherValue[0];
					}
					elseif ($publisherKey == "b" && !empty($publisherValue[0])) 
					{ 
						$metadataOU .= "&rft.pub=" . $publisherValue[0];
					} 
				}
			}
			elseif (isset($data["publisherarticle"][0]["d"]) && $data["publisherarticle"][0]["d"] != "") 
			{
				$tmp = explode(" : ", $data["publisherarticle"][0]["d"]);
				if (isset($tmp[0]) && $tmp[0] != "")
				{
					$metadataOU .= "&rft.place=" . $tmp[0];
				}
				if (isset($tmp[1]) && $tmp[1] != "")
				{
					$metadataOU .= "&rft.pub=" . $tmp[1];
				}		
			}
			if ($exportformat == "zotero" && !empty($data["language"][0]))
			{
				$metadataOU .= "&rft.language=" . $data["language"][0];
			}
		}
	}
    return $metadataOU;
  }
  
}

?>