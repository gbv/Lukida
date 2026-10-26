<?php

namespace PhpXmlRpc;

use PhpXmlRpc\Helper\XMLParser;
use PhpXmlRpc\Traits\LoggerAware;
use PhpXmlRpc\Traits\ParserAware;







class Encoder
{
    use LoggerAware;
    use ParserAware;

    

























    public function decode($xmlrpcVal, $options = array())
    {
        switch ($xmlrpcVal->kindOf()) {
            case 'scalar':
                if (in_array('extension_api', $options)) {
                    $val = $xmlrpcVal->scalarVal();
                    $typ = $xmlrpcVal->scalarTyp();
                    switch ($typ) {
                        case 'dateTime.iso8601':
                            $xmlrpcVal = array(
                                'xmlrpc_type' => 'datetime',
                                'scalar' => $val,
                                'timestamp' => \PhpXmlRpc\Helper\Date::iso8601Decode($val)
                            );
                            return (object)$xmlrpcVal;
                        case 'base64':
                            $xmlrpcVal = array(
                                'xmlrpc_type' => 'base64',
                                'scalar' => $val
                            );
                            return (object)$xmlrpcVal;
                        case 'string':
                            if (isset($options['extension_api_encoding'])) {
                                
                                if (function_exists('iconv')) {
                                    $dval = @iconv('UTF-8', $options['extension_api_encoding'], $val);
                                } elseif (function_exists('mb_convert_encoding')) {
                                    
                                    $dval = @mb_convert_encoding($val, $options['extension_api_encoding'], 'UTF-8');
                                } else {
                                    $dval = false;
                                }
                                if ($dval !== false) {
                                    return $dval;
                                }
                            }
                            
                        default:
                            return $val;
                    }
                }
                if (in_array('dates_as_objects', $options) && $xmlrpcVal->scalarTyp() == 'dateTime.iso8601') {
                    
                    
                    $out = $xmlrpcVal->scalarVal();
                    if (is_string($out)) {
                        $out = strtotime($out);
                        
                        
                        
                    }
                    if (is_int($out)) {
                        $result = new \DateTime();
                        $result->setTimestamp($out);

                        return $result;
                    } elseif (is_a($out, 'DateTimeInterface') || is_a($out, 'DateTime')) {
                        return $out;
                    }
                }
                return $xmlrpcVal->scalarVal();

            case 'array':
                $arr = array();
                foreach ($xmlrpcVal as $value) {
                    $arr[] = $this->decode($value, $options);
                }
                return $arr;

            case 'struct':
                
                
                
                
                if (in_array('decode_php_objs', $options) && $xmlrpcVal->_php_class != ''
                    && class_exists($xmlrpcVal->_php_class)
                ) {
                    $obj = @new $xmlrpcVal->_php_class();
                    foreach ($xmlrpcVal as $key => $value) {
                        $obj->$key = $this->decode($value, $options);
                    }
                    return $obj;
                } else {
                    $arr = array();
                    foreach ($xmlrpcVal as $key => $value) {
                        $arr[$key] = $this->decode($value, $options);
                    }
                    return $arr;
                }

            case 'msg':
                $paramCount = $xmlrpcVal->getNumParams();
                $arr = array();
                for ($i = 0; $i < $paramCount; $i++) {
                    $arr[] = $this->decode($xmlrpcVal->getParam($i), $options);
                }
                return $arr;

            
        }
    }

    





























