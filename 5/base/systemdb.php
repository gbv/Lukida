<?php

class systemdb extends com
{
  
  
  
  private   $centraldb;
  private   $centralversucht;
  private   $envdb;
  private   $localdb;
  private   $pdoOptions;
  private   $pdoOptionsCentral;
  protected $db;
  protected $dbc;
  protected $dbe;
  
  
  protected $lastDBError;

  public function __construct($f3)
  {
    parent::__construct($f3);
    if (APPDEBUG["FKT"]) $this->debug("FKT", __METHOD__);

    
    ini_set("default_socket_timeout", 2);

    
    
    
    
    $this->driver = $this->normalizeDriver($this->f3->get('db.driver'));

    
    
    
    
    
    
    
    $this->drivercentral = $this->normalizeDriver($this->f3->get('central.db.driver'));

    $this->pdoOptions        = $this->pdoOptionsFor($this->driver);
    $this->pdoOptionsCentral = $this->pdoOptionsFor($this->drivercentral);

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

    
    
    $this->centraldb      = false;
    $this->centralversucht = false;

    
    
    
    
    
    
    
    $this->pruefeApiKey();
  }

  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  private function setCentralDB()
  {
    if ($this->centralversucht)  return;
    $this->centralversucht = true;

    if (
      !$this->f3->get('central.db.host') || !$this->f3->get('central.db.name')
      || !$this->f3->get('central.db.user') || !$this->f3->get('central.db.pass')
    ) {
      return;
    }

    try {
      $this->dbc = new DB\SQL(
        $this->buildDSN($this->f3->get('central.db.host'), $this->f3->get('central.db.name'), true),
        $this->f3->get('central.db.user'),
        $this->f3->get('central.db.pass'),
        $this->pdoOptionsCentral
      );
      $this->centraldb = true; 
    } catch (PDOException $e) {
      if (strstr($e, 'SQLSTATE[')) {
        preg_match('/SQLSTATE\[(\w+)\] \[(\w+)\] (.*)/', $e, $matches);
        $Code    = ($matches[1] == 'HT000' ? $matches[2] : $matches[1]);
        $Message = $matches[3];
        $this->debug("PDO", "Central DB not available");
        $this->debug("PDO", $Code . " " . $Message);
        $this->mailDBError("Central DB not available", $Code . " " . $Message);
      } else {
        $this->debug("PDO", $e);
        $this->mailDBError("Central DB not available", $e->getMessage());
      }
      $this->centraldb = false;
    }
  }

  
  
  
  
  
  
  
  
  
  
  
  protected function pruefeApiKey()
  {
  }

  
  
  

  private function setEnvDB($Env)
  {
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    $Driver = $this->driverForEnv($Env);
    $User   = ($Driver == "pgsql") ? $this->f3->get('db.user') : $this->f3->get('deployuser.user');
    $Pass   = ($Driver == "pgsql") ? $this->f3->get('db.pass') : $this->f3->get('deployuser.pass');

    if (
      $this->f3->get('deploydbs.' . $Env) && $this->f3->get('db.name')
      && $User && $Pass
    ) {
      try {
        
        
        
        
        
        
        
        $this->dbe = new DB\SQL(
          $this->buildDSNFor($Driver, $this->f3->get('deploydbs.' . $Env), $this->f3->get('db.name')),
          $User,
          $Pass,
          $this->pdoOptionsFor($Driver)
        );
        $this->envdb = true; 
      } catch (PDOException $e) {
        if (strstr($e, 'SQLSTATE[')) {
          preg_match('/SQLSTATE\[(\w+)\] \[(\w+)\] (.*)/', $e, $matches);
          $Code    = ($matches[1] == 'HT000' ? $matches[2] : $matches[1]);
          $Message = $matches[3];
          $this->debug("PDO", $Env . " DB not available");
          $this->debug("PDO", $Code . " " . $Message);
          $this->mailDBError($Env . " DB not available", $Code . " " . $Message);
        } else {
          $this->debug("PDO", $e);
          $this->mailDBError($Env . " DB not available", $e->getMessage());
        }
        $this->envdb = false;
      }
    }
  }

  public function EncryptString($String)
  {
    if (!isset($this->dbpw)) {
      $this->logs->Insert("SessionError|EncryptString|" . $String, "partners");
      return "";
    }
    $key   = substr(sha1($this->dbpw, true), 0, 16);
    $iv    = openssl_random_pseudo_bytes(16);
    $Crypt = $iv . openssl_encrypt($String, 'AES-128-CBC', $key, OPENSSL_RAW_DATA, $iv);
    return $Crypt;
  }

  private function DecryptString($Crypt)
  {
    if (!isset($this->dbpw)) {
      $this->logs->Insert("SessionError|DecryptString|" . $Crypt, "partners");
      return "";
    }
    $key = substr(sha1($this->dbpw, true), 0, 16);
    $iv  = substr($Crypt, 0, 16);
    $String  = openssl_decrypt(substr($Crypt, 16), 'AES-128-CBC', $key, OPENSSL_RAW_DATA, $iv);
    return $String;
  }

  public function EncodeString($String)
  {
    if (trim($String) != "") {
      
      
      $String = trim($String);
    }
    return $String;
  }

  public function EncodeArray($Array)
  {
    return (serialize($Array));
  }

  private function DecodeString($String)
  {
    if (trim($String) != "") {
      $String = htmlspecialchars_decode(trim($String), ENT_NOQUOTES);
    }
    return $String;
  }

  private function DecodeArray($Array)
  {
    $Array = unserialize($Array);
    return ($Array);
  }

  private function getCountry2IP()
  {
    if (APPDEBUG["FKT"]) $this->debug("FKT", __METHOD__);

    if (ACTION == "batch") return "Germany";

    $Country = "";

    
    
    
    
    
    
    
    $this->createPath(IPCACHEDIR);
    $File = IPCACHEDIR . preg_replace('/[^0-9a-f.:]/i', "_", strtolower((string) $this->clientip)) . ".inc";
    if (file_exists($File)) {
      
      
      
      
      
      
      
      
      
      
      
      
      
      
      $Country = json_decode(file_get_contents($File), true);
      if ( !is_string($Country) )  $Country = "";
    } else {
      
      

      
      
      
      
      
      
      
      
      
      
      
      
      
      
      
      
      
      $Zentral = trim((string) $this->f3->get("central.db.schema"));
      $Tabelle = ($Zentral !== "" ? $Zentral . "." : "") . "ip2country";

      if ($this->isPG(true)) {
        $Data = array("ip" => $this->clientip);
        $SQL  = "SELECT country FROM " . $Tabelle . " WHERE cast(:ip as inet) >= ipfrom and cast(:ip as inet) < ipto order by ipto limit 1";
      } else {
        $Data = array("ip" => inet_pton($this->clientip));
        $SQL  = "SELECT country FROM " . $Tabelle . " WHERE :ip >= ipfrom and :ip < ipto order by ipto limit 1";
      }
      $Rows = $this->execDB($SQL, $Data, true);
      $Country = isset($Rows[0]["country"]) ? $Rows[0]["country"] : "Unknown";

      
      
      
      
      
      
      
      
      
      
      
      
      
      
      
      if ( @file_put_contents($File, json_encode($Country, true)) === false )
      {
        $this->createPath(dirname($File) . DIRECTORY_SEPARATOR);
        if ( @file_put_contents($File, json_encode($Country, true)) === false )
          $this->debug("LOG", "getCountry2IP: Zwischenspeicher nicht schreibbar: " . $File);
      }
    }

    
    
    if (!trim((string) $Country))  $Country = "Unknown";

    return $Country;
  }


  
  
  
  
  
  
  
  
  
  
  

  protected $driver        = "mysql";
  protected $drivercentral = "mysql";

  
  
  
  protected $driverenv     = null;

  
  
  
  
  
  
  
  
  
  
  public function isPG($Central = false)
  {
    if ($Central)                      return ($this->drivercentral == "pgsql");
    if ($this->driverenv !== null)     return ($this->driverenv     == "pgsql");
    return ($this->driver == "pgsql");
  }

  
  
  
  
  
  
  
  
  
  
  
  
  
  

  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  protected function isJsonType($Type)
  {
    return in_array(strtolower(trim((string) $Type)), array("json", "jsonb"), true);
  }

  protected function isOwnEnv($Env)
  {
    $Map = array("dev" => "development", "prod" => "production",
                 "test" => "test", "local" => "local");
    $Key = strtolower(trim((string) $Env));
    return (($Map[$Key] ?? $Key) === MODE);
  }

  protected function driverForEnv($Env)
  {
    return $this->normalizeDriver($this->f3->get('deploydb.' . $Env));
  }

  
  
  
  protected function beginEnvDriver($Env)
  {
    
    
    
    
    $this->driverenv = ($Env && !$this->isOwnEnv($Env)) ? $this->driverForEnv($Env) : null;
  }

