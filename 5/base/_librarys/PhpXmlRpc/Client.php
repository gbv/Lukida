<?php

namespace PhpXmlRpc;

use PhpXmlRpc\Exception\ValueErrorException;
use PhpXmlRpc\Helper\XMLParser;
use PhpXmlRpc\Traits\CharsetEncoderAware;
use PhpXmlRpc\Traits\DeprecationLogger;











class Client
{
    use DeprecationLogger;
    

    const USE_CURL_NEVER = 0;
    const USE_CURL_ALWAYS = 1;
    const USE_CURL_AUTO = 2;

    const OPT_ACCEPTED_CHARSET_ENCODINGS = 'accepted_charset_encodings';
    const OPT_ACCEPTED_COMPRESSION = 'accepted_compression';
    const OPT_AUTH_TYPE = 'authtype';
    const OPT_CA_CERT = 'cacert';
    const OPT_CA_CERT_DIR = 'cacertdir';
    const OPT_CERT = 'cert';
    const OPT_CERT_PASS = 'certpass';
    const OPT_COOKIES = 'cookies';
    const OPT_DEBUG = 'debug';
    const OPT_EXTRA_CURL_OPTS = 'extracurlopts';
    const OPT_EXTRA_SOCKET_OPTS = 'extrasockopts';
    const OPT_KEEPALIVE = 'keepalive';
    const OPT_KEY = 'key';
    const OPT_KEY_PASS = 'keypass';
    const OPT_NO_MULTICALL = 'no_multicall';
    const OPT_PASSWORD = 'password';
    const OPT_PROXY = 'proxy';
    const OPT_PROXY_AUTH_TYPE = 'proxy_authtype';
    const OPT_PROXY_PASS = 'proxy_pass';
    const OPT_PROXY_PORT = 'proxyport';
    const OPT_PROXY_USER = 'proxy_user';
    const OPT_REQUEST_CHARSET_ENCODING = 'request_charset_encoding';
    const OPT_REQUEST_COMPRESSION = 'request_compression';
    const OPT_RETURN_TYPE = 'return_type';
    const OPT_SSL_VERSION = 'sslversion';
    const OPT_TIMEOUT = 'timeout';
    const OPT_USERNAME = 'username';
    const OPT_USER_AGENT = 'user_agent';
    const OPT_USE_CURL = 'use_curl';
    const OPT_VERIFY_HOST = 'verifyhost';
    const OPT_VERIFY_PEER = 'verifypeer';
    const OPT_EXTRA_HEADERS = 'extra_headers';

    
    protected static $requestClass = '\\PhpXmlRpc\\Request';
    
    protected static $responseClass = '\\PhpXmlRpc\\Response';

    



    protected $errno;
    



    protected $errstr;

    

    


    protected $method = 'http';
    


    protected $server;
    


    protected $port = 0;
    


    protected $path;

    


    protected $debug = 0;
    


    protected $username = '';
    


    protected $password = '';
    


    protected $authtype = 1;
    


    protected $cert = '';
    


    protected $certpass = '';
    


    protected $cacert = '';
    


    protected $cacertdir = '';
    


    protected $key = '';
    


    protected $keypass = '';
    


    protected $verifypeer = true;
    


    protected $verifyhost = 2;
    





    protected $sslversion = 0;
    


    protected $proxy = '';
    


    protected $proxyport = 0;
    


    protected $proxy_user = '';
    


    protected $proxy_pass = '';
    


    protected $proxy_authtype = 1;
    


    protected $cookies = array();
    


    protected $extrasockopts = array();
    


    protected $extracurlopts = array();
    


    protected $timeout = 0;
    


    protected $use_curl = self::USE_CURL_AUTO;
    







    protected $no_multicall = false;
    









    protected $accepted_compression = array();
    





    protected $request_compression = '';
    




    protected $keepalive = false;
    




    protected $accepted_charset_encodings = array();
    









    protected $request_charset_encoding = '';
    














    protected $return_type = XMLParser::RETURN_XMLRPCVALS;
    




    protected $user_agent;

    




    protected $extra_headers = array();

    



    public $xmlrpc_curl_handle = null;

    


    protected static $options = array(
        self::OPT_ACCEPTED_CHARSET_ENCODINGS,
        self::OPT_ACCEPTED_COMPRESSION,
        self::OPT_AUTH_TYPE,
        self::OPT_CA_CERT,
        self::OPT_CA_CERT_DIR,
        self::OPT_CERT,
        self::OPT_CERT_PASS,
        self::OPT_COOKIES,
        self::OPT_DEBUG,
        self::OPT_EXTRA_CURL_OPTS,
        self::OPT_EXTRA_SOCKET_OPTS,
        self::OPT_KEEPALIVE,
        self::OPT_KEY,
        self::OPT_KEY_PASS,
        self::OPT_NO_MULTICALL,
        self::OPT_PASSWORD,
        self::OPT_PROXY,
        self::OPT_PROXY_AUTH_TYPE,
        self::OPT_PROXY_PASS,
        self::OPT_PROXY_USER,
        self::OPT_PROXY_PORT,
        self::OPT_REQUEST_CHARSET_ENCODING,
        self::OPT_REQUEST_COMPRESSION,
        self::OPT_RETURN_TYPE,
        self::OPT_SSL_VERSION,
        self::OPT_TIMEOUT,
        self::OPT_USE_CURL,
        self::OPT_USER_AGENT,
        self::OPT_USERNAME,
        self::OPT_VERIFY_HOST,
        self::OPT_VERIFY_PEER,
        self::OPT_EXTRA_HEADERS,
    );

    
















    public function __construct($path, $server = '', $port = '', $method = '')
    {
        
        if ($server == '' && $port == '' && $method == '') {
            $parts = parse_url($path);
            $server = $parts['host'];
            $path = isset($parts['path']) ? $parts['path'] : '';
            if (isset($parts['query'])) {
                $path .= '?' . $parts['query'];
            }
            if (isset($parts['fragment'])) {
                $path .= '#' . $parts['fragment'];
            }
            if (isset($parts['port'])) {
                $port = $parts['port'];
            }
            if (isset($parts['scheme'])) {
                $method = $parts['scheme'];
            }
            if (isset($parts['user'])) {
                $this->username = $parts['user'];
            }
            if (isset($parts['pass'])) {
                $this->password = $parts['pass'];
            }
        }
        if ($path == '' || $path[0] != '/') {
            $this->path = '/' . $path;
        } else {
            $this->path = $path;
        }
        $this->server = $server;
        if ($port != '') {
            $this->port = $port;
        }
        if ($method != '') {
            $this->method = $method;
        }

        
        if (function_exists('gzinflate') || (
                function_exists('curl_version') && (($info = curl_version()) &&
                    ((is_string($info) && strpos($info, 'zlib') !== null) || isset($info['libz_version'])))
            )
        ) {
            $this->accepted_compression = array('gzip', 'deflate');
        }

        
        $this->keepalive = true;

        
        $this->accepted_charset_encodings = array('UTF-8', 'ISO-8859-1', 'US-ASCII');

        
        
        

        
        $this->user_agent = PhpXmlRpc::$xmlrpcName . ' ' . PhpXmlRpc::$xmlrpcVersion;
    }

    





