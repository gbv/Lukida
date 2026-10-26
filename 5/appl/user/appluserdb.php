<?php

class appluserdb extends appl
{
  const MAIL_NICHT_MERKEN = array("feedback", "suggest", "notify");

  const LOOKUP_CACHE_MAXALTER = 2592000;

  public function __construct($f3)
  {
    parent::__construct($f3);
    if (APPDEBUG["FKT"]) $this->debug("FKT", __METHOD__);
  }

  public function getDialogMailDB($Type)
  {
    if (APPDEBUG["FKT"]) $this->debug("FKT", __METHOD__);

    if (!trim((string) $Type))  return "";

    $Exists = $this->ExecDB("SELECT 1 AS ok FROM information_schema.TABLES"
                          . " WHERE TABLE_SCHEMA = :schema AND TABLE_NAME = 'admindialogs'",
                            array("schema" => $this->schemaName("user")));
    if (!count($Exists))  return "";

    $Column = (MODE == "production") ? "mailprod" : "mailtest";
    $SQL    = "SELECT " . $Column . " AS mail";
    $SQL   .= " FROM " . $this->schemaOf("user") . ".admindialogs";
    $SQL   .= " WHERE client = :client AND active = :active";

    $SQL   .= $this->isPG()
            ? " AND action->>'type' = :type"
            : " AND JSON_UNQUOTE(JSON_EXTRACT(action, '$.type')) = :type";
    $Rows   = $this->ExecDB($SQL, array("client" => $this->client, "active" => "y", "type" => trim($Type)));

    return (count($Rows) == 1 && isset($Rows[0]["mail"])) ? trim($Rows[0]["mail"]) : "";
  }

  private $columncache = array();

  private function hasColumnDB($Table, $Column)
  {
    $Key = $Table . "." . $Column;
    if (isset($this->columncache[$Key]))  return $this->columncache[$Key];

    $Rows = $this->ExecDB("SELECT 1 AS ok FROM information_schema.COLUMNS"
                        . " WHERE table_schema = :schema AND table_name = :tbl AND column_name = :col",
                          array("schema" => $this->schemaName("user"),
                                "tbl"    => $Table,
                                "col"    => $Column));
    return ($this->columncache[$Key] = (count($Rows) > 0));
  }

  public function getDialogsDB()
  {
    if (APPDEBUG["FKT"]) $this->debug("FKT", __METHOD__);

    if (!$this->hasColumnDB("admindialogfields", "dialogcode"))  return array();

    $SQL  = "SELECT dialogcode, fieldcode, seq, fieldtype, fieldoptions, editable, autofill, rules";
    $SQL .= " FROM " . $this->schemaOf("user") . ".admindialogfields";
    $SQL .= " WHERE client = :client AND active = :active ORDER BY dialogcode, seq";
    $Rows = $this->ExecDB($SQL, array("client" => $this->client, "active" => "y"));

    $Dias = array();
    foreach ($Rows as $Row) {
      $Opts = (array) json_decode($Row["fieldoptions"], true);

      if ( isset($Opts["options"]) && is_array($Opts["options"]) ) {
        foreach ($Opts["options"] as &$Opt) {
          if ( isset($Opt["textcode"]) && trim($Opt["textcode"]) !== "" ) {
            $T = $this->getText($Opt["textcode"]);
            $Opt["label"] = ($T !== "") ? $T : (isset($Opt["value"]) ? $Opt["value"] : "");
          } elseif ( isset($Opt["value"]) ) {
            $Opt["label"] = $Opt["value"];
          }
        }
        unset($Opt);
      }

      $Dias[$Row["dialogcode"]]["code"]     = $Row["dialogcode"];
      $Dias[$Row["dialogcode"]]["fields"][] = array(
        "fieldcode"    => $Row["fieldcode"],
        "label"        => $this->getText($Row["fieldcode"]),
        "seq"          => (int) $Row["seq"],
        "fieldtype"    => $Row["fieldtype"],
        "fieldoptions" => $Opts,
        "editable"     => $Row["editable"] == "y",
        "autofill"     => $Row["autofill"] == "y",
        "rules"        => (array) json_decode($Row["rules"], true),
      );
    }
    return $Dias;
  }

