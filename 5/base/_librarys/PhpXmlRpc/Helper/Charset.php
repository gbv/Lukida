<?php

namespace PhpXmlRpc\Helper;

use PhpXmlRpc\Exception\ValueErrorException;
use PhpXmlRpc\PhpXmlRpc;
use PhpXmlRpc\Traits\DeprecationLogger;




class Charset
{
    use DeprecationLogger;

    
    protected $xml_iso88591_Entities = array("in" => array(), "out" => array());

    

    protected $charset_supersets = array(
        'US-ASCII' => array('ISO-8859-1', 'ISO-8859-2', 'ISO-8859-3', 'ISO-8859-4',
            'ISO-8859-5', 'ISO-8859-6', 'ISO-8859-7', 'ISO-8859-8',
            'ISO-8859-9', 'ISO-8859-10', 'ISO-8859-11', 'ISO-8859-12',
            'ISO-8859-13', 'ISO-8859-14', 'ISO-8859-15', 'UTF-8',
            'EUC-JP', 'EUC-', 'EUC-KR', 'EUC-CN',),
    );

    
    protected static $instance = null;

    






    public static function instance()
    {
        if (self::$instance === null) {
            self::$instance = new static();
        }

        return self::$instance;
    }

    


    protected function __construct()
    {
    }

    















    protected function buildConversionTable($tableName)
    {
        switch ($tableName) {
            case 'xml_iso88591_Entities':
                if (count($this->xml_iso88591_Entities['in'])) {
                    return;
                }
                for ($i = 0; $i < 32; $i++) {
                    $this->xml_iso88591_Entities["in"][] = chr($i);
                    $this->xml_iso88591_Entities["out"][] = "&#{$i};";
                }

                

                for ($i = 160; $i < 256; $i++) {
                    $this->xml_iso88591_Entities["in"][] = chr($i);
                    $this->xml_iso88591_Entities["out"][] = "&#{$i};";
                }
                break;

            




















            default:
                throw new ValueErrorException('Unsupported table: ' . $tableName);
        }
    }

    



























