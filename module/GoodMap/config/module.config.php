<?php
namespace GoodMap;

use Laminas\ServiceManager\Factory\InvokableFactory;
use Laminas\Router\Http\Literal;
use Laminas\Router\Http\Segment;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;

return [
    'router' => [
        'routes' => [
            'good-map' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/good-map[/:action[/:id]]',
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
            'fold' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/fold[/:action[/:id]]',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[0-9]*',
                    ],
                    'defaults' => [
                        'controller'    => Controller\FoldController::class,
                        'action'        => 'index',
                    ],
                ],
            ],
        ],
    ],    
    'controllers' => [
        'factories' => [
            Controller\IndexController::class => Controller\Factory\IndexControllerFactory::class,
            Controller\FoldController::class => Controller\Factory\FoldControllerFactory::class,
        ],
    ],
    'access_filter' => [
        'controllers' => [
            Controller\IndexController::class => [
                // Allow access to authenticated users.
                ['actions' => '*', 'allow' => '@']
            ],
            Controller\FoldController::class => [
                // Allow access to authenticated users.
                ['actions' => '*', 'allow' => '@']
            ],
        ],
    ],    
    'service_manager' => [
        'factories' => [
            Service\GoodMapManager::class => Service\Factory\GoodMapManagerFactory::class,
            Service\FoldManager::class => Service\Factory\FoldManagerFactory::class,
        ],
    ],    
    'view_manager' => [
        'template_path_stack' => [
            'ai' => __DIR__ . '/../view',
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

