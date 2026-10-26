<?php

namespace PhpXmlRpc;

use PhpXmlRpc\Exception\HttpException;
use PhpXmlRpc\Helper\Http;
use PhpXmlRpc\Helper\XMLParser;
use PhpXmlRpc\Traits\CharsetEncoderAware;
use PhpXmlRpc\Traits\DeprecationLogger;
use PhpXmlRpc\Traits\ParserAware;
use PhpXmlRpc\Traits\PayloadBearer;













class Request
{
    use CharsetEncoderAware;
    use DeprecationLogger;
    use ParserAware;
    use PayloadBearer;

    
    protected $methodname;
    
    protected $params = array();
    
    protected $debug = 0;

    



    private $httpResponse = array();

    



    public function __construct($methodName, $params = array())
    {
        $this->methodname = $methodName;
        foreach ($params as $param) {
            $this->addParam($param);
        }
    }

    





    public function method($methodName = '')
    {
        if ($methodName != '') {
            $this->methodname = $methodName;
        }

        return $this->methodname;
    }

    






    public function addParam($param)
    {
        
        if (is_object($param) && is_a($param, 'PhpXmlRpc\Value')) {
            $this->params[] = $param;

            return true;
        } else {
            $this->getLogger()->error('XML-RPC: ' . __METHOD__ . ': value passed in must be a PhpXmlRpc\Value');
            return false;
        }
    }

    





    public function getParam($i)
    {
        return $this->params[$i];
    }

    




    public function getNumParams()
    {
        return count($this->params);
    }

    





    public function serialize($charsetEncoding = '')
    {
        $this->createPayload($charsetEncoding);

        return $this->payload;
    }

    





    public function createPayload($charsetEncoding = '')
    {
        $this->logDeprecationUnlessCalledBy('serialize');

        if ($charsetEncoding != '') {
            $this->content_type = 'text/xml; charset=' . $charsetEncoding;
        } else {
            $this->content_type = 'text/xml';
        }

        $result = $this->xml_header($charsetEncoding);
        $result .= '<methodName>' . $this->getCharsetEncoder()->encodeEntities(
                $this->methodname, PhpXmlRpc::$xmlrpc_internalencoding, $charsetEncoding) . "</methodName>\n";
        $result .= "<params>\n";
        foreach ($this->params as $p) {
            $result .= "<param>\n" . $p->serialize($charsetEncoding) .
                "</param>\n";
        }
        $result .= "</params>\n";
        $result .= $this->xml_footer();

        $this->payload = $result;
    }

    





    public function xml_header($charsetEncoding = '')
    {
        $this->logDeprecationUnlessCalledBy('createPayload');

        if ($charsetEncoding != '') {
            return "<?xml version=\"1.0\" encoding=\"$charsetEncoding\" ?" . ">\n<methodCall>\n";
        } else {
            return "<?xml version=\"1.0\"?" . ">\n<methodCall>\n";
        }
    }

    




    public function xml_footer()
    {
        $this->logDeprecationUnlessCalledBy('createPayload');

        return '</methodCall>';
    }

    















    public function parseResponseFile($fp, $headersProcessed = false, $returnType = 'xmlrpcvals')
    {
        $ipd = '';
        
        while ($data = fread($fp, 32768)) {
            $ipd .= $data;
        }
        return $this->parseResponse($ipd, $headersProcessed, $returnType);
    }

    

