    public function setOption($name, $value)
    {
        if (in_array($name, static::$options)) {
            $this->$name = $value;
            return $this;
        }

        throw new ValueErrorException("Unsupported option '$name'");
    }

    




    public function getOption($name)
    {
        if (in_array($name, static::$options)) {
            return $this->$name;
        }

        throw new ValueErrorException("Unsupported option '$name'");
    }

    



    public function getOptions()
    {
        $values = array();
        foreach (static::$options as $opt) {
            $values[$opt] = $this->getOption($opt);
        }
        return $values;
    }

    




    public function setOptions($options)
    {
        foreach ($options as $name => $value) {
            $this->setOption($name, $value);
        }

        return $this;
    }

    















    public function setDebug($level)
    {
        $this->debug = $level;
        return $this;
    }

    














    public function setCredentials($user, $password, $authType = 1)
    {
        $this->username = $user;
        $this->password = $password;
        $this->authtype = $authType;
        return $this;
    }

    










    public function setCertificate($cert, $certPass = '')
    {
        $this->cert = $cert;
        $this->certpass = $certPass;
        return $this;
    }

    








    public function setCaCertificate($caCert, $isDir = false)
    {
        if ($isDir) {
            $this->cacertdir = $caCert;
        } else {
            $this->cacert = $caCert;
        }
        return $this;
    }

    









    public function setKey($key, $keyPass)
    {
        $this->key = $key;
        $this->keypass = $keyPass;
        return $this;
    }

    










    public function setSSLVerifyPeer($i)
    {
        $this->logDeprecation('Method ' . __METHOD__ . ' is deprecated');

        $this->verifypeer = $i;
        return $this;
    }

    








    public function setSSLVerifyHost($i)
    {
        $this->logDeprecation('Method ' . __METHOD__ . ' is deprecated');

        $this->verifyhost = $i;
        return $this;
    }

    









    public function setSSLVersion($i)
    {
        $this->logDeprecation('Method ' . __METHOD__ . ' is deprecated');

        $this->sslversion = $i;
        return $this;
    }

    












    public function setProxy($proxyHost, $proxyPort, $proxyUsername = '', $proxyPassword = '', $proxyAuthType = 1)
    {
        $this->proxy = $proxyHost;
        $this->proxyport = $proxyPort;
        $this->proxy_user = $proxyUsername;
        $this->proxy_pass = $proxyPassword;
        $this->proxy_authtype = $proxyAuthType;
        return $this;
    }

    










    public function setAcceptedCompression($compMethod)
    {
        if ($compMethod == 'any') {
            $this->accepted_compression = array('gzip', 'deflate');
        } elseif ($compMethod == false) {
            $this->accepted_compression = array();
        } else {
            $this->accepted_compression = array($compMethod);
        }
        return $this;
    }

    










    public function setRequestCompression($compMethod)
    {
        $this->logDeprecation('Method ' . __METHOD__ . ' is deprecated');

        $this->request_compression = $compMethod;
        return $this;
    }

    





















    public function setCookie($name, $value = '', $path = '', $domain = '', $port = null)
    {
        $this->cookies[$name]['value'] = rawurlencode($value);
        if ($path || $domain || $port) {
            $this->cookies[$name]['path'] = $path;
            $this->cookies[$name]['domain'] = $domain;
            $this->cookies[$name]['port'] = $port;
        }
        return $this;
    }

    








    public function setCurlOptions($options)
    {
        $this->logDeprecation('Method ' . __METHOD__ . ' is deprecated');

        $this->extracurlopts = $options;
        return $this;
    }

    





    public function setUseCurl($useCurlMode)
    {
        $this->logDeprecation('Method ' . __METHOD__ . ' is deprecated');

        $this->use_curl = $useCurlMode;
        return $this;
    }

    








    public function setUserAgent($agentString)
    {
        $this->logDeprecation('Method ' . __METHOD__ . ' is deprecated');

        $this->user_agent = $agentString;
        return $this;
    }

    






    public function getUrl($component = null)
    {
        if (is_int($component) || ($component !== null && ctype_digit($component))) {
            switch ($component) {
                case PHP_URL_SCHEME:
                    return $this->method;
                case PHP_URL_HOST:
                    return $this->server;
                case PHP_URL_PORT:
                    return $this->port;
                case  PHP_URL_PATH:
                    return $this->path;
                case '':

                default:
                    throw new ValueErrorException("Unsupported component '$component'");
            }
        }

        $url = $this->method . '://' . $this->server;
        if ($this->port == 0 || ($this->port == 80 && in_array($this->method, array('http', 'http10', 'http11',  'http11_only', 'h2c'))) ||
            ($this->port == 443 && in_array($this->method, array('https', 'h2')))) {
            return $url . $this->path;
        } else {
            return $url . ':' . $this->port . $this->path;
        }
    }

    




































    public function send($req, $timeout = 0, $method = '')
    {
        if ($method !== '' || $timeout !== 0) {
            $this->logDeprecation("Using non-default values for arguments 'method' and 'timeout' when calling method " . __METHOD__ . ' is deprecated');
        }

        
        
        if ($method == '') {
            $method = $this->method;
        }

        if ($timeout == 0) {
            $timeout = $this->timeout;
        }

        if (is_array($req)) {
            
            
            return $this->multicall($req, $timeout, $method);
        } elseif (is_string($req)) {
            $n = new static::$requestClass('');
            
            $n->setPayload($req);
            $req = $n;
        }

        
        $req->setDebug($this->debug);

        
        
        
        
        
        $useCurl = ($this->use_curl == self::USE_CURL_ALWAYS) || ($this->use_curl == self::USE_CURL_AUTO && (
            in_array($method, array('https', 'http11', 'http11_only', 'h2c', 'h2')) ||
            ($this->username != '' && $this->authtype != 1) ||
            ($this->proxy != '' && $this->proxy_user != '' && $this->proxy_authtype != 1)
            
            
            
        ));

        
        if ($useCurl) {
            $r = $this->sendPayloadCURL(
                $req,
                $this->server,
                $this->port,
                $timeout,
                $this->username,
                $this->password,
                $this->authtype,
                $this->cert,
                $this->certpass,
                $this->cacert,
                $this->cacertdir,
                $this->proxy,
                $this->proxyport,
                $this->proxy_user,
                $this->proxy_pass,
                $this->proxy_authtype,
                
                $method == 'http11' ? 'http' : ($method == 'http11_only' ? 'http11' : $method),
                $this->keepalive,
                $this->key,
                $this->keypass,
                $this->sslversion
            );
        } else {
            $r = $this->sendPayloadSocket(
                $req,
                $this->server,
                $this->port,
                $timeout,
                $this->username,
                $this->password,
                $this->authtype,
                $this->cert,
                $this->certpass,
                $this->cacert,
                $this->cacertdir,
                $this->proxy,
                $this->proxyport,
                $this->proxy_user,
                $this->proxy_pass,
                $this->proxy_authtype,
                $method == 'http11_only' ? 'http11' : $method,
                $this->key,
                $this->keypass,
                $this->sslversion
            );
        }

        return $r;
    }

    








