<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

class Pearloader
{
  function load($package, $class, $options = null)
  {
    require_once($package.'/'.$class.'.php');
    $classname = $package."_".$class;
    if(is_null($options))
    {
      return new $classname();
    }
    elseif (is_array($options))
    {
      $reflector = new ReflectionClass($classname);
      return $reflector->newInstanceArgs($options);
    }
    else
    {
      return new $classname($options);
    }
  }

  function loadmarc($package, $class, $marc)
  {
    require_once($package.'/'.$class.'.php');
    $classname = $package."_".$class;
    return new $classname($marc, File_MARC::SOURCE_STRING);
  }

}

?>