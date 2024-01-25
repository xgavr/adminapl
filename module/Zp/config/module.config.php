<?php
namespace Zp;

use Laminas\Router\Http\Literal;
use Laminas\Router\Http\Segment;
use Laminas\ServiceManager\Factory\InvokableFactory;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;

return [
    'router' => [
        'routes' => [
            'zp' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/zp[/:action[/:id]]',
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
            'accrual' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/accrual[/:action[/:id]]',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[0-9]*',
                    ],
                    'defaults' => [
                        'controller'    => Controller\AccrualController::class,
                        'action'        => 'index',
                    ],
                ],
            ],
            'personal' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/personal[/:action[/:id]]',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[0-9]*',
                    ],
                    'defaults' => [
                        'controller'    => Controller\PersonalController::class,
                        'action'        => 'index',
                    ],
                ],
            ],
            'position' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/position[/:action[/:id]]',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[0-9]*',
                    ],
                    'defaults' => [
                        'controller'    => Controller\PositionController::class,
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
            Controller\AccrualController::class => [
                // Allow access to authenticated users.
                ['actions' => '*', 'allow' => '@']
            ],
            Controller\PersonalController::class => [
                // Allow access to authenticated users.
                ['actions' => '*', 'allow' => '@']
            ],
            Controller\PositionController::class => [
                // Allow access to authenticated users.
                ['actions' => '*', 'allow' => '@']
            ],
        ],
    ],    
    'controllers' => [
        'factories' => [
            Controller\AccrualController::class => Controller\Factory\AccrualControllerFactory::class,
            Controller\IndexController::class => Controller\Factory\IndexControllerFactory::class,
            Controller\PersonalController::class => Controller\Factory\PersonalControllerFactory::class,
            Controller\PositionController::class => Controller\Factory\PositionControllerFactory::class,
        ],
    ],
    'service_manager' => [
        'factories' => [
            Service\ZpManager::class => Service\Factory\ZpManagerFactory::class,
        ],
    ],    
    'view_manager' => [
        'template_path_stack' => [
            'Zp' => __DIR__ . '/../view',
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
