<?php

// Prefilled Values for DevMode
$Value       = "";
$DevMode  = false;
if (isset($_SESSION["config_discover"]["discover"]["devmode"]) 
       && $_SESSION["config_discover"]["discover"]["devmode"] == "1" )
{
  $DevMode  = true;
  if (isset($_SESSION["config_discover"]["discover"]["devvalues"]) 
         && $_SESSION["config_discover"]["discover"]["devvalues"] != "" )
  {
    $DevValues = explode(",",$_SESSION["config_discover"]["discover"]["devvalues"]);
    $Value = trim($DevValues[array_rand($DevValues)]);
  }
}

$ThemeMode     = (isset($_SESSION["config_discover"]["dev"]["thememode"]) 
                     && $_SESSION["config_discover"]["dev"]["thememode"] == "1" ) 
               ? true : false;
$ThemeCustomer = (isset($_SESSION["config_general"]["theme"]["theme"]) 
                     && $_SESSION["config_general"]["theme"]["theme"] != "" ) 
               ? $_SESSION["config_general"]["theme"]["theme"] : "";
$FacetDataPool = (isset($_SESSION["config_" . $modul]["navbar"]["facet_datapool"]) 
                     && $_SESSION["config_" . $modul]["navbar"]["facet_datapool"] == "1" ) 
               ? true : false;
$Assistant     = (isset($_SESSION["config_" . $modul]["navbar"]["assistant"]) 
                     && $_SESSION["config_" . $modul]["navbar"]["assistant"] == "1" ) 
               ? true : false;

// Logo Settings
$logotitle = $_SESSION["config_general"]["general"]["title"];
$logocheck = true;

// User already logged in?
$Login  = ( isset($_SESSION["login"]) && $_SESSION["login"] != "" ) ? true : false;

$lbs = ( isset($_SESSION["config_general"]["lbs"]["available"]) && $_SESSION["config_general"]["lbs"]["available"] != "" ) ? true : false;

?>

