<?php

class user extends appluserdb
{
  public $bmscon;
  public $cases;
  public $casesmedia;
  public $collections;
  public $hierarchy;
  public $ilngroups;
  public $bms;
  public $bmsid;
  public $levels;
  public $links;
  public $locations;

  public $params;

  public $items;
  public $logs;
  public $search;
  public $works;

  public $results;

  public $combs;
  public $display;
  public $paias;

  public function __construct($f3)
  {
    parent::__construct($f3);

    if (APPDEBUG["FKT"]) $this->debug("FKT", __METHOD__);

    $this->cases       = array();
    $this->collections = array();
    $this->hierarchy   = array("childs" => array(), "parents" => array());
    $this->ilngroups   = array();
    $this->items       = array();
    $this->bms         = array();
    $this->bmsid       = 1;
    $this->bmscon      = (object) array();
    $this->levels      = array();
    $this->links       = array();
    $this->locations   = array();
    $this->logs        = array();
    $this->params      = array();
    $this->results     = array();
    $this->search      = array();
    $this->works       = array();

    $this->combs       = array();
    $this->display     = array();
    $this->paias       = array();

    $this->getConfig();

    $this->getLevels();

    $this->getCases();

    $this->getCasesMedia();

    $this->getBMS();

    $this->getLinks();

    $this->getCollections();

    $this->getLocations();

    $this->bmsid = $this->checkPostedStr("bms");
    if ($this->bmsid == "") {
      $this->bmsid = $this->checkDecryptedPostedStr("bms");
      if ($this->bmsid == "") {
        $this->bmsid = 1;
      }
    }
  }

  private function checkUser()
  {
    if (APPDEBUG["FKT"]) $this->debug("FKT", __METHOD__);

    $Display = array();
    $Display["user"]     = (isset($this->posted["display"]["user"])  && is_bool($this->posted["display"]["user"])  && $this->posted["display"]["user"])  ? true : false;
    $Display["items"]    = (isset($this->posted["display"]["items"]) && is_bool($this->posted["display"]["items"]) && $this->posted["display"]["items"]) ? true : false;
    $Display["fees"]     = (isset($this->posted["display"]["fees"])  && is_bool($this->posted["display"]["fees"])  && $this->posted["display"]["fees"])  ? true : false;
    $this->display       = $Display;

    if (!$this->setBMS()) return array();

    $User = isset($this->posted["login"]) ? $this->posted["login"] : "";
    $Pass = isset($this->posted["pw"])    ? $this->posted["pw"]    : "";

    $this->results = $this->bmscon->checkUser($User, $Pass);

    if (isset($this->results["stat"]) && $this->results["stat"] == "error") return $this->results;

    if (isset($this->results["user"]["name"])) {
      $this->results["user"]["changepw"]  = (isset($this->configs["bms"]["changepassword"])    && $this->configs["bms"]["changepassword"]    == "y") ? true : false;
      $this->results["user"]["cancelres"] = (isset($this->configs["bms"]["cancelreservation"]) && $this->configs["bms"]["cancelreservation"] == "y") ? true : false;
    }

    $DisplayGroups  = array();
    $PAIAGroups     = array();
    foreach (array("usercollectables", "userorders", "userrentals", "userreservations") as $AccGrp) {
      if (
        isset($this->configs["account"][$AccGrp . ".show"])    && $this->configs["account"][$AccGrp . ".show"]    == "y"
        && isset($this->configs["account"][$AccGrp . ".pos"])     && $this->configs["account"][$AccGrp . ".pos"]     != ""
        && isset($this->configs["account"][$AccGrp . ".paiaids"]) && $this->configs["account"][$AccGrp . ".paiaids"] != ""
      ) {
        $DisplayGroups[$this->configs["account"][$AccGrp . ".pos"]] = $AccGrp;
        $Ps = explode(",", $this->configs["account"][$AccGrp . ".paiaids"]);
        foreach ($Ps as $P) {
          $PAIAGroups[$P] = $AccGrp;
        }
      }
    }

    ksort($DisplayGroups);
    $DisplayGroups = array_values($DisplayGroups);
    $FindGroups     = array_flip($DisplayGroups);

    $Items          = array();
    foreach ($DisplayGroups as $Key => $Val) {

      $Items["group_" . $Key] = array("label" => $this->texts["account." . $Val] ?? ("account." . $Val), "code" => $Val, "items" => array());
    }

    if (isset($this->results["items"])  && count($this->results["items"])) {
      $PItems = $this->results["items"];

      foreach ($PItems as $Ind => $Itm) {
        if (!isset($Itm["endtime"]))  $PItems[$Ind]["endtime"] = "";
      }
      array_multisort(array_column($PItems, 'endtime'), SORT_DESC, $PItems);

      foreach ($PItems as $I => $Item) {
        if (
          !isset($Item["status"])
          || !isset($PAIAGroups[$Item["status"]])
          || !isset($FindGroups[$PAIAGroups[$Item["status"]]])
        )  continue;

        $Group = $FindGroups[$PAIAGroups[$Item["status"]]];
        $SID   = substr($Item["storageid"], strpos($Item["storageid"], "@") + 1);
        $Label = isset($Item["label"]) ?  $Item["label"] : "";
        if (strpos($Label, "::") !== false)  $Label = substr($Label, strpos($Label, "::") + 1);
        if (strpos($Label, ":")  !== false)  $Label = substr($Label, strpos($Label, ":") + 1);

        $Tmp = array(
          "id"      => isset($Item["item"])  ? $Item["item"]  : "",
          "title"   => isset($Item["about"]) ? $Item["about"] : "",
          "label"   => $Label,
          "status"  => array(
            "start"       => isset($Item["starttime"]) && trim($Item["starttime"]) ? $this->format2German($Item["starttime"]) : "",
            "end"         => isset($Item["endtime"])   && trim($Item["endtime"])   ? $this->format2German($Item["endtime"])   : "",
            "queue"       => isset($Item["queue"])    ? $Item["queue"]    : "-",
            "renewals"    => isset($Item["renewals"]) ? $Item["renewals"] : "0",
            "maxrenewals" => intval($this->configs["bms"]["maxrenewals"]),
            "reminder"    => isset($Item["reminder"]) ? $Item["reminder"] : "0",
            "state"       => $Item["status"]
          ),
          "storage" => array(
            "name"        => isset($Item["storage"])  ? $Item["storage"]  : "",
            "id"          => $SID,
            "label"       => isset($this->texts["DESK" . substr($SID, 8)]) ? $this->texts["DESK" . substr($SID, 8)] : ""
          ),
          "actions"  => array(
            "cancel"     => isset($Item["cancancel"]) ? $Item["cancancel"] : false,
            "renew"      => isset($Item["canrenew"]) ? $Item["canrenew"] : false
          )
        );
        if (isset($Item["canrenew"]) && $Item["canrenew"] && isset($Item["renewals"]) && $Item["renewals"] <= intval($this->configs["bms"]["maxrenewals"])) {
          $Tmp["actions"]["renew"]  = array(
            "type"    => "renew",
            "id"      => isset($Item["item"])  ? $Item["item"]  : "",
            "login"   => "<login>"
          );
        }
        if (isset($Item["cancancel"]) && $Item["cancancel"]) {
          $Tmp["actions"]["cancel"] = array(
            "type"    => "cancel",
            "id"      => isset($Item["item"])  ? $Item["item"]  : "",
            "login"   => "<login>"
          );
        }
        $Items["group_" . $Group]["items"][] = $Tmp;
      }
    }
    $this->results["items"] = $Items;

    if (isset($this->results["fees"]["items"]) && is_array($this->results["fees"]["items"]) && count($this->results["fees"]["items"])) {
      foreach ($this->results["fees"]["items"] as $I => $Item) {
        $this->results["fees"]["items"][$I]["date"] = $this->format2German($Item["date"]);
      }
    }

    if (
      isset($this->configs["account"]["userotherorders.show"])
      && $this->configs["account"]["userotherorders.show"] == "y"
    ) {
      $Mails = $this->getMailDB($this->results["login"]);
      $this->results["otherorders"] = array(

        "label" => $this->texts["account.userotherorders"] ?? "account.userotherorders",
        "code"  => "userotherorders",
        "items" => $Mails
      );
    }
    return $this->results;
  }

  private function doActionLocal($Type, $ID, $Data, $DialogMail = "")
  {
    if (APPDEBUG["FKT"]) $this->debug("FKT", __METHOD__);

    $File                    = dirname(__FILE__) . DIRECTORY_SEPARATOR . "clients"
      . DIRECTORY_SEPARATOR . $this->client
      . DIRECTORY_SEPARATOR . "action.php";
    $Status = "";
    $Code   = "";
    $More   = "";
    if (file_exists($File)) {

      include $File;
    }

    if ($Status === "") {
      return $this->sendDialogMail($Type, $ID, $Data, $DialogMail);
    }

    return array("status" => $Status, "code" => $Code, "more" => $More);
  }

  private function sendDialogMail($Type, $ID, $Data, $DialogMail = "")
  {
    if (APPDEBUG["FKT"]) $this->debug("FKT", __METHOD__);

    $Mode = (MODE == "production") ? "prod" : "test";
    $Tos  = array();
    if (trim((string) $DialogMail)) {
      $Tos = explode(",", trim($DialogMail));
    } elseif (isset($this->configs["mail." . $Mode][$Type]) && trim($this->configs["mail." . $Mode][$Type])) {
      $Tos = explode(",", trim($this->configs["mail." . $Mode][$Type]));
    }

    if (!count($Tos)) {

      if ( $this->nurEinmalProStunde("dialogmail|" . $this->client . "|" . $Type) )
      {
        $this->sendErrorMail(array(
          "subject" => "Config-Mail-Missing (" . $this->client . ")",
          "message" => "Dialog \"" . $Type . "\": weder eine Adresse in dialogs.mail" . $Mode
                     . " noch mail." . $Mode . "." . $Type . " in der Konfiguration."
        ));
      }
      return array("status" => -1, "code" => "mailerror", "more" => "no target");
    }

    $User    = isset($Data["user"]) ? $Data["user"] : array();
    $Fields  = $Data;
    unset($Fields["user"]);

    $Subject = $this->getText("mail.subject." . $Type);
    if (!$Subject || $Subject == "mail.subject." . $Type)  $Subject = $this->getText("bibline.subject");
    if (!$Subject)  $Subject = "Anfrage";

    $SchonDa = array();
    foreach ($Fields as $Wert) {
      $SchonDa[] = trim((string) (is_array($Wert) ? implode(", ", $Wert) : $Wert));
    }

    $Msg  = "<table border='1' cellpadding='4' cellspacing='0'>";

    if ($ID !== "") {
      $Msg .= "<tr><td>Titel</td><td>" . $this->getMarcText($ID, "bib_01") . "</td></tr>";

      $In = trim((string) $this->getMarcIntern($ID, "int_100", "journal"));
      if ($In !== "") {
        $LabelIn = $this->getText("bibline.in");
        $Msg .= "<tr><td>" . ($LabelIn ? $LabelIn : "In") . "</td><td>" . htmlspecialchars($In) . "</td></tr>";
      }

      $Sig = trim((string) $this->checkDecryptedPostedStr("signature"));
      if ($Sig !== "" && !in_array($Sig, $SchonDa, true)) {
        $LabelSig = $this->getText("items.signature");
        $Msg .= "<tr><td>" . ($LabelSig ? $LabelSig : "Signatur") . "</td><td>" . htmlspecialchars($Sig) . "</td></tr>";
      }

      $Msg .= "<tr><td>ID</td><td>" . $ID . "</td></tr>";
    }
    foreach (array("username", "name", "email") as $Key) {
      if (isset($User[$Key]) && trim((string) $User[$Key])) {
        $Msg .= "<tr><td>" . $this->getText("user." . $Key) . "</td><td>" . $User[$Key] . "</td></tr>";
      }
    }
    foreach ($Fields as $Key => $Val) {
      if (is_array($Val))  $Val = implode(", ", $Val);
      if (!trim((string) $Val))  continue;
      $Label = $this->getText($Key);
      $Msg  .= "<tr><td>" . ($Label ? $Label : $Key) . "</td><td>" . htmlspecialchars((string) $Val) . "</td></tr>";
    }
    $Msg .= "</table>";

    $Names = array();
    foreach ($Tos as $To)  $Names[] = ucfirst(explode("@", $To)[0]);

    $Titel = ($ID !== "") ? trim($this->getMarcText($ID, "bib_01")) : "";
    if ($Titel === "" && isset($Fields["title"]))  $Titel = trim((string) $Fields["title"]);

    $Result = $this->DoSendMail($Names, $Tos, $Subject, $Msg, $Type,
                                ($Titel !== "") ? array("title" => $Titel) : array());
    if (!isset($Result["status"]) || $Result["status"] < 0) {
      return array("status" => -1, "code" => "mailerror", "more" => "");
    }

    $this->updStatDB("Action", "Dialog|" . $Type);
    return array("status" => 0, "code" => "mailsuccessful", "more" => "");
  }

  private function formatSolrItemColl($Item)
  {
    $Item = urlencode($Item);
    return $Item;
  }

  private function formatSolrItem($Item)
  {
    return str_replace(
      array("%",  ":",    " ",  "-",  "/"),
      array("%25", "\:", "\%20", "\-", "\/"),
      $Item
    );
  }

  private function getCases()
  {
    if (APPDEBUG["FKT"]) $this->debug("FKT", __METHOD__);

    $File = $this->prepareCachePath(array("settings"), "cases");
    if (file_exists($File)) {
      if (MODE != "production") $this->updStatDB("Cache", "Cases.yes");
      $this->cases = json_decode(file_get_contents($File), true);
    } else {

      if (MODE != "production") $this->updStatDB("Cache", "Cases.no");
      $this->cases  = $this->getAdminCasesDB();

      file_put_contents($File, json_encode($this->cases, true));
    }
  }

  private function getCasesMedia()
  {
    if (APPDEBUG["FKT"]) $this->debug("FKT", __METHOD__);

    $File = $this->prepareCachePath(array("settings"), "casesmedia");
    if (file_exists($File)) {
      if (MODE != "production") $this->updStatDB("Cache", "Casesmedia.yes");
      $this->casesmedia = json_decode(file_get_contents($File), true);
    } else {

      if (MODE != "production") $this->updStatDB("Cache", "casesmedia.no");
      $this->casesmedia  = $this->getAdminCasesMediaDB();

      file_put_contents($File, json_encode($this->casesmedia, true));
    }
  }

  private function getCollections()
  {
    if (APPDEBUG["FKT"]) $this->debug("FKT", __METHOD__);

    $File = $this->prepareCachePath(array("settings"), "collections");
    if (file_exists($File)) {
      if (MODE != "production") $this->updStatDB("Cache", "Collections.yes");
      $this->collections = json_decode(file_get_contents($File), true);
    } else {

      if (MODE != "production") $this->updStatDB("Cache", "Collections.no");
      $this->collections  = $this->getCollectionsDB();
      file_put_contents($File, json_encode($this->collections, true));
    }
  }

  private function getLocations()
  {
    if (APPDEBUG["FKT"]) $this->debug("FKT", __METHOD__);

    $Mode = strtolower(substr(MODE, 0, 4)) == "prod" ? "prod" : "test";

    $File = $this->prepareCachePath(array("settings"), "locations");
    if ( file_exists($File) )
    {
      if (MODE != "production") $this->updStatDB("Cache", "Locations.yes");
      $Tmp = (string) file_get_contents($File);
    }
    else
    {
      if (MODE != "production") $this->updStatDB("Cache", "Locations.no");
      $Tmp = trim((string) $this->getLocationsDB($this->client, $Mode));
      if ( $Tmp === "" )
      {
        $Tmp = isset($this->configs["import." . $Mode]["loc.data"])
          && trim($this->configs["import." . $Mode]["loc.data"])
          ? $this->configs["import." . $Mode]["loc.data"] : "";
      }
      file_put_contents($File, $Tmp);
    }

    $this->locations = ($Tmp) ? json_decode($Tmp, true) : [];
  }

