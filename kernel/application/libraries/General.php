<?php

class General
{
  protected $catalogues         = array();
  protected $collgsize          = "";
  protected $collection         = array();
  protected $collection_details = array();
  protected $contents           = array();
  protected $cover              = "";
  protected $exemplar           = array();
  protected $format             = "";
  protected $isbn               = "";
  protected $leader             = "";
  protected $marc               = "";
  protected $medium             = array();
  protected $online             = "";
  protected $parents            = array();
  protected $PPN                = "";
  protected $pretty             = array();
  protected $words              = "";
  protected $proofofpossession  = array();

  protected function SessionExits()
  {
    if ( ! isset($_SESSION["config_general"]) )
    {
      echo "ERROR:NO_SESSION";
      return false;
    }
    else
    {
      return true;
    }
  }

  protected function countLBS()
  {
    return ( isset($_SESSION["info"]["lbscount"]) ) ? $_SESSION["info"]["lbscount"] : 0;
  }

  protected function formatEuro($Betrag)
  {
    return number_format($Betrag,2,",",".") . " €";
  }

  protected function ParamExits($Name, $Parameter, $Key1="", $Key2="",$Key3="")
  {
    // Existiert Variable
    if ( ! isset($Parameter) )
    {
      echo "ERROR:NO_PARAMETER:" . $Name;
      return false;
    }

    // Existiert Key im Array
    if ($Key1 != "" )
    {
      if ( ! isset($Parameter[$Key1]) )
      {
        echo "ERROR:NO_PARAMETER:" . $Name;
        return false;
      }
    }
    if ($Key2 != "" )
    {
      if ( ! isset($Parameter[$Key1][$Key2]) )
      {
        echo "ERROR:NO_PARAMETER:" . $Name;
        return false;
      }
    }
    if ($Key3 != "" )
    {
      if ( ! isset($Parameter[$Key1][$Key2][$Key3]) )
      {
        echo "ERROR:NO_PARAMETER:" . $Name;
        return false;
      }
      if ( $Parameter[$Key1][$Key2][$Key3] == "" && is_bool($Parameter[$Key1][$Key2][$Key3]) )
      {
        echo "ERROR:NO_PARAMETER:" . $Name;
        return false;
      }
      return true;
    }
    if ($Key2 != "" )
    {
      if ( $Parameter[$Key1][$Key2] == "" && is_bool($Parameter[$Key1][$Key2]) )
      {
        echo "ERROR:NO_PARAMETER:" . $Name;
        return false;
      }
      return true;
    }
    if ($Key1 != "" )
    {
      if ( $Parameter[$Key1] == "" && is_bool($Parameter[$Key1]) )
      {
        echo "ERROR:NO_PARAMETER:" . $Name;
        return false;
      }
      return true;
    }
    if ( $Parameter == "" && is_bool($Parameter) )
    {
      echo "ERROR:NO_PARAMETER:" . $Name;
      return false;
    }
    return true;
  }

  protected function FileExits($File)
  {
    if ( ! file_exists($File) )
    {
      echo "ERROR:NO_FILE:" . $File;
      return false;
    }
    else
    {
      return true;
    }
  }

  protected function header()
  {
    return "<!DOCTYPE html><html lang='de'><head>
    <meta charset='utf-8'></head><body>";
  }

  protected function footer()
  {
    return "</body></html>";
  }

  protected function printarray($level,$Array)
  {
    $Output = "";
    foreach ( $Array as $Field => $Value )
    {
      $Output .= $Field . ": ";
      if ( ! is_array($Value) )
      {
        $Output .= $Value . " | ";
      }
      else
      {
        $Output .= $this->printarray($level++,$Value);
      }
    }
    return $Output;
  }

  protected function printtable($level, $Array)
  {
    $Output = "";
    foreach ( $Array as $Field => $Value )
    {
      $Output .= "<tr><td class='tabcell'>";
      $Output .= ($level==2) ? "<b>" . $Field . "</b>" : $Field;
      $Output .= "</td><td class='tabcell'>";
      if ( !is_array($Value) )
      {
        $Output .= htmlentities($Value,ENT_QUOTES);
      }
      else
      {
        $Output .= "<table>";
        $level++;
        $Output .= $this->printtable($level, $Value);
        $level--;
        $Output .= "</table>";
      }
      $Output .= "</td></tr>";
    }
    return $Output;
  }

  protected function format_uri( $string, $separator = ' ' )
  {
    return $title = str_replace( array( '\'','"',',',';','<','>',' ','!','§','$','%','&','/','(',')','{','}','[',']'), ' ', $string);
  }

  protected function Trim_Text($input, $length, $ellipses = true, $strip_html = true)
  {
    //strip tags, if desired
    if ($strip_html) {
      $input = strip_tags($input);
    }

    //no need to trim, already shorter than trim length
    if (strlen($input) <= $length) {
      return $input;
    }

    //find last space within length
    $last_space = strrpos(substr($input, 0, $length), ' ');
    $trimmed_text = substr($input, 0, $last_space);

    //add ellipses (...)
    if ($ellipses) {
      $trimmed_text .= '...';
    }

    return $trimmed_text;
  }

  protected function Mark_Text($Text)
  {
    // Do not mark on internal searches
    if ( strpos($_SESSION["data"]["search"], ":") !== false )
    {
      $Tmp = explode(":", $_SESSION["data"]["search"]);
      if ( in_array(strtolower(trim($Tmp[0])), array("author","autor","id","isn","subject","schlagwort","title","titel","series","reihe","publisher","verlag","year","jahr","toc","inhalt","class","sachgebiet")) )
      {
        return ($Text);
      }
    }
 
    // Replace slash one space
    $Search = str_replace('/', ' ', $_SESSION['data']['search']);

    // Replace multiple spaces, tabs, linebreaks by one space
    $Search = preg_replace('!\s+!', ' ', $Search);
    $Words  = array_unique(explode(" ", $Search));

    foreach ( $Words as $Word )
    {
      if( preg_match('/[' . utf8_encode("äÄöÖüÜß") . ']/', $Word) == 1 )
      { 
        if ( strpos($Word,utf8_encode("ä")) !== false )  $Words[] = str_replace(utf8_encode("ä"),"ae",$Word);
        if ( strpos($Word,utf8_encode("Ä")) !== false )  $Words[] = str_replace(utf8_encode("Ä"),"Ae",$Word);
        if ( strpos($Word,utf8_encode("ö")) !== false )  $Words[] = str_replace(utf8_encode("ö"),"oe",$Word);
        if ( strpos($Word,utf8_encode("Ö")) !== false )  $Words[] = str_replace(utf8_encode("Ö"),"Oe",$Word);
        if ( strpos($Word,utf8_encode("ü")) !== false )  $Words[] = str_replace(utf8_encode("ü"),"ue",$Word);
        if ( strpos($Word,utf8_encode("Ü")) !== false )  $Words[] = str_replace(utf8_encode("Ü"),"Ue",$Word);
        if ( strpos($Word,utf8_encode("ß")) !== false )  $Words[] = str_replace(utf8_encode("ß"),"ss",$Word);
        $Words[] = str_replace(array(utf8_encode("ä"),utf8_encode("Ä"),utf8_encode("ö"),utf8_encode("Ö"),utf8_encode("ü"),utf8_encode("Ü"),utf8_encode("ß")),array("ae","Ae","oe","Oe","ue","Ue","ss"),$Word);
      } 
    }
    $Words  = array_unique($Words);

    // Phase one: Prepare replacements
    foreach ( $Words as $Word )
    {
      // Remove leading field group ids
      if ( strpos($Word, ":") !== false )
      {
        $Tmp = explode(":",$Word);

        // Skip internal searches
        if ( in_array($Tmp[0],array("author","autor","id","isn","subject","schlagwort","title","titel","series","reihe","publisher","verlag","year","jahr","toc","inhalt")) )  continue;

        array_shift($Tmp);
        $Word = implode(":", $Tmp);
      }

      // Skip to small words
      if ( strlen($Word) < 3 )  continue;

      // Skip words inside marked text
      if ( stripos("span,class,markfoundtext", $Word) !== false ) continue;

      /*** quote the text for regex ***/
      $Word = preg_quote($Word);
      /*** highlight the words ***/
      $Text = preg_replace("/($Word)/i", '<span class="markfoundtext">\1</span>', $Text);

    }

    // Phase two: Do replacements
    $Text   = str_ireplace("<e§m>","<span class='search'>", $Text);
    $Text   = str_ireplace("</e§m>","</span>", $Text);
    return $Text;
  }

  protected function AddData($Location, $Data)
  {
    if (isset($_SESSION["config_discover"]["fullview"]["fullviewlogging"]) && $_SESSION["config_discover"]["fullview"]["fullviewlogging"] == "1" && $Data == "" )
    {
      file_put_contents("Empty_Fields.txt", $this->PPN . ", " . $Location . "\n",FILE_APPEND);
    }
    return $Data;
  }

