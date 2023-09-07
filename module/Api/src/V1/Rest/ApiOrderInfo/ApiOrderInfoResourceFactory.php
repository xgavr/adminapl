<?php
namespace Api\V1\Rest\ApiOrderInfo;

use Application\Service\OrderManager;
use Application\Service\GoodsManager;
use Application\Service\SupplierOrderManager;

class ApiOrderInfoResourceFactory
{
    public function __invoke($services)
    {
        $entityManager = $services->get('doctrine.entitymanager.orm_default');
        $orderManager = $services->get(OrderManager::class);
        $goodManager = $services->get(GoodsManager::class);
        $supplierOrderManager = $services->get(SupplierOrderManager::class);

        return new ApiOrderInfoResource($entityManager, $orderManager, $goodManager,
                $supplierOrderManager);
    }
}
