<?php

if ( ! isset($_SESSION["iln"]) )  return;

$PPNLink = $this->CI->internal_search("ppnlink:".$this->PPN);

// $this->CI->printArray2Screen($PPNLink);

if ( ! isset($PPNLink["results"]) ) return;

$PPNStg = json_encode(array_keys($PPNLink["results"]));
//$PPNStg = json_encode(array(12,3,2,3,4,2));
foreach ( $PPNLink["results"] as $One )
{
  $this->contents = $One["contents"];
  $Pretty = $this->SetContents("preview");  
  $Output .= "<div class=''>";
  $Output .= "<button class='col-xs-12 col-sm-6 btn btn-default publication' onclick='$.open_fullview(\"" . $One["id"] . "\"," . $PPNStg . ",\"publications\",\"" . $this->PPN . "\");'>";
  $Output .= "<table><tr><td>";
  $Output .= $this->SetCover2($One["cover"]);
  $Output .= "</td><td>";  
  $Output .= ( isset($Pretty["part"]) && $Pretty["part"] != "" ) ? $this->trim_text($Pretty["part"],50) : $this->trim_text($Pretty["title"],50);
  $Output .= "<br /><small>" . $this->trim_text($Pretty["pv_publishershort"],60) . "</small>";
  $Output .= "</td></tr></table>";
  $Output .= "</button>";
  $Output .= "</div>";
}

return;

?>