  protected function LoadTabElements($Tab)
  {
    $Output = "";
    // Show elements based on ini-file
    $Elements = explode(",",$_SESSION['config_discover']["fullview"][$Tab]);
    foreach ( $Elements as $Element )
    {
      // Check if lokal file is available
      if ( file_exists(LIBRARYPATH . DIRECTORY_SEPARATOR . 'code' . DIRECTORY_SEPARATOR . $Element .'.php') )
      {
        include(LIBRARYPATH . DIRECTORY_SEPARATOR . 'code' . DIRECTORY_SEPARATOR . $Element .'.php');
      }
      else
      {
        // Check, if element is available
        if ( ! file_exists(KERNELFORMATS . DIRECTORY_SEPARATOR . 'elements' . DIRECTORY_SEPARATOR . $Element .'.php') )
        {
          // Whoops, we don't have a page for that!
          show_404();
        }
        include(KERNELFORMATS . DIRECTORY_SEPARATOR . 'elements' . DIRECTORY_SEPARATOR . $Element .'.php');
      }
    }
    return ($Output);
  }

  protected function LoadTab($Tab)
  {
    $Output = "";

    // Check if lokal file is available
    if ( file_exists(LIBRARYPATH . DIRECTORY_SEPARATOR . 'code' . DIRECTORY_SEPARATOR . $Tab .'.php') )
    {
      include(LIBRARYPATH . DIRECTORY_SEPARATOR . 'code' . DIRECTORY_SEPARATOR . $Tab .'.php');
    }
    else
    {
      // Check, if element is available
      if ( ! file_exists(KERNELFORMATS . DIRECTORY_SEPARATOR . 'tabs' . DIRECTORY_SEPARATOR . $Tab .'.php') )
      {
        // Whoops, we don't have a page for that!
        show_404();
      }
      include(KERNELFORMATS . DIRECTORY_SEPARATOR . 'tabs' . DIRECTORY_SEPARATOR . $Tab .'.php');
    }
    return ($Output);
  }

  protected function LoadElement($Element)
  {
    $Output = "";
    // Check if customer file is available
    if ( file_exists(LIBRARYPATH . 'code' . DIRECTORY_SEPARATOR . $Element .'.php') )
    {
      include(LIBRARYPATH . 'code' . DIRECTORY_SEPARATOR . $Element .'.php');
    }
    else
    {
      // Check, if element is available
      if ( ! file_exists(KERNELFORMATS . DIRECTORY_SEPARATOR . 'elements' . DIRECTORY_SEPARATOR . $Element .'.php') )
      {
        // Whoops, we don't have a page for that!
        show_404();
      }
      include(KERNELFORMATS . DIRECTORY_SEPARATOR . 'elements' . DIRECTORY_SEPARATOR . $Element .'.php');
    }
    return ($Output);
  }

  public function SetContents($type)
  {
    // Format contents for possible output
    // Merge multiple records and subfields into prepared strings / arrays 
    // for faster access
    $pretty = array();

    if ( $type == "preview" )
    {
      $pretty["title"]            = $this->PrettyFields(array("245" => array("a" => " : ",
                                                                             "b" => " : ")));

      $pretty["part"]             = $this->PrettyFields(array("245" => array("n" => " | ",
                                                                             "p" => " : ")));
  
      // $pretty["titlesecond"]      = $this->PrettyFields(array("245" => array("c" => " : ")));
  
      $pretty["author"]           = $this->GetAuthors();
  
      $pretty["pv_publisher"]     = $this->GetPublisher();
  
      $pretty["pv_pubarticle"]    = $this->PrettyFields(array("773" => array("i" => " ",
                                                                             "t" => " ",
                                                                             "d" => " ",
                                                                             "g" => " ",
                                                                             "q" => ". Band: ")));

      $pretty["serial"]           = $this->GetArray(array("490" => array("a","v")));
    
      $pretty["physicaldescription"] = $this->PrettyFields(array("300" => array("a" => ". ",
                                                                                "b" => ", ",
                                                                                "c" => ", ",
                                                                                "e" => ", ")));
      // Add original characters 
      $pretty = $this->AddOriginalCharacters($pretty,"preview");
    }

    if ( $type == "fullview" )
    {
      $pretty["publisher"]           = $this->GetCompleteArray(array("264" => array("a","b","c")));
      if ( empty($pretty["publisher"]) )
      {
        $pretty["publisher"]           = $this->GetArray(array("260" => array("a","b","c")));
      }
                                     
      $pretty["publisherarticle"]    = $this->GetArray(array("773" => array("a","i","t","b","d","g","h","q","w")));

      $pretty["uniformtitle"]        = $this->PrettyFields(array("240" => array("a" => " | ")));
      
      $pretty["associates"]          = $this->GetAssociates();

      $pretty["summary"]             = $this->PrettyFields(array("520" => array("a" => " | ")));
      
      $pretty["citation"]            = $this->PrettyFields(array("935" => array("e" => " | ")));

      $pretty["computerfile"]        = $this->PrettyFields(array("256" => array("a" => " | ")));

      $pretty["systemdetails"]       = $this->PrettyFields(array("538" => array("a" => " | ")));
  
      $pretty["edition"]             = $this->PrettyFields(array("250" => array("a" => " | ")));
      
      $pretty["reproduction"]        = $this->GetArray(array("338" => array("a","b","c","d","e","f","n")));
    
      $pretty["corporation"]         = $this->GetSimpleArray(array("110" => array("a"),
                                                                   "111" => array("a"),
                                                                   "710" => array("a"),
                                                                   "711" => array("a")));

      // $pretty["doi"]                 = $this->GetDOIs();
  
      $pretty["notes"]               = $this->GetSimpleArray(array("500" => array("a")));

      $pretty["includes"]            = $this->PrettyFields(array("249" => array("a" => " | "),
                                                                 "501" => array("a" => " | ")));

      $pretty["publishedjournal"]    = $this->PrettyFields(array("515" => array("a" => " | ")));

      $pretty["footnote"]            = $this->PrettyFields(array("338" => array("a" => " ",
                                                                                "n" => ": ")));

      $pretty["othereditions"]       = $this->GetArray(array("780" => array("a","i","t","b","d","g","h","q","w")));

      $pretty["remarks"]             = $this->GetCompleteArray(array("772" => array("i","t","w"),
                                                                     "770" => array("i","t","w"),
                                                                     "785" => array("i","t","w")));

      $pretty["seealso"]             = $this->GetCompleteArray(array("787" => array("i","t","w"),
                                                                     "776" => array("i","t","w")));

      $pretty["languagenotes"]       = $this->PrettyFields(array("546" => array("a" => " | ")));
      
      $pretty["dissertation"]        = $this->PrettyFields(array("502" => array("a" => " | ")));

      $pretty["recording"]           = $this->GetSimpleArray(array("518" => array("a")));
      
      $pretty["language"]            = $this->GetSimpleArray(array("041" => array("a")));

      $pretty["languageorigin"]      = $this->GetSimpleArray(array("041" => array("h")));

      $pretty["classification"]      = $this->GetCompleteArray(array("084" => array("a","2")));
  
      $pretty["subject"]             = $this->GetSimpleArray(array("689" => array("a")));

      $pretty["genre"]               = $this->GetSimpleArray(array("655" => array("a")));
                                                                   
      $pretty["in830"]               = $this->GetUplink("830");
      
      $pretty["in800"]               = $this->GetUplink("800");

      $pretty["additionalinfo"]      = $this->GetArray(array("856" => array("u","3","y")));

      $pretty["provenance"]          = $this->GetProvenance();

      $pretty["digitalresource"]     = $this->GetArray(array("981" => array("2","r","y")));
    
      $pretty["contentnotes"]        = $this->GetArray(array("989" => array("2","a")));

      $pretty["class"]               = $this->GetArray(array("983" => array("a","b")));

      $pretty["classiln"]            = $this->GetArray(array("983" => array("2","a","b")));

      $pretty["subjectheadings"]     = $this->GetArray(array("982" => array("2","a")));

      $pretty["isbn"]                = $this->PrettyFields(array("020" => array("9" => " | ", "a" => " | "),
                                                                 "773" => array("z" => " | ")));

      $pretty["issn"]                = $this->PrettyFields(array("022" => array("a" => " | "),
                                                                 "773" => array("x" => " | ")));

      // $pretty["ismn"]                = $this->PrettyFields(array("024" => array("a" => " | ")));

      $pretty["siblings"]            = $this->GetArray(array("787" => array("i","n","t","w")));

      $pretty["originalyear"]        = $this->PrettyFields(array("534" => array("c" => " | ")));

      // Add original characters 
      $pretty = $this->AddOriginalCharacters($pretty,"fullview");
    }

    if ( $type == "export" )
    {
      $zdbid = $this->GetSimpleArray(array("773" => array("w")));
      foreach ($zdbid as $One)
      {
        if ( substr($One,0,8) == "(DE-600)" )
        { 
          $pretty["zdbid"] = substr($One,8);
          break;
        }
      }
      $pretty["details"] = $this->GetArray(array("952" => array("a","e","d","h","j")));
    }

    return $pretty;
  }

  protected function GetUplink($BaseField)
  {
    $Data = $this->GetArray(array($BaseField => array("a","b","p","v","w")));
    if ( !isset($Data[0]["a"]) )
    {
      $Tmp = $this->GetSimpleArray(array("490" => array("a")));
      if ( count($Tmp) > 0 )  $Data[0]["a"] = $Tmp[0];
    }
    return ( $Data );
  }