  public function getAdminCasesDB()
  {
    if (APPDEBUG["FKT"]) $this->debug("FKT", __METHOD__);

    $Data = array(
      'client'    => $this->client,
      'active'    => 'y'
    );
    $View = "admincases";
    $Has5 = $this->hasColumnDB($View, "detailaction5");
    $SQL  = "SELECT seq, name, active, filters, light, itemcomments, itembutton, itemicon, detailwindow, detailinfos, detailbutton, detailaction, ids, comment";
    if ($Has5)  $SQL .= ", detailaction5";
    $SQL .= " FROM " . $this->schemaOf("user") . ".admincases";
    $SQL .= " WHERE client = :client and active = :active order by seq, name";
    $Rows = $this->ExecDB($SQL, $Data);

    $Cases = array();
    foreach ($Rows as $Row) {
      if (!isset($Row["seq"]))  continue;

      $Cases[$Row["seq"]][] = array(
        "name"         => $Row["name"],
        "active"       => ($Row["active"] == "y") ? true : false,
        "filters"      => json_decode($Row["filters"], true),
        "light"        => $Row["light"],
        "itemcomments" => json_decode($Row["itemcomments"], true),
        "itembutton"   => $Row["itembutton"],
        "itemicon"     => $Row["itemicon"],
        "detailwindow" => $Row["detailwindow"],
        "detailinfos"  => json_decode($Row["detailinfos"], true),
        "detailbutton" => $Row["detailbutton"],

        "detailaction" => (isset($Row["detailaction5"]) && trim($Row["detailaction5"]))
                            ? $Row["detailaction5"] : $Row["detailaction"],
        "ids"          => $Row["ids"],
        "comment"      => $Row["comment"]
      );
    }
    return $Cases;
  }

  public function getAdminCasesMediaDB()
  {
    if (APPDEBUG["FKT"]) $this->debug("FKT", __METHOD__);

    $Data = array(
      'client'    => $this->client,
      'active'    => 'y'
    );
    $View = "admincasesmedia";
    $Has5 = $this->hasColumnDB($View, "detailaction5");
    $SQL  = "SELECT seq, name, active, filters, light, itemcomments, itembutton, itemicon, detailwindow, detailinfos, detailbutton, detailaction, ids, comment";
    if ($Has5)  $SQL .= ", detailaction5";
    $SQL .= " FROM " . $this->schemaOf("user") . ".admincasesmedia";
    $SQL .= " WHERE client = :client and active = :active order by seq, name";
    $Rows = $this->ExecDB($SQL, $Data);

    $Cases = array();
    foreach ($Rows as $Row) {
      if (!isset($Row["seq"]))  continue;

      $Cases[$Row["seq"]][] = array(
        "name"         => $Row["name"],
        "active"       => ($Row["active"] == "y") ? true : false,
        "filters"      => json_decode($Row["filters"], true),
        "light"        => $Row["light"],
        "itemcomments" => json_decode($Row["itemcomments"], true),
        "itembutton"   => $Row["itembutton"],
        "itemicon"     => $Row["itemicon"],
        "detailwindow" => $Row["detailwindow"],
        "detailinfos"  => json_decode($Row["detailinfos"], true),
        "detailbutton" => $Row["detailbutton"],

        "detailaction" => (isset($Row["detailaction5"]) && trim($Row["detailaction5"]))
                            ? $Row["detailaction5"] : $Row["detailaction"],
        "ids"          => $Row["ids"],
        "comment"      => $Row["comment"]
      );
    }
    return $Cases;
  }

  public function getAdminLevelsDB()
  {
    if (APPDEBUG["FKT"]) $this->debug("FKT", __METHOD__);

    $HasHaptic = $this->hasColumnDB("adminlevels", "holdingshaptic");

    $HasSortF  = $this->hasColumnDB("adminlevels", "sortfield");
    $HasCDRem  = $this->hasColumnDB("adminlevels", "collectiondetailsrem");

    $SQL  = "SELECT level, sort, collectionsaddfull, collectionsadd, collectionsrem, holdingilns, holdingisils, locations, classlocals, buildings, dois, ipranges";
    if ($HasHaptic)  $SQL .= ", holdingshaptic";
    if ($HasSortF)   $SQL .= ", sortfield";
    if ($HasCDRem)   $SQL .= ", collectiondetailsrem";

    $SQL .= " FROM " . $this->schemaOf("user") . ".adminlevels where client=:client order by sort";
    $Data = array(
      "client" => $this->client,
    );
    $Rows = $this->ExecDB($SQL, $Data);

    $Levels = array();
    foreach ($Rows as $Row) {

      $IPRanges = json_decode((string) $Row["ipranges"], true);
      if (!is_array($IPRanges))  $IPRanges = array();

      $Levels[$Row["level"]] = array(
        "sort"               => isset($Row["sort"]) ? (int) $Row["sort"] : 100,
        "collectionsaddfull" => json_decode($Row["collectionsaddfull"], true),
        "collectionsadd"     => json_decode($Row["collectionsadd"], true),
        "collectionsrem"     => json_decode($Row["collectionsrem"], true),
        "ipranges"           => $IPRanges,
        "holdingilns"        => $Row["holdingilns"]  !== null && trim($Row["holdingilns"]) !== ''   ? explode(",", trim($Row["holdingilns"]))  : array(),
        "holdingisils"       => $Row["holdingisils"] !== null && trim($Row["holdingisils"]) !== ''   ? explode(",", trim($Row["holdingisils"])) : array(),

        "holdingshaptic"     => $HasHaptic && $Row["holdingshaptic"] !== null && trim($Row["holdingshaptic"]) !== '' ? explode(",", trim($Row["holdingshaptic"])) : array(),
        "locations"          => $Row["locations"]    !== null && trim($Row["locations"]) !== ''   ? explode(",", trim($Row["locations"]))    : array(),
        "classlocals"        => $Row["classlocals"]  !== null && trim($Row["classlocals"]) !== ''   ? explode(",", trim($Row["classlocals"]))  : array(),
        "buildings"          => $Row["buildings"]    !== null && trim($Row["buildings"])    !== ''  ? explode(",", trim($Row["buildings"]))    : array(),
        "dois"               => $Row["dois"]         !== null && trim($Row["dois"]) !== ''   ? explode(",", trim($Row["dois"]))         : array(),

        "sortfield"          => $HasSortF && $Row["sortfield"] !== null && trim($Row["sortfield"]) !== '' ? trim($Row["sortfield"]) : "",

        "collectiondetailsrem" => $HasCDRem ? (is_array($X = json_decode((string) $Row["collectiondetailsrem"], true)) ? $X : array()) : array()
      );
    }
    return $Levels;
  }