  private function getConfig()
  {
    $this->configs["mycolls"] = (isset($this->configs["other"]["mycollections"])) ? (array) json_decode($this->configs["other"]["mycollections"], true) : array();

    if ( isset($this->configs["hosting"]["kisuggest"]) && $this->configs["hosting"]["kisuggest"] == "y" )
    {
      if ( !isset($this->clientconfigs["project"]) || !is_array($this->clientconfigs["project"]) ) $this->clientconfigs["project"] = array();
      $this->clientconfigs["project"]["kisuggest"] = "y";
    }

    $this->clientconfigs["account"]["wallet.show"]      = "n";
    $this->clientconfigs["account"]["walletapple.show"] = "n";

    $this->configs["ilnmans"] = (isset($this->configs["item"]["ilnlist"])  && trim($this->configs["item"]["ilnlist"]))
      ? explode(",", $this->configs["item"]["ilnlist"])
      : array();

    $this->configs["isils"]   = (isset($this->configs["item"]["isillist"]) && trim($this->configs["item"]["isillist"]))
      ? explode(",", $this->configs["item"]["isillist"])
      : array();

    $this->configs["ilns"]     = array();
    foreach ($this->configs["ilnmans"] as $ILNMAN) {
      if (($Pos = stripos($ILNMAN, "_")) !== false) {
        $this->configs["ilns"][] = substr($ILNMAN, 0, $Pos);
      } else {
        $this->configs["ilns"][] = $ILNMAN;
      }
    }

    $this->configs["ilnandman"] = array();
    foreach ($this->configs["ilnmans"] as $ILNMAN) {
      if (($Pos = stripos($ILNMAN, "_")) !== false) {
        $this->configs["ilnandman"][substr($ILNMAN, 0, $Pos)][] = substr($ILNMAN, $Pos + 1);
      } else {
        $this->configs["ilnandman"][$ILNMAN] = array();
      }
    }

    if (!$this->lang) {
      if (
        isset($this->configs["init"]["language"]) && trim($this->configs["init"]["language"])
        && in_array(trim($this->configs["init"]["language"]), array("e", "g"))
      ) {
        $this->lang = trim($this->configs["init"]["language"]);
      } else {
        $this->lang = "g";
      }
    }
  }

  public function getClientName()
  {
    $Name  = isset($this->configs["client"]["clientname"]) ? (string) $this->configs["client"]["clientname"] : "";
    $Teile = array_map("trim", explode(",", $Name, 2));
    if ($this->lang == "e" && isset($Teile[1]) && $Teile[1] !== "") return $Teile[1];
    return $Teile[0];
  }

  private function getLevels()
  {
    if (APPDEBUG["FKT"]) $this->debug("FKT", __METHOD__);

    $File = $this->prepareCachePath(array("settings"), "levels");
    if (file_exists($File)) {
      if (MODE != "production") $this->updStatDB("Cache", "Levels.yes");
      $this->levels = json_decode(file_get_contents($File), true);
    } else {

      if (MODE != "production") $this->updStatDB("Cache", "Levels.no");
      $this->levels  = $this->getAdminLevelsDB();
      file_put_contents($File, json_encode($this->levels, true));
    }
  }

  private function getBMS()
  {
    if (APPDEBUG["FKT"]) $this->debug("FKT", __METHOD__);

    $File = $this->prepareCachePath(array("settings"), "bms");
    if (file_exists($File)) {
      if (MODE != "production") $this->updStatDB("Cache", "BMS.yes");
      $this->bms = json_decode(file_get_contents($File), true);
    } else {

      if (MODE != "production") $this->updStatDB("Cache", "BMS.no");
      $this->bms  = $this->getAdminBMSDB();
      file_put_contents($File, json_encode($this->bms, true));
    }

    $BMSList = array();
    foreach ($this->bms as $bmsid => $l) {
      $Label = isset($l["namecode"]) && $l["namecode"] ? $this->getText($l["namecode"]) : "";
      if (!$Label)  $Label = isset($l["namecode"]) ? $l["namecode"] : (string) $bmsid;
      $BMSList[] = array("bmsid" => (string) $bmsid, "label" => $Label);
    }

    $this->configs["bmslist"] = $BMSList;

    $this->clientconfigs["bmslist"] = $BMSList;

    $Dialogs = $this->getDialogsDB();
    $this->configs["dialogs"]       = $Dialogs;
    $this->clientconfigs["dialogs"] = $Dialogs;

    foreach ($this->bms as $bmsid => $l) {
      if (isset($l["iln"])) {
        $this->configs["iln2bms"][$l["iln"]][$bmsid] = array(
          "bmsid"    => $bmsid,
          "db"       => isset($l["options"]["db"]) ? $l["options"]["db"] : "",
          "isil"     => isset($l["isil"])          ? $l["isil"]          : "",
          "namecode" => isset($l["namecode"])      ? $l["namecode"]      : ""
        );
      }
    }
  }

  public function getMaintenance()
  {
    if (APPDEBUG["FKT"]) $this->debug("FKT", __METHOD__);

    $Jetzt = time();
    $Out   = array();

    foreach ($this->getMaintenanceDB() as $BmsID => $W) {
      if (!isset($W["state"]) || $W["state"] != "window") continue;

      $Von = isset($W["from"]) ? strtotime((string) $W["from"]) : false;
      $Bis = isset($W["to"])   ? strtotime((string) $W["to"])   : false;
      if (!$Von || !$Bis || $Jetzt < $Von || $Jetzt > $Bis) continue;

      $Out[] = array(
        "bmsid"  => (string) $BmsID,
        "from"   => (string) $W["from"],
        "to"     => (string) $W["to"],
        "text_g" => isset($W["text_g"]) ? (string) $W["text_g"] : "",
        "text_e" => isset($W["text_e"]) ? (string) $W["text_e"] : "",
      );
    }
    return $Out;
  }

  public function getMessages()
  {
    if (APPDEBUG["FKT"]) $this->debug("FKT", __METHOD__);

    return $this->getMessagesDB();
  }

  private function getLinks()
  {
    if (APPDEBUG["FKT"]) $this->debug("FKT", __METHOD__);

    $File = $this->prepareCachePath(array("settings"), "links");
    if (file_exists($File)) {
      if (MODE != "production") $this->updStatDB("Cache", "Links.yes");
      $this->links = json_decode(file_get_contents($File), true);
    } else {

      if (MODE != "production") $this->updStatDB("Cache", "Links.no");
      $this->links  = $this->getAdminLinksDB();
      file_put_contents($File, json_encode($this->links, true));
    }
  }

  private function getUser()
  {
    if (APPDEBUG["FKT"]) $this->debug("FKT", __METHOD__);

    $Login = isset($this->posted["login"])          ? $this->posted["login"]          : "";
    $Token = isset($this->posted["tokens"]["user"]) ? $this->posted["tokens"]["user"] : "";

    $Display = array();
    $Display["user"]     = true;
    $this->display       = $Display;

    if (!$this->setBMS()) return array();

    $this->results = $this->bmscon->ReadUser($Login, $Token);
    if (count($this->results) == 0) return array();

    return $this->results;
  }

  private function checkDeliveryUserType($Dialog, $User)
  {
    if (APPDEBUG["FKT"]) $this->debug("FKT", __METHOD__);

    if (!in_array($Dialog, array("delivery", "delivery2"))) return true;

    $Switch = isset($this->configs["item"]["delivery.usertypescheck"])
              ? strtolower(trim($this->configs["item"]["delivery.usertypescheck"]))
              : "";
    if ($Switch != "y") return true;

    $Allowed = isset($this->configs["item"]["delivery.usertypes"])
               ? array_values(array_filter(array_map("trim", explode(",", $this->configs["item"]["delivery.usertypes"])), "strlen"))
               : array();
    if (!count($Allowed)) return true;

    $Type = isset($User["type"]) ? $User["type"] : "";
    if (is_array($Type)) $Type = isset($Type[0]) ? $Type[0] : "";
    $Type = trim((string) $Type);
    if ($Type === "") return true;

    $Parts = explode(":", $Type);
    $Group = trim((string) end($Parts));

    if (!in_array($Group, $Allowed, true)) {
      $this->updStatDB("Action", "Delivery|notauthorized");
      if (APPDEBUG["LOG"]) $this->debug("LOG", "Delivery denied for user type '" . $Group . "'");
      return false;
    }
    return true;
  }

  private function readConvertFindex($Add = false, $Prefix = "M")
  {
    $Solr = new solr($this);
    $Solr->getSearch($Add);

    $Marc = new marc($this);
    $Marc->getConverts($Add, $Prefix);
  }

  private function searchControl()
  {
    $SType  = $this->checkPostedStr("search", "type") == "lukida" ? "lukida" : "findex";
    $SQuery = $this->checkPostedStr("search", "query");
    $EPN    = $this->checkPostedStr("epn");

    if ($SType == "lukida") {

      $SQuery .= " ";

      $BracketsRegex = "/([A-Za-z0-9]+)(?:\((?P<paren>[^()]+)\)"
                     . "|:(?P<colon>\"[^\"]*\"|'[^']*'|[A-Za-z0-9_\-,*]+))\s/";
      preg_match_all($BracketsRegex, $SQuery, $IntComs);

      if (isset($IntComs[1]) && is_array($IntComs[1]) && count($IntComs[1])) {
        foreach ($IntComs[1] as $Idx => $IC) {
          if ($IC == "id") {

            $Roh = (string) ($IntComs[2][$Idx] ?? "");
            if ( $Roh === "" )  $Roh = trim((string) ($IntComs[3][$Idx] ?? ""), "\"'");
            $IDs    = ($Roh !== "") ? explode(",", $Roh) : array();
            $IDs    = array_filter(array_unique($IDs));
            $NewIDs = array();
            foreach ($IDs as &$ID) {
              $ID = trim($ID);
              if (!$this->readCache2Marc($ID)) {
                if (MODE != "production") $this->updStatDB("Cache", "Marc.no");
                $NewIDs[] = $ID;
              }
            }

            $SQuery = trim(str_replace(
              $IntComs[0][$Idx],
              count($NewIDs) ? "id(" . implode(",", $NewIDs) . ")" : "",
              $SQuery
            ));
          }
        }

        $this->search["search"]["query"] = trim($SQuery);
      }
    }

    if (trim((string) ($this->search["search"]["query"] ?? ""))) $this->readConvertFindex();

    if (isset($this->hierarchy["childs"]) && is_array($this->hierarchy["childs"]) && count($this->hierarchy["childs"])) {
      $Parents = array();

      foreach ($this->hierarchy["childs"] as $PID) {
        if (!$this->readCache2Marc($PID, true)) {
          $Parents[] = $PID;
        }
      }

      $Parents = array_filter(array_unique(array_values($Parents)));

      if (count($Parents)) {
        $this->search = array(
          "search"   => array(
            "query" => "id(" . implode(", ", $Parents) . ")",
            "type"     => "lukida"
          ),
          "start"    => 0,
          "limit"    => count($Parents),
          "sort"     => "",
          "spell"    => false,
          "phonetic" => false,
          "facets"   => array(),
          "url"      => $this->buildBasicURL("all"),
          "counts"   => array()
        );

        $this->readConvertFindex(true, "PM");
      }
    }

    $Step = (isset($this->config["items"]["journalssteppeditems"]) && trim($this->config["items"]["journalssteppeditems"]) == "y") ? true : false;

    if (isset($this->display["items"]) && $this->display["items"]) {
      if (isset($this->works["marc"]) && is_array($this->works["marc"]) && count($this->works["marc"])) {
        if (!$EPN) {

          $APS  = (isset($this->configs["item"]["articleparentshift"])   && trim($this->configs["item"]["articleparentshift"])   == "y") ? true : false;

          $AWOP = (isset($this->configs["item"]["articleswithoutparents"]) && trim($this->configs["item"]["articleswithoutparents"]) == "y") ? true : false;
          $JWI  = (isset($this->configs["item"]["journalswithoutitems"]) && trim($this->configs["item"]["journalswithoutitems"]) == "y") ? true : false;

          $IlnBms = array();
          $Vorrat = array();
          foreach ($this->works["marc"] as $VID => $VOne) {
            if (!isset($VOne["ilngroups"]) || !is_array($VOne["ilngroups"]))  continue;
            foreach ($VOne["ilngroups"] as $VILN => $VEPNs) {
              if (!isset($this->configs["iln2bms"][$VILN]))  continue;
              if (!array_key_exists($VILN, $IlnBms))  $IlnBms[$VILN] = $this->setBMS($VILN);
              if (!$IlnBms[$VILN])  continue;
              $VBMS = $IlnBms[$VILN];
              if (!isset($Vorrat[$VBMS]))  $Vorrat[$VBMS] = array("iln" => $VILN, "map" => array());
              foreach ((array) $VEPNs as $VEPN) {
                $Vorrat[$VBMS]["map"][$VID][] = $VEPN;
              }
            }
          }
          foreach ($Vorrat as $VBMS => $VEins) {
            if (count($VEins["map"]) < 2)  continue;
            if ($this->setBMS($VEins["iln"]) && $this->bmscon) {
              $this->bmscon->prepareItems($VEins["map"]);
            }
          }

          foreach ($this->works["marc"] as $ID => $One) {
            $this->params["shift"] = false;

            if (

              $APS && !$AWOP && isset($this->hierarchy["childs"][$ID])
              && isset($this->works["add"][$this->hierarchy["childs"][$ID]])
              && isset($One["owner"])                 && $One["owner"]
              && isset($One["online"])                && !$One["online"]
              && isset($One["itemcount"]["physical"]) && !$One["itemcount"]["physical"]
              && isset($One["formatcode"])             && $One["formatcode"] == "article"
            ) {

              $this->params["shift"]    = true;
              $this->params["parentid"] = $this->hierarchy["childs"][$ID];
              $this->params["childid"]  = $ID;

              $One = $this->works["add"][$this->hierarchy["childs"][$ID]];
            }

            if (
              !isset($One["format"])                || !$One["format"]
              || !isset($One["online"])                ||  $One["online"]
              || !isset($One["itemcount"]["physical"]) || !$One["itemcount"]["physical"]
              || !isset($One["owner"])                 || !$One["owner"]
              || ($One["format"] == "Journal" && $JWI)

              || !isset($One["ilngroups"])             || !is_array($One["ilngroups"])
              || !count($One["ilngroups"])
            ) {

              continue;
            }

            if (!$Step || !in_array($One["format"], array("Journal"))) {
              if (!isset($this->paias[$ID]))            $this->paias[$ID]           = array();
              if (!isset($this->results["paia"][$ID]))  $this->results["paia"][$ID] = array();

              foreach ($One["ilngroups"] as $ILN => $EPNs) {

                if (!isset($this->configs["iln2bms"][$ILN]))   continue;

                $BMSID = $this->setBMS($ILN);
                if (!$BMSID)  $this->finalize(array("status" => -1, "code" => "missingbms"));

                $Host = (MODE == "production")                  ? $this->bms[$BMSID]["apiprod"]    : $this->bms[$BMSID]["apitest"];
                $ISIL = isset($this->bms[$BMSID]["isil"]) ? $this->bms[$BMSID]["isil"] . "/" : "";

                if ($this->display["paia"]) {
                  if ($this->params["shift"]) $this->results["paia"][$ID][] = array("Parentshift: " . $this->params["childid"] . " -> " . $this->params["parentid"]);
                  $this->results["paia"][$ID][] = array("ILN: " . $ILN, "Host: " . $Host . "/" . $ISIL, "EPN(s): " . implode(", ", $EPNs));
                }

                $Tmp = $this->bmscon->getPPN($this->params["shift"] ? $this->params["parentid"] : $ID, $One["format"]);

                foreach ($Tmp as $I => $T) {
                  if (isset($T["epn"]) && in_array($T["epn"], $EPNs))   $this->paias[$ID][$I] = $T;
                }
              }
            }
          }
        } else {

          if (isset($this->works["marc"][$ID]["items"][$EPN])) {

            $this->setBMS();
            $this->works["marc"][$ID]["items"] = array($EPN => $this->works["marc"][$ID]["items"][$EPN]);
          }

          $Tmp = $this->bmscon->getEPN($EPN, $this->works["marc"][$ID]["format"]);
          foreach ($Tmp as $I => $T) {
            $this->paias[$ID][$I] = $T;
          }
        }
      }

      if (isset($this->works["marc"]) && is_array($this->works["marc"]) && count($this->works["marc"])) {

        $Item   = new item($this);
        foreach ($this->works["marc"] as $ID => $One) {
          $Key = array_search($ID, array_column($this->results["data"], "id"));
          $this->results["data"][$Key]["items"] = $Item->getItems($ID, $EPN);
        }
      }
    }
  }

