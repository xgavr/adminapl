<?php
namespace Api\V1\Rest\ApiAccountComitent;

use ApiMarketPlace\Service\ReportManager;
use Zp\Service\ZpManager;

class ApiAccountComitentResourceFactory
{
    public function __invoke($services)
    {
        $entityManager = $services->get('doctrine.entitymanager.orm_default');
        $reportManager = $services->get(ReportManager::class);
        $zpManager = $services->get(ZpManager::class);
        
        return new ApiAccountComitentResource($entityManager, $reportManager,
                $zpManager);
    }
}
