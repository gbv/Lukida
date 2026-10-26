<?php

class resolver
{
  private $CS;
  private $config;

  public function __construct($CS, $Config)
  {
    $this->CS     = $CS;
    $this->config = $Config;
  }

  private function hasResolverId(array $data): bool
  {
    if (!empty($data['doi'])   && trim($data['doi'])   !== '') return true;
    if (!empty($data['isbn'])  && trim($data['isbn'])  !== '') return true;
    if (!empty($data['issn'])  && trim($data['issn'])  !== '') return true;
    if (!empty($data['zdbid']) && trim($data['zdbid']) !== '') return true;

    return false;
  }

  private function getJOP_Full($link, $libparam)
  {
    $joponlyfulltext = true;

     stream_context_set_default([
    'ssl'  => ['verify_peer' => false, 'verify_peer_name' => false],
    'http' => ['timeout' => 3]
    ]);
    try
    {
        $ezb_xml = @simplexml_load_file($link, 'SimpleXMLElement', LIBXML_NOWARNING);
    }
    catch (Exception $e)
    {
    if ( MODE != "production" ) $this->CS->debug("PROBLEM_RESOLVER_JOP_" . $this->CS->client, $this->CS->posted["id"] . ": " . $e->getMessage() . " - " . $link);
    }

    if( !empty($ezb_xml))
    {
        if( !isset($ezb_xml->Full->Error))
        {
            $ref = ""; $refUrl = ""; $refUrl = "";

            if(  $ezb_xml && isset($ezb_xml->Full->ElectronicData->References->Reference[0]) &&
                 $ref = $ezb_xml->Full->ElectronicData->References->Reference[0] )
            {
                $refUrl   = isset($ref->URL) ? $ref->URL : "";
                $refLabel = isset($ref->Label) ? $ref->Label : "";
            }
            if(  $ezb_xml && isset($joponlyfulltext) && isset($ezb_xml->Full->ElectronicData->ResultList->Result) &&
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

                    if( !empty($resultAccessURL) && ($resultStatus == "0" || $resultStatus == "1" || $resultStatus == "2") )
                    {
                        if( $resultAccessLevel != "homepage" )
                        {
                            $resultAdditionalDecoded = html_entity_decode($resultAdditional, ENT_QUOTES, 'UTF-8');
                            if( strpos($resultAdditionalDecoded, "DFG-gef rdert") !== false
                            || strpos($resultAdditional, "DFG-gef") !== false )
                            {
                                return $resultAccessURL;
                            }
                            else
                            {
                                $accessUrl = $resultAccessURL;
                            }
                        }
                    }
                }
                if ( MODE != "production" && empty($accessUrl) ) $this->CS->debug("ALI_JOP_NORESULT_" . $this->CS->client, $link);
                return $accessUrl;
            }
            elseif(  $refUrl != "" && $refLabel == "EZB-Opac" )
            {
                return $refUrl . "&" . $libparam;
            }
            else return "";
        }
        else return "";
    }
    return "";
  }

  private function get_resolver_link($resolverBase, $data)
  {
    $resolver = key($resolverBase);
    $return = array();
    if( !empty($resolverBase[$resolver]) )
    {
        $openurlReferer   = empty($this->config["openurl"]) ? "Lukida"
                             : $this->config["openurl"] ;
        $openurlEntry     = $resolverBase[$resolver] . "?sid=GBV:" . $openurlReferer
                             . (($resolver !== "redi") ? "&ctx_enc=info:ofi/enc:UTF-8" : "");
        $resolverMetadata  = $this->getOpenURLmetaData($data, $resolver);
        $resolverLink     = $openurlEntry . $resolverMetadata;

        switch ($resolver)
        {
            case "ovid":
                $return[$resolver] = $resolverLink;
                break;

                case "redi":
                $resolverLink = str_replace("&rft.","&",$resolverLink);
                $resolverLink = str_replace(" ","%20",$resolverLink);

            $ctx = stream_context_create([
           'ssl'  => ['verify_peer' => false, 'verify_peer_name' => false],
           'http' => [
       'method'        => 'GET',
       'timeout'       => 3,
           'ignore_errors' => true,
       'header'        => "User-Agent: Mozilla/5.0\r\n",
        ],
    ]);

    try
    {
        $headers = get_headers($resolverLink, 1, $ctx);
    }
    catch (Exception $e)
    {
        if ( MODE != "production" ) $this->CS->debug("PROBLEM_RESOLVER_HEADER_" . $this->CS->client . "_" . strtoupper($resolver), $this->CS->posted["id"] . ": " . $e->getMessage() . " - " . $resolverLink);
        $headers = false;
    }

    if ($headers === false) {
        if ( MODE != "production" ) $this->CS->debug("PROBLEM_RESOLVER_HEADER_" . $this->CS->client. "_" . strtoupper($resolver), $this->CS->posted["id"] . ": ". $resolverLink);
        break;
    }

    if (isset($headers[0]) && (strpos($headers[0], "301") !== false || strpos($headers[0], "302") !== false)) {
        $locations = isset($headers["Location"])
            ? (is_array($headers["Location"]) ? $headers["Location"] : [$headers["Location"]])
            : [];

        foreach ($locations as $aLocation) {
            if ($aLocation
                && strpos($aLocation, "http") !== false
                && strpos($aLocation, ".redi-bw.de") === false
                && strpos($aLocation, "ezb.uni-regensburg.de") === false) {

                $return[$resolver] = $aLocation;
                return $return;
            }
        }
    }
    if ( MODE != "production" && empty($return) ) $this->CS->debug("ALI_REDI_NORESULT_" . $this->CS->client, $resolverLink);
    break;

    case "redi2":
    if ( MODE != "production" ) $this->CS->debug("ALI_REDI2_REQUEST", $resolverLink);
    $resolverLink = str_replace("&rft.", "&", $resolverLink);
    $resolverLink = str_replace("&rft.", "&", $resolverLink);
    $resolverLink = str_replace(" ", "%20", $resolverLink);

    $ctx = stream_context_create([
        'ssl'  => ['verify_peer' => false, 'verify_peer_name' => false],
        'http' => [
            'method'        => 'GET',
            'timeout'       => 2,
            'ignore_errors' => true,
            'header'        => "User-Agent: Mozilla/5.0\r\n",
        ],
    ]);

    try
    {
        $headers = get_headers($resolverLink, 1, $ctx);
    }
    catch (Exception $e)
    {
        if ( MODE != "production" ) $this->CS->debug("PROBLEM_RESOLVER_HEADER_" . $this->CS->client . "_" . strtoupper($resolver), $this->CS->posted["id"] . ": " . $e->getMessage() . " - " . $resolverLink);
        $headers = false;
    }

    if ($headers === false) {
        if ( MODE != "production" ) $this->CS->debug("PROBLEM_RESOLVER_HEADER_" . $this->CS->client . "_" . strtoupper($resolver), $this->CS->posted["id"] . ": " . $resolverLink);
        break;
    }

    if (isset($headers[0]) && (strpos($headers[0], "301") !== false || strpos($headers[0], "302") !== false)) {
        $locations = isset($headers["Location"])
            ? (is_array($headers["Location"]) ? $headers["Location"] : [$headers["Location"]])
            : [];

        foreach ($locations as $aLocation) {
            if ($aLocation
                && strpos($aLocation, "http") !== false
                && strpos($aLocation, ".redi-bw.de") === false
                && strpos($aLocation, "ezb.uni-regensburg.de") === false) {

                $return[$resolver] = $aLocation;
                return $return;
            }
        }
    }
    if ( MODE != "production" && empty($return) ) $this->CS->debug("ALI_REDI2_NORESULT_" . $this->CS->client, $resolverLink);
    break;

                        case "sfx":
                if ( MODE != "production" ) $this->CS->debug("ALI_SFX_REQUEST", $resolverLink);
                $sfxFullUrl = $this->getSFX_Full($resolverLink);
                if( $sfxFullUrl != "" )
                {
                    $return[$resolver] = $sfxFullUrl;
                }
                break;
        }
    }
    return $return;
  }

  private function getOpenURLmetaData($data,$resolver)
  {
    $metadataOU  = "";
    $libGenre951 = empty($data["genre"]) ? "" : $data["genre"];

      if( !empty($data["format"]) && $resolver != "zotero" )
      {
        $libGenre = ($libGenre951 == "MV" || $libGenre951 == "ST")
                ? (($libGenre951 == "MV") ? "Bookitem" : "Book")
                : ($libGenre951 == "AR" ? "Article" : ($libGenre951 == "JT" ? "Journal" : ucfirst($data["format"])));

        if ( $resolver == "jop" ) {

          $hasJournalId = !empty($data["issn"]) || !empty($data["zdbid"]);
          $isArticle    = strpos(strtolower($data["format"]), "article") !== false;
          $metadataOU   = "&rft.genre=" . (($isArticle && $hasJournalId) ? "article" : "journal");
          } else {
            $metadataOU = "&rft.genre=" . $libGenre;
          }
       }
    if( !empty($data["issn"]) && $data["issn"] != " " )
    {
        $metadataISSN = trim($data["issn"]);
        $metadataISSN = rtrim($metadataISSN, ' ,;');
        if( strpos($data["issn"]," ") !== false )
        {
            $metadataISSN = strstr($data["issn"], ' ', true);
        }
        if( $data["issn"] != "" && strpos($data["issn"],"-") === false )
        {
            $metadataISSN = substr($data["issn"],0,4) . '-' . substr($data["issn"],4);
        }
        $metadataOU .= "&rft." . "issn="   . $metadataISSN;
    }
    if( !empty($data["isbn"]) && $data["isbn"] != " " )
    {
    $isbn = strpos($data["isbn"]," ") !== false ? strstr($data["isbn"], ' ', true) : $data["isbn"];
    $isbn = rtrim(trim($isbn), ",;");
    $metadataOU .= "&rft.isbn=" . $isbn;
    }

    if( !empty($data["date"]) && $data["date"] != " " )
    {
      $date = str_replace(array("[","]"), array("",""), $data["date"]);
      $date = trim($date, "- ");

      if ( preg_match('/^(\d{4}-\d{2}-\d{2})/', $date, $m) ) {
        $date = $m[1];
      } elseif ( preg_match('/^(\d{4}-\d{2})/', $date, $m) ) {
        $date = $m[1];
      } elseif ( preg_match('/(\d{4})/', $date, $m) ) {
        $date = $m[1];
      } else {
        $date = "";
    }

    if ( !empty($date) ) $metadataOU .= "&rft.date=" . $date;
    }

    if( !empty($data["volume"]) && $data["volume"] != " " ) { $metadataOU .= "&rft." . "volume=" . $data["volume"]; }
    if( !empty($data["issue"])  && $data["issue"]  != " " ) { $metadataOU .= "&rft." . "issue="  . $data["issue"];  }
    if( !empty($data["pages"])  && $data["pages"]  != " " )
    {
        if ( strpos($data["pages"], "-") !== false )
        {
            $metadataOU .= "&rft." . "spage=" . strstr($data["pages"], '-', true);
            $metadataOU .= "&rft." . "epage=" . substr(strstr($data["pages"], "-"), 1);
        }
        else
        {
            if( strpos($data["pages"], ".") === false )
            {
                $metadataOU .= "&rft." . "spage=" . $data["pages"];
            }
            $metadataOU .= "&rft." . "pages=" . $data["pages"];
        }
    }
    if( !empty($data["doi"]) && ($resolver != "jop" || strpos(strtolower($data["format"]),"article") !== false) )
    {
      $doiBase     = "https://doi.org/";
      $doi         = $data["doi"];
      $doi         = (strpos(strtolower($doi), "http") === false ) ? ((($resolver == "zotero" || $resolver == "refworks") ? $doiBase : "") . $doi)
                                                                   :  (($resolver == "zotero" || $resolver == "refworks") ? $doi : strstr($doi,$doiBase,true));
      $metadataOU .= (($resolver == "zotero") ? "&rft_id=info:doi/" : (($resolver == "jop") ? "&id=doi%3A" : "&rft.doi=")) . $doi;
    }
    if(  $resolver != "jop" )
    {
        if( !empty($data["partTitle"]) )    { $metadataOU .= "&rft.atitle=" . urlencode(html_entity_decode($data["partTitle"]));    }
        if( !empty($data["articleTitle"]) ) { $metadataOU .= "&rft.atitle=" . urlencode(html_entity_decode($data["articleTitle"])); }
        if( !empty($data["title"]) )        { $metadataOU .= (empty($data["collTitle"]) ? "&rft.title=" : "&rft.atitle=")  . urlencode(html_entity_decode($data["title"]));        }
        if( !empty($data["collTitle"]) )    { $metadataOU .= "&rft.btitle=" . urlencode(html_entity_decode($data["collTitle"]));    }
        if( !empty($data["series"]) )       { $metadataOU .= "&rft.series=" . urlencode(html_entity_decode($data["series"]));       }

        if( strpos("ovid,redi,sfx",$resolver) === false )
        {
            if( !empty($data["edition"]) )
            {
                $metadataOU .= "&rft.edition=" . urlencode(html_entity_decode($data["edition"]));
            }
            $authors = empty($data["author"]) ? (empty($data["associates"]) ? "" : $data["associates"]) : (empty($data["associates"]) ? $data["author"] : array_merge($data["author"],$data["associates"]));
            if( !empty($authors) )
            {   $ai = 1;
                foreach( $authors as $author )
                {
                    $metadataOU .= "&rft." . "au=" . $author;
                    if( strpos($author, ', ') !== false && $ai == 1)
                    {
                        $metadataOU .= "&rft." . "aulast="  . strstr($author, ', ', true);
                        $metadataOU .= "&rft." . "aufirst=" . substr(strstr($author, ", "), 2);
                    }
                    if ($resolver != "zotero" && $resolver != "refworks") break;
                    $ai++;
                }
            }
            if( !empty($data["place"]) )                             { $metadataOU .= "&rft.place="       . $data["place"];    }
            if( !empty($data["publisher"]) )                         { $metadataOU .= "&rft.pub="         . urlencode(html_entity_decode($data["publisher"])); }
            if( $resolver == "zotero" && !empty($data["physdesc"]) ) { $metadataOU .= "&rft.tpages="      . urlencode(html_entity_decode($data["physdesc"])); }
            if( $resolver == "zotero" && !empty($data["language"]) ) { $metadataOU .= "&rft.language="    . $data["language"]; }
        }
    }
    return $metadataOU;
  }

  private function get_jop_link($data)
  {
    $ezbLink = "";
    if(  !empty($data["issn"]) || !empty($data["isbn"]) || !empty($data["zdbid"]) )
    {
        $zdbid            = ((empty($data["issn"]) && empty($data["isbn"]) && !empty($data["zdbid"])) ? $data["zdbid"] : "");

        $ezbbibidRaw      = $this->config["ezbbibid"] ?? "";
        $ezbbibid         = !empty($ezbbibidRaw) ? trim(explode(",", $ezbbibidRaw)[0]) : null;
        $isil             = empty($this->config["isil"]) ? null : $this->config["isil"] ;
        $bibparam         = !empty($ezbbibid) ? ("bibid%3D" . $ezbbibid) : (!empty($isil) ? ("isil%3D" . $isil) : "");
        $openurlReferer   = empty($this->config["openurl"]) ? "Lukida" : $this->config["openurl"] ;
        $openurlMetadata  = str_replace("&rft.","&",$this->getOpenURLmetaData($data, "jop"));
        $ezbLinkExtension = "sid=GBV:" . $openurlReferer . $openurlMetadata .
                            (!empty($bibparam) ? ("&pid=online%3D1%26" . (!empty($bibparam) ? ($bibparam . "%26") : "") . (!empty($zdbid) ? ("zdbid%3D" . $zdbid) : "")) : "");
        $ezbLink          = "https://services.dnb.de/fize-service/gvr/full.xml?" . $ezbLinkExtension;
        if ( MODE != "production" ) $this->CS->debug("ALI_JOP_REQUEST", $ezbLink);

        $ezbTarget        = $this->getJOP_Full($ezbLink, str_replace('%3D', '=', $bibparam));
        return $ezbTarget;
    }
    return "";
  }

  private function getSFX_Full($link)
  {
    $sfx_xml_url = $link . "&sfx.response_type=simplexml";

    stream_context_set_default([
    'ssl'  => ['verify_peer' => false, 'verify_peer_name' => false],
    'http' => ['timeout' => 3]
     ]);

    try
    {
        $sfx_xml = @simplexml_load_file($sfx_xml_url, 'SimpleXMLElement', LIBXML_NOWARNING);
    }
    catch (Exception $e)
    {
    if ( MODE != "production" ) $this->CS->debug("PROBLEM_RESOLVER_SFX_" . $this->CS->client, $this->CS->posted["id"] . ": " . $e->getMessage() . " - " . $link);
    }
    if ( !empty($sfx_xml) )
    {
        if( !empty($sfx_xml_targets = $sfx_xml->targets))
        {
            foreach ($sfx_xml_targets->target as $target)
            {
                if( $target->target_name == 'MESSAGE_NO_FULLTXT')
                {
                    return "";
                }
                elseif( $target->service_type == 'getFullTxt')
                {
                    if( !empty($target->target_url))
                    {
                        return (string)$target->target_url;
                    }
                }
            }

            if ( MODE != "production" ) $this->CS->debug("ALI_SFX_NORESULT_" . $this->CS->client, $link);
            return "";
        }
        else return "";
    }
    else return "";
  }

  public function exportlink($data, $format)
  {
    $openurlEntry    = "";    $openurlBase     = "";
    $openurlReferer  = "";    $openurlMetadata = "";

    switch ($format)
    {
        case "refworks" : if( $this->config["refworks"] == "1")
                              $openurlBase = "https://www.refworks.com/express/expressimport.asp";
                          break;
        case "zotero"   : if( $this->config["zotero"] == "1")
                              $openurlBase = "ctx_ver=Z39.88-2004";
                          break;
    }

    if( $openurlBase != "")
    {
        $libGenre951     = $data["genre"];
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
        $genreZotero     = isset($zoteroFormats[strtolower($data["format"])]) ? strtolower($data["format"]) : (($libGenre951 == "MV" || $libGenre951 == "ST")
                           ? (($libGenre951 == "MV") ? "inbook" : "book")
                           : ($libGenre951 == "AR" ? "article" : ($libGenre951 == "JT" ? "journal" : strtolower($data["format"]) )));
        $openurlReferer  = !empty($this->config["openurl"]) ? $this->config["openurl"] : "Lukida";

        $openurlEntry    = $openurlBase . ($format == "zotero" ? "&" : "?") .
                            "sid=GBV:" . $openurlReferer . "&ctx_enc=info:ofi/enc:UTF-8" .
                            ($format == "zotero" ? ("&rft_val_fmt=info:ofi/fmt:kev:mtx:" . (isset($zoteroFormats[$genreZotero]) ? $zoteroFormats[$genreZotero] : $genreZotero)) : "") .
                            ($format == "zotero" ? ("&rft_id=" . $this->config["base_url"] . "/search?v%3D" . $data["id"]) : "");
        $openurlMetadata = $this->getOpenURLmetaData($data,$format);

        $link            = $openurlEntry . (($format == "refworks") ? str_replace("&rft.","&",$openurlMetadata) : $openurlMetadata);
    }
    else
    {
        $link = null;
    }

    return $link;
  }

  public function linkresolver($data)
  {
    $linkarray = array();

    $hasId = $this->hasResolverId($data);

    if (!$hasId)
    {
      return $linkarray;
    }

    $fulltextPriosArray = ["multi"];
    foreach( $this->config as $confItemKey => $confItemValue )
    {
        if( strpos("ovid,redi,sfx,redi2", $confItemKey) !== false && $confItemValue === true )
        {
            $fulltextPriosArray[] = $confItemKey;
        }
    }
    if( $this->config["jop"] === true )
    {
        $fulltextPriosArray[] = "jop";
    }

    for( $i=(strpos("multi,single",$fulltextPriosArray[0]) !== false ? 1 : 0);$i<count($fulltextPriosArray);$i++ )
    {
        $resolver   = $fulltextPriosArray[$i];
        if( !empty($this->config[$resolver]))
        {
            if( $resolver == "jop" )
            {
                if( ($Link = $this->get_jop_link($data)) != "" )
                {
                    $linkarray["jop"] = str_replace(array(chr(39),chr(34)), array("%27","%22"), $Link);
                }
            }
            else
            {
                $resolverBaseUrl = $this->config[($resolver . "base")];
                if ( strpos($resolver, "redi") !== false && strpos($resolverBaseUrl, "{ezbbibid}") !== false )
                {
                    $ezbids     = explode(",", $this->config["ezbbibid"] ?? "");
                    $ezbidIndex = ($resolver === "redi2") ? 1 : 0;
                    $ezbid      = strtolower(trim($ezbids[$ezbidIndex] ?? ""));
                    $resolverBaseUrl = str_replace("{ezbbibid}", $ezbid, $resolverBaseUrl);
                }
                $linkarray += $this->get_resolver_link(array($resolver => $resolverBaseUrl), $data);
            }
        }
        if( $fulltextPriosArray[0] !== "multi" && !empty($linkarray) )
        {
            break;
        }
    }

    return $linkarray;
  }
}

?>
