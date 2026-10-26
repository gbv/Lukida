<?php


















class folio extends bms
{
  
  private const CANCELREASON = "75187e8d-e25a-47a7-89ad-23ba612338de";

  
  private const OPENREQUESTS = array("Open - Not yet filled", "Open - In transit", "Open - Awaiting pickup");

  private static $systemtokens = array();   
  private static $verified     = array();   

  private $lookups = array();
  private $tenant;
  private $systemuser;
  private $systempass;

  public function __construct($BMSID, $CS)
  {
    parent::__construct($BMSID, $CS);

    
    $this->tenant     = $this->option("tenant");
    $this->systemuser = $this->option("systemuser");
    $this->systempass = $this->option("systempass");

    if ( APPDEBUG["FKT"] ) $this->CS->debug("FKT",__METHOD__);
  }

  
  
  

  private function configured()
  {
    if ( $this->link && $this->tenant && $this->systemuser && $this->systempass )  return true;

    $this->CS->debug("ERR", array("type" => "FOLIO", "error" => "adminbms.options unvollstaendig (tenant, systemuser, systempass)", "bmsid" => $this->bmsid));
    return false;
  }

  
  private function login($User, $Pass)
  {
    $Response = $this->http("POST", $this->link . "/authn/login-with-expiry",
                            array("username" => $User, "password" => $Pass),
                            array("Content-Type: application/json", "x-okapi-tenant: " . $this->tenant));

    if ( preg_match('/^Set-Cookie:\s*folioAccessToken=([^;\s]+)/mi', $Response["headers"], $Match) )
    {
      $Expires = isset($Response["json"]["accessTokenExpiration"]) ? strtotime($Response["json"]["accessTokenExpiration"]) : false;
      return array("token" => $Match[1], "expires" => $Expires ?: time() + 600);
    }
    return array("error" => $Response["http"], "message" => $Response["json"]["errors"][0]["message"] ?? "");
  }

  private function systemToken($Renew = false)
  {
    $Key = md5($this->link . "|" . $this->tenant . "|" . $this->systemuser);

    if ( !$Renew && isset(self::$systemtokens[$Key]) && self::$systemtokens[$Key]["expires"] > time() + 60 )  return self::$systemtokens[$Key]["token"];

    
    
    $File = $this->CS->prepareCachePath(array("settings"), "foliosystemtoken");
    if ( !$Renew && file_exists($File) )
    {
      $Cached = json_decode((string) file_get_contents($File), true);
      if ( isset($Cached["key"], $Cached["token"], $Cached["expires"]) && $Cached["key"] === $Key && $Cached["expires"] > time() + 60 )
      {
        self::$systemtokens[$Key] = $Cached;
        return $Cached["token"];
      }
    }

    $Login = $this->login($this->systemuser, $this->systempass);
    if ( !isset($Login["token"]) )
    {
      $this->CS->debug("ERR", array("type" => "FOLIO", "error" => "Systemkonto: Anmeldung fehlgeschlagen", "http" => $Login["error"], "bmsid" => $this->bmsid));
      return "";
    }

    self::$systemtokens[$Key] = array("key" => $Key, "token" => $Login["token"], "expires" => $Login["expires"]);
    file_put_contents($File, json_encode(self::$systemtokens[$Key]));
    @chmod($File, 0600);
    return $Login["token"];
  }

  
  
  private function call($Method, $Path, $Data = null, $UserToken = null)
  {
    if ( APPDEBUG["FKT"] ) $this->CS->debug("FKT",__METHOD__);

    $Token = $UserToken ?? $this->systemToken();
    for ( $Try = 0; $Try < 2; $Try++ )
    {
      $Headers = array("Content-Type: application/json", "x-okapi-tenant: " . $this->tenant);
      if ( $Token )  $Headers[] = "Cookie: folioAccessToken=" . $Token;

      $Response = $this->http($Method, $this->link . $Path, $Data, $Headers);
      if ( $UserToken !== null || $Response["http"] != 401 )  break;
      $Token = $this->systemToken(true);
    }
    return $Response;
  }

  private function getJson($Path)
  {
    $Response = $this->call("GET", $Path);
    return ( $Response["http"] == 200 && is_array($Response["json"]) ) ? $Response["json"] : array();
  }

  private function cql($Query)
  {
    return rawurlencode($Query);
  }

  private function cqlList($Values)
  {
    return "(" . implode(" or ", array_map(fn($V) => '"' . str_replace('"', '\"', $V) . '"', $Values)) . ")";
  }

  private function errorMessage($Response)
  {
    if ( isset($Response["json"]["errors"][0]["message"]) )  return $Response["json"]["errors"][0]["message"];
    if ( !$Response["ok"] )                                   return $this->CS->getText("account.servernotresponding");
    return trim(substr(strip_tags($Response["body"]), 0, 200));
  }

  
  
