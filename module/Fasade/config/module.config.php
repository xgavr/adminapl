<?php
namespace Fasade;

use Laminas\ServiceManager\Factory\InvokableFactory;
use Laminas\Router\Http\Literal;
use Laminas\Router\Http\Segment;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;

return [
    'router' => [
        'routes' => [
            'fasade' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/fasade[/:action[/:id]]',
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
            'catalog' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/catalog[/:action[/:id]]',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[0-9]*',
                    ],
                    'defaults' => [
                        'controller'    => Controller\IndexController::class,
                        'action'        => 'catalog',
                    ],
                ],
            ],
            'group-site' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/group-site[/:action[/:id]]',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[0-9]*'
                    ],
                    'defaults' => [
                        'controller'    => Controller\GroupSiteController::class,
                        'action'        => 'index',
                    ],
                ],
            ],        
        ],
    ],    
    'controllers' => [
        'factories' => [
            Controller\GroupSiteController::class => Controller\Factory\GroupSiteControllerFactory::class,
            Controller\IndexController::class => Controller\Factory\IndexControllerFactory::class,
        ],
    ],
    'access_filter' => [
        'controllers' => [
            Controller\GroupSiteController::class => [
                // Allow access to authenticated users.
                ['actions' => '*', 'allow' => '@']
            ],
            Controller\IndexController::class => [
                // Allow access to authenticated users.
                ['actions' => '*', 'allow' => '@']
            ],
        ],
    ],    
    'service_manager' => [
        'factories' => [
            Service\FasadeManager::class => Service\Factory\FasadeManagerFactory::class,
            Service\GroupSiteManager::class => Service\Factory\GroupSiteManagerFactory::class,
        ],
    ],    
    'view_manager' => [
        'template_path_stack' => [
            'Fasade' => __DIR__ . '/../view',
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

