<?php

$Version   = KERNEL . " " . KERNELVERSION . "." .  LIBRARYVERSION;
$Copyright = " &copy; " . date("Y");
$Impressum = ( isset($_SESSION["config_general"]["general"]["imprint"]) 
            && $_SESSION["config_general"]["general"]["imprint"] != "" ) 
            ? "<a href='" . $_SESSION["config_general"]["general"]["imprint"] 
            . "' target='_blank'><span class='imprint'></span></a> &middot; " : "";
$Mode      = ( strtolower(MODE) == "production" ) ? "" : " &middot; " . ucfirst(MODE) . " " . ucfirst(LIBRARY);

echo "<div class='row'>";
echo "<div id='version_search' class='lastline col-sm-offset-5 col-sm-7 col-md-offset-4 col-md-8 col-lg-offset-3 col-lg-9 text-center collapse'>" . $Impressum . $Version . $Copyright . $Mode . "</div>";
echo "<div id='version_start' class='lastline text-center'>" . $Impressum . $Version . $Copyright . $Mode . "</div>";
echo "</div>";

if ( $front )
{
  // Laden der Systemmodule
  foreach ( $_SESSION["config_system"]["frontjs"] as $value )
  {
    echo "<script type='text/javascript' src='" . base_url("/systemassets/" . $value) . "'></script>";
  }
}
else
{
  // Laden der Systemmodule
  foreach ( $_SESSION["config_system"]["systemjs"] as $value )
  {
    echo "<script type='text/javascript' src='" . base_url("/systemassets/" . $value) . "'></script>";
  }
  
  // Laden der allgemeinen Kundenmodule
  foreach ( $_SESSION["config_general"]["js"] as $value )
  {
    echo "<script type='text/javascript' src='" . base_url("/assets/" . $value) . "'></script>";
  }
  
  // Laden des Kundenmoduls
  foreach ( $_SESSION["config_". $modul]["js"] as $value )
  {
    echo "<script type='text/javascript' src='" . base_url("/assets/" . $value) . "'></script>";
  }
}

// Check if $User is still logged in
if ( isset($_SESSION["login"]) && $_SESSION["login"] != "" )
{
  echo "<script>";
  echo "usrconfig=" . json_encode($_SESSION["login"]) . ";";
  echo "</script>";
}

// Load language settings
echo "<script>";
echo "configlang=" . json_encode($_SESSION['language_'.$_SESSION["language"]]) . ";";
echo "</script>";
  
if ( ! $front )
{
  // Check if internal commands or intitial searches habe been issued
  if ( isset($initsearch) && $initsearch != "" )
  {
    echo "\n<script>";
    echo "\nsearchvals.inittext = '" . $initsearch . "';";
    echo "\n</script>";
  }
  if ( isset($initfacets) && $initfacets != "" )
  {
    echo "\n<script>";
    echo "\nsearchvals.initfacets = JSON.parse(decodeURIComponent(escape(window.atob('" . $initfacets . "'))));";
    echo "\n</script>";
  }
}

?>
</body></html>
