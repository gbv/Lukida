<?php

/**
 * Interface for all search indexes that deliver a result for a search term
 *
 * @author Richard Großer <richard.grosser@thulb.uni-jena.de>
 */
interface SearchService {
    /**
     * Run a search on the index system
     * 
     * @param String $term
     * @param int $page     
     * @param boolean $useFacets
     */
    public function search($term, $page, $useFacets);
    
    /**
     * Get an array of ppn of similar publications for a single ppn ("pica production number")
     * 
     * @param string $ppn
     */
    public function getSimilarPublications($ppn);
}
