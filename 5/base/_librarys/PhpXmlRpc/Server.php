<?php

namespace PhpXmlRpc;

use PhpXmlRpc\Exception\NoSuchMethodException;
use PhpXmlRpc\Exception\ValueErrorException;
use PhpXmlRpc\Helper\Http;
use PhpXmlRpc\Helper\Interop;
use PhpXmlRpc\Helper\Logger;
use PhpXmlRpc\Helper\XMLParser;
use PhpXmlRpc\Traits\CharsetEncoderAware;
use PhpXmlRpc\Traits\DeprecationLogger;
use PhpXmlRpc\Traits\ParserAware;













class Server
{
    use CharsetEncoderAware;
    use DeprecationLogger;
    use ParserAware;

    const OPT_ACCEPTED_COMPRESSION = 'accepted_compression';
    const OPT_ALLOW_SYSTEM_FUNCS = 'allow_system_funcs';
    const OPT_COMPRESS_RESPONSE = 'compress_response';
    const OPT_DEBUG = 'debug';
    const OPT_EXCEPTION_HANDLING = 'exception_handling';
    const OPT_FUNCTIONS_PARAMETERS_TYPE = 'functions_parameters_type';
    const OPT_PHPVALS_ENCODING_OPTIONS = 'phpvals_encoding_options';
    const OPT_RESPONSE_CHARSET_ENCODING = 'response_charset_encoding';

    
    protected static $responseClass = '\\PhpXmlRpc\\Response';

    






    protected $functions_parameters_type = 'xmlrpcvals';

    





    protected $phpvals_encoding_options = array('auto_dates');

    









    protected $debug = 1;

    








    protected $exception_handling = 0;

    





    protected $compress_response = false;

    




    protected $accepted_compression = array();

    



    protected $allow_system_funcs = true;

    




    private $accepted_charset_encodings = array();

    









    protected $response_charset_encoding = '';

    protected static $options = array(
        self::OPT_ACCEPTED_COMPRESSION,
        self::OPT_ALLOW_SYSTEM_FUNCS,
        self::OPT_COMPRESS_RESPONSE,
        self::OPT_DEBUG,
        self::OPT_EXCEPTION_HANDLING,
        self::OPT_FUNCTIONS_PARAMETERS_TYPE,
        self::OPT_PHPVALS_ENCODING_OPTIONS,
        self::OPT_RESPONSE_CHARSET_ENCODING,
    );

    




    public $user_data = null;

    



    protected $dmap = array();

    


    protected $debug_info = '';

    protected static $_xmlrpc_debuginfo = '';
    protected static $_xmlrpcs_occurred_errors = '';
    protected static $_xmlrpcs_prev_ehandler = '';

    











    public function __construct($dispatchMap = null, $serviceNow = true)
    {
        
        
        if (function_exists('gzinflate')) {
            $this->accepted_compression[] = 'gzip';
        }
        if (function_exists('gzuncompress')) {
            $this->accepted_compression[] = 'deflate';
        }
        if (function_exists('gzencode') || function_exists('gzcompress')) {
            $this->compress_response = true;
        }

        
        $this->accepted_charset_encodings = array('UTF-8', 'ISO-8859-1', 'US-ASCII');

        
        
        
        
        if ($dispatchMap) {
            $this->setDispatchMap($dispatchMap);
            if ($serviceNow) {
                $this->service();
            }
        }
    }

    





    public function setOption($name, $value)
    {
        switch ($name) {
            case self::OPT_ACCEPTED_COMPRESSION :
            case self::OPT_ALLOW_SYSTEM_FUNCS:
            case self::OPT_COMPRESS_RESPONSE:
            case self::OPT_DEBUG:
            case self::OPT_EXCEPTION_HANDLING:
            case self::OPT_FUNCTIONS_PARAMETERS_TYPE:
            case self::OPT_PHPVALS_ENCODING_OPTIONS:
            case self::OPT_RESPONSE_CHARSET_ENCODING:
                $this->$name = $value;
                break;
            default:
                throw new ValueErrorException("Unsupported option '$name'");
        }

        return $this;
    }

    




    public function getOption($name)
    {
        switch ($name) {
            case self::OPT_ACCEPTED_COMPRESSION:
            case self::OPT_ALLOW_SYSTEM_FUNCS:
            case self::OPT_COMPRESS_RESPONSE:
            case self::OPT_DEBUG:
            case self::OPT_EXCEPTION_HANDLING:
            case self::OPT_FUNCTIONS_PARAMETERS_TYPE:
            case self::OPT_PHPVALS_ENCODING_OPTIONS:
            case self::OPT_RESPONSE_CHARSET_ENCODING:
                return $this->$name;
            default:
                throw new ValueErrorException("Unsupported option '$name'");
        }
    }

    



    public function getOptions()
    {
        $values = array();
        foreach(static::$options as $opt) {
            $values[$opt] = $this->getOption($opt);
        }
        return $values;
    }

    




    public function setOptions($options)
    {
        foreach($options as $name => $value) {
            $this->setOption($name, $value);
        }

        return $this;
    }

    














    public function setDebug($level)
    {
        $this->debug = $level;
        return $this;
    }

    







    public static function xmlrpc_debugmsg($msg)
    {
        static::$_xmlrpc_debuginfo .= $msg . "\n";
    }

    






    public static function error_occurred($msg)
    {
        static::$_xmlrpcs_occurred_errors .= $msg . "\n";
    }

    








