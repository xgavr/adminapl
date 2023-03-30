<?php
namespace Api\V1\Rest\Good;

use Application\Service\GoodsManager;

class GoodResourceFactory
{
    public function __invoke($services)
    {
        $entityManager = $services->get('doctrine.entitymanager.orm_default');
        $goodManager = $services->get(GoodsManager::class);
        
        return new GoodResource($entityManager, $goodManager);
    }
}
