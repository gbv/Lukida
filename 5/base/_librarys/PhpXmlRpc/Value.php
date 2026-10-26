<?php

namespace PhpXmlRpc;

use PhpXmlRpc\Exception\StateErrorException;
use PhpXmlRpc\Exception\TypeErrorException;
use PhpXmlRpc\Exception\ValueErrorException;
use PhpXmlRpc\Traits\CharsetEncoderAware;
use PhpXmlRpc\Traits\DeprecationLogger;








class Value implements \Countable, \IteratorAggregate, \ArrayAccess
{
    use CharsetEncoderAware;
    use DeprecationLogger;

    public static $xmlrpcI4 = "i4";
    public static $xmlrpcI8 = "i8";
    public static $xmlrpcInt = "int";
    public static $xmlrpcBoolean = "boolean";
    public static $xmlrpcDouble = "double";
    public static $xmlrpcString = "string";
    public static $xmlrpcDateTime = "dateTime.iso8601";
    public static $xmlrpcBase64 = "base64";
    public static $xmlrpcArray = "array";
    public static $xmlrpcStruct = "struct";
    public static $xmlrpcValue = "undefined";
    public static $xmlrpcNull = "null";

    public static $xmlrpcTypes = array(
        "i4" => 1,
        "i8" => 1,
        "int" => 1,
        "boolean" => 1,
        "double" => 1,
        "string" => 1,
        "dateTime.iso8601" => 1,
        "base64" => 1,
        "array" => 2,
        "struct" => 3,
        "null" => 1,
    );

    
    protected $me = array();
    


    protected $mytype = 0;
    
    protected $_php_class = null;

    










    public function __construct($val = -1, $type = '')
    {
        
        
        if ($val !== -1 || $type != '') {
            switch ($type) {
                case '':
                    $this->mytype = 1;
                    $this->me['string'] = $val;
                    break;
                case 'i4':
                case 'i8':
                case 'int':
                case 'double':
                case 'string':
                case 'boolean':
                case 'dateTime.iso8601':
                case 'base64':
                case 'null':
                    $this->mytype = 1;
                    $this->me[$type] = $val;
                    break;
                case 'array':
                    $this->mytype = 2;
                    $this->me['array'] = $val;
                    break;
                case 'struct':
                    $this->mytype = 3;
                    $this->me['struct'] = $val;
                    break;
                default:
                    $this->getLogger()->error("XML-RPC: " . __METHOD__ . ": not a known type ($type)");
            }
        }
    }

    
















    public function addScalar($val, $type = 'string')
    {
        $typeOf = null;
        if (isset(static::$xmlrpcTypes[$type])) {
            $typeOf = static::$xmlrpcTypes[$type];
        }

        if ($typeOf !== 1) {
            $this->getLogger()->error("XML-RPC: " . __METHOD__ . ": not a scalar type ($type)");
            return 0;
        }

        
        
        
        if ($type == static::$xmlrpcBoolean) {
            if (strcasecmp($val, 'true') == 0 || $val == 1 || ($val == true && strcasecmp($val, 'false'))) {
                $val = true;
            } else {
                $val = false;
            }
        }

        switch ($this->mytype) {
            case 1:
                $this->getLogger()->error('XML-RPC: ' . __METHOD__ . ': scalar xmlrpc value can have only one value');
                return 0;
            case 3:
                $this->getLogger()->error('XML-RPC: ' . __METHOD__ . ': cannot add anonymous scalar to struct xmlrpc value');
                return 0;
            case 2:
                
                
                $class = get_class($this);
                $this->me['array'][] = new $class($val, $type);

                return 1;
            default:
                
                $this->me[$type] = $val;
                $this->mytype = $typeOf;

                return 1;
        }
    }

    












    public function addArray($values)
    {
        if ($this->mytype == 0) {
            $this->mytype = static::$xmlrpcTypes['array'];
            $this->me['array'] = $values;

            return 1;
        } elseif ($this->mytype == 2) {
            
            $this->me['array'] = array_merge($this->me['array'], $values);

            return 1;
        } else {
            $this->getLogger()->error('XML-RPC: ' . __METHOD__ . ': already initialized as a [' . $this->kindOf() . ']');
            return 0;
        }
    }

    












    public function addStruct($values)
    {
        if ($this->mytype == 0) {
            $this->mytype = static::$xmlrpcTypes['struct'];
            $this->me['struct'] = $values;

            return 1;
        } elseif ($this->mytype == 3) {
            
            $this->me['struct'] = array_merge($this->me['struct'], $values);

            return 1;
        } else {
            $this->getLogger()->error('XML-RPC: ' . __METHOD__ . ': already initialized as a [' . $this->kindOf() . ']');
            return 0;
        }
    }

    




    public function kindOf()
    {
        switch ($this->mytype) {
            case 3:
                return 'struct';
            case 2:
                return 'array';
            case 1:
                return 'scalar';
            default:
                return 'undef';
        }
    }


    




