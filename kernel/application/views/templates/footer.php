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
    echo "\nsearchvals.inittext = '" . str_replace("'", "\'", $initsearch) . "';";
    echo "\n</script>";
  }
  if ( isset($initfacets) && $initfacets != "" )
  {
    echo "\n<script>";
    echo "\nvar urldata = JSON.parse(decodeURIComponent(escape(window.atob('" . $initfacets . "'))));";
    echo "\nstatevals=urldata.statevals;";
    echo "\nsearchvals.initfacets=urldata.facetvals;";
    echo "\n</script>";
  }
}

?>
</div></body></html>
