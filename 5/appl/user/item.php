<?php

class item
{
  
  
  
  private $CS;
  private $aps;
  private $awop;
  private $best;
  private $casesetnr;
  private $childid;
  private $comment;
  private $epn;
  private $id;
  private $lightsblue;
  private $lightsgreen;
  private $lightsyellow;
  private $location;
  private $mode;
  private $one;
  private $pid;
  private $signature;
  private $stock;
  
  
  private $allcomment;
  private $alllocation;
  private $allsignature;
  private $allstock;

  public function __construct($CS)
  {

    
    
    $this->CS        = $CS;
    $this->mode      = (MODE == "production") ? "prod" : "test";
    $this->awop      = (isset($this->CS->configs["item"]["articleswithoutparents"]) && trim($this->CS->configs["item"]["articleswithoutparents"]) == "y")
                       ? true : false;
    $this->aps       = (isset($this->CS->configs["item"]["articleparentshift"])     && trim($this->CS->configs["item"]["articleparentshift"])     == "y")
                       ? true : false;

    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    $this->lightsblue   = array("indigo", "blue", "light-blue", "cyan", "purple", "deep-purple");
    $this->lightsgreen  = array("green", "light-green", "lime", "teal");
    $this->lightsyellow = array("amber", "orange", "yellow", "deep-orange");

    if ( APPDEBUG["FKT"] ) $this->CS->debug("FKT",__METHOD__);
  }

  
  
  

  
  
  
  

  
  
  

  
  private function ilnDB($ILN)
  {
    if ( !$ILN || !isset($this->CS->configs["iln2bms"][$ILN]) )  return "";
    $Map = $this->CS->configs["iln2bms"][$ILN];
    if ( !is_array($Map) )  return "";
    
    if ( isset($Map["db"]) )  return $Map["db"];
    $First = reset($Map);
    return ( is_array($First) && isset($First["db"]) ) ? $First["db"] : "";
  }

  private function normLight($Light)
  {
    return preg_replace('/-\d{1,2}$/', '', strtolower(trim((string) $Light)));
  }


  
  
  
  
  
  
  
  
  
  
  
  
  
  
  private function useParent()
  {
    return ( $this->pid && !$this->awop && !$this->aps );
  }

  private function isHapticArticle()
  {
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    $Online = isset($this->one["online"])                ? $this->one["online"]                : false;
    $Format = isset($this->one["formatcode"])            ? $this->one["formatcode"]            : "";
    $Phys   = isset($this->one["itemcount"]["physical"]) ? $this->one["itemcount"]["physical"] : 0;
    $Owner  = isset($this->one["owner"])                 ? $this->one["owner"]                 : false;

    return ( $this->pid
          && isset($this->CS->works["add"][$this->pid])
          && $Owner
          && !$Online
          && !$Phys
          && $Format == "article" );
  }

  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  private $ausdrueckeErlaubt = false;

  private function istAusdruck($Wert)
  {
    if ( !$this->ausdrueckeErlaubt )  return false;
    $W = trim((string) $Wert);
    return ( strlen($W) >= 3 && $W[0] === "/" && preg_match('~^/.+/[imsuxU]*$~s', $W) === 1 );
  }

  private function trifftAusdruck($Muster, $Str)
  {
    $Treffer = @preg_match(trim((string) $Muster), (string) $Str);
    if ( $Treffer === false )
    {
      $this->setLog("ERROR", "Ausdruck " . $Muster . " ist nicht lesbar - trifft nicht");
      return false;
    }
    return ( $Treffer === 1 );
  }

  private function checkStrInWildArr($Str, $Arr)
  {
    
    
    
    foreach ( $Arr as $One )
    {
      if ( $this->istAusdruck($One) )
      {
        if ( $this->trifftAusdruck($One, $Str) )  return true;
      }
      elseif ( substr($One, -1) == "*" )
      {
        $Pos = strpos($One, "*");
        if ( substr($One, 0, $Pos) == substr($Str, 0, $Pos) ) return true;
      }
      else
      {
        if ( $One == $Str )  return true;
      }
    }
    return false;
  }

  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  private function checkStrContainsArr($Str, $Arr)
  {
    $Str = (string) $Str;
    if ( !strlen($Str) )  return false;

    foreach ( $Arr as $One )
    {
      $One  = trim($One);
      if ( $this->istAusdruck($One) )
      {
        if ( $this->trifftAusdruck($One, $Str) )  return true;
        continue;
      }
      $Kern = trim($One, "*");
      if ( $Kern === "" )  { if ( $One !== "" )  return true;  continue; }

      
      if ( substr($One, 0, 1) !== "*" && substr($One, -1) === "*" )
      {
        if ( strpos($Str, $Kern) === 0 )    return true;
      }
      elseif ( strpos($Str, $Kern) !== false )  return true;
    }
    return false;
  }

  
  
  
  
  private function checkArrContainsArr($Werte, $Arr)
  {
    foreach ( (array) $Werte as $W )
      if ( $this->checkStrContainsArr($W, $Arr) )  return true;
    return false;
  }

  private function checkArrInWildArr($Arr1, $Arr2)
  {
    
    
    
    foreach ( $Arr1 as $Str )
    {
      foreach ( $Arr2 as $One )
      {
        if ( $this->istAusdruck($One) )
        {
          if ( $this->trifftAusdruck($One, $Str) )  return true;
        }
        elseif ( substr($One, -1) == "*" )
        {
          $Pos = strpos($One, "*");
          if ( substr($One, 0, $Pos) == substr($Str, 0, $Pos) ) return true;
        }
        else
        {
          if ( $One == $Str )  return true;
        }
      }
    }
    
    return false;
  }

  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  private function inLevelCollection()
  {
    $Titel = ( isset($this->one["collections"]) && is_array($this->one["collections"]) ) ? $this->one["collections"] : array();
    if ( !count($Titel) || !is_array($this->CS->levels) )  return false;
    foreach ( $this->CS->levels as $Level )
    {
      if ( !isset($Level["collectionsaddfull"]) || !is_array($Level["collectionsaddfull"]) )  continue;
      if ( count(array_intersect($Titel, array_map("strtolower", $Level["collectionsaddfull"]))) )  return true;
    }
    return false;
  }

  
  
  private function itemENR($Item)
  {
    $W = isset($Item["enr"]) ? $Item["enr"] : ( isset($Item["1"]) ? $Item["1"] : "" );
    if ( is_array($W) )  $W = isset($W[0]) ? $W[0] : "";
    return trim((string) $W);
  }

  private function hasPaia(&$Item)
  {
    
    if ( count($Item) )  return ( isset($Item["id"]) && trim($Item["id"]) != "" );

    
    if ( !isset($this->CS->paias[$this->id]) || !is_array($this->CS->paias[$this->id]) )  return false;
    foreach ( $this->CS->paias[$this->id] as $PI )
    {
      if ( isset($PI["id"]) && trim($PI["id"]) != "" )  return true;
    }
    return false;
  }

  private function isLimitation($Item, $Limit)
  {
    return
       ( (isset($Item["loanitems"][0]["limitation"])         && is_array($Item["loanitems"][0]["limitation"])
       && count($Item["loanitems"][0]["limitation"])         && in_array($Limit, $Item["loanitems"][0]["limitation"]) )
      || (isset($Item["presentationitems"][0]["limitation"]) && is_array($Item["presentationitems"][0]["limitation"])
       && count($Item["presentationitems"][0]["limitation"]) && in_array($Limit, $Item["presentationitems"][0]["limitation"]) )
      || (isset($Item["interloanitems"][0]["limitation"])    && is_array($Item["interloanitems"][0]["limitation"])
       && count($Item["interloanitems"][0]["limitation"])    && in_array($Limit, $Item["interloanitems"][0]["limitation"]) ) ) ? true : false;
  }

  private function setLog($Group, $Message)
  {
    if ( APPDEBUG["FKT"] ) $this->CS->debug("FKT",__METHOD__);

    $this->one["logs"][] = str_pad($Group, 10, " ") . ": " . $Message;
  }

  private function getArrReducedWildArr($RedArray, $MainArray)
  {
    foreach ( $MainArray as $MInd => $Str )
    {
      foreach ( $RedArray as $One )
      {
        if ( substr($One, -1) == "*" )
        {
          $Pos = strpos($One, "*");
          if ( substr($One, 0, $Pos) == substr($Str, 0, $Pos) ) unset($MainArray[$MInd]);
        }
        else
        {
          if ( $One == $Str )  unset($MainArray[$MInd]);
        }
      }
    }
    return $MainArray;
  }

  private function getMarcSubFieldArray($Field, $Subfield)
  {
    $Vals = array();
    if ( isset($this->one["marc"][$Field]) && is_array($this->one["marc"][$Field]) )
    {
      foreach ($this->one["marc"][$Field] as $Rec )
      {
        if ( array_key_exists($Subfield, $Rec) && is_array($Rec[$Subfield]) )
        {
          foreach ($Rec[$Subfield] as $Val)
          {
            if ( !in_array($Val, $Vals) )  $Vals[] = $Val;
          }
        }
      }
    }
    return $Vals;
  }

  private function getMarcSubFieldsArray($Field, $Subfields = array())
  {
    $Recs = array();
    if ( isset($this->one["marc"][$Field]) && is_array($this->one["marc"][$Field]) )
    {
      foreach ($this->one["marc"][$Field] as $Rec )
      {
        $Vals = array();
        foreach ($Rec as $Subf => $SubRec)
        {
          if ( in_array($Subf, $Subfields) )
          {
            if (is_array($SubRec) )
            {
              foreach ($SubRec as $SubV)
              {
                $Vals[$Subf][] = $SubV;
              }
            }
            else
            {
              $Vals[$Subf][] = $SubRec;
            }
          }
        }
        if ( count($Vals) ) $Recs[] = $Vals;
      }
    }
    return $Recs;
  }

  private function addArr2Arr(&$Arr1, $Arr2)
  {
    $Arr1 = array_unique(array_merge($Arr1, $Arr2));
  }

  private function getPage()
  {
    
    $Recs = $this->getMarcSubFieldsArray("M773", array("I1", "g"));
    foreach ( $Recs as $Rec )
    {
      if ( !isset($Rec["I1"][0]) || $Rec["I1"][0] != 1 || !isset($Rec["g"]) || !is_array($Rec["g"]) )  continue;

      foreach ($Rec["g"] as $G)
      {
        if ( substr($G, 0, 6) == "pages:" ) return substr($G,6);
      }
    }
    
    return "";
  }

  private function getVolume()
  {
    
    $Recs = $this->getMarcSubFieldsArray("M773", array("I1", "g"));
    foreach ( $Recs as $Rec )
    {
      if ( !isset($Rec["I1"][0]) || $Rec["I1"][0] != 1 || !isset($Rec["g"]) || !is_array($Rec["g"]) )  continue;

      $Tmp  = [];
      foreach ($Rec["g"] as $G)
      {
        if ( substr($G, 0, 7) == "volume:" ) $Tmp[] = substr($G,7);
        if ( substr($G, 0, 5) == "year:" )   $Tmp[] = "(" . substr($G,5) . ")";
        if ( substr($G, 0, 7) == "number:" ) $Tmp[] = ", " . substr($G,7);
      }
      return implode(" ", $Tmp);
    }
    
    return "";
  }

  private function getMainData()
  {
    
    $More   = array();
    $More[] = "ID:\\t"             . $this->id;
    $More[] = "Titel:\\t"          . $this->CS->getMarcText($this->id, "bib_01");
    $More[] = "Veröffentlicht:\\t" . $this->CS->getMarcText($this->id, "bib_12", 1) . ", "
                                   . $this->CS->getMarcText($this->id, "bib_12", 2) . ", "
                                   . $this->CS->getMarcText($this->id, "bib_12", 3);
    $More[] = "Autoren:\\t"        . $this->CS->getMarcText($this->id, "bib_04");
    $More[] = "Beteiligte:\\t"     . $this->CS->getMarcText($this->id, "bib_05");
    return implode("\\n", $More);
  }

  
  
  
  private function repairLink($Link, $Item = [], $Encode = true)
  {
    $Kod = function ($Wert) use ($Encode) { return $Encode ? urlencode($Wert) : $Wert; };
    
     if (  stripos($Link, "§")      !== false )     $Link = str_ireplace("§",      "#",       $Link);
     if (  stripos($Link, "<id>")   !== false )     $Link = str_ireplace("<id>",   $this->id, $Link);
     if (  stripos($Link, "<isbn>") !== false )
     {
       $Fill = isset($this->CS->works["marc"][$this->id]["marc"]["M020"][0]["a"][0])
               ? $this->CS->works["marc"][$this->id]["marc"]["M020"][0]["a"][0]
               : "";
       if ( $Fill )                                $Link = str_ireplace("<isbn>", $Fill,     $Link);
     }
     
     
     
     
     
     
     
     
     if (  stripos($Link, "<M980.b>") !== false )
     {
       $Fill = isset($Item["b"][0]) && trim($Item["b"][0]) && $Item["b"][0] != "--%%--" ? $Item["b"][0] : "";
       if ( $Fill )                                $Link = str_ireplace("<M980.b>", $Kod($Fill), $Link);
     }
     if (  stripos($Link, "<M980.d>") !== false )
     {
       $Fill = isset($Item["d"][0]) && trim($Item["d"][0]) && $Item["d"][0] != "--%%--" ? $Item["d"][0] : "";
       
       
       
       $Switch = $this->CS->confPairs("item", "locationmap");
       if ( array_key_exists($Fill, $Switch) )     $Fill = $Switch[$Fill];
       if ( $Fill )                                $Link = str_ireplace("<M980.d>", $Kod($Fill), $Link);
     }
     
     
     
     
     
     
     
     if (  stripos($Link, "<signatur>") !== false || stripos($Link, "<signature>") !== false )
     {
       $Fill = "";
       if ( isset($Item["d"]) && is_array($Item["d"]) )
       {
         
         
         $Kopie  = $Item;
         $Return = $this->modifySignatureLocal($Kopie);
         if ( isset($Return["status"]) && $Return["status"] && isset($Return["values"][0]) )  $Fill = (string) $Return["values"][0];
         if ( $Fill === "" && isset($Item["d"][0]) && $Item["d"][0] !== "--%%--" )             $Fill = (string) $Item["d"][0];
       }
       if ( $Fill !== "" )                         $Link = str_ireplace(array("<signatur>", "<signature>"), $Kod(trim($Fill)), $Link);
     }
     if (  stripos($Link, "<M980.f>") !== false )
     {
       $Fill = isset($Item["f"][0]) && trim($Item["f"][0]) && $Item["f"][0] != "--%%--" ? $Item["f"][0] : "";
       if ( $Fill )                                $Link = str_ireplace("<M980.f>", $Kod($Fill), $Link);
     }
     return $Link;
  }