    public function serializeDebug($charsetEncoding = '')
    {
        
        
        
        
        
        $out = '';
        if ($this->debug_info != '') {
            $out .= "<!-- SERVER DEBUG INFO (BASE64 ENCODED):\n" . base64_encode($this->debug_info) . "\n-->\n";
        }
        if (static::$_xmlrpc_debuginfo != '') {
            $out .= "<!-- DEBUG INFO:\n" . $this->getCharsetEncoder()->encodeEntities(str_replace('--', '_-', static::$_xmlrpc_debuginfo), PhpXmlRpc::$xmlrpc_internalencoding, $charsetEncoding) . "\n-->\n";
            
            
            
        }

        return $out;
    }

    








    public function service($data = null, $returnPayload = false)
    {
        if ($data === null) {
            $data = file_get_contents('php://input');
        }
        $rawData = $data;

        
        $this->debug_info = '';

        
        if ($this->debug > 1) {
            $this->debugMsg("+++GOT+++\n" . $data . "\n+++END+++");
        }

        $resp = $this->parseRequestHeaders($data, $reqCharset, $respCharset, $respEncoding);
        if (!$resp) {
            
            $resp = $this->parseRequest($data, $reqCharset);

            
            
            
            
            
            
            $resp->raw_data = $rawData;
        }

        if ($this->debug > 2 && static::$_xmlrpcs_occurred_errors != '') {
            $this->debugMsg("+++PROCESSING ERRORS AND WARNINGS+++\n" .
                static::$_xmlrpcs_occurred_errors . "+++END+++");
        }

        $payload = $this->generatePayload($resp, $respCharset);

        if ($returnPayload) {
            return $payload;
        }

        $this->printPayload($payload, $resp->getContentType(), $respEncoding);

        
        return $resp;
    }

    
















    public function addToMap($methodName, $function, $sig = null, $doc = false, $sigDoc = false, $parametersType = false,
        $exceptionHandling = false)
    {
       $this->add_to_map($methodName, $function, $sig, $doc, $sigDoc, $parametersType, $exceptionHandling);
    }

    

















    public function add_to_map($methodName, $function, $sig = null, $doc = false, $sigDoc = false, $parametersType = false,
        $exceptionHandling = false)
    {
        $this->logDeprecationUnlessCalledBy('addToMap');

        $this->dmap[$methodName] = array(
            'function' => $function,
            'docstring' => $doc,
        );
        if ($sig) {
            $this->dmap[$methodName]['signature'] = $sig;
        }
        if ($sigDoc) {
            $this->dmap[$methodName]['signature_docs'] = $sigDoc;
        }
        if ($parametersType) {
            $this->dmap[$methodName]['parameters_type'] = $parametersType;
        }
        if ($exceptionHandling !== false) {
            $this->dmap[$methodName]['exception_handling'] = $exceptionHandling;
        }
    }

    






    protected function verifySignature($in, $sigs)
    {
        
        if (is_object($in)) {
            $numParams = $in->getNumParams();
        } else {
            $numParams = count($in);
        }
        foreach ($sigs as $curSig) {
            if (count($curSig) == $numParams + 1) {
                $itsOK = 1;
                for ($n = 0; $n < $numParams; $n++) {
                    if (is_object($in)) {
                        $p = $in->getParam($n);
                        if ($p->kindOf() == 'scalar') {
                            $pt = $p->scalarTyp();
                        } else {
                            $pt = $p->kindOf();
                        }
                    } else {
                        $pt = ($in[$n] == 'i4') ? 'int' : strtolower($in[$n]); 
                    }

                    
                    if ($pt != $curSig[$n + 1] && $curSig[$n + 1] != Value::$xmlrpcValue) {
                        $itsOK = 0;
                        $pno = $n + 1;
                        $wanted = $curSig[$n + 1];
                        $got = $pt;
                        break;
                    }
                }
                if ($itsOK) {
                    return array(1, '');
                }
            }
        }
        if (isset($wanted)) {
            return array(0, "Wanted {$wanted}, got {$got} at param {$pno}");
        } else {
            return array(0, "No method signature matches number of parameters");
        }
    }

    