  protected function PrettyFields($Filter)
  {
    $Output = "";
    $Tmp    = array();
    foreach ( $Filter as $Field => $Subfields )
    {
      if ( array_key_exists($Field, $this->contents) )
      {
        foreach ($this->contents[$Field] as $Record)
        {
          $Indys  = array();
          foreach ( $Record as $Subrecord )
          {
            foreach ( $Subfields as $Sub => $Separator )
            {
              foreach ( $Subrecord as $Key => $Value )
              {
                if ( $Key == "I1" || $Key == "I2" )
                {
                  $Indys[$Key] = $Value;
                  continue;
                }

                if ( (string)$Key == (string)$Sub )
                {
                  // M024a = only ISMN if first indicator == "2"
                  if (  $Field == "024" && (string)$Key == "a" && isset($Indys["I1"]) && $Indys["I1"] != "2" ) continue;

                  if ( ! in_array($Value, $Tmp) )
                  {
                    // New value
                    if ( count($Tmp) >= 1 )
                    {
                      if ( strpos($Separator, "~") === false )
                      {
                        $Output .= ($Separator != "") ? $Separator : " ";
                        $Output .= htmlspecialchars($Value);
                      }
                      else
                      {
                        $Doppelt = explode("~",$Separator);
                        $Output .= $Doppelt[0] . htmlspecialchars($Value) . $Doppelt[1];
                      }
                    }
                    else
                    {
                      $Output .= htmlspecialchars($Value);
                    }
                    $Tmp[]  = htmlspecialchars($Value);
                  }
                }
              }
            }
          }
        }
      }
    }
    return $Output;
  }

  protected function GetAuthors()
  {
    $Authors   = array();

    $People = $this->GetArray(array("100" => array("a","c","4","e")));
    foreach ( $People as $One )
    {
      $Name = 
      $Authors[]   = array("name" => $this->FormatPersonName($One),
                           "role" => $this->FormatPersonRole($One, true));
    }

    $People = $this->GetArray(array("700" => array("a","c","4","e")));
    foreach ( $People as $One )
    {
      // Only catch authors
      if ( ( isset($One["4"]) && $One["4"] == "aut" ) 
        || ( isset($One["e"]) && in_array(strtolower(substr($One["e"],0,7)), array("verfass","author")) ) ) 
      {
        $Authors[]   = array("name" => $this->FormatPersonName($One), 
                             "role" => $this->FormatPersonRole($One, true));
      }
    }
    return $Authors;
  }

  protected function GetAssociates()
  {
    $Associates = array();
  
    // Get 700a people
    $People = $this->GetArray(array("700" => array("a","c","4","e")));
    foreach ( $People as $One )
    {
      // Skip 700a author(s)
      if ( ( isset($One["4"]) && $One["4"] == "aut" ) 
        || ( isset($One["e"]) && in_array(strtolower(substr($One["e"],0,7)), array("verfass","author")) ) )   continue;

      $Associates[]  = array("name" => $this->FormatPersonName($One),
                             "role" => $this->FormatPersonRole($One, false));
    }
    return $Associates;
  }

  protected function FormatPersonName($One)
  {
    if ( isset($One["a"]) && $One["a"] != "" && isset($One["c"]) && $One["c"] != "" )
    {
      return htmlspecialchars($One["a"] . " " . $One["c"]);
    }
    if ( !isset($One["a"]) && isset($One["c"]) && $One["c"] != "" )
    {
      return htmlspecialchars($One["a"]);
    }
    if ( isset($One["a"]) && $One["a"] != "" && !isset($One["c"]) )
    {
      return htmlspecialchars($One["a"]);
    }
  }

  protected function FormatPersonRole($One, $onlyauthor = true)
  {
    $Role = "";
    if (isset($One["4"]) ? $One["4"] : "") 
    {
      $Role = $this->CI->database->code2text($One["4"]);
    }
    elseif (isset($One["e"]) && $One["e"] != "" && strlen($One["e"]) == 3)
    {
      $Role = $this->CI->database->code2text($One["e"]);
    }
    elseif (isset($One["e"]) && $One["e"] != "") 
    {
      $Tmp = preg_replace("/[^a-zA-Z0-9öäü ]+/", "", strtolower($One["e"]));
      if ( in_array($Tmp, array("bearb","begr","hrsg","komp","mitarb","red","ubers","adressat","komm","stecher","verstorb","zeichner","präses","praeses","resp","widmungsempfänger","widmungsempfaenger","zensor","beiträger","beitraeger","beiträger k","beitraeger k","beiträger m","beitraeger m","interpr","verf")) )
      {
        $Tmp = str_replace(
          array("bearb","begr","hrsg","komp","mitarb","red","ubers","adressat","komm","stecher","verstorb","zeichner","präses","praeses","resp","widmungsempfänger","widmungsempfaenger","zensor","beiträger","beitraeger","beiträger k","beitraeger k","beiträger m","beitraeger m","interpr","verf"), 
          array("EDI","FDR","EDT","CMP","CBR","PBD","TRL","RCP","CMM","EGR","DCS","DRM","CHM","CHM","RSP","DTE","DTE","CNS","CTL","CTL","CTA","CTA","CTM","CTM","IPT","AUT"), $Tmp);
        $Role = $this->CI->database->code2text($Tmp);
      }
    }
    else
    {
      $Role = ( $onlyauthor ) ? $this->CI->database->code2text("aut") : "";
    }
    return $Role;
  }

  protected function GetPublisher()
  {
    $Publisher = $this->PrettyFields(array("250" => array("a" => "~."))) . " ";

    if ( isset($this->contents["264"]) )
    {
      $Publisher .= $this->PrettyFields(array("264" => array("c" => ", ")));
    }
    else
    {
      $Publisher .= $this->PrettyFields(array("260" => array("c" => ", ")));
    }

    if ( trim($Publisher) == "" )
    {
      $Publisher = $this->PrettyFields(array("952" => array("j" => "~.")));
    }
    return trim($Publisher);
  }

  protected function GetArray($Filter)
  {
    // Last identical subfield will survive
    $Output = array();
    foreach ( $Filter as $Field => $Subfields )
    {
      if ( array_key_exists($Field, $this->contents) )
      {
        foreach ($this->contents[$Field] as $Record)
        {
          $Tmp = array();
          foreach ( $Record as $Subrecord )
          {
            foreach ( $Subfields as $Sub )
            {
              foreach ( $Subrecord as $Key => $Value )
              {
                if ( (string)$Key == (string)$Sub )
                {
                  if ( $Key == "w" && !in_array(substr($Value,0,8), array("(DE-601)","(DE-627)") ) )  continue; 
                  $Tmp[$Sub] = htmlspecialchars($Value);
                }
              }
            }
          }
          $Output[] = $Tmp;
        }
      }
    }
    return $Output;
  }

  protected function GetArrayFirst($Filter)
  {
    // First identical subfield will survive
    $Output = array();
    foreach ( $Filter as $Field => $Subfields )
    {
      if ( array_key_exists($Field, $this->contents) )
      {
        foreach ($this->contents[$Field] as $Record)
        {
          $Tmp = array();
          foreach ( $Record as $Subrecord )
          {
            foreach ( $Subfields as $Sub )
            {
              foreach ( $Subrecord as $Key => $Value )
              {
                if ( (string)$Key == (string)$Sub )
                {
                  if ( $Key == "w" && !in_array(substr($Value,0,8), array("(DE-601)","(DE-627)") ) )  continue; 
                  if ( !isset($Tmp[$Sub]) ) $Tmp[$Sub] = htmlspecialchars($Value);
                }
              }
            }
          }
          $Output[] = $Tmp;
        }
      }
    }
    return $Output;
  }

  protected function GetSimpleArray($Filter)
  {
    $Output = array();
    foreach ( $Filter as $Field => $Subfields )
    {
      if ( array_key_exists($Field, $this->contents) )
      {
        foreach ($this->contents[$Field] as $Record)
        {
          foreach ( $Record as $Subrecord )
          {
            foreach ( $Subfields as $Sub )
            {
              foreach ( $Subrecord as $Key => $Value )
              {
                if ( (string)$Key == (string)$Sub )
                {
                  if ( !in_array($Value, $Output) )  $Output[] = htmlspecialchars($Value);
                }
              }
            }
          }
        }
      }
    }
    return $Output;
  }

  protected function GetCompleteArray($Filter)
  {
    $Output = array();
    foreach ( $Filter as $Field => $Subfields )
    {
      if ( array_key_exists($Field, $this->contents) )
      {
        foreach ($this->contents[$Field] as $Record)
        {
          $Tmp = array();
          foreach ( $Record as $Subrecord )
          {
            foreach ( $Subfields as $Sub )
            {
              foreach ( $Subrecord as $Key => $Value )
              {
                if ( (string)$Key == (string)$Sub )
                {
                  $Tmp[$Sub][] = htmlspecialchars($Value);
                }
              }
            }
          }
          $Output[] = $Tmp;
        }
      }
    }
    return $Output;
  }
  
