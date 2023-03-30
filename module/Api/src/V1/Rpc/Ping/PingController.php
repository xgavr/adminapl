<?php
namespace Api\V1\Rpc\Ping;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\ApiTools\ContentNegotiation\ViewModel;
use Laminas\ApiTools\ContentNegotiation\JsonModel;

class PingController extends AbstractActionController
{
    public function pingAction()
    {
        return new JsonModel([
            'ack' => time()
        ]);
    }
}
