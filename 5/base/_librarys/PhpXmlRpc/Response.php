<?php

namespace PhpXmlRpc;

use PhpXmlRpc\Exception\StateErrorException;
use PhpXmlRpc\Traits\CharsetEncoderAware;
use PhpXmlRpc\Traits\DeprecationLogger;
use PhpXmlRpc\Traits\PayloadBearer;
















class Response
{
    use CharsetEncoderAware;
    use DeprecationLogger;
    use PayloadBearer;

    
    protected $val = 0;
    
    protected $valtyp;
    
    protected $errno = 0;
    
    protected $errstr = '';

    protected $httpResponse = array('headers' => array(), 'cookies' => array(), 'raw_data' => '', 'status_code' => null);

    















    public function __construct($val, $fCode = 0, $fString = '', $valType = '', $httpResponse = null)
    {
        if ($fCode != 0) {
            
            $this->errno = $fCode;
            $this->errstr = $fString;
        } else {
            
            $this->val = $val;
            if ($valType == '') {
                
                if (is_object($this->val) && is_a($this->val, 'PhpXmlRpc\Value')) {
                    $this->valtyp = 'xmlrpcvals';
                } elseif (is_string($this->val)) {
                    $this->valtyp = 'xml';
                } else {
                    $this->valtyp = 'phpvals';
                }
            } else {
                $this->valtyp = $valType;
                
                if (($this->valtyp == 'xmlrpcvals' && (!is_a($this->val, 'PhpXmlRpc\Value'))) ||
                    ($this->valtyp == 'xml' && (!is_string($this->val)))) {
                    $this->getLogger()->error('XML-RPC: ' . __METHOD__ . ': value passed in does not match type ' . $valType);
                }
            }
        }

        if (is_array($httpResponse)) {
            $this->httpResponse = array_merge(array('headers' => array(), 'cookies' => array(), 'raw_data' => '', 'status_code' => null), $httpResponse);
        }
    }

    




    public function faultCode()
    {
        return $this->errno;
    }

    




    public function faultString()
    {
        return $this->errstr;
    }

    






    public function value()
    {
        return $this->val;
    }

    


    public function valueType()
    {
        return $this->valtyp;
    }

    










    public function cookies()
    {
        return $this->httpResponse['cookies'];
    }

    





    public function httpResponse()
    {
        return $this->httpResponse;
    }

    






    public function serialize($charsetEncoding = '')
    {
        if ($charsetEncoding != '') {
            $this->content_type = 'text/xml; charset=' . $charsetEncoding;
        } else {
            $this->content_type = 'text/xml';
        }

        if (PhpXmlRpc::$xmlrpc_null_apache_encoding) {
            $result = "<methodResponse xmlns:ex=\"" . PhpXmlRpc::$xmlrpc_null_apache_encoding_ns . "\">\n";
        } else {
            $result = "<methodResponse>\n";
        }
        if ($this->errno) {
            
            $result .= "<fault>\n" .
                "<value>\n<struct><member><name>faultCode</name>\n<value><int>" . $this->errno .
                "</int></value>\n</member>\n<member>\n<name>faultString</name>\n<value><string>" .
                $this->getCharsetEncoder()->encodeEntities($this->errstr, PhpXmlRpc::$xmlrpc_internalencoding, $charsetEncoding) .
                "</string></value>\n</member>\n</struct>\n</value>\n</fault>";
        } else {
            if (is_object($this->val) && is_a($this->val, 'PhpXmlRpc\Value')) {
                $result .= "<params>\n<param>\n" . $this->val->serialize($charsetEncoding) . "</param>\n</params>";
            } else if (is_string($this->val) && $this->valtyp == 'xml') {
                $result .= "<params>\n<param>\n" .
                    $this->val .
                    "</param>\n</params>";
            } else if ($this->valtyp == 'phpvals') {
                    $encoder = new Encoder();
                    $val = $encoder->encode($this->val);
                    $result .= "<params>\n<param>\n" . $val->serialize($charsetEncoding) . "</param>\n</params>";
            } else {
                throw new StateErrorException('cannot serialize xmlrpc response objects whose content is native php values');
            }
        }
        $result .= "\n</methodResponse>";

        $this->payload = $result;

        return $result;
    }

    



