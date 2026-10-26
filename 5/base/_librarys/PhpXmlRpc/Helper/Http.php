<?php

namespace PhpXmlRpc\Helper;

use PhpXmlRpc\Exception\HttpException;
use PhpXmlRpc\PhpXmlRpc;
use PhpXmlRpc\Traits\LoggerAware;

class Http
{
    use LoggerAware;

    protected $acceptedStatusCodes = array('200');

    







    public static function decodeChunked($buffer)
    {
        
        $length = 0;
        $new = '';

        
        
        $chunkEnd = strpos($buffer, "\r\n") + 2;
        $temp = substr($buffer, 0, $chunkEnd);
        $chunkSize = hexdec(trim($temp));
        $chunkStart = $chunkEnd;
        while ($chunkSize > 0) {
            $chunkEnd = strpos($buffer, "\r\n", $chunkStart + $chunkSize);

            
            if ($chunkEnd == false) {
                $chunk = substr($buffer, $chunkStart);
                
                $new .= $chunk;
                $length += strlen($chunk);
                break;
            }

            
            $chunk = substr($buffer, $chunkStart, $chunkEnd - $chunkStart);
            
            $new .= $chunk;
            
            $length += strlen($chunk);
            
            $chunkStart = $chunkEnd + 2;

            $chunkEnd = strpos($buffer, "\r\n", $chunkStart) + 2;
            if ($chunkEnd == false) {
                break; 
            }
            $temp = substr($buffer, $chunkStart, $chunkEnd - $chunkStart);
            $chunkSize = hexdec(trim($temp));
            $chunkStart = $chunkEnd;
        }

        return $new;
    }

    