  protected function AddOriginalCharacters($pretty, $area)
  {
    $origin = $this->GetArray(array("880" => array("6","a","b","c","e","4")));

    foreach($origin as $rec)
    { 
      if ( $area == "preview" )
      {
        // Title
        if ( isset($rec["6"]) && substr($rec["6"],0,3) == "245" )
        {
          if ( isset($rec["a"]) && $rec["a"] != "" && isset($rec["b"]) && $rec["b"] != "" )
          {
            $pretty["title"] .= ( isset($pretty["title"]) && $pretty["title"] != "" ) ? " | " . $rec["a"] . " : " . $rec["b"] : $rec["a"] . " : " . $rec["b"];
          }
          if ( isset($rec["a"]) && $rec["a"] != "" && (!isset($rec["b"]) || $rec["b"] == "" ) )
          {
            $pretty["title"] .= ( isset($pretty["title"]) && $pretty["title"] != "" ) ? " | " . $rec["a"] : $rec["a"];
          }
          if ( ( !isset($rec["a"]) || $rec["a"] == "" ) && isset($rec["b"]) && $rec["b"] != "" )
          {
            $pretty["title"] .= ( isset($pretty["title"]) && $pretty["title"] != "" ) ? " | " . $rec["b"] : $rec["b"];
          }
          if ( isset($rec["c"]) && $rec["c"] != "" )     $pretty["title"] .= " " . $rec["c"];
        }

        // Author
        if ( isset($rec["6"]) && substr($rec["6"],0,3) == "100" )
        {
          $pretty["author"][] = array("name" => $this->FormatPersonName($rec),
                                      "role" => $this->FormatPersonRole($rec, true));
        }

        // PV_Publisher
        if ( isset($rec["6"]) && ( substr($rec["6"],0,3) == "260" || substr($rec["6"],0,3) == "264" || substr($rec["6"],0,3) == "250" ) )
        {
          if ( isset($pretty["pv_publisher"]) && $pretty["pv_publisher"] != ""  ) $pretty["pv_publisher"] .= " | ";

          if ( isset($rec["a"]) && $rec["a"] != "" )  $pretty["pv_publisher"] .= $rec["a"];
          if ( isset($rec["c"]) && $rec["c"] != "" )  $pretty["pv_publisher"] .= " " . $rec["c"];
        }

        // Serial
        if ( isset($rec["6"]) && substr($rec["6"],0,3) == "490" )
        {
          $Tmp = array();
          if ( isset($rec["a"]) && $rec["a"] != "" )  $Tmp["a"] = $rec["a"];
          if ( isset($rec["v"]) && $rec["v"] != "" )  $Tmp["v"] = $rec["v"];
          if ( isset($pretty["serial"]) )
          {
            if ( !in_array($Tmp,$pretty["serial"]) )
            {
              array_push($pretty["serial"],$Tmp);
            }
            else
            {
              $pretty["serial"] = array($Tmp);
            }
          }
        }
      }

      if ( $area == "fullview" )
      {

        // Associates
        if ( isset($rec["6"]) && substr($rec["6"],0,3) == "700" )
        {
          $pretty["associates"][] = array("name" => $this->FormatPersonName($rec),
                                          "role" => $this->FormatPersonRole($rec, false));
        }

        // Publisher
        if ( isset($rec["6"]) && ( substr($rec["6"],0,3) == "260" || substr($rec["6"],0,3) == "264" ) )
        {
          $Tmp = array();
          if ( isset($rec["a"]) && $rec["a"] != "" )  $Tmp["a"] = $rec["a"];
          if ( isset($rec["b"]) && $rec["b"] != "" )  $Tmp["b"] = $rec["b"];
          if ( isset($rec["c"]) && $rec["c"] != "" )  $Tmp["c"] = $rec["c"];
          if ( isset($pretty["publisher"]) )
          {
            if ( !in_array($Tmp,$pretty["publisher"]) )
            {
              array_push($pretty["publisher"],$Tmp);
            }
            else
            {
              $pretty["publisher"] = array($Tmp);
            }
          }
        }

        // Edition
        if ( isset($rec["6"]) && substr($rec["6"],0,3) == "250" )
        {
          $pretty["edition"] = (isset($pretty["edition"]) && $pretty["edition"] != "" ) 
                             ? $pretty["edition"] . " | " . $rec["a"] 
                             : $rec["a"];
        }
      }
    }
    return $pretty;
  }

  protected function GetProvenance()
  {
    $Provenance = array();
    if ( array_key_exists("561", $this->contents) )
    {
      $Tmp        = $this->GetArray(array("561" => array("3","5","a")));
      foreach ( $Tmp as $P )
      {
        if ( $_SESSION["filter"]["datapool"] == "local" && isset($P["5"]) && $_SESSION["config_general"]["general"]["isil"] != $P["5"] )  continue;
        if ( !isset($P["3"]) && !isset($P["a"]) )  continue;

        if ( !isset( $_SESSION["isils"][$P["5"]]) )
        {
          $_SESSION["isils"][$P["5"]] = $this->CI->database->getCentralDB("isil", array("isil" => $P["5"]))[$P["5"]];
        }

        $Text = (isset($_SESSION["isils"][$P["5"]]["shortname"])) ? $_SESSION["isils"][$P["5"]]["shortname"] . " ": "";
        if ( isset($P["3"]) )
        {
          $Teile = explode("Signatur:", $P["3"]);
          $Text .= (count($Teile) > 1) ? trim($Teile[1])      : trim($P["3"]);
        }
        $Text .= (isset($P["3"]) && isset($P["a"])) ? ", "    : "";

        if ( isset($P["a"]) )
        {
          $Teile = explode(" ", str_replace("  ", " ", $P["a"]));
          $Found = false;
          foreach ($Teile as $Ind => $Teil) 
          {
            if ( strripos($Teil, "https://") !== false || strripos($Teil, "http://") !== false )
            {
              $Found = true;
              unset($Teile[$Ind]);
            }
          }
          $Tmp2 = implode(" ", $Teile);
          if ( $Found ) 
          {
            $Teile = explode("§%§", str_replace(array(":", ";"), "§%§", $Tmp2));
            if ( isset($Teile[1]) && trim($Teile[1]) )
            {
              $Teile[0] = $Teile[0] . ":";
              $Teile[1] = "<a href='javascript:$.link_search(\"author\",\"" . trim(str_replace(","," ",$Teile[1])) . "\")'>" . trim($Teile[1]) . "</a>";
            }
            $Tmp2 = implode(" ", $Teile);
          }
          $Text .= $Tmp2;
        }
        $Provenance[] =  trim($Text);
      }
    }
    return $Provenance;
  }

  protected function GetDOIs()
  {
    $DOIs = array();
    if ( array_key_exists("024", $this->contents) )
    {
      $DOI = $this->GetArray(array("024" => array("a","2")));
      foreach ($DOI as $One) 
      {
        if ( isset($One["2"]) && isset($One["a"]) && $One["2"] == "doi" && $One["a"] != "" )  $DOIs[] = $One["a"];
      }
    }
    return $DOIs;
  }

  protected function SetCover($type = "preview")
  {
    $ExternalCover = ( isset($_SESSION["config_discover"]["cover"]["covermode"]) && $_SESSION["config_discover"]["cover"]["covermode"] == "1" ) ? true : false;
    $ExternalPath = ( isset($_SESSION["config_discover"]["cover"]["coverpath"]) && $_SESSION["config_discover"]["cover"]["coverpath"] != "" ) ? $_SESSION["config_discover"]["cover"]["coverpath"] : "";
    $CoverSize = ( isset($_SESSION["config_discover"]["cover"]["coversize"]) && $_SESSION["config_discover"]["cover"]["coversize"] != "" ) ? $_SESSION["config_discover"]["cover"]["coversize"] : "";
    $CoverToken = ( isset($_SESSION["config_discover"]["cover"]["covertoken"]) && $_SESSION["config_discover"]["cover"]["covertoken"] != "" ) ? $_SESSION["config_discover"]["cover"]["covertoken"] : "";
    
    if ( $this->isbn != "" && $ExternalCover && $ExternalPath != "" && $CoverSize != "" && $CoverToken != "" )
    {
      // External Cover
      $CoverPath = str_replace("{isbn}",$this->isbn, $ExternalPath."/".$CoverSize."?access_token=".$CoverToken);
      return "<img class='external2image img-responsive' data-cover='" . $this->cover . "' src='" . $CoverPath . "' width='75px' onerror='$.correctCover(this,\"" . $this->cover . "\");' />";
    }
    else
    {
      // Internal Cover
      return "<span class='gbvicon'>" . $this->cover . "</span>";
    }
  }
  
  protected function SetCoverPublication($cover)
  {
    // Internes Cover
    return "<span class='gbvicon center-block'>" . $this->cover . "</span>";
  }