  private function replacekeywords($Str)
  {
     return str_ireplace(array("<point>", "<comma>"), array(".", ","), $Str);
  }

  
  private function getActionElements($Case, $Item=array())
  {
    if ( APPDEBUG["FKT"] ) $this->CS->debug("FKT",__METHOD__);

    if ( !isset($Case["detailaction"]) || !$Case["detailaction"] )  return array();

    $More = explode(".", $Case["detailaction"]);
    $DA   = array_shift($More);
    $DA  .= "." . array_shift($More);
    $More = implode(".", $More);
    $More = explode(",", $More);
    $More = array_map(array($this,"replacekeywords"), $More);

    if ( substr($DA,0,2) == "l." )
    {
      $L = substr($DA,2);
      
      

      if ( isset($this->CS->links[$L]) )
      {
        if ( $this->CS->lang == "g" && ( !isset($this->CS->links[$L]["german"])  || !isset($this->CS->links[$L]["germanlink"])) )  return [];
        if ( $this->CS->lang == "e" && ( !isset($this->CS->links[$L]["english"]) || !isset($this->CS->links[$L]["englishlink"])) ) return [];

        return  array(
                                  "type"    => "link",
                                  
                                  
                                  
                                  
                                  "link"    => array("url"    => $this->repairLink($this->CS->lang == "g" ? $this->CS->links[$L]["germanlink"] : $this->CS->links[$L]["englishlink"], $Item),
                                                    "label"   => $this->CS->lang == "g" ? $this->CS->links[$L]["german"] : $this->CS->links[$L]["english"],
                                                     "target" => "_blank"),
                                  "icon"    => "mdi-link",
                                  "button"  => $Case["detailbutton"]
                        );
      }
    }

    switch ($DA)
    {
      
      case  "p.order":
      {
        return array(
                                    "type"    => "request",
                                    "request" => array("id"  => (isset($Item["id"])) ? $Item["id"] : "",
                                                       "type" => "order"),
                                    "icon"    => "mdi-arrow-right-bold-box-outline",
                                    "button"  => $Case["detailbutton"]
                          );
        break;
      }
      case  "p.ordergroups":
      {
        

        
        if ( count($More) != 2 ) break;

        return array(
                                    "type"      => "request",
                                    "request"   => array("id"   => (isset($Item["id"])) ? $Item["id"] : "",
                                                         "type" => "order"),
                                    "icon"      => "mdi-arrow-right-bold-box-outline",
                                    "button"    => $Case["detailbutton"],
                                    "deskgroup" => $More[0],
                                    "usergroup" => $More[1]
                          );
        break;
      }
      case  "p.reserve":
      {
        return  array(
                                    "type"    => "request",
                                    "request" => array("id"   => (isset($Item["id"])) ? $Item["id"] : "",
                                                       "type" => "reserve"),
                                    "icon"    => "mdi-arrow-right-bold-box-outline",
                                    "button"  => $Case["detailbutton"]
                          );
        break;
      }
      case  "p.reservegroups":
      {
        

        
        if ( count($More) != 2 ) break;

        return array(
                                    "type"      => "request",
                                    "request"   => array("id"   => (isset($Item["id"])) ? $Item["id"] : "",
                                                         "type" => "reserve"),
                                    "icon"      => "mdi-arrow-right-bold-box-outline",
                                    "button"    => $Case["detailbutton"],
                                    "deskgroup" => $More[0],
                                    "usergroup" => $More[1]
                          );
        break;
      }

      
      case  "m.openaccess":
      {
        
        if ( !isset($Item["link"]) || !$Item["link"] )  break;
        return  array(
                                  "type"    => "link",
                                  "link"    => array("url"    => $Item["link"],
                                                    "label"   => isset($Item["text"]) ? $Item["text"] : $this->CS->getHost($Item["link"]),
                                                     "target" => "_blank"),
                                  "icon"    => "mdi-link",
                                  "button"  => $Case["detailbutton"]
                        );

        break;
      }
      case  "m.remote":
      {
        if ( !isset($Item["link"]) || !$Item["link"] )  break;
        return  array(
                                  "type"    => "link",
                                  "link"    => array("url"    => $Item["link"],
                                                    "label"   => isset($Item["text"]) ? $Item["text"] : $this->CS->getHost($Item["link"]),
                                                    "target" => "_blank"),
                                  "icon"    => "mdi-link",
                                  "button"  => $Case["detailbutton"]
                        );

        break;
      }

      
      case  "o.mailelse":
      {
        
        
        
        
        
        if ( !isset($this->CS->configs["mail.".$this->mode]["else"]) || !$this->CS->configs["mail.".$this->mode]["else"] ) break;
        return  array(
                         "type"    => "dialog",
                         "dialog"  => "else",
                         "icon"    => "mdi-email-outline",
                         "button"  => $Case["detailbutton"]
                       );

        break;
      }
      case  "o.mailnotify":
      {
        
        
        
        
        
        if ( !isset($this->CS->configs["mail.".$this->mode]["notify"]) || !$this->CS->configs["mail.".$this->mode]["notify"] ) break;
        return  array(
                         "type"    => "dialog",
                         "dialog"  => "notify",
                         "icon"    => "mdi-email-outline",
                         "button"  => $Case["detailbutton"]
                       );

        break;
      }
      case  "o.mailorder":
      {
        
        
        
        
        
        if ( !isset($this->CS->configs["mail.".$this->mode]["order"]) || !$this->CS->configs["mail.".$this->mode]["order"] ) break;
        return  array(
                         "type"    => "dialog",
                         "dialog"  => "order",
                         "icon"    => "mdi-email-outline",
                         "button"  => $Case["detailbutton"]
                       );

        break;
      }
      case  "o.mailreserve":
      {
        
        
        
        
        
        if ( !isset($this->CS->configs["mail.".$this->mode]["reserve"]) || !$this->CS->configs["mail.".$this->mode]["reserve"] ) break;
        return  array(
                         "type"    => "dialog",
                         "dialog"  => "reserve",
                         "icon"    => "mdi-email-outline",
                         "button"  => $Case["detailbutton"]
                       );

        break;
      }
      case  "o.mailsuggest":
      {
        
        
        
        
        
        if ( !isset($this->CS->configs["mail.".$this->mode]["suggest"]) || !$this->CS->configs["mail.".$this->mode]["suggest"] ) break;
        return  array(
                         "type"    => "dialog",
                         "dialog"  => "suggest",
                         "icon"    => "mdi-email-outline",
                         "button"  => $Case["detailbutton"]
                       );

        break;
      }

      
      case "o.clientmail":
      {
        
        

        
        if ( count($More) < 3 || count($More) > 4  ) break;

        $MailTo = isset($More[0]) ? $More[0] : "";
        if ( $MailTo )  $MailTo = $this->CS->getText($MailTo);

        $MailSubject = isset($More[1]) ? $More[1] : "";
        if ( $MailSubject )  $MailSubject = $this->CS->getText($MailSubject);

        $MessBef = isset($More[2]) ? $More[2] : "";
        if ( $MessBef )
        {
          $MessBef = $this->CS->getText($MessBef);
          if (  stripos($MessBef, "<M001>") !== false )
          {
            $MessBef = str_ireplace("<M001>", $this->id, $MessBef);
          }
          if (  stripos($MessBef, "<M981.r>") !== false )
          {
            $Fill = "";
            $MessBef = str_ireplace("<M981.r>", $Fill, $MessBef);
          }

        }

        $MessAft = isset($More[3]) ? $More[3] : "";
        if ( $MessAft )
        {
          $MessAft = $this->CS->getText($MessAft);
          if (  stripos($MessAft, "<M001>") !== false )
          {
            $MessAft = str_ireplace("<M001>", $this->id, $MessAft);
          }
          if (  stripos($MessAft, "<M981.r>") !== false )
          {
            $Fill = $this->CS->getMarcFieldFirst($this->id, "M981", "r");
            $MessAft = str_ireplace("<M981.r>", $Fill, $MessAft);
          }
        }

        
        
        
        
        
        
        
        
        
        
        
        
        $MailLoc = isset($Item["f"][0]) && trim($Item["f"][0]) && $Item["f"][0] != "--%%--" ? $Item["f"][0] : "";
        $MailSig = isset($Item["d"][0]) && trim($Item["d"][0]) && $Item["d"][0] != "--%%--" ? $Item["d"][0] : "";
        $MailVon = array("<M980.f>", "<M980.d>", "<signatur>");
        $MailNach = array($MailLoc, $MailSig, $MailSig);
        if ( $MessBef !== "" )  $MessBef = str_ireplace($MailVon, $MailNach, $MessBef);
        if ( $MessAft !== "" )  $MessAft = str_ireplace($MailVon, $MailNach, $MessAft);

        return  array(
                        "type"    => "clientmail",
                        "mail"    => array("to"      => $MailTo,
                                           "subject" => $MailSubject,
                                           "messbef" => $MessBef,
                                           "more"    => $this->getMainData($Item),
                                           "messaft" => $MessAft),
                        "icon"    => $Case["itemicon"],
                        "button"  => $Case["detailbutton"]
                       );

        
        break;
      }
      case "o.delivery":
      {
        
        
        
        
        return  array(
                      "type"    => "dialog",
                      "dialog"  => $this->CS->conf("other", "delivery.dialog", "delivery"),
                      "icon"    => $Case["itemicon"],
                      "button"  => $Case["detailbutton"]
                     );
        break;
      }
      case "o.dialog":
      {
        return  array(
                      "type"    => "dialog",
                      "dialog"  => isset($More[0]) ? $More[0] : "",
                      "icon"    => $Case["itemicon"],
                      "button"  => $Case["detailbutton"]
                     );
        break;
       }
      case "o.externlibrary":
      {
        $Col = strtolower($this->CS->getCollectionFromPPN($this->id));
        if ( isset($Col, $this->CS->configs["link"]["interlibrary.". $Col]) )
        {
          $FLPPN = ( preg_match("/\d{4,}/", $this->id, $firstdigit) )
                   ? substr($this->id, strpos($this->id, $firstdigit[0]))
                   : $this->id;

          return  array(
                          "type"    => "link",
                          "link"    => array("url"   => $this->CS->configs["link"]["externlibrary"] . "/(DE-627)" . $FLPPN,
                                            "target" => "_blank"),
                          "icon"    => $Case["itemicon"],
                          "button"  => $Case["detailbutton"]
                         );

        }
        break;
      }
      case "o.inactive":
      {
        return  array(
                      "type"    => "inactive",
                      "icon"    => $Case["itemicon"],
                      "button"  => $Case["detailbutton"]
                     );
        break;
      }
      case "o.interlibrary":
      {
        $Col = strtolower($this->CS->getCollectionFromPPN($this->id));
        if ( isset($Col, $this->CS->configs["link"]["interlibrary.". $Col]) )
        {
          $FLPPN = ( preg_match("/\d{4,}/", $this->id, $firstdigit) )
                   ? substr($this->id, strpos($this->id, $firstdigit[0]))
                   : $this->id;
          $Lang  = ( isset($this->CS->lang) && $this->CS->lang == "e" ) ? "EN" : "DU";

          return  array(
                          "type"    => "link",
                          "link"    => array("url"   => $this->CS->configs["link"]["interlibrary.". $Col]
                                                        . "/PPNSET?PPN=" . $FLPPN . "&LNG=" . $Lang,
                                            "target" => "_blank"),
                          "icon"    => $Case["itemicon"],
                          "button"  => $Case["detailbutton"]
                         );
        }
        break;
      }
      case  "m.masterlink":
      {
        if ( !isset($this->one["masterlink"]) || !trim($this->one["masterlink"]) )  break;
        return  array(
                                  "type"    => "link",
                                  "link"    => array("url"    => $this->one["masterlink"],
                                                    "label"   => isset($Item["text"]) ? $Item["text"] : $this->CS->getHost($Item["link"]),
                                                     "target" => "_blank"),
                                  "icon"    => "mdi-link",
                                  "button"  => $Case["detailbutton"]
                        );
        break;
      }
      case  "o.opaclink":
      {
        
        
        
        
        $DB       = $this->ilnDB(isset($Item["2"]["0"]) ? $Item["2"]["0"] : "");

        $OPACLINK = "";
        if ( MODE == "production"
          && isset($this->CS->bms[$this->CS->bmsid]["options"]["prodopaclink"])
          && $this->CS->bms[$this->CS->bmsid]["options"]["prodopaclink"] )
        {
          $OPACLINK = $this->CS->bms[$this->CS->bmsid]["options"]["prodopaclink"];
        }
        elseif ( MODE != "production"
          && isset($this->CS->bms[$this->CS->bmsid]["options"]["testopaclink"])
          && $this->CS->bms[$this->CS->bmsid]["options"]["testopaclink"] )
        {
          $OPACLINK = $this->CS->bms[$this->CS->bmsid]["options"]["testopaclink"];
        }
        if ( $DB && $OPACLINK )
        {
          return  array(
                                    "type"    => "link",
                                    "link"    => array("url"    => $OPACLINK . "/DB=" . $DB . "/XMLPRS=N/PPN?PPN=" . $this->id,
                                                       "target" => "_blank"),
                                    "icon"    => "mdi-link",
                                    "button"  => $Case["detailbutton"]
                        );
        }
        else
        {
          $Col = $this->CS->getCollectionFromPPN($this->id);
          if ( in_array($Col, array("KXP", "OLC", "SWB", "OEVK")) )
          {
            $Links = array("KXP"  => "https://kxp.k10plus.de/DB=2.1",
                           "OLC"  => "https://olc.k10plus.de",
                           "SWB"  => "https://swb.bsz-bw.de",
                           "OEVK" => "https://oevk.k10plus.de");
            $ID    = substr($this->id, $Col == "KXP" ? 0 : strlen($Col));
            return  array(
                                    "type"    => "link",
                                    "link"    => array("url"    => $Links[$Col] . "/PPNSET?PPN=" . $ID,
                                                       "target" => "_blank"),
                                    "icon"    => "mdi-link",
                                    "button"  => $Case["detailbutton"]
                          );
          }
        }
        break;
      }
      case "o.related":
      {
          return  array(
                                    "type"    => "change",
                                    "change"  => "related",
                                    "icon"    => $Case["itemicon"],
                                    "button"  => $Case["itembutton"]
                          );
          break;
      }
      case "o.search":
      {
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        $Ausdruck = trim(implode(",", $More));
        if ( $Ausdruck !== "" )
        {
          $Query = $this->repairLink($Ausdruck, $Item, false);
          if ( strpos($Query, "<") !== false )  break;
        }
        else
        {
          if ( !$this->pid ) break;
          $Query = "id:" . $this->pid;
        }

        return  array(
                                  "type"    => "search",
                                  "search"  => array("query" => $Query,
                                                     "type"  => "lukida"),
                                  "icon"    => $Case["itemicon"],
                                  "button"  => $Case["itembutton"]
                        );
        break;
      }
      case "o.skip":
      {
        return  array(
                      "type"    => "skip"
                     );
        break;
      }

      
      case "o.iiif":
      {
        if ( !isset($Item["link"]) || !$Item["link"] )  break;
        return  [
                 "type"    => "link",
                 "link"    => array("url"    => $Item["link"] . "/fragment/content_search={query}",
                                   "label"   => isset($Item["text"]) ? $Item["text"] : $this->CS->getHost($Item["link"]),
                                    "target" => "_blank"),
                 "icon"    => "mdi-link",
                 "button"  => $Case["detailbutton"]
                ];
        break;
      }
      case  "o.flensburgzhb":
      {
        if ( isset($More[0]) && $More[0] == "openaccess" )
        {
          if ( !isset($Item["link"]) || !$Item["link"] || !isset($Item["y"][0]) || !in_array(substr($Item["y"][0],1,1),array("1", "2")) )  break;

          $ZHBLNK = substr($Item["y"][0],1,1) == "1" ? "uni" : "hs";

          return  array(
                                  "type"    => "link",
                                  "link"    => array("url"    => "https://" . $ZHBLNK . "-flensburg.idm.oclc.org/login?url=" . $Item["link"],
                                                    "label"   => isset($Item["text"]) ? $Item["text"] : $this->CS->getHost($Item["link"]),
                                                     "target" => "_blank"),
                                  "icon"    => "mdi-link",
                                  "button"  => $Case["detailbutton"]
                        );
        }
        break;
      }
      case "o.goettingensub":
      {
        if ( isset($More[0]) && $More[0] == "search" )
        {
          
          

          if ( !isset($this->one["rootid"]) ) break;
          return array(
                                  "type"    => "search",
                                  "search"  => array("query" => "id:" . $this->one["rootid"],
                                                     "type"  => "lukida"),
                                  "icon"    => $Case["itemicon"],
                                  "button"  => $Case["itembutton"]
                      );
          break;
        }
      }
      case  "o.neubrandenburghs":
      {
        if ( isset($More[0]) && $More[0] == "aquisitionrequest" )
        {
          if ( !isset($Item["link"]) || !$Item["link"] )  break;
          return  array(
                                  "type"    => "link",
                                  "link"    => array("url"    => $Item["link"] . "?getppn=" . $this->id,
                                                    "label"   => "",
                                                     "target" => "_blank"),
                                  "icon"    => "mdi-link",
                                  "button"  => $Case["detailbutton"]
                        );
        }
        break;
      }
    }
    return array();
  }

  private function getDisplayElements($Disps, $Item = array())
  {
    if ( APPDEBUG["FKT"] ) $this->CS->debug("FKT",__METHOD__);

    $TxtCnt   = 1;
    $Texts    = array("g" => array(), "e" => array());
    

    foreach ($Disps as $Disp)
    {
      if ( substr($Disp,0,2) == "f." )
      {
        $Tmp = $this->getFOLIOElement($Item, substr($Disp,2));
        if ( !isset($Tmp["g"]) || !isset($Tmp["e"]) ) continue;
        $Texts["g"] = [...$Texts["g"],
                       ...$Tmp["g"]];
        $Texts["e"] = [...$Texts["e"],
                       ...$Tmp["e"]];
      }
      elseif ( substr($Disp,0,2) == "h." )
      {
        $Tmp = $this->CS->getTextBothLang(substr($Disp,2));
        if ( !isset($Tmp["german"]) || !isset($Tmp["english"]) ) continue;
        $Texts["g"][] = array("type" => "head",
                              "text" => $Tmp["german"]);
        $Texts["e"][] = array("type" => "head",
                              "text" => $Tmp["english"]);
      }
      elseif ( substr($Disp,0,2) == "m." )
      {
        $Tmp = $this->getMARCElement($Item, substr($Disp,2));
        if ( !isset($Tmp["g"]) || !isset($Tmp["e"]) ) continue;
        $Texts["g"] = [...$Texts["g"],
                       ...$Tmp["g"]];
        $Texts["e"] = [...$Texts["e"],
                       ...$Tmp["e"]];
      }
      elseif ( substr($Disp,0,2) == "o." )
      {
        $Tmp = $this->getOtherElement($Item, substr($Disp,2));
        if ( !isset($Tmp["g"]) || !isset($Tmp["e"]) ) continue;
        $Texts["g"] = [...$Texts["g"],
                       ...$Tmp["g"]];
        $Texts["e"] = [...$Texts["e"],
                       ...$Tmp["e"]];
      }
      elseif ( substr($Disp,0,2) == "p." )
      {
        $Tmp = $this->getPAIAElement($Item, substr($Disp,2));
        if ( !isset($Tmp["g"]) || !isset($Tmp["e"]) ) continue;
        $Texts["g"] = [...$Texts["g"],
                       ...$Tmp["g"]];
        $Texts["e"] = [...$Texts["e"],
                       ...$Tmp["e"]];
      }

      
      elseif ( substr($Disp,0,2) == "l." )
      {
        
        $Tmp = (isset($this->CS->links[substr($Disp,2)])) ? $this->CS->links[substr($Disp,2)] : array();
        if ( !isset($Tmp["german"])     || !isset($Tmp["english"]) )     continue;
        if ( !isset($Tmp["germanlink"]) || !isset($Tmp["englishlink"]) ) continue;

        $Tmp["germanlink"]  = $this->repairLink($Tmp["germanlink"]);
        $Tmp["englishlink"] = $this->repairLink($Tmp["englishlink"]);

        if (  stripos($Tmp["germanlink"], "<M980.b>") !== false )
        {
          $Fill = isset($Item["b"][0]) && trim($Item["b"][0]) && $Item["b"][0] != "--%%--" ? $Item["b"][0] : "";
          $Tmp["germanlink"] = str_ireplace("<M980.b>", urlencode($Fill), $Tmp["germanlink"]);
        }
        if (  stripos($Tmp["englishlink"], "<M980.b>") !== false )
        {
          $Fill = isset($Item["b"][0]) && trim($Item["b"][0]) && $Item["b"][0] != "--%%--" ? $Item["b"][0] : "";
          $Tmp["englishlink"] = str_ireplace("<M980.b>", urlencode($Fill), $Tmp["englishlink"]);
        }

        if (  stripos($Tmp["germanlink"], "<M980.d>") !== false )
        {
          $Fill = isset($Item["d"][0]) && trim($Item["d"][0]) && $Item["d"][0] != "--%%--" ? $Item["d"][0] : "";
          $Tmp["germanlink"] = str_ireplace("<M980.d>", urlencode($Fill), $Tmp["germanlink"]);
        }
        if (  stripos($Tmp["englishlink"], "<M980.d>") !== false )
        {
          $Fill = isset($Item["d"][0]) && trim($Item["d"][0]) && $Item["d"][0] != "--%%--" ? $Item["d"][0] : "";
          $Tmp["englishlink"] = str_ireplace("<M980.d>", urlencode($Fill), $Tmp["englishlink"]);
        }

        if (  stripos($Tmp["germanlink"], "<M980.f>") !== false )
        {
          $Fill = isset($Item["f"][0]) && trim($Item["f"][0]) && $Item["f"][0] != "--%%--" ? $Item["f"][0] : "";
          $Tmp["germanlink"] = str_ireplace("<M980.f>", urlencode($Fill), $Tmp["germanlink"]);
        }
        if (  stripos($Tmp["englishlink"], "<M980.f>") !== false )
        {
          $Fill = isset($Item["f"][0]) && trim($Item["f"][0]) && $Item["f"][0] != "--%%--" ? $Item["f"][0] : "";
          $Tmp["englishlink"] = str_ireplace("<M980.f>", urlencode($Fill), $Tmp["englishlink"]);
        }

        $Texts["g"][] = array("type" => "link",
                              "text" => $Tmp["german"],
                              "link" => $Tmp["germanlink"]);
        $Texts["e"][] = array("type" => "link",
                              "text" => $Tmp["english"],
                              "link" => $Tmp["englishlink"]);
      }
      elseif ( substr($Disp,0,2) == "t." )
      {
        $Tmp = $this->CS->getTextBothLang(substr($Disp,2));
        if ( !isset($Tmp["german"]) || !isset($Tmp["english"]) ) continue;
        $Texts["g"][] = array("type" => "text",
                              "text" => $Tmp["german"]);
        $Texts["e"][] = array("type" => "text",
                              "text" => $Tmp["english"]);
      }
      else
      {
        $Texts[] = $Disp;
      }
    }
     
    return $Texts;
  }