<nav class="navbar navbar-default navbar-fixed-top hidden-xs hidden-sm">
  <div class="container-fluid">
    <div class="navbar-header">
      <a class="navbar-brand" href="<?php echo base_url(); ?>" data-tooltip="tooltip" data-placement="buttom" title="<?php echo $logotitle ?>" data-container="body"></a>
    </div>

    <div class="navbar-collapse collapse searchbar">

      <div class="navbar-form navbar-right">

        <!-- Switches -->
        <div class="btn-group">

          <!-- Sprache -->
          <div class="btn-group dropdown">
            <button type='button' class='selectlanguage btn dropdown-toggle navbar-button-color' data-toggle="dropdown" data-tooltip="tooltip" data-placement="left" title="Sprache ausw&auml;hlen" data-container="body">
              <img alt="<?php echo ( $_SESSION['language']=='ger' ) ? 'Deutsch' : 'German'; ?>" class="lang_ger<?php if ( $_SESSION["language"]!="ger" ) echo " hide"; ?>" height='15' src='/systemassets/lukida/img/ger.png' />
              <img alt="<?php echo ( $_SESSION['language']=='ger' ) ? 'Englisch' : 'English'; ?>" class="lang_eng<?php if ( $_SESSION["language"]=="ger" ) echo " hide"; ?>" height='15' src='/systemassets/lukida/img/eng.png' />
              <span class="caret"></span>
              <span class="sr-only">Toggle Dropdown</span>
            </button>
            <ul class="dropdown-menu lang" role="menu">
              <li>
                <a href="javascript:void(0)" data-width='15px' data-src="/systemassets/lukida/img/ger.png" data-text="" data-value="ger">
                  <img alt="<?php echo ( $_SESSION['language']=='ger' ) ? 'Deutsch' : 'German'; ?>" width='20' src='/systemassets/lukida/img/ger.png' /> <span class="ger"></span>
                </a>
              </li>
              <li>
                <a href="javascript:void(0)" data-width='15px' data-src="/systemassets/lukida/img/eng.png" data-text="" data-value="eng">
                  <img alt="<?php echo ( $_SESSION['language']=='ger' ) ? 'Englisch' : 'English'; ?>" width='20' src='/systemassets/lukida/img/eng.png' /> <span class="eng"></span>
                </a>
              </li>
            </ul>
          </div>

          <!-- Ansicht -->
          <div class="btn-group hidden-xs">
            <button type='button' class='selectview btn dropdown-toggle navbar-button-color' data-toggle="dropdown" data-tooltip="tooltip" data-placement="left" title="Ansicht ausw&auml;hlen" data-container="body">
              <i class="fa fa-th-large"></i>
              <span class="caret"></span>
              <span class="sr-only">Toggle Dropdown</span>
            </button>
            <ul class="dropdown-menu layout" role="menu">
              <li class="">                   <a class="col1" href="javascript:void(0)" data-value="12"> </a></li>
              <li class="">                   <a class="col2" href="javascript:void(0)" data-value= "6"> </a></li>
              <li class="hidden-sm">          <a class="col3" href="javascript:void(0)" data-value= "4"> </a></li>
              <li class="hidden-sm hidden-md"><a class="col4" href="javascript:void(0)" data-value= "3"> </a></li>
            </ul>
          </div>

          <?php if ( $ThemeMode ) { ?>
            <!-- Theme -->
            <div class="btn-group">
              <button type='button' class='selecttheme btn dropdown-toggle navbar-button-color' data-toggle="dropdown" data-tooltip="tooltip" data-placement="left" title="Optik ausw&auml;hlen" data-container="body">
                <i class="fa fa-eye"></i>
                <span class="caret"></span>
                <span class="sr-only">Toggle Dropdown</span>
              </button>
              <ul class="dropdown-menu theme" role="menu">
                <?php
                if ( $ThemeCustomer != "" )
                {
                  echo "<li><a href='#' data-type='customer' data-rel='" . strtolower($ThemeCustomer) . "'>" . ucfirst($ThemeCustomer) . "</a></li>";
                  echo "<li class='divider'></li>";
                }
                ?>
                <li><a href="javascript:void(0)" data-type="system" data-rel="vzg">VZG</a></li>
                <li class="divider"></li>
                <li><a href="javascript:void(0)" data-type="bootstrap" data-rel="bootstrap-theme">Bootstrap</a></li>
                <li><a href="javascript:void(0)" data-type="bootstrap" data-rel="cerulean">Cerulean</a></li>
                <li><a href="javascript:void(0)" data-type="bootstrap" data-rel="cosmo">Cosmo</a></li>
                <li><a href="javascript:void(0)" data-type="bootstrap" data-rel="cyborg">Cyborg</a></li>
                <li><a href="javascript:void(0)" data-type="bootstrap" data-rel="darkly">Darkly</a></li>
                <li><a href="javascript:void(0)" data-type="bootstrap" data-rel="flatly">Flatly</a></li>
                <li><a href="javascript:void(0)" data-type="bootstrap" data-rel="journal">Journal</a></li>
                <li><a href="javascript:void(0)" data-type="bootstrap" data-rel="lumen">Lumen</a></li>
                <li><a href="javascript:void(0)" data-type="bootstrap" data-rel="paper">Paper</a></li>
                <li><a href="javascript:void(0)" data-type="bootstrap" data-rel="readable">Readable</a></li>
                <li><a href="javascript:void(0)" data-type="bootstrap" data-rel="sandstone">Sanstone</a></li>
                <li><a href="javascript:void(0)" data-type="bootstrap" data-rel="simplex">Simplex</a></li>
                <li><a href="javascript:void(0)" data-type="bootstrap" data-rel="slate">Slate</a></li>
                <li><a href="javascript:void(0)" data-type="bootstrap" data-rel="spacelab">Spacelab</a></li>
                <li><a href="javascript:void(0)" data-type="bootstrap" data-rel="superhero">Superhero</a></li>
                <li><a href="javascript:void(0)" data-type="bootstrap" data-rel="united">United</a></li>
                <li><a href="javascript:void(0)" data-type="bootstrap" data-rel="yeti">Yeti</a></li>
              </ul>
            </div>
          <?php } ?>
        </div>

        <!-- Favorites -->
        <div class="btn-group">
          <button type="button" onClick="javascript:$.open_favors()" class="favorites btn navbar-button-color dropdown-toggle" data-toggle="dropdown" data-tooltip="tooltip" data-placement="left" title="Merkliste" data-container="body">
            <i class="fa fa-star"></i>
          </button>
        </div>
        
        <?php if ( $lbs ) { ?>
        <!-- User NOT Login -->
        <div class="nologinarea btn-group<?php if ($Login) echo " hide";?>">
          <button onClick="javascript:$.loginonly('')" type="button" class="btn navbar-button-color" data-tooltip="tooltip" data-placement="left" title="Login" data-container="body">
            <i class="fa fa-user"></i>
          </button>
        </div>

        <!-- User Already Logged in -->
        <div class="loginarea btn-group<?php if (!$Login) echo " hide";?>">
          <button type="button" class="myarea btn navbar-button-color dropdown-toggle" data-toggle="dropdown" data-tooltip="tooltip" data-placement="left" title="Mein Bereich" data-container="body">
            <i class="fa fa-user"></i>
            <span class="caret"></span>
          </button>
          <ul class="dropdown-menu" role="menu">
            <li><a class="myarea2" href="javascript:$.open_user('userrentals')"><i class="fa fa-user"></i> Mein Bereich</a></li>
            <li class="divider"></li>
            <li><a class="userrentals" href="javascript:$.open_user('userrentals')">Ausleihen</a></li>
            <li><a class="userorders" href="javascript:$.open_user('userorders')">Bestellungen</a></li>
            <li><a class="userreservations" href="javascript:$.open_user('userreservations')">Vormerkungen</a></li>
            <li><a class="userfees" href="javascript:$.open_user('userfees')">Geb&uuml;hren</a></li>
            <li><a class="usersearches" href="javascript:$.open_user('usersearches')">Gespeicherte Suchen</a></li>

            <?php if ( isset($_SESSION["config_general"]["lbs"]["available"]) && $_SESSION["config_general"]["lbs"]["available"] != "" ) { ?>
            <li class="divider"></li>
            <li><a class="userstore" href="javascript:$.store_search()"><i class="glyphicon glyphicon-save"></i> Suche speichern</a></li>
            <?php } ?>

            <li class="divider"></li>
            <li><a href="javascript:$.logout()"><i class="glyphicon glyphicon-log-out"></i> Logout</a></li>
          </ul>
        </div>
        <?php } ?>

      </div>

      <!-- Such-Tripple-Feld -->
      <form class="navbar-form" role="search" method="get" action="javascript:$.navbar_search('#searchtext_md');">
        <div class="form-group" style="display:inline;">
          <div class="input-group" style="display:table;">
            
            <?php if ( $FacetDataPool ) { ?>
            <div class="input-group-btn" style="width:1%;">
              <div class="btn-group dropdown">
                <button type='button' class='selectdatapool btn btn-lg dropdown-toggle navbar-button-color' data-toggle="dropdown" data-tooltip="tooltip" data-placement="left" title="Bestand ausw&auml;hlen" data-container="body">
                    <span class="datapoollocal <?php if ( $_SESSION["filter"]["datapool"]=="global" ) echo " hide"; ?>"><i class="fa fa-map-marker"> </i> <span class="lang_datapoollocal">  <?php echo $_SESSION["language_".$_SESSION["language"]]["DATAPOOLLOCAL"]; ?></span></span>
                    <span class="datapoolglobal <?php if ( $_SESSION["filter"]["datapool"]=="local" ) echo " hide"; ?>"><i class="fa fa-globe"> </i> <span class="lang_datapoolglobal"> <?php echo $_SESSION["language_".$_SESSION["language"]]["DATAPOOLGLOBAL"]; ?> </span></span>
                  <span class="caret"></span>
                  <span class="sr-only">Toggle Dropdown</span>
                </button>
                <ul class="dropdown-menu facetpool" role="menu">
                  <li>
                    <a href="javascript:void(0)" data-icon="fa fa-map-marker" data-text="Diese Bibliothek" data-value="local">
                      <i class="fa fa-map-marker"></i> <span class="lang_datapoollocal"> </span>
                    </a>
                  </li>
                  <li>
                    <a href="javascript:void(0)" data-icon="fa fa-globe" data-text="Alle Bibliotheken" data-value="global">
                      <i class="fa fa-globe"></i> <span class="lang_datapoolglobal"> </span>
                    </a>
                  </li>
                </ul>
              </div>
            </div>
            <?php } ?>
            
            <input type="text" id="searchtext_md" autocomplete="off" class="form-control input-lg search_text typeahead" placeholder="Deine Suche..." value="<?php echo $Value; ?>">
            <span class="input-group-btn" style="width:1%;">
              <button type="submit" class="startsearch btn btn-lg navbar-button-color" data-tooltip="tooltip" data-placement="left" title="Suche starten" data-container="body"><span class="glyphicon glyphicon-search"></span></button>
              <?php if ( $Assistant ) { ?>
                <button onClick="javascript:$.open_assistant();" class="btn btn-lg navbar-button-color" data-tooltip="tooltip" data-placement="left" type="button" title="Assistent" data-container="body"><span class="glyphicon glyphicon-question-sign"></span></button>
              <?php } ?>
            </span>

          </div>
        </div>
      </form>

    </div><!--/.nav-collapse -->
  </div>