  private function verifyUser($Login, $Token)
  {
    if ( !$Token || !$this->configured() )  return null;

    if ( !array_key_exists($Token, self::$verified) )
    {
      $Response = $this->call("GET", "/bl-users/_self", null, $Token);
      self::$verified[$Token] = ( $Response["http"] == 200 && isset($Response["json"]["user"]["id"]) ) ? $Response["json"] : null;
    }

    $Self = self::$verified[$Token];
    if ( $Self === null )  return null;

    
    
    $User = $Self["user"];
    if ( $Login !== "" && !in_array((string) $Login, array((string) $User["id"], (string) ($User["username"] ?? ""), (string) ($User["barcode"] ?? "")), true) )  return null;

    return $Self;
  }

  
  private function lookup($Name)
  {
    if ( isset($this->lookups[$Name]) )  return $this->lookups[$Name];

    $Sources = array("locations"     => array("/locations?limit=10000",                  "locations",     "FolioLocations"),
                     "servicepoints" => array("/service-points?limit=10000",             "servicepoints", "FolioDesks"),
                     "loantypes"     => array("/loan-types?limit=10000",                 "loantypes",     "FolioLoanTypes"),
                     
                     
                     
                     "libraries"     => array("/location-units/libraries?limit=10000",   "loclibs",       "FolioLibraries"),
                     "policys"       => array("/loan-policy-storage/loan-policies?limit=10000", "loanPolicies", "FolioPolicys"));
    list($Path, $Field, $Stat) = $Sources[$Name];

    $File = $this->CS->prepareCachePath(array("settings"), "folio" . $Name);
    if ( file_exists($File) )
    {
      if ( MODE != "production" ) $this->CS->updStatDB("Cache", $Stat . ".yes");
      $Data = json_decode((string) file_get_contents($File), true);
      if ( is_array($Data) )  return $this->lookups[$Name] = $Data;
    }

    if ( MODE != "production" ) $this->CS->updStatDB("Cache", $Stat . ".no");
    $Data = array();
    foreach ( $this->getJson($Path)[$Field] ?? array() as $Record )
    {
      if ( isset($Record["id"]) )  $Data[$Record["id"]] = $Record;
    }
    
    if ( count($Data) )  file_put_contents($File, json_encode($Data));
    return $this->lookups[$Name] = $Data;
  }

  
  
  

  
  
  private const IDFIELDS = array("f1" => "id", "f2" => "instanceId", "f3" => "itemId", "f4" => "holdingsRecordId",
                                 "f5" => "pickupServicePointId", "f6" => "requestDate", "f7" => "requesterId",
                                 "f8" => "itemBarcode", "f9" => "userBarcode");

  private function getFolioID($Values)
  {
    $IDs = array();
    foreach ( self::IDFIELDS as $Short => $Field )
    {
      if ( isset($Values[$Field]) && $Values[$Field] !== "" )  $IDs[$Short] = $Values[$Field];
    }
    return http_build_query($IDs);
  }

  private function getFolioArray($ID)
  {
    parse_str((string) $ID, $Tmp);
    $IDs = array();
    foreach ( self::IDFIELDS as $Short => $Field )
    {
      if ( isset($Tmp[$Short]) && is_string($Tmp[$Short]) )  $IDs[$Field] = $Tmp[$Short];
    }
    return $IDs;
  }

  
  
  

  private function callNumber($Components, $Separator = " ")
  {
    if ( !is_array($Components) )  return "";
    $Prefix = trim($Components["prefix"] ?? "");
    $Number = trim($Components["callNumber"] ?? "");
    $Suffix = trim($Components["suffix"] ?? "");
    return trim(( $Prefix !== "" ? $Prefix . $Separator : "" ) . $Number . ( $Suffix !== "" ? " " . $Suffix : "" ));
  }

