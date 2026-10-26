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

  protected function statsExtraNamesDB()
  {
    return array("bms", "level");
  }

  protected function statsExtraValuesDB(array $Spalten)
  {
    if ( MODULE != "user" )  return array();

    $Aus = array();

    if (in_array("bms", $Spalten)) {
      $Login = isset($this->posted["login"]) ? trim((string) $this->posted["login"]) : "";
      $Aus["bms"] = ($Login !== "" && $this->bmsid)
                          ? substr((string) $this->bmsid, 0, 50)
                          : "-";
    }

    if (in_array("level", $Spalten)) {
      $Raum = "";
      if (isset($this->posted["search"]["userlevel"]) && trim((string) $this->posted["search"]["userlevel"]) != "")
        $Raum = trim((string) $this->posted["search"]["userlevel"]);
      elseif (isset($this->posted["level"]) && trim((string) $this->posted["level"]) != "")
        $Raum = trim((string) $this->posted["level"]);
      $Aus["level"] = ($Raum !== "") ? substr($Raum, 0, 50) : "-";
    }

    return $Aus;
  }

  protected function getLevelsDB()
  {
    if ( APPDEBUG["FKT"] ) $this->debug("FKT",__METHOD__);

    $Data = array('client' => $this->client);

    $SQL  = "select level from adminlevels where client = :client and sort > 0 order by sort";

    $Rows = $this->execDB($SQL, $Data);
    $Levl = array();
    foreach ($Rows as $Row)
    {
      $Levl[] = $Row["level"];
    }

    return $Levl;
  }

  protected function getApplTextsDB()
  {
    if ( APPDEBUG["FKT"] ) $this->debug("FKT",__METHOD__);

    $SQL = "select shortcut, german, english from " . $this->schemaOf(MODULE)
         . (MODULE == "admin" ? ".bibtexts" : ".adminbibtexts");

    try {
      $Rows = $this->execDB($SQL);
    } catch (\Throwable $Fehler) {
      return [];
    }

    return $this->textZeilen($Rows);
  }

  private function textZeilen($Rows)
  {
    $Texts = [];
    foreach ((array) $Rows as $Row) {
      if (!isset($Row["shortcut"]))  continue;
      $Texts[$Row["shortcut"]] = array(
        "german"  => $Row["german"]  !== null && trim($Row["german"])  !== '' ? trim($Row["german"])  : "",
        "english" => $Row["english"] !== null && trim($Row["english"]) !== '' ? trim($Row["english"]) : ""
      );
    }
    return $Texts;
  }
}

?>