    protected function parseRequestHeaders(&$data, &$reqEncoding, &$respEncoding, &$respCompression)
    {
        
        
        if (count($_SERVER) == 0) {
            $this->getLogger()->error('XML-RPC: ' . __METHOD__ . ': cannot parse request headers as $_SERVER is not populated');
        }

        if ($this->debug > 1) {
            if (function_exists('getallheaders')) {
                $this->debugMsg(''); 
                foreach (getallheaders() as $name => $val) {
                    $this->debugMsg("HEADER: $name: $val");
                }
            }
        }

        if (isset($_SERVER['HTTP_CONTENT_ENCODING'])) {
            $contentEncoding = str_replace('x-', '', $_SERVER['HTTP_CONTENT_ENCODING']);
        } else {
            $contentEncoding = '';
        }

        $rawData = $data;

        
        if ($contentEncoding != '' && strlen($data)) {
            if ($contentEncoding == 'deflate' || $contentEncoding == 'gzip') {
                
                
                if (function_exists('gzinflate') && in_array($contentEncoding, $this->accepted_compression)) {
                    if ($contentEncoding == 'deflate' && $degzdata = @gzuncompress($data)) {
                        $data = $degzdata;
                        if ($this->debug > 1) {
                            $this->debugMsg("\n+++INFLATED REQUEST+++[" . strlen($data) . " chars]+++\n" . $data . "\n+++END+++");
                        }
                    } elseif ($contentEncoding == 'gzip' && $degzdata = @gzinflate(substr($data, 10))) {
                        $data = $degzdata;
                        if ($this->debug > 1) {
                            $this->debugMsg("+++INFLATED REQUEST+++[" . strlen($data) . " chars]+++\n" . $data . "\n+++END+++");
                        }
                    } else {
                        $r = new static::$responseClass(0, PhpXmlRpc::$xmlrpcerr['server_decompress_fail'],
                            PhpXmlRpc::$xmlrpcstr['server_decompress_fail'], '', array('raw_data' => $rawData)
                        );

                        return $r;
                    }
                } else {
                    $r = new static::$responseClass(0, PhpXmlRpc::$xmlrpcerr['server_cannot_decompress'],
                        PhpXmlRpc::$xmlrpcstr['server_cannot_decompress'], '', array('raw_data' => $rawData)
                    );

                    return $r;
                }
            }
        }

        
        if ($this->response_charset_encoding == 'auto') {
            $respEncoding = '';
            if (isset($_SERVER['HTTP_ACCEPT_CHARSET'])) {
                
                
                $http = new Http();
                $clientAcceptedCharsets = $http->parseAcceptHeader($_SERVER['HTTP_ACCEPT_CHARSET']);
                $knownCharsets = $this->getCharsetEncoder()->knownCharsets();
                foreach ($clientAcceptedCharsets as $accepted) {
                    foreach ($knownCharsets as $charset) {
                        if (strtoupper($accepted) == strtoupper($charset)) {
                            $respEncoding = $charset;
                            break 2;
                        }
                    }
                }
            }
        } else {
            $respEncoding = $this->response_charset_encoding;
        }

        if (isset($_SERVER['HTTP_ACCEPT_ENCODING'])) {
            $respCompression = $_SERVER['HTTP_ACCEPT_ENCODING'];
        } else {
            $respCompression = '';
        }

        
        
        $parser = $this->getParser();
        $reqEncoding = $parser->guessEncoding(isset($_SERVER['CONTENT_TYPE']) ? $_SERVER['CONTENT_TYPE'] : '',
            $data);

        return null;
    }

    











    public function parseRequest($data, $reqEncoding = '')
    {
        

        
        if ($reqEncoding != '') {
            
            
            
            
            
            
            
            if (!in_array($reqEncoding, array('UTF-8', 'US-ASCII')) && !XMLParser::hasEncoding($data)) {
                if (function_exists('mb_convert_encoding')) {
                    $data = mb_convert_encoding($data, 'UTF-8', $reqEncoding);
                } else {
                    if ($reqEncoding == 'ISO-8859-1') {
                        $data = utf8_encode($data);
                    } else {
                        $this->getLogger()->error('XML-RPC: ' . __METHOD__ . ': unsupported charset encoding of received request: ' . $reqEncoding);
                    }
                }
            }
        }
        
        
        if (in_array(PhpXmlRpc::$xmlrpc_internalencoding, array('UTF-8', 'ISO-8859-1', 'US-ASCII'))) {
            $options = array(XML_OPTION_TARGET_ENCODING => PhpXmlRpc::$xmlrpc_internalencoding);
        } else {
            $options = array(XML_OPTION_TARGET_ENCODING => 'UTF-8', 'target_charset' => PhpXmlRpc::$xmlrpc_internalencoding);
        }
        
        $options['methodname_callback'] = array($this, 'methodNameCallback');

        $xmlRpcParser = $this->getParser();
        try {
            
            
            
            $_xh = $xmlRpcParser->parse($data, $this->functions_parameters_type, XMLParser::ACCEPT_REQUEST, $options);
            
            if (!is_array($_xh)) {
                $_xh = $xmlRpcParser->_xh;
            }
        } catch (NoSuchMethodException $e) {
            return new static::$responseClass(0, $e->getCode(), $e->getMessage());
        }

        if ($_xh['isf'] == 3) {
            
            
            preg_match('/^XML error ([0-9]+)/', $_xh['isf_reason'], $matches);
            return new static::$responseClass(
                0,
                (PhpXmlRpc::isUsingInteropFaults() ? PhpXmlRpc::$xmlrpcerr['invalid_xml'] : PhpXmlRpc::$xmlrpcerrxml + (int)$matches[1]),
                $_xh['isf_reason']);
        } elseif ($_xh['isf']) {
            
            
            return new static::$responseClass(
                0,
                PhpXmlRpc::$xmlrpcerr['invalid_request'],
                PhpXmlRpc::$xmlrpcstr['invalid_request'] . ' ' . $_xh['isf_reason']);
        } else {
            
            
            
            if ($this->functions_parameters_type != 'xmlrpcvals' ||
                (isset($this->dmap[$_xh['method']]['parameters_type']) &&
                    ($this->dmap[$_xh['method']]['parameters_type'] != 'xmlrpcvals')
                )
            ) {
                if ($this->debug > 1) {
                    $this->debugMsg("\n+++PARSED+++\n" . var_export($_xh['params'], true) . "\n+++END+++");
                }

                return $this->execute($_xh['method'], $_xh['params'], $_xh['pt']);
            } else {
                
                $req = new Request($_xh['method']);
                
                for ($i = 0; $i < count($_xh['params']); $i++) {
                    $req->addParam($_xh['params'][$i]);
                }

                if ($this->debug > 1) {
                    $this->debugMsg("\n+++PARSED+++\n" . var_export($req, true) . "\n+++END+++");
                }

                return $this->execute($req);
            }
        }
    }

    









