<?php
namespace Fin;

use Laminas\Router\Http\Literal;
use Laminas\Router\Http\Segment;
use Laminas\ServiceManager\Factory\InvokableFactory;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;

return [
    'router' => [
        'routes' => [
            'fin' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/fin[/:action[/:id]]',
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
            'balance' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/balance[/:action[/:id]]',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[0-9]*',
                    ],
                    'defaults' => [
                        'controller'    => Controller\BalanceController::class,
                        'action'        => 'index',
                    ],
                ],
            ],
            'dds' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/dds[/:action[/:id]]',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[0-9]*',
                    ],
                    'defaults' => [
                        'controller'    => Controller\DdsController::class,
                        'action'        => 'index',
                    ],
                ],
            ],
            'opu' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/opu[/:action[/:id]]',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[0-9]*',
                    ],
                    'defaults' => [
                        'controller'    => Controller\OpuController::class,
                        'action'        => 'index',
                    ],
                ],
            ],
        ],
    ],
    'access_filter' => [
        'controllers' => [
            Controller\BalanceController::class => [
                // Allow access to authenticated users.
                ['actions' => '*', 'allow' => '@']
            ],
            Controller\DdsController::class => [
                // Allow access to authenticated users.
                ['actions' => '*', 'allow' => '@']
            ],
            Controller\IndexController::class => [
                // Allow access to authenticated users.
                ['actions' => '*', 'allow' => '@']
            ],
            Controller\OpuController::class => [
                // Allow access to authenticated users.
                ['actions' => '*', 'allow' => '@']
            ],
        ],
    ],    
    'controllers' => [
        'factories' => [
            Controller\BalanceController::class => Controller\Factory\BalanceControllerFactory::class,
            Controller\DdsController::class => Controller\Factory\DdsControllerFactory::class,
            Controller\IndexController::class => Controller\Factory\IndexControllerFactory::class,
            Controller\OpuController::class => Controller\Factory\OpuControllerFactory::class,
        ],
    ],
    'service_manager' => [
        'factories' => [
            Service\BalanceManager::class => Service\Factory\BalanceManagerFactory::class,
            Service\DdsManager::class => Service\Factory\DdsManagerFactory::class,
            Service\FinManager::class => Service\Factory\FinManagerFactory::class,
        ],
    ],    
    'view_manager' => [
        'template_path_stack' => [
            'Fin' => __DIR__ . '/../view',
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