  private function getMARCElement($Item, $Element)
  {
    if ( APPDEBUG["FKT"] ) $this->CS->debug("FKT",__METHOD__);

    $Texts = array("g" => array(), "e" => array());

    $Tmp     = explode(".", $Element);
    $Element = array_shift($Tmp);
    $Tmp     = implode(".", $Tmp);
    $More    = explode(",", $Tmp);

    switch ($Element)
    {
      
      case "hamburghsu":
      {
        
        
        
        
        
        if ( isset($More[0]) && $More[0] == "handapparat" )
        {
          $Nr = ( isset($Item["f"][0]) && trim($Item["f"][0]) !== "" && trim($Item["f"][0]) != "--%%--" ) ? trim($Item["f"][0]) : "";
          if ( $Nr === "" )  break;

          $T = $this->CS->getTextBothLang("handapparat");
          $G = ( isset($T["german"])  && trim($T["german"])  ) ? trim($T["german"])  : "Handapparat";
          $E = ( isset($T["english"]) && trim($T["english"]) ) ? trim($T["english"]) : "Reserve collection";

          $L  = isset($this->CS->links["hand"]) ? $this->CS->links["hand"] : array();
          $LG = isset($L["germanlink"])  ? trim($this->repairLink($L["germanlink"]))  : "";
          $LE = isset($L["englishlink"]) ? trim($this->repairLink($L["englishlink"])) : "";
          if ( $LE === "" )  $LE = $LG;

          $Texts["g"][] = $LG !== "" ? array("type" => "link", "text" => $G . " " . $Nr, "link" => $LG)
                                     : array("type" => "text", "text" => $G . " " . $Nr);
          $Texts["e"][] = $LE !== "" ? array("type" => "link", "text" => $E . " " . $Nr, "link" => $LE)
                                     : array("type" => "text", "text" => $E . " " . $Nr);
        }
        break;
      }

      
      case "articlestock":
      {
        
        
        
        $Val =  isset($this->one["marc"]["M773"][0]["g"][0]) ? $this->one["marc"]["M773"][0]["g"][0] : "";

        if ( $Val )
        {
          $this->addArr2Arr($this->stock, array($Val));
          $T   = $this->CS->getTextBothLang("items.stock");
          $Texts["g"][] = array("type" => "text",
                                "text" => $T["german"]  . ": " . $Val);
          $Texts["e"][] = array("type" => "text",
                                "text" => $T["english"]  . ": " . $Val);
        }
        break;
      }

      
      case "comment":
      {
        
        $Black = explode("|", $this->CS->configs["item"]["commentblacklist"]);
        
        $Vals  = array();
        if ( isset($Item["k"]) && is_array($Item["k"]) )
        {
          foreach ($Item["k"] as $Key => $Val)
          {
           if (in_array($Val, $Black)) continue;
           
           $Vals[] = $Val;
          }
        }

        if ( isset($Item["text"]) && !in_array($Item["text"], $Black) )
        {
          $Vals[] = $Item["text"];
        }

        if ( isset($Item["comment"]) && is_array($Item["comment"]) )
        {
          $Vals = [...$Vals,
                   ...array_diff($Item["comment"], $Black)];
        }

        if ( is_array($Vals) && count($Vals) )
        {
          $this->addArr2Arr($this->comment, $Vals);

          $Vals = array_filter(array_values(array_unique($Vals)));

          foreach ( $Vals as $Val )
          {
            $Texts["g"][] = array("type" => "text",
                                  "text" => strip_tags($Val));
            $Texts["e"][] = array("type" => "text",
                                  "text" => strip_tags($Val));
          }
        }
        break;
      }

      
      case "commentstrict":
      {
        

        $Gesehen = array();

        if ( isset($this->one["marc"]["M980"]) && is_array($this->one["marc"]["M980"]) && count($this->one["marc"]["M980"])
          && isset($Item["iln"]) && trim($Item["iln"]) && isset($Item["enr"]) && trim($Item["enr"]) )
        foreach ( $this->one["marc"]["M980"] as $Rec )
        {
          
          
          
          
          
          
          if ( isset($Rec["2"][0]) && $Rec["2"][0] == $Item["iln"]
            && ( (isset($Rec["1"][0]) && $Rec["1"][0] == $Item["enr"]) || $Item["enr"] == "00" )
            && isset($Rec["k"]) && is_array($Rec["k"]) && count($Rec["k"]) )
          {
            foreach ($Rec["k"] as $Val )
            {
              if ( !trim($Val) )  continue;
              
              
              
              
              if ( in_array(strip_tags($Val), $Gesehen) )  continue;
              $Gesehen[] = strip_tags($Val);
              $Texts["g"][] = array("type" => "text",
                                    "text" => strip_tags($Val));
              $Texts["e"][] = array("type" => "text",
                                    "text" => strip_tags($Val));
            }
          }
        }

        if ( isset($Item["text"]) && trim($Item["text"]) )
        {
          $Texts["g"][] = array("type" => "text",
                                "text" => $Item["text"]);
           $Texts["e"][] = array("type" => "text",
                                 "text" => $Item["text"]);
        }
        break;
      }

      
      case "count":
      {
        $Val = isset($Item["a"][0]) ? intval($Item["a"][0]) : "";

        if ( $Val && $Val >= 1 )
        {
          $T   = $this->CS->getTextBothLang("items.count" . ($Val >= 2 ? "s": ""));
          $Texts["g"][] = array("type" => "text",
                                "text" => $Val . " " . $T["german"]);
          $Texts["e"][] = array("type" => "text",
                                "text" => $Val . " " . $T["english"]);
        }
        break;
      }

      
      case "direct":
      {
        if ( is_array($More) && count($More) >= 4 )
        {
          $Mama = "";
          $Kind = "";
          
          if ( array_key_exists($this->id,$this->CS->hierarchy["childs"]) ) {
            $Kind = $this->id;
            $Mama = $this->CS->hierarchy["childs"][$this->id];
          } elseif ( array_key_exists($this->id,$this->CS->hierarchy["parents"]) ) {
            $Kind = $this->CS->hierarchy["parents"][$this->id][0];
            $Mama = $this->id;
          } else {
            $Kind = $this->id;
            $Mama = "";
          }

          

          $Tmp = [];
          $Type = substr(strtolower($More[0]),0,1) == "m" ? "M" : "P";

          if ( trim($Kind) && $Type == "M" && isset($this->CS->works["marc"]) && is_array($this->CS->works["marc"]) && count($this->CS->works["marc"]) )
          {
            foreach ($this->CS->works["marc"] as $OneM)
            {
              if ( isset($OneM["marc"]) && is_array($OneM["marc"]) && count($OneM["marc"]) )
              {
                if ( isset($OneM["marc"]["M001"]) && $OneM["marc"]["M001"] == $Kind )
                {
                  
                  $Tmp = $OneM["marc"];
                  break;
                }
              }
            }
          }
          if ( trim($Mama) && $Type == "P" && isset($this->CS->works["add"]) && is_array($this->CS->works["add"]) && count($this->CS->works["add"]) )
          {
            foreach ($this->CS->works["add"] as $OneM)
            {
              if ( isset($OneM["marc"]) && is_array($OneM["marc"]) && count($OneM["marc"]) )
              {
                if ( isset($OneM["marc"]["PM001"]) && $OneM["marc"]["PM001"] == $Mama)
                {
                  
                  $Tmp = $OneM["marc"];
                  break;
                }
              }
            }
          }

          if ( is_array($Tmp) && count($Tmp) )
          {
            if ( isset($Tmp[strtoupper($More[0])][$More[1]][$More[2]][$More[3]]) )
            {
              $Text = $Tmp[strtoupper($More[0])][$More[1]][$More[2]][$More[3]];

              $Bef = array("german" => "", "english" => "");
              $Aft = array("german" => "", "english" => "");
              if ( isset($More[4]) )  $Bef = $this->CS->getTextBothLang($More[4]);
              if ( isset($More[5]) )  $Aft = $this->CS->getTextBothLang($More[5]);

              $Texts["g"][] = array("type" => "text",
                                    "text" => implode(" ", [$Bef["german"],
                                                            $Text,
                                                            $Aft["german"]]));
              $Texts["e"][] = array("type" => "text",
                                    "text" => implode(" ", [$Bef["english"],
                                                            $Text,
                                                            $Aft["english"]]));
            }
          }
        }
        break;
      }

      
      case "host":
      {
        
        if ( isset($Item["host"]) && trim($Item["host"]) )
        {
          $Texts["g"][] = array("type" => "text",
                                "text" => $Item["host"]);
          $Texts["e"][] = array("type" => "text",
                                "text" => $Item["host"]);
        }
        break;
      }

      
      case "location":
      {
        
        if ( isset($Item["f"][0]) && trim($Item["f"][0]) && $Item["f"][0] != "--%%--" )
        {
          $Texts["g"][] = array("type" => "text",
                                "text" => trim($Item["f"][0]));
          $Texts["e"][] = array("type" => "text",
                                "text" => trim($Item["f"][0]));
        }
        break;
      }

      
      case "stock":
      {
        $Vals      = array();
        if ( isset($Item["g"]) && is_array($Item["g"]) )
        {
          foreach ($Item["g"] as $Key => $Val)
          {
            $Vals[] = substr($Val,0,8) == "Bestand:" ? trim(substr($Val, 8)) : $Val;
          }
        }

        

        if ( isset($Item["stock"]) && is_array($Item["stock"]) )
        {
          $Vals = [...$Vals,
                   ...$Item["stock"]];
        }

        

        if ( is_array($Vals) && count($Vals) )
        {
          $this->addArr2Arr($this->stock, $Vals);

          $T   = $this->CS->getTextBothLang("items.stock");
          $Texts["g"][] = array("type" => "text",
                                "text" => $T["german"]  . ": " . implode(", ", $Vals));
           $Texts["e"][] = array("type" => "text",
                                 "text" => $T["english"]  . ": " . implode(", ", $Vals));
        }
        break;
      }

      
      case "stockstrict":
      {
        if ( isset($this->one["marc"]["M980"]) && is_array($this->one["marc"]["M980"]) && count($this->one["marc"]["M980"])
          && isset($Item["iln"]) && trim($Item["iln"]) && isset($Item["enr"]) && trim($Item["enr"]) )
        foreach ( $this->one["marc"]["M980"] as $Rec )
        {
          if ( isset($Rec["2"][0]) && $Rec["2"][0] == $Item["iln"]
            && ( (isset($Rec["1"][0]) && $Rec["1"][0] == $Item["enr"]) || $Item["enr"] == "00" )
            && isset($Rec["g"]) && is_array($Rec["g"]) && count($Rec["g"]) )
          {
            foreach ($Rec["g"] as $Val )
            {
              $Texts["g"][] = array("type" => "text",
                                    "text" => strip_tags($Val));
              $Texts["e"][] = array("type" => "text",
                                    "text" => strip_tags($Val));
            }
          }
        }
        break;
      }

      
      case "signature":
      {
        $Vals      = array();
        if ( isset($Item["d"]) && is_array($Item["d"]) )
        {
          $Return = $this->modifySignatureLocal($Item);
          if ( isset($Return["status"]) && $Return["status"] ) return $Return["values"];
        }
        break;
      }

      
      case "locationsignature":
      {
        
        
        
        
        
        $Vals      = array();
        $Loc       = isset($Item["f"][0]) && trim($Item["f"][0]) && $Item["f"][0] != "--%%--"
                     ? trim($Item["f"][0]) : "";
        if ( isset($Item["d"]) && is_array($Item["d"]) )
        {
          $Return = $this->modifySignatureLocal($Item, $Loc);
          if ( isset($Return["status"]) && $Return["status"] ) return $Return["values"];
        }
        break;
      }

      
      case "prov":
      {
        
        

        if ( isset($this->one["marc"]["M982"]) && is_array($this->one["marc"]["M982"]) )
        {
          $Vals      = array();
          foreach ( $this->one["marc"]["M982"] as $Rec )
          {
            
            if ( isset($Rec["1"][0]) && isset($Item["1"]) && $Rec["1"][0] == $Item["1"]
              && isset($Rec["2"][0]) && isset($Item["2"]) && $Rec["2"][0] == $Item["2"]
              && isset($Rec["a"][0]) && substr($Rec["a"][0],0,11) == "Provenienz:")  $Vals[] = $Rec["a"][0];
          }
          if ( count($Vals) )
          {
            $Texts["g"][] = array("type" => "text",
                                  "text" => implode(", ", $Vals));
             $Texts["e"][] = array("type" => "text",
                                   "text" => implode(", ", $Vals));
          }
        }
        break;
      }

      
      case "usagelink":
      {
        
        if ( isset($Item["usagelinks"]) && is_array($Item["usagelinks"]) && count($Item["usagelinks"]) )
        {
          foreach ( $Item["usagelinks"] as $UL )
          {
            if ( isset($UL["usagetext"]) && isset($UL["usagelink"]) )
            {
              $Texts["g"][] = array("type" => "link",
                                    "text" => $UL["usagetext"],
                                    "link" => $UL["usagelink"]);
              $Texts["e"][] = array("type" => "link",
                                    "text" => $UL["usagetext"],
                                    "link" => $UL["usagelink"]);
            }
            if ( isset($UL["usagetext"]) && !isset($UL["usagelink"]) )
            {
              $Texts["g"][] = array("type" => "text",
                                    "text" => $UL["usagetext"]);
              $Texts["e"][] = array("type" => "text",
                                    "text" => $UL["usagetext"]);
            }
            if ( !isset($UL["usagetext"]) && isset($UL["usagelink"]) )
            {
              $Texts["g"][] = array("type" => "link",
                                    "text" => "",
                                    "link" => $UL["usagelink"]);
              $Texts["e"][] = array("type" => "link",
                                    "text" => "",
                                    "link" => $Item["usagelink"]);
            }
          }
        }
        break;
      }
    }
    return $Texts;
  }

  private function getOtherElement($Item, $Element)
  {
    if ( APPDEBUG["FKT"] ) $this->CS->debug("FKT",__METHOD__);

    $Texts = array("g" => array(), "e" => array());

    $Tmp     = explode(".", $Element);
    $Element = array_shift($Tmp);
    $Tmp     = implode(".", $Tmp);
    $More    = explode(",", $Tmp);

    switch ($Element)
    {
      
      case "opaclink":
      {
        
        
        
        
        $DB       = $this->ilnDB(isset($Item["2"]["0"]) ? $Item["2"]["0"] : "");
        $OPACLINK = "";
        if ( MODE == "production" && isset($this->CS->bms[$this->CS->bmsid]["options"]["prodopaclink"]) && $this->CS->bms[$this->CS->bmsid]["options"]["prodopaclink"] )
        {
          $OPACLINK = $this->CS->bms[$this->CS->bmsid]["options"]["prodopaclink"];
        }
        elseif ( MODE != "production" && isset($this->CS->bms[$this->CS->bmsid]["options"]["testopaclink"]) && $this->CS->bms[$this->CS->bmsid]["options"]["testopaclink"] )
        {
          $OPACLINK = $this->CS->bms[$this->CS->bmsid]["options"]["testopaclink"];
        }
        if ( $DB && $OPACLINK )
        {
          $Texts["g"][] = array("type" => "head",
                                "text" => "");
          $Texts["g"][] = array("type" => "link",
                                "text" => "Zum OPAC ",
                                "link" => $OPACLINK . "/DB=" . $DB . "/XMLPRS=N/PPN?PPN=" . $this->id);
          $Texts["e"][] = array("type" => "head",
                                "text" => "");
          $Texts["e"][] = array("type" => "link",
                                "text" => "To the OPAC ",
                                "link" => $OPACLINK . "/DB=" . $DB . "/XMLPRS=N/PPN?PPN=" . $this->id);
        }
        return $Texts;
      }

      
      case "signatures":
      {
        return $this->getSignatureStockComment(true, false, false);
      }

      
      case "signaturestocks":
      {
        return $this->getSignatureStockComment(true, true, false);
      }

      
      case "signaturecomments":
      {
        return $this->getSignatureStockComment(true, false, true);
      }

      
      case "signaturestockcomments":
      {
        return $this->getSignatureStockComment(true, true, true);
      }

      
      case "stocks":
      {
        return $this->getSignatureStockComment(false, true, false);
      }

      
      case "stockcomments":
      {
        return $this->getSignatureStockComment(false, true, true);

      }

      
      case "comments":
      {
        return $this->getSignatureStockComment(false, false, true);
      }

      
      
      case "commentsnoparent":
      {
        return $this->getSignatureStockComment(false, false, true, false);
      }

      
      case "usagecode":
      {
        $Rec = $this->getMarcSubFieldArray("M980", "u");
        foreach ( $Rec as $UC )
        {
          if ( !trim($UC) )  continue;

          $L   = $this->CS->getTextBothLang("usagecode.".strtolower(trim($UC)));
          if ( !is_array($L) || !count($L) )  return array();

          if (substr($L["german"],0,4) == "http" )
          {
            $T   = $this->CS->getTextBothLang("campusnetwork");
            if ( !is_array($T) || !count($T) )  return array();
            $Texts["g"][] = array("type" => "text",
                                  "text" => $T["german"]);
            $Texts["e"][] = array("type" => "text",
                                  "text" => $T["english"]);

            $T   = $this->CS->getTextBothLang("usagecode");
            if ( !is_array($T) || !count($T) )  return array();
            $Texts["g"][] = array("type" => "link",
                                  "text" => $T["german"],
                                  "link" => $L["german"]);
            $Texts["e"][] = array("type" => "link",
                                  "text" => $T["english"],
                                  "link" => $L["english"]);
          }
          else
          {
            $Texts["g"][] = array("type" => "text",
                                  "text" => $L["german"]);
            $Texts["e"][] = array("type" => "text",
                                  "text" => $L["english"]);
          }
        }
        return $Texts;
      }

      
      case "vscout":
      {
        
        $VSLink  = ( isset($this->CS->configs["other"]["vscout.link"]) && $this->CS->configs["other"]["vscout.link"] )
                   ? $this->CS->configs["other"]["vscout.link"]
                   : "";
        $VSWhite = ( isset($this->CS->configs["other"]["vscout.whitelist"]) && $this->CS->configs["other"]["vscout.whitelist"] )
                   ? (array) json_decode($this->CS->configs["other"]["vscout.whitelist"], true)
                   : array();
        $VSBlack = ( isset($this->CS->configs["other"]["vscout.blacklist"]) && $this->CS->configs["other"]["vscout.blacklist"] )
                   ? (array) json_decode($this->CS->configs["other"]["vscout.blacklist"], true)
                   : array();


        
        
        
        

        $ItemILNLoc = ( isset($Item["2"][0]) && $Item["2"][0]
                     && isset($Item["f"][0]) && $Item["f"][0] ) ? trim($Item["2"][0]) . ":" . trim($Item["f"][0]) : "";
        $ItemSig    = ( isset($Item["d"][0]) && $Item["d"][0] ) ? trim($Item["d"][0])                             : "";


        $Found = false;

        
        foreach ($VSWhite as $I => $Block)
        {
          $FoundKeys = 0;
          foreach ($Block as $Key => $Val)
          {
            
            
            
            if ( isset($Item[$Key]) )
            {
              if ( substr($Val, -1) == "*" )
              {
                $Pos = strpos($Val, "*");
                $Mul = false;
                foreach ( $Item[$Key] as $IV )
                {
                  if ( $Mul ) continue;
                  if ( substr($Val, 0, $Pos) == substr($IV,0, $Pos) )
                  {
                    $FoundKeys++;
                    $Mul = true;
                  }
                }
              }
              else
              {
                if ( isset($Item[$Key]) && in_array($Val, $Item[$Key] ) ) $FoundKeys++;
              }
            }
          }
          if ( count($Block) == $FoundKeys )
          {
            $Found = true;
            break;
          }
        }
        

        if ( !$Found ) return $Texts;


        
        foreach ($VSBlack as $I => $Block)
        {
          $FoundKeys = 0;
          foreach ($Block as $Key => $Val)
          {
            if ( isset($Item[$Key]) )
            {
              if ( substr($Val, -1) == "*" )
              {
                $Pos = strpos($Val, "*");
                foreach ( $Item[$Key] as $IV )
                {
                  if ( substr($Val, 0, $Pos) == substr($IV,0, $Pos) ) $FoundKeys++;
                }
              }
              else
              {
                if ( isset($Item[$Key]) && in_array($Val, $Item[$Key] ) ) $FoundKeys++;
              }
            }
          }
          if ( count($Block) == $FoundKeys )
          {
            $Found = false;
            break;
          }
        }
        if ( !$Found ) return $Texts;


        

        if ( $VSLink && $ItemSig )
        {
          $Texts["g"][] = array("type" => "head",
                                "text" => "");
          $Texts["g"][] = array("type" => "link",
                                "text" => "Ihr Weg zum Medium ",
                                "link" => str_replace("{sig}", $ItemSig, $VSLink));
          $Texts["e"][] = array("type" => "head",
                                "text" => "");
          $Texts["e"][] = array("type" => "link",
                                "text" => "Your path to the medium ",
                                "link" => str_replace("{sig}", $ItemSig, $VSLink));
        }
        return $Texts;
      }

      case "rostockub":
      {
        if ( isset($More[0]) && $More[0] == "cdrom" )
        {
          if ( isset($Item["remoteitems"][0]["href"]) && trim($Item["remoteitems"][0]["href"]) )
          {
            $T   = $this->CS->getTextBothLang("cdrom");
            $Texts["g"][] = array("type" => "link",
                                  "text" => $T["german"],
                                  "link" => trim($Item["remoteitems"][0]["href"]));
            $Texts["e"][] = array("type" => "link",
                                  "text" => $T["english"],
                                  "link" => trim($Item["remoteitems"][0]["href"]));
          }
          
        }
      }
      return $Texts;
    }
  }

