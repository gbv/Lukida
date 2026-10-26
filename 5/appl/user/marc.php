<?php

class marc
{
  private $add;
  private $ArtCode;
  private $ArticleLink;
  private $Client;
  private $ColDetails;
  private $Collection;
  private $Comb;
  private $Convert;
  private $Counts;
  private $CS;
  private $Display;
  private $Fields;
  private $Format;
  private $ID;
  private $ILNGroups;
  private $Items;
  private $lang;
  private $Links;
  private $MasterLink;
  private $SolrOne;
  private $MItems;
  private $MultiItems;
  private $Online;
  private $OriginalYear;
  private $OwnCollection;
  private $Owner;
  private $OwnOrg;
  private $parent;
  private $Parents;
  private $Prefix;
  private $Searches;
  private $Series;
  private $Year;

  public function __construct($CS)
  {
    $this->CS     = $CS;

    $this->add    = false;
    $this->Client = $CS->client;
    $this->lang   = $CS->lang;
    $this->parent = false;

    if ( APPDEBUG["FKT"] ) $this->CS->debug("FKT",__METHOD__);
  }

  private function addOriginalCharacters(&$Fields)
  {
    if ( APPDEBUG["FKT"] ) $this->CS->debug("FKT",__METHOD__);

    $Intl = $this->getCompleteArray(array($this->Prefix . "880" => array(" 0","4","6","a","b","c","e","n","v")));

    foreach($Intl as $Rec)
    {
      $Simple = array();
      foreach ( $Rec as $Key => $Values )
      {
        foreach ( $Values as $Value )
        {
          if ( !isset($Simple[$Key]) ) $Simple[$Key] = $Value;
        }
      }

      if ( isset($Simple["6"]) && substr($Simple["6"],0,3) == "245" )
      {
        $Tmp = "";
        if ( isset($Simple["a"]) && $Simple["a"] != "" && isset($Simple["b"]) && $Simple["b"] != "" )       $Tmp  = $Simple["a"] . " : " . $Simple["b"];
        if ( isset($Simple["a"]) && $Simple["a"] != "" && (!isset($Simple["b"]) || $Simple["b"] == "" ) )   $Tmp  = $Simple["a"];
        if ( ( !isset($Simple["a"]) || $Simple["a"] == "" ) && isset($Simple["b"]) && $Simple["b"] != "" )  $Tmp  = $Simple["b"];
        if ( isset($Simple["c"]) && $Simple["c"] != "" )                                                    $Tmp .= " / " . $Simple["c"];
        $Pos  = (isset($Fields["bib_01"]["items"]) && is_array($Fields["bib_01"]["items"])) ? count($Fields["bib_01"]["items"]) : 0;
        if ( !isset($Fields["bib_01"]["items"]) ) $Fields["bib_01"]["items"] = array();
        $Fields["bib_01"]["items"]["item_".++$Pos] = array("display" => "newline");
        $Fields["bib_01"]["items"]["item_".++$Pos] = array("text" => $Tmp, "action" => array());

        if ( isset($Simple["n"]) && $Simple["n"] != "" )
        {
           $Tmp2 = $Simple["n"];
          $Pos2  = (isset($Fields["bib_03"]["items"]) && is_array($Fields["bib_03"]["items"])) ? count($Fields["bib_03"]["items"]) : 0;
          if ( !isset($Fields["bib_03"]["items"]) ) $Fields["bib_03"]["items"] = array();
          $Fields["bib_03"]["items"]["item_".++$Pos2] = array("text" => $Tmp2, "action" => array());
        }
      }

      if ( isset($Simple["6"]) && substr($Simple["6"],0,3) == "246" )
      {
        $Tmp = "";
        if ( isset($Simple["a"]) && $Simple["a"] != "" && isset($Simple["b"]) && $Simple["b"] != "" )       $Tmp  = $Simple["a"] . " : " . $Simple["b"];
        if ( isset($Simple["a"]) && $Simple["a"] != "" && (!isset($Simple["b"]) || $Simple["b"] == "" ) )   $Tmp  = $Simple["a"];
        if ( ( !isset($Simple["a"]) || $Simple["a"] == "" ) && isset($Simple["b"]) && $Simple["b"] != "" )  $Tmp  = $Simple["b"];
        if ( isset($Simple["c"]) && $Simple["c"] != "" )                                                    $Tmp .= " " . $Simple["c"];
        $Pos  = (isset($Fields["bib_42"]["items"]) && is_array($Fields["bib_42"]["items"])) ? count($Fields["bib_42"]["items"]) : 0;
        if ( !isset($Fields["bib_42"]["items"]) ) $Fields["bib_42"]["items"] = array();
        $Fields["bib_42"]["items"]["item_".++$Pos] = array("text" => $Tmp, "action" => array());
      }

      if ( isset($Simple["6"]) && substr($Simple["6"],0,3) == "490" )
      {
        $Tmp = "";
        if ( isset($Simple["a"]) && $Simple["a"] != "" )  $Tmp .= $Simple["a"];
        $Pos  = (isset($Fields["bib_08"]["items"]) && is_array($Fields["bib_08"]["items"])) ? count($Fields["bib_08"]["items"]) : 0;
        if ( !isset($Fields["bib_08"]["items"]) ) $Fields["bib_08"]["items"] = array();
        $Fields["bib_08"]["items"]["item_".++$Pos] = array("display" => "newline");
        $Fields["bib_08"]["items"]["item_".++$Pos] = array("text" => $Tmp,
                                                           "action" => array("type"   => "search",
                                                                             "search" => array(
                                                                                               "query" => "series(" . $Simple["a"] . ")",
                                                                                               "type"  => "lukida"
                                                                                              )));
        $Tmp = "";
        if ( isset($Simple["v"]) && $Simple["v"] != "" )  $Tmp .= $Simple["v"];
        $Fields["bib_08"]["items"]["item_".++$Pos] = array("text"   => $Tmp,
                                                           "action" => array());
      }

      if ( isset($Simple["6"]) && substr($Simple["6"],0,3) == "250" && isset($Simple["a"]) )
      {
        $Pos  = (isset($Fields["bib_10"]["items"]) && is_array($Fields["bib_10"]["items"])) ? count($Fields["bib_10"]["items"]) : 0;
        if ( !isset($Fields["bib_10"]["items"]) ) $Fields["bib_10"]["items"] = array();
        $Fields["bib_10"]["items"]["item_".++$Pos] = array("text" => $Simple["a"], "action" => array());
      }
    }
    return $Fields;
  }

  private function clear($Str)
  {
    if ( APPDEBUG["FKT"] ) $this->CS->debug("FKT",__METHOD__);

    return "\"" . trim($Str ?? "") . "\"";
  }

  private function formatAddInfo($Seq, $Label)
  {
    if ( APPDEBUG["FKT"] ) $this->CS->debug("FKT",__METHOD__);

    $Item = 0;
    $Retn = array();

    $Recs = $this->getCompleteArray(array($this->Prefix . "856" => array("3","t","u","x","y","z")));

    foreach ( $Recs as $Rec )
    {
      $Link  = $this->getSFTexts($Rec, "u");
      if ( !$Link ) continue;

      if ( $Item & count($Recs) ) $Retn["item_".++$Item]     = array("display" => "newline");

      $Add  = array();
      $Add[] = $this->getSFTexts($Rec, "3");
      $Add[] = $this->getSFTexts($Rec, "t");
      $Add[] = $this->getSFTexts($Rec, "x");
      $Add[] = $this->getSFTexts($Rec, "y");
      $Add[] = $this->getSFTexts($Rec, "z");
      $Add   = array_filter($Add);

      $Retn["item_".++$Item]    = array("text"   => (count($Add) ? implode(", ", $Add) : "Online") . " (" . $this->CS->getHost($Link) . ")",
                                        "action" => array(
                                                          "type"   => "link",
                                                          "link"    => array(
                                                                             "url"    => $Link,
                                                                             "target" => "_blank"
                                                                            )
                                                         ),
                                        "more"   => array()
                                       );
    }

    $Returns = array();
    if ( !empty($Retn) )
    {
      $Returns["bib_".$Seq]     = array(
                                       "label"  => $this->CS->getText("bibline.".$Label),
                                       "code"   => $Label,
                                       "items"  => $Retn
                                      );
    }
    return $Returns;
  }

  private function formatCits($Seq, $Label)
  {
    if ( APPDEBUG["FKT"] ) $this->CS->debug("FKT",__METHOD__);

    $Item = 0;
    $Retn = array();

    $Recs = $this->getCompleteArray(array($this->Prefix . "510" => array("a")));

    $Add  = array();
    foreach ( $Recs as $Rec )
    {
      $Add[] = $this->getSFTexts($Rec, "a");
    }

    $Add = array_filter($Add);
    if ( !count( $Add) ) return array();

    $Retn["item_".++$Item] = array("text"   => implode(" | ", $Add),
                                   "action" => array(),
                                   "more"   => array());

    $Returns = array();
    if ( !empty($Retn) )
    {
      $Returns["bib_".$Seq]     = array(
                                       "label"  => $this->CS->getText("bibline.".$Label),
                                       "code"   => $Label,
                                       "items"  => $Retn
                                      );
    }
    return $Returns;
  }

  private function formatAddTitl($Seq, $Label)
  {
    if ( APPDEBUG["FKT"] ) $this->CS->debug("FKT",__METHOD__);

    $Item = 0;
    $Retn = array();

    $Recs = $this->getCompleteArray(array($this->Prefix . "246" => array("I1","a","b","i")));

    foreach ( $Recs as $Rec )
    {
      if ( !isset($Rec["I1"]["0"]) || $Rec["I1"]["0"] != "1" ) continue;
      if ( !isset($Rec["a"]["0"]) ) continue;

      if ( $Item ) $Retn["item_".++$Item]     = array("display" => "newline");

      $Add  = array();
      $Add[] = $this->getSFTexts($Rec, "a");
      $Add[] = $this->getSFTexts($Rec, "b", ": ");
      $Add[] = $this->getSFTexts($Rec, "i", " (", ")");
      $Add   = array_filter($Add);

      $Retn["item_".++$Item]  = array("text"   => implode("", $Add),
                                      "action" => array(),
                                      "more"   => array()
                                     );
    }

    $Returns = array();
    if ( !empty($Retn) )
    {
      $Returns["bib_".$Seq]     = array(
                                       "label"  => $this->CS->getText("bibline.".$Label),
                                       "code"   => $Label,
                                       "items"  => $Retn
                                      );
    }
    return $Returns;
  }

  private function formatFormat($Seq, $Label)
  {
    if ( APPDEBUG["FKT"] ) $this->CS->debug("FKT",__METHOD__);

    $Retn["item_1"] = array("text"   => $this->CS->getText("format." . str_replace(" ", "", strtolower($this->Format))),
                            "action" => array(),
                            "more"   => array());

    $Returns["bib_".$Seq]     = array(
                                       "label"  => $this->CS->getText("bibline.".$Label),
                                       "code"   => $Label,
                                       "items"  => $Retn
                                      );
    return $Returns;
  }

  private function formatClass($Seq, $Label)
  {
    if ( APPDEBUG["FKT"] ) $this->CS->debug("FKT",__METHOD__);

    $Recs = $this->getCompleteArray(array($this->Prefix . "084" => array("2","a")));
    $Item = 0;
    $Clas = array();
    foreach ( $Recs as $Rec )
    {
      if ( !isset($Rec["a"]["0"]) || !isset($Rec["2"]["0"]) ) continue;

      $CSys = strtoupper($Rec["2"]["0"]);
      foreach ( $Rec["a"] as $Code )
      {
        $CRec = $this->CS->getCDBClass($CSys, $Code);
        if ( isset($CRec["link"]) && trim($CRec["link"]) )
        {
          $CRec["link"] = str_replace("{class}", $Code, $CRec["link"]);
        }

        if ( isset($CRec["description"]) )
        {
          $Clas["item_".++$Item]  = array("text"   => $CRec["description"],
                                          "code"   => $CSys . " " . $Code,
                                          "action" => array(
                                                            "type"   => "search",
                                                            "search" => array(
                                                                              "query" => "topic(" . $CSys . " " . $Code . ")",
                                                                              "type"  => "lukida"
                                                                             )
                                                           ),
                                          "more"   => $CRec
                                         );
        }
        else
        {
          $Clas["item_".++$Item]  = array("text"   => $CSys . " " . $Code,
                                          "action" => array(
                                                            "type"   => "search",
                                                            "search" => array(
                                                                              "query" => "topic(" . $CSys . " " . $Code . ")",
                                                                              "type"  => "lukida"
                                                                             )
                                                           ),
                                          "more"    => array()
                                         );
        }
      }
    }

    $Classes = array();
    if ( !empty($Clas) )
    {
      $Classes["bib_".$Seq] = array(
                                    "label"  => $this->CS->getText("bibline.".$Label),
                                    "code"   => $Label,
                                    "items"  => $Clas
                                   );
    }
    return $Classes;
  }

  private function formatClassL($Seq, $Label)
  {
    if ( APPDEBUG["FKT"] ) $this->CS->debug("FKT",__METHOD__);

    $Recs = $this->getCompleteArray(array($this->Prefix . "983" => array("a","b")));
    $Gesehen = array();
    $Item = 0;
    $Clas = array();

    foreach ( $Recs as $Rec )
    {
      if ( !isset($Rec["a"]["0"]) ) continue;

      $Schluessel = $Rec["a"]["0"] . "|" . (isset($Rec["b"]["0"]) ? $Rec["b"]["0"] : "");
      if ( isset($Gesehen[$Schluessel]) ) continue;
      $Gesehen[$Schluessel] = true;
      $Text = isset($Rec["b"]["0"]) ? $Rec["b"]["0"] : $Rec["a"]["0"];
      $Code = $Rec["a"]["0"];

      $ClassText = ( isset($this->CS->configs["bibliographic"]["classlocaltext"])
                  && trim($this->CS->configs["bibliographic"]["classlocaltext"]) )
                   ? strtolower(trim($this->CS->configs["bibliographic"]["classlocaltext"])) : "text";

      if ( $ClassText == "text" )
      {
         $Tmp  =  $this->CS->getText("classlocal.". strtolower(str_replace(" ", "", $Text)));
         if ( $Tmp )  $Text = $Tmp;
      }
       if ( $ClassText == "code2" )
       {
         $Teile = preg_split('/\s+/', trim($Code));
         $Punkt = implode(" ", array_slice($Teile, 0, 2));
         $Tmp   = $this->CS->getText("classlocal." . strtolower(str_replace(" ", "", $Punkt)));
         if ( $Tmp )
         {
           $Text = $Punkt . " " . $Tmp;
           $Code = "";
         }
       }

      if ( $this->CS->confIsYes("bibliographic", "classlocalcodefirst")
        && trim((string) $Code) !== "" && $Code !== $Text )
      {
        $Clas["item_".++$Item] = array("text"   => $Code,
                                       "code"   => "",
                                       "action" => array(
                                                         "type"   => "search",
                                                         "search" => array(
                                                                            "query" => "classlocal(" . $this->clear($Rec["a"]["0"]) . ")",
                                                                            "type"  => "lukida"
                                                                          )
                                                        ),
                                       "more"   => array()
                                      );
        $Code = "";
      }

      $Clas["item_".++$Item]  = array("text"   => $Text,
                                      "code"   => $Code,
                                      "action" => array(
                                                        "type"   => "search",
                                                        "search" => array(
                                                                           "query" => "classlocal(" . $this->clear($Rec["a"]["0"]) . ")",
                                                                           "type"  => "lukida"
                                                                         )
                                                       ),
                                      "more"   => array()
                                     );
    }

    $Classes = array();
    if ( !empty($Clas) )
    {
      $Classes["bib_".$Seq] = array(
                                   "label"  => $this->CS->getText("bibline.".$Label),
                                   "code"   => $Label,
                                   "items"  => $Clas
                                  );
    }
    return $Classes;
  }

  private function formatCollec($Seq, $Label)
  {
    if ( APPDEBUG["FKT"] ) $this->CS->debug("FKT",__METHOD__);

    $Recs = $this->getCompleteArray(array($this->Prefix . "935" => array("a")));
    $Item = 0;
    $Col  = array();

    foreach ( $Recs as $Rec )
    {
      if ( !isset($Rec["a"]["0"]) ) continue;

      $Text =  $this->CS->getText("collection.". strtolower(str_replace(" ", "", $Rec["a"]["0"])));
      if ( !$Text ) continue;

      $Logo = in_array($this->logoSchluessel($Rec["a"]["0"]), $this->logoSammlungen());

      $Col["item_".++$Item]   = array("text"   => $Text,
                                      "action" => array(
                                                        "type"   => "search",
                                                        "search" => array(
                                                                           "query" => "package(BIBCOLL_" . $Rec["a"]["0"] . ")",
                                                                           "type"  => "lukida"
                                                                         )
                                                       ),
                                      "logo"   => $Logo,
                                      "more"   => array()
                                     );
    }

    $Cols = array();
    if ( !empty($Col) )
    {
      $Cols["bib_".$Seq] = array(
                                   "label"  => $this->CS->getText("bibline.".$Label),
                                   "code"   => $Label,
                                   "items"  => $Col
                                  );
    }
    return $Cols;
  }

  private function formatSize($Seq, $Label)
  {
    if ( APPDEBUG["FKT"] ) $this->CS->debug("FKT",__METHOD__);

    $Recs = $this->getCompleteArray(array($this->Prefix . "300" => array("a","b","c")));
    $Item = 0;
    $Col  = array();

    foreach ( $Recs as $Rec )
    {
      $Add  = array();
      $Add[] = $this->getSFTexts($Rec, "a");
      $Add[] = $this->getSFTexts($Rec, "b", " : ");
      $Add[] = $this->getSFTexts($Rec, "c", " ; ");
      $Add   = array_filter($Add);

      $Retn["item_".++$Item]  = array("text"   => implode("", $Add),
                                      "action" => array(),
                                      "more"   => array()
                                     );
    }

    $Retns = array();
    if ( !empty($Retn) )
    {
      $Retns["bib_".$Seq] = array(
                                   "label"  => $this->CS->getText("bibline.".$Label),
                                   "code"   => $Label,
                                   "items"  => $Retn
                                  );
    }
    return $Retns;
  }