    public function encode($phpVal, $options = array())
    {
        $type = gettype($phpVal);
        switch ($type) {
            case 'string':
                if (in_array('auto_dates', $options) && preg_match(PhpXmlRpc::$xmlrpc_datetime_format, $phpVal)) {
                    $xmlrpcVal = new Value($phpVal, Value::$xmlrpcDateTime);
                } else {
                    $xmlrpcVal = new Value($phpVal, Value::$xmlrpcString);
                }
                break;
            case 'integer':
                $xmlrpcVal = new Value($phpVal, Value::$xmlrpcInt);
                break;
            case 'double':
                $xmlrpcVal = new Value($phpVal, Value::$xmlrpcDouble);
                break;
            case 'boolean':
                $xmlrpcVal = new Value($phpVal, Value::$xmlrpcBoolean);
                break;
            case 'array':
                
                
                
                $j = 0;
                $arr = array();
                $ko = false;
                foreach ($phpVal as $key => $val) {
                    $arr[$key] = $this->encode($val, $options);
                    if (!$ko && $key !== $j) {
                        $ko = true;
                    }
                    $j++;
                }
                if ($ko) {
                    $xmlrpcVal = new Value($arr, Value::$xmlrpcStruct);
                } else {
                    $xmlrpcVal = new Value($arr, Value::$xmlrpcArray);
                }
                break;
            case 'object':
                if (is_a($phpVal, 'PhpXmlRpc\Value')) {
                    $xmlrpcVal = $phpVal;
                
                } elseif (is_a($phpVal, 'DateTimeInterface') || is_a($phpVal, 'DateTime')) {
                    $xmlrpcVal = new Value($phpVal->format('Ymd\TH:i:s'), Value::$xmlrpcDateTime);
                } elseif (in_array('extension_api', $options) && $phpVal instanceof \stdClass && isset($phpVal->xmlrpc_type)) {
                    
                    if (isset($phpVal->scalar)) {
                        switch ($phpVal->xmlrpc_type) {
                            case 'base64':
                                $xmlrpcVal = new Value($phpVal->scalar, Value::$xmlrpcBase64);
                                break;
                            case 'datetime':
                                $xmlrpcVal = new Value($phpVal->scalar, Value::$xmlrpcDateTime);
                                break;
                            default:
                                $xmlrpcVal = new Value();
                        }
                    } else {
                        $xmlrpcVal = new Value();
                    }

                } else {
                    $arr = array();
                    foreach ($phpVal as $k => $v) {
                        $arr[$k] = $this->encode($v, $options);
                    }
                    $xmlrpcVal = new Value($arr, Value::$xmlrpcStruct);
                    if (in_array('encode_php_objs', $options)) {
                        
                        $xmlrpcVal->_php_class = get_class($phpVal);
                    }
                }
                break;
            case 'NULL':
                if (in_array('extension_api', $options)) {
                    $xmlrpcVal = new Value('', Value::$xmlrpcString);
                } elseif (in_array('null_extension', $options)) {
                    $xmlrpcVal = new Value('', Value::$xmlrpcNull);
                } else {
                    $xmlrpcVal = new Value();
                }
                break;
            case 'resource':
                if (in_array('extension_api', $options)) {
                    $xmlrpcVal = new Value((int)$phpVal, Value::$xmlrpcInt);
                } else {
                    $xmlrpcVal = new Value();
                }
                break;
            
            default:
                
                $xmlrpcVal = new Value();
                break;
        }

        return $xmlrpcVal;
    }

    













    public function decodeXml($xmlVal, $options = array())
    {
        
        $valEncoding = XMLParser::guessEncoding('', $xmlVal);
        if ($valEncoding != '') {

            
            
            
            
            
            
            
            if (!in_array($valEncoding, array('UTF-8', 'US-ASCII')) && !XMLParser::hasEncoding($xmlVal)) {
                if (function_exists('mb_convert_encoding')) {
                    $xmlVal = mb_convert_encoding($xmlVal, 'UTF-8', $valEncoding);
                } else {
                    if ($valEncoding == 'ISO-8859-1') {
                        $xmlVal = utf8_encode($xmlVal);
                    } else {
                        $this->getLogger()->error('XML-RPC: ' . __METHOD__ . ': invalid charset encoding of xml text: ' . $valEncoding);
                    }
                }
            }
        }

        
        
        if (in_array(PhpXmlRpc::$xmlrpc_internalencoding, array('UTF-8', 'ISO-8859-1', 'US-ASCII'))) {
            $parserOptions = array(XML_OPTION_TARGET_ENCODING => PhpXmlRpc::$xmlrpc_internalencoding);
        } else {
            $parserOptions = array(XML_OPTION_TARGET_ENCODING => 'UTF-8', 'target_charset' => PhpXmlRpc::$xmlrpc_internalencoding);
        }

        $xmlRpcParser = $this->getParser();
        $_xh = $xmlRpcParser->parse(
            $xmlVal,
            XMLParser::RETURN_XMLRPCVALS,
            XMLParser::ACCEPT_REQUEST | XMLParser::ACCEPT_RESPONSE | XMLParser::ACCEPT_VALUE | XMLParser::ACCEPT_FAULT,
            $parserOptions
        );
        
        if (!is_array($_xh)) {
            $_xh = $xmlRpcParser->_xh;
        }

        if ($_xh['isf'] > 1) {
            

            $this->getLogger()->error('XML-RPC: ' . $_xh['isf_reason']);

            return false;
        }

        switch ($_xh['rt']) {
            case 'methodresponse':
                $v = $_xh['value'];
                if ($_xh['isf'] == 1) {
                    
                    $vc = $v['faultCode'];
                    
                    $vs = $v['faultString'];
                    $r = new Response(0, $vc->scalarVal(), $vs->scalarVal());
                } else {
                    $r = new Response($v);
                }
                return $r;

            case 'methodcall':
                $req = new Request($_xh['method']);
                for ($i = 0; $i < count($_xh['params']); $i++) {
                    $req->addParam($_xh['params'][$i]);
                }
                return $req;

            case 'value':
                return $_xh['value'];

            case 'fault':
                
                $v = $_xh['value'];
                
                
                
                $vc = isset($v['faultCode']) ? $v['faultCode']->scalarVal() : PhpXmlRpc::$xmlrpcerr['invalid_return'];
                
                $vs = isset($v['faultString']) ? $v['faultString']->scalarVal() : '';
                if (!is_int($vc) || $vc == 0) {
                    $vc = PhpXmlRpc::$xmlrpcerr['invalid_return'];
                }
                return new Response(0, $vc, $vs);

            default:
                return false;
        }
    }
}