  protected function Link($Typ, $Value, $Text="")
  {
    if ( in_array($Typ,array("id","author","class","foreignid","genre","publisher","series","subject","year")) )
    {
      // Internal links
      return "<a href='javascript:$.link_search(\"" . $Typ . "\",\"" . str_replace(array('&quot;',"'"),' ', $Value) . "\")'>" . (($Text=="") ? $Value : $Text). " <span class='fa fa-link'></span></a>";
    }
    elseif ($Typ == "web")
    {
      return "<a href='" . $Value . "' target='_blank'>" . $Text . " <span class='fa fa-external-link'></span></a>";
    }
    else
    {
      // External links
      $Host = (parse_url($Value,PHP_URL_HOST)) ? parse_url($Value,PHP_URL_HOST) : "";
      if ( substr($Host,0,4) == "www.")   $Host = substr($Host,4);
      if ( $Host != "") $Host = " (" . $Host .")";
      return 
        "<a href='" . $Value . "' target='_blank'>" . $Typ . $Host . " <span class='fa fa-external-link'></span></a>";
    }
  }

  public function GetMARC($Contents, $Field, $OnlyFirstRecord = false, $OnlyW601 = false, $OnlyFirstSubfield = false)
  {
    // Return first MARC fields as string
    if ( $Field >= "000" && $Field <= "009")   return ( array_key_exists($Field, $Contents) ) ? $Contents[$Field] : "-";

    // Return remaining MARC fields as array
    $Records = array();
    if ( array_key_exists($Field, $Contents) )  
    {
      foreach ( $Contents[$Field] as $Rec )
      {
        $Record = array();
        foreach ( $Rec as $Subrec )
        {
          if ( $OnlyW601 && isset($Subrec["w"]) && !in_array(substr($Subrec["w"],0,8), array("(DE-601)","(DE-627)")) ) continue;
          foreach ( $Subrec as $Key => $Value )
          {
            if ( $OnlyFirstSubfield )
            {
              if ( ! isset($Record[$Key]) ) $Record[$Key] = ( $OnlyW601 && $Key == "w" && in_array(substr($Value,0,8),array("(DE-601)","(DE-627)") ) ) ? substr($Value,8) : $Value;
            }
            else
            {
              $Record[$Key] = ( $OnlyW601 && $Key == "w" && in_array(substr($Value,0,8),array("(DE-601)","(DE-627)") ) ) ? substr($Value,8) : $Value;
            }
          }
        }
        if ( $OnlyFirstRecord ) return $Record;
        $Records[] = $Record;
      }
    }
    return $Records;
  }

  public function GetMARCSubfield($Contents, $Field, $Subfield )
  {
    $Data = array();
    if ( array_key_exists($Field, $Contents) )
    {
      foreach ( $Contents[$Field] as $Record )
      {
        foreach ( $Record as $Subrecord )
        {
          foreach ( $Subrecord as $Key => $Value )
          {
            if ( $Key == $Subfield )
            {
              $Data[] = $Value;
            }
          }
        }
      }
    }
    return ($Data);
  }

  public function GetMARCSubfieldFirstString($Contents, $Field, $Subfield )
  {
    if ( array_key_exists($Field, $Contents) )
    {
      foreach ( $Contents[$Field] as $Record )
      {
        foreach ( $Record as $Subrecord )
        {
          foreach ( $Subrecord as $Key => $Value )
          {
            if ( $Key == $Subfield )
            {
              return htmlentities($Value);
            }
          }
        }
      }
    }
    return "";
  }

  public function GetMARCFullArray($Contents, $Field)
  {
    // Return first MARC fields as string
    if ( $Field >= "000" && $Field <= "009")   return ( array_key_exists($Field, $Contents) ) ? $Contents[$Field] : "-";

    // Return remaining MARC fields as array ( grouped by subfield, values always array )
    $Records = array();
    if ( array_key_exists($Field, $Contents) )  
    {
      foreach ( $Contents[$Field] as $Rec )
      {
        $Record = array();
        foreach ( $Rec as $Subrec )
        {
          foreach ( $Subrec as $Key => $Value )
          {
            if ( !isset($Record[$Key]) ) $Record[$Key] = array();
            $Record[$Key][] = $Value;
          }
        }
        $Records[] = $Record;
      }
    }
    return $Records;
  }

  public function GetRelatedPubs($T, $PPN, $Modus)
  {
    // Modus
    // 1: Mehrbändige Werke
    // 2: Schriftenreihen
    // 3: Enthaltene Werke
  
    $RelatedPubs = array();
    $PPNLink = $this->CI->internal_search("ppnlink",$PPN);
    if ( ! isset($PPNLink["results"]) ) return ($RelatedPubs);
  
    //$this->printArray2Screen($PPNLink);
  
    $PPNStg = json_encode(array_keys($PPNLink["results"]));
  
    foreach ( $PPNLink["results"] as $One )
    {
      $Pretty = $T->SetContents("preview");
  
      $Title = "";
      if ( $Modus == 1 || $Modus == 3 )
      {
        $Title = $this->Get245npa($One["contents"], $Modus);
        $Sort  = $this->Get245n($One["contents"]);
    }
      else
      {
        $Title = $this->Get245an($One["contents"]);
        $Sort  = $this->Get490v($One["contents"]);
      }
  
      $Publisher = "";
      $Publisher = $this->Get250a($One["contents"]);
      $Publisher = ($Publisher != "" ) ? $Publisher . ", " . $this->GetPublisherYear($One["contents"]) :  $this->GetPublisherYear($One["contents"]);
        
      $RelatedPubs[$One["id"]] = array
      (
      "format"    => $One["format"],
      "cover"     => $One["cover"],
      "title"     => $Title,
      "publisher" => $Publisher,
      "sort"      => $Sort
      );
    }
    uasort($RelatedPubs, function ($a, $b) { return $a['sort'] <=> $b['sort']; });
    return ($RelatedPubs);
  }

  public function GetIncludedPubs($T, $PPN)
  {
    // Zeitschriften mit Einzelheften
    $PPNLink  = $this->CI->internal_search("ppnlink",$PPN, '("Book","Journal","Serial Volume")');
    $PPNStg   = json_encode(array_keys($PPNLink["results"]));
    $Journals = array();
    $Counter  = 0;
    foreach ( $PPNLink["results"] as $One )
    {
      $Pretty = $T->SetContents("preview");
  
      if ( substr($One["leader"],7,1) == "m" || substr($One["leader"],7,1) == "d" )
      {
        $Counter++;
        $Title = $this->Get245ab($One["contents"]);
        if ( $Title == "" )  $Title = $this->Get490av($One["contents"]);
        if ( $Title == "" )  $Title = "Nr." . $Counter;

        $Sort  = explode(".", $this->Get490v($One["contents"]));
        $Sort  = $Sort[0];
  
        $Journals[$One["id"]] = array
        (
        "format"    => $One["format"],
        "cover"     => $One["cover"],
        "title"     => $Title,
        "publisher" => $this->GetPublisherYear($One["contents"]),
        "sort"      => $Sort
        );
      }
      uasort($Journals, function ($a, $b) { return $a['sort'] <=> $b['sort']; });
    }
  
    // Artikel
    $PPNLink = $this->CI->internal_search("ppnlink",$PPN, '("Article")');
    $PPNStg   = json_encode(array_keys($PPNLink["results"]));
    $Articles = array();
    $Counter  = 0;
    foreach ( $PPNLink["results"] as $One )
    {
      $Pretty = $T->SetContents("preview");
  
      if ( substr($One["leader"],7,1) == "a" )
      {
        $Articles[$One["id"]] = array
        (
        "format"    => $One["format"],
        "cover"     => $One["cover"],
        "title"     => $this->Get245ab($One["contents"]),
        "publisher" => $this->Get952j($One["contents"])
        );
      }
    }
    return (array("articles" => $Articles, "journals" => $Journals ));
  }  

  private function Get245ab($Contents)
  {
    $Titel = "";
    if ( array_key_exists("245", $Contents) )
    {
      foreach ( $Contents["245"] as $Record )
      {
        foreach ( $Record as $Subrecord )
        {
          foreach ( $Subrecord as $Key => $Value )
          {
            if ( $Key == "a" )    $Titel .= ($Titel != "" ) ? " | " . $Value : $Value;
            if ( $Key == "b" )    $Titel .= ($Titel != "" ) ? " : " . $Value : $Value;
          }
        }
      }
    }
    return ($Titel);
  }
  
  private function Get245an($Contents)
  {
    $Titel = "";
    if ( array_key_exists("245", $Contents) )
    {
      foreach ( $Contents["245"] as $Record )
      {
        foreach ( $Record as $Subrecord )
        {
          foreach ( $Subrecord as $Key => $Value )
          {
            if ( $Key == "a" )    $Titel .= ($Titel != "" ) ? " | " . $Value : $Value;
            if ( $Key == "n" )    $Titel .= ($Titel != "" ) ? " : " . $Value : $Value;
          }
        }
      }
    }
    return ($Titel);
  }
  
  private function Get245npa($Contents, $Modus)
  {
    $Titel = "";
    $A = "";
    if ( array_key_exists("245", $Contents) )
    {
      foreach ( $Contents["245"] as $Record )
      {
        foreach ( $Record as $Subrecord )
        {
          foreach ( $Subrecord as $Key => $Value )
          {
            if ( $Key == "n" )                 $Titel .= ($Titel != "" ) ? " | " . $Value : $Value;
            if ( $Key == "p" && $Modus == 1 )  $Titel .= ($Titel != "" ) ? ", " . $Value : $Value;
            if ( $Key == "p" && $Modus == 3 )  $Titel .= ($Titel != "" ) ? ": " . $Value : $Value;
            if ( $Key == "a" )                 $A = $Value;
          }
        }
      }
    }
  
    if ( $Titel == "" && $A != "" ) $Titel = $A;
  
    return ($Titel);
  }

