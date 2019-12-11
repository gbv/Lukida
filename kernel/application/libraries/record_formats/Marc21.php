<?php

class Marc21 extends General
{
  protected $CI;

  public function __construct()
  {
    // Assign the CodeIgniter super-object
    $this->CI =& get_instance();

    // Load pear loader class
    $this->CI->load->library('Pearloader');
  }

  // ********************************************
  // ************** MARC-Functions **************
  // ********************************************

  private function prepare($result)
  {
    $result  = str_replace("#29;","", $result);
    $result  = str_replace("#30;",chr(30), $result);
    $result  = str_replace("#31;",chr(31), $result);
    return ($result);
  }

  private function SetMarcFormat()
  {
    // Get Leader
    $Leader = $this->marc->getLeader();

    if ( in_array("Gutenberg", $this->collection) )
    {
      $this->leader  = $Leader;
      $this->format  = "ebook";
      $this->cover   = "N";
      $this->ppnlink = 0;
      $this->online  = 1;
      return;
    }

    $Pos6     = substr($Leader,6,1);
    $Pos7     = substr($Leader,7,1);
    $Pos19    = substr($Leader,19,1);
    $F007     = $this->marc->getFields("007");
    $F007Max  = count($F007)-1;
    $F007     = ($F007 && $F007[$F007Max]) ? $F007[$F007Max]->getData() : "";

    $F007_0   = substr($F007,0,1);
    $F007_1   = substr($F007,1,1);
    $F008     = $this->marc->getFields("008");
    $F008Max  = count($F008)-1;
    $F008_21  = ($F008 && $F008[$F008Max]) ? substr($F008[$F008Max],29,1) : "";
    $F951a    = "";
    if ( $Tmp = ( $this->marc->getField("951",true) ) )
    {
      if ( $Tmp = $Tmp->getSubfield('a') )
      {
        $F951a = $Tmp->getData();
      }
    }

    // Pre-Block
    if ( $F007_0 == "v" )                                                            { $Cover = "E"; $Online = 0; $PPNLink = 0; $Name = "motionpicture"; }
    elseif ( $Pos7 != "s" && $F007_0 == "h" )                                        { $Cover = "I"; $Online = 0; $PPNLink = 0; $Name = "microform"; }

    // Block A
    elseif ( $Pos6 == "a" && in_array($Pos7, array("m","i")) && $F951a == "JV" )     { $Cover = "Q"; $Online = 0; $PPNLink = 1; $Name = "serialvolume"; }
    elseif ( $Pos6 == "a" && in_array($Pos7, array("m","i")) && $F007_0 == "c" )     { $Cover = "N"; $Online = 1; $PPNLink = 0; $Name = "ebook"; } 
    elseif ( $Pos6 == "a" && in_array($Pos7, array("m","i")) )                       { $Cover = "B"; $Online = 0; $PPNLink = 0; $Name = "book"; }
    elseif ( $Pos6 == "a" && $Pos7 == "d" )                                          { $Cover = "Q"; $Online = 0; $PPNLink = 0; $Name = "serialvolume"; }
    elseif ( $Pos6 == "a" && in_array($Pos7, array("s","i")) && $F007_0 == "c" )     { $Cover = "M"; $Online = 1; $PPNLink = 0; $Name = "ejournal"; }
    elseif ( $Pos6 == "a" && in_array($Pos7, array("s","i")) 
                          && in_array($F008_21,array("p","n")) )                     { $Cover = "F"; $Online = 0; $PPNLink = 0; $Name = "journal"; }
    elseif ( $Pos6 == "a" && in_array($Pos7, array("s","i")) && $F008_21 == "m" )    { $Cover = "R"; $Online = 0; $PPNLink = 0; $Name = "monographseries"; }
    elseif ( $Pos6 == "a" && in_array($Pos7, array("s","i")) )                       { $Cover = "Q"; $Online = 0; $PPNLink = 0; $Name = "serialvolume"; }
    elseif ( $Pos6 == "a" && in_array($Pos7, array("a","b")) && $F007_0 == "c" )     { $Cover = "L"; $Online = 1; $PPNLink = 0; $Name = "electronicarticle"; }
    elseif ( $Pos6 == "a" && in_array($Pos7, array("a","b")) )                       { $Cover = "A"; $Online = 0; $PPNLink = 0; $Name = "article"; }

    // Block M
    elseif ( $Pos6 == "m" && $Pos7 == "m" && $F007_1 == "r" )                        { $Cover = "N"; $Online = 1; $PPNLink = 0; $Name = "ebook"; }
    elseif ( $Pos6 == "m" && $Pos7 == "m" && $F007_0 == "c" )                        { $Cover = "D"; $Online = 0; $PPNLink = 0; $Name = "datamedia"; }
    elseif ( $Pos6 == "m" && $Pos7 == "m" )                                          { $Cover = "D"; $Online = 1; $PPNLink = 0; $Name = "electronicressource"; }
    elseif ( $Pos6 == "m" && $Pos7 == "b" && $F007_1 == "r" )                        { $Cover = "L"; $Online = 1; $PPNLink = 0; $Name = "electronicarticle"; }
    elseif ( $Pos6 == "m" && $Pos7 == "b" && $F007_0 == "c" )                        { $Cover = "D"; $Online = 0; $PPNLink = 0; $Name = "datamedia"; }
    elseif ( $Pos6 == "m" && $Pos7 == "b" )                                          { $Cover = "D"; $Online = 1; $PPNLink = 0; $Name = "electronicressource"; }
    elseif ( $Pos6 == "m" && in_array($Pos7, array("s","i")) )                       { $Cover = "M"; $Online = 1; $PPNLink = 0; $Name = "ejournal"; }
    elseif ( $Pos6 == "m" && in_array($Pos7, array("a","b")) )                       { $Cover = "L"; $Online = 1; $PPNLink = 0; $Name = "electronicarticle"; }

    // Block E
    elseif ( $Pos6 == "e" )                                                          { $Cover = "J"; $Online = 0; $PPNLink = 0; $Name = "map"; }

    // Block D, F, T
    elseif ( in_array($Pos6, array("d","f","t" )) )                                  { $Cover = "G"; $Online = 0; $PPNLink = 0; $Name = "manuscript"; }

    // Block I, J
    elseif ( in_array($Pos6, array("i","j")) )                                       { $Cover = "C"; $Online = 0; $PPNLink = 0; $Name = "soundrecording"; }

    // Block G
    elseif ( $Pos6 == "g" )                                                          { $Cover = "E"; $Online = 0; $PPNLink = 0; $Name = "projectedmedium"; }

    // Block R
    elseif ( $Pos6 == "r" && $Pos7 == "a" )                                          { $Cover = "K"; $Online = 0; $PPNLink = 0; $Name = "picture"; }
    elseif ( $Pos6 == "r" )                                                          { $Cover = "O"; $Online = 0; $PPNLink = 0; $Name = "game"; }

    // Block C
    elseif ( $Pos6 == "c" )                                                          { $Cover = "H"; $Online = 0; $PPNLink = 0; $Name = "musicalscore"; }

    // Block P
    elseif ( $Pos6 == "p" )                                                          { $Cover = "P"; $Online = 0; $PPNLink = 1; $Name = "mixedmaterials"; }

    // Post-Block
    elseif ( $F007_0 == "c" )                                                        { $Cover = "D"; $Online = 1; $PPNLink = 0; $Name = "electronicressource"; }
    else                                                                             { $Cover = "O"; $Online = 0; $PPNLink = 0; $Name = "unknown"; }

    $this->format  = $Name;
    $this->cover   = $Cover;
    $this->ppnlink = $PPNLink;
    $this->online  = $Online;
    $this->leader  = $Leader;
  }

