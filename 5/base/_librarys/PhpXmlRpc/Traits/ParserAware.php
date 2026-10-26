<?php

namespace PhpXmlRpc\Traits;

use PhpXmlRpc\Helper\XMLParser;





trait ParserAware
{
    protected static $parser;

    
    public function getParser()
    {
        if (self::$parser === null) {
            self::$parser = new XMLParser();
        }
        return self::$parser;
    }

    



    public static function setParser($parser)
    {
        self::$parser = $parser;
    }
}