  private function formatCorp($Seq, $Label)
  {
    if ( APPDEBUG["FKT"] ) $this->CS->debug("FKT",__METHOD__);

    $Item = 0;
    $Retn = array();

    $Recs = $this->getCompleteArray(array($this->Prefix . "110" => array(" 0","4","6","a","b","c","d"),
                                          $this->Prefix . "111" => array(" 0","4","6","a","b","c","d")));
    $RecsOrg    = $this->getCompleteArray(array($this->Prefix . "880" => array("4","6","a","b","c")));

    foreach ( $Recs as $Field => $Rec )
    {
      $Text = "";
      $Tex  = array();
      $Add  = array();
      $Norm = $this->getNorm($Rec);
      $NormGND = $this->getGNDNorm(substr($Field,0,4) == "M110" ? "organisation" : "event", $Rec);

      $Text = "";
      $Add  = array();
      $Norm = $this->getNorm($Rec);
      $NormGND = $this->getGNDNorm("organisation", $Rec);

      $Text  = $this->getSFTexts($Rec, "a");
      $Text .= $this->getSFTexts($Rec, "b", ". ");
      $Text .= $this->getSFTexts($Rec, "c", " ");
      $Text .= $this->getOrgCorp(substr($Field,1,3), $Rec, $RecsOrg);
      if ( !$Text ) continue;

      $Add[] = $this->getSFTexts($Rec, "g");
      $Roles = $this->getPersonRoles($Rec, false);
      if ( count($Roles) )
      {
        foreach ( $Roles as &$Role ) {
          $Role = $this->CS->getText("role.".$Role);
         }
        $Roles = array_filter($Roles);
      }
      if ( count($Roles) ) {
        $Add = [...$Add,
                ...$Roles];
      } else {
        $Add[] = $this->getSFTexts($Rec, "e");
      }
      $Add   = array_filter($Add);

      $Retn["item_".++$Item]    = array("text"   => count($Add) ? $Text . " (" . implode(", ", $Add) . ")" : $Text,
                                        "action" => array(
                                                          "type"   => "search",
                                                          "search" => array("query" => ( $Norm )
                                                                                       ? "norm(" . $Norm . ")"
                                                                                       : "corporation(" . $this->clear($Text) . ")",
                                                                            "type"  => "lukida")
                                                         ),
                                        "more"   => $NormGND);
    }

    $Recs       = $this->getCompleteArray(array($this->Prefix . "710" => array(" 0","4","6","a","b","e","g")));
    $RecsOrg    = $this->getCompleteArray(array($this->Prefix . "880" => array( "4","6","a","b","c")));

    if ( $Item & count($Recs) ) $Retn["item_".++$Item]     = array("display" => "newline");
    foreach ( $Recs as $Field => $Rec )
    {
      $Text = "";
      $Add  = array();
      $Norm = $this->getNorm($Rec);
      $NormGND = $this->getGNDNorm("organisation", $Rec);

      $Text  = $this->getSFTexts($Rec, "a");
      $Text .= $this->getSFTexts($Rec, "b", ". ");
      $Text .= $this->getSFTexts($Rec, "c", " ");
      $Text .= $this->getOrgCorp(substr($Field,1,3), $Rec, $RecsOrg);
      if ( !$Text ) continue;

      $Add[] = $this->getSFTexts($Rec, "g");
      $Roles = $this->getPersonRoles($Rec, false);
      if ( count($Roles) )
      {
        foreach ( $Roles as &$Role ) {
          $Role = $this->CS->getText("role.".$Role);
         }
        $Roles = array_filter($Roles);
      }
      if ( count($Roles) ) {
        $Add = [...$Add,
                ...$Roles];
      } else {
        $Add[] = $this->getSFTexts($Rec, "e");
      }
      $Add   = array_filter($Add);

      $Retn["item_".++$Item]    = array("text"   => count($Add) ? $Text . " (" . implode(", ", $Add) . ")" : $Text,
                                        "action" => array(
                                                          "type"   => "search",
                                                          "search" => array(
                                                                            "query" => ( $Norm ) ? "norm(" . $Norm . ")" : "corporation(" . $this->clear($Text) . ")",
                                                                            "type"  => "lukida"
                                                                           )
                                                         ),
                                        "more"   => $NormGND
                                       );
      $Retn["item_".++$Item]     = array("display" => "newline");
    }

    $Returns = array();
    if ( !empty($Retn) )
    {
      $Returns["bib_".$Seq]     = array(
                                       "label"  => $this->CS->getText("bibline.".$Label),
                                       "code"   => $Label,
                                       "items"  => $Retn
                                      );
    }
    return $Returns;
  }

  private function formatPart($Seq, $Label)
  {
    if ( APPDEBUG["FKT"] ) $this->CS->debug("FKT",__METHOD__);

    $Tmp   = array("A" => [],
                   "N" => [],
                   "P" => [],
                   "V" => []);
    $Parts = array();
    $Item  = 0;

    $Recs = $this->getCompleteArray(array($this->Prefix . "245" => array("n","p")));
    foreach ( $Recs as $Rec )
    {
      if ( isset($Rec["n"]) && count($Rec["n"]) )
      {
        foreach ($Rec["n"] as $Text)
        {
          $Tmp["N"][] = $Text;
        }
      }
      if ( isset($Rec["p"]) && count($Rec["p"]) )
      {
        foreach ($Rec["p"] as $Text)
        {
          $Tmp["P"][] = $Text;
        }
      }
    }
    if ( count($Tmp["N"]) )
    {
      foreach ( $Tmp["N"] as $I => $N )
      {
        if ( isset($Tmp["P"][$I]) )
        {
          $Parts[] = $N . ": " . $Tmp["P"][$I];
        }
        else
        {
          $Parts[] = $N;
        }
      }
    }

    if ( !count($Parts) )
    {
      $Recs = $this->getCompleteArray(array($this->Prefix . "773" => array("g")));
      foreach ( $Recs as $Rec )
      {
        if ( isset($Rec["g"]) && count($Rec["g"]) )
        {
          foreach ($Rec["g"] as $Text)
          {
            if ( substr($Text, 0, 7) == "volume:" || substr($Text, 0, 5) == "year:"
              || substr($Text, 0, 7) == "number:" || substr($Text, 0, 6) == "pages:"
              || substr($Text, 0, 6) == "month:"  || substr($Text, 0, 7) == "extent:"
              || substr($Text, 0, 4) == "day:" )  continue;

            $Text = preg_replace('/\s*;\s*day:\s*\d+\s*$/u', "", $Text);
            if ( trim($Text) === "" )  continue;
            $Parts[] = $Text;
          }
        }
      }
    }

    if ( !count($Parts) )
    {
      $Recs = $this->getCompleteArray(array($this->Prefix . "490" => array("a","v")));
      foreach ( $Recs as $Rec )
      {
        if ( isset($Rec["a"]) && count($Rec["a"]) )
        {
          foreach ($Rec["a"] as $Text)
          {
            $Tmp["A"][] = $Text;
          }
        }
        if ( isset($Rec["v"]) && count($Rec["v"]) )
        {
          foreach ($Rec["v"] as $Text)
          {
            $Tmp["V"][] = $Text;
          }
        }
      }
      if ( count($Tmp["A"]) )
      {
        foreach ( $Tmp["A"] as $I => $A )
        {
          if ( isset($Tmp["V"][$I]) )
          {
            $Parts[] = $A . ": " . $Tmp["V"][$I];
          }
          else
          {
            $Parts[] = $A;
          }
        }
      }
    }

    $Parts = array_unique($Parts);

    $AllParts = array();
    if ( count($Parts) )
    {
      $AllParts["item_".++$Item]  = array("text"   => implode("; ", $Parts),
                                          "action" => array(),
                                          "more"   => array()
                                         );
    }

    $Return = array();
    if ( !empty($AllParts) )
    {
      $Return["bib_".$Seq] = array(
                                         "label"  => $this->CS->getText("bibline.".$Label),
                                         "code"   => $Label,
                                         "items"  => $AllParts
                                         );
    }
    return $Return;
  }

  private function internArtl($Seq, $Label)
  {
    if ( APPDEBUG["FKT"] ) $this->CS->debug("FKT",__METHOD__);

    $Recs = $this->getCompleteArray(array($this->Prefix . "773" => array("I1", "d", "g", "t", "w")));
    if ( empty($Recs) )  return array();

    $Parts = array();
    foreach ( $Recs as $Rec )
    {
      if ( isset($Rec["I1"][0]) && $Rec["I1"][0] == "1" )
      {
        if ( isset($Rec["g"]) && count($Rec["g"]) )
        {
          foreach ($Rec["g"] as $Text)
          {
            if ( substr($Text, 0, 5) == "year:" )    $Parts["year"]   = substr($Text,5);
            if ( substr($Text, 0, 7) == "number:" )  $Parts["number"] = substr($Text,7);
            if ( substr($Text, 0, 6) == "pages:" )   $Parts["pages"]  = substr($Text,6);
            if ( substr($Text, 0, 7) == "volume:" )  $Parts["volume"] = substr($Text,7);
          }
        }
      }
      else
      {
        if ( isset($Rec["d"]) && count($Rec["d"]) )
        {
          foreach ($Rec["d"] as $Text)
          {
            $Parts["publisher"] = $Text;
          }
        }

        if ( isset($Rec["g"]) && count($Rec["g"]) )
        {
          foreach ($Rec["g"] as $Text)
          {
            $Parts["part"] = $Text;
          }
        }

        if ( isset($Rec["t"]) && count($Rec["t"]) )
        {
          foreach ($Rec["t"] as $Text)
          {
            $Parts["journal"] = $Text;
          }
        }

        if ( isset($Rec["w"]) && count($Rec["w"]) )
        {
          foreach ($Rec["w"] as $Text)
          {
            if ( substr($Text, 0, 8) == "(DE-600)" ) $Parts["zdbid"]  = substr($Text,8);
            if ( substr($Text, 0, 8) == "(DE-627)" ) $Parts["rootid"] = substr($Text,8);
          }
        }
      }
    }

    $Return = array();
    if ( !empty($Parts) )
    {
      $Return["int_".$Seq] = array("code"   => $Label,
                                   "items"  => $Parts
                                  );
    }
    return $Return;
  }

  private function internPubl($Seq, $Label)
  {
    if ( APPDEBUG["FKT"] ) $this->CS->debug("FKT",__METHOD__);

    $Recs = $this->getCompleteArray(array($this->Prefix . "264" => array("a", "b", "c")));

    $Publ = array();

    if ( isset($Recs["M264_0"]) )
    {
      $Rec = $Recs["M264_0"];
      $Publ["place"]     = $this->getSFTexts($Rec, "a");
      $Publ["publisher"] = $this->getSFTexts($Rec, "b");
      $Publ["year"]      = $this->getSFTexts($Rec, "c");
    }

    $Return = array();
    if ( !empty($Publ) )
    {
      $Return["int_".$Seq] = array("code"   => $Label,
                                   "items"  => $Publ
                                  );
    }
    return $Return;
  }

  private function internDetl($Seq, $Label)
  {
    if ( APPDEBUG["FKT"] ) $this->CS->debug("FKT",__METHOD__);

    $Detl = array();

    $Recs = $this->getCompleteArray(array($this->Prefix . "502" => array("a", "b", "c", "d")));

    if ( isset($Recs["M502_0"]) )
    {
      $Rec   = $Recs["M502_0"];
      $Add   = array();

      $Add[] = $this->getSFTexts($Rec, "a");
      $Add[] = $this->getSFTexts($Rec, "b");
      $Add   = array_filter($Add);

      $Detl["type"]        = implode(" ", $Add);
      $Detl["institution"] = $this->getSFTexts($Rec, "c");
      $Detl["year"]        = $this->getSFTexts($Rec, "d");
    }

    $Recs = $this->getCompleteArray(array($this->Prefix . "300" => array("a")));

    if ( isset($Recs["M300_0"]) )
    {
      $Rec   = $Recs["M300_0"];
      $Detl["description"] = $this->getSFTexts($Rec, "a");
    }

    $Recs = $this->getCompleteArray(array($this->Prefix . "256" => array("a")));

    if ( isset($Recs["M256_0"]) )
    {
      $Rec   = $Recs["M256_0"];
      $Detl["computerfile"] = $this->getSFTexts($Rec, "a");
    }

    $Recs = $this->getCompleteArray(array($this->Prefix . "520" => array("a")));

    if ( isset($Recs["M520_0"]) )
    {
      $Rec   = $Recs["M520_0"];
      $Detl["summary"] = $this->getSFTexts($Rec, "a");
    }

    $Recs = $this->getCompleteArray(array($this->Prefix . "655" => array("a")));

    if ( isset($Recs["M655_0"]) )
    {
      $Rec   = $Recs["M655_0"];
      $Detl["genre"] = $this->getSFTexts($Rec, "a");
    }

    $Return = array();
    if (!empty($Detl) )
    {
      $Return["int_".$Seq] = array("code"   => $Label,
                                   "items"  => $Detl
                                  );
    }
    return $Return;
  }

  private function internLink($Seq, $Label)
  {
    if ( APPDEBUG["FKT"] ) $this->CS->debug("FKT",__METHOD__);

    $Recs = $this->getCompleteArray(array($this->Prefix . "856" => array("u")));

    $Link = array();

    if ( isset($Recs["M856_0"]) )
    {
      $Rec = $Recs["M856_0"];
      $Link["volltext"]  = $this->getSFTexts($Rec, "u");
    }

    $Return = array();
    if ( !empty($Link) )
    {
      $Return["int_".$Seq] = array("code"   => $Label,
                                   "items"  => $Link
                                  );
    }
    return $Return;
  }

  private function formatISBN($Seq, $Label)
  {
    if ( APPDEBUG["FKT"] ) $this->CS->debug("FKT",__METHOD__);

    $Item = 0;
    $Retn = array();

    $Seen  = array();
    $Recs  = $this->getCompleteArray(array($this->Prefix . "020" => array("9","a")));
    foreach ( $Recs as $Rec )
    {
      $Text = trim($this->getSFTexts($Rec, "9"));
      if ( !$Text )  $Text = trim($this->getSFTexts($Rec, "a"));
      if ( !$Text )  continue;

      $Key = strtoupper(str_replace(array("-", " "), "", $Text));
      if ( isset($Seen[$Key]) )  continue;
      $Seen[$Key] = true;

      if ( $Item ) $Retn["item_".++$Item]     = array("display" => "newline");

      $Retn["item_".++$Item]  = array("text"   => $Text,
                                      "action" => array(),
                                      "more"   => array()
                                     );
    }

    $Recs  = $this->getCompleteArray(array($this->Prefix . "024" => array("a")));
    foreach ( $Recs as $Rec )
    {
      if ( !isset($Rec["a"]["0"]) ) continue;

      $value = trim($this->getSFTexts($Rec, "a"));
      $normalized = str_replace('-', '', $value);

      if (strpos($value, "10.") === 0) continue;

      if (strpos($value, "urn:") === 0) continue;

      if (!preg_match('/^(97[89])?\d{9}[\dxX]$/', $normalized)) continue;

      $Key = strtoupper($normalized);
      if ( isset($Seen[$Key]) )  continue;
      $Seen[$Key] = true;

      if ( $Item ) $Retn["item_".++$Item] = array("display" => "newline");

      $Retn["item_".++$Item] = array(
      "text"   => $value,
      "action" => array(),
      "more"   => array()
      );
    }
    if ( !count($Retn) )
    {
      $Recs  = $this->getCompleteArray(array($this->Prefix . "773" => array("z")));
      foreach ( $Recs as $Rec )
      {
        if ( !isset($Rec["z"]["0"]) ) continue;

        if ( $Item ) $Retn["item_".++$Item]     = array("display" => "newline");

        $Add   = array();
        $Add[] = $this->getSFTexts($Rec, "z");

        $Retn["item_".++$Item]  = array("text"   => implode(" ", $Add),
                                        "action" => array(),
                                        "more"   => array()
                                       );
      }
    }

    if ( !count($Retn) )
    {
      $Recs  = $this->getCompleteArray(array($this->Prefix . "776" => array("z")));
      foreach ( $Recs as $Rec )
      {
        if ( !isset($Rec["z"]["0"]) ) continue;

        if ( $Item ) $Retn["item_".++$Item]     = array("display" => "newline");

        $Add   = array();
        $Add[] = $this->getSFTexts($Rec, "z");
        $Retn["item_".++$Item]  = array("text"   =>  implode(" ", $Add),
                                        "action" => array(),
                                        "more"   => array()
                                       );
      }
    }

    $Sauber  = array();
    $Gesehen = array();
    $Nr      = 0;
    $Marke   = null;
    foreach ( $Retn as $Eintrag )
    {
      if ( !isset($Eintrag["text"]) )
      {
        if ( $Nr )  $Marke = $Eintrag;
        continue;
      }
      $Schluessel = strtoupper(str_replace(array("-", " "), "", (string) $Eintrag["text"]));
      if ( $Schluessel === "" || isset($Gesehen[$Schluessel]) )  continue;
      $Gesehen[$Schluessel] = true;

      if ( $Marke !== null ) { $Sauber["item_".++$Nr] = $Marke; $Marke = null; }
      $Sauber["item_".++$Nr] = $Eintrag;
    }
    $Retn = $Sauber;

    $Returns = array();
    if ( !empty($Retn) )
    {
      $Returns["bib_".$Seq] = array(
                                  "text"   => $this->CS->getText("bibline.".$Label),
                                  "code"   => $Label,
                                  "items"  => $Retn
                                  );
    }
    return $Returns;
  }

  private function formatEdition($Seq, $Label)
  {
    if ( APPDEBUG["FKT"] ) $this->CS->debug("FKT",__METHOD__);

    $Recs  = $this->getCompleteArray(array($this->Prefix . "348" => array("a")));
    $Item  = 0;
    $Edits = array();
    foreach ( $Recs as $Rec )
    {
      if ( !isset($Rec["a"]["0"]) ) continue;
      $Edits["item_".++$Item]  = array("text"   => str_ireplace(array(", Musikalische Ausgabeform", "Musikalische Ausgabeform"), "", $Rec["a"]["0"]),
                                       "action" => array(),
                                       "more"   => array()
                                      );
    }

    $Recs = $this->getCompleteArray(array($this->Prefix . "250" => array("a","b")));
    foreach ( $Recs as $Rec )
    {
      if ( !isset($Rec["a"]["0"]) ) continue;
      $Edits["item_".++$Item]  = array("text"   => str_ireplace(array(", Musikalische Ausgabeform", "Musikalische Ausgabeform"), "", $Rec["a"]["0"]),
                                       "action" => array(),
                                       "more"   => array()
                                      );
      if ( !isset($Rec["b"]["0"]) ) continue;
      $Edits["item_".++$Item]  = array("text"   => "/ " . $Rec["b"]["0"],
                                       "action" => array(),
                                       "more"   => array()
                                      );
    }

    $Editions = array();
    if ( !empty($Edits) )
    {
      $Editions["bib_".$Seq] = array(
                                         "text"   => $this->CS->getText("bibline.".$Label),
                                         "code"   => $Label,
                                         "items"  => $Edits
                                         );
    }
    return $Editions;
  }

  private function formatGenre($Seq, $Label)
  {
    if ( APPDEBUG["FKT"] ) $this->CS->debug("FKT",__METHOD__);

    $Recs = $this->getCompleteArray(array($this->Prefix . "655" => array("0","a","x","y","z")));
    $Item = 0;
    $Gens = array();
    foreach ( $Recs as $Rec )
    {
      if ( !isset($Rec["a"]["0"]) ) continue;

      $Unter = array();
      if ( isset($Rec["x"]["0"]) ) $Unter[] = $Rec["x"]["0"];
      if ( isset($Rec["y"]["0"]) ) $Unter[] = $Rec["y"]["0"];
      if ( isset($Rec["z"]["0"]) ) $Unter[] = $Rec["z"]["0"];

      $Norm = $this->getNorm($Rec);
      $Gens["item_".++$Item]  = array("text"   => $Rec["a"]["0"],
                                       "action" => array(
                                                         "type"   => "search",
                                                         "search" => array(
                                                                            "query" => ( $Norm ) ? "norm(" . $Norm . ")"
                                                                                                 : "genre(" . $this->clear($Rec["a"]["0"]) . ")",
                                                                            "type"  => "lukida"
                                                                          )
                                                        ),
                                       "more"   => array()
                                      );
      if ( count($Unter) )
      {
        $Gens["item_".++$Item]  = array("text"   => " (" . implode(", ", $Unter) . ")",
                                        "action" => array(),
                                        "more"   => array()
                                       );
         }
    }

    $Genres = array();
    if ( !empty($Gens) )
    {
      $Genres["bib_".$Seq] = array(
                                   "label"  => $this->CS->getText("bibline.".$Label),
                                   "code"   => $Label,
                                   "items"  => $Gens
                                  );
    }
    return $Genres;
  }