    protected function sendViaSocket($req, $method, $server, $port, $path, $opts)
    {
        

        
        
        
        $payload = $req->getPayload();
        if (empty($payload)) {
            $payload = $req->serialize($opts['request_charset_encoding']);
        }

        
        $encodingHdr = '';
        if ($opts['request_compression'] == 'gzip' || $opts['request_compression'] == 'deflate') {
            if ($opts['request_compression'] == 'gzip' && function_exists('gzencode')) {
                $a = @gzencode($payload);
                if ($a) {
                    $payload = $a;
                    $encodingHdr = "Content-Encoding: gzip\r\n";
                } else {
                    $this->getLogger()->warning('XML-RPC: ' . __METHOD__ . ': gzencode failure in compressing request');
                }
            } else if (function_exists('gzcompress')) {
                $a = @gzcompress($payload);
                if ($a) {
                    $payload = $a;
                    $encodingHdr = "Content-Encoding: deflate\r\n";
                } else {
                    $this->getLogger()->warning('XML-RPC: ' . __METHOD__ . ': gzcompress failure in compressing request');
                }
            } else {
                $this->getLogger()->warning('XML-RPC: ' . __METHOD__ . ': desired request compression method is unsupported by this PHP install');
            }
        } else {
            if ($opts['request_compression'] != '') {
                $this->getLogger()->warning('XML-RPC: ' . __METHOD__ . ': desired request compression method is unsupported');
            }
        }

        
        $credentials = '';
        if ($opts['username'] != '') {
            if ($opts['authtype'] != 1) {
                $this->getLogger()->error('XML-RPC: ' . __METHOD__ . ': warning. Only Basic auth is supported with HTTP 1.0');
                return new static::$responseClass(0, PhpXmlRpc::$xmlrpcerr['unsupported_option'],
                    PhpXmlRpc::$xmlrpcerr['unsupported_option'] . ': only Basic auth is supported with HTTP 1.0');
            }
            $credentials = 'Authorization: Basic ' . base64_encode($opts['username'] . ':' . $opts['password']) . "\r\n";
        }

        $acceptedEncoding = '';
        if (is_array($opts['accepted_compression']) && count($opts['accepted_compression'])) {
            $acceptedEncoding = 'Accept-Encoding: ' . implode(', ', $opts['accepted_compression']) . "\r\n";
        }

        if ($port == 0) {
            $port = ($method === 'https') ? 443 : 80;
        }

        $proxyCredentials = '';
        if ($opts['proxy']) {
            if ($opts['proxyport'] == 0) {
                $opts['proxyport'] = 8080;
            }
            $connectServer = $opts['proxy'];
            $connectPort = $opts['proxyport'];
            $transport = 'tcp';
            $protocol = $method;
            if ($method === 'http10' || $method === 'http11') {
                $protocol = 'http';
            } elseif ($method === 'h2') {
                $protocol = 'https';
            } else if (strpos($protocol, ':') !== false) {
                $this->getLogger()->error('XML-RPC: ' . __METHOD__ . ": warning - attempted hacking attempt?. The protocol requested for the call is: '$protocol'");
                return new static::$responseClass(0, PhpXmlRpc::$xmlrpcerr['unsupported_option'], PhpXmlRpc::$xmlrpcerr['unsupported_option'] .
                    " attempted hacking attempt?. The protocol requested for the call is: '$protocol'");
            }
            
            
            $uri = $protocol . '://' . $server . ':' . $port . $path;
            if ($opts['proxy_user'] != '') {
                if ($opts['proxy_authtype'] != 1) {
                    $this->getLogger()->error('XML-RPC: ' . __METHOD__ . ': warning. Only Basic auth to proxy is supported with HTTP 1.0');
                    return new static::$responseClass(0, PhpXmlRpc::$xmlrpcerr['unsupported_option'],
                        PhpXmlRpc::$xmlrpcerr['unsupported_option'] . ': only Basic auth to proxy is supported with socket transport');
                }
                $proxyCredentials = 'Proxy-Authorization: Basic ' . base64_encode($opts['proxy_user'] . ':' .
                    $opts['proxy_pass']) . "\r\n";
            }
        } else {
            $connectServer = $server;
            $connectPort = $port;
            
            $transport = ($method === 'https') ? 'tls' : 'tcp';
            $uri = $path;
        }

        
        
        $cookieHeader = '';
        if (count($opts['cookies'])) {
            $version = '';
            foreach ($opts['cookies'] as $name => $cookie) {
                
                $cookieHeader .= ' ' . $name . '=' . $cookie['value'] . ";";
            }
            $cookieHeader = 'Cookie:' . $version . substr($cookieHeader, 0, -1) . "\r\n";
        }

        $extraHeaders = '';
        if (is_array($this->extra_headers) && $this->extra_headers) {
            $extraHeaders = implode("\r\n", $this->extra_headers) . "\r\n";
        }

        
        
        if (($port == 80 && in_array($method, array('http', 'http10', 'http11'))) || ($port == 443 && $method == 'https')) {
            $port = '';
        } else {
            $port = ':' . $port;
        }

        $op = 'POST ' . $uri . " HTTP/1.0\r\n" .
            'User-Agent: ' . $opts['user_agent'] . "\r\n" .
            'Host: ' . $server . $port . "\r\n" .
            $credentials .
            $proxyCredentials .
            $acceptedEncoding .
            $encodingHdr .
            'Accept-Charset: ' . implode(',', $opts['accepted_charset_encodings']) . "\r\n" .
            $cookieHeader .
            'Content-Type: ' . $req->getContentType() . "\r\n" .
            $extraHeaders .
            'Content-Length: ' . strlen($payload) . "\r\n\r\n" .
            $payload;

        if ($opts['debug'] > 1) {
            $this->getLogger()->debug("---SENDING---\n$op\n---END---");
        }

        $contextOptions = array();
        if ($method == 'https') {
            if ($opts['cert'] != '') {
                $contextOptions['ssl']['local_cert'] = $opts['cert'];
                if ($opts['certpass'] != '') {
                    $contextOptions['ssl']['passphrase'] = $opts['certpass'];
                }
            }
            if ($opts['cacert'] != '') {
                $contextOptions['ssl']['cafile'] = $opts['cacert'];
            }
            if ($opts['cacertdir'] != '') {
                $contextOptions['ssl']['capath'] = $opts['cacertdir'];
            }
            if ($opts['key'] != '') {
                $contextOptions['ssl']['local_pk'] = $opts['key'];
            }
            $contextOptions['ssl']['verify_peer'] = $opts['verifypeer'];
            $contextOptions['ssl']['verify_peer_name'] = $opts['verifypeer'];

            if ($opts['sslversion'] != 0) {
                
                
                
                
                switch($opts['sslversion']) {
                    case 1: 
                        if (version_compare(PHP_VERSION, '7.2.0', '>=')) {
                            $contextOptions['ssl']['crypto_method'] = STREAM_CRYPTO_METHOD_TLS_CLIENT;
                        } else {
                            return new static::$responseClass(0, PhpXmlRpc::$xmlrpcerr['unsupported_option'],
                                PhpXmlRpc::$xmlrpcerr['unsupported_option'] . ': TLS-any only is supported with PHP 7.2 or later');
                        }
                        break;
                    case 2: 
                        $contextOptions['ssl']['crypto_method'] = STREAM_CRYPTO_METHOD_SSLv2_CLIENT;
                        break;
                    case 3: 
                        $contextOptions['ssl']['crypto_method'] = STREAM_CRYPTO_METHOD_SSLv3_CLIENT;
                        break;
                    case 4: 
                        $contextOptions['ssl']['crypto_method'] = STREAM_CRYPTO_METHOD_TLSv1_0_CLIENT;
                        break;
                    case 5: 
                        $contextOptions['ssl']['crypto_method'] = STREAM_CRYPTO_METHOD_TLSv1_1_CLIENT;
                        break;
                    case 6: 
                        $contextOptions['ssl']['crypto_method'] = STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT;
                        break;
                    case 7: 
                        if (defined('STREAM_CRYPTO_METHOD_TLSv1_3_CLIENT')) {
                            $contextOptions['ssl']['crypto_method'] = STREAM_CRYPTO_METHOD_TLSv1_3_CLIENT;
                        } else {
                            return new static::$responseClass(0, PhpXmlRpc::$xmlrpcerr['unsupported_option'],
                                PhpXmlRpc::$xmlrpcerr['unsupported_option'] . ': TLS-1.3 only is supported with PHP 7.4 or later');
                        }
                        break;
                    default:
                        return new static::$responseClass(0, PhpXmlRpc::$xmlrpcerr['unsupported_option'],
                            PhpXmlRpc::$xmlrpcerr['unsupported_option'] . ': Unsupported required TLS version');
                }
            }
        }

        foreach ($opts['extrasockopts'] as $proto => $protoOpts) {
            foreach ($protoOpts as $key => $val) {
                $contextOptions[$proto][$key] = $val;
            }
        }

        $context = stream_context_create($contextOptions);

        if ($opts['timeout'] <= 0) {
            $connectTimeout = ini_get('default_socket_timeout');
        } else {
            $connectTimeout = $opts['timeout'];
        }

        $this->errno = 0;
        $this->errstr = '';

        
        
        
        
        
        
        
        $fp = @stream_socket_client("$transport://$connectServer:$connectPort", $this->errno, $this->errstr, $connectTimeout,
            STREAM_CLIENT_CONNECT, $context);
        if ($fp) {
            if ($opts['timeout'] > 0) {
                stream_set_timeout($fp, $opts['timeout'], 0);
            }
        } else {
            if ($this->errstr == '') {
                $err = error_get_last();
                $this->errstr = $err['message'];
            }

            $this->errstr = 'Connect error: ' . $this->errstr;
            return new static::$responseClass(0, PhpXmlRpc::$xmlrpcerr['http_error'], $this->errstr . ' (' . $this->errno . ')');
        }

        
        

        if (!fputs($fp, $op, strlen($op))) {
            fclose($fp);
            $this->errstr = 'Write error';
            return new static::$responseClass(0, PhpXmlRpc::$xmlrpcerr['http_error'], $this->errstr);
        }

        $info = stream_get_meta_data($fp);
        if ($info['timed_out']) {
            fclose($fp);
            $this->errstr = 'Write timeout';
            return new static::$responseClass(0, PhpXmlRpc::$xmlrpcerr['http_error'], $this->errstr);
        }

        
        
        $ipd = '';
        do {
            
            
            $ipd .= fread($fp, 32768);

            $info = stream_get_meta_data($fp);
            if ($info['timed_out']) {
                fclose($fp);
                $this->errstr = 'Read timeout';
                return new static::$responseClass(0, PhpXmlRpc::$xmlrpcerr['http_error'], $this->errstr);
            }

        } while (!feof($fp));
        fclose($fp);

        return $req->parseResponse($ipd, false, $opts['return_type']);
    }

    














