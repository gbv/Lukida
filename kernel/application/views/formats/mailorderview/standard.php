<?php

//// Get & Formats required Fields
//$TabGen	= (iSset($_SESSION["config_discover"]["mailorderview"]["userelement"])		&& $_SESSION["config_discover"]["mailorderview"]["userelement"]    != "" )  ? $_SESSION["config_discover"]["mailorderview"]["userelement"] : "";
//
//// Start Output
//if ( $TabGen != "" )
//{
//  // Show general area element above all user tabs
//  $Output .= "<div class='col-xs-12'>";
//  $Output .= "<table class='table rowheight-reduced table-hover borderless small'><tbody>";
//  $Output .= $this->LoadElement($TabGen);
//  $Output .= "</tbody></table>";
//  $Output .= "</div>";
//}

$Output .= $this->LoadElement("mailorder");

                                                                                     
// ****** Ende Output ******
$Output .= "<p></p><div id='mailorder_messagebar'></div>";

// End Output

?>
