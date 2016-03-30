<?php

class Bootstrap
{
  protected $CI;
  private $PPN = "";
  private $cover      = "";
  private $isbn       = "";
  private $ppnlink    = "";
  private $leader     = "";
  private $format     = "";
  private $online     = "";
  private $contents   = array();
  private $catalogues = array();
  private $medium     = array();
  private $words      = "";
  private $pretty     = array();
  private $exemplar   = array();

  public function __construct()
  {
    // Assign the CodeIgniter super-object
    $this->CI =& get_instance();
  }

  // ********************************************
  // ************* Check-Functions **************
  // ********************************************

  private function SessionExits()
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

  private function ParamExits($Name, $Parameter, $Key1="", $Key2="",$Key3="")
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

  private function FileExits($File)
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

  // ********************************************
  // ************** Tool-Functions **************
  // ********************************************

  private function header()
  {
    return "<!DOCTYPE html><html lang='de'><head>
    <meta charset='utf-8'></head><body>";
  }

  private function footer()
  {
    return "</body></html>";
  }

  private function printarray($level,$Array)
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

  private function format_uri( $string, $separator = ' ' )
  {
    return $title = str_replace( array( '\'','"',',',';','<','>',' ','!','§','$','%','&','/','(',')','{','}','[',']'), ' ', $string);
  }

  private function Trim_Text($input, $length, $ellipses = true, $strip_html = true)
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

  private function Mark_Text($Text)
  {
    // $this->CI->appendFile("Test.txt", "---");
    // $this->CI->appendFile("Test.txt", $_SESSION['search']);

    // Replace multiple spaces, tabs, linebreaks by one space
    $Search = preg_replace('!\s+!', ' ', $_SESSION['data']['search']);
    $Words	= array_unique(explode(" ", $Search));

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
    $Words	= array_unique($Words);


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
      $Text = preg_replace("/($Word)/i", '<span class="search">\1</span>', $Text);

    }

