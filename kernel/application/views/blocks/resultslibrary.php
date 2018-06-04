<div class="container-fluid collapse" id="container_chart">
  <canvas id="chart"></canvas>
</div>
<div class="container-fluid collapse" id="container_log">
  <div id="searchlog">
    <div class="input-group col-md-12">
      <input type="text" id="searchlogtext" autofocus="autofocus" autocomplete="off" class="form-control input-lg" placeholder="Ihre Suche..." />
      <span class="input-group-btn">
        <button onClick="javascript:$.search_log();" class="btn btn-lg navbar-button-color" type="button" data-tooltip="tooltip" data-placement="left" title="Suche starten" data-container="body">
          <i class="fa fa-search" aria-hidden="true"></i>
        </button>
      </span>
    </div>
  </div>
  <div id="results_log"></div>
</div>
<div class="container-fluid collapse" id="container_cockpit">
  <div id="results_cockpit"></div>  
</div>