  private function ReadArea($Area)
  {
    if (APPDEBUG["FKT"]) $this->debug("FKT", __METHOD__);

    $this->results = array();

    switch ($Area) {
      case "findex": {
          $this->searchControl();

          if (isset($this->results["data"]) && is_array($this->results["data"]) && count($this->results["data"])) {
            foreach ($this->results["data"] as $ID => &$One) {
              if (isset($One["artcode"]))            unset($One["artcode"]);
              if (isset($One["collection_details"])) unset($One["collection_details"]);
              if (isset($One["collections"]))        unset($One["collections"]);
              if (isset($One["hapticarticlelinks"])) unset($One["hapticarticlelinks"]);
              if (isset($One["ilngroups"]))          unset($One["ilngroups"]);
              if (isset($One["itemcount"]))          unset($One["itemcount"]);
              if (isset($One["links"]))              unset($One["links"]);
              if (isset($One["marc"]))               unset($One["marc"]);
              if (isset($One["onlinearticlelinks"])) unset($One["onlinearticlelinks"]);
              if (isset($One["owncollection"]))      unset($One["owncollection"]);
              if (isset($One["owner"]))              unset($One["owner"]);
              if (isset($One["ownorg"]))             unset($One["ownorg"]);
              if (isset($One["searches"]))           unset($One["searches"]);
              if (isset($One["series"]))             unset($One["series"]);

              if (!$this->display["items"] && isset($One["items"]))          unset($One["items"]);
              if (!$this->display["bib"]   && isset($One["bibliographic"]))  unset($One["bibliographic"]);
            }

            $this->results["data"] = array_values($this->results["data"]);
          }
          break;
        }
      case "facets": {
          $this->results["data"] = array(
            "formats"     => $this->formats,
            "types"       => array("both", "online", "physical"),
            "yearmin"     => 785,
            "yearmax"     => intval(date("Y") + 1),
            "levels"      => array_keys($this->getAdminLevelsDB()),
            "sorts"       => array("ranking", "yeardesc", "yearasc", "authorasc"),
            "languages"   => "ISO 639-2 Codes as Array",
            "topicfacets" => "Keywords as Array",
            "classlocals" => "Local class codes as Array",
            "locations"   => "Locations codes as Array",
            "targets"     => array("meta", "fulltext")
          );

          $this->results["hitspack"] = count($this->results["data"]);
          break;
        }
      case "isils": {

          $Search["search"]    = (isset($this->posted["search"]) && is_array($this->posted["search"]))                              ? $this->posted["search"]        : array();
          $Search["start"]     = (isset($this->posted["start"])  && is_numeric($this->posted["start"]))                             ? intval($this->posted["start"]) : 0;
          $Search["limit"]     = (isset($this->posted["limit"])  && is_numeric($this->posted["start"]) && $this->posted["limit"] <= 1000) ? intval($this->posted["limit"]) : 25;

          $this->results["data"]     = $this->getIsilsDB($Search);
          $this->results["hitspack"] = count($this->results["data"]);
          break;
        }

      case "collections": {

          $Search["search"]    = (isset($this->posted["search"]) && is_array($this->posted["search"]))                              ? $this->posted["search"]        : array();
          $Search["start"]     = (isset($this->posted["start"])  && is_numeric($this->posted["start"]))                             ? intval($this->posted["start"]) : 0;
          $Search["limit"]     = (isset($this->posted["limit"])  && is_numeric($this->posted["start"]) && $this->posted["limit"] <= 1000) ? intval($this->posted["limit"]) : 25;

          $this->results["data"]     = $this->findCollectionsDB($Search);
          $this->results["hitspack"] = count($this->results["data"]);
          break;
        }
    }
  }

  private function normalizeBMSResult($SuccessCode)
  {
    if ( !is_array($this->results) )  return;

    if ( !isset($this->results["status"]) && isset($this->results["stat"]) )
    {
      $this->results["status"] = ( $this->results["stat"] === "success" ) ? 0 : -1;
    }
    if ( isset($this->results["status"]) && $this->results["status"] >= 0 && !isset($this->results["code"]) )
    {
      $this->results["code"] = $SuccessCode;
    }
  }

  private function DoRequest()
  {
    if (APPDEBUG["FKT"]) $this->debug("FKT", __METHOD__);

    $Token = (isset($this->posted["tokens"]["user"]) && trim($this->posted["tokens"]["user"])) ? trim($this->posted["tokens"]["user"]) : "";
    $ID    = (isset($this->posted["id"])             && trim($this->posted["id"]))             ? trim($this->posted["id"])             : "";
    $Login = (isset($this->posted["login"])          && trim($this->posted["login"]))          ? trim($this->posted["login"])          : "";
    $Desk  = (isset($this->posted["desk"])           && trim($this->posted["desk"]))           ? trim($this->posted["desk"])           : "";
    $Type  = (isset($this->posted["requesttype"])    && trim($this->posted["requesttype"]))    ? trim($this->posted["requesttype"])    : "";
    if (!$this->bmsid || !$Token || !$ID || !$Login) return array("status" => -1, "code" => "paramsmissing");

    if (!$this->setBMS()) return array();

    $Cond = array();
    if ($Desk && isset($this->bms[$this->bmsid]["isil"])) {
      $Cond["desk"] = "http://uri.gbv.de/organization/isil/" . $this->bms[$this->bmsid]["isil"] . "@counter-" . $Desk;
    }

    if ($this->confIsYes("bms", "request.feeusertypes")) {
      $Cond["feeusertypes"] = true;
    }
    $this->results = $this->bmscon->Request($Login, $ID, $Token, $Cond, $Type);

    $this->normalizeBMSResult("requestsuccessful");
    return $this->results;
  }

  private function DoRenew()
  {
    if (APPDEBUG["FKT"]) $this->debug("FKT", __METHOD__);

    $Token = (isset($this->posted["tokens"]["user"]) && trim($this->posted["tokens"]["user"])) ? trim($this->posted["tokens"]["user"]) : "";
    $ID    = (isset($this->posted["id"])             && trim($this->posted["id"]))             ? trim($this->posted["id"])             : "";
    $Login = (isset($this->posted["login"])          && trim($this->posted["login"]))          ? trim($this->posted["login"])          : "";
    if (!$this->bmsid || !$Token || !$ID || !$Login) return array("status" => -1, "code" => "paramsmissing");

    if (!$this->setBMS()) return array();

    $this->results = $this->bmscon->Renew($Login, $ID, $Token);
    $this->normalizeBMSResult("renewsuccessful");
    return $this->results;
  }

  private function DoCancel()
  {
    if (APPDEBUG["FKT"]) $this->debug("FKT", __METHOD__);

    $Token = (isset($this->posted["tokens"]["user"]) && trim($this->posted["tokens"]["user"])) ? trim($this->posted["tokens"]["user"]) : "";
    $ID    = (isset($this->posted["id"])             && trim($this->posted["id"]))             ? trim($this->posted["id"])             : "";
    $Login = (isset($this->posted["login"])          && trim($this->posted["login"]))          ? trim($this->posted["login"])          : "";
    if (!$this->bmsid || !$Token || !$Login) return array("status" => -1, "code" => "paramsmissing");

    if (!$this->setBMS()) return array();

    $this->results = $this->bmscon->Cancel($Login, $ID, $Token);
    $this->normalizeBMSResult("cancelsuccessful");
    return $this->results;
  }

  private function DoDeleteMails()
  {
    if (APPDEBUG["FKT"]) $this->debug("FKT", __METHOD__);

    $User = $this->getUser();
    if (!isset($User["username"]) || !trim($User["username"])) return array("status" => -1, "code" => "paramsmissing");

    $this->removeMailsDB($User["username"]);
    return array("status" => 1, "code" => "deletemailssuccessful");
  }

  private function DoChangeMail()
  {
    if (APPDEBUG["FKT"]) $this->debug("FKT", __METHOD__);

    $Token = (isset($this->posted["tokens"]["user"]) && trim($this->posted["tokens"]["user"])) ? trim($this->posted["tokens"]["user"]) : "";
    $New   = (isset($this->posted["newmail"])        && trim($this->posted["newmail"]))        ? trim($this->posted["newmail"])        : "";
    $Login = (isset($this->posted["login"])          && trim($this->posted["login"]))          ? trim($this->posted["login"])          : "";
    if (!$this->bmsid || !$Token || !$New || !$Login) return array("status" => -1, "code" => "paramsmissing");

    if (!$this->setBMS()) return array();

    $this->results = $this->bmscon->ChangeMail($Login, $Token, $New);
    if (isset($this->results["status"]) && $this->results["status"] >= 0 && !isset($this->results["code"])) {
      $this->results["code"] = "changemailsuccessful";
    }
    return $this->results;
  }

  private function DoChangePW()
  {
    if (APPDEBUG["FKT"]) $this->debug("FKT", __METHOD__);

    $Token = (isset($this->posted["tokens"]["user"]) && trim($this->posted["tokens"]["user"])) ? trim($this->posted["tokens"]["user"]) : "";
    $Old   = (isset($this->posted["oldpw"])          && trim($this->posted["oldpw"]))          ? trim($this->posted["oldpw"])          : "";
    $New   = (isset($this->posted["newpw"])          && trim($this->posted["newpw"]))          ? trim($this->posted["newpw"])          : "";
    $Login = (isset($this->posted["login"])          && trim($this->posted["login"]))          ? trim($this->posted["login"])          : "";
    if (!$this->bmsid || !$Token || !$Old || !$New || !$Login) return array("status" => -1, "code" => "paramsmissing");

    if (!$this->setBMS()) return array();

    $Display = array();
    $Display["user"]  = true;
    $Display["items"] = true;
    $Display["fees"]  = true;
    $this->display = $Display;

    $this->results = $this->bmscon->ChangePW($Login, $Token, $Old, $New);
    if (isset($this->results["status"]) && $this->results["status"] >= 0 && !isset($this->results["code"])) {
      $this->results["code"] = "changepwsuccessful";
    }

    if (isset($this->results["stat"]) && $this->results["stat"] == "error") return $this->results;

    if (isset($this->results["user"]["name"])) {
      $this->results["user"]["changepw"]  = (isset($this->configs["bms"]["changepassword"])    && $this->configs["bms"]["changepassword"]    == "y") ? true : false;
      $this->results["user"]["cancelres"] = (isset($this->configs["bms"]["cancelreservation"]) && $this->configs["bms"]["cancelreservation"] == "y") ? true : false;
    }

    $DisplayGroups  = array();
    $PAIAGroups     = array();
    foreach (array("usercollectables", "userorders", "userrentals", "userreservations") as $AccGrp) {
      if (
        isset($this->configs["account"][$AccGrp . ".show"])    && $this->configs["account"][$AccGrp . ".show"]    == "y"
        && isset($this->configs["account"][$AccGrp . ".pos"])     && $this->configs["account"][$AccGrp . ".pos"]     != ""
        && isset($this->configs["account"][$AccGrp . ".paiaids"]) && $this->configs["account"][$AccGrp . ".paiaids"] != ""
      ) {
        $DisplayGroups[$this->configs["account"][$AccGrp . ".pos"]] = $AccGrp;
        $Ps = explode(",", $this->configs["account"][$AccGrp . ".paiaids"]);
        foreach ($Ps as $P) {
          $PAIAGroups[$P] = $AccGrp;
        }
      }
    }

    ksort($DisplayGroups);
    $DisplayGroups = array_values($DisplayGroups);
    $FindGroups     = array_flip($DisplayGroups);

    $Items          = array();
    foreach ($DisplayGroups as $Key => $Val) {

      $Items["group_" . $Key] = array("label" => $this->texts["account." . $Val] ?? ("account." . $Val), "code" => $Val, "items" => array());
    }

    if (isset($this->results["items"])  && count($this->results["items"])) {
      $PItems = $this->results["items"];

      foreach ($PItems as $Ind => $Itm) {
        if (!isset($Itm["endtime"]))  $PItems[$Ind]["endtime"] = "";
      }
      array_multisort(array_column($PItems, 'endtime'), SORT_DESC, $PItems);

      foreach ($PItems as $I => $Item) {
        if (
          !isset($Item["status"])
          || !isset($PAIAGroups[$Item["status"]])
          || !isset($FindGroups[$PAIAGroups[$Item["status"]]])
        )  continue;

        $Group = $FindGroups[$PAIAGroups[$Item["status"]]];
        $SID   = substr($Item["storageid"], strpos($Item["storageid"], "@") + 1);
        $Label = isset($Item["label"]) ?  $Item["label"] : "";
        if (strpos($Label, "::") !== false)  $Label = substr($Label, strpos($Label, "::") + 1);
        if (strpos($Label, ":")  !== false)  $Label = substr($Label, strpos($Label, ":") + 1);

        $Tmp = array(
          "id"      => isset($Item["item"])  ? $Item["item"]  : "",
          "title"   => isset($Item["about"]) ? $Item["about"] : "",
          "label"   => $Label,
          "status"  => array(
            "start"       => isset($Item["starttime"]) && trim($Item["starttime"]) ? $this->format2German($Item["starttime"]) : "",
            "end"         => isset($Item["endtime"])   && trim($Item["endtime"])   ? $this->format2German($Item["endtime"])   : "",
            "queue"       => isset($Item["queue"])    ? $Item["queue"]    : "-",
            "renewals"    => isset($Item["renewals"]) ? $Item["renewals"] : "0",
            "maxrenewals" => intval($this->configs["bms"]["maxrenewals"]),
            "reminder"    => isset($Item["reminder"]) ? $Item["reminder"] : "0",
            "state"       => $Item["status"]
          ),
          "storage" => array(
            "name"        => isset($Item["storage"])  ? $Item["storage"]  : "",
            "id"          => $SID,
            "label"       => isset($this->texts["DESK" . substr($SID, 8)]) ? $this->texts["DESK" . substr($SID, 8)] : ""
          ),
          "actions"  => array(
            "cancel"     => isset($Item["cancancel"]) ? $Item["cancancel"] : false,
            "renew"      => isset($Item["canrenew"]) ? $Item["canrenew"] : false
          )
        );
        if (isset($Item["canrenew"]) && $Item["canrenew"] && isset($Item["renewals"]) && $Item["renewals"] <= intval($this->configs["bms"]["maxrenewals"])) {
          $Tmp["actions"]["renew"]  = array(
            "type"    => "renew",
            "id"      => isset($Item["item"])  ? $Item["item"]  : "",
            "login"   => "<login>"
          );
        }
        if (isset($Item["cancancel"]) && $Item["cancancel"]) {
          $Tmp["actions"]["cancel"] = array(
            "type"    => "cancel",
            "id"      => isset($Item["item"])  ? $Item["item"]  : "",
            "login"   => "<login>"
          );
        }
        $Items["group_" . $Group]["items"][] = $Tmp;
      }
    }
    $this->results["items"] = $Items;

    if (isset($this->results["fees"]["items"]) && is_array($this->results["fees"]["items"]) && count($this->results["fees"]["items"])) {
      foreach ($this->results["fees"]["items"] as $I => $Item) {
        $this->results["fees"]["items"][$I]["date"] = $this->format2German($Item["date"]);
      }
    }

    if (
      isset($this->configs["account"]["userotherorders.show"])
      && $this->configs["account"]["userotherorders.show"] == "y"
    ) {
      $this->results["otherorders"] = array(

        "label" => $this->texts["account.userotherorders"] ?? "account.userotherorders",
        "code"  => "userotherorders",
        "items" => $this->getMailDB($Login)
      );
    }
    return $this->results;
  }

