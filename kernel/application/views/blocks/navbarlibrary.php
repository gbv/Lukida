<?php

// Logo Settings
$logotitle = $_SESSION["config_general"]["general"]["title"];
$logocheck = true;

?>

<nav class="navbar navbar-default navbar-fixed-top">
  <div class="container-fluid">

    <div class="row vertical-align">

      <div class="col-xs-2 col-sm-4 col-md-3">
        <a class="navbar-brand" href="<?php echo base_url(); ?>" data-tooltip="tooltip" data-placement="buttom" title="<?php echo $logotitle ?>" data-container="body"></a>
      </div><!--
    --><div class="col-xs-3 col-sm-3 col-md-2">
    <div class="btn-group dropdown">
      <button type='button' class='btn dropdown-toggle navbar-button-color' data-toggle="dropdown" data-tooltip="tooltip" data-placement="left" title="Chart ausw&auml;hlen" data-container="body">
        Charts
        <span class="caret"></span>
      </button>
      <ul class="dropdown-menu chart" role="menu">
        <li class="dropdown-header">Benutzer</li>
        <li><a href="javascript:void(0)" data-value="searches"><i class="fa fa-bar-chart" aria-hidden="true"></i> Suchen</a></li>
        <li><a href="javascript:void(0)" data-value="usage"><i class="fa fa-line-chart" aria-hidden="true"></i> Nutzung</a></li>
                <!--
                <li role="separator" class="divider"></li>
                <li class="dropdown-header">System</li>
                <li><a href="javascript:void(0)" data-value="devices">Spass</a></li>
              -->
            </ul>
          </div>
      </div><!--
    --><div class="col-xs-7 col-sm-5 col-md-7 text-right">

        <!-- Optionen -->
        <div class="btn-group">
          <button class="btn navbar-button-color" type="button" data-toggle="collapse" onclick="$.toggle_area('option');">
            <span id='optiondown'><i class="fa fa-angle-double-down" aria-hidden="true"></i></span>
            <span id='optionup' class="collapse"><i class="fa fa-angle-double-up" aria-hidden="true"></i></span> 
            <span class='lang_ger'>Optionen</span><span class='lang_eng hide'>Options</span>
          </button>
        </div>

        <!-- Hilfe -->
        <div class="btn-group">
          <button class="btn navbar-button-color" type="button" data-toggle="collapse" onclick="$.toggle_area('help');">
            <span id='helpdown'><i class="fa fa-angle-double-down" aria-hidden="true"></i></span>
            <span id='helpup' class="collapse"><i class="fa fa-angle-double-up" aria-hidden="true"></i></span> 
            <span class='lang_ger'>Hilfe</span><span class='lang_eng hide'>Help</span>
          </button>
        </div>

        <div class="btn-group">

          <!-- Refresh -->
          <button type='button' class='btn navbar-button-color' title="Aktualisieren" data-tooltip="tooltip" data-placement="left" data-container="body" onclick="$.refresh();">
            <i class="fa fa-refresh" aria-hidden="true"></i>
          </button>

          <!-- Sprache -->
          <div class="btn-group dropdown">
            <button type='button' class='selectlanguage btn dropdown-toggle navbar-button-color' data-toggle="dropdown" data-tooltip="tooltip" data-placement="left" title="Sprache     ausw&auml;hlen" data-container="body">
    
              <img alt="<?php echo ( $_SESSION['language']=='ger' ) ? 'Deutsch' : 'German'; ?>" class="lang_ger<?php if ( $_SESSION["language"]!="ger" ) echo " hide"; ?>" height='   15' src='/systemassets/lukida/img/ger.png' />
              <img alt="<?php echo ( $_SESSION['language']=='ger' ) ? 'Englisch' : 'English'; ?>" class="lang_eng<?php if ( $_SESSION["language"]=="ger" ) echo " hide"; ?>" height=    '15' src='/systemassets/lukida/img/eng.png' />
              <span class="caret"></span>
              <span class="sr-only">Toggle Dropdown</span>
            </button>
            <ul class="dropdown-menu dropdown-menu-right lang" role="menu">
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
    
        </div>
    
        <!-- Back -->
        <button type='button' class='btn navbar-button-color' title="Zur&uuml;ck" data-tooltip="tooltip" data-placement="left" data-container="body" onclick="$.go('discover');">
          <i class="fa fa-sign-out" aria-hidden="true"></i>
        </button>
    

      </div>
    </div>
  </div>
</nav>