  private function SetMarcContents()
  {

    $ILNs = array();
    if ( isset($_SESSION["iln"]) && isset($_SESSION["iln"]) != "" )         $ILNs[] = $_SESSION["iln"];
    if ( isset($_SESSION["config_general"]["general"]["ilnsecond"]) 
            && $_SESSION["config_general"]["general"]["ilnsecond"] != "" )  $ILNs[] = $_SESSION["config_general"]["general"]["ilnsecond"];
    if ( isset($_SESSION["config_general"]["general"]["ilnmore"]) 
            && $_SESSION["config_general"]["general"]["ilnmore"] != "" )    $ILNs = array_unique(array_merge($ILNs,explode(",",$_SESSION["config_general"]["general"]["ilnmore"])));      
    //file_put_contents('ALEX_' . microtime() . '.txt', print_r($ILNs, true));

    $this->contents = array();
    foreach ($this->marc->getFields() as $tag => $data)
    {
      if ( $data->isControlField() )
      {
        if ( ! $data->isEmpty() )
        {
          $this->contents[$tag] = $data->getData();
        }
      }
      if ( $data->isDataField() )
      {
        // Init 
        $Sub = array();

        // Indicators
        $Tmp = $data->getIndicator(1);
        if ( trim($Tmp) != "") $Sub[] = array("I1" => trim($Tmp));
        $Tmp = $data->getIndicator(2);
        if ( trim($Tmp) != "") $Sub[] = array("I2" => trim($Tmp));

        // Get all subfields of record
        foreach ($data->getSubfields() as $code => $value)
        {
          if ( trim($code) == "0" )  $code=" 0";
          $Sub[] = array($code => $value->getData());
        }

        // Separate data in two arrays
        if ( $tag != "912" && $tag < "980" )
        {
          $this->contents[$tag][] = $Sub;
        }
        else
        {
          // 912-area keep only data for configured ilns
          if ( $tag == "912" && count($ILNs) )
          {
            if ( $data->getSubField("a") )
            {
              $Tmp = (string) trim($data->getSubField("a")->getData());

              if ( in_array(substr($Tmp,8,3), array_values($ILNs)) || 
                ( substr($Tmp,0,8) != "GBV_ILN_" && !in_array($Tmp, array("SYSFLAG_1", "SYSFLAG_A"))) ) 
              {
                if ( empty($this->contents[$tag]) || !in_array($Sub, $this->contents[$tag]) ) $this->contents[$tag][] = $Sub;
                continue;
              }
            }
            if ( isset($_SESSION["internal"]["marcfull"]) && $_SESSION["internal"]["marcfull"] == "1" )
            {
              $this->contents["{". $tag."}"][] = $Sub;
            }
          }

          // 980-area keep only data for configured iln
          if ( $tag >= "980" && count($ILNs) )
          {
            if ( $data->getSubField("2") )
            {
              $Tmp = $data->getSubField("2")->getData();
              if ( in_array($Tmp, $ILNs) )
              {
                $this->contents[$tag][] = $Sub;
                continue;
              }
            }
            if ( isset($_SESSION["internal"]["marcfull"]) && $_SESSION["internal"]["marcfull"] == "1" )
            {
              $this->contents["{". $tag."}"][] = $Sub;
            }
          }
        }
      }
    }
  }

