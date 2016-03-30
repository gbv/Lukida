<?php

class Marc21
{
  protected $CI;
  private $marc    = "";
  private $leader  = "";
  private $format  = "";
  private $ppnlink = "";
  private $cover   = "";
  private $online  = "";
  private $isbn    = "";
  private $parents    = array();
  private $catalogues = array();
  private $contents   = array();

  public function __construct()
  {
    // Assign the CodeIgniter super-object
    $this->CI =& get_instance();

    // Load pear loader class
    $this->CI->load->library('Pearloader');
  }

  // ********************************************
  // ************** Tool-Functions **************
  // ********************************************


  private function LogEmptyPPNs()
  {
    if (isset($_SESSION["config_discover"]["preview"]["previewlogging"]) && $_SESSION["config_discover"]["preview"]["previewlogging"] == "1" )
    {
      file_put_contents("Empty_PPNs.html", date("d.m.Y H:i:s") . " <a target='_blank' href='" . base_url($this->PPN) . "'>" . $this->PPN . "</a><br />",FILE_APPEND);
    }
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
    $Pos6 = substr($Leader,6,1);
    $Pos7 = substr($Leader,7,1);
    $Pos19 = substr($Leader,19,1);
    $F007 = $this->marc->getFields("007");
    $F007_0 = ($F007 && $F007[0]) ? substr($F007[0]->getData(),0,1) : "";
    $F007_0_2 = ($F007 && $F007[0]) ? substr($F007[0]->getData(),0,2) : "";
    $F008 = $this->marc->getFields("008");
    $F008_21 = ($F008 && $F008[0]) ? substr($F008[0],29,1) : "";

    if     ( $Pos6 == "a" && $Pos7 == "m" && $Pos19 == "a" )                    { $Cover = "14"; $Online = 0; $PPNLink = 1; $Name = "multivolumework"; }
    elseif ( $Pos6 == "a" && $Pos7 == "m" && $F007_0 == "h" )                   { $Cover = "12"; $Online = 0; $PPNLink = 0; $Name = "microform"; }
    elseif ( $Pos6 == "a" && $Pos7 == "m" )                                     { $Cover = "02"; $Online = 0; $PPNLink = 0; $Name = "book"; }
    elseif ( $Pos6 == "a" && $Pos7 == "a" )                                     { $Cover = "01"; $Online = 0; $PPNLink = 0; $Name = "article"; }
    elseif ( $Pos6 == "a" && $Pos7 == "d" )                                     { $Cover = "09"; $Online = 0; $PPNLink = 0; $Name = "journal"; }
    elseif ( $Pos6 == "a" && $Pos7 == "s" && in_array($F008_21,array("p","n"))) { $Cover = "09"; $Online = 0; $PPNLink = 0; $Name = "journal"; }
    elseif ( $Pos6 == "a" && $Pos7 == "s" && $F008_21 == "m" )                  { $Cover = "14"; $Online = 0; $PPNLink = 1; $Name = "series"; }
    elseif ( $Pos6 == "c"                 )                                     { $Cover = "08"; $Online = 0; $PPNLink = 0; $Name = "musicalscore"; }
    elseif ( $Pos6 == "e"                 )                                     { $Cover = "11"; $Online = 0; $PPNLink = 0; $Name = "map"; }
    elseif ( $Pos6 == "g" && $Pos7 == "a" )                                     { $Cover = "13"; $Online = 0; $PPNLink = 0; $Name = "movieadditionalmaterial"; }
    elseif ( $Pos6 == "g"                 )                                     { $Cover = "13"; $Online = 0; $PPNLink = 0; $Name = "movie"; }
    elseif ( $Pos6 == "j" && $Pos7 == "a" )                                     { $Cover = "15"; $Online = 0; $PPNLink = 0; $Name = "audiocarrieradditionalmaterial"; }
    elseif ( $Pos6 == "j"                 )                                     { $Cover = "15"; $Online = 0; $PPNLink = 0; $Name = "audiocarrier"; }
    elseif ( $Pos6 == "m" && $Pos7 == "m" && $F007_0_2 == "cu" )                { $Cover = "03"; $Online = 0; $PPNLink = 0; $Name = "datacarrier"; }
    elseif ( $Pos6 == "m" && $Pos7 == "m" )                                     { $Cover = "04"; $Online = 1; $PPNLink = 0; $Name = "ebook"; }
    elseif ( $Pos6 == "m" && $Pos7 == "a" )                                     { $Cover = "06"; $Online = 1; $PPNLink = 0; $Name = "earticle"; }
    elseif ( $Pos6 == "m" && $Pos7 == "d" && $F007_0_2 == "cu" )                { $Cover = "03"; $Online = 0; $PPNLink = 0; $Name = "datacarrier"; }
    elseif ( $Pos6 == "m" && $Pos7 == "s" )                                     { $Cover = "05"; $Online = 1; $PPNLink = 0; $Name = "ejournal"; }
    elseif ( $Pos6 == "p" && $Pos7 == "m" )                                     { $Cover = "17"; $Online = 0; $PPNLink = 1; $Name = "mixedmaterials"; }
    elseif ( $Pos6 == "r" && $Pos7 == "m" )                                     { $Cover = "07"; $Online = 0; $PPNLink = 0; $Name = "game"; }
    elseif ( $Pos6 == "r" && $Pos7 == "a" )                                     { $Cover = "16"; $Online = 0; $PPNLink = 0; $Name = "picture"; }
    elseif ( $Pos6 == "t"                 )                                     { $Cover = "10"; $Online = 0; $PPNLink = 0; $Name = "manuscript"; }
    else                                                                        { $Cover = "99"; $Online = 0; $PPNLink = 0; $Name = "unkwown"; }

    $this->format  = $Name;
    $this->cover   = $Cover;
    $this->ppnlink = $PPNLink;
    $this->online  = $Online;
    $this->leader  = $Leader;
  }

