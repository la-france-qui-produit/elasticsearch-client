<?php

namespace ProduitsDeFrance\ElasticsearchClientBundle\Service;

# use App\Exception\Client\ElasticsearchClientException;

use Elasticsearch\ClientBuilder;
use Elasticsearch\Common\Exceptions\ElasticsearchException;
use Psr\Log\LoggerInterface;

/**
 * Client
 *
 * Client of Elasticsearch cluster.
 */
class Client implements ClientInterface
{
    /** @var \Elasticsearch\ClientBuilder The Elasticsearch client. */
    private $client;

    private $logger;

    /**
     * Constructor.
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
        $this->client = ClientBuilder::create()
            ->setHosts([getenv('ES_HOST')])
            ->build();
    }

    /**
     * Index a list of documents.
     *
     * @param array  $documents A list of documents to index.
     * @param string $indexName The name of the index to use.
     */
    public function indexDocuments(array $documents, string $indexName = null): void
    {
        $bulkData = [];
        foreach ($documents as $document) {
            $rawDoc = $document->toArray();

            if ($indexName !== null) {
                $indexLine = [
                    '_index' => $indexName
                ];
            } else {
                $indexLine = [
                    '_index' => $rawDoc['indexName']
                ];
            }
            unset($rawDoc['indexName']);

            if (!empty($rawDoc['id'])) {
                $indexLine['_id'] = $rawDoc['id'];
            }
            unset($rawDoc['id']);

            $bulkData[] = [
                'index' => $indexLine
            ];
            $bulkData[] = $rawDoc;
        }

        try {
            $this->client->bulk([
                'body'   => $bulkData,
                'client' => [
                    'timeout'         => getenv('ES_TIMEOUT'),
                    'connect_timeout' => getenv('ES_CONNECT_TIMEOUT')
                ]
            ]);
        } catch (ElasticsearchException $e) {
            #throw new ElasticsearchClientException('Elasticsearch error while indexing new docs in index ' . $indexName, $e);
            throw new \RuntimeException('Elasticsearch error while indexing new docs in index ' . $indexName . ': ' . $e->getMessage());
        }
    }

    /**
     * Increments the value of a field.
     *
     * @param string $indexName      The name of the index to use.
     * @param string $documentId     ID of document to update.
     * @param string $field          Field of document to update.
     * @param int    $incrementCount Value to increment.
     */
    public function increment(string $indexName, string $documentId, string $field, int $incrementCount): void
    {
        $params = [
            'index' => $indexName,
            'id'    => $documentId,
            'body'  => [
                'script' => [
                    'source' => 'ctx._source.' . $field . ' += ' . $incrementCount
                ]
            ],
            'client' => [
                'timeout'         => getenv('ES_TIMEOUT'),
                'connect_timeout' => getenv('ES_CONNECT_TIMEOUT')
            ]
        ];

        try {
            $this->client->update($params);
        } catch (ElasticsearchException $e) {
            #throw new ElasticsearchClientException('Elasticsearch error while incrementing field ' . $field . ' of document ' . $documentId . ' in index ' . $indexName, $e);
            throw new \RuntimeException('Elasticsearch error while incrementing field ' . $field . ' of document ' . $documentId . ' in index ' . $indexName . ': ' . $e->getMessage());
        }
    }

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
    public function search(string $indexName, array $query, array $sort = array(), int $size = null, string $hydrateClass = null): array
    {
        $params = [
            'index' => $indexName,
            'body'  => [
                'query' => $query,
                "sort"  => $sort
            ],
            'client' => [
                'timeout'         => getenv('ES_TIMEOUT'),
                'connect_timeout' => getenv('ES_CONNECT_TIMEOUT')
            ]
        ];

        if ($size !== null) {
            $params['body']['size'] = $size;
        }

        try {
            $response = $this->client->search($params);

            if (isset($response['hits']) && isset($response['hits']['hits'])) {
                if ($hydrateClass === null) {
                    return $response['hits']['hits'];
                }

                return $this->hydrateHitsToEntities($response['hits']['hits'], $hydrateClass);
            } else {
                return [];
            }
        } catch (ElasticsearchException $e) {
            #throw new ElasticsearchClientException('Elasticsearch error while searching in index ' . $indexName, $e);
            throw new \RuntimeException('Elasticsearch error while searching in index ' . $indexName . ': ' . $e->getMessage());
        }
    }

    /**
     * Hydrates hits from ES response to entities of a class.
     *
     * @param array  $hits         The hits got from ES
     * @param string $hydrateClass The full path of the class to use for hydratation
     *
     * @return array a list of entities
     */
    private function hydrateHitsToEntities(array $hits, string $hydrateClass): array
    {
        $instances = [];
        foreach ($hits as $hit) {
            $instance = new $hydrateClass();

            $classParams = $instance->toArray();
            foreach ($classParams as $param => $paramValue) {
                if ($param === 'id') {
                    $instance->setId($hit['_id']);
                } else if ($param === 'indexName') {
                    $instance->setIndexName($hit['_index']);
                } else {
                    $instance->{ 'set' . ucfirst($param) }($hit['_source'][$param]);
                }
            }

            $instances[] = $instance;
        }

        return $instances;
    }
}

