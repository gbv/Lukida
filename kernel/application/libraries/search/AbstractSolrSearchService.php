<?php

/**
 * Abstract class for solr search indexes
 *
 * @author  Richard Großer <richard.grosser@thulb.uni-jena.de>
 * 
 * @todo    put as many general logic as possible in here, to share code between different solr search indexes
 */
abstract class AbstractSolrSearchService
{
    protected $config;

    public function setConfig($config)
    {
        $this->config = $config;
        return $this;
    }
}
