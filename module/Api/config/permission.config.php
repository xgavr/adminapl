<?php

namespace Laminas\ApiTools\Admin;

return [
    'access_filter' => [
        'controllers' => [
            Controller\App::class => [
                ['actions' => '*', 'allow' => '@']
            ],
            Controller\ApiToolsVersionController::class => [
                ['actions' => '*', 'allow' => '@']
            ],
            Controller\Dashboard::class => [
                ['actions' => '*', 'allow' => '@']
            ],
            Controller\SettingsDashboard::class => [
                ['actions' => '*', 'allow' => '@']
            ],
            Controller\Strategy::class => [
                ['actions' => '*', 'allow' => '@']
            ],
            Controller\CacheEnabled::class => [
                ['actions' => '*', 'allow' => '@']
            ],
            Controller\FsPermissions::class => [
                ['actions' => '*', 'allow' => '@']
            ],
            Controller\Config::class => [
                ['actions' => '*', 'allow' => '@']
            ],
            Controller\ModuleConfig::class => [
                ['actions' => '*', 'allow' => '@']
            ],
            Controller\Source::class => [
                ['actions' => '*', 'allow' => '@']
            ],
            Controller\Filters::class => [
                ['actions' => '*', 'allow' => '@']
            ],
            Controller\Hydrators::class => [
                ['actions' => '*', 'allow' => '@']
            ],
            Controller\Validators::class => [
                ['actions' => '*', 'allow' => '@']
            ],
            Controller\ModuleCreation::class => [
                ['actions' => '*', 'allow' => '@']
            ],
            Controller\Versioning::class => [
                ['actions' => '*', 'allow' => '@']
            ],
            Controller\Module::class => [
                ['actions' => '*', 'allow' => '@']
            ],
            Controller\Authentication::class => [
                ['actions' => '*', 'allow' => '@']
            ],
            Controller\Authorization::class => [
                ['actions' => '*', 'allow' => '@']
            ],
            Controller\RpcService::class => [
                ['actions' => '*', 'allow' => '@']
            ],
            Controller\InputFilter::class => [
                ['actions' => '*', 'allow' => '@']
            ],
            Controller\Documentation::class => [
                ['actions' => '*', 'allow' => '@']
            ],
            Controller\RestService::class => [
                ['actions' => '*', 'allow' => '@']
            ],
            Controller\DbAutodiscovery::class => [
                ['actions' => '*', 'allow' => '@']
            ],
            Controller\OAuth2Authentication::class => [
                ['actions' => '*', 'allow' => '@']
            ],
            Controller\HttpBasicAuthentication::class => [
                ['actions' => '*', 'allow' => '@']
            ],
            Controller\HttpDigestAuthentication::class => [
                ['actions' => '*', 'allow' => '@']
            ],
            Controller\DbAdapter::class => [
                ['actions' => '*', 'allow' => '@']
            ],
            Controller\DoctrineAdapter::class => [
                ['actions' => '*', 'allow' => '@']
            ],
            Controller\ContentNegotiation::class => [
                ['actions' => '*', 'allow' => '@']
            ],
            Controller\Package::class => [
                ['actions' => '*', 'allow' => '@']
            ],
            Controller\AuthenticationType::class => [
                ['actions' => '*', 'allow' => '@']
            ],
        ]
    ],
];
