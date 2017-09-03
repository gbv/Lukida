<?php

class Standard extends General
{
  protected $CI;

  public function __construct()
  {
    // Assign the CodeIgniter super-object
    $this->CI =& get_instance();
  }

  public function linkresolver($ppn)
  {
    $this->PPN = $ppn;
    $this->contents = $_SESSION["data"]["results"][$this->PPN]["contents"];
    $_SESSION["data"]["results"][$this->PPN] += $this->SetContents("export");
    $this->contents = $_SESSION["data"]["results"][$this->PPN];

    //$this->CI->printArray2File($this->contents);
    //return (array("sfx"=>"http://www.handball.de","jop"=>"http://www.fussball.de"));

    $linkarray = array();

    // LinkResolver SFX
    if ($_SESSION["config_general"]["export"]["sfxlink"] == "1")
    {
      if ( ( $Link = $this->getSFX_Link($this->contents) ) != "")
      {
        $linkarray["sfx"] = $Link;
      }
    }

    // LinkResolver Journals Online & Print
    if ( $_SESSION["config_general"]["export"]["joplink"] == "1" )
    {
      if ( ( $Link = $this->getEZB_Link($this->contents) ) != "")
      {
		//An quot in the URL caused errors when writing to the database table "links_resolved library":
		$linkarray["jop"] = str_replace(array(chr(39),chr(32)), array("%27","%22"), $Link);
      }
    }
	
    return $linkarray;
  }

  public function exportfile($data, $format, $ppn)
  {
    $this->PPN = $ppn;
    $this->contents = $_SESSION["data"]["results"][$this->PPN]["contents"];
    $_SESSION["data"]["results"][$this->PPN] += $this->SetContents("export");
    $this->contents = $_SESSION["data"]["results"][$this->PPN];

    // Create File Data

    // Um die Daten zu sehen, die folgende Zeile aktivieren:
    // $this->CI->printArray2File($data);

    switch ($format)
    {
      case "citavi":
        $openurlMetadata = $this->getCitaviMetaData($data);
        break;
      case "endnote":
        $openurlMetadata = $this->getEndnoteMetaData($data);
        break;
      case "bibtex":
        $openurlMetadata = $this->getBibtexMetaData($data);
        break;
    }

    // Einen String (Dateinhalt, keine Datei) zusammenbauen und zurueckliefern
    //return $data["id"] ." - " . $data["format"] ." - " . $format;

    return $openurlMetadata;
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
                          "sid=GBV&ctx_enc=info:ofi/enc:UTF-8&rfr_id=info:sid/gbv.de:" . $openurlReferer .
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
*****************************
 * EZB                 *
*****************************
*/ 
  protected function getEZB_Link($data)
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
	
	if ( (isset($data["issn"]) && $data["issn"] != "") || !empty($zdbid) )			
    {
      $ezbbibid			= (isset($_SESSION["config_general"]["general"]["ezbbibid"]) &&
                                 $_SESSION["config_general"]["general"]["ezbbibid"] != "") 
						  ? $_SESSION["config_general"]["general"]["ezbbibid"] 
						  : null;

      $isil             = (isset($_SESSION["config_general"]["general"]["isil"]) &&
                                 $_SESSION["config_general"]["general"]["isil"] != "") 
                          ? $_SESSION["config_general"]["general"]["isil"] 
                          : null;
						  
	  $bibparam			= utf8_encode(isset($ezbbibid) ? ("bibid%3D" . $ezbbibid) : (isset($isil) ? ("%26isil%3D" . $isil) : ""));

      $openurlReferer   = (isset($_SESSION["config_general"]["export"]["openurlreferer"]) &&
                                 $_SESSION["config_general"]["export"]["openurlreferer"] != "") 
                          ? $_SESSION["config_general"]["export"]["openurlreferer"] 
                          : "Lukida";

      $openurlMetadata  = $this->getEZB_Meta($data);

      $ezbLinkExtension = "sid=GBV:" . $openurlReferer . $openurlMetadata .
                          ("&pid=" . $bibparam . (!empty($zdbid) ? ("%26zdbid%3D" . $zdbid) : ""));
						  
      $ezbLink          = "https://services.dnb.de/fize-service/gvr/full.xml?" . $ezbLinkExtension;

      $ezbTarget        = $this->getEZB_Full(urlencode($ezbLink), str_replace('%3D', '=', $bibparam));	
      if ( $ezbTarget )   return $ezbTarget;
    }
    return "";
  }