    protected function sendViaCURL($req, $method, $server, $port, $path, $opts)
    {
        if (!function_exists('curl_init')) {
            $this->errstr = 'CURL unavailable on this install';
            return new static::$responseClass(0, PhpXmlRpc::$xmlrpcerr['no_curl'], PhpXmlRpc::$xmlrpcstr['no_curl']);
        }
        if ($method == 'https' || $method == 'h2') {
            
            if (($info = curl_version()) &&
                ((is_string($info) && strpos($info, 'OpenSSL') === null) || (is_array($info) && !isset($info['ssl_version'])))
            ) {
                $this->errstr = 'SSL unavailable on this install';
                return new static::$responseClass(0, PhpXmlRpc::$xmlrpcerr['no_ssl'], PhpXmlRpc::$xmlrpcstr['no_ssl']);
            }
        }
        if (($method == 'h2' && !defined('CURL_HTTP_VERSION_2_0')) ||
            ($method == 'h2c' && !defined('CURL_HTTP_VERSION_2_PRIOR_KNOWLEDGE'))) {
            $this->errstr = 'HTTP/2 unavailable on this install';
            return new static::$responseClass(0, PhpXmlRpc::$xmlrpcerr['no_http2'], PhpXmlRpc::$xmlrpcstr['no_http2']);
        }

        
        $curl = $this->prepareCurlHandle($req, $server, $port, $opts['timeout'], $opts['username'], $opts['password'],
            $opts['authtype'], $opts['cert'], $opts['certpass'], $opts['cacert'], $opts['cacertdir'], $opts['proxy'],
            $opts['proxyport'], $opts['proxy_user'], $opts['proxy_pass'], $opts['proxy_authtype'], $method,
            $opts['keepalive'], $opts['key'], $opts['keypass'], $opts['sslversion']);

        if (!$curl) {
            return new static::$responseClass(0, PhpXmlRpc::$xmlrpcerr['curl_fail'], PhpXmlRpc::$xmlrpcstr['curl_fail'] .
                ': error during curl initialization. Check php error log for details');
        }

        $result = curl_exec($curl);

        if ($opts['debug'] > 1) {
            $message = "---CURL INFO---\n";
            foreach (curl_getinfo($curl) as $name => $val) {
                if (is_array($val)) {
                    $val = implode("\n", $val);
                }
                $message .= $name . ': ' . $val . "\n";
            }
            $message .= '---END---';
            $this->getLogger()->debug($message);
        }

        if (!$result) {
            

            $this->errstr = 'no response';
            $resp = new static::$responseClass(0, PhpXmlRpc::$xmlrpcerr['curl_fail'], PhpXmlRpc::$xmlrpcstr['curl_fail'] .
                ': ' . curl_error($curl));
            if (PHP_MAJOR_VERSION < 8) curl_close($curl);
            if ($opts['keepalive']) {
                $this->xmlrpc_curl_handle = null;
            }
        } else {
            if (!$opts['keepalive']) {
                if (PHP_MAJOR_VERSION < 8) curl_close($curl);
            }
            $resp = $req->parseResponse($result, true, $opts['return_type']);
            if ($opts['keepalive']) {
                
                if (is_object($resp) && $resp->faultCode() == PhpXmlRpc::$xmlrpcerr['http_error']) {
                    if (PHP_MAJOR_VERSION < 8) curl_close($curl);
                    $this->xmlrpc_curl_handle = null;
                }
            }
        }

        return $resp;
    }

    










