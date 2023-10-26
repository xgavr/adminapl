<?php
namespace Api\V1\Rest\ApiClientInfo;

use Application\Service\OrderManager;
use Application\Service\GoodsManager;
use Application\Service\ClientManager;

class ApiClientInfoResourceFactory
{
    public function __invoke($services)
    {
        $entityManager = $services->get('doctrine.entitymanager.orm_default');
        $orderManager = $services->get(OrderManager::class);
        $goodManager = $services->get(GoodsManager::class);
        $clientManager = $services->get(ClientManager::class);

        return new ApiClientInfoResource($entityManager, $orderManager, $goodManager, $clientManager);
    }
}