  private function DoResetPW()
  {
    if (APPDEBUG["FKT"]) $this->debug("FKT", __METHOD__);

    $Login = (isset($this->posted["login"])          && trim($this->posted["login"]))          ? trim($this->posted["login"])          : "";

    if (!$this->setBMS()) return array();

    $this->results = $this->bmscon->ResetPW($Login);
    if (isset($this->results["status"]) && $this->results["status"] >= 0 && !isset($this->results["code"])) {
      $this->results["code"] = "changepwsuccessful";
    }
    return $this->results;
  }

  private function mailFreigabe($User, &$Grund)
  {
    $Grund = "";
    if ( isset($User["username"]) && trim((string) $User["username"]) !== "" )  return true;

    $Grund = "no user";
    return false;
  }

  private function DoSendMail($Name, $Mail, $Subject, $Message, $Type, $Other = array())
  {
    if (APPDEBUG["FKT"]) $this->debug("FKT", __METHOD__);

    $Mode = ucfirst(substr(MODE, 0, 4));
    $Data = array(
      "toname"   => $Name,
      "to"       => $Mail,
      "subject"  => $Mode . ": " . $Subject  . " (" . $this->configs["client"]["appname"] . ")",
      "message"  => $Message
    );

    if (isset($this->configs["hosting"]["mailfrom"]) && trim($this->configs["hosting"]["mailfrom"]))  $Data["from"]     = trim($this->configs["hosting"]["mailfrom"]);
    if (isset($this->configs["client"]["appname"])   && trim($this->configs["client"]["appname"]))    $Data["fromname"] = trim($this->configs["client"]["appname"]);

    $this->storeMailDB($this->getUser(), $Data, $Type, $Other);

    return $this->sendMail($Data);
  }

  private function setBMS($ILN = "")
  {
    if (APPDEBUG["FKT"]) $this->debug("FKT", __METHOD__);

    $BMSID = $this->bmsid;

    $ProILN = isset($this->configs["item"]["bmsbyiln"]) && $this->configs["item"]["bmsbyiln"] == "y";

    if ($ILN && isset($this->configs["iln2bms"][$ILN]) && is_array($this->configs["iln2bms"][$ILN])
      && (count($this->configs["iln2bms"][$ILN]) > 1 || $ProILN)) {
      $Map = $this->configs["iln2bms"][$ILN];
      if (isset($Map[$this->bmsid]["bmsid"])) {
        $BMSID = $Map[$this->bmsid]["bmsid"];
      } else {
        $First = reset($Map);
        if (isset($First["bmsid"])) $BMSID = $First["bmsid"];
      }
    }

    if (isset($this->bms[$BMSID])) {

      $Type =  (isset($this->bms[$BMSID]["type"])) ? $this->bms[$BMSID]["type"] : "";
      if (!in_array($Type, array("paia", "folio"), true)) return false;

      $this->bmscon = new $Type($BMSID, $this);

      return $BMSID;
    }
    return "";
  }

  public function getClientOptions()
  {
    if ( APPDEBUG["FKT"] ) $this->debug("FKT", __METHOD__);

    $Build = function($Values, $Prefix, $Normalize = false)
    {
      $Out = array();
      foreach ( $Values as $V )
      {
        $Code  = $Prefix . ( $Normalize ? strtolower(str_replace(" ", "", $V)) : strtolower($V) );
        $Label = $this->getText($Code);
        $Out[] = array("value" => $V, "label" => ($Label !== "" ? $Label : $V));
      }
      return $Out;
    };

    $Areas  = is_array($this->searchOptions) ? $this->searchOptions : array();

    $Levels = array();
    foreach ( $this->getAdminLevelsDB() as $Code => $One )
    {
      if ( (isset($One["sort"]) ? (int) $One["sort"] : 100) > 0 )  $Levels[] = $Code;
    }

    $Options = array(
      "areas"   => $Build($Areas,  "area."),
      "levels"  => $Build($Levels, "level."),
      "types"   => $Build(array("both", "online", "physical"), "type."),
      "targets" => $Build(array("meta", "fulltext"),           "target."),

      "sorts"   => $Build(array("ranking", "yeardesc", "yearasc", "authorasc"), "sort."),

      "formats" => $Build(is_array($this->formats) ? $this->formats : array(), "format.", true),
    );

    $FacetIDs = array("sort", "level", "range", "type", "formats", "maps", "formatsmain",
                      "phonetic", "languages", "topicfacets", "classlocals", "locations",
                      "genres", "target", "topicbkls", "topicrvks", "links", "missing");
    $Facets = array();
    foreach ( $FacetIDs as $ID )
    {
      $Label = $this->getText("facet." . $ID);
      if ( $Label !== "" )  $Facets[$ID] = $Label;
    }
    $Options["facets"] = $Facets;

    $Links = array();
    foreach ( (is_array($this->texts) ? $this->texts : array()) as $K => $V )
    {
      if ( strpos($K, "link.") !== 0 )  continue;
      $Lbl = $this->getText($K);
      if ( $Lbl !== "" )  $Links[substr($K, 5)] = $Lbl;
    }
    $Options["links"] = $Links;

    return $Options;
  }

  public function getCDBClass($Shortcut, $Code)
  {
    if (APPDEBUG["FKT"]) $this->CS->debug("FKT", __METHOD__);

    if ($Shortcut == "" || $Code == "") return array();

    $Search = array(
      "search" => array(
        "type"   => "code",
        "system" => $Shortcut,
        "code"   => $Code
      ),
      "start"  => 0,
      "limit"  => 1
    );

    $Datei = $this->prepareLookupCachePath($Shortcut . "_" . $this->lang, $Code);
    if (file_exists($Datei) && time() - (int) @filemtime($Datei) < self::LOOKUP_CACHE_MAXALTER) {
      if (MODE != "production") $this->updStatDB("Cache", "Classis.yes");
      $Gemerkt = json_decode(file_get_contents($Datei), true);
      if (is_array($Gemerkt)) return $Gemerkt;
    }
    if (MODE != "production") $this->updStatDB("Cache", "Classis.no");

    $Pack = $this->getClassisDB($Search);
    $CRec = (isset($Pack["0"])) ? $Pack["0"] : array();

    $Cont = array_intersect_key($CRec, array_flip(array("description", "name", "link", "parents")));
    $Parents = (isset($Cont["parents"])) ? $Cont["parents"] : array();
    if (count($Parents)) {
      $Cont["parents"] = array();
      foreach ($Parents as $Search => $Text) {
        $Cont["parents"][] = array(
          "text"   => trim($Text),
          "code"   => $Shortcut . " " . $Search,
          "action" => array(
            "type"   => "search",
            "search" => array(
              "query" => "topic(" . $Shortcut . " " . $Search . ")",
              "type"  => "lukida"
            )
          ),
          "more"   => array()
        );
      }
    }

    @file_put_contents($Datei, json_encode($Cont, true));
    @chmod($Datei, 0666);

    return $Cont;
  }

  public function getUniqueMultArray($MultArray)
  {
    return array_map("unserialize", array_unique(array_map("serialize", array_values($MultArray))));
  }

  const MARC_CACHE_MAXALTER = 86400;

  public function readMarcNeutral($ID, $UpDate = "")
  {
    $File = $this->prepareIdCachePath($ID, "marcxml");
    if (!file_exists($File)) return null;

    $Roh = json_decode(file_get_contents($File), true);
    if (!is_array($Roh) || !isset($Roh["marc"])) return null;

    $Alt = isset($Roh["up_date"]) ? (string) $Roh["up_date"] : "";
    if ($UpDate !== "" && $UpDate !== null) {
      if ($Alt === "" || $Alt !== (string) $UpDate) return null;

      if (time() - (int) @filemtime($File) > 3600)  @touch($File);
    } elseif (time() - (int) @filemtime($File) > self::MARC_CACHE_MAXALTER) {
      return null;
    }

    if (MODE != "production") $this->updStatDB("Cache", "Marcxml.yes");
    return array("marc"     => $Roh["marc"],
                 "erstesub" => isset($Roh["erstesub"]) ? $Roh["erstesub"] : array(),
                 "solr"     => isset($Roh["solr"])     ? $Roh["solr"]     : array());
  }

  public function writeMarcNeutral($ID, $Daten, $UpDate = "")
  {
    if (!is_array($Daten) || !isset($Daten["marc"])) return false;
    if ($UpDate === "" && isset($this->works["updates"][$ID])) $UpDate = $this->works["updates"][$ID];

    if (MODE != "production") $this->updStatDB("Cache", "Marcxml.no");
    $File = $this->prepareIdCachePath($ID, "marcxml");
    file_put_contents($File, json_encode(array(
      "up_date"  => (string) $UpDate,
      "marc"     => $Daten["marc"],
      "erstesub" => isset($Daten["erstesub"]) ? $Daten["erstesub"] : array(),
      "solr"     => isset($Daten["solr"])     ? $Daten["solr"]     : array()
    ), true));
    @chmod($File, 0666);
    return true;
  }

  public function readCache2Marc($ID, $Add = false, $UpDate = null)
  {
    if (APPDEBUG["FKT"]) $this->debug("FKT", __METHOD__);

    $Prefix  = $Add ? "PM" : "M";
    $Neutral = $this->readMarcNeutral($ID, $UpDate === null ? "" : (string) $UpDate);
    if (!is_array($Neutral) || !isset($Neutral["solr"]) || !count($Neutral["solr"])) {
      if (MODE != "production") $this->updStatDB("Cache", "Marc.no");
      return false;
    }

    $Merk = isset($this->works["solr"]) ? $this->works["solr"] : null;
    $this->works["solr"] = array(array_merge($Neutral["solr"], array(
      "id"                 => $ID,
      "fullrecord_marcxml" => ""
    )));

    $Marc = new marc($this);
    $Marc->getConverts($Add, $Prefix);

    if ($Merk === null) unset($this->works["solr"]);
    else                $this->works["solr"] = $Merk;

    $Da = $Add ? isset($this->works["add"][$ID]) : isset($this->works["marc"][$ID]);
    if (MODE != "production") $this->updStatDB("Cache", $Da ? "Marc.yes" : "Marc.no");
    return $Da;
  }

  public function getMarcFieldFirst($ID, $Field, $Subfield)
  {
    if (isset($this->works["marc"][$ID]["marc"][$Field]) && is_array($this->works["marc"][$ID]["marc"][$Field])) {
      foreach ($this->works["marc"][$ID]["marc"][$Field] as $Rec) {
        if (isset($Rec[$Subfield][0]) && isset($Rec[$Subfield][0]))  return $Rec[$Subfield][0];
      }
    }
    return "";
  }

  public function getMarcText($ID, $BibLineID, $ItemNr = "")
  {
    $Return = array();
    if (isset($this->works["marc"][$ID]["bibliographic"]["groupitems"][$BibLineID]["items"]) && is_array($this->works["marc"][$ID]["bibliographic"]["groupitems"][$BibLineID]["items"])) {
      if (!$ItemNr) {
        foreach ($this->works["marc"][$ID]["bibliographic"]["groupitems"][$BibLineID]["items"] as $Item) {
          if (isset($Item["text"])) $Return[] = $Item["text"];
        }
      } else {
        if (isset($this->works["marc"][$ID]["bibliographic"]["groupitems"][$BibLineID]["items"]["item_" . $ItemNr]["text"])) {
          $Return[] = $this->works["marc"][$ID]["bibliographic"]["groupitems"][$BibLineID]["items"]["item_" . $ItemNr]["text"];
        }
      }
    }
    return implode(", ", $Return);
  }

  public function getMarcArray($ID, $BibLineID, $ItemNr = "")
  {
    $Return = array();
    if (isset($this->works["marc"][$ID]["bibliographic"]["groupitems"][$BibLineID]["items"]) && is_array($this->works["marc"][$ID]["bibliographic"]["groupitems"][$BibLineID]["items"])) {
      if (!$ItemNr) {
        foreach ($this->works["marc"][$ID]["bibliographic"]["groupitems"][$BibLineID]["items"] as $Item) {
          if (isset($Item["text"])) $Return[] = $Item["text"];
        }
      } else {
        if (isset($this->works["marc"][$ID]["bibliographic"]["groupitems"][$BibLineID]["items"]["item_" . $ItemNr]["text"])) {
          $Return[] = $this->works["marc"][$ID]["bibliographic"]["groupitems"][$BibLineID]["items"]["item_" . $ItemNr]["text"];
        }
      }
    }
    return $Return;
  }

  public function getMarcIntern($ID, $IntID, $Key)
  {
    if (!$ID || !$IntID || !$Key) return "";

    return isset($this->works["marc"][$ID]["bibliographic"]["groupitems"][$IntID]["items"][$Key])
      ? $this->works["marc"][$ID]["bibliographic"]["groupitems"][$IntID]["items"][$Key]
      : "";
  }

