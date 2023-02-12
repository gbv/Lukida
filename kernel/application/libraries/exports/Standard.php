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
    // Create File Data
    // Um die Daten zu sehen, die folgende Zeile aktivieren:
    // $this->CI->printArray2File($data);
    $exportTags = array( 
        "format"         => array("citavi" => "TY  - ", "endnote" => "%0 ", "bibtex" => "@"            ),
        "id"             => array("citavi" => "ID  - ", "endnote" => "%M ", "bibtex" => "-"            ),
        "title"          => array("citavi" => "T1  - ", "endnote" => "%T ", "bibtex" => "title = {"    ),
        "booktitle"      => array("citavi" => "BT  - ", "endnote" => "%B ", "bibtex" => "booktitle = {"),
        "subtitle"       => array("citavi" => "T2  - ", "endnote" => "%Q ", "bibtex" => "note = {"     ),
        "series"         => array("citavi" => "T3  - ", "endnote" => "%B ", "bibtex" => "series = {"   ),
        "journal"        => array("citavi" => "JF  - ", "endnote" => "%J ", "bibtex" => "journal = {"  ),
        "author"         => array("citavi" => "A1  - ", "endnote" => "%A ", "bibtex" => "author = {"   ),
        "associates"     => array("citavi" => "A2  - ", "endnote" => "%E ", "bibtex" => "editor = {"   ),
        "language"       => array("citavi" => "LA  - ", "endnote" => "%G ", "bibtex" => "language = {" ),
        "note"           => array("citavi" => "N1  - ", "endnote" => "%Z ", "bibtex" => "note = {"     ),
        "school"         => array(                                          "bibtex" => "school = {"   ),
        "type"           => array(                                          "bibtex" => "type = {"     ),
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
        "doi"            => array("citavi" => "DO  - ", "endnote" => "%R ", "bibtex" => "doi = {"      ),
        "subject"        => array("citavi" => "KW  - ", "endnote" => "%K ", "bibtex" => "keywords = {" ),
        "volltext"       => array("citavi" => "UR  - ", "endnote" => "%U ", "bibtex" => "url = {"      ),
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
    if ($format == "bibtex")
    {
      if (substr($data["leader"],7,1) == "m" && substr($data["leader"],19,1) == "a")
        $data["format"] = "collection";
      elseif (!empty($data["genre"][0]["name"]) && strpos($data["genre"][0]["name"], "Hochschul") !== false) 
      {
        if (!empty($data["contents"][502][0][0]["b"]) || !empty($data["contents"][502][0][0]["a"]))
        {
          if(strpos($data["contents"][502][0][0]["b"], "Diss") !== false) 
               $data["format"] = "phdthesis";
          elseif(strpos($data["contents"][502][0][0]["a"], "Studienarb") !== false)
               $data["format"] = "techreport";
          else $data["format"] = "mastersthesis";
        }
      }
      elseif (!empty($data["genre"][0]["name"]) && strpos($data["genre"][0]["name"], "Konferenz") !== false) 
      {
        if(strpos($data["contents"][338][0][0]["a"], "Band") !== false)
             $data["format"] = "proceedings";
        else $data["format"] = "conference";
      }
      elseif (!empty($data["format"]) && $data["format"] != "article" && empty($data["publisher"][0]["b"]))
      {
        $data["format"] = "booklet";
      }
      elseif (!empty($data["isbn"]) && $data["format"] == "article")
      {
        $data["format"] = "inbook";
      }
      elseif(!empty($data["isbn"]) && !empty($data["contents"][338][0][0]["a"]) && strpos($data["contents"][338][0][0]["a"], "Band") !== false)
      {
        $data["format"] = "incollection";
      }
      elseif (!empty($data["format"]) && $data["format"] == "journal")
        $data["format"] = "periodical";
      elseif (!empty($data["format"]) && $data["format"] == "unknown")
        $data["format"] = "unpublished";
      elseif (!empty($data["format"]) && strpos("article,book,datamedia,game,manuscript,map,microform,mixedmaterials,monographseries,motionpicture,musicalscore,picture,projectedmedium,serialvolume,soundrecording,", $data["format"]) === false)
        $data["format"] = "misc"; 
    }
    if (!empty($data["format"])) 
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
      if (!empty($data["part"]))
      {
         $metadataOU .= $tagPrefix . $exportTags["title"][$format] . htmlspecialchars_decode($data["part"]) . $tagExtention;
         $metadataOU .= $tagPrefix . $exportTags["booktitle"][$format] . htmlspecialchars_decode($data["title"]) . $tagExtention;
      }
      else 
         $metadataOU .= $tagPrefix . $exportTags["title"][$format] . htmlspecialchars_decode($data["title"]) . $tagExtention;
    }
    if (isset($data["publisherarticle"])) 
    {
      if (is_array($data["publisherarticle"])) 
      {
        if (count($data["publisherarticle"]) >= 1 && isset($data["publisherarticle"][0]["t"]) && $data["publisherarticle"][0]["t"] != "") 
        {
          $publisherarticle = htmlspecialchars_decode($data["publisherarticle"][0]["t"]);
        }
      }
      else $publisherarticle = htmlspecialchars_decode($data["publisherarticle"]);
      if (!empty($publisherarticle)) 
      {
        $metadataOU .= $tagPrefix . (empty($data["isbn"]) ? $exportTags["journal"][$format] : $exportTags["series"][$format]) . (stripos($publisherarticle, "in:") !== false ?
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
          $seri = 1;
          foreach($data["serial"] as $serial) 
          {
            foreach($serial as $sKey=>$sValue) 
            {
              if (!empty($sValue) && $sKey == "a") 
              {
                $metadataOU .= (($seri == 1) ? ($tagPrefix . $exportTags["series"][$format]) : " | ") . ((stripos($sValue, "in:") !== false) ? trim(substr($sValue,stripos($sValue, "in:") + 3)) : $sValue);
              }
              else 
			  { $metadataOU .= " " . $sValue;
              }
            }
            $seri++;
          }
          $metadataOU .= $tagExtention;
        }
      }
      elseif ($data["serial"] != "") 
      {
        $metadataOU .= $tagPrefix . $exportTags["series"][$format] . ((stripos($data["serial"], "in: ") !== false) ? trim(substr($data["serial"],stripos($data["serial"], "in:") + 3)) : $data["serial"]) . $tagExtention;
      }
    }
    if (isset($data["author"])) 
    {
      if (!empty($data["author"])) 
      {
          $ai = 1; $metadataAu = "";
          foreach($data["author"] as $author) 
          {
            if (!empty($author["name"]))
            {
              $metadataAu = ($format == "bibtex") ? ($metadataAu . (($ai > 1) ? " and " : ($tagPrefix . $exportTags["author"][$format])) . $author["name"]) : 
                             ($tagPrefix . $exportTags["author"][$format] . $author["name"]);
              $ai++;
            }
          }
          if( !empty($metadataAu) ) 
            $metadataOU .= $metadataAu . $tagExtention;
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
             $metadataOU .= $tagPrefix . $exportTags["note"][$format] . htmlspecialchars_decode($note) . $tagExtention;
          }
        }
    }
	if (isset($data["dissertation"]) && $data["dissertation"] != "")
    {
		$metadataOU .= $tagPrefix . $exportTags["note"][$format] . $data["dissertation"] . $tagExtention;
    }
	if (isset($data["summary"]) && $data["summary"] != "")
    {
		$metadataOU .= $tagPrefix . $exportTags["summary"][$format] . htmlspecialchars_decode($data["summary"]) . $tagExtention;
    }
	if (isset($data["associates"])) 
    {
      if (!empty($data["associates"])) 
      {
          $assi = 1; $metadataAss = "";
          foreach($data["associates"] as $associate) 
          {
            if (($format != "bibtex" || $associate["role"] == "Herausgeber") && !empty($associate["name"]))
            {
              $metadataAss = ($format == "bibtex") ? ($metadataAss . (($assi > 1) ? " and " : ($tagPrefix . $exportTags["associates"][$format])) . $associate["name"]) : 
                             ($tagPrefix . $exportTags["author"][$format] . $associate["name"]);
              $assi++;
            }
          }
          if( !empty($metadataAss) ) 
            $metadataOU .= $metadataAss . $tagExtention;
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
        if (!empty($isbnVal))
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
    if (isset($exportTags["school"][$format]) && !empty($data["contents"][502][0][1]["c"]))
    {
      $metadataOU .= $tagPrefix . $exportTags["school"][$format] . $data["contents"][502][0][1]["c"] . $tagExtention;
    }
    if (isset($exportTags["type"][$format]) && (!empty($data["contents"][502][0][0]["a"]) ||
        !empty($data["contents"][502][0][0]["b"]) || !empty($data["contents"][338][0][0]["a"])))
    {
       $metadataOU .= $tagPrefix . $exportTags["type"][$format] . 
       (!empty($data["contents"][502][0][0]["a"]) ? $data["contents"][502][0][0]["a"] : (
        !empty($data["contents"][502][0][0]["b"]) ? $data["contents"][502][0][0]["b"] : (
        !empty($data["contents"][338][0][0]["a"]) ? $data["contents"][338][0][0]["a"] : ""))) . $tagExtention;
    }
    if (isset($data["publisher"][0]) && $data["publisher"][0] != "") 
    {
	  foreach($data["publisher"][0] as $publisherKey => $publisherValue)
	  {
		if ($publisherKey == "a" && !empty($publisherValue[0])) 
		{ 
			$metadataOU .= $tagPrefix . $exportTags["placepublished"][$format] . htmlspecialchars_decode($publisherValue[0]) . $tagExtention;
		}
		elseif ($publisherKey == "b" && !empty($publisherValue[0])) 
		{ 
			$metadataOU .= $tagPrefix . $exportTags["publisher"][$format] . htmlspecialchars_decode($publisherValue[0]) . $tagExtention;
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
	if (!empty($data["contents"]["024"]))
	{
		foreach($data["contents"]["024"] as $key24=>$value24) 
		{
            if (!empty($value24[2][2]) && $value24[2][2] == "doi")
              $metadataOU .= $tagPrefix . $exportTags["doi"][$format] . ((strpos(strtolower($value24[1]["a"]), "http") === false) ?  "https://doi.org/" : "") . $value24[1]["a"] . $tagExtention;
        }
    }
	if (isset($data["subject"][0]) && $data["subject"][0] != "") 
    {
		$metadataOU .= $tagPrefix . $exportTags["subject"][$format];
		foreach($data["subject"] as $aSubjectKey => $aSubject) 
        {
			if ($aSubject['name'] != "") 
			{
			$metadataOU .= $aSubject['name'] . ((count($data["subject"]) > 1 && $aSubjectKey < count($data["subject"]) - 1) ? " / " : "" );
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
  {//http://swb.bsz-bw.de/DB=2.1/DWN?PPN=1622490614&PRS=bibtex
   //var_dump(file_get_contents("http://findex.gbv.de/index/discovery/select?q=id:" . $data["id"] . "&fl=id,issn,isbn,ctrlnum,doi_str_mv,language,genre_facet,format,format_phy_str_mv,format_facet,title_full,title_short,hierarchy_top_title,is_hierarchy_title,authorswithroles_txt_mv,author2,author2-role,publisher,publishDate,publishPlace,hochschulschrift_txt_mv,abstract,container_title,container_volume,container_issue,container_start_page,source,url,ausleihindikator_str_mv"));
   //var_dump($data);
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
          if( strpos($headers[1],"301 Moved Permanently") !== false || strpos($headers[1],"302 Found") !== false )
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
					$metadataOU .= ( $exportformat == "jop" ? "&"  : "&rft." ) . "date=" . str_replace(array("[","]"),array("",""),$dValue);
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
				$metadataOU .= ( $exportformat == "jop" ? "&"  : "&rft." ) . "date=" . str_replace(array("[","]"),array("",""),$year[1]);
		}
		elseif (!empty($data["publisher"][0]["c"][0])) 
		{
			$metadataOU .= ( $exportformat == "jop" ? "&"  : "&rft." ) . "date=" . str_replace(array("[","]"),array("",""),$data["publisher"][0]["c"][0]);
		}	
		elseif (!empty($data["contents"]["008"]) && ctype_digit(substr($data["contents"]["008"],7,4))) 
		{
			$metadataOU .= ( $exportformat == "jop" ? "&"  : "&rft." ) . "date=" . str_replace(array("[","]"),array("",""),substr($data["contents"]["008"],7,4));
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
                    //Chr(32),%20 space    chr(39), %27 Single quote    chr(34), %22 Double Quotes    Chr(38), %26 &
					$publisherarticle = str_replace(array("&amp;","&quot;","&lt;","&gt;","&lsqb;","&rsqb;","&lcub;","&rcub;"), array("%26","%22","%3C","%3E","%5B","%5D","%7B","%7D"),$data["publisherarticle"][0]["t"]);
				}
				}
				else { $publisherarticle = $data["publisherarticle"];
				}
				if (!empty($publisherarticle)) 
				{
                    //Chr(32),%20 space    chr(39), %27 Single quote    chr(34), %22 Double Quotes    Chr(38), %26 &
					$metadataOU .= "&rft.atitle=" . str_replace(array("&amp;","&quot;","&lt;","&gt;","&lsqb;","&rsqb;","&lcub;","&rcub;"), array("%26","%22","%3C","%3E","%5B","%5D","%7B","%7D"),$data["title"]) . "&rft.title=" . (stripos($publisherarticle, "in:") !== false ?
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
			if (!empty($data["edition"])) 
			{
				$metadataOU .= "&rft.edition=" . (is_array($data["edition"]) ? (!empty($data["edition"][0]) ? $data["edition"][0] : "") : $data["edition"]);
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
						$metadataOU .= "&rft.au=" . $author["name"];
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
					$metadataOU .= "&rft.au=" . $data["author"];
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
            if ($exportformat == "zotero" && !empty($data["contents"]["024"]))
	        {
	            foreach($data["contents"]["024"] as $key24=>$value24) 
		        {
                  if (!empty($value24[2][2]) && $value24[2][2] == "doi")
                    $metadataOU .= "&rft_id=info:doi/" . ((strpos(strtolower($value24[1]["a"]), "http") === false) ?  "https://doi.org/" : "") . $value24[1]["a"];
                }
            }
		}
	}
    return $metadataOU;
  }
  
}

?>