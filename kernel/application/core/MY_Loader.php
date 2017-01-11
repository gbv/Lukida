<?php

class MY_Loader extends CI_Loader
{

  public function __construct()
  {
    parent::__construct();
  }

  public function library_view($folder, $view, $vars = array(), $return = FALSE) 
  {
    $this->_ci_view_paths = array_merge($this->_ci_view_paths, array($folder => TRUE));
    return $this->_ci_load(array(
      '_ci_view' => $view,
      '_ci_vars' => $vars,
      '_ci_return' => $return
    ));
  }
}