    // Phase two: Do replacements
    $Text	= str_ireplace("<e§m>","<span class='search'>", $Text);
    $Text	= str_ireplace("</e§m>","</span>", $Text);
    return $Text;
  }

  private function AddData($Location, $Data)
  {
    if (isset($_SESSION["config_discover"]["fullview"]["fullviewlogging"]) && $_SESSION["config_discover"]["fullview"]["fullviewlogging"] == "1" && $Data == "" )
    {
      file_put_contents("Empty_Fields.txt", $this->PPN . ", " . $Location . "\n",FILE_APPEND);
    }
    return $Data;
  }

  private function LoadTabElements($Tab)
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

  private function LoadTab($Tab)
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

  private function LoadElement($Element)
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


  // ********************************************
  // ************* Format-Functions *************
  // ********************************************

  private function SetContents($type)
  {
    $temp = "";
    // Format contents for possible output
    // Merge multiple records and subfields into pretty strings
    $pretty = array();

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
                                                                         
    if ( $type != "preview" )
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

      $temp = $this->PrettyFields(array("020" => array("9" => " | ",
                                                       "a" => " | ")));
      if ($temp === "") { $temp = $this->PrettyFields(array("773" => array("z" => " | "))); }
      $pretty["isbn"] = $temp; 
      $temp = "";
 
      $temp = $this->PrettyFields(array("022" => array("a" => " | ")));
      if ($temp == "") { $temp = $this->PrettyFields(array("773" => array("x" => " | "))); }
      $pretty["issn"] = $temp;
      $temp = "";
    
      $pretty["classification"]      = $this->PrettyFields(array("084" => array("a" => " - ",
                                                                                "9" => " - ")));
  
      $pretty["subject"]             = $this->GetSimpleArray(array("650" => array("a","z","v"),
                                                                   "653" => array("a"),
                                                                   "689" => array("a")));
                                                                   
      $pretty["in830"]               = $this->GetArray(array("830" => array("a","b","p","v","w")));

      $pretty["in800"]               = $this->GetArray(array("800" => array("a","t","v","w")));

      //$pretty["additionalinfo"]    = $this->GetArray(array("856" => array("u","3")));

      $temp = $this->PrettyFields(array("952" => array("a" => " | ")));
      if ($temp != "") { $pretty["part"] = $temp; 
                         $temp = "";
      }
      $temp = $this->PrettyFields(array("260" => array("c" => " | ")));
      if ($temp != "") { $pretty["year"] = $temp;
                         $temp = "";
      }
      $temp = $this->PrettyFields(array("952" => array("j" => " | ")));
      if ($temp != "") { $pretty["year"] = $temp; 
                         $temp = "";
      }
      $temp = $this->PrettyFields(array("952" => array("d" => " | ")));
      if ($temp != "") { $pretty["volume"] = $temp; 
                         $temp = "";
      }
      $temp = $this->PrettyFields(array("952" => array("e" => " | ")));
      if ($temp != "") { $pretty["issue"] = $temp; 
                         $temp = "";
      }
      $temp = $this->PrettyFields(array("952" => array("h" => " | ")));
      if ($temp != "") { $pretty["pages"] = $temp; 
                         $temp = "";
      }
      $temp = $this->PrettyFields(array("260" => array("a" => " | ")));
      if ($temp != "") {
        $pretty["place"] = $temp;
        $temp = "";
      }
      $temp = $this->PrettyFields(array("260" => array("b" => " | ")));
      if ($temp != "") {
        $pretty["publisherOnly"] = $temp;
        $temp = "";
      }
      $temp = $this->PrettyFields(array("773" => array("d" => " | ")));
      if ($temp != "") { 
        if ( strpos($temp, " : ") !== false ) {
          $pretty["place"] = strstr($temp, " : ", true);
          $pretty["publisherOnly"] = substr(strstr($temp, " : "), 3);
        }
        else { $pretty["publisher"] = $temp; }
        $temp = "";
      }

    }
    return $pretty;
  }

  private function PrettyFields($Filter)
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

  private function GetAuthors()
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

  private function GetAssociates()
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

  private function GetArray($Filter)
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

  private function GetSimpleArray($Filter)
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
  
  private function SetCover($type = "preview")
  {
    $buchhandel_cover = ( isset($_SESSION["config_system"]["systemcommon"]["covermode"]) && $_SESSION["config_system"]["systemcommon"]["covermode"] == "1" ) ? true : false;
    //"<img class='preview' src='" . $xpfad2 . "' width='50px'  id='pic_" . $this->PPN . "' onerror='$.missing_picture(\"" . $this->PPN . "\",\"" . $xpfad . "\");' />";

    // Internes Cover
    $pfadintern = base_url() . "assets/images/formats/" . $this->cover . ".png";
    
    if ( $this->isbn != "" && $buchhandel_cover )
    {
      // Externes Cover (Versuch)
      $pfadextern = "http://vlb.de/GetBlob.aspx?strIsbn=" . $this->isbn . "&size=S";

      if ( $type == "preview" )
      {
        //return "<img src='" . $pfadextern . "' width='75px' id='pic_" . $this->PPN . "' onerror='$.missing_picture(\"" . $this->PPN . "\",\"" . $pfadintern . "\")' />";
        return "<img class='externalimage img-responsive' src='" . $pfadextern . "' width='75px' />";
      }
      else
      {
        //return "<img class='center-block' src='" . $pfadextern . "' width='75px' id='pic_" . $this->PPN . "' onerror='$.missing_picture(\'" . $this->PPN . "\',\'" . $pfadintern . "\')' />";
        return "<img class='center-block externalimage' src='" . $pfadextern . "' width='75px' />";
      } 
    }
    else
    {
      if ( $type == "preview" )
      {
        return "<img class='img-responsive' src='" . $pfadintern . "' width='75px'>";
      }
      else
      {
        return "<img  class='img-responsive center-block' src='" . $pfadintern . "' width='75px'>";
      } 
    }
  }
  
  private function SetCover2($cover)
  {
    // Internes Cover
    $pfadintern = base_url() . "assets/images/formats/" . $cover . ".png";
    return "<img src='" . $pfadintern . "' width='75px'>";
  }

  public function Link($Typ, $Value, $Text="")
  {
    if ( in_array($Typ,array("id","author","series","publisher","subject","year")) )
    {
      return "<a href='javascript:$.link_search(\"" . $Typ . ":".$Value."\")'>" . (($Text=="") ? $Value : $Text). " <span class='fa fa-link'></span></a>";
    }
    else
    {
      return "<a href='" . $Value . "' target='_blank'>" . $Typ . " <span class='fa fa-external-link'></span></a>";
    }
  }


  // ********************************************
  // ************** Start-Function **************
  // ********************************************

  public function preview($container, $params)
  {
    // Check, if modul is available
    if ( ! file_exists(KERNELFORMATS . "preview/" . $_SESSION["config_discover"]["preview"]["preview"] .'.php'))
    {
      // Whoops, we don't have a page for that!
      show_404();
    }

    // Set Format Part Variables
    $Header	= (isset($_SESSION["config_discover"]["preview"]["previewheader"]) && $_SESSION["config_discover"]["preview"]["previewheader"] == "1" ) ? true : false;
    $Body		= (isset($_SESSION["config_discover"]["preview"]["previewbody"])   && $_SESSION["config_discover"]["preview"]["previewbody"]   == "1" ) ? true : false;
    $Footer	= (isset($_SESSION["config_discover"]["preview"]["previewfooter"]) && $_SESSION["config_discover"]["preview"]["previewfooter"] == "1" ) ? true : false;

    // Variablen initialisieren
    $this->NR = $container["start"];
    $collgsize = $params["collgsize"];
    switch ( $collgsize )
    {
      case "12":
      {
        $columns = "col-xs-12 col-sm-12 col-md-12 col-lg-12";
        break;
      }
      case "6":
      {
        $columns = "col-xs-12 col-sm-6 col-md-6 col-lg-6";
        break;
      }
      case "4":
      {
        $columns = "col-xs-12 col-sm-6 col-md-4 col-lg-4";
        break;
      }
      default:
      case "3":
      {
        $columns = "col-xs-12 col-sm-6 col-md-4 col-lg-3";
        break;
      }
    }

    $Ausgabe = "";
    foreach ( $container["results"] as $Erg )
    {
      $this->NR++;
      $this->PPN = $Erg["id"];
      $this->contents = $Erg["contents"];
      $this->format = $Erg["format"];
      $this->cover = $Erg["cover"];
      $this->isbn = $Erg["isbn"];
      $this->pretty = $this->SetContents("preview");
      $this->words .= " " . $this->pretty["title"] . " " . implode(" ",$this->pretty["author"]);

      //$this->CI->printArray2File($this->contents + $this->pretty);      
      //$this->CI->printArray2File($Erg);      
 
      $Output = "<div id='" . $this->PPN . "' class='medium " . $columns . "'><div class='panel'>";
      $Output .= "<a href='javascript:$.open_fullview(\"" . $this->PPN . "\");'>";
      include(KERNELFORMATS . "preview/" . $_SESSION["config_discover"]["preview"]["preview"] .'.php');
      $Output .= "</a></div></div>";
      $Ausgabe .= $Output;
    }

    // Transport-Container beladen
    $container["results"] = $Ausgabe;
    $container["words"] = $this->words;

    if ( true )
    {
      // Facetten für Anzeige filtern
      $Filter  = array();

      // Online
      $Count = 0;
      while ( isset($container["facets"]["remote_bool"][$Count] ) )
      {
        if ( $container["facets"]["remote_bool"][$Count+1] > 0 )
        {
          $Filter["online"][$container["facets"]["remote_bool"][$Count]] = $container["facets"]["remote_bool"][$Count+1];
        }
        $Count += 2;
      }
      if ( ! isset($Filter["online"]["false"]) )
      {
        $Filter["online"]["false"]  = 0;
      }
      if ( ! isset($Filter["online"]["true"]) )
      {
        $Filter["online"]["true"]  = 0;
      }
      $Filter["online"]["total"] = $Filter["online"]["false"] + $Filter["online"]["true"];

      // Format
      $Count = 0;
      while ( isset($container["facets"]["format_phy_str_mv"][$Count] ) )
      {
        if ( $container["facets"]["format_phy_str_mv"][$Count+1] > 0 )
        {
          $Filter["format"][$container["facets"]["format_phy_str_mv"][$Count]] = $container["facets"]["format_phy_str_mv"][$Count+1];
        }
        $Count += 2;
      }

      // Jahr
      //$Count = 0;
      //while ( isset($container["facets"]["publishDate"][$Count] ) )
      //{
      //  if ( ( $MinJahr == "" || $MinJahr > $container["facets"]["publishDate"][$Count] ) && ( $container["facets"]["publishDate"][$Count+1] > 0 ) )
      //  {
      //    $MinJahr = $container["facets"]["publishDate"][$Count];
      //  }
      //  if ( ( $MaxJahr == "" || $MaxJahr < $container["facets"]["publishDate"][$Count] ) && ( $container["facets"]["publishDate"][$Count+1] > 0 ) )
      //  {
      //    $MaxJahr = $container["facets"]["publishDate"][$Count];
      //  }
      //  $Count += 2;
      //}

      // Facetten für Anzeige vorbereiten
      $container["yearmin"] = isset($container["stats"]["publishDate"]["min"]) ? $container["stats"]["publishDate"]["min"] : "1900";
      $container["yearmax"] = isset($container["stats"]["publishDate"]["max"]) ? $container["stats"]["publishDate"]["max"] : date("Y");
      $container["online"]  = (isset($Filter["online"])) ? $Filter["online"] : "";
      $container["formats"] = (isset($Filter["format"])) ? $Filter["format"] : "";

    }

    // Transport-Container verschicken
    return ( $container );
  }
  
  public function fullview ( $params )
  {
    // Check params
    if ( ! $this->ParamExits("param[ppn]", $params,"ppn") ) return false;
    if ( ! $this->ParamExits("param[dlgid]", $params,"dlgid") ) return false;
    if ( ! $this->FileExits(KERNELFORMATS . "fullview/" . $_SESSION["config_discover"]["fullview"]["fullview"] . ".php") ) return false;

    // Prepare variables for loaded code
    $this->PPN        = $params['ppn'];
    $this->dlgid      = $params['dlgid'];
    $this->medium     = $_SESSION["data"]["results"][$this->PPN];
    $this->contents   = $this->medium["contents"];
    $this->leader     = $this->medium["leader"];
    $this->format     = $this->medium["format"];
    $this->online     = $this->medium["online"];
    $this->ppnlink    = $this->medium["ppnlink"];
    $this->cover      = $this->medium["cover"];
    $this->catalogues = $this->medium["catalogues"];
    $this->isbn       = $this->medium["isbn"];
    $this->pretty     = $this->SetContents("fullview");
    $_SESSION["data"]["pretty"][$this->PPN] = $this->pretty;
    //$this->CI->printArray2File($this->medium + $this->pretty);

    // Start Output
    $Output = $this->header();

    // Load module inside div
    $Output = "<div id='fullview'>";
    include(KERNELFORMATS . "fullview/" . $_SESSION["config_discover"]["fullview"]["fullview"] .'.php');
    $Output .= "</div>";

    // End Output
    $Output .= $this->footer();

    // Return Output
    return ( $Output );
  }

  public function userview ( $params )
  {
    // Check Session & Parameters
    if ( ! $this->ParamExits("param[action]", $params,"action") ) return false;
    if ( ! $this->ParamExits("_SESSION[config_discover][userview][userview]",$_SESSION,"config_discover","userview","userview") ) return false;
    if ( ! $this->FileExits(KERNELFORMATS . "userview/" . $_SESSION["config_discover"]["userview"]["userview"] . ".php") ) return false;

    // Prepare variables for loaded code
    $Action = $params['action'];

    // Start Output
    $Output = $this->header();

    // Load module inside div
    $Output = "<div id='userview'>";
    include(KERNELFORMATS . "userview/" . $_SESSION["config_discover"]["userview"]["userview"] .'.php');
    $Output .= "</div>";

    // End Output
    $Output .= $this->footer();

    // Return Output
    return ( $Output );
  }

  public function mailorderview ( $params )
  {
    // Check Session & Parameters
    if ( ! $this->ParamExits("param[ppn]", $params,"ppn") ) return false;
    if ( ! $this->ParamExits("_SESSION[config_discover][mailorderview][mailorderview]",$_SESSION,"config_discover","mailorderview","mailorderview") ) return false;
    if ( ! $this->FileExits(KERNELFORMATS . "mailorderview/" . $_SESSION["config_discover"]["mailorderview"]["mailorderview"] . ".php") ) return false;

    // Prepare variables for loaded code
    $this->PPN        = $params['ppn'];
    $this->exemplar   = $params['exemplar'];
    $this->medium     = $_SESSION["data"]["results"][$this->PPN];
    $this->contents   = $this->medium["contents"];
    $this->leader     = $this->medium["leader"];
    $this->format     = $this->medium["format"];
    $this->ppnlink    = $this->medium["ppnlink"];
    $this->cover      = $this->medium["cover"];
    $this->catalogues = $this->medium["catalogues"];
    $this->isbn       = $this->medium["isbn"];
    $this->pretty     = $_SESSION["data"]["pretty"][$this->PPN];

    // Start Output
    $Output = $this->header();

    // Load module inside div
    $Output = "<div id='mailorderview'>";
    include(KERNELFORMATS . "mailorderview/" . $_SESSION["config_discover"]["mailorderview"]["mailorderview"] .'.php');
    $Output .= "</div>";

    // End Output
    $Output .= $this->footer();

    // Return Output
    return ( $Output );
  }
}
?>