    protected function createCURLHandle($req, $method, $server, $port, $path, $opts)
    {
        if ($port == 0) {
            if (in_array($method, array('http', 'http10', 'http11', 'h2c'))) {
                $port = 80;
            } else {
                $port = 443;
            }
        }

        
        $payload = $req->getPayload();
        if (empty($payload)) {
            $payload = $req->serialize($opts['request_charset_encoding']);
        }

        
        $encodingHdr = '';
        if (($opts['request_compression'] == 'gzip' || $opts['request_compression'] == 'deflate')) {
            if ($opts['request_compression'] == 'gzip' && function_exists('gzencode')) {
                $a = @gzencode($payload);
                if ($a) {
                    $payload = $a;
                    $encodingHdr = 'Content-Encoding: gzip';
                } else {
                    $this->getLogger()->warning('XML-RPC: ' . __METHOD__ . ': gzencode failure in compressing request');
                }
            } else if (function_exists('gzcompress')) {
                $a = @gzcompress($payload);
                if ($a) {
                    $payload = $a;
                    $encodingHdr = 'Content-Encoding: deflate';
                } else {
                    $this->getLogger()->warning('XML-RPC: ' . __METHOD__ . ': gzcompress failure in compressing request');
                }
            } else {
                $this->getLogger()->warning('XML-RPC: ' . __METHOD__ . ': desired request compression method is unsupported by this PHP install');
            }
        } else {
            if ($opts['request_compression'] != '') {
                $this->getLogger()->warning('XML-RPC: ' . __METHOD__ . ': desired request compression method is unsupported');
            }
        }

        if (!$opts['keepalive'] || !$this->xmlrpc_curl_handle) {
            if ($method == 'http11' || $method == 'http10' || $method == 'h2c') {
                $protocol = 'http';
            } else {
                if ($method == 'h2') {
                    $protocol = 'https';
                } else {
                    
                    $protocol = $method;
                    if (strpos($protocol, ':') !== false) {
                        $this->getLogger()->error('XML-RPC: ' . __METHOD__ . ": warning - attempted hacking attempt?. The curl protocol requested for the call is: '$protocol'");
                        return false;
                    }
                }
            }
            $curl = curl_init($protocol . '://' . $server . ':' . $port . $path);
            if (!$curl) {
                return false;
            }
            if ($opts['keepalive']) {
                $this->xmlrpc_curl_handle = $curl;
            }
        } else {
            $curl = $this->xmlrpc_curl_handle;
        }

        
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        if ($opts['debug'] > 1) {
            curl_setopt($curl, CURLOPT_VERBOSE, true);
            
        }
        curl_setopt($curl, CURLOPT_USERAGENT, $opts['user_agent']);
        
        curl_setopt($curl, CURLOPT_POST, 1);
        
        curl_setopt($curl, CURLOPT_POSTFIELDS, $payload);

        
        curl_setopt($curl, CURLOPT_HEADER, 1);

        
        
        if (is_array($opts['accepted_compression']) && count($opts['accepted_compression'])) {
            
            
            if (count($opts['accepted_compression']) == 1) {
                curl_setopt($curl, CURLOPT_ENCODING, $opts['accepted_compression'][0]);
            } else {
                curl_setopt($curl, CURLOPT_ENCODING, '');
            }
        }
        
        $headers = array('Content-Type: ' . $req->getContentType(), 'Accept-Charset: ' . implode(',', $opts['accepted_charset_encodings']));
        
        if (!$opts['keepalive']) {
            $headers[] = 'Connection: close';
        }
        
        if ($encodingHdr) {
            $headers[] = $encodingHdr;
        }

        if (is_array($this->extra_headers) && $this->extra_headers) {
            $headers = array_merge($headers, $this->extra_headers);
        }

        
        
        $headers[] = 'Expect:';

        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        
        
        if ($opts['timeout']) {
            curl_setopt($curl, CURLOPT_TIMEOUT, $opts['timeout']);
        }

        
        
        
        
        switch ($method) {
            case 'http10':
                curl_setopt($curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
                break;
            case 'http11':
                curl_setopt($curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
                break;
            case 'h2c':
                if (defined('CURL_HTTP_VERSION_2_PRIOR_KNOWLEDGE')) {
                    curl_setopt($curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_2_PRIOR_KNOWLEDGE);
                } else {
                    $this->getLogger()->error('XML-RPC: ' . __METHOD__ . ': warning. HTTP2 is not supported by the current PHP/curl install');
                    if (PHP_MAJOR_VERSION < 8) curl_close($curl);
                    return false;
                }
                break;
            case 'h2':
                curl_setopt($curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_2_0);
                break;
        }

        if ($opts['username'] && $opts['password']) {
            curl_setopt($curl, CURLOPT_USERPWD, $opts['username'] . ':' . $opts['password']);
            if (defined('CURLOPT_HTTPAUTH')) {
                curl_setopt($curl, CURLOPT_HTTPAUTH, $opts['authtype']);
            } elseif ($opts['authtype'] != 1) {
                $this->getLogger()->error('XML-RPC: ' . __METHOD__ . ': warning. Only Basic auth is supported by the current PHP/curl install');
                if (PHP_MAJOR_VERSION < 8) curl_close($curl);
                return false;
            }
        }

        
        if ($method == 'https' || $method == 'h2') {
            
            if ($opts['cert']) {
                curl_setopt($curl, CURLOPT_SSLCERT, $opts['cert']);
            }
            
            if ($opts['certpass']) {
                curl_setopt($curl, CURLOPT_SSLCERTPASSWD, $opts['certpass']);
            }
            
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, $opts['verifypeer']);
            
            if ($opts['cacert']) {
                curl_setopt($curl, CURLOPT_CAINFO, $opts['cacert']);
            }
            if ($opts['cacertdir']) {
                curl_setopt($curl, CURLOPT_CAPATH, $opts['cacertdir']);
            }
            
            if ($opts['key']) {
                curl_setopt($curl, CURLOPT_SSLKEY, $opts['key']);
            }
            
            if ($opts['keypass']) {
                curl_setopt($curl, CURLOPT_SSLKEYPASSWD, $opts['keypass']);
            }
            
            
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, $opts['verifyhost']);
            
            curl_setopt($curl, CURLOPT_SSLVERSION, $opts['sslversion']);
        }

        
        if ($opts['proxy']) {
            if ($opts['proxyport'] == 0) {
                $opts['proxyport'] = 8080; 
            }
            curl_setopt($curl, CURLOPT_PROXY, $opts['proxy'] . ':' . $opts['proxyport']);
            if ($opts['proxy_user']) {
                curl_setopt($curl, CURLOPT_PROXYUSERPWD, $opts['proxy_user'] . ':' . $opts['proxy_pass']);
                if (defined('CURLOPT_PROXYAUTH')) {
                    curl_setopt($curl, CURLOPT_PROXYAUTH, $opts['proxy_authtype']);
                } elseif ($opts['proxy_authtype'] != 1) {
                    $this->getLogger()->error('XML-RPC: ' . __METHOD__ . ': warning. Only Basic auth to proxy is supported by the current PHP/curl install');
                    if (PHP_MAJOR_VERSION < 8) curl_close($curl);
                    return false;
                }
            }
        }

        
        
        if (count($opts['cookies'])) {
            $cookieHeader = '';
            foreach ($opts['cookies'] as $name => $cookie) {
                $cookieHeader .= $name . '=' . $cookie['value'] . '; ';
            }
            curl_setopt($curl, CURLOPT_COOKIE, substr($cookieHeader, 0, -2));
        }

        foreach ($opts['extracurlopts'] as $opt => $val) {
            curl_setopt($curl, $opt, $val);
        }

        if ($opts['debug'] > 1) {
            $this->getLogger()->debug("---SENDING---\n$payload\n---END---");
        }

        return $curl;
    }

    
























