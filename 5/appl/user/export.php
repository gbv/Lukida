<?php

class export
{
  private $CS;
  private $config;
  private $excelHeaderWritten = false;

  private const FORMATE = array(
    "citavi"  => "citavi",
    "endnote" => "endnote",
    "bibtex"  => "bibtex",
    "excel"   => "excel",
    "ris"     => "citavi",
    "zotero"  => "citavi",
    "csv"     => "excel",
  );

  public function formatname($format)
  {
    return self::FORMATE[strtolower(trim((string) $format))] ?? "";
  }

  public function __construct($CS, $Config)
  {
    $this->CS     = $CS;
    $this->config = $Config;
  }

  private function getExcelText($input)
  {
    return  trim(preg_replace(array("/;/","/\t/","/\n/","/\r/","/\v/"), array(";;"," ","","",""), $input));
  }

  public function exportfile($data, $format)
  {
    $exportTags = array
    (
        "format"              => array("citavi" => "TY  - ", "endnote" => "%0 ", "bibtex" => "@"            ),
        "id"                  => array("citavi" => "ID  - ", "endnote" => "%M ", "bibtex" => "-"            ),
        "title"               => array("citavi" => "T1  - ", "endnote" => "%T ", "bibtex" => "title = {"    ),
        "booktitle"           => array("citavi" => "BT  - ", "endnote" => "%B ", "bibtex" => "booktitle = {"),
        "subtitle"            => array("citavi" => "T2  - ", "endnote" => "%Q ", "bibtex" => "note = {"     ),
        "series"              => array("citavi" => "T3  - ", "endnote" => "%S ", "bibtex" => "series = {"   ),
        "journal"             => array("citavi" => "JF  - ", "endnote" => "%J ", "bibtex" => "journal = {"  ),
        "author"              => array("citavi" => "A1  - ", "endnote" => "%A ", "bibtex" => "author = {"   ),
        "associates"          => array("citavi" => "A2  - ", "endnote" => "%E ", "bibtex" => "editor = {"   ),
        "language"            => array("citavi" => "LA  - ", "endnote" => "%G ", "bibtex" => "language = {" ),
        "note"                => array("citavi" => "N1  - ", "endnote" => "%Z ", "bibtex" => "note = {"     ),
        "school"              => array(                                          "bibtex" => "school = {"   ),
        "type"                => array(                                          "bibtex" => "type = {"     ),
        "summary"             => array("citavi" => "N2  - ", "endnote" => "%X ", "bibtex" => "abstract = {" ),
        "issn"                => array("citavi" => "SN  - ", "endnote" => "%@ ", "bibtex" => "issn = {"     ),
        "isbn"                => array("citavi" => "SN  - ", "endnote" => "%@ ", "bibtex" => "isbn = {"     ),
        "edition"             => array("citavi" => "ET  - ", "endnote" => "%7 ", "bibtex" => "edition = {"  ),
        "callnumber"          => array(                      "endnote" => "%L ",                            ),
        "physicaldescription" => array("citavi" => "U1  - ", "endnote" => "%P ", "bibtex" => "note = {"     ),
        "place"               => array("citavi" => "CY  - ", "endnote" => "%C ", "bibtex" => "address = {"  ),
        "publisher"           => array("citavi" => "PB  - ", "endnote" => "%I ", "bibtex" => "publisher = {"),
        "date"                => array("citavi" => "PY  - ", "endnote" => "%D ", "bibtex" => "year = {"     ),
        "volume"              => array("citavi" => "VL  - ", "endnote" => "%V ", "bibtex" => "volume = {"   ),
        "issue"               => array("citavi" => "IS  - ", "endnote" => "%N ", "bibtex" => "number = {"   ),
        "startpage"           => array("citavi" => "SP  - "                                                 ),
        "endpage"             => array("citavi" => "EP  - "                                                 ),
        "pages"               => array(                      "endnote" => "%P ", "bibtex" => "pages = {"    ),
        "doi"                 => array("citavi" => "DO  - ", "endnote" => "%R ", "bibtex" => "doi = {"      ),
        "subject"             => array("citavi" => "KW  - ", "endnote" => "%K ", "bibtex" => "keywords = {" ),
        "volltext"            => array("citavi" => "UR  - ", "endnote" => "%U ", "bibtex" => "url = {"      ),
        "institute"           => array("citavi" => "S1  - ", "endnote" => "%W "                             ),
        "database"            => array("citavi" => "S2  - ", "endnote" => "%~ "                             ),
        "sid"                 => array("citavi" => "S3  - "                                                 ),
        "url"                 => array("citavi" => "L3  - "                                                 ),
        "endtag"              => array("citavi" => "ER  - ",                     "bibtex" => "}"            )
    );
    $exportFormats = array
    (
        "article"        => array("bibtex" => "Article",       "endnote" => "Journal Article",        "citavi" => "Journal Article"                       ),
        "book"           => array("bibtex" => "Book",          "endnote" => "Book",                   "citavi" => "Book"                                  ),
        "journal"        => array("bibtex" => "Periodical",    "endnote" => "Journal",        "citavi" => "Journal"                       ),
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

    $tagPrefix       = $format == "bibtex" ? "\t"     : "";
    $tagExtention    = $format == "bibtex" ? "},\r\n" : ($format == "excel" ? "" : "\r\n");

    $libGenre951 = empty($data["genre"]) ? "" : $data["genre"];
    $libGenre   = ($libGenre951 == "MV" || $libGenre951 == "ST")
                   ? (($libGenre951 == "MV") ? "Bookitem" : "Book")
                   : ($libGenre951 == "AR" ? "Article" : ($libGenre951 == "JT" ? "Journal" : ucfirst($data["format"]) ));

    $metadataOU = "";
    if( $format == "excel" )
    {
        $delimiter =  ";";
        $dataArray = array("header" => "", "body" => "");

        $excelKeys = array("format","id","title","booktitle","journal","subtitle","series","author","associates","isbn","issn","place","publisher","date","volume","issue","pages","language","note","school","type","summary","edition","physicaldescription","doi","subject","volltext","url","standort","signatur");

        if(!$this->excelHeaderWritten)
        {
            for( $i = 0; $i < count($excelKeys); $i++ )
            {
                $metadataOU .= ucfirst($excelKeys[$i]) . (($i < count($excelKeys)-1) ?  $delimiter : "\n");
            }

            $this->excelHeaderWritten = true;
        }

        for( $i = 0; $i < count($excelKeys); $i++ )
        {
            if( strpos(",format,id,subtitle,series,isbn,issn,place,publisher,date,volume,issue,pages,language,school,type,summary,edition,doi,subject,volltext,url,standort,signatur,", ("," . $excelKeys[$i] . ",")) !== false )
        {
            $value = !empty($data[$excelKeys[$i]]) ? $data[$excelKeys[$i]] : "";

            if ($excelKeys[$i] === "pages" && $value !== "")
         {
            $value = '="' . $this->getExcelText($value) . '"';
            $metadataOU .= $value . (($i < count($excelKeys)-1) ? $delimiter : "\n");
         }
           else
         {
        $metadataOU .= ($value !== "" ? $this->getExcelText($value) : "") . (($i < count($excelKeys)-1) ? $delimiter : "\n");
         }
        }
          elseif( $excelKeys[$i] == "title"  )
            {
                if( !empty($data["partTitle"]) )
                {
                   $metadataOU .= $this->getExcelText($data["partTitle"]) . $delimiter;

                   $metadataOU .= (empty($data["title"]) ? "" : $this->getExcelText($data["title"])) .  $delimiter . $delimiter;
                }
                elseif( !empty($data["articleTitle"]) )
                {
                   $metadataOU .= $this->getExcelText($data["articleTitle"]) . $delimiter . $delimiter;

                   $metadataOU .= (empty($data["title"]) ? "" : $this->getExcelText($data["title"])) . $delimiter;
                }
                else
                {
                   $metadataOU .= (empty($data["title"]) ? "" : $this->getExcelText($data["title"])) . $delimiter;

                   $metadataOU .= (empty($data["collTitle"]) ? "" : $this->getExcelText($data["collTitle"])) .  $delimiter . $delimiter;
                }
            }
            elseif( $excelKeys[$i] == "author" || $excelKeys[$i] == "associates" )
            {
                if( !empty($data[$excelKeys[$i]]) )
                {
                    $ai = 1;
                    foreach($data[$excelKeys[$i]] as $aData)
                    {
                        if( !empty($aData) )
                        {
                            $metadataOU .= (($ai > 1) ? " | " : "") . $this->getExcelText($aData);
                            $ai++;
                        }
                    }
                }
                $metadataOU .= $delimiter;
            }
            elseif( $excelKeys[$i] == "note" )
            {
                if(  !empty($data["notes"]) )
                {
                    $ai = 1;
                    foreach($data["notes"] as $note )
                    {
                        if( !empty($note) && $note != "" )
                        {
                           $metadataOU .= (($ai > 1) ? " | " : "") . $this->getExcelText($note);
                        }
                    }
                }
                $metadataOU .= $delimiter;
            }
            elseif( $excelKeys[$i] == "physicaldescription" )
            {
                if(  !empty($data["physdesc"]) )
                {
                    $metadataOU .= $this->getExcelText($data["physdesc"]);
                }
                $metadataOU .= $delimiter;
            }
           elseif( !($excelKeys[$i] == "booktitle" || $excelKeys[$i] == "journal") )
           {
                if( !empty($data[$excelKeys[$i]]) )
                {
                   $metadataOU .= $this->getExcelText($data[$excelKeys[$i]]);
                }
                $metadataOU .= $delimiter;
           }
        }
    }
    else
    {
        if( $format == "bibtex" || $format == "endnote" || $format == "citavi" )
        {
            if( $libGenre951 == "MC"  )
            {
                $data["format"] = $exportFormats["collection"][$format];
            }
            elseif( !empty($data["genre"]) && strpos($data["genre"], "Hochschul") !== false )
            {
                if( !empty($data["type"]) )
                {
                  if( strpos($data["type"], "Diss") !== false )
                       $data["format"] = $exportFormats["phdthesis"][$format];
                  elseif( strpos($data["type"], "Studienarb") !== false )
                       $data["format"] = $exportFormats["techreport"][$format];
                  else $data["format"] = $exportFormats["mastersthesis"][$format];
                }
            }
            elseif( !empty($data["genre"]) && strpos($data["genre"], "Konferenz") !== false )
            {
                if( !empty($data["type"] && strpos($data["type"], "Band") !== false) )
                     $data["format"] = $exportFormats["proceedings"][$format];
                else $data["format"] = $exportFormats["conference"][$format];
            }
            elseif( !empty($data["format"]) && $data["format"] != "article" && (empty($data["publisher"]) || $data["publisher"] == " ") )
            {
                $data["format"] = $exportFormats["booklet"][$format];
            }
            elseif( !empty($libGenre) && $libGenre == "Bookitem" )
            {
                $data["format"] = $exportFormats["inbook"][$format];
            }
            elseif((!empty($libGenre) && ($libGenre != "Article" && $libGenre != "Journal")) && !empty($data["type"]) && strpos($data["type"], "Band") !== false )
            {
                $data["format"] = $exportFormats["incollection"][$format];
            }
            elseif( !empty($data["format"]) && $data["format"] == "journal" )
            {
                $data["format"] = $exportFormats["journal"][$format];
            }

            elseif( !empty($data["format"]) && $data["format"] == "unknown" )
            {
            $data["format"] = $exportFormats["unpublished"][$format];
            }
            elseif (isset($exportFormats[$data["format"]]))
            {
            $data["format"] = $exportFormats[$data["format"]][$format];
            }
            else
            {
            $data["format"] = $exportFormats["unknown"][$format];
            }
        }
        else
        {
            $data["format"] = $libGenre;
        }

        if( !empty($data["format"]) )
        {
          if (!empty($data["online"])) {
            if ($format === "bibtex") {
              $data["notes"][] = "Electronic resource";
            } else {
              $data["format"] = "Electronic " . $data["format"];
            }
          }

          $metadataOU .= $exportTags["format"][$format] . $data["format"] . ( $format == "bibtex" ? "" : $tagExtention );
          }

        if( $format === "bibtex")
        {
            $metadataOU .= "{GBV";
        }
        if( !empty($data["id"]) )
        {
            $metadataOU .= $exportTags["id"][$format] . $data["id"] . ( $format == "bibtex" ? ",\r\n" : $tagExtention );
        }
        if( !empty($data["title"]) )
        {
            if( !empty($data["partTitle"]) && $data["partTitle"] != " " )
            {
               $metadataOU .= $tagPrefix . $exportTags["title"][$format] . htmlspecialchars_decode($data["partTitle"]) . $tagExtention;
               $metadataOU .= $tagPrefix . $exportTags["booktitle"][$format] . htmlspecialchars_decode($data["title"]) . $tagExtention;
            }
            elseif( !empty($data["articleTitle"]) && $data["articleTitle"] != " " )
            {
               $metadataOU .= $tagPrefix . $exportTags["title"][$format] . htmlspecialchars_decode($data["articleTitle"]) . $tagExtention;
               $metadataOU .= $tagPrefix . (($libGenre == "Article") ? $exportTags["journal"][$format]
                                    : $exportTags["booktitle"][$format]) . htmlspecialchars_decode($data["title"]) . $tagExtention;
            }
            else
            {
               $metadataOU .= $tagPrefix . $exportTags["title"][$format] . htmlspecialchars_decode($data["title"]) . $tagExtention;
               $metadataOU .= (empty($data["collTitle"]) || $data["collTitle"] == " ") ? ""
                       : ($tagPrefix . $exportTags["booktitle"][$format] . htmlspecialchars_decode($data["collTitle"]) . $tagExtention);
            }
        }
        $tmpTagArray = explode(",", "subtitle,series");
        foreach( $tmpTagArray  as $tmpTag)
        {
            if( !empty($exportTags[$tmpTag][$format]) && !empty($data[$tmpTag]) && $data[$tmpTag] != " " )
            {
                $metadataOU .= $tagPrefix . $exportTags[$tmpTag][$format] . $data[$tmpTag] . $tagExtention;
            }
        }
        if( !empty($exportTags["author"][$format]) && !empty($data["author"]) )
        {
            $ai = 1; $metadataAu = "";
            foreach($data["author"] as $author)
            {
                $metadataAu .= ($format == "bibtex") ? ((($ai > 1) ? " and " : ($tagPrefix . $exportTags["author"][$format])) . $author) :
                               ($tagPrefix . $exportTags["author"][$format] . $author . $tagExtention);
                $ai++;
            }
            if( !empty($metadataAu) )
                $metadataOU .= $metadataAu . (($format == "bibtex") ? $tagExtention : "");
        }
        if( !empty($data["associates"]) )
        {
            $assi = 1; $metadataAss = "";
            foreach($data["associates"] as $associate )
            {
                $metadataAss .= ($format == "bibtex") ? ((($assi > 1) ? " and " : ($tagPrefix . $exportTags["associates"][$format])) . $associate) :
                                ($tagPrefix . $exportTags["associates"][$format] . $associate . $tagExtention);
                $assi++;
            }
            if( !empty($metadataAss) )
                $metadataOU .= $metadataAss . (($format == "bibtex") ? $tagExtention : "");
        }
        $tmpTagArray = explode(",", "isbn,issn,type,place,publisher,date,volume,issue,callnumber");
        foreach( $tmpTagArray  as $tmpTag)
        {
            if( !empty($exportTags[$tmpTag][$format]) && (!empty($data[$tmpTag]) && $data[$tmpTag] != " ") )
            {
                $metadataOU .= $tagPrefix . $exportTags[$tmpTag][$format] . htmlspecialchars_decode((is_array($data[$tmpTag]) ? $data[$tmpTag][0] : $data[$tmpTag])) . $tagExtention;
            }
        }
        if( !empty($data["edition"]) )
        {
            $metadataOU .= $tagPrefix . $exportTags["edition"][$format] . htmlspecialchars_decode($data["edition"]) . $tagExtention;
        }
        if( !empty($data["pages"]) )
        {
            if( $format == "citavi" )
            {
                if(  strpos($data["pages"], "-") !== false )
                {
                    $metadataOU .= $tagPrefix . $exportTags["startpage"][$format] . strstr($data["pages"], '-', true) . $tagExtention;
                    $metadataOU .= $tagPrefix . $exportTags["endpage"][$format] . substr(strstr($data["pages"], "-"), 1) . $tagExtention;
                }
                else
                {
                    $metadataOU .= $tagPrefix . $exportTags["startpage"][$format] . $data["pages"] . $tagExtention;
                }
            }
            else
            {
                $metadataOU .= $tagPrefix . $exportTags["pages"][$format] . $data["pages"] . $tagExtention;
            }
        }
        $tmpTagArray = explode(",", "subject,school,volltext,doi,summary,language,physicaldescription");
        foreach( $tmpTagArray  as $tmpTag)
        {
            if( !empty($exportTags[$tmpTag][$format]) && !empty($data[$tmpTag]) )
            {
                $metadataOU .= $tagPrefix . $exportTags[$tmpTag][$format] . htmlspecialchars_decode($data[$tmpTag]) . $tagExtention;
            }
        }
        if( !empty($data["notes"]) && is_array($data["notes"]) && count($data["notes"]) >= 1 )
        {
            foreach($data["notes"] as $note )
            {
                if( !empty($note) && $note != "" )
                {
                   $metadataOU .= $tagPrefix . $exportTags["note"][$format] . htmlspecialchars_decode($note) . $tagExtention;
                }
            }
        }
        if( !empty($data["physdesc"]) )
        {
          $metadataOU .= $tagPrefix . $exportTags["physicaldescription"][$format] . $data["physdesc"] . $tagExtention;
        }
        if( !empty($exportTags["note"][$format]) && !empty($data["computerfile"]) )
        {
            $metadataOU .= $tagPrefix . $exportTags["note"][$format] . $data["computerfile"] . $tagExtention;
        }

        if( !empty($exportTags["institute"][$format]))
        {
            $metadataOU .= $tagPrefix . $exportTags["institute"][$format] . "Gemeinsamer Bibliotheksverbund (GBV) / Verbundzentrale des GBV (VZG)\r\n";
        }
        if( !empty($exportTags["database"][$format]))
        {
            $metadataOU .= $tagPrefix . $exportTags["database"][$format] . $this->config["title"] . $tagExtention;
        }
        if( !empty($exportTags["sid"][$format]))
        {
            $metadataOU .= $tagPrefix . $exportTags["sid"][$format] . $this->config["openurl"] . $tagExtention;
        }
        if( !empty($exportTags["url"][$format]))
        {
            $metadataOU .= $tagPrefix . $exportTags["url"][$format] . $this->config["base_url"] . "/id%7Bcolon%7D" . $data["id"] . $tagExtention;
        }
        if( $format == "bibtex")
        {
            $metadataOU = substr($metadataOU, 0, -3) . "\r\n";
        }
        if( !empty($exportTags["endtag"][$format]))
        {
            $metadataOU .= (($format == "bibtex") ? "" : $tagPrefix) . $exportTags["endtag"][$format];
        }

        if ($format !== "excel") {
            $metadataOU = rtrim($metadataOU, "\r\n") . "\r\n\r\n";
        }
    }
    return $metadataOU;
  }
}

?>