  private function formatIn($Seq, $Label)
  {
    if ( APPDEBUG["FKT"] ) $this->CS->debug("FKT",__METHOD__);

    $Recs = $this->getCompleteArray(array($this->Prefix . "830" => array("a","b","p","v","w")));
    if ( !isset($Recs[$this->Prefix . "830_0"]["a"]["0"]) )
    {
      $Recs490 = $this->getCompleteArray(array($this->Prefix . "490" => array("a")));
      $Recs[$this->Prefix . "830_0"]["a"]["0"] = isset($Recs490[$this->Prefix . "490_0"]["a"]["0"]) ? $Recs490[$this->Prefix . "490_0"]["a"]["0"] : "";
    }

    $Item = 0;
    $Is   = array();
    foreach ( $Recs as $Rec )
    {
      $Text  = "";
      $Link  = "";
      if ( isset($Rec["a"][0]) && $Rec["a"][0] != "" )  $Text .= $Rec["a"][0] . " ";
      if ( isset($Rec["b"][0]) && $Rec["b"][0] != "" )  $Text .= $Rec["b"][0] . " ";
      if ( isset($Rec["p"][0]) && $Rec["p"][0] != "" )  $Text .= $Rec["p"][0] . ". ";
      if ( isset($Rec["v"][0]) && $Rec["v"][0] != "" )  $Text .= $Rec["v"][0];
      if ( isset($Rec["w"][0]) && in_array(substr($Rec["w"][0],0,8), array("(DE-601)","(DE-627)") ) )
      {
        $Link =  trim(substr($Rec["w"][0],8));
      }
      if ( !$Text ) continue;

      if ( $Link )
      {
        $Is["item_".++$Item]  = array("text"   => $Text,
                                      "action" => array(
                                                        "type"   => "search",
                                                        "search" => array(
                                                                          "query" => "id(" . $Link . ")",
                                                                          "type"  => "lukida"
                                                                         )
                                                       ),
                                      "more"   => array()
                                     );
      }
      else
      {
        $Is["item_".++$Item]  = array("text"   => $Text,
                                      "action" => array(),
                                      "more"   => array()
                                     );
      }
    }

    $Ins = array();
    if ( !empty($Is) )
    {
      $Ins["bib_".$Seq] = array(
                                "label"  => $this->CS->getText("bibline.".$Label),
                                "code"   => $Label,
                                "items"  => $Is
                               );
    }
    return $Ins;
  }

  private function formatInclude($Seq, $Label)
  {
    if ( APPDEBUG["FKT"] ) $this->CS->debug("FKT",__METHOD__);

    $Recs = $this->getSubfield("501", "a");
    if ( empty($Recs) )  return array();

    return array(("bib_".$Seq) => array(
                                "label"  => $this->CS->getText("bibline.".$Label),
                                "code"   => $Label,
                                "items"  => array_unique($Recs)
                               )
                );
  }

  private function formatLicense($Seq, $Label)
  {
    if ( APPDEBUG["FKT"] ) $this->CS->debug("FKT",__METHOD__);

    $Recs = $this->getCompleteArray(array($this->Prefix . "540" => array("a", "f","u")));
    if ( empty($Recs) )  return array();

    $Item = 0;
    $Lics = array();
    foreach ( $Recs as $Rec )
    {
      $Text  = ( isset($Rec["a"]["0"]) ) ? $Rec["a"]["0"] : "";
      $Text .= ( isset($Rec["f"]["0"]) ) ? " " . $Rec["f"]["0"] : "";
      $Link  = ( isset($Rec["u"]["0"]) ) ? $Rec["u"]["0"] : "";

      if ( $Link )
      {
        $Lics["item_".++$Item]  = array("text"   => $Text,
                                        "action" => array(
                                                          "type"    => "link",
                                                          "link"    => array(
                                                                             "url"    => $Link,
                                                                             "target" => "_blank"
                                                                            )
                                                         ),
                                        "more"   => array()
                                       );
      }
      else
      {
        $Lics["item_".++$Item]  = array("text"   => $Text,
                                        "action" => array(),
                                        "more"   => array()
                                        );
      }
    }

    $Licenses = array();
    if ( !empty($Lics) )
    {
      $Licenses["bib_".$Seq] = array(
                                         "label"  => $this->CS->getText("bibline.".$Label),
                                         "code"   => $Label,
                                         "items"  => $Lics
                                         );
    }
    return $Licenses;
  }

  private function formatOtherEs($Seq, $Label)
  {
    if ( APPDEBUG["FKT"] ) $this->CS->debug("FKT",__METHOD__);

    $Item = 0;
    $OEds = array();

    $Recs = $this->getCompleteArray(array($this->Prefix . "780" => array("a","b","d","g","h","i","q","t","w")));

    foreach ( $Recs as $Rec )
    {
      if ( $Item > 0 )
      {
        $OEds["item_".++$Item]  = array("display" => "newline");
      }

      $Text = "";
      $Tex  = array();
      $Add  = array();

      $Tex[] = $this->getSFTexts($Rec, "i");
      $Tex[] = $this->getSFTexts($Rec, "t");
      if ( count($Tex) ) $Text = implode(" ", $Tex);
      if ( !$Text ) $Text = $this->getSubfieldFirst($this->Prefix . "245", "a");

      $Add[] = $this->getSFTexts($Rec, "a");
      $Add[] = $this->getSFTexts($Rec, "d", ", ");
      $Add[] = $this->getSFTexts($Rec, "g", " ", ". ");
      $Add[] = $this->getSFTexts($Rec, "q", " ", $this->CS->getText("bibline.volume") . ": ");
      $Add[] = $this->getSFTexts($Rec, "h", " ");

      if ( isset($Rec["w"][0]) && in_array(substr($Rec["w"][0],0,8), array("(DE-601)","(DE-627)") ) )
      {
        $OEds["item_".++$Item]  = array("text"   => $Text . " " . implode ("", $Add),
                                        "action" => array(
                                                          "type"   => "search",
                                                          "search" => array(
                                                                            "query" => "id(" . trim(substr($Rec["w"]["0"],8)) . ")",
                                                                            "type"  => "lukida"
                                                                            )
                                                         ),
                                        "more"   => array()
                                       );
      }
      else
      {
        $OEds["item_".++$Item]  = array("text"   => $Text . " " . implode ("", $Add),
                                        "action" => array(),
                                        "more"   => array()
                                       );
      }
    }

    $Recs = $this->getCompleteArray(array($this->Prefix . "785" => array("a","b","d","g","h","i","q","t","w")));

    foreach ( $Recs as $Rec )
    {
      if ( $Item > 0 )
      {
        $OEds["item_".++$Item]  = array("display" => "newline");
      }

      $Text = "";
      $Tex  = array();
      $Add  = array();

      $Tex[] = $this->getSFTexts($Rec, "i");
      $Tex[] = $this->getSFTexts($Rec, "t");
      if ( count($Tex) ) $Text = implode(" ", $Tex);
      if ( !$Text ) $Text = $this->getSubfieldFirst($this->Prefix . "245", "a");

      $Add[] = $this->getSFTexts($Rec, "a");
      $Add[] = $this->getSFTexts($Rec, "d", ", ");
      $Add[] = $this->getSFTexts($Rec, "g", " ", ". ");
      $Add[] = $this->getSFTexts($Rec, "q", " ", $this->CS->getText("bibline.volume") . ": ");
      $Add[] = $this->getSFTexts($Rec, "h", " ");

      if ( isset($Rec["w"][0]) && in_array(substr($Rec["w"][0],0,8), array("(DE-601)","(DE-627)") ) )
      {
        $OEds["item_".++$Item]  = array("text"   => $Text . " " . implode ("", $Add),
                                        "action" => array(
                                                          "type"   => "search",
                                                          "search" => array(
                                                                            "query" => "id(" . trim(substr($Rec["w"]["0"],8)) . ")",
                                                                            "type"  => "lukida"
                                                                            )
                                                         ),
                                        "more"   => array()
                                       );
      }
      else
      {
        $OEds["item_".++$Item]  = array("text"   => $Text . " " . implode ("", $Add),
                                        "action" => array(),
                                        "more"   => array()
                                       );
      }
    }

    $OtherEs = array();
    if ( !empty($OEds) )
    {
      $OtherEs["bib_".$Seq] = array(
                                         "label"  => $this->CS->getText("bibline.".$Label),
                                         "code"   => $Label,
                                         "items"  => $OEds
                                         );
    }
    return $OtherEs;
  }

  private function formatPeople($SeqA, $LabelA, $SeqB, $LabelB)
  {
    if ( APPDEBUG["FKT"] ) $this->CS->debug("FKT",__METHOD__);

    $Recs       = $this->getCompleteArray(array($this->Prefix . "100" => array(" 0","4","6","a","c","d","e"),
                                                $this->Prefix . "700" => array(" 0","4","6","a","c","d","e")));
    $RecsOrg    = $this->getCompleteArray(array($this->Prefix . "880" => array( "6","a")));
    $Item   = array("A" => 0      ,"B" => 0);
    $Person = array("A" => array(),"B" => array());

    foreach ( $Recs as $Cat => $Rec )
    {
      $Typ  = (substr($Cat,1,3) == "100") ? "A" : "B";
      $Cnt  = $Item[$Typ];

      $Roles = $this->getPersonRoles($Rec, $Typ == "A");
      $Roles = array_filter($Roles);
      if ( $Typ == "A" )  $Roles = array_diff($Roles, array("aut"));
      foreach ( $Roles as &$Role )
      {
        $Role = $this->CS->getText("role.".$Role);
      }
      $Roles = array_filter($Roles);

      $Name  = $this->formatPersonName($Rec);
      $Name .= $this->getOrgName($Typ == "A", $Rec, $RecsOrg);

      $Norm    = $this->getNorm($Rec);
      $NormGND = $this->getGNDNorm("person", $Rec);

      $Text  = $Name;
      $Datum = isset($Rec["d"][0]) ? $Rec["d"][0] : "";

      $Tmp = [];
      if ( trim($Datum) )    $Tmp[] = $Datum;
      if ( count($Roles) )   $Tmp[] = implode(", ", $Roles);

      if ( count($Tmp) ) $Text .= " (" . implode(", ", $Tmp) . ")";

      $Person[$Typ]["item_".++$Cnt]   = array("text"   => $Text,
                                              "action" => array(
                                                                "type"   => "search",
                                                                "search" => array(
                                                                                  "query" => ( $Norm )
                                                                                             ? "norm(" . $Norm . ")"
                                                                                             : "author(" . $this->clear($Name) . ")",
                                                                                  "type"  => "lukida"
                                                                                 )
                                                               ),
                                              "more"   => $NormGND);
      $Item[$Typ] = $Cnt;
    }

    $People = array();
    if ( !empty($Person["A"]) )
    {
      $People["bib_".$SeqA] = array(
                                    "label"  => $this->CS->getText("bibline.".$LabelA),
                                    "code"   => $LabelA,
                                    "items"  => $Person["A"]
                                   );
    }
    if ( !empty($Person["B"]) )
    {
      $People["bib_".$SeqB] = array(
                                    "label"  => $this->CS->getText("bibline.".$LabelB),
                                    "code"   => $LabelB,
                                    "items"  => $Person["B"]
                                   );
    }
    return $People;
  }

  private function formatPersonName($One)
  {
    if ( APPDEBUG["FKT"] ) $this->CS->debug("FKT",__METHOD__);

    if ( isset($One["a"][0]) && $One["a"][0] != "" && isset($One["c"][0]) && $One["c"][0] != "" )
    {
      return $One["a"][0] . " " . $One["c"][0];
    }
    if ( !isset($One["a"][0]) && isset($One["c"][0]) && $One["c"][0] != "" )
    {
      return $One["a"][0];
    }
    if ( isset($One["a"][0]) && $One["a"][0] != "" && !isset($One["c"][0]) )
    {
      return $One["a"][0];
    }
  }

  private function formatProven($Seq, $Label)
  {
    if ( APPDEBUG["FKT"] ) $this->CS->debug("FKT",__METHOD__);

    $PROV    = ( isset($this->CS->configs["bibliographic"]["provenance"]) && $this->CS->configs["bibliographic"]["provenance"] )
               ? trim(strtolower($this->CS->configs["bibliographic"]["provenance"])) : "";
    if ( !$PROV ) return array();

    $Recs = $this->getCompleteArray(array($this->Prefix . "361" => array("0","5","a","f","k","l","o","s","u","z")));

    if ( empty($Recs) )  return array();

    $Item = 0;
    $Pros = array();
    foreach ( $Recs as $Rec )
    {
      if ( !isset($Rec["5"]["0"]) || !isset($Rec["a"]["0"]) ) continue;
      if ( !isset($Rec["5"]["0"]) && ( $PROV == "iln" || $PROV == "iln*" ) ) continue;

      if ( $PROV == "iln" )
      {
        if ( !in_array($Rec["5"]["0"],$this->CS->configs["isils"]) )  continue;
      }
      if ( $PROV == "iln*" )
      {
        $Found = false;
        foreach ($this->CS->configs["isils"] as $I)
        {
          if ( $I == $Rec["5"]["0"] || strncmp($I . "-", $Rec["5"]["0"], strlen($I)+1) === 0 )
          {
            $Found = true;
            break;
          }
        }
        if ( !$Found ) continue;
      }

      $CRec = $this->getCDBIsil($Rec["5"]["0"]);
      if ( !isset($CRec["name"]) || !isset($CRec["infos"]["shortname"]) )  continue;

      if ( $Item > 0 )
      {
        $Pros["item_".++$Item]  = array("display" => "newline");
      }

      $Add  = array();
      $Norm = $this->getNorm($Rec);

      $Text  = $CRec["infos"]["shortname"];
      if ( !$Text ) continue;

      $Add[] = $Text;
      $Add[] = $this->getSFTexts($Rec, "s", ", ");

      $Bez   = $this->getSFTexts($Rec, "o", ", ");
      $Name  = $this->getSFTexts($Rec, "a", "");
      $Add[] = ($Bez) ? $Bez . ": " . $Name : ", " . $Name;
      $Add[] = $this->getSFTexts($Rec, "f", ", ");

      $Add[] = $this->getSFTexts($Rec, "k", ", ");
      $Add[] = $this->getSFTexts($Rec, "l", ", ");
      $Add[] = $this->getSFTexts($Rec, "z", ", ");
      $Add   = array_filter($Add);

      $Pros["item_".++$Item]    = array("text"   => implode("", $Add));
      $Pros["item_".++$Item]    = array("text"   => "",
                                        "action" => array(
                                                          "type"   => "search",
                                                          "search" => array("query" => ( $Norm )
                                                                                       ? "norm(" . $Norm . ")"
                                                                                       : "author(" . $this->clear($Name) . ")",
                                                                            "type"  => "lukida")
                                                         ),
                                        "more"   => array());
    }
    $Provenances = array();
    if ( !empty($Pros) )
    {
      $Provenances["bib_".$Seq] = array(
                                        "label"  => $this->CS->getText("bibline.".$Label),
                                        "code"   => $Label,
                                        "items"  => $Pros
                                       );
    }
    return $Provenances;
  }

  private function formatFinger($Seq, $Label)
  {
    if ( APPDEBUG["FKT"] ) $this->CS->debug("FKT",__METHOD__);

    $FingType    = ( isset($this->CS->configs["bibliographic"]["fingerprint"]) && $this->CS->configs["bibliographic"]["fingerprint"] )
                   ? trim(strtolower($this->CS->configs["bibliographic"]["fingerprint"])) : "";
    if ( !$FingType ) return array();

    $Recs = $this->getCompleteArray(array($this->Prefix . "026" => array("5","e")));
    if ( empty($Recs) )  return array();

    $Item = 0;
    $Fings = array();
    foreach ( $Recs as $Rec )
    {
      if ( !isset($Rec["5"]["0"]) || !isset($Rec["e"]["0"]) ) continue;
      if ( !isset($Rec["5"]["0"]) && $FingType == "iln" )    continue;

      $ISILs = array_values(array_intersect($this->CS->configs["isils"], $Rec["5"]));

      if ( !count($ISILs) )
      {
        if ( $FingType == "iln" ) continue;
        $ISILs =  $Rec["5"];
      }

      $ISIL = $ISILs[0];

      $CRec = $this->getCDBIsil($ISIL);
      if ( !isset($CRec["name"]) || !isset($CRec["infos"]["shortname"]) )  continue;

      if ( $Item > 0 )
      {
        $Fings["item_".++$Item]  = array("display" => "newline");
      }

      $Add  = array();
      $Norm = $this->getNorm($Rec);

      $Text  = $CRec["infos"]["shortname"];
      if ( !$Text ) continue;

      $Fings["item_".++$Item]    = array("text"   => $Rec["e"]["0"] . " (" . $Text . ")");
    }

    $Finger = array();
    if ( !empty($Fings) )
    {
      $Finger["bib_".$Seq] = array(
                                   "label"  => $this->CS->getText("bibline.".$Label),
                                   "code"   => $Label,
                                   "items"  => $Fings
                                  );
    }
    return $Finger;
  }

