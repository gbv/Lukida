<div class="container-fluid">
	<div id="container_results" class="collapse">
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

    <div id="results"></div>
  </div>
</div>
<div class="container-fluid">
	<div id="container">
    <canvas id="chart"></canvas>
  </div>
</div>