  public function action()
  {
    if (APPDEBUG["FKT"]) $this->debug("FKT", __METHOD__);

    $Type   = (isset($this->posted["type"])   && $this->posted["type"]   != "") ? strtolower($this->posted["type"])   : "";
    if (!$Type) $this->finalize(array("status" => -1, "code" => "missingvalue"));

    switch ($Type) {
      case "cancel": {
          $this->updStatDB("Action", ucfirst($Type));
          $this->results = $this->DoCancel();
          break;
        }
      case "changemail": {
          $this->updStatDB("Action", ucfirst($Type));
          $this->results = $this->DoChangeMail();
          break;
        }
      case "deletemails": {
          $this->updStatDB("Action", ucfirst($Type));
          $this->results = $this->DoDeleteMails();
          break;
        }
      case "changepw": {
          $this->updStatDB("Action", ucfirst($Type));
          $this->results = $this->DoChangePW();
          break;
        }
      case "delivery": {
          $DeliType       = (isset($this->configs["other"]["delivery.type"]) && $this->configs["other"]["delivery.type"] != "")
            ? $this->configs["other"]["delivery.type"]
            : "";

          switch ($DeliType) {
            case "mail": {
                $User     = $this->getUser();
                if ( !$this->mailFreigabe($User, $Grund) ) {
                  $this->results = array("status" => -1, "code" => "mailerror", "more" => $Grund);
                  break;
                }

                $this->updStatDB("Action", "Delivery|mail");
                $ID   = $this->checkPostedStr("id");
                $Subject  = (isset($this->configs["other"]["delivery.mailsubject"]) && $this->configs["other"]["delivery.mailsubject"] != "")
                  ? $this->configs["other"]["delivery.mailsubject"]
                  : "Scan-Dienst Bestellung";
                $To       = $this->checkDecryptedPostedStr("to");
                $To       = (in_array(MODE, array("local", "development"))) ? $this->f3->get("author.mail") : $To;
                $Name     = explode("@", $To)[0];
                $Stock    = $this->checkDecryptedPostedStr("stock");
                $Sig      = $this->checkDecryptedPostedStr("signature");
                $Vol      = $this->checkDecryptedPostedStr("volume");
                $Remark   = $this->checkDecryptedPostedStr("remark");
                $Pages    = $this->checkDecryptedPostedStr("pages");

                if (!$this->readCache2Marc($ID)) {
                }

                $Message  = "<head><style>";
                $Message .= "table {font-family: Arial, Helvetica, sans-serif; border-collapse: collapse; width: 100%;}";
                $Message .= "table td, #customers th {border: 1px solid #ddd; padding: 8px;}";
                $Message .= "table th { padding: 12px 6px 12px 6px; text-align: left; background-color: lightblue; color: white;}";
                $Message .= "</style></head><body>";

                $Message .= "<h3>Angaben vom Benutzer</h3><table>";
                $Message .= "<tr><td>Jahrgang / Band</td><td>" . $Vol    . "</td></tr>";
                $Message .= "<tr><td>Seiten</td><td>"          . $Pages  . "</td></tr>";
                $Message .= "<tr><td>Anmerkung</td><td>"       . $Remark . "</td></tr>";
                $Message .= "</table>";

                $Message .= "<h3>Signatur und Bestand</h3><table>";
                $Message .= "<tr><td>Signatur</td><td>"        . $Sig    . "</td></tr>";
                $Message .= "<tr><td>Bestand</td><td>"         . $Stock  . "</td></tr>";
                $Message .= "</table>";

                if (is_array($User)) {
                  $Message .= "<h3>Benutzer</h3><table>";
                  foreach ($User as $Key => $Val) {
                    $Message .= "<tr><td>" . $this->getText("user." . $Key) . "</td><td>" . $Val . "</td></tr>";
                  }
                  $Message .= "</table>";
                }

                $Message .= "<h3>Medium</h3><table>";
                $Message .= "<tr><td>" . "Titel"      . "</td><td>" . $this->getMarcText($ID, "bib_01") . "</td></tr>";
                $Message .= "<tr><td>" . "Autoren"    . "</td><td>" . $this->getMarcText($ID, "bib_04") . "</td></tr>";
                $Message .= "<tr><td>" . "Beteiligte" . "</td><td>" . $this->getMarcText($ID, "bib_05") . "</td></tr>";
                $Message .= "<tr><td>" . "ID"         . "</td><td>" . $ID                          . "</td></tr>";
                $Message .= "</table>";

                $Message .= "</body>";

                $SData["title"] = $this->getMarcText($ID, "bib_01");
                $this->results = $this->DoSendMail($Name, $To, $Subject, $Message, "Delivery|mail", $SData);
                break;
              }
            default: {
                $this->results = array("status" => -1, "code" => "unknowntype");
                break;
              }
          }

          $this->finalize($this->results);
        }
      case "dialog": {

          if ( empty($this->posted["nocount"]) )  $this->updStatDB("Action", $Type);
          $ID   = $this->checkPostedStr("id");
          $Type = $this->checkPostedStr("action", "type");
          $Data = $this->checkDecryptedPostedArr("data");
          $Data["user"] = $User = $this->getUser();

          if ( trim((string) $Type) === "" )
          {
            $this->debug("DialogOhneTyp", $this->posted);
            $this->results = array("status" => -1, "code" => "dialog.typemissing");
            break;
          }

          $this->debug("DialogClient", $this->client);
          $this->debug("DialogClient", $this->posted);

          if (!$this->checkDeliveryUserType($Type, $User)) {
            $this->results = array(
              "status" => -1,
              "stat"   => "error",
              "code"   => "delivery.notauthorized",
              "mess"   => array($this->getText("delivery.notauthorized"))
            );
            break;
          }

          if ($ID !== "" && !$this->readCache2Marc($ID)) {

            $Search  = array();
            $Search["search"]    = array("type" => "lukida", "query" => "id:" . $ID);
            $Search["start"]     = (isset($this->posted["start"])    && is_numeric($this->posted["start"]))                             ? intval($this->posted["start"]) : 0;
            $Search["limit"]     = (isset($this->posted["limit"])    && is_numeric($this->posted["start"]) && $this->posted["limit"] <= 1000) ? intval($this->posted["limit"]) : 25;
            $Level               = (isset($this->posted["level"])    && trim($this->posted["level"]))                                    ? trim($this->posted["level"])   : "";
            $Search["url"]       = $this->buildBasicURL($Level);
            $Search["sort"]      = (isset($this->posted["sort"])     && in_array($this->posted["sort"], array("yeardesc", "yearasc", "acqdesc")))    ? $this->posted["sort"]          : "";
            $Search["spell"]     = false;
            $Search["phonetic"]  = false;
            $Search["facets"]    = array();
            $Search["counts"]    = array();
            $this->search = $Search;

            $Display = array();
            $Display["paia"]     = false;
            $Display["marc"]     = false;
            $Display["comb"]     = false;
            $Display["logs"]     = false;
            $Display["bib"]      = false;
            $Display["items"]    = false;
            $this->display = $Display;

            $this->readConvertFindex();
          }
          $this->results = $this->doActionLocal($Type, $ID, $Data, $this->getDialogMailDB($Type));
          break;
        }
      case "mail2library": {

          $ID   = $this->checkPostedStr("id");
          $User = $this->getUser();
          if ( !$this->mailFreigabe($User, $Grund) ) {
            $this->results = array("status" => -1, "code" => "mailerror", "more" => $Grund);
            break;
          }
          $Subject  = $this->checkDecryptedPostedStr("subject");
          if ($Subject) {
            $Tmp     = (isset($this->texts[$Subject])) ? $this->texts[$Subject] : array();
            $Subject = (isset($Tmp["german"]))         ? $Tmp["german"]         : "Betreff";
          }
          $UserInpt = $this->checkDecryptedPostedArr("userinput");
          $MailMeta = $this->checkDecryptedPostedArr("meta");
          $MailItem = $this->checkDecryptedPostedArr("item");

          $MailType = isset($MailMeta["type"]) && trim($MailMeta["type"]) ? (string) strtolower(trim($MailMeta["type"])) : "";
          if ($MailType == "") {
            $this->results = array("status" => -1, "code" => "mailerror", "more" => "wrong type");
            break;
          }

          if (!$this->readCache2Marc($ID)) {
          }

          $this->updStatDB("Action", "Mail|" . $MailType);

          $Tos   = array();
          $Names = array();
          $Mode  = (MODE == "production") ? "prod" : "test";
          if (isset($this->configs["mail." . $Mode][$MailType]) && trim($this->configs["mail." . $Mode][$MailType])) {
            $Tos   = explode(",", trim($this->configs["mail." . $Mode][$MailType]));
            foreach ($Tos as $To) {
              $Names[] = ucfirst(explode("@", $To)[0]);
            }
          } else {
            $MailData = array(
              "Client"   => $this->client,
              "MailType" => $MailType,
              "Error"    => "Mailtype nicht gefunden"
            );

            $this->sendErrorMail(array("subject" => "Config-Mail-Missing (" . $this->client . ")", "message" => $MailData));
          }

          $Head = "";
          if (isset($this->configs["mail"]["style2library"]) && $this->configs["mail"]["style2library"] == "table") {
            $Head    = "<head><style>";
            $Head   .= "table {font-family: Arial, Helvetica, sans-serif; border-collapse: collapse; width: 100%;}";
            $Head   .= "table td, #customers th {border: 1px solid #ddd; padding: 8px;}";
            $Head   .= "table th { padding: 12px 6px 12px 6px; text-align: left; background-color: lightblue; color: white;}";
            $Head   .= "</style></head>";
          }

          $Message  = "";

          if (in_array(MODE, array("local", "development"))) {
            $Message .= "<h3>Angaben fürs Development</h3><table>";
            $Message .= "<tr><td>Umgebung</td><td>" . MODE . "</td></tr>";
            $Message .= "<tr><td>Methode</td><td>mail2library</td></tr>";
            $Message .= "<tr><td>MailType</td><td>" . $MailType . "</td></tr>";
            $Message .= "</table>";
          }

          if (isset($UserInpt) && is_array($UserInpt)) {
            $Message .= "<h3>Angaben Benutzer*in</h3><table>";
            foreach ($UserInpt as $Key => $One) {
              $Tmp  = (isset($this->texts["user." . $Key])) ? $this->texts["user." . $Key] : array();
              $Feld = (isset($Tmp["german"]))     ? $Tmp["german"]     : $Key;
              $Message .= "<tr><td>" . $Feld . "</td><td>" . $One . "</td></tr>";
            }
            $Message .= "</table>";
          }

          $MailFields = (isset($this->configs["mail"]["userfields"]) && trim($this->configs["mail"]["userfields"])) ? explode(",", $this->configs["mail"]["userfields"]) : [];
          if (is_array($User)) {
            $Message .= "<h3>Benutzer*in</h3><table>";
            foreach ($User as $Key => $Val) {
              if (in_array($Key, $MailFields)) $Message .= "<tr><td>" . $this->getText("user." . $Key) . "</td><td>" . $Val . "</td></tr>";
            }
            $Message .= "</table>";
          }

          $Message .= "<h3>Medium</h3><table>";
          $Message .= "<tr><td>" . "Titel"      . "</td><td>" . $this->getMarcText($ID, "bib_01") . "</td></tr>";
          $Message .= "<tr><td>" . "Autoren"    . "</td><td>" . $this->getMarcText($ID, "bib_04") . "</td></tr>";
          $Message .= "<tr><td>" . "Beteiligte" . "</td><td>" . $this->getMarcText($ID, "bib_05") . "</td></tr>";
          $Message .= "<tr><td>" . "Publiziert" . "</td><td>" . $this->getMarcText($ID, "bib_12") . "</td></tr>";
          $Message .= "<tr><td>" . "Siehe auch" . "</td><td>" . $this->getMarcText($ID, "bib_22") . "</td></tr>";
          $Message .= "<tr><td>" . "Gattung"    . "</td><td>" . $this->getMarcText($ID, "bib_35") . "</td></tr>";
          $Message .= "<tr><td>" . "ID"         . "</td><td>" . $ID                               . "</td></tr>";
          $Message .= "</table>";

          if (isset($MailItem) && is_array($MailItem) && count($MailItem)) {
            $Message .= "<h3>Exemplar</h3><table>";
            foreach ($MailItem as $Key => $One) {
              $Tmp  = (isset($this->texts["user." . $Key])) ? $this->texts["user." . $Key] : array();
              $Feld = (isset($Tmp["german"]))     ? $Tmp["german"]     : $Key;
              $Message .= "<tr><td>" . $Feld . "</td><td>" . $One . "</td></tr>";
            }
            $Message .= "</table>";
          }

          if (isset($MailMeta) && is_array($MailMeta) && count($MailMeta)) {
            $Message .= "<h3>Weitere Angaben</h3><table>";
            if (isset($MailMeta["useragent"]) && $MailMeta["useragent"] != "") {
              $Message .= "<tr><td>UserAgent</td><td>" . $MailMeta["useragent"] . "</td></tr>";
            }
            if ($MailType == "feedback") {
              $Message .= "<tr><td>IP</td><td>" . $this->clientip . "</td></tr>";
            }
            if (isset($MailMeta["medialink"]) && $MailMeta["medialink"] != "") {
              $Message .= "<tr><td>Link zum Medium</td><td>" . $MailMeta["medialink"] . "</td></tr>";
            }
            $Message .= "</table>";
          }
          $SData["title"] = $this->getMarcText($ID, "bib_01");

          $this->results  = $this->DoSendMail(
            $Names,
            $Tos,
            $Subject,
            $Head .
              "<body>" . $Message . "</body>",
            $MailType,
            $SData
          );

          if (
            isset($this->configs["mail"]["copy2user"]) && $this->configs["mail"]["copy2user"] == "y"
            && isset($User["email"]) && trim($User["email"]) && isset($User["name"]) && trim($User["name"])
          ) {

            $Tos   = array($User["email"]);
            $Names = array($User["name"]);

            $Intro   = $this->getText('mailcopylibrary');
            $Message = "<h4><b><i>" . $Intro . "</i></b></h4><br><br>" . $Message;
            $this->results = $this->DoSendMail(
              $Names,
              $Tos,
              $Subject,
              $Head .
                "<body>" . $Message . "</body>",
              $MailType,
              $SData
            );
          }
          break;
        }
      case "mail2user": {
          $User = $this->getUser();
          $IDs  = $this->checkPostedArr("ids");
          if ( !$this->mailFreigabe($User, $Grund) ) {
            $this->results = array("status" => -1, "code" => "mailerror", "more" => $Grund);
            break;
          }
          $Subject  = $this->checkDecryptedPostedStr("subject");
          $Name     = $this->checkDecryptedPostedStr("mailname");
          $Msg      = $this->checkDecryptedPostedStr("message");
          $FromName = $this->checkDecryptedPostedStr("fromname");
          $MediaLink = $this->checkDecryptedPostedStr("medialink");
          $To       = $this->checkPostedStr("mailto");
          $MailMeta = $this->checkDecryptedPostedArr("meta");

          foreach ($IDs as $ID) {
            if (!$this->readCache2Marc($ID)) {
            }
            $this->updStatDB("Action", "Mail|User");
          }

          $Exemplare = $this->exemplarAngaben($IDs);

          $Head = "";
          if (isset($this->configs["mail"]["style2user"]) && $this->configs["mail"]["style2user"] == "table") {
            $Head    = "<head><style>";
            $Head   .= "table {font-family: Arial, Helvetica, sans-serif; border-collapse: collapse; width: 100%;}";
            $Head   .= "table td, #customers th {border: 1px solid #ddd; padding: 8px;}";
            $Head   .= "table th { padding: 12px 6px 12px 6px; text-align: left; background-color: lightblue; color: white;}";
            $Head   .= "</style></head>";
          }

          $Message = "";

          if (in_array(MODE, array("local", "development"))) {
            $Message .= "<h3>Angaben fürs Development</h3><table>";
            $Message .= "<tr><td>Umgebung</td><td>" . MODE . "</td></tr>";
            $Message .= "<tr><td>Methode</td><td>mail2user</td></tr>";
            $Message .= "</table>";
          }
          $PreMess = $this->getText('mail.initialtext');
          if (isset($PreMess) && trim($PreMess)) {
            $Message .= "<i>" . $PreMess . "</i><br><br>";
          }

          $Message .= "<h3>Von Benutzer*in " . $FromName . "</h3><table>";
          $Message .= "<tr><td>" . $Msg . "</td></tr>";
          $Message .= "</table>";

          foreach ($IDs as $Idx => $ID) {

            $Message .= "<h3>Medium Nr. " . ($Idx + 1) . "</h3><table>";
            $Message .= "<tr><td>" . "Titel"           . "</td><td>" . $this->getMarcText($ID, "bib_01") . "</td></tr>";
            $Message .= "<tr><td>" . "Autoren"         . "</td><td>" . $this->getMarcText($ID, "bib_04") . "</td></tr>";
            $Message .= "<tr><td>" . "Beteiligte"      . "</td><td>" . $this->getMarcText($ID, "bib_05") . "</td></tr>";
            $Message .= "<tr><td>" . "Publiziert"      . "</td><td>" . $this->getMarcText($ID, "bib_12") . "</td></tr>";

            if (isset($Exemplare[$ID]["zeilen"]) && count($Exemplare[$ID]["zeilen"])) {
              $Message .= "<tr><td>" . ($this->lang == "e" ? "Location / shelfmark" : "Standort / Signatur")
                        . "</td><td>" . implode("<br>", array_map("htmlspecialchars", $Exemplare[$ID]["zeilen"])) . "</td></tr>";
            }
            $Message .= "<tr><td>" . "ID"              . "</td><td>" . $ID                               . "</td></tr>";
            $Message .= "<tr><td>" . "Link zum Medium"  . "</td><td><a href='" . str_replace("\"", "", $MailMeta["medialink"]) . "/search?q=id:" . $ID . "'>hier</a></td></tr>";
            $Message .= "</table>";
          }

          $SData["title"] = $this->getMarcText($ID, "bib_01");
          $this->results = $this->DoSendMail(
            $Name,
            $To,
            $Subject,
            $Head .
              "<body>" . $Message . "</body>",
            "user",
            $SData
          );

          if (
            isset($this->configs["mail"]["copy2user"]) && $this->configs["mail"]["copy2user"] == "y"
            && isset($User["email"]) && trim($User["email"]) && isset($User["name"]) && trim($User["name"])
          ) {

            $To   = $User["email"];
            $Name = $User["name"];

            $Intro   = $this->getText('mailcopyuser');
            $Message = "<h4><b><i>" . $Intro . "</i></b></h4><br><br>" . $Message;

            $this->results = $this->DoSendMail(
              $Name,
              $To,
              $Subject,
              $Head .
                "<body>" . $Message . "</body>",
              "user",
              $SData
            );
          }
          break;
        }
      case "request": {
          $this->updStatDB("Action", ucfirst($Type));
          $this->results = $this->DoRequest();
          break;
        }
      case "renew": {
          $this->updStatDB("Action", ucfirst($Type));
          $this->results = $this->DoRenew();
          break;
        }
      case "resetpw": {
          $this->updStatDB("Action", ucfirst($Type));
          $this->results = $this->DoResetPW();
          break;
        }

      default: {
          $this->results = array("status" => -1, "code" => "unknowntype");
        }
    }

    $this->finalize($this->results);
  }

