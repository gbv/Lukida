<?php

$FacetDataPool = (isset($_SESSION["config_" . $modul]["navbar"]["facet_datapool"]) 
                     && $_SESSION["config_" . $modul]["navbar"]["facet_datapool"] == "1" ) 
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
      <a class="navbar-brand" href="<?php echo base_url(); ?>" data-tooltip="tooltip" data-placement="buttom" title="<?php echo $logotitle ?>" data-container="body"> <span class="hidden"> Start </span></a>
    </div>

    <div class="navbar-collapse collapse searchbar">

      <div class="navbar-form navbar-right">

        <!-- Switches -->
        <div class="btn-group">

          <!-- Sprache -->
          <div class="btn-group dropdown">
            <button type='button' class='selectlanguage btn dropdown-toggle navbar-button-color' data-toggle="dropdown" data-tooltip="tooltip" data-placement="left" title="Sprache ausw&auml;hlen" data-container="body">
              <img alt="<?php echo ( $_SESSION['language']=='ger' ) ? 'Spr. Deutsch' : 'Language German'; ?>" class="showger<?php if ( $_SESSION["language"]!="ger" ) echo " hide"; ?>" height='15' src='/systemassets/lukida/img/ger.png' />
              <img alt="<?php echo ( $_SESSION['language']=='ger' ) ? 'Spr. Englisch' : 'Language English'; ?>" class="showeng<?php if ( $_SESSION["language"]=="ger" ) echo " hide"; ?>" height='15' src='/systemassets/lukida/img/eng.png' />
              <span class="caret"></span>
              <span class="sr-only">Sprache ausw&auml;hlen</span>
            </button>
            <ul class="dropdown-menu lang" role="menu">
              <li>
                <a href="javascript:void(0)" data-width='15px' data-src="/systemassets/lukida/img/ger.png" data-text="" data-value="ger">
                  <img alt="<?php echo ( $_SESSION['language']=='ger' ) ? 'Sprache Deutsch' : 'Language German'; ?>" width='20' src='/systemassets/lukida/img/ger.png' /> <span class="ger"></span>
                </a>
              </li>
              <li>
                <a href="javascript:void(0)" data-width='15px' data-src="/systemassets/lukida/img/eng.png" data-text="" data-value="eng">
                  <img alt="<?php echo ( $_SESSION['language']=='ger' ) ? 'Sprache Englisch' : 'Language English'; ?>" width='20' src='/systemassets/lukida/img/eng.png' /> <span class="eng"></span>
                </a>
              </li>
            </ul>
          </div>

          <!-- Ansicht -->
          <div class="btn-group hidden-xs">
            <button type='button' class='selectview btn dropdown-toggle navbar-button-color' data-toggle="dropdown" data-tooltip="tooltip" data-placement="left" title="Ansicht ausw&auml;hlen" data-container="body">
              <i class="fa fa-th-large"></i>
              <span class="caret"></span>
              <span class="sr-only">Ansicht ausw&auml;hlen</span>
            </button>
            <ul class="dropdown-menu layout" role="menu">
              <li class="">                   <a class="col1" href="javascript:void(0)" data-value="12"> </a></li>
              <li class="">                   <a class="col2" href="javascript:void(0)" data-value= "6"> </a></li>
              <li class="hidden-sm">          <a class="col3" href="javascript:void(0)" data-value= "4"> </a></li>
              <li class="hidden-sm hidden-md"><a class="col4" href="javascript:void(0)" data-value= "3"> </a></li>
            </ul>
          </div>
        </div>

        <!-- Favorites -->
        <div class="btn-group">
          <button type="button" onClick="javascript:$.open_favors()" class="favorites btn navbar-button-color" data-tooltip="tooltip" data-placement="left" title="Merkliste" data-container="body">
            <i class="fa fa-star" title="Merkliste"></i>
            <span class="sr-only">Merkliste</span>
          </button>
        </div>
        
        <?php if ( $lbs ) { ?>
        <!-- User NOT Login -->
        <div class="nologinarea btn-group<?php if ($Login) echo " hide";?>">
          <button onClick="javascript:$.loginonly('')" type="button" class="btn navbar-button-color" data-tooltip="tooltip" data-placement="left" title="Login" data-container="body">
            <i class="fa fa-user" title="Login"></i>
            <span class="sr-only">Login</span>
          </button>
        </div>

        <!-- User Already Logged in -->
        <div class="loginarea btn-group<?php if (!$Login) echo " hide";?>">
          <button type="button" class="myarea btn navbar-button-color dropdown-toggle" data-toggle="dropdown" data-tooltip="tooltip" data-placement="left" title="Mein Bereich" data-container="body">
            <i class="fa fa-user" title="Mein Bereich"></i>
            <span class="caret"></span>
            <span class="sr-only">Mein Bereich</span>
          </button>
          <ul class="dropdown-menu" role="menu">
            <li><a class="myarea2" href="javascript:$.open_user('userrentals')"><i class="fa fa-user"></i> Mein Bereich</a></li>
            <li class="divider"></li>
            <li><a class="usercollectables" href="javascript:$.open_user('usercollectables')">Abholbare Medien</a></li>
            <li><a class="userrentals" href="javascript:$.open_user('userrentals')">Ausleihen</a></li>
            <li><a class="userorders" href="javascript:$.open_user('userorders')">Bestellungen</a></li>
            <li><a class="userreservations" href="javascript:$.open_user('userreservations')">Vormerkungen</a></li>
            <li><a class="userfees" href="javascript:$.open_user('userfees')">Geb&uuml;hren</a></li>
            <li class="divider"></li>
            <li><a href="javascript:$.logout()"><i class="glyphicon glyphicon-log-out"></i> Logout</a></li>
          </ul>
        </div>
        <?php } ?>
      </div>

      <!-- Such-Tripple-Feld -->
      <div class="navbar-form">

        <div style="display:table;" class="input-group">

          <!-- Einstellungen -->
          <span class="input-group-btn" style="width:1%;">
            <button type="button" onClick="javascript:$.open_settings()" class="settings btn btn-lg navbar-button-color" data-tooltip="tooltip" data-placement="left" title="Einstellungen" data-container="body">
              <i class="fa fa-sliders"></i> 
              <span class='countSettings' data-toggle="tooltip" data-placement="bottom" data-title="Aktive Einstellungen" data-trigger="hover manual"> </span>
              <span class="sr-only">Einstellungen</span>
            </button>
          </span>
          <!-- Suchfeld -->
          <div class="btn-group form-control input-lg ">
            <label class="sr-only" for="searchtext_md">Ihre Suche...</label>
            <input type="search" id="searchtext_md" autofocus="autofocus" autocomplete="off" class="search_text typeahead" placeholder="Ihre Suche..." value="" style="width:99% !important;">
            <span class="search_clear" style="display: none"><i class="fa fa-times fa-2x"></i></span>
          </div>
          <!-- Lupe -->
          <span class="input-group-btn" style="width:1%;">
            <button type="button" onClick="javascript:$.new_search('init',$('#searchtext_md').val());" class="startsearch btn btn-lg navbar-button-color" data-tooltip="tooltip" data-placement="left" title="Suche starten" value="Suche starten" data-container="body">
              <i class="fa fa-search"></i>
              <span class="sr-only">Suche starten</span>
            </button>
          </span>
        </div>
      </div>
    </div>

  </div>
