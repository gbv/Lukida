<?php

foreach ( $_SESSION["searches"] as $Item )
{
  $Output .= "<tr>";
  $Output .= "<td class='tablemiddle pull_left'>" . $this->CI->date2german($Item["datumzeit"])  . "</td>";
  $Output .= "<td class='tablemiddle pull_left'>" . $Item["suche"] . "</td>";
  $Output .= "<td align='right'><button onClick='$.facet_search(\"" . $Item["suche"] . "\",JSON.parse(decodeURIComponent(escape(window.atob(\"" . $Item["facetten"] . "\")))))' class='btn fullview-button-color'>" . $this->CI->database->code2text("START") . "</button></td>";
  $Output .= "</tr>";
}

?>