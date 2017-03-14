<?php

$PAIA = $_SESSION["login"] + $_SESSION["items"] + $_SESSION["fees"];

// $this->CI->printArray2Screen($PAIA);

// Part Lukida Driver & Host
$Driver = (isset($_SESSION["config_general"]["lbs"]["type"]) && $_SESSION["config_general"]["lbs"]["type"] != "" ) ? $_SESSION["config_general"]["lbs"]["type"] : "";
$Host = (isset($_SESSION["config_general"]["lbs"]["paia"]) && $_SESSION["config_general"]["lbs"]["paia"] != "" ) ? $_SESSION["config_general"]["lbs"]["paia"] : "";
$Output .= "<tr><td class='tabcell'>driver <i class='fa fa-arrow-right' aria-hidden='true'></i> host</td><td class='tabcell'><font color='red'>" . $Driver . " <i class='fa fa-arrow-right' aria-hidden='true'></i> " . $Host . "</font></td></tr>";

$Output .= $this->printtable(0,$PAIA);

?>