  private function SetMarcContents()
  {
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
        $Sub = array();

        // 912-area keep only data for configured iln
        if ( $tag == "912" && isset($_SESSION["iln"]) )
        {
          if ( $data->getSubField("a") )
          {
            $Tmp = $data->getSubField("a")->getData();
            if ( substr($Tmp,0,8) == "GBV_ILN_" && $Tmp != ("GBV_ILN_" . $_SESSION["iln"]) )  continue;
            if ( in_array($Tmp, array("SYSFLAG_1", "SYSFLAG_A", "GBV_GVK")) )  continue;
          }
        }

        // 980-area keep only data for configured iln
        if ( $tag >= "980" && isset($_SESSION["iln"]) )
        {
          if ( $data->getSubField("2") )
          {
            $Tmp = $data->getSubField("2")->getData();
            if ($Tmp != $_SESSION["iln"])  continue;
          }
        }
        foreach ($data->getSubfields() as $code => $value)
        {
          {
            $Sub[] = array($code => $value->getData());
          }
        }
        $this->contents[$tag][] = $Sub;
      }
    }
    //$this->CI->printArray2File($this->contents);
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
              if ( substr($Value,0,8) == "(DE-601)" )
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
      // Load MARC library and pass params
      $this->marc = $this->CI->pearloader->loadmarc('File','MARC', $this->prepare($one["fullrecord"]))->next();
      $this->SetMarcFormat();
      $this->SetMarcContents();
      $this->SetMarcParents();
      $this->SetMarcISBN();
      $this->SetMarcCatalogues();

      // Prepare reduced array
      $reduced = array
      (
        "id" 		       => $one["id"],
        "parents"      => $this->parents,
        //"fullrecord"   => $one["fullrecord"],
        //"marc"         => $this->marc,
        "leader"       => $this->leader,
        "format"       => $this->format,
        "ppnlink"      => $this->ppnlink,
        "cover"        => $this->cover,
        "isbn"         => $this->isbn,
        "online"       => $this->online,
        "catalogues"   => $this->catalogues,
        "contents"     => $this->contents
      );

      $results_reduced[$one["id"]]	= $reduced;
    }
    $container["results"] = $results_reduced;

    // $this->CI->printArray2File($container["results"]);

    return ($container);
  }

}

?>
