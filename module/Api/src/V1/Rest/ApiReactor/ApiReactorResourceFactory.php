<?php
namespace Api\V1\Rest\ApiReactor;

class ApiReactorResourceFactory
{
    public function __invoke($services)
    {
        $entityManager = $services->get('doctrine.entitymanager.orm_default');
        
        return new ApiReactorResource($entityManager);
    }
}