    public function parseResponse($data = '', $headersProcessed = false, $returnType = XMLParser::RETURN_XMLRPCVALS)
    {
        if ($this->debug > 0) {
            $this->getLogger()->debug("---GOT---\n$data\n---END---");
        }

        $this->httpResponse = array('raw_data' => $data, 'headers' => array(), 'cookies' => array());

        if ($data == '') {
            $this->getLogger()->error('XML-RPC: ' . __METHOD__ . ': no response received from server.');
            return new Response(0, PhpXmlRpc::$xmlrpcerr['no_data'], PhpXmlRpc::$xmlrpcstr['no_data']);
        }

        
        if (substr($data, 0, 4) == 'HTTP') {
            $httpParser = new Http();
            try {
                $httpResponse = $httpParser->parseResponseHeaders($data, $headersProcessed, $this->debug > 0);
            } catch (HttpException $e) {
                
                
                return new Response(0, $e->getCode(), $e->getMessage(), '', array('raw_data' => $data, 'status_code' => $e->statusCode()));
            } catch(\Exception $e) {
                return new Response(0, $e->getCode(), $e->getMessage(), '', array('raw_data' => $data));
            }
        } else {
            $httpResponse = $this->httpResponse;
        }

        
        $data = trim($data);

        

        
        
        $pos = strrpos($data, '</methodResponse>');
        if ($pos !== false) {
            $data = substr($data, 0, $pos + 17);
        }

        
        $respEncoding = XMLParser::guessEncoding(
            isset($httpResponse['headers']['content-type']) ? $httpResponse['headers']['content-type'] : '',
            $data
        );

        if ($this->debug >= 0) {
            $this->httpResponse = $httpResponse;
        } else {
            $httpResponse = null;
        }

        if ($this->debug > 0) {
            $start = strpos($data, '<!-- SERVER DEBUG INFO (BASE64 ENCODED):');
            if ($start) {
                $start += strlen('<!-- SERVER DEBUG INFO (BASE64 ENCODED):');
                
                $end = strpos($data, '-->', $start);
                $comments = substr($data, $start, $end - $start);
                $this->getLogger()->debug("---SERVER DEBUG INFO (DECODED)---\n\t" .
                    str_replace("\n", "\n\t", base64_decode($comments)) . "\n---END---", array('encoding' => $respEncoding));
            }
        }

        
        if ($returnType == 'xml') {
            return new Response($data, 0, '', 'xml', $httpResponse);
        }

        
        if ($respEncoding != '') {
            
            
            
            
            if (!in_array($respEncoding, array('UTF-8', 'US-ASCII')) && !XMLParser::hasEncoding($data)) {
                if (function_exists('mb_convert_encoding')) {
                    $data = mb_convert_encoding($data, 'UTF-8', $respEncoding);
                } else {
                    if ($respEncoding == 'ISO-8859-1') {
                        $data = utf8_encode($data);
                    } else {
                        $this->getLogger()->error('XML-RPC: ' . __METHOD__ . ': unsupported charset encoding of received response: ' . $respEncoding);
                    }
                }
            }
        }
        
        
        if (in_array(PhpXmlRpc::$xmlrpc_internalencoding, array('UTF-8', 'ISO-8859-1', 'US-ASCII'))) {
            $options = array(XML_OPTION_TARGET_ENCODING => PhpXmlRpc::$xmlrpc_internalencoding);
        } else {
            $options = array(XML_OPTION_TARGET_ENCODING => 'UTF-8', 'target_charset' => PhpXmlRpc::$xmlrpc_internalencoding);
        }

        $xmlRpcParser = $this->getParser();
        $_xh = $xmlRpcParser->parse($data, $returnType, XMLParser::ACCEPT_RESPONSE, $options);
        
        if (!is_array($_xh)) {
            $_xh = $xmlRpcParser->_xh;
        }

        
        if ($_xh['isf'] == 3) {

            

            
            
            

            $r = new Response(0, PhpXmlRpc::$xmlrpcerr['invalid_xml'],
                PhpXmlRpc::$xmlrpcstr['invalid_xml'] . ' ' . $_xh['isf_reason'], '', $httpResponse);

            if ($this->debug > 0) {
                $this->getLogger()->debug($_xh['isf_reason']);
            }
        }
        
        elseif ($_xh['isf'] == 2) {
            $r = new Response(0, PhpXmlRpc::$xmlrpcerr['xml_not_compliant'],
                PhpXmlRpc::$xmlrpcstr['xml_not_compliant'] . ' ' . $_xh['isf_reason'], '', $httpResponse);

            
            
            
            
        }
        
        
        
        elseif ($_xh['isf'] > 3 || ($returnType == XMLParser::RETURN_XMLRPCVALS && !$_xh['isf'] && !is_object($_xh['value']))) {
            
            $r = new Response(0, PhpXmlRpc::$xmlrpcerr['xml_parsing_error'], PhpXmlRpc::$xmlrpcstr['xml_parsing_error'],
                '', $httpResponse
            );

            
        } else {
            if ($this->debug > 1) {
                $this->getLogger()->debug(
                    "---PARSED---\n".var_export($_xh['value'], true)."\n---END---"
                );
            }

            $v = $_xh['value'];

            if ($_xh['isf']) {
                
                
                if ($returnType == XMLParser::RETURN_XMLRPCVALS) {
                    $errNo_v = $v['faultCode'];
                    $errStr_v = $v['faultString'];
                    $errNo = $errNo_v->scalarVal();
                    $errStr = $errStr_v->scalarVal();
                } else {
                    $errNo = $v['faultCode'];
                    $errStr = $v['faultString'];
                }

                if ($errNo == 0) {
                    
                    
                    $this->getLogger()->error('XML-RPC: ' . __METHOD__ . ': fault response received with faultCode 0 or null. Converted it to -1');
                    
                    
                    $errNo = -1;
                }

                $r = new Response(0, $errNo, $errStr, '', $httpResponse);
            } else {
                $r = new Response($v, 0, '', $returnType, $httpResponse);
            }
        }

        return $r;
    }

    