    public function multicall($reqs, $timeout = 0, $method = '', $fallback = true)
    {
        
        if (is_bool($timeout) && $fallback === true) {
            $fallback = !$timeout;
            $timeout = 0;
        }

        if ($method == '') {
            $method = $this->method;
        }

        if (!$this->no_multicall) {
            $results = $this->_try_multicall($reqs, $timeout, $method);
            
            if (is_array($results)) {
                
                return $results;
            } else {
                
                
                if ($fallback) {
                    
                    $this->no_multicall = true;
                } else {
                    $result = $results;
                }
            }
        } else {
            
            
            $fallback = true;
        }

        $results = array();
        if ($fallback) {
            
            
            foreach ($reqs as $req) {
                $results[] = $this->send($req, $timeout, $method);
            }
        } else {
            
            
            foreach ($reqs as $req) {
                $results[] = $result;
            }
        }

        return $results;
    }

    








    protected function _try_multicall($reqs, $timeout, $method)
    {
        
        $calls = array();
        foreach ($reqs as $req) {
            $call['methodName'] = new Value($req->method(), 'string');
            $numParams = $req->getNumParams();
            $params = array();
            for ($i = 0; $i < $numParams; $i++) {
                $params[$i] = $req->getParam($i);
            }
            $call['params'] = new Value($params, 'array');
            $calls[] = new Value($call, 'struct');
        }
        $multiCall = new static::$requestClass('system.multicall');
        $multiCall->addParam(new Value($calls, 'array'));

        
        $result = $this->send($multiCall, $timeout, $method);

        if ($result->faultCode() != 0) {
            
            return $result;
        }

        
        $rets = $result->value();
        $response = array();

        if ($this->return_type == 'xml') {
            for ($i = 0; $i < count($reqs); $i++) {
                
                $response[] = new static::$responseClass($rets, 0, '', 'xml', $result->httpResponse());
            }

        } elseif ($this->return_type == 'phpvals') {
            if (!is_array($rets)) {
                
                return new static::$responseClass(0, PhpXmlRpc::$xmlrpcerr['multicall_error'],
                    PhpXmlRpc::$xmlrpcstr['multicall_error'] . ': not an array', 'phpvals', $result->httpResponse());
            }
            $numRets = count($rets);
            if ($numRets != count($reqs)) {
                
                return new static::$responseClass(0, PhpXmlRpc::$xmlrpcerr['multicall_error'],
                    PhpXmlRpc::$xmlrpcstr['multicall_error'] . ': incorrect number of responses', 'phpvals',
                    $result->httpResponse());
            }

            for ($i = 0; $i < $numRets; $i++) {
                $val = $rets[$i];
                if (!is_array($val)) {
                    return new static::$responseClass(0, PhpXmlRpc::$xmlrpcerr['multicall_error'],
                        PhpXmlRpc::$xmlrpcstr['multicall_error'] . ": response element $i is not an array or struct",
                        'phpvals', $result->httpResponse());
                }
                switch (count($val)) {
                    case 1:
                        if (!isset($val[0])) {
                            
                            return new static::$responseClass(0, PhpXmlRpc::$xmlrpcerr['multicall_error'],
                                PhpXmlRpc::$xmlrpcstr['multicall_error'] . ": response element $i has no value",
                                'phpvals', $result->httpResponse());
                        }
                        
                        $response[$i] = new static::$responseClass($val[0], 0, '', 'phpvals', $result->httpResponse());
                        break;
                    case 2:
                        
                        $code = @$val['faultCode'];
                        if (!is_int($code)) {
                            
                            return new static::$responseClass(0, PhpXmlRpc::$xmlrpcerr['multicall_error'],
                                PhpXmlRpc::$xmlrpcstr['multicall_error'] . ": response element $i has invalid or no faultCode",
                                'phpvals', $result->httpResponse());
                        }
                        $str = @$val['faultString'];
                        if (!is_string($str)) {
                            return new static::$responseClass(0, PhpXmlRpc::$xmlrpcerr['multicall_error'],
                                PhpXmlRpc::$xmlrpcstr['multicall_error'] . ": response element $i has invalid or no FaultString",
                                'phpvals', $result->httpResponse());
                        }
                        $response[$i] = new static::$responseClass(0, $code, $str, 'phpvals', $result->httpResponse());
                        break;
                    default:
                        return new static::$responseClass(0, PhpXmlRpc::$xmlrpcerr['multicall_error'],
                            PhpXmlRpc::$xmlrpcstr['multicall_error'] . ": response element $i has too many items",
                            'phpvals', $result->httpResponse());
                }
            }

        } else {
            
            if ($rets->kindOf() != 'array') {
                return new static::$responseClass(0, PhpXmlRpc::$xmlrpcerr['multicall_error'],
                    PhpXmlRpc::$xmlrpcstr['multicall_error'] . ": response element $i is not an array", 'xmlrpcvals',
                    $result->httpResponse());
            }
            $numRets = $rets->count();
            if ($numRets != count($reqs)) {
                
                return new static::$responseClass(0, PhpXmlRpc::$xmlrpcerr['multicall_error'],
                    PhpXmlRpc::$xmlrpcstr['multicall_error'] . ': incorrect number of responses', 'xmlrpcvals',
                    $result->httpResponse());
            }

            foreach ($rets as $i => $val) {
                switch ($val->kindOf()) {
                    case 'array':
                        if ($val->count() != 1) {
                            return new static::$responseClass(0, PhpXmlRpc::$xmlrpcerr['multicall_error'],
                                PhpXmlRpc::$xmlrpcstr['multicall_error'] . ": response element $i has too many items",
                                'phpvals', $result->httpResponse());
                        }
                        
                        $response[] = new static::$responseClass($val[0], 0, '', 'xmlrpcvals', $result->httpResponse());
                        break;
                    case 'struct':
                        if ($val->count() != 2) {
                            return new static::$responseClass(0, PhpXmlRpc::$xmlrpcerr['multicall_error'],
                                PhpXmlRpc::$xmlrpcstr['multicall_error'] . ": response element $i has too many items",
                                'phpvals', $result->httpResponse());
                        }
                        
                        $code = @$val['faultCode'];
                        if (!$code || $code->kindOf() != 'scalar' || $code->scalarTyp() != 'int') {
                            return new static::$responseClass(0, PhpXmlRpc::$xmlrpcerr['multicall_error'],
                                PhpXmlRpc::$xmlrpcstr['multicall_error'] . ": response element $i has invalid or no faultCode",
                                'xmlrpcvals', $result->httpResponse());
                        }
                        
                        $str = @$val['faultString'];
                        if (!$str || $str->kindOf() != 'scalar' || $str->scalarTyp() != 'string') {
                            return new static::$responseClass(0, PhpXmlRpc::$xmlrpcerr['multicall_error'],
                                PhpXmlRpc::$xmlrpcstr['multicall_error'] . ": response element $i has invalid or no faultString",
                                'xmlrpcvals', $result->httpResponse());
                        }
                        $response[] = new static::$responseClass(0, $code->scalarVal(), $str->scalarVal(), 'xmlrpcvals', $result->httpResponse());
                        break;
                    default:
                        return new static::$responseClass(0, PhpXmlRpc::$xmlrpcerr['multicall_error'],
                            PhpXmlRpc::$xmlrpcstr['multicall_error'] . ": response element $i is not an array or struct",
                            'xmlrpcvals', $result->httpResponse());
                }
            }
        }

        return $response;
    }

    

    



















