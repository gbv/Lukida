<!DOCTYPE html>
<html lang="de">
  <head>
    <noscript><meta http-equiv="refresh" content="0; url=nojavascript" /></noscript>
    <title><?php echo $_SESSION["config_general"]['general']['title']; ?></title>
    <link href="favicon.ico" rel="shortcut icon" type="image/x-icon">
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php

    if ( isset($_SESSION["config_general"]["meta"]) )
    {
      foreach ($_SESSION["config_general"]["meta"] as $name => $content)
      {
        echo '<meta name="' . $name . '" content="' . $content . '">';
      }
    }

    $configtheme = ( isset($_SESSION["config_general"]["theme"]["theme"]) && $_SESSION["config_general"]["theme"]["theme"] != "" ) ? $_SESSION["config_general"]["theme"]["theme"] : "";
    
    $themeinfo = ($configtheme != "") ? array("customer", $configtheme) : array("system", "vzg");

    // Check Cookie in multiple theme mode
    if ( isset($_SESSION["config_discover"]["dev"]["thememode"]) && $_SESSION["config_discover"]["dev"]["thememode"] == "1" )
    {
      // Read Cookie
      $cookietheme = get_cookie("theme");
      $cookiearray = explode("|",$cookietheme);
      
      // Ensure correct customer theme
      if ( count($cookiearray) == 2 )
      {
        if ( $cookiearray[0] == "customer" && $cookiearray[1] != $configtheme )
        {
          $cookiearray[1] = $configtheme;
        }
        $themeinfo = $cookiearray;
      }
    }

    // Laden der Systemmodule
    $cssmodules = ( $front && isset($_SESSION["config_system"]["frontcss"]) ) ? $_SESSION["config_system"]["frontcss"] : $_SESSION["config_system"]["systemcss"];
    foreach ( $cssmodules as $value )
    {
      if ( stripos($value, "bootstrap-theme.min.cs") !== false && count($themeinfo) == 2 )
      {
     		if ( $themeinfo[0] == "customer" )
    		{
          $link = "/assets/css/" . $themeinfo[1] . ".css";
		    }
		    elseif ( $themeinfo[0] == "system" )
    		{
          $link = "/systemassets/lukida/css/" . $themeinfo[1] . ".css";
		    }
		    elseif ( $themeinfo[0] == "bootstrap" )
		    {
          $link = "/systemassets/" . str_replace("bootstrap-theme.min.cs", $themeinfo[1].".min.cs", $value);
        }
        echo "<link id='activetheme' rel='stylesheet' href='" . base_url($link) . "'>";
      }
      else
      {
        echo "<link rel='stylesheet' href='" . base_url("/systemassets/" . $value) . "'>";
      }
    }

    // Laden der allgemeinen Kundenmodule
    $cssmodules = ( $front && isset($_SESSION["config_general"]["frontcss"]) ) ? $_SESSION["config_general"]["frontcss"] : $_SESSION["config_general"]["css"];
    foreach ( $cssmodules as $value )
    {
      echo "<link rel='stylesheet' href='" . base_url("/assets/" . $value) . "'>";
    }

    // Laden des Kundenmoduls
    foreach ( $_SESSION["config_". $modul]["css"] as $value )
    {
      echo "<link rel='stylesheet' href='" . base_url("/assets/" . $value) . "'>";
    }
    ?>
  </head>
  <body class='<?php echo ( $front ) ? "frontbody" : "backbody" ?>'>
    <div id='zotero'><span class='Z3988' title='ctx_ver=Z39.88-2004&sid=GBV&ctx_enc=info:ofi/enc:UTF-8&rft_val_fmt=info:ofi/fmt:kev:mtx:book'></span></div>
    <div class="container-fluid">