  public function getEZB_Meta($data)
  {
    $metadataOU = "";
    if (isset($data["format"])) 
    {
      $metadataOU = "&genre=" . ((strpos(strtolower($data["format"]),"article") !== false) ? "article" : "journal");
    }
    if (isset($data["issn"])) 
    {
      $metadataISSN = (is_array($data["issn"] && count($data["issn"]) >= 1)) 
                     ? $data["issn"][0] : $data["issn"];
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
        $metadataOU .= "&issn=" . $metadataISSN;
      }
    }
    if (isset($data["edition"]) && $data["edition"] != "") 
    {
      $metadataOU .= "&edition=" . $data["edition"];
    }
	if (!empty($data["contents"]["952"][0]))
	{
		foreach($data["contents"]["952"][0] as $detailValue) {
			foreach($detailValue as $dKey=>$dValue) 
			{
				switch ($dKey)
				{
				case "j":
					$metadataOU .= "&date=" . $dValue;
					break;
				case "a":
					$metadataOU .= "&part=" . $dValue;
					break;
				case "d":
					$metadataOU .= "&volume=" . $dValue;
					break;
				case "e":
					$metadataOU .= "&issue=" . $dValue;
					break;
				case "h":
					if ( strpos($dValue, "-") !== false ) 
					{
						 $metadataOU .= "&spage=" . strstr($dValue, '-', true);
						 $metadataOU .= "&epage=" . substr(strstr($dValue, "-"), 1);
					}
					else $metadataOU .= "&pages=" . $dValue;
					break;
				}
			}
		}
	}
	else
	{
		if (!empty($data["in830"][0]["v"]))
		{
			$metadataOU .= "&volume=" . $data["in830"][0]["v"];
		}
		if (!empty($data["publisherarticle"][0]["g"]))
		{ 
			if (preg_match("#\((.*?)\)#", $data["publisherarticle"][0]["g"], $year))
				$metadataOU .= "&date=" . $year[1];
		}
		elseif (isset($data["publisher"][0]["c"]) && $data["publisher"][0]["c"] != "") 
		{
			$metadataOU .= "&date=" . $data["publisher"][0]["c"];
		}
		elseif (ctype_digit(substr($this->contents["008"],7,4))) 
		{
			$metadataOU .= "&date=" . substr($this->contents["008"],7,4);
		}
	}

    return $metadataOU;
  }

  protected function getEZB_Full($link, $bibparam)
  {	  
	$returnValue = "";
	$joponlyfulltext = (isset($_SESSION["config_general"]["export"]["joponlyfulltext"]) &&
                       $_SESSION["config_general"]["export"]["joponlyfulltext"] == "1") 
                       ? true : null;
	if ($ezb_xml = simplexml_load_file($link))
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
			 $ezb_xml_result = $ezb_xml->Full->ElectronicData->ResultList->Result )	
		{
			$resultStatus = "";  $accessLevel  = "";
			$resultStatus = json_decode($ezb_xml->Full->ElectronicData->ResultList->Result['state']);
			$accessLevel  =  $ezb_xml_result->AccessLevel;
		}
		//Get the results and select AccessURL. 
		//State "4" = "not on-licence".
		//AccessLevel "homepage" = no a good accurate result.
		if ( $ezb_xml_result && $resultStatus != "" && $resultStatus != "4" && $accessLevel != "homepage" )
		{
			if ( isset($ezb_xml_result->AccessURL) && $ezb_xml_result->AccessURL != "" )
			{
				//Link to the full text
				$returnValue = $ezb_xml_result->AccessURL;
			}
			elseif ( !isset($joponlyfulltext) && $refUrl != "" && $refLabel == "EZB-Opac" )
			{
				//EZB-website to the title with other possible links:
				$returnValue = $refUrl . "&" . $bibparam;
			}
			elseif ( !isset($joponlyfulltext) && isset($ezb_xml_result->JournalURL) && $ezb_xml_result->JournalURL != "" )
			{
				//link to the Journal
				$returnValue = $ezb_xml_result->JournalURL;
			}
		} 
		elseif ( !isset($joponlyfulltext) && $refUrl != "" && $refLabel == "EZB-Opac" )
		{
			//EZB-website to the title with other possible links:
			$returnValue = $refUrl . "&" . $bibparam;
		}
	  }
	  return array_values((array)$returnValue)[0];		
    }
	return false;
  }  
