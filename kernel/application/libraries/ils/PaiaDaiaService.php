<?php

/**
 * Provides access to an integrated library system via paia and daia.
 * 
 * @author  Alexander Karim <Alexander.Karim@gbv.de>
 * @author  Richard Großer <richard.grosser@thulb.uni-jena.de>
 * @see     https://verbundwiki.gbv.de/display/VZG/DAIA for more information about daia
 * @see     https://verbundwiki.gbv.de/display/VZG/PAIA for more information about paia
 * 
 */
class PaiaDaiaService implements ILSService
{
    /**
     * The Code Igniter super object
     * 
     * @var CI_Controller 
     */
    protected $CI;
    
    /**
     * The current User object
     *
     * @var User 
     */
    protected $ilsUser;


    /**
     * The isil number of the library (International Standard Identifier for Libraries and Related Organizations, ISO 15511)
     *
     * @see http://www.iso.org/iso/home/store/catalogue_ics/catalogue_detail_ics.htm?csnumber=57332 for more about ISO 15511
     * @var string 
     */
    private $isil;
    
    /**
     * URL with port and path to the paia service (usually path equals isil)
     * 
     * @var string 
     */
    private $paia;
    
    /**
     * URL of the daia service
     * 
     * @var string 
     */
    private $daia;

    public function __construct() {
        // Assign the CodeIgniter super-object
        $this->CI = & get_instance();

        // Load URL Helper
        $this->CI->load->helper('url');
    }

    /**
     * Set the isil number.
     * 
     * @param string $isil
     * @return PaiaDaiaService  (fluent interface)
     */
    public function setIsil($isil) {
        $this->isil = $isil;
        return $this;
    }

    /**
     * Set the url of the paia service.
     * 
     * @param string $paia
     * @return PaiaDaiaService  (fluent interface)
     */
    public function setPaia($paia) {
        $this->paia = $paia;
        return $this;
    }

    /**
     * Set the url of the daia service.
     * 
     * @param string $daia
     * @return PaiaDaiaService  (fluent interface)
     */
    public function setDaia($daia) {
        $this->daia = $daia;
        return $this;
    }
    
    /**
     * Set the User object.
     * 
     * @param User $user
     * @return PaiaDaiaService  (fluent interface)
     */
    public function setUser($user) {
        $this->ilsUser = $user;
        return $this;
    }

    // ********************************************
    // ************** Tool-Functions **************
    // ********************************************

