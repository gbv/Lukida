<?php

namespace PhpXmlRpc\Helper;

use PhpXmlRpc\PhpXmlRpc;






class Date
{
    














    public static function iso8601Encode($timet, $utc = 0)
    {
        if (is_a($timet, 'DateTimeInterface') || is_a($timet, 'DateTime')) {
            $timet = $timet->getTimestamp();
        }
        if (!$utc) {
            $t = date('Ymd\TH:i:s', $timet);
        } else {
            $t = gmdate('Ymd\TH:i:s', $timet);
        }

        return $t;
    }

    







    public static function iso8601Decode($idate, $utc = 0)
    {
        $t = 0;
        if (preg_match(PhpXmlRpc::$xmlrpc_datetime_format, $idate, $regs)) {
            if ($utc) {
                $t = gmmktime($regs[4], $regs[5], $regs[6], $regs[2], $regs[3], $regs[1]);
            } else {
                $t = mktime($regs[4], $regs[5], $regs[6], $regs[2], $regs[3], $regs[1]);
            }
        }

        return $t;
    }
}
