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

  private function convertFormat($Str)
  {
    return strtolower(preg_replace('/\s+/', '', $Str));
  }

  private function SetMarcFormat()
  {
    // Get Leader
    $this->leader = $this->marc->getLeader();

    switch ($this->format)
    {
      case "article":            { $this->cover = ($this->online==0) ? "A": "L"; break; }
      case "journal":            { $this->cover = ($this->online==0) ? "F": "M"; break; }
      case "book":               { $this->cover = ($this->online==0) ? "B": "N"; break; }
      case "datamedia":          { $this->cover = "D"; break; }
      case "game":               { $this->cover = "O"; break; }
      case "manuscript":         { $this->cover = "G"; break; }
      case "map":                { $this->cover = "J"; break; }
      case "microform":          { $this->cover = "I"; break; }
      case "mixedmaterials":     { $this->cover = "P"; break; }
      case "monographseries":    { $this->cover = "R"; break; }
      case "motionpicture":      { $this->cover = "E"; break; }
      case "musicalscore":       { $this->cover = "H"; break; }
      case "picture":            { $this->cover = "K"; break; }
      case "projectedmedium":    { $this->cover = "E"; break; }
      case "serialvolume":       { $this->cover = "Q"; break; }
      case "soundrecording":     { $this->cover = "C"; break; }
      case "unknown":            { $this->cover = "O"; break; }
    }
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

    // Electronic InterlibraryLoan
    $EILILNs = ( isset($_SESSION["config_general"]["interlibraryloan"]["onlineilns"]) 
            && $_SESSION["config_general"]["interlibraryloan"]["onlineilns"] != "" ) ? explode(",", $_SESSION["config_general"]["interlibraryloan"]["onlineilns"]) : array();

    $this->contents      = array();
    $this->elecinterloan = false;
    $Interloan           = false;
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
          if ( $tag == "912" && count($ILNs) && isset($Sub[0]["a"]) )
          {
            if ( in_array(substr($Sub[0]["a"],8,3), array_values($ILNs)) 
                 || substr($Sub[0]["a"],0,8) != "GBV_ILN_" && !in_array($Sub[0]["a"], array("SYSFLAG_1", "SYSFLAG_A")) )
            {
              if ( empty($this->contents[$tag]) || !in_array($Sub, $this->contents[$tag]) ) $this->contents[$tag][] = $Sub;
            }
            elseif ( in_array(substr($Sub[0]["a"],8,3), array_values($EILILNs)) && !$Interloan )
            {
              $Interloan = true;
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

    if ( $Interloan )
    {
      if ( substr($this->leader,7,1) == "s" && isset($this->contents["007"]) && substr($this->contents["007"],0,2) == "cr" )   $this->elecinterloan = true;
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
      $this->collection         = isset($one["collection"])                                                 ? $one["collection"]                                 : "";
      $this->collection_details = isset($one["collection_details"])                                         ? $one["collection_details"]                         : "";
      $this->format             = isset($one["format_phy_str_mv"][0])                                       ? $this->convertFormat($one["format_phy_str_mv"][0]) : "";
      $this->online             = (isset($one["remote_bool"]) && strtolower($one["remote_bool"]) == "true") ? 1                                                  : 0;

      // Load MARC library and pass params
      $this->marc = $this->CI->pearloader->loadmarc('File','MARCXML', $one["fullrecord_marcxml"])->next();
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
        "cover"              => $this->cover,
        "isbn"               => $this->isbn,
        "online"             => $this->online,
        "collection"         => $this->collection,
        "collection_details" => $this->collection_details,
        "catalogues"         => $this->catalogues,
        "contents"           => $this->contents,
        "proofofpossession"  => $this->proofofpossession,
        "elecinterloan"      => $this->elecinterloan
      );

      $results_reduced[$one["id"]]	= $reduced + $this->SetContents("preview");
    }
    $container["results"] = $results_reduced;

    // $this->CI->printArray2File($results_reduced);

    return ($container);
  }

}
