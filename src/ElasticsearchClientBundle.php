<?php

namespace ProduitsDeFrance\ElasticsearchClientBundle;

use ProduitsDeFrance\ElasticsearchClientBundle\Service\ClientInterface;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class ElasticsearchClientBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);
        
        $container->registerForAutoconfiguration(ClientInterface::class)->addTag(ClientInterface::TAG);
    }
}
