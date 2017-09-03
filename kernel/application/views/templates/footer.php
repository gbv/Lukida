<?php

if ( $front && isset($_SESSION["config_general"]["general"]["frontpagewithoutlukida"]) && $_SESSION["config_general"]["general"]["frontpagewithoutlukida"] == "1" )
{
  $Version   = "";
  $Copyright = "";
  $Mode      = "";
}
else
{
  $Version   = KERNEL . " " . KERNELVERSION . "." .  KERNELSUBVERSION . "." .  LIBRARYVERSION;
  $Copyright = " &copy; " . date("Y");
  $Mode      = ( strtolower(MODE) == "production" ) ? "" : " &middot; " . ucfirst(MODE) . " " . ucfirst(LIBRARY);
}

if ( $front && isset($_SESSION["config_general"]["general"]["frontpagewithoutlinks"]) && $_SESSION["config_general"]["general"]["frontpagewithoutlinks"] == "1" )
{
  // Frontpage without Links
  $Imprint = "";
  $About = "";
}
else
{
  $Imprint = ( isset($_SESSION["config_general"]["general"]["imprint"]) 
            && $_SESSION["config_general"]["general"]["imprint"] != "" ) 
            ? "<a href='" . $_SESSION["config_general"]["general"]["imprint"] 
            . "' target='_blank'><span class='imprint'>" . ( ( $_SESSION["language"] == "eng" ) ? "Imprint" : "Impressum" ) . "</span></a> &middot; " : "";
  $About = ( isset($_SESSION["config_general"]["general"]["about"]) 
            && $_SESSION["config_general"]["general"]["about"] != "" ) 
            ? " &middot; <a href='" . $_SESSION["config_general"]["general"]["about"] 
            . "' target='_blank'><span class='about'>" . ( ( $_SESSION["language"] == "eng" ) ? "About" : "Über" ) . "</span></a>" : "";
}            

echo "<div class='row'>";
echo "<div id='version_search' class='lastline col-sm-offset-5 col-sm-7 col-md-offset-4 col-md-8 col-lg-offset-3 col-lg-9 text-center collapse'>" . $Imprint . $Version . $Copyright . $Mode . $About ."</div>";
echo "<div id='version_start' class='lastline text-center'>" . $Imprint . $Version . $Copyright . $Mode . $About . "</div>";
echo "</div>";

if ( $front )
{
  // Laden der Systemmodule
  foreach ( $_SESSION["config_system"]["frontjs"] as $value )
  {
    echo "<script type='text/javascript' src='" . base_url("/systemassets/" . $value) . "'></script>";
  }

  // Laden der allgemeinen Kundenmodule
  if ( isset($_SESSION["config_general"]["frontjs"]) )
  {
    foreach ( $_SESSION["config_general"]["frontjs"] as $value )
    {
      echo "<script type='text/javascript' src='" . base_url("/assets/" . $value) . "'></script>";
    }
  }

}
else
{
  // Laden der Systemlibraries
  foreach ( $_SESSION["config_system"]["systemjs"] as $value )
  {
    echo "<script type='text/javascript' src='" . base_url("/systemassets/" . $value) . "'></script>";
  }
  
  // Laden der Systemmodullibraries
  foreach ( $_SESSION["config_system"][$modul."js"] as $value )
  {
    echo "<script type='text/javascript' src='" . base_url("/systemassets/" . $value) . "'></script>";
  }
  
  // Laden der allgemeinen Kundenlibraries
  foreach ( $_SESSION["config_general"]["js"] as $value )
  {
    echo "<script type='text/javascript' src='" . base_url("/assets/" . $value) . "'></script>";
  }
  
  // Laden des Kunden-Modullibraries
  foreach ( $_SESSION["config_". $modul]["js"] as $value )
  {
    echo "<script type='text/javascript' src='" . base_url("/assets/" . $value) . "'></script>";
  }
}

// Check if $User is still logged in
echo "<script>";
if ( isset($_SESSION["login"]) && $_SESSION["login"] != "" ) echo "\nusrconfig=" . json_encode($_SESSION["login"]) . ";";
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
