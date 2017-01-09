<?php

$PAIA = $_SESSION["login"] + $_SESSION["items"] + $_SESSION["fees"];

// $this->CI->printArray2Screen($PAIA);

$Output .= $this->printtable(0,$PAIA);

?>