    protected function sendPayloadHTTP10($req, $server, $port, $timeout = 0, $username = '', $password = '',
        $authType = 1, $proxyHost = '', $proxyPort = 0, $proxyUsername = '', $proxyPassword = '', $proxyAuthType = 1,
        $method = 'http')
    {
        $this->logDeprecation('Method ' . __METHOD__ . ' is deprecated');

        return $this->sendPayloadSocket($req, $server, $port, $timeout, $username, $password, $authType, null, null,
            null, null, $proxyHost, $proxyPort, $proxyUsername, $proxyPassword, $proxyAuthType, $method);
    }

    


























    protected function sendPayloadHTTPS($req, $server, $port, $timeout = 0, $username = '', $password = '',
        $authType = 1, $cert = '', $certPass = '', $caCert = '', $caCertDir = '', $proxyHost = '', $proxyPort = 0,
        $proxyUsername = '', $proxyPassword = '', $proxyAuthType = 1, $keepAlive = false, $key = '', $keyPass = '',
        $sslVersion = 0)
    {
        $this->logDeprecation('Method ' . __METHOD__ . ' is deprecated');

        return $this->sendPayloadCURL($req, $server, $port, $timeout, $username,
            $password, $authType, $cert, $certPass, $caCert, $caCertDir, $proxyHost, $proxyPort,
            $proxyUsername, $proxyPassword, $proxyAuthType, 'https', $keepAlive, $key, $keyPass, $sslVersion);
    }

    
























    protected function sendPayloadSocket($req, $server, $port, $timeout = 0, $username = '', $password = '',
        $authType = 1, $cert = '', $certPass = '', $caCert = '', $caCertDir = '', $proxyHost = '', $proxyPort = 0,
        $proxyUsername = '', $proxyPassword = '', $proxyAuthType = 1, $method = 'http', $key = '', $keyPass = '',
        $sslVersion = 0)
    {
        $this->logDeprecationUnlessCalledBy('send');

        return $this->sendViaSocket($req, $method, $server, $port, $this->path, array(
            'accepted_charset_encodings' => $this->accepted_charset_encodings,
            'accepted_compression' => $this->accepted_compression,
            'authtype' => $authType,
            'cacert' => $caCert,
            'cacertdir' => $caCertDir,
            'cert' => $cert,
            'certpass' => $certPass,
            'cookies' => $this->cookies,
            'debug' => $this->debug,
            'extracurlopts' => $this->extracurlopts,
            'extrasockopts' => $this->extrasockopts,
            'keepalive' => $this->keepalive,
            'key' => $key,
            'keypass' => $keyPass,
            'no_multicall' => $this->no_multicall,
            'password' => $password,
            'proxy' => $proxyHost,
            'proxy_authtype' => $proxyAuthType,
            'proxy_pass' => $proxyPassword,
            'proxyport' => $proxyPort,
            'proxy_user' => $proxyUsername,
            'request_charset_encoding' => $this->request_charset_encoding,
            'request_compression' => $this->request_compression,
            'return_type' => $this->return_type,
            'sslversion' => $sslVersion,
            'timeout' => $timeout,
            'username' => $username,
            'user_agent' => $this->user_agent,
            'use_curl' => $this->use_curl,
            'verifyhost' => $this->verifyhost,
            'verifypeer' => $this->verifypeer,
        ));
    }

    

