  private function serviceEntry($Action, $Extra = array())
  {
    return $Extra + array("limitation" => array(), "expected" => "-", "queue" => 0, "title" => "-", "delay" => "-", "href" => "-", "action" => $Action);
  }

  
  private function parseItems($Records, $Format)
  {
    $Locations = $this->lookup("locations");
    $LoanTypes = $this->lookup("loantypes");
    $Libraries = $this->lookup("libraries");

    $Items       = array();
    $NeedIDs     = false;    
    $QueueItems  = array();  

    foreach ( $Records as $Exp )
    {
      if ( !isset($Exp["id"], $Exp["hrid"]) )  continue;

      $ExpID    = $Exp["hrid"];
      $LocID    = $Exp["effectiveLocationId"] ?? "";
      $Location = $Locations[$LocID] ?? array();
      $Storage  = ( $Location["discoveryDisplayName"] ?? "" ) ?: ( $Location["name"] ?? "" );

      $Item = array(
        "epn"       => $ExpID,
        "count"     => 0,
        "id"        => $this->getFolioID(array("itemId"               => $Exp["id"],
                                               "holdingsRecordId"     => $Exp["holdingsRecordId"] ?? "",
                                               "itemBarcode"          => $Exp["barcode"] ?? "",
                                               "pickupServicePointId" => $Location["primaryServicePoint"] ?? "")),
        "about"     => trim(( $Exp["enumeration"] ?? "" ) ?: ( ( $Exp["volume"] ?? "" ) ?: ( $Exp["chronology"] ?? "" ) )),
        "label"     => $this->callNumber($Exp["effectiveCallNumberComponents"] ?? array()),
        "storage"   => $Storage,
        "storageid" => $Location["code"] ?? "",
        "status"    => str_replace(" ", "", strtolower($Exp["status"]["name"] ?? "")),
      );
      if ( $Item["label"] === "" )  unset($Item["label"]);

      
      
      
      
      
      
      
      
      
      
      
      
      
      
      
      
      
      
      
      
      
      
      
      
      
      
      
      
      
      
      $Library = $Libraries[$Location["libraryId"] ?? ""] ?? array();
      if ( ($Library["name"] ?? "") !== "" )  $Item["department"]   = trim($Library["name"]);
      if ( ($Library["code"] ?? "") !== "" )  $Item["departmentid"] = trim($Library["code"]);

      $StoreLoan = array("storageid" => $LocID, "storage" => $Storage ?: "-");

      switch ( strtolower($Exp["status"]["name"] ?? "") )
      {
        case "available":
        {
          $LoanType     = strtolower(substr($LoanTypes[$Exp["permanentLoanTypeId"] ?? ""]["name"] ?? "", 0, 3));
          $Item["loan"] = in_array($LoanType, array("u -", "b -", "c -"));   

          
          $Details = $Location["details"] ?? array();
          if ( is_array($Details) && ( $Details["Zugänglichkeit"] ?? "" ) == "geschlossen" )
          {
            $NeedIDs                   = true;
            $Item["loanitems"]         = array($this->serviceEntry("order", $StoreLoan));
            $Item["presentation"]      = false;
            $Item["presentationitems"] = array($this->serviceEntry("order"));
          }
          else
          {
            $Item["loanitems"]         = array($this->serviceEntry("-"));
            $Item["presentation"]      = true;
            $Item["presentationitems"] = array($this->serviceEntry("-", $StoreLoan));
          }
          break;
        }
        case "checked out":
        case "paged":
        case "awaiting pickup":
        case "awaiting delivery":
        case "in transit":
        {
          $NeedIDs                   = true;
          $QueueItems[]              = $Exp["id"];
          $Item["loan"]              = true;
          $Item["loanitems"]         = array($this->serviceEntry("reserve", $StoreLoan));
          $Item["presentation"]      = false;
          $Item["presentationitems"] = array($this->serviceEntry("order"));
          break;
        }
        default:
        {
          
          $Item["loan"]              = false;
          $Item["loanitems"]         = array($this->serviceEntry("-"));
          $Item["presentation"]      = false;
          $Item["presentationitems"] = array($this->serviceEntry("-"));
        }
      }

      $Items[$ExpID] = $Item;
    }
    if ( !count($Items) )  return $Items;

    
    
    if ( $NeedIDs )
    {
      $First    = reset($Records);
      $HoldID   = trim((string) ($First["holdingsRecordId"] ?? ""));
      $Instance = "";
      if ( $HoldID !== "" )
      {
        
        $Vorrat   = $this->bmsBestandLesen("holding:" . $HoldID);
        $Instance = ( $Vorrat !== null ) ? (string) ($Vorrat["instance"] ?? "")
                                         : (string) ($this->holdingHolen(array($HoldID))[$HoldID] ?? "");
      }
      if ( $Instance !== "" )
      {
        foreach ( $Items as &$Item )
        {
          $Item["id"] = $this->getFolioID(array("instanceId" => $Instance) + $this->getFolioArray($Item["id"]));
        }
        unset($Item);
      }
    }

    
    
    
    if ( count($QueueItems) )
    {
      
      
      $Due   = array();
      $Queue = array();
      $Offen = array();
      foreach ( $QueueItems as $ItemID )
      {
        $Vorrat = $this->bmsBestandLesen("leihe:" . $ItemID);
        if ( $Vorrat === null ) { $Offen[] = $ItemID; continue; }
        if ( trim((string) ($Vorrat["due"] ?? "")) !== "" )  $Due[$ItemID]   = $Vorrat["due"];
        if ( (int) ($Vorrat["queue"] ?? 0) > 0 )             $Queue[$ItemID] = (int) $Vorrat["queue"];
      }
      if ( count($Offen) )
      {
        list($NachDue, $NachQueue) = $this->leiheHolen($Offen);
        $Due   += $NachDue;
        $Queue += $NachQueue;
      }
      foreach ( $Items as &$Item )
      {
        $ItemID = $this->getFolioArray($Item["id"])["itemId"] ?? "";
        if ( !in_array($ItemID, $QueueItems, true) )  continue;
        $Item["loanitems"][0]["expected"] = isset($Due[$ItemID]) ? $this->CS->format2German($Due[$ItemID]) : "-";
        $Item["loanitems"][0]["queue"]    = $Queue[$ItemID] ?? 0;
      }
      unset($Item);
    }

    foreach ( $Items as &$Item )
    {
      $Item["bandlist"] = in_array($Format, $this->CS->formatsBandlist) && $Item["about"] !== "" && count($Items) > 1;
    }
    unset($Item);

    
    uasort($Items, function ($a, $b) { return $a['about'] <=> $b['about']; });
    return $Items;
  }

  
  
  

  
  private function readNotes($UUID)
  {
    $Notes  = array();
    $Blocks = array();
    $Names  = array("borrowing" => array("Ausleihe", "borrowing"),
                    "renewals"  => array("Leihfristverlängerung", "renewals"),
                    "requests"  => array("Bestellung/Vormerkung", "requests"));

    $Manual    = $this->getJson("/manualblocks?limit=100&query=" . $this->cql("userId==" . $UUID))["manualblocks"] ?? array();
    $Automated = $this->getJson("/automated-patron-blocks/" . rawurlencode($UUID))["automatedPatronBlocks"] ?? array();
    foreach ( array_merge($Manual, $Automated) as $MB )
    {
      $Text = $MB["patronMessage"] ?? ( $MB["message"] ?? "" );
      if ( trim($Text) !== "" )  $Notes[] = array("label" => $Text);
      foreach ( $Names as $Flag => $Name )
      {
        if ( !empty($MB[$Flag]) )  $Blocks[] = $this->lang == "g" ? $Name[0] : $Name[1];
      }
    }

    
    
    
    
    
    foreach ( $this->getJson("/note-links/domain/users/type/user/id/" . rawurlencode($UUID) . "?limit=100000&status=assigned&order=desc&orderBy=updatedDate")["notes"] ?? array() as $MB )
    {
      if ( ( $MB["type"] ?? "" ) != "Discovery" || !trim($MB["content"] ?? "") )  continue;

      
      
      
      
      
      $Text = $this->CS->htmlToPlainText($MB["content"]);
      if ( trim($Text) )  $Notes[] = array("label" => $Text);
    }

    return array("notes" => $Notes, "blocks" => array_values(array_unique($Blocks)));
  }