  private function Get245n($Contents)
  {
    if ( array_key_exists("245", $Contents) )
    {
      foreach ( $Contents["245"] as $Record )
      {
        foreach ( $Record as $Subrecord )
        {
          foreach ( $Subrecord as $Key => $Value )
          {
            if ( $Key == "n" ) return $Value;
          }
        }
      }
    }
    return ("");
  }
  
  private function Get490v($Contents)
  {
    if ( array_key_exists("490", $Contents) )
    {
      foreach ( $Contents["490"] as $Record )
      {
        foreach ( $Record as $Subrecord )
        {
          foreach ( $Subrecord as $Key => $Value )
          {
            if ( $Key == "v" )    return ($Value);
          }
        }
      }
    }
    return ("");
  }

  private function Get250a($Contents)
  {
    if ( array_key_exists("250", $Contents) )
    {
      foreach ( $Contents["250"] as $Record )
      {
        foreach ( $Record as $Subrecord )
        {
          foreach ( $Subrecord as $Key => $Value )
          {
            if ( $Key == "a" )    return ($Value);
          }
        }
      }
    }
    return ("");
  }
  
  private function GetPublisherYear($Contents)
  {
    if ( array_key_exists("245", $Contents) )
    {
      foreach ( $Contents["245"] as $Record )
      {
        foreach ( $Record as $Subrecord )
        {
          foreach ( $Subrecord as $Key => $Value )
          {
            if ( $Key == "n" )  return $Value;
          }
        }
      }
    }
    if ( array_key_exists("264", $Contents) )
    {
      foreach ( $Contents["264"] as $Record )
      {
        foreach ( $Record as $Subrecord )
        {
          foreach ( $Subrecord as $Key => $Value )
          {
            if ( $Key == "c" )  return $Value;
          }
        }
      }
      return $Jahr;
    }
    if ( array_key_exists("260", $Contents) )
    {
      foreach ( $Contents["260"] as $Record )
      {
        foreach ( $Record as $Subrecord )
        {
          foreach ( $Subrecord as $Key => $Value )
          {
            if ( $Key == "c" )  return $Value;
          }
        }
      }
    }
    return "";
  }
  
  private function Get490av($Contents)
  {
    $Titel = "";
    if ( array_key_exists("490", $Contents) )
    {
      foreach ( $Contents["490"] as $Record )
      {
        foreach ( $Record as $Subrecord )
        {
          foreach ( $Subrecord as $Key => $Value )
          {
            if ( $Key == "a" )    $Titel .= ($Titel != "" ) ? " | " . $Value : $Value;
            if ( $Key == "v" )    $Titel .= ($Titel != "" ) ? " ; " . $Value : $Value;
          }
        }
      }
    }
    return ($Titel);
  }
  
  private function Get952j($Contents)
  {
    $Jahr = "";
    if ( array_key_exists("952", $Contents) )
    {
      foreach ( $Contents["952"] as $Record )
      {
        foreach ( $Record as $Subrecord )
        {
          foreach ( $Subrecord as $Key => $Value )
          {
            if ( $Key == "j" )    $Jahr .= ($Jahr != "" ) ? " | " . $Value : $Value;
          }
        }
      }
    }
    return ($Jahr);
  }

  protected function getLBSName($isil)
  {
    return ( (isset($_SESSION["info"]["names"][$isil]) && $_SESSION["info"]["names"][$isil] != "" ) ? $_SESSION["info"]["names"][$isil] : $isil );
  }

  protected function getLBSILN($isil)
  {
    return ( ( isset($_SESSION["interfaces"]["lbs2"]) && $_SESSION["interfaces"]["lbs2"] == 1 && isset($_SESSION["config_general"]["general"]["ilnsecond"]) && isset($_SESSION["info"]["2"]["isil"]) && isset($_SESSION["info"]["2"]["iln"]) && $isil == $_SESSION["info"]["2"]["isil"] ) 
             ? $_SESSION["info"]["2"]["iln"] : $_SESSION["info"]["1"]["iln"] );

  }

  // *****************
  // * New functions * 
  // *****************

  private function CheckILN($ILN)
  {
    return ( isset($this->contents[912]) && $ILN && ( in_array( "GBV_ILN_".$ILN, $this->catalogues) ) ) ? true : false;
  }

  private function CheckParentILN($ILN)
  {
    // Einlesen des Elternteils
    $ParentContents = array();
    if ( count($this->medium["parents"]) )
    {
      $ParentPPN   = ( isset($this->medium["parents"][0]) ) ? $this->medium["parents"][0]          : "";
      if ( $ParentPPN != "" && $this->CI->EnsurePPN($ParentPPN) ) 
      {
        $ParentContents   = ( isset($_SESSION["data"]["results"][$ParentPPN]["contents"]) )   ? $_SESSION["data"]["results"][$ParentPPN]["contents"]   : "";
        $ParentCatalogues = ( isset($_SESSION["data"]["results"][$ParentPPN]["catalogues"]) ) ? $_SESSION["data"]["results"][$ParentPPN]["catalogues"] : "";
      }
    }
    return ( isset($ParentContents[912]) && $ILN && ( in_array( "GBV_ILN_".$ILN, $ParentCatalogues) ) ) ? true : false;
  }

  protected function isOwner()  
  {
    $ILN       = ( isset($_SESSION["iln"]) && $_SESSION["iln"] != "" ) 
                 ? $_SESSION["iln"] 
                 : "";
    $ClientILN = ( isset($_SESSION["config_general"]["general"]["client"])  && $_SESSION["config_general"]["general"]["client"] != "" ) 
                 ? $_SESSION["iln"]."_".strtoupper($_SESSION["config_general"]["general"]["client"])
                 : "";

    return ( ( $this->CheckILN($ILN) && !$ClientILN ) || ( $this->CheckILN($ClientILN) && $ClientILN ) );
  }

  protected function isSecondILNOwner()  
  {
    $SecILN = ( isset($_SESSION["config_general"]["general"]["ilnsecond"]) && $_SESSION["config_general"]["general"]["ilnsecond"] != "" )
              ? $_SESSION["config_general"]["general"]["ilnsecond"]
              : "";

    return $this->CheckILN($SecILN);
  }

  protected function isMoreILNOwner()  
  {
    $ILNs = ( isset($_SESSION["config_general"]["general"]["ilnmore"]) && $_SESSION["config_general"]["general"]["ilnmore"] != "" )
            ? array_unique(explode(",",$_SESSION["config_general"]["general"]["ilnmore"]))
            : array();

    foreach ($ILNs as $ILN)
    {
      if ( $this->CheckILN($ILN) )  return true;
    }
    return false;
  }

  protected function ParentisOwner()  
  {

    $ILN       = ( isset($_SESSION["iln"]) && $_SESSION["iln"] != "" ) 
                 ? $_SESSION["iln"] 
                 : "";
    $ClientILN = ( isset($_SESSION["config_general"]["general"]["client"])  && $_SESSION["config_general"]["general"]["client"] != "" ) 
                 ? $_SESSION["iln"]."_".strtoupper($_SESSION["config_general"]["general"]["client"])
                 : "";

    return ( ( $this->CheckParentILN($ILN) && !$ClientILN ) || ( $this->CheckParentILN($ClientILN) && $ClientILN ) );
  }

  protected function ParentisSecondILNOwner()  
  {
    $SecILN = ( isset($_SESSION["config_general"]["general"]["ilnsecond"]) && $_SESSION["config_general"]["general"]["ilnsecond"] != "" )
              ? $_SESSION["config_general"]["general"]["ilnsecond"]
              : "";

    return $this->CheckParentILN($SecILN);
  }

  protected function ParentisisMoreILNOwner()  
  {
    $ILNs = ( isset($_SESSION["config_general"]["general"]["ilnmore"]) && $_SESSION["config_general"]["general"]["ilnmore"] != "" )
            ? array_unique(explode(",",$_SESSION["config_general"]["general"]["ilnmore"]))
            : array();

    foreach ($ILNs as $ILN)
    {
      if ( $this->CheckParentILN($ILN) )  return true;
    }
    return false;
  }

  protected function isOnline()
  {
    return ( ( ( substr($this->medium["leader"],6,2) == "ma"
    || substr($this->medium["leader"],6,2) == "mm"
    || substr($this->medium["leader"],6,2) == "ms" 
    || substr($this->medium["leader"],6,2) == "aa" 
    || substr($this->medium["leader"],6,2) == "ab" 
    || substr($this->medium["leader"],6,2) == "am" 
    || substr($this->medium["leader"],6,2) == "as" )
    && substr($this->GetMARC($this->contents,"007"),0,2) == "cr" )
    || ( in_array("Gutenberg", $this->collection) ) ) ? true : false;
  }

  protected function isOnlineNew()
  {
    return ( substr($this->GetMARC($this->contents,"007"),0,2) == "cr" 
              || in_array("Gutenberg", $this->collection) ) ? true : false;
  }