    protected function execute($req, $params = null, $paramTypes = null)
    {
        static::$_xmlrpcs_occurred_errors = '';
        static::$_xmlrpc_debuginfo = '';

        if (is_object($req)) {
            $methodName = $req->method();
        } else {
            $methodName = $req;
        }

        $sysCall = $this->isSyscall($methodName);
        $dmap = $sysCall ? $this->getSystemDispatchMap() : $this->dmap;

        if (!isset($dmap[$methodName]['function'])) {
            
            return new static::$responseClass(0, PhpXmlRpc::$xmlrpcerr['unknown_method'], PhpXmlRpc::$xmlrpcstr['unknown_method']);
        }

        
        if (isset($dmap[$methodName]['signature'])) {
            $sig = $dmap[$methodName]['signature'];
            if (is_object($req)) {
                list($ok, $errStr) = $this->verifySignature($req, $sig);
            } else {
                list($ok, $errStr) = $this->verifySignature($paramTypes, $sig);
            }
            if (!$ok) {
                
                return new static::$responseClass(
                    0,
                    PhpXmlRpc::$xmlrpcerr['incorrect_params'],
                    PhpXmlRpc::$xmlrpcstr['incorrect_params'] . ": {$errStr}"
                );
            }
        }

        $func = $dmap[$methodName]['function'];

        
        if (is_string($func) && strpos($func, '::')) {
            $func = explode('::', $func);
        }

        
        if (is_array($func)) {
            if (is_object($func[0])) {
                $funcName = get_class($func[0]) . '->' . $func[1];
            } else {
                $funcName = implode('::', $func);
            }
        } else if ($func instanceof \Closure) {
            $funcName = 'Closure';
        } else {
            $funcName = $func;
        }

        
        if (!is_callable($func)) {
            $this->getLogger()->error("XML-RPC: " . __METHOD__ . ": function '$funcName' registered as method handler is not callable");
            return new static::$responseClass(
                0,
                PhpXmlRpc::$xmlrpcerr['server_error'],
                PhpXmlRpc::$xmlrpcstr['server_error'] . ": no function matches method"
            );
        }

        if (isset($dmap[$methodName]['exception_handling'])) {
            $exception_handling = (int)$dmap[$methodName]['exception_handling'];
        } else {
            $exception_handling = $this->exception_handling;
        }

        
        
        self::$_xmlrpcs_prev_ehandler = set_error_handler(array('\PhpXmlRpc\Server', '_xmlrpcs_errorHandler'));

        

        try {
            
            if (is_object($req)) {
                
                if ($sysCall) {
                    $r = call_user_func($func, $this, $req);
                } else {
                    $r = call_user_func($func, $req);
                }
                if (!is_a($r, 'PhpXmlRpc\Response')) {
                    $this->getLogger()->error("XML-RPC: " . __METHOD__ . ": function '$funcName' registered as method handler does not return an xmlrpc response object but a " . gettype($r));
                    if (is_a($r, 'PhpXmlRpc\Value')) {
                        $r = new static::$responseClass($r);
                    } else {
                        $r = new static::$responseClass(
                            0,
                            PhpXmlRpc::$xmlrpcerr['server_error'],
                            PhpXmlRpc::$xmlrpcstr['server_error'] . ": function does not return xmlrpc response object"
                        );
                    }
                }
            } else {
                
                if ($sysCall) {
                    array_unshift($params, $this);
                    $r = call_user_func_array($func, $params);
                } else {
                    
                    if ($this->functions_parameters_type == 'epivals') {
                        $r = call_user_func_array($func, array($methodName, $params, $this->user_data));
                        
                        if (is_array($r) && array_key_exists('faultCode', $r) && array_key_exists('faultString', $r)) {
                            $r = new static::$responseClass(0, (int)$r['faultCode'], (string)$r['faultString']);
                        } else {
                            
                            
                            $encoder = new Encoder();
                            $r = new static::$responseClass($encoder->encode($r, array('extension_api')));
                        }
                    } else {
                        $r = call_user_func_array($func, $params);
                    }
                }
                
                if (!is_a($r, '\PhpXmlRpc\Response')) {
                    
                    
                    $encoder = new Encoder();
                    $r = new static::$responseClass($encoder->encode($r, $this->phpvals_encoding_options));
                }
            }
        
        } catch (\Exception $e) {
            
            
            switch ($exception_handling) {
                case 2:
                    if (self::$_xmlrpcs_prev_ehandler) {
                        set_error_handler(self::$_xmlrpcs_prev_ehandler);
                        self::$_xmlrpcs_prev_ehandler = null;
                    } else {
                        restore_error_handler();
                    }
                    throw $e;
                case 1:
                    $errCode = $e->getCode();
                    if ($errCode == 0) {
                        $errCode = PhpXmlRpc::$xmlrpcerr['server_error'];
                    }
                    $r = new static::$responseClass(0, $errCode, $e->getMessage());
                    break;
                default:
                    $r = new static::$responseClass(0, PhpXmlRpc::$xmlrpcerr['server_error'], PhpXmlRpc::$xmlrpcstr['server_error']);
            }
        } catch (\Error $e) {
            
            
            switch ($exception_handling) {
                case 2:
                    if (self::$_xmlrpcs_prev_ehandler) {
                        set_error_handler(self::$_xmlrpcs_prev_ehandler);
                        self::$_xmlrpcs_prev_ehandler = null;
                    } else {
                        restore_error_handler();
                    }
                    throw $e;
                case 1:
                    $errCode = $e->getCode();
                    if ($errCode == 0) {
                        $errCode = PhpXmlRpc::$xmlrpcerr['server_error'];
                    }
                    $r = new static::$responseClass(0, $errCode, $e->getMessage());
                    break;
                default:
                    $r = new static::$responseClass(0, PhpXmlRpc::$xmlrpcerr['server_error'], PhpXmlRpc::$xmlrpcstr['server_error']);
            }
        }

        
        
        if (self::$_xmlrpcs_prev_ehandler) {
            set_error_handler(self::$_xmlrpcs_prev_ehandler);
            self::$_xmlrpcs_prev_ehandler = null;
        } else {
            restore_error_handler();
        }

        return $r;
    }

    