  private function getFOLIOElement($Item, $Text)
  {
    if ( APPDEBUG["FKT"] ) $this->CS->debug("FKT",__METHOD__);

    
    

    $Texts = array("g" => array(), "e" => array());

    switch ( $Text )
    {
      
      case 'status':
      {
        if ( isset($Item["status"]) && $Item["status"] )
        {
          $T   = $this->CS->getTextBothLang("foliostatus." . $Item["status"]);
          if ( !is_array($T) || !count($T) )  return array();
          $Texts["g"][] = array("type" => "text",
                                "text" => "FOLIO-Status: " . $T["german"]);
          $Texts["e"][] = array("type" => "text",
                                "text" => "FOLIO-State: ". $T["english"]);
        }
        break;
      }
    }
    return $Texts;
  }

  private function getPAIAElement($Item, $Text)
  {
    if ( APPDEBUG["FKT"] ) $this->CS->debug("FKT",__METHOD__);

    
    

    $Texts = array("g" => array(), "e" => array());

    switch ($Text)
    {
      
      case 'department':
      {
        if ( isset($Item["department"]) && $Item["department"] )
        {
          $Texts["g"][] = array("type" => "text",
                                "text" => strip_tags($Item["department"]));
          $Texts["e"][] = array("type" => "text",
                                "text" => strip_tags($Item["department"]));
        }
        break;
      }
      
      case 'label':
      {
        if ( isset($Item["label"]) && $Item["label"] )
        {
          $Texts["g"][] = array("type" => "text",
                                "text" => $Item["label"]);
          $Texts["e"][] = array("type" => "text",
                                "text" => $Item["label"]);
        }
        break;
      }
      
      case 'signature':
      {
        if ( isset($Item["label"]) )
        {
          $Div = $this->CS->conf("item", "signature.divider", ":");

          $Tmp = array_filter(explode($Div, $Item["label"]));

          if ( count($Tmp) > 1)  array_shift($Tmp);
          $Sig = implode($Div, $Tmp);
          $this->addArr2Arr($this->signature, array($Sig));

          $Array  = array("d" => array($Sig));
          $Return = $this->modifySignatureLocal($Array);
          if ( isset($Return["status"]) && $Return["status"] ) return $Return["values"];
        }
        break;
      }
      
      case 'storage':
      {
        if ( isset($Item["storage"]) && $Item["storage"] )
        {
          $Texts["g"][] = array("type" => "text",
                                "text" => $Item["storage"]);
          $Texts["e"][] = array("type" => "text",
                                "text" => $Item["storage"]);
        }
        break;
      }
      
      case 'storagelink':
      {
        if ( isset($Item["storage"]) && $Item["storage"]
          && isset($Item["storagelink"]) && $Item["storagelink"] )
        {
          
          $Texts["g"][] = array("type" => "link",
                                "text" => $Item["storage"] . " ",
                                "link" => $Item["storagelink"]);
          $Texts["e"][] = array("type" => "link",
                                "text" => $Item["storage"] . " ",
                                "link" => $Item["storagelink"]);
        }
        break;
      }
      
      case 'about':
      {
        if ( isset($Item["about"]) && $Item["about"] )
        {
          $Black = explode("|", $this->CS->configs["item"]["commentblacklist"]);
          if (in_array($Item["about"], $Black)) return array();
          $this->addArr2Arr($this->comment, array($Item["about"]));
          $Texts["g"][] = array("type" => "text",
                                "text" => $Item["about"]);
          $Texts["e"][] = array("type" => "text",
                                "text" => $Item["about"]);
        }
        break;
      }
      
      case 'aboutfirstline':
      {
        if ( isset($Item["about"]) && $Item["about"] )
        {
          $Tmp = preg_split("/\r\n|\n|\r/", $Item["about"]);
          $Tmp = $Tmp[0];

          $Black = explode("|", $this->CS->configs["item"]["commentblacklist"]);
          if (in_array($Tmp, $Black)) return array();
          $this->addArr2Arr($this->comment, array($Tmp));

          $Texts["g"][] = array("type" => "text",
                                "text" => $Tmp);
          $Texts["e"][] = array("type" => "text",
                                "text" => $Tmp);
        }
        break;
      }
      
      case 'queue':
      {
        $Tmp = array();
        if ( isset($Item["loanitems"][0]["queue"])         && $Item["loanitems"][0]["queue"]         != "-" ) $Tmp[] =  $Item["loanitems"][0]["queue"];
        if ( isset($Item["presentationitems"][0]["queue"]) && $Item["presentationitems"][0]["queue"] != "-" ) $Tmp[] =  $Item["presentationitems"][0]["queue"];
        
        if ( count($Tmp) )
        {
          $T   = $this->CS->getTextBothLang("items.queue");
          if ( !is_array($T) || !count($T) )  return array();
          $Texts["g"][] = array("type" => "text",
                                "text" => $T["german"] . ": " . $Tmp[0]);
          $Texts["e"][] = array("type" => "text",
                                "text" => $T["english"] . ": " . $Tmp[0]);
        }
        break;
      }
      
      case 'expected':
      {
        $Tmp = array();
        if ( isset($Item["loanitems"][0]["expected"])         && $Item["loanitems"][0]["expected"]         != "-" ) $Tmp[] =  $Item["loanitems"][0]["expected"];
        if ( isset($Item["presentationitems"][0]["expected"]) && $Item["presentationitems"][0]["expected"] != "-" ) $Tmp[] =  $Item["presentationitems"][0]["expected"];
        $Tmp = array_filter($Tmp);
        if ( count($Tmp) )
        {
          $T   = $this->CS->getTextBothLang("items.expected");
          if ( !is_array($T) || !count($T) )  return array();
          $Texts["g"][] = array("type" => "text",
                                "text" => $T["german"] . ": " . $Tmp[0]);
          $Texts["e"][] = array("type" => "text",
                                "text" => $T["english"] . ": " . $Tmp[0]);
        }
        break;
      }
      
      case "chronology":
      {
        if ( isset($Item["chronology"]) && $Item["chronology"] )
        {
          $T   = $this->CS->getTextBothLang("items.stock");
          if ( !is_array($T) || !count($T) )  return array();
          $this->addArr2Arr($this->stock, array($Item["chronology"]));

          $Texts["g"][] = array("type" => "text",
                                "text" => $T["german"] . ": " . $Item["chronology"]);
          $Texts["e"][] = array("type" => "text",
                                "text" => $T["english"] . ": " . $Item["chronology"]);
        }
        break;
      }
      
      case "epn":
      {
        if ( isset($Item["epn"]) && $Item["epn"] )
        {
          $T   = $this->CS->getTextBothLang("items.epn");
          if ( !is_array($T) || !count($T) )  return array();
          $Texts["g"][] = array("type" => "text",
                                "text" => $T["german"] . ": " . $Item["epn"]);
          $Texts["e"][] = array("type" => "text",
                                "text" => $T["english"] . ": " . $Item["epn"]);
        }
        break;
      }
    }
    return $Texts;
  }

  

  
  private function addDeskUsers($Loc, $Item, &$Exemplar = array())
  {
    if ( APPDEBUG["FKT"] ) $this->CS->debug("FKT",__METHOD__);

    

    $Desk  = "";
    $User  = "";
    $Mode  = ( isset($Exemplar["action"]["request"]["type"]) ) ? $Exemplar["action"]["request"]["type"] : "";

    if ( isset($Exemplar["action"]["deskgroup"]) && $Exemplar["action"]["deskgroup"]
      && isset($Exemplar["action"]["usergroup"]) && $Exemplar["action"]["usergroup"] )
    {
      $Desk = $Exemplar["action"]["deskgroup"];
      $User = $Exemplar["action"]["usergroup"];
      unset($Exemplar["action"]["deskgroup"]);
      unset($Exemplar["action"]["usergroup"]);
    }
    elseif ( $Mode && isset($this->CS->locations[$Mode]) && count($this->CS->locations[$Mode]) )
    {
      if ( isset($Item["storageid"]) && $Item["storageid"] != "-" )
      {
        $StorID = strtolower($Item["storageid"]);
        $AUI    = isset($Item["e"][0]) ? $Item["e"][0] : "";

        
        if ( isset($this->CS->locations[$Mode][$Loc][$StorID]) )
        {
          if ( $AUI && isset($this->CS->locations[$Mode][$Loc][$StorID][$AUI]["deskgroup"]) &&
                       isset($this->CS->locations[$Mode][$Loc][$StorID][$AUI]["usergroup"]) )
          {
            $Desk = $this->CS->locations[$Mode][$Loc][$StorID][$AUI]["deskgroup"];
            $User = $this->CS->locations[$Mode][$Loc][$StorID][$AUI]["usergroup"];
          }
          elseif ( isset($this->CS->locations[$Mode][$Loc][$StorID]["deskgroup"]) &&
                   isset($this->CS->locations[$Mode][$Loc][$StorID]["usergroup"]) )
          {
            $Desk = $this->CS->locations[$Mode][$Loc][$StorID]["deskgroup"];
            $User = $this->CS->locations[$Mode][$Loc][$StorID]["usergroup"];
          }
        }
        else
        {
          
          if ( isset($this->CS->locations["loc2group"][$Loc]) )
          {
            $Loc2 = $this->CS->locations["loc2group"][$Loc];
            if ( isset($this->CS->locations[$Mode][$Loc2][$StorID]) )
            {
              if ( $AUI && isset($this->CS->locations[$Mode][$Loc2][$StorID][$AUI]["deskgroup"])
                        && isset($this->CS->locations[$Mode][$Loc2][$StorID][$AUI]["usergroup"]) )
              {
                $Desk = $this->CS->locations[$Mode][$Loc2][$StorID][$AUI]["deskgroup"];
                $User = $this->CS->locations[$Mode][$Loc2][$StorID][$AUI]["usergroup"];
              }
              elseif ( isset($this->CS->locations[$Mode][$Loc2][$StorID]["deskgroup"]) &&
                       isset($this->CS->locations[$Mode][$Loc2][$StorID]["usergroup"]) )
              {
                $Desk = $this->CS->locations[$Mode][$Loc2][$StorID]["deskgroup"];
                $User = $this->CS->locations[$Mode][$Loc2][$StorID]["usergroup"];
              }
            }
          }
          else
          {
            
            if ( isset($this->CS->locations["group2loc"][$Loc]) )
            {
              foreach ($this->CS->locations["group2loc"][$Loc] as $Loc2)
              {
                if ( isset($this->CS->locations[$Mode][$Loc2][$StorID]) )
                {
                  if ( $AUI && isset($this->CS->locations[$Mode][$Loc2][$StorID][$AUI]["deskgroup"])
                            && isset($this->CS->locations[$Mode][$Loc2][$StorID][$AUI]["usergroup"]) )
                  {
                    $Desk = $this->CS->locations[$Mode][$Loc2][$StorID][$AUI]["deskgroup"];
                    $User = $this->CS->locations[$Mode][$Loc2][$StorID][$AUI]["usergroup"];
                  }
                  elseif ( isset($this->CS->locations[$Mode][$Loc2][$StorID]["deskgroup"]) &&
                           isset($this->CS->locations[$Mode][$Loc2][$StorID]["usergroup"]) )
                  {
                    $Desk = $this->CS->locations[$Mode][$Loc2][$StorID]["deskgroup"];
                    $User = $this->CS->locations[$Mode][$Loc2][$StorID]["usergroup"];
                  }
                  break;
                }
              }
            }
          }
        }
      }
    }

    $Desks = ( $Desk && isset($this->CS->locations["deskgroups"][$Desk]) ) ? $this->CS->locations["deskgroups"][$Desk] : array();
    $Users = ( $User && isset($this->CS->locations["usergroups"][$User]) ) ? $this->CS->locations["usergroups"][$User] : array();

    return array("desks" => $Desks,  "users" => $Users);
  }

  
  
  
  
  
  
  
  private function getSignatureStockComment($Sig, $Sto, $Com, $MitEltern = true)
  {
    
    
    
    

    $SSCs = array();

    
    

    if ( $Sto )
    {
      if ( isset($this->one["marc"]["M773"]) && is_array($this->one["marc"]["M773"]) )
      {
        $Recs   = $this->one["marc"]["M773"];
        foreach ( $Recs as $Rec )
        {
          if ( !isset($Rec["7"][0]) || !in_array($Rec["7"][0], array("nnnm", "nnns")) ) continue;

          $SC     = array("signature" => array(), "stock" => array(), "comment" => array());
          if ( $Sto && isset($Rec["g"]) && is_array($Rec["g"]) && count($Rec["g"]) )
          {
            $SC["stock"] = $this->getMARCElement($Rec, "stock");
          }
          if ( count($SC["stock"]) ) $SSCs[] = $SC;
        }
      }
    }

    if ( isset($this->one["marc"]["M980"]) && is_array($this->one["marc"]["M980"]) )
    {
      $Recs   = $this->one["marc"]["M980"];
      foreach ( $Recs as $Rec )
      {
        $SC        = array("signature" => array(), "stock" => array(), "comment" => array());

        if ( $Sig && isset($Rec["d"]) && is_array($Rec["d"]) && count($Rec["d"]) )
        {
          $SC["signature"] = $this->getMARCElement($Rec, "signature");
        }
        if ( $Sto && isset($Rec["g"]) && is_array($Rec["g"]) && count($Rec["g"]) )
        {
          $SC["stock"] = $this->getMARCElement($Rec, "stock");
        }
        if ( $Com && isset($Rec["k"]) && is_array($Rec["k"]) && count($Rec["k"]) )
        {
          $SC["comment"]   = $this->getMARCElement($Rec, "comment");
        }
        if ( count($SC["signature"]) || count($SC["stock"]) || count($SC["comment"]) ) $SSCs[] = $SC;
      }
    }

    if ( $MitEltern && $this->useParent() )
    {
      if ( isset($this->CS->works["add"][$this->pid]["marc"]["PM980"]) && is_array($this->CS->works["add"][$this->pid]["marc"]["PM980"]) )
      {
        $Recs   = $this->CS->works["add"][$this->pid]["marc"]["PM980"];
        foreach ( $Recs as $Rec )
        {
          $SC        = array("signature" => array(), "stock" => array(), "comment" => array());

          if ( $Sig && isset($Rec["d"]) && is_array($Rec["d"]) && count($Rec["d"]) )
          {
            $SC["signature"] = $this->getMARCElement($Rec, "signature");
          }
          if ( $Sto && isset($Rec["g"]) && is_array($Rec["g"]) && count($Rec["g"]) )
          {
            $SC["stock"] = $this->getMARCElement($Rec, "stock");
          }
          if ( $Com && isset($Rec["k"]) && is_array($Rec["k"]) && count($Rec["k"]) )
          {
            $SC["comment"]   = $this->getMARCElement($Rec, "comment");
          }
          if ( count($SC["signature"]) || count($SC["stock"]) || count($SC["comment"]) ) $SSCs[] = $SC;
        }
      }
    }

    

    
    
    
    $Texts = array("g" => array(), "e" => array());
    if ( is_array($SSCs) && count($SSCs) )
    {
      
      foreach ($SSCs as $SSC)
      {
        if ( isset($SSC["signature"]) && is_array($SSC["signature"]) && count($SSC["signature"]) )
        {
          if ( !isset($SSC["signature"]["g"]) || !isset($SSC["signature"]["e"]) ) continue;
          $Texts["g"] = [...$Texts["g"],
                         ...$SSC["signature"]["g"]];
          $Texts["e"] = [...$Texts["e"],
                         ...$SSC["signature"]["e"]];
        }
        if ( isset($SSC["stock"]) && is_array($SSC["stock"]) && count($SSC["stock"]) )
        {
          if ( !isset($SSC["stock"]["g"]) || !isset($SSC["stock"]["e"]) ) continue;
          $Texts["g"] = [...$Texts["g"],
                         ...$SSC["stock"]["g"]];
          $Texts["e"] = [...$Texts["e"],
                         ...$SSC["stock"]["e"]];
        }
        if ( isset($SSC["comment"]) && is_array($SSC["comment"]) && count($SSC["comment"]) )
        {
          if ( !isset($SSC["comment"]["g"]) || !isset($SSC["comment"]["e"]) ) continue;
          $Texts["g"] = [...$Texts["g"],
                         ...$SSC["comment"]["g"]];
          $Texts["e"] = [...$Texts["e"],
                         ...$SSC["comment"]["e"]];
        }
        $Texts["g"][] = array("type" => "space");
        $Texts["e"][] = array("type" => "space");
      }
    }

    
    return $Texts;
  }

  
  
  private function modifySignatureLocal(&$Item, $Location = "")
  {
    if ( APPDEBUG["FKT"] ) $this->CS->debug("FKT",__METHOD__);

    $File                    = dirname(__FILE__) . DIRECTORY_SEPARATOR . "clients"
                               . DIRECTORY_SEPARATOR . $this->CS->client
                               . DIRECTORY_SEPARATOR . "signature.php";
    $Status                  = true;
    $Vs                      = array();
    if ( file_exists($File) )
    {
      
      include $File;
    }
    else
    {
      
      $Vals = array();
      foreach ($Item["d"] as $Key => $Val)
      {
        if ( $Key > 0 )         continue;
        if ( $Val == "--%%--" ) continue;
        $Vals[] = $Val;
      }
      if ( is_array($Vals) && count($Vals) )
      {
        $this->addArr2Arr($this->signature, $Vals);
        $T  = $this->CS->getTextBothLang("items.signature");
        $Vs["g"][] = array("type" => "text",
                           "text" => $T["german"] . ": " .  implode(", ", $Vals));
        $Vs["e"][] = array("type" => "text",
                           "text" => $T["english"] . ": " .  implode(", ", $Vals));
        
      }
      else
      {
        $Status = false;
      }
    }

    
    
    
    
    
    
    if ( $Location !== "" )
    {
      if ( isset($Vals) && is_array($Vals) && count($Vals) )
      {
        $Vs   = array();
        $Locs = array();
        foreach ( $Vals as $Val )  $Locs[] = $Location . ":" . $Val;
        $T    = $this->CS->getTextBothLang("items.signature");
        $Vs["g"][] = array("type" => "text",
                           "text" => $T["german"]  . ": " . implode(", ", $Locs));
        $Vs["e"][] = array("type" => "text",
                           "text" => $T["english"] . ": " . implode(", ", $Locs));
      }
      else
      {
        $Status = false;
      }
    }

    
    
    
    
    
    
    
    
    $Labels = $this->CS->getTextBothLang("items.signature");
    foreach ( array("g" => "german", "e" => "english") as $Lang => $Key )
    {
      if ( !isset($Vs[$Lang]) || !is_array($Vs[$Lang]) )  continue;
      foreach ( $Vs[$Lang] as $Idx => $Val )
      {
        if ( !isset($Val["type"]) || $Val["type"] != "text" )  continue;
        if ( !isset($Val["text"]) )                            continue;
        $Vs[$Lang][$Idx]["text"] = $this->CS->markValueBold($Val["text"],
                                                            isset($Labels[$Key]) ? $Labels[$Key] : "");
      }
    }

    
    return array("status" => $Status, "values" => $Vs);
  }