/*
*****************************
 * SFX                 *
*****************************
*/
  protected function getSFX_Link($data)
  {
    if ((isset($_SESSION["config_general"]["export"]["sfxalsowithoutissn"]) &&
               $_SESSION["config_general"]["export"]["sfxalsowithoutissn"] == "1") ||
			  (isset($data["issn"]) && $data["issn"] != "" ))
    {
      $openurlBase = (isset($_SESSION["config_general"]["export"]["sfxbase"]) &&
                            $_SESSION["config_general"]["export"]["sfxbase"] != "") 
                          ? $_SESSION["config_general"]["export"]["sfxbase"] : null;

      $openurlReferer   = (isset($_SESSION["config_general"]["export"]["openurlreferer"]) &&
                                 $_SESSION["config_general"]["export"]["openurlreferer"] != "") 
                          ? $_SESSION["config_general"]["export"]["openurlreferer"] 
                          : "Lukida";

      $openurlEntry     = $openurlBase 
                         . "?sid=GBV&ctx_enc=info:ofi/enc:UTF-8&rfr_id=info:sid/gbv.de:" 
                         . $openurlReferer;

      $openurlMetadata  = $this->getOpenURLmetaData($data, "sfx");

      $sfxlink          = $openurlEntry . $openurlMetadata;

      if (isset($_SESSION["config_general"]["export"]["sfxonlyfulltext"]) &&
                $_SESSION["config_general"]["export"]["sfxonlyfulltext"] == "1")
      {
        $sfxFullUrl = $this->getSFX_Full($sfxlink);
        if ( $sfxFullUrl != "")
        {
          return $sfxFullUrl;
        }
      }
	  else { return $sfxlink; }
	}
    return "";
  }

  protected function getSFX_Full($link)
  {
    $returnValue = "";
    //Build the url for the sfx answer
    $sfx_xml_url = $link . "&sfx.response_type=simplexml";

    //Get the xml answer
    $sfx_xml_url_header = get_headers($sfx_xml_url);

    //Get the xml answer
    if (strpos($sfx_xml_url_header[0],"200") !== false)
    {
	  if ($sfx_xml = @simplexml_load_file($sfx_xml_url)) 
	  {
        //Go on xml tag 'targets'
		if (isset($sfx_xml->targets)) 
		{
			$sfx_xml_targets = $sfx_xml->targets;

			//Loop through results and select target_url for service_type 'getFullTxt'
			foreach ($sfx_xml_targets->target as $target)
			{
				if ($target->target_name == 'MESSAGE_NO_FULLTXT')
				{
					break;
				}
				elseif ($target->service_type == 'getFullTxt')
				{
					if (!empty($target->target_url))
					{
						$returnValue = $target->target_url;
						break;
					}
				}
			}
		}
	  }
    }	
    return array_values((array)$returnValue)[0];
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
      $metadataOU = "&rft.genre=" . $data["format"];
    }
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
          $metadataOU .= "&rft.title=" . $data["title"];
        }
      }
      else 
      { 
        $metadataOU .= "&rft.title=" . $data["title"];
      }
    }
    if ($exportformat != "sfx" && isset($data["serial"])) 
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
        $metadataOU .= "&rft.issn=" . $metadataISSN;
      }
    }
    if ($exportformat != "sfx" && isset($data["edition"]) && $data["edition"] != "") 
    {
      $metadataOU .= "&rft.edition=" . $data["edition"];
    }	
	if (!empty($data["contents"]["952"][0]))
	{
		foreach($data["contents"]["952"][0] as $detailValue) {
			foreach($detailValue as $dKey=>$dValue) 
			{
				switch ($dKey)
				{
				case "j":
					$metadataOU .= "&rft.date=" . $dValue;
					break;
				case "a":
					$metadataOU .= "&rft.part=" . $dValue;
					break;
				case "d":
					$metadataOU .= "&rft.volume=" . $dValue;
					break;
				case "e":
					$metadataOU .= "&rft.issue=" . $dValue;
					break;
				case "h":
					if ( strpos($dValue, "-") !== false ) 
					{
						 $metadataOU .= "&rft.spage=" . strstr($dValue, '-', true);
						 $metadataOU .= "&rft.epage=" . substr(strstr($dValue, "-"), 1);
					}
					else $metadataOU .= "&rft.pages=" . $dValue;
					break;
				}
			}
		}
	}
	else
	{
		if (!empty($data["in830"][0]["v"]))
		{
			$metadataOU .= "&rft.volume=" . $data["in830"][0]["v"];
		}
		if (!empty($data["publisherarticle"][0]["g"]))
		{ 
			if (preg_match("#\((.*?)\)#", $data["publisherarticle"][0]["g"], $year))
				$metadataOU .= "&rft.date=" . $year[1];
		}
		elseif (isset($data["publisher"][0]["c"]) && $data["publisher"][0]["c"] != "") 
		{
			$metadataOU .= "&rft.date=" . $data["publisher"][0]["c"];
		}	
		elseif (ctype_digit(substr($this->contents["008"],7,4))) 
		{
			$metadataOU .= "&rft.date=" . substr($this->contents["008"],7,4);
		}
	}
    if ($exportformat != "sfx" && isset($data["author"]))
    {
      if (is_array($data["author"])) 
      {
        if (count($data["author"]) >= 1) 
        {
          $aNr = 0;
          foreach($data["author"] as $author) 
          {
            $aNr += 1;
            $metadataOU .= "&rft.author=" . $author;
            if ($aNr == 1 && !empty($author) && strpos($author, ', ') !== false) 
            {
              $metadataOU .= "&rft.aulast=" . strstr($author, ', ', true);
              $metadataOU .= "&rft.aufirst=" . substr(strstr($author, ", "), 2);
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
	if ($exportformat != "sfx" && isset($data["publisher"][0]) && $data["publisher"][0] != "") 
    {
	  foreach($data["publisher"][0] as $publisherKey => $publisherValue)
	  {
		if ($publisherKey == "a") 
		{ 
			$metadataOU .= "&rft.place=" . $data["publisher"][0]["a"];
		}
		elseif ($publisherKey == "b") 
		{ 
			$metadataOU .= "&rft.pub=" . $data["publisher"][0]["b"];
		} 
	  }
    }
	elseif ($exportformat != "sfx" && isset($data["publisherarticle"][0]["d"]) && $data["publisherarticle"][0]["d"] != "") 
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
    return $metadataOU;
  }
  
/*  
*****************************
 * CITAVI                *
*****************************
*/
  public function getCitaviMetaData($data)
  {
    $metadataOU = "";
    if (isset($data["format"]) && $data["format"] != "") 
    {
      $metadataOU = "TY  - " . $data["format"] . "\r\n";
    }	
    if (isset($data["id"]) && $data["id"] != "") 
    {
      $metadataOU .= "ID  - " . $data["id"] . "\r\n";
    }
    if (isset($data["title"]) && $data["title"] != "") 
    {
      $metadataOU .= "T1  - " . ($data["title"]) . "\r\n";
    }
    if (isset($data["publisherarticle"])) 
    {
      if (is_array($data["publisherarticle"])) 
      {
        if (count($data["publisherarticle"]) >= 1 && $data["publisherarticle"][0]["t"] != "") 
        {
          $publisherarticle = $data["publisherarticle"][0]["t"];
        }
      }
      else { $publisherarticle = $data["publisherarticle"];
      }
      if (!empty($publisherarticle)) 
      {
        $metadataOU .= "JF  - " . (stripos($publisherarticle, "in:") !== false ?
        trim(substr($publisherarticle,stripos($publisherarticle, "in:") + 3)) :
        $publisherarticle) . "\r\n";
      }
    }
	if (isset($data["contents"]["240"][0][0]["a"]) && $data["contents"]["240"][0][0]["a"] != "") {
	  $metadataOU .= "T2  - " . $data["contents"]["240"][0][0]["a"] . "\r\n";
	}
	elseif (isset($data["contents"]["246"][0][1]["a"]) && $data["contents"]["246"][0][1]["a"] != "") {
	  $metadataOU .= "T2  - " . $data["contents"]["246"][0][1]["a"] . "\r\n";
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
                $metadataOU .= "T3  - " . ((stripos($sValue, "in:") !== false) ? trim(substr($sValue,stripos($sValue, "in:") + 3)) : $sValue);
              }
              else 
			  { $metadataOU .= " " . $sValue;
              }
            }
            $metadataOU .= "\r\n";
          }
        }
      }
      elseif ($data["serial"] != "") 
      {
        $metadataOU .= "T3  - " . ((stripos($data["serial"], "in: ") !== false) ? trim(substr($data["serial"],stripos($data["serial"], "in:") + 3)) : $data["serial"]) . "\r\n";
      }
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
            $metadataOU .= "A" . $aNr . "  - " . $author . "\r\n";
          }
        }
      }
      elseif ($data["author"] != "") 
      {
        $metadataOU .= "A1  - " . $data["author"] . "\r\n";
      }
    }
	if (!empty($data["language"][0]))
	{
		$metadataOU .= "LA  - " . $data["language"][0] . "\r\n";
	}
    if (isset($data["notes"]) && $data["notes"] != "") 
    {
      if (strpos($data["notes"]," | ")!==false) 
      {
        foreach (explode(" | ", $data["notes"]) as $note) 
        {
          $metadataOU .= "N1  - " . $note . "\r\n";
        }
      }
      else { $metadataOU .= "N1  - " . $data["notes"] . "\r\n";
      }
    }
	if (isset($data["dissertation"]) && $data["dissertation"] != "")
    {
		$metadataOU .= "N1  - " . $data["dissertation"] . "\r\n";
    }
	if (isset($data["summary"]) && $data["summary"] != "")
    {
		$metadataOU .= "N2  - " . $data["summary"] . "\r\n";
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
            $metadataOU .= "A2  - " . $associate["a"] . "\r\n";
          }
        }
      }
      elseif ($data["associates"] != "") 
      {
        $metadataOU .= "A2  - " . $data["associates"][a] . "\r\n";
      }
    }
	if (isset($data["computerfile"]) && $data["computerfile"] != "")
	{
		$metadataOU .= "N1  - " . $data["computerfile"] . "\r\n";
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
              $metadataOU .= "SN  - " . $isbn . "\r\n";
              break;
            }
          }
        }
      }
      elseif ($data["isbn"] != "") 
      {
        $metadataOU .= "SN  - " . $data["isbn"] . "\r\n";
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
		$metadataOU .= "BN  - " . $isbnVal . "\r\n";	
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
              $metadataOU .= "SN  - " . $issn . "\r\n";
              break;
            }
          }
        }
      }
      elseif ($data["issn"] != "") 
      {
        $metadataOU .= "SN  - " . $data["issn"] . "\r\n";
      }
    }
    if (isset($data["edition"]) && $data["edition"] != "") 
    {
      $metadataOU .= "ED  - " . $data["edition"] . "\r\n";
    }
	if (isset($data["physicaldescription"]) && $data["physicaldescription"] != "") 
    {
      $metadataOU .= "U1  - " . $data["physicaldescription"] . "\r\n";
    }
    if (isset($data["publisher"][0]) && $data["publisher"][0] != "") 
    {
	  foreach($data["publisher"][0] as $publisherKey => $publisherValue)
	  {
		if ($publisherKey == "a") 
		{ 
			$metadataOU .= "CY  - " . $publisherValue . "\r\n";
		}
		elseif ($publisherKey == "b") 
		{ 
			$metadataOU .= "PB  - " . $publisherValue . "\r\n";
		} 
	  }
    }
	elseif (isset($data["publisherarticle"][0]["d"]) && $data["publisherarticle"][0]["d"] != "") 
    {
		$tmp = explode(" : ", $data["publisherarticle"][0]["d"]);
		if (isset($tmp[0]) && $tmp[0] != "")
		{
			"CY  - " . $tmp[0] . "\r\n";
		}
		if (isset($tmp[1]) && $tmp[1] != "")
		{
			"PB  - " . $tmp[1] . "\r\n";
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
					$metadataOU .= "PY  - " . $dValue . "\r\n";
					break;
				case "d":
					$metadataOU .= "VL  - " . $dValue . "\r\n";
					break;
				case "e":
					$metadataOU .= "IS  - " . $dValue . "\r\n";
					break;
				case "h":
					if ( strpos($dValue, "-") !== false ) 
					{
						 $metadataOU .= "SP  - " . strstr($dValue, '-', true) . "\r\n";
						 $metadataOU .= "EP  - " . substr(strstr($dValue, "-"), 1) . "\r\n";
					}
					else $metadataOU .= "SP  - " . $dValue . "\r\n";
					break;
				}
			}
		}
	}
	else
	{
		if (!empty($data["in830"][0]["v"]))
		{
			$metadataOU .= "VL  - " . $data["in830"][0]["v"] . "\r\n";
		}
		if (!empty($data["publisherarticle"][0]["g"]))
		{ 
			if (preg_match("#\((.*?)\)#", $data["publisherarticle"][0]["g"], $year))
				$metadataOU .= "PY  - " . $year[1] . "\r\n";
		}
		elseif (isset($data["publisher"][0]["c"]) && $data["publisher"][0]["c"] != "") 
		{
			$metadataOU .= "PY  - " . $data["publisher"][0]["c"] . "\r\n";
		}
		elseif (ctype_digit(substr($this->contents["008"],7,4))) 
		{
			$metadataOU .= "PY  - " . substr($this->contents["008"],7,4) . "\r\n";
		}
	}
	if (isset($data["subject"][0]) && $data["subject"][0] != "") 
    {
		$metadataOU .= "KW  - ";
		foreach($data["subject"] as $aSubjectKey => $aSubject) 
        {
			if ($aSubject != "") 
			{
			$metadataOU .= $aSubject . ((count($data["subject"]) > 1 && $aSubjectKey < count($data["subject"]) - 1) ? " / " : "" );
            }
        }
		$metadataOU .= "\r\n";
    }
	if (isset($data["additionalinfo"][0]["u"]) && $data["additionalinfo"][0]["u"] != "") 
    {
      $metadataOU .= "UR  - " . $data["additionalinfo"][0]["u"] . "\r\n";
    }
    $metadataOU .= "S1  - Gemeinsamer Bibliotheksverbund (GBV) / Verbundzentrale des GBV (VZG)\r\n";
    $metadataOU .= "S2  - " . $_SESSION["config_general"]["general"]["title"] . "\r\n";
    $metadataOU .= "S3  - " . $_SESSION["config_general"]["export"]["openurlreferer"] . "\r\n";
    $metadataOU .= "L3  - " . base_url() . "id%7Bcolon%7D" . $data["id"] . "\r\n";
	$metadataOU .= "ER  - ";
    return $metadataOU;
  }

