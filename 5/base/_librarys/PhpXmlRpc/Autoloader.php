<?php

namespace PhpXmlRpc;





class Autoloader
{
    




    public static function register($prepend = false)
    {
        spl_autoload_register(array(__CLASS__, 'autoload'), true, $prepend);
    }

    




    public static function autoload($class)
    {
        if (0 !== strpos($class, 'PhpXmlRpc\\')) {
            return;
        }

        if (is_file($file = __DIR__ . str_replace(array('PhpXmlRpc\\', '\\'), '/', $class).'.php')) {
            require $file;
        }
    }
}
