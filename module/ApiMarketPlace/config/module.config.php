<?php
namespace ApiMarketPlace;

use Laminas\Router\Http\Literal;
use Laminas\Router\Http\Segment;
use Laminas\ServiceManager\Factory\InvokableFactory;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;

return [
    'router' => [
        'routes' => [
            'market-place' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/market-place[/:action[/:id]]',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[0-9]*',
                    ],
                    'defaults' => [
                        'controller'    => Controller\IndexController::class,
                        'action'        => 'index',
                    ],
                ],
            ],
        ],
    ],
    'access_filter' => [
        'controllers' => [
            Controller\IndexController::class => [
                // Allow access to authenticated users.
                ['actions' => '*', 'allow' => '@']
            ],
        ],
    ],    
    'controllers' => [
        'factories' => [
            Controller\IndexController::class => Controller\Factory\IndexControllerFactory::class,
        ],
    ],
    'service_manager' => [
        'factories' => [
            Service\Request::class => Service\Factory\RequestFactory::class,
            Service\Update::class => Service\Factory\UpdateFactory::class,
            Service\SberMarket::class => Service\Factory\SberMarketFactory::class,
        ],
    ],    
    'view_manager' => [
        'template_path_stack' => [
            'ApiMarketPlace' => __DIR__ . '/../view',
        ],
    ],
    'doctrine' => [
        'driver' => [
            __NAMESPACE__ . '_driver' => [
                'class' => AnnotationDriver::class,
                'cache' => 'array',
                'paths' => [__DIR__ . '/../src/Entity']
            ],
            'orm_default' => [
                'drivers' => [
                    __NAMESPACE__ . '\Entity' => __NAMESPACE__ . '_driver'
                ]
            ]
        ]
    ],    
];