/*  
*****************************
 * ENDNOTE                *
*****************************
*/
  public function getEndnoteMetaData($data)
  {
    $metadataOU = "";
    if (isset($data["format"]) && $data["format"] != "") 
    {
      $metadataOU = "%0 " . $data["format"] . "\r\n";
    }
    if (isset($data["id"]) && $data["id"] != "") 
    {
      $metadataOU .= "%M " . $data["id"] . "\r\n";
    }
    if (isset($data["title"]) && $data["title"] != "") 
    {
      $metadataOU .= "%T " . $data["title"] . "\r\n";
    }
    if (isset($data["publisherarticle"])) 
    {
      if (is_array($data["publisherarticle"])) 
      {
        if (count($data["publisherarticle"]) >= 1 && $data["publisherarticle"][0]["t"] != "") 
        {
          $publisherarticle = $data["publisherarticle"][0]["t"];
        }
      }
      else 
	  { $publisherarticle = $data["publisherarticle"];
      }
      if (!empty($publisherarticle)) 
      {
        $metadataOU .= "%J " . (stripos($publisherarticle, "in:") !== false ?
        trim(substr($publisherarticle,stripos($publisherarticle, "in:") + 3)) :
        $publisherarticle) . "\r\n";
      }
    }
	if (isset($data["contents"]["240"][0][0]["a"]) && $data["contents"]["240"][0][0]["a"] != "") 
	{
	  $metadataOU .= "%Q " . $data["contents"]["240"][0][0]["a"] . "\r\n";
	}
	elseif (isset($data["contents"]["246"][0][1]["a"]) && $data["contents"]["246"][0][1]["a"] != "") {
	  $metadataOU .= "%Q " . $data["contents"]["246"][0][1]["a"] . "\r\n";
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
                $metadataOU .= "%B " . ((stripos($sValue, "in:") !== false) ? trim(substr($sValue,stripos($sValue, "in:") + 3)) : $sValue);
              }
              else { $metadataOU .= " " . $sValue;
              }
            }
            $metadataOU .= "\r\n";
          }
        }
      }
      elseif ($data["serial"] != "") 
      {
        $metadataOU .= "%B " . ((stripos($data["serial"], "in:") !== false) ? trim(substr($data["serial"],stripos($data["serial"], "in:") + 3)) : $data["serial"]) . "\r\n";
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
            $metadataOU .= "%A " . $author . "\r\n";
          }
        }
      }
      elseif ($data["author"] != "") 
      {
        $metadataOU .= "%A " . $data["author"] . "\r\n";
      }
    }
	if (!empty($data["language"][0]))
	{
		$metadataOU .= "%G " . $data["language"][0] . "\r\n";
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
            $metadataOU .= "%A " . $associate["a"] . "\r\n";
          }
        }
      }
      elseif ($data["associates"] != "") 
      {
        $metadataOU .= "%A " . $data["associates"][a] . "\r\n";
      }
    }
	if (isset($data["computerfile"]) && $data["computerfile"] != "")
	{
		$metadataOU .= "%Z " . $data["computerfile"] . "\r\n";
	}
	if (isset($data["summary"]) && $data["summary"] != "")
    {
		$metadataOU .= "%X " . $data["summary"] . "\r\n";
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
              $metadataOU .= "%@ " . $isbn . "\r\n";
              break;
            }
          }
        }
      }
      elseif ($data["isbn"] != "") 
      {
        $metadataOU .= "%@ " . $data["isbn"] . "\r\n";
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
		$metadataOU .= "%@ " . $isbnVal . "\r\n";	
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
              $metadataOU .= "%@ " . $issn . "\r\n";
              break;
            }
          }
        }
      }
      elseif ($data["issn"] != "") 
      {
        $metadataOU .= "%@ " . $data["issn"] . "\r\n";
      }
    }
    if (isset($data["edition"]) && $data["edition"] != "") 
    {
      $metadataOU .= "%7 " . $data["edition"] . "\r\n";
    }
	if (isset($data["physicaldescription"]) && $data["physicaldescription"] != "") 
    {
      $metadataOU .= "%P " . $data["physicaldescription"] . "\r\n";
    }
    if (isset($data["publisher"][0]) && $data["publisher"][0] != "") 
    {
	  foreach($data["publisher"][0] as $publisherKey => $publisherValue)
	  {
		if ($publisherKey == "a") 
		{ 
			$metadataOU .= "%C " . $publisherValue . "\r\n";
		}
		elseif ($publisherKey == "b") 
		{ 
			$metadataOU .= "%I " . $publisherValue . "\r\n";
		} 
	  }
    }
	elseif (isset($data["publisherarticle"][0]["d"]) && $data["publisherarticle"][0]["d"] != "") 
    {
		$tmp = explode(" : ", $data["publisherarticle"][0]["d"]);
		if (isset($tmp[0]) && $tmp[0] != "")
		{
			$metadataOU .= "%C " . $tmp[0] . "\r\n";
		}
		if (isset($tmp[1]) && $tmp[1] != "")
		{
			$metadataOU .= "%I " . $tmp[1] . "\r\n";
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
					$metadataOU .= "%D " . $dValue . "\r\n";
					break;
				case "d":
					$metadataOU .= "%V " . $dValue . "\r\n";
					break;
				case "e":
					$metadataOU .= "%N " . $dValue . "\r\n";
					break;
				case "h":
					$metadataOU .= "%P " . $dValue . "\r\n";
					break;
				}
			}
		}
	}
	else
	{
		if (!empty($data["in830"][0]["v"]))
		{
			$metadataOU .= "%V " . $data["in830"][0]["v"] . "\r\n";
		}
		if (!empty($data["publisherarticle"][0]["g"]))
		{ 
			if (preg_match("#\((.*?)\)#", $data["publisherarticle"][0]["g"], $year))
				$metadataOU .= "%D " . $year[1] . "\r\n";
		}
		elseif (isset($data["publisher"][0]["c"]) && $data["publisher"][0]["c"] != "") 
		{
			$metadataOU .= "%D " . $data["publisher"][0]["c"] . "\r\n";
		}
		elseif (ctype_digit(substr($this->contents["008"],7,4))) 
		{
			$metadataOU .= "%D " . substr($this->contents["008"],7,4) . "\r\n";
		}
	}
	if (isset($data["notes"]) && $data["notes"] != "") 
	{
		if (strpos($data["notes"]," | ")!==false) 
		{
			foreach (explode(" | ", $data["notes"]) as $note) 
			{
				$metadataOU .= "%Z " . $note . "\r\n";
			}
		}
		else 
		{ $metadataOU .= "%Z " . $data["notes"] . "\r\n";
		}
	}
	if (isset($data["dissertation"]) && $data["dissertation"] != "")
    {
		$metadataOU .= "%Z " . $data["dissertation"] . "\r\n";
    }
	if (isset($data["subject"][0]) && $data["subject"][0] != "") 
    {
		$metadataOU .= "%K ";
		foreach($data["subject"] as $aSubjectKey => $aSubject) 
        {
			if ($aSubject != "") 
			{
			$metadataOU .= $aSubject . ((count($data["subject"]) > 1 && $aSubjectKey < count($data["subject"]) - 1) ? " / " : "" );
            }
        }
		$metadataOU .= "\r\n";
    }
	if (isset($data["additionalinfo"][0]["u"]) && $data["additionalinfo"][0]["u"] != "") 
    {
      $metadataOU .= "%U " . $data["additionalinfo"][0]["u"] . "\r\n";
    }
    $metadataOU .= "%U " . base_url() . "id%7Bcolon%7D" . $data["id"] . "\r\n";
	$metadataOU .= "%W Gemeinsamer Bibliotheksverbund (GBV) / Verbundzentrale des GBV (VZG)\r\n";
	$metadataOU .= "%~ " . $_SESSION["config_general"]["general"]["title"];
    return $metadataOU;
  }
  