  protected function isMulti()
  {
    return ( in_array($this->medium["format"],array("book","journal","monographseries","serialvolume","unknown")) ) ? true : false;
  }

  protected function getMulti($WithIncludedPubs=true)
  {
    $Exemplars = array();

    // Mehrbändige Werke, Schriftenreihen, Zeitschriften mit Einzelheften
    if ( in_array($this->medium["format"],array("book","monographseries","unknown")) )
    {
      // Mehrbändige Werke
      $Exemplars[] = array("label"  => $this->CI->database->code2text("RELATEDPUBLICATIONS"), 
                           "rembef" => array(),
                           "data"   => $this->GetRelatedPubsNew($this,$this->PPN,1),
                           "remaft" => array());
    }
    
    if ( in_array($this->medium["format"],array("serialvolume")) )
    {
      // Schriftenreihen
      $Exemplars[] = array("label"  => $this->CI->database->code2text("RELATEDPUBLICATIONS"),
                           "rembef" => array(),
                           "data"   => $this->GetRelatedPubsNew($this,$this->PPN,2),
                           "remaft" => array());
    }
   
    if ( in_array($this->medium["format"],array("journal","ejournal")) )
    {
      $IncludedPubs = $this->GetIncludedPubsNew($this,$this->PPN);
      if ( $WithIncludedPubs )
      {
        // Zeitschriften mit Einzelheften
        if ( count($IncludedPubs["journals"]) )
        {
          $Exemplars[] = array("label"  => $this->CI->database->code2text("RELATEDJOURNALS"),
                               "rembef" => array(),
                               "data"   => $IncludedPubs["journals"],
                               "remaft" => array());
        }
      }
      if ( count($IncludedPubs["articles"]) )
      {
        $Exemplars[] = array("label"  => $this->CI->database->code2text("RELATEDARTICLES"),
                             "rembef" => array(),
                             "data"   => $IncludedPubs["articles"],
                             "remaft" => array());
      }
    }

    return $Exemplars;
  }

  public function GetRelatedPubsNew($T, $PPN, $Modus)
  {
    // Modus
    // 1: Mehrbändige Werke
    // 2: Schriftenreihen
    // 3: Enthaltene Werke
  
    $RelatedPubs = array();
    $PPNLink = $this->CI->internal_search("ppnlink",$PPN);

    if ( ! isset($PPNLink["results"]) ) return ($RelatedPubs);
  
    $PPNStg = json_encode(array_keys($PPNLink["results"]));
  
    foreach ( $PPNLink["results"] as $One )
    {
      $Pretty = $T->SetContents("preview");
  
      $Title = "";
      if ( $Modus == 1 || $Modus == 3 )
      {
        $Title = $this->Get245npa($One["contents"], $Modus);
        $Sort  = $this->Get245n($One["contents"]);
      }
      else
      {
        $Title = $this->Get245an($One["contents"]);
        $Sort  = $this->Get490v($One["contents"]);
      }
  
      $Publisher = "";
      $Publisher = $this->Get250a($One["contents"]);
      $Publisher = ($Publisher != "" ) ? $Publisher . ", " . $this->GetPublisherYear($One["contents"]) :  $this->GetPublisherYear($One["contents"]);
        
      $RelatedPubs[$One["id"]] = array
      (
      "format"    => $One["format"],
      "cover"     => $One["cover"],
      "type"      => "ppn",
      "link"      => $One["id"],
      "label1"    => $Title,
      "label2"    => $Publisher,
      "sort"      => $Sort
      );
    }
    uasort($RelatedPubs, function ($a, $b) { return $a['sort'] <=> $b['sort']; });
    return ($RelatedPubs);
  }
  
  public function GetIncludedPubsNew($T, $PPN)
  {
    $Exemplars = array();

    $Journals = array();
    /*
    // Zeitschriften mit Einzelheften
    $PPNLink  = $this->CI->internal_search("ppnlink",$PPN, '("Book","Journal","Serial Volume")');
    // $PPNLink  = $this->CI->internal_search("ppnlink",$PPN);
    $PPNStg   = json_encode(array_keys($PPNLink["results"]));
    $Counter  = 0;
    foreach ( $PPNLink["results"] as $One )
    {
      $Pretty = $T->SetContents("preview");
  
      $Counter++;
      $Title = $this->Get245ab($One["contents"]);
      if ( $Title == "" )  $Title = $this->Get490av($One["contents"]);
      if ( $Title == "" )  $Title = "Nr." . $Counter;

      $Sort  = explode(".", $this->Get490v($One["contents"]));
      $Sort  = $Sort[0];
  
      $Journals[$One["id"]] = array
      (
        "format"    => $One["format"],
        "cover"     => $One["cover"],
        "type"      => "ppn",
        "link"      => $One["id"],
        "label1"    => $Title,
        "label2"    => $this->GetPublisherYear($One["contents"]),
        "sort"      => $Sort
      );
    }
    if ( count($Journals) )
    {
      uasort($Journals, function ($a, $b) { return $a['sort'] <=> $b['sort']; });
    }
    */
  
    // Artikel
    $PPNLink  = $this->CI->internal_search("ppnlink",$PPN, "Article");
    // $PPNLink  = $this->CI->internal_search("ppnlink",$PPN);
    $PPNStg   = json_encode(array_keys($PPNLink["results"]));
    $Articles = array();
    $Counter  = 0;
    foreach ( $PPNLink["results"] as $One )
    {
      $Pretty = $T->SetContents("preview");
  
      $Articles[$One["id"]] = array
      (
        "format"    => $One["format"],
        "cover"     => $One["cover"],
        "type"      => "ppn",
        "link"      => $One["id"],
        "label1"    => $this->Get245ab($One["contents"]),
        "label2"    => $this->Get952j($One["contents"])
      );
    }

    return (array("articles" => $Articles, "journals" => $Journals ));
  } 

  public function TrimTextNew($Text, $Length)
  {
    if (mb_strlen($Text) <= $Length) return $Text;

    return mb_substr($Text, 0, mb_strrpos(mb_substr($Text, 0, $Length), ' ')) . ' ...';
  }

  public function ShowTags($Text)
  {
    $Text = str_replace('%lt%', '&lt;', $Text);
    $Text = str_replace('%gt%', '&gt;', $Text);
    return $Text;
  }

