<?php

class Paia_daia extends General
{
  protected $CI;
  private $isil;
  private $paia;
  private $daia;

  public function __construct()
  {
    // Assign the CodeIgniter super-object
    $this->CI =& get_instance();

    // Load URL Helper
    $this->CI->load->helper('url');

    $this->isil = (isset($_SESSION["config_general"]["general"]["isil"]) && $_SESSION["config_general"]["general"]["isil"] != "" ) ? $_SESSION["config_general"]["general"]["isil"] : "";
    if ( $this->isil == "" )  return false;

    $this->paia = (isset($_SESSION["config_general"]["lbs"]["paia"]) && $_SESSION["config_general"]["lbs"]["paia"] != "" ) ? $_SESSION["config_general"]["lbs"]["paia"] : "";
    if ( $this->paia == "" )  return false;
    $this->paia .= "/" . $this->isil;

    $this->daia = (isset($_SESSION["config_general"]["lbs"]["daia"]) && $_SESSION["config_general"]["lbs"]["daia"] != "" ) ? $_SESSION["config_general"]["lbs"]["daia"] : "";
    if ( $this->daia == "" )  return false;
    $this->daia .= "/isil/" . $this->isil . "/";
  }

  // ********************************************
  // ************** Tool-Functions **************
  // ********************************************

  private function getit($file, $access_token)
  {
    $http = curl_init();
    curl_setopt($http, CURLOPT_URL, $file);
    curl_setopt($http, CURLOPT_HTTPHEADER, array('Authorization: Bearer ' .$access_token, 'Content-type: application/json; charset=UTF-8'));
    curl_setopt($http, CURLOPT_RETURNTRANSFER, true);
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
    $postData = stripslashes(json_encode($data_to_send));

    $http = curl_init();
    curl_setopt($http, CURLOPT_URL, $file);
    curl_setopt($http, CURLOPT_POST, true);
    curl_setopt($http, CURLOPT_POSTFIELDS, $postData);

    if ( substr(PHP_OS,0,3) )
    {
      curl_setopt ($http, CURLOPT_SSL_VERIFYHOST, 0);
      curl_setopt ($http, CURLOPT_SSL_VERIFYPEER, 0);
    }

    if (isset($access_token)) {
      curl_setopt($http, CURLOPT_HTTPHEADER, array('Content-type: application/json; charset=UTF-8', 'Authorization: Bearer ' .$access_token));
    } else {
      curl_setopt($http, CURLOPT_HTTPHEADER, array('Content-type: application/json; charset=UTF-8'));
    }
    curl_setopt($http, CURLOPT_RETURNTRANSFER, true);
    if( ! $data = curl_exec($http))
    {
      trigger_error(curl_error($http));
    }
    curl_close($http);
    return $data;
  }

  private function getAsArray($file)
  {

    $pure_response = $this->getit($file, $_SESSION['paiaToken']);
    $json_start = strpos($pure_response, '{');
    $json_response = substr($pure_response, $json_start);
    // Convert booleans into 0 and 1 before json_decode to keep the values
    $json_response = str_replace(": true,", ": 1,", $json_response);
    $json_response = str_replace(": false,", ": 0,", $json_response);
    $loans_response = json_decode($json_response, true);

    // if the login auth token is invalid, renew it (this is possible unless the session is expired)
    if (isset($loans_response['error']) && $loans_response['code'] == '401') {
      $this->login($_SESSION["userlogin"], $_SESSION["userpassword"]);
      $pure_response = $this->getit($file, $_SESSION['paiaToken']);
      $json_start = strpos($pure_response, '{');
      $json_response = substr($pure_response, $json_start);
      $loans_response = json_decode($json_response, true);
    }

    return $loans_response;
  }