/*  
*****************************
 * BIBTEX                *
*****************************
*/
  public function getBibtexMetaData($data)
  {
    $metadataOU = "";
    if (isset($data["format"]) && $data["format"] != "") 
    {
      $metadataOU = "@" . $data["format"];
    }
    $metadataOU .= "{GBV";
    if (isset($data["id"]) && $data["id"] != "") 
    {
      $metadataOU .= "-" . $data["id"] . ",\r\n";
    }
    if (isset($data["title"]) && $data["title"] != "") 
    {
      $metadataOU .= "\ttitle = {" . $data["title"] . "},\r\n";
    }
    if (isset($data["publisherarticle"])) 
	{
      if (is_array($data["publisherarticle"])) 
	  {
        if (count($data["publisherarticle"]) >= 1 && $data["publisherarticle"][0]["t"] != "") 
        {
          $publisherarticle = $data["publisherarticle"][0]["t"];
        }
      }
      else { $publisherarticle = $data["publisherarticle"];
      }
      if (!empty($publisherarticle)) 
      {
        $metadataOU .= "\tjournal = {" . (stripos($publisherarticle, "in:") !== false ?
        trim(substr($publisherarticle,stripos($publisherarticle, "in:") + 3)) :
        $publisherarticle) . "},\r\n";
      }
    }
	if (isset($data["contents"]["240"][0][0]["a"]) && $data["contents"]["240"][0][0]["a"] != "") {
	  $metadataOU .= "\tnote = {" . $data["contents"]["240"][0][0]["a"] . "}\r\n";
	}
	elseif (isset($data["contents"]["246"][0][1]["a"]) && $data["contents"]["246"][0][1]["a"] != "") {
	  $metadataOU .= "\tnote = {" . $data["contents"]["246"][0][1]["a"] . "}\r\n";
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
                $metadataOU .= "\tseries = {" . ((stripos($sValue, "in:") !== false) ? trim(substr($sValue,stripos($sValue, "in:") + 3)) : $sValue);
              }
              else 
			  { $metadataOU .= " " . $sValue;
              }
            }
            $metadataOU .= "},\r\n";
          }
        }
      }
      elseif ($data["serial"] != "") 
      {
        $metadataOU .= "\series = {" . ((stripos($data["serial"], "in:") !== false) ? trim(substr($data["serial"],stripos($data["serial"], "in:") + 3)) : $data["serial"]) . "},\r\n";
      }
    }
    if (isset($data["author"])) 
    {
      if (is_array($data["author"])) 
      {
        if (count($data["author"]) >= 1) 
        { 
		  $authors = "";
          foreach($data["author"] as $keyAuthor => $author) 
          {
            $authors .= ($keyAuthor > 0 ? " and " : "") . $author;
          }
		  $metadataOU .= "\tauthor = {" . $authors . "},\r\n";
        }
      }
      elseif ($data["author"] != "") 
      {
        $metadataOU .= "\tauthor = {" . $data["author"] . "},\r\n";
      }
    }
	if (!empty($data["language"][0]))
	{
		$metadataOU .= "\tlanguage = {" . $data["language"][0] . "},\r\n";
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
            $associates .=  (++$counter > 1 ? " and " : "") . $associate["a"];
          }
		  $metadataOU .= "\teditor = {" . $associates . "},\r\n";
        }
      }
      elseif ($data["associates"] != "") 
      {
        $metadataOU .= "\teditor = {" . $data["associates"][a] . "},\r\n";
      }
    }
	if (isset($data["computerfile"]) && $data["computerfile"] != "")
	{
		$metadataOU .= "\tnote = {" . $data["computerfile"] . "},\r\n";
	}
    if (isset($data["edition"]) && $data["edition"] != "") 
    {
      $metadataOU .= "\tedition = {" . $data["edition"] . "},\r\n";
    }
    if (isset($data["publisher"][0]) && $data["publisher"][0] != "") 
    {
	  foreach($data["publisher"][0] as $publisherKey => $publisherValue) 
	  {
		if ($publisherKey == "a") 
		{ 
			$metadataOU .= "\taddress = {" . $publisherValue . "},\r\n";
		}
		elseif ($publisherKey == "b") 
		{ 
			$metadataOU .= "\tpublisher = {" . $publisherValue . "},\r\n";
		} 
	  }
    }
	elseif (isset($data["publisherarticle"][0]["d"]) && $data["publisherarticle"][0]["d"] != "") 
    {
		$tmp = explode(" : ", $data["publisherarticle"][0]["d"]);
		if (isset($tmp[0]) && $tmp[0] != "")
		{
			$metadataOU .= "\taddress = {" . $tmp[0] . "},\r\n";
		}
		if (isset($tmp[1]) && $tmp[1] != "")
		{
			$metadataOU .= "\tpublisher = {" . $tmp[1] . "},\r\n";
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
					$metadataOU .= "\tyear = {" . $dValue . "},\r\n";
					break;
				case "d":
					$metadataOU .= "\tvolume = {" . $dValue . "},\r\n";
					break;
				case "e":
					$metadataOU .= "\tnumber = {" . $dValue . "},\r\n";
					break;
				case "h":
					$metadataOU .= "\tpages = {" . $dValue . "},\r\n";
					break;
				}
			}
		}
	}
	else
	{
		if (!empty($data["in830"][0]["v"]))
		{
			$metadataOU .= "\tvolume = {" . $data["in830"][0]["v"] . "},\r\n";
		}
		if (!empty($data["publisherarticle"][0]["g"]))
		{ 
			if (preg_match("#\((.*?)\)#", $data["publisherarticle"][0]["g"], $year))
				$metadataOU .= "\tyear = {" . $year[1] . "},\r\n";
		}
		elseif (isset($data["publisher"][0]["c"]) && $data["publisher"][0]["c"] != "") 
		{
			$metadataOU .= "\tyear = {" . $data["publisher"][0]["c"] . "},\r\n";
		}	
		elseif (ctype_digit(substr($this->contents["008"],7,4))) 
		{
			$metadataOU .= "\tyear = {" . substr($this->contents["008"],7,4) . "},\r\n";
		}		
		if (isset($data["physicaldescription"]) && $data["physicaldescription"] != "") 
		{
		  $metadataOU .= "\tpages = {" . $data["physicaldescription"] . "}\r\n";
		}
	}
    if (isset($data["notes"]) && $data["notes"] != "") 
    {
      if (strpos($data["notes"]," | ")!==false) 
	  {
        foreach (explode(" | ", $data["notes"]) as $note) 
        {
          $metadataOU .= "\tnote = {" . $note . "}\r\n";
        }
      }
      else { $metadataOU .= "\tnote = {" . $data["notes"] . "}\r\n";
      }
    }
	if (isset($data["dissertation"]) && $data["dissertation"] != "")
    {
		$metadataOU .= "\tschool = {" . $data["dissertation"] . "}\r\n";
    }		
    $metadataOU .= "}";
    return $metadataOU;
  }

}

?>