  private function formatPublish($Seq, $Label)
  {
    if ( APPDEBUG["FKT"] ) $this->CS->debug("FKT",__METHOD__);

    $Item = 0;
    $Pubs = [];

    $Recs = $this->getCompleteArray(array($this->Prefix . "773" => array("a","b","d","g","h","i","q","t","w")));
    $Rec  = ( count($Recs) ) ? array_shift($Recs) : array();
    $In   = "";
    if ( count($Rec) )
    {
      if ( isset($Rec["i"]["0"]) )
      {
        $Pubs["item_".++$Item]  = array("text"   => $Rec["i"]["0"],
                                        "action" => array(),
                                        "more"   => array()
                                       );
      }
      if ( isset($Rec["w"][0]) && in_array(substr($Rec["w"][0],0,8), array("(DE-601)","(DE-627)") ) )
      {
        if ( isset($Rec["t"][0]) && $Rec["t"][0] )
        {
          $Pubs["item_".++$Item]  = array("text"   => $Rec["t"]["0"],
                                          "action" => array(
                                                            "type"   => "search",
                                                            "search" => array(
                                                                               "query" => "id(" . trim(substr($Rec["w"]["0"],8)) . ")",
                                                                               "type"  => "lukida"
                                                                             )
                                                           ),
                                          "more"   => array()
                                         );
        }
        else
        {
          $Pubs["item_".++$Item]  = array("text"   => $this->getSubfieldFirst($this->Prefix . "245", "a"),
                                          "action" => array(
                                                            "type"   => "search",
                                                            "search" => array(
                                                                               "query" => "id(" . trim(substr($Rec["w"]["0"],8)) . ")",
                                                                               "type"  => "lukida"
                                                                             )
                                                           ),
                                          "more"   => array()
                                         );
        }
      }
      elseif ( isset($Rec["t"][0]) && $Rec["t"][0] )
      {
        $Pubs["item_".++$Item]  = array("text"   => $Rec["t"]["0"],
                                        "action" => array(),
                                        "more"   => array()
                                       );
      }

      if ( isset($Rec["a"][0]) && $Rec["a"][0] )
      {
        $Pubs["item_".++$Item]  = array("text"   => $Rec["a"][0],
                                        "action" => array(),
                                        "more"   => array()
                                       );
      }
      if ( isset($Rec["d"][0]) && $Rec["d"][0] )
      {
        $Pubs["item_".++$Item]  = array("text"   => $Rec["d"][0],
                                        "action" => array(),
                                        "more"   => array()
                                       );
      }
      if ( isset($Rec["g"][0]) && $Rec["g"][0] )
      {
        $Pubs["item_".++$Item]  = array("text"   => $Rec["g"][0],
                                        "action" => array(),
                                        "more"   => array()
                                       );
      }
      if ( isset($Rec["q"][0]) && $Rec["q"][0] )
      {
        $Pubs["item_".++$Item]  = array("text"   => $Rec["q"][0],
                                        "action" => array(),
                                        "more"   => array()
                                       );
      }
      if ( isset($Rec["h"][0]) && $Rec["h"][0] )
      {
        $Pubs["item_".++$Item]  = array("text"   => $Rec["h"][0],
                                        "action" => array(),
                                        "more"   => array()
                                       );
      }
    }

    $Recs    = $this->getCompleteArray(array($this->Prefix . "264" => array("6","a","b","c","e","I2")));
    $RecsOrg = $this->getCompleteArray(array($this->Prefix . "880" => array("6","a","b","c")));
    if ( count($Recs) )
    {
      $Copies = array();
      foreach ( $Recs as $Rec2 )
      {
        if ( !isset($Rec2["I2"][0]) || $Rec2["I2"][0] != "4" )  continue;
        foreach ( (array) (isset($Rec2["c"]) ? $Rec2["c"] : array()) as $One )
        {
          $One = trim((string) $One);
          if ( $One !== "" )  $Copies[] = "[" . $One . "]";
        }
      }

      foreach ( $Recs as $Field => $Rec )
      {
        if ( isset($Rec["I2"][0]) && $Rec["I2"][0] == "4" ) continue;

        $Text = ( isset($Rec["a"]["0"]) ) ? $Rec["a"]["0"] : $this->getSubfieldFirst($this->Prefix . "245", "a");
        $Org  = $this->getOrgPubl(substr($Field,1,3), $Rec, $RecsOrg);

        $Pubs["item_".++$Item]  = array("text"   => $Text,
                                        "action" => array(),
                                        "more"   => array()
                                       );
        if ( isset($Rec["b"]["0"]) )
        {
          $Pubs["item_".++$Item]  = array("text"   => $Rec["b"]["0"],
                                          "action" => array(
                                                            "type"   => "search",
                                                            "search" => array(
                                                                               "query" => "publisher(" . $this->clear($Rec["b"]["0"]) . ")",
                                                                               "type"  => "lukida"
                                                                             )
                                                           ),
                                          "more"   => array()
                                         );
        }

        if ( isset($Rec["c"]["0"]) )
        {
          $Pubs["item_".++$Item]  = array("text"   => $Rec["c"]["0"],
                                          "action" => array(),
                                          "more"   => array()
                                         );
        }

        if ( isset($Org["a"]) )
        {
          $Pubs["item_".++$Item]  = array("text"   => $Org["a"],
                                          "action" => array(),
                                          "more"   => array()
                                         );
        }
        if ( isset($Org["b"]) )
        {
          $Pubs["item_".++$Item]  = array("text"   => $Org["b"],
                                          "action" => array(
                                                            "type"   => "search",
                                                            "search" => array(
                                                                               "query" => "publisher(" . $this->clear($Org["b"]) . ")",
                                                                               "type"  => "lukida"
                                                                             )
                                                           ),
                                          "more"   => array()
                                         );
        }
        if ( isset($Org["c"]) )
        {
          $Pubs["item_".++$Item]  = array("text"   => $Org["c"],
                                          "action" => array(),
                                          "more"   => array()
                                         );
        }

        if ( isset($Rec["e"]["0"]) )
        {
          $Pubs["item_".++$Item]  = array("text"   => $Rec["e"]["0"],
                                          "action" => array(),
                                          "more"   => array()
                                         );
        }
      }

      if ( count($Copies) )
      {
        $LastKey = "";
        foreach ( $Pubs as $Key => $One )
        {
          if ( isset($One["text"]) )  $LastKey = $Key;
        }
        if ( $LastKey !== "" )
        {
          $Pubs[$LastKey]["text"] .= "; " . implode(", ", $Copies);
        }
        else
        {
          $Pubs["item_".++$Item]  = array("text"   => implode(", ", $Copies),
                                          "action" => array(),
                                          "more"   => array()
                                         );
        }
      }
    }

    $Publish = array();
    if ( !empty($Pubs) )
    {
      $Publish["bib_".$Seq] = array(
                                         "label"  => $this->CS->getText("bibline.".$Label),
                                         "code"   => $Label,
                                         "items"  => $Pubs
                                         );
    }
    return $Publish;
  }

  private function formatRemarks($Seq, $Label)
  {
    if ( APPDEBUG["FKT"] ) $this->CS->debug("FKT",__METHOD__);

    $Recs = $this->getCompleteArray(array($this->Prefix . "772" => array("i","t","w"),
                                          $this->Prefix . "770" => array("i","t","w")));

    if ( empty($Recs) )  return array();

    $Item = 0;
    $Rems = array();
    foreach ( $Recs as $Rec )
    {
      if ( !isset($Rec["i"]["0"]) && !isset($Rec["t"]["0"])) continue;

      $Text  = ( isset($Rec["i"]["0"]) ) ? $Rec["i"]["0"] : "";
      if ( isset($Rec["t"]["0"]) )
      {
        $Text .= ($Text) ? ": " . $Rec["t"]["0"] : $Rec["t"]["0"];
      }

      if ( isset($Rec["w"][0]) && in_array(substr($Rec["w"][0],0,8), array("(DE-601)","(DE-627)") ) )
      {
        $Rems["item_".++$Item]  = array("text"   => $Text,
                                        "action" => array(
                                                          "type"   => "search",
                                                          "search" => array(
                                                                            "query" => "id(" . trim(substr($Rec["w"]["0"],8)) . ")",
                                                                            "type"  => "lukida"
                                                                           )
                                                           ),
                                       "more"   => array()
                                         );
      }
      else
      {
        $Rems["item_".++$Item]  = array("text"   => $Text,
                                        "action" => array(),
                                        "more"   => array()
                                        );
      }
    }

    $Remarks = array();
    if ( !empty($Rems) )
    {
      $Remarks["bib_".$Seq] = array(
                                         "label"  => $this->CS->getText("bibline.".$Label),
                                         "code"   => $Label,
                                         "items"  => $Rems
                                         );
    }
    return $Remarks;
  }

  private function formatLang($Seq, $Label)
  {
    if ( APPDEBUG["FKT"] ) $this->CS->debug("FKT",__METHOD__);

    $Recs = $this->getCompleteArray(array($this->Prefix . "041" => array("a")));

    if ( empty($Recs) )  return array();

    $Item = 0;
    $Retn = array();
    foreach ( $Recs as $Rec )
    {
      if ( !isset($Rec["a"]) )  continue;

      if ( $Item ) $Retn["item_".++$Item]     = array("display" => "newline");

      $Texts = array();
      foreach ( $Rec["a"] as $LC )
      {
        $Texts[] = $this->CS->getText("lang.".$LC);
      }
      $Texts = array_filter($Texts);

      if ( count($Texts) )
      {
        $Retn["item_".++$Item]  = array("text"   => implode(", ", $Texts),
                                        "action" => array(),
                                        "more"   => array());
      }
    }

    $Returns = array();
    if ( !empty($Retn) )
    {
      $Returns["bib_".$Seq] = array(
                                    "label"  => $this->CS->getText("bibline.".$Label),
                                    "code"   => $Label,
                                    "items"  => $Retn
                                   );
    }
    return $Returns;
  }

  private function formatLangOrg($Seq, $Label)
  {
    if ( APPDEBUG["FKT"] ) $this->CS->debug("FKT",__METHOD__);

    $Recs = $this->getCompleteArray(array($this->Prefix . "041" => array("h")));

    if ( empty($Recs) )  return array();

    $Item = 0;
    $Retn = array();
    foreach ( $Recs as $Rec )
    {
      if ( !isset($Rec["h"]) )  continue;

      if ( $Item ) $Retn["item_".++$Item]     = array("display" => "newline");

      $Texts = array();
      foreach ( $Rec["h"] as $LC )
      {
        $Texts[] = $this->CS->getText("lang.".$LC);
      }
      $Texts = array_filter($Texts);

      if ( count($Texts) )
      {
        $Retn["item_".++$Item]  = array("text"   => implode(", ", $Texts),
                                        "action" => array(),
                                        "more"   => array());
      }
    }

    $Returns = array();
    if ( !empty($Retn) )
    {
      $Returns["bib_".$Seq] = array(
                                    "label"  => $this->CS->getText("bibline.".$Label),
                                    "code"   => $Label,
                                    "items"  => $Retn
                                   );
    }
    return $Returns;
  }

  private function formatDissert($Seq, $Label)
  {
    if ( APPDEBUG["FKT"] ) $this->CS->debug("FKT",__METHOD__);

    $Recs = $this->getCompleteArray(array($this->Prefix . "502" => array("a","b","c","d")));

    if ( empty($Recs) )  return array();

    $Item = 0;
    $Diss = array();
    foreach ( $Recs as $Rec )
    {
      $Add   = array();
      $Add[] = $this->getSFTexts($Rec, "a");
      $Add[] = $this->getSFTexts($Rec, "b");
      $Add[] = $this->getSFTexts($Rec, "c");
      $Add[] = $this->getSFTexts($Rec, "d");
      $Add   = array_filter($Add);

      if ( count($Add) )
      {
        $Diss["item_".++$Item]  = array("text"   => implode(", ", $Add),
                                        "action" => array(),
                                        "more"   => array());
      }
    }

    $Disserts = array();
    if ( !empty($Diss) )
    {
      $Disserts["bib_".$Seq] = array(
                                         "label"  => $this->CS->getText("bibline.".$Label),
                                         "code"   => $Label,
                                         "items"  => $Diss
                                         );
    }
    return $Disserts;
  }

  private function formatSeeAlso($Seq, $Label)
  {
    if ( APPDEBUG["FKT"] ) $this->CS->debug("FKT",__METHOD__);

    $Recs = $this->getCompleteArray(array($this->Prefix . "776" => array("I2", "a", "c", "d", "i", "n", "o", "t", "w", "z")));
    $Item = 0;
    $Retn = array();
     $More = array();

    foreach ( $Recs as $Rec )
    {
       $Text = "";
       $Add  = array();
      if ( isset($Rec["I2"]["0"]) && $Rec["I2"]["0"] == "8" )
      {
        if ( $Item ) $Retn["item_".++$Item]     = array("display" => "newline");

        $Lead = array();
        foreach ( array("i", "z", "n") as $SF )
        {
          $One = trim($this->getSFTexts($Rec, $SF, $SF == "z" ? "ISBN " : ""));
          if ( $One !== "" )  $Lead[] = $One;
        }
        $Text = count($Lead) ? implode(" ", $Lead) . ":" : "";

        $Title = trim($this->getSFTexts($Rec, "t"));
        $Auth  = trim($this->getSFTexts($Rec, "a"));
        if ( $Title !== "" && $Auth !== "" )       $Add[] = $Title . " / " . $Auth;
        elseif ( $Title !== "" )                   $Add[] = $Title;
        elseif ( $Auth !== "" )                    $Add[] = $Auth;
        $Add[] = trim($this->getSFTexts($Rec, "d"));
      }
      elseif ( isset($Rec["c"]["0"]) && trim($Rec["c"]["0"]) )
      {
        $More[] = array("z" => $this->getSFTexts($Rec, "z"),
                        "c" => $this->getSFTexts($Rec, "c"));
      }

      $Add   = array_filter($Add);

      if ( $Text || count($Add) )
      {
        if ( isset($Rec["w"]["0"]) && in_array(substr($Rec["w"]["0"],0,8), array("(DE-601)","(DE-627)") ) )
        {
          $Retn["item_".++$Item]  = array("text"   => trim($Text . " " . implode(", ", $Add)),
                                          "action" => array(
                                                            "type"   => "search",
                                                            "search" => array(
                                                                              "query" => "id(" . trim(substr($Rec["w"]["0"],8)) . ")",
                                                                              "type"  => "lukida"
                                                                             )
                                                           ),
                                          "more"   => array()
                                         );
        }
        elseif ( isset($Rec["o"]["0"]) && substr($Rec["o"]["0"],0,4) == "urn:" )
        {
          $Retn["item_".++$Item]  = array("text"   => trim($Text . " " . implode(", ", $Add)),
                                          "action" => array(
                                                            "type"   => "link",
                                                            "link"   => array(
                                                                              "url"    => "https://nbn-resolving.org/" . $Rec["o"]["0"],
                                                                              "target" => "_blank"
                                                                             )
                                                           ),
                                          "more"   => array()
                                         );
        }
        elseif ( isset( $Rec["z"]["0"]) )
        {
          $Retn["item_".++$Item]  = array("text"   =>trim($Text . " " . implode(", ", $Add)),
                                          "action" => array(
                                                            "type"   => "search",
                                                            "search" => array("query" => "isn(" . $Rec["z"]["0"] . ")",
                                                                              "type"  => "lukida")
                                                           ),
                                          "more"   => array());
        }
      }
    }

    if ( count($More) )
    {
      if ( $Item ) $Retn["item_".++$Item]     = array("display" => "newline");

      foreach ($More as $M )
      {
        $Retn["item_".++$Item]  = array("text"   => "ISBN " . trim($M["z"] . " " . $M["c"]),
                                        "action" => array(
                                                          "type"   => "search",
                                                          "search" => array("query" => "isn(" . $M["z"] . ")",
                                                                            "type"  => "lukida")
                                                         ),
                                        "more"   => array());
      }
    }

    $Recs = $this->getCompleteArray(array($this->Prefix . "535" => array("3","a")));
    foreach ( $Recs as $Rec )
    {
      $Inst = trim($this->getSFTexts($Rec, "a"));
      $Sign = trim($this->getSFTexts($Rec, "3"));
      if ( !$Inst && !$Sign )  continue;

      if ( $Item ) $Retn["item_".++$Item]  = array("display" => "newline");
      $Retn["item_".++$Item] = array("text"   => $this->CS->getText("bibline.reproductionof") . ": " . $Inst
                                                 . ( $Sign ? " – " : "" ),
                                     "action" => array(),
                                     "more"   => array());

      if ( $Sign )
      {
        $Retn["item_".++$Item] = array("text"   => $Sign,
                                       "action" => array(
                                                         "type"   => "search",
                                                         "search" => array("query" => "signature(" . $this->clear($Sign) . ")",
                                                                           "type"  => "lukida")
                                                        ),
                                       "more"   => array());
      }
    }

    $Recs = $this->getCompleteArray(array($this->Prefix . "533" => array("7","a","b","c","d","e","f")));
    foreach ( $Recs as $Rec )
    {
      if ( !isset($Rec["7"]["0"]) )  continue;

      $Head = array();
      foreach ( array("a","b","c","d") as $SF )
      {
        $One = trim($this->getSFTexts($Rec, $SF));
        if ( $One !== "" )  $Head[] = $One;
      }
      $Parts = array();
      if ( count($Head) )  $Parts[] = implode(", ", $Head);
      foreach ( array("e","f") as $SF )
      {
        $One = trim($this->getSFTexts($Rec, $SF));
        if ( $One !== "" )  $Parts[] = $One;
      }
      if ( !count($Parts) )  continue;

      if ( $Item ) $Retn["item_".++$Item]  = array("display" => "newline");
      $Retn["item_".++$Item] = array("text"   => $this->CS->getText("bibline.reproductionnote") . ": "
                                                 . implode(". – ", $Parts),
                                     "action" => array(),
                                     "more"   => array());
    }

    $Returns = array();
    if ( !empty($Retn) )
    {
      $Returns["bib_".$Seq]     = array(
                                       "label"  => $this->CS->getText("bibline.".$Label),
                                       "code"   => $Label,
                                       "items"  => $Retn
                                      );
    }
    return $Returns;
  }

  private function formatSeries($Seq, $Label)
  {
    if ( APPDEBUG["FKT"] ) $this->CS->debug("FKT",__METHOD__);

    $Recs = $this->getCompleteArray(array($this->Prefix . "490" => array("a", "v")));
    if ( empty($Recs) )  return array();

    $Item = 0;
    $Sers = array();
    foreach ( $Recs as $Rec )
    {
      if ( isset($Rec["a"]["0"]) )
      {
        $Sers["item_".++$Item]  = array("text"   => $Rec["a"]["0"],
                                        "action" => array(
                                                          "type"   => "search",
                                                          "search" => array(
                                                                             "query" => "series(" . $this->clear($Rec["a"]["0"]) . ")",
                                                                             "type"  => "lukida"
                                                                           )
                                                         ),
                                        "more"   => array()
                                       );
      }
      if ( isset($Rec["v"]["0"]) )
      {
        $Sers["item_".++$Item]  = array("text"   => $Rec["v"]["0"],
                                        "action" => array(),
                                        "more"   => array()
                                       );
      }
    }

    $Series = array();
    if ( !empty($Sers) )
    {
      $Series["bib_".$Seq] = array(
                                    "label"  => $this->CS->getText("bibline.".$Label),
                                    "code"   => $Label,
                                    "items"  => $Sers
                                   );
    }
    return $Series;
  }

  private function formatSibling($Seq, $Label)
  {
    if ( APPDEBUG["FKT"] ) $this->CS->debug("FKT",__METHOD__);

    $Recs = $this->getCompleteArray(array($this->Prefix . "787" => array("d","h","i","n","t","w","z")));

    if ( empty($Recs) )  return array();

    $Item = 0;
    $Retn = array();
    foreach ( $Recs as $Rec )
    {
      if ( !isset($Rec["i"]["0"]) && !isset($Rec["t"]["0"])) continue;

      $Add   = array();
      if ( isset($Rec["n"]["0"]) )
      {
        $Add[] = $this->getSFTexts($Rec, "n");
      }
      else
      {
        $Text  = $this->getSFTexts($Rec, "i");
        $Add[] = $this->getSFTexts($Rec, "t");
        $Add[] = $this->getSFTexts($Rec, "d");
        $Add[] = $this->getSFTexts($Rec, "h");
        $Add   = array_filter($Add);
      }
      $Text = isset($Text) && trim($Text) ? $Text . ": " : "";

      if ( isset($Rec["w"][0]) && in_array(substr($Rec["w"][0],0,8), array("(DE-601)","(DE-627)") ) )
      {
        $Retn["item_".++$Item]  = array("text"   => trim($Text . implode(", ", $Add)),
                                        "action" => array(
                                                          "type"   => "search",
                                                          "search" => array(
                                                                            "query" => "id(" . trim(substr($Rec["w"]["0"],8)) . ")",
                                                                            "type"  => "lukida"
                                                                           )
                                                         ),
                                        "more"   => array()
                                       );
      }
      elseif ( isset( $Rec["z"]["0"]) )
      {
        $Retn["item_".++$Item]  = array("text"   =>trim($Text . implode(", ", $Add)),
                                        "action" => array(
                                                          "type"   => "search",
                                                          "search" => array("query" => "isn(" . $Rec["z"]["0"] . ")",
                                                                            "type"  => "lukida")
                                                         ),
                                        "more"   => array());
      }
      else
      {
        $Retn["item_".++$Item]  = array("text"   => trim($Text . implode(", ", $Add)),
                                        "action" => array()
                                        );
      }
      $Retn["item_".++$Item]  = array("display" => "newline");
    }

    $Siblings = array();
    if ( !empty($Retn) )
    {
      $Siblings["bib_".$Seq] = array(
                                         "label"  => $this->CS->getText("bibline.".$Label),
                                         "code"   => $Label,
                                         "items"  => $Retn
                                         );
    }
    return $Siblings;
  }