  private function readAccountItems($UUID)
  {
    $ISIL     = $this->config["isil"] ?? "";
    $Policies = $this->lookup("policys");
    $Items    = array();

    
    $Loans = $this->getJson("/circulation/loans?limit=10000&query=" . $this->cql("userId==" . $UUID . ' and status.name=="Open" sortby dueDate/sort.descending'))["loans"] ?? array();

    
    $Queue   = array();
    $ItemIDs = array_values(array_filter(array_column($Loans, "itemId")));
    if ( count($ItemIDs) )
    {
      foreach ( $this->getJson("/circulation/requests?limit=10000&query=" . $this->cql("itemId==" . $this->cqlList($ItemIDs) . " and status==" . $this->cqlList(self::OPENREQUESTS)))["requests"] ?? array() as $Req )
      {
        if ( isset($Req["itemId"]) )  $Queue[$Req["itemId"]] = ( $Queue[$Req["itemId"]] ?? 0 ) + 1;
      }
    }

    foreach ( $Loans as $Loan )
    {
      $Policy   = $Policies[$Loan["loanPolicyId"] ?? ""] ?? array();
      $Allowed  = $Policy["renewalsPolicy"]["numberAllowed"] ?? 0;
      $Renewals = $Loan["renewalCount"] ?? 0;
      $QueuePos = $Queue[$Loan["itemId"] ?? ""] ?? 0;

      $Items[] = array(
        "item"      => $this->getFolioID(array("id"          => $Loan["id"] ?? "",
                                               "itemId"      => $Loan["itemId"] ?? "",
                                               "itemBarcode" => $Loan["item"]["barcode"] ?? "",
                                               "userBarcode" => $Loan["borrower"]["barcode"] ?? "")),
        "about"     => $Loan["item"]["title"] ?? "",
        "label"     => $this->callNumber($Loan["item"]["callNumberComponents"] ?? array(), ":"),
        "storage"   => $Loan["checkoutServicePoint"]["discoveryDisplayName"] ?? "",
        "storageid" => $Loan["item"]["location"]["name"] ?? "",
        "starttime" => $Loan["loanDate"] ?? "",
        "endtime"   => $Loan["dueDate"] ?? "",
        "queue"     => $QueuePos,
        "renewals"  => $Renewals,
        "reminder"  => $Loan["reminders"]["lastFeeBilled"]["number"] ?? 0,
        "canrenew"  => $QueuePos == 0 && !empty($Policy["renewable"])
                       && ( !empty($Policy["renewalsPolicy"]["unlimited"]) || $Renewals < $Allowed ),
        "cancancel" => false,
        "edition"   => "",
        "isil"      => $ISIL,
        "status"    => 3,
      );
    }

    
    foreach ( $this->getJson("/circulation/requests?limit=10000&query=" . $this->cql("requesterId==" . $UUID . " and status==" . $this->cqlList(self::OPENREQUESTS)))["requests"] ?? array() as $Req )
    {
      $Pickup = ( $Req["status"] ?? "" ) == "Open - Awaiting pickup";
      $Hold   = ( $Req["requestType"] ?? "" ) == "Hold";

      $Items[] = array(
        "item"      => $this->getFolioID($Req),
        "about"     => $Req["instance"]["title"] ?? "",
        "label"     => $this->callNumber($Req["item"]["callNumberComponents"] ?? array(), ":") ?: ( $Req["item"]["callNumber"] ?? "" ),
        "storage"   => $Req["pickupServicePoint"]["discoveryDisplayName"] ?? "",
        "storageid" => $Req["item"]["location"]["name"] ?? "",
        "starttime" => $Req["requestDate"] ?? "",
        
        "endtime"   => $Pickup ? ( $Req["holdShelfExpirationDate"] ?? "" ) : "",
        "queue"     => $Req["position"] ?? "",
        "renewals"  => 0,
        "reminder"  => 0,
        "canrenew"  => false,
        "cancancel" => !$Pickup && $Hold,
        "edition"   => "",
        "isil"      => $ISIL,
        "status"    => $Pickup ? 4 : ( $Hold ? 1 : 2 ),
      );
    }
    return $Items;
  }

