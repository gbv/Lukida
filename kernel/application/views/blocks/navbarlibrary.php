<?php

// Logo Settings
$logotitle = $_SESSION["config_general"]["general"]["title"];
$logocheck = true;

?>

<nav class="navbar navbar-default navbar-fixed-top hidden-xs hidden-sm">
  <div class="container-fluid">
    <div class="navbar-header">
      <a class="navbar-brand" href="<?php echo base_url(); ?>" data-tooltip="tooltip" data-placement="buttom" title="<?php echo $logotitle ?>" data-container="body"></a>
    </div>

       <div class="navbar-form navbar-right">

        <!-- Back -->
        <button type='button' class='btn navbar-button-color' 
                onclick="$.go('discover');">
          <i class="fa fa-long-arrow-left" aria-hidden="true"></i>
        </button>

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
      </div>
  
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

       <div class="navbar-form pull-right">

        <!-- Back -->
        <button type='button' class='btn navbar-button-color' 
                onclick="$.go('discover');">
          <i class="fa fa-long-arrow-left" aria-hidden="true"></i>
        </button>

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
        </div>
      </div>

    </div>
  </div>
</nav>