  private function formatSimple($Seq, $Label, $Filter)
  {
    if ( APPDEBUG["FKT"] ) $this->CS->debug("FKT",__METHOD__);

    $Recs = $this->prettyRecs($Filter);
    if ( empty($Recs) )  return array();

    return array(("bib_".$Seq) => array(
                                "label"  => $this->CS->getText("bibline.".$Label),
                                "code"   => $Label,
                                "items"  => $Recs
                               )
                );
  }

  private function formatSource($Seq, $Label)
  {
    if ( APPDEBUG["FKT"] ) $this->CS->debug("FKT",__METHOD__);

    $Recs = $this->Collection;
    if ( in_array("GVK", $Recs) && in_array("KXP", $Recs) ) $Recs = array_diff($Recs, array("GVK"));

    $Retn = array();
    $Item = 0;
    foreach ( $Recs as $Rec )
    {
      $CRec = isset($this->CS->collections[strtoupper($Rec)]) ? $this->CS->collections[strtoupper($Rec)] : array();

      if ( !isset($CRec["name"]) || !isset($CRec["link"]) )  continue;

      if ( in_array($Rec, array("KXP", "OLC", "SWB")) )
      {
        $Tmp = $this->CS->getCollAndIDFromPPN($this->ID);
        if ( isset($Tmp["id"]) )
        {
          $CRec["link"] .= "PPNSET?PPN=" . $Tmp["id"];
        }
      }

      $Retn["item_".++$Item]   = array("text"   => $CRec["name"],
                                       "action" => array(
                                                         "type"    => "link",
                                                         "link"    => array(
                                                                            "url"    => $CRec["link"],
                                                                            "target" => "_blank"
                                                                           )
                                                       ),
                                       "more"   => array()
                                      );
    }

    $Returns = array();
    if ( !empty($Retn) )
    {
      $Returns["bib_".$Seq] = array(
                                    "label"  => $this->CS->getText("bibline.".$Label),
                                    "code"   => $Label,
                                    "items"  => $Retn
                                   );
    }
    return $Returns;
  }

  private function uniqueSubjectChains($Retn)
  {
    if ( APPDEBUG["FKT"] ) $this->CS->debug("FKT",__METHOD__);

    $Chains = array();
    $Texts  = array();
    $Cur    = array();
    $CurTxt = array();

    foreach ( $Retn as $One )
    {
      if ( isset($One["display"]) && $One["display"] == "newline" )
      {
        if ( count($Cur) )  { $Chains[] = $Cur; $Texts[] = $CurTxt; }
        $Cur    = array();
        $CurTxt = array();
        continue;
      }
      $Cur[]    = $One;
      $CurTxt[] = isset($One["text"]) ? $One["text"] : "";
    }
    if ( count($Cur) )  { $Chains[] = $Cur; $Texts[] = $CurTxt; }

    $Keep = array_keys($this->CS->getUniqueMultArray($Texts));

    $Out  = array();
    $Item = 0;
    foreach ( $Keep as $K )
    {
      if ( count($Out) )  $Out["item_".++$Item] = array("display" => "newline");
      foreach ( $Chains[$K] as $One )  $Out["item_".++$Item] = $One;
    }
    return $Out;
  }

  private function formatSubject($Seq, $Label)
  {
    if ( APPDEBUG["FKT"] ) $this->CS->debug("FKT",__METHOD__);

    $Recs = $this->getCompleteArray(array($this->Prefix . "689" => array("I2"," 0","a","b","c","d","D","f","g","m","n","o","p","r","s","t")));
    $Item = 0;
    $Retn = array();

    foreach ( $Recs as $Rec )
    {
      if ( !isset($Rec["a"]["0"]) ) continue;

      if ( $Item > 0 && !isset($Rec["I2"]) ) $Retn["item_".++$Item]     = array("display" => "newline");

      $Add   = array();
      $Norm  = $this->getNorm($Rec);

      $NormGND = $this->subjectNormGND($Rec, "D");
      $Add[] = $this->getSFTexts($Rec, "a");
      $Add[] = $this->getSFTexts($Rec, "p", ". ");
      $Add[] = $this->getSFTexts($Rec, "n", ", ");
      $Add[] = $this->getSFTexts($Rec, "d", " (", ")");
      $Add[] = $this->getSFTexts($Rec, "b", " " );
      $Add[] = $this->getSFTexts($Rec, "t", ": ");
      $Add[] = $this->getSFTexts($Rec, "m", ", ");
      $Add[] = $this->getSFTexts($Rec, "o", ", ");
      $Add[] = $this->getSFTexts($Rec, "r", ", ");
      $Add[] = $this->getSFTexts($Rec, "s", ", ");
      $Add[] = $this->getSFTexts($Rec, "c", ", ");

      $Bra   = array();
      $Bra[] = $this->getSFTexts($Rec, "g");
      $Bra[] = $this->getSFTexts($Rec, "f");
      $Bra   = array_filter($Bra);
      if ( count($Bra) )  $Add[] = " (" . implode(", ", $Bra) . ") ";
      $Add   = array_filter($Add);

      $Retn["item_".++$Item]  = array("text"   => implode("", $Add),
                                      "action" => array(
                                                        "type"   => "search",
                                                        "search" => array(
                                                                           "query" => ( $Norm ) ? "norm(" . $Norm . ")" : "subject(" . $this->clear($Rec["a"]["0"]) . ")",
                                                                            "type"  => "lukida"
                                                                          )
                                                       ),
                                      "more"   => $NormGND
                                     );
    }

    $Ilns = $this->ilnsDesSuchraums();

    $Recs = $this->getCompleteArray(array($this->Prefix . "982" => array(" 0","2","a","s")));
    foreach ( $Recs as $Rec )
    {
      if ( !isset($Rec["a"]["0"]) ) continue;

      if ( count($Ilns) && isset($Rec["2"]["0"]) && !isset($Ilns[trim((string) $Rec["2"]["0"])]) )  continue;

      if ( $Item > 0 ) $Retn["item_".++$Item]     = array("display" => "newline");

      $Add   = array();
      $Norm  = $this->getNorm($Rec);
      $Add[] = $this->getSFTexts($Rec, "a");
      $Add   = array_filter($Add);

      $Retn["item_".++$Item]  = array("text"   => implode("", $Add),
                                      "action" => array(
                                                        "type"   => "search",
                                                        "search" => array(
                                                                           "query" => ( $Norm ) ? "norm(" . $Norm . ")" : "subject(" . $this->clear($Rec["a"]["0"]) . ")",
                                                                            "type"  => "lukida"
                                                                          )
                                                       ),

                                       "more"   => $this->subjectNormGND($Rec, "s")
                                      );
    }

    $Retn = $this->uniqueSubjectChains($Retn);

    $Returns = array();
    if ( !empty($Retn) )
    {
      $Returns["bib_".$Seq]     = array(
                                       "label"  => $this->CS->getText("bibline.".$Label),
                                       "code"   => $Label,
                                       "items"  => $Retn
                                      );
    }
    return $Returns;
  }

  private function formatTitle($Seq, $Label)
  {
    if ( APPDEBUG["FKT"] ) $this->CS->debug("FKT",__METHOD__);

    $Item = 0;
    $Retn = array();

    $Recs  = $this->getCompleteArray(array($this->Prefix . "245" => array("a", "b", "c")));
    foreach ( $Recs as $Rec )
    {
      if ( isset($Rec["a"]) && is_array($Rec["a"]) && count($Rec["a"]) )
      {
        foreach ($Rec["a"] as $A)
        {
          if ( $A )
          {
            $Retn["item_".++$Item]    = array("text"   => ($Item>1) ? ": " . $A : $A,
                                               "action" => array(),
                                               "more"   => array()
                                              );
          }
        }
      }
      if ( isset($Rec["b"]) && is_array($Rec["b"]) && count($Rec["b"]) )
      {
        foreach ($Rec["b"] as $B)
        {
          if ( $B )
          {
            $Retn["item_".++$Item]    = array("text"   => ($Item>1) ? ": " . $B : $B,
                                               "action" => array(),
                                               "more"   => array()
                                              );
          }
        }
      }
      if ( isset($Rec["c"]) && is_array($Rec["c"]) && count($Rec["c"]) )
      {
        foreach ($Rec["c"] as $C)
        {
          if ( $C )
          {
            $Retn["item_".++$Item]    = array("text"   => ($Item>1) ? "/ " . $C : $C,
                                               "action" => array(),
                                               "more"   => array()
                                              );
          }
        }
      }
    }

    $Recs  = $this->getCompleteArray(array($this->Prefix . "249" => array("a")));
    foreach ( $Recs as $Rec )
    {
      if ( isset($Rec["a"]) && is_array($Rec["a"]) && count($Rec["a"]) )
      {
        foreach ($Rec["a"] as $A)
        {
          if ( $A )
          {
            $Retn["item_".++$Item]    = array("text"   => ($Item>1) ? "; " . $A : $A,
                                               "action" => array(),
                                               "more"   => array()
                                              );
          }
        }
      }
    }

    $Returns = array();
    if ( !empty($Retn) )
    {
      $Returns["bib_".$Seq]     = array(
                                       "label"  => $this->CS->getText("bibline.".$Label),
                                       "code"   => $Label,
                                       "items"  => $Retn
                                      );
    }
    return $Returns;
  }

  private function formatVD16($Seq, $Label)
  {
    if ( APPDEBUG["FKT"] ) $this->CS->debug("FKT",__METHOD__);

    $Recs = $this->getCompleteArray(array($this->Prefix . "024" => array("2", "a",)));
    if ( empty($Recs) )  return array();

    $Item = 0;
    $VD   = array();
    foreach ( $Recs as $Rec )
    {
      if ( isset($Rec["2"]["0"]) && $Rec["2"]["0"] == "vd16" && isset($Rec["a"]["0"]) )
      {
        $VD["item_".++$Item]    = array("text"   => $Rec["a"]["0"],
                                        "action" => array(),
                                        "more"   => array()
                                       );
      }
    }

    $VDs = array();
    if ( !empty($VD) )
    {
      $VDs["bib_".$Seq]     = array(
                                    "label"  => $this->CS->getText("bibline.".$Label),
                                    "code"   => $Label,
                                    "items"  => $VD
                                   );
    }
    return $VDs;
  }

  private function formatVD17($Seq, $Label)
  {
    if ( APPDEBUG["FKT"] ) $this->CS->debug("FKT",__METHOD__);

    $Recs = $this->getCompleteArray(array($this->Prefix . "024" => array("2", "a")));
    if ( empty($Recs) )  return array();

    $Item = 0;
    $VD   = array();
    foreach ( $Recs as $Rec )
    {
      if ( isset($Rec["2"]["0"]) && $Rec["2"]["0"] == "vd17" && isset($Rec["a"]["0"]) )
      {
        $VD["item_".++$Item]    = array("text"   => $Rec["a"]["0"],
                                        "action" => array(),
                                        "more"   => array()
                                       );
      }
    }

    $VDs = array();
    if ( !empty($VD) )
    {
      $VDs["bib_".$Seq]     = array(
                                    "label"  => $this->CS->getText("bibline.".$Label),
                                    "code"   => $Label,
                                    "items"  => $VD
                                   );
    }
    return $VDs;
  }

  private function formatVD18($Seq, $Label)
  {
    if ( APPDEBUG["FKT"] ) $this->CS->debug("FKT",__METHOD__);

    $Recs = $this->getCompleteArray(array($this->Prefix . "024" => array("2", "a")));
    if ( empty($Recs) )  return array();

    $Item = 0;
    $VD   = array();
    foreach ( $Recs as $Rec )
    {
      if ( isset($Rec["2"]["0"]) && $Rec["2"]["0"] == "vd18" && isset($Rec["a"]["0"]) )
      {
        $VD["item_".++$Item]    = array("text"   => $Rec["a"]["0"],
                                        "action" => array(),
                                        "more"   => array()
                                       );
      }
    }

    $VDs = array();
    if ( !empty($VD) )
    {
      $VDs["bib_".$Seq]     = array(
                                    "label"  => $this->CS->getText("bibline.".$Label),
                                    "code"   => $Label,
                                    "items"  => $VD
                                   );
    }
    return $VDs;
  }

  private function formatDOI($Seq, $Label)
  {
    if ( APPDEBUG["FKT"] ) $this->CS->debug("FKT",__METHOD__);

    $Recs = $this->getCompleteArray(array($this->Prefix . "024" => array("2", "a")));
    if ( empty($Recs) )  return array();

    $Item = 0;
    $DOI  = array();
    foreach ( $Recs as $Rec )
    {
      if ( isset($Rec["2"]["0"]) && $Rec["2"]["0"] == "doi" && isset($Rec["a"]["0"]) )
      {
        $DOI["item_".++$Item]    = array("text"   => $Rec["a"]["0"],
                                         "action" => array(
                                                           "type"   => "link",
                                                           "link"    => array(
                                                                              "url"    => "https://www.doi.org/" . $Rec["a"]["0"],
                                                                              "target" => "_blank"
                                                                             )
                                                          ),
                                         "more"   => array()
                                       );
      }
    }

    $DOIs = array();
    if ( !empty($DOI) )
    {
      $DOIs["bib_".$Seq]     = array(
                                    "label"  => $this->CS->getText("bibline.".$Label),
                                    "code"   => $Label,
                                    "items"  => $DOI
                                   );
    }
    return $DOIs;
  }

  private function formatCover($Seq, $Label)
  {
    if ( APPDEBUG["FKT"] ) $this->CS->debug("FKT",__METHOD__);

    $Recs = $this->getCompleteArray(array($this->Prefix . "563" => array("a", "u")));
    if ( empty($Recs) )  return array();

    $Item = 0;
    $Retn = array();
    foreach ( $Recs as $Rec )
    {
      $Add   = array();
      $Add[] = $this->getSFTexts($Rec, "a");

      $Link  = $this->getSFTexts($Rec, "u");

      $Key["item_".++$Item]  = array("text"   => implode(", ", $Add),
                                     "action" => array(
                                                       "type"   => "link",
                                                       "link"    => $Link ? array("url"    => $Link,
                                                                                  "target" => "_blank")
                                                                          : array()
                                                      ),
                                      "more"   => array()
                                     );
      $Key["item_".++$Item]  = array("display" => "newline");
    }

    $Keys = array();
    if ( !empty($Key) )
    {
      $Keys["bib_".$Seq]     = array(
                                    "label"  => $this->CS->getText("bibline.".$Label),
                                    "code"   => $Label,
                                    "items"  => $Key
                                   );
    }
    return $Keys;
  }

  private function formatKeys($Seq, $Label)
  {
    if ( APPDEBUG["FKT"] ) $this->CS->debug("FKT",__METHOD__);

    $Recs = $this->getCompleteArray(array($this->Prefix . "024" => array("2", "a", "q")));

    $Item = 0;
    $Key  = array();
    foreach ( $Recs as $Rec )
    {
      $Tmp   = strtolower($this->getSFTexts($Rec, "q"));
      if ( substr($Tmp, 0, 5) == "nicht" )  continue;

      if ( isset($Rec["2"]["0"]) && $Rec["2"]["0"] == "doi" && isset($Rec["a"]["0"]) )  continue;

      $Add   = array();
      $Add[] = $this->getSFTexts($Rec, "q", "", ": ");
      $Add[] = $this->getSFTexts($Rec, "2", "", ": ");
      $Add[] = $this->getSFTexts($Rec, "a");

      $Key["item_".++$Item]  = array("text"   => implode("", $Add),
                                     "action" => array(),
                                     "more"   => array()
                                    );
      $Key["item_".++$Item]  = array("display" => "newline");
    }

    $Gesehen = array();
    foreach ( $this->getCompleteArray(array($this->Prefix . "035" => array("a"))) as $Rec )
    {
      if ( !isset($Rec["a"]["0"]) )  continue;
      if ( !preg_match('/^\(DE-599\)(ZDB.+)$/', trim($Rec["a"]["0"]), $Tr) )  continue;
      if ( isset($Gesehen[$Tr[1]]) )  continue;
      $Gesehen[$Tr[1]] = true;

      $Key["item_".++$Item]  = array("text"   => "ZDB-ID: " . $Tr[1],
                                     "action" => array(),
                                     "more"   => array()
                                    );
      $Key["item_".++$Item]  = array("display" => "newline");
    }

    $Keys = array();
    if ( !empty($Key) )
    {
      $Keys["bib_".$Seq]     = array(
                                    "label"  => $this->CS->getText("bibline.".$Label),
                                    "code"   => $Label,
                                    "items"  => $Key
                                   );
    }
    return $Keys;
  }

  private function formatNotes($Seq, $Label)
  {
    if ( APPDEBUG["FKT"] ) $this->CS->debug("FKT",__METHOD__);

    $Item = 0;
    $Note = array();

    $Recs = $this->getCompleteArray(array($this->Prefix . "500" => array("a")));

    foreach ( $Recs as $Rec )
    {
      if ( isset($Rec["a"]["0"]) )
      {
        $Note["item_".++$Item]  = array("text"   => $Rec["a"]["0"],
                                        "action" => array(),
                                        "more"   => array()
                                       );
        $Note["item_".++$Item]  = array("display" => "newline");
      }
    }

    $Recs = $this->getCompleteArray(array($this->Prefix . "255" => array("a","b","c")));

    foreach ( $Recs as $Rec )
    {
      if ( isset($Rec["a"]["0"]) )
      {
        $Note["item_".++$Item]  = array("code"   => "bibline.scale",
                                        "action" => array(),
                                        "more"   => array()
                                       );
        $Note["item_".++$Item]  = array("text"   => ": " . $Rec["a"]["0"],
                                        "action" => array(),
                                        "more"   => array()
                                       );
        $Note["item_".++$Item] = array("display" => "newline");
      }
      if ( isset($Rec["b"]["0"]) )
      {
        $Note["item_".++$Item]  = array("code"   => "bibline.projection",
                                        "action" => array(),
                                        "more"   => array()
                                       );
        $Note["item_".++$Item]  = array("text"   => ": " . $Rec["b"]["0"],
                                        "action" => array(),
                                        "more"   => array()
                                       );
        $Note["item_".++$Item] = array("display" => "newline");
      }
      if ( isset($Rec["c"]["0"]) )
      {
        $Note["item_".++$Item]  = array("code"   => "bibline.coordinates",
                                        "action" => array(),
                                        "more"   => array()
                                       );
        $Note["item_".++$Item]  = array("text"   => ": " . $Rec["c"]["0"],
                                        "action" => array(),
                                        "more"   => array()
                                       );
        $Note["item_".++$Item] = array("display" => "newline");
      }
    }

    $Recs = $this->getCompleteArray(array($this->Prefix . "383" => array("c")));

    foreach ( $Recs as $Rec )
    {
      if ( isset($Rec["c"]["0"]) )
      {
        $Note["item_".++$Item]  = array("code"   => "bibline.marking",
                                        "action" => array(),
                                        "more"   => array()
                                       );
        $Note["item_".++$Item]  = array("text"   => $Rec["c"]["0"],
                                        "action" => array(),
                                        "more"   => array()
                                       );
        $Note["item_".++$Item]  = array("display" => "newline");
      }
    }

    $Recs = $this->getCompleteArray(array($this->Prefix . "384" => array("a")));

    foreach ( $Recs as $Rec )
    {
      if ( isset($Rec["a"]["0"]) )
      {
        $Note["item_".++$Item]  = array("code"   => "bibline.tone",
                                        "action" => array(),
                                        "more"   => array()
                                       );
        $Note["item_".++$Item]  = array("text"   => $Rec["a"]["0"],
                                        "action" => array(),
                                        "more"   => array()
                                       );
        $Note["item_".++$Item]  = array("display" => "newline");
      }
    }

    $Notes = array();
    if ( !empty($Note) )
    {
      $Notes["bib_".$Seq]     = array(
                                    "label"  => $this->CS->getText("bibline.".$Label),
                                    "code"   => $Label,
                                    "items"  => $Note
                                   );
    }
    return $Notes;
  }

