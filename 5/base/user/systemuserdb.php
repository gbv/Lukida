<?php

class systemuserdb extends appl
{
  private static $ClientViewCols = null;

  public function __construct($f3)
  {
    parent::__construct($f3);
    if ( APPDEBUG["FKT"] ) $this->debug("FKT",__METHOD__);
  }

  protected function clientViewColumnsDB()
  {
    if ( self::$ClientViewCols !== null )  return self::$ClientViewCols;

    $SQL  = "SELECT column_name AS colname FROM information_schema.columns"
          . " where table_schema = :schema and table_name = 'hostingclients'"
          . " and column_name in ('active', 'validuntil')";
    $Rows = $this->execDB($SQL, array("schema" => $this->schemaName(MODULE)));

    $Spalten = array();
    foreach ( (array) $Rows as $Row )  $Spalten[] = $Row["colname"];

    return (self::$ClientViewCols = $Spalten);
  }

  protected function renewTokenSchrankeDB()
  {
    return null;
  }

  protected function renewTokenDB()
  {
    if ( APPDEBUG["FKT"] ) $this->debug("FKT",__FUNCTION__);

    $Schranke = $this->renewTokenSchrankeDB();
    if ( is_array($Schranke) )  return $Schranke;

    $Spalten = $this->clientViewColumnsDB();
    $Felder  = "client";
    if ( in_array("active", $Spalten) )      $Felder .= ", active";
    if ( in_array("validuntil", $Spalten) )  $Felder .= ", validuntil";

    $SQL  = "select " . $Felder . " from hostingclients where client=:client";
    $Rows = $this->execDB($SQL, array("client" => $this->client));
    if ( count($Rows) != 1 )  return array();

    $Row = $Rows[0];

    if ( isset($Row["active"]) && strtolower(trim((string) $Row["active"])) !== "y" )
    {
      $this->logDB("renewtoken abgelehnt: Zugang abgeschaltet", "api", "renewtoken", "ERROR");
      return array("status" => -1, "code" => "renewtokenblocked");
    }

    if ( isset($Row["validuntil"]) && trim((string) $Row["validuntil"]) !== ""
      && strtotime((string) $Row["validuntil"]) < time() )
    {
      $this->logDB("renewtoken abgelehnt: Schluessel abgelaufen am " . $Row["validuntil"], "api", "renewtoken", "ERROR");
      return array("status" => -1, "code" => "renewtokenexpired");
    }

    $Inhalt = array('ml' => 'user', 'cl' => $this->client, 'dt' => time());
    if ( trim((string) $this->apikey) !== "" )  $Inhalt['ky'] = $this->apikey;
    $APIToken = $this->encodeToken($Inhalt);
    $ValidUntil = (new DateTime())->modify("+" . $this->f3->get("application.daysvalidtoken") . " day")->format('Y-m-d H:i:s');

    $this->updStatDB("Token", "Renew");
    $this->logDB("renewtoken: neuer Schluessel ausgestellt, gueltig bis " . $ValidUntil, "api", "renewtoken", "OK");

    return array("token" => $APIToken, "validuntil" => $ValidUntil);
  }
}

?>
