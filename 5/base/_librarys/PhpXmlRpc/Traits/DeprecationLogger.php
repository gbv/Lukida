<?php

namespace PhpXmlRpc\Traits;

use PhpXmlRpc\PhpXmlRpc;

trait DeprecationLogger
{
    use LoggerAware;

    protected function logDeprecation($message)
    {
        if (PhpXmlRpc::$xmlrpc_silence_deprecations) {
            return;
        }

        $this->getLogger()->warning('XML-RPC Deprecated: ' . $message);
    }

    




    protected function logDeprecationUnlessCalledBy($expectedCaller)
    {
        if (PhpXmlRpc::$xmlrpc_silence_deprecations) {
            return;
        }

        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3);
        
        if ($trace[2]['function'] === $expectedCaller) {
            return;
        }

        $this->getLogger()->warning('XML-RPC Deprecated: ' . $trace[1]['class'] . '::' . $trace[1]['function'] .
            ' is only supposed to be called by ' . $expectedCaller);
    }
}