  private function getArticleLinks()
  {
    if ( APPDEBUG["FKT"] ) $this->CS->debug("FKT",__METHOD__);

    if ( $this->Format != "Article")  return false;

    $Links = array();
    if ( isset($this->Convert[$this->Prefix . "981"]) )
    {
      foreach ( $this->Convert[$this->Prefix . "981"] as $Record )
      {
        foreach ( $Record as $Key => $Values )
        {
          if ( $Key == "r" )
          {
            foreach ( $Values as $Value )
            {
              if ( !in_array($Value, $Links) )
              {
                $Links[]  = $Value;
              }
            }
          }
        }
      }
    }

    if ( isset($this->Convert[$this->Prefix . "856"]) )
    {
      foreach ( $this->Convert[$this->Prefix . "856"] as $Record )
      {
        foreach ( $Record as $Key => $Values )
        {
          if ( $Key == "u" )
          {
            foreach ( $Values as $Value )
            {
              if ( !in_array($Value, $Links) )
              {
                $Links[]  = $Value;
              }
            }
          }
        }
      }
    }
    return count($Links) ? true : false;
  }

  private function getArtCode()
  {
    if ( APPDEBUG["FKT"] ) $this->CS->debug("FKT",__METHOD__);

    return isset($this->Convert[$this->Prefix . "773"][0]["7"][0]) ? $this->Convert[$this->Prefix . "773"][0]["7"][0] : "";
  }

  private function getOwnOrg()
  {
    if ( APPDEBUG["FKT"] ) $this->CS->debug("FKT",__METHOD__);

    return isset($this->Convert[$this->Prefix . "535"][0]["a"][0]) ? $this->Convert[$this->Prefix . "535"][0]["a"][0] : "";
  }

  private function getBiblio()
  {
    if ( APPDEBUG["FKT"] ) $this->CS->debug("FKT",__METHOD__);

    $this->OriginalYear = ( isset($this->Convert[$this->Prefix . "008"]) && substr($this->Convert[$this->Prefix . "008"],6,1) == "r" )
                          ? substr($this->Convert[$this->Prefix . "008"],11,4)
                          : "";

    $Fields  = array();
    $Fields += $this->formatTitle  (   "01", "title");
    $Fields += $this->formatSimple (   "02", "uniformtitle",     array("240" => array("a","f","g","k","m","n","o","p","r","s")));
    $Fields += $this->formatPart   (   "03", "part");
    $Fields += $this->formatPeople (   "04", "author",
                                       "05", "associates");
    $Fields += $this->formatFormat (   "06", "format");
    $Fields += $this->formatCorp   (   "07", "corporation");
    $Fields += $this->formatSeries (   "08", "series");
    $Fields += $this->formatIn     (   "09", "in");
    $Fields += $this->formatEdition(   "10", "edition");
    $Fields += $this->formatSimple (   "11", "recording",        array("518" => array("a")));
    $Fields += $this->formatPublish(   "12", "publisher");
    $Fields += $this->formatSimple (   "13", "originalyear",     array("534" => array("c")));
    $Fields += $this->formatLang   (   "14", "language");
    $Fields += $this->formatLangOrg(   "15", "languageoriginal");
    $Fields += $this->formatSize   (   "16", "size");
    $Fields += $this->formatNotes  (   "17", "notes");
    $Fields += $this->formatInclude(   "18", "includes");
    $Fields += $this->formatSimple (   "19", "frequency",        array("515" => array("a")));
    $Fields += $this->formatOtherEs(   "20", "othereditions");
    $Fields += $this->formatRemarks(   "21", "remarks");
    $Fields += $this->formatSeeAlso(   "22", "seealso");
    $Fields += $this->formatSimple (   "23", "languagenotes",    array("546" => array("a")));
    $Fields += $this->formatDissert(   "24", "dissertation");
    $Fields += $this->formatCits   (   "25", "citation");
    $Fields += $this->formatSimple (   "26", "computerfile",     array("256" => array("a")));
    $Fields += $this->formatSimple (   "27", "systemdetails",    array("538" => array("a")));
    $Fields += $this->formatSimple (   "28", "issn",             array("022" => array("a"),
                                                                       "773" => array("x")));
    $Fields += $this->formatISBN   (   "29", "isbn");
    $Fields += $this->formatSimple (   "30", "summary",          array("520" => array("a")));
    $Fields += $this->formatAddInfo(   "31", "additionalinfo");
    $Fields += $this->formatProven (   "32", "provenance");
    $Fields += $this->formatSubject(   "33", "subject");
    $Fields += $this->formatCover  (   "34", "cover");
    $Fields += $this->formatGenre  (   "35", "genre");
    $Fields += $this->formatClass  (   "36", "classification");
    $Fields += $this->formatSource (   "37", "source");
    $Fields += $this->formatSibling(   "38", "sibling");
    $Fields += $this->formatLicense(   "39", "license");
    $Fields += $this->formatSimple (   "40", "contents",          array("505" => array("a", "t")));
    $Fields += $this->formatSimple (   "41", "worktitle",         array("130" => array("a")));
    $Fields += $this->formatAddTitl(   "42", "addtitle");
    $Fields += $this->formatClassL (   "43", "classlocal");
    $Fields += $this->formatVD16   (   "44", "vd16");
    $Fields += $this->formatVD17   (   "45", "vd17");
    $Fields += $this->formatVD18   (   "46", "vd18");
    $Fields += $this->formatDOI    (   "47", "doi");
    $Fields += $this->formatKeys   (   "48", "keys");
    $Fields += $this->formatFinger (   "49", "fingerprint");
    $Fields += $this->formatSimple (   "50", "appearancehistory", array("362" => array("a")));
    $Fields += $this->formatSimple (   "51", "orderid",           array("028" => array("a")));
    $Fields += $this->formatCollec (   "52", "collection");

    $Fields += $this->internArtl   (  "100", "article");
    $Fields += $this->internPubl   (  "101", "publisher");
    $Fields += $this->internDetl   (  "102", "details");
    $Fields += $this->internLink   (  "103", "links");

    $Fields  = $this->AddOriginalCharacters($Fields);

    $Groups = array("title" => array(), "detail" => array());
    foreach ( $this->biblineLayout() as $Section => $Lines )
    {
      foreach ( $Lines as $ID => $Type )
      {
        if ( isset($Fields[$ID]) )  $Groups[$Section][] = array("id" => $ID, "type" => $Type);
      }
    }

    foreach ( $Fields as $ID => $Field )
    {
      if ( !is_array($Field) || ( isset($Field["label"]) && $Field["label"] !== "" ) )  continue;
      $Code = isset($Field["code"]) ? $Field["code"] : $ID;
      $Lbl  = $this->CS->getText("bibline." . $Code);
      if ( $Lbl === "" )  $Lbl = $this->CS->getText("bibline." . $ID);
      if ( $Lbl !== "" )  $Fields[$ID]["label"] = $Lbl;
    }

    return array("label"      => $this->CS->getText("bibgroup.bibliographicdata"),
                 "code"       => "bibliographicdata",
                 "before"     => array(),
                 "groups"     => $Groups,
                 "groupitems" => $Fields,
                 "after"      => array());
  }

  private function biblineLayout()
  {
    return array(
      "title" => array(
        "bib_01" => "norm",
        "bib_03" => "norm",
        "bib_41" => "norm",
        "bib_42" => "norm",
        "bib_04" => "block",
        "bib_05" => "block",
        "bib_07" => "norm",
        "bib_12" => "norm",
        "bib_10" => "norm",
      ),
      "detail" => array(
        "bib_02" => "norm",
        "bib_35" => "norm",
        "bib_16" => "norm",
        "bib_40" => "long",
        "bib_18" => "norm",
        "bib_28" => "norm",
        "bib_29" => "norm",
        "bib_14" => "norm",
        "bib_15" => "norm",
        "bib_13" => "norm",
        "bib_50" => "norm",
        "bib_17" => "long",
        "bib_11" => "norm",
        "bib_24" => "norm",
        "bib_25" => "long",
        "bib_26" => "norm",
        "bib_27" => "long",
        "bib_34" => "norm",
        "bib_32" => "norm",
        "bib_49" => "norm",
        "bib_30" => "long",
        "bib_19" => "norm",
        "bib_23" => "norm",
        "bib_39" => "norm",
        "bib_08" => "norm",
        "bib_20" => "norm",
        "bib_31" => "norm",
        "bib_22" => "norm",
        "bib_21" => "norm",
        "bib_33" => "norm",
        "bib_36" => "class",
        "bib_52" => "norm",
        "bib_43" => "norm",
        "bib_44" => "norm",
        "bib_45" => "norm",
        "bib_46" => "norm",
        "bib_37" => "norm",
        "bib_38" => "norm",
        "bib_48" => "norm",
        "bib_47" => "norm",
        "bib_51" => "norm",
      ),
    );
  }

  private function getCDBIsil($Shortcut)
  {
    // Nachschlagen in der zentralen GBV-Datenbank gibt es nur in Lukida Pro
    return array();
  }

  private function kachelLogo()
  {
    $Liste = $this->logoSammlungen();
    if ( !count($Liste) )  return null;

    foreach ( $this->sammlungenDesSatzes() as $Hat )
      if ( in_array($this->logoSchluessel($Hat), $Liste) )  return true;

    return false;
  }

  private function sammlungenDesSatzes()
  {
    $Werte = array();
    foreach ( $this->getCompleteArray(array($this->Prefix . "935" => array("a"))) as $Rec )
      if ( isset($Rec["a"]["0"]) )  $Werte[] = $Rec["a"]["0"];

    return $Werte;
  }

  private function logoSchluessel($Wert)
  {
    return strtolower(str_replace(" ", "", (string) $Wert));
  }

  private function logoSammlungen()
  {
    $Roh = trim((string) $this->CS->conf("style", "cardlogocollections"));
    if ( $Roh === "" )  return array();

    $Liste = array();
    foreach ( explode(",", $Roh) as $Eine )
    {
      $Eine = $this->logoSchluessel(trim($Eine));
      if ( $Eine !== "" )  $Liste[] = $Eine;
    }
    return $Liste;
  }

  private function ilnsDesSuchraums()
  {
    $Level = isset($this->CS->search["level"]) ? trim((string) $this->CS->search["level"]) : "";
    if ( $Level === "" || !isset($this->CS->levels[$Level]["holdingilns"]) )  return array();

    $Ilns = array();
    foreach ( (array) $this->CS->levels[$Level]["holdingilns"] as $Wert )
    {
      if ( preg_match('/(\d+)\s*$/', (string) $Wert, $T) )  $Ilns[$T[1]] = true;
    }
    return $Ilns;
  }

  private function getCompleteArray($Filter)
  {
    if ( APPDEBUG["FKT"] ) $this->CS->debug("FKT",__METHOD__);

    $Output = array();
    $Count  = 0;
    foreach ( $Filter as $Field => $Subfields )
    {
      if ( array_key_exists($Field, $this->Convert) )
      {
        foreach ($this->Convert[$Field] as $Record)
        {
          $Tmp = array();
          foreach ( $Record as $Key => $Value )
          {
            if ( in_array((string)$Key, $Subfields) )
            {
              $Tmp[(string)$Key] = $Value;
            }
          }
          $Output[$Field."_".$Count] = $Tmp;
          $Count++;
        }
      }
    }
    return $Output;
  }

  private function getFlatArray($Filter)
  {
    if ( APPDEBUG["FKT"] ) $this->CS->debug("FKT",__METHOD__);

    $Output = array();
    $Count  = 0;
    foreach ( $Filter as $Field => $Subfields )
    {
      if ( array_key_exists($Field, $this->Convert) )
      {
        foreach ($this->Convert[$Field] as $Record)
        {
          foreach ($Subfields as $Subfield)
          {
            if ( isset($Record[$Subfield]) )
            {
              if ( is_array($Record[$Subfield]) )
              {
                $Output[$Field."_".$Count][$Subfield] = implode(", ", $Record[$Subfield]);
              }
              else
              {
                $Output[$Field."_".$Count][$Subfield] = $Record[$Subfield];
              }
            }
          }
          $Count++;
        }
      }
    }
    return $Output;
  }

  private function repairScheme($Url)
  {
    return preg_replace('~^(https?):/(?!/)~i', '$1://', (string) $Url);
  }

  private function getItems()
  {
    if ( APPDEBUG["FKT"] ) $this->CS->debug("FKT",__METHOD__);

    $Items    = array();
    $AddItems = array();

    $HWithO   = ( isset($this->CS->configs["item"]["hapticwithonline"])   && $this->CS->configs["item"]["hapticwithonline"]   == "y" ) ? true                                                                       : false;
    $PLocO    = ( isset($this->CS->configs["item"]["preferlocalonlines"]) && $this->CS->configs["item"]["preferlocalonlines"] == "y" ) ? true                                                                       : false;
    $OnlyMast = ( isset($this->CS->configs["item"]["onlymasterlink"])     && $this->CS->configs["item"]["onlymasterlink"]     == "y" ) ? true                                                                       : false;
    $SkipAUIs = ( isset($this->CS->configs["item"]["skipauis"])           && trim($this->CS->configs["item"]["skipauis"]) )            ? array_filter(explode(",", $this->CS->configs["item"]["skipauis"]))         : array();
    $SkipI2s  = ( isset($this->CS->configs["item"]["skipi2s"])            && trim($this->CS->configs["item"]["skipi2s"]) )             ? array_filter(explode(",", $this->CS->configs["item"]["skipi2s"]))          : array();
    $SkipMans = ( isset($this->CS->configs["item"]["skipmans"])           && trim($this->CS->configs["item"]["skipmans"]) )            ? array_filter(explode(",", $this->CS->configs["item"]["skipmans"]))         : array();
    $BlackCom = ( isset($this->CS->configs["item"]["commentblacklist"])   && trim($this->CS->configs["item"]["commentblacklist"]) )    ? array_filter(explode(",", $this->CS->configs["item"]["commentblacklist"])) : array();

    $Items  = array();

    $MExps = $this->getCompleteArray(array($this->Prefix . "980" => array("1","2","a","b","c","d","e","f","g","h","j","k","l","u","x","y","z")));

    $M980s = $MExps;

    $ILNGs = array();
    foreach ($MExps as $Rec)
    {
      $MAN = isset($Rec["y"][0]) ? $Rec["y"][0] : "";
      if ( $MAN && count($SkipMans) )
      {
        $Break = false;
        foreach ( $SkipMans as $SM )
        {
          if ( substr($SM, -1) == "*" )
          {
            $Pos = strpos($SM, "*");
            if ( substr($SM, 0, $Pos) == substr($MAN, 0, $Pos) ) $Break = true;
          }
          else
          {
             if ( $SM == $MAN )  $Break = true;
          }
          if ( $Break ) break;
        }
        if ( $Break ) continue;
      }

      $ALI = isset($Rec["e"][0]) ? $Rec["e"][0] : "";
      if ( !$ALI
        || !$ALI && in_array("empty",        $SkipAUIs)
        ||  $ALI && in_array($ALI,           $SkipAUIs)
        ||  $ALI && in_array($ALI.".online", $SkipAUIs) &&  $this->Online )
      {
        $AddItems[] = $Rec;
      }

      if ( !$this->Online || $HWithO )
      {
        if ( !$ALI && in_array("empty",        $SkipAUIs)
          ||  $ALI && in_array($ALI,           $SkipAUIs)
          ||  $ALI && in_array($ALI.".online", $SkipAUIs) &&  $this->Online )
        {
          continue;
        }

        $this->Counts["physical"]++;

        $ExpID = isset($Rec["b"][0]) ? strtoupper($Rec["b"][0]) : $this->Counts["physical"];

        if ( isset($Rec["2"][0]) && isset($Rec["b"][0]) ) $ILNGs[$Rec["2"][0]][] = strtoupper($Rec["b"][0]);

        foreach ($Rec as $Code => $SubRecs)
        {
          if ( is_array($SubRecs) )
          {
            $Items[$ExpID][$Code] = $SubRecs;
          }
        }
        $Items[$ExpID]["loc"] = isset($Rec["f"][0]) ? $Rec["f"][0]  : "default";
        $Items[$ExpID]["epn"] = $ExpID;
      }

      if ( count($ILNGs) ) $this->ILNGroups = $ILNGs;
    }

    if ( !$this->Online && !$HWithO ) return $Items;
    if ( !isset($this->CS->configs["item"]["links"]) || $this->CS->configs["item"]["links"] == "n" ) return $Items;

    $OA    = $this->get856OA();

    $MExps     = $this->getFlatArray(array($this->Prefix . "981" => array("1","2","r","w","y")));
    $Grps      = array();
    $GrpI      = 0;
    $LicLinks = array();

    foreach ($MExps as &$Rec)
    {
      if ( !isset($Rec["2"]) || !isset($Rec["1"]) )  continue;
      if ( !isset($Rec["r"]) && !isset($Rec["w"]) ) continue;

      if ( isset($Rec["r"]) )
      {
        $GID = ++$GrpI;
        $Grps[$GID] = array(
                            "iln" => $Rec["2"],
                            "enr" => $Rec["1"]
                           );
        $Grps[$GID]["link"] = $Rec["r"];
        $Grps[$GID]["host"] = $this->CS->getHost($Rec["r"]);
        if ( isset($Rec["y"]) )  $Grps[$GID]["text"] = $Rec["y"];
      }

      if ( isset($Rec["w"]) )
      {
        $UText = isset($Rec["y"])  ? $Rec["y"] : $this->CS->getText("bibline.license");
        $LicLinks[$Rec["2"] . "_" .$Rec["1"]][] = array(
                                                        "usagetext" => $UText,
                                                        "usagelink" => $Rec["w"]
                                                       );
      }
    }

    foreach ($Grps as &$GVals)
    {
      $ExpID = "onlinelocal_" . $this->Counts["online"]++;
      $Items[$ExpID] = $GVals;
      $Items[$ExpID]["loc"] = "onlinelocal";
      $Items[$ExpID]["epn"] = $ExpID;
      $Items[$ExpID]["oa"]  = $OA;

      if ( isset($LicLinks[$Items[$ExpID]["iln"] . "_" . $Items[$ExpID]["enr"]]) )
      {
        $Items[$ExpID]["usagelinks"] = $LicLinks[$Items[$ExpID]["iln"] . "_" . $Items[$ExpID]["enr"]];
      }

      if ( count($AddItems) )
      {
        $Items[$ExpID]["comment"] = array();
        $Items[$ExpID]["stock"]   = array();
        foreach ( $AddItems as $AIRec )
        {
          if ( isset($AIRec["k"]) )
          {
            foreach ( $AIRec["k"] as $K )
            {
              if (!in_array($K, $BlackCom)) $Items[$ExpID]["comment"][] = $K;
            }
          }
          if ( isset($AIRec["g"]) )
          {
            foreach ( $AIRec["g"] as $G )
            {
              $Items[$ExpID]["stock"][] =  (substr($G,0,8) == "Bestand:") ? trim(substr($G, 8)) : $G;
            }
          }
        }
      }

      if ( count($M980s) )
      {
        foreach ( $M980s as $M980Rec )
        {
          if ( !isset($M980Rec["1"][0]) || $M980Rec["1"][0] != $Items[$ExpID]["enr"] )  continue;
          if ( !isset($M980Rec["2"][0]) || $M980Rec["2"][0] != $Items[$ExpID]["iln"] )  continue;

          if ( !isset($Items[$ExpID]["a"]) && isset($M980Rec["a"]) )  $Items[$ExpID]["a"] = $M980Rec["a"];
          if ( !isset($Items[$ExpID]["b"]) && isset($M980Rec["b"]) )  $Items[$ExpID]["b"] = $M980Rec["b"];
          if ( !isset($Items[$ExpID]["c"]) && isset($M980Rec["c"]) )  $Items[$ExpID]["c"] = $M980Rec["c"];
          if ( !isset($Items[$ExpID]["d"]) && isset($M980Rec["d"]) )  $Items[$ExpID]["d"] = $M980Rec["d"];
          if ( !isset($Items[$ExpID]["e"]) && isset($M980Rec["e"]) )  $Items[$ExpID]["e"] = $M980Rec["e"];
          if ( !isset($Items[$ExpID]["f"]) && isset($M980Rec["f"]) )  $Items[$ExpID]["f"] = $M980Rec["f"];
          if ( !isset($Items[$ExpID]["g"]) && isset($M980Rec["g"]) )  $Items[$ExpID]["g"] = $M980Rec["g"];
          if ( !isset($Items[$ExpID]["h"]) && isset($M980Rec["h"]) )  $Items[$ExpID]["h"] = $M980Rec["h"];
          if ( !isset($Items[$ExpID]["j"]) && isset($M980Rec["j"]) )  $Items[$ExpID]["j"] = $M980Rec["j"];
          if ( !isset($Items[$ExpID]["k"]) && isset($M980Rec["k"]) )  $Items[$ExpID]["k"] = $M980Rec["k"];
          if ( !isset($Items[$ExpID]["l"]) && isset($M980Rec["l"]) )  $Items[$ExpID]["l"] = $M980Rec["l"];
          if ( !isset($Items[$ExpID]["u"]) && isset($M980Rec["u"]) )  $Items[$ExpID]["u"] = $M980Rec["u"];
          if ( !isset($Items[$ExpID]["x"]) && isset($M980Rec["x"]) )  $Items[$ExpID]["x"] = $M980Rec["x"];
          if ( !isset($Items[$ExpID]["y"]) && isset($M980Rec["y"]) )  $Items[$ExpID]["y"] = $M980Rec["y"];
          if ( !isset($Items[$ExpID]["z"]) && isset($M980Rec["z"]) )  $Items[$ExpID]["z"] = $M980Rec["z"];
        }
      }
    }

    if ( $this->Counts["online"] > 0 && $PLocO ) return $Items;

    $MasterLink = "";
    $MExps  = $this->getFlatArray(array($this->Prefix . "856" => array("I1","I2","3","t","u","x","y","z")));

    foreach ($MExps as $Rec)
    {
      $ExpID = "onlineglobal_" . $this->Counts["online"]++;

      if ( isset($Rec["I2"]) && in_array($Rec["I2"], $SkipI2s) ) continue;

      if ( ( !isset($Rec["I1"]) || !isset($Rec["I2"]) )
        && ( !( isset($Rec["I1"]) && $Rec["I1"] == "4"
          && isset($Rec["I2"]) && $Rec["I2"] == "2" ) )

          && ( ( isset($this->CS->configs["item"]["masterlinkany"])
              && $this->CS->configs["item"]["masterlinkany"] == "y" )
            || (isset($Rec["x"]) && $Rec["x"] == "Resolving-System") )
        && !$MasterLink && isset($Rec["u"]) )
      {
        $MasterLink = $Rec["u"];
      }
      else
      {
        if ( $OnlyMast )  continue;
      }

      $Text = [];
      foreach ($Rec as $Code => $Value)
      {
        if ( $Code == "3")  $Text[] = $Value;
        if ( $Code == "t")  $Text[] = $Value;
        if ( $Code == "x")  $Text[] = $Value;
        if ( $Code == "y")  $Text[] = $Value;
        if ( $Code == "z")  $Text[] = $Value;
        if ( $Code == "u")
        {
          $Value = $this->repairScheme($Value);
          $Items[$ExpID]["link"] = $Value;
          $Items[$ExpID]["host"] = $this->CS->getHost($Value);
        }
      }
      $Items[$ExpID]["text"] = implode(", ", $Text);
      $Items[$ExpID]["oa"]   = $OA;

      if ( count($AddItems) )
      {
        $Items[$ExpID]["comment"] = array();
        $Items[$ExpID]["stock"]   = array();
        foreach ( $AddItems as $AIRec )
        {
          if ( isset($AIRec["k"]) )
          {
            foreach ( $AIRec["k"] as $K )
            {
              $Items[$ExpID]["comment"][] = $K;
            }
          }
          if ( isset($AIRec["g"]) )
          {
            foreach ( $AIRec["g"] as $G )
            {
              $Items[$ExpID]["stock"][] =  (substr($G,0,8) == "Bestand:") ? trim(substr($G, 8)) : $G;
            }
          }
        }
      }

      $Items[$ExpID]["loc"]  = "onlineglobal";
      $Items[$ExpID]["epn"]  = $ExpID;
    }
    $this->MasterLink = $MasterLink;

    return $Items;
  }

