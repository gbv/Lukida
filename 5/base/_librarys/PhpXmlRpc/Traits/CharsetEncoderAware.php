<?php

namespace PhpXmlRpc\Traits;

use PhpXmlRpc\Helper\Charset;





trait CharsetEncoderAware
{
    protected static $charsetEncoder;

    public function getCharsetEncoder()
    {
        if (self::$charsetEncoder === null) {
            self::$charsetEncoder = Charset::instance();
        }
        return self::$charsetEncoder;
    }

    



    public static function setCharsetEncoder($charsetEncoder)
    {
        self::$charsetEncoder = $charsetEncoder;
    }
}