  private function readFees($UUID)
  {
    $Account  = $this->getJson("/patron/account/" . rawurlencode($UUID) . "?limit=10000&includeCharges=true");
    $Currency = $Account["totalCharges"]["isoCurrencyCode"] ?? "EUR";

    $Fees = array("amount" => number_format((float) ( $Account["totalCharges"]["amount"] ?? 0 ), 2, ".", "") . " " . $Currency,
                  "code"   => "account.userfees",
                  "items"  => array());
    foreach ( $Account["charges"] ?? array() as $Charge )
    {
      $Fees["items"][] = array(
        "feetype" => $Charge["reason"] ?? "",
        "about"   => $Charge["item"]["title"] ?? "",
        "amount"  => number_format((float) ( $Charge["chargeAmount"]["amount"] ?? 0 ), 2, ".", "") . " " . ( $Charge["chargeAmount"]["isoCurrencyCode"] ?? $Currency ),
        "date"    => $Charge["accrualDate"] ?? "",   
      );
    }
    return $Fees;
  }

  
  private function userData($Self)
  {
    $User     = $Self["user"];
    $Personal = $User["personal"] ?? array();
    $Address  = $Personal["addresses"][0] ?? array();

    return array(
      "uuid"     => $User["id"],
      "username" => $User["username"] ?? "",
      "barcode"  => $User["barcode"] ?? "",
      "name"     => implode(" ", array_filter(array($Personal["firstName"] ?? "", $Personal["lastName"] ?? ""))),
      "email"    => $Personal["email"] ?? "",
      "address"  => implode(", ", array_filter(array($Address["addressLine1"] ?? "",
                                                     $Address["addressLine2"] ?? "",
                                                     trim(( $Address["postalCode"] ?? "" ) . " " . ( $Address["city"] ?? "" )),
                                                     $Address["countryId"] ?? ""))),
      
      "expires"  => isset($User["expirationDate"]) ? substr($User["expirationDate"], 0, 10) : "",
      "type"     => $Self["patronGroup"]["desc"] ?? ( $Self["patronGroup"]["group"] ?? "" ),
      "expired"  => ( isset($User["active"]) && !$User["active"] )
                    || ( isset($User["expirationDate"]) && strtotime($User["expirationDate"]) < time() ),
      "message"  => trim($User["customFields"]["discoverynachricht"] ?? ""),
    );
  }

  private function loginError($More)
  {
    return array("stat" => "error",
                 "mess" => array($this->CS->getText("loginerror")),
                 "more" => array($More));
  }

  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  private const VORRATSBLOCK = 50;

  public function prepareItems($Map)
  {
    if ( APPDEBUG["FKT"] ) $this->CS->debug("FKT",__METHOD__);
    if ( !$this->configured() || !is_array($Map) )  return;

    
    $Woher = array();
    foreach ( $Map as $PPN => $EPNs )
    {
      $PPN = trim((string) $PPN);
      if ( $PPN === "" || !is_array($EPNs) )                 continue;
      if ( $this->bmsBestandLesen("ppn:" . $PPN) !== null )  continue;
      foreach ( $EPNs as $EPN )
      {
        $EPN = trim((string) $EPN);
        if ( $EPN !== "" )  $Woher[$EPN] = $PPN;
      }
    }
    
    if ( count($Woher) < 2 )  return;

    foreach ( array_chunk(array_keys($Woher), self::VORRATSBLOCK) as $Block )
    {
      $Response = $this->call("GET", "/item-storage/items?limit=10000&query="
                                     . $this->cql("hrid==" . $this->cqlList($Block)));
      if ( $Response["http"] != 200 || !isset($Response["json"]["items"]) || !is_array($Response["json"]["items"]) )  continue;

      $Treffer = array();
      foreach ( $Response["json"]["items"] as $Item )
      {
        $PPN = $Woher[trim((string) ($Item["hrid"] ?? ""))] ?? "";
        if ( $PPN !== "" )  $Treffer[$PPN]["items"][] = $Item;
      }
      foreach ( $Treffer as $PPN => $Json )  $this->bmsBestandSchreiben("ppn:" . $PPN, $Json);

      
      
      
      
      
      
      
      
      
      
      
      
      
      
      
      
      
      $Verleiht = array();
      foreach ( $Response["json"]["items"] as $Item )
      {
        if ( !isset($Item["id"]) )                                             continue;
        if ( strtolower($Item["status"]["name"] ?? "") === "available" )        continue;
        if ( !isset($Woher[trim((string) ($Item["hrid"] ?? ""))]) )             continue;
        $Verleiht[] = $Item["id"];
      }
      if ( count($Verleiht) )  $this->leiheHolen($Verleiht);

      
      $Holdings = array();
      foreach ( $Response["json"]["items"] as $Item )
      {
        if ( isset($Item["holdingsRecordId"]) && isset($Woher[trim((string) ($Item["hrid"] ?? ""))]) )
        {
          $Holdings[] = $Item["holdingsRecordId"];
        }
      }
      if ( count($Holdings) )  $this->holdingHolen($Holdings);
    }
  }

  
  
  
  
  
  
