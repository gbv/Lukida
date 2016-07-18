<?php
/**
 * A Simple collection of factory methods for core services of lukida
 *
 * @author Richard Großer <richard.grosser@thulb.uni-jena.de>
 */
class ServiceFactory {
    
    protected $CI;

    public function __construct() {
        $this->CI =& get_instance();
    }
    
    /**
     * Creates an instance of the configured ils service
     * 
     * @return ILSService|boolean
     */
    public function createILSService()
    {
        $type = $this->getILSType();
        $setupMethodName =  'configure' . $type . 'Service';
        
        if (method_exists($this, $setupMethodName)) {
            // load the ils service and create an instance
            $this->CI->load->library('ils/' . $type . 'Service', '', 'ilsService');
            
            // the ils service requires an ils user => load an instance too
            $this->CI->load->library('/ils/User', '', 'ilsUser');
            
            // configure the service
            $this->$setupMethodName();
            return true;
        }
        
        return false;
    }
    
    /**
     * Creates an instance of the requested or the default search index service
     *  
     * @param String $service
     */
    public function createSearchService($service = '')
    {
        // detect service to load
        $service = in_array($service, $this->getAvailableSearchServices()) ? $service : $this->getDefaultSearchService();
        
        // load the service, but necessary abstract classes first
        if ($this->CI->config->item('type', $service) === 'solr' 
            || $this->CI->config->item('type', 'index_system') === 'solr'
        ) {
            $this->CI->load->abstract_library('search/AbstractSolrSearchService');
        }
        $this->CI->load->library('search/' . ucfirst($service), '', 'searchService');
        
        // configure the service
        $this->configureSearchService($service);
        $_SESSION["interfaces"]["index_system"] = 1;
    }
    
    /**
     * Gets the configured ils type from the general.ini, converts it to
     * CamelCase and returns it
     * 
     * @return String
     */
    protected function getILSType()
    {
        return str_replace(' ', '', ucwords(str_replace('_', ' ', $this->CI->config->item('type', 'lbs'))));
    }

    /**
     * Sets the correct configuration for an already instantiated PaiaDaiaService
     * 
     * @return PaiaDaiaService|boolean
     */
    protected function configurePaiaDaiaService()
    {
        $config = $this->getPaiaDaiaConfiguration();
        if ($config 
            && $this->CI->ilsService instanceof PaiaDaiaService
            && $this->CI->ilsUser instanceof User
        ) {
            $paiaDaiaService = $this->CI->ilsService;
            
            $_SESSION["interfaces"]["lbs"] = 1;
            return $paiaDaiaService->setIsil($config['isil'])
                                   ->setPaia($config['paia'])
                                   ->setDaia($config['daia'])
                                   ->setUser($this->CI->ilsUser);
        }
        
        $_SESSION["interfaces"]["lbs"] = 0;
        return false;
    }
    
    /**
     * Get an array with the configuration values for paia/daia
     * 
     * @return Array|boolean
     */
    protected function getPaiaDaiaConfiguration()
    {
        $available = !(!is_null($this->CI->config->item('available', 'lbs')) && $this->CI->config->item('available', 'lbs') != "1");
        
        if ($available && $this->CI->config->item('isil', 'general') && $this->CI->config->item('paia', 'lbs') && $this->CI->config->item('daia', 'lbs')) {
            return [
                'isil'  => $this->CI->config->item('isil', 'general'),
                'paia'  => $this->CI->config->item('paia', 'lbs') . '/' . $this->CI->config->item('isil', 'general'),
                'daia'  => $this->CI->config->item('daia', 'lbs') . '/isil/' . $this->CI->config->item('isil', 'general') . '/'
            ];
        }
        
        return false;
    }
    
    protected function getDefaultSearchService()
    {
        $configDefault = $this->CI->config->item('default', 'index_system');
        $default = ($configDefault) ? $configDefault : 'findex';    // backwards compatibility with old config structure
        return $default;
    }
    
    protected function getAvailableSearchServices()
    {
        $configList = $this->CI->config->item('available', 'index_system');
        $availableIndexes = ($configList) ?  explode(',', $configList) : ['findex'];    // backwards compatibility
        return $availableIndexes;
    }
    
    protected function configureSearchService($service)
    {
        $type = ($this->CI->config->item('type', $service)) ? ucfirst($this->CI->config->item('type', $service)) : 'solr';
        $methodName = 'configure' . ucfirst($type) . 'SearchService';
        return $this->$methodName($service);
    }
    
    protected function configureSolrSearchService($service)
    {
        if (is_subclass_of($this->CI->searchService, 'AbstractSolrSearchService')) {
            $this->CI->searchService->setConfig($this->getSolrConfiguration($service));
        }
    }
    
    protected function getSolrConfiguration($service)
    {
        return array(
            'hostname'  => ($this->CI->config->item('host', $service)) ? $this->CI->config->item('host', $service) : $this->CI->config->item('host', 'index_system'),
            'port'      => ($this->CI->config->item('port', $service)) ? $this->CI->config->item('port', $service) : $this->CI->config->item('port', 'index_system'),
            'path'      => ($this->CI->config->item('path', $service)) ? $this->CI->config->item('path', $service) : $this->CI->config->item('path', 'index_system'),
            'wt'        => ($this->CI->config->item('wt', $service)) ? $this->CI->config->item('wt', $service) : 'json'
        );
    }
}
