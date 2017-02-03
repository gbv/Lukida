<!DOCTYPE html>
<html lang="de">
  <head>
    <noscript><meta http-equiv="refresh" content="0; url=nojavascript" /></noscript>
    <title><?php echo $_SESSION["config_general"]['general']['title']; ?></title>
    <link href="/favicon.ico" rel="shortcut icon" type="image/x-icon">
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

    // Laden der Systemlibraries
    $cssmodules = ( $front && isset($_SESSION["config_system"]["frontcss"]) ) ? $_SESSION["config_system"]["frontcss"] : $_SESSION["config_system"]["systemcss"];
    foreach ( $cssmodules as $value )
    {
      {
        echo "<link rel='stylesheet' href='" . base_url("/systemassets/" . $value) . "'>";
      }
    }

    // Laden der Modullibraries
    $cssmodules = $_SESSION["config_system"][$modul."css"];
    foreach ( $cssmodules as $value )
    {
      {
        echo "<link rel='stylesheet' href='" . base_url("/systemassets/" . $value) . "'>";
      }
    }

    // Laden der allgemeinen Kundenlibraries
    // Front
    $cssmodules = ( $front && isset($_SESSION["config_general"]["frontcss"]) ) ? $_SESSION["config_general"]["frontcss"] : $_SESSION["config_general"]["css"];
    foreach ( $cssmodules as $value )
    {
      echo "<link rel='stylesheet' href='" . base_url("/assets/" . $value) . "'>";
    }

    // Modul
    foreach ( $_SESSION["config_". $modul]["css"] as $value )
    {
      echo "<link rel='stylesheet' href='" . base_url("/assets/" . $value) . "'>";
    }
    ?>
  </head>
  <body class='<?php echo ( $front ) ? "frontbody" : "backbody" ?>'>
    <div id='zotero'><span class='Z3988' title='ctx_ver=Z39.88-2004&sid=GBV&ctx_enc=info:ofi/enc:UTF-8&rft_val_fmt=info:ofi/fmt:kev:mtx:book'></span></div>
    <div class="container-fluid">