  public function OutputButtons($Exemplars, $ButtonSize, $LineLength,$LinkResolver=true)
  {
    $Output = "";
    $Case   = ( strtolower(MODE) == "production" ) ? false : true;

    // Set javascript variable
    $LinksResolved = array();
    if ( $LinkResolver )
    {
      if ( ($LinksStored=$this->CI->internal_linkresolver($this->PPN)) != "" )
      {
        // $this->CI->printArray2Screen($LinksStored);
        if ( isset($LinksStored["links"]) )
        {
          if ( ( !is_array($LinksStored["links"]) && $LinksStored["links"] != "[]" && $LinksStored["links"] != "" )
            || ( is_array($LinksStored["links"]) && count($LinksStored["links"])) )
          {
            $LinksResolved = (array) json_decode($LinksStored["links"],true);
          }
        }
        $Output .= "<script>linkresolver=true;linkresolverclass='" . $ButtonSize . "';</script>";
      }
    }
    else
    {
      $Output .= "<script>linkresolver=false;</script>";
    }
    
    // Create Buttons
    $FirstArea = true;
    foreach ($Exemplars as $Area)
    {
      if ( isset($Area["data"]) && count($Area["data"]) )
      {
        // Space above
        if ( !$FirstArea ) $Output .= "<div class='space_buttons'></div>";
    
        // Area Label
        $Label = (isset($Area["label"]) && trim($Area["label"]) != "") ? $Area["label"] : "";
        $Output .= "<div>" . $Label . "</div>";
    
        // Area remarks before buttons
        if ( isset($Area["rembef"]) && count($Area["rembef"]) )
        {
          foreach ( $Area["rembef"] as $Key => $Val )
          {
            if ( count($Val) )
            {
              if ( $Key ) $Output .= "<div>" . $Key . "</div>";
              $Output .= "<ul><li><small>" . implode("</li><li>", $Val) . "</small></li></ul>";
            }
          }
        }
    
        // Area Buttons
        $Output .= "<div class='container-fluid'><div class='row'>";
        foreach ( $Area["data"] as $EPN => $Exemplar )
        {
          // Sort messages (Not unique)
          asort($Exemplar, SORT_REGULAR);
          $Exams = $Exemplar;
          $ExamCase = ($Case && isset($Exams["case"])) ? " <small>" . $Exams["case"]  . "</small>" : "";
          unset($Exams["case"]);
          ksort($Exams);
          $_SESSION["exemplar"][$this->PPN][$EPN] = $Exams;
    
          // General properties
          $Icon = "";  
          $Title = "";
          if ( isset($Exemplar["label1"]) && trim($Exemplar["label1"]) && strlen($Exemplar["label1"]) > $LineLength )
          {
            $Title = "title='" . $this->ShowTags($Exemplar["label1"]) . "'";
          }
    
          // Link
          if ( isset($Exemplar["link"]) && trim($Exemplar["link"]) != "" )
          {
            $Class  = $ButtonSize . " btn btn-default "
                    . ( ( isset($Exemplar["class"]) && $Exemplar["class"] ) ? $Exemplar["class"] : "btn-exemplar" );

            if ( isset($Exemplar["type"]) && !in_array($Exemplar["type"], array("external","idsearch","ppn","ppnlinksearch","textsearch")) )  continue;
            if ( isset($Exemplar["type"]) && $Exemplar["type"] == "ppn" )
            {
              $Action  = "onclick='$.open_fullview(\"" . $EPN . "\"," . json_encode(array_keys($Area["data"])) . ",\"publications\")'";
            }
            if ( isset($Exemplar["type"]) && $Exemplar["type"] == "external" )
            {
              $Action = "onclick='window.open(\"" . $Exemplar["link"] . "\",\"_blank\")' data-toggle='tooltip' title='" . $Exemplar["link"] . "'";
              $Icon   = " <span class='fa fa-external-link'></span>";
            }
            if ( isset($Exemplar["type"]) && substr($Exemplar["type"],-6) == "search" )
            {
              $Action = "onclick='$.link_search(\"" . substr($Exemplar["type"],0,strpos($Exemplar["type"],"search")) . "\",\"" . $Exemplar["link"] . "\")'";
            }
          }
    
          // Action
          if ( isset($Exemplar["action"]) && trim($Exemplar["action"]) != "" )
          {
            $Class  = $ButtonSize . " btn btn-default "
                    . ( ( isset($Exemplar["class"]) && $Exemplar["class"] ) ? $Exemplar["class"] : "btn-exemplar" );
            $Action = "onclick='$." . $Exemplar["action"] . "(\"" . ((isset($_SESSION["iln"])) ? $_SESSION["iln"] : "") 
                    . "\",\"" . $this->PPN . "\",\"" . $EPN . "\"," . json_encode($Exams,JSON_HEX_TAG) . ")'";
          }
    
          // Blind
          if ( !isset($Exemplar["action"]) && !isset($Exemplar["link"]) )
          {
            $Class  = $ButtonSize . " btn btn-default "
                    . ( ( isset($Exemplar["class"]) && $Exemplar["class"] ) ? $Exemplar["class"] : "empty-exemplar" );
            $Action = "";
          }
    
          //$Output .= "<a role='button' " . $Action . " class='" . $Class . "' " . $Title . " id='related_" . $EPN . "'>";
          $Output .= "<button " . $Action . " class='" . $Class . "' " . $Title . " id='related_" . $EPN . "'>";
          if ( isset($Exemplar["cover"])  && trim($Exemplar["cover"]) != "" )
          {
            $Output .= "<table width='100%'><tr><td data-toggle='tooltip' title='" . $this->CI->database->code2text($Exemplar["format"]) . "' class='publication-icon'>   ";
            $Output .= "<span class='gbvicon'>" . $Exemplar["cover"] . "</span></td><td class='text-left'>";
          }
          if ( isset($Exemplar["label1"]) && trim($Exemplar["label1"]) != "" ) $Output .= $this->ShowTags($this->TrimTextNew($Exemplar["label1"],$LineLength)) . $Icon . $ExamCase;
          if ( isset($Exemplar["label2"]) && trim($Exemplar["label2"]) != "" ) $Output .= "<br />" . $this->ShowTags($this->TrimTextNew($Exemplar["label2"],$LineLength));
          if ( isset($Exemplar["label3"]) && trim($Exemplar["label3"]) != "" ) $Output .= "<br />" . $this->ShowTags($this->TrimTextNew($Exemplar["label3"],$LineLength));
          if ( isset($Exemplar["cover"])  && trim($Exemplar["cover"]) != "" )
          {
            $Output .= "</td></tr></table>";
          }
          // $Output .= "</a>";
          $Output .= "</button>";
        }
    
        if ( count($LinksResolved) && $LinkResolver )
        {
          $Class  = $ButtonSize . " btn btn-default btn-exemplar";
          foreach ( $LinksResolved as $Solver => $Lk )
          {
            $Output .= "<button onclick='$.openLink(\"" . $Lk . "\")' class='". $Class . "'>" . $this->CI->database->code2text("FULLTEXT") . " (" .  $this->CI->   database->code2text( $Solver)  . ")</button>";
          }
        }
        elseif ($LinkResolver)
        {
          // LinkResolver Spaceholder for async js
          $Output .= "<div id='linkresolver_" . $this->dlgid . "'></div>";
        }
    
        $Output .= "</div></div>";
    
        // Area remarks after buttons
        if ( isset($Area["remaft"]) && count($Area["remaft"]) )
        {
          foreach ( $Area["remaft"] as $Key => $Val )
          {
            if ( count($Val) )
            {
              if ( $Key ) $Output .= "<div>" . $Key . "</div>";
              $Output .= "<ul><li><small>" . implode("</li><li>", $Val) . "</small></li></ul>";
            }
          }
        }
      }

      // Finalize Loop
      $FirstArea = false;
    }
    
    // Ensure Linkresolver (async)
    if ( $FirstArea && $LinkResolver )
    {
      $Output .= "<div id='linkresolvercontainer_" . $this->dlgid . "'></div>";
    }
    return ($Output);
  }

  public function getHost($URL)
  {
    if ( $Host = parse_url($URL,PHP_URL_HOST) )
    {
      if ( $Host == "www.bibliothek.uni-regensburg.de" ) return "Elektr.Zeitschriftenbibliothek";
      if ( substr($Host,0,4) == "www.")                  return substr($Host,4);
      return $Host;
    }
  }

  public function searchMARCSubFields($Filter, $Search)
  {
    $Output = array();
    $Search = strtolower(trim($Search));
    foreach ( $Filter as $Field => $Subfields )
    {
      if ( array_key_exists($Field, $this->contents) )
      {
        foreach ($this->contents[$Field] as $Record)
        {
          foreach ( $Record as $Subrecord )
          {
            foreach ( $Subfields as $Sub )
            {
              foreach ( $Subrecord as $Key => $Value )
              {
                if ( (string)$Key == (string)$Sub && $Search = strtolower(trim($Value)) )
                {
                  $Output[$Field][$Sub][] = htmlspecialchars($Value);
                }
              }
            }
          }
        }
      }
    }
    return $Output;
  }

  public function SearchMARCCompleteArray($Contents, $Filter)
  {
    $Output = array();
    foreach ( $Filter as $Field => $Subfields )
    {
      if ( array_key_exists($Field, $Contents) )
      {
        foreach ($Contents[$Field] as $Record)
        {
          $Tmp = array();
          foreach ( $Record as $Subrecord )
          {
            foreach ( $Subfields as $Sub )
            {
              foreach ( $Subrecord as $Key => $Value )
              {
                if ( (string)$Key == (string)$Sub )
                {
                  $Tmp[$Sub][] = htmlspecialchars($Value);
                }
              }
            }
          }
          $Output[] = $Tmp;
        }
      }
    }
    return $Output;
  }

  public function SearchMARCSimpleArray($Contents, $Filter)
  {
    $Output = array();
    foreach ( $Filter as $Field => $Subfields )
    {
      if ( array_key_exists($Field, $Contents) )
      {
        foreach ($Contents[$Field] as $Record)
        {
          foreach ( $Record as $Subrecord )
          {
            foreach ( $Subfields as $Sub )
            {
              foreach ( $Subrecord as $Key => $Value )
              {
                if ( (string)$Key == (string)$Sub )
                {
                  if ( !isset($Output[$Sub]) || !in_array($Value, $Output[$Sub]) )  $Output[$Sub][] = htmlspecialchars($Value);
                }
              }
            }
          }
        }
      }
    }
    return $Output;
  }

  public function SearchMARCExemplar($Contents, $ExpID)
  {
    if ( array_key_exists("980", $Contents) )
    {
      foreach ($Contents["980"] as $Record)
      {
        $Output = array();
        foreach ( $Record as $Subrecord )
        {
          foreach ( $Subrecord as $Key => $Value )
          {
            if ( !isset($Output[$Key]) || !in_array($Value, $Output[$Key]) )  $Output[$Key][] = htmlspecialchars($Value);
          }
        }
        if ( isset($Output["b"]) && in_array($ExpID, $Output["b"]) ) return $Output;
      }
    }
    return array();
  }

  public function Get856URLs($Contents)
  {
    $Array = array();
    if ( array_key_exists("856", $Contents) )
    {
      foreach ($Contents["856"] as $Record)
      {
        $One = array();
        foreach ( $Record as $Subrecord )
        {
          foreach ( $Subrecord as $Key => $Value )
          {
            if ( !isset($One[$Key]) )  $One[$Key] = htmlspecialchars(trim($Value));
          }
        }
        // Only URLs Indikator 4
        if ( isset($One["I1"]) && $One["I1"] == "4" && isset($One["u"]) && $One["u"] != "" )
        {
          $One["oa"] = ( isset($One["z"]) && $One["z"] != "" && ( stripos($One["z"], "lf") !== false 
                                                               || stripos($One["z"], "oa") !== false 
                                                               || stripos($One["z"], "oalizenz") !== false 
                                                               || stripos($One["z"], "openaccess") !== false
                                                               || stripos($One["z"], "kostenfrei") !== false ) ) ? true : false;
          $Array[] = $One;
        }
      }
    }
    return $Array;
  }

}