  private function getItemDataLocal(&$Item, &$Exemplar)
  {
    if ( APPDEBUG["FKT"] ) $this->CS->debug("FKT",__METHOD__);

    

    
    $File                    = dirname(__FILE__) . DIRECTORY_SEPARATOR . "clients"
                               . DIRECTORY_SEPARATOR . $this->CS->client
                               . DIRECTORY_SEPARATOR . "itemfunctions.php";
    if ( file_exists($File) )
    {
      include_once($File);
    }

    
    $File                    = dirname(__FILE__) . DIRECTORY_SEPARATOR . "clients"
                               . DIRECTORY_SEPARATOR . $this->CS->client
                               . DIRECTORY_SEPARATOR . "item.php";
    if ( file_exists($File) )
    {
      include($File);
    }
  }

  private function getGroupComment($ILN)
  {
    $Comments = array();
    if ( isset($this->one["marc"]["M986"]) && is_array($this->one["marc"]["M986"]) )
    {
      foreach ( $this->one["marc"]["M986"] as $Rec )
      {
        if ( isset($Rec["2"][0]) && $Rec["2"][0] == $ILN && isset($Rec["b"][0]) )  $Comments[] = $Rec["b"][0];
      }
    }
    return $Comments;
  }

  

  
  private function setCombs()
  {
    if ( APPDEBUG["FKT"] ) $this->CS->debug("FKT",__METHOD__);

    $Combs = array();
    $Locs  = array();
    $Loc   = "";
    $EPN   = 0;
    $Skip  = false;

    
    $MLocs = ( isset($this->CS->configs["item"]["multiplelocations"])   && $this->CS->configs["item"]["multiplelocations"]  == "y" )
             ? true : false;
    $MKeys = ( isset($this->one["items"]) && count($this->one["items"]) )
             ? array_keys($this->one["items"]) : array();

    
    
    
    

    if ( $this->pid && !$this->awop && !count($MKeys) && isset($this->CS->works["add"][$this->pid]) )
    {
      
      
      if ( $this->aps )
      {
        
        
        if ( $this->isHapticArticle() )
        {
          $this->one["items"] = $this->CS->works["add"][$this->pid]["items"];
          $MKeys     = ( isset($this->one["items"]) && count($this->one["items"]) )
                       ? array_keys($this->one["items"]) : array();
        }
      }
      else
      {
        
        
        $MKeys     = ( isset($this->CS->works["add"][$this->pid]["items"]) && count($this->CS->works["add"][$this->pid]["items"]) )
                   ? array_keys($this->CS->works["add"][$this->pid]["items"])
                   : array();
      }
    }

    $PKeys = ( isset($this->CS->paias[$this->id])
            && count($this->CS->paias[$this->id]) )  ? array_keys($this->CS->paias[$this->id]) : array();

    

    
    $AllItems   = array_unique(array_merge($MKeys, $PKeys));
    
    foreach ($AllItems as $LukidaID )
    {
      
      $Combs[$LukidaID] = (isset($this->CS->paias[$this->id][$LukidaID])) ? $this->CS->paias[$this->id][$LukidaID] : array();
      if ( !isset($Combs[$LukidaID]["about"]) ) $Combs[$LukidaID]["about"] = "";      

      
      $EPN = (isset($Combs[$LukidaID]["epn"])) ? $Combs[$LukidaID]["epn"] : 0;  

      
      if ( $EPN != 0 )
      {
        if (isset($this->one["items"][$EPN])) $Combs[$LukidaID] += $this->one["items"][$EPN];
      }
      else
      {
        if ( isset($this->one["items"][$LukidaID]) )
        {
          $Combs[$LukidaID] += $this->one["items"][$LukidaID];
        }
        else
        {
          if ( $this->useParent() && isset($this->CS->works["add"][$this->pid]["items"][$LukidaID]) )
          {
            $Combs[$LukidaID] += $this->CS->works["add"][$this->pid]["items"][$LukidaID];
          }
        }
      }
      
    }

    

    
    if ( count($Combs) )
    {
      foreach ($Combs as $ExpID => $Item)
      {
        
        if ( isset($Item["loc"]) && $Item["loc"] == "onlineglobal" )
        {
          $Item["loc"] = "online";
          $Loc = $Item["loc"];
        }
        elseif ( !$MLocs && isset($Item["loc"]) && $Item["loc"] == "onlinelocal" )
        {
          
          $Item["loc"] = "online";
          $Loc = $Item["loc"];
        }
        elseif ( $MLocs  )
        {
          
          if ( isset($Item["loc"]) && $Item["loc"] == "onlinelocal"
            && isset($this->CS->locations["onlineids"]) && is_array($this->CS->locations["onlineids"]) && count($this->CS->locations["onlineids"]) > 0
            && isset($Item["iln"]) && isset($this->CS->locations["onlineids"][$Item["iln"]]) )
          {
            $Loc = $this->CS->locations["onlineids"][$Item["iln"]];
          }
          
          elseif ( isset($Item["loc"]) && $Item["loc"] == "onlinelocal" )
          {
            $Item["loc"] = "online";
            $Loc = $Item["loc"];
          }
          
          elseif ( isset($this->CS->locations["imp"]) && is_array($this->CS->locations["imp"]) && count($this->CS->locations["imp"]) > 0 )
          {
            $ItemILNLoc  = "";
            $ItemILNSig  = "";
            $ItemILNMan  = "";
            $ItemILNAUI  = "";
            if ( isset($Item["2"][0]) ) $ItemILNLoc  = $Item["2"][0];
            if ( isset($Item["2"][0]) ) $ItemILNSig  = $Item["2"][0];
            if ( isset($Item["2"][0]) ) $ItemILNMan  = $Item["2"][0];
            if ( isset($Item["2"][0]) ) $ItemILNAUI  = $Item["2"][0];
            $ItemILNLoc  .= ":";
            $ItemILNSig  .= ":";
            $ItemILNMan  .= "@";
            $ItemILNAUI  .= ":";
            if ( isset($Item["f"][0]) ) $ItemILNLoc .= $Item["f"][0];
            if ( isset($Item["d"][0]) ) $ItemILNSig .= $Item["d"][0];
            if ( isset($Item["y"][0]) ) $ItemILNMan .= substr($Item["y"][0],2);
            if ( isset($Item["e"][0]) ) $ItemILNAUI .= $Item["e"][0];

            $Loc = "";
            
            if ( isset($this->CS->locations["skip"]["loc"]) && is_array($this->CS->locations["skip"]["loc"])
              && in_array($ItemILNLoc, $this->CS->locations["skip"]["loc"]) )
            {
              $Skips[] = $ExpID;
              continue;
            }
            
            elseif ( isset($this->CS->locations["skip"]["sig"]) && is_array($this->CS->locations["skip"]["sig"])
              && in_array($ItemILNSig, $this->CS->locations["skip"]["sig"]) )
            {
              $Skips[] = $ExpID;
              continue;
            }
            else
            {
              
              foreach ( $this->CS->locations["exp"] as $IL => $Conds )
              {
                
                

                $Found = array("loc" => false, "sig" => false, "man" => false, "aui" => false);
                
                if ( isset($Conds["loc"]) && count($Conds["loc"]) )
                {
                  
                  foreach ( $Conds["loc"] as $O )
                  {
                    if ( LocRule::matches($O, $ItemILNLoc, ":") )  {$Found["loc"] = true; break;}
                  }
                  if ( !$Found["loc"] ) continue;
                }
                else
                {
                  $Found["loc"] = true;
                }
                
                if ( isset($Conds["sig"]) && count($Conds["sig"]) )
                {
                  
                  foreach ( $Conds["sig"] as $O )
                  {
                    if ( LocRule::matches($O, $ItemILNSig, ":") )  {$Found["sig"] = true; break;}
                  }
                  if ( !$Found["sig"] ) continue;
                }
                else
                {
                  $Found["sig"] = true;
                }
                
                if ( isset($Conds["man"]) && count($Conds["man"]) )
                {
                  
                  foreach ( $Conds["man"] as $O )
                  {
                    if ( LocRule::matches($O, $ItemILNMan, "@") )  {$Found["man"] = true; break;}
                  }
                  if ( !$Found["man"] ) continue;
                }
                else
                {
                  $Found["man"] = true;
                }
                

                
                if ( isset($Conds["aui"]) && count($Conds["aui"]) )
                {
                  
                  foreach ( $Conds["aui"] as $O )
                  {
                    if ( LocRule::matches($O, $ItemILNAUI, ":") )  {$Found["aui"] = true; break;}
                  }
                  if ( !$Found["aui"] ) continue;
                }
                else
                {
                  $Found["aui"] = true;
                }
                
                if ($Found["loc"] && $Found["sig"] && $Found["man"]&& $Found["aui"])
                {
                  $Loc = $IL;
                  break;
                }
              }
              if ( $Loc == "" ) $Loc = "default";
            }
          }
          else
          {
            $Loc = "default";
          }
        }
        else
        {
          $Loc = "default";
        }

        

        
        
        
        
        
        
        
        
        $Switch = $this->CS->confPairs("item", "locationmap");
        if ( isset($Item["label"]) && array_key_exists($Item["label"], $Switch) )
        {
          $Loc = $Switch[$Item["label"]];
          if ( !isset($Item["about"]) ) $Item["about"] = "";  
        }

        $Combs[$ExpID]["loc"] = $Loc;
        if ( !isset($Combs[$ExpID]["about"]) ) $Combs[$ExpID]["about"] = "";
        $Locs[] = $Loc;
      }
      

      if ( (isset($this->CS->configs["item"]["sortabout"]) && $this->CS->configs["item"]["sortabout"] == "y") || $this->epn)
      {
        uasort($Combs, function ($a, $b) { return $b['about'] <=> $a['about']; });
      }
      $Locs   = array_values(array_unique($Locs));
    }

    
    
    

    
    
    

    

    
    if ( isset($this->CS->locations["hiersort"]) && is_array($this->CS->locations["hiersort"]) && count($this->CS->locations["hiersort"])
      && isset($Locs)                            && is_array($Locs)                            && count($Locs)
      && isset($Combs)                           && is_array($Combs)                           && count($Combs) )
    {
      $TmpC = array();
      $TmpL = array();
      foreach ( $this->CS->locations["hiersort"] as $Sort )
      {
        if (isset($Sort["id"]) && in_array($Sort["id"], $Locs) )
        {
          

          foreach ( $Combs as $EPN => $C )
          {
            if ( isset($C["loc"]) && $C["loc"] == $Sort["id"] )
            {
              $TmpC[$EPN] = $C;
              if ( !in_array($C["loc"], $TmpL) )  $TmpL[] = $C["loc"];
            }
          }

          
          
        }
      }

      $MissingLocs = array_diff($Locs, $TmpL);
      if ( count($MissingLocs) )
      {
        foreach ( $MissingLocs as $Miss )
        {
          foreach ( $Combs as $EPN => $C )
          {
            if ( isset($C["loc"]) && $C["loc"] == $Miss )
            {
              $TmpC[$EPN] = $C;
              if ( !in_array($C["loc"], $TmpL) )  $TmpL[] = $C["loc"];
            }
          }

          
          
        }
      }


      
      
      
      $Combs = $TmpC;
      $Locs  = $TmpL;
    }

    if ( !is_array($this->one) ) $this->one = array();
    $this->one["locs"]  = $Locs;
    $this->one["combs"] = $Combs;
    if ( $this->CS->display["comb"] ) $this->CS->results["comb"][$this->id] = $Combs;
    
  }

  
  private function setitems()
  {
    if ( APPDEBUG["FKT"] ) $this->CS->debug("FKT",__METHOD__);

    
    

    
    $this->one["logs"]  = array();
    $Items  = array();
    $GIdx   = 0;

    foreach ( $this->one["locs"] as $Loc )
    {
      $Exps       = array();
      $this->best = "red";

      foreach ($this->one["combs"] as $Item)
      {
        
        if ( !isset($Item["loc"]) || $Item["loc"] != $Loc ) continue;

        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        $this->comment   = array();
        $this->location  = array();
        $this->signature = array();
        $this->stock     = array();

        if ( isset($Item["f"][0]) )   $this->location[] = $Item["f"][0];

        if ( isset($Item["link"]) )
        {
          $this->setLog("Link", "New " . $this->CS->getHost($Item["link"]));
        }
        else
        {
          $this->setLog("Item", "New EPN " . $Item["epn"]);
        }

        $ILN = isset($Item["2"][0]) ? $Item["2"][0] : "";
        
        

        
        $this->getExps($Exps, $this->id, $this->CS->cases, $Item);

        
        
        
        
        
        $this->addArr2Arr($this->allcomment,   $this->comment);
        $this->addArr2Arr($this->alllocation,  $this->location);
        $this->addArr2Arr($this->allsignature, $this->signature);
        $this->addArr2Arr($this->allstock,     $this->stock);
      }

      if (count($Exps))
      {
        $Tmp = $this->CS->getTextBothLang("location." . $Loc);
        $Items["group_".++$GIdx] = array("code"       => $Loc,
                                         "label"      => $Loc,   
                                         "text"       => array("g" => array(array("type" => "text", "text" => $Tmp["german"])),
                                                               "e" => array(array("type" => "text", "text" => $Tmp["english"]))),
                                         "before"     => $this->getGroupComment($ILN),
                                         "groupitems" => $Exps,
                                         "light"      => $this->best,
                                         "after"      => array());
      }
    }

    
    
    if ( !$this->epn &&  isset($this->CS->configs["other"]["groupsreverse"]) && $this->CS->configs["other"]["groupsreverse"] == 'y' )
    {
      $this->AddMoregroups($Items, $GIdx);
    }

    if ( !$this->epn )
    {
      
      $this->setLog("Media", "New PPN " . $this->id);
      $Exps       = array();
      $Empty      = array();
      $this->best = "red";
      
      $this->comment   = $this->allcomment;
      $this->location  = $this->alllocation;
      $this->signature = $this->allsignature;
      $this->stock     = $this->allstock;
      $this->getExps($Exps, $this->id, $this->CS->casesmedia, $Empty);

      
      
      
      
      if (count($Exps))
      {
        $Tmp = $this->CS->getTextBothLang("location.media");
        $Items["group_".++$GIdx] = array("code"       => "media",
                                         "label"      => "media",   
                                         "text"       => array("g" => array(array("type" => "text", "text" => $Tmp["german"])),
                                                               "e" => array(array("type" => "text", "text" => $Tmp["english"]))),
                                         "before"     => array(),
                                         "groupitems" => $Exps,
                                         "light"      => $this->best,
                                         "after"      => array());
      }
    }

    
    
    

    
    if ( $this->CS->display["logs"] ) $this->CS->results["logs"][$this->id] = $this->one["logs"];

    
    
    if ( !$this->epn &&  isset($this->CS->configs["other"]["groupsreverse"]) && $this->CS->configs["other"]["groupsreverse"] == 'n' )
    {
      $this->AddMoregroups($Items, $GIdx);
    }

    if ( isset($this->CS->configs["other"]["groupsreverse"]) && $this->CS->configs["other"]["groupsreverse"] == 'y' )
    {
      $Keys  = array_keys($Items);
      $Vals  = array_reverse(array_values($Items));
      $Items = array_combine($Keys, $Vals);
    }

    return $Items;
  }

