<?php

namespace PhpXmlRpc;

use PhpXmlRpc\Helper\Charset;
use PhpXmlRpc\Helper\Http;
use PhpXmlRpc\Helper\Interop;
use PhpXmlRpc\Helper\XMLParser;




class PhpXmlRpc
{
    


    public static $xmlrpcerr = array(
        'unknown_method' => 1, 
        
        'invalid_return' => 2, 
        'incorrect_params' => 3, 
        'introspect_unknown' => 4, 
        'http_error' => 5, 
        'no_data' => 6, 
        'no_ssl' => 7, 
        'curl_fail' => 8, 
        'invalid_request' => 15, 
        'no_curl' => 16, 
        'server_error' => 17, 
        'multicall_error' => 18, 
        'multicall_notstruct' => 9, 
        'multicall_nomethod' => 10, 
        'multicall_notstring' => 11, 
        'multicall_recursion' => 12, 
        'multicall_noparams' => 13, 
        'multicall_notarray' => 14, 
        'no_http2' => 19, 
        'unsupported_option' => 20, 
        
        
        'invalid_xml' => 2, 
        'xml_not_compliant' => 2, 
        'xml_parsing_error' => 2, 

        'cannot_decompress' => 153,
        'decompress_fail' => 154,
        'dechunk_fail' => 155,
        'server_cannot_decompress' => 156,
        'server_decompress_fail' => 157,
    );

    


    public static $xmlrpcstr = array(
        'unknown_method' => 'Unknown method',
        
        'invalid_return' => 'Invalid response payload (you can use the setDebug method to allow analysis of the response)',
        'incorrect_params' => 'Incorrect parameters passed to method',
        'introspect_unknown' => "Can't introspect: method unknown",
        'http_error' => "Didn't receive 200 OK from remote server",
        'no_data' => 'No data received from server',
        'no_ssl' => 'No SSL support compiled in',
        'curl_fail' => 'CURL error',
        'invalid_request' => 'Invalid request payload',
        'no_curl' => 'No CURL support compiled in',
        'server_error' => 'Internal server error',
        'multicall_error' => 'Received from server invalid multicall response',
        'multicall_notstruct' => 'system.multicall expected struct',
        'multicall_nomethod' => 'Missing methodName',
        'multicall_notstring' => 'methodName is not a string',
        'multicall_recursion' => 'Recursive system.multicall forbidden',
        'multicall_noparams' => 'Missing params',
        'multicall_notarray' => 'params is not an array',
        'no_http2' => 'No HTTP/2 support compiled in',
        'unsupported_option' => 'Some client option is not supported with the transport method currently in use',
        
        
        'invalid_xml' => 'Invalid response payload (you can use the setDebug method to allow analysis of the response)',
        'xml_not_compliant' => 'Invalid response payload (you can use the setDebug method to allow analysis of the response)',
        'xml_parsing_error' => 'Invalid response payload (you can use the setDebug method to allow analysis of the response)',

        'cannot_decompress' => 'Received from server compressed HTTP and cannot decompress',
        'decompress_fail' => 'Received from server invalid compressed HTTP',
        'dechunk_fail' => 'Received from server invalid chunked HTTP',
        'server_cannot_decompress' => 'Received from client compressed HTTP request and cannot decompress',
        'server_decompress_fail' => 'Received from client invalid compressed HTTP request',
    );

    




    public static $xmlrpc_defencoding = "UTF-8";
    






    public static $xmlrpc_detectencodings = array();
    






    public static $xmlrpc_internalencoding = "UTF-8";

    


    public static $xmlrpcName = "XML-RPC for PHP";
    


    public static $xmlrpcVersion = "4.11.5";

    



    public static $xmlrpcerruser = 800;
    






    public static $xmlrpcerrxml = 100;

    



    public static $xmlrpc_null_extension = false;

    





    public static $xmlrpc_return_datetimes = false;

    





    public static $xmlrpc_reject_invalid_values = false;

    



    public static $xmlrpc_null_apache_encoding = false;

    public static $xmlrpc_null_apache_encoding_ns = "http://ws.apache.org/xmlrpc/namespaces/extensions";

    




    public static $xmlpc_double_precision = 128;

    






    public static $xmlrpc_datetime_format = '/^([0-9]{4})(0[1-9]|1[012])(0[1-9]|[12][0-9]|3[01])T([01][0-9]|2[0-4]):([0-5][0-9]):([0-5][0-9]|60)$/';

    






    public static $xmlrpc_int_format = '/^[ \t]*[+-]?[0-9]+[ \t]*$/';

    






    public static $xmlrpc_double_format = '/^[ \t]*[-+]?[0-9]*\.?[0-9]+([eE][-+]?[0-9]+)?[ \t]*$/';

    










    public static $xmlrpc_methodname_format = '|^[ \t]*[a-zA-Z0-9_.:/]+[ \t]*$|';

    



    public static $xmlrpc_silence_deprecations = true;

    

    





    public static function setLogger($logger)
    {
        Charset::setLogger($logger);
        Client::setLogger($logger);
        Encoder::setLogger($logger);
        Http::setLogger($logger);
        Request::setLogger($logger);
        Response::setLogger($logger);
        Server::setLogger($logger);
        Value::setLogger($logger);
        Wrapper::setLogger($logger);
        XMLParser::setLogger($logger);
    }

    






    public static function useInteropFaults()
    {
        self::$xmlrpcerr = Interop::$xmlrpcerr;

        self::$xmlrpcerruser = -Interop::$xmlrpcerruser;
    }

    public static function isUsingInteropFaults()
    {
        return self::$xmlrpcerruser == -Interop::$xmlrpcerruser;
    }

    






    public static function exportGlobals()
    {
        $reflection = new \ReflectionClass('PhpXmlRpc\PhpXmlRpc');
        foreach ($reflection->getStaticProperties() as $name => $value) {
            if (!in_array($name, array('xmlrpc_return_datetimes', 'xmlrpc_reject_invalid_values', 'xmlrpc_datetime_format',
                'xmlrpc_int_format', 'xmlrpc_double_format', 'xmlrpc_methodname_format', 'xmlrpc_silence_deprecations'))) {
                $GLOBALS[$name] = $value;
            }
        }

        
        

        $reflection = new \ReflectionClass('PhpXmlRpc\Value');
        foreach ($reflection->getStaticProperties() as $name => $value) {
            if (!in_array($name, array('logger', 'charsetEncoder'))) {
                $GLOBALS[$name] = $value;
            }
        }

        

        $parser = new Helper\XMLParser();
        $GLOBALS['xmlrpc_valid_parents'] = $parser->xmlrpc_valid_parents;

        $charset = Charset::instance();
        $GLOBALS['xml_iso88591_Entities'] = $charset->getEntities('iso88591');
    }

    















    public static function importGlobals()
    {
        $reflection = new \ReflectionClass('PhpXmlRpc\PhpXmlRpc');
        foreach ($reflection->getStaticProperties() as $name => $value) {
            if (!in_array($name, array('xmlrpc_return_datetimes', 'xmlrpc_reject_invalid_values', 'xmlrpc_datetime_format',
                'xmlrpc_int_format', 'xmlrpc_double_format', 'xmlrpc_methodname_format', 'xmlrpc_silence_deprecations')))
            {
                if (isset($GLOBALS[$name])) {
                    self::$$name = $GLOBALS[$name];
                }
            }
        }
    }
}