  private function countLevelFilters($Level)
  {
    if (!is_array($Level))  return 0;

    $Spalten = array("holdingilns", "holdingisils", "holdingshaptic", "locations", "dois", "classlocals", "buildings", "ipranges");
    $Anzahl  = 0;

    foreach ($Spalten as $Spalte) {
      if (!isset($Level[$Spalte]))  continue;
      $Wert = $Level[$Spalte];

      if (is_array($Wert)) {
        if (count($Wert))  $Anzahl++;
        continue;
      }
      $Wert = trim((string) $Wert);

      if ($Wert === "" || $Wert === "[]" || $Wert === "{}" || strtolower($Wert) === "null")  continue;
      $Anzahl++;
    }

    return $Anzahl;
  }

  private function getWiderLevel($Level)
  {
    if (!trim((string) $Level) || !isset($this->levels[$Level]))  return "";

    $Jetzt = $this->countLevelFilters($this->levels[$Level]);
    if ($Jetzt <= 0)  return "";

    $Beste  = "";
    $BesteN = -1;
    $BesteS = PHP_INT_MAX;

    foreach ($this->levels as $Name => $Eine) {
      if ($Name === $Level)  continue;

      $S = isset($Eine["sort"]) ? (int) $Eine["sort"] : 999;

      if ($S <= 0)  continue;

      $N = $this->countLevelFilters($Eine);
      if ($N >= $Jetzt)  continue;

      if ($N > $BesteN || ($N === $BesteN && $S < $BesteS)) {
        $Beste  = $Name;
        $BesteN = $N;
        $BesteS = $S;
      }
    }

    return $Beste;
  }

  public function buildBasicURL($Level = "all")
  {
    if (APPDEBUG["FKT"]) $this->debug("FKT", __METHOD__);

    $LevWHaptic = (isset($this->configs["facet"]["levelswithhaptics"]) && $this->configs["facet"]["levelswithhaptics"] == "y") ? true : false;

    $SolrPath = isset($this->configs["hosting"]["solrpath"]) ? trim($this->configs["hosting"]["solrpath"], "/ ") : "";
    $URL  = $this->conf("solr", "prot", "https://") . trim($this->conf("solr", "host", "findex.gbv.de"), "/ ");
    $URL .= "/" . $SolrPath . "/select?wt=json&defType=edismax";

    if (!trim($Level) || (!array_key_exists($Level, $this->levels))) $Level = "all";

    $Level = $this->levels[$Level];

    if ((isset($Level["collectionsrem"])     && is_array($Level["collectionsrem"])     && count($Level["collectionsrem"])
        && isset($Level["collectionsadd"])     && is_array($Level["collectionsadd"])     && count($Level["collectionsadd"]))
      || (isset($Level["collectionsaddfull"]) && is_array($Level["collectionsaddfull"]) && count($Level["collectionsaddfull"]))
    ) {

      $URL .= "&fq=collection:";

      if (
        isset($Level["collectionsrem"])         && is_array($Level["collectionsrem"])     && count($Level["collectionsrem"])
        && isset($Level["collectionsadd"])         && is_array($Level["collectionsadd"])     && count($Level["collectionsadd"])
        && isset($Level["collectionsaddfull"])     && is_array($Level["collectionsaddfull"]) && count($Level["collectionsaddfull"])
      ) {
        $URL .= "(((*+AND+NOT+collection:(" . implode("+OR+", array_map(array($this, "formatSolrItemColl"), $Level["collectionsrem"]))     . "))";
        $URL .=          "+OR+collection:(" . implode("+OR+", array_map(array($this, "formatSolrItemColl"), $Level["collectionsadd"]))     . "))";
        $URL .=          "+OR+collection:(" . implode("+OR+", array_map(array($this, "formatSolrItemColl"), $Level["collectionsaddfull"])) . "))";

        if (isset($Level["collectiondetailsrem"]) && is_array($Level["collectiondetailsrem"]) && count($Level["collectiondetailsrem"])) {
          $URL .= "+AND+NOT+collection_details:(" . implode("+OR+", array_map(array($this, "formatSolrItemColl"), $Level["collectiondetailsrem"])) . ")";
        }
      }

      elseif (
        isset($Level["collectionsrem"])     && is_array($Level["collectionsrem"])     && count($Level["collectionsrem"])
        && isset($Level["collectionsadd"])     && is_array($Level["collectionsadd"])     && count($Level["collectionsadd"])
      ) {
        $URL .= "((*+AND+NOT+collection:(" . implode("+OR+", array_map(array($this, "formatSolrItemColl"), $Level["collectionsrem"]))      . "))";
        $URL .=         "+OR+collection:(" . implode("+OR+", array_map(array($this, "formatSolrItemColl"), $Level["collectionsadd"]))      . "))";
      }

      elseif (isset($Level["collectionsaddfull"]) && is_array($Level["collectionsaddfull"]) && count($Level["collectionsaddfull"])) {
        $URL .=                         "(" . implode("+OR+", array_map(array($this, "formatSolrItemColl"), $Level["collectionsaddfull"])) . ")";
      }
    }

    $Vals = array();
    if (isset($Level["holdingilns"])  && is_array($Level["holdingilns"])  && count($Level["holdingilns"]))  $Vals = array_merge($Vals, $Level["holdingilns"]);
    if (isset($Level["holdingisils"]) && is_array($Level["holdingisils"]) && count($Level["holdingisils"])) $Vals = array_merge($Vals, $Level["holdingisils"]);
    $Hapt = (isset($Level["holdingshaptic"]) && is_array($Level["holdingshaptic"])) ? $Level["holdingshaptic"] : array();

    if (count($Vals) || count($Hapt)) {
      $Teile = array();
      if (isset($Level["collectionsaddfull"]) && is_array($Level["collectionsaddfull"]) && count($Level["collectionsaddfull"])) {
        $Teile[] = "collection:(" .
          implode("+OR+", array_map(array($this, "formatSolrItem"), $Level["collectionsaddfull"])) . ")";
      }
      if (count($Vals)) {
        $Teile[] = "collection_details:(" .
          implode("+OR+", array_map(array($this, "formatSolrItem"), $Vals)) . ")";
      }
      if (count($Hapt)) {
        $Teile[] = "(collection_details:(" .
          implode("+OR+", array_map(array($this, "formatSolrItem"), $Hapt)) . ")+AND+remote_bool:false)";
      }
      $URL .= "&fq=" . (count($Teile) > 1 ? "(" . implode("+OR+", $Teile) . ")" : $Teile[0]);
    }

    if (isset($Level["classlocals"]) && is_array($Level["classlocals"]) && count($Level["classlocals"])) {
      $URL .= "&fq=class_local_iln:(" . implode("+OR+", array_map(array($this, "formatSolrItem"), $Level["classlocals"])) . ")";
    }
    if (isset($Level["locations"]) && is_array($Level["locations"]) && count($Level["locations"])) {
      $Ls = array();
      $Bs = array();
      foreach ($Level["locations"] as $X) {
        if (strpos($X, "@") === false) {
          $Ls[] = $this->formatSolrItem($X);
        } else {
          $Bs[] = $this->formatSolrItem($X);
        }
      }
      if (count($Ls)) {

        $URL .= "&fq=(standort_iln_str_mv:(" . implode("+OR+", $Ls) . "))";
      }
      if (count($Bs)) {
        $URL .= "&fq=(selectbib_iln_str_mv:(" . implode("+OR+", $Bs) . "))";
      }
    }
    if (isset($Level["buildings"]) && is_array($Level["buildings"]) && count($Level["buildings"])) {
      $URL .= "&fq=(building:(" . implode("+OR+", array_map(array($this, "formatSolrItem"), $Level["buildings"])) . ")";
      if ($LevWHaptic) {
        $URL .= "+OR+remote_bool:false";
      }
      $URL .= ")";
    }

    return $URL;
  }

  private function exemplarAngaben($IDs)
  {
    $Aus = array();
    $IDs = array_values(array_filter(array_unique((array) $IDs)));
    if (!count($IDs)) return $Aus;

    $Merk = array("search" => $this->search, "display" => $this->display, "results" => $this->results);

    $this->search = array(
      "search" => array("type" => "lukida", "query" => "id:(" . implode(" OR ", $IDs) . ")"),
      "start"  => 0,
      "limit"  => 1000,
      "url"    => $this->buildBasicURL("all"),
    );
    $this->display = array("paia" => false, "marc" => false, "comb" => false, "logs" => false,
                           "bib" => true, "items" => true);
    $this->results = array();

    try {
      $this->ReadArea("findex");

      foreach ((array) ($this->results["data"] ?? array()) as $One) {
        $ID = isset($One["id"]) ? (string) $One["id"] : "";
        if ($ID === "") continue;

        $Orte = array(); $Sigs = array(); $Zeilen = array();
        foreach ((array) ($One["items"] ?? array()) as $Group) {

          if (in_array((string) ($Group["label"] ?? ""), array("online", "media", "searches"), true)) continue;

          foreach ((array) ($Group["groupitems"] ?? array()) as $GI) {
            $Ort = implode(", ", array_filter((array) ($GI["meta"]["location"]  ?? array())));
            $Sig = implode(", ", array_filter((array) ($GI["meta"]["signature"] ?? array())));
            if ($Ort !== "" && !in_array($Ort, $Orte, true)) $Orte[] = $Ort;
            if ($Sig !== "" && !in_array($Sig, $Sigs, true)) $Sigs[] = $Sig;

            $Zeile = trim($Ort . (($Ort !== "" && $Sig !== "") ? " · " : "") . $Sig);
            if ($Zeile !== "" && !in_array($Zeile, $Zeilen, true)) $Zeilen[] = $Zeile;
          }
        }
        $Aus[$ID] = array("orte" => $Orte, "signaturen" => $Sigs, "zeilen" => $Zeilen);
      }
    } catch (\Throwable $e) {

      $this->debug("LOG", array("exemplarangaben" => $e->getMessage()));
    }

    $this->search  = $Merk["search"];
    $this->display = $Merk["display"];
    $this->results = $Merk["results"];
    return $Aus;
  }

