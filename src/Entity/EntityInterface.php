<?php

namespace LaFranceQuiProduit\ElasticsearchClientBundle\Entity;

/**
 * Interface of standard Elasticsearch entity.
 */
interface EntityInterface
{
    public function setId(?string $id);

    public function getId();

    public function setIndexName(?string $indexName);

    public function getIndexName();
}

