<?php
namespace ApiReactor\V1\Rest\ApiReactorClients;

class ApiReactorClientsResourceFactory
{
    public function __invoke($services)
    {
        $entityManager = $services->get('doctrine.entitymanager.orm_default');
        
        return new ApiReactorClientsResource($entityManager);
    }
}
