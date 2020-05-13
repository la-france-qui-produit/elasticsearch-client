<?php

namespace LaFranceQuiProduit\ElasticsearchClientBundle\Entity;

/**
 * Standard Elasticsearch entity.
 */
class Entity implements EntityInterface
{
    protected $id = null;

    protected $indexName = null;

    public function __construct(?string $indexName = null)
    {
        if ($indexName !== null) {
            $this->indexName = $indexName;
        }
    }

    public function setId(?string $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function setIndexName(?string $indexName): self
    {
        $this->indexName = $indexName;

        return $this;
    }

    public function getIndexName(): ?string
    {
        return $this->indexName;
    }
}

