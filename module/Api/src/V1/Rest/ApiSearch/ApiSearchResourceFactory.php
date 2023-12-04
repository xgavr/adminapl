<?php
namespace Api\V1\Rest\ApiSearch;

use Search\Service\SearchManager;

class ApiSearchResourceFactory
{
    public function __invoke($services)
    {
        $entityManager = $services->get('doctrine.entitymanager.orm_default');
        $searchManager = $services->get(SearchManager::class);

        return new ApiSearchResource($entityManager, $searchManager);
    }
}
