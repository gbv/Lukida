<?php

$PAIA = $_SESSION[$_SESSION["info"]["1"]["isil"]]["login"] + $_SESSION[$_SESSION["info"]["1"]["isil"]]["items"] + $_SESSION[$_SESSION["info"]["1"]["isil"]]["fees"];

// $this->CI->printArray2Screen($PAIA);

// Part Lukida Driver & Host
$Output .= "<tr><td class='tabcell'>driver <i class='fa fa-arrow-right' aria-hidden='true'></i> host</td><td class='tabcell'><font color='red'>" . $_SESSION["info"]["1"]["driver"] . " <i class='fa fa-arrow-right' aria-hidden='true'></i> " . $_SESSION["info"]["1"]["host"] . "/" . $_SESSION["info"]["1"]["isil"] . "</font></td></tr>";

$Output .= $this->printtable(0,$PAIA);

if ( $this->countLBS() == 2 )
{
	$PAIA = $_SESSION[$_SESSION["info"]["2"]["isil"]]["login"] + $_SESSION[$_SESSION["info"]["2"]["isil"]]["items"] + $_SESSION[$_SESSION["info"]["2"]["isil"]]["fees"];
	$Output .= "<tr><td class='tabcell'>driver <i class='fa fa-arrow-right' aria-hidden='true'></i> host</td><td class='tabcell'><font color='red'>" . $_SESSION["info"]["2"]["driver"] . " <i class='fa fa-arrow-right' aria-hidden='true'></i> " . $_SESSION["info"]["2"]["host"] . "/" . $_SESSION["info"]["2"]["isil"] . "</font></td></tr>";
	$Output .= $this->printtable(0,$PAIA);
}

?>