  public function export()
  {
    $Appl   = (isset($this->posted["appl"])   && trim($this->posted["appl"]))   ? trim(strtolower($this->posted["appl"]))   : "";
    $Area   = (isset($this->posted["area"])   && trim($this->posted["area"]))   ? trim(strtolower($this->posted["area"]))   : "";
    $Format = (isset($this->posted["format"]) && trim($this->posted["format"])) ? trim(strtolower($this->posted["format"])) : "";
    $IDs    = $this->checkPostedArr("ids");

    if (($Appl && $Appl != $this->appl)              || !$Appl) $this->finalize(array("status" => -1, "code" => "wrongappl"));
    if (($Area && !in_array($Area, $this->getAreas)) || !$Area) $this->finalize(array("status" => -1, "code" => "wrongarea"));
    if (!$Format)                                               $this->finalize(array("status" => -1, "code" => "missingformat"));
    if (empty($IDs))                                            $this->finalize(array("status" => -1, "code" => "missingids"));

    if (MODE == "development")     $Mode = "dev";
    elseif (MODE == "production")  $Mode = "prod";
    else                             $Mode = MODE;

    $Config = array(
      'base_url'       => $this->configs["domain"][$Mode],
      'title'          => $this->configs["meta"]["description"],
      'ezbbibid'       => $this->configs["resolver"]["ezbbibid"],
      'openurl'        => $this->configs["resolver"]["openurl"]
    );

    $exp = new export($this, $Config);

    $Format = $exp->formatname($Format);
    if (!$Format)                                               $this->finalize(array("status" => -1, "code" => "wrongformat"));

    $exportString = "";

    $Exemplare = in_array($Format, array("excel")) ? $this->exemplarAngaben($IDs) : array();

    foreach ($IDs as $ID) {
      if (!$this->readCache2Marc($ID)) {

        $Search  = array();
        $Search["search"]    = array("type" => "lukida", "query" => "id:" . $ID);
        $Search["start"]     = (isset($this->posted["start"])    && is_numeric($this->posted["start"]))                             ? intval($this->posted["start"]) : 0;
        $Search["limit"]     = (isset($this->posted["limit"])    && is_numeric($this->posted["start"]) && $this->posted["limit"] <= 1000) ? intval($this->posted["limit"]) : 25;
        $Level               = (isset($this->posted["level"])    && trim($this->posted["level"]))                                    ? trim($this->posted["level"])   : "";
        $Search["url"]       = $this->buildBasicURL($Level);
        $Search["sort"]      = (isset($this->posted["sort"])     && in_array($this->posted["sort"], array("yeardesc", "yearasc", "acqdesc")))    ? $this->posted["sort"]          : "";
        $Search["spell"]     = false;
        $Search["phonetic"]  = false;
        $Search["facets"]    = array();
        $Search["counts"]    = array();
        $this->search = $Search;

        $Display = array();
        $Display["paia"]     = false;
        $Display["marc"]     = false;
        $Display["comb"]     = false;
        $Display["logs"]     = false;
        $Display["bib"]      = false;
        $Display["items"]    = false;
        $this->display = $Display;

        $this->readConvertFindex();
      }

      $noteText = $this->getMarcText($ID, "bib_17");
      $Marc245 = $this->works["marc"][$ID]["marc"]["M245"][0] ?? [];

      $titleMain = isset($Marc245["a"][0])
        ? rtrim(trim($Marc245["a"][0]), " /:;")
        : "";

      $titlePart = isset($Marc245["b"][0])
        ? rtrim(trim($Marc245["b"][0]), " /:;")
        : "";

      $titleFull = $titleMain;

      if ($titlePart !== "") {
        $titleFull = $titleMain . ": " . $titlePart;
      }
      $date100 = $this->getMarcIntern($ID, "int_100", "year");
      $date101 = $this->getMarcIntern($ID, "int_101", "year");

      $author = array_merge(
        $this->getMarcArray($ID, "bib_04", "1"),
        $this->getMarcArray($ID, "bib_05", "1")
      );

      $volume = $this->getMarcIntern($ID, "int_100", "volume");
      if (!$volume) {
        $volume = $this->getMarcIntern($ID, "int_100", "year");
      }

      $pages = $this->getMarcIntern($ID, "int_100", "pages");
      if (!$pages) {
        $pages = $this->getMarcIntern($ID, "int_100", "extent");
      }

      $callnumber = "";

      if (isset($this->works["marc"][$ID]["items"]) && is_array($this->works["marc"][$ID]["items"])) {
        foreach ($this->works["marc"][$ID]["items"] as $item) {
          if (isset($item["d"][0]) && trim($item["d"][0]) !== "") {
            $callnumber = trim($item["d"][0]);
            break;
          }
        }
      }

      $place = $this->getMarcIntern($ID, "int_101", "place");
      $publisher = $this->getMarcIntern($ID, "int_101", "publisher");

      if (empty($place) || empty($publisher)) {
        $m773d = $this->works["marc"][$ID]["marc"]["M773"][0]["d"][0] ?? "";
        if ($m773d !== "") {

          if (preg_match('/^([^:]+)\s*:\s*([^,]+)/', $m773d, $matches)) {
            if (empty($place)) {
              $place = trim($matches[1]);
            }
            if (empty($publisher)) {
              $publisher = trim($matches[2]);
            }
          }
        }
      }

      $parentTitle = $this->works["marc"][$ID]["marc"]["M773"][0]["t"][0] ?? "";

      $code = $this->works["marc"][$ID]["formatcode"] ?? "";

      if ($code === "journal") {
        $format = $this->getText("format." . $code);
      } else {
        $format = $code;
      }

      $rootId = $this->getMarcIntern($ID, "int_100", "rootid");
      $parentFormat = "";

      if (!empty($rootId) && trim($rootId) !== "") {

        if (!isset($this->works["add"][$rootId]) && !isset($this->works["marc"][$rootId])) {
          $this->search = array(
            "search"   => array(
              "query" => "id(" . $rootId . ")",
              "type"  => "lukida"
            ),
            "start"    => 0,
            "limit"    => 1,
            "sort"     => "",
            "spell"    => false,
            "phonetic" => false,
            "facets"   => array(),
            "url"      => $this->buildBasicURL("all"),
            "counts"   => array()
          );

          $this->readConvertFindex(true, "PM");
        }

        $parentFormat = $this->works["add"][$rootId]["formatcode"]
          ?? $this->works["marc"][$rootId]["formatcode"]
          ?? "";
      }

      $exportFormat = $format;

      if ($code === "article" && $parentFormat === "book") {
        $exportFormat = "inbook";
      }

      $Data = array(
        "id"            => $ID,
        "format"        => $exportFormat,

        "title"         => $titleFull,
        "collTitle"     => $parentTitle,
        "language"      => $this->getMarcText($ID, "bib_14"),
        "isbn"          => $this->getMarcText($ID, "bib_29"),
        "issn"          => $this->getMarcText($ID, "bib_28"),

        "notes"         => ($noteText !== "" ? array($noteText) : array()),
        "pages"         => $pages,
        "place"         => $place,
        "publisher"     => $publisher,
        "date"          => ($this->works["marc"][$ID]["formatcode"] === "article") ? ($date100 ?: $date101) : ($date101 ?: $date100),
        "edition"       => $this->getMarcText($ID, "bib_10"),
        "author"        => $author,
        "associates"    => $this->getMarcArray($ID, "bib_05", "1"),
        "zdbid"         => $this->getMarcIntern($ID, "int_100", "zdbid"),
        "doi"           => $this->getMarcText($ID, "bib_47"),
        "subtitle"      => $this->getMarcText($ID, "bib_42"),
        "series"        => $this->getMarcText($ID, "bib_08"),

        "part"          => $this->getMarcIntern($ID, "int_100", "part"),
        "issue"         => $this->getMarcIntern($ID, "int_100", "number"),
        "volume"        => $volume,
        "volltext"      => $this->getMarcIntern($ID, "int_103", "volltext"),
        "school"        => $this->getMarcIntern($ID, "int_102", "institution"),
        "type"          => $this->getMarcIntern($ID, "int_102", "type"),
        "physdesc"      => $this->getMarcIntern($ID, "int_102", "description"),
        "computerfile"  => $this->getMarcIntern($ID, "int_102", "computerfile"),
        "summary"       => $this->getMarcIntern($ID, "int_102", "summary"),
        "genre"         => $this->getMarcIntern($ID, "int_102", "genre"),
        "subject"       => $this->getMarcText($ID, "bib_33"),
        "callnumber"    => $callnumber,
        "online"        => $this->works["marc"][$ID]["online"] ?? false,

        "standort"      => implode("; ", (array) ($Exemplare[$ID]["orte"]       ?? array())),
        "signatur"      => implode("; ", (array) ($Exemplare[$ID]["signaturen"] ?? array()))
      );

      if (isset($Data["format"]) && $Data["format"] == "article") {

        $Tmp = $this->getMarcIntern($ID, "int_100", "journal");
        if ($Tmp) {
          $Data["articleTitle"] =  $Data["title"];
          $Data["title"]        =  $Tmp;
        }
      }

      $exportString .= $exp->exportfile($Data, $Format);
    }

    $this->results["export"] = $exportString;

    unset($this->results["data"]);

    $this->finalize($this->results, __FUNCTION__);
  }

  public function count()
  {
    $Appl = (isset($this->posted["appl"]) && trim($this->posted["appl"])) ? trim(strtolower($this->posted["appl"])) : "";
    $ID   = $this->checkPostedStr("id");
    $Art  = $this->checkPostedStr("kind");
    if ( ($Appl && $Appl != $this->appl) || !$Appl )  $this->finalize(array("status" => -1, "code" => "wrongappl"));
    if ( !$ID )                                        $this->finalize(array("status" => -1, "code" => "missingid"));
    if ( $Art != "request" )                           $this->finalize(array("status" => -1, "code" => "wrongkind"));

    $this->counterZaehlen("Requests", $ID, $this->checkPostedStr("rootid"));
    $this->finalize(array("status" => 1, "code" => "counted"));
  }

  private function counterZaehlen($Art, $ID, $TitelID = "")
  {
    $ID      = trim((string) $ID);
    $TitelID = trim((string) $TitelID) !== "" ? trim((string) $TitelID) : $ID;
    if ( $ID === "" )  return;

    $this->updStatDB("Counter", "Total_Item_" . $Art);

    $Sitzung = preg_replace("/[^A-Za-z0-9_-]/", "", (string) $this->checkPostedStr("tokens", "session"));
    if ( $Sitzung === "" )
    {
      $this->updStatDB("Counter", "Unique_Item_" . $Art);
      $this->updStatDB("Counter", "Unique_Title_" . $Art);
      return;
    }

    $Datei = $this->prepareCachePath(array("counter"), $Sitzung);
    $F = @fopen($Datei, "c+");
    if ( !$F )  return;
    flock($F, LOCK_EX);
    $Gesehen = json_decode((string) stream_get_contents($F), true);
    if ( !is_array($Gesehen) )  $Gesehen = array();

    $Neu = false;
    if ( empty($Gesehen[$Art]["item"][$ID]) )
    {
      $Gesehen[$Art]["item"][$ID] = 1;
      $this->updStatDB("Counter", "Unique_Item_" . $Art);
      $Neu = true;
    }
    if ( empty($Gesehen[$Art]["title"][$TitelID]) )
    {
      $Gesehen[$Art]["title"][$TitelID] = 1;
      $this->updStatDB("Counter", "Unique_Title_" . $Art);
      $Neu = true;
    }
    if ( $Neu )
    {
      ftruncate($F, 0);
      rewind($F);
      fwrite($F, json_encode($Gesehen));
    }
    flock($F, LOCK_UN);
    fclose($F);
  }

  public function resolve()
  {
    $Appl   = (isset($this->posted["appl"])   && trim($this->posted["appl"]))   ? trim(strtolower($this->posted["appl"]))   : "";
    $Area   = (isset($this->posted["area"])   && trim($this->posted["area"]))   ? trim(strtolower($this->posted["area"]))   : "";
    $ID     = (isset($this->posted["id"])     && trim($this->posted["id"]))     ? trim($this->posted["id"])                 : "";
    $Type   = (isset($this->posted["type"])   && trim($this->posted["type"]))   ? trim($this->posted["type"])               : "";

    if (($Appl && $Appl != $this->appl)              || !$Appl) $this->finalize(array("status" => -1, "code" => "wrongappl"));
    if (($Area && !in_array($Area, $this->getAreas)) || !$Area) $this->finalize(array("status" => -1, "code" => "wrongarea"));
    if (!$ID)                                                   $this->finalize(array("status" => -1, "code" => "missingid"));

    $NichtZaehlen = !empty($this->posted["nocount"]);
    if ( !$NichtZaehlen )  $this->updStatDB("Media", "Fullview");

    if ( !$NichtZaehlen && $Type != "suggest" )  $this->counterZaehlen("Investigations", $ID, $this->checkPostedStr("rootid"));

    if (in_array($Type, array("suggest"))) {
      $this->updateProjectDB("kisuggest", $ID);
      return $this->finalize();
    }

    $Check = $this->getResolversDB($ID);
    if (is_array($Check) && count($Check)) {

      if (MODE != "production") $this->updStatDB("Cache", "Resolver.yes");

      $Check = $this->resolveServiceTexts($Check);
      $this->finalize($Check);
    }

    $this->results = array();
    $this->display["ilns"]   = $this->configs["ilns"];

    $Rs = array();
    if (isset($this->configs["resolver"]["jop"])  && $this->configs["resolver"]["jop"]  == "y") $Rs[] = "jop";
    if (isset($this->configs["resolver"]["sfx"])  && $this->configs["resolver"]["sfx"]  == "y") $Rs[] = "sfx";
    if (isset($this->configs["resolver"]["ovid"]) && $this->configs["resolver"]["ovid"] == "y") $Rs[] = "ovid";
    if (isset($this->configs["resolver"]["redi"]) && $this->configs["resolver"]["redi"] == "y") {
      $Rs[] = "redi";

      $ezbids = explode(",", $this->configs["resolver"]["ezbbibid"] ?? "");
      if (count($ezbids) > 1 && !empty(trim($ezbids[1]))) $Rs[] = "redi2";
    }
    if (!$this->readCache2Marc($ID)) {

      $Search  = array();
      $Search["search"]    = array("type" => "lukida", "query" => "id:" . $ID);
      $Search["start"]     = 0;
      $Search["limit"]     = 1;
      $Search["url"]       = $this->buildBasicURL("all");
      $this->search        = $Search;

      $Display = array();
      $Display["paia"]     = false;
      $Display["marc"]     = false;
      $Display["comb"]     = false;
      $Display["logs"]     = false;
      $Display["bib"]      = false;
      $Display["items"]    = false;
      $this->display = $Display;

      $this->readConvertFindex();
    }

    if (MODE == "development")     $Mode = "dev";
    elseif (MODE == "production")  $Mode = "prod";
    else                             $Mode = MODE;

    $Config = array(
      'base_url'       => $this->configs["domain"][$Mode],
      'title'          => $this->getClientName(),
      'ezbbibid'       => $this->configs["resolver"]["ezbbibid"],
      'isil'           => $this->configs["isils"][0],
      'openurl'        => $this->configs["resolver"]["openurl"],
      'jop'            => (isset($this->configs["resolver"]["jop"])  && $this->configs["resolver"]["jop"]  == "y") ? true : false,
      'redi'           => (isset($this->configs["resolver"]["redi"]) && $this->configs["resolver"]["redi"] == "y") ? true : false,
      'redibase'       => $this->configs["resolver"]["redibase"],

      'redi2'          => (count(explode(",", $this->configs["resolver"]["ezbbibid"] ?? "")) > 1) ? true : false,
      'redi2base'      => $this->configs["resolver"]["redibase"] ?? "",
      'ezbbibid2'      => trim(explode(",", $this->configs["resolver"]["ezbbibid"] ?? "")[1] ?? ""),
      'sfx'            => (isset($this->configs["resolver"]["sfx"])  && $this->configs["resolver"]["sfx"]  == "y") ? true : false,
      'sfxbase'        => $this->configs["resolver"]["sfxbase"],
      'ovid'           => (isset($this->configs["resolver"]["ovid"])  && $this->configs["resolver"]["ovid"]  == "y") ? true : false,
      'ovidbase'       => $this->configs["resolver"]["ovidbase"],
      'refworks'       => true,
      'zotero'         => true
    );

    $Data = array(
      "id"            => $ID,
      "format"        => isset($this->works["marc"][$ID]["formatcode"]) ? $this->works["marc"][$ID]["formatcode"] : "",
      "title"         => $this->getMarcText($ID, "bib_01"),
      "language"      => $this->getMarcText($ID, "bib_14"),
      "isbn"          => $this->getMarcText($ID, "bib_29"),
      "issn"          => $this->getMarcText($ID, "bib_28"),
      "notes"         => $this->getMarcText($ID, "bib_17"),
      "pages"         => $this->getMarcIntern($ID, "int_100", "pages"),
      "place"         => $this->getMarcIntern($ID, "int_101", "place"),
      "publisher"     => $this->getMarcIntern($ID, "int_101", "publisher"),
      "date"          => $this->getMarcIntern($ID, "int_101", "year"),
      "edition"       => $this->getMarcText($ID, "bib_10"),
      "author"        => $this->getMarcArray($ID, "bib_04", "1"),
      "associates"    => $this->getMarcArray($ID, "bib_05", "1"),
      "zdbid"         => $this->getMarcIntern($ID, "int_100", "zdbid"),
      "doi"           => $this->getMarcText($ID, "bib_47"),
      "subtitle"      => $this->getMarcText($ID, "bib_42"),
      "series"        => $this->getMarcIntern($ID, "int_100", "journal"),
      "part"          => $this->getMarcIntern($ID, "int_100", "part"),
      "issue"         => $this->getMarcIntern($ID, "int_100", "number"),
      "volume"        => $this->getMarcIntern($ID, "int_100", "volume"),
      "volltext"      => $this->getMarcIntern($ID, "int_103", "volltext"),
      "school"        => $this->getMarcIntern($ID, "int_102", "institution"),
      "type"          => $this->getMarcIntern($ID, "int_102", "type"),
      "physdesc"      => $this->getMarcIntern($ID, "int_102", "description"),
      "computerfile"  => $this->getMarcIntern($ID, "int_102", "computerfile"),
      "summary"       => $this->getMarcIntern($ID, "int_102", "summary"),
      "genre"         => $this->getMarcIntern($ID, "int_102", "genre"),
      "subject"       => $this->getMarcText($ID, "bib_33")
    );

    if (isset($Data["format"]) && $Data["format"] == "article") {

      $Tmp = $this->getMarcIntern($ID, "int_100", "journal");
      if ($Tmp) {
        $Data["articleTitle"] =  $Data["title"];
        $Data["title"]        =  $Tmp;
      }
    }

    $Scope  = $this->conf("item", "resolverscope", "all");
    $Online = isset($this->works["marc"][$ID]["online"]) ? (bool) $this->works["marc"][$ID]["online"] : null;
    $Eigen  = isset($this->works["marc"][$ID]["owner"])  ? (bool) $this->works["marc"][$ID]["owner"]  : false;

    if ($Scope == "physical" && $Online === true) {
      $Rs = array();
    }
    elseif ($Scope == "foreignonline" && (($Online !== null && !$Online) || $Eigen)) {
      $Rs = array();
    }

    $Resolver = array();

    $LR = new resolver($this, $Config);
    if (count($Rs)) {

      foreach ($Rs as $R) {
        $this->updStatDB("Resolver", $R);
      }
      $Resolver  += $LR->linkresolver($Data);
      $Color = isset($this->CS->configs["item"]["resolver.color"]) && trim($this->CS->configs["item"]["resolver.color"]) ? $this->CS->configs["item"]["resolver.color"] : "green";
      foreach ($Resolver as $R => $URL) {

        $Resolver[$R] = array(
          "after"  => array(),
          "before" => array(),
          "text"   => array(
            "g" => array(array(
              "type" => "code",
              "code" => "resolver." . $R
            )),
            "e" => array(array(
              "type" => "code",
              "code" => "resolver." . $R
            ))
          ),
          "groupitems" => array(array(
            "short"    => array(
              "code"  => "itembutton.directopen",
              "icon"  => "mdi-link",
              "color" => $Color
            ),
            "comments" => array(
              "g" => array(
                array("type" => "code", "code" => "openinanewtab"),
                array("type" => "code", "code" => "items.fulltext")
              ),
              "e" => array(
                array("type" => "code", "code" => "openinanewtab"),
                array("type" => "code", "code" => "items.fulltext")
              )
            ),
            "light"    => "green",
            "action"   => array(
              "type" => "link",
              "link" => array(
                "url"    => $URL,
                "label"  => $this->getHost(trim($URL)),
                "target" => "_blank"
              )
            ),
            "view"     => array(),
            "users"    => array(),
            "desks"    => array(),
            "meta"     => array(
              "case"   => "LinkResolver " . ucfirst($R),
              "locid"  => "resolver." . $R,
              "window" => false
            )
          )),
          "label" => "resolver." . $R,
          "light" => "green"
        );
      }
    }

    $Resolver += array(
      "refworks" => $LR->exportlink($Data, 'refworks'),
      "zotero"   => $LR->exportlink($Data, 'zotero')
    );

    if ($Type == "media") {

      if (isset($this->configs["hosting"]["kisuggest"])  && $this->configs["hosting"]["kisuggest"]  == "y") {

        $Solr  = new solr($this);

        $Solr->getProject("https://findex.gbv.de/semsimlabim/?semsim_model=inomic-iln30-title&semsim_id=" . $ID, "", "kisuggest");

        if (isset($this->works["project"]["kisuggest"]) && is_array($this->works["project"]["kisuggest"]) && count($this->works["project"]["kisuggest"])) {
          $this->results = array();
          $this->works["marc"]   = array();
          $this->paias   = array();
          $this->items   = array();
          $this->logs    = array();

          $Final = array();
          $Score = array();
          $Sort  = array();
          foreach ($this->works["project"]["kisuggest"] as $I => $S) {
            if (isset($S["id"]) && $S["id"] == $ID) continue;

            $Final[]         = $S["id"];
            $Sort[$S["id"]]  = $I;
            $Score[$S["id"]] = $S["score"];

            if (count($Final) >= 50) break;
          }

          $this->storeProjectDB("kisuggest", $ID, $Final, $Score);

          $Search  = array();
          $Search["search"]    = array("type" => "lukida", "query" => "id:(" . implode(" OR ", $Final) . ")");
          $Search["start"]     = 0;
          $Search["limit"]     = 50;
          $Search["url"]       = $this->buildBasicURL("all");
          $this->search        = $Search;

          $Display = array();
          $Display["paia"]     = false;
          $Display["marc"]     = false;
          $Display["comb"]     = false;
          $Display["logs"]     = false;
          $Display["bib"]      = true;
          $Display["items"]    = true;
          $this->display = $Display;

          $this->ReadArea("findex");

          $Sorted = array();

          if (isset($this->results["data"]) && is_array($this->results["data"]) && count($this->results["data"])) {
            foreach ($Final as $PPN_Ranked) {
              foreach ($this->results["data"] as $FullOne) {
                if (isset($FullOne["id"]) && $FullOne["id"] == $PPN_Ranked) $Sorted[] = $FullOne;
              }
            }

            $this->results["data"] = $Sorted;
          }

          if (isset($this->results["hitspack"]) && $this->results["hitspack"] > 0) {
            $Resolver["kisuggest"] = $this->results;
          }
        }
      }
    }

    if (MODE != "production") $this->updStatDB("Cache", "Resolver.no");

    $this->setResolversDB($ID, $Resolver, array());

    $Resolver = $this->resolveServiceTexts($Resolver);

    $this->finalize($Resolver);
  }

