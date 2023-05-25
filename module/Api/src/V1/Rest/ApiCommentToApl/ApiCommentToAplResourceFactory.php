<?php
namespace Api\V1\Rest\ApiCommentToApl;

use Application\Service\CommentManager;
use Admin\Service\AplOrderService;

class ApiCommentToAplResourceFactory
{
    public function __invoke($services)
    {
        $entityManager = $services->get('doctrine.entitymanager.orm_default');
        $commentManager = $services->get(CommentManager::class);
        $aplOrderService = $services->get(AplOrderService::class);

        return new ApiCommentToAplResource($entityManager, $commentManager, $aplOrderService);
    }
}