  private function addCallSigns($Items)
  {
    if ( APPDEBUG["FKT"] ) $this->CS->debug("FKT",__METHOD__);

    if ( !is_array($Items) || !count($Items) )  return $Items;

    $Recs = $this->getCompleteArray(array($this->Prefix . "995" => array("1", "2", "a")));
    if ( empty($Recs) )  return $Items;

    $Erste = function($Wert) { return is_array($Wert) ? (isset($Wert[0]) ? (string) $Wert[0] : "") : (string) $Wert; };

    $Zeichen = array();
    foreach ( $Recs as $Rec )
    {
      $ILN = $Erste(isset($Rec["2"]) ? $Rec["2"] : "");
      $ENR = $Erste(isset($Rec["1"]) ? $Rec["1"] : "");
      if ( $ILN === "" || $ENR === "" || !isset($Rec["a"]) )  continue;
      foreach ( (array) $Rec["a"] as $A )
      {
        $A = trim((string) $A);
        if ( $A !== "" )  $Zeichen[$ILN . "_" . $ENR][] = $A;
      }
    }
    if ( !count($Zeichen) )  return $Items;

    foreach ( $Items as $ID => $Item )
    {
      $ILN = $Erste(isset($Item["2"]) ? $Item["2"] : (isset($Item["iln"]) ? $Item["iln"] : ""));
      $ENR = $Erste(isset($Item["1"]) ? $Item["1"] : (isset($Item["enr"]) ? $Item["enr"] : ""));
      if ( $ILN === "" || $ENR === "" )  continue;
      if ( isset($Zeichen[$ILN . "_" . $ENR]) )  $Items[$ID]["call"] = array_values(array_unique($Zeichen[$ILN . "_" . $ENR]));
    }

    return $Items;
  }

  private function getLinks()
  {
    if ( APPDEBUG["FKT"] ) $this->CS->debug("FKT",__METHOD__);

    if ( !isset($this->CS->configs["item"]["links"]) || $this->CS->configs["item"]["links"] == "n" ) return array();

    $HWithO   = ( isset($this->CS->configs["item"]["hapticwithonline"])   && $this->CS->configs["item"]["hapticwithonline"]   == "y" ) ? true : false;
    if ( !$this->Online && !$HWithO ) return array();

    $Links = array();

    $MExps = $this->getCompleteArray(array($this->Prefix . "776" => array("I2", "d", "i", "n", "o")));

    foreach ($MExps as $Rec)
    {
      if ( !isset($Rec["I2"]["0"]) || $Rec["I2"]["0"] != "8" ) continue;
      if ( !isset($Rec["o"]["0"])  || substr($Rec["o"]["0"],0,4) != "urn:" )  continue;

      $ExpID  = "link_" . $this->Counts["search"]++;

      $Text = $this->getSFTexts($Rec, "i", "", ": ");

      $Add   = array();
      $Add[] = $this->getSFTexts($Rec, "z", "ISBN ");
      $Add[] = $this->getSFTexts($Rec, "d");
      $Add[] = $this->getSFTexts($Rec, "n");
      $Add   = array_filter($Add);
      $Text  = trim($Text . " " . implode(", ", $Add));

      $Links[$ExpID]["link"]   = "https://nbn-resolving.org/" . $Rec["o"]["0"];
      $Links[$ExpID]["text"]   = $Text;
      $Links[$ExpID]["loc"]    = "link";
      $Links[$ExpID]["epn"]    = $ExpID;
    }

    return $Links;
  }

  private function getSearches()
  {
    if ( APPDEBUG["FKT"] ) $this->CS->debug("FKT",__METHOD__);

    if ( !isset($this->CS->configs["item"]["siblings"]) || $this->CS->configs["item"]["siblings"] == "n" ) return array();

    $Searches = array();

    $MExps = $this->getCompleteArray(array($this->Prefix . "776" => array("I2", "d", "i", "n", "w", "z")));

    foreach ($MExps as $Rec)
    {
      if ( !isset($Rec["I2"]["0"]) || $Rec["I2"]["0"] != "8" ) continue;
      if ( ( !isset($Rec["z"]["0"])  || !trim($Rec["z"]["0"]) )
        && ( !isset($Rec["w"]["0"])  || !in_array(substr($Rec["w"]["0"],0,8), array("(DE-601)","(DE-627)"))) ) continue;

      $ExpID  = "search_" . $this->Counts["search"]++;

      $Text = $this->getSFTexts($Rec, "i");

      $Add   = array();
      $Add[] = $this->getSFTexts($Rec, "n");
      $Add[] = $this->getSFTexts($Rec, "z", "ISBN ");
      $Add[] = $this->getSFTexts($Rec, "d");
      $Add   = array_filter($Add);
      $Text  = trim($Text . " " . implode(", ", $Add));

      $Searches[$ExpID]["search"] = ( isset($Rec["w"]["0"]) && in_array(substr($Rec["w"]["0"],0,8), array("(DE-601)","(DE-627)") ) )
                                    ? "id("   . trim(substr($Rec["w"]["0"],8)) . ")"
                                    : "isn("  . trim($Rec["z"]["0"])           . ")";
      $Searches[$ExpID]["text"]   = $Text;
      $Searches[$ExpID]["loc"]    = "search";
      $Searches[$ExpID]["epn"]    = $ExpID;
    }

    return $Searches;
  }

  private function getNorm($Norms)
  {
    if ( APPDEBUG["FKT"] ) $this->CS->debug("FKT",__METHOD__);

    if ( !isset($Norms["0"]) && !isset($Norms[" 0"]) )  return "";

    if ( isset($Norms["0"]) )
    {
      foreach ( $Norms["0"] as $One )
      {
        if ( isset($One) && substr($One,0,8) == "(DE-627)" ) return substr($One,8);
      }
    }
    if ( isset($Norms[" 0"]) )
    {
      foreach ( $Norms[" 0"] as $One )
      {
        if ( isset($One) && substr($One,0,8) == "(DE-627)" ) return substr($One,8);
      }
    }
    return "";
  }

  private function subjectNormGND($Rec, $Code)
  {
    $Art  = isset($Rec[$Code][0]) ? strtolower(trim($Rec[$Code][0])) : "";
    $Typ  = array("p" => "person", "k" => "organisation", "b" => "organisation",
                  "f" => "event",  "g" => "place");
    if ( $Art === "s" )  return array();
    $Norm = $this->getGNDNorm(isset($Typ[$Art]) ? $Typ[$Art] : "place", $Rec);
    return is_array($Norm) ? $Norm : array();
  }

  private function getGNDNorm($Type, $Norms)
  {
    // Nachschlagen in der zentralen GBV-Datenbank gibt es nur in Lukida Pro
    return ( isset($Norms["0"]) || isset($Norms[" 0"]) ) ? array() : "";
  }

  private function getOwnILNsOrCols()
  {
    if ( APPDEBUG["FKT"] ) $this->CS->debug("FKT",__METHOD__);

    foreach ($this->ColDetails as $One)
    {
      if ( substr($One, 0, 8) == "GBV_ILN_" && isset($this->CS->configs["ilnmans"]) && in_array(substr($One,8),  $this->CS->configs["ilnmans"]) ) return true;
    }

    if ( $this->OwnCollection )  return true;

    return false;
  }

  private function getParents()
  {
    if ( APPDEBUG["FKT"] ) $this->CS->debug("FKT",__METHOD__);

    $Parents = array();
    if ( isset($this->Convert[$this->Prefix . "773"]) )
    {
      foreach ( $this->Convert[$this->Prefix . "773"] as $Record )
      {
        foreach ( $Record as $Key => $Values )
        {
          if ( $Key == "w" )
          {
            foreach ( $Values as $Value )
            {
              if ( in_array(substr($Value,0,8), array("(DE-601)","(DE-627)")) )
              {
                $Parents[]  = trim(substr($Value,8));
              }
            }
          }
        }
      }
    }
    if ( isset($this->Convert[$this->Prefix . "800"]) )
    {
      foreach ( $this->Convert[$this->Prefix . "800"] as $Record )
      {
        foreach ( $Record as $Key => $Values )
        {
          if ( $Key == "w" )
          {
            foreach ( $Values as $Value )
            {
              if ( in_array(substr($Value,0,8), array("(DE-601)","(DE-627)")) )
              {
                $Parents[]  = trim(substr($Value,8));
              }
            }
          }
        }
      }
    }
    if ( isset($this->Convert[$this->Prefix . "810"]) )
    {
      foreach ( $this->Convert[$this->Prefix . "810"] as $Record )
      {
        foreach ( $Record as $Key => $Values )
        {
          if ( $Key == "w" )
          {
            foreach ( $Values as $Value )
            {
              if ( in_array(substr($Value,0,8), array("(DE-601)","(DE-627)")) )
              {
                $Parents[]  = trim(substr($Value,8));
              }
            }
          }
        }
      }
    }
    if ( isset($this->Convert[$this->Prefix . "830"]) )
    {
      foreach ( $this->Convert[$this->Prefix . "830"] as $Record )
      {
        foreach ( $Record as $Key => $Values )
        {
          if ( $Key == "w" )
          {
            foreach ( $Values as $Value )
            {
              if ( in_array(substr($Value,0,8), array("(DE-601)","(DE-627)")) )
              {
                $Parents[]  = trim(substr($Value,8));
              }
            }
          }
        }
      }
    }
    return array_unique($Parents);
  }

  private function getPersonRoles($One, $onlyauthor = true)
  {
    if ( APPDEBUG["FKT"] ) $this->CS->debug("FKT",__METHOD__);

    $Roles = [];
    if ( isset($One["4"]) && is_array($One["4"]) && count($One["4"]) )
    {
      $Roles = array_values($One["4"]);
    }
    elseif ( isset($One["e"]) && is_array($One["e"]) && count($One["e"]) )
    {
      foreach ( $One["e"] as $Role )
      {
        if ( strlen($Role) == 3 )
        {
          $Roles[] = $Role;
        }
        else
        {
          $Tmp = preg_replace("/[^a-zA-Z0-9öäü ]+/", "", strtolower($Role));
          if ( in_array($Tmp, array("bearb","begr","hrsg","komp","mitarb","red","ubers","adressat","komm",
                                    "stecher","verstorb","zeichner","präses","praeses","resp","widmungsempfänger",
                                    "widmungsempfaenger","zensor","beiträger","beitraeger","beiträger k","beitraeger k",
                                    "beiträger m","beitraeger m","interpr","verf")) )
          {
            $Tmp = str_replace(array("bearb","begr","hrsg","komp","mitarb","red","ubers","adressat","komm",
                                    "stecher","verstorb","zeichner","präses","praeses","resp","widmungsempfänger",
                                    "widmungsempfaenger","zensor","beiträger","beitraeger","beiträger k","beitraeger k",
                                    "beiträger m","beitraeger m","interpr","verf"),
                               array("edi","fdr","edt","cmp","cbr","pbd","trl","rcp","cmm","egr","dcs","drm","chm","chm",
                                     "rsp","dte","dte","cns","ctl","ctl","cta","cta","ctm","ctm","ipt","aut"), $Tmp);
            $Roles[] = $Tmp;
          }
        }
      }
    }
    else
    {
      if ( $onlyauthor ) $Roles[] = "aut";
    }
    return array_unique($Roles);
  }

  private function getOrgName($isAuthor, $Rec, $RecsOrg)
  {
    if ( !isset($Rec["6"][0]) )  return "";
    $Tmp = explode("-", $Rec["6"][0]);
    if ( !isset($Tmp[1]) ) return "";

    $Find = ($isAuthor ? "100" : "700" ) . "-" . $Tmp[1];
    foreach ( $RecsOrg as $RO )
    {
      if ( isset($RO["6"][0]) && substr($RO["6"][0],0,6) == $Find && isset($RO["a"][0]) )  return " " . $RO["a"][0];
    }
  }

  private function getOrgCorp($Field, $Rec, $RecsOrg)
  {
    if ( !isset($Rec["6"][0]) )  return "";
    $Tmp = explode("-", $Rec["6"][0]);
    if ( !isset($Tmp[1]) ) return "";
    $Find6 = $Field . "-" . $Tmp[1];

    if ( !isset($Rec["4"][0]) )  return "";
    $Find4 = $Rec["4"][0];

    foreach ( $RecsOrg as $RO )
    {
      if ( isset($RO["6"][0]) && substr($RO["6"][0],0,6) == $Find6 &&
           isset($RO["4"][0]) && $RO["4"][0] == $Find4 )
      {
        $Tmp2 = "";
        if ( isset($RO["a"][0]) && $RO["a"][0] != "" && isset($RO["b"][0]) && $RO["b"][0] != "" )       $Tmp2  = $RO["a"][0] . ". " . $RO["b"][0];
        if ( isset($RO["a"][0]) && $RO["a"][0] != "" && (!isset($RO["b"][0]) || $RO["b"][0] == "" ) )   $Tmp2  = $RO["a"][0];
        if ( ( !isset($RO["a"][0]) || $RO["a"][0] == "" ) && isset($RO["b"][0]) && $RO["b"][0] != "" )  $Tmp2  = $RO["b"][0];
        if ( isset($RO["c"][0]) && $RO["c"][0] != "" )                                                  $Tmp2 .= " " . $RO["c"][0];
        return $Tmp2;
      }
    }
    return "";
  }