</nav>


<nav class="navbar navbar-default navbar-fixed-top hidden-md hidden-lg">
  <div class="container-fluid">

    <div class="row">
      <div class="col-xs-3">
        <div class="navbar-header">
          <a class="navbar-brand" href="<?php echo base_url(); ?>" data-tooltip="tooltip" data-placement="buttom" title="<?php echo $logotitle ?>" data-container="body"></a>
        </div>
      </div>
      <div class="col-xs-9">
          <?php if ( $lbs ) { ?>

          <!-- User NOT Login -->
          <div class="nologinarea btn-group pull-right left-padding<?php if ($Login) echo " hide";?>">
            <button onClick="javascript:$.loginonly()" type="button" class="btn  navbar-button-color" data-tooltip="tooltip" data-placement="left" title="Login" data-container="body">
              <i class="fa fa-user"></i>
            </button>
          </div>

          <!-- User Already Logged in -->
          <div class="loginarea btn-group pull-right left-padding<?php if (!$Login) echo " hide";?>">
            <button type="button" class="myarea btn navbar-button-color dropdown-toggle" data-toggle="dropdown" data-tooltip="tooltip" data-placement="left" title="Mein Bereich">
              <i class="fa fa-user"></i>
              <span class="caret"></span>
            </button>
            <ul class="dropdown-menu" role="menu">
              <li><a class="myarea2" href="javascript:$.open_user('userrentals')"><i class="fa fa-user"></i> Mein Bereich</a></li>
              <li class="divider"></li>
              <li><a class="userrentals" href="javascript:$.open_user('userrentals')">Ausleihen</a></li>
              <li><a class="userorders" href="javascript:$.open_user('userorders')">Bestellungen</a></li>
              <li><a class="userreservations" href="javascript:$.open_user('userreservations')">Vormerkungen</a></li>
              <li><a class="userfees" href="javascript:$.open_user('userfees')">Geb&uuml;hren</a></li>
              <li><a class="usersearches" href="javascript:$.open_user('usersearches')">Gespeicherte Suchen</a></li>
              <?php if ( isset($_SESSION["config_general"]["lbs"]["available"]) && $_SESSION["config_general"]["lbs"]["available"] != "" ) { ?>
                <li class="divider"></li>
                <li><a class="userstore" href="javascript:$.store_search()"><i class="glyphicon glyphicon-save"></i> Suche speichern</a></li>
              <?php } ?>
              <li class="divider"></li>
              <li><a href="javascript:$.logout()"><i class="glyphicon glyphicon-log-out"></i> Logout</a></li>
            </ul>
          </div>

          <?php } ?>

          <!-- Favorites -->
          <div class="btn-group pull-right left-padding">
            <button type="button" onClick="javascript:$.open_favors()" class="favorites btn navbar-button-color dropdown-toggle" data-toggle="dropdown" data-tooltip="tooltip" data-placement="left" title="Merkliste" data-container="body">
              <i class="fa fa-star"></i>
            </button>
          </div>

          <!-- Switches -->
          <div class="btn-group pull-right left-padding">

            <!-- Sprache -->
            <div class="btn-group dropdown">
              <button type='button' class='selectlanguage btn dropdown-toggle navbar-button-color' data-toggle="dropdown"  data-tooltip="tooltip" data-placement="left" title="Sprache ausw&auml;hlen" data-container="body">
                <img alt="<?php echo ( $_SESSION['language']=='ger' ) ? 'Englisch' : 'English'; ?>" class="lang_ger<?php if ( $_SESSION["language"]!="ger" ) echo " hide"; ?>" height='15' src='/systemassets/lukida/img/ger.png' />
                <img alt="<?php echo ( $_SESSION['language']=='ger' ) ? 'Deutsch' : 'German'; ?>" class="lang_eng<?php if ( $_SESSION["language"]=="ger" ) echo " hide"; ?>" height='15' src='/systemassets/lukida/img/eng.png' />
                <span class="caret"></span>
                <span class="sr-only">Toggle Dropdown</span>
              </button>
              <ul class="dropdown-menu lang" role="menu">
                <li>
                  <a href="javascript:void(0)" data-width='15px' data-src="/systemassets/lukida/img/ger.png" data-text="" data-value="ger">
                    <img alt="<?php echo ( $_SESSION['language']=='ger' ) ? 'Deutsch' : 'German'; ?>" width='20' src='/systemassets/lukida/img/ger.png' /> <span class="ger"></span>
                  </a>
                </li>
                <li>
                  <a href="javascript:void(0)" data-width='15px' data-src="/systemassets/lukida/img/eng.png" data-text="" data-value="eng">
                    <img alt="<?php echo ( $_SESSION['language']=='ger' ) ? 'Englisch' : 'English'; ?>" width='20' src='/systemassets/lukida/img/eng.png' /> <span class="eng"></span>
                  </a>
                </li>
              </ul>
            </div>

            <!-- Ansicht -->
            <div class="btn-group hidden-xs">
              <button type='button' class='selecttview btn dropdown-toggle navbar-button-color' data-toggle="dropdown" data-tooltip="tooltip" data-placement="left" title="Ansicht ausw&auml;hlen" data-container="body">
                <i class="fa fa-th-large"></i>
                <span class="caret"></span>
                <span class="sr-only">Toggle Dropdown</span>
              </button>
              <ul class="dropdown-menu layout" role="menu">
                <li class="">                   <a class="col1" href="javascript:void(0)" data-value="12"> </a></li>
                <li class="">                   <a class="col2" href="javascript:void(0)" data-value= "6"> </a></li>
                <li class="hidden-sm">          <a class="col3" href="javascript:void(0)" data-value= "4"> </a></li>
                <li class="hidden-sm hidden-md"><a class="col4" href="javascript:void(0)" data-value= "3"> </a></li>
              </ul>
            </div>

            <?php
            if ( $ThemeMode )
            {
              ?>
              <!-- Theme -->
              <div class="btn-group">
                <button type='button' class='selecttheme btn dropdown-toggle navbar-button-color' data-toggle="dropdown"  data-tooltip="tooltip" data-placement="left" title="Optik ausw&auml;hlen" data-container="body">
                  <i class="fa fa-eye"></i>
                  <span class="caret"></span>
                  <span class="sr-only">Toggle Dropdown</span>
                </button>
                <ul class="dropdown-menu theme" role="menu">
                  <?php
                  if ( $ThemeCustomer != "" )
                  {
                    echo "<li><a href='#' data-type='customer' data-rel='" . strtolower($ThemeCustomer) . "'>" . ucfirst($ThemeCustomer) . "</a></li>";
                    echo "<li class='divider'></li>";
                  }
                  ?>
                  <li><a href="javascript:void(0)" data-type="system" data-rel="vzg">VZG</a></li>
                  <li class="divider"></li>
                  <li><a href="javascript:void(0)" data-type="bootstrap" data-rel="bootstrap-theme">Bootstrap</a></li>
                  <li><a href="javascript:void(0)" data-type="bootstrap" data-rel="cerulean">Cerulean</a></li>
                  <li><a href="javascript:void(0)" data-type="bootstrap" data-rel="cosmo">Cosmo</a></li>
                  <li><a href="javascript:void(0)" data-type="bootstrap" data-rel="cyborg">Cyborg</a></li>
                  <li><a href="javascript:void(0)" data-type="bootstrap" data-rel="darkly">Darkly</a></li>
                  <li><a href="javascript:void(0)" data-type="bootstrap" data-rel="flatly">Flatly</a></li>
                  <li><a href="javascript:void(0)" data-type="bootstrap" data-rel="journal">Journal</a></li>
                  <li><a href="javascript:void(0)" data-type="bootstrap" data-rel="lumen">Lumen</a></li>
                  <li><a href="javascript:void(0)" data-type="bootstrap" data-rel="paper">Paper</a></li>
                  <li><a href="javascript:void(0)" data-type="bootstrap" data-rel="readable">Readable</a></li>
                  <li><a href="javascript:void(0)" data-type="bootstrap" data-rel="sandstone">Sanstone</a></li>
                  <li><a href="javascript:void(0)" data-type="bootstrap" data-rel="simplex">Simplex</a></li>
                  <li><a href="javascript:void(0)" data-type="bootstrap" data-rel="slate">Slate</a></li>
                  <li><a href="javascript:void(0)" data-type="bootstrap" data-rel="spacelab">Spacelab</a></li>
                  <li><a href="javascript:void(0)" data-type="bootstrap" data-rel="superhero">Superhero</a></li>
                  <li><a href="javascript:void(0)" data-type="bootstrap" data-rel="united">United</a></li>
                  <li><a href="javascript:void(0)" data-type="bootstrap" data-rel="yeti">Yeti</a></li>
                </ul>
              </div>
              <?php
            }
            ?>
          </div>

          <!-- Facetts -->
          <div id="btn_facets" class="btn-group pull-right hidden-sm hidden-xs">
            <button id="FACETTS" onClick="javascript:$.open_facets()" type="button" class="filterresults btn navbar-button-color" data-tooltip="tooltip" data-placement="left" title="Ergebnisse eingrenzen" data-container="body">
              <i class="fa fa-filter"></i>
            </button>
          </div>

      </div>
    </div>

    <div class="row">
      <div class="col-xs-12">

        <!-- Such-Tripple-Feld -->
        <form class="navbar-form" role="search" method="get" action="javascript:$.navbar_search('#searchtext_xs');">
          <div class="form-group" style="display:inline;">
            <div class="input-group" style="display:table;">

              <?php if ( $FacetDataPool ) { ?>
              <div class="input-group-btn" style="width:1%;">
                <div class="btn-group dropdown">
                  <button type='button' class='selectdatapool btn btn-lg dropdown-toggle navbar-button-color' data-toggle="dropdown" data-tooltip="tooltip" data-placement="left" title="Bestand ausw&auml;hlen" data-container="body">
                    <span class="datapoollocal <?php if ( $_SESSION["filter"]["datapool"]!="local" ) echo " hide"; ?>"><i class="fa fa-map-marker"> </i> </span>
                    <span class="datapoolglobal <?php if ( $_SESSION["filter"]["datapool"]=="local" ) echo " hide"; ?>"><i class="fa fa-globe"> </i> </span>
                    <span class="caret"></span>
                    <span class="sr-only">Toggle Dropdown</span>
                  </button>
                  <ul class="dropdown-menu facetpool" role="menu">
                    <li>
                      <a href="javascript:void(0)" data-icon="fa fa-map-marker" data-text="" data-value="local">
                        <i class="fa fa-map-marker"></i> <span class="lang_datapoollocal"></span>
                      </a>
                    </li>
                    <li>
                      <a href="javascript:void(0)" data-icon="fa fa-globe" data-text="" data-value="global">
                        <i class="fa fa-globe"></i> <span class="lang_datapoolglobal"></span>
                      </a>
                    </li>
                  </ul>
                </div>
              </div>
              <?php } ?>
              <input type="text" id='searchtext_xs' autocomplete="off" class="form-control input-lg typeahead search_text" placeholder="Deine Suche..." value="<?php echo $Value; ?>">
              <span class="input-group-btn" style="width:1%;">
                <button type="submit" class="startsearch btn btn-lg navbar-button-color" data-tooltip="tooltip" data-placement="left" title="Suche starten" data-container="body"><span class="glyphicon glyphicon-search"></span></button>
                <?php if ( $Assistant ) { ?>
                  <button onClick="javascript:$.open_assistant();" class="btn btn-lg navbar-button-color" data-tooltip="tooltip" data-placement="left" type="button" title="Assistent" data-container="body"><span class="glyphicon glyphicon-question-sign"></span></button>
                <?php } ?>
              </span>

            </div>
          </div>
        </form>

      </div>
    </div>

  </div>
</nav>