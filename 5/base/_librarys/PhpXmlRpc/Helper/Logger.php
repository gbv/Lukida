<?php

namespace PhpXmlRpc\Helper;





class Logger
{
    protected static $instance = null;

    




    public static function instance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    

    






    public function debug($message, $context = array())
    {
        if (isset($context['encoding'])) {
            $this->debugMessage($message, $context['encoding']);
        } else {
            $this->debugMessage($message);
        }
    }

    






    public function warning($message, $context = array())
    {
        $this->errorLog(preg_replace('/^XML-RPC :/', 'XML-RPC Warning: ', $message));
    }

    






    public function error($message, $context = array())
    {
        $this->errorLog(preg_replace('/^XML-RPC :/', 'XML-RPC Error: ', $message));
    }

    

    










    public function debugMessage($message, $encoding = null)
    {
        
        if ($encoding == 'US-ASCII') {
            $encoding = 'UTF-8';
        }

        if (PHP_SAPI != 'cli') {
            $flags = ENT_COMPAT;
            
            if (defined('ENT_HTML401')) {
                $flags =  $flags | ENT_HTML401;
            }
            if (defined('ENT_SUBSTITUTE')) {
                $flags =  $flags | ENT_SUBSTITUTE;
            }
            if ($encoding != null) {
                print "<PRE>\n".htmlentities($message, $flags, $encoding)."\n</PRE>";
            } else {
                print "<PRE>\n".htmlentities($message, $flags)."\n</PRE>";
            }
        } else {
            print "\n$message\n";
        }

        
        flush();
    }

    







    public function errorLog($message)
    {
        error_log($message);
    }
}
