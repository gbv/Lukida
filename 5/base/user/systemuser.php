<?php

class systemuser extends systemuserdb
{
  public function __construct($f3)
  {
    parent::__construct($f3);
    if ( APPDEBUG["FKT"] ) $this->debug("FKT",__METHOD__);
  }

  public function renewtoken()
  {
    if ( APPDEBUG["FKT"] ) $this->debug("FKT",__METHOD__);

    $Results = $this->renewTokenDB();

    $this->finalize($Results, __FUNCTION__);
  }
}

?>