    protected function generatePayload($resp, $respCharset)
    {
        $header = $resp->xml_header($respCharset);
        if ($this->debug > 0) {
            $header .= $this->serializeDebug($respCharset);
        }

        
        
        
        $payload = $resp->getPayload();
        if (empty($payload)) {
            $payload = $resp->serialize($respCharset);
        }

        return $header . $payload;
    }

    





    protected function printPayload($payload, $respContentType, $respEncoding)
    {
        
        
        if (!headers_sent()) {
            header('Content-Type: ' . $respContentType);
            
            header("Vary: Accept-Charset");

            
            
            $phpNoSelfCompress = !ini_get('zlib.output_compression') && (ini_get('output_handler') != 'ob_gzhandler');
            if ($this->compress_response && $respEncoding != '' && $phpNoSelfCompress) {
                if (strpos($respEncoding, 'gzip') !== false && function_exists('gzencode')) {
                    $payload = gzencode($payload);
                    header("Content-Encoding: gzip");
                    header("Vary: Accept-Encoding");
                } elseif (strpos($respEncoding, 'deflate') !== false && function_exists('gzcompress')) {
                    $payload = gzcompress($payload);
                    header("Content-Encoding: deflate");
                    header("Vary: Accept-Encoding");
                }
            }

            
            
            
            if ($phpNoSelfCompress) {
                header('Content-Length: ' . (int)strlen($payload));
            }
        } else {
            
            
            $this->getLogger()->error('XML-RPC: ' . __METHOD__ . ': http headers already sent before response is fully generated. Check for php warning or error messages');
        }

        print $payload;
    }

    















    public function methodNameCallback($methodName, $xmlParser, $parser = null)
    {
        $sysCall = $this->isSyscall($methodName);
        $dmap = $sysCall ? $this->getSystemDispatchMap() : $this->dmap;

        if (!isset($dmap[$methodName]['function'])) {
            
            throw new NoSuchMethodException(PhpXmlRpc::$xmlrpcstr['unknown_method'], PhpXmlRpc::$xmlrpcerr['unknown_method']);
        }

        
        if (isset($dmap[$methodName]['parameters_type']) &&
            $dmap[$methodName]['parameters_type'] != $this->functions_parameters_type) {
            
            switch ($dmap[$methodName]['parameters_type']) {
                case XMLParser::RETURN_PHP:
                    xml_set_element_handler($parser, array($xmlParser, 'xmlrpc_se'), array($xmlParser, 'xmlrpc_ee_fast'));
                    break;
                case XMLParser::RETURN_EPIVALS:
                    xml_set_element_handler($parser, array($xmlParser, 'xmlrpc_se'), array($xmlParser, 'xmlrpc_ee_epi'));
                    break;
                
                case XMLParser::RETURN_XMLRPCVALS:
                default:
                    xml_set_element_handler($parser, array($xmlParser, 'xmlrpc_se'), array($xmlParser, 'xmlrpc_ee'));
            }
        }
    }

    





    protected function debugMsg($string)
    {
        $this->debug_info .= $string . "\n";
    }

    



    protected function isSyscall($methName)
    {
        return (strpos($methName, "system.") === 0);
    }

    



    public function setDispatchMap($dmap)
    {
        $this->dmap = $dmap;
        return $this;
    }

    


    public function getDispatchMap()
    {
        return $this->dmap;
    }

    


