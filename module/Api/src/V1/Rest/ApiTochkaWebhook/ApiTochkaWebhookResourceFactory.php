<?php
namespace Api\V1\Rest\ApiTochkaWebhook;

use Bankapi\Service\Tochka\Webhook;

class ApiTochkaWebhookResourceFactory
{
    public function __invoke($services)
    {

        $webhookManager = $services->get(Webhook::class);
        
        return new ApiTochkaWebhookResource($webhookManager);
    }
}