  private function getOrgPubl($Field, $Rec, $RecsOrg)
  {
    if ( !isset($Rec["6"][0]) )  return "";
    $Tmp = explode("-", $Rec["6"][0]);
    if ( !isset($Tmp[1]) ) return "";
    $Find6 = $Field . "-" . $Tmp[1];

    foreach ( $RecsOrg as $RO )
    {
      if ( isset($RO["6"][0]) && substr($RO["6"][0],0,6) == $Find6 )
      {
        $Return = [];
        if ( isset($RO["a"][0]) && $RO["a"][0] != "" )  $Return["a"]  = $RO["a"][0];
        if ( isset($RO["b"][0]) && $RO["b"][0] != "" )  $Return["b"]  = $RO["b"][0];
        if ( isset($RO["c"][0]) && $RO["c"][0] != "" )  $Return["c"]  = $RO["c"][0];
        return $Return;
      }
    }
    return "";
  }

  private function parseMarcXml($XML)
  {
    if ( APPDEBUG["FKT"] ) $this->CS->debug("FKT",__METHOD__);

    try
    {
      $xml = simplexml_load_string($XML);
    }
    catch (Exception $e)
    {
      return array();
    }

    $Convert = array();
    if ( !isset($xml->record[0]) ) return $Convert;

    $Convert["Lead"] = (isset($xml->record[0]->leader)) ? (string) $xml->record[0]->leader : "";

    foreach($xml->record[0]->controlfield as $CF)
    {
      $Convert[(string) $CF["tag"]] = (string) $CF[0];
    }

    $CTags    = array();
    $ErsteSub = array();
    foreach($xml->record[0]->datafield as $DF)
    {
      $Tag = (string) $DF["tag"];

      if ( $Tag >= "900" )
      {
        $ErsteSub[$Tag][isset($CTags[$Tag]) ? $CTags[$Tag] + 1 : 0] = (string) $DF->subfield;
      }

      if ( !isset($CTags[$Tag]) )
      {
        $CTags[$Tag] = 0;
      }
      else
      {
        $CTags[$Tag]++;
      }
      $DFI = $CTags[$Tag];
      $Convert[$Tag][$DFI] = array();

      if ( trim((string) $DF["ind1"]) )  $Convert[$Tag][$DFI]["I1"] = (string) $DF["ind1"];
      if ( trim((string) $DF["ind2"]) )  $Convert[$Tag][$DFI]["I2"] = (string) $DF["ind2"];

      $CSubs = array();
      foreach($DF->subfield as $SF)
      {
        $Sub = (string) $SF["code"];
        if ( !isset($CSubs[$Sub]) )
        {
          $CSubs[$Sub] = 0;
        }
        else
        {
          $CSubs[$Sub]++;
        }
        $SFI = $CSubs[$Sub];
        $Convert[$Tag][$DFI][$Sub][$SFI] = (string) $SF[0];
      }
      ksort($Convert[$Tag][$DFI]);
    }
    ksort($Convert);

    return array("marc" => $Convert, "erstesub" => $ErsteSub);
  }

  private function getReducedConvert($XML, $ID = "", $UpDate = "")
  {
    if ( APPDEBUG["FKT"] ) $this->CS->debug("FKT",__METHOD__);

    $Neutral = $ID ? $this->CS->readMarcNeutral($ID, $UpDate) : null;
    if ( !is_array($Neutral) )
    {
      $Neutral = $this->parseMarcXml($XML);

      $Neutral["solr"] = array(
        "collection"         => isset($this->SolrOne["collection"])         ? $this->SolrOne["collection"]         : array(),
        "collection_details" => isset($this->SolrOne["collection_details"]) ? $this->SolrOne["collection_details"] : array(),
        "format_phy_str_mv"  => isset($this->SolrOne["format_phy_str_mv"])  ? $this->SolrOne["format_phy_str_mv"]  : array(),
        "publishDateSort"    => isset($this->SolrOne["publishDateSort"])    ? $this->SolrOne["publishDateSort"]    : "",
        "remote_bool"        => isset($this->SolrOne["remote_bool"])        ? $this->SolrOne["remote_bool"]        : ""
      );
      if ( $ID && isset($Neutral["marc"]) && count($Neutral["marc"]) )
        $this->CS->writeMarcNeutral($ID, $Neutral, $UpDate);
    }

    $Convert  = $this->setzePraefix(isset($Neutral["marc"])     ? $Neutral["marc"]     : array());
    $ErsteSub = $this->setzePraefix(isset($Neutral["erstesub"]) ? $Neutral["erstesub"] : array());

    $this->reduceToClient($Convert, $ErsteSub);

    return $Convert;
  }

  private function setzePraefix($Felder)
  {
    if ( !is_array($Felder) || !count($Felder) )  return array();

    $Neu = array();
    foreach ( $Felder as $Tag => $Wert )
    {
      if ( $Tag === "Lead" )  $Neu[$this->Prefix == "PM" ? "PLead" : "Lead"] = $Wert;
      else                    $Neu[$this->Prefix . $Tag] = $Wert;
    }
    ksort($Neu);
    return $Neu;
  }

  private function reduceToClient(&$Convert, $ErsteSub)
  {
    if ( APPDEBUG["FKT"] ) $this->CS->debug("FKT",__METHOD__);

    $Grenze = $this->Prefix . "900";
    $I912   = $this->Prefix . "912";
    $I980   = $this->Prefix . "980";

    foreach ( $Convert as $Tag => $Recs )
    {
      if ( !is_array($Recs) || $Tag < $Grenze )  continue;

      foreach ( $Recs as $DFI => $Rec )
      {
        $Weg = false;

        if ( $Tag == $I912 )
        {
          $Tmp = isset($ErsteSub[$Tag][$DFI]) ? $ErsteSub[$Tag][$DFI] : "";
          if ( ( isset($this->CS->configs["ilnmans"]) && is_array($this->CS->configs["ilnmans"])
              && substr($Tmp,0,8) == "GBV_ILN_" && !in_array(substr($Tmp,8), $this->CS->configs["ilnmans"]) )
            || ( isset($this->CS->configs["isils"]) && is_array($this->CS->configs["isils"]) && substr($Tmp,0,5) == "ISIL_"
              && !in_array(substr($Tmp,5), $this->CS->configs["isils"]) )
            || ( substr($Tmp,0,8) != "GBV_ILN_" && substr($Tmp,0,5) != "ISIL_" && in_array($Tmp, array("SYSFLAG_1", "SYSFLAG_A")) ) )  $Weg = true;
        }
        else
        {
          $SF2 = ( isset($Rec["2"]) && is_array($Rec["2"]) && count($Rec["2"]) ) ? (string) end($Rec["2"]) : "";
          $SFY = ( isset($Rec["y"]) && is_array($Rec["y"]) && count($Rec["y"]) ) ? substr((string) end($Rec["y"]), 1) : "";

          if ( trim($SF2) && isset($this->CS->configs["ilns"])
            && is_array($this->CS->configs["ilns"])
            && !in_array($SF2, $this->CS->configs["ilns"]) )  $Weg = true;

          if ( !$Weg && $Tag == $I980
            && isset($this->CS->configs["ilnandman"][$SF2])
            && is_array($this->CS->configs["ilnandman"][$SF2])
            && count($this->CS->configs["ilnandman"][$SF2])
            && !in_array($SFY, $this->CS->configs["ilnandman"][$SF2]) )  $Weg = true;
        }

        if ( $Weg )  unset($Convert[$Tag][$DFI]);
      }

      if ( !count($Convert[$Tag]) )  unset($Convert[$Tag]);
      else                           $Convert[$Tag] = array_values($Convert[$Tag]);
    }
  }

  private function getSeries()
  {
    if ( APPDEBUG["FKT"] ) $this->CS->debug("FKT",__METHOD__);

    $Lead = isset($this->Convert["Lead"])                ? $this->Convert["Lead"]                : "";
    $M008 = isset($this->Convert[$this->Prefix . "008"]) ? $this->Convert[$this->Prefix . "008"] : "";

    if ( isset($Lead) && substr($Lead, 7,1) == "s"  && substr($M008,21,1) == "m")                              return true;
    if ( isset($Lead) && substr($Lead, 7,1) == "m"  && substr($M008, 6,1) == "m" && substr($Lead,19,1) == "a") return true;
    if ( isset($Lead) && substr($Lead, 6,2) == "ai"
      && in_array(substr($M008, 6,1), array("c","m")) && substr($Lead,19,1) == "a")                            return true;
    return false;
  }

  private function getSFTexts($Array, $Subfield, $Prefix = "", $PostFix = "")
  {
    if ( APPDEBUG["FKT"] ) $this->CS->debug("FKT",__METHOD__);

    $Texts = "";
    if ( isset($Array[$Subfield]) && is_array($Array[$Subfield]) && count($Array[$Subfield]) )
    {
      foreach ($Array[$Subfield] as $Text)
      {
        $Texts .= ($Texts) ? ", " . $Text : $Prefix . $Text . $PostFix;
      }
    }
    return $Texts;
  }

  private function getSubfield($Field, $Subfield = "")
  {
    if ( APPDEBUG["FKT"] ) $this->CS->debug("FKT",__METHOD__);

    $Data = array();
    if ( array_key_exists($Field, $this->Convert) )
    {
      foreach ( $this->Convert[$Field] as $Record )
      {
        foreach ( $Record as $Key => $Value )
          {
            if ( $Key == $Subfield )
            {
              $Data[] = $Value;
            }
          }
      }
    }
    return ($Data);
  }

  private function getSubfieldFirst($Field, $Subfield = "")
  {
    if ( APPDEBUG["FKT"] ) $this->CS->debug("FKT",__METHOD__);

    if ( array_key_exists($Field, $this->Convert) )
    {
      foreach ( $this->Convert[$Field] as $Record )
      {
        foreach ( $Record as $Key => $Subrecord )
        {
          if ( $Key == $Subfield )
          {
            foreach ( $Subrecord as $Value )
            {
              return $Value;
            }
          }
        }
      }
    }
    return "";
  }

  private function getVolume($Field, $Subfield, $Indikator1)
  {
    if ( APPDEBUG["FKT"] ) $this->CS->debug("FKT",__METHOD__);

    $Output = array();
    if ( array_key_exists($Field, $this->Convert) )
    {
      foreach ($this->Convert[$Field] as $Record)
      {
        if ( !isset($Record["I1"]) || $Record["I1"] != $Indikator1 )  continue;
        foreach ( $Record as $Key => $Values )
        {
          if ( (string)$Key == $Subfield )
          {
            foreach ($Values as $Val)
            {
              if (true)  $Output[] = $Val;
            }
          }
        }
      }
    }
    return $Output;
  }

  private function isOpenAccess($Text)
  {
    if ( stripos( $Text, "oa"                            ) !== false ) return true;
    if ( stripos( $Text, "oalizenz"                      ) !== false ) return true;
    if ( stripos( $Text, "openaccess"                    ) !== false ) return true;
    if ( stripos( $Text, "kostenfrei"                    ) !== false ) return true;
    if ( stripos( $Text, "open access-lizenzbedingungen" ) !== false ) return true;
    if ( stripos( $Text, "openaccess lizenz"             ) !== false ) return true;
    return false;
  }

  private function get856OA()
  {
    $MExps = $this->getFlatArray(array($this->Prefix . "856" => array("z")));
    foreach ($MExps as $Rec)
    {
      foreach ($Rec as $Code => $Value)
      {
        if ( $Code == "z" && $this->isOpenAccess($Value) )   return true;
      }
    }
    return false;
  }

  private function prettyRecs($Filter)
  {
    if ( APPDEBUG["FKT"] ) $this->CS->debug("FKT",__METHOD__);

    $Recs = array();
    $Item = 0;
    foreach ( $Filter as $Field => $Subfields )
    {
      if ( array_key_exists($this->Prefix . $Field, $this->Convert) )
      {
        foreach ($this->Convert[$this->Prefix . $Field] as $Record)
        {
          $Rec = array("text" => "", "action" => array(), "more"   => array());
          $Item++;
          foreach ( $Record as $Key => $Subrecord )
          {
            foreach ( $Subfields as $Sub )
            {
              if ( (string)$Key == (string)$Sub )
              {
                foreach ( $Subrecord as $Value )
                {
                  if ( $Rec["text"] )
                  {
                    $Rec["text"] .= " : " . $Value;
                  }
                  else
                  {
                    $Rec["text"] = $Value;
                  }
                }
              }
            }
          }
          if ($Rec["text"]) $Recs["item_" . $Item] = $Rec;
        }
      }
    }
    $Doub = array();
    foreach ( $Recs as $Ind => $Rec )
    {
      if ( !in_array($Rec["text"], $Doub) )
      {
        $Doub[] = $Rec["text"];
      }
      else
      {
        unset($Recs[$Ind]);
      }
    }
    return $Recs;
  }

  private function doConvert(&$One, $Prefix)
  {
    if ( APPDEBUG["FKT"] ) $this->CS->debug("FKT",__METHOD__);

    if ( !isset($One["fullrecord_marcxml"]) || !isset($One["id"]) ) return;
    $this->Counts = array("search" => 0, "physical" => 0, "online" => 0);

    $this->MasterLink = "";

    $this->SolrOne       = $One;
    $this->ID            = ($One["id"] != "" && $One["id"] != "0") ? (string) $One["id"] : "RAND".rand(100000000,999999999);
    $this->Collection    = ( isset($One["collection"]) )                                   ? $One["collection"]           : array();
    $this->ColDetails    = ( isset($One["collection_details"]) )                           ? $One["collection_details"]   : array();
    $this->Format        = ( isset($One["format_phy_str_mv"][0]) )                         ? $One["format_phy_str_mv"][0] : "";
    $this->Year          = ( isset($One["publishDateSort"]) )                              ? $One["publishDateSort"]      : "";
    $this->Online        = ( isset($One["remote_bool"]) && $One["remote_bool"] == "true" ) ? true                         : false;
    $this->Prefix        = $Prefix;
    $this->Convert       = $this->getReducedConvert($One["fullrecord_marcxml"], $this->ID,
                                                     isset($One["up_date"]) ? (string) $One["up_date"] : "");
    $this->Parents       = $this->getParents();

    $this->OwnCollection = (count(array_intersect($this->Collection, $this->CS->configs["mycolls"]))) ? true : false;
    $this->Owner         = $this->getOwnILNsOrCols();
    $this->ArticleLink   = $this->getArticleLinks();
    $this->Series        = $this->getSeries();
    $this->ArtCode       = $this->getArtCode();
    $this->OwnOrg        = $this->getOwnOrg();
    $this->Fields        = $this->getBiblio();
    $this->Items         = $this->addCallSigns($this->getItems());
    $this->Searches      = $this->getSearches();
    $this->Links         = $this->getLinks();

    if ( APPDEBUG["IDS"] && ( (is_array(APPDEBUG["IDS"]) && in_array($this->ID, APPDEBUG["IDS"])) || (!is_array(APPDEBUG["IDS"]) && APPDEBUG["IDS"] == $this->ID)) )
    {
      $this->CS->debug("IDS", $this->ID);
      $this->CS->debug("MCD", $this->Convert);
    }

    if ( APPDEBUG["MCD"] ) $this->CS->debug("MCD", $this->Convert);
    $Media  = array
    (
      "id"            => $this->ID,
      "format"        => $this->Format,
      "online"        => $this->Online,
      "series"        => $this->Series,
      "formatcode"    => str_replace(" ", "", strtolower($this->Format)),
      "artcode"       => $this->ArtCode,
      "ownorg"        => $this->OwnOrg,
      "year"          => $this->Year,
      "rootid"        => count($this->Parents) ? $this->Parents[0] : "",
      "area"          => "findex",
      "owner"         => $this->Owner,
      "collections"   => array_map('strtolower', $this->Collection),
      "cardlogo"      => $this->kachelLogo(),
      "collection_details" => $this->ColDetails,
      "owncollection" => $this->OwnCollection,
      "hapticarticlelinks" => !$this->Online && $this->ArticleLink ? true : false,
      "onlinearticlelinks" =>  $this->Online && $this->ArticleLink ? true : false,
      "items"         => $this->Items,
      "itemcount"     => $this->Counts,
      "searches"      => $this->Searches,
      "links"         => $this->Links,
      "ilngroups"     => $this->ILNGroups,
      "masterlink"    => $this->MasterLink,
      "bibliographic" => $this->Fields,
      "marc"          => $this->Convert
    );

    if ( isset($this->CS->configs["item"]["use264casyear"]) && $this->CS->configs["item"]["use264casyear"] == "y"
       && isset($this->Convert["M264"][0]["c"][0]) && trim($this->Convert["M264"][0]["c"][0]) )
    {
      $Media["year"] = $this->Convert["M264"][0]["c"][0];
    }

    if ( APPDEBUG["IDS"] && ( (is_array(APPDEBUG["IDS"]) && in_array($this->ID, APPDEBUG["IDS"])) || (!is_array(APPDEBUG["IDS"]) && APPDEBUG["IDS"] == $this->ID)) )
    {
      $this->CS->debug("IDS", $this->ID);
      $this->CS->debug("MED", $Media);
    }

    if ( APPDEBUG["MED"] ) $this->CS->debug("MED", $Media);

    return $Media;
  }

  public function getConverts($Add, $Prefix = "M")
  {
    if ( APPDEBUG["FKT"] ) $this->CS->debug("FKT",__METHOD__);

    $this->add = $Add;

    if ( isset($this->CS->works["solr"]) && is_array($this->CS->works["solr"]) && count($this->CS->works["solr"]) )
    {
      foreach ( $this->CS->works["solr"] as $SolrOne )
      {
        $ID = isset($SolrOne["id"]) ? $SolrOne["id"] : "";
        if ( !$ID ) continue;

        $UpDate = isset($SolrOne["up_date"]) ? (string) $SolrOne["up_date"] : "";
        $this->CS->works["updates"][$ID] = $UpDate;

        {
          $One = $this->doConvert($SolrOne, $Prefix);

          if ( !$this->add )
          {
            $this->CS->works["marc"][$ID] = $One;
            $this->CS->results["data"][]  = $One;

            if ( isset($One["format"]) )
            {
              if ( isset($One["rootid"]) && $One["rootid"] != "" )
              {
                $this->CS->hierarchy["childs"][$ID]               = $One["rootid"];

                $this->CS->hierarchy["parents"][$One["rootid"]][] = $ID;
              }
            }

            if ( isset($this->CS->display["marc"]) && $this->CS->display["marc"] )      $this->CS->results["marc"][$ID] = $this->Convert;
          }
          else
          {
            $this->CS->works["add"][$ID] = $One;

            $CIDs = isset($this->CS->hierarchy["parents"][$ID]) ? $this->CS->hierarchy["parents"][$ID] : array();

            $Kids = array_flip(array_column($this->CS->results["data"] ?? array(), "id"));

            foreach ( $CIDs as $CID )
            {
              if ( isset($this->CS->display["marc"]) && $this->CS->display["marc"]
                && isset($this->CS->results["marc"][$CID]) )
              {
                $this->CS->results["marc"][$CID] += $this->Convert;

                $APSInherit = ( isset($this->CS->configs["item"]["articleparentshift"])
                             && trim($this->CS->configs["item"]["articleparentshift"]) == "y" ) ? true : false;
                if ( $this->Owner && !$APSInherit && isset($this->CS->results["data"]) && is_array($this->CS->results["data"]) && count($this->CS->results["data"]) )
                {
                  if ( isset($Kids[$CID]) )
                  {
                    $this->CS->works["marc"][$CID]["owner"] = 1;
                  }
                }
              }
            }
          }
        }
      }
    }
    return;
  }
}

?>