    public function scalarVal()
    {
        $b = reset($this->me);

        return $b;
    }

    





    public function scalarTyp()
    {
        reset($this->me);
        $a = key($this->me);
        if ($a == static::$xmlrpcI4) {
            $a = static::$xmlrpcInt;
        }

        return $a;
    }

    





    public function serialize($charsetEncoding = '')
    {
        $val = reset($this->me);
        $typ = key($this->me);

        return '<value>' . $this->serializeData($typ, $val, $charsetEncoding) . "</value>\n";
    }

    







    protected function serializeData($typ, $val, $charsetEncoding = '')
    {
        $this->logDeprecationUnlessCalledBy('serialize');

        if (!isset(static::$xmlrpcTypes[(string)$typ])) {
            return '';
        }

        switch (static::$xmlrpcTypes[$typ]) {
            case 1:
                switch ($typ) {
                    case static::$xmlrpcBase64:
                        $rs = "<{$typ}>" . base64_encode($val) . "</{$typ}>";
                        break;
                    case static::$xmlrpcBoolean:
                        $rs = "<{$typ}>" . ($val ? '1' : '0') . "</{$typ}>";
                        break;
                    case static::$xmlrpcString:
                        
                        $rs = "<{$typ}>" . $this->getCharsetEncoder()->encodeEntities($val, PhpXmlRpc::$xmlrpc_internalencoding, $charsetEncoding) . "</{$typ}>";
                        break;
                    case static::$xmlrpcInt:
                    case static::$xmlrpcI4:
                    case static::$xmlrpcI8:
                        $rs = "<{$typ}>" . (int)$val . "</{$typ}>";
                        break;
                    case static::$xmlrpcDouble:
                        
                        
                        
                        
                        
                        $rs = "<{$typ}>" . preg_replace('/\\.?0+$/', '', number_format((float)$val, PhpXmlRpc::$xmlpc_double_precision, '.', '')) . "</{$typ}>";
                        break;
                    case static::$xmlrpcDateTime:
                        if (is_string($val)) {
                            $rs = "<{$typ}>{$val}</{$typ}>";
                        
                        } elseif (is_a($val, 'DateTimeInterface') || is_a($val, 'DateTime')) {
                            $rs = "<{$typ}>" . $val->format('Ymd\TH:i:s') . "</{$typ}>";
                        } elseif (is_int($val)) {
                            $rs = "<{$typ}>" . date('Ymd\TH:i:s', $val) . "</{$typ}>";
                        } else {
                            
                            $rs = "<{$typ}>{$val}</{$typ}>";
                        }
                        break;
                    case static::$xmlrpcNull:
                        if (PhpXmlRpc::$xmlrpc_null_apache_encoding) {
                            $rs = "<ex:nil/>";
                        } else {
                            $rs = "<nil/>";
                        }
                        break;
                    default:
                        
                        
                        $rs = "<{$typ}>{$val}</{$typ}>";
                }
                break;
            case 3:
                
                if ($this->_php_class) {
                    $rs = '<struct php_class="' . $this->_php_class . "\">\n";
                } else {
                    $rs = "<struct>\n";
                }
                $charsetEncoder = $this->getCharsetEncoder();
                
                foreach ($val as $key2 => $val2) {
                    $rs .= '<member><name>' . $charsetEncoder->encodeEntities($key2, PhpXmlRpc::$xmlrpc_internalencoding, $charsetEncoding) . "</name>\n";
                    $rs .= $val2->serialize($charsetEncoding);
                    $rs .= "</member>\n";
                }
                $rs .= '</struct>';
                break;
            case 2:
                
                $rs = "<array>\n<data>\n";
                
                foreach ($val as $element) {
                    $rs .= $element->serialize($charsetEncoding);
                }
                $rs .= "</data>\n</array>";
                break;
            default:
                
                $rs = '';
                break;
        }

        return $rs;
    }

    







    #[\ReturnTypeWillChange]
    public function count()
    {
        switch ($this->mytype) {
            case 3:
                return count($this->me['struct']);
            case 2:
                return count($this->me['array']);
            case 1:
                return 1;
            default:
                return 0;
        }
    }

    





    #[\ReturnTypeWillChange]
    public function getIterator()
    {
        switch ($this->mytype) {
            case 3:
                return new \ArrayIterator($this->me['struct']);
            case 2:
                return new \ArrayIterator($this->me['array']);
            case 1:
                return new \ArrayIterator($this->me);
            default:
                return new \ArrayIterator();
        }
    }

    