  private function holdingHolen($HoldingIDs)
  {
    $Instanzen = array();
    foreach ( array_chunk(array_values(array_unique(array_filter($HoldingIDs))), self::VORRATSBLOCK) as $Block )
    {
      foreach ( $this->getJson("/holdings-storage/holdings?limit=10000&query=" . $this->cql("id==" . $this->cqlList($Block)))["holdingsRecords"] ?? array() as $H )
      {
        if ( isset($H["id"], $H["instanceId"]) )  $Instanzen[$H["id"]] = $H["instanceId"];
      }
      
      foreach ( $Block as $ID )
      {
        $this->bmsBestandSchreiben("holding:" . $ID, array("instance" => $Instanzen[$ID] ?? ""));
      }
    }
    return $Instanzen;
  }

  
  
  
  private function leiheHolen($ItemIDs)
  {
    $Due   = array();
    $Queue = array();
    foreach ( $this->getJson("/circulation/loans?limit=10000&query=" . $this->cql("itemId==" . $this->cqlList($ItemIDs) . ' and status.name=="Open"'))["loans"] ?? array() as $Loan )
    {
      if ( isset($Loan["itemId"], $Loan["dueDate"]) )  $Due[$Loan["itemId"]] = $Loan["dueDate"];
    }
    foreach ( $this->getJson("/circulation/requests?limit=10000&query=" . $this->cql("itemId==" . $this->cqlList($ItemIDs) . " and status==" . $this->cqlList(self::OPENREQUESTS)))["requests"] ?? array() as $Req )
    {
      if ( isset($Req["itemId"]) )  $Queue[$Req["itemId"]] = ( $Queue[$Req["itemId"]] ?? 0 ) + 1;
    }
    
    
    foreach ( $ItemIDs as $ItemID )
    {
      $this->bmsBestandSchreiben("leihe:" . $ItemID, array("due"   => $Due[$ItemID]   ?? "",
                                                           "queue" => $Queue[$ItemID] ?? 0));
    }
    return array($Due, $Queue);
  }

  public function getPPN($PPN, $Format)
  {
    if ( APPDEBUG["FKT"] ) $this->CS->debug("FKT",__METHOD__);
    if ( !$this->configured() )  return array();

    
    
    
    
    
    
    
    $Json = $this->bmsBestandLesen("ppn:" . $PPN);
    if ( $Json === null )
    {
      $Response = $this->call("GET", "/item-storage/items?limit=1000&query=" . $this->cql('instance.hrid=="' . $PPN . '"'));
      
      
      $Json = ( $Response["http"] == 200 && is_array($Response["json"]) ) ? $Response["json"] : array();
      if ( count($Json) )  $this->bmsBestandSchreiben("ppn:" . $PPN, $Json);

      if ( !empty($this->CS->display["paia"]) )
      {
        $this->CS->results["paia"][$this->CS->params["shift"] ? $this->CS->params["childid"] : $PPN][] = $Response["json"] ?? $Response["body"];
      }
    }
    elseif ( !empty($this->CS->display["paia"]) )
    {
      $this->CS->results["paia"][$this->CS->params["shift"] ? $this->CS->params["childid"] : $PPN][] = $Json;
    }

    return $this->parseItems($Json["items"] ?? array(), $Format);
  }

  public function getEPN($EPN, $Format)
  {
    if ( APPDEBUG["FKT"] ) $this->CS->debug("FKT",__METHOD__);
    if ( !$this->configured() )  return array();

    
    $Json = $this->bmsBestandLesen("epn:" . $EPN);
    if ( $Json === null )
    {
      $Json = $this->getJson("/item-storage/items?query=" . $this->cql('hrid=="' . $EPN . '"'));
      if ( is_array($Json) && count($Json) )  $this->bmsBestandSchreiben("epn:" . $EPN, $Json);
    }
    return $this->parseItems($Json["items"] ?? array(), $Format);
  }