  private function SetProofOfPossession()
  {
    if ( ! isset($_SESSION["config_general"]["general"]["mode"]) || $_SESSION["config_general"]["general"]["mode"] != "BASED_ON_IP" ) return;

    $ilns = array();
    foreach ($this->marc->getFields("980") as $tag => $data)
    {
      if ( $data->isDataField() )
      {
        $iln  = "";
        $eln  = "";
        $ins  = "";
        $getx = "";
        foreach ($data->getSubfields() as $code => $value)
        {
          // ILN
          if ( $code == "2" ) $iln = $value->getData();
          // ELN/ILN
          if ( $code == "x" ) $getx = $value->getData();
        }
        if ( $getx != "" )
        {
          $Tmp = explode("/",$getx);
          if ( count($Tmp) == 1 )
          {
            $eln = str_pad ($Tmp[0],4,"0", STR_PAD_LEFT);
          }
          elseif ( count($Tmp) == 2) 
          {
            $eln = str_pad ($Tmp[0],4,"0", STR_PAD_LEFT);
            $ins = str_pad ($Tmp[1],4,"0", STR_PAD_LEFT);
          }
        }

        $ilns[] = rtrim($iln."/".$eln."/".$ins,"/");
      }
    }
    $this->proofofpossession = array_unique($ilns);
  }

