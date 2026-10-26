<?php

class systemdb extends com
{
  private   $localdb;
  private   $pdoOptions;
  protected $db;

  protected $lastDBError;

  public function __construct($f3)
  {
    parent::__construct($f3);
    if (APPDEBUG["FKT"]) $this->debug("FKT", __METHOD__);

    ini_set("default_socket_timeout", 2);

    $this->driver     = $this->normalizeDriver($this->f3->get('db.driver'));
    $this->pdoOptions = $this->pdoOptionsFor($this->driver);

    try {
      $this->db      = new DB\SQL(
        $this->buildDSN($this->f3->get('db.host'), $this->f3->get('db.name')),
        $this->f3->get('db.user'),
        $this->f3->get('db.pass'),
        $this->pdoOptions
      );
      $this->localdb = true;
    } catch (PDOException $e) {
      if (strstr($e, 'SQLSTATE[')) {
        preg_match('/SQLSTATE\[(\w+)\] \[(\w+)\] (.*)/', $e, $matches);
        $Code    = ($matches[1] == 'HT000' ? $matches[2] : $matches[1]);
        $Message = $matches[3];
        $this->debug("PDO", "Local DB not available");
        $this->debug("PDO", $Code . " " . $Message);
        $this->mailDBError("Local DB not available", $Code . " " . $Message);
      } else {
        $this->debug("PDO", $e);
        $this->mailDBError("Local DB not available", $e->getMessage());
      }
      $this->localdb = false;
      $this->finalize(array("status" => 0, "code" => "localdatabaseerror"));
    }
  }

  protected $driver = "mysql";

  public function isPG()
  {
    return ($this->driver == "pgsql");
  }

  protected function normalizeDriver($Value)
  {
    $Drv = strtolower(trim((string) $Value));
    return ($Drv == "pgsql" || $Drv == "postgres" || $Drv == "postgresql") ? "pgsql" : "mysql";
  }

  protected function pdoOptionsFor($Driver)
  {
    $Opts = array(
      PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
      PDO::ATTR_PERSISTENT         => false,
      PDO::ATTR_TIMEOUT            => 2
    );
    if ($Driver == "mysql") {
      if (substr(PHP_VERSION, 0, 3) >= "8.5") {
        $Opts[PDO\Mysql::ATTR_INIT_COMMAND] = "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci";
      } else {

        $Opts[PDO::MYSQL_ATTR_INIT_COMMAND] = "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci";
      }
    }
    return $Opts;
  }

  protected function buildDSN($Host, $Name)
  {
    if ($this->isPG())  return 'pgsql:host=' . $Host . ';port=5432;dbname=' . $Name . ';';
    return 'mysql:host=' . $Host . ';port=3306;dbname=' . $Name . ';charset=utf8mb4;';
  }

  private $uniquecache = array();

  protected function newIDFrom($Rows, $Conn)
  {
    if ($this->isPG()) {
      if (is_array($Rows) && isset($Rows[0]) && is_array($Rows[0]) && isset($Rows[0]["id"])) {
        return $Rows[0]["id"];
      }
      return 0;
    }
    return $Conn ? $Conn->lastInsertId() : 0;
  }

  protected function schemaOf($Module)
  {
    $Module = strtolower(trim($Module));
    if ($this->isPG())  return ($Module == "user") ? '"user"' : $Module;
    return $this->f3->get("application.type") . "_" . $Module;
  }

  protected function schemaName($Module)
  {
    return trim($this->schemaOf($Module), '"');
  }

  protected function execDB($SQL, $Params = [])
  {
    if (APPDEBUG["SQQ"])                    $this->debug("SQQ", $SQL);
    if (APPDEBUG["SQP"] && !empty($Params)) $this->debug("SQP", $Params);
    $Insert = (strncasecmp(trim($SQL), "insert ", 7) === 0) ? true : false;
    $Stats  = [];
    try {
      $Rows = $this->localdb ? $this->db->exec($SQL, $Params) : [];
      if ($Insert) {
        $Stats["newID"] = $this->newIDFrom($Rows, $this->db);
      }
      if (APPDEBUG["SQD"] &&  empty($Rows))   $this->debug("SQD", "No records found!");
      if (APPDEBUG["SQD"] && !empty($Rows))   $this->debug("SQD", $Rows);

      return $Insert ? $Stats : $Rows;

    } catch (PDOException $e) {

      $Kette = array();
      foreach (array_slice($e->getTrace(), 0, 6) as $Schritt) {
        $Kette[] = ($Schritt["class"] ?? "") . ($Schritt["type"] ?? " ") . ($Schritt["function"] ?? "?");
      }

      $this->debug("PDO", $e->getMessage()
                        . " | SQL: " . preg_replace('/\s+/', " ", trim((string) $SQL))
                        . " | via " . implode(" < ", $Kette));

      $this->lastDBError = $e->getMessage();

      if (!$this->istDoppelterSchluessel($e->getMessage())) {
        $this->mailDBError("execDB query failed", $e->getMessage(), $SQL);
      }
      return [];
    }
  }

  protected function istDoppelterSchluessel($Meldung)
  {
    $M = (string) $Meldung;
    return (strpos($M, "SQLSTATE[23505]") !== false)
        || (strpos($M, "SQLSTATE[23000]") !== false && stripos($M, "duplicate entry") !== false)
        || (stripos($M, "duplicate key value") !== false);
  }

  protected function getAdminConfigsDB($Selection = [], $Filter = false)
  {
    if (APPDEBUG["FKT"]) $this->debug("FKT", __METHOD__);

    if (is_array($Selection) && !count($Selection) && $Filter) return [];

    $Data = array(
      "client" => $this->client,
    );
    $SQL  = "SELECT category, setting, value FROM " . $this->schemaOf(MODULE);
    $SQL .= ".adminconfigs";
    $SQL .= " WHERE client = :client";
    if (is_array($Selection) && $Filter) $SQL .= " and category in ('" . implode("', '", $Selection) . "')";

    $Rows = $this->execDB($SQL, $Data);

    $Confs = [];
    foreach ($Rows as $Row) {
      $Confs[$Row["category"]][$Row["setting"]] = $Row["value"];
    }
    return $Confs;
  }

  protected function getAdminTextsDB($Selection = [], $Filter = false)
  {
    if (APPDEBUG["FKT"]) $this->debug("FKT", __METHOD__);

    if (is_array($Selection) && !count($Selection) && $Filter) return [];

    $Data = array(
      "client" => $this->client,
    );
    $SQL  = "select shortcut, german, english FROM " . $this->schemaOf(MODULE) . ".admintexts";
    $SQL .= " WHERE client = :client";
    if (is_array($Selection) && $Filter) $SQL .= " and (shortcut like '" . implode("' or shortcut like '", $Selection) . "')";
    $Rows = $this->ExecDB($SQL, $Data);

    $Texts = [];
    foreach ($Rows as $Row) {
      $Texts[$Row["shortcut"]] = array(
        "german"  => $Row["german"]  !== null && trim($Row["german"])  !== '' ? trim($Row["german"])  : "",
        "english" => $Row["english"] !== null && trim($Row["english"]) !== '' ? trim($Row["english"]) : ""
      );
    }

    return $Texts;
  }

  private static $StatsKey = array();

  private static $StatsExtra = array();
}