    public function kindOf()
    {
        return 'msg';
    }

    





    public function setDebug($level)
    {
        $this->debug = $level;
        return $this;
    }

    

    
    public function &__get($name)
    {
        switch ($name) {
            case 'me':
            case 'mytype':
            case '_php_class':
            case 'payload':
            case 'content_type':
                $this->logDeprecation('Getting property Request::' . $name . ' is deprecated');
                return $this->$name;
            case 'httpResponse':
                
                $canAccess = false;
                $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
                if (isset($trace[1]) && isset($trace[1]['class'])) {
                    if (is_subclass_of($trace[1]['class'], 'PhpXmlRpc\Request')) {
                        $canAccess = true;
                    }
                }
                if ($canAccess) {
                    $this->logDeprecation('Getting property Request::' . $name . ' is deprecated');
                    return $this->httpResponse;
                } else {
                    trigger_error("Cannot access protected property Request::httpResponse in " . __FILE__, E_USER_ERROR);
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
            case 'methodname':
            case 'params':
            case 'debug':
            case 'payload':
            case 'content_type':
                $this->logDeprecation('Setting property Request::' . $name . ' is deprecated');
                $this->$name = $value;
                break;
            case 'httpResponse':
                
                $canAccess = false;
                $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
                if (isset($trace[1]) && isset($trace[1]['class'])) {
                    if (is_subclass_of($trace[1]['class'], 'PhpXmlRpc\Request')) {
                        $canAccess = true;
                    }
                }
                if ($canAccess) {
                    $this->logDeprecation('Setting property Request::' . $name . ' is deprecated');
                    $this->httpResponse = $value;
                } else {
                    trigger_error("Cannot access protected property Request::httpResponse in " . __FILE__, E_USER_ERROR);
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
            case 'methodname':
            case 'params':
            case 'debug':
            case 'payload':
            case 'content_type':
                $this->logDeprecation('Checking property Request::' . $name . ' is deprecated');
                return isset($this->$name);
            case 'httpResponse':
                
                $canAccess = false;
                $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
                if (isset($trace[1]) && isset($trace[1]['class'])) {
                    if (is_subclass_of($trace[1]['class'], 'PhpXmlRpc\Request')) {
                        $canAccess = true;
                    }
                }
                if ($canAccess) {
                    $this->logDeprecation('Checking property Request::' . $name . ' is deprecated');
                    return isset($this->httpResponse);
                }
                
            default:
                return false;
        }
    }

    public function __unset($name)
    {
        switch ($name) {
            case 'methodname':
            case 'params':
            case 'debug':
            case 'payload':
            case 'content_type':
                $this->logDeprecation('Unsetting property Request::' . $name . ' is deprecated');
                unset($this->$name);
                break;
            case 'httpResponse':
                
                $canAccess = false;
                $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
                if (isset($trace[1]) && isset($trace[1]['class'])) {
                    if (is_subclass_of($trace[1]['class'], 'PhpXmlRpc\Request')) {
                        $canAccess = true;
                    }
                }
                if ($canAccess) {
                    $this->logDeprecation('Unsetting property Request::' . $name . ' is deprecated');
                    unset($this->httpResponse);
                } else {
                    trigger_error("Cannot access protected property Request::httpResponse in " . __FILE__, E_USER_ERROR);
                }
                break;
            default:
                
                $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);
                trigger_error('Undefined property via __unset(): ' . $name . ' in ' . $trace[0]['file'] . ' on line ' . $trace[0]['line'], E_USER_WARNING);
        }
    }
}