  public function checkUser($User, $Pass)
  {
    if ( APPDEBUG["FKT"] ) $this->CS->debug("FKT",__METHOD__);
    if ( !$this->configured() )  return $this->loginError($this->CS->getText("account.servernotresponding"));

    
    
    $Login = $this->login($User, $Pass);
    if ( !isset($Login["token"]) )
    {
      return $this->loginError($Login["error"] ? $this->CS->getText("account.checklogin") : $this->CS->getText("account.servernotresponding"));
    }

    $Self = $this->verifyUser("", $Login["token"]);
    if ( $Self === null )  return $this->loginError($this->CS->getText("account.checklogin"));

    $Data   = $this->userData($Self);
    $UUID   = $Data["uuid"];
    $Return = array("user" => array("token" => $Login["token"], "login" => $UUID), "login" => $UUID);
    $Notes  = array("notes" => array(), "blocks" => array());

    if ( !empty($this->CS->display["user"]) )
    {
      $Notes = $this->readNotes($UUID);
      $Return["user"] += array(
        "uuid"     => $UUID,
        "username" => $Data["username"],
        "barcode"  => $Data["barcode"],
        "name"     => $Data["name"],
        "email"    => $Data["email"],
        "address"  => $Data["address"],
        "expires"  => $Data["expires"],
        "status"   => $Data["expired"] ? "2" : ( count($Notes["blocks"]) ? "1" : "0" ),
        "type"     => array($Data["type"]),
        "notes"    => array_merge($Data["message"] !== "" ? array(array("label" => $Data["message"])) : array(), $Notes["notes"]),
      );
    }

    if ( !empty($this->CS->display["items"]) )  $Return["items"] = $this->readAccountItems($UUID);
    if ( !empty($this->CS->display["fees"]) )   $Return["fees"]  = $this->readFees($UUID);

    
    $Return["user"]["accountstatus"] = "";
    if ( $Data["expired"] )
    {
      $Return["user"]["accountstatus"] = $this->CS->getText("account.lockedexpired");
    }
    elseif ( count($Notes["blocks"]) )
    {
      $Return["user"]["accountstatus"] = $this->CS->getText("account.lockedlist") . ": " . implode(", ", $Notes["blocks"]);
    }
    if ( $Return["user"]["accountstatus"] !== "" )  $Return["user"]["notes"][] = array("label" => $Return["user"]["accountstatus"]);
    $Return["user"]["blocked"] = $Return["user"]["accountstatus"] !== "";

    if ( in_array(4, array_column($Return["items"] ?? array(), "status"), true) )
    {
      $Return["user"]["notes"][] = array("label" => $this->CS->getText("account.collectableremark"));
    }

    return $Return + array("stat" => "success",
                           "mess" => array($this->CS->getText("loginsuccessful")));
  }

  public function ReadUser($Login, $Token)
  {
    if ( APPDEBUG["FKT"] ) $this->CS->debug("FKT",__METHOD__);

    $Self = $this->verifyUser((string) $Login, (string) $Token);
    if ( $Self === null )  return array();

    $Data = $this->userData($Self);
    return array(
      "username" => $Data["uuid"],      
      "name"     => $Data["name"],
      "email"    => $Data["email"],
      "address"  => $Data["address"],
      "status"   => $Data["expired"] ? "2" : "0",
      "type"     => $Data["type"],
      "expires"  => $Data["expires"],
      "note"     => $Data["message"],
    );
  }

  public function Request($Login, $ID, $Token, $Cond, $Type)
  {
    if ( APPDEBUG["FKT"] ) $this->CS->debug("FKT",__METHOD__);

    $Self = $this->verifyUser((string) $Login, (string) $Token);
    if ( $Self === null )  return $this->actionError("action.requesterror", $this->CS->getText("account.checklogin"));

    $FIDs = $this->getFolioArray($ID);
    foreach ( array("instanceId", "itemId", "holdingsRecordId", "pickupServicePointId") as $Field )
    {
      if ( !isset($FIDs[$Field]) )  return $this->actionError("action.requesterror", "missingvalue: " . $Field);
    }

    
    $Pickup = $FIDs["pickupServicePointId"];
    if ( isset($Cond["desk"]) && preg_match('/([0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12})$/i', $Cond["desk"], $Match) )  $Pickup = $Match[1];

    $Response = $this->call("POST", "/circulation/requests", array(
      "requestLevel"          => "Item",
      
      "requestType"           => ( $Type == "reserve" ) ? "Hold" : "Page",
      "fulfillmentPreference" => "Hold Shelf",
      "requestDate"           => gmdate('Y-m-d\TH:i:s\Z'),
      "requesterId"           => $Self["user"]["id"],     
      "instanceId"            => $FIDs["instanceId"],
      "itemId"                => $FIDs["itemId"],
      "holdingsRecordId"      => $FIDs["holdingsRecordId"],
      "pickupServicePointId"  => $Pickup,
    ));

    
    if ( !in_array($Response["http"], array(200, 201)) )  return $this->actionError("action.requesterror", $this->errorMessage($Response));

    
    
    $this->bmsBestandVerwerfen();

    return $this->actionSuccess($Type == "reserve" ? "action.reservesuccessful" : "action.ordersuccessful",
                                $this->answerLines($Type == "reserve" ? "reserve" : "", "", $Response["json"]["pickupServicePoint"]["discoveryDisplayName"] ?? ""));
  }

