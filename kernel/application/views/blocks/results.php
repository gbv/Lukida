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

              <div class="FACETYEARTOTAL">
                <div class="well">
                  <input type="hidden" id="hiddenyearstart" />
                  <input type="hidden" id="hiddenyearend" />
                  <span class="FACETYEAR">Zeitraum </span> 
                  <span class="yearstart inlineedit"> </span> - <span class="yearend inlineedit"> </span>
                  <div class="navbar-form" id="pubyear"></div>
                </div>
              </div>

              <div id="FACETTYPTOTAL">
                <div class="well">
                  <div class="FACETTYP">Typ</div>
                  <div id="onlines"></div>
                </div>
              </div>

              <div id="FACETFORMATTOTAL">
                <div class="well">
                  <div class="FACETFORMAT">Formate </div>
                  <div id="formats"></div>
                </div>
              </div>
            </div>
          </div>
        </div>

      </div>

      <div class="col-sm-offset-5 col-sm-7 col-md-offset-4 col-md-8 col-lg-offset-3 col-lg-9">
        <div id='results_messagebar'></div>
        <div id='resultsonly' class='row row-auto'>
        </div>
      </div>

    </div>
  </div>
</div>