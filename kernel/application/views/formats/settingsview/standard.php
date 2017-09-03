<?php

// Start Output
$Output .= "<table class='table rowheight-reduced table-hover borderless small'><tbody>";
$Output .= "<tr>";
$Output .= "<td></td>";
$Output .= "<td align='left'>" . $this->CI->database->code2text("DESCRIPTION")   . "</td>";
$Output .= "<td align='center'>" . $this->CI->database->code2text("CREATED") . "</td>";
$Output .= "</tr>";

foreach ($settings as $One)
{
	$Output .= "<tr>";

  $Output .= "<td class='tablemiddle' align='center' id='checkbox_settingsload_" . $One["id"] . "'>";
  $Output .=  "<input type='checkbox' class='check_settingsload' data-id='" . $One["id"] . "' value=''>";
  $Output .= "</td>";

	$Output .= "<td class='tablemiddle' align='left'>" . $One["name"] . "</td>";

	$Output .= "<td class='tablemiddle' align='center'>" . $this->CI->datetime2german($One["created"]) . "</td>";

	$Output .= "</tr>";
}
$Output .= "</tbody></table>";

// Message Bar
$Output .= "<p>&nbsp;</p><div id='settingsview_messagebar'></div>";

?>