  private function postAsArray($file, $data)
  {
    $pure_response = $this->postit($file, $data, $_SESSION['paiaToken']);
    $json_start = strpos($pure_response, '{');
    $json_response = substr($pure_response, $json_start);
    $loans_response = json_decode($json_response, true);

    // if the login auth token is invalid, renew it (this is possible unless the session is expired)
    if ( isset($loans_response['error']) && ($loans_response['code']) )
    {
      if ($loans_response['error'] && $loans_response['code'] == '401') {
        $this->login($_SESSION["userlogin"], $_SESSION["userpassword"]);
        $pure_response = $this->postit($file, $data, $_SESSION['paiaToken']);
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
    $pure_response = $this->getit($this->paia.'/core/' . $_SESSION["userlogin"], $_SESSION['paiaToken']);
    $json_start = strpos($pure_response, '{');
    $json_response = substr($pure_response, $json_start);
    $user_response = json_decode($json_response, true);

    $user = array();
    $user['username'] = $_SESSION["userlogin"];
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
    $user['email'] = isset($user_response['email']) ? $user_response['email'] : "";
    $user['address'] = isset($user_response['address']) ? $user_response['address'] : "";
    $user['expires'] = isset($user_response['expires']) ? $user_response['expires'] : "";
    $user['status'] = isset($user_response['status']) ? $user_response['status'] : "";
    return $user;
  }

  private function getUserItems()
  {
    $loans_response = $this->getAsArray($this->paia.'/core/'.$_SESSION["userlogin"].'/items');
    return $loans_response['doc'];
  }

  private function getUserFees()
  {
    $fees_response = $this->getAsArray($this->paia.'/core/'.$_SESSION["userlogin"].'/fees');
    return $fees_response;
  }

  private function calcUserStatus()
  {
    $MsgText  = "";
    $Reminder = false;
    $Blocked  = false;

    // Check for collectable items
    if ( isset($_SESSION["items"]) )
    {
      $Found = false;
      foreach ( $_SESSION["items"] as $Item )
      {
        if ( isset($Item["status"]) && $Item["status"] == "4" )      $Found = true;
        if ( isset($Item["reminder"]) && $Item["reminder"] >= "3" )  $Reminder = true;
      }
    }

    if ( isset($_SESSION["login"]["status"]) && $_SESSION["login"]["status"] == "0" && $Reminder )  {$Blocked = true; $MsgText .= $this->CI->database->code2text("ACCOUNTREMINDER"); $_SESSION["login"]["status"] = "5";}
    if ( isset($_SESSION["login"]["status"]) && $_SESSION["login"]["status"] == "1" && !$Reminder ) {$Blocked = true; $MsgText .= $this->CI->database->code2text("ACCOUNTLOCKED");}
    if ( isset($_SESSION["login"]["status"]) && $_SESSION["login"]["status"] == "1" && $Reminder )  {$Blocked = true; $MsgText .= $this->CI->database->code2text("ACCOUNTREMINDER");}
    if ( isset($_SESSION["login"]["status"]) && $_SESSION["login"]["status"] == "2" )               {$Blocked = true; $MsgText .= $this->CI->database->code2text("ACCOUNTEXPIRED");}
    if ( isset($_SESSION["login"]["status"]) && $_SESSION["login"]["status"] == "3" && !$Reminder ) {$Blocked = true; $MsgText .= $this->CI->database->code2text("ACCOUNTFEES");}
    if ( isset($_SESSION["login"]["status"]) && $_SESSION["login"]["status"] == "3" && $Reminder )  {$Blocked = true; $MsgText .= $this->CI->database->code2text("ACCOUNTREMINDERFEES");}
    if ( isset($_SESSION["login"]["status"]) && $_SESSION["login"]["status"] == "4" )               {$Blocked = true; $MsgText .= $this->CI->database->code2text("ACCOUNTEXPIREDFEES");}

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
      $_SESSION['paiaToken'] = $array_response['access_token'];
      if (array_key_exists('patron', $array_response))
      {
        $_SESSION["userlogin"]		= $user;
        $_SESSION["userpassword"]	= $pw;
        $_SESSION["login"]        = $this->getUserDetails();
        $_SESSION["items"]        = $this->getUserItems();
        $_SESSION["fees"]         = $this->getUserFees();
        $_SESSION["userstatus"]   = $this->calcUserStatus();
        return $_SESSION["login"] + $_SESSION["userstatus"];
      }
    }
    else
    {
      unset ($_SESSION['paiaToken']);
      unset ($_SESSION["login"]);
      unset ($_SESSION["userlogin"]);
      unset ($_SESSION["userpassword"]);
      unset ($_SESSION["items"]);
      unset ($_SESSION["fees"]);
      unset ($_SESSION["userstatus"]);
    }
    return "-1";
  }

  public function userdata ()
  {
    if ( isset($_SESSION['paiaToken']) && isset($_SESSION["userlogin"]) )
    {
      $_SESSION["login"] 				= $this->getUserDetails();
      $_SESSION["items"] 				= $this->getUserItems();
      $_SESSION["fees"] 				= $this->getUserFees();
      $_SESSION["userstatus"]   = $this->calcUserStatus();
      return $_SESSION["login"];
    }
    return "-1";
  }

  public function logout ( )
  {
    $post_data = array("patron" => $_SESSION["userlogin"]);
    $logout_response = $this->postit($this->paia.'/auth/logout', $post_data, $_SESSION['paiaToken']);
    $json_start = strpos($logout_response, '{');
    $json_response = substr($logout_response, $json_start);
    $array_response = json_decode($json_response, true);

    if (array_key_exists('patron', $array_response))
    {
      unset ($_SESSION['paiaToken']);
      unset ($_SESSION["login"]);
      unset ($_SESSION["userlogin"]);
      unset ($_SESSION["userpassword"]);
      unset ($_SESSION["items"]);
      unset ($_SESSION["fees"]);
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

  public function request($uri)
  {
    if ( isset($_SESSION["login"]["status"]) && $_SESSION["login"]["status"] >= "1" )
    {
      return (array("status" => -3,
                    "error"  => ( isset($_SESSION["userstatus"]["message"]) && $_SESSION["userstatus"]["message"] == true && isset($_SESSION["userstatus"]["messagetext"])) ? $_SESSION["userstatus"]["messagetext"] : "Error" ));
    }

    $doc = array("doc" => array(array("item" => $uri)));
    $response = $this->postAsArray($this->paia.'/core/' . $_SESSION["userlogin"] .'/request', $doc);
    //$this->CI->printArray2File($response);
    if ( isset($response["doc"][0]["error"]) )
    {
      return (array("status" => -2,
                    "error"  => $response["doc"][0]["error"]));
    }
    return (array("status"    => $response["doc"][0]["status"],
                  "label"     => $response["doc"][0]["label"],
                  "starttime" => date("d.m.Y",strtotime($response["doc"][0]["starttime"]))));
  }

  public function cancel($uri)
  {
    if ( isset($_SESSION["login"]["status"]) && $_SESSION["login"]["status"] >= "1" )
    {
      return (array("status" => -3,
                    "error"  => ( isset($_SESSION["userstatus"]["message"]) && $_SESSION["userstatus"]["message"] == true && isset($_SESSION["userstatus"]["messagetext"])) ? $_SESSION["userstatus"]["messagetext"] : "Error" ));
    }

    $doc = array("doc" => array(array("item" => $uri)));
    $response = $this->postAsArray($this->paia.'/core/' . $_SESSION["userlogin"] .'/cancel', $doc);
    //$this->CI->printArray2File($response);
    if ( isset($response["doc"][0]["error"]) )
    {
      return (array("status" => -2,
                    "error"  => $response["doc"][0]["error"]));
    }
    return (array("status" => 0));
  }

  public function renew($uri)
  {
    if ( isset($_SESSION["login"]["status"]) && $_SESSION["login"]["status"] >= "1" )
    {
      return (array("status" => -3,
                    "error"  => ( isset($_SESSION["userstatus"]["message"]) && $_SESSION["userstatus"]["message"] == true && isset($_SESSION["userstatus"]["messagetext"])) ? $_SESSION["userstatus"]["messagetext"] : "Error" ));
    }

    $doc = array("doc" => array(array("item" => $uri)));
    $response = $this->postAsArray($this->paia.'/core/' . $_SESSION["userlogin"] .'/renew', $doc);
    if ( isset($response["doc"][0]["error"]) )
    {
      return (array("status" => -2,
        "error"  => $response["doc"][0]["error"]));
    }
    return (array("status" => 0,
      "endtime"  => date("d.m.Y",strtotime($response["doc"][0]["endtime"])),
      "renewals" => $response["doc"][0]["renewals"]));
  }
}
?>