</nav>


<nav class="navbar navbar-default navbar-fixed-top hidden-md hidden-lg">
  <div class="container-fluid">

    <div class="row">
      <div class="col-xs-3">
        <div class="navbar-header">
          <a class="navbar-brand" href="<?php echo base_url(); ?>" data-tooltip="tooltip" data-placement="buttom" title="<?php echo $logotitle ?>" data-container="body"> <span class="hidden"> Start </span> </a>
        </div>
      </div>
      <div class="col-xs-9">
        <div class="navbar-form navbar-right">      
          <?php if ( $lbs ) { ?>

          <!-- User NOT Login -->
          <div class="nologinarea btn-group pull-right left-padding<?php if ($Login) echo " hide";?>">
            <button onClick="javascript:$.loginonly()" type="button" class="btn  navbar-button-color" data-tooltip="tooltip" data-placement="left" title="Login" data-container="body">
              <i class="fa fa-user"></i>
              <span class="sr-only">Login</span>
            </button>
          </div>

          <!-- User Already Logged in -->
          <div class="loginarea btn-group pull-right left-padding<?php if (!$Login) echo " hide";?>">
            <button type="button" class="myarea btn navbar-button-color dropdown-toggle" data-toggle="dropdown" data-tooltip="tooltip" data-placement="left" title="Mein Bereich">
              <i class="fa fa-user"></i>
              <span class="caret"></span>
              <span class="sr-only">Mein Bereich</span>
            </button>
            <ul class="dropdown-menu" role="menu">
              <li><a class="myarea2" href="javascript:$.open_user('userrentals')"><i class="fa fa-user"></i> Mein Bereich</a></li>
              <li class="divider"></li>
              <li><a class="usercollectables" href="javascript:$.open_user('usercollectables')">Abholbare Medien</a></li>
              <li><a class="userrentals" href="javascript:$.open_user('userrentals')">Ausleihen</a></li>
              <li><a class="userorders" href="javascript:$.open_user('userorders')">Bestellungen</a></li>
              <li><a class="userreservations" href="javascript:$.open_user('userreservations')">Vormerkungen</a></li>
              <li><a class="userfees" href="javascript:$.open_user('userfees')">Geb&uuml;hren</a></li>
              <li class="divider"></li>
              <li><a href="javascript:$.logout()"><i class="glyphicon glyphicon-log-out"></i> Logout</a></li>
            </ul>
          </div>

          <?php } ?>

          <!-- Favorites -->
          <div class="btn-group pull-right left-padding">
            <button type="button" onClick="javascript:$.open_favors()" class="favorites btn navbar-button-color dropdown-toggle" data-toggle="dropdown" data-tooltip="tooltip" data-placement="left" title="Merkliste" data-container="body">
              <i class="fa fa-star"></i>
              <span class="sr-only">Merkliste</span>
            </button>
          </div>

          <!-- Switches -->
          <div class="btn-group pull-right left-padding">

            <!-- Sprache -->
            <div class="btn-group dropdown">
              <button type='button' class='selectlanguage btn dropdown-toggle navbar-button-color' data-toggle="dropdown"  data-tooltip="tooltip" data-placement="left" title="Sprache ausw&auml;hlen" data-container="body">
                <img alt="<?php echo ( $_SESSION['language']=='ger' ) ? 'Spr. Englisch' : 'Language English'; ?>" class="showger<?php if ( $_SESSION["language"]!="ger" ) echo " hide"; ?>" height='15' src='/systemassets/lukida/img/ger.png' />
                <img alt="<?php echo ( $_SESSION['language']=='ger' ) ? 'Spr. Deutsch' : 'Language German'; ?>" class="showeng<?php if ( $_SESSION["language"]=="ger" ) echo " hide"; ?>" height='15' src='/systemassets/lukida/img/eng.png' />
                <span class="caret"></span>
                <span class="sr-only">Toggle Dropdown</span>
              </button>
              <ul class="dropdown-menu lang" role="menu">
                <li>
                  <a href="javascript:void(0)" data-width='15px' data-src="/systemassets/lukida/img/ger.png" data-text="" data-value="ger">
                    <img alt="<?php echo ( $_SESSION['language']=='ger' ) ? 'Sprache Deutsch' : 'Language German'; ?>" width='20' src='/systemassets/lukida/img/ger.png' /> <span class="ger"></span>
                  </a>
                </li>
                <li>
                  <a href="javascript:void(0)" data-width='15px' data-src="/systemassets/lukida/img/eng.png" data-text="" data-value="eng">
                    <img alt="<?php echo ( $_SESSION['language']=='ger' ) ? 'Sprache Englisch' : 'Language English'; ?>" width='20' src='/systemassets/lukida/img/eng.png' /> <span class="eng"></span>
                  </a>
                </li>
              </ul>
            </div>

            <!-- Ansicht -->
            <div class="btn-group hidden-xs">
              <button type='button' class='selecttview btn dropdown-toggle navbar-button-color' data-toggle="dropdown" data-tooltip="tooltip" data-placement="left" title="Ansicht ausw&auml;hlen" data-container="body">
                <i class="fa fa-th-large"></i>
                <span class="caret"></span>
                <span class="sr-only">Ansicht ausw&auml;hlen</span>
              </button>
              <ul class="dropdown-menu layout" role="menu">
                <li class="">                   <a class="col1" href="javascript:void(0)" data-value="12"> </a></li>
                <li class="">                   <a class="col2" href="javascript:void(0)" data-value= "6"> </a></li>
                <li class="hidden-sm">          <a class="col3" href="javascript:void(0)" data-value= "4"> </a></li>
                <li class="hidden-sm hidden-md"><a class="col4" href="javascript:void(0)" data-value= "3"> </a></li>
              </ul>
            </div>
          </div>

        </div>
      </div>
    </div>

    <div class="row">
      <div class="col-xs-12">

        <div class="navbar-form">

          <div style="display:table;" class="input-group">
            <span class="input-group-btn" style="width:1%;">
              <button type="button" onClick="javascript:$.open_settings()" class="settings btn btn-lg navbar-button-color" data-tooltip="tooltip" data-placement="left" title="Einstellungen" data-container="body">
                <i class="fa fa-sliders"></i> <span class='countSettings' data-toggle="tooltip" data-placement="bottom" data-title="Aktive Einstellungen" data-trigger="hover manual"> </span>
                <span class="sr-only">Einstellungen</span>
              </button>
            </span>
            <div class="btn-group form-control input-lg ">
              <label class="sr-only" for="searchtext_xs">Ihre Suche...</label>
              <input type="search" id="searchtext_xs" autocomplete="off" class="search_text typeahead" placeholder="Ihre Suche..." value="" style="width:99% !important;">
              <span class="search_clear" style="display: none"><i class="fa fa-times fa-2x"></i></span>
            </div>
            <span class="input-group-btn" style="width:1%;">
              <button type="button" onClick="javascript:$.new_search('init',$('#searchtext_xs').val());" class="startsearch btn btn-lg navbar-button-color" data-tooltip="tooltip" data-placement="left" title="Suche starten" data-container="body">
                <i class="fa fa-search"></i>
                <span class="sr-only">Suche starten</span>
              </button>
              <button type="button" onClick="javascript:$.toggle_area('facets');" class="btn btn-lg navbar-button-color hidden-sm mobilefacets" data-tooltip="tooltip" data-placement="left" title="Facetten" data-container="body">
                <span class="facetsdown"><i class="fa fa-angle-double-down"></i></span>
                <span class="facetsup collapse"><i class="fa fa-angle-double-up"></i></span>
                <span class="sr-only">Facetten</span>
              </button>
            </span>
          </div>

        </div>

      </div>
    </div>

    <div class="row discoverfacets collapse hidden-sm">
      <div class="col-xs-12">
        <div class="panel">
          <ul class='nav nav-tabs' role='tablist'>
            <li role='presentation' class='active'><a href='#tab1' class='lbl_sorting' aria-controls='tab4' role='tab' data-toggle='tab'>Sortierung</a></li>
            <li role='presentation'><a href='#tab2' class='lbl_facetyear' aria-controls='tab1' role='tab' data-toggle='tab'>Zeitraum</a></li>
            <li role='presentation'><a href='#tab3' class='lbl_facettyp' aria-controls='tab2' role='tab' data-toggle='tab'>Typ</a></li>
            <li role='presentation'><a href='#tab4' class='lbl_facetformat' aria-controls='tab3' role='tab' data-toggle='tab'>Formate</a></li>
          </ul>
          <div class='tab-content'>

            <div role='tabpanel' class='tab-pane fade in active' id='tab1'>
              <div class="FACETSORTINGTOTAL">
                <div class="well">
                  <div class="btn-group btn-group-justified" role="group" aria-label="Sortierung">
                    <div class="btn-group">
                      <button type="button" onclick="javascript:$.set_sort('scoredesc')" class="btn navbar-button-color sortscoredesc"><span class="lbl_score">Relevanz</span> <i class="fa fa-arrow-down" aria-hidden="true"></i></button>
                    </div>
                    <div class="btn-group">
                      <button type="button" onclick="javascript:$.set_sort('yeardesc')" class="btn btn-default sortyeardesc"><span class="lbl_year">Jahr</span> <i class="fa fa-arrow-down" aria-hidden="true"></i></button>
                    </div>
                    <div class="btn-group">
                      <button type="button" onclick="javascript:$.set_sort('yearasc')" class="btn btn-default sortyearasc"><span class="lbl_year">Jahr</span> <i class="fa fa-arrow-up" aria-hidden="true"></i></button>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div role='tabpanel' class='tab-pane' id='tab2'>
              <div class="FACETYEARTOTAL">
                <div class="well">
                  <span class="FACETYEAR">Zeitraum </span> 
                  <span class='yearstart editable' data-type='number' data-mode='popup' data-container='body' data-placement='top' data-inputclass='yearinput'> </span> - 
                  <span class='yearend editable' data-type='number' data-mode='popup' data-container='body' data-placement='top' data-inputclass='yearinput'> </span>
                  <div id="pubyear2"></div>
                </div>
              </div>
            </div>
            <div role='tabpanel' class='tab-pane' id='tab3'>
              <div class="FACETTYPTOTAL">
                <div class="well">
                  <div class="onlines" id="onlines2"></div>
                </div>
              </div>
            </div>
            <div role='tabpanel' class='tab-pane' id='tab4'>
              <div class="FACETFORMATTOTAL">
                <div class="well">
                  <div class="formats" id="formats2"></div>
                </div>
              </div>
            </div>
          </div>
        </div>    
      </div>
    </div>
  </div>
</nav>
