<?php

/**
 * An Interface for services which connect to integrated library systems
 * 
 * @author Richard Großer <richard.grosser@thulb.uni-jena.de>
 */

interface ILSService
{   
    /**
     * login as $user
     * 
     * @param String $user
     * @param String $password
     */
    public function login($user, $password);
    
    /**
     * log the current user out
     */
    public function logout();
    
    /**
     * get data of the currently logged in user
     */
    public function userdata();
    
    /**
     * Prolong loan for an item
     * 
     * @param String $uri
     */
    public function renew($uri);
    
    /**
     * Request an item for reservation or delivery. 
     * 
     * @param String $uri
     */
    public function request($uri);
    
    /**
     * Cancel the request for an item. 
     * 
     * @param String $uri
     */
    public function cancel($uri);
    
    /**
     * Get document information for ppn ("pica production number")
     * 
     * @param String $ppn
     */
    public function document($ppn);
}
