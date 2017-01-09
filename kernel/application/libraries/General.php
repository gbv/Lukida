<?php

class General
{
  protected $catalogues = array();
  protected $collgsize  = "";
  protected $contents   = array();
  protected $cover      = "";
  protected $exemplar   = array();
  protected $format     = "";
  protected $isbn       = "";
  protected $leader     = "";
  protected $marc       = "";
  protected $medium     = array();
  protected $online     = "";
  protected $parents    = array();
  protected $PPN        = "";
  protected $ppnlink    = "";
  protected $pretty     = array();
  protected $words      = "";
  protected $proofofpossession = array();

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
        $Output .= $Value;
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
  
      $pretty["titlesecond"]      = $this->PrettyFields(array("245" => array("c" => " : "),
                                                              "260" => array("c" => " (~)")));
  
      $pretty["author"]           = $this->GetAuthors();
  
      $pretty["pv_publisher"]     = $this->PrettyFields(array("250" => array("a" => "~."),
                                                              "260" => array("a" => " - ",
                                                                             "b" => " : ",
                                                                             "c" => ", ")));
  
      $pretty["pv_publishershort"]= $this->PrettyFields(array("250" => array("a" => "~."),
                                                              "260" => array("c" => ", ")));
  
      $pretty["pv_pubarticle"]    = $this->PrettyFields(array("773" => array("i" => " ",
                                                                             "t" => " ",
                                                                             "d" => " ",
                                                                             "g" => " ",
                                                                             "q" => ". Band: ")));

      $pretty["part"]             = $this->PrettyFields(array("245" => array("n" => " | ",
                                                                             "p" => " : ")));

      $pretty["serial"]           = $this->GetArray(array("490" => array("a","v")));
    
      $pretty["physicaldescription"] = $this->PrettyFields(array("300" => array("a" => ". ",
                                                                                "b" => ", ",
                                                                                "c" => ", ",
                                                                                "e" => ", ")));
    }

    if ( $type == "fullview" )
    {
      $pretty["publisher"]           = $this->GetArray(array("260" => array("a","b","c")));
                                     
      $pretty["publisherarticle"]    = $this->GetArray(array("773" => array("i","t","d","g","q","w")));

      $pretty["uniformtitle"]        = $this->PrettyFields(array("240" => array("a" => " | ")));
      
      $pretty["associates"]          = $this->GetAssociates();

      $pretty["summary"]             = $this->PrettyFields(array("520" => array("a" => " | ")));
      
      $pretty["citation"]            = $this->PrettyFields(array("510" => array("a" => " | ")));

      $pretty["computerfile"]        = $this->PrettyFields(array("256" => array("a" => " | ")));

      $pretty["systemdetails"]       = $this->PrettyFields(array("538" => array("a" => " | ")));
  
      $pretty["edition"]             = $this->PrettyFields(array("250" => array("a" => " | ")));
      
      $pretty["reproduction"]        = $this->GetArray(array("533" => array("a","b","c","d","e","f","n")));
    
      $pretty["corporation"]         = $this->PrettyFields(array("110" => array("a" => " ",
                                                                                "b" => " "),
                                                                 "111" => array("a" => " ",
                                                                                "b" => " "),
                                                                 "710" => array("a" => " ",
                                                                                "b" => " "),
                                                                 "711" => array("a" => " ",
                                                                                "b" => " ")));
  
      $pretty["notes"]               = $this->PrettyFields(array("500" => array("a" => " | ")));

      $pretty["languagenotes"]       = $this->PrettyFields(array("546" => array("a" => " | ")));
      
      $pretty["dissertation"]        = $this->PrettyFields(array("502" => array("a" => " | ")));
      
      
      $pretty["language"]            = $this->GetSimpleArray(array("041" => array("a"),
                                                                   "040" => array("b")));


      $pretty["classification"]      = $this->PrettyFields(array("084" => array("a" => " - ",
                                                                                "9" => " - ")));
  
      $pretty["subject"]             = $this->GetSimpleArray(array("650" => array("a","z","v"),
                                                                   "653" => array("a"),
                                                                   "689" => array("a")));
                                                                   
      $pretty["in830"]               = $this->GetArray(array("830" => array("a","b","p","v","w")));

      $pretty["in800"]               = $this->GetArray(array("800" => array("a","t","v","w")));

      $pretty["additionalinfo"]      = $this->GetArray(array("856" => array("u","3")));
    
      $pretty["class"]               = $this->GetSimpleArray(array("983" => array("a")));

      $pretty["isbn"]                = $this->PrettyFields(array("020" => array("9" => " | ", "a" => " | "),
                                                                 "773" => array("z" => " | ")));

      $pretty["issn"]                = $this->PrettyFields(array("022" => array("a" => " | "),
                                                                 "773" => array("x" => " | ")));
    }

    if ( $type == "export" )
    {
      $pretty["zdbid"] = $this->PrettyFields(array("016" => array("a" => " | ")));

      $pretty["details"] = $this->GetArray(array("952" => array("a","e","d","h","j")));
    }
    return $pretty;
  }

  protected function PrettyFields($Filter)
  {
    $Output = "";
    $Tmp = array();
    foreach ( $Filter as $Field => $Subfields )
    {
      if ( array_key_exists($Field, $this->contents) )
      {
        foreach ($this->contents[$Field] as $Record)
        {
          foreach ( $Record as $Subrecord )
          {
            foreach ( $Subfields as $Sub => $Separator )
            {
              foreach ( $Subrecord as $Key => $Value )
              {
                //$this->CI->appendFile("k.txt", $Key . " ? "  . $Sub);
                if ( (string)$Key == (string)$Sub )
                {
                  //$this->CI->appendFile("k.txt", "Yes!");
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
    // Add 100a author(s)
    $Authors = array() + $this->GetSimpleArray(array("100" => array("a")));
  
    // Get 700a people
    $People = $this->GetArray(array("700" => array("a","4")));
    foreach ( $People as $One )
    {
      // Add 700a author(s)
      if ( isset($One["4"]) && $One["4"] == "aut" && isset($One["a"]) && $One["a"] != "" )  $Authors[]  = htmlspecialchars($One["a"]);
    }
    return $Authors;
  }

  protected function GetAssociates()
  {
    $Associates = array();
  
    // Get 700a people
    $People = $this->GetArray(array("700" => array("a","4","e")));
    foreach ( $People as $One )
    {
      // Skip 700a author(s)
      if ( ( isset($One["4"]) && $One["4"] == "aut" ) || ( isset($One["e"]) && strtolower($One["e"]) == "author" ) )  continue;
      $Associates[]  = $One;
    }
    return $Associates;
  }

  protected function GetArray($Filter)
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
    if ( in_array($Typ,array("id","author","class","series","publisher","subject","year")) )
    {
      // Internal links
      return "<a href='javascript:$.link_search(\"" . $Typ . "\",\"" . $Value . "\")'>" . (($Text=="") ? $Value : $Text). " <span class='fa fa-link'></span></a>";
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
}