  public function Renew($Login, $ID, $Token)
  {
    if ( APPDEBUG["FKT"] ) $this->CS->debug("FKT",__METHOD__);

    $Self = $this->verifyUser((string) $Login, (string) $Token);
    if ( $Self === null )  return $this->actionError("action.renewerror", $this->CS->getText("account.checklogin"));

    $FIDs = $this->getFolioArray($ID);
    if ( !isset($FIDs["itemBarcode"], $FIDs["userBarcode"]) )  return $this->actionError("action.renewerror", "missingvalue");

    
    if ( $FIDs["userBarcode"] !== (string) ( $Self["user"]["barcode"] ?? "" ) )  return $this->actionError("action.renewerror", $this->CS->getText("account.checklogin"));

    $Response = $this->call("POST", "/circulation/renew-by-barcode", array("itemBarcode" => $FIDs["itemBarcode"],
                                                                          "userBarcode" => $FIDs["userBarcode"]));
    if ( !in_array($Response["http"], array(200, 201)) )  return $this->actionError("action.renewerror", $this->errorMessage($Response));

    $this->bmsBestandVerwerfen();
    return $this->actionSuccess("action.renewsuccessful", $this->answerLines("renew", $Response["json"]["dueDate"] ?? ""));
  }

  public function Cancel($Login, $ID, $Token)
  {
    if ( APPDEBUG["FKT"] ) $this->CS->debug("FKT",__METHOD__);

    $Self = $this->verifyUser((string) $Login, (string) $Token);
    if ( $Self === null )  return array("status" => -2, "code" => "action.cancelerror", "more" => $this->CS->getText("account.checklogin"));

    $FIDs = $this->getFolioArray($ID);
    if ( !isset($FIDs["id"]) )  return array("status" => -2, "code" => "missingvalue");

    
    $Request = $this->call("GET", "/circulation/requests/" . rawurlencode($FIDs["id"]));
    if ( $Request["http"] != 200 || !isset($Request["json"]["id"]) )  return array("status" => -2, "code" => "action.cancelerror", "more" => $this->errorMessage($Request));
    $Record = $Request["json"];
    if ( ( $Record["requesterId"] ?? "" ) !== $Self["user"]["id"] )  return array("status" => -2, "code" => "action.cancelerror", "more" => $this->CS->getText("account.checklogin"));

    $Put = array_intersect_key($Record, array_flip(array("id", "requestLevel", "requestType", "requestDate", "requesterId", "instanceId", "itemId",
                                                         "holdingsRecordId", "fulfillmentPreference", "pickupServicePointId", "position")));
    $Put["status"]               = "Closed - Cancelled";
    $Put["cancellationReasonId"] = $this->option("cancelreasonid", self::CANCELREASON);
    $Put["cancelledByUserId"]    = $Self["user"]["id"];
    $Put["cancelledDate"]        = gmdate('Y-m-d\TH:i:s\Z');

    $Response = $this->call("PUT", "/circulation/requests/" . rawurlencode($FIDs["id"]), $Put);
    if ( !in_array($Response["http"], array(200, 204)) )  return array("status" => -2, "code" => "action.cancelerror", "more" => $this->errorMessage($Response));

    $this->bmsBestandVerwerfen();
    return array("status" => 0);
  }

  
  public function ChangeMail($Login, $Token, $New)
  {
    if ( APPDEBUG["FKT"] ) $this->CS->debug("FKT",__METHOD__);

    $Self = $this->verifyUser((string) $Login, (string) $Token);
    if ( $Self === null )  return array("status" => -2, "more" => $this->CS->getText("account.checklogin"));

    $UUID = $Self["user"]["id"];
    $User = $this->call("GET", "/users/" . rawurlencode($UUID));
    if ( $User["http"] != 200 || !isset($User["json"]["id"]) )  return array("status" => -2, "more" => $this->errorMessage($User));

    $Record = $User["json"];
    $Record["personal"]["email"] = $New;
    $Response = $this->call("PUT", "/users/" . rawurlencode($UUID), $Record);
    if ( !in_array($Response["http"], array(200, 204)) )  return array("status" => -2, "more" => $this->errorMessage($Response));

    unset(self::$verified[$Token]);   
    return array("status" => 0, "email" => $New);
  }

  public function ChangePW($Login, $Token, $Old, $New)
  {
    if ( APPDEBUG["FKT"] ) $this->CS->debug("FKT",__METHOD__);

    $Self = $this->verifyUser((string) $Login, (string) $Token);
    if ( $Self === null )  return array("status" => -2, "more" => $this->CS->getText("account.checklogin"));

    $Response = $this->call("POST", "/authn/update", array("userId"      => $Self["user"]["id"],
                                                          "password"    => $Old,
                                                          "newPassword" => $New));
    if ( !in_array($Response["http"], array(200, 204)) )  return array("status" => -2, "more" => $this->errorMessage($Response));

    
    
    $Tmp = $this->checkUser($Self["user"]["username"] ?? $Self["user"]["id"], $New);
    $Tmp["status"] = 1;
    return $Tmp;
  }

  
  public function ResetPW($Login)
  {
    if ( APPDEBUG["FKT"] ) $this->CS->debug("FKT",__METHOD__);
    if ( !$this->configured() )  return array("status" => -2, "more" => $this->CS->getText("account.servernotresponding"));

    $Response = $this->call("POST", "/bl-users/forgotten/password", array("id" => (string) $Login));
    if ( !in_array($Response["http"], array(200, 204)) )  return array("status" => -2, "more" => $this->errorMessage($Response));

    return array("status" => 0);
  }
}

?>