    public function getSystemDispatchMap()
    {
        if (!$this->allow_system_funcs) {
            return array();
        }

        return array(
            'system.listMethods' => array(
                'function' => 'PhpXmlRpc\Server::_xmlrpcs_listMethods',
                
                
                'signature' => array(array(Value::$xmlrpcArray)),
                'docstring' => 'This method lists all the methods that the XML-RPC server knows how to dispatch',
                'signature_docs' => array(array('list of method names')),
            ),
            'system.methodHelp' => array(
                'function' => 'PhpXmlRpc\Server::_xmlrpcs_methodHelp',
                'signature' => array(array(Value::$xmlrpcString, Value::$xmlrpcString)),
                'docstring' => 'Returns help text if defined for the method passed, otherwise returns an empty string',
                'signature_docs' => array(array('method description', 'name of the method to be described')),
            ),
            'system.methodSignature' => array(
                'function' => 'PhpXmlRpc\Server::_xmlrpcs_methodSignature',
                'signature' => array(array(Value::$xmlrpcArray, Value::$xmlrpcString)),
                'docstring' => 'Returns an array of known signatures (an array of arrays) for the method name passed. If no signatures are known, returns a none-array (test for type != array to detect missing signature)',
                'signature_docs' => array(array('list of known signatures, each sig being an array of xmlrpc type names', 'name of method to be described')),
            ),
            'system.multicall' => array(
                'function' => 'PhpXmlRpc\Server::_xmlrpcs_multicall',
                'signature' => array(array(Value::$xmlrpcArray, Value::$xmlrpcArray)),
                'docstring' => 'Boxcar multiple RPC calls in one request. See http://www.xmlrpc.com/discuss/msgReader$1208 for details',
                'signature_docs' => array(array('list of response structs, where each struct has the usual members', 'list of calls, with each call being represented as a struct, with members "methodname" and "params"')),
            ),
            'system.getCapabilities' => array(
                'function' => 'PhpXmlRpc\Server::_xmlrpcs_getCapabilities',
                'signature' => array(array(Value::$xmlrpcStruct)),
                'docstring' => 'This method lists all the capabilities that the XML-RPC server has: the (more or less standard) extensions to the xmlrpc spec that it adheres to',
                'signature_docs' => array(array('list of capabilities, described as structs with a version number and url for the spec')),
            ),
        );
    }

    


    public function getCapabilities()
    {
        $outAr = array(
            
            'xmlrpc' => array(
                
                'specUrl' => 'http://www.xmlrpc.com/spec',
                'specVersion' => 1
            ),
            
            'system.multicall' => array(
                
                'specUrl' => 'http://www.xmlrpc.com/discuss/msgReader$1208',
                'specVersion' => 1
            ),
            
            
            
            'introspection' => array(
                'specUrl' => 'http://phpxmlrpc.sourceforge.net/doc-2/ch10.html',
                'specVersion' => 2,
            ),
        );

        
        if (PhpXmlRpc::$xmlrpc_null_extension) {
            $outAr['nil'] = array(
                
                'specUrl' => 'http://www.ontosys.com/xml-rpc/extensions.php',
                'specVersion' => 1
            );
        }

        
        if (PhpXmlRpc::$xmlrpcerr['unknown_method'] === Interop::$xmlrpcerr['unknown_method']) {
            $outAr['faults_interop'] = array(
                
                'specUrl' => 'http://xmlrpc-epi.sourceforge.net/specs/rfc.fault_codes.php',
                'specVersion' => 20010516
            );
        }

        return $outAr;
    }

    






    public static function _xmlrpcs_getCapabilities($server, $req = null)
    {
        $encoder = new Encoder();
        return new static::$responseClass($encoder->encode($server->getCapabilities()));
    }

    






    public static function _xmlrpcs_listMethods($server, $req = null)
    {
        $outAr = array();
        foreach ($server->dmap as $key => $val) {
            $outAr[] = new Value($key, 'string');
        }
        foreach ($server->getSystemDispatchMap() as $key => $val) {
            $outAr[] = new Value($key, 'string');
        }

        return new static::$responseClass(new Value($outAr, 'array'));
    }

    






    public static function _xmlrpcs_methodSignature($server, $req)
    {
        
        if (is_object($req)) {
            $methName = $req->getParam(0);
            $methName = $methName->scalarVal();
        } else {
            $methName = $req;
        }
        if ($server->isSyscall($methName)) {
            $dmap = $server->getSystemDispatchMap();
        } else {
            $dmap = $server->dmap;
        }
        if (isset($dmap[$methName])) {
            if (isset($dmap[$methName]['signature'])) {
                $sigs = array();
                foreach ($dmap[$methName]['signature'] as $inSig) {
                    $curSig = array();
                    foreach ($inSig as $sig) {
                        $curSig[] = new Value($sig, 'string');
                    }
                    $sigs[] = new Value($curSig, 'array');
                }
                $r = new static::$responseClass(new Value($sigs, 'array'));
            } else {
                
                
                $r = new static::$responseClass(new Value('undef', 'string'));
            }
        } else {
            $r = new static::$responseClass(0, PhpXmlRpc::$xmlrpcerr['introspect_unknown'], PhpXmlRpc::$xmlrpcstr['introspect_unknown']);
        }

        return $r;
    }

    






    public static function _xmlrpcs_methodHelp($server, $req)
    {
        
        if (is_object($req)) {
            $methName = $req->getParam(0);
            $methName = $methName->scalarVal();
        } else {
            $methName = $req;
        }
        if ($server->isSyscall($methName)) {
            $dmap = $server->getSystemDispatchMap();
        } else {
            $dmap = $server->dmap;
        }
        if (isset($dmap[$methName])) {
            if (isset($dmap[$methName]['docstring'])) {
                $r = new static::$responseClass(new Value($dmap[$methName]['docstring'], 'string'));
            } else {
                $r = new static::$responseClass(new Value('', 'string'));
            }
        } else {
            $r = new static::$responseClass(0, PhpXmlRpc::$xmlrpcerr['introspect_unknown'], PhpXmlRpc::$xmlrpcstr['introspect_unknown']);
        }

        return $r;
    }

    





    public static function _xmlrpcs_multicall_error($err)
    {
        if (is_string($err)) {
            $str = PhpXmlRpc::$xmlrpcstr["multicall_{$err}"];
            $code = PhpXmlRpc::$xmlrpcerr["multicall_{$err}"];
        } else {
            $code = $err->faultCode();
            $str = $err->faultString();
        }
        $struct = array();
        $struct['faultCode'] = new Value($code, 'int');
        $struct['faultString'] = new Value($str, 'string');

        return new Value($struct, 'struct');
    }

    






