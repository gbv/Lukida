<?php

$FooterArray = array();

if ( !$front || !isset($_SESSION["config_general"]["general"]["frontpagewithoutlukida"]) || $_SESSION["config_general"]["general"]["frontpagewithoutlukida"] == "0" )
{
  $FooterArray[]  = KERNEL . " " . KERNELVERSION . "." .  KERNELSUBVERSION . "." . LIBRARYVERSION . " &copy; " . date("Y");
  $FooterArray[]  = ( strtolower(MODE) == "production" ) ? "" : "" . ucfirst(MODE) . " " . ucfirst(LIBRARY);
}

if ( !$front || !isset($_SESSION["config_general"]["general"]["frontpagewithoutlinks"]) || $_SESSION["config_general"]["general"]["frontpagewithoutlinks"] == "0" )
{
  if ( isset($_SESSION["config_general"]["general"]["imprint"])  && $_SESSION["config_general"]["general"]["imprint"] != "" )
  {
    $FooterArray[] = "<a href='" . $_SESSION["config_general"]["general"]["imprint"] . "' target='_blank'><span class='imprint'>" .  $this->database->code2text("IMPRINT") . "</span></a>";
  }
  if ( isset($_SESSION["config_general"]["general"]["dataprotection"]) && $_SESSION["config_general"]["general"]["dataprotection"] != "" ) 
  {
    $Tmp = explode(",",$_SESSION["config_general"]["general"]["dataprotection"]);
    if (count($Tmp) == 1)
    {
      $FooterArray[] = "<a href='" . $Tmp[0] . "' target='_blank'><span class='dataprotection'>" . $this->database->code2text("DATAPROTECTION") . "</span></a>";
    }
    if (count($Tmp) == 2)
    {
      $FooterArray[] = "<a class='showger' href='" . $Tmp[0] . "' target='_blank'><span class='dataprotection'>" . $this->database->code2text("DATAPROTECTION") . "</span></a>"
                     . "<a class='showeng hide' href='" . $Tmp[1] . "' target='_blank'><span class='dataprotection'>" . $this->database->code2text("DATAPROTECTION") . "</span></a>";
    }
  }
  if ( isset($_SESSION["config_general"]["general"]["about"]) && $_SESSION["config_general"]["general"]["about"] != "" ) 
  {
    $FooterArray[] = "<a href='" . $_SESSION["config_general"]["general"]["about"] . "' target='_blank'><span class='about'>" .  $this->database->code2text("ABOUT") . "</span></a>";
  }
}

echo "<div class='row'>";
echo "<div id='version_search' class='lastline col-sm-offset-5 col-sm-7 col-md-offset-4 col-md-8 col-lg-offset-3 col-lg-9 text-center collapse'>" . implode(" &middot; ", $FooterArray) . "</div>";
echo "<div id='version_start' class='lastline text-center'>" . implode(" &middot; ", $FooterArray) . "</div>";
echo "</div>";

if ( $front )
{
  // Laden der Systemmodule
  foreach ( $_SESSION["config_system"]["frontjs"] as $value )
  {
    echo "<script src='" . base_url("/systemassets/" . $value) . "'></script>";
  }

  // Laden der allgemeinen Kundenmodule
  if ( isset($_SESSION["config_general"]["frontjs"]) )
  {
    foreach ( $_SESSION["config_general"]["frontjs"] as $value )
    {
      echo "<script src='" . base_url("/assets/" . $value) . "'></script>";
    }
  }

}
else
{
  // Laden der Systemlibraries
  foreach ( $_SESSION["config_system"]["systemjs"] as $value )
  {
    echo "<script src='" . base_url("/systemassets/" . $value) . "'></script>";
  }
  
  // Laden der Systemmodullibraries
  foreach ( $_SESSION["config_system"][$modul."js"] as $value )
  {
    echo "<script src='" . base_url("/systemassets/" . $value) . "'></script>";
  }
  
  // Laden der allgemeinen Kundenlibraries
  foreach ( $_SESSION["config_general"]["js"] as $value )
  {
    echo "<script src='" . base_url("/assets/" . $value) . "'></script>";
  }
  
  // Laden des Kunden-Modullibraries
  foreach ( $_SESSION["config_". $modul]["js"] as $value )
  {
    echo "<script src='" . base_url("/assets/" . $value) . "'></script>";
  }
}

// Check if $User is still logged in
echo "<script>";
if ( isset($_SESSION["info"]["1"]["isil"]) && isset($_SESSION[$_SESSION["info"]["1"]["isil"]]["login"]) && $_SESSION[$_SESSION["info"]["1"]["isil"]]["login"] != "" ) echo "\nusrconfig=" . json_encode($_SESSION[$_SESSION["info"]["1"]["isil"]]["login"]) . ";";
if ( isset($_SESSION["language"]) && $_SESSION["language"] != "" ) 
{
  if ( isset($_SESSION['language_'.$_SESSION["language"]]) )  echo "\nconfiglang=" . json_encode($_SESSION['language_'.$_SESSION["language"]]) . ";";
}
  
if ( ! $front )
{
  // Check if internal commands or intitial searches habe been issued
  if ( isset($initsearch) && $initsearch != "" )
  {
    echo "\nsearchvals.inittext = '" . str_replace("'", "\'", $initsearch) . "';";
  }
  if ( isset($initfacets) && $initfacets != "" )
  {
    echo "\n$.init_client(\"" . $initfacets . "\");";
  }
}
echo "</script>";

// Sitelinks Searchbox
echo "<script type='application/ld+json'>";
echo '{';
echo '"@context": "http://www.schema.org",';
echo '"@type": "WebSite",';
echo '"url": "' . base_url() . '",';
echo '"potentialAction": ';
echo '{';
echo '"@type": "SearchAction",';
echo '"target": "' . base_url() . '{search_term_string}",';
echo '"query-input": "required name=search_term_string"';
echo '}';
echo '}';
echo "</script>";

?>
</div></body></html>
