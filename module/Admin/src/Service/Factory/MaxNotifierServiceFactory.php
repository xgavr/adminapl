<?php

declare(strict_types=1);

namespace Admin\Service\Factory;

use Admin\Service\MaxNotifierService;
use Laminas\Http\Client;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class MaxNotifierServiceFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): MaxNotifierService
    {
        $config = $container->get('Config')['max_messenger'];

        $httpClient = new Client();
        $httpClient->setOptions([
            'adapter' => Client\Adapter\Curl::class,
            'timeout' => 10,
        ]);

        return new MaxNotifierService(
            $config['api_url'],
            $config['token'],
            (int) $config['chat_id'],
            (int) $config['admin_id'],
            $httpClient
        );
    }
}