  private function AddMoregroups(&$Items, &$GIdx)
  {
    $MGs = $this->setMoreGroups();
    
    if ( isset($MGs["searches"]) && is_array($MGs["searches"]) && count($MGs["searches"]) )
    {
      $Items["group_".++$GIdx] = array("label"      => "searches",
                                       "before"     => array(),
                                       "groupitems" => $MGs["searches"],
                                       "light"      => "blue",
                                       "after"      => array());
    }
    if ( isset($MGs["links"]) && is_array($MGs["links"]) && count($MGs["links"]) )
    {
      $Items["group_".++$GIdx] = array("label"      => "links",
                                       "before"     => array(),
                                       "groupitems" => $MGs["links"],
                                       "light"      => "blue",
                                       "after"      => array());
    }
  }

  
  private function getExps(&$Exps, &$ID, &$ScopeCases, &$Item)
  {
    $OldSortLevel  = "";
    $Hits          = array();

    $DAIA = (isset($Item["id"]) && trim($Item["id"]) != "" )                    ? true                                      : false;
    $LOAN = ($DAIA  && isset($Item["loan"]))                                    ? $Item["loan"]                             : false;
    $PRES = ($DAIA  && isset($Item["presentation"]))                            ? $Item["presentation"]                     : false;
    $OPEN = ($DAIA  && isset($Item["openaccess"]))                              ? $Item["openaccess"]                       : false;
    $REMO = ($DAIA  && isset($Item["remote"]))                                  ? $Item["remote"]                           : false;
    $INTR = ($DAIA  && isset($Item["interloan"]))                               ? $Item["interloan"]                        : false;
    $EXPD = ($DAIA  && isset($Item["loanitems"][0]["expected"])
                          && $Item["loanitems"][0]["expected"] != "-" )         ? $Item["loanitems"][0]["expected"]         : "";
    if ( !$EXPD ) $EXPD = ($DAIA  && isset($Item["presentationitems"][0]["expected"])
                                        && $Item["presentationitems"][0]["expected"] != "-" ) ? $Item["presentationitems"][0]["expected"] : "";
    $QUEU = ($DAIA  && isset($Item["loanitems"][0]["queue"])
                          && $Item["loanitems"][0]["queue"]    != "-" )         ? $Item["loanitems"][0]["queue"]            : "";
    if ( !$QUEU ) $QUEU = ($DAIA  && isset($Item["presentationitems"][0]["queue"])
                                        && $Item["presentationitems"][0]["queue"] != "-" ) ? $Item["presentationitems"][0]["queue"] : "";
    $ACTN = ($DAIA  && isset($Item["loanitems"][0]["action"])
                          && $Item["loanitems"][0]["action"]   != "-" )         ? $Item["loanitems"][0]["action"]           : "";
    if ( !$ACTN ) $ACTN = ($DAIA  && isset($Item["presentationitems"][0]["action"])
                                        && $Item["presentationitems"][0]["action"] != "-" ) ? $Item["presentationitems"][0]["action"] : "";
    $Loc  = isset($Item["loc"]) ? $Item["loc"] : "";

    
    

    foreach ( $ScopeCases as $SortLevel => $Cases )
    {
      if ( $OldSortLevel != "" && $OldSortLevel != $SortLevel ) continue;
      foreach ( $Cases as $CI => $Case )
      {
        

        
        if ( !isset($Case["active"]) || !$Case["active"] )  continue;

        
        
        $this->ausdrueckeErlaubt = !empty($Case["ausdruecke"]);

        $this->setLog("Case", "Check " . $SortLevel . "." . $Case["name"]);

        $Match     = true;
        $Offen     = false;   
        $Verneint  = false;
        $VorMatch  = true;
        $Unbekannt = false;
        foreach ( $Case["filters"] as $Filter )
        {
          
          
          
          
          
          

          if ( !$Match )  continue;
          $this->setLog("Filter", "Check " . $Filter);

          
          
          
          
          
          
          
          
          
          
          
          
          
          
          
          
          
          
          
          
          
          
          
          
          
          
          $Verneint = false;
          if ( substr($Filter, 0, 6) === "nicht " )  { $Verneint = true;  $Filter = trim(substr($Filter, 6)); }
          elseif ( substr($Filter, 0, 1) === "!" )   { $Verneint = true;  $Filter = trim(substr($Filter, 1)); }
          if ( $Verneint )
          {
            $VorMatch  = $Match;
            $Unbekannt = false;
            $Offen     = true;
          }

          do {

          
          if ( substr($Filter,0,9) == "f.status." )
          {
            $StatiFound = false;
            if ( !isset($Item["status"]) )
            {
              $Match = false;
            }
            else
            {
              $Statis     = explode(",", substr($Filter,9));
              foreach ( $Statis as $Stati )
              {
                if ( substr($Stati,-1) == "*" )
                {
                  $Pos = strpos($Stati, "*");
                  if ( substr($Stati,0,$Pos) == substr($Item["status"],0,$Pos) ) $StatiFound = true;
                }
                else
                {
                  if ( $Stati == $Item["status"] )   $StatiFound = true;
                }
                if ( $StatiFound ) continue;
              }
              if ( !$StatiFound )  $Match = false;
            }
            continue;
          }
          elseif ( substr($Filter,0,11) == "f.nostatus." )
          {
            if ( isset($Item["status"]) )
            {
              $Statis     = explode(",", substr($Filter,11));
              $StatiFound = false;
              foreach ( $Statis as $Stati )
              {
                if ( substr($Stati,-1) == "*" )
                {
                  $Pos = strpos($Stati, "*");
                  if ( substr($Stati,0,$Pos) == substr($Item["status"],0,$Pos) ) $StatiFound = true;
                }
                else
                {
                  if ( $Stati == $Item["status"] )   $StatiFound = true;
                }
                if ( $StatiFound )
                {
                  $Match = false;
                }
              }
              
            }
            continue;
          }

          
          if ( substr($Filter,0,10) == "m.article." )
          {
            $M7737s = explode(",",substr($Filter,10));

            
            

            if (  !isset($this->one["artcode"]) || !in_array($this->one["artcode"], $M7737s) )  $Match = false;
            continue;
          }

          
          elseif ( substr($Filter,0,6) == "m.ali." )
          {
            $Tmp = substr($Filter,6);
            if ( stripos($Tmp, ",") !== false )
            {
              $M980Es = explode(",", $Tmp);
              $Tmp    = "";
            }
            elseif ( stripos($Tmp, "--%%--") !== false )
            {
              $Tmp      = str_replace("--%%--", "", $Tmp);
              $M980Es   = str_split($Tmp);
              $M980Es[] = "--%%--";
            }
            else
            {
              $M980Es = str_split($Tmp);
            }
            $ALI    = isset($Item["e"][0]) ? $Item["e"][0] : "";

            if ( !$ALI || !in_array($ALI, $M980Es) )   $Match = false;
          }

          
          
          
          
          
          elseif ( substr($Filter,0,8) == "m.noali." )
          {
            $Tmp = substr($Filter,8);
            if ( stripos($Tmp, ",") !== false )
            {
              $M980Es = explode(",", $Tmp);
              $Tmp    = "";
            }
            elseif ( stripos($Tmp, "--%%--") !== false )
            {
              $Tmp      = str_replace("--%%--", "", $Tmp);
              $M980Es   = str_split($Tmp);
              $M980Es[] = "--%%--";
            }
            else
            {
              $M980Es = str_split($Tmp);
            }
            $ALI    = isset($Item["e"][0]) ? $Item["e"][0] : "";

            if ( $ALI && in_array($ALI, $M980Es) )   $Match = false;
          }

          
          elseif ( substr($Filter,0,7) == "m.host." )
          {
            
            if ( !isset($Item["host"]) )  {$Match = false;  continue; }

            if ( !$this->checkStrInWildArr($Item["host"], explode(",", substr($Filter,7))) ) $Match = false;
            continue;
          }

          
          elseif ( substr($Filter,0,10) == "m.comment." )
          {
            
            
            
            
            
            
            
            
            
            if ( !isset($Item["k"][0]) )  {$Match = false;  continue; }

            if ( !$this->checkStrInWildArr($Item["k"][0], explode(",", substr($Filter,10))) ) $Match = false;
            continue;
          }

          
          
          
          
          
          
          
          
          
          
          elseif ( substr($Filter,0,18) == "m.commentcontains." )
          {
            if ( !isset($Item["k"]) || !count((array) $Item["k"]) )  {$Match = false;  continue; }

            if ( !$this->checkArrContainsArr($Item["k"], explode(",", substr($Filter,18))) ) $Match = false;
            continue;
          }

          
          elseif ( substr($Filter,0,11) == "m.textlink." )
          {
            
            if ( !isset($Item["text"]) )  {$Match = false;  continue; }

            if ( !$this->checkArrInWildArr(explode(", ", $Item["text"]), explode(",", substr($Filter,11))) ) $Match = false;
            continue;
          }

          
          elseif ( substr($Filter,0,16) == "m.yearolderthan." )
          {
            $Year    = (int) substr($Filter,16);
            if ( $Year < 750 || $Year > intval(date("Y")+1) || $Year >= $this->one["year"])  $Match = false;

            continue;
          }
          elseif ( substr($Filter,0,18) == "m.yearyoungerthan." )
          {
            $Year    = (int) substr($Filter,18);
            if ( $Year < 750 || $Year > intval(date("Y")+1) || $Year <= $this->one["year"])  $Match = false;

            continue;
          }

          
          elseif ( substr($Filter,0,9) == "m.format." )
          {
            $Forms    = explode(",", strtolower(substr($Filter,9)));
            if ( isset($this->one["formatcode"]) && !in_array(strtolower($this->one["formatcode"]), $Forms) )  $Match = false;
            continue;
          }
          elseif ( substr($Filter,0,11) == "m.noformat." )
          {
            $Forms    = explode(",", strtolower(substr($Filter,11)));
            if ( !isset($this->one["formatcode"]) || in_array(strtolower($this->one["formatcode"]), $Forms) )  $Match = false;
            continue;
          }

          
          elseif ( substr($Filter,0,19) == "m.locallinkcomment." )
          {
            $LinkCommentFound = false;
            if ( !isset($Item["text"]) )
            {
              $Match = false;
            }
            else
            {
              
              
              
              
              
              
              
              
              
              $LinkCommentFound = $this->checkStrInWildArr($Item["text"], explode(",", substr($Filter,19)));
              if ( !$LinkCommentFound )  $Match = false;
            }
            continue;
          }

          
          
          
          
          
          
          
          elseif ( substr($Filter,0,6) == "m.man." )
          {
            $M980Ys = explode(",",substr($Filter,6));

            
            
            

            if ( count($Item) )
            {
              
              $Man    = isset($Item["y"][0]) ? substr($Item["y"][0],1) : "";
              if ( !$Man || !$this->checkStrInWildArr($Man, $M980Ys) )   $Match = false;
            }
            else
            {
              
              $Mans = array();
               if ( isset($this->one["marc"]["M980"]) && is_array($this->one["marc"]["M980"]) )
              {
                foreach ( $this->one["marc"]["M980"] as $Rec )
                {
                  if ( isset($Rec["y"][0]) )  $Mans[] = substr($Rec["y"][0],1);
                }
              }
              if ( !$this->checkArrInWildArr($Mans, $M980Ys) )  $Match = false;
            }
            continue;
          }
          elseif ( substr($Filter,0,8) == "m.noman." )
          {
            $M980Ys = explode(",",substr($Filter,8));

            if ( count($Item) )
            {
              
              $Man    = isset($Item["y"][0]) ? substr($Item["y"][0],1) : "";
              if ( $this->checkStrInWildArr($Man, $M980Ys) )  $Match = false;
            }
            else
            {
              
              $Mans = array();
               if ( isset($this->one["marc"]["M980"]) && is_array($this->one["marc"]["M980"]) )
              {
                foreach ( $this->one["marc"]["M980"] as $Rec )
                {
                  if ( isset($Rec["y"][0]) )  $Mans[] =  substr($Rec["y"][0],1);
                }
              }
              if ( $this->checkArrInWildArr($Mans, $M980Ys) )  $Match = false;
            }
            continue;
          }

          
          elseif ( substr($Filter,0,6) == "m.iln." )
          {
            $M9802s = explode(",",substr($Filter,6));

            
            

            if ( count($Item) )
            {
              
              $ILN    = isset($Item["iln"]) ? $Item["iln"] : "";
              if ( !$ILN && isset($Item["2"]["0"]) )  $ILN = $Item["2"]["0"];
              if ( !$ILN || !$this->checkStrInWildArr($ILN, $M9802s) )   $Match = false;
            }
            else
            {
              
              $ILNs = array();
               if ( isset($this->one["marc"]["M980"]) && is_array($this->one["marc"]["M980"]) )
              {
                foreach ( $this->one["marc"]["M980"] as $Rec )
                {
                  if ( isset($Rec["2"][0]) )  $ILNs[] = $Rec["2"][0];
                }
              }
              if ( !$this->checkArrInWildArr($ILNs, $M9802s) )  $Match = false;
            }
            continue;
          }
          elseif ( substr($Filter,0,8) == "m.noiln." )
          {
            $M9802s = explode(",",substr($Filter,8));

            
            

            if ( count($Item) )
            {
              
              $ILN    = isset($Item["iln"]) ? $Item["iln"] : "";
              if ( !$ILN && isset($Item["2"]["0"]) )  $ILN = $Item["2"]["0"];
              if ( $ILN && $this->checkStrInWildArr($ILN, $M9802s) )   $Match = false;
            }
            else
            {
              
              $ILNs = array();
               if ( isset($this->one["marc"]["M980"]) && is_array($this->one["marc"]["M980"]) )
              {
                foreach ( $this->one["marc"]["M980"] as $Rec )
                {
                  if ( isset($Rec["2"][0]) )  $ILNs[] =  $Rec["2"][0];
                }
              }
              if ( $this->checkArrInWildArr($ILNs, $M9802s) )  $Match = false;
            }
            continue;
          }

          
          elseif ( substr($Filter,0,6) == "m.eln." )
          {
            $M980Xs = explode(",",substr($Filter,6));
            if ( count($Item) )
            {
              
              $ELN    = isset($Item["x"][0]) ? $Item["x"][0] : "";
              if ( !$ELN || !$this->checkStrInWildArr($ELN, $M980Xs) )   $Match = false;
            }
            else
            {
              
              $ELNs = array();
               if ( isset($this->one["marc"]["M980"]) && is_array($this->one["marc"]["M980"]) )
              {
                foreach ( $this->one["marc"]["M980"] as $Rec )
                {
                  if ( isset($Rec["x"][0]) )  $ELNs[] = $Rec["x"][0];
                }
              }
              if ( !$this->checkArrInWildArr($ELNs, $M980Xs) )  $Match = false;
            }
            continue;
          }
          elseif ( substr($Filter,0,8) == "m.noeln." )
          {
            $M980Xs = explode(",",substr($Filter,8));
            if ( count($Item) )
            {
              
              $ELN    = isset($Item["x"][0]) ? $Item["x"][0] : "";
              if ( $ELN && $this->checkStrInWildArr($ELN, $M980Xs) )   $Match = false;
            }
            else
            {
              
              $ELNs = array();
               if ( isset($this->one["marc"]["M980"]) && is_array($this->one["marc"]["M980"]) )
              {
                foreach ( $this->one["marc"]["M980"] as $Rec )
                {
                  if ( isset($Rec["x"][0]) )  $ELNs[] =  $Rec["x"][0];
                }
              }
              if ( $this->checkArrInWildArr($ELNs, $M980Xs) )  $Match = false;
            }
            continue;
          }


          
          elseif ( substr($Filter,0,10) == "m.manfull." )
          {
            $M980Ys = explode(",",substr($Filter,10));

            
            
            if ( count($Item) )
            {
              
              $Man    = isset($Item["y"][0]) ? $Item["y"][0] : "";
              if ( !$Man || !$this->checkStrInWildArr($Man, $M980Ys) )   $Match = false;
            }
            else
            {
              
              $Mans = array();
               if ( isset($this->one["marc"]["M980"]) && is_array($this->one["marc"]["M980"]) )
              {
                foreach ( $this->one["marc"]["M980"] as $Rec )
                {
                  if ( isset($Rec["y"][0]) )  $Mans[] = $Rec["y"][0];
                }
              }
              if ( !$this->checkArrInWildArr($Mans, $M980Ys) )  $Match = false;
            }
             continue;
          }
          elseif ( substr($Filter,0,12) == "m.nomanfull." )
          {
            $M980Ys = explode(",",substr($Filter,12));
            if ( count($Item) )
            {
              
              $Man    = isset($Item["y"][0]) ? $Item["y"][0] : "";
              if ( $Man && $this->checkStrInWildArr($Man, $M980Ys) )   $Match = false;
            }
            else
            {
              
              $Mans = array();
               if ( isset($this->one["marc"]["M980"]) && is_array($this->one["marc"]["M980"]) )
              {
                foreach ( $this->one["marc"]["M980"] as $Rec )
                {
                  if ( isset($Rec["y"][0]) )  $Mans[] = $Rec["y"][0];
                }
              }
              if ( $this->checkArrInWildArr($Mans, $M980Ys) )  $Match = false;
            }
             continue;
          }

          
          
          
          
          elseif ( substr($Filter,0,6) == "m.enr." || substr($Filter,0,8) == "m.noenr." )
          {
            $Nein = ( substr($Filter,0,8) == "m.noenr." );
            $ENRs = explode(",", substr($Filter, $Nein ? 8 : 6));
            $ENR  = $this->itemENR($Item);
            if ( !count($Item) )  { $Match = false;  continue; }
            $Treffer = ( $ENR !== "" && $this->checkStrInWildArr($ENR, $ENRs) );
            if ( $Nein ? $Treffer : !$Treffer )  $Match = false;
            continue;
          }

          
          elseif ( substr($Filter,0,6) == "o.sig." )
          {
            
            
            

            if ( !isset($Item["d"][0]) )  {$Match = false;  continue; }

            if ( !$this->checkStrInWildArr($Item["d"][0], explode(",", substr($Filter,6))) ) $Match = false;
            continue;
          }
          elseif ( substr($Filter,0,8) == "o.nosig." )
          {
            if ( !isset($Item["d"][0]) )  {$Match = false;  continue; }

            if ( $this->checkStrInWildArr($Item["d"][0], explode(",", substr($Filter,8))) ) $Match = false;
            continue;
          }

          
          
          
          
          
          
          elseif ( substr($Filter,0,7) == "o.call." || substr($Filter,0,9) == "o.nocall." )
          {
            $Nein  = ( substr($Filter,0,9) == "o.nocall." );
            $Werte = explode(",", substr($Filter, $Nein ? 9 : 7));
            $Calls = ( isset($Item["call"]) && is_array($Item["call"]) ) ? $Item["call"] : array();

            $Treffer = false;
            foreach ( $Calls as $Call )
            {
              if ( $this->checkStrInWildArr($Call, $Werte) )  { $Treffer = true;  break; }
            }
            if ( $Nein ? $Treffer : !$Treffer )  $Match = false;
            continue;
          }

          
          elseif ( substr($Filter,0,6) == "o.col." )
          {
            
            $OneCols  = ( isset($this->one["collections"]) && is_array($this->one["collections"]) ) ? $this->one["collections"] : array();
            $Cols     = explode(",", substr($Filter,6));
            $ColFound = false;
            foreach ($Cols as $Col)
            {
              if ( in_array(strtolower($Col), $OneCols) )   $ColFound = true;
              if ( $ColFound) continue;
            }

            
            if ( $this->aps && $this->isHapticArticle() )
            {
              $Mama = "";
              
              if ( array_key_exists($this->id,$this->CS->hierarchy["childs"]) ) {
                $Mama = $this->CS->hierarchy["childs"][$this->id];
                $OneCols  = ( isset($this->CS->works["add"][$Mama]["collections"]) && is_array($this->CS->works["add"][$Mama]["collections"]) ) ? $this->CS->works["add"][$Mama]["collections"] : array();
                $Cols     = explode(",", substr($Filter,6));
                foreach ($Cols as $Col)
                {
                  if ( in_array(strtolower($Col), $OneCols) )   $ColFound = true;
                  if ( $ColFound) continue;
                }
              }
            }

            
            if ( !$ColFound )  $Match = false;
            continue;
          }

          
          elseif ( substr($Filter,0,6) == "o.loc." )
          {
            if ( !isset($Item["f"][0]) )  {$Match = false;  continue; }

            
            
            

            if ( !$this->checkStrInWildArr($Item["f"][0], explode(",", substr($Filter,6))) ) $Match = false;
            continue;
          }
          elseif ( substr($Filter,0,8) == "o.noloc." )
          {
            if ( !isset($Item["f"][0]) )  {$Match = false;  continue; }

            if ( $this->checkStrInWildArr($Item["f"][0], explode(",", substr($Filter,8))) ) $Match = false;
            continue;
          }

          
          elseif ( substr($Filter,0,8) == "o.locid." )
          {
            
            
            

            if ( !$Loc )  {$Match = false;  continue; }

            if ( !$this->checkStrInWildArr($Loc, explode(",", substr($Filter,8))) ) $Match = false;
            continue;
          }
          elseif ( substr($Filter,0,10) == "o.nolocid." )
          {
            if ( !$Loc )  { continue; }

            if ( $this->checkStrInWildArr($Loc, explode(",", substr($Filter,10))) ) $Match = false;
            continue;
          }

          
          elseif ( substr($Filter,0,9) == "o.allloc." )
          {
            
            
            

            $Locs = array();
            if ( isset($this->one["marc"]["M980"]) && is_array($this->one["marc"]["M980"]) )
            {
              foreach ( $this->one["marc"]["M980"] as $Rec )
              {
                if ( isset($Rec["f"][0]) )  $Locs[] = $Rec["f"][0];
              }
            }
            if ( !$this->awop && !$Locs && isset($this->CS->works["marc"][$ID]["marc"]["M980"])
            && is_array($this->CS->works["marc"][$ID]["marc"]["M980"]) && count($this->CS->works["marc"][$ID]["marc"]["M980"]) )
            {
              
              foreach ( $this->CS->works["marc"][$ID]["marc"]["M980"] as $Rec )
              {
                if ( isset($Rec["f"][0]) )  $Locs[] = $Rec["f"][0];
              }
            }

            if ( !is_array($Locs) || !count($Locs) )  { $Match = false;  continue; }

            if ( !$this->checkArrInWildArr($Locs, explode(",", substr($Filter,9))) ) $Match = false;
            continue;
          }
          elseif ( substr($Filter,0,11) == "o.allnoloc." )
          {
            $Locs = array();
            if ( isset($this->one["marc"]["M980"]) && is_array($this->one["marc"]["M980"]) )
            {
              foreach ( $this->one["marc"]["M980"] as $Rec )
              {
                if ( isset($Rec["f"][0]) )  $Locs[] = $Rec["f"][0];
              }
            }

            
            

            if ( !count($Locs) )  { continue; }


            if ( $this->checkArrInWildArr($Locs, array_map(array($this,"replacekeywords"), explode(",", substr($Filter,11)))) ) $Match = false;
            continue;
          }

          
          elseif ( substr($Filter,0,14) == "o.allotherloc." )
          {
            $Locs = array();
            if ( isset($this->one["marc"]["M980"]) && is_array($this->one["marc"]["M980"]) )
            {
              foreach ( $this->one["marc"]["M980"] as $Rec )
              {
                if ( isset($Rec["f"][0]) )  $Locs[] = $Rec["f"][0];
              }
            }

            $Other = explode(",", substr($Filter,14));
            if ( count($Other) && count($Locs) )
            {
              $Rest = $this->getArrReducedWildArr($Other, $Locs);
              if ( !count($Rest) )  $Match = false;
            }
            continue;
          }

          
          elseif ( substr($Filter,0,11) == "o.alllocid." )
          {
            
            

            if ( !is_array($this->one["locs"]) || !count($this->one["locs"]) )  { $Match = false;  continue; }

            if ( !$this->checkArrInWildArr($this->one["locs"], explode(",", substr($Filter,11))) ) $Match = false;
            continue;
          }
          elseif ( substr($Filter,0,13) == "o.allnolocid." )
          {
            if ( !is_array($this->one["locs"])  || !count($this->one["locs"]) )  { continue; }

            if ( $this->checkArrInWildArr($this->one["locs"], explode(",", substr($Filter,13))) ) $Match = false;
            continue;
          }

          
          elseif ( substr($Filter,0,13) == "o.otherlocid." )
          {
            $Other = explode(",", substr($Filter,13));
            if ( count($Other) && count($this->one["locs"]) )
            {
              $Rest = $this->getArrReducedWildArr($Other, $this->one["locs"]);
              if ( !count($Rest) )  $Match = false;
            }
            continue;
          }

          
          elseif ( substr($Filter,0,9) == "o.noetnr." )
          {
            if ( !is_array($this->casesetnr) || !count($this->casesetnr) )   { continue; }

            $Nos = explode(",", substr($Filter,9));
            if ( count($Nos) )
            {
              if ( $this->checkArrInWildArr(array_keys($this->casesetnr), $Nos) ) $Match = false;
            }
            continue;
          }

          
          elseif ( substr($Filter,0,12) == "o.otheretnr." )
          {
            if ( !is_array($this->casesetnr) || !count($this->casesetnr) )   { continue; }

            $Others = explode(",", substr($Filter,12));
            
            
            if ( count($Others) )
            {
              $Rest = $this->getArrReducedWildArr($Others, array_keys($this->casesetnr));
              
              if ( !count($Rest) )  $Match = false;
            }
            continue;
          }

          
          elseif ( substr($Filter,0,8) == "o.level." )
          {
            $Level = (isset($this->CS->posted["search"]["userlevel"]) && trim($this->CS->posted["search"]["userlevel"]))
                     ? trim($this->CS->posted["search"]["userlevel"])
                     : "";
            

            if ( !$this->checkArrInWildArr(array($Level), explode(",", substr($Filter,8))) ) $Match = false;
            continue;
          }

          
          elseif ( substr($Filter,0,9) == "o.ownorg." )
          {
            $OwnOrg = ( isset($this->one["ownorg"]) && trim($this->one["ownorg"]) ) ? trim($this->one["ownorg"]) : "";
            $M535As     = explode(",", substr($Filter,9));
            $M535AFound = false;
            foreach ($M535As as $M535A)
            {
              if ( substr($M535A, -1) == "*" )
              {
                $Pos = strpos($M535A, "*");
                if ( substr($M535A, 0, $Pos) == substr($OwnOrg,0, $Pos) ) $M535AFound = true;
              }
              else
              {
                if ( $M535A == $OwnOrg )   $M535AFound = true;
              }
              if ( $M535AFound) continue;
            }
            if ( !$M535AFound )  $Match = false;
            continue;
          }
          
          elseif ( substr($Filter,0,12) == "o.ipspecial.")
          {
            $IPs = explode(",", substr($Filter,12));
            $this->setLog("IP", "Check Client IP " . $this->CS->clientip);
            $Hit = false;
            foreach ($IPs as $OneRange)
            {
              if ( !trim($OneRange) ) continue;
              $Range = explode("-",$OneRange);
              if ( count($Range) != 2 ) continue;
               if ( ip2long($this->CS->clientip) >= ip2long($Range[0]) && ip2long($this->CS->clientip) <= ip2long($Range[1]) )
              {
                $this->setLog("IP", "Found ip in range " . implode("-", $Range));
                $Hit = true;
              }
            }
            if ( !$Hit )  $Match = false;
            continue;   
          }

          
          elseif ( substr($Filter,0,10) == "p.storage." )
          {
            if ( !isset($Item["storage"]) )
            {
              $Match = false;
            }
            else
            {
            
            
            
            
            
            if ( !$this->checkStrInWildArr($Item["storage"], explode(",", substr($Filter,10))) )  $Match = false;
            }
            continue;
          }
          elseif ( substr($Filter,0,12) == "p.nostorage." )
          {
            if ( isset($Item["storage"]) )
            {
            
            
            
            
            
            if ( $this->checkStrInWildArr($Item["storage"], explode(",", substr($Filter,12))) )  $Match = false;
            }
            continue;
          }

          
          elseif ( substr($Filter,0,12) == "p.storageid." )
          {
            $StorageIDFound = false;
            if ( !isset($Item["storageid"]) )
            {
              $Match = false;
            }
            else
            {
            
            
            
            
            
            if ( !$this->checkStrInWildArr($Item["storageid"], explode(",", substr($Filter,12))) )  $Match = false;
            }
            continue;
          }
          
          elseif ( substr($Filter,0,14) == "p.nostorageid." )
          {
            if ( isset($Item["storageid"]) )
            {
            
            
            if ( $this->checkStrInWildArr($Item["storageid"], explode(",", substr($Filter,14))) )  $Match = false;
            }
            continue;
          }

          
          elseif ( substr($Filter,0,15) == "p.departmentid." )
          {
            $DepartmentIDFound = false;
            if ( !isset($Item["departmentid"]) )
            {
              $Match = false;
            }
            else
            {
            
            
            if ( !$this->checkStrInWildArr($Item["departmentid"], explode(",", substr($Filter,15))) )  $Match = false;
            }
            continue;
          }
          
          elseif ( substr($Filter,0,17) == "p.nodepartmentid." )
          {
            if ( isset($Item["departmentid"]) )
            {
            
            
            if ( $this->checkStrInWildArr($Item["departmentid"], explode(",", substr($Filter,17))) )  $Match = false;
            }
            continue;
          }

          
          else
          {
            switch ( $Filter )
            {
              
              case "m.articles":
              {
                if ( !isset($this->one["artcode"]) || $this->one["artcode"] != "nnam" )  $Match = false;
                break;
              }
              case "m.articlez":
              {
                if ( !isset($this->one["artcode"]) || $this->one["artcode"] != "nnas" )  $Match = false;
                break;
              }
              case "m.band":
              {
                 
                 
                 
                 
                 
                 if ( !isset($this->one["marc"]["Lead"]) || !in_array(substr($this->one["marc"]["Lead"],19,1), array("b","c")) )    $Match = false;
                 break;
              }
              case "m.hasparent":
              {
                if ( !isset($this->one["rootid"]) || !$this->one["rootid"] ) $Match = false;
                break;
              }
              case "m.blsecondlevel":
              case "m.epn":
              {
                if ( !$this->epn )    $Match = false;
                break;
              }
              case "m.haptic":
              {
                if ( isset($this->one["online"]) && $this->one["online"] )  $Match = false;
                break;
              }
              case "m.nothaptic":
              {
                if ( isset($this->one["online"]) && !$this->one["online"] )  $Match = false;
                break;
              }
              case "m.noarticle":
              {
                if ( isset($this->one["formatcode"]) && in_array($this->one["formatcode"], array("article")) )  $Match = false;
                break;
              }
              case "m.nojournal":
              {
                if ( isset($this->one["formatcode"]) && in_array($this->one["formatcode"], array("journal")) )  $Match = false;
                break;
              }
              case "m.noonlineitem":
              {
                if ( isset($this->one["itemcount"]["online"]) && $this->one["itemcount"]["online"] )  $Match = false;
                break;
              }
              case "m.nophysicalitem":
              {
                if ( isset($this->one["itemcount"]["physical"]) && $this->one["itemcount"]["physical"] )  $Match = false;
                break;
              }
              case "m.noseries":
              {
                if ( isset($this->one["series"]) && $this->one["series"] )  $Match = false;
                break;
              }
              case "m.notowner":
              {
                
                
                
                
                
                $NotOwner    = true;
                if ( isset($this->one["owner"]) && $this->one["owner"] )  $NotOwner = false;

                
                if ( !$NotOwner )  $Match = false;
                break;
              }
              case "m.online":
              {
                if ( !isset($Item["link"]) || !trim($Item["link"]) ) $Match = false;
                break;
              }
              case "m.owncollection":
              {
                
                
                
                
                
                
                
                if ( ( !isset($this->one["owncollection"]) || !$this->one["owncollection"] ) && !$this->inLevelCollection() )  $Match = false;
                break ;
              }
              case "m.clausthaltu.openaccess":
              case "m.potsdamub.openaccess":
              case "m.openaccess":
              {
                if ( !isset($Item["oa"])  || !$Item["oa"] )   $Match = false;
                break;
              }
              case "m.owner":
              {
                
                $Owner    = true;
                if ( !isset($this->one["owner"]) || !$this->one["owner"] )  $Owner = false;

                if ( $this->aps && $this->isHapticArticle() )
                {
                  
                  $Mama = "";
                  if ( array_key_exists($this->id,$this->CS->hierarchy["childs"]) ) {
                    $Mama = $this->CS->hierarchy["childs"][$this->id];
                    if ( !isset($this->CS->works["add"][$Mama]["owner"]) || !$this->CS->works["add"][$Mama]["owner"] )  $Owner = false;
                  }
                }

                
                if ( !$Owner )  $Match = false;
                break;
              }
              case "m.master":
              {
                 if (!((isset($this->one["marc"]["Lead"]) && substr($this->one["marc"]["Lead"],7,1) == "s"
                     && isset($this->one["marc"]["M008"]) && in_array(substr($this->one["marc"]["M008"],21,1), array("n","p","|","#"," ")) )
                   || ( isset($this->one["marc"]["Lead"]) && substr($this->one["marc"]["Lead"], 7,1) == "m"
                                                          && substr($this->one["marc"]["Lead"],19,1) == "a" )
                   || ( isset($this->one["marc"]["Lead"]) && substr($this->one["marc"]["Lead"], 7,1) == "s"
                     && isset($this->one["marc"]["M008"]) && substr($this->one["marc"]["M008"],21,1) == "m" )))    $Match = false;
                 break;
              }
              case "m.masterlink":
              {
                 if ( !isset($this->one["masterlink"]) || !trim($this->one["masterlink"]) ) $Match = false;
                 break;
              }
              case "m.blnosecondlevel":
              case "m.noepn":
              {
                if ( $this->epn )    $Match = false;
                break;
              }
              case "m.physical": 
              {
                if ( isset($Item["link"]) && trim($Item["link"]) ) $Match = false;
                break;
              }
              case "m.remote":  
              case "m.globallink":
              {
                if ( !isset($Item["link"])
                  || (isset($Item["link"]) && !$Item["link"])
                  || (isset($Item["iln"])  &&  $Item["iln"]) )        $Match = false;
                break;
              }
              case "m.locallink":
              {
                if ( !isset($Item["link"])
                  || (isset($Item["link"]) && !$Item["link"])
                  || !isset($Item["iln"])
                  || !$Item["iln"] )        $Match = false;
                break;
              }
              case "m.nolocallink":
              {
                
                
                
                if ( isset($Item["link"]) && $Item["link"]
                  && isset($Item["iln"])  && $Item["iln"] )        $Match = false;
                break;
              }
              case "m.series":
              {
                if ( !isset($this->one["series"]) || !$this->one["series"] )  $Match = false;
                break;
              }
              
              
              
              
              
              case "m.haslocation":
              case "m.nolocation":
              {
                
                
                
                $Ort = isset($Item["f"][0]) ? trim($Item["f"][0]) : "";
                $Hat = ( $Ort !== "" && $Ort !== "--%%--" );
                if ( !count($Item) || isset($Item["link"]) || ( $Filter == "m.haslocation" ? !$Hat : $Hat ) )  $Match = false;
                break;
              }
              
              
              
              
              
              case "m.parentowner":
              case "m.noparentowner":
              {
                if ( !$this->pid )  { $Match = false;  break; }
                $Mama = isset($this->CS->works["add"][$this->pid]) ? $this->CS->works["add"][$this->pid] : array();
                $Hat  = ( isset($Mama["owner"]) && $Mama["owner"] );
                if ( $Filter == "m.parentowner" ? !$Hat : $Hat )  $Match = false;
                break;
              }
              
              
              
              
              
              
              
              
              
              
              
              
              
              case "m.parentwithitem":
              case "m.parentwithoutitem":
              {
                if ( !$this->pid )  { $Match = false;  break; }
                $PM980 = isset($this->CS->works["add"][$this->pid]["marc"]["PM980"]) && is_array($this->CS->works["add"][$this->pid]["marc"]["PM980"])
                           ? $this->CS->works["add"][$this->pid]["marc"]["PM980"] : array();
                $Hat   = ( count($PM980) > 0 );
                if ( $Filter == "m.parentwithitem" ? !$Hat : $Hat )  $Match = false;
                break;
              }
              case "m.special.journal":
              {
                 if ( !isset($this->one["marc"]["Lead"]) || substr($this->one["marc"]["Lead"],7,1) != "s"
                   || !isset($this->one["marc"]["M008"]) || !in_array(substr($this->one["marc"]["M008"],21,1), array("n","p","|","#"," ")) )    $Match = false;
                 break;
              }
              case "m.special.monographicmaster":
              {
                 if ( !isset($this->one["marc"]["Lead"]) || substr($this->one["marc"]["Lead"], 7,1) != "m"
                                                         || substr($this->one["marc"]["Lead"],19,1) != "a" )    $Match = false;
                 break;
              }
              case "m.special.monographicseries":
              {
                 if ( !isset($this->one["marc"]["Lead"]) || substr($this->one["marc"]["Lead"], 7,1) != "s"
                   || !isset($this->one["marc"]["M008"]) || substr($this->one["marc"]["M008"],21,1) != "m" )    $Match = false;
                 break;
              }

              
              case "p.action":
              {
                if ( ( !isset($Item["loanitems"][0])         || !count($Item["loanitems"][0])         || $Item["loanitems"][0]["action"]         == "-" )
                  && ( !isset($Item["presentationitems"][0]) || !count($Item["presentationitems"][0]) || $Item["presentationitems"][0]["action"] == "-" ) ) $Match = false;
                break;
              }
              case "p.actionorder":
              {
                if ( ( !isset($Item["loanitems"][0])         || !count($Item["loanitems"][0])         || $Item["loanitems"][0]["action"]         != "order" )
                  && ( !isset($Item["presentationitems"][0]) || !count($Item["presentationitems"][0]) || $Item["presentationitems"][0]["action"] != "order" ) ) $Match = false;
                break;
              }
              case "p.actionreserve":
              {
                if ( ( !isset($Item["loanitems"][0])         || !count($Item["loanitems"][0])         || $Item["loanitems"][0]["action"]         != "reserve" )
                  && ( !isset($Item["presentationitems"][0]) || !count($Item["presentationitems"][0]) || $Item["presentationitems"][0]["action"] != "reserve" ) ) $Match = false;
                break;
              }
              case "p.approval":
              {
                if ( !$this->isLimitation($Item, "ApprovalRequired") ) $Match = false;
                break;
              }
              case "p.bandlist":
              {
                if ( count($Item) )
                {
                  if ( !isset($Item["bandlist"]) || !$Item["bandlist"] )  $Match = false;
                }
                else
                 {
                   if ( isset($this->CS->paias[$this->id]) && is_array($this->CS->paias[$this->id]) && count($this->CS->paias[$this->id]) )
                   {
                     foreach ( $this->CS->paias[$this->id] as $PI )
                     {
                       if ( !isset($PI["bandlist"]) || !$PI["bandlist"] )
                       {
                         $Match = false;
                         break;
                       }
                     }
                   }
                   
                }
                break;
              }
              case "p.expected":
              {
                if ( !$EXPD )  $Match = false;
                break;
              }
              case "p.firstband":
              {
                if ( isset($Item["count"]) && $Item["count"] > 0 )  $Match = false;
                break;
              }
              case "p.loan":
              {
                if ( !$LOAN )  $Match = false;
                break;
              }
              case "p.noaction":
              {
                if ( ( isset($Item["loanitems"][0])         && count($Item["loanitems"][0])         && $Item["loanitems"][0]["action"]         != "-" )
                  || ( isset($Item["presentationitems"][0]) && count($Item["presentationitems"][0]) && $Item["presentationitems"][0]["action"] != "-" ) )       $Match = false;
                break;
              }
              case "p.noapproval":
              {
                if ( $this->isLimitation($Item, "ApprovalRequired") ) $Match = false;
                break;
              }
              case "p.nobandlist":
              {
                if ( count($Item) )
                {
                  if ( isset($Item["bandlist"]) && $Item["bandlist"] )  $Match = false;
                }
                else
                 {
                   
                   if ( isset($this->CS->paias[$this->id]) && is_array($this->CS->paias[$this->id]) && count($this->CS->paias[$this->id]) )
                   {
                     $Tmp = true;
                     foreach ( $this->CS->paias[$this->id] as $PI )
                     {
                       if ( isset($PI["bandlist"]) && !$PI["bandlist"] )
                       {
                         $Tmp = false;
                         break;
                       }
                     }
                    if ( $Tmp )  $Match = false;
                   }
                }
                break;
              }
              case "p.nofirstband":
              {
                if ( !isset($Item["count"]) || $Item["count"] == 0 )  $Match = false;
                break;
              }
              case "p.noloan":
              {
                if ( $LOAN )  $Match = false;
                break;
              }
              case "p.nopaia":
              {
                
                
                if ( $this->hasPaia($Item) )  $Match = false;
                break;
              }
              case "p.noshortloan":
              {
                if ( $this->isLimitation($Item, "ShortLoan") ) $Match = false;
                break;
              }
              case "p.nostationary":
              {
                if ( $this->isLimitation($Item, "Stationary") ) $Match = false;
                break;
              }
              case "p.paia":
              {
                
                
                if ( !$this->hasPaia($Item) )  $Match = false;
                break;
              }
              case "p.pres":
              {
                if ( !$PRES )  $Match = false;
                break;
              }
              case "p.nopres":
              {
                if ( $PRES )  $Match = false;
                break;
              }
              case "p.queuefull":
              {
                if ( !isset($this->CS->configs["bms"]["maxqueue"]) || $QUEU == "-" || $QUEU < $this->CS->configs["bms"]["maxqueue"] )   $Match = false;
                break;
              }
              case "p.queuenullnoexpected":
              {
                if ( $QUEU != "0" || $EXPD ) $Match = false;
                break;
              }
              case "p.shortloan":
              {
                if ( !$this->isLimitation($Item, "ShortLoan") ) $Match = false;
                break;
              }
              case "p.stationary":
              {
                if ( !$this->isLimitation($Item, "Stationary") ) $Match = false;
                break;
              }

              
              case "o.ip":
              {
                if ( !isset($this->CS->configs["init"]["ipranges"]) || !trim($this->CS->configs["init"]["ipranges"]) )    $Match = false;
                $IPs = explode(PHP_EOL, $this->CS->configs["init"]["ipranges"]);
                $this->setLog("IP", "Check Client IP " . $this->CS->clientip);
                $Hit = false;
                foreach ($IPs as $OneRange)
                {
                  if ( !trim($OneRange) ) continue;
                  $Range = explode("-",$OneRange);
                  if ( count($Range) != 2 ) continue;
                   if ( ip2long($this->CS->clientip) >= ip2long($Range[0]) && ip2long($this->CS->clientip) <= ip2long($Range[1]) )
                  {
                    $this->setLog("IP", "Found ip in range " . implode("-", $Range));
                    $Hit = true;
                  }
                }
                if ( !$Hit )  $Match = false;
                break;
              }
              case "o.hannovergwlb.about.lost":
              {
                if ( !isset($Item["about"]) || strripos($Item["about"], "vermisst") === false )    $Match = false;
                break;
              }
              case "m.hallelit.mirador":
              {
                if ( !isset($Item["text"]) || $Item["text"]  != "Mirador" )   $Match = false;
                break;
              }
              case "o.rostockub.emailcld":
              {
                

                
                $Params = array("iln"     => "62",
                                "format"  => "json");
                if ( isset($Item["d"][0]) && trim($Item["d"][0]) )  $Params["sgn"] = trim($Item["d"][0]);
                if ( isset($Item["e"][0]) && trim($Item["e"][0]) )  $Params["aui"] = trim($Item["e"][0]);
                if ( isset($Item["f"][0]) && trim($Item["f"][0]) )  $Params["sst"] = trim($Item["f"][0]);

                

                
                $File = $this->CS->prepareCachePath(array("sstmap"), preg_replace("/[^a-zA-Z0-9]+/", "", implode("", $Params)));
                if ( file_exists($File) )
                {
                  $AddInfos = json_decode(file_get_contents($File),true);
                  
                }
                if ( !file_exists($File) || !isset($AddInfos["item"]["emailcld"]) || !trim($AddInfos["item"]["emailcld"]) ) $Match = false;
                break;
              }
              case "o.rostockub.emailscd":
              {
                

                
                $Params = array("iln"     => "62",
                                "format"  => "json");
                if ( isset($Item["f"][0]) && trim($Item["f"][0]) )  $Params["sst"] = trim($Item["f"][0]);
                if ( isset($Item["d"][0]) && trim($Item["d"][0]) )  $Params["sgn"] = trim($Item["d"][0]);
                if ( isset($Item["e"][0]) && trim($Item["e"][0]) )  $Params["aui"] = trim($Item["e"][0]);

                

                
                $File = $this->CS->prepareCachePath(array("sstmap"), preg_replace("/[^a-zA-Z0-9]+/", "", implode("", $Params)));
                if ( file_exists($File) )
                {
                  $AddInfos = json_decode(file_get_contents($File),true);
                  
                }
                if ( !file_exists($File) || !isset($AddInfos["item"]["emailscd"]) || !trim($AddInfos["item"]["emailscd"]) ) $Match = false;
                break;
              }

              default:
              {
                $this->setLog("ERROR", "Filter " . $Filter . " not found !");
                $Match = false;
                $Unbekannt = true;
              }
            }
          }
          } while ( false );

          
          
          if ( $Offen )
          {
            $Match = $Unbekannt ? false : ( $VorMatch && !$Match );
            $Offen = false;
          }
        }

        if ( $Match )
        {
          $Hits[] = array("SL" => $SortLevel,
                          "CI" => $CI);
          $this->setLog("Case", "Found " . $SortLevel . "." . $Case["name"]);
          if ( isset($this->casesetnr[$SortLevel]) )
          {
            $this->casesetnr[$SortLevel]++;
          }
          else
          {
            $this->casesetnr[$SortLevel] = 1;
          }
          $OldSortLevel = $SortLevel;
        }
      }
    }

    while ( count($Hits) > 0 )
    {
      
      $Exemplar = array();
      $Hit      = array_shift($Hits);
      $Case     = (isset($ScopeCases[$Hit["SL"]][$Hit["CI"]])) ? $ScopeCases[$Hit["SL"]][$Hit["CI"]] : array();

      
      $Exemplar["action"] = $this->getActionElements($Case, $Item);

      if ( isset($Exemplar["action"]["type"]) && $Exemplar["action"]["type"] == "skip" )  continue;

      
      
      
      
      if ( isset($Exemplar["action"]["button"]) && trim($Exemplar["action"]["button"]) !== "" )
        $Exemplar["action"]["label"] = $this->CS->getText($Exemplar["action"]["button"]);

      

       
      if ( isset($Case["itembutton"]) )       $Exemplar["short"]  = array("code"  => $Case["itembutton"],
                                                                          "label" => $this->CS->getText($Case["itembutton"]),
                                                                          "icon"  => $Case["itemicon"],
                                                                          "color" => $Case["light"]);


      
      if ( isset($Case["itemcomments"]) && is_array($Case["itemcomments"]) && count($Case["itemcomments"]) )
      {
        $Exemplar["comments"] = $this->getDisplayElements($Case["itemcomments"], $Item);
        
      }
      else
      {
        
        $Exemplar["comments"] = array("g" => array(),
                                      "e" => array());
      }
      if ( isset($Exemplar["action"][0]["link"]["label"]) )
      {
        $Exemplar["comments"]["g"] = [...$Exemplar["comments"]["g"],
                                      ...[["type" => "text", "text" => $Exemplar["action"][0]["link"]["label"]]]];
        $Exemplar["comments"]["e"] = [...$Exemplar["comments"]["e"],
                                      ...[["type" => "text", "text" => $Exemplar["action"][0]["link"]["label"]]]];
      }
      

      
      if ( isset($Case["detailinfos"]) && is_array($Case["detailinfos"]) && count($Case["detailinfos"]) )
      {
        if ( !$this->epn )
        {
          $Exemplar["view"] = $this->getDisplayElements(array_merge(array("h.item.item"), $Case["detailinfos"]), $Item);
         }
        else
        {
          $Exemplar["view"] = $this->getDisplayElements(array_merge(array("h.bibline.volume"), $Case["detailinfos"]), $Item);
        }
      }

      
      $Exemplar["meta"]              = array("case"       => $Hit["SL"] . "." .  $Case["name"],
                                             "window"     => $Case["detailwindow"] == 'y' ? true : false,
                                             "locid"      => $Loc,
                                             "author"     => $this->CS->getMarcText($ID, "bib_04"),
                                             "comment"    => $this->CS->getUniqueMultArray($this->comment),
                                             "location"   => $this->CS->getUniqueMultArray($this->location),
                                             "pagefromto" => $this->getPage(),
                                             "signature"  => $this->CS->getUniqueMultArray($this->signature),
                                             "stock"      => $this->CS->getUniqueMultArray($this->stock),
                                             "title"      => $this->CS->getMarcText($ID, "bib_01"),
                                             "volume"     => $this->getVolume());

      if ( isset($this->CS->configs["other"]["delivery.type"]) && $this->CS->configs["other"]["delivery.type"] == "mail" )
      {
        
         
         
      }


      if ( isset($Exemplar["short"]["code"]) && $Exemplar["short"]["code"] == "itembutton.bandlist" )
      {
        $Exemplar["meta"]["dialog"] = "bandlist";
        $Exemplar["meta"]["epn"]    = isset($Item["b"][0]) ? $Item["b"][0] : "";
      }
      elseif ( isset($Exemplar["action"]["type"]) && $Exemplar["action"]["type"] == "delivery" )
      {
        $Exemplar["meta"]["dialog"] = $this->CS->conf("other", "delivery.dialog", "delivery");
      }
      elseif ( isset($Exemplar["action"]["type"]) && $Exemplar["action"]["type"] == "dialog" )
      {
        $Exemplar["meta"]["dialog"] = "dialog";
      }
      elseif ( isset($Exemplar["action"]["type"]) && substr($Exemplar["action"]["type"],0,4) == "mail" )
      {
        $Exemplar["meta"]["dialog"] = "library";
      }
      else
      {
        $Exemplar["meta"]["dialog"] = "default";
      }

      
      if ( !isset($Exemplar["view"]) )  $Exemplar["view"] = array("g" => array(),
                                                                  "e" => array());
      if ( !isset($Exemplar["view"]["g"]) ) $Exemplar["view"]["g"] = array();
      if ( !isset($Exemplar["view"]["e"]) ) $Exemplar["view"]["e"] = array();


      
      if ( count($Item) && isset($this->CS->locations["comment"][$Loc]) )
      {
        if ( isset($this->CS->locations["comment"][$Loc]["g"]) && is_array($this->CS->locations["comment"][$Loc]["g"])
          && count($this->CS->locations["comment"][$Loc]["g"]) )
        {
          $Exemplar["comments"]["g"] = [...$Exemplar["comments"]["g"],
                                        ...$this->CS->locations["comment"][$Loc]["g"]];
        }
        if ( isset($this->CS->locations["comment"][$Loc]["e"]) && is_array($this->CS->locations["comment"][$Loc]["e"])
          && count($this->CS->locations["comment"][$Loc]["e"]) )
        {
          $Exemplar["comments"]["e"] = [...$Exemplar["comments"]["e"],
                                        ...$this->CS->locations["comment"][$Loc]["e"]];
        }

        

        foreach ($Exemplar["comments"]["g"] as &$v)
        {
          if ( isset($v["type"]) && $v["type"] == "link" && isset($v["link"]) && $v["link"] != "" )
          {
            $v["link"] = $this->repairLink($v["link"], $Item);
          }
        }
        foreach ($Exemplar["comments"]["e"] as &$v)
        {
          if ( isset($v["type"]) && $v["type"] == "link" && isset($v["link"]) && $v["link"] != "" )
          {
            $v["link"] = $this->repairLink($v["link"], $Item);
          }
        }
        
      }
      

      
      if ( count($Item) && isset($this->CS->locations["view"][$Loc]) )
      {
        if ( !isset($this->CS->locations["view"][$Loc]["g"]) )  $this->CS->locations["view"][$Loc]["g"] = array();
        if ( !isset($this->CS->locations["view"][$Loc]["e"]) )  $this->CS->locations["view"][$Loc]["e"] = array();
        $Exemplar["view"]["g"] = (isset($Exemplar["view"]["g"])) ? [...$Exemplar["view"]["g"], ...$this->CS->locations["view"][$Loc]["g"]]
                                                                 : $this->CS->locations["view"][$Loc]["g"];
        $Exemplar["view"]["e"] = (isset($Exemplar["view"]["e"])) ? [...$Exemplar["view"]["e"], ...$this->CS->locations["view"][$Loc]["e"]]
                                                                 : $this->CS->locations["view"][$Loc]["e"];
        foreach ($Exemplar["view"]["g"] as &$v)
        {
          if ( isset($v["type"]) && $v["type"] == "link" && isset($v["link"]) && $v["link"] != "" )
          {
            if (  stripos($v["link"], "<M980.d>") !== false )
            {
              $Fill = isset($Item["d"][0]) && trim($Item["d"][0]) && $Item["d"][0] != "--%%--" ? $Item["d"][0] : "";
              
              $Switch = $this->CS->confPairs("item", "locationmap");
              if ( array_key_exists($Fill, $Switch) )  $Fill = $Switch[$Fill];
              $v["link"] = str_ireplace("<M980.d>", urlencode($Fill), $v["link"]);
            }
            if (  stripos($v["link"], "<M980.f>") !== false )
            {
              $Fill = isset($Item["f"][0]) && trim($Item["f"][0]) && $Item["f"][0] != "--%%--" ? $Item["f"][0] : "";
              $v["link"] = str_ireplace("<M980.f>", urlencode($Fill), $v["link"]);
            }
          }
        }
        foreach ($Exemplar["view"]["e"] as &$v)
        {
          if ( isset($v["type"]) && $v["type"] == "link" && isset($v["link"]) && $v["link"] != "" )
          {
            if (  stripos($v["link"], "<M980.d>") !== false )
            {
              $Fill = isset($Item["d"][0]) && trim($Item["d"][0]) && $Item["d"][0] != "--%%--" ? $Item["d"][0] : "";
              
              $Switch = $this->CS->confPairs("item", "locationmap");
              if ( array_key_exists($Fill, $Switch) )  $Fill = $Switch[$Fill];
              $v["link"] = str_ireplace("<M980.d>", urlencode($Fill), $v["link"]);
            }
            if (  stripos($v["link"], "<M980.f>") !== false )
            {
              $Fill = isset($Item["f"][0]) && trim($Item["f"][0]) && $Item["f"][0] != "--%%--" ? $Item["f"][0] : "";
              $v["link"] = str_ireplace("<M980.f>", urlencode($Fill), $v["link"]);
            }
          }
        }
      }

      
      

      
      if ( count($Item) )
      {
        $DesksUsers = $this->addDeskUsers($Loc, $Item, $Exemplar);

        
        
        
        
        
        
        $RawDesks = isset($DesksUsers["desks"]) ? $DesksUsers["desks"] : array();
        $Exemplar["desks"] = array();
        foreach ( $RawDesks as $DID )
        {
          $Lbl = $this->CS->getText("location.desk." . $DID);
          $Exemplar["desks"][] = array("value" => $DID, "label" => ($Lbl !== "" ? $Lbl : $DID));
        }
        $Exemplar["users"] = isset($DesksUsers["users"]) ? $DesksUsers["users"] : array();
        if ( count($Exemplar["desks"]) )
        {
          $Exemplar["deskprompt"] = $this->CS->getText("selectdesk");
          $Exemplar["desklabel"]  = $this->CS->getText("desk");
        }
      }

      
      
      
      
      
      
      
      
      
      
      $ActType = isset($Exemplar["action"]["type"])   ? $Exemplar["action"]["type"]   : "";
      $ActDlg  = isset($Exemplar["action"]["dialog"]) ? $Exemplar["action"]["dialog"] : "";
      if ( ( !isset($Exemplar["users"]) || !count($Exemplar["users"]) )
        && isset($this->CS->configs["item"]["delivery.usertypes"]) && trim($this->CS->configs["item"]["delivery.usertypes"])
        && ( $ActType == "delivery"
          || ( $ActType == "dialog" && in_array($ActDlg, array("delivery", "delivery2")) ) ) )
      {
       $Exemplar["users"] = explode(",", $this->CS->configs["item"]["delivery.usertypes"]);
      }

      
      $this->getItemDataLocal($Item, $Exemplar);

      
      $Exemplar["light"] = (isset($Case["light"]) && $Case["light"]) ? $Case["light"] : "red";
      $Blue2Green        = (isset($this->CS->configs["item"]["statusblueasgreen"]) && $this->CS->configs["item"]["statusblueasgreen"] == "y") ? true : false;
      if ( in_array($this->best, array("red"))                   && in_array($this->normLight($Exemplar["light"]), $this->lightsblue) )   $this->best = $Blue2Green ? "green" : "blue";
      if ( in_array($this->best, array("red", "blue"))           && in_array($this->normLight($Exemplar["light"]), $this->lightsyellow) ) $this->best = "orange";
      if ( in_array($this->best, array("red", "blue", "orange")) && in_array($this->normLight($Exemplar["light"]), $this->lightsgreen) )  $this->best = "green";
      $Exps[] = $Exemplar;
    }
  }

