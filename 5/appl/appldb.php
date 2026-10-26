<?php

class appldb extends system
{
  public function __construct($f3)
  {
    parent::__construct($f3);
    if ( APPDEBUG["FKT"] ) $this->debug("FKT",__METHOD__);
  }

  public function prepareDaiaCachePath($Endpunkt, $Kennung = "", $Verzeichnis = false)
  {
    $Path = CACHESDIR . date("Ymd") . DIRECTORY_SEPARATOR . "_daia"
          . DIRECTORY_SEPARATOR . substr(md5((string) $Endpunkt), 0, 8);

    if ( $Verzeichnis )
    {
      $this->createPath($Path);
      return $Path;
    }

    $Name  = md5((string) $Kennung);
    $Path .= DIRECTORY_SEPARATOR . substr($Name, 0, 2);
    $this->createPath($Path);

    return $Path . DIRECTORY_SEPARATOR . $Name . ".inc";
  }

  protected function getLevelsDB()
  {
    if ( APPDEBUG["FKT"] ) $this->debug("FKT",__METHOD__);

    $Data = array('client' => $this->client);

    $SQL  = "select level from " . $this->schemaOf(MODULE) . ".adminlevels where client = :client and sort > 0 order by sort";

    $Rows = $this->execDB($SQL, $Data);
    $Levl = array();
    foreach ($Rows as $Row)
    {
      $Levl[] = $Row["level"];
    }

    return $Levl;
  }

}

?>
