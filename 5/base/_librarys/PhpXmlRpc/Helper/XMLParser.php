<?php

namespace PhpXmlRpc\Helper;

use PhpXmlRpc\PhpXmlRpc;
use PhpXmlRpc\Traits\DeprecationLogger;
use PhpXmlRpc\Value;
















class XMLParser
{
    use DeprecationLogger;

    const RETURN_XMLRPCVALS = 'xmlrpcvals';
    const RETURN_EPIVALS = 'epivals';
    const RETURN_PHP = 'phpvals';

    const ACCEPT_REQUEST = 1;
    const ACCEPT_RESPONSE = 2;
    const ACCEPT_VALUE = 4;
    const ACCEPT_FAULT = 8;

    



    protected $maxLogValueLength = 100;

    


















    protected $_xh = array(
        'ac' => '',
        'stack' => array(),
        'valuestack' => array(),
        'lv' => 0,
        'isf' => 0,
        'isf_reason' => '',
        'value' => null,
        'method' => false,
        'params' => array(),
        'pt' => array(),
        'rt' => '',
    );

    


    protected $xmlrpc_valid_parents = array(
        'VALUE' => array('MEMBER', 'DATA', 'PARAM', 'FAULT'),
        'BOOLEAN' => array('VALUE'),
        'I4' => array('VALUE'),
        'I8' => array('VALUE'),
        'EX:I8' => array('VALUE'),
        'INT' => array('VALUE'),
        'STRING' => array('VALUE'),
        'DOUBLE' => array('VALUE'),
        'DATETIME.ISO8601' => array('VALUE'),
        'BASE64' => array('VALUE'),
        'MEMBER' => array('STRUCT'),
        'NAME' => array('MEMBER'),
        'DATA' => array('ARRAY'),
        'ARRAY' => array('VALUE'),
        'STRUCT' => array('VALUE'),
        'PARAM' => array('PARAMS'),
        'METHODNAME' => array('METHODCALL'),
        'PARAMS' => array('METHODCALL', 'METHODRESPONSE'),
        'FAULT' => array('METHODRESPONSE'),
        'NIL' => array('VALUE'), 
        'EX:NIL' => array('VALUE'), 
    );

    
    protected $parsing_options = array();

    
    

    
    protected $maxChunkLength = 4194304;
    




    protected $current_parsing_options = array(
        'xmlrpc_null_extension' => false,
        'xmlrpc_return_datetimes' => false,
        'xmlrpc_reject_invalid_values' => false
    );

    








    public function __construct(array $options = array())
    {
        $this->parsing_options = $options;
    }

    

















    public function parse($data, $returnType = self::RETURN_XMLRPCVALS, $accept = 3, $options = array())
    {
        $this->_xh = array(
            'ac' => '',
            'stack' => array(),
            'valuestack' => array(),
            'lv' => 0,
            'isf' => 0,
            'isf_reason' => '',
            'value' => null,
            'method' => false, 
            'params' => false, 
            'pt' => array(),
            'rt' => '',
        );

        $len = strlen($data);

        
        if ($len == 0) {
            $this->_xh['isf'] = 3;
            $this->_xh['isf_reason'] = 'XML error 5: empty document';
            return $this->_xh;
        }

        $this->current_parsing_options = array('accept' => $accept);

        $mergedOptions = $this->parsing_options;
        foreach ($options as $key => $val) {
            $mergedOptions[$key] = $val;
        }

        foreach ($mergedOptions as $key => $val) {
            
            if (is_string($key) && !ctype_digit($key)) {
                
                switch($key) {
                    case 'target_charset':
                        if (function_exists('mb_convert_encoding')) {
                            $this->current_parsing_options['target_charset'] = $val;
                        } else {
                            $this->getLogger()->error('XML-RPC: ' . __METHOD__ . ": 'target_charset' option is unsupported without mbstring");
                        }
                        break;

                    case 'methodname_callback':
                        if (is_callable($val)) {
                            $this->current_parsing_options['methodname_callback'] = $val;
                        } else {
                            $this->getLogger()->error('XML-RPC: ' . __METHOD__ . ": Callback passed as 'methodname_callback' is not callable");
                        }
                        break;

                    case 'xmlrpc_null_extension':
                    case 'xmlrpc_return_datetimes':
                    case 'xmlrpc_reject_invalid_values':
                        $this->current_parsing_options[$key] = $val;
                        break;

                    default:
                        $this->getLogger()->error('XML-RPC: ' . __METHOD__ . ": unsupported option: $key");
                }
                unset($mergedOptions[$key]);
            }
        }

        if (!isset($this->current_parsing_options['xmlrpc_null_extension'])) {
            $this->current_parsing_options['xmlrpc_null_extension'] = PhpXmlRpc::$xmlrpc_null_extension;
        }
        if (!isset($this->current_parsing_options['xmlrpc_return_datetimes'])) {
            $this->current_parsing_options['xmlrpc_return_datetimes'] = PhpXmlRpc::$xmlrpc_return_datetimes;
        }
        if (!isset($this->current_parsing_options['xmlrpc_reject_invalid_values'])) {
            $this->current_parsing_options['xmlrpc_reject_invalid_values'] = PhpXmlRpc::$xmlrpc_reject_invalid_values;
        }

        
        $parser = xml_parser_create('');

        foreach ($mergedOptions as $key => $val) {
            xml_parser_set_option($parser, $key, $val);
        }

        
        xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 1);