    protected function sendPayloadCURL($req, $server, $port, $timeout = 0, $username = '', $password = '',
        $authType = 1, $cert = '', $certPass = '', $caCert = '', $caCertDir = '', $proxyHost = '', $proxyPort = 0,
        $proxyUsername = '', $proxyPassword = '', $proxyAuthType = 1, $method = 'https', $keepAlive = false, $key = '',
        $keyPass = '', $sslVersion = 0)
    {
        $this->logDeprecationUnlessCalledBy('send');

        return $this->sendViaCURL($req, $method, $server, $port, $this->path, array(
            'accepted_charset_encodings' => $this->accepted_charset_encodings,
            'accepted_compression' => $this->accepted_compression,
            'authtype' => $authType,
            'cacert' => $caCert,
            'cacertdir' => $caCertDir,
            'cert' => $cert,
            'certpass' => $certPass,
            'cookies' => $this->cookies,
            'debug' => $this->debug,
            'extracurlopts' => $this->extracurlopts,
            'extrasockopts' => $this->extrasockopts,
            'keepalive' => $keepAlive,
            'key' => $key,
            'keypass' => $keyPass,
            'no_multicall' => $this->no_multicall,
            'password' => $password,
            'proxy' => $proxyHost,
            'proxy_authtype' => $proxyAuthType,
            'proxy_pass' => $proxyPassword,
            'proxyport' => $proxyPort,
            'proxy_user' => $proxyUsername,
            'request_charset_encoding' => $this->request_charset_encoding,
            'request_compression' => $this->request_compression,
            'return_type' => $this->return_type,
            'sslversion' => $sslVersion,
            'timeout' => $timeout,
            'username' => $username,
            'user_agent' => $this->user_agent,
            'use_curl' => $this->use_curl,
            'verifyhost' => $this->verifyhost,
            'verifypeer' => $this->verifypeer,
        ));
    }

    

























    protected function prepareCurlHandle($req, $server, $port, $timeout = 0, $username = '', $password = '',
         $authType = 1, $cert = '', $certPass = '', $caCert = '', $caCertDir = '', $proxyHost = '', $proxyPort = 0,
         $proxyUsername = '', $proxyPassword = '', $proxyAuthType = 1, $method = 'https', $keepAlive = false, $key = '',
         $keyPass = '', $sslVersion = 0)
    {
        $this->logDeprecationUnlessCalledBy('sendViaCURL');

        return $this->createCURLHandle($req, $method, $server, $port, $this->path, array(
            'accepted_charset_encodings' => $this->accepted_charset_encodings,
            'accepted_compression' => $this->accepted_compression,
            'authtype' => $authType,
            'cacert' => $caCert,
            'cacertdir' => $caCertDir,
            'cert' => $cert,
            'certpass' => $certPass,
            'cookies' => $this->cookies,
            'debug' => $this->debug,
            'extracurlopts' => $this->extracurlopts,
            'keepalive' => $keepAlive,
            'key' => $key,
            'keypass' => $keyPass,
            'no_multicall' => $this->no_multicall,
            'password' => $password,
            'proxy' => $proxyHost,
            'proxy_authtype' => $proxyAuthType,
            'proxy_pass' => $proxyPassword,
            'proxyport' => $proxyPort,
            'proxy_user' => $proxyUsername,
            'request_charset_encoding' => $this->request_charset_encoding,
            'request_compression' => $this->request_compression,
            'return_type' => $this->return_type,
            'sslversion' => $sslVersion,
            'timeout' => $timeout,
            'username' => $username,
            'user_agent' => $this->user_agent,
            'use_curl' => $this->use_curl,
            'verifyhost' => $this->verifyhost,
            'verifypeer' => $this->verifypeer,
        ));
    }

    
    public function &__get($name)
    {
        if (in_array($name, static::$options)) {
            $this->logDeprecation('Getting property Client::' . $name . ' is deprecated');
            return $this->$name;
        }

        switch ($name) {
            case 'errno':
            case 'errstr':
            case 'method':
            case 'server':
            case 'port':
            case 'path':
                $this->logDeprecation('Getting property Client::' . $name . ' is deprecated');
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
        if (in_array($name, static::$options)) {
            $this->logDeprecation('Setting property Client::' . $name . ' is deprecated');
            $this->$name = $value;
            return;
        }

        switch ($name) {
            case 'errno':
            case 'errstr':
            case 'method':
            case 'server':
            case 'port':
            case 'path':
                $this->logDeprecation('Setting property Client::' . $name . ' is deprecated');
                $this->$name = $value;
                return;
            default:
                
                $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);
                trigger_error('Undefined property via __set(): ' . $name . ' in ' . $trace[0]['file'] . ' on line ' . $trace[0]['line'], E_USER_WARNING);
        }
    }

    public function __isset($name)
    {
        if (in_array($name, static::$options)) {
            $this->logDeprecation('Checking property Client::' . $name . ' is deprecated');
            return isset($this->$name);
        }

        switch ($name) {
            case 'errno':
            case 'errstr':
            case 'method':
            case 'server':
            case 'port':
            case 'path':
                $this->logDeprecation('Checking property Client::' . $name . ' is deprecated');
                return isset($this->$name);
            default:
                return false;
        }
    }

    public function __unset($name)
    {
        if (in_array($name, static::$options)) {
            $this->logDeprecation('Unsetting property Client::' . $name . ' is deprecated');
            unset($this->$name);
            return;
        }

        switch ($name) {
            case 'errno':
            case 'errstr':
            case 'method':
            case 'server':
            case 'port':
            case 'path':
                $this->logDeprecation('Unsetting property Client::' . $name . ' is deprecated');
                unset($this->$name);
                return;
            default:
                
                $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);
                trigger_error('Undefined property via __unset(): ' . $name . ' in ' . $trace[0]['file'] . ' on line ' . $trace[0]['line'], E_USER_WARNING);
        }
    }
}
