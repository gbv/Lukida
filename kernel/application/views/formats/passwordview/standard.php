<?php

$Rules = (isset($_SESSION["config_general"]["general"]["passwordrules"])   
             && $_SESSION["config_general"]["general"]["passwordrules"])  
              ? $_SESSION["config_general"]["general"]["passwordrules"] : "";

$Tmp  = explode(",",$Rules);
$Link = "";
if (count($Tmp) == 1)
{
	$Link = trim($Tmp[0]);
}
elseif (count($Tmp) == 2)
{
	$Link = ( $_SESSION["language"] == "ger" ) ? trim($Tmp[0]) : trim($Tmp[1]);
}

$Output .= "<div class='form-horizontal'>";
$Output .= "<div class='form-group'><label for='kennung' class='col-sm-5 control-label'>" . $this->CI->database->code2text("OLDPASSWORD") . "</label>";
$Output .= "<div class='col-sm-7'><input type='password' class='form-control keyenter' id='oldpassword' placeholder='" . $this->CI->database->code2text("OLDPASSWORD") . "'></div></div>";
$Output .= "<div class='form-group'><label for='passwort' class='col-sm-5 control-label'>" . $this->CI->database->code2text("NEWPASSWORD1") . "</label>";
$Output .= "<div class='col-sm-7'><input type='password' class='form-control keyenter' id='newpasswort1' placeholder=" . $this->CI->database->code2text("NEWPASSWORD1") . "'></div></div>";
$Output .= "<div class='form-group'><label for='passwort' class='col-sm-5 control-label'>" . $this->CI->database->code2text("NEWPASSWORD2") . "</label>";
$Output .= "<div class='col-sm-7'><input type='password' class='form-control keyenter' id='newpasswort2' placeholder='" . $this->CI->database->code2text("NEWPASSWORD2") . "'></div></div>";
if ( $Link )
{
	$Output .= "<div class='form-group'><div class='col-sm-offset-5 col-sm-7'><a href='" . $Link . "' target='_blank'>" . $this->CI->database->code2text("PASSWORDRULES") . " <i class=\"fa fa-external-link\"></i></a></div></div>";
}
$Output .= "<div id='password_messagebar'></div></div>";

?>