  private function resolveServiceTexts($Resolver)
  {
    if (!is_array($Resolver)) return $Resolver;
    foreach ($Resolver as $Key => $Grp) {
      if (!is_array($Grp) || !isset($Grp["groupitems"]) || !is_array($Grp["groupitems"])) continue;

      if (isset($Grp["text"]) && is_array($Grp["text"])) {
        foreach (array("g", "e") as $L) {
          if (!isset($Grp["text"][$L]) || !is_array($Grp["text"][$L])) continue;
          foreach ($Grp["text"][$L] as $I => $Row) {
            if (isset($Row["type"], $Row["code"]) && $Row["type"] == "code") {
              $Both = $this->getTextBothLang($Row["code"]);
              $Grp["text"][$L][$I] = array("type" => "text", "text" => ($L == "g" ? $Both["german"] : $Both["english"]));
            }
          }
        }
      }

      foreach ($Grp["groupitems"] as $GI => $Item) {
        if (isset($Item["comments"]) && is_array($Item["comments"])) {
          foreach (array("g", "e") as $L) {
            if (!isset($Item["comments"][$L]) || !is_array($Item["comments"][$L])) continue;
            foreach ($Item["comments"][$L] as $I => $C) {
              if (isset($C["type"], $C["code"]) && $C["type"] == "code") {
                $Both = $this->getTextBothLang($C["code"]);
                $Item["comments"][$L][$I] = array("type" => "text", "text" => ($L == "g" ? $Both["german"] : $Both["english"]));
              }
            }
          }
        }
        if (isset($Item["short"]["code"]) && !isset($Item["short"]["label"])) {
          $Lbl = $this->getText($Item["short"]["code"]);
          if ($Lbl !== "")  $Item["short"]["label"] = $Lbl;
        }
        $Grp["groupitems"][$GI] = $Item;
      }

      $Resolver[$Key] = $Grp;
    }
    return $Resolver;
  }

  public function login()
  {
    if (APPDEBUG["FKT"]) $this->debug("FKT", __METHOD__);

    $Appl  = (isset($this->posted["appl"]) && trim($this->posted["appl"]))
      ? trim(strtolower($this->posted["appl"])) : "";

    if (($Appl && $Appl != $this->appl) || !$Appl) $this->finalize(array("status" => -1, "code" => "wrongappl"));

    $this->results = $this->checkUser();

    if (isset($this->results["stat"]) && $this->results["stat"] == "error") {

      if (APPDEBUG["LOG"]) $this->debug("LOG", "User Login ERROR");
      if (APPDEBUG["LOG"]) $this->debug("LOG", $this->user);

      $this->finalize($this->results);
    }

    $this->user       = isset($this->results["user"]) ? $this->results["user"] : array();
    if (APPDEBUG["LOG"]) $this->debug("LOG", "User Login OK");
    if (APPDEBUG["LOG"]) $this->debug("LOG", $this->user);

    $this->results += array(
      "status" => 1,
      "code"   => "logingranted"
    );

    $this->updStatDB("Action", "Login");

    if (isset($this->results["login"]) && trim($this->results["login"])) {
      $this->updUserLoginDB($this->results["login"]);
    }

    $this->finalize($this->results);
  }

  public function search()
  {
    if (APPDEBUG["FKT"]) $this->debug("FKT", __METHOD__);

    $Appl  = (isset($this->posted["appl"])            && trim($this->posted["appl"]))                ? trim(strtolower($this->posted["appl"])) : "";
    $Area  = (isset($this->posted["area"])            && trim($this->posted["area"]))                ? trim(strtolower($this->posted["area"])) : "";

    if (($Appl && $Appl != $this->appl)                 || !$Appl) $this->finalize(array("status" => -1, "code" => "wrongappl"));
    if (($Area && !in_array($Area, $this->searchAreas)) || !$Area) $this->finalize(array("status" => -1, "code" => "wrongarea"));

    $Search  = array();
    $Search["search"]    = (isset($this->posted["search"])   && is_array($this->posted["search"]))                                    ? $this->posted["search"]        : array();
    $Search["start"]     = (isset($this->posted["start"])    && is_numeric($this->posted["start"]) && $this->posted["start"] >=    0) ? intval($this->posted["start"]) : 0;
    $Search["limit"]     = (isset($this->posted["limit"])    && is_numeric($this->posted["limit"]) && $this->posted["limit"] <= 1000) ? intval($this->posted["limit"]) : 25;
    $Level               = (isset($this->posted["level"])    && trim($this->posted["level"]))                                         ? trim($this->posted["level"])   : "";
    $Search["url"]       = $this->buildBasicURL($Level);
    $Search["level"]     = (isset($this->posted["level"])    && trim($this->posted["level"]))         ? trim($this->posted["level"])  : "";
    $Search["sort"]      = (isset($this->posted["sort"])     && in_array($this->posted["sort"], array("yeardesc", "yearasc", "acqdesc", "authorasc"))) ? $this->posted["sort"]          : "";
    $Search["spell"]     = (isset($this->posted["spell"])    && is_bool($this->posted["spell"])    && $this->posted["spell"])         ? true                           : false;
    $Search["phonetic"]  = (isset($this->posted["phonetic"]) && is_bool($this->posted["phonetic"]) && $this->posted["phonetic"])      ? true                           : false;
    $Search["facets"]    = (isset($this->posted["facets"])   && is_array($this->posted["facets"]))                                    ? $this->posted["facets"]        : array();
    $Search["counts"]    = (isset($this->posted["counts"])   && is_array($this->posted["counts"]))                                    ? $this->posted["counts"]        : array();
    $this->search        = $Search;

    $NichtZaehlen = !empty($this->posted["nocount"]);

    $Anfrage   = isset($Search["search"]["query"]) ? trim((string) $Search["search"]["query"]) : "";
    $Startseite = ( empty($this->posted["advanced"])
                    && preg_match('/^(acqdate|erwdatum|new|neu)\([^()]*\)$/i', $Anfrage) === 1 );

    if (
      !$NichtZaehlen
      && !$Startseite
      && isset($Search["search"]["query"])
      && !in_array(substr($Search["search"]["query"], 0, 3), array("id:", "id("))
    ) {
      $this->updStatDB("Level", $Level);
      $SearchMode = (in_array(substr($Search["search"]["query"], 0, 8), array("ppnlink:", "ppnlink("))) ? "Related" : "Search";
      $this->updStatDB($SearchMode, "Query");

      if ( !empty($this->posted["advanced"]) && $Search["start"] == 0 )
        $this->updStatDB("Search", "Advanced");

      if ( $SearchMode == "Search" && $Search["start"] == 0 && $Search["limit"] > 0 )
        $this->updStatDB("Counter", "Searches_Platform");

      $Design = isset($this->posted["design"]) ? strtolower(trim((string) $this->posted["design"])) : "";
      if ( $Design !== "" && preg_match('/^[a-z]{1,20}$/', $Design) )  $this->updStatDB("Design", $Design);

      if (isset($Search["facets"]["yearmin"]) || isset($Search["facets"]["yearmax"])) $this->updStatDB($SearchMode, "Range");
      foreach ($Search["facets"] as $Key => $Val) {
        if (in_array($Key, array("yearmin", "yearmax")))  continue;
        $this->updStatDB($SearchMode, ucfirst($Key));
      }
    }

    $Mode = (isset($this->posted["display"]["mode"]) && is_array($this->posted["display"]["mode"]))
              ? $this->posted["display"]["mode"] : array();
    $Lib  = (isset($Mode["lib"]) && $Mode["lib"]) ? true : false;

    $Display = array();
    $Display["paia"]     = $Lib;
    $Display["comb"]     = $Lib;
    $Display["logs"]     = $Lib;
    $Display["mode"]     = $Mode;

    $Display["marc"]     = $Lib || (isset($this->posted["display"]["marc"])  && is_bool($this->posted["display"]["marc"])  && $this->posted["display"]["marc"])  ? true : false;
    $Display["bib"]      = (isset($this->posted["display"]["bib"])   && is_bool($this->posted["display"]["bib"])   && $this->posted["display"]["bib"])   ? true : false;
    $Display["items"]    = (isset($this->posted["display"]["items"]) && is_bool($this->posted["display"]["items"]) && $this->posted["display"]["items"]) ? true : false;
    $this->display = $Display;

    $this->ReadArea($Area);

    if (isset($Search["counts"]["links"]) && $Search["counts"]["links"]) {

      $Links = $this->readDB("adminstructures", $this->client, 0, 100, ["type" => "facet1"], ["sort"]);

      foreach ($Links as &$L) {
        $this->results["counts"]["links"][] = [
          "code"   => "norm",
          "label"  => trim($L["shortcutl1"]) ? $this->getText($L["shortcutl1"]) : "",
          "label2" => trim($L["shortcutl2"]) ? $this->getText($L["shortcutl2"]) : "",
          "label3" => trim($L["shortcutl3"]) ? $this->getText($L["shortcutl3"]) : "",
          "value"  => trim($L["action"])     ? $L["action"]                     : ""
        ];
      }
    }

    if ( empty($this->results["hitspack"]) && !empty($this->results["data"]) && is_array($this->results["data"]) ) {
      $Anzahl = count($this->results["data"]);
      $this->results["status"]   = 0;
      $this->results["hits"]     = $Anzahl;
      $this->results["hitspack"] = $Anzahl;
      $this->results["start"]    = $Search["start"];
    }

    if (isset($this->results["hitspack"]) && $this->results["hitspack"] > 0) {
      if ($Display["bib"] && !$NichtZaehlen) {
        $this->updStatDB("Media", "preview", $this->results["hitspack"]);
      }
      $this->results += array(
        "status" => 1,
        "code"   => "dataloaded"
      );
    } else {
      $this->results += array(
        "status" => 0,
        "code"   => "nodata"
      );

      $Weiter = $this->getWiderLevel($Level);
      if ($Weiter) {
        $this->results["levelup"] = array(
          "level"    => $Weiter,

          "label"    => $this->getText("level." . $Weiter),
          "textcode" => "level." . $Weiter,
          "filters"  => $this->countLevelFilters($this->levels[$Weiter]),
          "from"     => array(
            "level"   => $Level,
            "filters" => $this->countLevelFilters($this->levels[$Level]),
          ),
        );
      }

      $NoHitsDialog = isset($this->configs["other"]["nohitsdialog"])
                      ? trim($this->configs["other"]["nohitsdialog"]) : "";

      if ($NoHitsDialog !== "" && preg_match('/^https?:\/\//i', $NoHitsDialog)) {
        $Label = $this->getText("nohitsdialog.link");
        if ($Label === "")  $Label = $this->getText("search.nohitslink");
        $this->results["nohitsdialog"] = array(
          "link"  => $NoHitsDialog,
          "label" => $Label
        );
      }
      elseif ($NoHitsDialog !== "" && isset($this->configs["dialogs"][$NoHitsDialog]["fields"])) {

        $Label = $this->getText("nohitsdialog." . $NoHitsDialog);
        if ($Label === "") {
          foreach ($this->configs["dialogs"][$NoHitsDialog]["fields"] as $Feld) {
            if (isset($Feld["fieldtype"]) && $Feld["fieldtype"] == "head"
              && isset($Feld["label"]) && trim($Feld["label"]) !== "") {
              $Label = $Feld["label"];
              break;
            }
          }
        }

        $this->results["nohitsdialog"] = array(
          "code"  => $NoHitsDialog,
          "label" => $Label
        );
      }
    }

    $this->finalize($this->results);
  }
}
