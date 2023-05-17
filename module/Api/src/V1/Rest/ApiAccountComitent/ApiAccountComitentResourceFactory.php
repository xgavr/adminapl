<?php
namespace Api\V1\Rest\ApiAccountComitent;

class ApiAccountComitentResourceFactory
{
    public function __invoke($services)
    {
        $entityManager = $services->get('doctrine.entitymanager.orm_default');
        
        return new ApiAccountComitentResource($entityManager);
    }
}
