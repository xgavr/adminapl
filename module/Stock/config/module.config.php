<?php
namespace Stock;

use Laminas\Router\Http\Segment;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;

return [
    'router' => [
        'routes' => [
            'stock' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/stock[/:action[/:id]]',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[0-9]*',
                    ],
                    'defaults' => [
                        'controller'    => Controller\IndexController::class,
                        'action'        => 'index',
                    ],
                ],
                'may_terminate' => true,
                'child_routes' => [
                    // You can place additional routes that match under the
                    // route defined above here.
                ],
            ],    
            'ot' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/ot[/:action[/:id]]',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[0-9]*',
                    ],
                    'defaults' => [
                        'controller'    => Controller\OtController::class,
                        'action'        => 'index',
                    ],
                ],
                'may_terminate' => true,
                'child_routes' => [
                    // You can place additional routes that match under the
                    // route defined above here.
                ],
            ],    
            'pt' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/pt[/:action[/:id]]',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[0-9]*',
                    ],
                    'defaults' => [
                        'controller'    => Controller\PtController::class,
                        'action'        => 'index',
                    ],
                ],
                'may_terminate' => true,
                'child_routes' => [
                    // You can place additional routes that match under the
                    // route defined above here.
                ],
            ],    
            'ptu' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/ptu[/:action[/:id]]',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[0-9]*',
                    ],
                    'defaults' => [
                        'controller'    => Controller\PtuController::class,
                        'action'        => 'index',
                    ],
                ],
                'may_terminate' => true,
                'child_routes' => [
                    // You can place additional routes that match under the
                    // route defined above here.
                ],
            ],    
            'st' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/st[/:action[/:id]]',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[0-9]*',
                    ],
                    'defaults' => [
                        'controller'    => Controller\StController::class,
                        'action'        => 'index',
                    ],
                ],
                'may_terminate' => true,
                'child_routes' => [
                    // You can place additional routes that match under the
                    // route defined above here.
                ],
            ],    
            'vtp' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/vtp[/:action[/:id]]',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[0-9]*',
                    ],
                    'defaults' => [
                        'controller'    => Controller\VtpController::class,
                        'action'        => 'index',
                    ],
                ],
                'may_terminate' => true,
                'child_routes' => [
                    // You can place additional routes that match under the
                    // route defined above here.
                ],
            ],    
            'vt' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/vt[/:action[/:id]]',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[0-9]*',
                    ],
                    'defaults' => [
                        'controller'    => Controller\VtController::class,
                        'action'        => 'index',
                    ],
                ],
                'may_terminate' => true,
                'child_routes' => [
                    // You can place additional routes that match under the
                    // route defined above here.
                ],
            ],    
        ],
    ],
    'access_filter' => [
        'controllers' => [
            Controller\IndexController::class => [
                ['actions' => '*', 'allow' => '@'],
            ],
            Controller\OtController::class => [
                ['actions' => '*', 'allow' => '@'],
            ],
            Controller\PtController::class => [
                ['actions' => '*', 'allow' => '@'],
            ],
            Controller\PtuController::class => [
                ['actions' => '*', 'allow' => '@'],
            ],
            Controller\StController::class => [
                ['actions' => '*', 'allow' => '@'],
            ],
            Controller\VtpController::class => [
                ['actions' => '*', 'allow' => '@'],
            ],
            Controller\VtController::class => [
                ['actions' => '*', 'allow' => '@'],
            ],
        ],
    ],    
    'controllers' => [
        'factories' => [
            Controller\IndexController::class => Controller\Factory\IndexControllerFactory::class,
            Controller\OtController::class => Controller\Factory\OtControllerFactory::class,
            Controller\PtController::class => Controller\Factory\PtControllerFactory::class,
            Controller\PtuController::class => Controller\Factory\PtuControllerFactory::class,
            Controller\StController::class => Controller\Factory\StControllerFactory::class,
            Controller\VtController::class => Controller\Factory\VtControllerFactory::class,
            Controller\VtpController::class => Controller\Factory\VtpControllerFactory::class,
        ],
    ],
    'service_manager' => [
        'factories' => [
            Service\OtManager::class => Service\Factory\OtManagerFactory::class,
            Service\PtManager::class => Service\Factory\PtManagerFactory::class,
            Service\PtuManager::class => Service\Factory\PtuManagerFactory::class,
            Service\StManager::class => Service\Factory\StManagerFactory::class,
            Service\VtManager::class => Service\Factory\VtManagerFactory::class,
            Service\VtpManager::class => Service\Factory\VtpManagerFactory::class,
        ],
    ],        
    'view_manager' => [
        'template_path_stack' => [
            'Stock' => __DIR__ . '/../view',
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