    private function getit($file, $access_token) {
        $http = curl_init();
        curl_setopt($http, CURLOPT_URL, $file);
        curl_setopt($http, CURLOPT_HTTPHEADER, array('Authorization: Bearer ' . $access_token, 'Content-type: application/json; charset=UTF-8'));
        curl_setopt($http, CURLOPT_RETURNTRANSFER, true);
        if (substr(PHP_OS, 0, 3)) {
            curl_setopt($http, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($http, CURLOPT_SSL_VERIFYPEER, 0);
        }
        $data = curl_exec($http);
        curl_close($http);
        return $data;
    }

    private function postit($file, $data_to_send, $access_token = null) {
        // json-encoding
        $postData = stripslashes(json_encode($data_to_send));

        $http = curl_init();
        curl_setopt($http, CURLOPT_URL, $file);
        curl_setopt($http, CURLOPT_POST, true);
        curl_setopt($http, CURLOPT_POSTFIELDS, $postData);

        if (substr(PHP_OS, 0, 3)) {
            curl_setopt($http, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($http, CURLOPT_SSL_VERIFYPEER, 0);
        }

        if (isset($access_token)) {
            curl_setopt($http, CURLOPT_HTTPHEADER, array('Content-type: application/json; charset=UTF-8', 'Authorization: Bearer ' . $access_token));
        } else {
            curl_setopt($http, CURLOPT_HTTPHEADER, array('Content-type: application/json; charset=UTF-8'));
        }
        curl_setopt($http, CURLOPT_RETURNTRANSFER, true);
        if (!$data = curl_exec($http)) {
            trigger_error(curl_error($http));
        }
        curl_close($http);
        return $data;
    }

    private function getAsArray($file) {

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

    private function postAsArray($file, $data) {
        $pure_response = $this->postit($file, $data, $_SESSION['paiaToken']);
        $json_start = strpos($pure_response, '{');
        $json_response = substr($pure_response, $json_start);
        $loans_response = json_decode($json_response, true);

        // if the login auth token is invalid, renew it (this is possible unless the session is expired)
        if (isset($loans_response['error']) && ($loans_response['code'])) {
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

    private function getPpnByBarcode($barcode) {
        $barcode = str_replace("/", " ", $barcode);
        if (preg_match("/bar:(.*)$/", $barcode, $match)) {
            $barcode = $match[1];
        } else {
            return false;
        }
        $searchUrl = "XML=1.0/CMD?ACT=SRCHA&IKT=1016&SRT=YOP&TRM=bar+$barcode";

        $doc = new DomDocument();
        $doc->load($searchUrl);
        // get Availability information from DAIA
        $itemlist = $doc->getElementsByTagName('SHORTTITLE');
        if (isset($itemlist->item(0)->attributes) && count($itemlist->item(0)->attributes) > 0) {
            $ppn = $itemlist->item(0)->attributes->getNamedItem('PPN')->nodeValue;
        } else {
            return false;
        }
        return $ppn;
    }

    private function getUserDetails() {
        $pure_response = $this->getit($this->paia . '/core/' . $_SESSION["userlogin"], $_SESSION['paiaToken']);
        $json_start = strpos($pure_response, '{');
        $json_response = substr($pure_response, $json_start);
        $user_response = json_decode($json_response, true);
        $nameArr = ( isset($user_response['name']) ) ? explode(',', $user_response['name']) : array();

        //$this->CI->printArray2File($user_response);    

        $this->ilsUser->setUsername($_SESSION["userlogin"])
                      ->setFirstname((count($nameArr) === 2) ? $nameArr[1] : '')
                      ->setLastname((count($nameArr) === 2) ? $nameArr[0] : $user_response['name'])
                      ->setEmail(isset($user_response['email']) ? $user_response['email'] : '')
                      ->setAddress(isset($user_response['address']) ? $user_response['address'] : '')
                      ->setExpires(isset($user_response['expires']) ? $user_response['expires'] : '')
                      ->setStatus(isset($user_response['status']) ? $user_response['status'] : '');

        return $this->ilsUser->toArray();
    }

    private function getUserItems() {
        $loans_response = $this->getAsArray($this->paia . '/core/' . $_SESSION["userlogin"] . '/items');
        return $loans_response['doc'];
    }

    private function getUserFees() {
        $fees_response = $this->getAsArray($this->paia . '/core/' . $_SESSION["userlogin"] . '/fees');
        //$this->CI->printArray2File($fees_response);
        return $fees_response;
    }

    // ********************************************
    // ************** Main-Functions **************
    // ********************************************

    /**
     * Login to the integrated library system with paia.
     * 
     * @param string $user
     * @param string $pw
     * @return string
     * 
     * @todo define consistent return value
     * @todo use exceptions instead of error codes
     * @todo move user specific data out of the $_SESSION superglobal into the User object ($this->ilsUser)
     */
    public function login($user, $pw) {
        $post_data = array("username" => $user, "password" => $pw, "grant_type" => "password", "scope" => "read_patron read_fees read_items write_items change_password");
        $login_response = $this->postit($this->paia . '/auth/login', $post_data);
        //$this->CI->printArray2File($login_response);
        //$this->CI->printArray2File($this->paia.'/auth/login');
        $json_start = strpos($login_response, '{');
        $json_response = substr($login_response, $json_start);
        $array_response = json_decode($json_response, true);
        if (is_array($array_response) && array_key_exists('access_token', $array_response)) {
            $_SESSION['paiaToken'] = $array_response['access_token'];
            if (array_key_exists('patron', $array_response)) {
                $_SESSION["userlogin"] = $user;
                $_SESSION["userpassword"] = $pw;
                $_SESSION["login"] = $this->getUserDetails();
                $_SESSION["items"] = $this->getUserItems();
                $_SESSION["fees"] = $this->getUserFees();
                //$this->CI->printArray2File($_SESSION["login"]);
                // Check for collectable items
                $Found = false;
                foreach ($_SESSION["items"] as $Item) {
                    if ($Item["status"] == "4") {
                        $Found = true;
                        break;
                    }
                }
                $Container = ( $Found ) ? $_SESSION["login"] + array("collectable" => true) : $_SESSION["login"] + array("collectable" => false);
                return $Container;
            }
        } else {
            unset($_SESSION['paiaToken']);
            unset($_SESSION["login"]);
            unset($_SESSION["userlogin"]);
            unset($_SESSION["userpassword"]);
            unset($_SESSION["items"]);
            unset($_SESSION["fees"]);
        }
        return "-1";
    }

    /**
     * Get the userdata of the currently logged in user.
     * 
     * @return string
     * 
     * @todo only use return values (i.e. transform to a real getter function, don't write into superglobals)
     * @todo get away from the $_SESSION superglobal and use the user object instead ($this->ilsUser)
     * @todo don't use not documented and ambiquous error codes
     */
    public function userdata() {
        if (isset($_SESSION['paiaToken']) && isset($_SESSION["userlogin"])) {
            $_SESSION["login"] = $this->getUserDetails();
            $_SESSION["items"] = $this->getUserItems();
            $_SESSION["fees"] = $this->getUserFees();
            //$this->CI->printArray2File($_SESSION["items"]);
            return $_SESSION["login"];
        }
        return "-1";
    }

    /**
     * Logout the user.
     * 
     * @return string
     * 
     * @todo get away from the $_SESSION superglobal and use an user object instead
     * @todo don't use not documented and ambiquous error codes
     */
    public function logout() {
        //$this->CI->printArray2File($_SESSION);
        $post_data = array("patron" => $_SESSION["userlogin"]);
        $logout_response = $this->postit($this->paia . '/auth/logout', $post_data, $_SESSION['paiaToken']);
        $json_start = strpos($logout_response, '{');
        $json_response = substr($logout_response, $json_start);
        $array_response = json_decode($json_response, true);
        //$this->CI->printArray2File($array_response);

        if (array_key_exists('patron', $array_response)) {
            unset($_SESSION['paiaToken']);
            unset($_SESSION["login"]);
            unset($_SESSION["userlogin"]);
            unset($_SESSION["userpassword"]);
            unset($_SESSION["items"]);
            unset($_SESSION["fees"]);
            return $array_response['patron'];
        }
        return "-1";
    }

    /**
     * Get availability information for a document.
     * 
     * @param   string  $ppn    "pica production number"
     * @return  array
     */
    public function document($ppn) {
        $response = $this->getit($this->daia . "?id=ppn:" . $ppn . "&format=json", "");
        //$this->CI->printArray2File($this->daia."?id=ppn:".$ppn."&format=json");
        //$this->CI->printArray2File($response);
        $json_start = strpos($response, '{');
        $json_response = substr($response, $json_start);
        $array_response = json_decode($json_response, true);
        return ( $array_response );
    }

    /**
     * Request an item for reservation or delivery. 
     * 
     * @param string $uri
     * @return array
     */
    public function request($uri) {
        $doc = array("doc" => array(array("item" => $uri)));
    //$this->CI->printArray2File($doc);        
        $response = $this->postAsArray($this->paia . '/core/' . $_SESSION["userlogin"] . '/request', $doc);
    //$this->CI->printArray2File($this->paia . '/core/' . $_SESSION["userlogin"] . '/request');
    //$this->CI->printArray2File($response);
        if (isset($response["doc"][0]["error"])) {
            return (array("status" => -2,
                "error" => $response["doc"][0]["error"]));
        }
        return (array("status" => $response["doc"][0]["status"],
            "label" => $response["doc"][0]["label"],
            "starttime" => date("d.m.Y", strtotime($response["doc"][0]["starttime"]))));
    }

    /**
     * Cancel the request for an item. 
     * 
     * @param type $uri
     * @return type
     */
    public function cancel($uri) {
        $doc = array("doc" => array(array("item" => $uri)));
        $response = $this->postAsArray($this->paia . '/core/' . $_SESSION["userlogin"] . '/cancel', $doc);
        //$this->CI->printArray2File($response);
        if (isset($response["doc"][0]["error"])) {
            return (array("status" => -2,
                "error" => $response["doc"][0]["error"]));
        }
        return (array("status" => 0));
    }

    /**
     * Prolong loan for an item.
     * 
     * @param type $uri
     * @return type
     */
    public function renew($uri) {
        $doc = array("doc" => array(array("item" => $uri)));
        $response = $this->postAsArray($this->paia . '/core/' . $_SESSION["userlogin"] . '/renew', $doc);
        if (isset($response["doc"][0]["error"])) {
            return (array("status" => -2,
                "error" => $response["doc"][0]["error"]));
        }
        $this->userdata();
        return (array("status" => 0));
    }
}
