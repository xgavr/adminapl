<?php
namespace Api\V1\Rest\ApiSuppliersPrices;

class ApiSuppliersPricesResourceFactory
{
    public function __invoke($services)
    {
        $entityManager = $services->get('doctrine.entitymanager.orm_default');
        
        return new ApiSuppliersPricesResource($entityManager);
    }
}