    #[\ReturnTypeWillChange]
    public function offsetSet($offset, $value)
    {
        switch ($this->mytype) {
            case 3:
                if (!($value instanceof Value)) {
                    throw new TypeErrorException('It is only possible to add Value objects to an XML-RPC Struct');
                }
                if (is_null($offset)) {
                    
                    throw new ValueErrorException('It is not possible to add anonymous members to an XML-RPC Struct');
                } else {
                    $this->me['struct'][$offset] = $value;
                }
                return;
            case 2:
                if (!($value instanceof Value)) {
                    throw new TypeErrorException('It is only possible to add Value objects to an XML-RPC Array');
                }
                if (is_null($offset)) {
                    $this->me['array'][] = $value;
                } else {
                    
                    $this->me['array'][$offset] = $value;
                }
                return;
            case 1:
                
                
                reset($this->me);
                $type = key($this->me);
                if ($type != $offset && ($type != 'i4' || $offset != 'int')) {
                    throw new ValueErrorException('...');
                }
                $this->me[$type] = $value;
                return;
            default:
                
                throw new ValueErrorException("XML-RPC Value is of type 'undef' and its value can not be set using array index");
        }
    }

    





    #[\ReturnTypeWillChange]
    public function offsetExists($offset)
    {
        switch ($this->mytype) {
            case 3:
                return isset($this->me['struct'][$offset]);
            case 2:
                return isset($this->me['array'][$offset]);
            case 1:
                
                if ($offset == 'i4') {
                    
                    reset($this->me);
                    return $offset == key($this->me);
                } else {
                    return $offset == $this->scalarTyp();
                }
            default:
                return false;
        }
    }

    






    #[\ReturnTypeWillChange]
    public function offsetUnset($offset)
    {
        switch ($this->mytype) {
            case 3:
                unset($this->me['struct'][$offset]);
                return;
            case 2:
                unset($this->me['array'][$offset]);
                return;
            case 1:
                
                
                throw new StateErrorException("XML-RPC Value is of type 'scalar' and its value can not be unset using array index");
            default:
                throw new StateErrorException("XML-RPC Value is of type 'undef' and its value can not be unset using array index");
        }
    }

    






    #[\ReturnTypeWillChange]
    public function offsetGet($offset)
    {
        switch ($this->mytype) {
            case 3:
                return isset($this->me['struct'][$offset]) ? $this->me['struct'][$offset] : null;
            case 2:
                return isset($this->me['array'][$offset]) ? $this->me['array'][$offset] : null;
            case 1:
                
                $value = reset($this->me);
                $type = key($this->me);
                return $type == $offset ? $value : (($type == 'i4' && $offset == 'int') ? $value : null);
            default:
                
                throw new StateErrorException("XML-RPC Value is of type 'undef' and can not be accessed using array index");
        }
    }

    

    









    public function structMemExists($key)
    {
        $this->logDeprecation('Method ' . __METHOD__ . ' is deprecated');

        return array_key_exists($key, $this->me['struct']);
    }

    








    public function structMem($key)
    {
        $this->logDeprecation('Method ' . __METHOD__ . ' is deprecated');

        return $this->me['struct'][$key];
    }

    





    public function structReset()
    {
        $this->logDeprecation('Method ' . __METHOD__ . ' is deprecated');

        reset($this->me['struct']);
    }

    






    public function structEach()
    {
        $this->logDeprecation('Method ' . __METHOD__ . ' is deprecated');

        $key = key($this->me['struct']);
        $value = current($this->me['struct']);
        next($this->me['struct']);
        return array(1 => $value, 'value' => $value, 0 => $key, 'key' => $key);
    }

    








    public function arrayMem($key)
    {
        $this->logDeprecation('Method ' . __METHOD__ . ' is deprecated');

        return $this->me['array'][$key];
    }

    






    public function arraySize()
    {
        $this->logDeprecation('Method ' . __METHOD__ . ' is deprecated');

        return count($this->me['array']);
    }

    






    public function structSize()
    {
        $this->logDeprecation('Method ' . __METHOD__ . ' is deprecated');

        return count($this->me['struct']);
    }

    
    public function &__get($name)
    {
        switch ($name) {
            case 'me':
            case 'mytype':
            case '_php_class':
                $this->logDeprecation('Getting property Value::' . $name . ' is deprecated');
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
            case 'me':
            case 'mytype':
            case '_php_class':
                $this->logDeprecation('Setting property Value::' . $name . ' is deprecated');
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
            case 'me':
            case 'mytype':
            case '_php_class':
                $this->logDeprecation('Checking property Value::' . $name . ' is deprecated');
                return isset($this->$name);
            default:
                return false;
        }
    }

    public function __unset($name)
    {
        switch ($name) {
            case 'me':
            case 'mytype':
            case '_php_class':
                $this->logDeprecation('Unsetting property Value::' . $name . ' is deprecated');
                unset($this->$name);
                break;
            default:
                
                $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);
                trigger_error('Undefined property via __unset(): ' . $name . ' in ' . $trace[0]['file'] . ' on line ' . $trace[0]['line'], E_USER_WARNING);
        }
    }
}
