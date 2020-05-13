<?php

namespace LaFranceQuiProduit\ElasticsearchClientBundle\Entity;

/**
 * Utils for Elasticsearch document.
 */
trait EntityUtils
{
    public function __toArray()
    {
        return call_user_func('get_object_vars', $this);
    }
}