  private function setMoreGroups()
  {
    $RTRN = array();

    if ( isset($this->CS->works["marc"][$this->id]["searches"]) && count($this->CS->works["marc"][$this->id]["searches"]) )
    {
      $Color = isset($this->CS->configs["item"]["search.color"]) && trim($this->CS->configs["item"]["search.color"]) ? $this->CS->configs["item"]["search.color"] : "green";
      $S     = array();
      foreach ( $this->CS->works["marc"][$this->id]["searches"] as $SID => $Search )
      {
        $S[] = array("short"    => array("code"  => "itembutton.openedition",
                                         "label" => $this->CS->getText("itembutton.openedition"),
                                         "icon"  => "mdi-magnify",
                                         "color" => $Color),
                     "comments" => array("g" => array(array("type" => "text", "text" => $Search["text"])),
                                         "e" => array(array("type" => "text", "text" => $Search["text"]))),
                     "light"    => "blue",
                     "action"   => array("type"   => "search",
                                         "search" => array("query" => $Search["search"],
                                                           "type"  => "lukida")),
                     "view"     => array(),
                     "users"    => array(),
                     "desks"    => array(),
                     "meta"     => array("case"   => "Search brothers and sisters",
                                         "locid"  => "search." . $SID,
                                         "window" => false));
      }

      if ( count($S) )  $RTRN["searches"] = $S;
    }

    if ( isset($this->CS->works["marc"][$this->id]["links"]) && count($this->CS->works["marc"][$this->id]["links"]) )
    {
      $Color = isset($this->CS->configs["item"]["link.color"]) && trim($this->CS->configs["item"]["link.color"]) ? $this->CS->configs["item"]["link.color"] : "green";
      $L     = array();
      foreach ( $this->CS->works["marc"][$this->id]["links"] as $SID => $Link )
      {
        $L[] = array("short"    => array("code"  => "itembutton.openedition",
                                         "label" => $this->CS->getText("itembutton.openedition"),
                                         "icon"  => "mdi-link",
                                         "color" => $Color),
                     "comments" => array("g" => array(array("type" => "text", "text" => $Link["text"])),
                                         "e" => array(array("type" => "text", "text" => $Link["text"]))),
                     "light"    => "blue",
                     "action"   => array("type"   => "link",
                                         "link"   => array("url"    => $Link["link"],
                                                           "label"  => "",
                                                           "target" => "_blank")),
                     "view"     => array(),
                     "users"    => array(),
                     "desks"    => array(),
                     "meta"     => array("case"   => "Link to brothers and sisters",
                                         "locid"  => "link." . $SID,
                                         "window" => false));
      }

      if ( count($L) )  $RTRN["links"] = $L;
    }
    return $RTRN;
  }

  
  
  


  
  
  
  public function getItems($ID, $EPN = "")
  {
    if ( APPDEBUG["FKT"] ) $this->CS->debug("FKT",__METHOD__);

    
    
    

    $this->id        = $ID;
    $this->epn       = $EPN;
    $this->one       = &$this->CS->works["marc"][$ID];
    $this->pid       = isset($this->one["rootid"]) ? $this->one["rootid"] : "";
    $this->casesetnr = array();
    $this->comment   = array();
    $this->location  = array();
    $this->signature = array();
    $this->stock     = array();
    $this->allcomment   = array();
    $this->alllocation  = array();
    $this->allsignature = array();
    $this->allstock     = array();

    
    

    
    $this->setCombs();

    
    return $this->setItems();
  }

  
  
  
}

?>
