<?php
namespace Api\V1\Rest\ApiQrcode;

use Bank\Service\SbpManager; 

class ApiQrcodeResourceFactory
{
    public function __invoke($services)
    {
        $sbpManager = $services->get(SbpManager::class);
        
        return new ApiQrcodeResource($sbpManager);
    }
}