    public function encodeEntities($data, $srcEncoding = '', $destEncoding = '')
    {
        if ($srcEncoding == '') {
            
            $srcEncoding = PhpXmlRpc::$xmlrpc_internalencoding;
        }

        if ($destEncoding == '') {
            $destEncoding = 'US-ASCII';
        }

        
        
        
        if (!in_array($srcEncoding, array('UTF-8', 'ISO-8859-1', 'US-ASCII')) && $srcEncoding != $destEncoding &&
            function_exists('mb_convert_encoding')) {
            $data = mb_convert_encoding($data, 'UTF-8', str_replace('US-ASCII', 'ASCII', $srcEncoding));
            $srcEncoding = 'UTF-8';
        }

        $conversion = strtoupper($srcEncoding . '_' . $destEncoding);

        
        switch ($conversion) {
            case 'UTF-8_UTF-8':
            case 'ISO-8859-1_ISO-8859-1':
            case 'US-ASCII_UTF-8':
            case 'US-ASCII_US-ASCII':
            case 'US-ASCII_ISO-8859-1':
            
                $escapedData = str_replace(array('&', '"', "'", '<', '>'), array('&amp;', '&quot;', '&apos;', '&lt;', '&gt;'), $data);
                break;

            case 'UTF-8_US-ASCII':
            case 'UTF-8_ISO-8859-1':
                
                $escapedData = '';
                
                $data = (string)$data;
                $ns = strlen($data);
                for ($nn = 0; $nn < $ns; $nn++) {
                    $ch = $data[$nn];
                    $ii = ord($ch);
                    
                    if ($ii < 32) {
                        if ($conversion == 'UTF-8_US-ASCII') {
                            $escapedData .= sprintf('&#%d;', $ii);
                        } else {
                            $escapedData .= $ch;
                        }
                    }
                    else if ($ii < 128) {
                        
                        
                        switch ($ii) {
                            case 34:
                                $escapedData .= '&quot;';
                                break;
                            case 38:
                                $escapedData .= '&amp;';
                                break;
                            case 39:
                                $escapedData .= '&apos;';
                                break;
                            case 60:
                                $escapedData .= '&lt;';
                                break;
                            case 62:
                                $escapedData .= '&gt;';
                                break;
                            default:
                                $escapedData .= $ch;
                        } 
                    } 
                    elseif ($ii >> 5 == 6) {
                        $b1 = ($ii & 31);
                        $b2 = (ord($data[$nn + 1]) & 63);
                        $ii = ($b1 * 64) + $b2;
                        $escapedData .= sprintf('&#%d;', $ii);
                        $nn += 1;
                    } 
                    elseif ($ii >> 4 == 14) {
                        $b1 = ($ii & 15);
                        $b2 = (ord($data[$nn + 1]) & 63);
                        $b3 = (ord($data[$nn + 2]) & 63);
                        $ii = ((($b1 * 64) + $b2) * 64) + $b3;
                        $escapedData .= sprintf('&#%d;', $ii);
                        $nn += 2;
                    } 
                    elseif ($ii >> 3 == 30) {
                        $b1 = ($ii & 7);
                        $b2 = (ord($data[$nn + 1]) & 63);
                        $b3 = (ord($data[$nn + 2]) & 63);
                        $b4 = (ord($data[$nn + 3]) & 63);
                        $ii = ((((($b1 * 64) + $b2) * 64) + $b3) * 64) + $b4;
                        $escapedData .= sprintf('&#%d;', $ii);
                        $nn += 3;
                    }
                }

                
                if ($conversion == 'UTF-8_ISO-8859-1') {
                    $this->buildConversionTable('xml_iso88591_Entities');
                    $escapedData = str_replace(array_slice($this->xml_iso88591_Entities['out'], 32), array_slice($this->xml_iso88591_Entities['in'], 32), $escapedData);
                }
                break;

            case 'ISO-8859-1_UTF-8':
                $escapedData = str_replace(array('&', '"', "'", '<', '>'), array('&amp;', '&quot;', '&apos;', '&lt;', '&gt;'), $data);
                
                if (function_exists('mb_convert_encoding')) {
                        $escapedData = mb_convert_encoding($escapedData, 'UTF-8', 'ISO-8859-1');
                } else {
                    $escapedData = utf8_encode($escapedData);
                }
                break;

            case 'ISO-8859-1_US-ASCII':
                $this->buildConversionTable('xml_iso88591_Entities');
                $escapedData = str_replace(array('&', '"', "'", '<', '>'), array('&amp;', '&quot;', '&apos;', '&lt;', '&gt;'), $data);
                $escapedData = str_replace($this->xml_iso88591_Entities['in'], $this->xml_iso88591_Entities['out'], $escapedData);
                break;

            





















            default:
                if (function_exists('mb_convert_encoding')) {
                    
                    
                    
                    if ($srcEncoding === 'UTF-8') {
                        $data = htmlspecialchars($data,  defined('ENT_XML1') ? ENT_XML1 | ENT_QUOTES : ENT_QUOTES, 'UTF-8');
                    }
                    if ($srcEncoding !== $destEncoding) {
                        try {
                            
                            $data = @mb_convert_encoding($data, str_replace('US-ASCII', 'ASCII', $destEncoding), str_replace('US-ASCII', 'ASCII', $srcEncoding));
                        } catch (\ValueError $e) {
                            $data = false;
                        }
                    }
                    if ($data === false) {
                        $escapedData = '';
                        $this->getLogger()->error('XML-RPC: ' . __METHOD__ . ": Converting from $srcEncoding to $destEncoding via mbstring: failed...");
                    } else {
                        if ($srcEncoding === 'UTF-8') {
                            $escapedData = $data;
                        } else {
                            $escapedData = htmlspecialchars($data, defined('ENT_XML1') ? ENT_XML1 | ENT_QUOTES : ENT_QUOTES, $destEncoding);
                        }
                    }
                } else {
                    $escapedData = '';
                    $this->getLogger()->error('XML-RPC: ' . __METHOD__ . ": Converting from $srcEncoding to $destEncoding: not supported...");
                }
        }

        return $escapedData;
    }

    


    public function knownCharsets()
    {
        $knownCharsets = array('UTF-8', 'ISO-8859-1', 'US-ASCII');
        
        
        if (function_exists('mb_list_encodings')) {
            $knownCharsets = array_unique(array_merge($knownCharsets, array_diff(mb_list_encodings(), array(
                'pass', 'auto', 'wchar', 'BASE64', 'UUENCODE', 'ASCII', 'HTML-ENTITIES', 'Quoted-Printable',
                '7bit','8bit', 'byte2be', 'byte2le', 'byte4be', 'byte4le'
            ))));
        }
        return $knownCharsets;
    }

    

    








    public function isValidCharset($encoding, $validList)
    {
        $this->logDeprecation('Method ' . __METHOD__ . ' is deprecated');

        if (is_string($validList)) {
            $validList = explode(',', $validList);
        }
        if (in_array(strtoupper($encoding), $validList)) {
            return true;
        } else {
            if (array_key_exists($encoding, $this->charset_supersets)) {
                foreach ($validList as $allowed) {
                    if (in_array($allowed, $this->charset_supersets[$encoding])) {
                        return true;
                    }
                }
            }

            return false;
        }
    }

    







    public function getEntities($charset)
    {
        $this->logDeprecation('Method ' . __METHOD__ . ' is deprecated');

        switch ($charset)
        {
            case 'iso88591':
                return $this->xml_iso88591_Entities;
            default:
                throw new ValueErrorException('Unsupported charset: ' . $charset);
        }
    }
}