    public static function _xmlrpcs_multicall_do_call($server, $call)
    {
        if ($call->kindOf() != 'struct') {
            return static::_xmlrpcs_multicall_error('notstruct');
        }
        $methName = @$call['methodName'];
        if (!$methName) {
            return static::_xmlrpcs_multicall_error('nomethod');
        }
        if ($methName->kindOf() != 'scalar' || $methName->scalarTyp() != 'string') {
            return static::_xmlrpcs_multicall_error('notstring');
        }
        if ($methName->scalarVal() == 'system.multicall') {
            return static::_xmlrpcs_multicall_error('recursion');
        }

        $params = @$call['params'];
        if (!$params) {
            return static::_xmlrpcs_multicall_error('noparams');
        }
        if ($params->kindOf() != 'array') {
            return static::_xmlrpcs_multicall_error('notarray');
        }

        $req = new Request($methName->scalarVal());
        foreach ($params as $i => $param) {
            
            if (!$req->addParam($param)) {
                $i++; 
                return static::_xmlrpcs_multicall_error(new static::$responseClass(0,
                    PhpXmlRpc::$xmlrpcerr['incorrect_params'],
                    PhpXmlRpc::$xmlrpcstr['incorrect_params'] . ": probable xml error in param " . $i));
            }
        }

        $result = $server->execute($req);

        if ($result->faultCode() != 0) {
            return static::_xmlrpcs_multicall_error($result); 
        }

        return new Value(array($result->value()), 'array');
    }

    






    public static function _xmlrpcs_multicall_do_call_phpvals($server, $call)
    {
        if (!is_array($call)) {
            return static::_xmlrpcs_multicall_error('notstruct');
        }
        if (!array_key_exists('methodName', $call)) {
            return static::_xmlrpcs_multicall_error('nomethod');
        }
        if (!is_string($call['methodName'])) {
            return static::_xmlrpcs_multicall_error('notstring');
        }
        if ($call['methodName'] == 'system.multicall') {
            return static::_xmlrpcs_multicall_error('recursion');
        }
        if (!array_key_exists('params', $call)) {
            return static::_xmlrpcs_multicall_error('noparams');
        }
        if (!is_array($call['params'])) {
            return static::_xmlrpcs_multicall_error('notarray');
        }

        
        
        $pt = array();
        $wrapper = new Wrapper();
        foreach ($call['params'] as $val) {
            
            if ($val instanceof \stdClass && isset($val->xmlrpc_type)) {
                $pt[] = $val->xmlrpc_type == 'datetime' ? Value::$xmlrpcDateTime : $val->xmlrpc_type;
            } else {
                $pt[] = $wrapper->php2XmlrpcType(gettype($val));
            }
        }

        $result = $server->execute($call['methodName'], $call['params'], $pt);

        if ($result->faultCode() != 0) {
            return static::_xmlrpcs_multicall_error($result); 
        }

        return new Value(array($result->value()), 'array');
    }

    






    public static function _xmlrpcs_multicall($server, $req)
    {
        $result = array();
        
        if (is_object($req)) {
            $calls = $req->getParam(0);
            foreach ($calls as $call) {
                $result[] = static::_xmlrpcs_multicall_do_call($server, $call);
            }
        } else {
            $numCalls = count($req);
            for ($i = 0; $i < $numCalls; $i++) {
                $result[$i] = static::_xmlrpcs_multicall_do_call_phpvals($server, $req[$i]);
            }
        }

        return new static::$responseClass(new Value($result, 'array'));
    }

    









    public static function _xmlrpcs_errorHandler($errCode, $errString, $filename = null, $lineNo = null, $context = null)
    {
        
        if (error_reporting() == 0) {
            return;
        }

        
        
        
        if (PHP_VERSION_ID >= 70400) {
            static::error_occurred($errString);
        } elseif ($errCode != E_STRICT) {
            static::error_occurred($errString);
        }

        
        if (self::$_xmlrpcs_prev_ehandler == '') {
            
            
            if (ini_get('log_errors') && (intval(ini_get('error_reporting')) & $errCode)) {
                
                if (self::$logger === null) {
                    self::$logger = Logger::instance();
                }
                self::$logger->error($errString);
            }
        } else {
            
            if (self::$_xmlrpcs_prev_ehandler != array('\PhpXmlRpc\Server', '_xmlrpcs_errorHandler')) {
                if (is_array(self::$_xmlrpcs_prev_ehandler)) {
                    
                    call_user_func_array(self::$_xmlrpcs_prev_ehandler, array($errCode, $errString, $filename, $lineNo, $context));
                } else {
                    $method = self::$_xmlrpcs_prev_ehandler;
                    $method($errCode, $errString, $filename, $lineNo, $context);
                }
            }
        }
    }

    

    