  private function SetMarcParents()
  {
    $Parents = array();
    if ( isset($this->contents["773"]) )
    {
      foreach ( $this->contents["773"] as $Record )
      {
        foreach ( $Record as $Subrecord )
        {
          foreach ( $Subrecord as $Key => $Value )
          {
            if ( $Key == "w" )
            {
              if ( in_array(substr($Value,0,8), array("(DE-601)","(DE-627)") ) ) 
              {
                if ( !in_array(trim(substr($Value,8)), $Parents) )
                {
                  $Parents[]  = trim(substr($Value,8));
                }
              }
            }
          }
        }
      }
    }
    if ( isset($this->contents["800"]) )
    {
      foreach ( $this->contents["800"] as $Record )
      {
        foreach ( $Record as $Subrecord )
        {
          foreach ( $Subrecord as $Key => $Value )
          {
            if ( $Key == "w" )
            {
              if ( in_array(substr($Value,0,8), array("(DE-601)","(DE-627)") ) ) 
              {
                if ( !in_array(trim(substr($Value,8)), $Parents) )
                {
                  $Parents[]  = trim(substr($Value,8));
                }
              }
            }
          }
        }
      }
    }
    if ( isset($this->contents["830"]) )
    {
      foreach ( $this->contents["830"] as $Record )
      {
        foreach ( $Record as $Subrecord )
        {
          foreach ( $Subrecord as $Key => $Value )
          {
            if ( $Key == "w" )
            {
              if ( in_array(substr($Value,0,8), array("(DE-601)","(DE-627)") ) ) 
              {
                if ( !in_array(trim(substr($Value,8)), $Parents) )
                {
                  $Parents[]  = trim(substr($Value,8));
                }
              }
            }
          }
        }
      }
    }    
    $this->parents = $Parents;
  }

  private function SetMarcISBN()
  {
    if ( isset($this->contents["020"]) )
    {
      foreach ( $this->contents["020"] as $Record )
      {
        foreach ( $Record as $Subrecord )
        {
          foreach ( $Subrecord as $Key => $Value )
          {
            if ( $Key == "a" )
            {
              $Tmp = filter_var($Value, FILTER_SANITIZE_NUMBER_INT);
              if ( strlen($Tmp) == 13 )
              {
                $this->isbn = $Tmp;
                return;
              }
            }
          }
        }
      }
    }
    $this->isbn = "";
  }

  private function SetMarcCatalogues()
  {
    $Cats = array();

    if ( isset($this->contents[912]) )
    {
      foreach ( $this->contents[912] as $Record )
      {
        foreach ( $Record as $Subrecord )
        {
          foreach ( $Subrecord as $Key => $Value )
          {
            if ( $Key == "a" )
            {
              $Cats[] = $Value;
            }
          }
        }
      }
    }    
    $this->catalogues = $Cats;
  }


  // ********************************************
  // ************** Start-Function **************
  // ********************************************

  public function convert($container)
  {
    // Loop container, create new reduced array
    $results_reduced	= array();
    foreach ( $container["results"] as $one )
    {
      $this->collection         = isset($one["collection"])         ? $one["collection"]         : "";
      $this->collection_details = isset($one["collection_details"]) ? $one["collection_details"] : "";

      // Load MARC library and pass params
      $this->marc = $this->CI->pearloader->loadmarc('File','MARC', $this->prepare($one["fullrecord"]))->next();
      $this->SetMarcFormat();
      $this->SetMarcContents();
      $this->SetMarcParents();
      $this->SetMarcISBN();
      $this->SetMarcCatalogues();
      $this->SetProofOfPossession();

      // Prepare reduced array
      $reduced = array
      (
        "id" 		             => $one["id"],
        "parents"            => $this->parents,
        "leader"             => $this->leader,
        "format"             => $this->format,
        "ppnlink"            => $this->ppnlink,
        "cover"              => $this->cover,
        "isbn"               => $this->isbn,
        "online"             => $this->online,
        "collection"         => $this->collection,
        "collection_details" => $this->collection_details,
        "catalogues"         => $this->catalogues,
        "contents"           => $this->contents,
        "proofofpossession"  => $this->proofofpossession
      );

      $results_reduced[$one["id"]]	= $reduced + $this->SetContents("preview");
    }
    $container["results"] = $results_reduced;

    // $this->CI->printArray2File($results_reduced);

    return ($container);
  }

}
