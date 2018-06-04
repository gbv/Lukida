<?php

$PAIA = $_SESSION["login"] + $_SESSION["items"] + $_SESSION["fees"];

// $this->CI->printArray2Screen($PAIA);

// Part Lukida Driver & Host
$Driver = (isset($_SESSION["config_general"]["lbs"]["type"]) && $_SESSION["config_general"]["lbs"]["type"] != "" ) ? $_SESSION["config_general"]["lbs"]["type"] : "";
if ( strtolower(MODE) == "production" )
{
	$Host = (isset($_SESSION["config_general"]["lbsprod"]["paia"]) && $_SESSION["config_general"]["lbsprod"]["paia"] != "" ) ? $_SESSION["config_general"]["lbsprod"]["paia"] : "";
}
else
{
	$Host = (isset($_SESSION["config_general"]["lbsdevtest"]["paia"]) && $_SESSION["config_general"]["lbsdevtest"]["paia"] != "" ) ? $_SESSION["config_general"]["lbsdevtest"]["paia"] : "";
}
$Output .= "<tr><td class='tabcell'>driver <i class='fa fa-arrow-right' aria-hidden='true'></i> host</td><td class='tabcell'><font color='red'>" . $Driver . " <i class='fa fa-arrow-right' aria-hidden='true'></i> " . $Host . "</font></td></tr>";

$Output .= $this->printtable(0,$PAIA);

?>