<div class="container-fluid">
  <div id="results" class='results collapse'>
    <div class="row">

      <div id="facets" class="hidden-xs col-sm-5 col-md-4 col-lg-3">
        <div class="panel">

          <div class="panel-heading navbar-panel-color">
            <span class="TOTALRECS"></span>
            <span class="RESULTFILTER">Ergebnisse eingrenzen</span>

          </div>

          <div id="facetbody" class="panel-collapse collapse in">
            <div class="panel-body start-body">

              <div class="SORTING">
                  <div class="btn-group btn-group-justified" role="group" aria-label="Sortierung">
                    <div class="btn-group">
                      <button type="button" onclick="javascript:$.set_sort('scoredesc')" class="btn navbar-button-color sortscoredesc" style="padding:6px"><span class="lbl_score">Relevanz</span> <i class="fa fa-arrow-down" aria-hidden="true"></i></button>
                    </div>
                    <div class="btn-group">
                      <button type="button" onclick="javascript:$.set_sort('yeardesc')" class="btn btn-default sortyeardesc" style="padding:6px"><span class="lbl_year">Jahr</span> <i class="fa fa-arrow-down" aria-hidden="true"></i></button>
                    </div>
                    <div class="btn-group">
                      <button type="button" onclick="javascript:$.set_sort('yearasc')" class="btn btn-default sortyearasc" style="padding:6px"><span class="lbl_year">Jahr</span> <i class="fa fa-arrow-up" aria-hidden="true"></i></button>
                    </div>
                  </div>
                  <p>
                  </p>
              </div>

              <div class="FACETYEARTOTAL">
                <div class="well">
                  <span class="lbl_facetyear">Zeitraum </span> 
                  <span class='yearstart editable' data-type='number' data-mode='popup' data-container='body' data-placement='top' data-inputclass='yearinput'> </span> - 
                  <span class='yearend editable' data-type='number' data-mode='popup' data-container='body' data-placement='top' data-inputclass='yearinput'> </span>
                  <div class="navbar-form" id="pubyear1"></div>
                </div>
              </div>

              <div class="FACETTYPTOTAL">
                <div class="well">
                  <div class="lbl_facettyp">Typ</div>
                  <div class="onlines" id="onlines1"></div>
                </div>
              </div>

              <div class="FACETFORMATTOTAL">
                <div class="well">
                  <div class="lbl_facetformat">Formate </div>
                  <div class="formats" id="formats1"></div>
                </div>
              </div>


            </div>
          </div>
        </div>

      </div>

      <div class="col-sm-offset-5 col-sm-7 col-md-offset-4 col-md-8 col-lg-offset-3 col-lg-9">
        <div id="results_querybar" class="well query collapse">
          <div id='results_query'></div>
        </div>
        <div id='results_messagebar'></div>
        <div id='resultsonly' class='row row-auto'>
        </div>
      </div>

    </div>
  </div>
</div>