        switch ($returnType) {
            case self::RETURN_PHP:
                xml_set_element_handler($parser, array($this, 'xmlrpc_se'), array($this, 'xmlrpc_ee_fast'));
                break;
            case self::RETURN_EPIVALS:
                xml_set_element_handler($parser, array($this, 'xmlrpc_se'), array($this, 'xmlrpc_ee_epi'));
                break;
            
            case XMLParser::RETURN_XMLRPCVALS:
            default:
                xml_set_element_handler($parser, array($this, 'xmlrpc_se'), array($this, 'xmlrpc_ee'));
        }

        xml_set_character_data_handler($parser, array($this, 'xmlrpc_cd'));
        xml_set_default_handler($parser, array($this, 'xmlrpc_dh'));

        try {
            
            for ($offset = 0; $offset < $len; $offset += $this->maxChunkLength) {
                $chunk = substr($data, $offset, $this->maxChunkLength);
                
                if (!@xml_parse($parser, $chunk, $offset + $this->maxChunkLength >= $len)) {
                    $errCode = xml_get_error_code($parser);
                    $errStr = sprintf('XML error %s: %s at line %d, column %d', $errCode, xml_error_string($errCode),
                        xml_get_current_line_number($parser), xml_get_current_column_number($parser));
                    $this->_xh['isf'] = 3;
                    $this->_xh['isf_reason'] = $errStr;
                }
                
                if ($this->_xh['isf'] >= 2) {
                    break;
                }
            }
        
        } catch (\Exception $e) {
            if (PHP_MAJOR_VERSION < 8) xml_parser_free($parser);
            $this->current_parsing_options = array();
            
            throw $e;
        } catch (\Error $e) {
            if (PHP_MAJOR_VERSION < 8) xml_parser_free($parser);
            $this->current_parsing_options = array();
            
            
            throw $e;
        }

        if (PHP_MAJOR_VERSION < 8) xml_parser_free($parser);
        $this->current_parsing_options = array();

        
        if ($this->_xh['params'] === false) {
            $this->_xh['params'] = array();
        }

