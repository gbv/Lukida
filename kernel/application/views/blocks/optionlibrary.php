<div class="container-fluid">
  <div class="collapse" id="libraryoption">
    <div class="row">
      <div class="col-sm-5 col-md-4 col-lg-3">
        <div class="well usage searches">
          <p>Zeitraum</p>
          <div class="input-group">
            <span class="input-group-addon open-daterange" for=daterange><i class="fa fa-calendar" aria-hidden="true"></i></span>
            <input id="daterange" type="text" class="form-control" placeholder="Username" aria-describedby="sizing-addon1">
          </div>
        </div>
      </div>
      <div class="col-sm-4 col-md-6 col-lg-6">
        <div class="well usage collapse">
          <p>Indikatoren - Gruppen</p>
          <div class="row">
            <div class="col-sm-6 col-md-3">
              <div class='checkbox'>
                <label><input name='Exporte' type='checkbox' data-value="Export_Bibtex,Export_Citavi,Export_Endnote,Export_Refworks">Exporte</label>
              </div>
              <div class='checkbox'>
                <label><input name='Facetten' type='checkbox' data-value="Facet_datapool,Facet_format,Facet_iln,Facet_typ,Facet_year">Facetten</label>
              </div>
            </div>
            <div class="col-sm-6 col-md-3">
              <div class='checkbox'>
                <label><input name='Layouts' type='checkbox' data-value="Layout_1,Layout_2,Layout_3,Layout_4" checked="checked">Layouts</label>
              </div>
              <div class='checkbox'>
                <label><input name='LBS' type='checkbox' data-value="LBS_Document,LBS_Login,LBS_Logout,LBS_Renew,LBS_Request">LBS</label>
              </div>
            </div>
            <div class="col-sm-6 col-md-3">
              <div class='checkbox'>
                <label><input name='Mails' type='checkbox' data-value="Mail,MailInfo,MailOrder,MailOrderView,MailSonder">Mails</label>
              </div>
              <div class='checkbox'>
                <label><input name='Views' type='checkbox' data-value="UserView,FullView">Sichten</label>
              </div>
            </div>
            <div class="col-sm-6 col-md-3">
              <div class='checkbox'>
                <label><input name='Sprachen' type='checkbox' data-value="Language_Eng,Language_Ger" checked="checked">Sprachen</label>
              </div>
              <div class='checkbox'>
                <label><input name='Bibliothek' type='checkbox' data-value="Command,LibrarySpecial,StoreUserSearch">Bibliothek</label>
              </div>
              <!--
              <div class='checkbox'>
                <label><input name='Sonstiges' type='checkbox' value="'ViewInit','NoJavaScript'">Sonstiges</label>
              </div>
              -->
            </div>
          </div>
        </div>
      </div>
      <div class="col-sm-3 col-md-2 col-lg-3">
        <div class="well usage collapse">
          <p>Weiteres</p>
            <div class='checkbox'>
              <label><input name='Stapel' type='checkbox' value="0">Gestapelt</label>
            </div>
        </div>
      </div>
    </div>
    <div class="row">
      <div class="col-xs-6 col-sm-2">
        <div class="well devicescreens">
          <p>Jahr</p>
          <div class="input-group number-spinner">
            <span class="input-group-btn">
              <a class="btn btn-danger" data-dir="dwn"><span class="glyphicon glyphicon-minus"></span></a>
            </span>
            <input type="text" disabled id="year" class="form-control text-center" value="2017" max=2049 min=2016>
            <span class="input-group-btn">
              <a class="btn btn-info" data-dir="up"><span class="glyphicon glyphicon-plus"></span></a>
            </span>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>