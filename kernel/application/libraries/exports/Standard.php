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
    if ($_SESSION["config_general"]["export"]["sfxLink"] !== false)
    {
      if ( ( $Link = $this->getSFX_Link($this->contents) ) != "")
      {
        $linkarray["sfx"] = $Link;
      }
    }

    // LinkResolver Journals Online & Print
    if ( $_SESSION["config_general"]["export"]["jopLink"] !== false )
    {
      if ( ( $Link = $this->getEZB_Link($this->contents) ) != "")
      {
        $linkarray["jop"] = $Link;
      }
    }
    return $linkarray;
  }

  public function exportfile($data, $format)
  {
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
      case "refworks"     : if ($_SESSION["config_general"]["export"]["refworks"] !== false)
								$openurlBase = "https://www.refworks.com/express/expressimport.asp";
        break;
      case "zotero"       : if ($_SESSION["config_general"]["export"]["zotero"] !== false)
								$openurlBase = "ctx_ver=Z39.88-2004";
        break;
    }

    if ($openurlBase != "") 
    {
      $openurlReferer  = (isset($_SESSION["config_general"]["export"]["openurlReferer"]) &&
                                $_SESSION["config_general"]["export"]["openurlReferer"] != "") 
                              ? $_SESSION["config_general"]["export"]["openurlReferer"] : "Lukida";

      $isil            = (isset($_SESSION["config_general"]["general"]["isil"]) &&
                                $_SESSION["config_general"]["general"]["isil"] != "") 
                                ? $_SESSION["config_general"]["general"]["isil"] : null;

      $openurlEntry    = $openurlBase . ($format == "zotero" ? "&" : "?") . 
                          "sid=GBV&ctx_enc=info:ofi/enc:UTF-8&rfr_id=info:sid/gbv.de:" . $openurlReferer .
                          ($format == "zotero" ? ("&rft_val_fmt=info:ofi/fmt:kev:mtx:" .                        
                          ((isset($data["format"]) && $data["format"] == "book") ? "book" : "journal")) : "");

      $openurlMetadata = $this->getOpenURLmetaData($data);

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
    if (isset($data["issn"])  && $data["issn"] != "" )
    {
      $ezbConfig        = $_SESSION["config_general"]["export"]["jopLinkOption"];

      $isil             = (isset($_SESSION["config_general"]["general"]["isil"]) &&
                                 $_SESSION["config_general"]["general"]["isil"] != "") 
                          ? $_SESSION["config_general"]["general"]["isil"] 
                          : null;

      $openurlReferer   = (isset($_SESSION["config_general"]["export"]["openurlReferer"]) &&
                                 $_SESSION["config_general"]["export"]["openurlReferer"] != "") 
                          ? $_SESSION["config_general"]["export"]["openurlReferer"] 
                          : "Lukida";

      $openurlMetadata  = $this->getEZB_Meta($data);

      $ezbLinkExtension = "sid=GBV:" . $openurlReferer . $openurlMetadata .
                          ("&pid=" . (isset($isil) ? ("isil%3D" . $isil) : ""));

      $ezbLink          = "https://services.dnb.de/fize-service/gvr/html-service.htm?" . $ezbLinkExtension;

      if ( $this->getEZB_Full($ezbLink) )   return $ezbLink;
    }
    return "";
  }

  public function getEZB_Meta($data)
  {
    $metadataOU = "";
    if (isset($data["format"])) 
    {
      $metadataOU = "&genre=" . ((strpos(strtolower($data["format"]),"article") !== false) ? "article" :
      ((strpos(strtolower($data["format"]),"journal") !== false) ? "journal" : $data["format"]));
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
    if (isset($data["details"][0]["a"]) && $data["details"][0]["a"] != "") 
    {
      $metadataOU .= "&part=" . $data["details"][0]["a"];
    }
    if (isset($data["details"][0]["d"]) && $data["details"][0]["d"] != "") 
    {
      $metadataOU .= "&volume=" . $data["details"][0]["d"];
    }
    if (isset($data["details"][0]["e"]) && $data["details"][0]["e"] != "") 
    {
      $metadataOU .= "&issue=" . $data["details"][0]["e"];
    }
    if (isset($data["details"][0]["h"]) && $data["details"][0]["h"] != "") 
    {
      $metadataOU .= "&pages=" . $data["details"][0]["h"];
      if ( strpos($data["details"][0]["h"], "-") !== false ) 
      {
        $metadataOU .= "&spage=" . strstr($data["details"][0]["h"], '-', true);
        $metadataOU .= "&epage=" . substr(strstr($data["details"][0]["h"], "-"), 1);
      }
    }
    return $metadataOU;
  }

  protected function getEZB_Full($link)
  {
    $returnValue = "";

    //Get the xml answer
    $url_header = get_headers($link);

    //Get the xml answer
    if (strpos($url_header[0],"200") !== false)
    {
      $data     = file_get_contents($link);
      if ( stripos($data,"keine gedruckte Version") === false 
        || stripos($data,"keine elektronische Version") === false ) return true;
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
    if (isset($data["issn"])  && $data["issn"] != "" )
    {
      $openurlBase = (isset($_SESSION["config_general"]["export"]["sfxBase"]) &&
                            $_SESSION["config_general"]["export"]["sfxBase"] != "") 
                          ? $_SESSION["config_general"]["export"]["sfxBase"] : null;

      $isil             = (isset($_SESSION["config_general"]["general"]["isil"]) &&
                                 $_SESSION["config_general"]["general"]["isil"] != "") 
                          ? $_SESSION["config_general"]["general"]["isil"] 
                          : null;

      $openurlReferer   = (isset($_SESSION["config_general"]["export"]["openurlReferer"]) &&
                                 $_SESSION["config_general"]["export"]["openurlReferer"] != "") 
                          ? $_SESSION["config_general"]["export"]["openurlReferer"] 
                          : "Lukida";

      $openurlEntry     = $openurlBase 
                         . "?sid=GBV&ctx_enc=info:ofi/enc:UTF-8&rfr_id=info:sid/gbv.de:" 
                         . $openurlReferer;

      $openurlMetadata  = $this->getOpenURLmetaData($data);

      $sfxlink          = $openurlEntry . $openurlMetadata;

      if (isset($_SESSION["config_general"]["export"]["sfxOnlyFulltext"]) &&
                $_SESSION["config_general"]["export"]["sfxOnlyFulltext"] == true)
      {
        $sfxFullUrl = $this->getSFX_Full($sfxlink);
        if ( $sfxFullUrl != "")
        {
          return $sfxFullUrl;
        }
      }
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
      $sfx_xml     = simplexml_load_file($sfx_xml_url);

      //Go on xml tag 'targets'
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
 
  public function getOpenURLmetaData($data)
  {
    $metadataOU = "";
    if (isset($data["format"])) 
    {
      $metadataOU = "&rft.genre=" .  $data["format"];
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
                $metadataOU .= "&rft.jtitle=" . ((stripos($sValue, "in:") !== false) ? trim(substr($sValue,stripos($sValue, "in:") + 3)) : $sValue);
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
        $metadataOU .= "&rft.jtitle=" . ((stripos($data["serial"], "in:") !== false) ? trim(substr($data["serial"],stripos($data["serial"], "in:") + 3)) : $data["serial"]);
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

    if (isset($data["edition"]) && $data["edition"] != "") 
    {
      $metadataOU .= "&rft.edition=" . $data["edition"];
    }
    if (isset($data["details"][0]["a"]) && $data["details"][0]["a"] != "") 
    {
      $metadataOU .= "&rft.part=" . $data["details"][0]["a"];
    }
    if (isset($data["details"][0]["j"]) && $data["details"][0]["j"] != "") 
    {
      $metadataOU .= "&rft.date=" . $data["details"][0]["j"];
    }
    if (isset($data["details"][0]["d"]) && $data["details"][0]["d"] != "") 
    {
      $metadataOU .= "&rft.volume=" . $data["details"][0]["d"];
    }
    if (isset($data["details"][0]["e"]) && $data["details"][0]["e"] != "") {
      $metadataOU .= "&rft.issue=" . $data["details"][0]["e"];
    }
    if (isset($data["details"][0]["h"]) && $data["details"][0]["h"] != "") 
    {
      $metadataOU .= "&rft.pages=" . $data["details"][0]["h"];
      if ( strpos($data["details"][0]["h"], "-") !== false ) 
      {
        $metadataOU .= "&rft.spage=" . strstr($data["details"][0]["h"], '-', true);
        $metadataOU .= "&rft.epage=" . substr(strstr($data["details"][0]["h"], "-"), 1);
      }
    }
    if (isset($data["publisher"][0]["a"]) && $data["publisher"][0]["a"] != "") 
    {
      $metadataOU .= "&rft.place=" . $data["publisher"][0]["a"];
    }
    if (isset($data["publisher"][0]["b"]) && $data["publisher"][0]["b"] != "") 
    {
      $metadataOU .= "&pub=" . $data["publisher"][0]["b"];
    }
    return $metadataOU;
  }

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
              $metadataOU .= "BN  - " . $isbn . "\r\n";
              break;
            }
          }
        }
      }
      elseif ($data["isbn"] != "") 
      {
        $metadataOU .= "BN  - " . $data["isbn"] . "\r\n";
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
    /*if (isset($data["details"][0]["a"]) && $data["details"][0]["a"] != "") 
    {
      $metadataOU .= "  - " . $data["details"][0]["a"] . "\r\n";
    }
    */
    if (isset($data["details"][0]["j"]) && $data["details"][0]["j"] != "") 
    {
      $metadataOU .= "PY  - " . $data["details"][0]["j"] . "\r\n";
    }
	elseif (isset($data["publisher"][0]["c"]) && $data["publisher"][0]["c"] != "") 
	{
	  $metadataOU .= "PY  - " . $data["publisher"][0]["c"] . "\r\n";
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
	elseif (isset($data["contents"]["300"][0][0]["a"]) && $data["contents"]["300"][0][0]["a"] != "") 
	{
	  $metadataOU .= "PB  - " . $data["contents"]["300"][0][0]["a"] . "\r\n";
	}	
    if (isset($data["details"][0]["d"]) && $data["details"][0]["d"] != "") 
    {
      $metadataOU .= "VL  - " . $data["details"][0]["d"] . "\r\n";
    }
    if (isset($data["details"][0]["e"]) && $data["details"][0]["e"] != "") 
    {
      $metadataOU .= "IS  - " . $data["details"][0]["e"] . "\r\n";
    }	
	if (isset($data["additionalinfo"][0]["u"]) && $data["additionalinfo"][0]["u"] != "") 
    {
      $metadataOU .= "UR  - " . $data["additionalinfo"][0]["u"] . "\r\n";
    }
    if (isset($data["details"][0]["h"]) && $data["details"][0]["h"] != "") 
    {
      if ( strpos($data["details"][0]["h"], "-") !== false ) 
      {
        $metadataOU .= "SP  - " . strstr($data["details"][0]["h"], '-', true) . "\r\n";
        $metadataOU .= "EP  - " . substr(strstr($data["details"][0]["h"], "-"), 1) . "\r\n";
      }
	}
    $metadataOU .= "S1  - Gemeinsamer Bibliotheksverbund (GBV) / Verbundzentrale des GBV (VZG)\r\n";
    $metadataOU .= "S2  - OPAC Magdeburg\r\n";
    $metadataOU .= "S3  - Lukida.ub_md\r\n";
    $metadataOU .= "L3  - " . base_url() . $data["id"] . "/id\r\n";
    return $metadataOU;
  }

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
    /*if (isset($data["details"][0]["a"]) && $data["details"][0]["a"] != "") 
    {
      $metadataOU .= "% " . $data["details"][0]["a"] . "\r\n";
    }
    */
    if (isset($data["details"][0]["j"]) && $data["details"][0]["j"] != "") 
    {
      $metadataOU .= "%D " . $data["details"][0]["j"] . "\r\n";
    }
	elseif (isset($data["publisher"][0]["c"]) && $data["publisher"][0]["c"] != "") 
	{
	  $metadataOU .= "%D " . $data["publisher"][0]["c"] . "\r\n";
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
    if (isset($data["details"][0]["d"]) && $data["details"][0]["d"] != "") 
    {
      $metadataOU .= "%V " . $data["details"][0]["d"] . "\r\n";
    }
    if (isset($data["details"][0]["e"]) && $data["details"][0]["e"] != "") 
    {
      $metadataOU .= "%N " . $data["details"][0]["e"] . "\r\n";
    }
    if (isset($data["details"][0]["h"]) && $data["details"][0]["h"] != "") 
    {
      $metadataOU .= "%P " . $data["details"][0]["h"] . "\r\n";
    }
	elseif (isset($data["contents"]["300"][0][0]["a"]) && $data["contents"]["300"][0][0]["a"] != "") 
	{
	  $metadataOU .= "%P " . $data["contents"]["300"][0][0]["a"] . "\r\n";
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
	if (isset($data["additionalinfo"][0]["u"]) && $data["additionalinfo"][0]["u"] != "") 
    {
      $metadataOU .= "%U " . $data["additionalinfo"][0]["u"] . "\r\n";
    }
    $metadataOU .= "%U " . base_url() . $data["id"] . "/id\r\n";
	$metadataOU .= "%W Gemeinsamer Bibliotheksverbund (GBV) / Verbundzentrale des GBV (VZG)\r\n";
    return $metadataOU;
  }

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
    if (isset($data["edition"]) && $data["edition"] != "") 
    {
      $metadataOU .= "\tedition = {" . $data["edition"] . "},\r\n";
    }
    /*if (isset($data["details"][0]["a"]) && $data["details"][0]["a"] != "") 
    {
      $metadataOU .= "\t = {" . $data["details"][0]["a"] . "},\r\n";
    }
    */
    if (isset($data["details"][0]["j"]) && $data["details"][0]["j"] != "") 
    {
      $metadataOU .= "\tyear = {" . $data["details"][0]["j"] . "},\r\n";
    }
	elseif (isset($data["publisher"][0]["c"]) && $data["publisher"][0]["c"] != "") 
	{
	  $metadataOU .= "\tyear = {" . $data["publisher"][0]["c"] . "},\r\n";
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
    if (isset($data["details"][0]["d"]) && $data["details"][0]["d"] != "") 
    {
      $metadataOU .= "\tvolume = {" . $data["details"][0]["d"] . "},\r\n";
    }
    if (isset($data["details"][0]["e"]) && $data["details"][0]["e"] != "") 
    {
      $metadataOU .= "\tnumber = {" . $data["details"][0]["e"] . "},\r\n";
    }
    if (isset($data["details"][0]["h"]) && $data["details"][0]["h"] != "") 
    {
      $metadataOU .= "\tpages = {" . $data["details"][0]["h"] . "}\r\n";
    }
	elseif (isset($data["contents"]["300"][0][0]["a"]) && $data["contents"]["300"][0][0]["a"] != "") 
	{
	  $metadataOU .= "\tpages = {" . $data["contents"]["300"][0][0]["a"] . "}\r\n";
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
    $metadataOU .= "}";
    return $metadataOU;
  }

}

?>