  public function getMaintenanceDB()
  {
    if (APPDEBUG["FKT"]) $this->debug("FKT", __METHOD__);

    $SQL  = "SELECT bmsid, options FROM " . $this->schemaOf("user") . ".adminbms";
    $SQL .= " WHERE client = :client";
    if (MODE == "production") $SQL .= " AND testonly = 'n'";
    $SQL .= " ORDER BY bmsid";

    $Rows = $this->ExecDB($SQL, array("client" => $this->client));

    $Out = array();
    foreach ($Rows as $Row) {
      $Opt = json_decode((string) $Row["options"], true);
      if (!is_array($Opt) || !isset($Opt["maintenance"]) || !is_array($Opt["maintenance"])) continue;
      $Out[(string) $Row["bmsid"]] = $Opt["maintenance"];
    }
    return $Out;
  }

  public function getAdminBMSDB()
  {
    if (APPDEBUG["FKT"]) $this->debug("FKT", __METHOD__);

    $Data = array('client'    => $this->client);
    $SQL  = "SELECT bmsid, namecode, type, isil, iln, apitest, apiprod, options, logins";
    $SQL .= " FROM " . $this->schemaOf("user") . ".adminbms";
    $SQL .= " WHERE client = :client";
    if (MODE == "production") $SQL .=  " and testonly = 'n'";

    $Data = array(
      "client" => $this->client,
    );
    $Rows = $this->ExecDB($SQL, $Data);

    $BMS = array();
    foreach ($Rows as $Row) {
      $BMS[$Row["bmsid"]] = array(
        "namecode" => $Row["namecode"],
        "type"     => $Row["type"],
        "isil"     => $Row["isil"],
        "iln"      => $Row["iln"],
        "apitest"  => $Row["apitest"],
        "apiprod"  => $Row["apiprod"],
        "options"  => json_decode($Row["options"], true)
      );
      if (in_array(MODE, array("local", "development"))) $BMS[$Row["bmsid"]] += array("logins"   => json_decode($Row["logins"], true));
    }
    return $BMS;
  }

  public function getAdminLinksDB()
  {
    if (APPDEBUG["FKT"]) $this->debug("FKT", __METHOD__);

    $SQL  = "SELECT shortcut, german, english, link FROM " . $this->schemaOf("user") . ".adminlinks";
    $SQL .= " WHERE client = :client";

    $Data = array(
      "client" => $this->client,
    );
    $Rows = $this->ExecDB($SQL, $Data);

    $Links = array();
    foreach ($Rows as $Row) {
      $Tmp = (isset($Row["link"]) && trim($Row["link"])) ? explode(",", trim($Row["link"])) : array();
      if (!isset($Tmp[0]))  continue;
      if (!isset($Tmp[1]))  $Tmp[1] = $Tmp[0];
      $Links[$Row["shortcut"]] = array(
        "german"      => $Row["german"]  !== null && trim($Row["german"])  !== '' ? trim($Row["german"])  : "",
        "germanlink"  => $Tmp[0],
        "english"     => $Row["english"] !== null && trim($Row["english"]) !== '' ? trim($Row["english"]) : "",
        "englishlink" => $Tmp[1]
      );
    }
    return $Links;
  }
}
