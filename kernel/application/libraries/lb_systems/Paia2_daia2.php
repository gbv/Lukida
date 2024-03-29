<?php

//$this->CI->printArray2File($response);

class Paia2_daia2 extends General
{
  protected $CI;
  private   $isil;
  private   $paia;
  private   $daia;
  private   $header;

  public function __construct($params)
  {
    // Assign the CodeIgniter super-object
    $this->CI =& get_instance();

    // Load URL Helper
    $this->CI->load->helper('url');

    if ( $params["isil"] == "" || $params["paia"] == "" || $params["daia"] == "" )  return false;
    $this->isil   = $params["isil"];
    $this->paia   = $params["paia"] . "/" . $params["isil"];
    $this->daia   = $params["daia"] . "/" . $params["isil"] . "/daia";
    $this->header = array('Content-type: application/json; charset=utf-8');
    if ( $_SESSION["language"] == "eng" ) $this->header[] = 'Accept-Language: en';
  }

  // ********************************************
  // ************** Tool-Functions **************
  // ********************************************

  private function getit($file, $access_token)
  {
    $http = curl_init();
    curl_setopt($http, CURLOPT_URL, $file);
    $AutoHeader   = $this->header;
    $AutoHeader[] = 'Authorization: Bearer '.$access_token;
    curl_setopt($http, CURLOPT_HTTPHEADER, $AutoHeader);
    curl_setopt($http, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($http, CURLOPT_CONNECTTIMEOUT, 2); 
    curl_setopt($http, CURLOPT_TIMEOUT, 20); 

    if ( substr(PHP_OS,0,3) )
    {
      curl_setopt ($http, CURLOPT_SSL_VERIFYHOST, 0);
      curl_setopt ($http, CURLOPT_SSL_VERIFYPEER, 0);
    }

    $data = curl_exec($http);
    curl_close($http);
    return $data;
  }

  private function postit($file, $data_to_send, $access_token = null)
  {
    // json-encoding
    $postData = json_encode($data_to_send);
    $http = curl_init();
    curl_setopt($http, CURLOPT_URL, $file);
    curl_setopt($http, CURLOPT_POST, true);
    curl_setopt($http, CURLOPT_POSTFIELDS, $postData);
    curl_setopt($http, CURLOPT_CONNECTTIMEOUT, 2); 
    curl_setopt($http, CURLOPT_TIMEOUT, 20); 
    curl_setopt($http, CURLOPT_ENCODING ,"utf-8");    

    if ( substr(PHP_OS,0,3) )
    {
      curl_setopt ($http, CURLOPT_SSL_VERIFYHOST, 0);
      curl_setopt ($http, CURLOPT_SSL_VERIFYPEER, 0);
    }

    if (isset($access_token)) {
      $AutoHeader   = $this->header;
      $AutoHeader[] = 'Authorization: Bearer '.$access_token;
      curl_setopt($http, CURLOPT_HTTPHEADER, $AutoHeader);
    } else {
      curl_setopt($http, CURLOPT_HTTPHEADER, $this->header);
    }
    curl_setopt($http, CURLOPT_RETURNTRANSFER, true);

    if( ! $data =  curl_exec($http))
    {
      trigger_error(curl_error($http));
    }

    if ( substr($file,-12) == "/auth/change" )  $data = utf8_decode($data);

    curl_close($http);
    return $data;
  }

  private function getAsArray($file)
  {

    $pure_response = $this->getit($file, $_SESSION[$this->isil]['paiaToken']);
    $json_start = strpos($pure_response, '{');
    $json_response = substr($pure_response, $json_start);
    // Convert booleans into 0 and 1 before json_decode to keep the values
    $json_response = str_replace(": true,", ": 1,", $json_response);
    $json_response = str_replace(": false,", ": 0,", $json_response);
    $loans_response = json_decode($json_response, true);

    // if the login auth token is invalid, renew it (this is possible unless the session is expired)
    if (isset($loans_response['error']) && $loans_response['code'] == '401') {
      $this->login($_SESSION[$this->isil]["userlogin"], $_SESSION[$this->isil]["userpassword"]);
      $pure_response = $this->getit($file, $_SESSION[$this->isil]['paiaToken']);
      $json_start = strpos($pure_response, '{');
      $json_response = substr($pure_response, $json_start);
      $loans_response = json_decode($json_response, true);
    }

    return $loans_response;
  }

  private function postAsArray($file, $data)
  {
    $pure_response = $this->postit($file, $data, $_SESSION[$this->isil]['paiaToken']);
    $json_start = strpos($pure_response, '{');
    $json_response = substr($pure_response, $json_start);
    $loans_response = json_decode($json_response, true);

    // if the login auth token is invalid, renew it (this is possible unless the session is expired)
    if ( isset($loans_response['error']) && ($loans_response['code']) )
    {
      if ($loans_response['error'] && $loans_response['code'] == '401') {
        $this->login($_SESSION[$this->isil]["userlogin"], $_SESSION[$this->isil]["userpassword"]);
        $pure_response = $this->postit($file, $data, $_SESSION[$this->isil]['paiaToken']);
        $json_start = strpos($pure_response, '{');
        $json_response = substr($pure_response, $json_start);
        $loans_response = json_decode($json_response, true);
      }
    }
    return $loans_response;
  }

  private function getPpnByBarcode($barcode)
  {
    $barcode = str_replace("/"," ",$barcode);
    if (preg_match("/bar:(.*)$/", $barcode, $match))
    {
      $barcode = $match[1];
    } else
    {
      return false;
    }
    $searchUrl = "XML=1.0/CMD?ACT=SRCHA&IKT=1016&SRT=YOP&TRM=bar+$barcode";

    $doc = new DomDocument();
    $doc->load($searchUrl);
    // get Availability information from DAIA
    $itemlist = $doc->getElementsByTagName('SHORTTITLE');
    if (isset($itemlist->item(0)->attributes) && count($itemlist->item(0)->attributes) > 0)
    {
      $ppn = $itemlist->item(0)->attributes->getNamedItem('PPN')->nodeValue;
    }
    else
    {
      return false;
    }
    return $ppn;
  }

  private function getUserDetails()
  {
    $pure_response = $this->getit($this->paia.'/core/' . $_SESSION[$this->isil]["userlogin"], $_SESSION[$this->isil]['paiaToken']);
    $json_start = strpos($pure_response, '{');
    $json_response = substr($pure_response, $json_start);
    $user_response = json_decode($json_response, true);

    $user = array();
    $user['username'] = $_SESSION[$this->isil]["userlogin"];
    if ( isset($user_response['name']) )
    {
      $nameArr = explode(',', $user_response['name']);
      if ( count($nameArr) == 2 )
      {
        $user['firstname'] = $nameArr[1];
        $user['lastname'] = $nameArr[0];
      }
      else
      {
        $user['firstname'] = "";
        $user['lastname'] = $user_response['name'];
      }
    }
    $user['email']   = isset($user_response['email'])   ? $user_response['email']   : "";
    $user['note']    = isset($user_response['note'])    ? $user_response['note']    : "";
    $user['address'] = isset($user_response['address']) ? $user_response['address'] : "";
    $user['expires'] = isset($user_response['expires']) ? $user_response['expires'] : "";
    $user['status']  = isset($user_response['status'])  ? $user_response['status']  : "";
    $user['type']    = isset($user_response['type'][0]) ? substr($user_response['type'][0],strripos($user_response['type'][0],"user-type")+10) : "";
    return $user;
  }

  private function getUserItems()
  {
    $loans_response = $this->getAsArray($this->paia.'/core/'.$_SESSION[$this->isil]["userlogin"].'/items');
    return $loans_response['doc'];
  }

  private function getUserFees()
  {
    $fees_response = $this->getAsArray($this->paia.'/core/'.$_SESSION[$this->isil]["userlogin"].'/fees');
    return $fees_response;
  }

  private function calcUserStatus()
  {
    $MsgText  = "";
    $Reminder = false;
    $Blocked  = false;

    // Check for collectable items
    if ( isset($_SESSION[$this->isil]["items"]) )
    {
      $Found = false;
      foreach ( $_SESSION[$this->isil]["items"] as $Item )
      {
        if ( isset($Item["status"]) && $Item["status"] == "4" )      $Found = true;
        if ( isset($Item["reminder"]) && $Item["reminder"] >= "3" )  $Reminder = true;
      }
    }

    if ( isset($_SESSION[$this->isil]["login"]["status"]) && $_SESSION[$this->isil]["login"]["status"] == "0" && $Reminder )  {$Blocked = true; $MsgText .= $this->CI->database->code2text("ACCOUNTREMINDER"); $_SESSION[$this->isil]["login"]["status"] = "5";}
    if ( isset($_SESSION[$this->isil]["login"]["status"]) && $_SESSION[$this->isil]["login"]["status"] == "1" && !$Reminder ) {$Blocked = true; $MsgText .= $this->CI->database->code2text("ACCOUNTLOCKED");}
    if ( isset($_SESSION[$this->isil]["login"]["status"]) && $_SESSION[$this->isil]["login"]["status"] == "1" && $Reminder )  {$Blocked = true; $MsgText .= $this->CI->database->code2text("ACCOUNTREMINDER");}
    if ( isset($_SESSION[$this->isil]["login"]["status"]) && $_SESSION[$this->isil]["login"]["status"] == "2" )               {$Blocked = true; $MsgText .= $this->CI->database->code2text("ACCOUNTEXPIRED");}
    if ( isset($_SESSION[$this->isil]["login"]["status"]) && $_SESSION[$this->isil]["login"]["status"] == "3" && !$Reminder ) {$Blocked = true; $MsgText .= $this->CI->database->code2text("ACCOUNTFEES");}
    if ( isset($_SESSION[$this->isil]["login"]["status"]) && $_SESSION[$this->isil]["login"]["status"] == "3" && $Reminder )  {$Blocked = true; $MsgText .= $this->CI->database->code2text("ACCOUNTREMINDERFEES");}
    if ( isset($_SESSION[$this->isil]["login"]["status"]) && $_SESSION[$this->isil]["login"]["status"] == "4" )               {$Blocked = true; $MsgText .= $this->CI->database->code2text("ACCOUNTEXPIREDFEES");}

    if ( $Found ) $MsgText .= ( $MsgText != "" ) ? "<br />" . $this->CI->database->code2text("COLLECTABLEREMARK") : $this->CI->database->code2text("COLLECTABLEREMARK");

    return array ( "blocked" => $Blocked,
                   "message" => ($MsgText != "") ? true : false,
                   "messagetext" => $MsgText );
  }

  // ********************************************
  // ************** Main-Functions **************
  // ********************************************

  public function login ( $user, $pw )
  {
    $post_data = array("username" => $user, "password" => $pw, "grant_type" => "password", "scope" => "read_patron read_fees read_items write_items change_password");
    $login_response = $this->postit($this->paia.'/auth/login', $post_data);
    $json_start = strpos($login_response, '{');
    $json_response = substr($login_response, $json_start);
    $array_response = json_decode($json_response, true);
    if (is_array($array_response) && array_key_exists('access_token', $array_response))
    {
      $_SESSION[$this->isil]['paiaToken'] = $array_response['access_token'];
      if (array_key_exists('patron', $array_response))
      {
        $_SESSION[$this->isil]["userlogin"]		= $user;
        $_SESSION[$this->isil]["userpassword"]	= $pw;
        $_SESSION[$this->isil]["login"]        = $this->getUserDetails();
        $_SESSION[$this->isil]["items"]        = $this->addIsil("items",$this->getUserItems());
        $_SESSION[$this->isil]["fees"]         = $this->addIsil("fees",$this->getUserFees());
        $_SESSION[$this->isil]["userstatus"]   = $this->calcUserStatus();
        return $_SESSION[$this->isil]["login"] + $_SESSION[$this->isil]["userstatus"];
      }
    }
    else
    {
      unset ($_SESSION[$this->isil]['paiaToken']);
      unset ($_SESSION[$this->isil]["login"]);
      unset ($_SESSION[$this->isil]["userlogin"]);
      unset ($_SESSION[$this->isil]["userpassword"]);
      unset ($_SESSION[$this->isil]["items"]);
      unset ($_SESSION[$this->isil]["fees"]);
      unset ($_SESSION[$this->isil]["userstatus"]);
    }
    return "-1";
  }

  public function userdata ()
  {
    if ( isset($_SESSION[$this->isil]['paiaToken']) && isset($_SESSION[$this->isil]["userlogin"]) )
    {
      $_SESSION[$this->isil]["login"] 			= $this->getUserDetails();
      $_SESSION[$this->isil]["items"] 			= $this->addIsil("items",$this->getUserItems());
      $_SESSION[$this->isil]["fees"] 				= $this->addIsil("fees",$this->getUserFees());
      $_SESSION[$this->isil]["userstatus"]  = $this->calcUserStatus();
      return $_SESSION[$this->isil]["login"];
    }
    return "-1";
  }

  private function addIsil($Typ,$Array)
  {
    if ( $Typ == "items" )
    {
      foreach ( $Array as $Key => &$One )
      {
        $One["isil"] = $this->isil;
      }
    }
    if ( $Typ == "fees" )
    {
      foreach ( $Array["fee"] as $Key => &$One )
      {
        $One["isil"] = $this->isil;
      }
    }
    return ( $Array );
  }

  public function logout ( )
  {
    $post_data = array("patron" => $_SESSION[$this->isil]["userlogin"]);
    $logout_response = $this->postit($this->paia.'/auth/logout', $post_data, $_SESSION[$this->isil]['paiaToken']);
    $json_start = strpos($logout_response, '{');
    $json_response = substr($logout_response, $json_start);
    $array_response = json_decode($json_response, true);

    unset ($_SESSION[$this->isil]['paiaToken']);
    unset ($_SESSION[$this->isil]["login"]);
    unset ($_SESSION[$this->isil]["userlogin"]);
    unset ($_SESSION[$this->isil]["userpassword"]);
    unset ($_SESSION[$this->isil]["items"]);
    unset ($_SESSION[$this->isil]["fees"]);
    unset ($_SESSION[$this->isil]["userstatus"]);

    if (array_key_exists('patron', $array_response))
    {
      return $array_response['patron'];
    }
    return "-1";
  }

  public function document($ppn)
  {
    $response = $this->getit($this->daia."?id=ppn:".$ppn."&format=json","");
    $json_start = strpos($response, '{');
    $json_response = substr($response, $json_start);
    $array_response = json_decode($json_response, true);
    return ( $array_response );
  }

  public function request($iln, $uri, $conditions=array())
  {
    if ( isset($_SESSION[$this->isil]["login"]["status"]) && $_SESSION[$this->isil]["login"]["status"] >= "1" )
    {
      return (array("status" => -3,
                    "error"  => ( isset($_SESSION[$this->isil]["userstatus"]["message"]) && $_SESSION[$this->isil]["userstatus"]["message"] == true && isset($_SESSION[$this->isil]["userstatus"]["messagetext"])) ? $_SESSION[$this->isil]["userstatus"]["messagetext"] : "Error" ));
    }
    if ( isset($conditions["desk"]) && $conditions["desk"] != "" )
    {
      $doc = array("doc" => array(array("item" => $uri, "confirm" => array("http://purl.org/ontology/paia#StorageCondition" => array($conditions["desk"])))));
    }
    elseif ( isset($conditions["feeusertypes"]) && isset($_SESSION[$this->isil]["login"]["type"]) && in_array($_SESSION[$this->isil]["login"]["type"],$conditions["feeusertypes"]) )
    {
      $doc = array("doc" => array(array("item" => $uri, "confirm" => array("http://purl.org/ontology/paia#FeeCondition" => array("http://purl.org/ontology/dso#Reservation")))));
    }
    else
    {
      $doc = array("doc" => array(array("item" => $uri)));
    }
    $response = $this->postAsArray($this->paia.'/core/' . $_SESSION[$this->isil]["userlogin"] .'/request', $doc);
    if ( isset($response["error"]) )
    {
      return (array("status" => -2,
                    "error"  => $response["error"]));
    }
    if ( isset($response["doc"][0]["error"]) )
    {
      return (array("status" => -2,
                    "error"  => $response["doc"][0]["error"]));
    }
    return (array("status"    => (isset($response["doc"][0]["status"]))    ? $response["doc"][0]["status"] : "0",
                  "label"     => (isset($response["doc"][0]["label"]))     ? $response["doc"][0]["label"] : "",
                  "storage"   => (isset($response["doc"][0]["storage"]))   ? $response["doc"][0]["storage"] : "",
                  "starttime" => (isset($response["doc"][0]["starttime"])) ? date("d.m.Y",strtotime($response["doc"][0]["starttime"])) : ""));
  }

  public function cancel($uri)
  {
    if ( isset($_SESSION[$this->isil]["login"]["status"]) && $_SESSION[$this->isil]["login"]["status"] >= "1" )
    {
      return (array("status" => -3,
                    "error"  => ( isset($_SESSION[$this->isil]["userstatus"]["message"]) && $_SESSION[$this->isil]["userstatus"]["message"] == true && isset($_SESSION[$this->isil]["userstatus"]["messagetext"])) ? $_SESSION[$this->isil]["userstatus"]["messagetext"] : "Error" ));
    }

    $doc = array("doc" => array(array("item" => $uri)));
    $response = $this->postAsArray($this->paia.'/core/' . $_SESSION[$this->isil]["userlogin"] .'/cancel', $doc);

    if ( isset($response["error"]) )
    {
      return (array("status" => -2,
                    "error"  => $response["error"]));
    }
    if ( isset($response["doc"][0]["error"]) )
    {
      return (array("status" => -2,
                    "error"  => $response["doc"][0]["error"]));
    }
    return (array("status" => 0));
  }

  public function renew($uri)
  {
    if ( isset($_SESSION[$this->isil]["login"]["status"]) && $_SESSION[$this->isil]["login"]["status"] >= "1" )
    {
      return (array("status" => -3,
                    "error"  => ( isset($_SESSION[$this->isil]["userstatus"]["message"]) && $_SESSION[$this->isil]["userstatus"]["message"] == true && isset($_SESSION[$this->isil]["userstatus"]["messagetext"])) ? $_SESSION[$this->isil]["userstatus"]["messagetext"] : "Error" ));
    }

    $doc = array("doc" => array(array("item" => $uri)));
    $response = $this->postAsArray($this->paia.'/core/' . $_SESSION[$this->isil]["userlogin"] .'/renew', $doc);

    if ( isset($response["error"]) )
    {
      return (array("status"  => -2,
                    "error"   => $response["error"]));
    }
    if ( isset($response["doc"][0]["error"]) )
    {
      return (array("status"  => -2,
                    "error"   => $response["doc"][0]["error"]));
    }
    return (array("status"    => 0,
                  "endtime"   => (isset($response["doc"][0]["endtime"]))   ? date("d.m.Y",strtotime($response["doc"][0]["endtime"])) : "",
                  "renewals"  => (isset($response["doc"][0]["renewals"]))  ? $response["doc"][0]["renewals"]                         : "1",
                  "cancancel" => (isset($response["doc"][0]["cancancel"])) ? $response["doc"][0]["cancancel"]                        : "",     // not useable yet
                  "queue"     => (isset($response["doc"][0]["queue"]))     ? $response["doc"][0]["queue"]                            : "0",    // not useable yet
                  "reminder"  => (isset($response["doc"][0]["reminder"]))  ? $response["doc"][0]["reminder"]                         : "1"
           ));
  }

  public function changepw($old, $new)
  {
    /*
    if ( isset($_SESSION[$this->isil]["login"]["status"]) && $_SESSION[$this->isil]["login"]["status"] >= "1" )
    {
      return (array("status" => -2,
                    "error"  => ( isset($_SESSION[$this->isil]["userstatus"]["message"]) && $_SESSION[$this->isil]["userstatus"]["message"] == true && isset($_SESSION[$this->isil]["userstatus"]["messagetext"])) ? $_SESSION[$this->isil]["userstatus"]["messagetext"] : "Error" ));
    }
    */
    $post_data = array("patron" => $_SESSION[$this->isil]["userlogin"], "username" => $_SESSION[$this->isil]["userlogin"], "old_password" => $old, "new_password" => $new);

    $change_response = json_decode($this->postit($this->paia.'/auth/change', $post_data, $_SESSION[$this->isil]['paiaToken']),true);

    if ( isset($change_response["error_description"]) )
    {
      return (array("status" => -2,
                    "error"  => $this->CI->database->code2text("PASSWORDCHANGEFAILED")));
    }

    $this->login($_SESSION[$this->isil]["userlogin"], $new);

    return (array("status" => 0));
  }
}
?>