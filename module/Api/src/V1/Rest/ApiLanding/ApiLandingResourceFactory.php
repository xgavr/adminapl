<?php
namespace Api\V1\Rest\ApiLanding;

use Application\Service\OrderManager;
use Application\Service\ClientManager;

class ApiLandingResourceFactory
{
    public function __invoke($services)
    {
        $entityManager = $services->get('doctrine.entitymanager.orm_default');
        $orderManager = $services->get(OrderManager::class);
        $clientManager = $services->get(ClientManager::class);

        return new ApiLandingResource($entityManager, $orderManager, $clientManager);
    }
}