  protected function endEnvDriver()
  {
    $this->driverenv = null;
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

  
  
  
  
  
  
  
  
  
  protected function buildDSN($Host, $Name, $Central = false)
  {
    return $this->buildDSNFor($this->isPG($Central) ? "pgsql" : "mysql", $Host, $Name, $Central);
  }

  
  
  
  protected function buildDSNFor($Driver, $Host, $Name, $Central = false)
  {
    if ($Driver == "pgsql") {
      if ($Central)  return 'pgsql:host=' . $Host . ';port=5432;dbname=' . $Name . ';';
      
      
      
      
      
      
      $DB = trim((string) $this->f3->get('db.database'));
      if (!$DB)  $DB = trim((string) $this->f3->get('application.type'));
      if (!$DB)  $DB = $Name;
      return 'pgsql:host=' . $Host . ';port=5432;dbname=' . $DB . ';';
    }
    return 'mysql:host=' . $Host . ';port=3306;dbname=' . $Name . ';charset=utf8mb4;';
  }

  
  
  
  
  protected function sqlLimit($Central = false)
  {
    return $this->isPG($Central) ? " limit :limit offset :start" : " limit :start,:limit";
  }

  
  
  
  
  protected function sqlUpsert($Table, $Fields, $Conflict = array())
  {
    $Quoted = array();
    foreach ($Fields as $F)  $Quoted[] = $this->ident($F);
    $Cols = implode(", ", $Quoted);
    $Vals = ":" . implode(", :", $Fields);

    if ($this->isPG()) {
      if (!count($Conflict))  $Conflict = array("id");
      $Set = array();
      foreach ($Fields as $F) {
        if (in_array($F, $Conflict))  continue;
        $Set[] = $this->ident($F) . " = EXCLUDED." . $this->ident($F);
      }
      $SQL = "insert into " . $Table . " (" . $Cols . ") values (" . $Vals . ")";
      $QConf = array();
      foreach ($Conflict as $C)  $QConf[] = $this->ident($C);
      $SQL .= " on conflict (" . implode(", ", $QConf) . ") do update set " . implode(", ", $Set);
      $SQL .= " returning id";
      return $SQL;
    }

    $Set = array();
    foreach ($Fields as $F)  $Set[] = $F . " = :" . $F;
    
    
    return "insert into " . $Table . " (" . $Cols . ") values (" . $Vals . ")"
         . " ON DUPLICATE KEY UPDATE " . implode(", ", $Set) . ", id = LAST_INSERT_ID(id)";
  }

  
  
  
  
  
  
  
  
  
  
  protected function sqlReplace($Table, $Fields, $Conflict = array())
  {
    $Vals = ":" . implode(", :", $Fields);

    if ( !$this->isPG() )
      return "replace into " . $Table . " (" . implode(", ", $Fields) . ") values (" . $Vals . ")";

    $Quoted = array();
    foreach ( $Fields as $F )  $Quoted[] = $this->ident($F);
    $SQL = "insert into " . $Table . " (" . implode(", ", $Quoted) . ") values (" . $Vals . ")";

    if ( !count($Conflict) )  return $SQL;

    $Set = array();
    foreach ( $Fields as $F )
    {
      if ( in_array($F, $Conflict) )  continue;
      $Set[] = $this->ident($F) . " = EXCLUDED." . $this->ident($F);
    }
    $QC = array();
    foreach ( $Conflict as $C )  $QC[] = $this->ident($C);
    $SQL .= " on conflict (" . implode(", ", $QC) . ")";
    $SQL .= count($Set) ? " do update set " . implode(", ", $Set) : " do nothing";
    return $SQL;
  }

  protected function sqlFulltext($Columns, $Param = ":search")
  {
    if (!is_array($Columns))  $Columns = array($Columns);

    if ($this->isPG()) {
      $Parts = array();
      foreach ($Columns as $C)  $Parts[] = "coalesce(" . $C . "::text,'')";
      
      
      
      
      return "(" . implode(" || ' ' || ", $Parts) . ") ILIKE '%' || replace(" . $Param . ", '*', '%') || '%'";
    }
    return "MATCH(" . implode(", ", $Columns) . ") AGAINST (" . $Param . " in boolean mode)";
  }

  
  
  
  private $uniquecache = array();

  protected function uniqueKeyOf($Table)
  {
    $Table = trim($Table);
    if (isset($this->uniquecache[$Table]))  return $this->uniquecache[$Table];

    $Cols = array();
    if ($this->isPG()) {
      
      
      
      
      
      
      
      
      
      
      
      
      
      
      
      
      
      
      
      
      
      
      
      
      
      
      
      
      $SQL  = "with t as (select to_regclass(:tbl) as rel),";
      $SQL .= " u as (select i.indkey from pg_index i join t on i.indrelid = t.rel";
      $SQL .= "        where i.indisunique and not i.indisprimary and i.indpred is null";
      $SQL .= "        order by i.indnatts, i.indexrelid limit 1)";
      $SQL .= " select a.attname from u cross join t";
      $SQL .= " join unnest(u.indkey) with ordinality k(att, ord) on k.att <> 0";
      $SQL .= " join pg_attribute a on a.attrelid = t.rel and a.attnum = k.att";
      $SQL .= " order by k.ord";
      foreach ($this->execDB($SQL, array("tbl" => trim($Table, '"'))) as $R)  $Cols[] = $R["attname"];
    }

    return ($this->uniquecache[$Table] = $Cols);
  }

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

  
  
  
  
  
  protected function sqlDup($Set, $Conflict)
  {
    if ($this->isPG()) {
      $Q = array();
      foreach ($Conflict as $C)  $Q[] = $this->ident($C);
      return " on conflict (" . implode(", ", $Q) . ") do update set " . $Set;
    }
    return " on duplicate key update " . $Set;
  }

  
  
  
  
  
  
  protected function ident($Name)
  {
    return $this->isPG() ? '"' . str_replace('"', '', $Name) . '"' : $Name;
  }

  
  
  protected function schemaName($Module)
  {
    return trim($this->schemaOf($Module), '"');
  }

  
  
  
  protected function sqlDate($Column, $Pattern = "%d.%m.%Y")
  {
    if ($this->isPG()) {
      $Map = array("%d" => "DD", "%m" => "MM", "%Y" => "YYYY", "%y" => "YY",
                   "%H" => "HH24", "%i" => "MI", "%s" => "SS");
      return "to_char(" . $this->ident($Column) . ", '" . strtr($Pattern, $Map) . "')";
    }
    return "DATE_FORMAT(" . $Column . ", '" . $Pattern . "')";
  }

  protected function execDB($SQL, $Params = [], $Central = false, $Env = "")
  {
    if (APPDEBUG["SQQ"])                    $this->debug("SQQ", $SQL);
    if (APPDEBUG["SQP"] && !empty($Params)) $this->debug("SQP", $Params);
    $Insert = (strncasecmp(trim($SQL), "insert ", 7) === 0) ? true : false;
    $Stats  = [];
    try {
      if ($Central) {
        $this->setCentralDB();
        $Rows = $this->centraldb ? $this->dbc->exec($SQL, $Params) : [];
        if ($Insert) {
          $Stats["newID"]       = $this->newIDFrom($Rows, $this->dbc);
        }
      
      
      
      
      
      
      
      
      
      
      
      
      
      
      
      
      
      
      } elseif ($Env && !$this->isOwnEnv($Env)) {
        $this->setEnvDB($Env);
        $Rows = $this->envdb   ? $this->dbe->exec($SQL, $Params)  : [];
        if ($Insert) {
          $Stats["newID"]       = $this->newIDFrom($Rows, $this->dbe);
        }
      } else {
        $Rows = $this->localdb   ? $this->db->exec($SQL, $Params)  : [];
        if ($Insert) {
          $Stats["newID"]       = $this->newIDFrom($Rows, $this->db);
        }
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

  



  protected function klartextDoppelterSchluessel($Meldung)
  {
    $M = (string) $Meldung;

    
    if (preg_match('/Key \(([^)]*)\)=\(([^)]*)\) already exists/i', $M, $T)) {
      $Spalten = array_map("trim", explode(",", $T[1]));
      $Werte   = array_map("trim", explode(",", $T[2]));
      $Paare   = array();
      foreach ($Spalten as $i => $Spalte) {
        $Wert = isset($Werte[$i]) ? $Werte[$i] : "";
        $Paare[] = $Spalte . " = " . ($Wert === "" ? "(leer)" : $Wert);
      }
      $Leere = array_filter($Spalten, fn($Sp, $i) => trim($Werte[$i] ?? "") === "", ARRAY_FILTER_USE_BOTH);

      return "Diesen Eintrag gibt es bereits: " . implode(", ", $Paare) . "."
           . (count($Leere) ? " Bitte " . implode(" und ", $Leere) . " ausfuellen." : " Bitte den vorhandenen Eintrag bearbeiten.");
    }

    
    if (preg_match("/Duplicate entry '([^']*)' for key '([^']*)'/i", $M, $T)) {
      return "Diesen Eintrag gibt es bereits (" . $T[1] . "). Bitte die Pflichtfelder ausfuellen"
           . " oder den vorhandenen Eintrag bearbeiten.";
    }

    return "Diesen Eintrag gibt es bereits.";
  }

  protected function checkGetUserDB($User, $Pass)
  {
    if (APPDEBUG["FKT"]) $this->debug("FKT", __METHOD__);

    $Data = array(
      "username" => $User,
      "active"   => "y"
    );
    $SQL  = "select username, clients, pwhash, gender, title, firstname, lastname, mail, start, avatar";
    if (MODULE == "hosting") $SQL .= " from users";
    if (MODULE == "admin")   $SQL .= " from hostingusers";
    if (MODULE == "user")    $SQL .= " from adminusers";
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    $SQL .= " where lower(username) = lower(:username) and active = :active";
    $Rows = $this->execDB($SQL, $Data);

    if (count($Rows) == "1") {
      $Row = $Rows[0];
      if (password_verify($Pass, $Row["pwhash"])) {
        unset($Row["pwhash"]);
        $Row["clients"] = (MODULE != "user") ? json_decode($Row["clients"]) :  array($Row["clients"]);
        $Row["token"]   = $this->encodeToken(array(
          'un' => $Row["username"],
          'cl' => $this->client,
          'av' => $Row["avatar"],
          'dt' => time()
        ));
        $Row["name"]    = $Row["firstname"] . " " . $Row["lastname"];
        $Row["notes"]   = [];
        sort($Row["clients"]);
        return $Row;
      }
    }
    return [];
  }

  
  
  
  
  
  
  
  
  
  protected function getLocationsDB($Client, $Env)
  {
  if ( APPDEBUG["FKT"] ) $this->debug("FKT",__METHOD__);

  try
  {
  $Tab  = $this->schemaOf(MODULE) . ((MODULE == "admin") ? ".locations" : ".adminlocations");
  $Rows = $this->execDB("select data from " . $Tab . " where client = :client and env = :env",
                        array("client" => $Client, "env" => $Env));
  return isset($Rows[0]["data"]) ? (string) $Rows[0]["data"] : "";
  }
  catch ( Exception $e )
  {
  return "";
  }
  }

  protected function getAdminConfigsDB($Selection = [], $Filter = false)
  {
    if (APPDEBUG["FKT"]) $this->debug("FKT", __METHOD__);

    if (is_array($Selection) && !count($Selection) && $Filter) return [];

    $Data = array(
      "client" => $this->client,
    );
    $SQL  = "SELECT category, setting, value FROM " . $this->schemaOf(MODULE);
    $SQL .= (MODULE == "admin") ? ".configs" : ".adminconfigs";
    $SQL .= " WHERE client = :client";
    if (is_array($Selection) && $Filter) $SQL .= " and category in ('" . implode("', '", $Selection) . "')";

    $Rows = $this->execDB($SQL, $Data);

    $Confs = [];
    foreach ($Rows as $Row) {
      $Confs[$Row["category"]][$Row["setting"]] = $Row["value"];
    }
    return $Confs;
  }

  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  protected function getHostingClientDB($Client = "")
  {
    if (APPDEBUG["FKT"]) $this->debug("FKT", __METHOD__);

    $Tabelle = (MODULE == "hosting") ? "clients" : $this->schemaOf(MODULE) . ".hostingclients";
    $SQL     = "select * from " . $Tabelle . " where client = :client";

    try   { $Rows = $this->execDB($SQL, array("client" => ($Client ? $Client : $this->client))); }
    catch (Exception $E) { return array(); }

    if (!is_array($Rows) || !count($Rows))  return array();

    
    
    
    
    
    
    
    
    $Felder = array("solrpath", "kisuggest", "messageprovider", "messagechannel",
                    "messagekey", "mybibuser", "mybibpass", "mybibprodhost", "mybibtesthost",
                    "scopes");
    $Aus = array();
    foreach ($Felder as $F)
    {
      if (isset($Rows[0][$F]))  $Aus[$F] = $Rows[0][$F];
    }
    return $Aus;
  }

  
  
  
  
  
  
  
  
  
  protected function getClientConfigDB($Client, $Category, $Setting, $Default = "")
  {
    static $Merker = array();

    $Client = trim((string) $Client);
    if ( $Client === "" )  return $Default;

    $Schluessel = $Client . "|" . $Category . "|" . $Setting;
    if ( !array_key_exists($Schluessel, $Merker) )
    {
      $SQL  = "select value from " . $this->schemaOf(MODULE);
      $SQL .= (MODULE == "admin") ? ".configs" : ".adminconfigs";
      $SQL .= " where client = :client and category = :category and setting = :setting";
      $Rows = $this->execDB($SQL, array("client" => $Client, "category" => $Category, "setting" => $Setting));
      $Merker[$Schluessel] = ( is_array($Rows) && count($Rows) && isset($Rows[0]["value"]) )
                               ? trim((string) $Rows[0]["value"]) : "";
    }
    return ( $Merker[$Schluessel] === "" ) ? $Default : $Merker[$Schluessel];
  }

  protected function getAdminTextsDB($Selection = [], $Filter = false, $Client = "")
  {
    if (APPDEBUG["FKT"]) $this->debug("FKT", __METHOD__);

    if (is_array($Selection) && !count($Selection) && $Filter) return [];

    
    $Data = array(
      "client" => $Client ? $Client : $this->client,
    );
    $SQL  = (MODULE == "admin") ? "SELECT id," : "select";
    $SQL .= " shortcut, german, english FROM " . $this->schemaOf(MODULE);
    $SQL .= (MODULE == "admin") ? ".texts" : ".admintexts";
    $SQL .= " WHERE client = :client";
    if (is_array($Selection) && $Filter) $SQL .= " and (shortcut like '" . implode("' or shortcut like '", $Selection) . "')";
    $Rows = $this->ExecDB($SQL, $Data);

    
    $Texts = [];
    foreach ($Rows as $Row) {
      $Texts[$Row["shortcut"]] = array(
        "german"  => $Row["german"]  !== null && trim($Row["german"])  !== '' ? trim($Row["german"])  : "",
        "english" => $Row["english"] !== null && trim($Row["english"]) !== '' ? trim($Row["english"]) : ""
      );
      if ($Client)   $Texts[$Row["shortcut"]]["id"] = $Row["id"];
    }

    return $Texts;
  }


  protected function getRegisDB($Regis)
  {
    if (APPDEBUG["FKT"]) $this->debug("FKT", __METHOD__);

    $Data = array(
      "regisid" => $Regis,
    );

    
    
    
    
    
    
    
    
    $SQL  = "select userid, " . $this->ident("end") . " from registrations where regisid = :regisid";
    $Rows = $this->execDB($SQL, $Data);

    
    
    if (count($Rows) != 1) {
      return array("status" => 0, "code" => "regiserror", "debug" => "registrations_row_not_found");
    }

    
    
    if (!isset($Rows[0]["end"]) || strtotime($Rows[0]["end"]) < strtotime("today")) {
      return array("status" => 0, "code" => "regiserror", "debug" => "registrations_row_expired");
    }

    $UserID = $Rows[0]["userid"];

    $Data = array(
      "id"     => $UserID,
      "active" => "n"
    );
    $SQL  = "select username, gender, title, firstname, lastname";
    $SQL .= " from users where id = :id and active = :active";
    $Rows = $this->execDB($SQL, $Data);

    if (count($Rows) == "1")  return array("regis" => $Rows[0]);

    
    
    $Rows = $this->execDB("select username, active from users where id = :id", array("id" => $UserID));
    return array(
      "status" => 0,
      "code"   => "regiserror",
      "debug"  => "user_not_found_or_not_pending",
      "debuguserid" => $UserID,
      "debuguser"   => count($Rows) == 1 ? $Rows[0] : "no user row with this id"
    );
  }

  protected function getUserDB($User)
  {
    if (APPDEBUG["FKT"]) $this->debug("FKT", __METHOD__);

    $Data = array(
      "username" => $User,
    );
    if (MODULE == "hosting") $Data["active"] = "y";
    $SQL  = "select username, clients, gender, title, firstname, lastname, mail, start, avatar";
    if (MODULE == "hosting") $SQL .= " from users        where username = :username and active = :active";
    if (MODULE == "admin")   $SQL .= " from hostingusers where username = :username";
    if (MODULE == "user")    $SQL .= " from adminusers   where username = :username";
    $Rows = $this->execDB($SQL, $Data);

    if (count($Rows) == "1") {
      $Row = $Rows[0];
      $Row["clients"] = (array) json_decode($Row["clients"]);
      sort($Row["clients"]);
      return $Row;
    }
    return [];
  }

  
  
  
  
  
  
  
  
  
  
  
  
  
  
  private function validColumn($Name, $Fields)
  {
    $Name = strtolower(trim((string) $Name));
    return isset($Fields[$Name]) ? $Name : "";
  }

  
  
  
  private function safeSorts($Sorts, $Fields)
  {
    $Out = array();
    foreach ((array) $Sorts as $Sort) {
      if (!is_string($Sort) && !is_numeric($Sort))  continue;
      $Parts = preg_split('/\s+/', trim((string) $Sort));
      $Col   = $this->validColumn($Parts[0], $Fields);
      if (!$Col)  continue;
      $Dir   = (isset($Parts[1]) && strtolower($Parts[1]) === "desc") ? " desc" : "";
      $Out[] = $this->ident($Col) . $Dir;
    }
    return $Out;
  }

  
  
  
  
  
  
  
  
  
  
  
  
  protected function readDB($Area, $Client = "", $Start = 0, $Limit = 25, $Filters = [], $Sorts = [], $Group = "", $Env = "", $Special = "", $Search = "")
  {
    $this->beginEnvDriver($Env);
    try {
      return $this->readDBRows($Area, $Client, $Start, $Limit, $Filters, $Sorts, $Group, $Env, $Special, $Search);
    } finally {
      $this->endEnvDriver();
    }
  }

  protected function readDBRows($Area, $Client = "", $Start = 0, $Limit = 25, $Filters = [], $Sorts = [], $Group = "", $Env = "", $Special = "", $Search = "")
  {
    
    
    if ( in_array($Area, array("favors", "historys", "profiles", "settings")) )
    {
      
      
      
      
      
      if ( !trim((string) $this->user) )  return ([]);

      $UserHash = $this->getUserHash($this->user);
      $SQL  = "select data from " . $Area . " where client = :client and " . $this->ident("user") . " = :user";
      $Rows = $this->execDB($SQL, array("client" => $Client, "user" => $UserHash), false, $Env);

      if ( !count($Rows) )
      {
        
        
        $LegacyHash = $this->getHash($this->user);
        $LegacyRows = $this->execDB($SQL, array("client" => $Client, "user" => $LegacyHash), false, $Env);
        if ( count($LegacyRows) == 1 )
        {
          $this->execDB("update " . $Area . " set " . $this->ident("user") . " = :newuser where client = :client and " . $this->ident("user") . " = :olduser",
                         array("newuser" => $UserHash, "client" => $Client, "olduser" => $LegacyHash), false, $Env);
          $Rows = $LegacyRows;
        }
      }

      if ( count($Rows) == 1 && isset($Rows[0]["data"]) )
      {
        return (array) json_decode($Rows[0]["data"], true);
      }
      return ([]);
    }

    

    
    $Fields = [];
    $Data   = array(
      "schema" => $this->schemaName(MODULE),
      "area"   => $Area
    );
    $SQL   = "SELECT column_name AS colname, data_type AS coltype FROM information_schema.columns where table_schema = :schema and table_name = :area";
    $Rows  = $this->execDB($SQL, $Data, false, $Env);

    foreach ($Rows as $Row) {
      $Fields[$Row["colname"]] = $Row["coltype"];
    }

    
    if (!count($Rows))  return ([]);

    $ClientsTables = array("adminusers", "users");

    $Data   = [];
    $Where  = false;
    if (MODULE != "hosting" && $Client && !in_array($Area, $ClientsTables)) $Data['client']  = $Client;

    
    $SpecialType  = "";
    $SpecialValue = "";
    if ($Special && strpos($Special, ":") !== false) {
      list($SpecialType, $SpecialValue) = explode(":", $Special, 2);
    }
    
    
    
    
    
    
    
    
    
    $HoursOverRange = in_array($SpecialType, array("monthhours", "yearhours"));

    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    foreach ($Filters as $Sp => $Wert) {
      if (is_array($Wert))  continue;
      $Typ = isset($Fields[$Sp]) ? strtolower((string) $Fields[$Sp]) : "";
      if ($Typ === "")  continue;
      if (strpos($Typ, "date") === false && strpos($Typ, "timestamp") === false)  continue;
      if (trim((string) $Wert) === "")  return ([]);
    }
    if (in_array($SpecialType, array("day", "month", "year", "monthhours", "yearhours"))
        && trim((string) $SpecialValue) === "")  return ([]);

    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    $DatumsMuster = array("day"        => '/^(\d{4}-\d{2}-\d{2})$/',
                          "month"      => '/^(\d{4}-\d{2})(?:-\d{2})?$/',
                          "monthhours" => '/^(\d{4}-\d{2})(?:-\d{2})?$/',
                          "year"       => '/^(\d{4})(?:-\d{2}){0,2}$/',
                          "yearhours"  => '/^(\d{4})(?:-\d{2}){0,2}$/');
    if (isset($DatumsMuster[$SpecialType])) {
      if (!preg_match($DatumsMuster[$SpecialType], trim((string) $SpecialValue), $DatumsTeile))  return ([]);
      $SpecialValue = $DatumsTeile[1];
    }

    $StatsAgg = (in_array($Area,["statslive", "adminstatslive"]) && in_array($SpecialType, array("day", "month", "year", "monthhours", "yearhours")));

    
    
    
    
    if (!$StatsAgg && in_array($Area, ["statslive", "adminstatslive"]) && isset($Filters["day"]) && !is_array($Filters["day"])) {
      $SpecialType  = "day";
      $SpecialValue = $Filters["day"];
      unset($Filters["day"]);
      $StatsAgg = true;
    }

    if ($StatsAgg) {
      $HourFields = [];
      for ($Hour = 0; $Hour < 24; $Hour++)  $HourFields[] = "hour_" . str_pad($Hour, 2, "0", STR_PAD_LEFT);
      if ($SpecialType == "day" || $HoursOverRange) {
        
        
        $HourSums = array_map(function ($Field) { return "sum(" . $Field . ") as " . $Field; }, $HourFields);
        
        
        $SQL  = "select category, stat, api" . ($HoursOverRange ? "" : ", day") . ", " . implode(", ", $HourSums) . " from " . $Area;
      } else {
        $SQL  = "select category, stat, api, day, sum(" . implode(" + ", $HourFields) . ") as sum from " . $Area;
      }
    } elseif ($Group == "sum") {
      $SQL  = "select count(*) as count from " . $Area;
    } else {
      $SQL  = "select * from " . $Area;
    }
    if (MODULE != "hosting" && $Client) {
      if (in_array($Area, $ClientsTables)) {
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        $SQL .= " where " . ($this->isPG() ? "clients::text" : "clients") . " like :clientlike";
        $Data["clientlike"] = "%" . $Client . "%";
      } else {
        $SQL .= " where client = :client";
      }
      $Where = true;
    }
    if (count($Filters)) {
      $Count = 0;
      foreach ($Filters as $Flid => $Filt) {
        
        
        
        $Col = $this->validColumn($Flid, $Fields);
        if (!$Col)  continue;
        if (is_array($Filt)) {
          $Ps = array();
          foreach (array_values($Filt) as $Vi => $Val) {
            $P    = "flt_" . $Count . "_" . $Vi;
            $Ps[] = ":" . $P;
            $Data[$P] = $Val;
          }
          if (!count($Ps))  continue;
          $SQL .= (!$Where ? " where " : " and ") . $this->ident($Col) . " in (" . implode(", ", $Ps) . ")";
        } else {
          $P = "flt_" . $Count;
          $SQL .= (!$Where ? " where " : " and ") . $this->ident($Col) . " = :" . $P;
          $Data[$P] = $Filt;
        }
        $Count++;
        $Where = true;
      }
    }
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    $Search = trim((string) $Search);
    if ($Search !== "") {
      $Teile = array();
      foreach ($Fields as $Sp => $Typ) {
        if ($Sp == "client")  continue;
        if (strpos($Typ, "char") === false && strpos($Typ, "text") === false)  continue;
        $Teile[] = "lower(" . $this->ident($Sp) . ") like :suchwort";
      }
      if (count($Teile)) {
        $SQL .= (!$Where ? " where " : " and ") . "(" . implode(" or ", $Teile) . ")";
        
        
        
        
        $Wort = str_replace(array("\\", "%", "_"), array("\\\\", "\\%", "\\_"), mb_strtolower($Search));
        $Data["suchwort"] = "%" . $Wort . "%";
        $Where = true;
      }
    }

    if ($Special || $StatsAgg) {
      if ($Special == "current") {
        
        
        $SQL .= (!$Where ? " where " : " and ")
              . ($this->isPG() ? " now() - interval '5 minutes' BETWEEN start AND \"end\""
                               : " NOW() - INTERVAL 5 MINUTE BETWEEN start AND end");
        $Where = true;
      } elseif ($StatsAgg && $SpecialType == "day") {
        $SQL .= (!$Where ? " where " : " and ") . "day = :specialday";
        $Data["specialday"] = $SpecialValue;
        $Where = true;
      } elseif ($StatsAgg && ($SpecialType == "month" || $SpecialType == "monthhours")) {
        
        
        
        
        
        
        
        $Von = $SpecialValue . "-01";
        $SQL .= (!$Where ? " where " : " and ") . "day >= :specialfrom and day < :specialto";
        $Data["specialfrom"] = $Von;
        $Data["specialto"]   = date("Y-m-d", strtotime($Von . " +1 month"));
        $Where = true;
      } elseif ($StatsAgg && ($SpecialType == "year" || $SpecialType == "yearhours")) {
        $SQL .= (!$Where ? " where " : " and ") . "day >= :specialfrom and day < :specialto";
        $Data["specialfrom"] = $SpecialValue . "-01-01";
        $Data["specialto"]   = ((int) $SpecialValue + 1) . "-01-01";
        $Where = true;
      }
    }

    if ($StatsAgg) {
      $SQL .= " group by category, stat, api" . ($HoursOverRange ? "" : ", day");
    }

    $SafeSorts = $this->safeSorts($Sorts, $Fields);
    if (count($SafeSorts)) {
      $SQL .= " order by " . implode(", ", $SafeSorts);
    } elseif ($StatsAgg && !$HoursOverRange) {
      $SQL .= " order by day";
    } elseif ($HoursOverRange) {
      
      
      $SQL .= " order by category, stat, api";
    }

    
    if ($StatsAgg) {
      $SQL .= " limit 200000";
    } else {
      
      
      $SQL .= $this->isPG() ? (" limit " . $Limit . " offset " . $Start)
                            : (" limit " . $Start . ", " . $Limit);
    }

    
    
    

    $Rows = $this->execDB($SQL, $Data, false, $Env);

    

    foreach ($Rows as $i => &$Row) {
      foreach ($Fields as $Field => $Type) {
        if (!isset($Rows[$i][$Field]) || !trim($Rows[$i][$Field]))  continue;

        
        if ($Type == "date") {
          
          $Rows[$i][$Field] = substr($Rows[$i][$Field], 8, 2) . "." . substr($Rows[$i][$Field], 5, 2) . "." . substr($Rows[$i][$Field], 0, 4);
        } elseif ($Type == "datetime") {
          
          $Rows[$i][$Field] = substr($Rows[$i][$Field], 8, 2)  . "." . substr($Rows[$i][$Field], 5, 2) . "." . substr($Rows[$i][$Field], 0, 4) . " "
            . substr($Rows[$i][$Field], 11, 5);
        } elseif ($this->isJsonType($Type)) {
          
          $Rows[$i][$Field] = (array) json_decode($Rows[$i][$Field], true);
        } elseif ($Type == "float") {
          
          $Rows[$i][$Field] = number_format($Rows[$i][$Field], 2, ".", "");
        } elseif ($Type == "password") {
          
          $Rows[$i][$Field] = "";
        } elseif ($Type == "blob") {
          
          $Rows[$i][$Field] = $this->DecodeArray($this->DecryptString($Rows[$i][$Field]));
        }
      }
    }

    return $Rows;
  }

  protected function updSessionDB($Session = "")
  {
    if (APPDEBUG["FKT"]) $this->debug("FKT", __METHOD__);

    if (!$Session)  $Session = $this->getUID();

    $Data = array(
      "token" => $Session,
      "start" => date("Y-m-d H:i:s"),
      "end"   => date("Y-m-d H:i:s")
    );
    if (MODULE != "hosting") $Data['client']  = $this->client;

    if (MODULE == "hosting") {
      $E = $this->ident("end");
      $SQL  = "insert into sessions (token, start, " . $E . ") values (:token, :start, :end)";
      $SQL .= $this->sqlDup($E . "=:end", array("token"));
    } else {
      $E = $this->ident("end");
      $SQL  = "insert into sessions (client, token, start, " . $E . ") values (:client, :token, :start, :end)";
      $SQL .= $this->sqlDup($E . "=:end", array("token", "client"));
    }
    $this->execDB($SQL, $Data);
  }

  protected function updDeviceDB()
  {
    if (APPDEBUG["FKT"]) $this->debug("FKT", __METHOD__);

    $User    = (isset($this->posted["login"]))               ? $this->posted["login"]               : "";
    if (!$User) $User = (isset($this->posted["userid"]))   ? $this->posted["userid"]              : "";
    $Token   = (isset($this->posted["tokens"]["device"]))    ? $this->posted["tokens"]["device"]    : "";
    $Serial  = (isset($this->posted["platform"]["serial"]))  ? $this->posted["platform"]["serial"]  : "";
    $Product = (isset($this->posted["platform"]["product"])) ? $this->posted["platform"]["product"] : "";
    if (!$User || !$Token) return;

    $Data = array(
      "user"    => $User,
      "token"   => $Token,
      "product" => $Product,
      "serial"  => $Serial,
      "start"   => date("Y-m-d H:i:s"),
      "end"     => date("Y-m-d H:i:s")
    );
    if (MODULE != "hosting")  $Data['client']  = $this->client;

    if (MODULE == "hosting") {
      $U = $this->ident("user"); $E = $this->ident("end");
      $SQL  = "insert into devices (" . $U . ", token, product, serial, start, " . $E . ") values (:user, :token, :product, :serial, :start, :end)";
      $SQL .= $this->sqlDup("product=:product, serial=:serial, " . $E . "=:end", array("user", "token"));
    } else {
      $U = $this->ident("user"); $E = $this->ident("end");
      $SQL  = "insert into devices (client, " . $U . ", token, product, serial, start, " . $E . ") values (:client, :user, :token, :product, :serial, :start, :end)";
      $SQL .= $this->sqlDup("product=:product, serial=:serial, " . $E . "=:end", array("client", "user", "token"));
    }
    $this->execDB($SQL, $Data);
  }

  protected function updPlatformDB()
  {
    if (APPDEBUG["FKT"]) $this->debug("FKT", __METHOD__);

    $Platform = (isset($this->posted["platform"])    && $this->posted["platform"]    != "") ? $this->posted["platform"]    : "";
    if ($Platform == "") return;

    
    if (isset($Platform['browser']) && trim($Platform['browser'])) {
      $this->updStatDB("Platform", "browser_" . $Platform['browser']);
    }

    
    if (isset($Platform['colordepth']) && trim($Platform['colordepth'])) {
      $this->updStatDB("Platform", "colordepth_" . $Platform['colordepth']);
    }

    if (isset($Platform['cordova']) && trim($Platform['cordova'])) {
      $this->updStatDB("Platform", "cordova_" . $Platform['cordova']);
    }

    
    if (isset($Platform['cpu']) && trim($Platform['cpu'])) {
      $this->updStatDB("Platform", "cpu_" . $Platform['cpu']);
    }

    
    if (isset($Platform['device']) && trim($Platform['device'])) {
      $this->updStatDB("Platform", "device_" . $Platform['device']);
    }

    
    if (isset($Platform['engine']) && trim($Platform['engine'])) {
      $this->updStatDB("Platform", "engine_" . $Platform['engine']);
    }

    
    if (isset($Platform['os']) && trim($Platform['os'])) {
      $this->updStatDB("Platform", "os_" . $Platform['os']);
    }

    
    if (isset($Platform['screen']) && trim($Platform['screen'])) {
      $this->updStatDB("Platform", "screen_" . $Platform['screen']);
    }

    
    if (isset($Platform['product']) && trim($Platform['product'])) {
      $this->updStatDB("Platform", "product_" . $Platform['product']);
    }
  }

  protected function updFavorsDB()
  {
    if (APPDEBUG["FKT"]) $this->debug("FKT", __METHOD__);

    if (MODULE == "hosting") {
      $U = $this->ident("user");
      $SQL  = "insert into userfavors (" . $U . ", category, categoryid, favorized, updated)";
      $SQL .= " values (:user, :category, :categoryid, :favorized, :updated)";
      $SQL .= $this->sqlDup("updated=:updated", array("user", "category", "categoryid"));
    } else {
      $U = $this->ident("user");
      $SQL  = "insert into userfavors (client, " . $U . ", category, categoryid, favorized, updated)";
      $SQL .= " values (:client, :user, :category, :categoryid, :favorized, :updated)";
      $SQL .= $this->sqlDup("updated=:updated", array("client", "user", "category", "categoryid"));
    }
    $Rows = 0;
    foreach ($this->posted["favors"] as $Favor) {
      $Tmp = explode("_", $Favor);
      if (!array_key_exists($Tmp[0], $this->structure)) continue;
      $Data = array(
        "user"       => $this->user,
        "category"   => $Tmp[0],
        "categoryid" => $Tmp[1],
        "favorized"  => date("Y-m-d H:i:s"),
        "updated"    => date("Y-m-d H:i:s")
      );
      if (MODULE != "hosting")  $Data['client']  = $this->client;
      $Rows += $this->execDB($SQL, $Data);
    }
    return ($Rows >= 0) ? true : false;
  }

  protected function delFavorDB()
  {
    if (APPDEBUG["FKT"]) $this->debug("FKT", __METHOD__);

    $SQL  = "delete from userfavors where " . $this->ident("user") . "=:user and category=:category and categoryid=:categoryid";
    if (MODULE != "hosting") {
      $SQL .= " and client=:client";
    }

    foreach ($this->posted["favordel"] as $Favor) {
      $Tmp = explode("_", $Favor);
      $Data = array(
        "user"       => $this->user,
        "category"   => $Tmp[0],
        "categoryid" => $Tmp[1]
      );
      if (MODULE != "hosting")  $Data['client']  = $this->client;
      $Rows += $this->execDB($SQL, $Data);
    }
    return ($Rows >= 0) ? true : false;
  }

  protected function updSearchesDB()
  {
    if (APPDEBUG["FKT"]) $this->debug("FKT", __METHOD__);

    if (MODULE == "hosting") {
      $U = $this->ident("user");
      $SQL  = "insert into usersearches (" . $U . ", category, text, searched, amount)";
      $SQL .= " values (:user, :category, :text, :searched, 1)";
      $SQL .= $this->sqlDup("searched=:searched, amount=usersearches.amount+1", array("user", "category", "text"));
    } else {
      $U = $this->ident("user");
      $SQL  = "insert into usersearches (client, " . $U . ", category, text, searched, amount)";
      $SQL .= " values (:client, :user, :category, :text, :searched, 1)";
      $SQL .= $this->sqlDup("searched=:searched, amount=usersearches.amount+1", array("client", "user", "category", "text"));
    }
    $Rows = 0;
    foreach ($this->posted["searches"] as $Search) {
      $Data = array(
        "user"     => $this->user,
        "category" => $Search["area"],
        "text"     => isset($Search["text"]) ? $Search["text"] : "",
        "searched" => date("Y-m-d H:i:s", strtotime($Search["date"]))
      );
      if (MODULE != "hosting")  $Data['client']  = $this->client;
      $Rows += $this->execDB($SQL, $Data);
    }
    return ($Rows >= 0) ? true : false;
  }

  protected function updSettingDB()
  {
    if (APPDEBUG["FKT"]) $this->debug("FKT", __METHOD__);

    if (MODULE == "hosting") {
      $U = $this->ident("user");
      $SQL  = "insert into usersettings (" . $U . ", category, setting, value)";
      $SQL .= " values (:user, :category, :setting, :value)";
      $SQL .= $this->sqlDup("value=:value", array("user", "category", "setting"));
    } else {
      $U = $this->ident("user");
      $SQL  = "insert into usersettings (client, " . $U . ", category, setting, value)";
      $SQL .= " values (:client, :user, :category, :setting, :value)";
      $SQL .= $this->sqlDup("value=:value", array("client", "user", "category", "setting"));
    }
    $Rows = 0;
    foreach ($this->posted["settings"] as $Cat => $Settings) {
      foreach ($Settings as $Setting => $Value) {
        $Data = array(
          "user"     => $this->user,
          "category" => $Cat,
          "setting"  => $Setting,
          "value"    => $Value
        );
        if (MODULE != "hosting")  $Data['client']  = $this->client;
        $Rows += $this->execDB($SQL, $Data);
      }
    }
    return ($Rows >= 0) ? true : false;
  }

  protected function getVersionDB()
  {
    if (APPDEBUG["FKT"]) $this->debug("FKT", __METHOD__);

    $Data = array('client'  => $this->client);
    $SQL  = "select counter, value from versions where client in ('All', :client)";

    $Rows = $this->execDB($SQL, $Data);

    $Data = [];
    foreach ($Rows as $Row) {
      $Data[$Row["counter"]] = $Row["value"];
    }

    if (!isset($Data["Main"]) || !isset($Data["Sub"]) || !isset($Data[$this->client]) || !isset($Data["ValidUntil"])) return [];

    
    

    
    return (array(
      "version"    => $Data["Main"] . "." . $Data["Sub"] . "." . $Data[$this->client],
      "validuntil" => $Data["ValidUntil"]
    ));
  }

  
  
  

  public function getBatchAreasDB($Module)
  {
    if (APPDEBUG["FKT"]) $this->debug("FKT", __METHOD__);

    $Data = array(
      "module" => $Module
    );

    $SQL  = "select area, icon, sort from " . $this->schemaOf("hosting") . ".areas";
    $SQL .= " where module = :module";
    $SQL .= " order by sort";
    $Rows = $this->execDB($SQL, $Data);

    $Areas   = [];
    foreach ($Rows as $Row) {
      $Areas[$Row["area"]] = array(
        "icon" => $Row["icon"],
        "code" => "area." . $Row["area"],
        "sort" => $Row["sort"]
      );
    }

    
    return $Areas;
  }

  public function getBatchAdminConfigsDB($Client, $Selection = [], $Complete = false)
  {
    if (APPDEBUG["FKT"]) $this->debug("FKT", __METHOD__);

    $Data = array('client'  => $Client);
    if (in_array(MODE, array("local", "development")))  $Selection[] = 'development';

    $SQL  = "select category, setting, value from " . $this->schemaOf("hosting") . ".adminconfigs where client = :client";
    if (!$Complete) $SQL .= " and category in ('" . implode("', '", $Selection) . "')";

    $Rows = $this->execDB($SQL, $Data);
    return $Rows;
  }

  public function getBatchAdminLinksDB($Client)
  {
    if (APPDEBUG["FKT"]) $this->debug("FKT", __METHOD__);

    $Data = array('client'  => $Client);
    $SQL  = "select shortcut, german, english, link from " . $this->schemaOf("hosting") . ".adminlinks where client = :client";

    
    return $this->execDB($SQL, $Data);
  }

  public function getBatchAdminTextsDB($Client, $Selection = [], $NotSelection = [], $Complete = false)
  {
    if (APPDEBUG["FKT"]) $this->debug("FKT", __METHOD__);

    $Data = array(
      "client" => $Client,
    );
    
    
    
    
    
    
    $ApplTexts = method_exists($this, "getApplBatchTextsDB")
              ? (array) $this->getApplBatchTextsDB($Selection, $NotSelection, $Complete)
              : array();

    $SQL  = "SELECT shortcut, german, english FROM " . $this->schemaOf("hosting") . ".admintexts";
    $SQL .= " WHERE client = :client";
    if (!$Complete && count($Selection))    $SQL .= " AND ( shortcut     like '" . implode("' OR shortcut like '", $Selection) . "')";
    if (!$Complete && count($NotSelection)) $SQL .= " AND ( shortcut not like '" . implode("' AND shortcut not like '", $NotSelection) . "')";
    $Rows = $this->ExecDB($SQL, $Data);

    
    
    $Texts = $ApplTexts;
    foreach ($Rows as $Row) {
      $Texts[$Row["shortcut"]] = array(
        "german"  => $Row["german"]  !== null && trim($Row["german"])  !== '' ? trim($Row["german"])  : "",
        "english" => $Row["english"] !== null && trim($Row["english"]) !== '' ? trim($Row["english"]) : ""
      );
    }
    return $Texts;
  }

  public function getBatchHostingConfigsDB()
  {
    if (APPDEBUG["FKT"]) $this->debug("FKT", __METHOD__);

    $SQL  = "select category, setting, value, name from " . $this->schemaOf("hosting") . ".configs";
    return $this->execDB($SQL);
  }

  public function getBatchHostingTextsDB()
  {
    if (APPDEBUG["FKT"]) $this->debug("FKT", __METHOD__);

    $SQL  = "SELECT shortcut, german, english FROM " . $this->schemaOf("hosting") . ".texts";
    return $this->execDB($SQL);
  }

  public function getCounterDB($Category, $Counter)
  {
    if (APPDEBUG["FKT"]) $this->debug("FKT", __METHOD__);

    if (trim($Counter) == "") return "";

    
    $this->db->begin();

    
    $Data = array(
      "category" => $Category,
      "counter"  => $Counter
    );
    if (MODULE != "hosting")  $Data['client']  = $this->client;

    if (MODULE == "hosting") {
      $SQL = "insert into counters (category, counter, value)         values (:category, :counter, 1)" . $this->sqlDup("value = counters.value + 1", array("category", "counter"));
    } else {
      $SQL = "insert into counters (client, category, counter, value) values (:client, :category, :counter, 1)" . $this->sqlDup("value = counters.value + 1", array("client", "category", "counter"));
    }
    $Res = $this->execDB($SQL, $Data);

    
    $Data = array(
      "category" => $Category,
      "counter"  => $Counter
    );
    if (MODULE != "hosting")  $Data['client']  = $this->client;

    if (MODULE == "hosting") {
      $SQL = "SELECT value from counters where category=:category and counter=:counter";
    } else {
      $SQL = "SELECT value from counters where client=:client and category=:category and counter=:counter";
    }
    $Row = $this->execDB($SQL, $Data);

    
    $this->db->commit();

    
    return ((count($Row) == 1) ? $Row[0]["value"] : "");
  }

  public function logDB($Report, $Category = "system", $Log = "-", $State = "OK", $Data = "", $Client = null)
  {
    if (APPDEBUG["FKT"]) $this->debug("FKT", __METHOD__);

    if (trim($Report) == "")  return;

    $Log = array(
      "category" => $Category,
      "log"      => $Log,
      "state"    => $State,
      "report"   => $Report,
      "data"     => json_encode($Data, true),
      "created"  => date("Y-m-d H:i:s"),
      "user"     => $this->user,
      "avatar"   => $this->avatar
    );
    
    
    
    
    
    if (MODULE != "hosting")  $Log['client']  = ($Client !== null && $Client !== "") ? $Client : $this->client;

    
    if (MODULE == "hosting") {
      $SQL  = "insert into logs (category, log, state, report, data, created, " . $this->ident("user") . ", avatar)";
      $SQL .= " values (:category, :log, :state, :report, :data, :created, :user, :avatar)";
    } else {
      $SQL  = "insert into logs (client, category, log, state, report, data, created, " . $this->ident("user") . ", avatar)";
      $SQL .= " values (:client, :category, :log, :state, :report, :data, :created, :user, :avatar)";
    }
    $this->execDB($SQL, $Log);
  }

  
  
  
  
  
  
  
  
  
  
  public function updStatDB($Category, $Stat, $Count = 1, $Client = null)
  {
    if (APPDEBUG["FKT"]) $this->debug("FKT", __METHOD__);

    if ($Category == "" || $Stat == "") return;

    
    
    
    
    
    
    
    
    
    
    
    
    $Kunde = ($Client !== null && trim((string) $Client) !== "") ? trim((string) $Client) : $this->client;
    if (MODULE != "hosting" && trim((string) $Kunde) == "") return;

    
    
    
    
    
    
    
    
    
    
    
    
    
    
    $Category = ucfirst($Category);
    if ($Category != "Platform")  $Stat = ucfirst($Stat);

    
    
    
    
    
    
    
    
    
    
    
    $APIStat = (trim((string) $this->apikey) !== "")
                 ? preg_replace("/[^a-z0-9]+/", "", strtolower($this->apikey))
                 : ((isset($this->posted["stat"]) && $this->posted["stat"] != "") ?  preg_replace("/[^a-z0-9]+/", "", strtolower($this->posted["stat"])) : "-");
    $Hour    = date('H');

    
    $StaData = array(
      "category" => $Category,
      "stat"     => $Stat,
      "day"      => date("Y-m-d"),
      "country"  => substr($this->getCountry2IP(), 0, 50),
      "api"      => substr($APIStat, 0, 50)
    );
    if (MODULE != "hosting")  $StaData['client']  = $Kunde;

    
    
    
    
    
    
    $StaData = array_merge($StaData, $this->statsExtraValuesDB($this->statsExtraColumnsDB()));

    $Keys  = array_keys($StaData);
    $Cols  = array();
    $Marks = array();
    foreach ($Keys as $Key) {
      $Cols[]  = $this->ident($Key);
      $Marks[] = ":" . $Key;
    }
    $Cols[]  = "hour_" . $Hour;
    $Marks[] = (MODULE == "hosting") ? "1" : $Count;

    $SQL = "insert into stats (" . implode(", ", $Cols) . ") values (" . implode(", ", $Marks) . ")"
         . $this->sqlDup("hour_" . $Hour . "=stats.hour_" . $Hour . "+" . $Count,
                         $this->statsUniqueKeyDB("stats", MODULE, $Keys));

    $this->execDB($SQL, $StaData);
  }

  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  private static $StatsKey = array();

  protected function statsUniqueKeyDB($Table, $Modul, $Fallback)
  {
    if (!$this->isPG())  return $Fallback;

    $Merk = $this->schemaName($Modul) . "." . $Table;
    if (isset(self::$StatsKey[$Merk]))  return self::$StatsKey[$Merk];

    
    
    
    $SQL  = "with t as (select to_regclass(:tbl) as rel),";
    $SQL .= " u as (select i.indkey from pg_index i join t on i.indrelid = t.rel";
    $SQL .= "        where i.indisunique and not i.indisprimary and i.indpred is null";
    $SQL .= "        order by i.indnatts, i.indexrelid limit 1)";
    $SQL .= " select a.attname as spalte from u cross join t";
    $SQL .= " join unnest(u.indkey) with ordinality k(att, ord) on k.att <> 0";
    $SQL .= " join pg_attribute a on a.attrelid = t.rel and a.attnum = k.att";
    $SQL .= " order by k.ord";

    $Rows = $this->execDB($SQL, array("tbl" => $this->schemaOf($Modul) . "." . $Table));

    $Key = array();
    foreach ($Rows as $Row)  $Key[] = $Row["spalte"];

    
    
    if (!count($Key))  return $Fallback;

    self::$StatsKey[$Merk] = $Key;
    return $Key;
  }

  
  
  
  
  
  
  
  
  
  
  
  
  private static $StatsExtra = array();

  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  protected function statsExtraNamesDB()
  {
    return array();
  }

  protected function statsExtraValuesDB(array $Spalten)
  {
    return array();
  }

  protected function statsExtraColumnsDB($Table = "stats", $Modul = MODULE)
  {
    $Schema = $this->schemaName($Modul);
    $Merk   = $Schema . "." . $Table;
    if (isset(self::$StatsExtra[$Merk]))  return self::$StatsExtra[$Merk];

    
    
    $Namen = $this->statsExtraNamesDB();
    if ( !count($Namen) )  return (self::$StatsExtra[$Merk] = array());

    $Platzhalter = array();
    $Daten       = array("schema" => $Schema, "table" => $Table);
    foreach ( array_values($Namen) as $i => $Name )
    {
      $Platzhalter[]        = ":n" . $i;
      $Daten["n" . $i]      = $Name;
    }

    $SQL  = "SELECT column_name AS colname FROM information_schema.columns"
          . " where table_schema = :schema and table_name = :table"
          . " and column_name in (" . implode(", ", $Platzhalter) . ")";
    $Rows = $this->execDB($SQL, $Daten);

    $Spalten = array();
    foreach ($Rows as $Row)  $Spalten[] = $Row["colname"];

    self::$StatsExtra[$Merk] = $Spalten;
    return $Spalten;
  }

  public function storeRegisDB($DataPost)
  {
    if (APPDEBUG["FKT"]) $this->debug("FKT", __METHOD__);

    
    $Regis = (isset($DataPost["regis"]) && trim($DataPost["regis"]) != "") ? trim($DataPost["regis"]) : "";
    if (!$Regis) {
      return (array("status" => -1, "code" => "storeregiserror"));
    }

    
    
    $Data = array("regisid" => $Regis);
    
    
    
    
    
    
    
    
    $SQL  = "select userid, " . $this->ident("end") . " from registrations where regisid = :regisid";
    $Rows = $this->execDB($SQL, $Data);
    if (count($Rows) != 1 || !isset($Rows[0]["end"]) || strtotime($Rows[0]["end"]) < strtotime("today")) {
      return (array("status" => -1, "code" => "storeregiserror"));
    }
    $UserID = $Rows[0]["userid"];

    $Data = array("id" => $UserID, "active" => "n");
    $SQL  = "select username from users where id = :id and active = :active";
    $Rows = $this->execDB($SQL, $Data);
    if (count($Rows) != 1) {
      return (array("status" => -1, "code" => "storeregiserror"));
    }

    
    $SQL   = "update users set firstname = :firstname, lastname = :lastname, pwhash = :pwhash, avatar = :avatar, gender = :gender, title = :title, active = :active";
    $SQL  .= " where id = :id";
    $Data  = array(
      "firstname" => $DataPost["firstname"],
      "lastname"  => $DataPost["lastname"],
      "pwhash"    => password_hash($DataPost["password"], PASSWORD_DEFAULT),
      "avatar"    => $DataPost["avatar"],
      "gender"    => $DataPost["gender"],
      "title"     => $DataPost["title"],
      "id"        => $UserID,
      "active"    => 'y'
    );

    try {
      
      $this->execDB($SQL, $Data);

      
      $this->execDB("delete from registrations where regisid = :regisid", array("regisid" => $Regis));

      return (array(
        "status" => 0,
        "code"   => "storeregissuccessful"
      ));
    } catch (\PDOException $e) {
      $err = $e->errorInfo;
      return (array(
        "status" => -1,
        "code"   => "storeregiserror",
        "more"   => $err
      ));
    }
  }

  public function storeRowDB($Area, $DataPost, $Client, $Replace = false)
  {
    if (APPDEBUG["FKT"]) $this->debug("FKT", __METHOD__);

    
    
    if ( in_array($Area, array("favors", "historys", "profiles", "settings")) )
    {
      
      
      
      if ( !trim((string) $this->user) )
      {
        return (array("status" => -1, "code" => "storeerror", "debug" => "no_user_identity"));
      }

      $UserHash = $this->getUserHash($this->user);

      
      
      $LegacyHash = $this->getHash($this->user);
      
      $U = $this->ident("user");
      $this->execDB("update " . $Area . " set " . $U . " = :newuser where client = :client and " . $U . " = :olduser",
                     array("newuser" => $UserHash, "client" => $Client, "olduser" => $LegacyHash));

      $Data = array(
                    "client" => $Client,
                    "user"   => $UserHash,
                    "data"   => json_encode($DataPost, true)
                   );

      
      
      
      
      
      if ( $this->isPG() )
      {
        $SQL  = "insert into " . $Area . " (client, " . $U . ", data) values (:client, :user, :data)";
        $SQL .= " on conflict (client, " . $U . ") do update set data = EXCLUDED.data returning id";
      }
      else
      {
        $SQL  = "insert into " . $Area . " (client, user, data) values (:client, :user, :data)";
        $SQL .= $this->sqlDup("data = :data2", array("client", "user"));
        $Data["data2"] = $Data["data"];
      }
      try
      {
        $this->execDB($SQL, $Data);
        return (array(
          "status" => 0,
          "code"   => "storesuccessful"
        ));
      }
      catch(\PDOException $e)
      {
        $err = $e->errorInfo;
        return (array(
          "status" => -1,
          "code"   => "storeerror",
          "more"   => $err
        ));
      }
    }

    

    
    $ID     = (isset($DataPost["id"])     && trim($DataPost["id"])     != "") ? trim($DataPost["id"])     : "";
    if (!$ID) unset($DataPost["id"]); 
    $FID    = (isset($DataPost["fid"])    && trim($DataPost["fid"])    != "") ? trim($DataPost["fid"])    : "";

    
    $Types = [];
    $Data  = array(
      "schema" => $this->schemaName(MODULE),
      "table"  => $Area
    );
    $SQL   = "SELECT column_name AS colname, data_type AS coltype FROM information_schema.columns where table_schema = :schema and table_name = :table";
    $Rows  = $this->execDB($SQL, $Data);
    foreach ($Rows as $Row) {
      $Types[$Row["colname"]] = $Row["coltype"];
    }

    if ($FID) {
      $Data = array(
        "type" => isset($DataPost["type"]) ? $DataPost["type"] : "",
        "fid"  => $FID
      );
      if ($Client)  $Data['client']  = $Client;
      $SQL  = "select id from " . $Area  . " where type = :type and fid = :fid";
      if ($Client)  $SQL .= " and client = :client";
      $Rows = $this->execDB($SQL, $Data);
      if (count($Rows) == 1 && isset($Rows[0]["id"])) {
        $ID = $Rows[0]["id"];
      }
      
    }

    
    if ($ID) {
      $Mode = "UPD";
      unset($DataPost["id"]);
      $Data = array(
        "id" => $ID
      );
      if ($Client)  $Data['client']  = $Client;
      $SQL  = "select * from " . $Area  . " where id = :id";
      if ($Client)  $SQL .= " and client = :client";
      $Rows = $this->execDB($SQL, $Data);

      
      
      
      
      
      
      
      
      
      
      
      
      
      
      
      
      
      if (!isset($Rows[0]) || !is_array($Rows[0])) {
        
        
        
        
        
        return (array(
          "status" => -1,
          "code"   => "storeerror",
          "debug"  => "row_not_found",
          "more"   => array("Dieser Eintrag ist nicht mehr vorhanden - er wurde"
                          . " zwischenzeitlich gelöscht oder neu angelegt."
                          . " Bitte die Seite neu laden und noch einmal speichern."
                          . " (" . $Area . ", id " . $ID . ($Client ? ", " . $Client : "") . ")"),
        ));
      }

      foreach ($Rows[0] as $Field => $Value) {
        if ($Types[$Field] == "float") {
          $Old[$Field] = number_format($Value, 2, ".", "");
        }
        
        
        
        
        
        else {
          $Old[$Field] = $Value;
        }
      }
    } else {
      $Mode = "INS";
      $Old  = [];
    }

    
    $Log  = [];
    $Data = [];
    if ($Client)  $DataPost['client']  = $Client;
    foreach ($DataPost as $Field => &$Value) {
      
      
      
      
      
      
      
      if (!isset($Types[$Field])) {
        unset($DataPost[$Field]);
        continue;
      }

      

      
      
      
      
      
      
      
      
      
      
      $IstZeitTyp = in_array($Types[$Field], array(
        "date", "datetime", "timestamp", "time",
        "timestamp without time zone", "timestamp with time zone",
        "time without time zone", "time with time zone",
      ));

      
      
      
      
      
      
      
      
      
      
      
      
      
      
      
      $IstZahlTyp = in_array($Types[$Field], array(
        "integer", "bigint", "smallint", "numeric", "decimal",
        "real", "double precision", "int", "tinyint", "mediumint",
        "float", "double",
      ));

      if (($IstZeitTyp || $IstZahlTyp) && trim((string) $Value) === "") {
        $Value = null;
      } elseif ($Types[$Field] == "date" && preg_match('/^(\d{2})\.(\d{2})\.(\d{4})$/', trim((string) $Value), $M)) {
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        $Value = $M[3] . "-" . $M[2] . "-" . $M[1];
      } elseif ($Types[$Field] == "datetime"
                && preg_match('/^(\d{2})\.(\d{2})\.(\d{4})[ T](\d{2}):(\d{2})/', trim((string) $Value), $M)) {
        
        $Value = $M[3] . "-" . $M[2] . "-" . $M[1] . " " . $M[4] . ":" . $M[5] . ":00";
      } elseif (in_array($Types[$Field], array("float", "real", "double precision", "numeric", "decimal", "double"))) {
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        if ( strpos((string) $Value, ",") !== false )
        {
          $Tmp = str_replace(",", "§§", $Value);
          $Tmp = str_replace(array(",", "."), "", $Tmp);
          $Value = str_replace("§§", ".", $Tmp);
        }
        $Value = number_format((float) $Value, 2, ".", "");
      } elseif ($Types[$Field] == "password") {
        
        $Value = strlen(trim($Value)) ? $this->getHash($Value) : "";
      } elseif ($this->isJsonType($Types[$Field])) {
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        $Roh = is_string($Value) ? trim($Value) : null;
        if ( $Roh !== null && $Roh !== "" && json_decode($Roh) !== null )
          $Value = $Roh;                      
        elseif ( $Roh !== null && strtolower($Roh) === "null" )
          $Value = "null";
        else
          $Value = json_encode($Value, true); 
      }

      
      

      
      if ($Types[$Field] != "password") {
        if (isset($Old[$Field])) {
          
          if (serialize($Old[$Field]) != serialize($Value)) {
            $Log[$Field]  = array(
              "old" => $Old[$Field],
              "new" => $Value
            );
          }
        } else {
          if ($Value) {
            $Log[$Field]  = array(
              "old" => "-",
              "new" => $Value
            );
          }
        }
      }

      
      $Data[$Field] = $Value;
    }

    

    if (!count($Data))  return [];

    
    
    
    
    
    
    
    
    if ($Area === "configs" && !$ID) {
      foreach (array("category" => "Kategorie", "setting" => "Schalter") as $Feld => $Name) {
        if (trim((string) ($Data[$Feld] ?? "")) === "") {
          return (array("status" => -1, "code" => "storeincomplete",
                        "more"   => "Bitte " . $Name . " ausfuellen - ohne sie laesst sich der Eintrag nicht zuordnen."));
        }
      }
    }

    
    if ($ID) {
      
      $SQL   = "update " . $Area  . " set ";
      $First = true;
      foreach ($Data as $Field => $Value) {
        if (!$First) $SQL .= ", ";
        $SQL .= $this->ident($Field) . " = :" . $Field;
        $First = false;
      }
      $SQL .= " where id=:id";
    } elseif ($Replace) {
      
      
      
      $SQL = $this->sqlUpsert($Area, array_keys($Data), $this->uniqueKeyOf($Area));
    } else {
      
      
      $QCols = array();
      foreach (array_keys($Data) as $F)  $QCols[] = $this->ident($F);
      $SQL = "insert into " . $Area  . " (" . implode(", ", $QCols) . ") values (:" . implode(", :", array_keys($Data)) . ")";
      if ($this->isPG())  $SQL .= " returning id";
    }

    if ($ID)  $Data["id"] = $ID;
    try {
      
      
      
      
      
      
      
      
      $this->lastDBError = "";
      $WriteResult = $this->execDB($SQL, $Data);
      if (trim((string) $this->lastDBError) !== "") {
        
        
        if ($this->istDoppelterSchluessel($this->lastDBError)) {
          return (array("status" => -1,
                        "code"   => "storeduplicate",
                        "more"   => $this->klartextDoppelterSchluessel($this->lastDBError)));
        }
        return (array("status" => -1, "code" => "storeerror", "more" => $this->lastDBError));
      }

      if (!$ID) {
        
        
        
        
        
        
        if (empty($WriteResult["newID"])) {
          return (array(
            "status" => -1,
            "code"   => "storeerror",
            "debug"  => $this->lastDBError 
          ));
        }
        
        
        if ($this->isPG()) {
          $ID = $WriteResult["newID"];
        } else {
          $SQL = "SELECT LAST_INSERT_ID() as id";
          $Row = $this->execDB($SQL);
          $ID  = $Row[0]["id"];
        }
      }

      
      if (count($Log)) {
        $this->logDB($Mode, $Area, $ID, "OK", $Log, $Client);
      }

      return (array(
        "status" => 0,
        "id"     => $ID,
        "code"   => "storesuccessful"
      ));
    } catch (\PDOException $e) {
      $err = $e->errorInfo;
      return (array(
        "status" => -1,
        "code"   => "storeerror",
        "more"   => $err
      ));
    }
  }

  public function removeRowDB($Area, $ID, $Client)
  {
    if (APPDEBUG["FKT"]) $this->debug("FKT", __METHOD__);

    

    
    $Data = array(
      "id" => $ID
    );
    if ($Client)  $Data['client']  = $Client;
    $SQL  = "select * from " . $Area  . " where id = :id";
    if ($Client)  $SQL .= " and client = :client";
    $Rows = $this->execDB($SQL, $Data);

    if (!count($Rows)) {
      return (array(
        "status" => -1,
        "code"   => "nothingtoremove"
      ));
    }

    foreach ($Rows[0] as $Field => $Value) {
      $Old[$Field] = $Value;
    }
    unset($Old["id"]);

    $Log  = [];
    foreach ($Old as $Field => $Value) {
      $Log[$Field]  = array(
        "old" => $Old[$Field],
        "new" => "-"
      );
    }

    
    
    
    if ($Area == "users" && MODULE == "hosting") {
      $this->execDB("delete from registrations where userid = :userid", array("userid" => $ID));
    }

    $Data = array("id" => $ID);
    $SQL  = "delete from " . $Area . " where id = :id";
    
    
    
    
    
    $Result = $this->execDB($SQL, $Data);
    if ($Result !== 1) {
      return (array(
        "status" => -1,
        "code"   => "removeerror"
      ));
    }

    
    $this->logDB("DEL", $Area, $ID, "OK", $Log, $Client);

    return (array(
      "status" => 1,
      "code"   => "removesuccessful"
    ));
  }

  
  
  
}
