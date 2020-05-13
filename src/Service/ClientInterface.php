<?php

namespace LaFranceQuiProduit\ElasticsearchClientBundle\Service;

/**
 * ClientInterface
 *
 * Interface of client of Elasticsearch cluster.
 */
interface ClientInterface
{
    const TAG = 'elasticsearch-client.service';    

    /**
     * Index a list of documents.
     *
     * @param array  $documents A list of documents to index.
     * @param string $indexName The name of the index to use.
     */
    public function indexDocuments(array $documents, string $indexName = null);

    /**
     * Increments the value of a field.
     *
     * @param string $indexName      The name of the index to use.
     * @param string $documentId     ID of document to update.
     * @param string $field          Field of document to update.
     * @param int    $incrementCount Value to increment.
     */
    public function increment(string $indexName, string $documentId, string $field, int $incrementCount);

    /**
     * Search query.
     *
     * @param string      $indexName    The name of ES index
     * @param array       $query        Query
     * @param array       $sort         Sort query
     * @param int         $size         Number of results to get
     * @param string|null $hydrateClass Class to use to hydrate results
     *
     * @return array a list of results from ES as array or a list of entities
     */
    public function search(string $indexName, array $query, array $sort = array(), int $size = null, string $hydrateClass = null);

    /**
     * Hydrates hits from ES response to entities of a class.
     *
     * @param array  $hits         The hits got from ES
     * @param string $hydrateClass The full path of the class to use for hydratation
     *
     * @return array a list of entities
     */
    private function hydrateHitsToEntities(array $hits, string $hydrateClass);
}