    public function xml_header($charsetEncoding = '')
    {
        if ($charsetEncoding != '') {
            return "<?xml version=\"1.0\" encoding=\"$charsetEncoding\"?" . ">\n";
        } else {
            return "<?xml version=\"1.0\"?" . ">\n";
        }
    }

    

    
    public function &__get($name)
    {
        switch ($name) {
            case 'val':
            case 'valtyp':
            case 'errno':
            case 'errstr':
            case 'payload':
            case 'content_type':
                $this->logDeprecation('Getting property Response::' . $name . ' is deprecated');
                return $this->$name;
            case 'hdrs':
                $this->logDeprecation('Getting property Response::' . $name . ' is deprecated');
                return $this->httpResponse['headers'];
            case '_cookies':
                $this->logDeprecation('Getting property Response::' . $name . ' is deprecated');
                return $this->httpResponse['cookies'];
            case 'raw_data':
                $this->logDeprecation('Getting property Response::' . $name . ' is deprecated');
                return $this->httpResponse['raw_data'];
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
            case 'val':
            case 'valtyp':
            case 'errno':
            case 'errstr':
            case 'payload':
            case 'content_type':
                $this->logDeprecation('Setting property Response::' . $name . ' is deprecated');
                $this->$name = $value;
                break;
            case 'hdrs':
                $this->logDeprecation('Setting property Response::' . $name . ' is deprecated');
                $this->httpResponse['headers'] = $value;
                break;
            case '_cookies':
                $this->logDeprecation('Setting property Response::' . $name . ' is deprecated');
                $this->httpResponse['cookies'] = $value;
                break;
            case 'raw_data':
                $this->logDeprecation('Setting property Response::' . $name . ' is deprecated');
                $this->httpResponse['raw_data'] = $value;
                break;
            default:
                
                $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);
                trigger_error('Undefined property via __set(): ' . $name . ' in ' . $trace[0]['file'] . ' on line ' . $trace[0]['line'], E_USER_WARNING);
        }
    }

    public function __isset($name)
    {
        switch ($name) {
            case 'val':
            case 'valtyp':
            case 'errno':
            case 'errstr':
            case 'payload':
            case 'content_type':
                $this->logDeprecation('Checking property Response::' . $name . ' is deprecated');
                return isset($this->$name);
            case 'hdrs':
                $this->logDeprecation('Checking property Response::' . $name . ' is deprecated');
                return isset($this->httpResponse['headers']);
            case '_cookies':
                $this->logDeprecation('Checking property Response::' . $name . ' is deprecated');
                return isset($this->httpResponse['cookies']);
            case 'raw_data':
                $this->logDeprecation('Checking property Response::' . $name . ' is deprecated');
                return isset($this->httpResponse['raw_data']);
            default:
                return false;
        }
    }

    public function __unset($name)
    {
        switch ($name) {
            case 'val':
            case 'valtyp':
            case 'errno':
            case 'errstr':
            case 'payload':
            case 'content_type':
                $this->logDeprecation('Unsetting property Response::' . $name . ' is deprecated');
                unset($this->$name);
                break;
            case 'hdrs':
                $this->logDeprecation('Unsetting property Response::' . $name . ' is deprecated');
                unset($this->httpResponse['headers']);
                break;
            case '_cookies':
                $this->logDeprecation('Unsetting property Response::' . $name . ' is deprecated');
                unset($this->httpResponse['cookies']);
                break;
            case 'raw_data':
                $this->logDeprecation('Unsetting property Response::' . $name . ' is deprecated');
                unset($this->httpResponse['raw_data']);
                break;
            default:
                
                $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);
                trigger_error('Undefined property via __unset(): ' . $name . ' in ' . $trace[0]['file'] . ' on line ' . $trace[0]['line'], E_USER_WARNING);
        }
    }
}