    public function parseResponseHeaders(&$data, $headersProcessed = false, $debug = 0)
    {
        $httpResponse = array('raw_data' => $data, 'headers'=> array(), 'cookies' => array(), 'status_code' => null);

        
        if (preg_match('/^HTTP\/1\.[0-1] 200 Connection established/', $data)) {
            
            $pos = strpos($data, "\r\n\r\n");
            if ($pos || is_int($pos)) {
                $bd = $pos + 4;
            } else {
                $pos = strpos($data, "\n\n");
                if ($pos || is_int($pos)) {
                    $bd = $pos + 2;
                } else {
                    
                    $bd = 0;
                }
            }
            if ($bd) {
                
                $data = substr($data, $bd);
            } else {
                $this->getLogger()->error('XML-RPC: ' . __METHOD__ . ': HTTPS via proxy error, tunnel connection possibly failed');
                throw new HttpException(PhpXmlRpc::$xmlrpcstr['http_error'] . ' (HTTPS via proxy error, tunnel connection possibly failed)', PhpXmlRpc::$xmlrpcerr['http_error']);
            }
        }

        
        while (preg_match('/^HTTP\/1\.1 1[0-9]{2} /', $data)) {
            $pos = strpos($data, 'HTTP', 12);
            
            
            if (!$pos && !is_int($pos)) {
                

                break;
            }
            $data = substr($data, $pos);
        }

        
        
        
        
        if ($headersProcessed && preg_match('/^HTTP\/[0-9](?:\.[0-9])? (?:401|30[1278]) /', $data)) {
            if (preg_match('/(\r?\n){2}HTTP\/[0-9](?:\.[0-9])? 200 /', $data)) {
                $data = preg_replace('/^HTTP\/[0-9](?:\.[0-9])? (?:401|30[1278]) .+?(?:\r?\n){2}(HTTP\/[0-9.]+ 200 )/s', '$1', $data, 1);
            }
        }

        if (preg_match('/^HTTP\/([0-9](?:\.[0-9])?) ([0-9]{3}) /', $data, $matches)) {
            $httpResponse['protocol_version'] = $matches[1];
            $httpResponse['status_code'] = $matches[2];
        }

        if (!in_array($httpResponse['status_code'], $this->acceptedStatusCodes)) {
            $errstr = substr($data, 0, strpos($data, "\n") - 1);
            $this->getLogger()->error('XML-RPC: ' . __METHOD__ . ': HTTP error, got response: ' . $errstr);
            throw new HttpException(PhpXmlRpc::$xmlrpcstr['http_error'] . ' (' . $errstr . ')', PhpXmlRpc::$xmlrpcerr['http_error'], null, $httpResponse['status_code']);
        }

        
        $pos = strpos($data, "\r\n\r\n");
        if ($pos || is_int($pos)) {
            $bd = $pos + 4;
        } else {
            $pos = strpos($data, "\n\n");
            if ($pos || is_int($pos)) {
                $bd = $pos + 2;
            } else {
                
                
                $bd = 0;
            }
        }

        
        $ar = preg_split("/\r?\n/", trim(substr($data, 0, $pos)));

        foreach ($ar as $line) {
            
            $arr = explode(':', $line, 2);
            if (count($arr) > 1) {
                
                
                $headerName = strtolower(trim($arr[0]));
                if ($headerName == 'set-cookie') {
                    $cookie = $arr[1];
                    
                    if (isset($httpResponse['headers'][$headerName])) {
                        $httpResponse['headers'][$headerName] .= ', ' . trim($cookie);
                    } else {
                        $httpResponse['headers'][$headerName] = trim($cookie);
                    }
                    
                    
                    $cookie = explode(';', $cookie);
                    foreach ($cookie as $pos => $val) {
                        $val = explode('=', $val, 2);
                        $tag = trim($val[0]);
                        $val = isset($val[1]) ? trim($val[1]) : '';
                        if ($pos === 0) {
                            $cookieName = $tag;
                            
                            if (preg_match('/^"(.*)"$/', $val, $matches)) {
                                $val = $matches[1];
                            }
                            $httpResponse['cookies'][$cookieName] = array('value' => urldecode($val));
                        } else {
                            $httpResponse['cookies'][$cookieName][$tag] = $val;
                        }
                    }
                } else {
                    
                    
                    
                    $httpResponse['headers'][$headerName] = trim($arr[1]);
                }
            } elseif (isset($headerName)) {
                
                
                $httpResponse['headers'][$headerName] .= ' ' . trim($line);
            }
        }

        $data = substr($data, $bd);

        if ($debug && count($httpResponse['headers'])) {
            $msg = '';
            foreach ($httpResponse['headers'] as $header => $value) {
                $msg .= "HEADER: $header: $value\n";
            }
            foreach ($httpResponse['cookies'] as $header => $value) {
                $msg .= "COOKIE: $header={$value['value']}\n";
            }
            $this->getLogger()->debug($msg);
        }

        
        if (!$headersProcessed) {

            
            if (isset($httpResponse['headers']['transfer-encoding']) && $httpResponse['headers']['transfer-encoding'] == 'chunked') {
                if (!$data = static::decodeChunked($data)) {
                    $this->getLogger()->error('XML-RPC: ' . __METHOD__ . ': errors occurred when trying to rebuild the chunked data received from server');
                    throw new HttpException(PhpXmlRpc::$xmlrpcstr['dechunk_fail'], PhpXmlRpc::$xmlrpcerr['dechunk_fail'], null, $httpResponse['status_code']);
                }
            }

            
            
            if (isset($httpResponse['headers']['content-encoding'])) {
                $httpResponse['headers']['content-encoding'] = str_replace('x-', '', $httpResponse['headers']['content-encoding']);
                if ($httpResponse['headers']['content-encoding'] == 'deflate' || $httpResponse['headers']['content-encoding'] == 'gzip') {
                    
                    if (function_exists('gzinflate')) {
                        if ($httpResponse['headers']['content-encoding'] == 'deflate' && $degzdata = @gzuncompress($data)) {
                            $data = $degzdata;
                            if ($debug) {
                                $this->getLogger()->debug("---INFLATED RESPONSE---[" . strlen($data) . " chars]---\n$data\n---END---");
                            }
                        } elseif ($httpResponse['headers']['content-encoding'] == 'gzip' && $degzdata = @gzinflate(substr($data, 10))) {
                            $data = $degzdata;
                            if ($debug) {
                                $this->getLogger()->debug("---INFLATED RESPONSE---[" . strlen($data) . " chars]---\n$data\n---END---");
                            }
                        } else {
                            $this->getLogger()->error('XML-RPC: ' . __METHOD__ . ': errors occurred when trying to decode the deflated data received from server');
                            throw new HttpException(PhpXmlRpc::$xmlrpcstr['decompress_fail'], PhpXmlRpc::$xmlrpcerr['decompress_fail'], null, $httpResponse['status_code']);
                        }
                    } else {
                        $this->getLogger()->error('XML-RPC: ' . __METHOD__ . ': the server sent deflated data. Your php install must have the Zlib extension compiled in to support this.');
                        throw new HttpException(PhpXmlRpc::$xmlrpcstr['cannot_decompress'], PhpXmlRpc::$xmlrpcerr['cannot_decompress'], null, $httpResponse['status_code']);
                    }
                }
            }
        } 

        return $httpResponse;
    }

    






    public function parseAcceptHeader($header)
    {
        $accepted = array();
        foreach(explode(',', $header) as $c) {
            if (preg_match('/^([^;]+); *q=([0-9.]+)/', $c, $matches)) {
                $c = $matches[1];
                $w = $matches[2];
            } else {
                $c = preg_replace('/;.*/', '', $c);
                $w = 1;
            }
            $accepted[(trim($c))] = $w;
        }
        arsort($accepted);
        return array_keys($accepted);
    }

    



    public function setAcceptedStatusCodes($statusCodes)
    {
        $this->acceptedStatusCodes = $statusCodes;
    }
}