        return $this->_xh;
    }

    












    public function xmlrpc_se($parser, $name, $attrs, $acceptSingleVals = false)
    {
        
        if ($this->_xh['isf'] >= 2) {
            return;
        }

        
        if (count($this->_xh['stack']) == 0) {
            
            
            

            
            if ($acceptSingleVals === false) {
                $accept = $this->current_parsing_options['accept'];
            } else {
                $this->logDeprecation('Using argument $acceptSingleVals for method ' . __METHOD__ . ' is deprecated');
                $accept = self::ACCEPT_REQUEST | self::ACCEPT_RESPONSE | self::ACCEPT_VALUE;
            }
            if (($name == 'METHODCALL' && ($accept & self::ACCEPT_REQUEST)) ||
                ($name == 'METHODRESPONSE' && ($accept & self::ACCEPT_RESPONSE)) ||
                ($name == 'VALUE' && ($accept & self::ACCEPT_VALUE)) ||
                ($name == 'FAULT' && ($accept & self::ACCEPT_FAULT))) {
                $this->_xh['rt'] = strtolower($name);
            } else {
                $this->_xh['isf'] = 2;
                $this->_xh['isf_reason'] = 'missing top level xmlrpc element. Found: ' . $name;

                return;
            }
        } else {
            
            $parent = end($this->_xh['stack']);
            if (!array_key_exists($name, $this->xmlrpc_valid_parents) || !in_array($parent, $this->xmlrpc_valid_parents[$name])) {
                $this->_xh['isf'] = 2;
                $this->_xh['isf_reason'] = "xmlrpc element $name cannot be child of $parent";

                return;
            }
        }

        switch ($name) {
            
            case 'VALUE':
                
                $this->_xh['vt'] = 'value'; 
                $this->_xh['ac'] = '';
                $this->_xh['lv'] = 1;
                $this->_xh['php_class'] = null;
                break;

            case 'I8':
            case 'EX:I8':
                if (PHP_INT_SIZE === 4) {
                    
                    $this->_xh['isf'] = 2;
                    $this->_xh['isf_reason'] = "Received i8 element but php is compiled in 32 bit mode";

                    return;
                }
                

            case 'I4':
            case 'INT':
            case 'STRING':
            case 'BOOLEAN':
            case 'DOUBLE':
            case 'DATETIME.ISO8601':
            case 'BASE64':
                if ($this->_xh['vt'] != 'value') {
                    
                    $this->_xh['isf'] = 2;
                    $this->_xh['isf_reason'] = "$name element following a {$this->_xh['vt']} element inside a single value";

                    return;
                }
                $this->_xh['ac'] = ''; 
                break;

            case 'STRUCT':
            case 'ARRAY':
                if ($this->_xh['vt'] != 'value') {
                    
                    $this->_xh['isf'] = 2;
                    $this->_xh['isf_reason'] = "$name element following a {$this->_xh['vt']} element inside a single value";

                    return;
                }
                
                $curVal = array(
                    'values' => array(),
                    'type' => $name,
                );
                
                if (@isset($attrs['PHP_CLASS'])) {
                    $curVal['php_class'] = $attrs['PHP_CLASS'];
                }
                $this->_xh['valuestack'][] = $curVal;
                $this->_xh['vt'] = 'data'; 
                break;

            case 'DATA':
                if ($this->_xh['vt'] != 'data') {
                    
                    $this->_xh['isf'] = 2;
                    $this->_xh['isf_reason'] = "found two data elements inside an array element";

                    return;
                }

            case 'METHODCALL':
            case 'METHODRESPONSE':
                
                break;

            case 'PARAMS':
                $this->_xh['params'] = array();
                break;

            case 'METHODNAME':
            case 'NAME':
                
                $this->_xh['ac'] = '';
                break;

            case 'FAULT':
                $this->_xh['isf'] = 1;
                break;

            case 'MEMBER':
                
                $this->_xh['valuestack'][count($this->_xh['valuestack']) - 1]['name'] = null;
                
                

            case 'PARAM':
                
                $this->_xh['vt'] = null;
                break;

            case 'NIL':
            case 'EX:NIL':
                if ($this->current_parsing_options['xmlrpc_null_extension']) {
                    if ($this->_xh['vt'] != 'value') {
                        
                        $this->_xh['isf'] = 2;
                        $this->_xh['isf_reason'] = "$name element following a {$this->_xh['vt']} element inside a single value";

                        return;
                    }
                    
                    $this->_xh['ac'] = '';

                } else {
                    $this->_xh['isf'] = 2;
                    $this->_xh['isf_reason'] = 'Invalid NIL value received. Support for NIL can be enabled via \\PhpXmlRpc\\PhpXmlRpc::$xmlrpc_null_extension';

                    return;
                }
                break;

            default:
                
                
                $this->_xh['isf'] = 2;
                $this->_xh['isf_reason'] = "found not-xmlrpc xml element $name";

                return;
        }

        
        $this->_xh['stack'][] = $name;

        
        if ($name != 'VALUE') {
            $this->_xh['lv'] = 0;
        }
    }

    












    public function xmlrpc_ee($parser, $name, $rebuildXmlrpcvals = 1)
    {
        if ($this->_xh['isf'] >= 2) {
            return;
        }

        
        
        
        $currElem = array_pop($this->_xh['stack']);

        switch ($name) {
            case 'VALUE':
                
                if ($this->_xh['vt'] == 'value') {
                    $this->_xh['value'] = $this->_xh['ac'];
                    $this->_xh['vt'] = Value::$xmlrpcString;
                }

                
                if (isset($this->current_parsing_options['target_charset']) && $this->_xh['vt'] === Value::$xmlrpcString) {
                    $this->_xh['value'] = mb_convert_encoding($this->_xh['value'], $this->current_parsing_options['target_charset'], 'UTF-8');
                }

                if ($rebuildXmlrpcvals > 0) {
                    
                    $temp = new Value($this->_xh['value'], $this->_xh['vt']);
                    
                    if (isset($this->_xh['php_class'])) {
                        $temp->_php_class = $this->_xh['php_class'];
                    }
                    $this->_xh['value'] = $temp;
                } elseif ($rebuildXmlrpcvals < 0) {
                    if ($this->_xh['vt'] == Value::$xmlrpcDateTime) {
                        $this->_xh['value'] = (object)array(
                            'xmlrpc_type' => 'datetime',
                            'scalar' => $this->_xh['value'],
                            'timestamp' => \PhpXmlRpc\Helper\Date::iso8601Decode($this->_xh['value'])
                        );
                    } elseif ($this->_xh['vt'] == Value::$xmlrpcBase64) {
                        $this->_xh['value'] = (object)array(
                            'xmlrpc_type' => 'base64',
                            'scalar' => $this->_xh['value']
                        );
                    }
                } else {
                    
                    
                    
                    
                }

                
                
                $vscount = count($this->_xh['valuestack']);
                if ($vscount && $this->_xh['valuestack'][$vscount - 1]['type'] == 'ARRAY') {
                    $this->_xh['valuestack'][$vscount - 1]['values'][] = $this->_xh['value'];
                }
                break;

            case 'STRING':
                $this->_xh['vt'] = Value::$xmlrpcString;
                $this->_xh['lv'] = 3; 
                $this->_xh['value'] = $this->_xh['ac'];
                break;

            case 'BOOLEAN':
                $this->_xh['vt'] = Value::$xmlrpcBoolean;
                $this->_xh['lv'] = 3; 
                
                
                
                
                
                
                
                
                if ($this->_xh['ac'] == '1' || strcasecmp($this->_xh['ac'], 'true') === 0) {
                    $this->_xh['value'] = true;
                } else {
                    
                    
                    if ($this->_xh['ac'] != '0' && strcasecmp($this->_xh['ac'], 'false') !== 0) {
                        if (!$this->handleParsingError('invalid data received in BOOLEAN value: ' .
                            $this->truncateValueForLog($this->_xh['ac']), __METHOD__)) {
                            return;
                        }
                    }
                    $this->_xh['value'] = false;
                }
                break;

            case 'EX:I8':
                $name = 'i8';
                
            case 'I4':
            case 'I8':
            case 'INT':
                
                
                $this->_xh['vt'] = strtolower($name);
                $this->_xh['lv'] = 3; 
                if (!preg_match(PhpXmlRpc::$xmlrpc_int_format, $this->_xh['ac'])) {
                    if (!$this->handleParsingError('non numeric data received in INT value: ' .
                        $this->truncateValueForLog($this->_xh['ac']), __METHOD__)) {
                        return;
                    }
                    
                    $this->_xh['value'] = 'ERROR_NON_NUMERIC_FOUND';
                } else {
                    
                    $this->_xh['value'] = (int)$this->_xh['ac'];
                }
                break;

            case 'DOUBLE':
                $this->_xh['vt'] = Value::$xmlrpcDouble;
                $this->_xh['lv'] = 3; 
                if (!preg_match(PhpXmlRpc::$xmlrpc_double_format, $this->_xh['ac'])) {
                    if (!$this->handleParsingError('non numeric data received in DOUBLE value: ' .
                        $this->truncateValueForLog($this->_xh['ac']), __METHOD__)) {
                        return;
                    }

                    $this->_xh['value'] = 'ERROR_NON_NUMERIC_FOUND';
                } else {
                    
                    $this->_xh['value'] = (float)$this->_xh['ac'];
                }
                break;

            case 'DATETIME.ISO8601':
                $this->_xh['vt'] = Value::$xmlrpcDateTime;
                $this->_xh['lv'] = 3; 
                if (!preg_match(PhpXmlRpc::$xmlrpc_datetime_format, $this->_xh['ac'])) {
                    if (!$this->handleParsingError('invalid data received in DATETIME value: ' .
                        $this->truncateValueForLog($this->_xh['ac']), __METHOD__)) {
                        return;
                    }
                }
                if ($this->current_parsing_options['xmlrpc_return_datetimes']) {
                    try {
                        $this->_xh['value'] = new \DateTime($this->_xh['ac']);

                    
                    
                    } catch(\Exception $e) {
                        
                        if (!$this->handleParsingError('invalid data received in DATETIME value. Error ' .
                            $e->getMessage(), __METHOD__)) {
                            return;
                        }
                    }
                } else {
                    $this->_xh['value'] = $this->_xh['ac'];
                }
                break;

            case 'BASE64':
                $this->_xh['vt'] = Value::$xmlrpcBase64;
                $this->_xh['lv'] = 3; 
                if ($this->current_parsing_options['xmlrpc_reject_invalid_values']) {
                    $v = base64_decode($this->_xh['ac'], true);
                    if ($v === false) {
                        $this->_xh['isf'] = 2;
                        $this->_xh['isf_reason'] = 'Invalid data received in BASE64 value: '. $this->truncateValueForLog($this->_xh['ac']);
                        return;
                    }
                } else {
                    $v = base64_decode($this->_xh['ac']);
                    if ($v === '' && $this->_xh['ac'] !== '') {
                        
                        $this->getLogger()->error('XML-RPC: ' . __METHOD__ . ': invalid data received in BASE64 value: ' .
                            $this->truncateValueForLog($this->_xh['ac']));
                    }
                }
                $this->_xh['value'] = $v;
                break;

            case 'NAME':
                $this->_xh['valuestack'][count($this->_xh['valuestack']) - 1]['name'] = $this->_xh['ac'];
                break;

            case 'MEMBER':
                
                if ($this->_xh['vt']) {
                    $vscount = count($this->_xh['valuestack']);
                    if ($this->_xh['valuestack'][$vscount - 1]['name'] === null) {
                        if (!$this->handleParsingError('missing NAME inside STRUCT in received xml', __METHOD__)) {
                            return;
                        }
                        $this->_xh['valuestack'][$vscount - 1]['name'] = '';
                    }
                    $this->_xh['valuestack'][$vscount - 1]['values'][$this->_xh['valuestack'][$vscount - 1]['name']] = $this->_xh['value'];
                } else {
                    if (!$this->handleParsingError('missing VALUE inside STRUCT in received xml', __METHOD__)) {
                        return;
                    }
                }
                break;

            case 'DATA':
                $this->_xh['vt'] = null; 
                break;

            case 'STRUCT':
            case 'ARRAY':
                
                $currVal = array_pop($this->_xh['valuestack']);
                $this->_xh['value'] = $currVal['values'];
                $this->_xh['vt'] = strtolower($name);
                if (isset($currVal['php_class'])) {
                    $this->_xh['php_class'] = $currVal['php_class'];
                }
                break;

            case 'PARAM':
                
                
                if ($this->_xh['vt']) {
                    $this->_xh['params'][] = $this->_xh['value'];
                    $this->_xh['pt'][] = $this->_xh['vt'];
                } else {
                    if (!$this->handleParsingError('missing VALUE inside PARAM in received xml', __METHOD__)) {
                        return;
                    }
                }
                break;

            case 'METHODNAME':
                if (!preg_match(PhpXmlRpc::$xmlrpc_methodname_format, $this->_xh['ac'])) {
                    if (!$this->handleParsingError('invalid data received in METHODNAME: '.
                        $this->truncateValueForLog($this->_xh['ac']), __METHOD__)) {
                        return;
                    }
                }
                $methodName = trim($this->_xh['ac']);
                $this->_xh['method'] = $methodName;
                
                if (isset($this->current_parsing_options['methodname_callback'])) {
                    call_user_func($this->current_parsing_options['methodname_callback'], $methodName, $this, $parser);
                }
                break;

            case 'NIL':
            case 'EX:NIL':
                
                
                    $this->_xh['vt'] = 'null';
                    $this->_xh['value'] = null;
                    $this->_xh['lv'] = 3;
                
                break;

            
            
            case 'PARAMS':
            case 'FAULT':
                break;

            case 'METHODCALL':
                
                if ($this->_xh['method'] === false) {
                    $this->_xh['isf'] = 2;
                    $this->_xh['isf_reason'] = "missing METHODNAME element inside METHODCALL";
                }
                break;

            case 'METHODRESPONSE':
                
                if ($this->_xh['isf'] != 1 && $this->_xh['params'] === false) {
                    $this->_xh['isf'] = 2;
                    $this->_xh['isf_reason'] = "missing both FAULT and PARAMS elements inside METHODRESPONSE";
                } elseif ($this->_xh['isf'] == 0 && count($this->_xh['params']) !== 1) {
                    $this->_xh['isf'] = 2;
                    $this->_xh['isf_reason'] = "PARAMS element inside METHODRESPONSE should have exactly 1 PARAM";
                } elseif ($this->_xh['isf'] == 1 && $this->_xh['params'] !== false) {
                    $this->_xh['isf'] = 2;
                    $this->_xh['isf_reason'] = "both FAULT and PARAMS elements found inside METHODRESPONSE";
                }
                break;

            default:
                
                
                
                break;
        }
    }

    







    public function xmlrpc_ee_fast($parser, $name)
    {
        $this->xmlrpc_ee($parser, $name, 0);
    }

    







    public function xmlrpc_ee_epi($parser, $name)
    {
        $this->xmlrpc_ee($parser, $name, -1);
    }

    







    public function xmlrpc_cd($parser, $data)
    {
        
        if ($this->_xh['isf'] >= 2) {
            return;
        }

        
        if ($this->_xh['lv'] != 3) {
            $this->_xh['ac'] .= $data;
        }
    }

    








    public function xmlrpc_dh($parser, $data)
    {
        
        if ($this->_xh['isf'] >= 2) {
            return;
        }

        if (substr($data, 0, 1) == '&' && substr($data, -1, 1) == ';') {
            $this->_xh['ac'] .= $data;
        }
    }

    

























    public static function guessEncoding($httpHeader = '', $xmlChunk = '', $encodingPrefs = null)
    {
        
        

        
        
        
        
        
        
        
        
        
        

        
        $matches = array();
        if (preg_match('/;\s*charset\s*=([^;]+)/i', $httpHeader, $matches)) {
            return strtoupper(trim($matches[1], " \t\""));
        }

        
        
        
        
        
        
        
        if (preg_match('/^(?:\x00\x00\xFE\xFF|\xFF\xFE\x00\x00|\x00\x00\xFF\xFE|\xFE\xFF\x00\x00)/', $xmlChunk)) {
            return 'UCS-4';
        } elseif (preg_match('/^(?:\xFE\xFF|\xFF\xFE)/', $xmlChunk)) {
            return 'UTF-16';
        } elseif (preg_match('/^(?:\xEF\xBB\xBF)/', $xmlChunk)) {
            return 'UTF-8';
        }

        
        
        
        
        
        
        
        
        if (preg_match('/^<\?xml\s+version\s*=\s*' . "((?:\"[a-zA-Z0-9_.:-]+\")|(?:'[a-zA-Z0-9_.:-]+'))" .
            '\s+encoding\s*=\s*' . "((?:\"[A-Za-z][A-Za-z0-9._-]*\")|(?:'[A-Za-z][A-Za-z0-9._-]*'))/",
            $xmlChunk, $matches)) {
            return strtoupper(substr($matches[2], 1, -1));
        }

        
        if (function_exists('mb_detect_encoding')) {
            if ($encodingPrefs == null && PhpXmlRpc::$xmlrpc_detectencodings != null) {
                $encodingPrefs = PhpXmlRpc::$xmlrpc_detectencodings;
            }
            if ($encodingPrefs) {
                $enc = mb_detect_encoding($xmlChunk, $encodingPrefs);
            } else {
                $enc = mb_detect_encoding($xmlChunk);
            }
            
            
            if ($enc == 'ASCII') {
                $enc = 'US-' . $enc;
            }

            return $enc;
        } else {
            
            
            
            
            
            
            
            return PhpXmlRpc::$xmlrpc_defencoding;
        }
    }

    







    public static function hasEncoding($xmlChunk)
    {
        
        
        if (preg_match('/^(?:\x00\x00\xFE\xFF|\xFF\xFE\x00\x00|\x00\x00\xFF\xFE|\xFE\xFF\x00\x00)/', $xmlChunk)) {
            return true;
        } elseif (preg_match('/^(?:\xFE\xFF|\xFF\xFE)/', $xmlChunk)) {
            return true;
        } elseif (preg_match('/^(?:\xEF\xBB\xBF)/', $xmlChunk)) {
            return true;
        }

        
        
        
        
        
        if (preg_match('/^<\?xml\s+version\s*=\s*' . "((?:\"[a-zA-Z0-9_.:-]+\")|(?:'[a-zA-Z0-9_.:-]+'))" .
            '\s+encoding\s*=\s*' . "((?:\"[A-Za-z][A-Za-z0-9._-]*\")|(?:'[A-Za-z][A-Za-z0-9._-]*'))/",
            $xmlChunk)) {
            return true;
        }

        return false;
    }

    




    protected function handleParsingError($message, $method = '')
    {
        if ($this->current_parsing_options['xmlrpc_reject_invalid_values']) {
            $this->_xh['isf'] = 2;
            $this->_xh['isf_reason'] = ucfirst($message);
            return false;
        } else {
            $this->getLogger()->error('XML-RPC: ' . ($method != '' ? $method . ': ' : '') . $message);
            return true;
        }
    }

    




    protected function truncateValueForLog($data)
    {
        if (strlen($data) > $this->maxLogValueLength) {
            return substr($data, 0, $this->maxLogValueLength - 3) . '...';
        }

        return $data;
    }

    

    









    public function xmlrpc_se_any($parser, $name, $attrs)
    {
        
        $this->logDeprecation('Method ' . __METHOD__ . ' is deprecated');

        $this->xmlrpc_se($parser, $name, $attrs, true);
    }

    public function &__get($name)
    {
        switch ($name) {
            case '_xh':
            case 'xmlrpc_valid_parents':
                $this->logDeprecation('Getting property XMLParser::' . $name . ' is deprecated');
                return $this->$name;
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
            
            case 'accept':
                $this->logDeprecation('Setting property XMLParser::' . $name . ' is deprecated');
                $this->current_parsing_options['accept'] = $value;
                break;
            case '_xh':
            case 'xmlrpc_valid_parents':
                $this->logDeprecation('Setting property XMLParser::' . $name . ' is deprecated');
                $this->$name = $value;
                break;
            default:
                
                $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);
                trigger_error('Undefined property via __set(): ' . $name . ' in ' . $trace[0]['file'] . ' on line ' . $trace[0]['line'], E_USER_WARNING);
        }
    }

    public function __isset($name)
    {
        switch ($name) {
            case 'accept':
                $this->logDeprecation('Checking property XMLParser::' . $name . ' is deprecated');
                return isset($this->current_parsing_options['accept']);
            case '_xh':
            case 'xmlrpc_valid_parents':
                $this->logDeprecation('Checking property XMLParser::' . $name . ' is deprecated');
                return isset($this->$name);
            default:
                return false;
        }
    }

    public function __unset($name)
    {
        switch ($name) {
            
            case 'accept':
                $this->logDeprecation('Unsetting property XMLParser::' . $name . ' is deprecated');
                unset($this->current_parsing_options['accept']);
                break;
            case '_xh':
            case 'xmlrpc_valid_parents':
                $this->logDeprecation('Unsetting property XMLParser::' . $name . ' is deprecated');
                unset($this->$name);
                break;
            default:
                
                $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);
                trigger_error('Undefined property via __unset(): ' . $name . ' in ' . $trace[0]['file'] . ' on line ' . $trace[0]['line'], E_USER_WARNING);
        }
    }
}
