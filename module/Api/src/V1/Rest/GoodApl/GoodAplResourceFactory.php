<?php
namespace Api\V1\Rest\GoodApl;

use Application\Service\GoodsManager;

class GoodAplResourceFactory
{
    public function __invoke($services)
    {
        $entityManager = $services->get('doctrine.entitymanager.orm_default');
        $goodManager = $services->get(GoodsManager::class);
        
        return new GoodAplResource($entityManager, $goodManager);
    }
}