    protected function xml_header($charsetEncoding = '')
    {
        $this->logDeprecation('Method ' . __METHOD__ . ' is deprecated');

        if ($charsetEncoding != '') {
            return "<?xml version=\"1.0\" encoding=\"$charsetEncoding\"?" . ">\n";
        } else {
            return "<?xml version=\"1.0\"?" . ">\n";
        }
    }

    
    public function &__get($name)
    {
        switch ($name) {
            case self::OPT_ACCEPTED_COMPRESSION :
            case self::OPT_ALLOW_SYSTEM_FUNCS:
            case self::OPT_COMPRESS_RESPONSE:
            case self::OPT_DEBUG:
            case self::OPT_EXCEPTION_HANDLING:
            case self::OPT_FUNCTIONS_PARAMETERS_TYPE:
            case self::OPT_PHPVALS_ENCODING_OPTIONS:
            case self::OPT_RESPONSE_CHARSET_ENCODING:
                $this->logDeprecation('Getting property Request::' . $name . ' is deprecated');
                return $this->$name;
            case 'accepted_charset_encodings':
                
                $canAccess = false;
                $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
                if (isset($trace[1]) && isset($trace[1]['class'])) {
                    if (is_subclass_of($trace[1]['class'], 'PhpXmlRpc\Server')) {
                        $canAccess = true;
                    }
                }
                if ($canAccess) {
                    $this->logDeprecation('Getting property Request::' . $name . ' is deprecated');
                    return $this->accepted_compression;
                } else {
                    trigger_error("Cannot access protected property Server::accepted_charset_encodings in " . __FILE__, E_USER_ERROR);
                }
                break;
            default:
                
                $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);
                trigger_error('Undefined property via __get(): ' . $name . ' in ' . $trace[0]['file'] . ' on line ' . $trace[0]['line'], E_USER_WARNING);
                $result = null;
                return $result;
        }
    }

    public function __set($name, $value)
    {
        switch ($name) {
            case self::OPT_ACCEPTED_COMPRESSION :
            case self::OPT_ALLOW_SYSTEM_FUNCS:
            case self::OPT_COMPRESS_RESPONSE:
            case self::OPT_DEBUG:
            case self::OPT_EXCEPTION_HANDLING:
            case self::OPT_FUNCTIONS_PARAMETERS_TYPE:
            case self::OPT_PHPVALS_ENCODING_OPTIONS:
            case self::OPT_RESPONSE_CHARSET_ENCODING:
                $this->logDeprecation('Setting property Request::' . $name . ' is deprecated');
                $this->$name = $value;
                break;
            case 'accepted_charset_encodings':
                
                $canAccess = false;
                $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
                if (isset($trace[1]) && isset($trace[1]['class'])) {
                    if (is_subclass_of($trace[1]['class'], 'PhpXmlRpc\Server')) {
                        $canAccess = true;
                    }
                }
                if ($canAccess) {
                    $this->logDeprecation('Setting property Request::' . $name . ' is deprecated');
                    $this->accepted_compression = $value;
                } else {
                    trigger_error("Cannot access protected property Server::accepted_charset_encodings in " . __FILE__, E_USER_ERROR);
                }
                break;
            default:
                
                $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);
                trigger_error('Undefined property via __set(): ' . $name . ' in ' . $trace[0]['file'] . ' on line ' . $trace[0]['line'], E_USER_WARNING);
        }
    }

    public function __isset($name)
    {
        switch ($name) {
            case self::OPT_ACCEPTED_COMPRESSION :
            case self::OPT_ALLOW_SYSTEM_FUNCS:
            case self::OPT_COMPRESS_RESPONSE:
            case self::OPT_DEBUG:
            case self::OPT_EXCEPTION_HANDLING:
            case self::OPT_FUNCTIONS_PARAMETERS_TYPE:
            case self::OPT_PHPVALS_ENCODING_OPTIONS:
            case self::OPT_RESPONSE_CHARSET_ENCODING:
                $this->logDeprecation('Checking property Request::' . $name . ' is deprecated');
                return isset($this->$name);
            case 'accepted_charset_encodings':
                
                $canAccess = false;
                $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
                if (isset($trace[1]) && isset($trace[1]['class'])) {
                    if (is_subclass_of($trace[1]['class'], 'PhpXmlRpc\Server')) {
                        $canAccess = true;
                    }
                }
                if ($canAccess) {
                    $this->logDeprecation('Checking property Request::' . $name . ' is deprecated');
                    return isset($this->accepted_compression);
                }
                
            default:
                return false;
        }
    }

    public function __unset($name)
    {
        switch ($name) {
            case self::OPT_ACCEPTED_COMPRESSION :
            case self::OPT_ALLOW_SYSTEM_FUNCS:
            case self::OPT_COMPRESS_RESPONSE:
            case self::OPT_DEBUG:
            case self::OPT_EXCEPTION_HANDLING:
            case self::OPT_FUNCTIONS_PARAMETERS_TYPE:
            case self::OPT_PHPVALS_ENCODING_OPTIONS:
            case self::OPT_RESPONSE_CHARSET_ENCODING:
                $this->logDeprecation('Unsetting property Request::' . $name . ' is deprecated');
                unset($this->$name);
                break;
            case 'accepted_charset_encodings':
                
                $canAccess = false;
                $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
                if (isset($trace[1]) && isset($trace[1]['class'])) {
                    if (is_subclass_of($trace[1]['class'], 'PhpXmlRpc\Server')) {
                        $canAccess = true;
                    }
                }
                if ($canAccess) {
                    $this->logDeprecation('Unsetting property Request::' . $name . ' is deprecated');
                    unset($this->accepted_compression);
                } else {
                    trigger_error("Cannot access protected property Server::accepted_charset_encodings in " . __FILE__, E_USER_ERROR);
                }
                break;
            default:
                
                $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);
                trigger_error('Undefined property via __unset(): ' . $name . ' in ' . $trace[0]['file'] . ' on line ' . $trace[0]['line'], E_USER_WARNING);
        }
    }
}
